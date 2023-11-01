<?php
/**
 * Admin Settings Page, General Tab.
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 5.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, General Tab class
 *
 * @since 1.0.0
 * @since 3.22.0 Unknown.
 */
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	 * Constructor
	 *
	 * Executes settings tab actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array.
	 *
	 * @since 1.0.0
	 * @since 3.13.0 Unknown.
	 * @since 5.6.0 use LLMS_Roles::get_all_role_names() to retrieve the list of roles who can bypass enrollments.
	 *              Add content protection setting.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array();

		$settings[] = array(
			'id'    => 'section_features',
			'type'  => 'sectionstart',
			'class' => 'top',
		);

		$settings[] = array(
			'id'    => 'features',
			'title' => __( 'Features', 'lifterlms' ),
			'type'  => 'title',
		);

		$settings[] = array(
			'type'  => 'custom-html',
			'value' => sprintf(
				__( 'Automatic Recurring Payments: <strong>%s</strong>', 'lifterlms' ),
				LLMS_Site::get_feature( 'recurring_payments' ) ? __( 'Enabled', 'lifterlms' ) : __( 'Disabled', 'lifterlms' )
			),
		);

		$settings[] = array(
			'id'   => 'section_features',
			'type' => 'sectionend',
		);

		$settings[] = array(
			'id'   => 'section_tools',
			'type' => 'sectionstart',
		);

		$settings[] = array(
			'id'    => 'general_settings',
			'title' => __( 'General Settings', 'lifterlms' ),
			'type'  => 'title',
		);

		$settings[] = array(
			'class'             => 'llms-select2',
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select user roles', 'lifterlms' ),
			),
			'default'           => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
			'desc'              => __( 'Users with the selected roles will bypass enrollment, drip, and prerequisite restrictions for courses and memberships.', 'lifterlms' ),
			'id'                => 'llms_grant_site_access',
			'options'           => array_filter(
				LLMS_Roles::get_all_role_names(),
				function ( $role ) {
					return 'student' !== $role;
				},
				ARRAY_FILTER_USE_KEY
			),
			'title'             => __( 'Unrestricted Preview Access', 'lifterlms' ),
			'type'              => 'multiselect',
		);

		$settings[] = array(
			'title'   => __( 'Content Protection', 'lifterlms' ),
			'desc'    => __( 'Prevent users from copying website content and downloading images.', 'lifterlms' ) . '<br><br>' . __( 'Users with Unrestricted Preview Access will not be affected by this setting.', 'lifterlms' ),
			'id'      => 'lifterlms_content_protection',
			'default' => 'no',
			'type'    => 'checkbox',
		);

		$settings[] = array(
			'id'   => 'general_settings',
			'type' => 'sectionend',
		);

		return apply_filters( 'lifterlms_general_settings', $settings );

	}

	/**
	 * save settings to the database
	 *
	 * @return void
	 */
	public function save() {

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

	}

}

return new LLMS_Settings_General();
