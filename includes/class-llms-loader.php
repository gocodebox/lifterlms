<?php
/**
 * LifterLMS file loader.
 *
 * @package LifterLMS/Classes
 *
 * @since 4.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Loader
 *
 * @since 4.0.0
 */
class LLMS_Loader {

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
	 * @since 1.0.0
	 * @since 3.15.0 Unknown.
	 * @since 4.0.0 Moved from `LifterLMS` class.
	 *
	 * @param string $class Class name being called.
	 * @return void
	 */
	public function autoload( $class ) {

		$class = strtolower( $class );

		$path    = null;
		$fileize = str_replace( '_', '.', $class );
		$file    = 'class.' . $fileize . '.php';

		if ( strpos( $class, 'llms_meta_box' ) === 0 ) {
			$path = LLMS_PLUGIN_DIR . '/includes/admin/post-types/meta-boxes/';
		} elseif ( strpos( $class, 'llms_widget_' ) === 0 ) {
			$path = LLMS_PLUGIN_DIR . '/includes/widgets/';
		} elseif ( strpos( $class, 'llms_integration_' ) === 0 ) {
			$path = LLMS_PLUGIN_DIR . '/includes/integrations/';
		} elseif ( strpos( $class, 'llms_controller_' ) === 0 ) {
			$path = LLMS_PLUGIN_DIR . '/includes/controllers/';
		} elseif ( 0 === strpos( $class, 'llms_abstract' ) ) {
			$path = LLMS_PLUGIN_DIR . '/includes/abstracts/';
			$file = $fileize . '.php';
		} elseif ( 0 === strpos( $class, 'llms_interface' ) ) {
			$path = LLMS_PLUGIN_DIR . '/includes/interfaces/';
			$file = $fileize . '.php';
		} elseif ( strpos( $class, 'llms_' ) === 0 ) {
			$path = LLMS_PLUGIN_DIR . '/includes/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			require_once $path . $file;
			return;
		}
	}

	/**
	 * Includes that are included everywhere
	 *
	 * @since 4.0.0
	 * @since 4.4.0 Include `LLMS_Assets` class.
	 * @since 4.12.0 Class `LLMS_Staging` always loaded instead of only loaded on admin panel.
	 * @since 4.13.0 Include `LLMS_DOM_Document` class.
	 * @since 5.0.0 Include `LLMS_Forms`, `LLMS_Form_Post_Type`, `LLMS_Form_Templates`, and `LLMS_Form_Handler`.
	 *
	 * @return void
	 */
	public function includes() {

		// Abstract classes that are not caught by the autoloader.
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.database.query.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.payment.gateway.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.post.model.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-generator-posts.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-session-data.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-session-database-handler.php';

		// Models.
		require_once LLMS_PLUGIN_DIR . 'includes/models/class-llms-event.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.access.plan.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.add-on.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.coupon.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.course.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.instructor.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.lesson.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.membership.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.notification.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.order.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.post.instructors.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.product.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.question.choice.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.question.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.quiz.attempt.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.quiz.attempt.question.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.quiz.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.section.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.student.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.student.quizzes.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.transaction.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.user.achievement.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.user.certificate.php';
		require_once LLMS_PLUGIN_DIR . 'includes/models/model.llms.user.postmeta.php';

		// Functions.
		require_once LLMS_PLUGIN_DIR . 'includes/llms.functions.core.php';

		// Classes.
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-assets.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-dom-document.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-events.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-events-core.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-events-query.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-grades.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-mime-type-extractor.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-sessions.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class-llms-staging.php';

		// Forms.
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-form-field.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-form-handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-form-post-type.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-form-templates.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-form-validator.php';
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
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.gateway.manual.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.hasher.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.install.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.l10n.js.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.lesson.handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.nav.menus.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.oembed.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.person.handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.playnice.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.post.handler.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.post.relationships.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.post-types.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.query.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.query.quiz.attempt.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.query.user.postmeta.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.question.types.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.review.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.session.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.sidebars.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.student.dashboard.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.student.query.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.user.permissions.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.view.manager.php';

		// Controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.achievements.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.certificates.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.lesson.progression.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.orders.php';
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.quizzes.php';

		// Form controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.account.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.login.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/controllers/class.llms.controller.registration.php';

		// Hooks.
		require_once LLMS_PLUGIN_DIR . 'includes/llms.template.hooks.php';

		// Notifications.
		require_once LLMS_PLUGIN_DIR . 'includes/notifications/class.llms.notifications.php';
		require_once LLMS_PLUGIN_DIR . 'includes/notifications/class.llms.notifications.query.php';

		// Privacy components.
		require_once LLMS_PLUGIN_DIR . 'includes/privacy/class-llms-privacy.php';

		// Processors.
		require_once LLMS_PLUGIN_DIR . 'includes/processors/class.llms.processors.php';

		// Shortcodes.
		require_once LLMS_PLUGIN_DIR . 'includes/shortcodes/class.llms.shortcode.checkout.php';
		require_once LLMS_PLUGIN_DIR . 'includes/shortcodes/class.llms.shortcode.my.account.php';
		require_once LLMS_PLUGIN_DIR . 'includes/shortcodes/class.llms.shortcodes.php';

		// Theme support.
		require_once LLMS_PLUGIN_DIR . 'includes/theme-support/class-llms-theme-support.php';

		// Widgets.
		require_once LLMS_PLUGIN_DIR . 'includes/widgets/class.llms.widget.php';
		require_once LLMS_PLUGIN_DIR . 'includes/widgets/class.llms.widgets.php';

	}

