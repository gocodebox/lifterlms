<?php
/**
 * LLMS_Install class file
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Install LifterLMS
 *
 * Creates required pages, cronjobs, options, tables, and more.
 *
 * Additionally handles running database updates and migrations required with plugin updates.
 *
 * @since 1.0.0
 * @since 4.0.0 Added db update functions for session manager library cleanup.
 * @since 4.15.0 Added db update functions for orphan access plans cleanup.
 * @since 5.2.0 Removed private class property $db_updates.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Install::db_updates()` method
 *              - `LLMS_Install::update_notice()` method
 */
class LLMS_Install {

	/**
	 * Instances of the bg updater.
	 *
	 * @var LLMS_Background_Updater
	 */
	public static $background_updater;

	/**
	 * Initialize the install class
	 *
	 * Hooks all actions.
	 *
	 * @since 3.0.0
	 * @since 3.4.3 Unknown.
	 *
	 * @return void
	 */
	public static function init() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once 'admin/llms.functions.admin.php';

		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 4 );
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'update_actions' ) );
		add_action( 'admin_init', array( __CLASS__, 'wizard_redirect' ) );
	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'lifterlms_current_version' ) !== llms()->version ) {
			self::install();
			do_action( 'lifterlms_updated' );
		}
	}

	/**
	 * Create LifterLMS cron jobs
	 *
	 * @since 1.0.0
	 * @since 3.28.0 Remove unused cronjob `lifterlms_cleanup_sessions`.
	 * @since 4.0.0 Add expired session cleanup.
	 * @since 4.5.0 Add log backup cron.
	 *
	 * @return void
	 */
	public static function create_cron_jobs() {

		$crons = array(
			array(
				/**
				 * Filter the recurrence interval at which files in the LifterLMS logs are scanned and backed up.
				 *
				 * @since 4.5.0
				 *
				 * @link https://developer.wordpress.org/reference/functions/wp_get_schedules/
				 *
				 * @param string $recurrence Cron job recurrence interval. Must be valid interval as retrieved from `wp_get_schedules()`. Default is "daily".
				 */
				'hook'     => 'llms_backup_logs',
				'interval' => apply_filters( 'llms_backup_logs_interval', 'daily' ),
			),
			array(
				/**
				 * Filter the recurrence interval at which files in the LifterLMS tmp directory are cleaned.
				 *
				 * @since 4.5.0
				 *
				 * @link https://developer.wordpress.org/reference/functions/wp_get_schedules/
				 *
				 * @param string $recurrence Cron job recurrence interval. Must be valid interval as retrieved from `wp_get_schedules()`. Default is "daily".
				 */
				'hook'     => 'llms_cleanup_tmp',
				'interval' => apply_filters( 'llms_cleanup_tmp_interval', 'daily' ),
			),
			array(
				'hook'     => 'llms_send_tracking_data',
				/**
				 * Filter the recurrence interval at which tracking data is gathered and sent.
				 *
				 * @since Unknown
				 *
				 * @link https://developer.wordpress.org/reference/functions/wp_get_schedules/
				 *
				 * @param string $recurrence Cron job recurrence interval. Must be valid interval as retrieved from `wp_get_schedules()`. Default is "daily".
				 */
				'interval' => apply_filters( 'llms_tracker_schedule_interval', 'daily' ),
			),
			array(
				'hook'     => 'llms_delete_expired_session_data',
				/**
				 * Filter the recurrence interval at which expired session are removed from the database.
				 *
				 * @since 4.0.0
				 *
				 * @link https://developer.wordpress.org/reference/functions/wp_get_schedules/
				 *
				 * @param string $recurrence Cron job recurrence interval. Must be valid interval as retrieved from `wp_get_schedules()`. Default is "hourly".
				 */
				'interval' => apply_filters( 'llms_delete_expired_session_data_recurrence', 'hourly' ),
			),
		);

		foreach ( $crons as $data ) {
			if ( ! wp_next_scheduled( $data['hook'] ) ) {
				wp_schedule_event( time(), $data['interval'], $data['hook'] );
			}
		}
	}

	/**
	 * Create basic course difficulties on installation
	 *
	 * @since 3.0.4
	 *
	 * @return void
	 */
	public static function create_difficulties() {

		foreach ( self::get_difficulties() as $name ) {

			// Only create if it doesn't already exist.
			if ( ! get_term_by( 'name', $name, 'course_difficulty' ) ) {

				wp_insert_term( $name, 'course_difficulty' );

			}
		}
	}

	/**
	 * Create files needed by LifterLMS
	 *
	 * @since 3.0.0
	 * @since 3.15.0 Unknown.
	 *
	 * @return void
	 */
	public static function create_files() {
		$upload_dir = wp_upload_dir();
		$files      = array(
			array(
				'base'    => LLMS_LOG_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => LLMS_LOG_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => LLMS_TMP_DIR,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => LLMS_TMP_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
			}
		}
	}

	/**
	 * Store all default options in the DB.
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @since 4.0.0 Include abstract table file.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public static function create_options() {

		$settings = LLMS_Admin_Settings::get_settings_tabs();

		foreach ( $settings as $section ) {
			foreach ( $section->get_settings( true ) as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
	}

	/**
	 * Get array of essential starter pages.
	 *
	 * @since 7.3.0
	 *
	 * @return array
	 */
	public static function get_pages() {
		/**
		 * Filters the essential starter pages.
		 *
		 * These are the pages that are going to be created when installing LifterLMS.
		 * All these pages, as long as their `docs_url`, `description` and `wizard_title`
		 * fields are defined, are going to be shown in the Setup Wizard.
		 *
		 * @since 7.3.0
		 *
		 * @param array $pages A multidimensional array defining the essential starter pages.
		 */
		return apply_filters(
			'llms_install_get_pages',
			array(
				array(
					'content'      => '',
					'option'       => 'lifterlms_shop_page_id',
					'slug'         => 'courses',
					'title'        => __( 'Course Catalog', 'lifterlms' ),
					'wizard_title' => __( 'Course Catalog', 'lifterlms' ),
					'description'  => __( 'This page is where your visitors will find a list of all your available courses.', 'lifterlms' ),
					'docs_url'     => 'https://lifterlms.com/docs/course-catalog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Course%20Catalog',
				),
				array(
					'content'      => '',
					'option'       => 'lifterlms_memberships_page_id',
					'slug'         => 'memberships',
					'title'        => __( 'Membership Catalog', 'lifterlms' ),
					'wizard_title' => __( 'Membership Catalog', 'lifterlms' ),
					'description'  => __( 'This page is where your visitors will find a list of all your available memberships.', 'lifterlms' ),
					'docs_url'     => 'https://lifterlms.com/docs/membership-catalog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Membership%20Catalog',
				),
				array(
					'content'      => '[lifterlms_checkout]',
					'option'       => 'lifterlms_checkout_page_id',
					'slug'         => 'purchase',
					'title'        => __( 'Purchase', 'lifterlms' ),
					'wizard_title' => __( 'Checkout', 'lifterlms' ),
					'description'  => __( 'This is the page where visitors will be directed in order to pay for courses and memberships.', 'lifterlms' ),
					'docs_url'     => 'https://lifterlms.com/docs/checkout-page/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Checkout%20Page',
				),
				array(
					'content'      => '[lifterlms_my_account]',
					'option'       => 'lifterlms_myaccount_page_id',
					'slug'         => 'dashboard',
					'title'        => __( 'Dashboard', 'lifterlms' ),
					'wizard_title' => __( 'Student Dashboard', 'lifterlms' ),
					'description'  => __( 'Page where students can view and manage their current enrollments, earned certificates and achievements, account information, and purchase history.', 'lifterlms' ),
					'docs_url'     => 'https://lifterlms.com/docs/student-dashboard/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Student%20Dashboard',
				),
			)
		);
	}

	/**
	 * Create essential starter pages.
	 *
	 * @since 1.0.0
	 * @since 3.24.0 Unknown.
	 * @since 7.3.0 Using `$this->get_pages()` method now.
	 *
	 * @return boolean False on error, true on success.
	 */
	public static function create_pages() {
		/**
		 * Filters the essential pages to be installed.
		 *
		 * @since 3.0.0
		 *
		 * {@see `llms_install_get_pages} filter hook.
		 *
		 * @param array $pages A multidimensional array defining the essential starter pages to be installed.
		 */
		$pages = apply_filters( 'llms_install_create_pages', self::get_pages() );
		foreach ( $pages as $page ) {
			if ( ! llms_create_page( $page['slug'], $page['title'], $page['content'], $page['option'] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Create LifterLMS DB tables
	 *
	 * @since 1.0.0
	 * @since 3.3.1 Unknown.
	 *
	 * @return void
	 */
	public static function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Create default LifterLMS Product & Access Plan Visibility Options
	 *
	 * @since 3.6.0
	 * @since 3.8.0 Unknown.
	 *
	 * @return void
	 */
	public static function create_visibilities() {
		foreach ( array_keys( llms_get_access_plan_visibility_options() ) as $term ) {
			if ( ! get_term_by( 'name', $term, 'llms_access_plan_visibility' ) ) {
				wp_insert_term( $term, 'llms_access_plan_visibility' );
			}
		}
		foreach ( array_keys( llms_get_product_visibility_options() ) as $term ) {
			if ( ! get_term_by( 'name', $term, 'llms_product_visibility' ) ) {
				wp_insert_term( $term, 'llms_product_visibility' );
			}
		}
	}

	/**
	 * Dispatches the bg updater
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public static function dispatch_db_updates() {
		self::$background_updater->save()->dispatch();
	}

	/**
	 * Retrieve the default difficulty terms that should be created on a fresh install
	 *
	 * @since 3.3.1
	 *
	 * @return array
	 */
	public static function get_difficulties() {
		return apply_filters(
			'llms_install_create_difficulties',
			array(
				_x( 'Beginner', 'course difficulty name', 'lifterlms' ),
				_x( 'Intermediate', 'course difficulty name', 'lifterlms' ),
				_x( 'Advanced', 'course difficulty name', 'lifterlms' ),
			)
		);
	}

	/**
	 * Get a string of table data that can be passed to dbDelta() to install LLMS tables.
	 *
	 * @since 3.0.0
	 * @since 3.16.9 Unknown
	 * @since 3.16.9 Unknown
	 * @since 3.34.0 Added `llms_install_get_schema` filter to method return.
	 * @since 3.36.0 Added `wp_lifterlms_events` table.
	 * @since 4.0.0 Added `wp_lifterlms_sessions` table.
	 * @since 4.5.0 Added `wp_lifterlms_events_open_sessions` table.
	 * @since 7.8.0 Added column `can_be_resumed` and `current_question_id` to the quiz attempt table.
	 *
	 * @return string
	 */
	private static function get_schema() {

		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$tables = "
CREATE TABLE `{$wpdb->prefix}lifterlms_user_postmeta` (
  meta_id bigint(20) NOT NULL auto_increment,
  user_id bigint(20) NOT NULL,
  post_id bigint(20) NOT NULL,
  meta_key varchar(255) NULL,
  meta_value longtext NULL,
  updated_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`meta_id`),
  KEY user_id (`user_id`),
  KEY post_id (`post_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_quiz_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) DEFAULT NULL,
  `quiz_id` bigint(20) DEFAULT NULL,
  `lesson_id` bigint(20) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` varchar(15) DEFAULT '',
  `attempt` bigint(20) DEFAULT NULL,
  `grade` float DEFAULT NULL,
  `can_be_resumed` tinyint(1) DEFAULT '0',
  `current_question_id` bigint(20) DEFAULT NULL,
  `questions` longtext,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `quiz_id` (`quiz_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_product_to_voucher` (
  `product_id` bigint(20) NOT NULL,
  `voucher_id` bigint(20) NOT NULL,
  KEY `product_id` (`product_id`),
  KEY `voucher_id` (`voucher_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_voucher_code_redemptions` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `code_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `redemption_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code_id` (`code_id`),
  KEY `user_id` (`user_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_vouchers_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint(20) NOT NULL,
  `code` varchar(20) NOT NULL DEFAULT '',
  `redemption_count` bigint(20) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `voucher_id` (`voucher_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` varchar(11) DEFAULT '0',
  `type` varchar(75) DEFAULT NULL,
  `subscriber` varchar(255) DEFAULT NULL,
  `trigger_id` varchar(75) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `post_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `subscriber` (`subscriber`(191))
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `actor_id` bigint(20) DEFAULT NULL,
  `object_type` varchar(55) DEFAULT NULL,
  `object_id` bigint(20) DEFAULT NULL,
  `event_type` varchar(55) DEFAULT NULL,
  `event_action` varchar(55) DEFAULT NULL,
  `meta` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY actor_id (`actor_id`),
  KEY object_id (`object_id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_events_open_sessions` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`event_id` bigint(20) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_key` char(32) NOT NULL,
  `data` longtext NOT NULL,
  `expires` BIGINT unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_key` (`session_key`)
) $collate;
";

		/**
		 * Filter the database table schema.
		 *
		 * @since 3.34.0
		 *
		 * @param string $tables  A semi-colon (`;`) separated list of database table creating commands.
		 * @param string $collate Database collation statement.
		 */
		return apply_filters( 'llms_install_get_schema', $tables, $collate );
	}

	/**
	 * Initializes the bg updater class
	 *
	 * @since 3.4.3
	 * @since 3.6.0 Unknown.
	 * @since 5.2.0 Use `LLMS_PLUGIN_DIR` to include required class file.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public static function init_background_updater() {

		self::$background_updater = new LLMS_Background_Updater();
	}

	/**
	 * Core install function
	 *
	 * @since 1.0.0
	 * @since 3.13.0 Unknown.
	 * @since 5.0.0 Install forms.
	 * @since 5.2.0 Moved DB update logic to LLMS_Install::run_db_updates().
	 *
	 * @return void
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		/**
		 * Action run immediately prior to LLMS_Install::install() routine.
		 *
		 * @since Unknown
		 */
		do_action( 'lifterlms_before_install' );

		LLMS_Site::set_lock_url();
		self::create_tables();
		self::create_options();
		LLMS_Roles::install();

		self::verify_permalinks();

		LLMS_Post_Types::register_post_types();
		LLMS_Post_Types::register_taxonomies();

		llms()->query->init_query_vars();
		llms()->query->add_endpoints();

		self::create_cron_jobs();
		self::create_files();
		self::create_difficulties();
		self::create_visibilities();

		LLMS_Forms::instance()->install();

		$version    = get_option( 'lifterlms_current_version', null );
		$db_version = get_option( 'lifterlms_db_version', $version );

		// Trigger first time run redirect.
		if ( ( is_null( $version ) || is_null( $db_version ) ) || 'no' === get_option( 'lifterlms_first_time_setup', 'no' ) ) {
			update_option( '_llms_first_time_setup_redirect', 'yes', false );
		}

		self::run_db_updates( $db_version );
		self::update_llms_version();

		flush_rewrite_rules();

		/**
		 * Action run immediately after the LLMS_Install::install() routine has completed.
		 *
		 * @since Unknown
		 */
		do_action( 'lifterlms_after_install' );
	}

	/**
	 * Retrieve permalinks structure to verify if they are set, and any new defaults are saved
	 *
	 * @since 7.6.0
	 *
	 * @return void
	 */
	public static function verify_permalinks() {
		if ( ! get_option( 'lifterlms_permalinks' ) ) {
			llms_switch_to_site_locale();

			// Retrieve the permalink structure, which will also save the default structure if it's not set.
			llms_get_permalink_structure();

			llms_restore_locale();
		}
	}

	/**
	 * Remove the difficulties created by the `create_difficulties()` function
	 *
	 * Used during uninstall when "remove_all_data" is set.
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public static function remove_difficulties() {

		foreach ( self::get_difficulties() as $name ) {

			$term = get_term_by( 'name', $name, 'course_difficulty' );
			if ( $term ) {

				wp_delete_term( $term->term_id, 'course_difficulty' );

			}
		}
	}

	/**
	 * Run database updates
	 *
	 * If no updates are required for the current version, records the DB version as the current
	 * plugin version.
	 *
	 * @since 5.2.0
	 *
	 * @param string $db_version The DB version to upgrade from.
	 * @return void
	 */
	private static function run_db_updates( $db_version ) {

		if ( ! is_null( $db_version ) ) {

			// Load the upgrader.
			$upgrader = new LLMS_DB_Upgrader( $db_version );
			if ( $upgrader->update() ) {
				return;
			}
		}

		self::update_db_version();
	}

	/**
	 * Handle form submission of update related actions
	 *
	 * @since 3.4.3
	 * @since 5.2.0 Use `LLMS_DB_Upgrader` and remove the "force upgrade" action handler.
	 *
	 * @return void
	 */
	public static function update_actions() {

		if ( empty( $_GET['llms-db-update'] ) ) {
			return;
		}

		if ( ! llms_verify_nonce( 'llms-db-update', 'do_db_updates', 'GET' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform the requested action.', 'lifterlms' ) );
		}

		LLMS_Admin_Notices::delete_notice( 'bg-db-update' );

		$upgrader = new LLMS_DB_Upgrader( get_option( 'lifterlms_db_version' ) );
		$upgrader->enqueue_updates();
		llms_redirect_and_exit( remove_query_arg( array( 'llms-db-update' ) ) );
	}

	/**
	 * Update the LifterLMS DB record to the latest version
	 *
	 * @since 3.0.0
	 * @since 3.4.3 Unknown.
	 *
	 * @param string $version Version number.
	 * @return void
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'lifterlms_db_version' );
		add_option( 'lifterlms_db_version', is_null( $version ) ? llms()->version : $version );
	}

	/**
	 * Update the LifterLMS version record to the latest version
	 *
	 * @since 3.0.0
	 * @since 3.4.3 Unknown.
	 *
	 * @param string $version Version number.
	 * @return void
	 */
	public static function update_llms_version( $version = null ) {
		delete_option( 'lifterlms_current_version' );
		add_option( 'lifterlms_current_version', is_null( $version ) ? llms()->version : $version );
	}

	/**
	 * Redirects users to the setup wizard
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 * @since 5.2.0 Use strict array comparison and `wp_safe_redirect()` in favor of `wp_redirect()`.
	 *
	 * @return void
	 */
	public static function wizard_redirect() {

		if ( 'yes' === get_option( '_llms_first_time_setup_redirect', 'no' ) ) {

			update_option( '_llms_first_time_setup_redirect', 'no' );

			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'llms-setup' ), true ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || apply_filters( 'llms_prevent_automatic_wizard_redirect', false ) ) {
				return;
			}

			if ( current_user_can( 'install_plugins' ) ) {

				wp_safe_redirect( admin_url() . '?page=llms-setup' );
				exit;

			}
		}
	}

	/**
	 * Get the WP User ID of the first available user who can 'manage_options'
	 *
	 * @since 5.0.0
	 *
	 * @return int Returns the ID of the current user if they can 'manage_options'.
	 *             Otherwise returns the ID of the first Administrator if they can 'manage_options'.
	 *             Returns 0 if the first Administrator cannot 'manage_options' or the current site has no Administrators.
	 */
	public static function get_can_install_user_id() {

		$capability = 'manage_options';

		if ( current_user_can( $capability ) ) {
			return get_current_user_id();
		}

		// Get the first user with administrator role.
		// Here, for simplicity, we're assuming the administrator's role capabilities are the original ones.
		$first_admin_user = get_users(
			array(
				'role'    => 'Administrator',
				'number'  => 1,
				'orderby' => 'ID',
			)
		);

		// Return 0 if the first Administrator cannot 'manage_options' or the current site has no Administrators.
		return ! empty( $first_admin_user ) && $first_admin_user[0]->has_cap( $capability ) ? $first_admin_user[0]->ID : 0;
	}
}

LLMS_Install::init();
