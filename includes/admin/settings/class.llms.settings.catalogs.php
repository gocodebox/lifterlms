<?php
/**
* Admin Settings Page "Catalogs" Tab
* @since  3.0.0
* @version 3.0.0
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Catalogs extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {

		$this->id    = 'catalogs';
		$this->label = __( 'Catalogs', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 * @return array
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	public function get_settings() {

		return apply_filters( 'lifterlms_catalog_settings', array(

			array(
				'class' => 'top',
				'id' => 'course_archive_options',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'course_options',
				'title' => __( 'Course Catalog Settings', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'class'		=> 'llms-select2-post',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-post-type' => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'desc' => '<br/>' . sprintf( __( 'This page is where your visitors will find a list of all your available courses. %sMore information%s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/course-catalog/" target="_blank">', '</a>' ),
				'id' => 'lifterlms_shop_page_id',
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_shop_page_id', '' ) ),
				'title' => __( 'Course Catalog', 'lifterlms' ),
				'type' => 'select',
			),

			array(
				'default'	=> 10,
				'desc' => '<br/>' . __( 'To show all courses on one page, enter -1', 'lifterlms' ),
				'id' => 'lifterlms_shop_courses_per_page',
				'title' => __( 'Courses per page', 'lifterlms' ),
				'type' => 'text',
			),

			array(
				'default' => 'menu_order',
				'desc'  => '<br />' . __( 'Determines the display order for courses on the courses page.', 'lifterlms' ),
				'id'    => 'lifterlms_shop_ordering',
				'options' => array(
					'menu_order,ASC' => __( 'Order (Low to High)', 'lifterlms' ),
					'title,ASC' => __( 'Title (A - Z)', 'lifterlms' ),
					'title,DESC' => __( 'Title (Z - A)', 'lifterlms' ),
					'date,DESC' => __( 'Most Recent', 'lifterlms' ),
				),
				'title' => __( 'Catalog Sorting', 'lifterlms' ),
				'type' => 'select',

			),

			array(
				'type' => 'sectionend',
				'id' => 'course_archive_options',
			),

			array(
				'class' => 'top',
				'id' => 'membership_options',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'membership_options',
				'title' => __( 'Memberships Catalog', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'class'		=> 'llms-select2-post',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-post-type' => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'default'	=> '',
				'desc' => '<br/>' . __( 'This page is where your visitors will find a list of all your available memberships.', 'lifterlms' ),
				'id' => 'lifterlms_memberships_page_id',
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_memberships_page_id', '' ) ),
				'title' => __( 'Memberships Page', 'lifterlms' ),
				'type' => 'select',
			),

			array(
				'title' => __( 'Memberships per page', 'lifterlms' ),
				'desc' => '<br/>' . __( 'To show all memberships on one page, enter -1', 'lifterlms' ),
				'id' => 'lifterlms_memberships_per_page',
				'type' => 'text',
				'default'	=> '10',
				'css' => 'min-width:200px;',
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

			// @todo this setting doesn't belong here
			array(
				'class' => 'llms-select2-post',
				'custom_attributes' => array(
					'data-allow-clear' => true,
					'data-post-type' => 'llms_membership',
					'data-placeholder' => __( 'Select a membership', 'lifterlms' ),
				),
				'default'	=> '',
				'desc' => '<br/>' . __( 'Only allow access to site to users with a specific membership level. Users will be able to view and purchase membership level.', 'lifterlms' ),
				'id' => 'lifterlms_membership_required',
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_membership_required', '' ) ),
				'title' => __( 'Restrict site by membership level', 'lifterlms' ),
				'type' => 'select',
			),

			array(
				'id' => 'membership_options',
				'type' => 'sectionend',
			),

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

return new LLMS_Settings_Catalogs();
