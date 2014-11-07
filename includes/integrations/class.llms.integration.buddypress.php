<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* BuddyPress Integration
*
* @version 1.0
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

		if($this->enabled) {
			add_action('bp_setup_nav',array($this,'add_profile_nav_items'));
		}
	}


	/**
	 * Add LLMS navigation items to the BuddyPress User Profile
	 * @return  null
	 */
	public function add_profile_nav_items() {
		bp_core_new_nav_item( array(
			'name' => __( 'Courses', 'lifterlms' ),
			'slug' => 'courses',
			'position' => 20,
			'screen_function' => array($this,'courses'),
			'show_for_displayed_user' => true,
			'default_subnav_slug' => 'test'
		));
	}


	/**
	 * Checks checks if the LLMS BuddyPress integration is enabled
	 * @return boolean
	 */
	public function is_available() {
		if(get_option('lifterlms_buddypress_enabled') == 'yes') {
			return true;
		}
		return false;
	}


	/**
	 * Checks if the BuddyPress plugin is installed & activated
	 * @return boolean
	 */
	public function is_installed() {
		if(class_exists('BuddyPress')) {
			return true;
		}
		return false;
	}


	/**
	 * Callback for "My Courses" profile screen
	 * @return null
	 */
	public function courses() {
		add_action('bp_template_title', array($this,'courses_title'));
		add_action('bp_template_content', array($this,'courses_content'));
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * "My Courses" profile screen title
	 * @return null
	 */
	public function courses_title() {
		_e( 'My Courses', 'lifterlms' );
	}

	/**
	 * "My Courses" profile screen content
	 * @return null
	 */
	public function courses_content() {
		llms_get_template('myaccount/my-courses.php');
	}


	/**
	 * Returns a permalink for the registration page as selected in buddypress options
	 * @return string / permalink
	 */
	public function get_registration_permalink() {
		$option = get_option('bp-pages');
		if(array_key_exists('register', $option)) {
			return get_the_permalink($option['register']);
		}
	}

}
?>