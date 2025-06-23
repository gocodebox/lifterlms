<?php
/**
 * BuddyPress Integration
 *
 * @package LifterLMS/Integrations/Classes
 *
 * @since 1.0.0
 * @version 6.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Integration.
 *
 * @since 1.0.0
 * @since 3.37.17 Fixed `courses` pagination.
 */
class LLMS_Integration_Buddypress extends LLMS_Abstract_Integration {

	public $id = 'buddypress';

	/**
	 * Display order on Integrations tab.
	 *
	 * @var integer
	 */
	protected $priority = 5;

	/**
	 * Current endpoint's key being processed.
	 *
	 * @var string
	 */
	private $current_endpoint_key;

	/**
	 * Profile endpoints.
	 *
	 * @var array[]
	 */
	private $endpoints;

	/**
	 * Options data abstract version.
	 *
	 * This is used to determine the behavior of the `get_option()` method.
	 *
	 * Concrete classes should use version 2 in order to use the new (future default)
	 * behavior of the method.
	 *
	 * @var int
	 */
	protected $version = 2;

	/**
	 * Configure the integration.
	 *
	 * Do things like configure ID and title here.
	 *
	 * @since 3.12.0
	 *
	 * @return void
	 */
	protected function configure() {

		add_action( 'init', array( $this, 'set_title_and_description' ) );

		if ( $this->is_available() ) {

			add_action( 'bp_setup_nav', array( $this, 'add_profile_nav_items' ) );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks' ), 40, 1 );
			add_filter( 'lifterlms_update_account_redirect', array( $this, 'maybe_alter_update_account_redirect' ) );

			// Groups Add-on integration.
			add_filter( 'llms_groups_enqueue_dashboard_style', array( $this, 'return_true_on_bp_my_profile' ) );
			add_filter( 'llms_groups_maybe_hide_dashboard_tab', array( $this, 'return_true_on_bp_my_profile' ) );

		}
	}

