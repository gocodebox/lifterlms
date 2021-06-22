<?php
/**
 * Plugin installation
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Install
 *
 * @since 1.0.0
 * @since 3.28.0 Unknown.
 * @since 3.34.0 Added filter to the return of the get_schema() method.
 * @since 3.36.0 Added `wp_lifterlms_events` table.
 * @since 4.0.0 Added `wp_lifterlms_sessions` table.
 *              Added session cleanup cron.
 *              Added db update functions for session manager library cleanup.
 * @since 4.15.0 Added db update functions for orphan access plans cleanup.
 * @since 5.0.0 Install forms during installation.
 */
class LLMS_Install {

	public static $background_updater;

	/**
	 * Database update functions
	 *
	 * Array key is the database version and array values are
	 * arrays of callback functions for the update.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'3.0.0'  => array(
			'llms_update_300_create_access_plans',
			'llms_update_300_del_deprecated_options',
			'llms_update_300_migrate_account_field_options',
			'llms_update_300_migrate_coupon_data',
			'llms_update_300_migrate_course_postmeta',
			'llms_update_300_migrate_lesson_postmeta',
			'llms_update_300_migrate_order_data',
			'llms_update_300_migrate_email_postmeta',
			'llms_update_300_update_orders',
			'llms_update_300_update_db_version',
		),
		'3.0.3'  => array(
			'llms_update_303_update_students_role',
			'llms_update_303_update_db_version',
		),
		'3.4.3'  => array(
			'llms_update_343_update_relationships',
			'llms_update_343_update_db_version',
		),
		'3.6.0'  => array(
			'llms_update_360_set_product_visibility',
			'llms_update_360_update_db_version',
		),
		'3.8.0'  => array(
			'llms_update_380_set_access_plan_visibility',
			'llms_update_380_update_db_version',
		),
		'3.12.0' => array(
			'llms_update_3120_update_order_end_dates',
			'llms_update_3120_update_integration_options',
			'llms_update_3120_update_db_version',
		),
		'3.13.0' => array(
			'llms_update_3130_create_default_instructors',
			'llms_update_3130_builder_notice',
			'llms_update_3130_update_db_version',
		),
		'3.16.0' => array(
			'llms_update_3160_update_quiz_settings',
			'llms_update_3160_lesson_to_quiz_relationships_migration',
			'llms_update_3160_attempt_migration',
			'llms_update_3160_ensure_no_dupe_question_rels',
			'llms_update_3160_ensure_no_lesson_dupe_rels',
			'llms_update_3160_update_question_data',
			'llms_update_3160_update_attempt_question_data',
			'llms_update_3160_update_quiz_to_lesson_rels',
			'llms_update_3160_builder_notice',
			'llms_update_3160_update_db_version',
		),
		'3.28.0' => array(
			'llms_update_3280_clear_session_cleanup_cron',
			'llms_update_3280_update_db_version',
		),
		'4.0.0'  => array(
			'llms_update_400_remove_session_options',
			'llms_update_400_clear_session_cron',
			'llms_update_400_update_db_version',
		),
		'4.5.0'  => array(
			'llms_update_450_migrate_events_open_sessions',
			'llms_update_450_update_db_version',
		),
		'4.15.0' => array(
			'llms_update_4150_remove_orphan_access_plans',
			'llms_update_4150_update_db_version',
		),
		'5.0.0'  => array(
			'llms_update_500_legacy_options_autoload_off',
			'llms_update_500_update_db_version',
			'llms_update_500_add_admin_notice',
		),
	);

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
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'lifterlms_current_version' ) !== LLMS()->version ) {
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
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' );
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}

	}

	/**
	 * Store all default options in the DB
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @since 4.0.0 Include abstract table file.
	 *
	 * @return void
	 */
	public static function create_options() {

		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.admin.table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.settings.php';

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
	 * Create essential starter pages
	 *
	 * @since 1.0.0
	 * @since 3.24.0 Unknown.
	 *
	 * @return boolean False on error, true on success.
	 */
	public static function create_pages() {
		$pages = apply_filters(
			'llms_install_create_pages',
			array(
				array(
					'content' => '',
					'option'  => 'lifterlms_shop_page_id',
					'slug'    => 'courses',
					'title'   => __( 'Course Catalog', 'lifterlms' ),
				),
				array(
					'content' => '',
					'option'  => 'lifterlms_memberships_page_id',
					'slug'    => 'memberships',
					'title'   => __( 'Membership Catalog', 'lifterlms' ),
				),
				array(
					'content' => '[lifterlms_checkout]',
					'option'  => 'lifterlms_checkout_page_id',
					'slug'    => 'purchase',
					'title'   => __( 'Purchase', 'lifterlms' ),
				),
				array(
					'content' => '[lifterlms_my_account]',
					'option'  => 'lifterlms_myaccount_page_id',
					'slug'    => 'dashboard',
					'title'   => __( 'Dashboard', 'lifterlms' ),
				),
			)
		);
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
	 * Queue all required db updates into the bg update queue
	 *
	 * @since 3.0.0
	 * @since 3.4.3 Unknown.
	 *
	 * @return void
	 */
	public static function db_updates() {

		$current_db_version = get_option( 'lifterlms_db_version' );
		$queued             = false;

		foreach ( self::$db_updates as $version => $callbacks ) {

			if ( version_compare( $current_db_version, $version, '<' ) ) {

				foreach ( $callbacks as $callback ) {

					self::$background_updater->log( sprintf( 'Queuing %s - %s', $version, $callback ) );
					self::$background_updater->push_to_queue( $callback );
					$queued = true;

				}
			}
		}

		if ( $queued ) {
			add_action( 'shutdown', array( __CLASS__, 'dispatch_db_updates' ) );
		}

	}

	/**
	 * Dispatches the bg updater
	 *
	 * Prevents small database updates from displaying the "updating" admin notice
	 * instead of the "completed" notice.
	 * These small updates would finish on a second thread faster than the main
	 * thread and the wrong notice would be displayed.
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
	 * Get a string of table data that can be passed to dbDelta() to install LLMS tables
	 *
	 * @since 3.0.0
	 * @since 3.16.9 Unknown
	 * @since 3.16.9 Unknown
	 * @since 3.34.0 Added `llms_install_get_schema` filter to method return.
	 * @since 3.36.0 Added `wp_lifterlms_events` table.
	 * @since 4.0.0 Added `wp_lifterlms_sessions` table.
	 * @since 4.5.0 Added `wp_lifterlms_events_open_sessions` table.
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
	 *
	 * @return void
	 */
	public static function init_background_updater() {

		include_once dirname( __FILE__ ) . '/class.llms.background.updater.php';
		self::$background_updater = new LLMS_Background_Updater();

	}

	/**
	 * Core install function
	 *
	 * @since 1.0.0
	 * @since 3.13.0 Unknown.
	 * @since 5.0.0 Install forms.
	 *
	 * @return void
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		do_action( 'lifterlms_before_install' );

		LLMS_Site::set_lock_url();
		self::create_tables();
		self::create_options();
		LLMS_Roles::install();

		LLMS_Post_Types::register_post_types();
		LLMS_Post_Types::register_taxonomies();

		LLMS()->query->init_query_vars();
		LLMS()->query->add_endpoints();

		self::create_cron_jobs();
		self::create_files();
		self::create_difficulties();
		self::create_visibilities();

		LLMS_Forms::instance()->install();

		$version    = get_option( 'lifterlms_current_version', null );
		$db_version = get_option( 'lifterlms_db_version', $version );

		// Trigger first time run redirect.
		if ( ( is_null( $version ) || is_null( $db_version ) ) || 'no' === get_option( 'lifterlms_first_time_setup', 'no' ) ) {

			set_transient( '_llms_first_time_setup_redirect', 'yes', 30 );

		}

		// Show the update notice since there are db updates to run.
		$versions = array_keys( self::$db_updates );
		if ( ! is_null( $db_version ) && version_compare( $db_version, end( $versions ), '<' ) ) {

			self::update_notice();

		} else {

			self::update_db_version();

		}

		self::update_llms_version();

		flush_rewrite_rules();

		do_action( 'lifterlms_after_install' );

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
	 * Handle form submission of update related actions
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public static function update_actions() {

		// Start the updater if the run button was clicked.
		if ( ! empty( $_GET['llms-db-update'] ) ) {

			if ( ! llms_verify_nonce( 'llms-db-update', 'do_db_updates', 'GET' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}

			// Prevent page refreshes from triggering a second queue / batch.
			if ( ! self::$background_updater->is_updating() ) {
				self::db_updates();
			}

			self::update_notice();

		}

		// Force update triggered.
		if ( ! empty( $_GET['llms-force-db-update'] ) ) {

			if ( ! llms_verify_nonce( 'llms-force-db-update', 'force_db_updates', 'GET' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}

			do_action( 'wp_llms_bg_updater_cron' );

			wp_redirect( admin_url( 'admin.php?page=llms-settings' ) );

			exit;

		}

	}

	/**
	 * Stores an admin notice for the current state of the background updater
	 *
	 * @since 3.4.3
	 *
	 * @return void
	 */
	public static function update_notice() {

		include_once 'admin/class.llms.admin.notices.php';

		if ( LLMS_Admin_Notices::has_notice( 'bg-db-update' ) ) {
			LLMS_Admin_Notices::delete_notice( 'bg-db-update' );
		}

		if ( version_compare( get_option( 'lifterlms_db_version' ), LLMS()->version, '<' ) ) {

			if ( ! self::$background_updater ) {
				self::init_background_updater();
			}

			// Update is running or button was just pressed.
			if ( self::$background_updater->is_updating() || ! empty( $_GET['llms-db-update'] ) ) {

				LLMS_Admin_Notices::add_notice(
					'bg-db-update',
					array(
						'dismissible' => false,
						'template'    => 'admin/notices/db-updating.php',
					)
				);

			} else {

				LLMS_Admin_Notices::add_notice(
					'bg-db-update',
					array(
						'dismissible' => false,
						'template'    => 'admin/notices/db-update.php',
					)
				);

			}
		} else {

			LLMS_Admin_Notices::add_notice(
				'bg-db-update',
				__( 'The LifterLMS database update is complete.', 'lifterlms' ),
				array(
					'dismissible'      => true,
					'dismiss_for_days' => 0,
				)
			);

		}

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
		add_option( 'lifterlms_db_version', is_null( $version ) ? LLMS()->version : $version );
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
		add_option( 'lifterlms_current_version', is_null( $version ) ? LLMS()->version : $version );
	}

	/**
	 * Redirects users to the setup wizard
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return void
	 */
	public static function wizard_redirect() {

		if ( get_transient( '_llms_first_time_setup_redirect' ) ) {

			delete_transient( '_llms_first_time_setup_redirect' );

			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'llms-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || apply_filters( 'llms_prevent_automatic_wizard_redirect', false ) ) {
				return;
			}

			if ( current_user_can( 'install_plugins' ) ) {

				wp_redirect( admin_url() . '?page=llms-setup' );
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
