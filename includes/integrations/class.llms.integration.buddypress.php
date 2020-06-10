<?php
/**
 * BuddyPress Integration
 *
 * @package LifterLMS/Integrations/Classes
 *
 * @since 1.0.0
 * @version 3.37.17
 */

defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress Integration
 *
 * @since 1.0.0
 * @since 3.12.2 Unknown.
 * @since 3.14.4 Unknown.
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
	 * Configure the integration
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
	 * Add LLMS navigation items to the BuddyPress User Profile
	 *
	 * @since 1.0.0
	 *
	 * @return  void
	 */
	public function add_profile_nav_items() {
		global $bp;

		// add the main nav menu.
		bp_core_new_nav_item(
			array(
				'name'                    => __( 'Courses', 'lifterlms' ),
				'slug'                    => 'courses',
				'position'                => 20,
				'screen_function'         => array( $this, 'courses_screen' ),
				'show_for_displayed_user' => false,
				'default_subnav_slug'     => 'courses',
			)
		);

		$parent_url    = $bp->loggedin_user->domain . 'courses/';
		$is_my_profile = bp_is_my_profile(); // only let the logged in user access subnav screens.

		// add sub nav items.
		bp_core_new_subnav_item(
			array(
				'name'            => __( 'Courses', 'lifterlms' ),
				'slug'            => 'courses',
				'parent_slug'     => 'courses',
				'parent_url'      => $parent_url,
				'screen_function' => array( $this, 'courses_screen' ),
				'user_has_access' => $is_my_profile,
			)
		);

		bp_core_new_subnav_item(
			array(
				'name'            => __( 'Memberships', 'lifterlms' ),
				'slug'            => 'memberships',
				'parent_slug'     => 'courses',
				'parent_url'      => $parent_url,
				'screen_function' => array( $this, 'memberships_screen' ),
				'user_has_access' => $is_my_profile,
			)
		);

		bp_core_new_subnav_item(
			array(
				'name'            => __( 'Achievements', 'lifterlms' ),
				'slug'            => 'achievements',
				'parent_slug'     => 'courses',
				'parent_url'      => $parent_url,
				'screen_function' => array( $this, 'achievements_screen' ),
				'user_has_access' => $is_my_profile,
			)
		);

		bp_core_new_subnav_item(
			array(
				'name'            => __( 'Certificates', 'lifterlms' ),
				'slug'            => 'certificates',
				'parent_slug'     => 'courses',
				'parent_url'      => $parent_url,
				'screen_function' => array( $this, 'certificates_screen' ),
				'user_has_access' => $is_my_profile,
			)
		);

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
	 *
	 * @return void
	 */
	public function achievements_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_achievements' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Certificates" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 *
	 * @return void
	 */
	public function certificates_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_certificates' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Courses" profile screen
	 *
	 * @since 1.0.0
	 * @since 3.14.4 Unknown.
	 * @since 3.37.17 Added action and filters to fix handling pagination links mofication.
	 *
	 * @return void
	 */
	public function courses_screen() {

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
	 * Remove specific paginate links filter after the template has been rendered
	 *
	 * @since 3.37.17
	 */
	public function remove_courses_paginate_links_filter() {
		remove_filter( 'paginate_links', array( $this, 'modify_courses_paginate_links' ) );
	}

	/**
	 * Modify the pagination links displayed on the courses endpoint in the bp member profile
	 *
	 * @since 3.37.17
	 *
	 * @param string $link Default link.
	 * @return string
	 */
	public function modify_courses_paginate_links( $link ) {

		global $wp_rewrite;

		// Retrieve the `courses` subnav item.
		$courses_subnav_item = buddypress()->members->nav->get_secondary(
			array(
				'parent_slug' => 'courses',
				'slug'        => 'courses',
			)
		);

		if ( is_array( $courses_subnav_item ) ) {
			$courses_subnav_item = reset( $courses_subnav_item );
		} else {
			return $link;
		}

		$query = parse_url( $link, PHP_URL_QUERY );

		if ( $query ) {
			$link = str_replace( '?' . $query, '', $link );
		}

		$parts = explode( '/', untrailingslashit( $link ) );
		$page  = end( $parts );

		/**
		 * Here's the core of this filter.
		 *
		 * What happens is that the paginate links on the 'courses' tab are of this type:
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
		 * Despite one might expect `$courses_subnav_item->link` doesn't point to `example.local/members/admin/courses/courses/`
		 * but to `example.local/members/admin/courses/`, which is the link of the parent nav, the main nav item,
		 * this because the 'courses' subnav item is the default of the 'courses' nav item.
		 *
		 * (the fact that both the slugs are "courses" doesn't matter here, it doesn't determine any conflict).
		 */
		$search  = $courses_subnav_item->link . $wp_rewrite->pagination_base . '/' . $page . '/';
		$replace = $courses_subnav_item->link . $courses_subnav_item->slug . '/' . $wp_rewrite->pagination_base . '/' . $page . '/';

		/**
		 * For links to page/1 let's back on the main nav item link to avoid ugly URLs, so we replace something like
		 * `example.local/members/admin/courses/courses/page/1`
		 * to something like
		 * `example.local/members/admin/courses/`
		 */
		if ( 1 === absint( $page ) ) {
			$search  = $replace;
			$replace = $courses_subnav_item->link;
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
	 *
	 * @return void
	 */
	public function memberships_screen() {
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
