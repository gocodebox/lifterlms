<?php
/**
 * LifterLMS file loader
 *
 * @package LifterLMS/Classes
 *
 * @since 4.0.0
 * @version 7.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Loader.
 *
 * @since 4.0.0
 * @since 5.3.0 Add traits to `autoload()`.
 */
class LLMS_Loader {

	/**
	 * These classes do not conform to any of the LifterLMS class name or file name standards.
	 *
	 * @todo Rename these classes and/or add a namespace to them.
	 *
	 * @since 6.0.0
	 *
	 * @var string[] [ $lowercase_class_name => $path_relative_to_LLMS_PLUGIN_DIR ]
	 */
	private $non_standard_classes = array(
		// Missing "_Abstract_" from class name.
		'llms_admin_metabox'                 => 'includes/abstracts/abstract.llms.admin.metabox.php',
		'llms_admin_table'                   => 'includes/abstracts/abstract.llms.admin.table.php',
		'llms_analytics_widget'              => 'includes/abstracts/abstract.llms.analytics.widget.php',
		'llms_database_query'                => 'includes/abstracts/abstract.llms.database.query.php',
		'llms_payment_gateway'               => 'includes/abstracts/abstract.llms.payment.gateway.php',
		'llms_post_model'                    => 'includes/abstracts/abstract.llms.post.model.php',
		'llms_shortcode_course_element'      => 'includes/abstracts/abstract.llms.shortcode.course.element.php',
		'llms_shortcode'                     => 'includes/abstracts/abstract.llms.shortcode.php',
		'llms_update'                        => 'includes/abstracts/abstract.llms.update.php',

		// Missing "_Admin_" from class name.
		'llms_export_api'                    => 'includes/admin/class-llms-export-api.php',

		// Meta box fields.
		'llms_metabox_field'                 => 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php',
		'llms_metabox_textarea_w_tags_field' => 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.textarea.tags.php',
		'meta_box_field_interface'           => 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php',

		// Missing "Model" from class name.
		'llms_access_plan'                   => 'includes/models/model.llms.access.plan.php',
		'llms_add_on'                        => 'includes/models/model.llms.add-on.php',
		'llms_coupon'                        => 'includes/models/model.llms.coupon.php',
		'llms_course'                        => 'includes/models/model.llms.course.php',
		'llms_event'                         => 'includes/models/class-llms-event.php',
		'llms_instructor'                    => 'includes/models/model.llms.instructor.php',
		'llms_lesson'                        => 'includes/models/model.llms.lesson.php',
		'llms_membership'                    => 'includes/models/model.llms.membership.php',
		'llms_notification'                  => 'includes/models/model.llms.notification.php',
		'llms_order'                         => 'includes/models/model.llms.order.php',
		'llms_post_instructors'              => 'includes/models/model.llms.post.instructors.php',
		'llms_product'                       => 'includes/models/model.llms.product.php',
		'llms_question_choice'               => 'includes/models/model.llms.question.choice.php',
		'llms_question'                      => 'includes/models/model.llms.question.php',
		'llms_quiz_attempt'                  => 'includes/models/model.llms.quiz.attempt.php',
		'llms_quiz_attempt_question'         => 'includes/models/model.llms.quiz.attempt.question.php',
		'llms_quiz'                          => 'includes/models/model.llms.quiz.php',
		'llms_section'                       => 'includes/models/model.llms.section.php',
		'llms_student'                       => 'includes/models/model.llms.student.php',
		'llms_student_quizzes'               => 'includes/models/model.llms.student.quizzes.php',
		'llms_transaction'                   => 'includes/models/model.llms.transaction.php',
		'llms_user_achievement'              => 'includes/models/model.llms.user.achievement.php',
		'llms_user_certificate'              => 'includes/models/model.llms.user.certificate.php',
		'llms_user_postmeta'                 => 'includes/models/model.llms.user.postmeta.php',

		// Miscellaneous.
		'llms_admin_reporting'               => 'includes/admin/reporting/class.llms.admin.reporting.php',
		'llms_admin_system_report'           => 'includes/admin/class.llms.admin.system-report.php',
		'llms_bbp_widget_course_forums_list' => 'includes/widgets/class.llms.bbp.widget.course.forums.list.php',
		'llms_media_protection'              => 'includes/class-llms-media-protection.php',
		'llms_db_upgrader'                   => 'includes/class-llms-db-ugrader.php',
		'llms_emails'                        => 'includes/class.llms.emails.php',
		'llms_payment_gateway_manual'        => 'includes/class.llms.gateway.manual.php',
		'llms_settings_page'                 => 'includes/admin/settings/class.llms.settings.page.php',
		'llms_table_notificationsettings'    => 'includes/admin/settings/tables/class.llms.table.notification.settings.php',
		'llms_table_student_certificates'    => 'includes/admin/reporting/tables/llms.table.certificates.php',
		'llms_table_studentmanagement'       => 'includes/admin/post-types/tables/class.llms.table.student.management.php',

		// Deprecated classes.
		'llms_achievement_user'              => 'includes/achievements/class.llms.achievement.user.php',
		'llms_certificate_user'              => 'includes/certificates/class.llms.certificate.user.php',
	);

