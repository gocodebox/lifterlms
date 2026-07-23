<?php
/**
 * Admin Site Health
 *
 * Adds LifterLMS diagnostics to the WordPress core Site Health Info screen.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Site_Health class.
 *
 * Hooks the {@see 'debug_information'} filter to surface LifterLMS-specific
 * data (settings, gateways, integrations, template overrides, constants) on the
 * Tools > Site Health > Info screen. Core-provided sections (WordPress, Server,
 * Theme, Plugins) are intentionally not duplicated.
 *
 * Data is sourced from {@see LLMS_Data::get_data()}, which is also used by the
 * telemetry tracker, so the underlying data class is left untouched.
 *
 * @since [version]
 */
class LLMS_Admin_Site_Health {

	/**
	 * Keys whose values may be sensitive (emails, URLs) and are excluded from the copied report.
	 *
	 * @since [version]
	 *
	 * @var string[]
	 */
	private static $sensitive_substrings = array( 'email', 'url', 'login', 'permalink' );

	/**
	 * Register hooks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'debug_information', array( __CLASS__, 'add_debug_info' ) );
	}

	/**
	 * Add LifterLMS sections to the Site Health Info report.
	 *
	 * Registers unconditionally: the debug_information filter only fires on the
	 * Info screen, where WP_Debug_Data is guaranteed to be loaded first. The
	 * class_exists() fallback in this callback guards against future core changes.
	 *
	 * @since [version]
	 *
	 * @param array $info The debug information array.
	 * @return array
	 */
	public static function add_debug_info( $info ) {

		if ( ! class_exists( 'WP_Debug_Data' ) ) {
			return $info;
		}

		$report = LLMS_Data::get_data( 'system_report' );

		$info['lifterlms-settings']     = self::section_from_map( __( 'LifterLMS Settings', 'lifterlms' ), $report['settings'] );
		$info['lifterlms-gateways']     = self::section_from_map( __( 'Payment Gateways', 'lifterlms' ), $report['gateways'] );
		$info['lifterlms-integrations'] = self::section_from_map( __( 'Integrations', 'lifterlms' ), $report['integrations'] );
		$info['lifterlms-templates']    = self::section_templates( $report['template_overrides'] );

		if ( isset( $report['constants'] ) ) {
			$info['lifterlms-constants'] = self::section_from_map( __( 'LifterLMS Constants', 'lifterlms' ), $report['constants'] );
		}

		return $info;
	}

	/**
	 * Build a Site Health section from a flat key => value map.
	 *
	 * @since [version]
	 *
	 * @param string $label Section label.
	 * @param array  $data  Flat associative array of data.
	 * @return array
	 */
	private static function section_from_map( $label, $data ) {

		$fields = array();

		foreach ( $data as $key => $value ) {
			$fields[ $key ] = array(
				'label'   => self::humanize( $key ),
				'value'   => self::to_string( $value ),
				'private' => self::is_sensitive_key( $key ),
			);
		}

		return array(
			'label'  => $label,
			'fields' => $fields,
		);
	}

	/**
	 * Build a Site Health section for template overrides.
	 *
	 * Each override is collapsed into a single field whose value lists every
	 * overridden template on its own line.
	 *
	 * @since [version]
	 *
	 * @param array $overrides Array of template override data.
	 * @return array
	 */
	private static function section_templates( $overrides ) {

		$rows = array();

		foreach ( $overrides as $override ) {
			$rows[] = sprintf(
				'%1$s (core: %2$s) - %3$s (version: %4$s)',
				$override['template'],
				$override['core_version'],
				$override['location'],
				$override['version']
			);
		}

		return array(
			'label'      => __( 'Template Overrides', 'lifterlms' ),
			'show_count' => true,
			'fields'     => array(
				'overrides' => array(
					'label' => __( 'Overrides', 'lifterlms' ),
					'value' => $rows ? implode( "\n", $rows ) : __( 'No overrides found.', 'lifterlms' ),
				),
			),
		);
	}

	/**
	 * Humanize a snake_case key for display.
	 *
	 * @since [version]
	 *
	 * @param string $key The data key.
	 * @return string
	 */
	private static function humanize( $key ) {

		return ucwords( str_replace( '_', ' ', $key ) );
	}

	/**
	 * Cast a value to its display string.
	 *
	 * @since [version]
	 *
	 * @param mixed $value The raw value.
	 * @return string
	 */
	private static function to_string( $value ) {

		if ( is_bool( $value ) ) {
			return $value ? __( 'Yes', 'lifterlms' ) : __( 'No', 'lifterlms' );
		}

		if ( is_array( $value ) ) {
			return implode( ', ', array_map( array( __CLASS__, 'to_string' ), $value ) );
		}

		return (string) $value;
	}

	/**
	 * Determine whether a key's value should be treated as sensitive.
	 *
	 * @since [version]
	 *
	 * @param string $key The data key.
	 * @return bool
	 */
	private static function is_sensitive_key( $key ) {

		$key = strtolower( (string) $key );

		foreach ( self::$sensitive_substrings as $needle ) {
			if ( false !== strpos( $key, $needle ) ) {
				return true;
			}
		}

		return false;
	}
}

LLMS_Admin_Site_Health::init();
