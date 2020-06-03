<?php
/**
 * SendWP Connect
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.36.1
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_SendWP class
 *
 * @since 3.36.1
 * @since 3.37.0 Sanitize URLs, clean up jQuery references, add loading feedback when connector button is clicked.
 * @since 3.37.3 Modify the ID used to determine where to splice in SendWP Options.
 * @since [version] Refactor to utiize `LLMS_Abstract_Email_Provider`.
 */
class LLMS_SendWP extends LLMS_Abstract_Email_Provider {

	/**
	 * LifterLMS MailHawk Partner ID.
	 *
	 * @var int
	 */
	const PARTNER_ID = 2007;

	/**
	 * Connector's ID.
	 *
	 * @var string
	 */
	protected $id = 'sendwp';

	/**
	 * Validate installation request and perform the plugin install or return errors.
	 *
	 * This method overrides the parent in order to keep the method public to maintain
	 * backwards compatibility.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Sanitize URLS returned by SendWP functions and add nonce verification.
	 * @since [version] Use parent method.
	 *
	 * @return array
	 */
	public function do_remote_install() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Intentional for backwards compat.

		return parent::do_remote_install();

	}

	/**
	 * Configures the response returned when `do_remote_install()` is successful.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function do_remote_install_success() {
		return array(
			'partner_id'      => self::PARTNER_ID,
			'register_url'    => esc_url( sendwp_get_server_url() . '_/signup' ),
			'client_name'     => esc_url( sendwp_get_client_name() ),
			'client_secret'   => esc_url( sendwp_get_client_secret() ),
			'client_redirect' => esc_url( sendwp_get_client_redirect() ),
		);
	}

	/**
	 * Retrieve description text to be used in the settings area.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_description() {

		return sprintf(
			// Translators: %s = Anchor tag html linking to SendWP.com.
			__( '%s makes WordPress email delivery as simple as a few clicks so you can relax, knowing your important emails are being delivered on time.', 'lifterlms' ),
			'<a href="https://lifterlikes.com/sendwp" target="_blank" rel="noopener noreferrer">' . $this->get_title() . '</a>'
		);

	}

	/**
	 * Retrieve the connector's name / title.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_title() {
		return __( 'SendWP', 'lifterlms' );
	}

	/**
	 * Determine if SendWP is installed and connected for sending.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	protected function is_connected() {
		return ( function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() );
	}

	/**
	 * Determines if connector plugin is installed
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	protected function is_installed() {
		return function_exists( 'sendwp_get_server_url' );
	}

	/**
	 * Get the "Connect" Setting field html.
	 *
	 * @since 3.36.1
	 * @since [version] Abstract methods used to determine if SendWP is connected.
	 *
	 * @return string
	 */
	protected function get_connect_setting() {

		if ( $this->is_connected() ) {

			$ret = array(
				__( 'Your site is connected to SendWP.', 'lifterlms' ),
			);

			if ( function_exists( 'sendwp_forwarding_enabled' ) && sendwp_forwarding_enabled() ) {
				$ret[] = sprintf(
					// Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag.
					__( '%1$sManage your account%2$s.', 'lifterlms' ),
					'<a href="https://sendwp.com/account/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			} else {
				$ret[] = sprintf(
					// Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag.
					'<em>' . __( 'Email sending is currently disabled. %1$sVisit the SendWP Settings%2$s to enable sending..', 'lifterlms' ) . '</em>',
					'<a href="' . admin_url( '/tools.php?page=sendwp' ) . '">',
					'</a>'
				);
			}

			return '<p>' . implode( ' ', $ret ) . '</p>';

		}

		return '<button class="button button-primary" id="llms-sendwp-connect"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> Connect SendWP</button>';

	}

	/**
	 * Determine if inline scripts and styles should be output.
	 *
	 * @since 3.36.1
	 * @since [version] Don't output inline CSS & JS when connected.
	 *
	 * @return bool
	 */
	protected function should_output_inline() {

		// Short circuit if missing unauthorized.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return ( 'lifterlms_page_llms-settings' === $screen->id && 'engagements' === llms_filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) && ! $this->is_connected() );

	}

	/**
	 * Output some quick and dirty inline JS.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Add nonce and replace references to `$` with `jQuery`.
	 * @since [version] Refactored to utilize `window.llms.emailConnectors`.
	 *
	 * @return void
	 */
	public function output_js() {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<script>
			jQuery( '#llms-sendwp-connect' ).on( 'click', function( e ) {

				e.preventDefault();

				LLMS.Spinner.start( jQuery( this ), 'small' );

				var data = {
					action: 'llms_sendwp_remote_install',
					_llms_sendwp_nonce: '<?php echo wp_create_nonce( 'llms-sendwp-install' ); ?>',
				};

				window.llms.emailConnectors.remoteInstall( jQuery( this ), data, function( res ) {

					window.llms.emailConnectors.registerClient( res.register_url, {
						client_name: res.client_name,
						client_secret: res.client_secret,
						client_redirect: res.client_redirect,
						partner_id: res.partner_id,
					} );

				} );

			} );
		</script>
		<?php

	}

}

return new LLMS_SendWP();
