<?php
/**
 * Tests for LLMS_Site
 *
 * @package LifterLMS/Tests
 *
 * @group site
 *
 * @since 3.7.4
 */
class LLMS_Test_Site extends LLMS_UnitTestCase {

	/**
	 * Test clear_lock_url() function
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function test_clear_lock_url() {

		update_option( 'llms_site_url', 'http://mockurl.tld/' );
		LLMS_Site::clear_lock_url();
		$this->assertEquals( '', get_option( 'llms_site_url' ) );

	}

	/**
	 * Test check_status() method
	 *
	 * @since 4.12.0
	 *
	 * @return void
	 */
	public function test_check_status() {

		$actions  = did_action( 'llms_site_clone_detected' );
		$original = get_site_url();

		// Not a clone.
		$this->assertFalse( LLMS_Site::check_status() );

		// Simulate the site being cloned.
		update_option( 'siteurl', 'http://fakeurl.tld' );

		$this->assertTrue( LLMS_Site::check_status() );
		$this->assertSame( ++$actions, did_action( 'llms_site_clone_detected' ) );

		// Site has been ignored.
		update_option( 'llms_site_url_ignore', 'yes' );
		$this->assertFalse( LLMS_Site::check_status() );

		// Restore URL.
		update_option( 'siteurl', $original );

	}

	/**
	 * Test lock url getter and setter functions
	 *
	 * @since 3.8.0
	 * @since 4.12.0 Added urls with "www".
	 * @since 5.9.0 Pass an explicit integer to `substr_replace()`.
	 *
	 * @return void
	 */
	public function test_get_set_lock_url() {

		$urls = array(
			'https://whatever.com',
			'http://whatever.com',
			'https://www.whatever.com',
			'http://www.whatever.com',
			'https://w.com',
			'https://whatever-with-a-dash.net',
			'http://wh.at',
			'http://wah.tld',
			'http://waht.tld',
		);

		foreach ( $urls as $url ) {

			update_option( 'siteurl', $url );

			$site_url = get_site_url();

			// This is what the lock url should be.
			$lock_url = substr_replace( $site_url, LLMS_Site::$lock_string, intval( strlen( $site_url ) / 2 ), 0 );

			// Make sure they match.
			$this->assertEquals( $lock_url, LLMS_Site::get_lock_url() );

			// Save it.
			LLMS_Site::set_lock_url();

			// Make sure it saves the right option.
			$this->assertEquals( $lock_url, get_option( 'llms_site_url' ) );

			// This should match the original URL.
			$this->assertEquals( $site_url, LLMS_Site::get_url() );

		}

	}

	/**
	 * Test feature getter and setter functions
	 *
	 * @since 3.8.0
	 * @since 4.12.0 Test against feature constants.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_get_set_features() {

		// Should return an array of defaults even when option doesnt exist.
		delete_option( 'llms_site_get_features' );
		$this->assertTrue( is_array( LLMS_Site::get_features() ) );

		// Fake feature always returns false.
		$this->assertFalse( LLMS_Site::get_feature( 'mock_feature' ) );

		// Test getters/setters with a real feature.
		LLMS_Site::update_feature( 'recurring_payments', true );
		$this->assertTrue( LLMS_Site::get_feature( 'recurring_payments' ) );

		LLMS_Site::update_feature( 'recurring_payments', false );
		$this->assertFalse( LLMS_Site::get_feature( 'recurring_payments' ) );

		// Constant not set.
		$this->assertNull( LLMS_Unit_Test_Util::call_method( 'LLMS_Site', 'get_feature_constant', array( 'recurring_payments' ) ) );
		$this->assertFalse( LLMS_Site::get_feature( 'recurring_payments' ) );

		// Constant is set.
		llms_maybe_define_constant( 'LLMS_SITE_FEATURE_RECURRING_PAYMENTS', true );
		$this->assertTrue( LLMS_Site::get_feature( 'recurring_payments' ) );

	}


	/**
	 * Test is_clone() function
	 *
	 * @since 3.7.4
	 *
	 * @return void
	 */
	public function test_is_clone() {

		$original = get_site_url();

		// Not a clone because the url is the lock url.
		$this->assertFalse( LLMS_Site::is_clone() );

		// The url has changed.
		update_option( 'siteurl', 'http://fakeurl.tld' );
		$this->assertTrue( LLMS_Site::is_clone() );

		// Change it back to the original.
		update_option( 'siteurl', $original );
		$this->assertFalse( LLMS_Site::is_clone() );

		// Change the schema (should not be identified as a clone).
		update_option( 'siteurl', set_url_scheme( $original, 'https' ) );
		$this->assertFalse( LLMS_Site::is_clone() );

	}

	/**
	 * Test is_clone() when using a constant set to `true`.
	 *
	 * @since 4.13.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_is_clone_constant_true() {

		// Not a clone.
		$this->assertFalse( LLMS_Site::is_clone() );

		define( 'LLMS_SITE_IS_CLONE', true );
		$this->assertTrue( LLMS_Site::is_clone() );

	}

	/**
	 * Test is_clone() when using a constant set to `false`.
	 *
	 * @since 4.13.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_is_clone_constant_false() {

		$original = get_site_url();

		// Is a clone.
		update_option( 'siteurl', 'http://fakeurl.tld' );
		$this->assertTrue( LLMS_Site::is_clone() );

		define( 'LLMS_SITE_IS_CLONE', false );
		$this->assertFalse( LLMS_Site::is_clone() );

		update_option( 'siteurl', $original );

	}

	/**
	 * Test is_clone_ignored() function
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function test_is_clone_ignored() {

		$this->assertFalse( LLMS_Site::is_clone_ignored() );

		update_option( 'llms_site_url_ignore', 'yes' );
		$this->assertTrue( LLMS_Site::is_clone_ignored() );

		update_option( 'llms_site_url_ignore', 'no' );
		$this->assertFalse( LLMS_Site::is_clone_ignored() );

		update_option( 'llms_site_url_ignore', 'mock' );
		$this->assertFalse( LLMS_Site::is_clone_ignored() ) ;

	}

}
