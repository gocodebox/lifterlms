<?php
/**
 * Display a Wizard
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display a Wizard class.
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Admin_Wizard {

	/**
	 * Wizard ID.
	 *
	 * @since [version]
	 * @var string
	 */
	protected string $id;

	/**
	 * Views directory.
	 *
	 * @since [version]
	 * @var string
	 */
	protected string $views_dir;

	/**
	 * Steps.
	 *
	 * @since [version]
	 * @var array
	 */
	protected array $steps;

	/**
	 * Page title.
	 *
	 * @since [version]
	 * @var string
	 */
	protected string $title;

	/**
	 * Error message.
	 *
	 * @since [version]
	 * @var WP_Error|null
	 */
	protected ?WP_Error $error = null;

	/**
	 * Add hooks.
	 *
	 * @since [version]
	 * @return void
	 */
	protected function add_hooks(): void {

		/**
		 * Whether the LifterLMS Wizard is enabled.
		 *
		 * This filter may be used to entirely disable the setup wizard.
		 *
		 * The dynamic portion of this filter, `{$this->id}`, refers to the wizard type. E.g. "setup".
		 *
		 * @since [version]
		 *
		 * @param boolean $enabled Whether the wizard is enabled.
		 */
		if ( ! apply_filters( "llms_enable_{$this->id}_wizard", true ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
		add_filter( 'llms_admin_show_header', array( $this, 'hide_admin_header' ), 10, 3 );
	}

	/**
	 * Hide the admin header on the wizard pages.
	 *
	 * @since [version]
	 *
	 * @param bool      $show   Whether to show the admin header.
	 * @param WP_Screen $screen Current admin screen.
	 * @param string    $page   Current admin page.
	 * @return bool
	 */
	public function hide_admin_header( bool $show, WP_Screen $screen, string $page ): bool {
		if ( "llms-{$this->id}" === $page ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Register wizard setup page.
	 *
	 * @since [version]
	 *
	 * @return string The hook suffix of the setup wizard page ("admin_page_llms-setup"), or `false` if the user does not have the capability required.
	 */
	public function admin_menu(): string {

		/**
		 * Filter the WP User capability required to access and run the setup wizard.
		 *
		 * The dynamic portion of this filter, `{$this->id}`, refers to the wizard type. E.g. "setup".
		 *
		 * @since [version]
		 *
		 * @param string $cap Required user capability. Default value is `install_plugins`.
		 */
		$cap = apply_filters( "llms_{$this->id}_wizard_access", 'install_plugins' );

		$hook = add_dashboard_page(
			$this->title,
			'',
			$cap,
			'llms-' . $this->id,
			array( $this, 'output' )
		);

		update_option( 'lifterlms_first_time_' . $this->id, 'yes' );

		return $hook;
	}

	/**
	 * Enqueue static assets for the setup wizard screens.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function enqueue(): bool {

		if ( ! isset( $_GET['page'] ) || 'llms-' . $this->id !== $_GET['page'] ) {
			return '';
		}

		return llms()->assets->enqueue_script( 'llms-admin-wizard' ) && llms()->assets->enqueue_style( 'llms-admin-wizard' );

	}

	/**
	 * Retrieve the redirect URL at the conclusion of the wizard.
	 *
	 * @since [version]
	 *
	 * @param int[] $course_ids WP_Post IDs of the course(s) generated during the import.
	 * @return string
	 */
	abstract protected function get_completed_url( array $course_ids ): string;

	/**
	 * Retrieve the current step and default to the intro.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_current_step(): string {
		$step_keys = array_keys( $this->get_steps() );

		$step = sanitize_text_field( wp_unslash( $_GET['step'] ?? '' ) );

		return $step ? llms_filter_input_sanitize_string( INPUT_GET, 'step' ) : $step_keys[0]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get slug if next step.
	 *
	 * @since [version]
	 *
	 * @param ?string $step Step to use as current.
	 * @return string
	 */
	public function get_next_step( string $step = null ): string {
		$step = $step ?? $this->get_current_step();
		$keys = array_keys( $this->get_steps() );
		$i    = array_search( $step, $keys, true );

		// Next step doesn't exist or the next step would be greater than the index of the last step.
		if ( false === $i || $i + 1 >= count( $keys ) ) {
			return false;
		}

		return $keys[ ++$i ] ?? '';
	}

	/**
	 * Get slug if prev step.
	 *
	 * @since [version]
	 *
	 * @param ?string $step Step to use as current.
	 * @return string
	 */
	public function get_prev_step( string $step = null ): string {
		$step = $step ?? $this->get_current_step();
		$keys = array_keys( $this->get_steps() );
		$i    = array_search( $step, $keys, true );

		if ( false === $i || $i - 1 < 0 ) {
			return false;
		}

		return $keys[ $i - 1 ] ?? '';
	}

	/**
	 * Get the text to display on the "save" buttons.
	 *
	 * @since [version]
	 *
	 * @param string $step Step to get text for.
	 * @return string The translated text.
	 */
	private function get_save_text( string $step ): string {

		/**
		 * Filter the Save button text for a given step in the setup wizard.
		 *
		 * The first dynamic portion of this hook, `$this->id`, refers to the type of wizard being displayed.
		 *
		 * The second dynamic portion of this hook, `$step`, refers to the slug of the current step.
		 *
		 * @since [version]
		 *
		 * @param string $text Button text string.
		 */
		return apply_filters( "llms_{$this->id}_wizard_get_{$step}_save_text", $this->get_steps()[ $step ]['save'] ?? '' );
	}

	/**
	 * Get the text to display on the "skip" buttons.
	 *
	 * @since [version]
	 *
	 * @param string $step Step to get text for.
	 * @return string Translated text.
	 */
	private function get_skip_text( string $step ): string {

		/**
		 * Filter the skip button text for a given step in the setup wizard.
		 *
		 * The first dynamic portion of this hook, `$this->id`, refers to the type of wizard being displayed.
		 *
		 * The second dynamic portion of this hook, `$step`, refers to the slug of the current step.
		 *
		 * @since [version]
		 *
		 * @param string $text Button text string.
		 */
		return apply_filters( "llms_{$this->id}_wizard_get_{$step}_skip_text", $this->get_steps()[ $step ]['skip'] ?? '' );

	}

	/**
	 * Get the URL to a step.
	 *
	 * @since [version]
	 *
	 * @param string $step Step slug.
	 * @return string
	 */
	protected function get_step_url( string $step ): string {

		$args = array(
			'page' => 'llms-' . $this->id,
			'step' => $step,
		);

		return add_query_arg( $args, admin_url() );
	}

	/**
	 * Get an array of step slugs => titles.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_steps(): array {

		/**
		 * Filter the steps included in the setup wizard.
		 *
		 * The dynamic portion of this hook, `$this->id`, refers to the type of wizard being displayed.
		 *
		 * @since [version]
		 *
		 * @param string[] $steps Array of setup wizard steps. The array key is the slug/id of the step and the array value
		 *                        is the step's title displayed in the wizard's navigation.
		 */
		return apply_filters( "llms_{$this->id}_wizard_steps", $this->steps );

	}

	/**
	 * Output the HTML content of the setup page.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output(): void {
		$views_dir = trailingslashit( esc_attr( $this->views_dir ) );
		$step_html = '';
		$steps     = $this->get_steps();
		$current   = $this->get_current_step() ?? 'intro';
		$prev      = $this->get_prev_step();
		$next      = $this->get_next_step();
		$transient = $this->get_transient();

		if ( in_array( $current, array_keys( $steps ), true ) ) {

			ob_start();
			include $views_dir . 'step-' . $current . '.php';
			$step_html = ob_get_clean();

		}

		/**
		 * Filter the HTML of a step within the setup wizard.
		 *
		 * The first dynamic portion of this hook, `$this->id`, refers to the type of wizard being displayed.
		 *
		 * The second dynamic portion of this hook, `$current`, refers to the slug of the current step.
		 *
		 * This filter can be used to output the HTML for a custom step in the setup wizard.
		 *
		 * @since [version]
		 *
		 * @param string                  $step_html HTML of the step.
		 * @param LLMS_Admin_Setup_Wizard $wizard    Setup wizard class instance.
		 */
		$step_html = apply_filters( "llms_{$this->id}_wizard_{$current}_html", $step_html, $this );

		include $views_dir . 'main.php';

	}

	/**
	 * Handle saving data during setup.
	 *
	 * @since [version]
	 *
	 * @return null|WP_Error
	 */
	public function save() {

		if ( ! isset( $_POST['llms_setup_nonce'] ) || ! llms_verify_nonce( 'llms_setup_nonce', 'llms_setup_save' ) || ! current_user_can( 'manage_lifterlms' ) ) {
			return null;
		}

		$response = new WP_Error( 'llms-setup-save-invalid', __( 'There was an error saving your data, please try again.', 'lifterlms' ) );

		$step = llms_filter_input( INPUT_POST, 'llms_setup_save' );

		if ( method_exists( $this, 'save_' . $step ) ) {
			$response = $this->{"save_{$step}"}();
		}

		if ( is_wp_error( $response ) ) {
			$this->error = $response;
			return $response;
		}

		if ( ! is_array( $response ) ) {
			$response = array( $response );
		}

		$url = ( 'finish' === $step ) ? $this->get_completed_url( $response ) : $this->get_step_url( $this->get_next_step() );

		try {
			llms_redirect_and_exit( $url );
		} catch ( Exception $exception ) {
			return new WP_Error( "llms-{$this->id}-save-redirect", $exception->getMessage() );
		}

		return null;
	}

	/**
	 * Returns wizard transient if set.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_transient(): array {
		return array();
	}

}
