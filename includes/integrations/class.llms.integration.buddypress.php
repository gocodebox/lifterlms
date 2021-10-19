<?php
/**
 * BuddyPress Integration
 *
 * @package LifterLMS/Integrations/Classes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Integration
 *
 * @since 1.0.0
 * @since 3.37.17 Fixed `courses` pagination.
 */
class LLMS_Integration_Buddypress extends LLMS_Abstract_Integration {

	public $id = 'buddypress';

	/**
	 * Display order on Integrations tab
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

		$this->title       = __( 'BuddyPress', 'lifterlms' );
		$this->description = sprintf( __( 'Add LifterLMS information to user profiles and enable membership restrictions for activity, group, and member directories. %1$sLearn More%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-buddypress/" target="_blank">', '</a>' );

		if ( $this->is_available() ) {

			add_action( 'bp_setup_nav', array( $this, 'add_profile_nav_items' ) );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks' ), 40, 1 );

		}

	}

	/**
	 * Retrieve integration settings.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_integration_settings() {

		$settings = array();

		if ( $this->is_available() ) {

			$endpoints = $this->get_profile_endpoints( false );

			$display_eps = array();

			foreach ( $endpoints as $ep_key => $endpoint ) {
				$display_eps[ $ep_key ] = $endpoint['title'];
			}

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
	 * Populate list of endpoints from LifterLMS Dashboard Settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function populate_profile_endpoints() {

		$exclude_llms_eps = array( 'dashboard', 'edit-account', 'signout' );
		$endpoints        = array_diff_key( LLMS_Student_Dashboard::get_tabs(), array_flip( $exclude_llms_eps ) );

		foreach ( $endpoints as $ep_key => &$endpoint ) {
			unset( $endpoint['nav_item'] );
			unset( $endpoint['url'] );
		}

		/**
		 * Filter profile endpoints.
		 *
		 * Modify the LifterLMS dashboard endpoints which can be added to the BuddyPress profile page as custom tabs.
		 *
		 * @since [version]
		 *
		 * @param array $endpoints Array of endpoint data.
		 */
		$this->endpoints = apply_filters( 'llms_buddypress_profile_endpoints', $endpoints );

	}

	/**
	 * Get a list of custom endpoints to add to BuddyPress profile page.
	 *
	 * @since [version]
	 *
	 * @param bool $active_only If true, returns only active endpoints.
	 * @return array
	 */
	public function get_profile_endpoints( $active_only = true ) {

		if ( ! isset( $this->endpoints ) ) {
			$this->populate_profile_endpoints();
		}

		$endpoints = $this->endpoints;

		if ( $active_only ) {

			$active = $this->get_option( 'profile_endpoints', array_keys( $endpoints ) );

			// If no endpoints are saved an empty string is returned and we need an array for the comparison below.
			if ( '' === $active ) {
				return array();
			}

			foreach ( array_keys( $endpoints ) as $endpoint ) {

				// Remove endpoints that aren't stored in the settings.
				if ( ! in_array( $endpoint, $active, true ) ) {
					unset( $endpoints[ $endpoint ] );
				}
			}
		}

		// Remove endpoints that don't have an endpoint.
		foreach ( $endpoints as $ep_key => $endpoint ) {

			if ( empty( $endpoint['endpoint'] ) ) {
				unset( $endpoints[ $ep_key ] );
			}
		}

		return $endpoints;

	}

	/**
	 * Add LLMS navigation items to the BuddyPress User Profile
	 *
	 * @since 1.0.0
	 * @since [version] Display all registered dashboard tabs (enabled in the settings) automatically.
	 *
	 * @return void
	 */
	public function add_profile_nav_items() {

		global $bp;

		$profile_endpoints = $this->get_profile_endpoints();
		if ( empty( $profile_endpoints ) ) {
			return;
		}

		$first_endpoint = reset( $profile_endpoints );
		$main_nav_slug  = apply_filters( 'llms_bp_main_nav_item_slug', _x( 'courses', 'BuddyPress profile main nav item slug', 'lifterlms' ) );
		$parent_url     = $bp->loggedin_user->domain . $main_nav_slug . '/';

		// Add the main nav menu.
		bp_core_new_nav_item(
			array(
				'name'                    => apply_filters( 'llms_bp_main_nav_item_label', _x( 'Courses', 'BuddyPress profile main nav item label', 'lifterlms' ) ),
				'slug'                    => $main_nav_slug,
				'position'                => apply_filters( 'llms_bp_main_nav_item_position', 20 ),
				'show_for_displayed_user' => false,
				'default_subnav_slug'     => $first_endpoint['endpoint'],
			)
		);

		$is_my_profile = bp_is_my_profile(); // Only let the logged in user access subnav screens.

		foreach ( $profile_endpoints as $ep_key => $profile_endpoint ) {
			// Add sub nav item.
			bp_core_new_subnav_item(
				array(
					'name'            => $profile_endpoint['title'],
					'slug'            => $profile_endpoint['endpoint'],
					'parent_slug'     => $main_nav_slug,
					'parent_url'      => $parent_url,
					'screen_function' => function() use ( $ep_key, $profile_endpoint ) {
						$this->endpoint_content( $ep_key, $profile_endpoint['content'] );
					},
					'user_has_access' => $is_my_profile,
				)
			);
		}

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
	 * Callback for "Achievements" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function achievements_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::achievements_screen()', '[version]' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_achievements' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Certificates" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function certificates_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::certificates_screen()', '[version]' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_certificates' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Courses" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @since 3.37.17 Added action and filters to fix handling pagination links mofication.
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function courses_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::courses_screen()', '[version]' );

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
	 * @since [version]
	 *
	 * @param string   $ep_key         The endpoint's key being processed.
	 * @param Callable $ep_template_cb The endpoint's template callback.
	 * @return void
	 */
	public function endpoint_content( $ep_key, $ep_template_cb ) {

		// Register scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );

		// Prevent paginate links alteration performed in includes/functions/llms.functions.templates.dashboard.php.
		add_filter( 'llms_modify_dashboard_pagination_links_disable', '__return_true', 999 );

		// Add specific paginate links filter.
		add_filter( 'paginate_links', array( $this, 'modify_paginate_links' ) );

		// Store what endpoint key we're processing.
		$this->current_endpoint_key = $ep_key;

		add_action( 'bp_template_content', $ep_template_cb );

		// Remove specific paginate links filter after the template has been rendered.
		add_action( 'bp_template_content', array( $this, 'remove_paginate_links_filter' ), 15 );

		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );

	}

	/**
	 * Enqueue assets specific for the profile endpoints.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		// The iziModal is needed by my_achievements.
		llms()->assets->enqueue_style( 'llms-iziModal' );
		llms()->assets->enqueue_script( 'llms-iziModal' );
	}

	/**
	 * Remove specific paginate links filter after the template has been rendered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function remove_paginate_links_filter() {
		remove_filter( 'paginate_links', array( $this, 'modify_paginate_links' ) );
	}

	/**
	 * Remove specific paginate links filter after the template has been rendered
	 *
	 * @since 3.37.17
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::remove_paginate_links_filter()}.
	 *
	 * @return void
	 */
	public function remove_courses_paginate_links_filter() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::remove_courses_paginate_links_filter()', '[version]' );

		remove_filter( 'paginate_links', array( $this, 'modify_courses_paginate_links' ) );
	}

	/**
	 * Modify the pagination links displayed on the courses endpoint in the bp member profile
	 *
	 * @since 3.37.17
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::modify_paginate_links()}.
	 *
	 * @param string $link Default link.
	 * @return string
	 */
	public function modify_courses_paginate_links( $link ) {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::modify_courses_paginate_links()', '[version]' );

		$this->current_endpoint_key;
		return $this->modify_paginate_links( $link );

	}

	/**
	 * Modify the pagination links displayed on the first endpoint in the bp member profile.
	 *
	 * @since [version]
	 *
	 * @param string $link Default link.
	 * @return string
	 */
	public function modify_paginate_links( $link ) {

		global $wp_rewrite;

		$endpoints = $this->get_profile_endpoints();

		if ( key( $endpoints ) !== $this->current_endpoint_key ) {
			return $link;
		}

		// Retrieve the current subnav item.
		$first_subnav_item = buddypress()->members->nav->get_secondary(
			array(
				'parent_slug' => apply_filters( 'llms_bp_main_nav_item_slug', _x( 'courses', 'BuddyPress profile main nav item slug', 'lifterlms' ) ),
				'slug'        => $endpoints[ $this->current_endpoint_key ]['endpoint'],
			)
		);

		if ( is_array( $first_subnav_item ) ) {
			$first_subnav_item = reset( $first_subnav_item );
		} else {
			return $link;
		}

		$query = wp_parse_url( $link, PHP_URL_QUERY );

		if ( $query ) {
			$link = str_replace( '?' . $query, '', $link );
		}

		$parts = explode( '/', untrailingslashit( $link ) );
		$page  = end( $parts );

		/**
		 * Here's the core of this filter.
		 *
		 * What happens is that the paginate links on the 'courses' tab (as example for the fist tab) are of this type:
		 * `example.local/members/admin/courses/page/N`
		 * where 'courses' is the slug of the main nav item.
		 * While the "working" paginate links must be of the type:
		 * `example.local/members/admin/courses/courses/page/N`
		 * where the first 'courses' is the slug of the main nav item, and the second is the slug of
		 * the subnav item, which is also the default "endpoint" for the main nav item.
		 *
		 * So what we do here is to replace all the occurrences of something like
		 * `example.local/members/admin/courses/page/N` to something like
		 * `example.local/members/admin/courses/courses/page/N`
		 *
		 * Despite one might expect `$first_subnav_item->link` doesn't point to `example.local/members/admin/courses/courses/`
		 * but to `example.local/members/admin/courses/`, which is the link of the parent nav, the main nav item,
		 * this because the 'courses' subnav item is the default of the 'courses' nav item.
		 *
		 * (the fact that both the slugs are "courses" doesn't matter here, it doesn't determine any conflict).
		 */
		$search  = $first_subnav_item->link . $wp_rewrite->pagination_base . '/' . $page . '/';
		$replace = $first_subnav_item->link . $first_subnav_item->slug . '/' . $wp_rewrite->pagination_base . '/' . $page . '/';

		/**
		 * For links to page/1 let's back on the main nav item link to avoid ugly URLs, so we replace something like
		 * `example.local/members/admin/courses/courses/page/1`
		 * to something like
		 * `example.local/members/admin/courses/`
		 */
		if ( 1 === absint( $page ) ) {
			$search  = $replace;
			$replace = $first_subnav_item->link;
		}

		$link = str_replace(
			$search,
			$replace,
			$link
		);

		if ( $query ) {
			$link .= '?' . $query;
		}

		return $link;

	}


	/**
	 * Callback for "memberships" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @deprecated [version] Deprecated with no replacement. {@see LLMS_Integration_Buddypress::endpoint_content()}.
	 *
	 * @return void
	 */
	public function memberships_screen() {

		llms_deprecated_function( 'LLMS_Integration_Buddypress::memberships_screen()', '[version]' );

		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_memberships' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Allows restricting of BP Directory Pages for Activity and Members via LifterLMS membership restrictions
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

}
