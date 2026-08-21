<?php
/**
 * Tests for LLMS_Media_Protector
 *
 * @package LifterLMS/Tests
 *
 * @group media_protection
 *
 * @since 10.1.0
 */
class LLMS_Test_Media_Protector extends LLMS_UnitTestCase {

	/**
	 * Cache group used by the media protector.
	 *
	 * @var string
	 */
	private const CACHE_GROUP = 'llms_media_authorization';

	/**
	 * Set up before class.
	 *
	 * Loads the media protector class file.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-media-protector.php';
	}

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		wp_cache_flush_group( self::CACHE_GROUP );
	}

	/**
	 * Build a cache key for a given media/user pair to match the key format used internally.
	 *
	 * @param int $media_id Media post ID.
	 * @param int $user_id  User ID.
	 * @return string
	 */
	private function build_cache_key( $media_id, $user_id ) {
		return 'llms-media-authorization-' . $media_id . '-' . $user_id;
	}

	/**
	 * Test that invalidate_authorization_cache() deletes a pre-existing entry for the supplied user.
	 *
	 * @return void
	 */
	public function test_invalidate_authorization_cache_deletes_for_supplied_user() {

		$user_id   = $this->factory->user->create();
		$media_id  = 1234;
		$cache_key = $this->build_cache_key( $media_id, $user_id );

		// Seed the cache with a previously cached unprotected sentinel.
		wp_cache_set( $cache_key, 'null', self::CACHE_GROUP, MINUTE_IN_SECONDS );
		$this->assertSame( 'null', wp_cache_get( $cache_key, self::CACHE_GROUP ) );

		( new LLMS_Media_Protector() )->invalidate_authorization_cache( $media_id, $user_id );

		$found = false;
		$value = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		$this->assertFalse( $found );
		$this->assertFalse( $value );
	}

	/**
	 * Test that invalidate_authorization_cache() without a user argument targets the current user only.
	 *
	 * @return void
	 */
	public function test_invalidate_authorization_cache_defaults_to_current_user() {

		$current_user_id = $this->factory->user->create();
		$other_user_id   = $this->factory->user->create();

		wp_set_current_user( $current_user_id );
		$this->assertSame( $current_user_id, get_current_user_id() );

		$protector = new LLMS_Media_Protector();
		$media_id  = 9999;

		wp_cache_set( $this->build_cache_key( $media_id, $current_user_id ), 'null', self::CACHE_GROUP, MINUTE_IN_SECONDS );
		wp_cache_set( $this->build_cache_key( $media_id, $other_user_id ), 'null', self::CACHE_GROUP, MINUTE_IN_SECONDS );

		$protector->invalidate_authorization_cache( $media_id );

		$current_found = false;
		wp_cache_get( $this->build_cache_key( $media_id, $current_user_id ), self::CACHE_GROUP, false, $current_found );
		$this->assertFalse( $current_found );

		// Other user's entry must remain intact.
		$this->assertSame( 'null', wp_cache_get( $this->build_cache_key( $media_id, $other_user_id ), self::CACHE_GROUP ) );
	}

	/**
	 * Test that add_authorization_meta_to_media_post() invalidates the cached authorization for the current user.
	 *
	 * Reproduces the bug in issue #3167: with a persistent object cache the cached
	 * authorization result survives the protection state change and the URL is not rewritten.
	 *
	 * @return void
	 */
	public function test_add_authorization_meta_invalidates_cached_authorization() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$attachment_id = $this->factory->post->create(
			array(
				'post_type'  => 'attachment',
				'post_author' => $user_id,
			)
		);

		$cache_key = $this->build_cache_key( $attachment_id, $user_id );

		// Simulate a previously cached "unprotected" sentinel for this user.
		wp_cache_set( $cache_key, 'null', self::CACHE_GROUP, MINUTE_IN_SECONDS );

		( new LLMS_Media_Protector() )->add_authorization_meta_to_media_post( $attachment_id );

