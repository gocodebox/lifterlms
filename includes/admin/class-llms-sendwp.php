<?php
/**
 * SendWP Connect
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.36.1
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_SendWP class
 *
 * @since 3.36.1
 * @since 3.37.0 Sanitize URLs, clean up jQuery references, add loading feedback when connector button is clicked.
 * @since 3.37.3 Modify the ID used to determine where to splice in SendWP Options.
 * @since 3.40.0 Refactor to utilize `LLMS_Abstract_Email_Provider`.
 * @since 6.0.0 Removed `LLMS_SendWP::do_remote_install()` in favor of `LLMS_Abstract_Email_Provider::do_remote_install()`.
 */
class LLMS_SendWP extends LLMS_Abstract_Email_Provider {

	/**
	 * LifterLMS SendWP Partner ID.
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
	 * Configures the response returned when `do_remote_install()` is successful.
	 *
	 * @since 3.40.0
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
	 * @since 3.40.0
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
	 * @since 3.40.0
	 *
	 * @return string
	 */
	protected function get_title() {
		return __( 'SendWP', 'lifterlms' );
	}

	/**
	 * Determine if SendWP is installed and connected for sending.
	 *
	 * @since 3.40.0
	 *
	 * @return boolean
	 */
	protected function is_connected() {
		return ( function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() );
	}

	/**
	 * Determines if connector plugin is installed
	 *
	 * @since 3.40.0
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
	 * @since 3.40.0 Abstract methods used to determine if SendWP is connected.
	 * @since 5.3.2 Update the URL for managing an account.
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
					'<a href="https://app.sendwp.com/dashboard" target="_blank" rel="noopener noreferrer">',
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

		return '<button class="llms-button-outline" id="llms-sendwp-connect"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> Connect SendWP</button>';
	}

	/**
	 * Output some quick and dirty inline JS.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Add nonce and replace references to `$` with `jQuery`.
	 * @since 3.40.0 Refactored to utilize `window.llms.emailConnectors`.
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
					_llms_sendwp_nonce: '<?php echo esc_js( wp_create_nonce( 'llms-sendwp-install' ) ); ?>',
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
