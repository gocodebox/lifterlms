<?php
/**
 * Base class used by email delivery provider "connector" classes
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.40.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Email_Provider
 *
 * @since 3.40.0
 * @since 6.0.0 Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
 */
abstract class LLMS_Abstract_Email_Provider {

	/**
	 * Connector's ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Array of supported providers.
	 *
	 * @var array
	 */
	protected $providers = array(
		'mailhawk',
		'sendwp',
	);

	/**
	 * Configures the response returned when `do_remote_install()` is successful.
	 *
	 * @since 3.40.0
	 *
	 * @return array
	 */
	abstract protected function do_remote_install_success();

	/**
	 * Retrieve the settings area HTML for the connect button
	 *
	 * @since 3.40.0
	 *
	 * @return string
	 */
	abstract protected function get_connect_setting();

	/**
	 * Retrieve description text to be used in the settings area.
	 *
	 * @since 3.40.0
	 *
	 * @return string
	 */
	abstract protected function get_description();

	/**
	 * Retrieve the connector's name / title.
	 *
	 * @since 3.40.0
	 *
	 * @return string
	 */
	abstract protected function get_title();

	/**
	 * Determines if connector plugin is connected for sending.
	 *
	 * @since 3.40.0
	 *
	 * @return boolean
	 */
	abstract protected function is_connected();

	/**
	 * Determines if connector plugin is installed
	 *
	 * @since 3.40.0
	 *
	 * @return boolean
	 */
	abstract protected function is_installed();

	/**
	 * Constructor.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Filter the available email providers
		 *
		 * @since 3.40.0
		 *
		 * @param string[] $this->providers List of email provider ids.
		 */
		$this->providers = apply_filters( 'llms_email_delivery_providers', $this->providers );

