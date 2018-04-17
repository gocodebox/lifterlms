<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Admin Assets Class
* @since    1.0.0
* @version  3.17.4
*/
class LLMS_Admin_Assets {

	/**
	 * allows injecting "min" in file name suffix.
	 * @var string
	 */
	public static $min = '.min'; //'.min';

	/**
	 * Constructor
	 * @since    1.0.0
	 * @version  3.17.4
	 */
	public function __construct() {

		$debug = ( defined( 'SCRIPT_DEBUG' ) ) ? SCRIPT_DEBUG : false;

		self::$min = ( $debug ) ? '' : '.min';

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );

	}

	/**
	 * Determine if the current screen should load LifterLMS assets
	 * @return   boolean
	 * @since    3.7.0
	 * @version  3.7.2
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
		}

		return false;

	}

	/**
	 * Enqueue stylesheets
	 * @return void
	 * @since    1.0.0
	 * @version  3.17.4
	 */
	public function admin_styles() {

		if ( ! $this->is_llms_page() ) {
			return;
		}

		wp_enqueue_style( 'llms-admin-styles', plugins_url( '/assets/css/admin' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		wp_style_add_data( 'llms-admin-styles', 'rtl', 'replace' );
		wp_style_add_data( 'llms-admin-styles', 'suffix', LLMS_Admin_Assets::$min );

		wp_enqueue_style( 'chosen-styles', plugins_url( '/assets/chosen/chosen' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );

		wp_enqueue_style( 'llms-select2-styles', plugins_url( '/assets/select2/css/select2' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );

		$screen = get_current_screen();

		if ( 'lifterlms_page_llms-add-ons' === $screen->id || 'lifterlms_page_llms-settings' === $screen->id ) {
			wp_register_style( 'llms-admin-add-ons', plugins_url( '/assets/css/admin-add-ons' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ), array(), LLMS()->version, 'all' );
			wp_enqueue_style( 'llms-admin-add-ons' );
			wp_style_add_data( 'llms-admin-add-ons', 'rtl', 'replace' );
			wp_style_add_data( 'llms-admin-add-ons', 'suffix', LLMS_Admin_Assets::$min );
		}

	}

	/**
	 * Enqueue scripts
	 * @return   void
	 * @since    1.0.0
	 * @version  3.17.4
	 */
	public function admin_scripts() {

		global $post_type;
		$screen = get_current_screen();

		if ( 'widgets' === $screen->id ) {

			wp_enqueue_script( 'llms-widget-syllabus', plugins_url( '/assets/js/llms-widget-syllabus' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

		}

		wp_register_script( 'llms-metaboxes',  plugins_url( '/assets/js/llms-metaboxes' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'jquery-ui-datepicker' ), LLMS()->version, true );
		wp_register_script( 'llms-select2', plugins_url( '/assets/select2/js/select2' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

		if ( ( post_type_exists( $screen->id ) && post_type_supports( $screen->id, 'llms-membership-restrictions' ) ) || 'dashboard_page_llms-setup' === $screen->id ) {

			wp_enqueue_script( 'llms-select2' );
			wp_enqueue_script( 'llms-metaboxes' );

		}

		$tables = apply_filters( 'llms_load_table_resources_pages', array(
			'course',
			'lifterlms_page_llms-reporting',
			'llms_membership',
		) );
		if ( in_array( $screen->id, $tables ) ) {
			wp_register_script( 'llms-admin-tables',  plugins_url( '/assets/js/llms-admin-tables' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_script( 'llms-admin-tables' );
		}

		if ( $this->is_llms_page() ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_register_script( 'llms-admin-scripts', plugins_url( '/assets/js/llms-admin' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_script( 'llms-admin-scripts' );

			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/flick/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );

			wp_register_script( 'llms',  plugins_url( '/assets/js/llms' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );

			wp_enqueue_script( 'chosen-jquery', plugins_url( 'assets/chosen/chosen.jquery' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_script( 'llms-ajax', plugins_url( '/assets/js/llms-ajax' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );

			wp_enqueue_media();

			wp_register_script( 'top-modal', plugins_url( '/assets/js/vendor/topModal.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_script( 'llms-select2' );

			if ( 'course' == $post_type ) {

				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}

			if ( 'course' == $post_type || 'llms_membership' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-students', plugins_url( '/assets/js/llms-metabox-students' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms-select2' ), LLMS()->version, true );
				wp_enqueue_script( 'llms-metabox-product', plugins_url( '/assets/js/llms-metabox-product' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms', 'top-modal' ), LLMS()->version, true );
				wp_enqueue_script( 'llms-metabox-instructors', plugins_url( '/assets/js/llms-metabox-instructors' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms', 'top-modal' ), LLMS()->version, true );

			}

			if ( 'lesson' == $post_type ) {
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_certificate' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-certificate', plugins_url( '/assets/js/llms-metabox-certificate' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_achievement' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-achievement', plugins_url( '/assets/js/llms-metabox-achievement' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_membership' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_voucher' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-voucher', plugins_url( '/assets/js/llms-metabox-voucher' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			}
			if ( 'llms_coupon' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
			}

			if ( 'lifterlms_page_llms-reporting' === $screen->base || 'lifterlms_page_llms-settings' === $screen->base ) {

				wp_register_script( 'llms-google-charts', 'https://www.gstatic.com/charts/loader.js' );
				wp_register_script( 'llms-analytics', plugins_url( '/assets/js/llms-analytics' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms', 'llms-admin-scripts', 'llms-google-charts' ), LLMS()->version, true );

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
								wp_enqueue_script( 'llms-quiz-attempt-review', plugins_url( '/assets/js/llms-quiz-attempt-review' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms' ), LLMS()->version, true );
							}
						break;

					}
				}
			}// End if().

			wp_enqueue_script( 'top-modal' );

			wp_enqueue_script( 'llms' );
			wp_enqueue_script( 'llms-metaboxes' );

		}// End if().

		if ( 'lifterlms_page_llms-settings' == $screen->id ) {

			wp_enqueue_media();
			wp_enqueue_script( 'llms-admin-settings', plugins_url( '/assets/js/llms-admin-settings' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );

		} elseif ( 'admin_page_llms-course-builder' === $screen->id ) {

			self::register_quill();

			wp_enqueue_editor();

			wp_enqueue_style( 'llms-builder-styles', plugins_url( '/assets/css/builder' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ), array( 'llms-quill-bubble' ), LLMS()->version, 'screen' );
			wp_style_add_data( 'llms-builder-styles', 'rtl', 'replace' );
			wp_style_add_data( 'llms-builder-styles', 'suffix', LLMS_Admin_Assets::$min );

			wp_enqueue_style( 'webui-popover', plugins_url( 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
			wp_style_add_data( 'webui-popover', 'suffix', LLMS_Admin_Assets::$min );

			wp_enqueue_script( 'webui-popover', plugins_url( 'assets/vendor/webui-popover/jquery.webui-popover.min.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_style( 'llms-datetimepicker', plugins_url( 'assets/vendor/datetimepicker/jquery.datetimepicker.min.css', LLMS_PLUGIN_FILE ) );
			wp_enqueue_script( 'llms-datetimepicker', plugins_url( 'assets/vendor/datetimepicker/jquery.datetimepicker.full.min.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );

			if ( apply_filters( 'llms_builder_use_heartbeat', true ) ) {
				wp_enqueue_script( 'heartbeat' );
			}
			wp_enqueue_script( 'llms-builder', plugins_url( '/assets/js/llms-builder' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'backbone', 'underscore', 'post', 'llms-quill' ), LLMS()->version, true );

		}

	}

	/**
	 * Initialize the "llms" object for other scripts to hook into
	 * @return void
	 * @since    1.0.0
	 * @version  3.7.5
	 */
	public function admin_print_scripts() {

		global $post;
		if ( ! empty( $post ) ) {

			$postdata = array(
				'id' => $post->ID,
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
	 * @return   void
	 * @since    3.16.0
	 * @version  3.17.2
	 */
	public static function register_quill( $modules = array() ) {

		$min = ( ! defined( 'WP_DEBUG' ) || WP_DEBUG == false ) ? '.min' : '';

		if ( ! wp_script_is( 'llms-quill', 'registered' ) ) {

			wp_register_script( 'llms-quill',  plugins_url( '/assets/vendor/quill/quill' . $min . '.js', LLMS_PLUGIN_FILE ), array(), '1.3.5', true );
			wp_register_style( 'llms-quill-bubble', plugins_url( '/assets/vendor/quill/quill.bubble' . $min . '.css', LLMS_PLUGIN_FILE ), array(), '1.3.5', 'screen' );

		}

		foreach ( $modules as $module ) {

			$url = plugins_url( '/assets/vendor/quill/quill.module.' . $module . $min . '.js', LLMS_PLUGIN_FILE );
			wp_register_script( 'llms-quill-' . $module, $url, array( 'llms-quill' ), LLMS()->version, true );

		}

	}

}

return new LLMS_Admin_Assets;
