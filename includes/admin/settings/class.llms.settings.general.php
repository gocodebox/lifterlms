<?php
/**
 * Admin Settings Page, General Tab
 * @since  1.0.0
 * @version  3.5.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'register_hooks' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 * @since  1.0.0
	 * @version  3.5.0
	 */
	public function get_settings() {

		return apply_filters( 'lifterlms_general_settings', array(

			array(
					'type' => 'custom-html',
					'value' => self::get_stats_widgets(),
			),

			array(
					'type' => 'custom-html',
					'value' => self::get_small_banners(),
			),

			array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' => 'top' ),

			array(
				'title' => __( 'Quick Links',
				'lifterlms' ),
					'type' => 'title',
					'desc' => '

					<div class="llms-list">
						<ul>
							<li><p>' . sprintf( __( 'Version: %s', 'lifterlms' ), LLMS()->version ) . '</p></li>
							<li><p>' . sprintf( __( 'Need help? Get support on the %1$sforums%2$s', 'lifterlms' ), '<a href="https://wordpress.org/support/plugin/lifterlms" target="_blank">' , '</a>' ) . '</p></li>
							<li><p>' . sprintf( __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Get started at %s', 'lifterlms' ), '<a href="https://lifterlms.com/docs" target="_blank">https://lifterlms.com/docs</a>' ) . '</p></li>
							<li><p>' . sprintf( __( 'Get LifterLMS news, updates, and more on our %1$sblog%2$s', 'lifterlms' ), '<a href="http://blog.lifterlms.com/" target="_blank">', '</a>' ) . '</p></li>
						</ul>
					</div>',
				'id' => 'activation_options',
			),

			array( 'type' => 'sectionend', 'id' => 'general_information' ),

			array(
				'id' => 'section_features',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'features',
				'title' => __( 'Features', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'type' => 'custom-html',
				'value' => sprintf(
					__( 'Automatic Recurring Payments: <strong>%s</strong>', 'lifterlms' ),
					LLMS_Site::get_feature( 'recurring_payments' ) ? __( 'Enabled', 'lifterlms' ) : __( 'Disabled', 'lifterlms' )
				),
			),

			array(
				'id' => 'section_tools',
				'type' => 'sectionend',
			),

			array(
				'id' => 'section_tools',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'tools_utilities',
				'title' => __( 'Tools and Utilities', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'desc' => __( 'Allows you to choose to enable or disable automatic recurring payments which may be disabled on a staging site.', 'lifterlms' ),
				'name' => 'automatic-payments',
				'title' => __( 'Automatic Payments', 'lifterlms' ),
				'type' 		=> 'button',
				'value' => __( 'Reset Automatic Payments', 'lifterlms' ),
			),

			array(
				'desc' => __( 'Manage User Sessions. LifterLMS creates custom user sessions to manage, payment processing, quizzes and user registration. If you are experiencing issues or incorrect error messages are displaying. Clearing out all of the user session data may help.', 'lifterlms' ),
				'name' => 'clear-sessions',
				'title' => __( 'Sessions', 'lifterlms' ),
				'type' 		=> 'button',
				'value' => __( 'Clear All Session Data', 'lifterlms' ),
			),

			array(
				'desc' => __( 'If you opted into LifterLMS Tracking and no longer wish to participate, you may opt out here.', 'lifterlms' ),
				'name' => 'reset-tracking',
				'title' => __( 'Tracking Status', 'lifterlms' ),
				'type' 		=> 'button',
				'value' => __( 'Reset Tracking Status', 'lifterlms' ),
			),

			array(
				'value' => '
					<tr valign="top"><th><label>' . __( 'Setup Wizard', 'lifterlms' ) . '</label></th>
					<td class="forminp forminp-button">
					<div id="llms-form-wrapper">
						<span class="description">' . __( 'If you want to run the LifterLMS Setup Wizard again or skipped it and want to return now, click below.', 'lifterlms' ) . '</span>
						<br><br>
						<a class="llms-button-primary" href="' . admin_url() . '?page=llms-setup">' . __( 'Return to Setup Wizard', 'lifterlms' ) . '</a>
					</div>
					</td></tr>
				',
				'type' => 'custom-html-no-wrap',
			),

			array(
				'desc' => __( 'Clears the cached data displayed on various reporting screens. This does not affect actual student progress, it only clears cached progress data. This data will be regenerated the next time it is accessed.', 'lifterlms' ),
				'name' => 'clear-cache',
				'title' => __( 'Clear Student Progress Cache', 'lifterlms' ),
				'type' 		=> 'button',
				'value' => __( 'Clear Cache', 'lifterlms' ),
			),

			array(
				'id' => 'section_tools',
				'type' => 'sectionend',
			),

		) );

	}

	/**
	 * register new hooks
	 * @return void
	 * @since  1.0.0
	 * @version  3.0.0
	 */
	public function register_hooks() {

		// @todo this doesnt appaer like it does what its supposed to...
		if ( isset( $_POST['clear-sessions'] ) ) {
			session_unset();
		}

		if ( isset( $_POST['clear-cache'] ) ) {

			global $wpdb;

			// Delete all cached student data
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key = %s or meta_key = %s;",
				'llms_overall_progress', 'llms_overall_grade'
			) );

		}

		if ( isset( $_POST['reset-tracking'] ) ) {
			update_option( 'llms_allow_tracking', 'no' );
		}

		// deletes the "ignore" url so the staging modal will re-appear
		if ( isset( $_POST['automatic-payments'] ) ) {
			LLMS_Site::clear_lock_url();
			update_option( 'llms_site_url_ignore', 'no' );
		}

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

	public static function get_stats_widgets() {

		ob_start();

		echo '<h3>' . __( 'Activity This Week', 'lifterlms' ) . '</h3>';
		echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>';
		llms_get_template( 'admin/reporting/tabs/widgets.php', array(
			'json' => json_encode( array(
				'current_tab' => 'settings',
				'current_range' => 'last-7-days',
				'current_students' => array(),
				'current_courses' => array(),
				'current_memberships' => array(),
				'dates' => array(
					'start' => date( 'Y-m-d', current_time( 'timestamp' ) - WEEK_IN_SECONDS ),
					'end' => current_time( 'Y-m-d' ),
				),
			) ),
			'widget_data' => array(
				array(
					'enrollments' => array(
						'title' => __( 'Enrollments', 'lifterlms' ),
						'cols' => '1-4',
						'content' => __( 'loading...', 'lifterlms' ),
						'info' => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
					),
					'registrations' => array(
						'title' => __( 'Registrations', 'lifterlms' ),
						'cols' => '1-4',
						'content' => __( 'loading...', 'lifterlms' ),
						'info' => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
					),
					'sold' => array(
						'title' => __( 'Net Sales', 'lifterlms' ),
						'cols' => '1-4',
						'content' => __( 'loading...', 'lifterlms' ),
						'info' => __( 'Total of all successful transactions during this period', 'lifterlms' ),
					),
					'lessoncompletions' => array(
						'title' => __( 'Lessons Completed', 'lifterlms' ),
						'cols' => '1-4',
						'content' => __( 'loading...', 'lifterlms' ),
						'info' => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
					),
				),
			),
		) );

		return ob_get_clean();

	}

	public static function get_small_banners() {
		$small_banners = array(
				array(
						'title' => 'Ultimate Course Creation Framework',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/online-course.jpg',
						'link' => 'http://courseclinic.com/?utm_source=Plugin&utm_medium=Plugin+Settings&utm_campaign=Plugin+to+Course+Clinic+Opt-in',
				),
				array(
						'title' => 'LifterLMS Demo Course',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-expert.jpg',
						'link' => 'http://demo.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/?ims=phyxo&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=Demo+Ad+001',
				),
				array(
						'title' => 'Course Blueprint',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/students-engaged.jpg',
						'link' => 'https://lifterlms.com/free-lifterlms-course?ims=aympo&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=CBP+Ad+001',
				),
				array(
						'title' => 'LifterLMS Optin',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-optin.jpg',
						'link' => 'http://lifterlms.com/fast-start?ims=pfckn&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=FS+Ad+001',
				),
		);

		$html = '<div class="llms-widget-row">';

		foreach ($small_banners as $banner) {

			$html .= '<div class="llms-widget-1-4">
							<div class="llms-widget llms-banner-image">
								<a href="' . $banner['link'] . '" target="_blank">
									<img width="100%" src="' . $banner['image'] . '" alt="' . $banner['image'] . '">
								</a>
							</div>
						</div>';
		}

		$html .= '</div>';

		return $html;
	}

}

return new LLMS_Settings_General();
