<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Membership Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Membership extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'membership';
		$this->label = __( 'Membership', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		$protected_post_types = array( 'lesson', 'llms_certificate', 'llms_my_certificate', 'llms_question', 'llms_quiz' );

		$args = array(
			'public' => true
		);

		$post_types = get_post_types( $args );

		foreach( $post_types as $key => $type ) {
			if ( in_array($type, $protected_post_types) ) {
				unset( $post_types[$key] );
			}
		}

		return apply_filters( 'lifterlms_membership_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'membership_options', 'class' => 'top' ),

			array( 'title' => __( 'Membership Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Customize your membership for a unique user experience.', 'id' => 'membership_options' ),

			array(
				'title' => __( 'Membership Page', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying memberships.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_memberships_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
			),
			array(
				'title' => __( 'Restrict site by membership level', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Only allow access to site to users with a specific membership level. Users will be able to view and purchase membership level.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_membership_required',
				'type' 		=> 'single_select_membership',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
			),

			array(
				'title' => __( 'Redirect members to checkout', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Automatically redirect users to checkout after selecting membership.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'redirect_to_checkout',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Select the post types that you would like to have the ability to restrict to membership level\'s on an individual basis', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Automatically redirect users to checkout after selecting membership.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'redirect_to_checkout',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Membership Page', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying memberships.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_membership_restricted_box',
				'type' 		=> 'multiselect',
				'options'	=> $post_types,
				'default'	=> '',
				'class'		=> 'select2',
				'multi'		=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'membership_options' ),

		) );
	}

	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 */
	public function save() {
		$settings = $this->get_settings();

		LLMS_Admin_Settings::save_fields( $settings );

	}

	/**
	 * get settings from the database
	 *
	 * @return array
	 */
	public function output() {
		$settings = $this->get_settings( );

		LLMS_Admin_Settings::output_fields( $settings );
	}

}

return new LLMS_Settings_Membership();
