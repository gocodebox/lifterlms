<?php
/**
 * Test page functions.
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_page
 *
 * @since 3.38.0
 * @since [version] Added tests for llms_get_paged_query_var(), llms_get_endpoint_url(), _llms_normalize_endpoint_base_url().
 */
class LLMS_Test_Functions_Fage extends LLMS_UnitTestCase {

	/**
	 * Test the llms_confirm_payment_url() function.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_llms_confirm_payment_url() {

		LLMS_Install::create_pages();

		$base = get_permalink( llms_get_page_id( 'checkout' ) ) . '&confirm-payment';

		// No additional args provided.
		$this->assertEquals( $base, llms_confirm_payment_url() );

		// Has order key.
		$this->assertEquals( $base . '&order=fake', llms_confirm_payment_url( 'fake' ) );

		// Has redirect.
		$this->mockGetRequest( array(
			'redirect' => get_site_url(),
		) );
		$this->assertEquals( $base . '&redirect=' . urlencode( get_site_url() ), llms_confirm_payment_url() );

		// Has both.
		$this->assertEquals( $base . '&order=fake&redirect=' . urlencode( get_site_url() ), llms_confirm_payment_url( 'fake' ) );

	}

	/**
	 * Test llms_get_endpoint_url() when pretty permalinks are disabled.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_llms_get_endpoint_url_no_pretty_permalinks() {

		LLMS_Install::create_pages();

		$permalink = get_permalink( llms_get_page_id( 'myaccount' ) );
		$this->go_to( $permalink );

		foreach ( llms()->query->get_query_vars() as $var => $slug ) {

			$this->assertEquals( "{$permalink}&{$slug}", llms_get_endpoint_url( $var ) );
			$this->assertEquals( "{$permalink}&{$slug}=test", llms_get_endpoint_url( $var, 'test' ) );
			$this->assertEquals( "https://fake.tld/?{$slug}=1", llms_get_endpoint_url( $var, 1, 'https://fake.tld/' ) );

		}

	}

	/**
	 * Test llms_get_endpoint_url() when pretty permalinks are enabled with a trailing slash.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_llms_get_endpoint_url_with_trailing_slash_pretty_permalink() {

		global $wp_rewrite;

		$orig_permastruct = get_option( 'permalink_structure' );

		LLMS_Install::create_pages();

		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->init();


		$permalink = get_permalink( llms_get_page_id( 'myaccount' ) );
		$this->go_to( $permalink );

		foreach ( llms()->query->get_query_vars() as $var => $slug ) {

			$this->assertEquals( "{$permalink}{$slug}/", llms_get_endpoint_url( $var ) );
			$this->assertEquals( "{$permalink}{$slug}/test/", llms_get_endpoint_url( $var, 'test' ) );
			$this->assertEquals( "https://fake.tld/{$slug}/1/", llms_get_endpoint_url( $var, 1, 'https://fake.tld/' ) );
			$this->assertEquals( "https://fake.tld/{$slug}/1/?whatever=yes", llms_get_endpoint_url( $var, 1, 'https://fake.tld/?whatever=yes' ) );

		}

		$this->go_to( '' );

		update_option( 'permalink_structure', $orig_permastruct );
		$wp_rewrite->init();

	}

	/**
	 * Test llms_get_endpoint_url() when pretty permalinks are enabled with a trailing slash.
	 *
	 * @since 5.9.0
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1983
	 *
	 * @return void
	 */
	public function test_llms_get_endpoint_url_without_trailing_slash_pretty_permalink() {

		global $wp_rewrite;

		$orig_permastruct = get_option( 'permalink_structure' );

		LLMS_Install::create_pages();

		update_option( 'permalink_structure', '/%postname%' );
		$wp_rewrite->init();


		$permalink = get_permalink( llms_get_page_id( 'myaccount' ) );
		$this->go_to( $permalink );

		foreach ( llms()->query->get_query_vars() as $var => $slug ) {

			$this->assertEquals( "{$permalink}/{$slug}", llms_get_endpoint_url( $var ) );
			$this->assertEquals( "{$permalink}/{$slug}/test", llms_get_endpoint_url( $var, 'test' ) );
			$this->assertEquals( "https://fake.tld/{$slug}/1", llms_get_endpoint_url( $var, 1, 'https://fake.tld/' ) );
			$this->assertEquals( "https://fake.tld/{$slug}/1?whatever=yes", llms_get_endpoint_url( $var, 1, 'https://fake.tld/?whatever=yes' ) );

		}

		$this->go_to( '' );

		update_option( 'permalink_structure', $orig_permastruct );
		$wp_rewrite->init();

	}

