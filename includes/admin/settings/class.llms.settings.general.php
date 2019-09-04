<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, General Tab
 *
 * @since    1.0.0
 * @version  3.22.0
 */
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	 * Constructor
	 *
	 * executes settings tab actions
	 */
	public function __construct() {

		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return   array
	 * @since    1.0.0
	 * @version  3.13.0
	 */
	public function get_settings( $settings_only = false ) {

		$settings = array();

		if ( ! $settings_only ) {

			$settings[] = array(
				'type'  => 'custom-html',
				'value' => self::get_stats_widgets(),
			);

			$settings[] = array(
				'type'  => 'custom-html',
				'value' => self::get_small_banners(),
			);

		}

		$settings[] = array(
			'type'  => 'sectionstart',
			'id'    => 'general_information',
			'class' => 'top',
		);

		$settings[] = array(
			'title' => __( 'Quick Links', 'lifterlms' ),
			'type'  => 'title',
			'desc'  => '
				<div class="llms-list">
					<ul>
						<li><p>' . sprintf( __( 'Version: %s', 'lifterlms' ), LLMS()->version ) . '</p></li>
						<li><p>' . sprintf( __( 'Need help? Get support on the %1$sforums%2$s', 'lifterlms' ), '<a href="https://wordpress.org/support/plugin/lifterlms" target="_blank">', '</a>' ) . '</p></li>
						<li><p>' . sprintf( __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Get started at %s', 'lifterlms' ), '<a href="https://lifterlms.com/docs" target="_blank">https://lifterlms.com/docs</a>' ) . '</p></li>
						<li><p>' . sprintf( __( 'Get LifterLMS news, updates, and more on our %1$sblog%2$s', 'lifterlms' ), '<a href="http://blog.lifterlms.com/" target="_blank">', '</a>' ) . '</p></li>
					</ul>
				</div>',
			'id'    => 'activation_options',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'general_information',
		);

		$settings[] = array(
			'id'   => 'section_features',
			'type' => 'sectionstart',
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

		$roles    = array();
		$wp_roles = wp_roles()->roles;
		foreach ( $wp_roles as $key => $wp_role ) {
			if ( 'student' === $key ) {
				continue; }
			$roles[ $key ] = $wp_role['name'];
		}
		$settings[] = array(
			'class'             => 'llms-select2',
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select user roles', 'lifterlms' ),
			),
			'default'           => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
			'desc'              => __( 'Users with the selected roles will bypass enrollment, drip, and prerequisite restrictions for courses and memberships.', 'lifterlms' ),
			'id'                => 'llms_grant_site_access',
			'options'           => $roles,
			'title'             => __( 'Unrestricted Preview Access', 'lifterlms' ),
			'type'              => 'multiselect',
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

	public static function get_stats_widgets() {

		ob_start();

		echo '<h3>' . __( 'Activity This Week', 'lifterlms' ) . '</h3>';
		echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>';
		llms_get_template(
			'admin/reporting/tabs/widgets.php',
			array(
				'json'        => json_encode(
					array(
						'current_tab'         => 'settings',
						'current_range'       => 'last-7-days',
						'current_students'    => array(),
						'current_courses'     => array(),
						'current_memberships' => array(),
						'dates'               => array(
							'start' => date( 'Y-m-d', current_time( 'timestamp' ) - WEEK_IN_SECONDS ),
							'end'   => current_time( 'Y-m-d' ),
						),
					)
				),
				'widget_data' => array(
					array(
						'enrollments'       => array(
							'title'   => __( 'Enrollments', 'lifterlms' ),
							'cols'    => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info'    => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
						),
						'registrations'     => array(
							'title'   => __( 'Registrations', 'lifterlms' ),
							'cols'    => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info'    => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
						),
						'sold'              => array(
							'title'   => __( 'Net Sales', 'lifterlms' ),
							'cols'    => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info'    => __( 'Total of all successful transactions during this period', 'lifterlms' ),
						),
						'lessoncompletions' => array(
							'title'   => __( 'Lessons Completed', 'lifterlms' ),
							'cols'    => '1-4',
							'content' => __( 'loading...', 'lifterlms' ),
							'info'    => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
						),
					),
				),
			)
		);

		return ob_get_clean();

	}

	/**
	 * Get advert banner html
	 *
	 * @return   string
	 * @since    1.0.0
	 * @version  3.22.0
	 */
	public static function get_small_banners() {

		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.addons.php';
		$view = new LLMS_Admin_AddOns();
		$url  = esc_url( admin_url( 'admin.php?page=llms-add-ons' ) );

		ob_start();
		echo '<br>';
		echo '<h3 style="display:inline;">' . __( 'Most Popular Add-ons, Courses, and Resources', 'lifterlms' ) . '</h3>';
		echo '&nbsp;&nbsp;&nbsp;<a class="llms-button-primary small" href="' . $url . '">' . __( 'View More &rarr;', 'lifterlms' ) . '</a><br>';
		$view->output_for_settings();
		return ob_get_clean();

	}

}

return new LLMS_Settings_General();
