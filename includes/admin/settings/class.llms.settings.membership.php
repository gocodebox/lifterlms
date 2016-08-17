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
		// Get shop page
		$memberships_page_id = llms_get_page_id( 'memberships' );

		$base_slug = ($memberships_page_id > 0 && get_page( $memberships_page_id )) ? get_page_uri( $memberships_page_id ) : 'memberships';

		return apply_filters( 'lifterlms_membership_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'membership_options', 'class' => 'top' ),

			array( 'title' => __( 'Membership Settings', 'lifterlms' ), 'type' => 'title','desc' => 'Customize your membership for a unique user experience.', 'id' => 'membership_options' ),

			array(
				'title' => __( 'Memberships Page', 'lifterlms' ),
				'desc' 		=> '<br/>' . __( 'Page used for displaying memberships.', 'lifterlms' ),
				'id' 		=> 'lifterlms_memberships_page_id',
				'type' 		=> 'select',
				'default'	=> '',
				'class'		=> 'llms-select2-post',
				'custom_attributes' => array(
					'data-post-type' => 'page',
				),
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_memberships_page_id', '' ) ),
			),

			array(
				'title' => __( 'Memberships per page', 'lifterlms' ),
				'desc' 		=> '<br/>' . __( 'To show all memberships on one page, enter -1', 'lifterlms' ),
				'id' 		=> 'lifterlms_memberships_per_page',
				'type' 		=> 'text',
				'default'	=> '10',
				'css' 		=> 'min-width:200px;',
			),

			array(
				'default' => 'menu_order',
				'desc'  => '<br />' . __( 'Determines the display order for items on the memberships page.', 'lifterlms' ),
				'id'    => 'lifterlms_memberships_ordering',
				'options' => array(
					'menu_order,ASC' => __( 'Order (Low to High)', 'lifterlms' ),
					'title,ASC' => __( 'Title (A - Z)', 'lifterlms' ),
					'title,DESC' => __( 'Title (Z - A)', 'lifterlms' ),
					'date,DESC' => __( 'Most Recent', 'lifterlms' ),
				),
				'title' => __( 'Memberships Sorting', 'lifterlms' ),
				'type' => 'select',

			),


			array(
				'title' => __( 'Restrict site by membership level', 'lifterlms' ),
				'desc' 		=> '<br/>' . __( 'Only allow access to site to users with a specific membership level. Users will be able to view and purchase membership level.', 'lifterlms' ),
				'id' 		=> 'lifterlms_membership_required',
				'type' 		=> 'single_select_membership',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
			),

			array(
				'title' => __( 'Redirect members to checkout', 'lifterlms' ),
				'desc' 		=> '<br/>' . __( 'Automatically redirect users to checkout after selecting membership.', 'lifterlms' ),
				'id' 		=> 'redirect_to_checkout',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
				'desc_tip'	=> true,
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