	/**
	 * Includes that are required only on the admin panel
	 *
	 * @since 4.0.0
	 * @since 4.7.0 Always load `LLMS_Admin_Reporting`.
	 * @since 4.8.0 Add `LLMS_Export_API`.
	 * @since 4.12.0 Class `LLMS_Staging` always loaded instead of only loaded on admin panel.
	 * @since 5.0.0 Include `LLMS_Forms_Unsupported_Versions` class.
	 *
	 * @return void
	 */
	public function includes_admin() {

		// This should be an abstract.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php';

		// This should be moved to the interfaces directory.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php';

		// Abstracts.
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.admin.metabox.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/abstract.llms.admin.table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-email-provider.php';

		// Functions.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/llms.functions.admin.php';

		// Admin classes.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-export-download.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-review.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-users-table.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-export-api.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-mailhawk.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-sendwp.php';
		require_once LLMS_PLUGIN_DIR . 'includes/forms/class-llms-forms-unsupported-versions.php';

		// Admin classes (files to be renamed).
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
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/tables/class.llms.table.student.management.php';

		// Classes.
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.dot.com.api.php';
		require_once LLMS_PLUGIN_DIR . 'includes/class.llms.generator.php';

		// Controllers.
		require_once LLMS_PLUGIN_DIR . 'includes/controllers/class.llms.controller.admin.quiz.attempts.php';

		// Reporting.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/class.llms.admin.reporting.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/widgets/class.llms.analytics.widget.ajax.php';

		// Load setup wizard conditionally.
		if ( 'llms-setup' === llms_filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) ) {
			require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.setup.wizard.php';
		}

	}

	/**
	 * Include libraries
	 *
	 * @since 4.0.0
	 * @since 4.9.0 Adds constants which can be used to identify when included libraries have been loaded.
	 * @since 5.0.0 Load core libraries from new location, add WP Background Processing lib, add LLMS Helper.
	 *
	 * @return void
	 */
	public function includes_libraries() {

		$libs = array(
			array(
				'const' => 'LLMS_BLOCKS_LIB',
				'test'  => function_exists( 'has_blocks' ) && ! defined( 'LLMS_BLOCKS_VERSION' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-blocks/lifterlms-blocks.php',
			),
			array(
				'const' => 'LLMS_REST_API_LIB',
				'test'  => ! class_exists( 'LifterLMS_REST_API' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-rest/lifterlms-rest.php',
			),
			array(
				'const' => 'LLMS_HELPER_LIB',
				'test'  => ! class_exists( 'LifterLMS_Helper' ),
				'file'  => LLMS_PLUGIN_DIR . 'libraries/lifterlms-helper/lifterlms-helper.php',
			),
		);

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
