<?php
/**
 * Tests for LLMS_Voucher redemption limits.
 *
 * @package LifterLMS/Tests
 *
 * @group voucher
 *
 * @since 11.1.1
 */
class LLMS_Test_Voucher extends LLMS_UnitTestCase {

	/**
	 * A code stops granting enrollments once its redemption limit is reached.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_stops_at_redemption_limit() {

		$voucher = $this->create_voucher( 1, 2 );
		$code    = $voucher->get_voucher_codes()[0];
		$product = $voucher->get_products()[0];

		$redeemed = 0;
		while ( $redeemed < 2 ) {
			$user_id = $this->factory->user->create();
			$this->assertTrue( $voucher->use_voucher( $code->code, $user_id ) );
			$this->assertTrue( llms_is_user_enrolled( $user_id, $product ) );
			++$redeemed;
		}

		$third  = $this->factory->user->create();
		$result = $voucher->use_voucher( $code->code, $third );

		$this->assertWPErrorCodeEquals( 'max', $result );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" has already been redeemed the maximum number of times.', $code->code ), $result );
		$this->assertFalse( llms_is_user_enrolled( $third, $product ) );
		$this->assertSame( 2, $this->count_redemptions( $code->id ) );
	}

	/**
	 * The same user cannot redeem one code twice.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_rejects_repeat_redemption_by_same_user() {

		$voucher = $this->create_voucher( 1, 2 );
		$code    = $voucher->get_voucher_codes()[0];
		$user_id = $this->factory->user->create();

		$this->assertTrue( $voucher->use_voucher( $code->code, $user_id ) );

		$result = $voucher->use_voucher( $code->code, $user_id );

		$this->assertWPErrorCodeEquals( 'error', $result );
		$this->assertWPErrorMessageEquals( 'You have already redeemed this voucher.', $result );
		$this->assertSame( 1, $this->count_redemptions( $code->id ) );
	}

	/**
	 * A redemption saved by an overlapping request while this one waited for the lock counts toward the limit.
	 *
	 * Checking the limit before the lock is acquired would let this redemption through.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_checks_limit_after_lock() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$product = $voucher->get_products()[0];
		$first   = $this->factory->user->create();
		$second  = $this->factory->user->create();

		$result = $this->use_voucher_after_competing_redemption( $voucher, $code, $first, $second );

		$this->assertWPErrorCodeEquals( 'max', $result );
		$this->assertWPErrorMessageEquals( sprintf( 'Voucher code "%s" has already been redeemed the maximum number of times.', $code->code ), $result );
		$this->assertSame( 1, $this->count_redemptions( $code->id ) );
		$this->assertFalse( llms_is_user_enrolled( $second, $product ) );
	}

	/**
	 * A redemption saved by the same user's overlapping request while this one waited for the lock is found.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_checks_user_after_lock() {

		$voucher = $this->create_voucher( 1, 2 );
		$code    = $voucher->get_voucher_codes()[0];
		$product = $voucher->get_products()[0];
		$user_id = $this->factory->user->create();

		$result = $this->use_voucher_after_competing_redemption( $voucher, $code, $user_id, $user_id );

		$this->assertWPErrorCodeEquals( 'error', $result );
		$this->assertWPErrorMessageEquals( 'You have already redeemed this voucher.', $result );
		$this->assertSame( 1, $this->count_redemptions( $code->id ) );
		$this->assertFalse( llms_is_user_enrolled( $user_id, $product ) );
	}

	/**
	 * Redeem after an overlapping request saves a redemption while this one waits for the lock.
	 *
	 * The competing row is saved from the `query` filter when the lock is asked for.
	 *
	 * @since 11.1.1
	 *
	 * @param LLMS_Voucher $voucher            Voucher being redeemed.
	 * @param object       $code               Voucher code row.
	 * @param int          $competing_user_id  User ID written into the competing redemption.
	 * @param int          $redeeming_user_id  User ID passed to use_voucher().
	 * @return true|WP_Error
	 */
	private function use_voucher_after_competing_redemption( $voucher, $code, $competing_user_id, $redeeming_user_id ) {

		$race = function ( $query ) use ( $voucher, $code, $competing_user_id, &$race ) {
			if ( false !== strpos( $query, 'GET_LOCK' ) ) {
				remove_filter( 'query', $race );
				$voucher->save_redeemed_code(
					array(
						'user_id' => $competing_user_id,
						'code_id' => $code->id,
					)
				);
			}
			return $query;
		};

		add_filter( 'query', $race );

		try {
			return $voucher->use_voucher( $code->code, $redeeming_user_id );
		} finally {
			remove_filter( 'query', $race );
		}
	}