	/**
	 * An array of paths and what the class name starts with.
	 *
	 * @since 6.0.0
	 *
	 * @var string[] [ $path_relative_to_LLMS_PLUGIN_DIR => $class_name_starts_with ]
	 */
	private $class_paths = array(
		'includes/admin/tools/'                 => 'llms_admin_tool_',
		'includes/admin/'                       => 'llms_admin_',
		'includes/controllers/'                 => 'llms_controller_',
		'includes/emails/'                      => 'llms_email',
		'includes/forms/'                       => 'llms_form',
		'includes/integrations/'                => 'llms_integration_',
		'includes/admin/post-types/meta-boxes/' => 'llms_meta_box_',
		'includes/notifications/views/'         => 'llms_notification_view_',
		'includes/notifications/'               => 'llms_notification',
		'includes/privacy/'                     => 'llms_privacy',
		'includes/processors/'                  => 'llms_processor',
		'includes/shortcodes/'                  => 'llms_shortcode',
		'includes/widgets/'                     => 'llms_widget',
		'includes/'                             => 'llms_',
	);

	/**
	 * Constructor
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->includes_libraries();

		$this->includes();

		if ( is_admin() ) {
			$this->includes_admin();
		} else {
			$this->includes_frontend();
		}
	}

	/**
	 * Auto-load LLMS classes.
	 *
	 * @todo Add a {@link https://www.php.net/manual/en/language.namespaces.php namespace} to every file to simplify autoloading.
	 *
	 * @since 1.0.0
	 * @since 3.15.0 Unknown.
	 * @since 4.0.0 Moved from `LifterLMS` class.
	 * @since 5.3.0 Add traits.
	 * @since 6.0.0 Increased the number of files that are autoloaded instead of manually loaded on every request.
	 *              Return early if not a LifterLMS core class.
	 *
	 * @param string $class Class name being called.
	 * @return void
	 */
	public function autoload( $class ) {

		$class = strtolower( $class );
		if ( 0 !== strpos( $class, 'llms_' ) && 'lifterlms' !== $class && 'meta_box_field_interface' !== $class ) {
			return;
		}
		$path    = null;
		$fileize = str_replace( '_', '-', $class );
		$file    = 'class-' . $fileize . '.php';

		if ( array_key_exists( $class, $this->non_standard_classes ) ) {
			$path = LLMS_PLUGIN_DIR . $this->non_standard_classes[ $class ];
			$file = null;

		} elseif ( 0 === strpos( $class, 'llms_abstract_' ) ) {
			$path = LLMS_PLUGIN_DIR . 'includes/abstracts/';
			$file = $fileize . '.php';

		} elseif (
			0 === strpos( $class, 'llms_analytics_' ) && false !== strrpos( $class, '_widget', - 7 )
		) {
			$path = LLMS_PLUGIN_DIR . 'includes/admin/reporting/widgets/';
			$file = 'class.llms.analytics.widget.' . substr( $class, 15, - 7 ) . '.php';

		} elseif ( 0 === strpos( $class, 'llms_interface_' ) ) {
			$path = LLMS_PLUGIN_DIR . 'includes/interfaces/';
			$file = $fileize . '.php';

		} elseif (
			0 === strpos( $class, 'llms_metabox_' ) && false !== strrpos( $class, '_field', - 6 )
		) {
			$path = LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/';
			$file = 'llms-class-meta-box-' . substr( $fileize, 13, - 6 ) . '.php';

		} elseif ( 0 === strpos( $class, 'llms_table_' ) ) {
			/** @todo Prefix file names with 'class-' */
			$path = LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/';
			$file = $fileize . '.php';

		} elseif ( 0 === strpos( $class, 'llms_trait_' ) ) {
			$path = LLMS_PLUGIN_DIR . 'includes/traits/';
			$file = $fileize . '.php';
		}

		if ( is_null( $path ) ) {
			foreach ( $this->class_paths as $class_path => $class_name_starts_with ) {
				if ( 0 === strpos( $class, $class_name_starts_with ) ) {
					$path = LLMS_PLUGIN_DIR . $class_path;
					break;
				}
			}
		}

		if ( $path ) {
			if ( is_readable( $path . $file ) ) {
				require_once $path . $file;
				return;
			}

			$file = str_replace( '-', '.', $file );
			if ( is_readable( $path . $file ) ) {
				require_once $path . $file;
				return;
			}
		}
	}

