<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Assets Class
*
* Sets up the enqueue scripts and styles for the Admin pages.
* TODO: register scripts. make page ids a db option.
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Assets {

	/**
	* allows injecting "min" in file name suffix.
	* @access public
	* @var string
	*/
	public static $min = '.min'; //'.min';

	/**
	* Constructor
	*
	* executes enqueue functions on admin_enqueue_scripts
	*/
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );

	}

	/**
	* Returns array of the page ids we want to enqueue scripts on.
	*
	* @return   array
	* @since    1.0.0
	* @version  3.1.0
	*/
	public function get_llms_admin_page_ids() {
		$screen_id = 'lifterlms';

	    return apply_filters( 'lifterlms_admin_page_ids', array(
	    	$screen_id . '_page_llms-settings',
	    	'llms-settings',
	    	$screen_id . '_page_llms-reporting',
	    	'llms-reporting',
	    	$screen_id . '_page_llms-students',
	    	'llms-students',
	    	'course',
	    	'edit-course',
	    	'edit-course_cat',

	    	'lesson',
	    	'edit-lesson',

	    	'section',
	    	'edit-section',

	    	'llms_certificate',
	    	'edit-llms_certificate',

	    	'llms_engagement',
	    	'edit-llms_engagement',

	    	'llms_achievement',
	    	'edit-llms_achievement',

	    	'llms_membership',
	    	'edit-llms_membership',

	    	'llms_order',
	    	'edit-llms_order',

	    	'llms_quiz',
	    	'edit-llms_quiz',

	    	'llms_question',
	    	'edit-llms_question',

	    	'llms_email',
	    	'edit-llms_email',

			'llms_voucher',
			'edit-llms_voucher',
			'llms_coupon',
	    ));
	}

	/**
	* Enqueue stylesheets
	*
	* @return void
	*/
	public function admin_styles() {

		wp_enqueue_style( 'llms-admin-styles', plugins_url( '/assets/css/admin' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		wp_enqueue_style( 'chosen-styles', plugins_url( '/assets/chosen/chosen' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );
		wp_enqueue_style( 'llms-select2-styles', plugins_url( '/assets/select2/css/select2' . LLMS_Admin_Assets::$min . '.css', LLMS_PLUGIN_FILE ) );

		$screen = get_current_screen();
		if ( 'lifterlms_page_llms-add-ons' === $screen->id ) {
			wp_register_style( 'llms-admin-add-ons', plugins_url( '/assets/css/admin-add-ons.min.css', LLMS_PLUGIN_FILE ), array(), LLMS()->version, 'all' );
			wp_enqueue_style( 'llms-admin-add-ons' );
		}

	}

	/**
	* Enqueue scripts
	*
	* @return void
	*/
	public function admin_scripts() {
		global $post_type;
		$screen = get_current_screen();

		if ( 'widgets' === $screen->id ) {

			wp_enqueue_script( 'llms-widget-syllabus', plugins_url( '/assets/js/llms-widget-syllabus' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

		}

		wp_register_script( 'llms-metaboxes',  plugins_url( '/assets/js/llms-metaboxes' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'jquery-ui-datepicker' ), '', true );
		wp_register_script( 'llms-select2', plugins_url( '/assets/select2/js/select2' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

		if ( ( post_type_exists( $screen->id ) && post_type_supports( $screen->id, 'llms-membership-restrictions' ) ) || 'dashboard_page_llms-setup' === $screen->id ) {

			wp_enqueue_script( 'llms-select2' );
			wp_enqueue_script( 'llms-metaboxes' );

		}

		$tables = array(
			'course',
			'lifterlms_page_llms-reporting',
			'llms_membership',
		);
		if ( in_array( $screen->id, $tables ) ) {
			wp_register_script( 'llms-admin-tables',  plugins_url( '/assets/js/llms-admin-tables' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), LLMS()->version, true );
			wp_enqueue_script( 'llms-admin-tables' );
		}

		if ( in_array( $screen->id, LLMS_Admin_Assets::get_llms_admin_page_ids() ) ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_register_script( 'llms-admin-scripts', plugins_url( '/assets/js/llms-admin' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			wp_enqueue_script( 'llms-admin-scripts' );

			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/flick/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );

			wp_register_script( 'llms',  plugins_url( '/assets/js/llms' . LLMS_Frontend_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_script( 'chosen-jquery', plugins_url( 'assets/chosen/chosen.jquery' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_script( 'llms-ajax', plugins_url( '/assets/js/llms-ajax' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			wp_enqueue_script( 'llms-metabox', plugins_url( '/assets/js/llms-metabox' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_media();

			wp_register_script( 'top-modal', plugins_url( '/assets/js/vendor/topModal.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );

			wp_enqueue_script( 'llms-select2' );

			if ( 'course' == $post_type ) {

				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-syllabus', plugins_url( '/assets/js/llms-metabox-syllabus' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}

			if ( 'course' == $post_type || 'llms_membership' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-students', plugins_url( '/assets/js/llms-metabox-students' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms-select2' ), '', true );
				wp_enqueue_script( 'llms-metabox-product', plugins_url( '/assets/js/llms-metabox-product' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'top-modal' ), '', true );

			}

			if ( 'lesson' == $post_type ) {
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_certificate' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-certificate', plugins_url( '/assets/js/llms-metabox-certificate' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_achievement' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-achievement', plugins_url( '/assets/js/llms-metabox-achievement' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_membership' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-fields', plugins_url( '/assets/js/llms-metabox-fields' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_question' == $post_type ) {
				wp_enqueue_script( 'llms-metabox-single-question', plugins_url( '/assets/js/llms-metabox-single-question' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_quiz' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
				wp_enqueue_script( 'llms-metabox-quiz-builder', plugins_url( '/assets/js/llms-metabox-quiz-builder' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms-select2' ), '', true );
			}
			if ( 'llms_voucher' == $post_type ) {

				wp_enqueue_script( 'llms-metabox-voucher', plugins_url( '/assets/js/llms-metabox-voucher' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery' ), '', true );
			}
			if ( 'llms_coupon' == $post_type ) {
				wp_enqueue_script( 'llms-select2' );
			}

			if ( 'lifterlms_page_llms-reporting' === $screen->base || 'lifterlms_page_llms-settings' === $screen->base ) {

				wp_register_script( 'llms-google-charts', 'https://www.gstatic.com/charts/loader.js' );
				wp_register_script( 'llms-analytics', plugins_url( '/assets/js/llms-analytics' . LLMS_Admin_Assets::$min . '.js', LLMS_PLUGIN_FILE ), array( 'jquery', 'llms-admin-scripts', 'llms-google-charts' ), '', true );

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
					}

				}

			}

			wp_enqueue_script( 'top-modal' );

			wp_enqueue_script( 'llms' );
			wp_enqueue_script( 'llms-metaboxes' );

		}

	}

	/**
	 * Initialize the "llms" object for other scripts to hook into
	 * @return void
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
		echo '<script type="text/javascript">window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . LLMS_l10n::get_js_strings( true ) . ';</script>';

	}

}

return new LLMS_Admin_Assets;
