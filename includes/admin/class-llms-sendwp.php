<?php
/**
 * SendWP Connect.
 *
 * @package  LifterLMS/Admin/Classes
 *
 * @since 3.36.1
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_SendWP class..
 *
 * @since 3.36.1
 */
class LLMS_SendWP {

	/**
	 * Constructor.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Disable the SendWP Connector class and settings
		 *
		 * @since 3.36.1
		 *
		 * @param bool $disabled Whether or not this class is disabled.
		 */
		if ( apply_filters( 'llms_disable_sendwp', false ) ) {
			return;
		}

		add_filter( 'lifterlms_engagements_settings', array( $this, 'add_settings' ) );
		add_action( 'admin_print_styles', array( $this, 'output_css' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_js' ) );
		add_action( 'wp_ajax_llms_sendwp_remote_install', array( $this, 'ajax_callback_remote_install' ) );

	}

	/**
	 * Add Settings.
	 *
	 * @since 3.36.1
	 *
	 * @param array $settings Existing settings.
	 * @return array
	 */
	public function add_settings( $settings ) {

		// Short circuit if missing unauthorized.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return $settings;
		}

		$new_settings = array(
			array(
				'id'   => 'sendwp_connect_start',
				'type' => 'sectionstart',
			),
			array(
				'id'    => 'sendwp_title',
				'title' => __( 'SendWP Email', 'lifterlms' ),
				'type'  => 'title',
				'desc'  => sprintf(
					// Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag.
					__( '%1$sSendWP%2$s makes WordPress email delivery as simple as a few clicks so you can relax, knowing your important emails are being delivered on time.', 'lifterlms' ),
					'<a href="https://lifterlikes.com/sendwp" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
			),
			array(
				'id'    => 'sendwp_connect',
				'type'  => 'custom-html',
				'value' => $this->get_connect_setting(),
			),
			array(
				'id'   => 'sendwp_connect_end',
				'type' => 'sectionend',
			),
		);

		array_splice( $settings, $this->get_splice_index( $settings ), 0, $new_settings );

		return $settings;

	}

	/**
	 * Ajax callback for installing SendWP Plugin.
	 *
	 * @since 3.36.1
	 *
	 * @hook wp_ajax_llms_sendwp_remote_install
	 *
	 * @return void
	 */
	public function ajax_callback_remote_install() {

		$ret = $this->do_remote_install();
		ob_clean();
		wp_send_json( $ret, ! empty( $ret['status'] ) ? $ret['status'] : 200 );

	}

	/**
	 * Remote installation method.
	 *
	 * @since 3.36.1
	 *
	 * @return array
	 */
	public function do_remote_install() {

		if ( ! current_user_can( 'install_plugins' ) ) {
			return array(
				'code'    => 'llms_sendwp_install_unauthorized',
				'message' => __( 'You do not have permission to perform this action.', 'lifterlms' ),
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

		return array(
			'partner_id'      => 2007,
			'register_url'    => sendwp_get_server_url() . '_/signup',
			'client_name'     => sendwp_get_client_name(),
			'client_secret'   => sendwp_get_client_secret(),
			'client_redirect' => sendwp_get_client_redirect(),
		);

	}

	/**
	 * Install / Activate SendWP plugin.
	 *
	 * @since 3.36.1
	 *
	 * @return WP_Error|true
	 */
	private function install() {

		$is_sendwp_installed = false;
		foreach ( get_plugins() as $path => $details ) {
			if ( false === strpos( $path, '/sendwp.php' ) ) {
				continue;
			}
			$is_sendwp_installed = true;
			$activate            = activate_plugin( $path );
			if ( is_wp_error( $activate ) ) {
				return $activate;
			}
			break;
		}

		$install = null;
		if ( ! $is_sendwp_installed ) {

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			// Use the WordPress Plugins API to get the plugin download link.
			$api = plugins_api(
				'plugin_information',
				array(
					'slug' => 'sendwp',
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

		// Final check to see if SendWP is available.
		if ( ! function_exists( 'sendwp_get_server_url' ) ) {
			return new WP_Error( 'llms_sendwp_not_found', __( 'SendWP Plugin not found. Please try again.', 'lifterlms' ), $install );
		}

		return true;

	}

	/**
	 * Find the end of the "email_options" section to splice in new settings.
	 *
	 * @since 3.36.1
	 *
	 * @param array $settings Default engagement settings.
	 * @return int
	 */
	private function get_splice_index( $settings ) {
		foreach ( $settings as $i => $setting ) {
			if ( 'email_options' === $setting['id'] && 'sectionend' === $setting['type'] ) {
				return $i + 1;
			}
		}
		return $i;
	}

	/**
	 * Get the "Connect" Setting field html.
	 *
	 * @since 3.36.1
	 *
	 * @return string
	 */
	private function get_connect_setting() {

		if ( function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() ) {

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
	 *
	 * @return bool
	 */
	protected function should_output_inline() {

		// Short circuit if missing unauthorized.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return ( 'lifterlms_page_llms-settings' === $screen->id && 'engagements' === llms_filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) );

	}

	/**
	 * Output some quick and dirty inline CSS.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function output_css() {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<style type="text/css">
			#llms-sendwp-connect {
				font-size: 16px;
				height: auto;
				margin: 0 0 6px;
				padding: 8px 14px;
			}
			#llms-sendwp-connect .fa {
				margin-right: 4px;
			}
		</style>
		<?php
	}


	/**
	 * Output some quick and dirty inline JS.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function output_js() {

		if ( ! $this->should_output_inline() ) {
			return;
		}

		?>
		<script>
			var btn = document.getElementById( 'llms-sendwp-connect' )
			btn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				llms_sendwp_remote_install();
			} );

			/**
			 * Perform AJAX request to install SendWP plugin.
			 *
			 * @since 3.36.1
			 *
			 * @return void
			 */
			function llms_sendwp_remote_install() {
				var data = {
					'action': 'llms_sendwp_remote_install',
				};
				jQuery.post( ajaxurl, data, function( res ) {
					llms_sendwp_register_client( res.register_url, res.client_name, res.client_secret, res.client_redirect, res.partner_id );
				} ).fail( function( jqxhr ) {
					$( btn ).parent().find( '.llms-error' ).remove();
					if ( jqxhr.responseJSON && jqxhr.responseJSON.message ) {
						$( '<p class="llms-error">' + LLMS.l10n.replace( 'Error: %s', { '%s': jqxhr.responseJSON.message } ) + '</p>' ).insertAfter( $( btn ) );
						console.log( jqxhr );
					}
				} );
			}

			/**
			 * Register client with SendWP.
			 *
			 * @since 3.36.1
			 *
			 * @param {string} register_url Registration URL.
			 * @param {string} client_name Client name.
			 * @param {string} client_secret Client secret.
			 * @param {string} client_redirect Client redirect URL.
			 * @param {int} partner_id SendWP partner ID.
			 * @return {void}
			 */
			function llms_sendwp_register_client( register_url, client_name, client_secret, client_redirect, partner_id ) {

				var form = document.createElement( 'form' );
				form.setAttribute( 'method', 'POST' );
				form.setAttribute( 'action', register_url );

				function llms_sendwp_append_form_input( name, value ) {
					var input = document.createElement( 'input' );
					input.setAttribute( 'type', 'hidden' );
					input.setAttribute( 'name', name );
					input.setAttribute( 'value', value );
					form.appendChild( input );
				}

				llms_sendwp_append_form_input( 'client_name', client_name );
				llms_sendwp_append_form_input( 'client_secret', client_secret );
				llms_sendwp_append_form_input( 'client_redirect', client_redirect );
				llms_sendwp_append_form_input( 'partner_id', partner_id );

				document.body.appendChild( form );
				form.submit();

			}
		</script>
		<?php

	}

}

return new LLMS_SendWP();