	/**
	 * Test the llms_get_page_id() function.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_llms_get_page_id() {

		$pages = array(
			'checkout' => 'checkout',
			'courses' => 'shop',
			'myaccount' => 'myaccount',
			'memberships' => 'memberships',
		);

		// Clear options maybe installed by other tests.
		foreach ( array_values( $pages ) as $option ) {
			delete_option( 'lifterlms_' . $option . '_page_id' );
		}

		// Options don't exist.

		// Backwards compat.
		$this->assertEquals( -1, llms_get_page_id( 'shop' ) );

		foreach ( array_keys( $pages ) as $slug ) {
			$this->assertEquals( -1, llms_get_page_id( $slug ) );
		}

		// Options do exist.
		LLMS_Install::create_pages();

		// Backwards compat.
		$this->assertEquals( get_option( 'lifterlms_shop_page_id' ), llms_get_page_id( 'shop' ) );

		foreach ( $pages as $slug => $option ) {

			$id = llms_get_page_id( $slug );

			// Number.
			$this->assertTrue( is_int( $id ) );

			// Equals expected option value.
			$this->assertEquals( get_option( 'lifterlms_' . $option . '_page_id' ), $id );

		}

	}

	/**
	 * Test the llms_get_paged_query_var() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_paged_query_var() {

		// No `paged` or `page` query var set.
		$this->assertEquals(
			1,
			llms_get_paged_query_var()
		);

		// `page` query var set.
		set_query_var( 'page', 2 );
		$this->assertEquals(
			2,
			llms_get_paged_query_var()
		);

		// `paged` query var set - it'll win over `page`.
		set_query_var( 'paged', 4 );
		$this->assertEquals(
			4,
			llms_get_paged_query_var()
		);

		// `paged` query var set to falsy - `page` query var value will be returned.
		set_query_var( 'paged', 0 );
		$this->assertEquals(
			2,
			llms_get_paged_query_var()
		);

	}

	/**
	 * Test the _llms_normalize_endpoint_base_url() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function test__llms_normalize_endpoint_base_url() {

		$temp = get_option( 'permalink_structure' );
		update_option( 'permalink_structure', '/%postname%/' );

		global $wp_rewrite;
		$wp_rewrite->init();

		$url      = 'https://example.com/members/admin/courses/my-courses/page/2/';
		$endpoint = 'something';
		set_query_var( 'page', 2 );

		// Provided endpoint not found in the url: nothing to do.
		$this->assertEquals(
			$url,
			_llms_normalize_endpoint_base_url( $url, $endpoint )
		);

		$endpoint = 'members';
		// Provided endpoint found in the url, but not as last part (except for the paging info): nothing to do.
		$this->assertEquals(
			$url,
			_llms_normalize_endpoint_base_url( $url, $endpoint )
		);

		$endpoint = 'my-courses';

		// Provided endpoint found in the url, as last part (except for the paging info).
		$this->assertEquals(
			'https://example.com/members/admin/courses/',
			_llms_normalize_endpoint_base_url( $url, $endpoint )
		);

		// Teardown.
		update_option( 'permalink_structure', $temp );
		$wp_rewrite->init();

	}

	/**
	 * Test the llms_get_endpoint_url() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function test_llms_get_endpoint_url() {

		LLMS_Install::create_pages();

		// Get the courses endpoint url when on the dashboard's my grades tab.
		$account_url = llms_get_page_url( 'myaccount' );
		$this->go_to( $account_url . '&my-grades' );

		// Ugly permalinks.
		$endpoint = 'view-courses';
		$this->assertEquals(
			$account_url . '&my-courses',
			llms_get_endpoint_url( $endpoint )
		);

		// Pretty permalinks.
		$temp = get_option( 'permalink_structure' );
		update_option( 'permalink_structure', '/%postname%/' );

		global $wp_rewrite;
		$wp_rewrite->init();

		$account_url = llms_get_page_url( 'myaccount' );
		$this->go_to( $account_url );

		$endpoint = 'view-courses';
		$this->assertEquals(
			$account_url . 'my-courses/',
			llms_get_endpoint_url( $endpoint ),
		);

		/**
		 * Simulate we're on a location for which there is no permalink.
		 * Since llms_get_endpoint_url's third parameter `$permalink` is not passed,
		 * the endpoint url is based off the current URL.
		 */
		$buddypress_lifterlms_base_url = home_url( '/members/admin/courses/' );
		// e.g. on a BuddyPress courses profile base url.
		$this->go_to( $buddypress_lifterlms_base_url );
		$this->assertEquals(
			$buddypress_lifterlms_base_url . 'my-courses/',
			llms_get_endpoint_url( $endpoint ),
		);

		// Now on a BuddyPress courses profile page (page 2).
		$this->go_to( $buddypress_lifterlms_base_url . 'my-courses/page/2/' );
		set_query_var( 'page', 2 );
		$this->assertEquals(
			$buddypress_lifterlms_base_url . 'my-courses/',
			llms_get_endpoint_url( $endpoint ),
		);

		// Now on a BuddyPress my-courses profile page (page 1).
		$this->go_to( $buddypress_lifterlms_base_url . 'my-courses/' );
		set_query_var( 'page', 1 );
		$this->assertEquals(
			$buddypress_lifterlms_base_url . 'my-courses/',
			llms_get_endpoint_url( $endpoint ),
		);

		// Teardown.
		update_option( 'permalink_structure', $temp );
		$wp_rewrite->init();

	}

}
