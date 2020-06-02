<?php
/**
 * MailHawk Connect
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_MailHawk class
 *
 * @since [version]
 */
class LLMS_MailHawk {

	/**
	 * LifterLMS MailHawk Partner ID.
	 *
	 * @var int
	 */
	const PARTNER_ID = 1;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );

	}

	/**
	 * Initialize the MailHawk Connector
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function init() {

		/**
		 * Disable the MailHawk Connector class and settings
		 *
		 * @since [version]
		 *
		 * @param bool $disabled Whether or not this class is disabled.
		 */
		if ( apply_filters( 'llms_disable_mailhawk', false ) ) {
			return;
		}

		// Disable other email delivery services if MailHawk is already connected.
		if ( $this->is_connected() ) {
			add_filter( 'llms_disable_sendwp', '__return_true' );
		}

		add_filter( 'llms_email_delivery_services', array( $this, 'add_settings' ), 20 );
		add_action( 'admin_print_styles', array( $this, 'output_css' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_js' ) );
		add_action( 'wp_ajax_llms_mailhawk_remote_install', array( $this, 'ajax_callback_remote_install' ) );

	}

	/**
	 * Ajax callback for installing SendWP Plugin.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function ajax_callback_remote_install() {

		$ret = $this->do_remote_install();
		ob_clean();
		wp_send_json( $ret, ! empty( $ret['status'] ) ? $ret['status'] : 200 );

	}

	/**
	 * Add Settings.
	 *
	 * @since [version]
	 *
	 * @param array $settings Existing settings.
	 * @return array
	 */
	public function add_settings( $settings ) {

		// Short circuit if missing authorization.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return $settings;
		}

		$new_settings = array(
			array(
				'id'    => 'mailhawk_title',
				'type'  => 'subtitle',
				'title' => __( 'MailHawk', 'lifterlms' ),
				'desc'  => $this->get_desc_text(),
			),
			array(
				'id'    => 'mailhawk_connect',
				'type'  => 'custom-html',
				'value' => $this->get_connect_setting(),
			),
		);

		return array_merge( $settings, $new_settings );

	}

	/**
	 * Validate installation request and perform the plugin install or return errors.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function do_remote_install() {

		if ( ! llms_verify_nonce( '_llms_mailhawk_nonce', 'llms-mailhawk-install' ) ) {
			return array(
				'code'    => 'llms_mailhawk_install_nonce_failure',
				'message' => esc_html__( 'Security check failed.', 'lifterlms' ),
				'status'  => 401,
			);
		} elseif ( ! current_user_can( 'install_plugins' ) ) {
			return array(
				'code'    => 'llms_mailhawk_install_unauthorized',
				'message' => esc_html__( 'You do not have permission to perform this action.', 'lifterlms' ),
				'status'  => 403,
			);
		}

		$install = $this->install();

		if ( is_wp_error( $install ) ) {
			return array(
				'code'    => $install->get_error_code(),
				'message' => $install->get_error_message(),
				'status'  => 400,
			);
		}

		if ( ! defined( 'MAILHAWK_VERSION' ) ) {
			return array(
				'code'    => 'mailhawk_missing',
				'message' => 'MailHawk not installed.',
				'status'  => 400,
			);
		}

		// You can change this to redirect back to your own plugin...
		$redirect = \MailHawk\get_admin_mailhawk_uri();

		return array(
			'partner_id'   => self::PARTNER_ID,
			'register_url' => esc_url( trailingslashit( MAILHAWK_LICENSE_SERVER_URL ) ),
			'client_state' => esc_html( \MailHawk\Keys::instance()->state() ),
			'redirect_uri' => esc_url( $redirect ),
		);

	}

	/**
	 * Retrieve description text to be used in the settings area.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_desc_text() {

		if ( $this->is_connected() ) {
			return '';
		}

		$link = '<a href="https://lifterlikes.com/mailhawk" target="_blank">MailHawk</a>';
		$desc = '<em>' . __( 'A better way to send email.', 'lifterlms' ) . '</em><br>';

		// Translators: %s = Anchor tag html linking to MailHawk.io.
		$desc .= sprintf( __( 'Never worry about sending email again. %s takes care of everything for you starting at <strong>$14.97 per month</strong>.', 'lifterlms' ), $link );

		return $desc;

	}

	/**
	 * Retrieve the settings area HTML for the connect button
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_connect_setting() {

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

		return sprintf( '<button type="button" class="button button-primary big-button" id="llms-mailhawk-connect"><span class="dashicons dashicons-email-alt"></span> %s</button>', __( 'Connect MailHawk', 'lifterlms' ) );

	}

	/**
	 * Install the plugin via the WP plugin installer.
	 *
	 * @since [version]
	 *
	 * @return boolean|WP_Error Error object or `true` when successful.
	 */
	private function install() {

		$is_mailhawk_installed = false;

		foreach ( get_plugins() as $path => $details ) {
			if ( false === strpos( $path, '/mailhawk.php' ) ) {
				continue;
			}
			$is_mailhawk_installed = true;
			$activate              = activate_plugin( $path );
			if ( is_wp_error( $activate ) ) {
				return $activate;
			}
			break;
		}

		$install = null;
		if ( ! $is_mailhawk_installed ) {

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			// Use the WordPress Plugins API to get the plugin download link.
			$api = plugins_api(
				'plugin_information',
				array(
					'slug' => 'mailhawk',
				)
			);
			if ( is_wp_error( $api ) ) {
				return $api;
			}

			// Use the AJAX upgrader skin to quietly install the plugin.
			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$install  = $upgrader->install( $api->download_link );
			if ( is_wp_error( $install ) ) {
				return $install;
			}

			$activate = activate_plugin( $upgrader->plugin_info() );
			if ( is_wp_error( $activate ) ) {
				return $activate;
			}
		}

		// Final check to see if MailHawk is available.
		if ( ! defined( 'MAILHAWK_VERSION' ) ) {
			return new WP_Error( 'llms_mailhawk_not_found', __( 'MailHawk plugin not found. Please try again.', 'lifterlms' ), $install );
		}

		return true;

	}

	/**
	 * Determines if MailHawk is installed and connected for sending.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	protected function is_connected() {

		return ( function_exists( '\MailHawk\mailhawk_is_connected' ) && \MailHawk\mailhawk_is_connected() );

	}

	/**
	 * Determine if inline scripts and styles should be output.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	protected function should_output_inline() {

		// Short circuit if unauthorized.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return ( 'lifterlms_page_llms-settings' === $screen->id && 'engagements' === llms_filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) && ! $this->is_connected() );

	}

	/**
	 * Output some quick and dirty inline CSS.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_css() {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<style type="text/css">
			#llms-mailhawk-connect {
				font-size: 16px;
				height: auto;
				margin: 0 0 6px;
				padding: 8px 14px;
				position: relative;
			}
			#llms-mailhawk-connect .dashicons {
				margin: -4px 4px 0 0;
				vertical-align: middle;
			}
		</style>
		<?php

	}

	/**
	 * Output some quick and dirty inline JS.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_js() {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<script>

			var $llmsMailHawkBtn = jQuery( '#llms-mailhawk-connect' );
			$llmsMailHawkBtn.on( 'click', function( e ) {
				e.preventDefault();
				LLMS.Spinner.start( $llmsMailHawkBtn, 'small' );
				llms_mailhawk_remote_install();
			} );

			/**
			 * Perform AJAX request to install MailHawk plugin.
			 *
			 * @since [version]
			 *
			 * @return void
			 */
			function llms_mailhawk_remote_install() {
				var data = {
					action: 'llms_mailhawk_remote_install',
					_llms_mailhawk_nonce: '<?php echo wp_create_nonce( 'llms-mailhawk-install' ); ?>',
				};

				jQuery.post( ajaxurl, data, function ( res ) {
					llms_mailhawk_register_client( res.register_url, res.client_state, res.redirect_uri, res.partner_id );
				} ).fail( function( jqxhr ) {
					LLMS.Spinner.stop( $llmsMailHawkBtn );
					$llmsMailHawkBtn.parent().find( '.llms-error' ).remove();
					if ( jqxhr.responseJSON && jqxhr.responseJSON.message ) {
						jQuery( '<p class="llms-error">' + LLMS.l10n.replace( 'Error: %s', { '%s': jqxhr.responseJSON.message } ) + '</p>' ).insertAfter( $llmsMailHawkBtn );
						console.log( jqxhr );
					}
				} );
			}

			/**
			 * Register client with MailHawk.
			 *
			 * @since [version]
			 *
			 * @param {String}  register_url Registration URL.
			 * @param {String}  client_state string state for oauth.
			 * @param {String}  redirect_uri Client redirect URL.
			 * @param {Integer} partner_id   MailHawk partner ID.
			 * @return {Void}
			 */
			function llms_mailhawk_register_client( register_url, client_state, redirect_uri, partner_id ) {

				var form = document.createElement( 'form' );
				form.setAttribute( 'method', 'POST' );
				form.setAttribute( 'action', register_url );

				function llms_mailhawk_append_form_input( name, value ) {
					var input = document.createElement( 'input' );
					input.setAttribute( 'type', 'hidden' );
					input.setAttribute( 'name', name );
					input.setAttribute( 'value', value );
					form.appendChild( input);
				}

				llms_mailhawk_append_form_input( 'mailhawk_plugin_signup', 'yes' );
				llms_mailhawk_append_form_input( 'state', client_state );
				llms_mailhawk_append_form_input( 'redirect_uri', redirect_uri );
				llms_mailhawk_append_form_input( 'partner_id', partner_id );

				document.body.appendChild( form );
				form.submit();

			}
		</script>
		<?php

	}

}

return new LLMS_MailHawk();
