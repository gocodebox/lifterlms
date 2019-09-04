<?php
/**
 * Admin Assets Class
 *
 * @since 1.0.0
 * @version 3.35.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Assets class.
 *
 * @since 1.0.0
 * @since 3.35.0 Explicitly set asset versions.
 * @since 3.35.1 Don't reference external scripts & styles.
 */
class LLMS_Admin_Assets {

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @version  3.17.5
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );

	}

	/**
	 * Determine if the current screen should load LifterLMS assets
	 *
	 * @return   boolean
	 * @since    3.7.0
	 * @version  3.19.4
	 */
	public function is_llms_page() {

		$screen = get_current_screen();

		$id = str_replace( 'edit-', '', $screen->id );

		if ( false !== strpos( $id, 'lifterlms' ) ) {
			return true;
		} elseif ( false !== strpos( $id, 'llms' ) ) {
			return true;
		} elseif ( in_array( $id, array( 'course', 'lesson' ) ) ) {
			return true;
		} elseif ( ! empty( $screen->post_type ) && post_type_supports( $screen->post_type, 'llms-membership-restrictions' ) ) {
			return true;
		} elseif ( in_array( $screen->id, array( 'users' ) ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Enqueue stylesheets
	 *
	 * @since 1.0.0
	 * @since 3.29.0 Unknown.
	 * @since 3.35.0 Explicitly set asset versions.
	 *
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style( 'llms-admin-styles', LLMS_PLUGIN_URL . 'assets/css/admin' . LLMS_ASSETS_SUFFIX . '.css', array(), LLMS()->version );
		wp_style_add_data( 'llms-admin-styles', 'rtl', 'replace' );
		wp_style_add_data( 'llms-admin-styles', 'suffix', LLMS_ASSETS_SUFFIX );

		if ( ! $this->is_llms_page() ) {
			return;
		}

		wp_enqueue_style( 'llms-select2-styles', LLMS_PLUGIN_URL . 'assets/vendor/select2/css/select2' . LLMS_ASSETS_SUFFIX . '.css', array(), '4.0.3' );

		$screen = get_current_screen();

		if ( 'lifterlms_page_llms-add-ons' === $screen->id || 'lifterlms_page_llms-settings' === $screen->id ) {
			wp_register_style( 'llms-admin-add-ons', LLMS_PLUGIN_URL . 'assets/css/admin-add-ons' . LLMS_ASSETS_SUFFIX . '.css', array(), LLMS()->version, 'all' );
			wp_enqueue_style( 'llms-admin-add-ons' );
			wp_style_add_data( 'llms-admin-add-ons', 'rtl', 'replace' );
			wp_style_add_data( 'llms-admin-add-ons', 'suffix', LLMS_ASSETS_SUFFIX );
		}

	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 * @since 3.22.0 Unknown.
	 * @since 3.35.0 Explicitly set asset versions.
	 * @since 3.35.1 Don't reference external scripts & styles.
	 *
	 * @return   void
	 */
	public function admin_scripts() {

		global $post_type;
		$screen = get_current_screen();

		if ( 'widgets' === $screen->id ) {

			wp_enqueue_script( 'llms-widget-syllabus', LLMS_PLUGIN_URL . 'assets/js/llms-widget-syllabus' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );

		}

		wp_register_script( 'llms-metaboxes', LLMS_PLUGIN_URL . 'assets/js/llms-metaboxes' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-datepicker', 'llms-admin-scripts' ), LLMS()->version, true );
		wp_register_script( 'llms-select2', LLMS_PLUGIN_URL . 'assets/vendor/select2/js/select2' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '4.0.3', true );

		if ( ( post_type_exists( $screen->id ) && post_type_supports( $screen->id, 'llms-membership-restrictions' ) ) || 'dashboard_page_llms-setup' === $screen->id ) {

			wp_enqueue_script( 'llms-select2' );
			wp_enqueue_script( 'llms-metaboxes' );

		}

		$tables = apply_filters(
			'llms_load_table_resources_pages',
			array(
				'course',
				'lifterlms_page_llms-reporting',
				'llms_membership',
			)
		);
		if ( in_array( $screen->id, $tables ) ) {
			wp_register_script( 'llms-admin-tables', LLMS_PLUGIN_URL . 'assets/js/llms-admin-tables' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_script( 'llms-admin-tables' );
		}

		if ( $this->is_llms_page() ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_register_script( 'llms', LLMS_PLUGIN_URL . 'assets/js/llms' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );

			wp_register_script( 'llms-admin-scripts', LLMS_PLUGIN_URL . 'assets/js/llms-admin' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'llms-select2' ), LLMS()->version, true );
			wp_enqueue_script( 'llms-admin-scripts' );

			wp_register_style( 'jquery-ui-flick', LLMS_PLUGIN_URL . 'assets/vendor/jquery-ui-flick/jquery-ui-flick' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.11.2' );
			wp_enqueue_style( 'jquery-ui-flick' );

			wp_enqueue_script( 'llms-ajax', LLMS_PLUGIN_URL . 'assets/js/llms-ajax' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );

			wp_enqueue_media();

			wp_register_script( 'top-modal', LLMS_PLUGIN_URL . 'assets/js/vendor/topModal.js', array( 'jquery' ), '1.0.0', true );

			wp_enqueue_script( 'llms-select2' );

			if ( 'course' == $post_type ) {

				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}

			if ( 'course' == $post_type || 'llms_membership' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-students', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-students' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms-select2' ), LLMS()->version, true );
				wp_enqueue_script( 'llms-metabox-product', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-product' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'top-modal' ), LLMS()->version, true );
				wp_enqueue_script( 'llms-metabox-instructors', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-instructors' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'top-modal' ), LLMS()->version, true );

			}

			if ( 'lesson' == $post_type ) {
				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_certificate' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-certificate', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-certificate' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_achievement' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-achievement', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-achievement' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_membership' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_voucher' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-voucher', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-voucher' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_coupon' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
			}

			if ( 'lifterlms_page_llms-reporting' === $screen->base || 'lifterlms_page_llms-settings' === $screen->base ) {

				wp_register_script( 'llms-google-charts', LLMS_PLUGIN_URL . 'assets/js/vendor/gcharts-loader.min.js', array(), '2019-09-04' );
				wp_register_script( 'llms-analytics', LLMS_PLUGIN_URL . 'assets/js/llms-analytics' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'llms-admin-scripts', 'llms-google-charts' ), LLMS()->version, true );

				if ( 'lifterlms_page_llms-settings' === $screen->base ) {

					wp_enqueue_script( 'llms-analytics' );
					wp_enqueue_script( 'llms-metaboxes' );

				} elseif ( isset( $_GET['tab'] ) ) {

					switch ( $_GET['tab'] ) {
						case 'enrollments':
						case 'sales':
							wp_enqueue_script( 'llms-select2' );
							wp_enqueue_script( 'llms-analytics' );
							wp_enqueue_script( 'llms-metaboxes' );

							break;

						case 'students':
							if ( isset( $_GET['stab'] ) && 'courses' === $_GET['stab'] ) {
								wp_enqueue_script( 'llms-metaboxes' );
							}
							break;

						case 'quizzes':
							if ( isset( $_GET['stab'] ) && 'attempts' === $_GET['stab'] ) {
								wp_enqueue_script( 'llms-quiz-attempt-review', LLMS_PLUGIN_URL . 'assets/js/llms-quiz-attempt-review' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms' ), LLMS()->version, true );
							}
							break;

					}
				}
			}

			wp_enqueue_script( 'top-modal' );

			wp_enqueue_script( 'llms' );
			wp_enqueue_script( 'llms-metaboxes' );

		}

		if ( 'lifterlms_page_llms-settings' == $screen->id ) {

			wp_enqueue_media();
			wp_enqueue_script( 'llms-admin-settings', LLMS_PLUGIN_URL . 'assets/js/llms-admin-settings' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-sortable' ), LLMS()->version, true );

		} elseif ( 'admin_page_llms-course-builder' === $screen->id ) {

			self::register_quill();

			wp_enqueue_editor();

			wp_enqueue_style( 'llms-builder-styles', LLMS_PLUGIN_URL . 'assets/css/builder' . LLMS_ASSETS_SUFFIX . '.css', array( 'llms-quill-bubble' ), LLMS()->version, 'screen' );
			wp_style_add_data( 'llms-builder-styles', 'rtl', 'replace' );
			wp_style_add_data( 'llms-builder-styles', 'suffix', LLMS_ASSETS_SUFFIX );

			wp_enqueue_style( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.2.15' );
			wp_style_add_data( 'webui-popover', 'suffix', LLMS_ASSETS_SUFFIX );

			wp_enqueue_script( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_style( 'llms-datetimepicker', LLMS_PLUGIN_URL . 'assets/vendor/datetimepicker/jquery.datetimepicker.min.css', array(), '1.3.4' );
			wp_enqueue_script( 'llms-datetimepicker', LLMS_PLUGIN_URL . 'assets/vendor/datetimepicker/jquery.datetimepicker.full' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '1.3.4', true );

			if ( apply_filters( 'llms_builder_use_heartbeat', true ) ) {
				wp_enqueue_script( 'heartbeat' );
			}

			wp_enqueue_script( 'llms-builder', LLMS_PLUGIN_URL . 'assets/js/llms-builder' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'backbone', 'underscore', 'post', 'llms-quill' ), LLMS()->version, true );

		} elseif ( 'lifterlms_page_llms-add-ons' === $screen->id ) {

			wp_enqueue_script( 'llms-addons', LLMS_PLUGIN_URL . '/assets/js/llms-admin-addons' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms' ), LLMS()->version, true );

		}

	}

	/**
	 * Initialize the "llms" object for other scripts to hook into
	 *
	 * @return void
	 * @since    1.0.0
	 * @version  3.7.5
	 */
	public function admin_print_scripts() {

		global $post;
		if ( ! empty( $post ) ) {

			$postdata = array(
				'id'        => $post->ID,
				'post_type' => $post->post_type,
			);

		} else {

			$postdata = array();

		}

		echo '
			<script type="text/javascript">
				window.llms = window.llms || {};
				window.llms.admin_url = "' . admin_url() . '";
				window.llms.post = ' . json_encode( $postdata ) . ';
			</script>
		';

		echo '<script type="text/javascript">window.LLMS = window.LLMS || {};</script>';
		echo '<script type="text/javascript">window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . LLMS_L10n::get_js_strings( true ) . ';</script>';

	}

	/**
	 * Register Quill CSS & JS
	 *
	 * @return   void
	 * @since    3.16.0
	 * @version  3.17.8
	 */
	public static function register_quill( $modules = array() ) {

		if ( ! wp_script_is( 'llms-quill', 'registered' ) ) {

			wp_register_script( 'llms-quill', LLMS_PLUGIN_URL . 'assets/vendor/quill/quill' . LLMS_ASSETS_SUFFIX . '.js', array(), '1.3.5', true );
			wp_register_style( 'llms-quill-bubble', LLMS_PLUGIN_URL . 'assets/vendor/quill/quill.bubble' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.3.5', 'screen' );

		}

		foreach ( $modules as $module ) {

			$url = LLMS_PLUGIN_URL . 'assets/vendor/quill/quill.module.' . $module . LLMS_ASSETS_SUFFIX . '.js';
			wp_register_script( 'llms-quill-' . $module, $url, array( 'llms-quill' ), LLMS()->version, true );

		}

	}

}

return new LLMS_Admin_Assets();