		$found = false;
		wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );

		$this->assertFalse( $found, 'add_authorization_meta_to_media_post() must invalidate the cached authorization result.' );
	}

	/**
	 * Test that is_authorized_to_view() overwrites a previously cached 'null' value once the media is protected.
	 *
	 * This verifies the fix to use wp_cache_set() in place of wp_cache_add() so a stale sentinel
	 * cannot be retained by a persistent object cache.
	 *
	 * @return void
	 */
	public function test_is_authorized_to_view_overwrites_stale_null_cache() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$attachment_id = $this->factory->post->create(
			array(
				'post_type'   => 'attachment',
				'post_author' => $user_id,
			)
		);

		$cache_key = $this->build_cache_key( $attachment_id, $user_id );

		// Simulate stale cache: media was viewed while unprotected, so 'null' was cached.
		wp_cache_set( $cache_key, 'null', self::CACHE_GROUP, MINUTE_IN_SECONDS );

		// Add the protection meta and trigger invalidation.
		( new LLMS_Media_Protector() )->add_authorization_meta_to_media_post( $attachment_id );

		$protector = new LLMS_Media_Protector();

		// The current user is the author, so authorization must be true.
		$is_authorized = $protector->is_authorized_to_view( $user_id, $attachment_id );

		$this->assertTrue( $is_authorized );

		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		$this->assertNotSame( 'null', $cached, 'Cached value should be overwritten once protection is set.' );
		$this->assertTrue( (bool) $cached );
	}

	/**
	 * Create a protected attachment post with a real file in the uploads directory.
	 *
	 * @since [version]
	 *
	 * @return int Attachment post ID.
	 */
	private function create_protected_attachment() {

		$author_id     = $this->factory->user->create();
		$attachment_id = $this->factory->post->create(
			array(
				'post_type'   => 'attachment',
				'post_author' => $author_id,
			)
		);

		$upload_dir = wp_upload_dir();
		$filename   = 'llms-signed-url-test-' . $attachment_id . '.pdf';
		file_put_contents( trailingslashit( $upload_dir['basedir'] ) . $filename, 'test file contents' );

		update_post_meta( $attachment_id, '_wp_attached_file', $filename );
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_test_media_authorization' );

		return $attachment_id;
	}

	/**
	 * Mock the current request with the query args parsed from a signed URL.
	 *
	 * @since [version]
	 *
	 * @param string $url Signed URL as returned by LLMS_Media_Protector::get_signed_url().
	 * @return array Parsed query args.
	 */
	private function mock_signed_request( $url ) {

		$args = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $args );
		$this->mockGetRequest( $args );

		return $args;
	}

	/**
	 * Test that get_signed_url() produces a URL with the expected parameters.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_signed_url() {

		$attachment_id = $this->create_protected_attachment();
		$url           = ( new LLMS_Media_Protector() )->get_signed_url( $attachment_id );

		$args = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $args );

		$this->assertEquals( $attachment_id, $args[ LLMS_Media_Protector::URL_PARAMETER_ID ] );
		$this->assertGreaterThan( time(), (int) $args[ LLMS_Media_Protector::URL_PARAMETER_EXPIRES ] );
		$this->assertMatchesRegularExpression( '/^[0-9a-f]{64}$/', $args[ LLMS_Media_Protector::URL_PARAMETER_TOKEN ] );

		// The cosmetic file name parameter is last so the URL ends with the file extension.
		$this->assertStringEndsWith( '.pdf', $url );
	}

	/**
	 * Test that get_signed_url() returns an empty string for invalid media IDs.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_signed_url_invalid_media() {

		$protector = new LLMS_Media_Protector();

		$this->assertSame( '', $protector->get_signed_url( 0 ) );
		$this->assertSame( '', $protector->get_signed_url( 999999999 ) );

		// Non-attachment posts cannot be signed.
		$this->assertSame( '', $protector->get_signed_url( $this->factory->post->create() ) );
	}

	/**
	 * Test that the llms_media_signed_url_ttl filter controls the expiration timestamp.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_signed_url_ttl_filter() {

		$attachment_id = $this->create_protected_attachment();

		$ttl_filter = function () {
			return 5 * MINUTE_IN_SECONDS;
		};
		add_filter( 'llms_media_signed_url_ttl', $ttl_filter );

		$url = ( new LLMS_Media_Protector() )->get_signed_url( $attachment_id, DAY_IN_SECONDS );

		remove_filter( 'llms_media_signed_url_ttl', $ttl_filter );

		$args = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $args );

		$expires = (int) $args[ LLMS_Media_Protector::URL_PARAMETER_EXPIRES ];
		$this->assertLessThanOrEqual( time() + 5 * MINUTE_IN_SECONDS, $expires );
	}

	/**
	 * Test that a signed URL validates via is_valid_signed_request().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_valid_signed_request() {

		$attachment_id = $this->create_protected_attachment();
		$protector     = new LLMS_Media_Protector();

		$this->mock_signed_request( $protector->get_signed_url( $attachment_id ) );

		$this->assertTrue( $protector->is_valid_signed_request( $attachment_id ) );
	}

	/**
	 * Test that an expired token is rejected.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_valid_signed_request_expired() {

		$attachment_id = $this->create_protected_attachment();
		$protector     = new LLMS_Media_Protector();

		$expires = time() - 10;
		$token   = LLMS_Unit_Test_Util::call_method( $protector, 'get_signed_url_token', array( $attachment_id, $expires ) );

		$this->mockGetRequest(
			array(
				LLMS_Media_Protector::URL_PARAMETER_ID      => $attachment_id,
				LLMS_Media_Protector::URL_PARAMETER_EXPIRES => $expires,
				LLMS_Media_Protector::URL_PARAMETER_TOKEN   => $token,
			)
		);

		$this->assertFalse( $protector->is_valid_signed_request( $attachment_id ) );
	}

	/**
	 * Test that tampered tokens and parameters are rejected.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_valid_signed_request_tampered() {

		$attachment_id = $this->create_protected_attachment();
		$protector     = new LLMS_Media_Protector();

		$args = $this->mock_signed_request( $protector->get_signed_url( $attachment_id ) );

		// Tampered token.
		$this->mockGetRequest(
			array_merge(
				$args,
				array( LLMS_Media_Protector::URL_PARAMETER_TOKEN => str_repeat( '0', 64 ) )
			)
		);
		$this->assertFalse( $protector->is_valid_signed_request( $attachment_id ) );

		// Extended expiration with the original token.
		$this->mockGetRequest(
			array_merge(
				$args,
				array( LLMS_Media_Protector::URL_PARAMETER_EXPIRES => time() + YEAR_IN_SECONDS )
			)
		);
		$this->assertFalse( $protector->is_valid_signed_request( $attachment_id ) );

		// Token minted for another file.
		$other_attachment_id = $this->create_protected_attachment();
		$this->mockGetRequest( $args );
		$this->assertFalse( $protector->is_valid_signed_request( $other_attachment_id ) );

		// Missing token / expires.
		$this->mockGetRequest( array( LLMS_Media_Protector::URL_PARAMETER_ID => $attachment_id ) );
		$this->assertFalse( $protector->is_valid_signed_request( $attachment_id ) );
	}

	/**
	 * Test that serve_file() authorizes a valid signed request for a protected file
	 * even when the current user is not authorized to view it.
	 *
	 * The `llms_media_serve_method` filter runs immediately after the authorization
	 * decision and receives it, making it a safe observation point which avoids
	 * actually serving the file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_serve_file_authorizes_valid_signed_request() {

		$attachment_id = $this->create_protected_attachment();
		$protector     = new LLMS_Media_Protector();

		// The current (anonymous) user is not authorized on their own.
		wp_set_current_user( 0 );
		add_filter( 'llms_test_media_authorization', '__return_false' );
		$this->assertFalse( $protector->is_authorized_to_view( 0, $attachment_id ) );

		$this->mock_signed_request( $protector->get_signed_url( $attachment_id ) );

		$authorized   = null;
		$interceptor  = function ( $serve_method, $media_id, $is_authorized ) use ( &$authorized ) {
			$authorized = $is_authorized;
			throw new LLMS_Unit_Test_Exception_Exit( 'intercepted' );
		};
		add_filter( 'llms_media_serve_method', $interceptor, 10, 3 );

		// Swallow "headers already sent" warnings raised by header() calls in the CLI test environment.
		set_error_handler(
			function ( $errno, $errstr ) {
				return false !== strpos( $errstr, 'Cannot modify header information' );
			},
			E_WARNING
		);

		try {
			$protector->serve_file();
		} catch ( LLMS_Unit_Test_Exception_Exit $exception ) {
			// Expected: serving was intercepted after the authorization decision.
		} finally {
			restore_error_handler();
			remove_filter( 'llms_media_serve_method', $interceptor );
			remove_filter( 'llms_test_media_authorization', '__return_false' );
		}

		$this->assertTrue( $authorized );
	}

	/**
	 * Test that handle_upload() stores the supplied authorization hook name.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_handle_upload_persists_custom_hook_name() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( 'llms-media-protector-test.txt' );
		file_put_contents( $tmp, 'test' );

		$_FILES['file'] = array(
			'name'     => 'llms-media-protector-test.txt',
			'tmp_name' => $tmp,
			'type'     => 'text/plain',
			'error'    => 0,
			'size'     => filesize( $tmp ),
		);

		$protector = new LLMS_Media_Protector();
		$media_id  = $protector->handle_upload(
			'file',
			0,
			'llms_test_authorize_media_view',
			array(),
			array(
				'test_form' => false,
				'action'    => 'testing',
			)
		);

		unset( $_FILES['file'] );

		$this->assertFalse( is_wp_error( $media_id ), is_wp_error( $media_id ) ? $media_id->get_error_message() : '' );
		$this->assertSame( 'llms_test_authorize_media_view', $protector->get_authorization_filter_name( $media_id ) );
	}
}