	public function set_title_and_description() {
		$this->title       = __( 'BuddyPress', 'lifterlms' );
		$this->description = sprintf( __( 'Add LifterLMS information to user profiles and enable membership restrictions for activity, group, and member directories. %1$sLearn More%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-buddypress/" target="_blank">', '</a>' );
	}

	/**
	 * Retrieve integration settings.
	 *
	 * @since 6.3.0
	 *
	 * @return array
	 */
	public function get_integration_settings() {

		$settings = array();

		if ( $this->is_available() ) {

			$display_eps = $this->get_profile_endpoints_options();

			$settings[] = array(
				'class'   => 'llms-select2',
				'desc'    => '<br>' . __( 'The following LifterLMS Student Dashboard areas will be added to the BuddyPress user profiles', 'lifterlms' ),
				'default' => array_keys( $display_eps ),
				'id'      => $this->get_option_name( 'profile_endpoints' ),
				'options' => $display_eps,
				'type'    => 'multiselect',
				'title'   => __( 'User Profile Endpoints', 'lifterlms' ),
			);

		}

		return $settings;
	}

	/**
	 * Add LLMS navigation items to the BuddyPress User Profile.
	 *
	 * @since 1.0.0
	 * @since 6.3.0 Display all registered dashboard tabs (enabled in the settings) automatically.
	 *              Use `bp_loggedin_user_domain()` to determine the current user domain
	 *              to be used in the profile nav item's links, in favor of relying on the global `$bp`.
	 * @since 6.8.0 Revert adding nav items only on bp my profile. @link https://github.com/gocodebox/lifterlms/issues/2142.
	 *
	 * @return void
	 */
	public function add_profile_nav_items() {

		$profile_endpoints = $this->get_profile_endpoints();

		if ( empty( $profile_endpoints ) ) {
			return;
		}

		$bp_is_my_profile = bp_is_my_profile();
		$user_domain      = bp_loggedin_user_domain();
		$first_endpoint   = reset( $profile_endpoints );
		/**
		 * Filters the LifterLMS main nav item slug in the BuddyPress  profile menu.
		 *
		 * @since 6.3.0
		 *
		 * @param string $slug The LifterLMS main nav item slug in the BuddyPress profile menu.
		 */
		$main_nav_slug = apply_filters( 'llms_buddypress_main_nav_item_slug', _x( 'courses', 'BuddyPress profile main nav item slug', 'lifterlms' ) );
		$parent_url    = $user_domain . $main_nav_slug . '/';

		// Add the main nav menu.
		bp_core_new_nav_item(
			array(
				/**
				 * Filters the LifterLMS main nav item label in the BuddyPress profile menu.
				 *
				 * @since 6.3.0
				 *
				 * @param string $label The LifterLMS main nav item label in the BuddyPress profile menu.
				 */
				'name'                    => apply_filters( 'llms_buddypress_main_nav_item_label', _x( 'Courses', 'BuddyPress profile main nav item label', 'lifterlms' ) ),
				'slug'                    => $main_nav_slug,
				/**
				 * Filters the LifterLMS main nav item position in the BuddyPress profile menu.
				 *
				 * @since 6.3.0
				 *
				 * @param string $position The LifterLMS main nav item position in the BuddyPress profile menu.
				 */
				'position'                => apply_filters( 'llms_buddypress_main_nav_item_position', 20 ),
				'default_subnav_slug'     => $first_endpoint['endpoint'],
				'show_for_displayed_user' => false,
			)
		);

		foreach ( $profile_endpoints as $ep_key => $profile_endpoint ) {
			// Add sub nav item.
			bp_core_new_subnav_item(
				array(
					'name'            => $profile_endpoint['title'],
					'slug'            => $profile_endpoint['endpoint'],
					'parent_slug'     => $main_nav_slug,
					'parent_url'      => $parent_url,
					'screen_function' => function () use ( $ep_key, $profile_endpoint ) {
						$this->endpoint_content( $ep_key, $profile_endpoint['content'] );
					},
					'user_has_access' => $bp_is_my_profile,
				)
			);
		}
	}

	/**
	 * Redirect on the same bb profile page when successfully update the account.
	 *
	 * @since 6.3.0
	 *
	 * @param string $account_update_redirect_url Account update redirect url.
	 * @return string
	 */
	public function maybe_alter_update_account_redirect( $account_update_redirect_url ) {
		return bp_is_my_profile() ? bp_get_requested_url() : $account_update_redirect_url;
	}

	/**
	 * Checks if the BuddyPress plugin is installed & activated
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_installed() {
		return ( class_exists( 'BuddyPress' ) );
	}

	/**
	 * Callback for "Achievements" profile screen.
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function achievements_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::achievements_screen()', '6.3.0' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_achievements' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Certificates" profile screen.
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function certificates_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::certificates_screen()', '6.3.0' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_certificates' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Courses" profile screen.
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @since 3.37.17 Added action and filters to fix handling pagination links mofication.
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function courses_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::courses_screen()', '6.3.0' );

		// Prevent paginate links alteration performed in includes/functions/llms.functions.templates.dashboard.php.
		add_filter( 'llms_modify_dashboard_pagination_links_disable', '__return_true', 999 );

		// Add specific paginate links filter.
		add_filter( 'paginate_links', array( $this, 'modify_courses_paginate_links' ) );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_courses' );

		// Remove specific paginate links filter after the template has been rendered.
		add_action( 'bp_template_content', array( $this, 'remove_courses_paginate_links_filter' ), 15 );

		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for endpoint profile content.
	 *
	 * @since 6.3.0
	 *
	 * @param string   $ep_key         The endpoint's key being processed.
	 * @param Callable $ep_template_cb The endpoint's template callback.
	 * @return void
	 */
	public function endpoint_content( $ep_key, $ep_template_cb ) {

		// Store what endpoint key we're processing.
		$this->current_endpoint_key = $ep_key;

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );

		// Prevent paginate links alteration performed in includes/functions/llms.functions.templates.dashboard.php.
		add_filter( 'llms_modify_dashboard_pagination_links_disable', '__return_true', 999 );

		// Add specific paginate links filter.
		add_filter( 'paginate_links', array( $this, 'modify_paginate_links' ) );

		add_action( 'bp_template_content', $ep_template_cb );

		// Remove specific paginate links filter after the template has been rendered.
		add_action( 'bp_template_content', array( $this, 'remove_paginate_links_filter' ), 15 );

		// This triggers 'bp_template_content' action hook.
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Enqueue assets specific for the profile endpoints.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		if ( empty( $this->current_endpoint_key ) ) {
			return;
		}

		if ( 'view-achievements' === $this->current_endpoint_key ) {
			// The iziModal is needed by the achievements endpoint.
			llms()->assets->enqueue_style( 'llms-iziModal' );
			llms()->assets->enqueue_script( 'llms-iziModal' );
		}

		if ( 'edit-account' === $this->current_endpoint_key ) {
			// Needed in the account edit endpoint.
			llms()->assets->enqueue_style( 'llms-select2-styles' );
			llms()->assets->enqueue_script( 'llms-select2' );
			wp_add_inline_script(
				'llms',
				"window.llms.address_info = '" . wp_json_encode( llms_get_countries_address_info() ) . "';"
			);
		}
	}

