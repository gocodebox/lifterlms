<?php
/**
 * LLMS_Forms_Unsupported_Versions file
 *
 * @package LifterLMS/Classes/Forms
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles admin interface changes when forms cannot be managed with the block editor
 *
 * The file, class, and all class methods will be removed without warning when the overall supported
 * WordPress version is 5.7. Class methods are public in order to function within the WordPress API
 * but should be considered private for this reason.
 *
 * @since 5.0.0
 *
 * @access private
 */
class LLMS_Forms_Unsupported_Versions {

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function __construct() {

		if ( LLMS_Forms::instance()->are_requirements_met() ) {
			return;
		}

		add_action( 'current_screen', array( $this, 'init' ) );
	}

	/**
	 * Add actions depending on the current screen
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function init() {

		$screen = get_current_screen();

		if ( 'edit-llms_form' === $screen->id ) {

			add_action( 'admin_print_styles', array( $this, 'print_styles' ) );
			add_action( 'admin_notices', array( $this, 'output_notice' ) );

		} elseif ( 'llms_form' === $screen->id ) {

			llms_redirect_and_exit( admin_url( 'edit.php?post_type=llms_form' ) );

		}
	}

	/**
	 * Output an admin error notice alerting users when requirements are not met.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function output_notice() {
		?>
		<div class="notice notice-error">
			<p><b><?php esc_html_e( 'Minimum Version Requirements Error', 'lifterlms' ); ?></b></p>
			<p><?php printf( esc_html__( 'In order to manage LifterLMS Forms you must upgrade to at least WordPress version %s or later or install the latest version of the Gutenberg plugin.', 'lifterlms' ), esc_html( LLMS_Forms::instance()::MIN_WP_VERSION ) ); ?></p>
			<p><?php esc_html_e( 'If you do not upgrade, your forms will display properly on the frontend and users will be able to create accounts, enroll, and checkout but you will be unable to customize them.', 'lifterlms' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output dirty inline CSS to prevent interaction with the posts table list
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function print_styles() {
		echo '<style type="text/css" id="llms-forms-unsupported-styles">#the-list { pointer-events: none; filter: blur( 1px ); }</style>';
	}
}

return new LLMS_Forms_Unsupported_Versions();
