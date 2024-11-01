<?php
/**
 * LLMS_Admin_Assets class
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 1.0.0
 * @version 7.4.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register and enqueue admin assets.
 *
 * @since 1.0.0
 */
class LLMS_Admin_Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 3.17.5 Unknown.
	 * @since 6.0.0 Add hooks for admin inline footer scripts, inline header styles, and block editor assets.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'elementor_editor_assets' ) );
	}

	/**
	 * Output inline scripts in the admin footer.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function admin_print_footer_scripts() {
		llms()->assets->output_inline( 'footer' );
	}

	/**
	 * Output inline styles in the header.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function admin_print_styles() {
		llms()->assets->output_inline( 'style' );
	}

	/**
	 * Enqueue assets for the block editor.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function block_editor_assets() {

		$screen = get_current_screen();
		if ( $screen && $screen->is_block_editor && in_array( $screen->post_type, array( 'llms_certificate', 'llms_my_certificate' ), true ) ) {
			$this->block_editor_assets_for_certificates();
		}
	}

	public function elementor_editor_assets() {
		if ( isset( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] ) && 'course' === get_post_type( intval( $_REQUEST['post'] ) ) ) {
			llms()->assets->enqueue_script( 'llms-admin-elementor-editor' );
			wp_localize_script( 'llms-admin-elementor-editor', 'llms_elementor', array( 'builder_url' => admin_url( 'admin.php?page=llms-course-builder&course_id=' . intval( $_REQUEST['post'] ) ) ) );
		}
	}

	/**
	 * Enqueue block editor assets for certificate post types.
	 *
	 * @since 6.0.0
	 * @since 6.5.0 Use `wp_slash()` after `wp_json_encode()` to prevent issues encountered when strings contain single quotes.
	 *
	 * @return void
	 */
	private function block_editor_assets_for_certificates() {

		llms()->assets->enqueue_script( 'llms-admin-certificate-editor' );

		$settings = array(
			'default_image' => llms()->certificates()->get_default_image( get_the_ID() ),
			'sizes'         => llms_get_certificate_sizes(),
			'orientations'  => llms_get_certificate_orientations(),
			'units'         => llms_get_certificate_units(),
			'colors'        => array(
				array(
					'name'  => __( 'White', 'lifterlms' ),
					'slug'  => 'white',
					'color' => '#ffffff',
				),
				array(
					'name'  => __( 'White Smoke', 'lifterlms' ),
					'slug'  => 'white-smoke',
					'color' => '#f5f5f5',
				),
				array(
					'name'  => __( 'Ivory', 'lifterlms' ),
					'slug'  => 'ivory',
					'color' => '#fffff0',
				),
			),
			'merge_codes'   => llms_get_certificate_merge_codes(),
		);
		llms()->assets->enqueue_inline(
			'llms-admin-certificate-settings',
			"window.llms = window.llms || {};window.llms.certificates=JSON.parse( '" . wp_slash( wp_json_encode( $settings ) ) . "' );",
			'footer'
		);

		global $wp_version;
		$supports_fonts = version_compare( $wp_version, '5.9-src', '>=' );

		$fonts = $supports_fonts ? llms_get_certificate_fonts() : new stdClass();

		$styles = '';
		foreach ( $fonts as $id => $data ) {

			if ( ! empty( $data['href'] ) ) {
				wp_enqueue_style( 'llms-font-' . $id, $data['href'], array(), LLMS_VERSION );
			}

			$css     = $data['fontFamily'];
			$styles .= ".editor-styles-wrapper .has-{$id}-font-family { font-family: {$css} !important }\n";
		}

		llms()->assets->enqueue_inline(
			'llms-admin-certificate-styles',
			$styles,
			'style'
		);
	}

	/**
	 * Determine if the current screen should load LifterLMS assets
	 *
	 * @since 3.7.0
	 * @since 3.19.4 Unknown.
	 *
	 * @return bool
	 */
	public function is_llms_page() {

		$screen = get_current_screen();

		$id = str_replace( 'edit-', '', $screen->id );

		if ( false !== strpos( $id, 'lifterlms' ) ) {
			return true;
		} elseif ( false !== strpos( $id, 'llms' ) ) {
			return true;
		} elseif ( in_array( $id, array( 'course', 'lesson' ), true ) ) {
			return true;
		} elseif ( ! empty( $screen->post_type ) && post_type_supports( $screen->post_type, 'llms-membership-restrictions' ) ) {
			return true;
		} elseif ( in_array( $screen->id, array( 'users' ), true ) ) {
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
	 * @since 5.0.0 Use `LLMS_Assets` for registration/enqueue of styles.
	 * @since 5.5.0 Use `LLMS_Assets` for the enqueue of `llms-addons`.
	 * @since 7.2.0 Use `LLMS_ASSETS_VERSION` for asset versions.
	 *
	 * @return void
	 */
	public function admin_styles() {

		wp_enqueue_style( 'llms-admin-styles', LLMS_PLUGIN_URL . 'assets/css/admin' . LLMS_ASSETS_SUFFIX . '.css', array(), LLMS_ASSETS_VERSION );
		wp_style_add_data( 'llms-admin-styles', 'rtl', 'replace' );
		wp_style_add_data( 'llms-admin-styles', 'suffix', LLMS_ASSETS_SUFFIX );

		if ( ! $this->is_llms_page() ) {
			return;
		}

		llms()->assets->enqueue_style( 'llms-select2-styles' );

		$screen = get_current_screen();

		if ( 'lifterlms_page_llms-add-ons' === $screen->id || 'lifterlms_page_llms-dashboard' === $screen->id ) {
			llms()->assets->enqueue_style( 'llms-admin-add-ons' );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @since 3.22.0 Unknown.
	 * @since 3.35.0 Explicitly set asset versions.
	 * @since 3.35.1 Don't reference external scripts & styles.
	 * @since 4.3.3 Move logic for reporting/analytics scripts to `maybe_enqueue_reporting()`.
	 * @since 4.4.0 Enqueue the main `llms` script.
	 * @since 5.0.0 Clean up duplicate references to llms-select2 and register the script using `LLMS_Assets`.
	 *              Remove topModal vendor dependency.
	 *              Add `llms-admin-forms` on the forms post table screen.
	 * @since 5.5.0 Use `LLMS_Assets` for the enqueue of `llms-admin-add-ons`.
	 * @since 6.0.0 Enqueue certificate and achievement related js in `llms_my_certificate`, `llms_my_achievement` post types as well.
	 * @since 7.1.0 Enqueue `postbox` script on the new dashboard page.
	 * @since 7.2.0 Use `LLMS_ASSETS_VERSION` for asset versions.
	 *              Enqueue reporting scripts on dashboard page.
	 * @since 7.4.1 Enqueue `postbox` script on the new resources page.
	 *
	 * @return void
	 */
	public function admin_scripts() {

		global $post_type, $post;
		$screen = get_current_screen();

		if ( 'widgets' === $screen->id ) {

			wp_enqueue_script( 'llms-widget-syllabus', LLMS_PLUGIN_URL . 'assets/js/llms-widget-syllabus' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );

		}

		llms()->assets->register_script( 'llms-select2' );
		wp_register_script( 'llms-metaboxes', LLMS_PLUGIN_URL . 'assets/js/llms-metaboxes' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-datepicker', 'llms-admin-scripts', 'llms-select2' ), LLMS_ASSETS_VERSION, true );

		if ( ( post_type_exists( $screen->id ) && post_type_supports( $screen->id, 'llms-membership-restrictions' ) ) || 'dashboard_page_llms-setup' === $screen->id ) {
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
		if ( in_array( $screen->id, $tables, true ) ) {
			wp_register_script( 'llms-admin-tables', LLMS_PLUGIN_URL . 'assets/js/llms-admin-tables' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			wp_enqueue_script( 'llms-admin-tables' );
		}

		wp_register_script( 'llms', LLMS_PLUGIN_URL . 'assets/js/llms' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
		wp_register_script( 'llms-admin-scripts', LLMS_PLUGIN_URL . 'assets/js/llms-admin' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'llms-select2' ), LLMS_ASSETS_VERSION, true );

		if ( $this->is_llms_page() ) {

			llms()->assets->enqueue_script( 'llms' );

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_script( 'llms-admin-scripts' );

			wp_register_style( 'jquery-ui-flick', LLMS_PLUGIN_URL . 'assets/vendor/jquery-ui-flick/jquery-ui-flick' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.11.2' );
			wp_enqueue_style( 'jquery-ui-flick' );

			wp_enqueue_script( 'llms-ajax', LLMS_PLUGIN_URL . 'assets/js/llms-ajax' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );

			wp_enqueue_media();

			if ( 'course' === $post_type ) {

				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
				if ( ! use_block_editor_for_post_type( 'course' ) && $post ) {
					wp_enqueue_script( 'llms-launch-course-button', LLMS_PLUGIN_URL . 'assets/js/llms-launch-course-button' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
					wp_localize_script( 'llms-launch-course-button', 'llms_launch_course', array( 'builder_url' => admin_url( 'admin.php?page=llms-course-builder&course_id=' . intval( $post->ID ) ) ) );
				}
			}

			if ( 'course' === $post_type || 'llms_membership' === $post_type ) {

				wp_enqueue_script( 'llms-metabox-students', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-students' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms-select2' ), LLMS_ASSETS_VERSION, true );
				wp_enqueue_script( 'llms-metabox-product', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-product' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms' ), LLMS_ASSETS_VERSION, true );
				wp_enqueue_script( 'llms-metabox-instructors', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-instructors' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms' ), LLMS_ASSETS_VERSION, true );

			}

			if ( 'lesson' === $post_type ) {
				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			}
			if ( in_array( $post_type, array( 'llms_certificate', 'llms_my_certificate' ), true ) ) {

				wp_enqueue_script( 'llms-metabox-certificate', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-certificate' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			}
			if ( in_array( $post_type, array( 'llms_achievement', 'llms_my_achievement' ), true ) ) {

				wp_enqueue_script( 'llms-metabox-achievement', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-achievement' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			}
			if ( 'llms_membership' === $post_type ) {
				wp_enqueue_script( 'llms-metabox-fields', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-fields' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			}
			if ( 'llms_voucher' === $post_type ) {

				wp_enqueue_script( 'llms-metabox-voucher', LLMS_PLUGIN_URL . 'assets/js/llms-metabox-voucher' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			}

			$this->maybe_enqueue_reporting( $screen );

			wp_enqueue_script( 'llms' );
			wp_enqueue_script( 'llms-metaboxes' );

			// Load forms advert/compat script.
			if ( 'edit-llms_form' === $screen->id ) {
				llms()->assets->enqueue_script( 'llms-admin-forms' );
			}
		}

		if ( 'dashboard' === $screen->base ) {
			$this->maybe_enqueue_reporting( $screen );
		}

		if ( 'lifterlms_page_llms-settings' === $screen->id ) {

			wp_enqueue_media();
			wp_enqueue_script( 'llms-admin-settings', LLMS_PLUGIN_URL . 'assets/js/llms-admin-settings' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-sortable' ), LLMS_ASSETS_VERSION, true );

		} elseif ( 'admin_page_llms-course-builder' === $screen->id ) {

			self::register_quill();

			wp_enqueue_editor();

			wp_enqueue_style( 'llms-builder-styles', LLMS_PLUGIN_URL . 'assets/css/builder' . LLMS_ASSETS_SUFFIX . '.css', array( 'llms-quill-bubble' ), LLMS_ASSETS_VERSION, 'screen' );
			wp_style_add_data( 'llms-builder-styles', 'rtl', 'replace' );
			wp_style_add_data( 'llms-builder-styles', 'suffix', LLMS_ASSETS_SUFFIX );

			wp_enqueue_style( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.2.15' );
			wp_style_add_data( 'webui-popover', 'suffix', LLMS_ASSETS_SUFFIX );

			wp_enqueue_script( 'webui-popover', LLMS_PLUGIN_URL . 'assets/vendor/webui-popover/jquery.webui-popover' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), LLMS_ASSETS_VERSION, true );
			wp_enqueue_style( 'llms-datetimepicker', LLMS_PLUGIN_URL . 'assets/vendor/datetimepicker/jquery.datetimepicker.min.css', array(), '1.3.4' );
			wp_enqueue_script( 'llms-datetimepicker', LLMS_PLUGIN_URL . 'assets/vendor/datetimepicker/jquery.datetimepicker.full' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery' ), '1.3.4', true );

			if ( apply_filters( 'llms_builder_use_heartbeat', true ) ) {
				wp_enqueue_script( 'heartbeat' );
			}

			wp_enqueue_script( 'llms-builder', LLMS_PLUGIN_URL . 'assets/js/llms-builder' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'backbone', 'underscore', 'post', 'llms-quill' ), LLMS_ASSETS_VERSION, true );

		} elseif ( 'lifterlms_page_llms-add-ons' === $screen->id ) {
			llms()->assets->enqueue_script( 'llms-addons' );
		} elseif ( in_array( $screen->id, array( 'lifterlms_page_llms-dashboard', 'lifterlms_page_llms-resources' ), true ) ) {
			wp_enqueue_script( 'postbox' );
		}

		if (
			'edit-llms_my_certificate' === $screen->id ||
			(
				'lifterlms_page_llms-reporting' === $screen->id &&
				'students' === llms_filter_input( INPUT_GET, 'tab' ) &&
				'certificates' === llms_filter_input( INPUT_GET, 'stab' )
			)
		) {
			llms()->assets->enqueue_script( 'llms-admin-award-certificate' );
			wp_enqueue_style( 'wp-editor' );
		}
	}

	/**
	 * Initialize the "llms" object for other scripts to hook into
	 *
	 * @since 1.0.0
	 * @since 3.7.5 Unknown.
	 * @since 4.4.0 Add `ajax_nonce`.
	 * @since 4.5.1 Add an analytics localization object.
	 * @since 5.0.0 Output Form location information as a window variable for block editor utilization.
	 * @since 5.9.0 Use `wp_slash()` after `wp_json_encode()` to prevent issues encountered when strings contain single quotes.
	 * @since 7.1.1 Add `home_url`.
	 *
	 * @return void
	 */
	public function admin_print_scripts() {

		$screen = get_current_screen();

		global $post;

		$postdata = array();

		if ( ! empty( $post ) ) {

			$postdata = array(
				'id'        => $post->ID,
				'post_type' => $post->post_type,
			);

		}

		echo '
			<script type="text/javascript">
				window.llms = window.llms || {};
				window.llms.ajax_nonce = "' . esc_attr( wp_create_nonce( LLMS_AJAX::NONCE ) ) . '";
				window.llms.admin_url = "' . esc_url( admin_url() ) . '";
				window.llms.home_url = "' . esc_url( home_url() ) . '";
				window.llms.post = ' . wp_json_encode( $postdata ) . ';
				window.llms.analytics = ' . wp_json_encode( $this->get_analytics_options() ) . ';
			</script>
		';

		echo '<script type="text/javascript">window.LLMS = window.LLMS || {};</script>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_js_strings returns json_encoded strings.
		echo '<script type="text/javascript">window.LLMS.l10n = window.LLMS.l10n || {}; window.LLMS.l10n.strings = ' . wp_json_encode( LLMS_L10n::get_js_strings( false ) ) . ';</script>';

		$forms = LLMS_Forms::instance()->get_post_type();

		if ( $forms === $screen->id ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in `wp_slash()`.and wp_json_encode()
			echo "<script>window.llms.formLocations = JSON.parse( '" . wp_slash( wp_json_encode( LLMS_Forms::instance()->get_locations() ) ) . "' );</script>";
		}

		if ( ! empty( $screen->is_block_editor ) || 'customize' === $screen->base ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in `wp_slash()`.and wp_json_encode()
			echo "<script>window.llms.userInfoFields = JSON.parse( '" . wp_slash( wp_json_encode( llms_get_user_information_fields_for_editor() ) ) . "' );</script>";
		}
	}

	/**
	 * Retrieve an array of options used to localize the `llms.analytics` JS instance.
	 *
	 * @since 4.5.1
	 *
	 * @return array
	 */
	protected function get_analytics_options() {

		/**
		 * Create a number format string readable by google charts
		 *
		 * Replacing `9.9` with `9,9` and `0,0` with `0.0` to prevent loading errors encountered
		 * as a result of the chart pattern not allowing usage of a comma for the decimal separator.
		 *
		 * @see https://stackoverflow.com/a/18204679/400568
		 */
		$currency_format = str_replace( array( '9.9', '0,0', '9' ), array( '9,9', '0.0', '#' ), llms_price_raw( 9990.00 ) );

		/**
		 * Customize Javascript localization options passed to the `llms.analytics` JS instance.
		 *
		 * @since 4.5.1
		 *
		 * @param array $opts Associative array of option data.
		 */
		return apply_filters( 'llms_get_analytics_js_options', compact( 'currency_format' ) );
	}

	/**
	 * Register and enqueue scripts used on and related-to reporting and analytics
	 *
	 * @since 4.3.3
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.2.0 Load on dashboard screen.
	 *              Use `LLMS_ASSETS_VERSION` for asset versions.
	 *
	 * @param WP_Sreen $screen Screen object from WP `get_current_screen()`.
	 * @return void
	 */
	protected function maybe_enqueue_reporting( $screen ) {

		if ( in_array( $screen->base, array( 'lifterlms_page_llms-reporting', 'lifterlms_page_llms-dashboard', 'dashboard' ), true ) ) {

			$current_tab = llms_filter_input( INPUT_GET, 'tab' );

			wp_register_script( 'llms-google-charts', LLMS_PLUGIN_URL . 'assets/js/vendor/gcharts-loader.min.js', array(), '2019-09-04', false );
			wp_register_script( 'llms-analytics', LLMS_PLUGIN_URL . 'assets/js/llms-analytics' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms', 'llms-admin-scripts', 'llms-google-charts' ), LLMS_ASSETS_VERSION, true );

			// Dashboard page where we have analytics widgets.
			if ( in_array( $screen->base, array( 'lifterlms_page_llms-dashboard', 'dashboard' ), true ) ) {

				wp_enqueue_script( 'llms-analytics' );

			} elseif ( 'lifterlms_page_llms-reporting' === $screen->base ) {

				if ( in_array( $current_tab, array( 'enrollments', 'sales' ), true ) ) {
					wp_enqueue_script( 'llms-analytics' );
				} elseif ( 'quizzes' === $current_tab && 'attempts' === llms_filter_input( INPUT_GET, 'stab' ) ) {
					wp_enqueue_script( 'llms-quiz-attempt-review', LLMS_PLUGIN_URL . 'assets/js/llms-quiz-attempt-review' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'llms' ), LLMS_ASSETS_VERSION, true );
				}
			}
		}
	}

	/**
	 * Register Quill CSS & JS
	 *
	 * @since 3.16.0
	 * @since 3.17.8 Unknown.
	 * @since 6.10.0 Load modules using `llms()->assets`.
	 *
	 * @return void
	 */
	public static function register_quill( $modules = array() ) {

		if ( ! wp_script_is( 'llms-quill', 'registered' ) ) {
			wp_register_script( 'llms-quill', LLMS_PLUGIN_URL . 'assets/vendor/quill/quill' . LLMS_ASSETS_SUFFIX . '.js', array(), '2.0.2', true );
			wp_register_style( 'llms-quill-bubble', LLMS_PLUGIN_URL . 'assets/vendor/quill/quill.bubble' . LLMS_ASSETS_SUFFIX . '.css', array(), '1.3.5', 'screen' );
		}

		foreach ( $modules as $module ) {
			llms()->assets->register_script( "llms-quill-{$module}" );
		}
	}
}

return new LLMS_Admin_Assets();