	/**
	 * The redemption is saved and the lock released before the user is enrolled, and the lock is released when a redemption is refused.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_releases_lock() {

		global $wpdb;

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$user_id = $this->factory->user->create();

		$locks = array();
		$spy   = function ( $query ) use ( &$locks ) {
			if ( preg_match( "/GET_LOCK\\( '([^']+)'/", $query, $m ) ) {
				$locks[] = $m[1];
			}
			return $query;
		};
		add_filter( 'query', $spy );

		// IS_FREE_LOCK() returns 0 while any connection, including this one, holds the lock.
		$during_enrollment = array();
		$on_enrollment     = function () use ( &$during_enrollment, &$locks, $code, $wpdb ) {
			$during_enrollment[] = array(
				'free'        => $wpdb->get_var( $wpdb->prepare( 'SELECT IS_FREE_LOCK( %s )', $locks[0] ) ),
				'redemptions' => $this->count_redemptions( $code->id ),
			);
		};
		add_action( 'llms_user_enrolled_in_course', $on_enrollment );

		$this->assertTrue( $voucher->use_voucher( $code->code, $user_id ) );

		remove_action( 'llms_user_enrolled_in_course', $on_enrollment );

		$this->assertSame(
			array(
				array(
					'free'        => '1',
					'redemptions' => 1,
				),
			),
			$during_enrollment
		);

		// Refused: the code is used up.
		$this->assertWPErrorCodeEquals( 'max', $voucher->use_voucher( $code->code, $this->factory->user->create() ) );

		// Refused: the code doesn't exist.
		$this->assertWPErrorCodeEquals( 'not-found', $voucher->use_voucher( 'not-a-code', $user_id ) );

		remove_filter( 'query', $spy );

		$this->assertCount( 3, $locks );
		foreach ( array_unique( $locks ) as $lock ) {
			$this->assertSame( '1', $wpdb->get_var( $wpdb->prepare( 'SELECT IS_FREE_LOCK( %s )', $lock ) ) );
		}
		$this->assertCount( 2, array_unique( $locks ) );
	}

	/**
	 * Databases that can't provide the redemption lock.
	 *
	 * @since 11.1.1
	 *
	 * @return array[]
	 */
	public function provider_lock_unavailable() {
		return array(
			'not supported, the query fails' => array( 'SELECT GET_LOCK_UNSUPPORTED()' ),
			'refused, the query returns NULL' => array( 'SELECT NULL' ),
			'held elsewhere until the timeout' => array( 'SELECT 0' ),
		);
	}

	/**
	 * Without the lock a code is still redeemed, the limit still applies, and no database error is printed.
	 *
	 * @since 11.1.1
	 *
	 * @dataProvider provider_lock_unavailable
	 *
	 * @param string $replacement SQL run in place of the lock query.
	 * @return void
	 */
	public function test_use_voucher_without_lock( $replacement ) {

		global $wpdb;

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$product = $voucher->get_products()[0];
		$first   = $this->factory->user->create();
		$second  = $this->factory->user->create();

		$queries     = array();
		$unavailable = function ( $query ) use ( &$queries, $replacement ) {
			$queries[] = $query;
			return false !== strpos( $query, 'GET_LOCK' ) ? $replacement : $query;
		};
		add_filter( 'query', $unavailable );

		// A database error that isn't suppressed would be printed into the page.
		$show = $wpdb->show_errors();
		ob_start();

		$redeemed = $voucher->use_voucher( $code->code, $first );
		$repeated = $voucher->use_voucher( $code->code, $first );
		$used_up  = $voucher->use_voucher( $code->code, $second );

		$output = ob_get_clean();
		$wpdb->show_errors( $show );
		remove_filter( 'query', $unavailable );

		$this->assertTrue( $redeemed );
		$this->assertTrue( llms_is_user_enrolled( $first, $product ) );
		$this->assertWPErrorCodeEquals( 'max', $repeated );
		$this->assertWPErrorCodeEquals( 'max', $used_up );
		$this->assertFalse( llms_is_user_enrolled( $second, $product ) );
		$this->assertSame( 1, $this->count_redemptions( $code->id ) );
		$this->assertSame( '', $output );
		$this->assertCount( 3, preg_grep( '/GET_LOCK/', $queries ) );
		$this->assertEmpty( preg_grep( '/RELEASE_LOCK/', $queries ) );
	}

	/**
	 * With the lock turned off a code is still redeemed and the limit still applies.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_use_voucher_with_lock_off() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];
		$product = $voucher->get_products()[0];
		$first   = $this->factory->user->create();

		$queries = array();
		$spy     = function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $spy );
		add_filter( 'llms_lock_timeout', '__return_zero' );

		$redeemed = $voucher->use_voucher( $code->code, $first );
		$used_up  = $voucher->use_voucher( $code->code, $this->factory->user->create() );

		remove_filter( 'llms_lock_timeout', '__return_zero' );
		remove_filter( 'query', $spy );

		$this->assertTrue( $redeemed );
		$this->assertTrue( llms_is_user_enrolled( $first, $product ) );
		$this->assertWPErrorCodeEquals( 'max', $used_up );
		$this->assertSame( 1, $this->count_redemptions( $code->id ) );
		$this->assertEmpty( preg_grep( '/(GET|RELEASE)_LOCK/', $queries ) );
	}

	/**
	 * Count redemption rows for a voucher code.
	 *
	 * @since 11.1.1
	 *
	 * @param int $code_id Voucher code ID.
	 * @return int
	 */
	private function count_redemptions( $code_id ) {

		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM {$wpdb->prefix}lifterlms_voucher_code_redemptions WHERE code_id = %d",
				$code_id
			)
		);
	}
}
