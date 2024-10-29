<?php
/**
 * LLMS_CLI_Abstract_Command file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands;

/**
 * Base CLI command for use by LifterLMS CLI commands
 *
 * @since 0.0.1
 */
abstract class AbstractCommand extends \WP_CLI_Command {

	/**
	 * Determines whether or not a command is being chained.
	 *
	 * When chaining commands (like `addon uninstall --deactivate`) we skip
	 * output of the secondary command (deactivate won't output it's success/error).
	 *
	 * @var boolean
	 */
	protected $chaining = false;

	/**
	 * Chain a command within the class
	 *
	 * @since 0.0.1
	 *
	 * @param string $command    Method name of the command to chain.
	 * @param array  $args       Indexed array of positional command arguments to pass to the chained command.
	 * @param array  $assoc_args Associative array of command options to pass to the chained command.
	 * @return void
	 */
	protected function chain_command( $command, $args = array(), $assoc_args = array() ) {
		$this->chaining = true;
		$this->$command( $args, $assoc_args );
		$this->chaining = false;
	}

	/**
	 * Retrieve an LLMS_Add_On object for a given add-on by it's slug.
	 *
	 * @since 0.0.1
	 *
	 * @param string               $slug     An add-on slug. Must be prefixed.
	 * @param bool|WP_Error|string $err      If truthy, will return `null` and use log to the console using a WP_CLI method as defined by $err_type.
	 *                                       Pass `true` to output a default error message.
	 *                                       Pass a WP_Error object or string to use as the error.
	 * @param string               $err_type Method to pass `$err` to when an error is encountered. Default `\WP_CLI::error()`.
	 *                                       Use `\WP_CLI::warning()` or `\WP_CLI::log()` where appropriate.
	 * @return LLMS_Add_On|boolean|null Returns an add-on object if the add-on can be located or `false` if not found.
	 *                                  Returns `null` when an error is encountered and `$err` is a truthy.
	 */
	protected function get_addon( $slug, $err = false, $err_type = 'error' ) {

		$addon  = llms_get_add_on( $this->prefix_slug( $slug ), 'slug' );
		$exists = ! empty( $addon->get( 'id' ) );

		if ( ! $exists && $err ) {
			$err = is_bool( $err ) ? sprintf( 'Invalid slug: %s.', $slug ) : $err;
			return \WP_CLI::$err_type( $err );
		}

		return ! $exists ? false : $addon;
	}

	/**
	 * Prefix an add-on slug with `lifterlms-` if it's not already present.
	 *
	 * @since 0.0.1
	 *
	 * @param string $slug Add-on slug.
	 * @return string
	 */
	protected function prefix_slug( $slug ) {
		if ( 0 !== strpos( $slug, 'lifterlms-' ) ) {
			$slug = "lifterlms-{$slug}";
		}
		return $slug;
	}

}
