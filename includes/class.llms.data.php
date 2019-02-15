<?php
defined( 'ABSPATH' ) || exit;

/**
 * Retrieve data sets used by various other classes and functions
 * @since    3.0.0
 * @version  3.24.0
 */
class LLMS_Data {

	/**
	 * Get the data data
	 * @param    string     $dataset  dataset to retrieve data for [tracker|system_report]
	 * @param    string     $format   data return format (unused for unrecalled reasons)
	 * @return   array
	 * @since    3.0.0
	 * @version  3.17.0
	 */
	public static function get_data( $dataset, $format = 'array' ) {

		$data = array();

		// add admin email for tracker requests
		if ( 'tracker' === $dataset ) {
			$data['email'] = apply_filters( 'llms_get_data_admin_email', get_option( 'admin_email' ) );
		}

		// general data
		$data['url'] = home_url();

		// wp info
		$data['wordpress'] = self::get_wp_data();

		// llms settings
		$data['settings'] = self::get_llms_settings();

		// gateways
		$data['gateways'] = self::get_gateway_data();

		// server info
		$data['server'] = self::get_server_data();

		// browser / os
		$data['browser'] = self::get_browser_data();

		// theme info
		$data['theme'] = self::get_theme_data();

		// plugin info
		$data['plugins'] = self::get_plugin_data();

		if ( 'tracker' === $dataset ) {

			// published content type counts
			$data['post_counts'] = self::get_post_type_counts();

			// user data
			$data['user_counts'] = self::get_user_counts();

			// count student engagements
			$data['engagement_counts'] = self::get_engagement_counts();

			// order data
			$data['order_counts'] = self::get_order_counts();

		}

		$data['integrations'] = self::get_integrations_data();

		$data['template_overrides'] = self::get_templates_data();

		return $data;

	}

	/**
	 * add browser and os info to the system report
	 * @return   array
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	private static function get_browser_data() {

		$data = array(
			'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
		);

		return $data;

	}

	/**
	 * Get student engagement counts for various llms interactions
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_engagement_counts() {

		global $wpdb;

		$data = array();

		$data['certificates'] = absint( $wpdb->get_var( "SELECT COUNT( * ) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_certificate_earned'" ) );
		$data['achievements'] = absint( $wpdb->get_var( "SELECT COUNT( * ) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_achievement_earned'" ) );
		$enrollments = $wpdb->get_results( "SELECT meta_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND ( meta_value = 'Enrolled' OR meta_value = 'enrolled' ) GROUP BY user_id, post_id" );
		$data['enrollments'] = count( $enrollments );
		$data['course_completions'] = absint( $wpdb->get_var( "SELECT COUNT( * ) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_is_complete' AND meta_value = 'yes' " ) );

		return $data;

	}

	/**
	 * Retrieve metadata from a file.
	 * Copied from WCs get_file_version which is based on WP Core's get_file_data function.
	 * @param    string    $file   Path to the file
	 * @return   string
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	private static function get_file_version( $file ) {

		// Avoid notices if file does not exist
		if ( ! file_exists( $file ) ) {
			return '';
		}

		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$version = _cleanup_header_comment( $match[1] );
		}

		return $version;

	}

	/**
	 * Get data about llms payment gateways
	 * @return   array
	 * @since    3.0.0
	 * @version  3.17.8
	 */
	private static function get_gateway_data() {

		$data = array();

		foreach ( LLMS()->payment_gateways()->get_payment_gateways() as $obj ) {

			$data[ $obj->get_admin_title() ] = $obj->is_enabled() ? 'Enabled' : 'Disabled';

			if ( $obj->supports( 'test_mode' ) ) {
				$data[ $obj->get_admin_title() . '_test_mode' ] = $obj->is_test_mode_enabled() ? 'Enabled' : 'Disabled';
			}

			$data[ $obj->get_admin_title() . '_logging' ] = $obj->get_logging_enabled();
			$data[ $obj->get_admin_title() . '_order' ] = $obj->get_display_order();

		}

		return $data;

	}