	/**
	 * Includes that are included everywhere.
	 *
	 * @since 4.0.0
	 * @since 4.4.0 Include `LLMS_Assets` class.
	 * @since 4.12.0 Class `LLMS_Staging` always loaded instead of only loaded on admin panel.
	 * @since 4.13.0 Include `LLMS_DOM_Document` class.
	 * @since 5.0.0 Include `LLMS_Forms`, `LLMS_Form_Post_Type`, `LLMS_Form_Templates`, and `LLMS_Form_Handler`.
	 * @since 5.2.0 Include `LLMS_DB_Upgrader`.
	 * @since 5.6.0 Include `LLMS_Prevent_Concurrent_Logins`.
	 * @since 6.0.0 Included `LLMS_Block_Library`, `LLMS_Controller_Awards`, and `LLMS_Engagement_Handler`.
	 *              Removed loading of class files that don't instantiate their class in favor of autoloading.
	 * @since 6.4.0 Included `LLMS_Shortcodes` before `LLMS_Controller_Orders`.
	 * @since 7.0.0 Include `LLMS_Controller_Checkout`.
	 * @since 7.2.0 Include `LLMS_Shortcodes_Blocks`.
	 *
	 * @return void
	 */
	public function includes() {

		// Instantiate LLMS_Shortcodes before LLMS_Controller_Orders.
		require_once LLMS_PLUGIN_DIR . 'includes/shortcodes/class.llms.shortcodes.php';
		require_once LLMS_PLUGIN_DIR . 'includes/shortcodes/class.llms.shortcodes.blocks.php';

		// Functions.
		require_once LLMS_PLUGIN_DIR . 'includes/llms.functions.core.php';

		// Classes.
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-block-library.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-events-core.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-rest-fields.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-sessions.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-staging.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-prevent-concurrent-logins.php';

		// Forms.
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-admin-bar.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-classic-editor.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-data.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-dynamic-fields.php';

		// Classes (files to be renamed).
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.assets.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.ajax.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.ajax.handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.cache.helper.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.comments.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.date.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.install.php';
		include_once LLMS_PLUGIN_DIR . 'includes/class.llms.l10n.frontend.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.nav.menus.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.oembed.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.playnice.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.post.relationships.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.post-types.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.query.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.question.types.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.review.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.sidebars.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.student.dashboard.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.user.permissions.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.view.manager.php';

		// Controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.achievements.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class-llms-controller-awards.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.certificates.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.lesson.progression.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class-llms-controller-checkout.php'; // Added out of alpha order to preserve action load order.
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.orders.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.quizzes.php';

		// Form controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.account.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.login.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.registration.php';

		// Hooks.
		require_once LLMS_PLUGIN_DIR . 'includes/llms.template.hooks.php';

		// Privacy components.
		require_once LLMS_PLUGIN_DIR . 'includes/privacy/class-llms-privacy.php';

		// Theme support.
		require_once LLMS_PLUGIN_DIR . 'includes/theme-support/class-llms-theme-support.php';

		// Widgets.
		require_once LLMS_PLUGIN_DIR . 'includes/widgets/class.llms.widget.php';
		require_once LLMS_PLUGIN_DIR . 'includes/widgets/class.llms.widgets.php';

		// Elementor support.
		require_once LLMS_PLUGIN_DIR . 'includes/elementor/class-llms-elementor-widgets.php';
	}

