<?php
/**
 * Admin Settings Page, Dashboard Tab.
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, Dashboard Tab class
 *
 * @since 1.0.0
 * @since 3.22.0 Unknown.
 */
class LLMS_Settings_Dashboard extends LLMS_Settings_Page {

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

		$this->id    = 'dashboard';
		$this->label = __( 'Dashboard', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array.
	 *
	 * @since [version]
	 *
	 * @return array
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
			'id'    => 'section_quick_links',
			'type'  => 'sectionstart',
			'class' => 'top',
		);

		$settings[] = array(
			'title' => __( 'Quick Links', 'lifterlms' ),
			'type'  => 'title',
			'desc'  => '
				<div class="llms-list">
					<ul>
						<li><p>' . sprintf( __( 'Version: %s', 'lifterlms' ), llms()->version ) . '</p></li>
						<li><p>' . sprintf( __( 'Need help? Get support on the %1$sforums%2$s', 'lifterlms' ), '<a href="https://wordpress.org/support/plugin/lifterlms" target="_blank">', '</a>' ) . '</p></li>
						<li><p>' . sprintf( __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Get started at %s', 'lifterlms' ), '<a href="https://lifterlms.com/docs" target="_blank">https://lifterlms.com/docs</a>' ) . '</p></li>
						<li><p>' . sprintf( __( 'Get LifterLMS news, updates, and more on our %1$sblog%2$s', 'lifterlms' ), '<a href="http://blog.lifterlms.com/" target="_blank">', '</a>' ) . '</p></li>
					</ul>
				</div>',
			'id'    => 'quick_links',
		);

		$settings[] = array(
			'id'   => 'section_quick_links',
			'type' => 'sectionend',
		);

		return apply_filters( 'lifterlms_dashboard_settings', $settings );

	}

	public static function get_stats_widgets() {

		ob_start();

		echo '<h3>' . __( 'Activity This Week', 'lifterlms' ) . '</h3>';
		echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>';
		llms_get_template(
			'admin/reporting/tabs/widgets.php',
			array(
				'json'        => wp_json_encode(
					array(
						'current_tab'         => 'dashboard',
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
	 * Get advert banner HTML.
	 *
	 * @since 1.0.0
	 * @since 3.22.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return string
	 */
	public static function get_small_banners() {

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

return new LLMS_Settings_Dashboard();
