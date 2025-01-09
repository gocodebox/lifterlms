<?php
/**
 * Display a Setup Wizard
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 7.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display a Setup Wizard class.
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling error.
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.14 Ensure redirect to the imported course when a course is imported at setup completion.
 * @since 4.4.4 Method `LLMS_Admin_Setup_Wizard::scripts()` & `LLMS_Admin_Setup_Wizard::output_step_html()` are deprecated with no replacements.
 * @since 4.8.0 Removed private class property "generated_course_id".
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Admin_Setup_Wizard::generator_course_status()` method
 *              - `LLMS_Admin_Setup_Wizard::output_step_html()` method
 *              - `LLMS_Admin_Setup_Wizard::scripts()` method
 *              - `LLMS_Admin_Setup_Wizard::watch_course_generation()` method
 * @since 7.4.0 Abstracted: {@see LLMS_Abstract_Admin_Wizard}.
 */
class LLMS_Admin_Setup_Wizard extends LLMS_Abstract_Admin_Wizard {

	/**
	 * Configure wizard.
	 *
	 * @since 3.0.0
	 * @since 4.4.4 Remove output of inline scripts.
	 * @since 7.4.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id        = 'setup';
		$this->views_dir = LLMS_PLUGIN_DIR . 'includes/admin/views/setup-wizard/';
		$this->title     = esc_html__( 'LifterLMS Setup Wizard', 'lifterlms' );
		$this->steps     = array(
			'intro'    => array(
				'title' => esc_html__( 'Welcome!', 'lifterlms' ),
				'save'  => esc_html__( 'Save & Continue', 'lifterlms' ),
				'skip'  => esc_html__( 'Skip this step', 'lifterlms' ),
			),
			'pages'    => array(
				'title' => esc_html__( 'Page Setup', 'lifterlms' ),
				'save'  => esc_html__( 'Save & Continue', 'lifterlms' ),
				'skip'  => esc_html__( 'Skip this step', 'lifterlms' ),
			),
			'payments' => array(
				'title' => esc_html__( 'Payments', 'lifterlms' ),
				'save'  => esc_html__( 'Save & Continue', 'lifterlms' ),
				'skip'  => esc_html__( 'Skip this step', 'lifterlms' ),
			),
			'coupon'   => array(
				'title' => esc_html__( 'Coupon', 'lifterlms' ),
				'save'  => esc_html__( 'Allow', 'lifterlms' ),
				'skip'  => esc_html__( 'No thanks', 'lifterlms' ),
			),
			'finish'   => array(
				'title' => esc_html__( 'Finish!', 'lifterlms' ),
				'save'  => esc_html__( 'Import Courses', 'lifterlms' ),
				'skip'  => esc_html__( 'Skip this step', 'lifterlms' ),
			),
		);

		$this->add_hooks();

		// Add HTML around importable courses on last step.
		add_action( 'llms_before_importable_course', array( $this, 'output_before_importable_course' ) );
		add_action( 'llms_after_importable_course', array( $this, 'output_after_importable_course' ) );

		// Hide action buttons on importable courses during last step.
		add_filter( 'llms_importable_course_show_action', '__return_false' );

		// Enqueue importer styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_importer' ) );
	}

	/**
	 * Enqueue importer styles.
	 *
	 * @since 7.4.0
	 *
	 * @return bool
	 */
	public function enqueue_importer(): bool {
		if ( 'finish' === $this->get_current_step() ) {
			return llms()->assets->enqueue_style( 'llms-admin-importer' );
		}

		return false;
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
	public function output_before_importable_course( array $course ): void {

		$id = absint( $course['id'] ?? null );
		?>
		<label>
		<div class="llms-switch">
			<input class="llms-toggle llms-toggle-round" id="llms-setup-import-course-<?php echo esc_attr( $id ); ?>" name="llms_setup_course_import_ids[]" value="<?php echo esc_attr( $id ); ?>" type="checkbox">
			<label for="llms-setup-import-course-<?php echo esc_attr( $id ); ?>"><span class="screen-reader-text"><?php esc_attr_e( 'Toggle to import course', 'lifterlms' ); ?>
			</label>
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
	public function output_after_importable_course( array $course ): void {
		echo '</label>';
	}

	/**
	 * Retrieve the redirect URL to use after an import is complete at the conclusion of the wizard.
	 *
	 * If a single course is imported, redirects to that course's edit page, otherwise redirects
	 * to the course post table list sorted by created date with the most recent courses first.
	 *
	 * @since 7.4.0
	 *
	 * @param int[] $course_ids WP_Post IDs of the course(s) generated during the import.
	 * @return string
	 */
	protected function get_completed_url( array $course_ids ): string {

		$count = count( $course_ids );

		if ( 1 === $count ) {
			return get_edit_post_link( $course_ids[0], 'not-display' ) ?? '';
		}

		return admin_url( 'edit.php?post_type=course&orderby=date&order=desc' );
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
		$ret = new WP_Error( 'llms-setup-coupon-save-unknown', esc_html__( 'There was an error saving your data, please try again.', 'lifterlms' ) );

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

		return LLMS_Install::create_pages() ? true : new WP_Error( 'llms-setup-pages-save', esc_html__( 'There was an error saving your data, please try again.', 'lifterlms' ) );
	}

	/**
	 * Save the "Payments" step.
	 *
	 * @since 4.8.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return bool Always returns true.
	 */
	protected function save_payments(): bool {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is verified in `save()`.
		$country = isset( $_POST['country'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'country' ) : get_lifterlms_country();
		update_option( 'lifterlms_country', $country );

		$currency = isset( $_POST['currency'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'currency' ) : get_lifterlms_currency();
		update_option( 'lifterlms_currency', $currency );

		$manual = isset( $_POST['manual_payments'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'manual_payments' ) : 'no';
		update_option( 'llms_gateway_manual_enabled', $manual );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return true;
	}

	/**
	 * Save the "Finish" step.
	 *
	 * @since 4.8.0
	 *
	 * @return WP_Error|int[]|bool Returns an array of generated WP_Post IDs on success, `false` when no import IDs are posted, otherwise returns a WP_Error.
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
}

function llms_load_admin_setup_wizard() {
	return new LLMS_Admin_Setup_Wizard();
}
add_action( 'init', 'llms_load_admin_setup_wizard' );
