<?php
/**
 * MailHawk Connect
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.40.0
 * @version 3.40.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_MailHawk class
 *
 * @since 3.40.0
 */
class LLMS_MailHawk extends LLMS_Abstract_Email_Provider {

	/**
	 * LifterLMS MailHawk Partner ID.
	 *
	 * @var int
	 */
	const PARTNER_ID = 3;

	/**
	 * Connector's ID.
	 *
	 * @var string
	 */
	protected $id = 'mailhawk';

	/**
	 * Configures the response returned when `do_remote_install()` is successful.
	 *
	 * @since 3.40.0
	 *
	 * @return array
	 */
	protected function do_remote_install_success() {
		return array(
			'partner_id'   => self::PARTNER_ID,
			'register_url' => esc_url( trailingslashit( MAILHAWK_LICENSE_SERVER_URL ) ),
			'client_state' => esc_html( \MailHawk\Keys::instance()->state() ),
			'redirect_uri' => esc_url( \MailHawk\get_admin_mailhawk_uri() ),
		);
	}

	/**
	 * Retrieve the settings area HTML for the connect button
	 *
	 * @since 3.40.0
	 *
	 * @return string
	 */
	protected function get_connect_setting() {

		if ( $this->is_connected() ) {

			$ret = array(
				__( 'Your site is connected to MailHawk.', 'lifterlms' ),
			);

			$settings_url = esc_url( admin_url( '/tools.php?page=mailhawk' ) );

			if ( function_exists( '\MailHawk\mailhawk_is_suspended' ) && ! \MailHawk\mailhawk_is_suspended() ) {
				$ret[] = sprintf(
					// Translators: %1$s = Opening anchor tag to WP MailHawk Settings; Opening anchor tag to MailHawk.io account page; %2$s = Closing anchor tag.
					__( '%1$sView settings%3$s or %2$smanage your account%3$s.', 'lifterlms' ),
					'<a href="' . $settings_url . '">',
					'<a href="https://mailhawk.io/account/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			} else {
				$ret[] = sprintf(
					// Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag.
					'<em>' . __( 'Email sending is currently disabled. %1$sVisit MailHawk Settings%2$s to enable sending.', 'lifterlms' ) . '</em>',
					'<a href="' . $settings_url . '">',
					'</a>'
				);
			}

			return '<p>' . implode( ' ', $ret ) . '</p>';

		}

		return sprintf( '<button type="button" class="llms-button-outline" id="llms-mailhawk-connect"><span class="dashicons dashicons-email-alt"></span> %s</button>', __( 'Connect MailHawk', 'lifterlms' ) );
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
			// Translators: %s = Anchor tag html linking to MailHawk.io.
			__( 'Never worry about sending email again. %s takes care of everything for you starting for a small monthly fee.', 'lifterlms' ),
			'<a href="https://lifterlikes.com/mailhawk" target="_blank">' . $this->get_title() . '</a>'
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
		return __( 'MailHawk', 'lifterlms' );
	}

	/**
	 * Determines if MailHawk is installed and connected for sending.
	 *
	 * @since 3.40.0
	 *
	 * @return boolean
	 */
	protected function is_connected() {
		return ( function_exists( '\MailHawk\mailhawk_is_connected' ) && \MailHawk\mailhawk_is_connected() );
	}

	/**
	 * Determines if connector plugin is installed
	 *
	 * @since 3.40.0
	 *
	 * @return boolean
	 */
	protected function is_installed() {
		return defined( 'MAILHAWK_VERSION' );
	}

	/**
	 * Output some quick and dirty inline JS.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function output_js( $additional_js = '' ) {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<script>
			jQuery( '#llms-mailhawk-connect' ).on( 'click', function( e ) {

				e.preventDefault();

				LLMS.Spinner.start( jQuery( this ), 'small' );

				var data = {
					action: 'llms_mailhawk_remote_install',
					_llms_mailhawk_nonce: '<?php echo esc_js( wp_create_nonce( 'llms-mailhawk-install' ) ); ?>',
				};

				window.llms.emailConnectors.remoteInstall( jQuery( this ), data, function( res ) {

					window.llms.emailConnectors.registerClient( res.register_url, {
						'mailhawk_plugin_signup': 'yes',
						'state': res.client_state,
						'redirect_uri': res.redirect_uri,
						'partner_id': res.partner_id
					} );

				} );

			} );
		</script>
		<?php
	}
}

return new LLMS_MailHawk();
