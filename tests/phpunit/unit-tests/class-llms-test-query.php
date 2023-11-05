<?php
/**
 * Tests for LLMS_Query class
 *
 * @package LifterLMS/Tests
 *
 * @group query
 *
 * @since 4.5.0
 */
class LLMS_Test_Query extends LLMS_UnitTestCase {

	/**
	 * Set up test case
	 *
	 * @since 4.5.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Query();

	}

	/**
	 * Assertion helper to ensure that the `$wp_query->query` variables equal an expected array
	 *
	 * @since 5.0.2
	 *
	 * @param array  $expected Expected query array.
	 * @param string $message  Error message.
	 * @return void
	 */
	private function assertQueryVarsEqual( $expected, $message = null ) {

		global $wp_query;
		$this->assertEquals( $expected, $wp_query->query, urldecode( $message ) );

	}

	/**
	 * Tests the add_endpoints() method
	 *
	 * This is a large "integration" test that ensures that student dashboard
	 * endpoints are properly added to the `$wp_rewrite` list.
	 *
	 * It does "real" tests by simulating a visit to the URL and testing that the expected
	 * `$wp_query->query` variables are set.
	 *
	 * It runs tests with the default values of the endpoints, with custom translated values in various
	 * languages (to ensure non-latin characters work in customized slugs) and finally adds a random string
	 * of alphanumeric latin chars for testing.
	 *
	 * It additionally tests pagination for endpoints that utilize pagination work regardless of the customized
	 * slug.
	 *
	 * @since 5.0.2
	 *
	 * @link https://github.com/gocodebox/lifterlms/issues/1639
	 *
	 * @return void
	 */
	public function test_add_endpoints() {

		LLMS_Install::create_pages();

		// Setup.
		$temp = get_option( 'permalink_structure' );
		update_option( 'permalink_structure', '/%postname%/' );

		global $wp_rewrite;
		$wp_rewrite->init();

		$account_url = llms_get_page_url( 'myaccount' );
		$options     = array(
			'view-courses'      => 'lifterlms_myaccount_courses_endpoint',
			'my-grades'         => 'lifterlms_myaccount_grades_endpoint',
			'view-memberships'  => 'lifterlms_myaccount_memberships_endpoint',
			'view-achievements' => 'lifterlms_myaccount_achievements_endpoint',
			'view-certificates' => 'lifterlms_myaccount_certificates_endpoint',
			'view-favorites'    => 'lifterlms_myaccount_favorites_endpoint',
			'notifications'     => 'lifterlms_myaccount_notifications_endpoint',
			'edit-account'      => 'lifterlms_myaccount_edit_account_endpoint',
			'redeem-voucher'    => 'lifterlms_myaccount_redeem_vouchers_endpoint',
			'orders'            => 'lifterlms_myaccount_orders_endpoint',
		);

		$non_latin = array(
			'view-courses'      => 'ビューコース', // Japanese.
			'my-grades'         => 'мои-оценки', // Russian.
			'view-memberships'  => 'ਵੇਖੋ-ਸਦੱਸਤਾ', // Punjabi.
			'view-achievements' => 'nailiyyətlər', // Azerbaijani.
			'view-certificates' => 'ເບິ່ງໃບຢັ້ງຢືນ', // Lao.
			'view-favorites'    => '즐겨찾기', // Korean.
			'notifications'     => '通知', // Chinese (Simplified).
			'edit-account'      => 'חשבון-עריכה', // Hebrew.
			'redeem-voucher'    => 'چھڑانا', // Urdu.
			'orders'            => 'आदेश', // Hindi.
		);

		foreach ( LLMS_Student_Dashboard::get_tabs() as $id => $tab ) {

			if ( empty( $tab['endpoint'] ) ) {
				continue;
			}

			$tests = array(
				$tab['endpoint'],
				wp_generate_password( 6, false, false ),
				$non_latin[ $id ],
			);

			foreach ( $tests as $option ) {

				update_option( $options[ $id ], urlencode( $option ) );
				new LLMS_Student_Dashboard();
				$this->main->add_endpoints();
				flush_rewrite_rules();

				$url = isset( $tab['url'] ) ? $tab['url'] : llms_get_endpoint_url( $id, null, $account_url );

				$this->go_to( $url );

				$expect = array(
					'pagename' => 'dashboard',
				);

				if ( 'dashboard' === $id ) {
					$expect['page'] = '';
				} else {
					$expect[ $id ] = '';
				}

				$this->assertQueryVarsEqual( $expect, $id . ' - ' . $url );

				if ( ! empty( $tab['paginate'] ) ) {
					$url .= 'page/1';
					$this->go_to( $url );
					$expect['paged'] = 1;
					$this->assertQueryVarsEqual( $expect, $url );

				}

			}

		}

		$this->go_to( '' );

		// Teardown.
		update_option( 'permalink_structure', $temp );
		$wp_rewrite->init();

	}

