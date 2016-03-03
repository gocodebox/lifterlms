<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* BuddyPress Integration
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Integration_Buddypress {
	public $id = 'bp';
	public $title = 'BuddyPress';

	/**
	 * Constructor
	 *
	 * @return  null
	 */
	public function __construct() {
		$this->available = $this->is_available();
		$this->installed = $this->is_installed();

		$this->enabled = ($this->available && $this->installed) ? true : false;

		if ($this->enabled) {
			add_action( 'bp_setup_nav',array( $this, 'add_profile_nav_items' ) );
		}
	}


	/**
	 * Add LLMS navigation items to the BuddyPress User Profile
	 * @return  null
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

		$parent_url = $bp->loggedin_user->domain.'courses/';
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
	 * Checks checks if the LLMS BuddyPress integration is enabled
	 * @return boolean
	 */
	public function is_available() {
		if (get_option( 'lifterlms_buddypress_enabled' ) == 'yes') {
			return true;
		}
		return false;
	}


	/**
	 * Checks if the BuddyPress plugin is installed & activated
	 * @return boolean
	 */
	public function is_installed() {
		if (class_exists( 'BuddyPress' )) {
			return true;
		}
		return false;
	}




	/**
	 * Callback for "Achievements" profile screen
	 * @return null
	 */
	public function achievements_screen() {
		// add_action('bp_template_title', array($this,'achievements_title'));
		add_action( 'bp_template_content', array( $this, 'achievements_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

		/**
		 * "Achievements" profile screen content
		 * @return null
		 */
	public function achievements_content() {
		llms_get_template( 'myaccount/my-achievements.php' );
	}



	/**
	 * Callback for "Certificates" profile screen
	 * @return null
	 */
	public function certificates_screen() {
		// add_action('bp_template_title', array($this,'certificates_title'));
		add_action( 'bp_template_content', array( $this, 'certificates_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

		/**
		 * "Certificates" profile screen content
		 * @return null
		 */
	public function certificates_content() {
		llms_get_template( 'myaccount/my-certificates.php' );
	}



	/**
	 * Callback for "Courses" profile screen
	 * @return null
	 */
	public function courses_screen() {
		// add_action('bp_template_title', array($this,'courses_title'));
		add_action( 'bp_template_content', array( $this, 'courses_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

		/**
		 * "Courses" profile screen content
		 * @return null
		 */
	public function courses_content() {
		llms_get_template( 'myaccount/my-courses.php' );
	}





	/**
	 * Returns a permalink for the registration page as selected in buddypress options
	 * @return string / permalink
	 */
	public function get_registration_permalink() {
		$option = get_option( 'bp-pages' );
		if (array_key_exists( 'register', $option )) {
			return get_the_permalink( $option['register'] );
		}
	}

}
