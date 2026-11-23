<?php
/**
 * Tests for the LLMS_Locks class
 *
 * @package LifterLMS/Tests
 *
 * @group locks
 *
 * @since [version]
 */
class LLMS_Test_Locks extends LLMS_Unit_Test_Case {

	/**
	 * A second database connection, standing in for an overlapping request.
	 *
	 * @var wpdb
	 */
	private $other;

	/**
	 * Lock keys acquired during a test.
	 *
	 * @var string[]
	 */
	private $keys = array( 'test_key', 'test_key_a', 'test_key_b' );

	/**
	 * Whether the test database provides named locks.
	 *
	 * Clusters such as Percona XtraDB Cluster refuse them.
	 *
	 * @var bool
	 */
	private $supported;

	/**
	 * Whether the test database lets a connection hold several named locks.
	 *
	 * MySQL before 5.7.5 and MariaDB before 10.0.2 release a connection's lock when it acquires another.
	 *
	 * @var bool
	 */
	private $multiple;

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->other = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
		$this->other->suppress_errors();

		$this->supported = $this->other_acquires( 'test_probe_a' );
		$this->multiple  = $this->supported && $this->other_acquires( 'test_probe_b' ) && $this->other_holds( 'test_probe_a' );
		$this->other_releases( 'test_probe_a' );
		$this->other_releases( 'test_probe_b' );
	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		array_map( array( 'LLMS_Locks', 'release' ), $this->keys );
		$this->other->close();
		parent::tear_down();
	}

	/**
	 * Retrieve the database name of a lock.
	 *
	 * @since [version]
	 *
	 * @param string $key Lock key.
	 * @return string
	 */
	private function get_name( $key ) {

		global $wpdb;
		return 'llms_' . md5( DB_NAME . '|' . $wpdb->prefix . '|' . $key );
	}

	/**
	 * Try to take a lock from the other connection without waiting.
	 *
	 * @since [version]
	 *
	 * @param string $key Lock key.
	 * @return bool Whether the other connection now holds the lock.
	 */
	private function other_acquires( $key ) {
		return '1' === $this->other->get_var( $this->other->prepare( 'SELECT GET_LOCK( %s, 0 )', $this->get_name( $key ) ) );
	}

	/**
	 * Determine whether the other connection holds a lock.
	 *
	 * @since [version]
	 *
	 * @param string $key Lock key.
	 * @return bool
	 */
	private function other_holds( $key ) {
		return $this->other->get_var( 'SELECT CONNECTION_ID()' ) === $this->other->get_var( $this->other->prepare( 'SELECT IS_USED_LOCK( %s )', $this->get_name( $key ) ) );
	}

	/**
	 * Skip a test that needs a database with named locks.
	 *
	 * @since [version]
	 *
	 * @param bool $multiple Whether the test holds several locks on one connection.
	 * @return void
	 */
	private function require_locks( $multiple = false ) {

		if ( ! $this->supported ) {
			$this->markTestSkipped( 'The test database does not provide named locks.' );
		}
		if ( $multiple && ! $this->multiple ) {
			$this->markTestSkipped( 'The test database holds one named lock per connection.' );
		}
	}

	/**
	 * Release a lock held by the other connection.
	 *
	 * @since [version]
	 *
	 * @param string $key Lock key.
	 * @return void
	 */
	private function other_releases( $key ) {
		$this->other->query( $this->other->prepare( 'SELECT RELEASE_LOCK( %s )', $this->get_name( $key ) ) );
	}

	/**
	 * Run a callback while lock queries are replaced, recording what the callback prints.
	 *
	 * Database errors are displayed while the callback runs, so an error that isn't suppressed ends up in the output.
	 *
	 * @since [version]
	 *
	 * @param string   $replacement SQL run in place of `GET_LOCK()` and `RELEASE_LOCK()` queries.
	 * @param callable $callback    Callback.
	 * @return array {
	 *     @type mixed    $result  The callback's return value.
	 *     @type string   $output  Anything printed while the callback ran.
	 *     @type string[] $queries The lock queries the callback tried to run.
	 * }
	 */
	private function without_locks( $replacement, $callback ) {

		global $wpdb;

		$queries = array();
		$replace = function ( $query ) use ( $replacement, &$queries ) {
			if ( preg_match( '/(GET|RELEASE)_LOCK\(/', $query ) ) {
				$queries[] = $query;
				return $replacement;
			}
			return $query;
		};

		add_filter( 'query', $replace );
		$show = $wpdb->show_errors();
		ob_start();
		try {
			$result = $callback();
		} finally {
			$output = ob_get_clean();
			$wpdb->show_errors( $show );
			remove_filter( 'query', $replace );
		}

		return compact( 'result', 'output', 'queries' );
	}

	/**
	 * Databases that can't provide a lock.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	public function provider_unavailable() {
		return array(
			'not supported, the query fails (Galera, Percona XtraDB Cluster)' => array( 'SELECT GET_LOCK_UNSUPPORTED()' ),
			'refused, the query returns NULL' => array( 'SELECT NULL' ),
			'held elsewhere until the timeout, the query returns 0' => array( 'SELECT 0' ),
		);
	}

	/**
	 * A held lock keeps an overlapping request out until it is released.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_acquire_and_release() {

		global $wpdb;

		$this->require_locks();

		$this->assertTrue( LLMS_Locks::acquire( 'test_key' ) );
		$this->assertEquals( $wpdb->get_var( 'SELECT CONNECTION_ID()' ), $wpdb->get_var( $wpdb->prepare( 'SELECT IS_USED_LOCK( %s )', $this->get_name( 'test_key' ) ) ) );
		$this->assertFalse( $this->other_acquires( 'test_key' ) );

		$this->assertTrue( LLMS_Locks::release( 'test_key' ) );
		$this->assertTrue( $this->other_acquires( 'test_key' ) );
		$this->other_releases( 'test_key' );

		// Not held anymore.
		$this->assertFalse( LLMS_Locks::release( 'test_key' ) );
	}

	/**
	 * Acquiring a lock the request already holds doesn't stack: one release frees it.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_acquire_twice_releases_once() {

		$this->require_locks();

		$this->assertTrue( LLMS_Locks::acquire( 'test_key' ) );
		$this->assertTrue( LLMS_Locks::acquire( 'test_key' ) );

		$this->assertTrue( LLMS_Locks::release( 'test_key' ) );
		$this->assertTrue( $this->other_acquires( 'test_key' ) );
		$this->other_releases( 'test_key' );
	}

	/**
	 * Locks with different keys are independent, and several can be held at once.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_keys_are_independent() {

		$this->require_locks( true );

		$this->assertTrue( $this->other_acquires( 'test_key_a' ) );

		$this->assertTrue( LLMS_Locks::acquire( 'test_key_b' ) );
		$this->assertTrue( LLMS_Locks::acquire( 'test_key' ) );
		$this->assertFalse( $this->other_acquires( 'test_key_b' ) );
		$this->assertFalse( $this->other_acquires( 'test_key' ) );

		// Releasing one keeps the other.
		$this->assertTrue( LLMS_Locks::release( 'test_key' ) );
		$this->assertFalse( $this->other_acquires( 'test_key_b' ) );

		$this->other_releases( 'test_key_a' );
	}

	/**
	 * A lock held by an overlapping request is waited for, then the request continues without it.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_acquire_times_out_when_held_elsewhere() {

		$this->require_locks();

		$this->assertTrue( $this->other_acquires( 'test_key' ) );

		$start = microtime( true );
		$this->assertFalse( LLMS_Locks::acquire( 'test_key', 1 ) );
		$this->assertGreaterThanOrEqual( 0.9, microtime( true ) - $start );

		// Nothing to release, and the other request still holds its lock.
		$this->assertFalse( LLMS_Locks::release( 'test_key' ) );
		$this->assertTrue( $this->other_holds( 'test_key' ) );

		// Available once the other request is done.
		$this->other_releases( 'test_key' );
		$this->assertTrue( LLMS_Locks::acquire( 'test_key', 1 ) );
	}

	/**
	 * Without a lock the request continues, nothing is printed, and nothing is released later.
	 *
	 * @since [version]
	 *
	 * @dataProvider provider_unavailable
	 *
	 * @param string $replacement SQL run in place of the lock query.
	 * @return void
	 */
	public function test_acquire_unavailable( $replacement ) {

		global $wpdb;

		$suppress = $wpdb->suppress_errors( false );

		$res = $this->without_locks(
			$replacement,
			function () {
				return array( LLMS_Locks::acquire( 'test_key' ), LLMS_Locks::release( 'test_key' ) );
			}
		);

		$suppressed_after = $wpdb->suppress_errors( $suppress );

		$this->assertSame( array( false, false ), $res['result'] );
		$this->assertSame( '', $res['output'] );

		// Only the attempt to acquire reached the database.
		$this->assertCount( 1, $res['queries'] );
		$this->assertStringContainsString( 'GET_LOCK', $res['queries'][0] );

		// Error reporting is back to how the caller had it.
		$this->assertFalse( $suppressed_after );

		// A real lock still works afterwards, where the database has them.
		$this->assertSame( $this->supported, LLMS_Locks::acquire( 'test_key' ) );
	}

	/**
	 * Whatever the test database supports, asking for a lock prints nothing and reports whether the lock is held.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_acquire_on_test_database() {

		global $wpdb;

		$show = $wpdb->show_errors();
		ob_start();
		$acquired = LLMS_Locks::acquire( 'test_key' );
		$released = LLMS_Locks::release( 'test_key' );
		$output   = ob_get_clean();
		$wpdb->show_errors( $show );

		$this->assertSame( '', $output );
		$this->assertSame( $this->supported, $acquired );
		$this->assertSame( $this->supported, $released );
	}

	/**
	 * The timeout filter can turn a lock off, and sets how long to wait.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_timeout_filter() {

		$seen = array();
		$off  = function ( $timeout, $key ) use ( &$seen ) {
			$seen[] = array( $timeout, $key );
			return 0;
		};

		add_filter( 'llms_lock_timeout', $off, 10, 2 );
		$res = $this->without_locks(
			'SELECT 1',
			function () {
				return LLMS_Locks::acquire( 'test_key', 7 );
			}
		);
		remove_filter( 'llms_lock_timeout', $off, 10 );

		$this->assertFalse( $res['result'] );
		$this->assertSame( array(), $res['queries'] );
		$this->assertSame( array( array( 7, 'test_key' ) ), $seen );

		// A filtered timeout is the one sent to the database.
		$one_second = function () {
			return 1;
		};
		add_filter( 'llms_lock_timeout', $one_second );
		$res = $this->without_locks(
			'SELECT 0',
			function () {
				return LLMS_Locks::acquire( 'test_key', 7 );
			}
		);
		remove_filter( 'llms_lock_timeout', $one_second );

		$this->assertStringContainsString( "GET_LOCK( '" . $this->get_name( 'test_key' ) . "', 1 )", $res['queries'][0] );
	}

	/**
	 * Database drop-ins that send reads to replicas.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	public function provider_replica_drop_ins() {
		return array(
			'HyperDB'     => array( 'LLMS_Test_Locks_Hyperdb', 'send_reads_to_masters' ),
			'LudicrousDB' => array( 'LLMS_Test_Locks_Ludicrousdb', 'send_reads_to_primaries' ),
		);
	}

	/**
	 * With a database drop-in that sends reads to replicas, the lock is taken on the primary.
	 *
	 * @since [version]
	 *
	 * @dataProvider provider_replica_drop_ins
	 *
	 * @param string $drop_in  Name of the class standing in for the drop-in's `$wpdb`.
	 * @param string $expected The method expected to be called before the lock query.
	 * @return void
	 */
	public function test_acquire_sends_reads_to_primary( $drop_in, $expected ) {

		global $wpdb;

		$original = $wpdb;
		$wpdb     = new $drop_in( $original ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restored below.

		try {
			$this->assertSame( $this->supported, LLMS_Locks::acquire( 'test_key' ) );
			$this->assertSame( array( $expected, 'GET_LOCK' ), $wpdb->log );
		} finally {
			$wpdb = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}

/**
 * Stands in for HyperDB's `$wpdb`.
 *
 * @since [version]
 */
class LLMS_Test_Locks_Hyperdb {

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix;

	/**
	 * Calls made, in order.
	 *
	 * @var string[]
	 */
	public $log = array();

	/**
	 * The real database connection.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @param wpdb $db The real database connection.
	 */
	public function __construct( $db ) {
		$this->db     = $db;
		$this->prefix = $db->prefix;
	}

	/**
	 * Send the rest of the request's reads to the primary.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function send_reads_to_masters() {
		$this->log[] = __FUNCTION__;
	}

	/**
	 * Run a query and return its first value.
	 *
	 * @since [version]
	 *
	 * @param string $query SQL.
	 * @return string|null
	 */
	public function get_var( $query ) {
		$this->log[] = strtok( substr( $query, 7 ), '(' );
		return $this->db->get_var( $query );
	}

	/**
	 * Pass everything else to the real connection.
	 *
	 * @since [version]
	 *
	 * @param string $name Method name.
	 * @param array  $args Arguments.
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		return $this->db->$name( ...$args );
	}
}

/**
 * Stands in for LudicrousDB's `$wpdb`, which has both methods and reports the older one as deprecated.
 *
 * @since [version]
 */
class LLMS_Test_Locks_Ludicrousdb extends LLMS_Test_Locks_Hyperdb {

	/**
	 * Send the rest of the request's reads to the primary.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function send_reads_to_primaries() {
		$this->log[] = __FUNCTION__;
	}
}
