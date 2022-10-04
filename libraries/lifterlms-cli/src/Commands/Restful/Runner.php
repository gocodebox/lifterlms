<?php
/**
 * File Summary
 *
 * Forked from wp-cli/restful (by Daniel Bachhuber, released under the MIT license https://opensource.org/licenses/MIT).
 * https://github.com/wp-cli/restful
 *
 * @package LifterLMS_CLI/Classes
 *
 * @since 0.0.1
 * @version 0.0.1
 *
 * @link https://github.com/wp-cli/restful/blob/master/inc/Runner.php
 * @link https://github.com/wp-cli/restful/commit/6ea62c149944d8fcb31a7ade7b4f65fb72c8a5a3
 */

namespace LifterLMS\CLI\Commands\Restful;

/**
 * LifterLMS REST API to LifterLMS CLI Bridge.
 *
 * Hooks into the REST API, figures out which endpoints come from LifterLMS,
 * and registers them as CLI commands.
 *
 * @since 0.0.1
 */
class Runner {

	public static function after_wp_load() {

		if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
			return;
		}

		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();

		do_action( 'rest_api_init', $wp_rest_server );

		$request = new \WP_REST_Request( 'GET', '/' );
		$request->set_param( 'context', 'help' );

		$response      = $wp_rest_server->dispatch( $request );
		$response_data = $response->get_data();
		if ( empty( $response_data ) ) {
			return;
		}