	/**
	 * Includes that are required only on the admin panel
	 *
	 * @since 4.0.0
	 * @since 4.7.0 Always load `LLMS_Admin_Reporting`.
	 * @since 4.8.0 Add `LLMS_Export_API`.
	 * @since 4.12.0 Class `LLMS_Staging` always loaded instead of only loaded on admin panel.
	 * @since 5.0.0 Include `LLMS_Forms_Unsupported_Versions` class.
	 * @since 5.9.0 Drop usage of deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 * @since 7.2.0 Include `LLMS_Admin_Dashboard_Wigdet` class.
	 *
	 * @return void
	 */
	public function includes_admin() {

		// Functions.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/llms.functions.admin.php';

		// Admin classes.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-header.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-export-download.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-plugins.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-review.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-users-table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-mailhawk.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-sendwp.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-unsupported-versions.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-permalinks.php';

		// Admin classes (files to be renamed).
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.dashboard.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.dashboard-widget.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.import.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.menus.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.core.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.post-types.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.reviews.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.user.custom.fields.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-profile.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.student.bulk.enroll.php';

		// Post types.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/class.llms.post.tables.php';

		// Controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.admin.quiz.attempts.php';

		// Reporting.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/widgets/class.llms.analytics.widget.ajax.php';

		// Load setup wizard conditionally.
		if ( 'llms-setup' === llms_filter_input( INPUT_GET, 'page' ) ) {
			require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.setup.wizard.php';
		}
	}

	/**
	 * Include libraries
	 *
	 * @since 4.0.0
	 * @since 4.9.0 Adds constants which can be used to identify when included libraries have been loaded.
	 * @since 5.0.0 Load core libraries from new location, add WP Background Processing lib, add LLMS Helper.
	 * @since 5.1.3 Add keys to the $libs array and pass them through a filter.
	 * @since 5.5.0 Add LLMS-CLI to the list of included libraries.
	 *
	 * @return void
	 */
	public function includes_libraries() {

		$libs = array(
			'blocks' => array(
				'const' => 'LLMS_BLOCKS_LIB',
				'test'  => function_exists( 'has_blocks' ) && ! defined( 'LLMS_BLOCKS_VERSION' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-blocks/lifterlms-blocks.php',
			),
			'cli'    => array(
				'const' => 'LLMS_CLI_LIB',
				'test'  => ! function_exists( 'llms_cli' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-cli/lifterlms-cli.php',
			),
			'rest'   => array(
				'const' => 'LLMS_REST_API_LIB',
				'test'  => ! class_exists( 'LifterLMS_REST_API' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-rest/lifterlms-rest.php',
			),
			'helper' => array(
				'const' => 'LLMS_HELPER_LIB',
				'test'  => ! class_exists( 'LifterLMS_Helper' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-helper/lifterlms-helper.php',
			),
		);

		/**
		 * Filters the list of LifterLMS libraries to be loaded.
		 *
		 * @since 5.1.3
		 *
		 * @param array $libs {
		 *     Array of library data. Each array key serves as a unique ID for the library.
		 *
		 *     @type string $const Name of the constant used to identify if the library is loaded as a library.
		 *     @type bool   $test  A test which is evaluated to determine if the library should be loaded. Returning `false` causes the library not to load.
		 *     @type string $file  Path to the main library file's location in the LifterLMS core plugin.
		 * }
		 */
		$libs = apply_filters( 'llms_included_libs', $libs );
		foreach ( $libs as $lib ) {

			if ( $lib['test'] ) {
				define( $lib['const'], true );
				require_once $lib['file'];
			}
		}

		// Action Scheduler.
		require_once LLMS_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

		// WP Background Processing.
		require_once LLMS_PLUGIN_DIR . 'vendor/deliciousbrains/wp-background-processing/wp-background-processing.php';
	}

	/**
	 * Includes that are required only on the frontend
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Removed deprecated classes: LLMS_Frontend_Forms & LLMS_Frontend_Password.
	 *
	 * @return void
	 */
	public function includes_frontend() {

		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.frontend.assets.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.https.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.template.loader.php';
	}
}

return new LLMS_Loader();
