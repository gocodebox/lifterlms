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
}
