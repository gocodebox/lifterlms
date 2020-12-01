<?php
/**
 * Display a Setup Wizard
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 4.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display a Setup Wizard class
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling error.
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.14 Ensure redirect to the imported course when a course is imported at setup completion.
 * @since 4.4.4 Method `LLMS_Admin_Setup_Wizard::scripts()` & `LLMS_Admin_Setup_Wizard::output_step_html()` are deprecated with no replacements.
 * @since 4.8.0 Removed private class property "generated_course_id".
 */
class LLMS_Admin_Setup_Wizard {

	/**
	 * Instance of WP_Error
	 *
	 * @var WP_Error
	 */
	public $error;

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 * @since 4.4.4 Remove output of inline scripts.
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Whether or not the LifterLMS Setup Wizard is enabled.
		 *
		 * This filter may be used to entirely disable the setup wizard.
		 *
		 * @since 3.0.0
		 *
		 * @param boolean $enabled Whether or not the wizard is enabled.
		 */
		if ( apply_filters( 'llms_enable_setup_wizard', true ) ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'save' ) );

			// Add HTML around importable courses on last step.
			add_action( 'llms_before_importable_course', array( $this, 'output_before_importable_course' ) );
			add_action( 'llms_after_importable_course', array( $this, 'output_after_importable_course' ) );

			// Hide action buttons on importable courses during last step.
			add_filter( 'llms_importable_course_show_action', '__return_false' );

		}

	}

	/**
	 * Register wizard setup page
	 *
	 * @since 3.0.0
	 * @since 4.4.4 Added dashboard page title.
	 *
	 * @return string The hook suffix of the setup wizard page ("admin_page_llms-setup"), or `false` if the user does not have the capability required.
	 */
	public function admin_menu() {

		/**
		 * Filter the WP User capability required to access and run the setup wizard.
		 *
		 * @since 3.0.0
		 *
		 * @param string $cap Required user capability. Default value is `install_plugins`.
		 */
		$cap = apply_filters( 'llms_setup_wizard_access', 'install_plugins' );

		$hook = add_dashboard_page( __( 'LifterLMS Setup Wizard', 'lifterlms' ), '', $cap, 'llms-setup', array( $this, 'output' ) );

		update_option( 'lifterlms_first_time_setup', 'yes' );

		return $hook;

	}

	/**
	 * Enqueue static assets for the setup wizard screens
	 *
	 * @since 3.0.0
	 * @since 3.17.8 Unknown.
	 * @since 4.4.4 Use `LLMS_Assets` for asset registration and queuing.
	 * @since 4.8.0 Add return boolean based on enqueue return instead of void.
	 *
	 * @return boolean
	 */
	public function enqueue() {

		$extra = true;

		if ( 'finish' === $this->get_current_step() ) {
			$extra = llms()->assets->enqueue_style( 'llms-admin-importer' );
		}

		return llms()->assets->enqueue_script( 'llms-admin-setup' ) && llms()->assets->enqueue_style( 'llms-admin-setup' ) && $extra;

	}

	/**
	 * Retrieve the redirect URL to use after an import is complete at the conclusion of the wizard
	 *
	 * If a single course is imported, redirects to that course's edit page, otherwise redirects
	 * to the course post table list sorted by created date with the most recent courses first.
	 *
	 * @since 4.8.0
	 *
	 * @param int[] $course_ids WP_Post IDs of the course(s) generated during the import.
	 * @return string
	 */
	protected function get_completed_url( $course_ids ) {

		$count = count( $course_ids );

		if ( 1 === $count ) {
			return get_edit_post_link( $course_ids[0], 'not-display' );
		}

		return admin_url( 'edit.php?post_type=course&orderby=date&order=desc' );

	}

	/**
	 * Retrieve the current step and default to the intro
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return string
	 */
	public function get_current_step() {
		return empty( $_GET['step'] ) ? 'intro' : llms_filter_input( INPUT_GET, 'step', FILTER_SANITIZE_STRING ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get slug if next step
	 *
	 * @since 3.0.0
	 * @since 4.8.0 Combined combined if/elseif into a single condition & use strict `array_search()` comparison.
	 *
	 * @param string $step Step to use as current.
	 * @return string|false
	 */
	public function get_next_step( $step = '' ) {

		$step = $step ? $step : $this->get_current_step();
		$keys = array_keys( $this->get_steps() );
		$i    = array_search( $step, $keys, true );

		// Next step doesn't exist or the next step would be greater than the index of the last step.
		if ( false === $i || $i + 1 >= count( $keys ) ) {
			return false;
		}

		return $keys[ ++$i ];
	}

	/**
	 * Get slug if prev step
	 *
	 * @since 3.0.0
	 * @since 4.8.0 Combined combined if/elseif into a single condition & use strict `array_search()` comparison.
	 *
	 * @param string $step Step to use as current.
	 * @return string|false
	 */
	public function get_prev_step( $step = '' ) {

		$step = $step ? $step : $this->get_current_step();
		$keys = array_keys( $this->get_steps() );
		$i    = array_search( $step, $keys, true );

		if ( false === $i || $i - 1 < 0 ) {
			return false;
		}

		return $keys[ $i - 1 ];
	}

	/**
	 * Get the text to display on the "save" buttons
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Unknown.
	 * @since 4.8.0 Added a filter on the return value.
	 *
	 * @param string $step Step to get text for.
	 * @return string The translated text.
	 */
	private function get_save_text( $step ) {

		$text = __( 'Save & Continue', 'lifterlms' );

		if ( 'coupon' === $step ) {
			$text = __( 'Allow', 'lifterlms' );
		} elseif ( 'finish' === $step ) {
			$text = __( 'Import Courses', 'lifterlms' );
		}

		/**
		 * Filter the Save button text for a given step in the setup wizard
		 *
		 * The dynamic portion of this hook, `$step`, refers to the slug of the current step.
		 *
		 * @since 4.8.0
		 *
		 * @param string $text Button text string.
		 */
		return apply_filters( "llms_setup_wizard_get_{$step}_save_text", $text );
	}

	/**
	 * Get the text to display on the "skip" buttons
	 *
	 * @since 3.0.0
	 * @since 4.8.0 Added a filter on the return value.
	 *
	 * @param string $step Step to get text for.
	 * @return string Translated text.
	 */
	private function get_skip_text( $step ) {

		$text = __( 'Skip this step', 'lifterlms' );

		if ( 'coupon' === $step ) {
			$text = __( 'No thanks', 'lifterlms' );
		}

		/**
		 * Filter the skip button text for a given step in the setup wizard
		 *
		 * The dynamic portion of this hook, `$step`, refers to the slug of the current step.
		 *
		 * @since 4.8.0
		 *
		 * @param string $text Button text string.
		 */
		return apply_filters( "llms_setup_wizard_get_{$step}_skip_text", $text );

	}

	/**
	 * Get the URL to a step
	 *
	 * @since 3.0.0
	 *
	 * @param string $step Step slug.
	 * @return string
	 */
	private function get_step_url( $step ) {
		return add_query_arg(
			array(
				'page' => 'llms-setup',
				'step' => $step,
			),
			admin_url()
		);
	}

	/**
	 * Get an array of step slugs => titles
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_steps() {

		$steps = array(
			'intro'    => __( 'Welcome!', 'lifterlms' ),
			'pages'    => __( 'Page Setup', 'lifterlms' ),
			'payments' => __( 'Payments', 'lifterlms' ),
			'coupon'   => __( 'Coupon', 'lifterlms' ),
			'finish'   => __( 'Finish!', 'lifterlms' ),
		);

		/**
		 * Filter the steps included in the setup wizard
		 *
		 * @since 4.8.0
		 *
		 * @param string[] $steps Array of setup wizard steps. The array key is the slug/id of the step and the array value
		 *                        is the step's title displayed in the wizard's navigation.
		 */
		return apply_filters( 'llms_setup_wizard_steps', $steps );

	}

	/**
	 * Output the HTML content of the setup page
	 *
	 * @since 3.0.0
	 * @since 3.16.14 Unknown.
	 * @since 4.8.0 Refactored to include HTML from a view file instead of hardcoded HTML into the method.
	 *
	 * @return void
	 */
	public function output() {

		$step_html = '';
		$steps     = $this->get_steps();
		$current   = $this->get_current_step();
		$prev      = $this->get_prev_step();
		$next      = $this->get_next_step();

		if ( in_array( $current, array_keys( $this->get_steps() ), true ) ) {

			ob_start();
			include LLMS_PLUGIN_DIR . 'includes/admin/views/setup-wizard/step-' . $current . '.php';
			$step_html = ob_get_clean();

		}

		/**
		 * Filter the HTML of a step within the setup wizard.
		 *
		 * The dynamic portion of this hook, `$current`, refers to the slug of the current step.
		 *
		 * This filter can be used to output the HTML for a custom step in the setup wizard.
		 *
		 * @since 4.8.0
		 *
		 * @param string                  $step_html HTML of the step.
		 * @param LLMS_Admin_Setup_Wizard $wizard    Setup wizard class instance.
		 */
		$step_html = apply_filters( "llms_setup_wizard_{$current}_html", $step_html, $this );

		include LLMS_PLUGIN_DIR . 'includes/admin/views/setup-wizard/main.php';

	}

	/**
	 * Output HTML prior to each importable course
	 *
	 * Adds an opening label wrapper and adds HTML data to turn the element into a toggleable form element.
	 *
	 * @since 4.8.0
	 *
	 * @param array $course Importable course data array.
	 * @return void
	 */
	public function output_before_importable_course( $course ) {

		$id = absint( $course['id'] );
		?>
		<label>
			<div class="llms-switch">
				<input class="llms-toggle llms-toggle-round" id="llms-setup-import-course-<?php echo $id; ?>" name="llms_setup_course_import_ids[]" value="<?php echo $id; ?>" type="checkbox">
				<label for="llms-setup-import-course-<?php echo $id; ?>"><span class="screen-reader-text"><?php _e( 'Toggle to import course', 'lifterlms' ); ?></label>
			</div>
		<?php

	}

	/**
	 * Output HTML after to each importable course
	 *
	 * Closes the label element opened in `output_before_importable_course()`.
	 *
	 * @since 4.8.0
	 *
	 * @param array $course Importable course data array.
	 * @return void
	 */
	public function output_after_importable_course( $course ) {
		echo '</label>';
	}

	/**
	 * Handle saving data during setup
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Unknown.
	 * @since 3.35.0 Sanitize input data; load sample data from `sample-data` directory.
	 * @since 3.37.14 Ensure redirect to proper course when a course is imported at the end of setup.
	 * @since 4.8.0 Moved logic for each wizard step into it's own method.
	 *
	 * @return null|WP_Error
	 */
	public function save() {

		if ( ! isset( $_POST['llms_setup_nonce'] ) || ! llms_verify_nonce( 'llms_setup_nonce', 'llms_setup_save' ) || ! current_user_can( 'manage_lifterlms' ) ) {
			return null;
		}

		$res = new WP_Error( 'llms-setup-save-invalid', __( 'There was an error saving your data, please try again.', 'lifterlms' ) );

		$step = llms_filter_input( INPUT_POST, 'llms_setup_save', FILTER_SANITIZE_STRING );
		if ( method_exists( $this, 'save_' . $step ) ) {
			$res = call_user_func( array( $this, 'save_' . $step ) );
		}

		if ( is_wp_error( $res ) ) {
			$this->error = $res;
			return $res;
		}

		$url = ( 'finish' === $step ) ? $this->get_completed_url( $res ) : $this->get_step_url( $this->get_next_step() );

		return llms_redirect_and_exit( $url );

	}

	/**
	 * Save the "Coupon" step
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Error|boolean Returns `true` on success otherwise returns a WP_Error.
	 */
	protected function save_coupon() {

		update_option( 'llms_allow_tracking', 'yes' );
		$req = LLMS_Tracker::send_data( true );

		$ret = new WP_Error( 'llms-setup-coupon-save-unknown', __( 'There was an error saving your data, please try again.', 'lifterlms' ) );

		if ( is_wp_error( $req ) ) {
			$ret = $req;
		} elseif ( empty( $req['success'] ) && isset( $req['message'] ) ) {
			$ret = new WP_Error( 'llms-setup-coupon-save-tracking-api', $req['message'] );
		} elseif ( ! empty( $req['success'] ) && true === $req['success'] ) {
			$ret = true;
		}

		return $ret;

	}

	/**
	 * Save the "Pages" creation step
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Error|boolean Returns `true` on success otherwise returns a WP_Error.
	 */
	protected function save_pages() {

		return LLMS_Install::create_pages() ? true : new WP_Error( 'llms-setup-pages-save', __( 'There was an error saving your data, please try again.', 'lifterlms' ) );

	}

	/**
	 * Save the "Payments" step.
	 *
	 * @since 4.8.0
	 *
	 * @return boolean Always returns true
	 */
	protected function save_payments() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is verified in `save()`.
		$country = isset( $_POST['country'] ) ? llms_filter_input( INPUT_POST, 'country', FILTER_SANITIZE_STRING ) : get_lifterlms_country();
		update_option( 'lifterlms_country', $country );

		$currency = isset( $_POST['currency'] ) ? llms_filter_input( INPUT_POST, 'currency', FILTER_SANITIZE_STRING ) : get_lifterlms_currency();
		update_option( 'lifterlms_currency', $currency );

		$manual = isset( $_POST['manual_payments'] ) ? llms_filter_input( INPUT_POST, 'manual_payments', FILTER_SANITIZE_STRING ) : 'no';
		update_option( 'llms_gateway_manual_enabled', $manual );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return true;

	}

	/**
	 * Save the "Finish" step.
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Error|int[]|boolaen Returns an array of generated WP_Post IDs on success, `false` when no import IDs are posted, otherwise returns a WP_Error.
	 */
	protected function save_finish() {

		$ids = (array) llms_filter_input( INPUT_POST, 'llms_setup_course_import_ids', FILTER_DEFAULT, FILTER_FORCE_ARRAY );
		$ids = array_filter( array_map( 'absint', $ids ) );
		if ( ! $ids ) {
			return false;
		}

		$res = LLMS_Export_API::get( $ids );
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$gen = new LLMS_Generator( $res );
		$gen->set_generator();
		$gen->generate();

		if ( $gen->is_error() ) {
			return $gen->get_results();
		}

		return $gen->get_generated_courses();

	}

	/**
	 * Allow the Sample Content installed during the final step to be published rather than drafted
	 *
	 * @since 3.3.0
	 * @deprecated 4.8.0 LLMS_Admin_Setup_Wizard::generator_course_status() is deprecated with no replacement.
	 *
	 * @param string $status Post status.
	 * @return string
	 */
	public function generator_course_status( $status ) {
		llms_deprecated_function( 'LLMS_Admin_Setup_Wizard::generator_course_status()', '4.8.0' );
		return 'publish';
	}

	/**
	 * Outputs the HTML "body" for the requested step
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Fixed spelling error.
	 * @deprecated 4.4.4
	 *
	 * @param string $step Step slug.
	 * @return void
	 */
	public function output_step_html( $step ) {
		llms_deprecated_function( 'LLMS_Admin_Setup_Wizard::output_step_html()', '4.4.4' );
	}

	/**
	 * Quick and dirty JS "file"
	 *
	 * @since 3.0.0
	 * @deprecated 4.4.4
	 *
	 * @return void
	 */
	public function scripts() {
		llms_deprecated_function( 'LLMS_Admin_Setup_Wizard::scripts()', '4.4.4' );
	}

	/**
	 * Callback function to store imported course information
	 *
	 * Uses this to handle redirect after import and generation is completed.
	 *
	 * @since 3.37.14
	 * @deprecated 4.8.0 LLMS_Admin_Setup_Wizard::watch_course_generation() is deprecated with no replacement.
	 *
	 * @param LLMS_Course $course Course object.
	 * @return void
	 */
	public function watch_course_generation( $course ) {
		llms_deprecated_function( 'LLMS_Admin_Setup_Wizard::watch_course_generation()', '4.8.0' );
	}

}

return new LLMS_Admin_Setup_Wizard();
