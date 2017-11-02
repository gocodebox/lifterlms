<?php
/**
 * BuddyPress Integration
 * @since    1.0.0
 * @version  3.14.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Integration_Buddypress extends LLMS_Abstract_Integration {

	public $id = 'buddypress';

	/**
	 * Display order on Integrations tab
	 * @var  integer
	 */
	protected $priority = 5;

	/**
	 * Configure the integration
	 * Do things like configure ID and title here
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	protected function configure() {

		$this->title = __( 'BuddyPress', 'lifterlms' );
		$this->description = sprintf( __( 'Add LifterLMS information to user profiles and enable membership restrictions for activity, group, and member directories. %1$sLearn More%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-buddypress/" target="_blank">', '</a>' );

		if ( $this->is_available() ) {

			add_action( 'bp_setup_nav',array( $this, 'add_profile_nav_items' ) );

			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks' ), 40, 1 );

		}

	}

	/**
	 * Add LLMS navigation items to the BuddyPress User Profile
	 * @return  null
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_profile_nav_items() {
		global $bp;
		// add the main nav menu
		bp_core_new_nav_item( array(
			'name' => __( 'Courses', 'lifterlms' ),
			'slug' => 'courses',
			'position' => 20,
			'screen_function' => array( $this,'courses_screen' ),
			'show_for_displayed_user' => false,
			'default_subnav_slug' => 'courses',
		));

		$parent_url = $bp->loggedin_user->domain . 'courses/';
		$is_my_profile = bp_is_my_profile(); // only let the logged in user access subnav screens

		// add sub nav items
		bp_core_new_subnav_item(array(
			'name'            => __( 'Courses', 'lifterlms' ),
			'slug'            => 'courses',
			'parent_slug'     => 'courses',
			'parent_url'      => $parent_url,
			'screen_function' => array( $this,'courses_screen' ),
			'user_has_access' => $is_my_profile,
		));

		bp_core_new_subnav_item(array(
			'name'            => __( 'Memberships', 'lifterlms' ),
			'slug'            => 'memberships',
			'parent_slug'     => 'courses',
			'parent_url'      => $parent_url,
			'screen_function' => array( $this,'memberships_screen' ),
			'user_has_access' => $is_my_profile,
		));

		bp_core_new_subnav_item(array(
			'name'            => __( 'Achievements', 'lifterlms' ),
			'slug'            => 'achievements',
			'parent_slug'     => 'courses',
			'parent_url'      => $parent_url,
			'screen_function' => array( $this,'achievements_screen' ),
			'user_has_access' => $is_my_profile,
		));

		bp_core_new_subnav_item(array(
			'name'            => __( 'Certificates', 'lifterlms' ),
			'slug'            => 'certificates',
			'parent_slug'     => 'courses',
			'parent_url'      => $parent_url,
			'screen_function' => array( $this,'certificates_screen' ),
			'user_has_access' => $is_my_profile,
		));
	}

	/**
	 * Checks if the BuddyPress plugin is installed & activated
	 * @return boolean
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_installed() {
		return ( class_exists( 'BuddyPress' ) );
	}

	/**
	 * Callback for "Achievements" profile screen
	 * @return null
	 * @since   1.0.0
	 * @version 3.14.4
	 */
	public function achievements_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_achievements' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Certificates" profile screen
	 * @return null
	 * @since   1.0.0
	 * @version 3.14.4
	 */
	public function certificates_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_certificates' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "Courses" profile screen
	 * @return null
	 * @since   1.0.0
	 * @version 3.14.4
	 */
	public function courses_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_courses' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for "memberships" profile screen
	 * @return null
	 * @since   1.0.0
	 * @version 3.14.4
	 */
	public function memberships_screen() {
		add_action( 'bp_template_content', 'lifterlms_template_student_dashboard_my_memberships' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Allows restricting of BP Directory Pages for Activity and Members via LifterLMS membership restrictions
	 * @param    array     $results  array of restriction results
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function restriction_checks( $results ) {

		// only check directories
		if ( ! bp_is_directory() ) {
			return $results;
		}

		$post_id = null;

		// activity
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

				$results['content_id'] = $post_id;
				$results['restriction_id'] = $restriction_id;
				$results['reason'] = 'membership';

			}
		}

		return $results;

	}

}