	/**
	 * Test maybe_404_certificate()
	 *
	 * This test runs in a separate process because something before it is making it hard
	 * to mock the `$wp_query` and `$post` globals.
	 *
	 * @since 4.5.0
	 * @since 6.0.0 Ensure a post author exists for tested posts.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_maybe_404_certificate() {

		global $post, $wp_query;
		$temp = $post;

		$admin       = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$post_author = $this->factory->user->create();

		// Not set.
		$post = null;
		$this->main->maybe_404_certificate();
		$this->assertFalse( $wp_query->is_404() );

		$tests = array(
			'llms_my_certificate' => true,
			'post'                => false,
			'page'                => false,
			'course'              => false,
		);

		foreach ( $tests as $post_type => $expect ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type', 'post_author' ) );
			$wp_query->init();

			// Logged out user.
			$this->main->maybe_404_certificate();
			$this->assertEquals( $expect, $wp_query->is_404(), $post_type );

			// Logged in admin can always see.
			$wp_query->init();
			wp_set_current_user( $admin );
			$this->main->maybe_404_certificate();
			$this->assertFalse( $wp_query->is_404(), $post_type );

			wp_set_current_user( null );

		}

		$post = $temp;

	}

	/**
	 * Test maybe_redirect_certificates() when a redirect is not expected.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_maybe_redirect_certificates_caught() {

		global $wp, $wp_query;

		$cert = $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_certificate' ) );

		$wp->request = '/my_certificate/' . $cert->post_name;
		$wp_query->is_404 = true;

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( sprintf( '%1$s [302] YES', get_permalink( $cert->ID ) ) );

		$this->main->maybe_redirect_certificate();

	}

	/**
	 * Test maybe_redirect_certificates() in scenarios where a redirect is not expected.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_maybe_redirect_certificates_not_caught() {

		$this->set_permalink_structure( '/%postname%/' );

		// Assert that the LLMS_Unit_Test_Exception_Redirect is not thrown.
		$this->expectNotToPerformAssertions();

		global $wp, $wp_query;

		// Not a 404.
		$wp->request = '/fake/url';
		$wp_query->is_404 = false;
		$this->main->maybe_redirect_certificate();

		// Is a 404 but url doesn't contain "/my_certificate/".
		$wp_query->is_404 = true;
		$this->main->maybe_redirect_certificate();

		// Does contain "/my_certificate/" but isn't a 404.
		$wp->request = '/my_certificate/slug';
		$wp_query->is_404 = false;
		$this->main->maybe_redirect_certificate();

		// Doesn't redirect because the certificate doesn't exist.
		$wp->request = '/my_certificate/fake-slug-doesnt-exist';
		$wp_query->is_404 = true;
		$this->main->maybe_redirect_certificate();

		// A real post that contains "/my_certificate/" but isn't an `llms_my_certificate` post type.
		// This is something of a dumb test because in this scenario the page would be loaded and not 404 but just in case...
		$parent = $this->factory->post->create( array( 'post_type' => 'page', 'post_name' => 'my_certificate' ) );
		$wp->request = wp_parse_url( get_permalink( $parent ), PHP_URL_PATH );
		$wp_query->is_404 = true;
		$this->main->maybe_redirect_certificate();

		// The child post.
		$child = $this->factory->post->create( array( 'post_type' => 'page', 'post_parent' => $parent ) );
		$wp->request = wp_parse_url( get_permalink( $child ), PHP_URL_PATH );
		$this->main->maybe_redirect_certificate();

		// Create this scenario: https://github.com/gocodebox/lifterlms/pull/1855#pullrequestreview-804521213
		$parent_parent = $this->factory->post->create( array( 'post_type' => 'page' ) );
		wp_update_post( array( 'ID' => $parent, 'post_parent' => $parent_parent ) );

		$child_post  = get_post( $child );
		$parent_post = get_post( $parent_parent );

		$cert = $this->factory->post->create( array( 'post_type' => 'llms_my_certificate', 'post_name' => "{$parent_post->post_name}{$child_post->post_name}" ) );

		$wp->request = wp_parse_url( get_permalink( $child ), PHP_URL_PATH );
		$this->main->maybe_redirect_certificate();

		$this->set_permalink_structure( false );

	}

}
