<?php
/**
 * LifterLMS Integration Abstract
 *
 * @package LifterLMS/Abstracts
 *
 * @since 3.0.0
 * @version 4.21.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Integration abstract class
 *
 * @since 3.0.0
 */
abstract class LLMS_Abstract_Integration extends LLMS_Abstract_Options_Data {

	/**
	 * Integration ID
	 *
	 * Defined by extending class as a variable.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Integration Title
	 *
	 * Should be defined by extending class in configure() function (so it can be translated).
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Integration Description
	 *
	 * Should be defined by extending class in configure() function (so it can be translated).
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * Integration Missing Dependencies Description
	 *
	 * Should be defined by extending class in configure() function (so it can be translated).
	 *
	 * Displays on the settings screen when `$this->is_installed()` is `false` to help users
	 * identify what requirements are missing.
	 *
	 * @var string
	 */
	public $description_missing = '';

	/**
	 * Reference to the integration plugin's main plugin file basename
	 *
	 * In the `configure()` method call `plugin_basename()` on the main plugin file.
	 *
	 * @var string
	 */
	protected $plugin_basename = '';

	/**
	 * Integration Priority
	 *
	 * Determines the order of the settings on the Integrations settings table.
	 *
	 * Built-in core integrations fire at 5.
	 *
	 * @var integer
	 */
	protected $priority = 20;

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 * @since 3.18.2 Unknown.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->configure();

		add_filter( 'lifterlms_integrations_settings_' . $this->id, array( $this, 'add_settings' ), $this->priority, 1 );

		/**
		 * Trigger an action when the integration is initialized.
		 *
		 * The dynamic portion of this hook, `{$this->id}`, refers to the integration's unique ID.
		 *
		 * @since 4.21.0
		 *
		 * @param object $instance Class instance of the class extending the `LLMS_Abstract_Integration` abstract.
		 */
		do_action( "llms_integration_{$this->id}_init", $this );

		if ( ! empty( $this->plugin_basename ) ) {
			add_action( "plugin_action_links_{$this->plugin_basename}", array( $this, 'plugin_action_links' ), 100, 4 );
		}

	}

	/**
	 * Configure the integration
	 *
	 * Set required class properties and so on.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	abstract protected function configure();

	/**
	 * Merge the default abstract settings with the actual integration settings
	 *
	 * Automatically called via filter upon construction.
	 *
	 * @since 3.17.8
	 *
	 * @param array $settings Existing settings from other integrations.
	 * @return array
	 */
	public function add_settings( $settings ) {
		return array_merge( $settings, $this->get_settings() );
	}

	/**
	 * Get additional settings specific to the integration
	 *
	 * Extending classes should override this with the settings
	 * specific to the integration.
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function get_integration_settings() {
		return array();
	}

	/**
	 * Retrieve the integration priority property.
	 *
	 * @since 3.33.1
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Retrieve an array of integration related settings
	 *
	 * @since 3.8.0
	 * @since 3.21.1 Automatically output the `$description_missing` message when requirements are not met.
	 * @since 4.21.0 Add an 'id' to the missing description HTML setting.
	 *
	 * @return array
	 */
	protected function get_settings() {

		$settings   = array();
		$settings[] = array(
			'type' => 'sectionstart',
			'id'   => 'llms_integration_' . $this->id . '_start',
		);
		$settings[] = array(
			'desc'  => $this->description,
			'id'    => 'llms_integration_' . $this->id . '_title',
			'title' => $this->title,
			'type'  => 'title',
		);
		$settings[] = array(
			'desc'    => __( 'Check to enable this integration.', 'lifterlms' ),
			'default' => 'no',
			'id'      => $this->get_option_name( 'enabled' ),
			'type'    => 'checkbox',
			'title'   => __( 'Enable / Disable', 'lifterlms' ),
		);

		if ( ! $this->is_installed() && ! empty( $this->description_missing ) ) {
			$settings[] = array(
				'id'    => 'llms_integration_' . $this->id . '_missing_requirements_desc',
				'type'  => 'custom-html',
				'value' => '<em>' . $this->description_missing . '</em>',
			);
		}

		$settings   = array_merge( $settings, $this->get_integration_settings() );
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'llms_integration_' . $this->id . '_end',
		);

		/**
		 * Filters the integration's settings
		 *
		 * The dynamic portion of this hook, `{$this->id}`, refers to the integration's ID.
		 *
		 * @since 3.8.0
		 *
		 * @param array[] $settings Array of settings arrays.
		 * @param object  $instance Class instance of the class extending the `LLMS_Abstract_Integration` abstract.
		 */
		return apply_filters( "llms_integration_{$this->id}_get_settings", $settings, $this );
	}

	/**
	 * Retrieve the option name prefix.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function get_option_prefix() {
		return $this->option_prefix . 'integration_' . $this->id . '_';
	}

	/**
	 * Autoload default option values from values defined in the integration settings array
	 *
	 * This will only run when extending integration classes define a version property greater than 1.
	 *
	 * This is a callback function for the WP core filter `default_option_{$option}`.
	 *
	 * @since 4.21.0
	 *
	 * @param mixed  $default_value        The default value. If no value is passed to `get_option()`, this will be an empty string.
	 *                                     Otherwise it will be the default value passed to the method.
	 * @param string $full_option_name     The full (prefixed) option name.
	 * @param bool   $passed_default_value Whether or not a default value was passed to `get_option()`.
	 * @return mixed The default option value.
	 */
	public function get_option_default_value( $default_value, $full_option_name, $passed_default_value ) {

		// If a default value is explicitly passed, use it.
		if ( $passed_default_value ) {
			return $default_value;
		}

		foreach ( $this->get_settings() as $setting ) {

			if ( ! empty( $setting['id'] ) && $full_option_name === $setting['id'] ) {
				return isset( $setting['default'] ) ? $setting['default'] : $default_value;
			}
		}

		return $default_value;

	}

	/**
	 * Determine if the integration is enabled via the checkbox on the admin panel
	 * and the necessary plugin (if any) is installed and activated
	 *
	 * @since 3.0.0
	 * @since 3.17.8 Unknown.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return ( $this->is_installed() && $this->is_enabled() );
	}

	/**
	 * Determine if the integration had been enabled via checkbox
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return ( 'yes' === $this->get_option( 'enabled', 'no' ) );
	}

	/**
	 * Determine if required dependencies are installed.
	 *
	 * Extending classes should override this to perform dependency checks.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @return boolean
	 */
	public function is_installed() {
		return true;
	}

	/**
	 * Add plugin settings Action Links
	 *
	 * @since 3.37.9
	 * @since 4.21.0 Don't check `$context`. If the plugin isn't active this won't run anyway so it's a useless check.
	 *
	 * @param string[] $links   Existing action links.
	 * @param string   $file    Path to the plugin file, relative to the plugin directory.
	 * @param array    $data    Plugin data.
	 * @param string   $context Plugin's content (eg: active, invactive, etc...).
	 * @return string[]
	 */
	public function plugin_action_links( $links, $file, $data, $context ) {

		$url = add_query_arg(
			array(
				'page'    => 'llms-settings',
				'tab'     => 'integrations',
				'section' => $this->id,
			),
			admin_url( 'admin.php' )
		);

		$links[] = '<a href="' . esc_url( $url ) . '">' . _x( 'Settings', 'Link text for integration plugin settings', 'lifterlms' ) . '</a>';

		return $links;

	}

}