	/**
	 * Get data about existing llms integrations
	 * @todo integration settings unique to the integration should be included here
	 * @return   array
	 * @since    3.0.0
	 * @version  3.17.8
	 */
	private static function get_integrations_data() {

		$data = array();

		$integrations = LLMS()->integrations();

		foreach ( $integrations->integrations() as $obj ) {

			// @todo upgrade this when integration absrtact is finished
			if ( method_exists( $obj, 'is_available' ) ) {

				$data[ $obj->title ] = $obj->is_available() ? 'Yes' : 'No';

			}
		}

		return $data;

	}

	/**
	 * Get LifterLMS settings
	 * @return   array
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	private static function get_llms_settings() {

		$data = array();

		$data['version'] = LLMS()->version;
		$data['db_version'] = get_option( 'lifterlms_db_version' );

		$data['course_catalog'] = self::get_page_data( 'lifterlms_shop_page_id' );
		$data['membership_catalog'] = self::get_page_data( 'lifterlms_memberships_page_id' );
		$data['student_dashboard'] = self::get_page_data( 'lifterlms_myaccount_page_id' );
		$data['checkout_page'] = self::get_page_data( 'lifterlms_checkout_page_id' );

		$data['course_catalog_per_page'] = get_option( 'lifterlms_shop_courses_per_page' );
		$data['course_catalog_sorting'] = get_option( 'lifterlms_shop_ordering' );

		$data['membership_catalog_per_page'] = get_option( 'lifterlms_memberships_per_page' );
		$data['membership_catalog_sorting'] = get_option( 'lifterlms_memberships_ordering' );

		$data['site_membership'] = self::get_page_data( 'lifterlms_membership_required' );

		$data['courses_endpoint'] = get_option( 'lifterlms_myaccount_courses_endpoint' );
		$data['edit_endpoint'] = get_option( 'lifterlms_myaccount_edit_account_endpoint' );
		$data['lost_password_endpoint'] = get_option( 'lifterlms_myaccount_lost_password_endpoint' );
		$data['vouchers_endpoint'] = get_option( 'lifterlms_myaccount_redeem_vouchers_endpoint' );

		$data['autogenerate_username'] = get_option( 'lifterlms_registration_generate_username', 'no' );

		$data['password_strength_meter'] = get_option( 'lifterlms_registration_password_strength', 'no' );
		$data['minimum_password_strength'] = get_option( 'lifterlms_registration_password_min_strength' );

		$data['terms_required'] = get_option( 'lifterlms_registration_require_agree_to_terms', 'no' );
		$data['terms_page'] = self::get_page_data( 'lifterlms_terms_page_id' );

		$data['checkout_names'] = get_option( 'lifterlms_user_info_field_names_checkout_visibility' );
		$data['checkout_address'] = get_option( 'lifterlms_user_info_field_address_checkout_visibility' );
		$data['checkout_phone'] = get_option( 'lifterlms_user_info_field_phone_checkout_visibility' );
		$data['checkout_email_confirmation'] = get_option( 'lifterlms_user_info_field_email_confirmation_checkout_visibility', 'no' );

		$data['open_registration'] = get_option( 'lifterlms_enable_myaccount_registration', 'no' );
		$data['registration_names'] = get_option( 'lifterlms_user_info_field_names_registration_visibility' );
		$data['registration_address'] = get_option( 'lifterlms_user_info_field_address_registration_visibility' );
		$data['registration_phone'] = get_option( 'lifterlms_user_info_field_phone_registration_visibility' );
		$data['registration_voucher'] = get_option( 'lifterlms_voucher_field_registration_visibility' );
		$data['registration_email_confirmation'] = get_option( 'lifterlms_user_info_field_email_confirmation_registration_visibility', 'no' );

		$data['account_names'] = get_option( 'lifterlms_user_info_field_names_account_visibility' );
		$data['account_address'] = get_option( 'lifterlms_user_info_field_address_account_visibility' );
		$data['account_phone'] = get_option( 'lifterlms_user_info_field_phone_account_visibility' );
		$data['account_email_confirmation'] = get_option( 'lifterlms_user_info_field_email_confirmation_account_visibility', 'no' );

		$data['confirmation_endpoint'] = get_option( 'lifterlms_myaccount_confirm_payment_endpoint' );
		$data['force_ssl_checkout'] = get_option( 'lifterlms_checkout_force_ssl' );
		$data['country'] = get_lifterlms_country();
		$data['currency'] = get_lifterlms_currency();
		$data['currency_position'] = get_option( 'lifterlms_currency_position' );
		$data['thousand_separator'] = get_option( 'lifterlms_thousand_separator' );
		$data['decimal_separator'] = get_option( 'lifterlms_decimal_separator' );
		$data['decimals'] = get_option( 'lifterlms_decimals' );
		$data['trim_zero_decimals'] = get_option( 'lifterlms_trim_zero_decimals', 'no' );

		$data['recurring_payments'] = ( LLMS_Site::get_feature( 'recurring_payments' ) ) ? 'yes' : 'no';

		$data['email_from_address'] = get_option( 'lifterlms_email_from_address' );
		$data['email_from_name'] = get_option( 'lifterlms_email_from_name' );
		$data['email_footer_text'] = get_option( 'lifterlms_email_footer_text' );
		$data['email_header_image'] = get_option( 'lifterlms_email_header_image' );
		$data['cert_bg_width'] = get_option( 'lifterlms_certificate_bg_img_width' );
		$data['cert_bg_height'] = get_option( 'lifterlms_certificate_bg_img_height' );
		$data['cert_legacy_compat'] = get_option( 'lifterlms_certificate_legacy_image_size' );

		return $data;

	}

	/**
	 * Get number of orders per order status
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_order_counts() {

		$data = array();

		$orders = wp_count_posts( 'llms_order' );

		foreach ( llms_get_order_statuses() as $status => $name ) {

			$data[ $status ] = absint( $orders->{$status} );

		}

		return $data;

	}

	/**
	 * Get an option that should return a page ID
	 * and return the page name and ID as a formatted string
	 * @param    string     $option  option name in the wp_options table
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_page_data( $option ) {
		$id = get_option( $option );
		if ( absint( $id ) ) {
			return sprintf( '%1$s (#%2$d) [%3$s]', get_the_title( $id ), $id, get_permalink( $id ) );
		}
		return 'Not Set'; // don't translate this or you won't be able to read it smartypants...
	}

	/**
	 * get an array of plugin data, sorted into two arrays (active and inactive)
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_plugin_data() {

		// ensure we have our plugin function
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		$active = array();
		$inactive = array();

		foreach ( get_plugins() as $path => $data ) {

			if ( is_plugin_active( $path ) ) {
				$active[ $path ] = $data;
			} else {
				$inactive[ $path ] = $data;
			}
		}

		return array(
			'active' => $active,
			'inactive' => $inactive,
		);

	}

	/**
	 * Retrieve the number of published posts for various LLMS post types
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_post_type_counts() {

		$data = array();

		$posts = array(
			'course',
			'section',
			'lesson',
			'llms_quiz',
			'llms_question',
			'llms_review',

			'llms_membership',

			'llms_access_plan',
			'llms_coupon',
			'llms_voucher',

			'llms_engagement',
			'llms_achievement',
			'llms_certificate',
			'llms_email',
		);

		foreach ( $posts as $post_type ) {
			$count = wp_count_posts( $post_type );
			$data[ str_replace( 'llms_', '', $post_type ) ] = absint( $count->publish );
		}

		return $data;

	}

	/**
	 * Get PHP & Server Data
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_server_data() {

		global $wpdb;

		$data = array();

		if ( function_exists( 'ini_get' ) ) {
			$data['php_max_input_vars'] = ini_get( 'max_input_vars' );
			$data['php_memory_limit'] = ini_get( 'memory_limit' );
			$data['php_post_max_size'] = ini_get( 'post_max_size' );
			$data['php_time_limt'] = ini_get( 'max_execution_time' );
			$data['php_suhosin'] = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
		}

		$data['mysql_version'] = $wpdb->db_version();

		$data['php_curl'] = function_exists( 'curl_init' ) ? 'Yes' : 'No';
		$data['php_default_timezone'] = date_default_timezone_get();
		$data['php_fsockopen'] = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
		$data['php_max_upload_size'] = size_format( wp_max_upload_size() );
		$data['php_soap'] = class_exists( 'SoapClient' ) ? 'Yes' : 'No';

		if ( function_exists( 'phpversion' ) ) {
			$data['php_version'] = phpversion();
		}

		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$data['software'] = $_SERVER['SERVER_SOFTWARE'];
		}

		$data['wp_memory_limit'] = WP_MEMORY_LIMIT;

		ksort( $data );

		return $data;
	}

	/**
	 * Retrieve information about template overrides
	 * @return   array
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	private static function get_templates_data() {

		$path = LLMS()->plugin_path() . '/templates/';

		$templates = array_merge( glob( $path . '*.php' ), glob( $path . '**/*.php' ) );

