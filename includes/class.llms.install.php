<?php
/**
 * Plugin installation
 * @since   1.0.0
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// @todo why is this here?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class LLMS_Install {

	/**
	 * Array of databse updates to be run
	 * @var  array
	 */
	private static $db_updates = array(
		'3.0.0' => 'updates/lifterlms-update-3.0.0.php',
		'3.0.3' => 'updates/lifterlms-update-3.0.3.php',
	);

	/**
	 * Initialize the install class
	 * Hooks all actions
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'wizard_redirect' ) );
		add_action( 'init', array( __CLASS__, 'db_updates' ), 5 );

	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function check_version() {

		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'lifterlms_current_version' ) !== LLMS()->version ) {
			self::install();
			do_action( 'lifterlms_updated' );
		}

	}

	/**
	 * Create LifterLMS cron jobs
	 * @return  void
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public static function create_cron_jobs() {

		if ( ! wp_next_scheduled( 'lifterlms_cleanup_sessions' )) {
			wp_schedule_event( time(), 'twicedaily', 'lifterlms_cleanup_sessions' );
		}

		if ( ! wp_next_scheduled( 'llms_send_tracking_data' )) {
			wp_schedule_event( time(), apply_filters( 'llms_tracker_schedule_interval', 'daily' ), 'llms_send_tracking_data' );
		}

	}

	/**
	 * Create basic course difficulties on installation
	 * @return   void
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	public static function create_difficulties() {

		$difficulties = apply_filters( 'llms_install_create_difficulties', array(
			_x( 'Beginner', 'course difficulty name', 'lifterlms' ),
			_x( 'Intermediate', 'course difficulty name', 'lifterlms' ),
			_x( 'Advanced', 'course difficulty name', 'lifterlms' ),
		) );

		foreach ( $difficulties as $name ) {

			// only create if it doesn't already exist
			if ( ! get_term_by( 'name', $name, 'course_difficulty' ) ) {

				$id = wp_insert_term( $name, 'course_difficulty' );

			}

		}

	}

	/**
	 * Create files needed by LifterLMS
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function create_files() {

		$upload_dir      = wp_upload_dir();
		$files = array(
			array(
				'base' 		=> LLMS_LOG_DIR,
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all',
			),
			array(
				'base' 		=> LLMS_LOG_DIR,
				'file' 		=> 'index.html',
				'content' 	=> '',
			),
		);
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}

	}

	/**
	 * Store all default options in the DB
	 * @return  void
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public static function create_options() {

		include_once( 'admin/class.llms.admin.settings.php' );

		$settings = LLMS_Admin_Settings::get_settings_tabs();

		foreach ( $settings as $section ) {
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}

	}

	/**
	 * Create essential starter pages
	 * @return   boolean    false on error, true on success
	 * @since    1.0.0
	 * @version  3.0.0
	 */
	public static function create_pages() {
		$pages = apply_filters( 'llms_install_create_pages', array(
			array(
				'content' => '',
				'option' => 'lifterlms_shop_page_id',
				'slug' => 'courses',
				'title' => __( 'Course Catalog', 'lifterlms' ),
			),
			array(
				'content' => '',
				'option' => 'lifterlms_memberships_page_id',
				'slug' => 'memberships',
				'title' => __( 'Membership Catalog', 'lifterlms' ),
			),
			array(
				'content' => '[lifterlms_checkout]',
				'option' => 'lifterlms_checkout_page_id',
				'slug' => 'purchase',
				'title' => __( 'Purchase', 'lifterlms' ),
			),
			array(
				'content' => '[lifterlms_my_account]',
				'option' => 'lifterlms_myaccount_page_id',
				'slug' => 'my-courses',
				'title' => __( 'My Courses', 'lifterlms' ),
			),
		) );
		foreach ( $pages as $page ) {
			if ( ! llms_create_page( $page['slug'], $page['title'], $page['content'], $page['option'] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Create LifterLMS user roles
	 * @return void
	 * @since  1.0.0
	 * @version  3.0.0
	 */
	public static function create_roles() {

		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		add_role( 'student', __( 'Student', 'lifterlms' ),
			array(
				'read' => true,
			)
		);

	}

	/**
	 * Create LifterLMS DB tables
	 * @return  void
	 * @since   1.0.0
	 * @version 3.0.0
	 */
	public static function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );

	}

	/**
	 * Check available database updates and run them if db is less than the update version
	 * @return void
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public static function db_updates() {

		// start the updater if the run button was clicked
		if ( isset( $_GET['llms-db-update'] ) ) {
			if ( ! wp_verify_nonce( $_GET['llms-db-update'], 'do_db_updates' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'lifterlms' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'lifterlms' ) );
			}

			LLMS_Admin_Notices::delete_notice( 'db-update' );

			$do_update = 'yes';
			update_option( 'llms_doing_database_update', $do_update );

		} // get the current state of the updates
		else {

			$do_update = get_option( 'llms_doing_database_update', 'no' );

		}

		// if we're in the midst of an update keep going
		if ( 'yes' === $do_update ) {

			$current_db_version = get_option( 'lifterlms_db_version', 0 );

			$finished = true;

			include_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.update.php';

			foreach ( self::$db_updates as $version => $updater ) {

				if ( version_compare( $current_db_version, $version, '<' ) ) {

					$u = include_once $updater;
					$finished = false;

				}

			}

			// if there are no more updates to run output an admin notice
			if ( $finished ) {

				// this runs on init so this may not be available
				include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
				LLMS_Admin_Notices::add_notice( 'llms_bg_updates_complete', __( 'LifterLMS background update completed!', 'lifterlms' ), array(
					'dismissible' => true,
					'dismiss_for_days' => 0,
				) );

				do_action( 'lifterlms_background_updates_complete' );
				update_option( 'llms_doing_database_update', 'no' );

			}

		}

	}

	/**
	 * Get a string of table data that can be passed to dbDelta() to install LLMS tables
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
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
CREATE TABLE {$wpdb->prefix}lifterlms_user_postmeta (
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
";

		return $tables;

	}

	/**
	 * Core install function
	 * @return  void
	 * @since   1.0.0
	 * @version 3.0.4 - added difficulty creation
	 */
	public static function install() {

		do_action( 'lifterlms_before_install' );

		LLMS_Site::set_lock_url();
		self::create_options();
		self::create_tables();
		self::create_roles();

		LLMS_Post_Types::register_post_types();
		LLMS_Post_Types::register_taxonomies();

		LLMS()->query->init_query_vars();
		LLMS()->query->add_endpoints();

		self::create_cron_jobs();
		self::create_files();
		self::create_difficulties();

		$version = get_option( 'lifterlms_current_version', null );
		$db_version = get_option( 'lifterlms_db_version', null );

		// trigger first time run redirect
		if ( ( is_null( $version ) || is_null( $db_version ) ) || 'no' === get_option( 'lifterlms_first_time_setup', 'no' ) ) {

			set_transient( '_llms_first_time_setup_redirect', 'yes', 30 );

		}

		if ( 'no' === get_option( 'llms_doing_database_update', 'no' ) ) {

			if ( version_compare( $db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {

				// may not be available since this runs on init
				include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

				// if a notice already exists clear it out and add the most current one
				if ( LLMS_Admin_Notices::has_notice( 'db-update' ) ) {
					LLMS_Admin_Notices::delete_notice( 'db-update' );
				}

				LLMS_Admin_Notices::add_notice( 'db-update', array(
					'dismissible' => false,
					'template' => 'admin/notices/db-update.php',
				) );

			} else {

				self::update_db_version();

			}

		}

		self::update_llms_version();

		flush_rewrite_rules();

		do_action( 'lifterlms_after_install' );

	}

	/**
	 * Redirects users to the setup wizard
	 * @return   void
	 * @since    1.0.0
	 * @version  3.0.0
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
	 * Update the LifterLMS DB record to the latest version
	 * @param  string $version version number
	 * @return void
	 *
	 * @since  3.0.0
	 */
	public static function update_db_version( $version = null ) {
		update_option( 'lifterlms_db_version', is_null( $version ) ? LLMS()->version : $version );
	}

	/**
	 * Update the LifterLMS version record to the latest version
	 * @param  string $version version number
	 * @return void
	 *
	 * @since  3.0.0
	 */
	private static function update_llms_version( $version = null ) {
		update_option( 'lifterlms_current_version', is_null( $version ) ? LLMS()->version : $version );
	}

}

LLMS_Install::init();