		foreach ( $response_data['routes'] as $route => $route_data ) {

			// Skip non LifterLMS routes.
			if ( 0 !== strpos( $route, '/llms/' ) ) {
				continue;
			}

			if ( empty( $route_data['schema']['title'] ) ) {
				\WP_CLI::debug( "No schema title found for {$route}, skipping LifterLMS CLI REST command registration.", 'lifterlms' );
				continue;
			}

			$name         = $route_data['schema']['title'];
			$rest_command = new Command( $name, $route, $route_data['schema'] );
			self::register_route_commands( $rest_command, $route, $route_data );

		}

	}


	private static function get_command_root_desc( $resource ) {
		$resource = str_replace( array( '-', 'students', 'api' ), array( ' ', 'student', 'API' ), $resource );
		if ( 's' !== substr( $resource, -1 ) ) {
			$resource .= 's';
		}
		return sprintf( 'Manage %s.', $resource );
	}

	private static function get_command_short_desc( $command, $resource ) {

		$before = '';
		$after  = '';


		switch ( $command ) {
			case 'create':
				$before = 'Creates a new';
				break;

			case 'delete':
				$before = 'Deletes an existing';
				break;

			case 'diff':
				$before = 'Compare';
				$resource = self::pluralize_resource( $resource );
				$after = 'between environments';
				break;

			case 'edit':
				$before = 'Launches system editor to edit the';
				$after = 'content';
				break;

			case 'generate':
				$before = 'Generates some';
				$resource = self::pluralize_resource( $resource );
				break;

			case 'get':
				$before = 'Gets details about a';
				break;

			case 'list':
				$before = 'Gets a list of ';
				$resource = self::pluralize_resource( $resource );
				break;

			case 'update':
				$before = 'Updates an existing';
				break;
		}

		return trim( implode( ' ', array( $before, $resource, $after ) ) ) . '.';
	}

	private static function pluralize_resource( $resource ) {

		switch ( $resource ) {
			default:
				$resource .= 's';
		}

		return $resource;
	}

	private static function get_supported_commands( $route, $route_data ) {

		$supported_commands = array();
		foreach ( $route_data['endpoints'] as $endpoint ) {

			$parsed_args   = preg_match_all( '#\([^\)]+\)#', $route, $matches );
			$resource_id   = ! empty( $matches[0] ) ? array_pop( $matches[0] ) : null;
			$trimmed_route = rtrim( $route );
			$is_singular   = $resource_id === substr( $trimmed_route, - strlen( $resource_id ) );

			// List a collection
			if ( array( 'GET' ) == $endpoint['methods']
				&& ! $is_singular ) {
				$supported_commands['list'] = ! empty( $endpoint['args'] ) ? $endpoint['args'] : array();
			}

			// Create a specific resource
			if ( array( 'POST' ) == $endpoint['methods']
				&& ! $is_singular ) {
				$supported_commands['create'] = ! empty( $endpoint['args'] ) ? $endpoint['args'] : array();
			}

			// Get a specific resource
			if ( array( 'GET' ) == $endpoint['methods']
				&& $is_singular ) {
				$supported_commands['get'] = ! empty( $endpoint['args'] ) ? $endpoint['args'] : array();
			}

			// Update a specific resource
			if ( in_array( 'POST', $endpoint['methods'] )
				&& $is_singular ) {
				$supported_commands['update'] = ! empty( $endpoint['args'] ) ? $endpoint['args'] : array();
			}

			// Delete a specific resource
			if ( array( 'DELETE' ) == $endpoint['methods']
				&& $is_singular ) {
				$supported_commands['delete'] = ! empty( $endpoint['args'] ) ? $endpoint['args'] : array();
			}
		}

		return $supported_commands;

	}

	public static function before_invoke_command() {

		/**
		 * If `--user` was passed the user will already be set, otherwise there won't be a user.
		 *
		 * It is "safe" to assume that someone using the CLI has admin access and we'll set the current
		 * user to be the first admin we find in the DB that has the `manage_options` cap.
		 */
		if ( ! get_current_user_id() ) {
			$user = \LLMS_Install::get_can_install_user_id();
			if ( $user ) {
				wp_set_current_user( $user );
			}
		}

		if ( \WP_CLI::get_config( 'debug' ) && ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

	}

	/**
	 * Register WP-CLI commands for all endpoints on a route
	 *
	 * @param string
	 * @param array  $endpoints
	 */
	private static function register_route_commands( $rest_command, $route, $route_data ) {

		$resource = str_replace( array( 'llms_', '_' ), array( '', '-' ), $route_data['schema']['title'] );
		$parent   = "llms {$resource}";

		$supported_commands = self::get_supported_commands( $route, $route_data );
		foreach ( $supported_commands as $command => $endpoint_args ) {

			$synopsis = array();
			if ( in_array( $command, array( 'delete', 'get', 'update' ) ) ) {
				$synopsis[] = array(
					'name'        => 'id',
					'type'        => 'positional',
					'description' => 'The id for the resource.',
					'optional'    => false,
				);
			}

			foreach ( $endpoint_args as $name => $args ) {
				$arg_reg = array(
					'name'        => $name,
					'type'        => 'assoc',
					'description' => ! empty( $args['description'] ) ? $args['description'] : '',
					'optional'    => empty( $args['required'] ) ? true : false,
				);
				foreach ( array( 'enum', 'default' ) as $key ) {
					if ( isset( $args[ $key ] ) ) {
						$new_key             = 'enum' === $key ? 'options' : $key;
						$arg_reg[ $new_key ] = $args[ $key ];
					}
				}
				$synopsis[] = $arg_reg;
			}

			if ( in_array( $command, array( 'list', 'get' ) ) ) {
				$synopsis[] = array(
					'name'        => 'fields',
					'type'        => 'assoc',
					'description' => 'Limit response to specific fields. Defaults to all fields.',
					'optional'    => true,
				);
				$synopsis[] = array(
					'name'        => 'field',
					'type'        => 'assoc',
					'description' => 'Get the value of an individual field.',
					'optional'    => true,
				);
				$synopsis[] = array(
					'name'        => 'format',
					'type'        => 'assoc',
					'description' => 'Render response in a particular format.',
					'optional'    => true,
					'default'     => 'table',
					'options'     => array(
						'table',
						'json',
						'csv',
						'ids',
						'yaml',
						'count',
						'headers',
						'body',
						'envelope',
					),
				);
			}

			if ( in_array( $command, array( 'create', 'update', 'delete' ) ) ) {
				$synopsis[] = array(
					'name'        => 'porcelain',
					'type'        => 'flag',
					'description' => 'Output just the id when the operation is successful.',
					'optional'    => true,
				);
			}

			$methods = array(
				'list'   => 'list_items',
				'create' => 'create_item',
				'delete' => 'delete_item',
				'get'    => 'get_item',
				'update' => 'update_item',
			);

			// Add the root command, eg: wp llms course.
			\WP_CLI::add_command(
				"{$parent}",
				$rest_command,
				array(
					'shortdesc' => self::get_command_root_desc( $resource ),
				)
			);

			// Register main subcommands, eg: wp llms course create, wp llms course delete, etc...
			\WP_CLI::add_command(
				"{$parent} {$command}",
				array( $rest_command, $methods[ $command ] ),
				array(
					'shortdesc'     => self::get_command_short_desc( $command, $resource ),
					'synopsis'      => $synopsis,
					'before_invoke' => array( __CLASS__, 'before_invoke_command' ),
				)
			);

			// If listing is supported, add the diff command.
			if ( 'list' === $command ) {
				\WP_CLI::add_command(
					"{$parent} diff",
					array( $rest_command, 'diff_items' ),
					array(
						'shortdesc' => self::get_command_short_desc( 'diff', $resource ),
						'before_invoke' => array( __CLASS__, 'before_invoke_command' ),
					)
				);
			}

			// If creation is supported, add the generate command.
			if ( 'create' === $command ) {
				\WP_CLI::add_command(
					"{$parent} generate",
					array( $rest_command, 'generate_items' ),
					array(
						'shortdesc' => self::get_command_short_desc( 'generate', $resource ),
						'synopsis'  => self::get_generate_command_synopsis( $synopsis ),
						'before_invoke' => array( __CLASS__, 'before_invoke_command' ),
					)
				);
			}


			// If updating and getting is supported, add the edit command.
			if ( 'update' === $command && array_key_exists( 'get', $supported_commands ) ) {
				$synopsis   = array();
				$synopsis[] = array(
					'name'        => 'id',
					'type'        => 'positional',
					'description' => 'The id for the resource.',
					'optional'    => false,
				);
				\WP_CLI::add_command(
					"{$parent} edit",
					array( $rest_command, 'edit_item' ),
					array(
						'shortdesc' => self::get_command_short_desc( 'edit', $resource ),
						'synopsis'  => $synopsis,
						'before_invoke' => array( __CLASS__, 'before_invoke_command' ),
					)
				);
			}
		}
	}

	private static function get_generate_command_synopsis( $create_synopsis ) {

		$generate_synopsis = array(
			array(
				'name'        => 'count',
				'type'        => 'assoc',
				'description' => 'Number of items to generate.',
				'optional'    => true,
				'default'     => 10,
			),
			array(
				'name'        => 'format',
				'type'        => 'assoc',
				'description' => 'Render generation in specific format.',
				'optional'    => true,
				'default'     => 'progress',
				'options'     => array(
					'progress',
					'ids',
				),
			),
		);

		return array_merge( $generate_synopsis, $create_synopsis );

	}

}