		/**
		 * Dynamically adjust the priority.
		 *
		 * A "connected" provider will always load first, ensuring
		 * that it can disable the other providers.
		 *
		 * When no providers are connected, they'll all load at 10
		 * and display in alphabetical order as a result of the order
		 * the files are included.
		 */
		$priority = $this->is_connected() ? 5 : 10;
		add_action( 'admin_init', array( $this, 'init' ), $priority );

	}

	/**
	 * Initialize the Connector
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function init() {

		// Disable other email delivery services if the current connector is already connected.
		if ( $this->is_connected() ) {
			$this->disable_other_providers();
		}

		/**
		 * Disable the Connector class and settings
		 *
		 * The dynamic portion of this filter, `{$this->id}`, refers
		 * to the id of the email provider. See `$this->providers` for a list of supported providers.
		 *
		 * @since 3.40.0
		 *
		 * @param bool $disabled Whether or not this class is disabled.
		 */
		if ( apply_filters( "llms_disable_{$this->id}", false ) ) {
			return;
		}

		add_filter( 'llms_email_delivery_services', array( $this, 'add_settings' ) );
		add_action( 'wp_ajax_llms_' . $this->id . '_remote_install', array( $this, 'ajax_callback_remote_install' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_js' ) );

	}

	/**
	 * Determines if the plugin is already installed and activates it if it is
	 *
	 * @since 3.40.0
	 *
	 * @return boolean|WP_Error `true` when plugin is installed and successfully activated.
	 *                           `WP_Error` when plugin is installed and there was an error activating it.
	 *                           `false` when plugin is not installed.
	 */
	protected function activate_already_installed_plugin() {

		$is_plugin_installed = false;

		foreach ( get_plugins() as $path => $details ) {
			if ( false === strpos( $path, '/' . $this->id . '.php' ) ) {
				continue;
			}
			$is_plugin_installed = true;
			$activate            = activate_plugin( $path );
			if ( is_wp_error( $activate ) ) {
				return $activate;
			}
			break;
		}

		return $is_plugin_installed;

	}

	/**
	 * Add Settings.
	 *
	 * @since 3.40.0
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
				'id'    => $this->id . '_title',
				'type'  => 'subtitle',
				'title' => $this->get_title(),
				'desc'  => $this->is_connected() ? '' : $this->get_description(),
			),
			array(
				'id'    => $this->id . '_connect',
				'type'  => 'custom-html',
				'value' => $this->get_connect_setting(),
			),
		);

		return array_merge( $settings, $new_settings );

	}

	/**
	 * Ajax callback for installing the connector's plugin.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function ajax_callback_remote_install() {

		$ret = $this->do_remote_install();
		ob_clean();
		wp_send_json( $ret, ! empty( $ret['status'] ) ? $ret['status'] : 200 );

	}

	/**
	 * Determines if the current user can perform the remote installation.
	 *
	 * @since 3.40.0
	 *
	 * @return true|array
	 */
	protected function can_remote_install() {

		if ( ! llms_verify_nonce( '_llms_' . $this->id . '_nonce', 'llms-' . $this->id . '-install' ) ) {
			return array(
				'code'    => 'llms_' . $this->id . '_install_nonce_failure',
				'message' => esc_html__( 'Security check failed.', 'lifterlms' ),
				'status'  => 401,
			);
		} elseif ( ! current_user_can( 'install_plugins' ) ) {
			return array(
				'code'    => 'llms_' . $this->id . '_install_unauthorized',
				'message' => esc_html__( 'You do not have permission to perform this action.', 'lifterlms' ),
				'status'  => 403,
			);
		}

		return true;

	}

	/**
	 * Automatically disables other providers when the current provider is connected.
	 *
	 * @since 3.40.0
	 *
	 * @return void.
	 */
	protected function disable_other_providers() {

		$disable = array_diff( $this->providers, array( $this->id ) );
		foreach ( $disable as $id ) {
			add_filter( 'llms_disable_' . $id, '__return_true' );
		}

	}

	/**
	 * Validate installation request and perform the plugin install or return errors.
	 *
	 * @since 3.40.0
	 *
	 * @return array
	 */
	protected function do_remote_install() {

		$can_install = $this->can_remote_install();
		if ( true !== $can_install ) {
			return $can_install;
		}

		$install = $this->install();

		if ( is_wp_error( $install ) ) {
			return array(
				'code'    => $install->get_error_code(),
				'message' => $install->get_error_message(),
				'status'  => 400,
			);
		}

		return $this->do_remote_install_success();

	}

	/**
	 * Install the plugin via the WP plugin installer.
	 *
	 * @since 3.40.0
	 *
	 * @return boolean|WP_Error Error object or `true` when successful.
	 */
	protected function install() {

		// Check if the plugin already exists and activate it if it is.
		$ret = $this->activate_already_installed_plugin();

		// Plugin doesn't exist, install it.
		if ( false === $ret ) {
			$ret = $this->install_plugin();
		}

		// Final check to ensure the connector is installed and activated.
		if ( true === $ret && ! $this->is_installed() ) {
			// Translators: %s = title of the email delivery plugin.
			return new WP_Error( 'llms_' . $this->id . '_not_found', sprtinf( __( '%s plugin not found. Please try again.', 'lifterlms' ), $this->get_title() ), $install );
		}

		return $ret;

	}

	/**
	 * Install the plugin via the WP Plugin Repo.
	 *
	 * @since 3.40.0
	 *
	 * @return boolean|WP_Error `true` on success, error object otherwise.
	 */
	protected function install_plugin() {

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// Use the WordPress Plugins API to get the plugin download link.
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => $this->id,
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

		return true;

	}

	/**
	 * Determine if inline scripts and styles should be output.
	 *
	 * @since 3.40.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return bool
	 */
	protected function should_output_inline() {

		// Short circuit if unauthorized.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		$screen = get_current_screen();
		return ( 'lifterlms_page_llms-settings' === $screen->id && 'engagements' === llms_filter_input( INPUT_GET, 'tab' ) && ! $this->is_connected() );

	}

}
