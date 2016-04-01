<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LLMS_Install Class
 */
class LLMS_Install {

	protected $min_wp_version = '3.5';
	public $current_wp_version;

	/**
	 * LLMS_Install Constructor.
	 *
	 * Adds install actions to init and admin init
	 */
	public function __construct() {

		$this->current_wp_version = get_bloginfo( 'version' );
		register_activation_hook( LLMS_PLUGIN_FILE, array( $this, 'install' ) );
		add_action( 'admin_init', array( $this, 'first_time_setup' ) );
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );
		add_action( 'init', array( $this, 'install_settings' ) );
		add_action( 'admin_init', array( $this, 'update_relationships' ) );
		add_action( 'admin_init', array( $this, 'update_course_outline' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'init_query' ) );
		//add_action( 'init', array( $this, 'update_courses_archive' ) );

		add_action( 'admin_init', array( $this, 'create_voucher_tables' ) );
	}

	/**
	 * custom error notice ~ this needs to be moved to it's own class / factory
	 * @return string [error message]
	 */
	public function custom_error_notice() {
	 	global $current_screen;
	 	if ( $current_screen->parent_base == 'plugins' ) {
		 	echo '<div class="error"><p>Warning - LifterLMS is only compatable with Wordpress version '
		 		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
		 		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>'; }
	}

	public function first_time_setup() {
		if ( ! get_option( 'lifterlms_first_time_setup' ) ) {

			add_action( 'admin_notices', array( $this, 'welcome_message' ) );

		}
	}

	public function welcome_message() {
		global $current_screen;

	 	if ( $current_screen->base !== 'lifterlms_page_llms-settings' ) {

		 	echo '<div id="welcome-panel" class="welcome-panel">
         	<div class="welcome-panel-content">
					<h3>Welcome to LifterLMS!</h3>
					<p class="about-description">Before you start building your course check out our
					<a href="' . get_admin_url() . '/admin.php?page=llms-settings' . '">Quick Setup Guide</a></p>

					<form method="post" id="llms-skip-setup-form">
					<input type="hidden" name="action" value="llms-skip-setup" />
					' . wp_nonce_field( 'llms_skip_setup', '_wpnonce', true, false ) . '
					<p><input type="submit" class="llms-admin-link" name="llms-skip-setup" value="No thanks, I know what I\'m doing" /></p>
					</form>
				</div>
				</div>';

	 	}
	}

	/**
	 * Check if installed WP version is compatable with plugin requirements.
	 */
	public function check_wp_version() {
		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp_version, '<' ) ) {

			add_action( 'admin_notices', array( $this, 'custom_error_notice' ) );
		}
	}

	/**
	 * Update course, lesson and section syllabus
	 * @since  v1.0.6
	 * Updates users to new method of storing relationship
	 *
	 * @return void
	 */
	public function update_courses_archive() {
		$courses_archive = get_option( 'lifterlms_shop_page_id', '' );

		if ($courses_archive) {
			update_option( 'lifterlms_courses_page_id', $courses_archive );
			delete_option( 'lifterlms_shop_page_id' );
			flush_rewrite_rules();
		}

	}

	/**
	 * Update course, lesson and section syllabus
	 * @since  v1.0.6
	 * Updates users to new method of storing relationship
	 *
	 * @return void
	 */
	public function update_relationships() {
		$relationship_updated = get_option( 'lifterlms_relationship_update', 'no' ) === 'yes' ? true : false;
		if ( ! $relationship_updated) {
			$course_args = array(
				'posts_per_page'   => -1,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_type'        => 'course',
				'suppress_filters' => true,
			);
			$courses = get_posts( $course_args );
			foreach ($courses as $course) {
				$syllabus = get_post_meta( $course->ID, '_sections' );
				if ($syllabus) {
					foreach ($syllabus as $keys => $values) {
						if (isset( $values )) {
							foreach ($values as $k => $v) {
								if ($v['section_id']) {
									update_post_meta( $v['section_id'], '_parent_course', $course->ID );
									foreach ($v['lessons'] as $lk => $lv) {
										if ($v['lessons']) {
											update_post_meta( $lv['lesson_id'], '_parent_course', $course->ID );
											update_post_meta( $lv['lesson_id'], '_parent_section', $v['section_id'] );
										}
									}
								}
							}
						}
					}
				}
			}
			update_option( 'lifterlms_relationship_update', 'yes' );
		}
	}

	public function update_course_outline() {
		$course_outline_updated = get_option( 'lifterlms_course_outline_updated', 'no' ) === 'yes' ? true : false;
		if ( ! $course_outline_updated) {

			//get all courses _sections
			$args = array(
			'posts_per_page' 	=> -1,
			'post_type' 		=> 'course',
			'nopaging' 			=> true,
			'meta_query' 		=> array(
				array(
				    'key' => '_sections',
				    'compare' => '=',
				    ),
				),
			);
			$courses = get_posts( $args );

			if ( $courses ) {

				foreach ( $courses as $course ) {
					$syllabus = get_post_meta( $course->ID, '_sections', true );

					if ( ! empty( $syllabus )) {

						//set sections and lessons to post meta
						foreach ( $syllabus as $section ) {

							$section_id = $section['section_id'];
							$section_order = $section['position'];
							$lessons = $section['lessons'];

							//update parent_course and llms_order for sections
							update_post_meta( $section_id, '_parent_course', $course->ID );
							update_post_meta( $section_id, '_llms_order', $section_order );

							//loop through lessons and update llms_order, parent_section and parent_course
							if ( ! empty( $lessons ) ) {

								foreach ( $lessons as $lesson ) {

									$lesson_id = $lesson['lesson_id'];
									$lesson_order = $lesson['position'];

									update_post_meta( $lesson_id, '_parent_course', $course->ID );
									update_post_meta( $lesson_id, '_parent_section', $section_id );
									update_post_meta( $lesson_id, '_llms_order', $lesson_order );
								}

							}

						}

					}

				}
			}

			update_option( 'lifterlms_course_outline_updated', 'yes' );
		}

	}

	/**
	 * Core install method
	 *
	 * Registers post types, sidebars, taxonomies
	 * Sets cron jobs
	 * Flushes rewrites
	 *
	 * @return void
	 */
	public function install() {
		$this->create_options();
		$this->create_tables();
		$this->create_voucher_tables();
		$this->create_roles();

		// Register Post Types
		include_once( 'class.llms.post-types.php' );

		$this->register_post_types();
		$this->cron();
		flush_rewrite_rules();
	}

	/**
	 * initialize Query Variables and Endpoints
	 * @return void
	 */
	public function init_query() {
		LLMS()->query->init_query_vars();
		LLMS()->query->add_endpoints();
	}

	/**
	 * Register CPTs and Taxonomies
	 * @return null
	 */
	public function register_post_types() {
		include_once( 'class.llms.post-types.php' );

		include_once( 'class.llms.sidebars.php' );
		LLMS_Sidebars::register_lesson_sidebars();
		LLMS_Sidebars::register_course_sidebars();
	}

	/**
	 * Install Settings
	 * Only fires once.
	 *
	 * Creates posts and pages used by lifterLMS
	 *
	 * @return void
	 */
	public function install_settings() {
		$installation_complete = get_option( 'lifterlms_settings_installed', 'no' ) === 'yes' ? true : false;
		if ( ! $installation_complete ) {
			self::create_pages();
			self::create_posts();
			update_option( 'lifterlms_settings_installed', 'yes' );
		}
	}

	/**
	 * Create starter pages
	 *
	 * TODO: This could be refactored to loop through and gen the pages.
	 */
	public static function create_pages() {
		$membership_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Memberships',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '',
		) );
		$shop_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Courses',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '',
		) );
		$checkout_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Purchase',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '[lifterlms_checkout]',
		) );
		$account_page = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'My Courses',
			'post_author' 	=> 1,
			'post_status'   => 'publish',
			'post_content'  => '[lifterlms_my_account]',
		) );
		$membership_page_id = wp_insert_post( $membership_page, true );
		$shop_page_id = wp_insert_post( $shop_page, true );
		$checkout_page_id = wp_insert_post( $checkout_page, true );
		$account_page_id = wp_insert_post( $account_page, true );
	}

	/**
	 * create any posts needed by lifterLMS
	 *
	 * @return void
	 */
	public function create_posts() {
		$new_user_email = apply_filters( 'lifterlms_new_page', array(
			'post_type' 	=> 'llms_email',
			'post_title'    => 'Welcome Email',
			'post_content'  => '<p>Hey there {user_login}</p>,
								<p>Thanks for creating an account on {site_title}!</p>
								Your username is <strong>{user_login}.</strong>
								You can access your account here: <a href="{site_url}">{site_title}</a>.',
			'post_status'   => 'publish',
			'post_author'   => 1,
		) );
		$new_user_email_id = wp_insert_post( $new_user_email, true );
		update_post_meta( $new_user_email_id,'_event_id', 'email_new_user' );
		update_post_meta( $new_user_email_id,'_email_subject', 'Welcome to {site_title}' );
	}

	/**
	 * Creates activation and update options in db
	 *
	 * @return void
	 */
	public function create_options() {
		//store installed version
		add_option( 'lifterlms_current_version', LLMS_VERSION );
		add_option( 'lifterlms_is_activated', '' );
		add_option( 'lifterlms_update_key', '' );
		add_option( 'lifterlms_authkey', 'YA5j24mKX38yyLZf2CD6YX6i78Kr94tg' );

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
	 * Create lifterLMS tables
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb, $lifterlms;
		$wpdb->hide_errors();
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		//lifterLMS Tables
		$lifterlms_tables = "
			CREATE TABLE {$wpdb->prefix}lifterlms_order (
			  order_id bigint(20) NOT NULL auto_increment,
			  user_id bigint(20) NOT NULL,
			  created_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  order_completed varchar(200) NOT NULL DEFAULT 'no',
			  completed_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  product_id bigint(20) NOT NULL,
			  order_post_id bigint(20) NULL,
			  PRIMARY KEY  (order_id),
			  KEY user_id (user_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}lifterlms_user_postmeta (
			  meta_id bigint(20) NOT NULL auto_increment,
			  user_id bigint(20) NOT NULL,
			  post_id bigint(20) NOT NULL,
			  meta_key varchar(255) NULL,
			  meta_value longtext NULL,
			  updated_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY  (meta_id),
			  KEY user_id (user_id),
			  KEY post_id (post_id)
			) $collate;
		";
		try {
			$conn = dbDelta( $lifterlms_tables );
		} catch (Exception $e) {
			throw new Exception( 'Instalation failed. Error creating lifterLMS tables in database.', 0, $e );
		}
	}

	/**
	 * Create voucher tables
	 */
	public function create_voucher_tables() {

		$tables_created = (get_option( 'lifterlms_voucher_tables_installed', 'no' ) == 'yes') ? true : false;

		if ( ! $tables_created) {
			global $wpdb;

			$wpdb->hide_errors();
			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) {
					$collate .= " COLLATE $wpdb->collate";
				}
			}
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			//lifterLMS Tables
			$lifterlms_tables = "
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
			try {
				$conn = dbDelta( $lifterlms_tables );
				update_option( 'lifterlms_voucher_tables_installed', 'yes' );
			} catch (Exception $e) {
				throw new Exception( 'Instalation failed. Error creating lifterLMS voucher tables in database.', 0, $e );
			}
		}
	}

	/**
	 * Create lifterLMS user roles
	 * Creates student role and assigns new users to student role.
	 *
	 * @return void
	 */
	public function create_roles() {
		global $wp_roles;

		// this function should only ever run once!
		$roles_installed = (get_option( 'lifterlms_student_role_created', 'no' ) == 'yes') ? true : false;
		if ( ! $roles_installed ) {
			add_role(
				'student',
				__( 'Student', 'lifterlms' ),
				array(
					'read' => true,
				)
			);

			/**
			 * Migrate "person" -> "student"
			 * Temporary query to move all existing "person" roles to updated "student" role
			 * @since  v1.0.6
			 */
			$persons = new WP_User_Query( array( 'role' => 'person' ) );
			if ( ! empty( $persons->results ) ) {
				foreach ( $persons->results as $user ) {
					$user->add_role( 'student' );
					$user->remove_role( 'person' );
					$user->remove_cap( 'person' );
				}
			}
			update_option( 'lifterlms_student_role_created', 'yes' );
		}
	}

	/**
	 * Create lifterLMS cron jobs
	 * @return  void
	 */
	public function cron() {

		if ( ! wp_next_scheduled( 'lifterlms_cleanup_sessions' )) {
				wp_schedule_event( time(), 'twicedaily', 'lifterlms_cleanup_sessions' );
		}

	}

}

return new LLMS_Install();
