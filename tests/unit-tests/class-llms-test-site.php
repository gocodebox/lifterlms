<?php
/**
 * Tests for LLMS_Site
 * @since    3.7.4
 * @version  3.7.4
 */
class LLMS_Test_Site extends LLMS_UnitTestCase {

	/**
	 * Test clear_lock_url() function
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function test_clear_lock_url() {

		update_option( 'llms_site_url', 'http://mockurl.tld/' );
		LLMS_Site::clear_lock_url();
		$this->assertEquals( '', get_option( 'llms_site_url' ) );

	}

	/**
	 * Test lock url getter and setter functions
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function test_get_set_lock_url() {

		$urls = array(
			'https://whatever.com',
			'http://whatever.com',
			'https://w.com',
			'https://whatever-with-a-dash.net',
			'http://wh.at',
			'http://wah.tld',
			'http://waht.tld',
		);

		foreach ( $urls as $url ) {

			update_option( 'siteurl', $url );

			$site_url = get_site_url();

			// this is what the lock url should be
			$lock_url = substr_replace( $site_url, LLMS_Site::$lock_string, strlen( $site_url ) / 2, 0 );

			// make sure they match
			$this->assertEquals( $lock_url, LLMS_Site::get_lock_url() );

			// save it
			LLMS_Site::set_lock_url();

			// make sure it saves the right option
			$this->assertEquals( $lock_url, get_option( 'llms_site_url' ) );

			// this should match the original URL
			$this->assertEquals( $site_url, LLMS_Site::get_url() );

		}

	}

	/**
	 * Test feature getter and setter functions
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function test_get_set_features() {

		// should return an array of defaults even when option doesnt exist
		delete_option( 'llms_site_get_features' );
		$this->assertTrue( is_array( LLMS_Site::get_features() ) );

		// fake feature always returns false
		$this->assertFalse( LLMS_Site::get_feature( 'mock_feature' ) );

		foreach ( array( 'recurring_payments' ) as $feature ) {

			// test getters/setters
			LLMS_Site::update_feature( $feature, true );
			$this->assertTrue( LLMS_Site::get_feature( $feature ) );

			LLMS_Site::update_feature( $feature, false );
			$this->assertFalse( LLMS_Site::get_feature( $feature ) );

		}

	}

	/**
	 * Test is_clone() function
	 * @return   void
	 * @since    3.7.4
	 * @version  3.7.4
	 */
	public function test_is_clone() {

		$original = get_site_url();

		// not a clone because the url is the lock url
		$this->assertFalse( LLMS_Site::is_clone() );

		// the url has changed
		update_option( 'siteurl', 'http://fakeurl.tld' );
		$this->assertTrue( LLMS_Site::is_clone() );

		// change it back to the original
		update_option( 'siteurl', $original );
		$this->assertFalse( LLMS_Site::is_clone() );

		// change the schema (should not be identified as a clone)
		update_option( 'siteurl', set_url_scheme( $original, 'https' ) );
		$this->assertFalse( LLMS_Site::is_clone() );

	}

	/**
	 * Test is_clone_ignored() function
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
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