	/**
	 * Remove specific paginate links filter after the template has been rendered.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function remove_paginate_links_filter() {
		remove_filter( 'paginate_links', array( $this, 'modify_paginate_links' ) );
	}

	/**
	 * Remove specific paginate links filter after the template has been rendered.
	 *
	 * @since 3.37.17
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::remove_paginate_links_filter()}.
	 *
	 * @return void
	 */
	public function remove_courses_paginate_links_filter() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::remove_courses_paginate_links_filter()', '6.3.0' );

		remove_filter( 'paginate_links', array( $this, 'modify_courses_paginate_links' ) );
	}

	/**
	 * Modify the pagination links displayed on the courses endpoint in the bp member profile.
	 *
	 * @since 3.37.17
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::modify_paginate_links()}.
	 *
	 * @param string $link Default link.
	 * @return string
	 */
	public function modify_courses_paginate_links( $link ) {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::modify_courses_paginate_links()', '6.3.0' );

		$this->current_endpoint_key;
		return $this->modify_paginate_links( $link );
	}

	/**
	 * Modify the pagination links for the endpoints in the bp member profile.
	 *
	 * This fixes the pagination not correctly working on the fist subnav.
	 *
	 * @since 6.3.0
	 *
	 * @param string $link Default link.
	 * @return string
	 */
	public function modify_paginate_links( $link ) {

		global $wp_rewrite;

		// With ugly permalinks actually the whole BuddyPress member profile page doesn't work.
		if ( ! get_option( 'permalink_structure' ) ) {
			return $link;
		}

		// Remove query vars if any, we'll add them back later.
		$query = wp_parse_url( $link, PHP_URL_QUERY );
		if ( $query ) {
			$link = str_replace( '?' . $query, '', $link );
		}

		// Retrieve link's page number.
		$parts = explode( '/', untrailingslashit( $link ) );
		$page  = end( $parts );

		// For links to page 1 let's remove it to avoid ugly URLs.
		if ( 1 === (int) $page ) {
			$link = str_replace(
				user_trailingslashit( $wp_rewrite->pagination_base . '/' . $page ),
				'',
				$link
			);
		}

		$endpoints = $this->get_profile_endpoints();

		// If we're not the first subnav, our job is done, add back the query var and return.
		if ( key( $endpoints ) !== $this->current_endpoint_key ) {
			return $query ? $link . '?' . $query : $link;
		}

		// Retrieve our first subnav menu item.
		$first_subnav_item = buddypress()->members->nav->get_secondary(
			array(
				/** This filter is documented above */
				'parent_slug' => apply_filters( 'llms_buddypress_main_nav_item_slug', _x( 'courses', 'BuddyPress profile main nav item slug', 'lifterlms' ) ),
				'slug'        => $endpoints[ $this->current_endpoint_key ]['endpoint'],
			)
		);

		if ( is_array( $first_subnav_item ) ) {
			$first_subnav_item = reset( $first_subnav_item );
		} else { // Bail.
			return $query ? $link . '?' . $query : $link;
		}

		$current_page = llms_get_paged_query_var();

		/**
		 * Here's the core of this filter.
		 *
		 * What happens is that the pagination links on the first page of the fist subnav,
		 * e.g. 'my-courses' endpoint (as example for the fist subnav) are of this type:
		 * `example.local/members/admin/courses/page/N`,
		 * where 'courses' is the slug of the main nav item.
		 * While the "working" paginate links must be of the type:
		 * `example.local/members/admin/courses/my-courses/page/N`
		 * where 'courses' is the slug of the main nav item, and 'my-courses' is the slug of
		 * the subnav item which is the default subnav for the main nav item.
		 *
		 * So what we do here is replacing the link that looks like:
		 * `example.local/members/admin/courses/page/N` to something like:
		 * `example.local/members/admin/courses/my-courses/page/N`
		 *
		 * Despite one might expect, `$first_subnav_item->link` doesn't point to `example.local/members/admin/courses/my-courses/`
		 * but to `example.local/members/admin/courses/`, which is the link of the parent nav, the main nav item,
		 * this because the 'my-courses' subnav item is the default of the 'courses' nav item (default_subnav_slug).
		 */
		if ( 1 === $current_page ) {
			$link = user_trailingslashit( $first_subnav_item->link . $first_subnav_item->slug . '/' . $wp_rewrite->pagination_base . '/' . $page );
		} elseif ( 1 === (int) $page ) {
			/**
			 * For links to page 1, when not on page 1, let's back on the main nav item URL, so we replace something like
			 * `example.local/members/admin/courses/my-courses/`
			 * to something like
			 * `example.local/members/admin/courses/`
			 */
			$link = $first_subnav_item->link;
		}

		return $query ? $link . '?' . $query : $link;
	}

	/**
	 * Helper that returns true when on BuddyPress My Profile.
	 *
	 * @since 6.3.0
	 *
	 * @param mixed $arg Argument to return when not on BuddyPress My Profile.
	 * @return mixed
	 */
	public function return_true_on_bp_my_profile( $arg = null ) {
		return bp_is_my_profile() ? true : $arg;
	}

	/**
	 * Callback for "memberships" profile screen.
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated 6.3.0 Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function memberships_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::memberships_screen()', '6.3.0' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_memberships' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Allows restricting of BP Directory Pages for Activity and Members via LifterLMS membership restrictions.
	 *
	 * @since 3.12.0
	 *
	 * @param array $results Array of restriction results.
	 * @return array
	 */
	public function restriction_checks( $results ) {

		// Only check directories.
		if ( ! bp_is_directory() ) {
			return $results;
		}

		$post_id = null;

		// Activity.
		if ( bp_is_activity_component() ) {

			$post_id = bp_core_get_directory_page_id( 'activity' );

		} elseif ( bp_is_members_component() ) {

			$post_id = bp_core_get_directory_page_id( 'members' );

		} elseif ( bp_is_groups_component() ) {

			$post_id = bp_core_get_directory_page_id( 'groups' );

		}

		if ( $post_id ) {

			$restriction_id = llms_is_post_restricted_by_membership( $post_id, get_current_user_id() );

			if ( $restriction_id ) {

				$results['content_id']     = $post_id;
				$results['restriction_id'] = $restriction_id;
				$results['reason']         = 'membership';

			}
		}

		return $results;
	}

	/**
	 * Get profile endpoints options.
	 *
	 * Used to populate the settings' select.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function get_profile_endpoints_options() {

		$endpoints = $this->get_profile_endpoints( false );

		return array_combine(
			array_keys( $endpoints ),
			array_column( $endpoints, 'title' )
		);
	}

	/**
	 * Populate list of endpoints from LifterLMS Dashboard Settings.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function populate_profile_endpoints() {

		$exclude_llms_eps = array( 'dashboard', 'signout' );
		$exclude_fields   = array( 'nav_item', 'url', 'paginate' );
		$endpoints        = array();

		foreach ( LLMS_Student_Dashboard::get_tabs() as $ep_key => $endpoint ) {
			if ( ! in_array( $ep_key, $exclude_llms_eps, true ) ) {
				$endpoints[ $ep_key ] = array_diff_key( $endpoint, array_flip( $exclude_fields ) );
			}
		}

		/**
		 * Filter profile endpoints.
		 *
		 * Modify the LifterLMS dashboard endpoints which can be added to the BuddyPress profile page as custom tabs.
		 *
		 * @since 6.3.0
		 *
		 * @param array $endpoints Array of endpoint data.
		 */
		$this->endpoints = apply_filters( 'llms_buddypress_profile_endpoints', $endpoints );
	}

	/**
	 * Get a list of custom endpoints to add to BuddyPress profile page.
	 *
	 * @since 6.3.0
	 * @since 6.8.0 Remove redundant check on `is_null()`: `isset()` already implies it.
	 *
	 * @param bool $active_only If true, returns only active endpoints.
	 * @return array
	 */
	public function get_profile_endpoints( $active_only = true ) {

		if ( ! isset( $this->endpoints ) ) {
			$this->populate_profile_endpoints();
		}

		// Remove endpoints that don't have an 'endpoint' value.
		$endpoints = array_filter(
			$this->endpoints,
			function ( $endpoint ) {
				return ! empty( $endpoint['endpoint'] );
			}
		);

		if ( $active_only ) {

			// If no endpoints are saved an empty string is returned.
			$active = $this->get_option( 'profile_endpoints', array_keys( $endpoints ) );

			// Filter active endpoints only.
			$endpoints = '' === $active
				?
				array()
				:
				array_intersect_key(
					$endpoints,
					array_flip( $active )
				);

		}

		return $endpoints;
	}
}
