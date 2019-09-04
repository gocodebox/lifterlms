<?php
/**
 * Admin Settings Page "Memberships" Tab
 *
 * @since   3.5.0
 * @version 3.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Settings_Memberships extends LLMS_Settings_Page {

	/**
	 * Constructor
	 *
	 * executes settings tab actions
	 */
	public function __construct() {

		$this->id    = 'memberships';
		$this->label = __( 'Memberships', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return  array
	 * @since   3.5.0
	 * @version 3.5.0
	 */
	public function get_settings() {

		return apply_filters(
			'lifterlms_membership_settings',
			array(

				array(
					'class' => 'top',
					'id'    => 'membership_general_options',
					'type'  => 'sectionstart',
				),

				array(
					'id'    => 'membership_general_options_title',
					'title' => __( 'Membership Settings', 'lifterlms' ),
					'type'  => 'title',
				),

				array(
					'class'             => 'llms-select2-post',
					'custom_attributes' => array(
						'data-allow-clear' => true,
						'data-post-type'   => 'llms_membership',
						'data-placeholder' => __( 'Select a membership', 'lifterlms' ),
					),
					'default'           => '',
					'desc'              => '<br/>' . __( 'Only allow access to site to users with a specific membership level. Users will be able to view and purchase membership level.', 'lifterlms' ),
					'id'                => 'lifterlms_membership_required',
					'options'           => llms_make_select2_post_array( get_option( 'lifterlms_membership_required', '' ) ),
					'title'             => __( 'Restrict site by membership level', 'lifterlms' ),
					'type'              => 'select',
				),

				array(
					'id'   => 'membership_general_options',
					'type' => 'sectionend',
				),

				array(
					'class' => 'top',
					'id'    => 'membership_catalog_options',
					'type'  => 'sectionstart',
				),

				array(
					'id'    => 'membership_catalog_options_title',
					'title' => __( 'Memberships Catalog', 'lifterlms' ),
					'type'  => 'title',
				),

				array(
					'class'             => 'llms-select2-post',
					'custom_attributes' => array(
						'data-allow-clear' => true,
						'data-post-type'   => 'page',
						'data-placeholder' => __( 'Select a page', 'lifterlms' ),
					),
					'default'           => '',
					'desc'              => '<br/>' . __( 'This page is where your visitors will find a list of all your available memberships.', 'lifterlms' ),
					'id'                => 'lifterlms_memberships_page_id',
					'options'           => llms_make_select2_post_array( get_option( 'lifterlms_memberships_page_id', '' ) ),
					'title'             => __( 'Memberships Page', 'lifterlms' ),
					'type'              => 'select',
				),

				array(
					'title'   => __( 'Memberships per page', 'lifterlms' ),
					'desc'    => '<br/>' . __( 'To show all memberships on one page, enter -1', 'lifterlms' ),
					'id'      => 'lifterlms_memberships_per_page',
					'type'    => 'number',
					'default' => 9,
					'css'     => 'min-width:200px;',
				),

				array(
					'default' => 'menu_order',
					'desc'    => '<br />' . __( 'Determines the display order for items on the memberships page.', 'lifterlms' ),
					'id'      => 'lifterlms_memberships_ordering',
					'options' => array(
						'menu_order,ASC' => __( 'Order (Low to High)', 'lifterlms' ),
						'title,ASC'      => __( 'Title (A - Z)', 'lifterlms' ),
						'title,DESC'     => __( 'Title (Z - A)', 'lifterlms' ),
						'date,DESC'      => __( 'Most Recent', 'lifterlms' ),
					),
					'title'   => __( 'Memberships Sorting', 'lifterlms' ),
					'type'    => 'select',

				),

				array(
					'id'   => 'membership_catalog_options',
					'type' => 'sectionend',
				),

			)
		);
	}

	/**
	 * save settings to the database
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function save() {

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

	}

	/**
	 * Output settings
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function output() {
		$settings = $this->get_settings();
		LLMS_Admin_Settings::output_fields( $settings );
	}

}

return new LLMS_Settings_Memberships();
