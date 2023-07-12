<?php
/**
 * LifterLMS Admin Notification Condition Class.
 *
 * @package LifterLMS/Admin/Notifications/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Notification_Condition
 *
 * @since [version]
 */
class LLMS_Admin_Notification_Condition {

	/**
	 * @var string
	 */
	private string $show_hide;

	/**
	 * @var string
	 */
	private string $callback;

	/**
	 * @var string
	 */
	private string $operator;

	/**
	 * @var string[]
	 */
	private array $values;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @param object $args Condition arguments object.
	 * @return void
	 */
	public function __construct( object $args ) {

		$this->show_hide = $args->show_hide ?? 'show';
		$this->callback  = $args->callback ?? null;
		$this->operator  = $args->operator ?? null;
		$this->values    = array_map(
			'trim',
			explode( ',', $args->values )
		) ?? [];

	}

	/**
	 * Check if the condition is met.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function check_callback(): bool {
		$return   = false;
		$callback = $this->callback;
		$operator = $this->operator;

		if ( method_exists( $this, $callback ) ) {
			if ( $operator ) {
				$return = $this->$callback( $this->values, $this->operator );
			} else {
				$return = $this->$callback( $this->values );
			}
		}

		if ( function_exists( $callback ) ) {
			$function = $callback;

			if ( $operator ) {
				$return = $function( $this->values, $this->operator );
			} else {
				$return = $function( $this->values );
			}
		}

		return $this->show_hide === 'show' ? $return : ! $return;

	}

	/**
	 * Checks if all plugins in array are active.
	 *
	 * First checks for short slug match, e.g. "lifterlms" before
	 * checking full plugin basename "lifterlms/lifterlms.php".
	 *
	 * @since [version]
	 *
	 * @param array $values Array of plugin slugs.
	 * @return bool
	 */
	private function plugins_active( array $values ): bool {

		$plugins_active = false;

		foreach ( $values as $value ) {
			if ( $this->plugin_active( $value ) ) {
				$plugins_active = true;
			}
		}

		return $plugins_active;

	}

	/**
	 * Checks if a single plugin is active.
	 *
	 * @since [version]
	 *
	 * @param string $value Plugin slug.
	 * @return bool
	 */
	private function plugin_active( string $value ): bool {

		$active_plugins = get_option( 'active_plugins', array() );

		if ( in_array( $value, $active_plugins, true ) ) {
			return true;
		}

		foreach ( $active_plugins as $active_plugin ) {
			$without_php      = basename( $active_plugin, '.php' );
			$plugin_parts     = explode( '/', $active_plugin );
			$folder_name      = $plugin_parts[0];
			$file_name        = $plugin_parts[1];
			$file_without_php = basename( $file_name, '.php' );

			if ( $value === $without_php || $value === $folder_name || $value === $file_name || $value === $file_without_php ) {
				return true;
			}

		}

		return false;

	}

	/**
	 * Checks if all plugins in array are inactive.
	 *
	 * @since [version]
	 *
	 * @param array $values Array of plugin slugs.
	 * @return bool
	 */
	private function plugins_inactive( array $values ): bool {

		$plugins_inactive = false;

		foreach ( $values as $value ) {
			if ( $this->plugin_inactive( $value ) ) {
				$plugins_inactive = true;
			}
		}

		return $plugins_inactive;

	}

	/**
	 * Checks if a single plugin is inactive.
	 *
	 * @since [version]
	 *
	 * @param string $value Plugin slug.
	 * @return bool
	 */
	private function plugin_inactive( string $value ): bool {
		return ! $this->plugin_active( $value );
	}

	/**
	 * Checks LifterLMS version.
	 *
	 * @since [version]
	 *
	 * @param array  $values   Array of versions.
	 * @param string $operator Comparison operator.
	 * @return bool
	 */
	private function llms_version( array $values, string $operator ): bool {

		return version_compare( llms()->version, $values[0], $operator );

	}

}