		$overrides = array();

		foreach ( $templates as $file ) {

			$name = str_replace( $path, '', $file );
			$found = llms_get_template_override( $name );
			if ( $found ) {
				$overrides[] = array(
					'core_version' => self::get_file_version( $file ),
					'location' => $found,
					'version' => self::get_file_version( $found . $name ),
					'template' => $name,
				);
			}
		}

		return $overrides;

	}

	/**
	 * Get an array of theme data
	 * @return   array
	 * @since    3.0.0
	 * @version  3.11.2
	 */
	private static function get_theme_data() {

		$data = array();
		// @codingStandardsIgnoreStart
		$theme_data = wp_get_theme();
		$data['name'] = $theme_data->get( 'Name' );
		$data['version'] = $theme_data->get( 'Version' );
		$data['themeuri'] = $theme_data->get( 'ThemeURI' );
		$data['authoruri'] = $theme_data->get( 'AuthorURI' );
		$data['template'] = $theme_data->get( 'Template' );
		$data['child_theme'] = is_child_theme() ? 'Yes' : 'No';
		$data['llms_support'] = ( ! current_theme_supports( 'lifterlms' ) ) ? 'No' : 'Yes';
		// @codingStandardsIgnoreEnd

		return $data;

	}

	/**
	 * Det the number of users and users by role registered on the site
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private static function get_user_counts() {

		$data = array();

		$users = count_users();

		$data = $users['avail_roles'];
		$data['total'] = $users['total_users'];

		return $data;

	}

	/**
	 * Get some WP core settings and info
	 * @return   array
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	private static function get_wp_data() {

		$data = array();

		$data['home_url'] = get_home_url();
		$data['site_url'] = get_site_url();
		$data['login_url'] = wp_login_url();
		$data['version'] = get_bloginfo( 'version' );
		$data['debug_mode'] = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No';
		$data['debug_log'] = ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? 'Yes' : 'No';
		$data['debug_display'] = ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) ? 'Yes' : 'No';
		$data['locale'] = get_locale();
		$data['multisite'] = is_multisite() ? 'Yes' : 'No';
		$data['page_for_posts'] = self::get_page_data( 'page_for_posts' );
		$data['page_on_front'] = self::get_page_data( 'page_on_front' );
		$data['permalink_structure'] = get_option( 'permalink_structure' );
		$data['show_on_front'] = get_option( 'show_on_front' );
		$data['wp_cron'] = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? 'Yes' : 'No';

		return $data;

	}

}
