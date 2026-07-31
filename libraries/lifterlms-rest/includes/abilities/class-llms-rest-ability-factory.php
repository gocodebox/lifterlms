<?php
/**
 * LLMS_REST_Ability_Factory class file
 *
 * @package LifterLMS_REST/Abilities
 *
 * @since 10.1.0
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Converts LifterLMS REST API controllers into WordPress Abilities.
 *
 * Given an ability configuration describing a REST controller, an operation, and a route,
 * this factory derives the ability's input and output JSON Schemas from the controller's
 * own schema definitions and registers the ability with the WordPress Abilities API (WP 6.9+).
 *
 * Execution dispatches an internal `WP_REST_Request` through `rest_do_request()`, so all
 * existing route permission callbacks, validation, sanitization, and hooks apply unchanged.
 *
 * @since 10.1.0
 */
class LLMS_REST_Ability_Factory {

	/**
	 * Ability name namespace prefix.
	 *
	 * @var string
	 */
	const ABILITY_NAMESPACE = 'lifterlms';

	/**
	 * Map of operation to controller permission check method.
	 *
	 * @var array
	 */
	const PERMISSION_METHODS = array(
		'list'   => 'get_items_permissions_check',
		'get'    => 'get_item_permissions_check',
		'create' => 'create_item_permissions_check',
		'update' => 'update_item_permissions_check',
		'delete' => 'delete_item_permissions_check',
	);

	/**
	 * Valid JSON Schema types.
	 *
	 * @var string[]
	 */
	private static $valid_types = array( 'string', 'number', 'integer', 'boolean', 'object', 'array', 'null' );

	/**
	 * Scalar JSON Schema types eligible for output-schema widening.
	 *
	 * @var string[]
	 */
	private static $scalar_types = array( 'string', 'integer', 'number', 'boolean' );

	/**
	 * Cached controller instances keyed by class name.
	 *
	 * @var array
	 */
	private static $controllers = array();

	/**
	 * Register a single ability from a configuration array.
	 *
	 * Expected configuration keys:
	 *
	 * @since 10.1.0
	 *
	 * @param array $config {
	 *     Ability configuration.
	 *
	 *     @type string $name                  Required. Un-namespaced ability name, e.g. `list-courses`.
	 *     @type string $label                 Required. Human readable label.
	 *     @type string $description           Required. Detailed description of what the ability does.
	 *     @type string $controller            Required. REST controller class name the ability executes against.
	 *     @type string $operation             Required. One of `list`, `get`, `create`, `update`, `delete`.
	 *     @type string $method                Required. HTTP method used when dispatching the internal request.
	 *     @type string $route                 Required. Full REST route with optional `{param}` placeholders,
	 *                                         e.g. `/llms/v1/students/{id}/enrollments`.
	 *     @type string $schema_controller     Optional. Controller class used to derive input/output schemas.
	 *                                         Defaults to `controller`.
	 *     @type string $permission_controller Optional. Controller class used for the permission callback.
	 *                                         Defaults to `controller`.
	 *     @type string $args_method           Optional. HTTP method passed to `get_endpoint_args_for_item_schema()`
	 *                                         when deriving input args. Defaults based on `operation`.
	 *     @type array  $path_params           Optional. Map of route placeholder names to descriptions.
	 * }
	 * @return WP_Ability|null The registered ability on success, `null` on failure.
	 */
	public static function register( $config ) {

		if ( ! function_exists( 'wp_register_ability' ) || ! class_exists( $config['controller'] ) ) {
			return null;
		}

		// Captured by the callbacks below so requests built outside REST dispatch carry the same defaults.
		$config['default_params'] = self::get_default_params( $config );

		$args = array(
			'label'               => $config['label'],
			'description'         => $config['description'],
			'category'            => self::ABILITY_NAMESPACE,
			'input_schema'        => self::get_input_schema( $config ),
			'output_schema'       => self::get_output_schema( $config ),
			'execute_callback'    => function ( $input = null ) use ( $config ) {
				return self::execute( $config, $input );
			},
			'permission_callback' => function ( $input = null ) use ( $config ) {
				return self::check_permission( $config, $input );
			},
			'meta'                => array(
				'show_in_rest' => true,
			),
		);

		if ( in_array( $config['operation'], array( 'list', 'get' ), true ) ) {
			$args['meta']['annotations'] = array(
				'readonly' => true,
			);
		} elseif ( 'delete' === $config['operation'] ) {
			$args['meta']['annotations'] = array(
				'destructive' => true,
			);
		}

		return wp_register_ability( self::ABILITY_NAMESPACE . '/' . $config['name'], $args );
	}

	/**
	 * Retrieve a (cached) controller instance.
	 *
	 * @since 10.1.0
	 *
	 * @param string $class_name Controller class name.
	 * @return object|null
	 */
	private static function get_controller( $class_name ) {

		if ( ! class_exists( $class_name ) ) {
			return null;
		}

		if ( ! isset( self::$controllers[ $class_name ] ) ) {
			self::$controllers[ $class_name ] = new $class_name();
		}

		return self::$controllers[ $class_name ];
	}

	/**
	 * Derive the ability input schema from the controller for the configured operation.
	 *
	 * @since 10.1.0
	 *
	 * @param array $config Ability configuration.
	 * @return array JSON Schema object.
	 */
	private static function get_input_schema( $config ) {

		$schema = self::sanitize_args_to_schema( self::get_endpoint_args( $config ) );

		foreach ( self::get_path_params( $config ) as $param => $description ) {

			$schema['properties'][ $param ] = array(
				'type'        => 'integer',
				'description' => $description,
			);

			$schema['required']   = isset( $schema['required'] ) ? $schema['required'] : array();
			$schema['required'][] = $param;
		}

		if ( isset( $schema['required'] ) ) {
			$schema['required'] = array_values( array_unique( $schema['required'] ) );
		}

		return $schema;
	}

	/**
	 * Retrieve the raw endpoint args (WP REST format) for the configured operation.
	 *
	 * @since 10.1.0
	 *
	 * @param array $config Ability configuration.
	 * @return array
	 */
	private static function get_endpoint_args( $config ) {

		$controller = self::get_controller( ! empty( $config['schema_controller'] ) ? $config['schema_controller'] : $config['controller'] );
		$args       = array();

		if ( $controller ) {

			switch ( $config['operation'] ) {

				case 'list':
					if ( method_exists( $controller, 'get_collection_params' ) ) {
						$args = $controller->get_collection_params();
					}
					break;

				case 'get':
					if ( method_exists( $controller, 'get_get_item_params' ) ) {
						$args = $controller->get_get_item_params();
					}
					break;

				case 'create':
				case 'update':
					$args_method = ! empty( $config['args_method'] ) ? $config['args_method'] : ( 'create' === $config['operation'] ? WP_REST_Server::CREATABLE : WP_REST_Server::EDITABLE );
					if ( method_exists( $controller, 'get_endpoint_args_for_item_schema' ) ) {
						$args = $controller->get_endpoint_args_for_item_schema( $args_method );
					}
					break;

				case 'delete':
					if ( ! empty( $config['args_method'] ) && method_exists( $controller, 'get_endpoint_args_for_item_schema' ) ) {
						$args = $controller->get_endpoint_args_for_item_schema( $config['args_method'] );
					} elseif ( method_exists( $controller, 'get_delete_item_args' ) ) {
						$args = $controller->get_delete_item_args();
					}
					break;

			}
		}

		return is_array( $args ) ? $args : array();
	}

	/**
	 * Retrieve the default values of the endpoint args for the configured operation.
	 *
	 * During REST dispatch the server applies these defaults from the route registration.
	 * Requests built manually for the ability callbacks skip route matching, so the same
	 * defaults are set on the request explicitly (e.g. the enrollments `trigger` param,
	 * which permission checks read before dispatch occurs).
	 *
	 * @since 10.1.0
	 *
	 * @param array $config Ability configuration.
	 * @return array Map of param name to default value.
	 */
	private static function get_default_params( $config ) {

		$defaults = array();

		foreach ( self::get_endpoint_args( $config ) as $key => $arg ) {
			if ( is_array( $arg ) && array_key_exists( 'default', $arg ) ) {
				$defaults[ $key ] = $arg['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Derive the ability output schema from the controller item schema.
	 *
	 * The derived schema is relaxed (scalar types widened, formats stripped) because the
	 * Abilities API strictly validates execution output against the output schema, and
	 * REST item schemas don't always match the exact shapes controllers return.
	 *
	 * @since 10.1.0
	 *
	 * @param array $config Ability configuration.
	 * @return array JSON Schema object.
	 */
	private static function get_output_schema( $config ) {

		if ( 'delete' === $config['operation'] ) {
			return array(
				'type'       => 'object',
				'properties' => array(
					'deleted' => array(
						'type'        => 'boolean',
						'description' => __( 'Whether the resource was deleted.', 'lifterlms' ),
					),
				),
			);
		}

		$controller  = self::get_controller( ! empty( $config['schema_controller'] ) ? $config['schema_controller'] : $config['controller'] );
		$item_schema = array( 'type' => 'object' );

		if ( $controller && method_exists( $controller, 'get_item_schema' ) ) {
			$item_schema = self::relax_output_schema( self::sanitize_schema( $controller->get_item_schema() ) );
		}

		if ( 'list' === $config['operation'] ) {
			return array(
				'type'  => 'array',
				'items' => $item_schema,
			);
		}

		return $item_schema;
	}

	/**
	 * Execute the ability by dispatching an internal REST request.
	 *
	 * @since 10.1.0
	 *
	 * @param array      $config Ability configuration.
	 * @param array|null $input  Validated ability input.
	 * @return mixed|WP_Error Response data on success, `WP_Error` on failure.
	 */
	private static function execute( $config, $input = null ) {

		$request  = self::build_request( $config, $input );
		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( $response->is_error() ) {
			return $response->as_error();
		}

		if ( 'delete' === $config['operation'] ) {
			return array(
				'deleted' => true,
			);
		}

		return $response->get_data();
	}

	/**
	 * Check permissions by delegating to the controller's permission check for the operation.
	 *
	 * The controller's `WP_Error` results are normalized to `false`: `WP_Ability::execute()`
	 * treats a `WP_Error` permission result as incorrect usage, and execution dispatches
	 * through `rest_do_request()` anyway, which enforces the route's own permission callback
	 * and surfaces its detailed error.
	 *
	 * @since 10.1.0
	 *
	 * @param array      $config Ability configuration.
	 * @param array|null $input  Ability input.
	 * @return boolean
	 */
	private static function check_permission( $config, $input = null ) {

		$controller = self::get_controller( ! empty( $config['permission_controller'] ) ? $config['permission_controller'] : $config['controller'] );
		$method     = isset( self::PERMISSION_METHODS[ $config['operation'] ] ) ? self::PERMISSION_METHODS[ $config['operation'] ] : '';

		if ( ! $controller || ! $method || ! method_exists( $controller, $method ) ) {
			return false;
		}

		return true === $controller->{$method}( self::build_request( $config, $input ) );
	}

	/**
	 * Build an internal REST request from ability input.
	 *
	 * Route placeholders (e.g. `{id}`) are substituted with the corresponding input values
	 * and moved from the query/body parameters into the request's URL parameters. Setting
	 * them as URL params keeps them readable (e.g. `$request['id']`) by controller
	 * permission checks invoked directly via {@see LLMS_REST_Ability_Factory::check_permission()},
	 * where no route matching occurs to populate them, while keeping the request body free
	 * of path parameters the controllers don't expect there.
	 *
	 * @since 10.1.0
	 *
	 * @param array      $config Ability configuration.
	 * @param array|null $input  Ability input.
	 * @return WP_REST_Request
	 */
	private static function build_request( $config, $input = null ) {

		$input      = is_array( $input ) ? $input : array();
		$route      = $config['route'];
		$params     = $input;
		$url_params = array();

		foreach ( array_keys( self::get_path_params( $config ) ) as $param ) {
			if ( isset( $params[ $param ] ) ) {
				$url_params[ $param ] = absint( $params[ $param ] );
				$route                = str_replace( '{' . $param . '}', (string) $url_params[ $param ], $route );
				unset( $params[ $param ] );
			}
		}

		$request = new WP_REST_Request( $config['method'], $route );
		$request->set_url_params( $url_params );

		if ( in_array( $config['method'], array( 'GET', 'DELETE' ), true ) ) {
			$request->set_query_params( $params );
		} else {
			$request->set_body_params( $params );
		}

		if ( ! empty( $config['default_params'] ) ) {
			$request->set_default_params( $config['default_params'] );
		}

		return $request;
	}

	/**
	 * Retrieve the route path params and their descriptions.
	 *
	 * Placeholders are parsed from the configured route. Descriptions can be customized
	 * via the `path_params` configuration key.
	 *
	 * @since 10.1.0
	 *
	 * @param array $config Ability configuration.
	 * @return array Map of param name to description.
	 */
	private static function get_path_params( $config ) {

		$params = array();

		if ( ! preg_match_all( '/\{([a-z_]+)\}/', $config['route'], $matches ) ) {
			return $params;
		}

		foreach ( $matches[1] as $param ) {
			if ( ! empty( $config['path_params'][ $param ] ) ) {
				$params[ $param ] = $config['path_params'][ $param ];
			} else {
				$params[ $param ] = __( 'Unique identifier for the resource.', 'lifterlms' );
			}
		}

		return $params;
	}

	/**
	 * Convert a WordPress REST args array into a valid JSON Schema object.
	 *
	 * Strips PHP callbacks and other non-schema keys, converts per-field boolean `required`
	 * flags into a JSON Schema `required` array, and recursively sanitizes nested schemas.
	 *
	 * @since 10.1.0
	 *
	 * @param array $args WordPress REST API arguments array.
	 * @return array JSON Schema object.
	 */
	private static function sanitize_args_to_schema( $args ) {

		$properties = array();
		$required   = array();

		foreach ( $args as $key => $arg ) {

			if ( ! is_array( $arg ) ) {
				continue;
			}

			$properties[ $key ] = self::sanitize_schema( $arg );

			/**
			 * Some fields (e.g. post `title` and `content`) explicitly disable REST validation
			 * and accept multiple shapes (a plain string or a `{raw}` object). Widen the declared
			 * type so ability input validation matches the REST API's actual behavior.
			 */
			if ( array_key_exists( 'validate_callback', $arg ) && null === $arg['validate_callback'] && isset( $properties[ $key ]['type'] ) ) {
				$properties[ $key ]['type'] = self::$valid_types;
			}

			if ( isset( $arg['required'] ) && true === $arg['required'] ) {
				$required[] = $key;
			}
		}

		$schema = array(
			'type'       => 'object',
			'properties' => $properties,
		);

		if ( ! empty( $required ) ) {
			$schema['required'] = array_values( array_unique( $required ) );
		}

		return $schema;
	}

	/**
	 * Recursively sanitize a JSON Schema node.
	 *
	 * Copies only valid JSON Schema keywords, normalizes types, and recurses into
	 * `properties`, `items`, and `additionalProperties`.
	 *
	 * @since 10.1.0
	 *
	 * @param array $schema A JSON Schema node (or a WP REST arg definition).
	 * @return array Sanitized schema node.
	 */
	private static function sanitize_schema( $schema ) {

		$sanitized = array();

		if ( isset( $schema['type'] ) ) {
			$sanitized = self::normalize_type( $sanitized, $schema['type'] );
		}

		foreach ( array( 'description', 'default', 'format', 'minimum', 'maximum', 'minLength', 'maxLength', 'minItems', 'maxItems', 'title' ) as $keyword ) {
			if ( isset( $schema[ $keyword ] ) ) {
				$sanitized[ $keyword ] = $schema[ $keyword ];
			}
		}

		if ( isset( $schema['enum'] ) && is_array( $schema['enum'] ) ) {
			$sanitized['enum'] = array_values( array_unique( $schema['enum'], SORT_REGULAR ) );
		}

		if ( isset( $schema['readonly'] ) && $schema['readonly'] ) {
			$sanitized['readOnly'] = true;
		}

		if ( isset( $schema['items'] ) && is_array( $schema['items'] ) ) {
			$sanitized['items'] = self::sanitize_schema( $schema['items'] );
		}

		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {

			$sanitized['properties'] = array();
			$required                = array();

			foreach ( $schema['properties'] as $key => $property ) {

				if ( ! is_array( $property ) ) {
					continue;
				}

				$sanitized['properties'][ $key ] = self::sanitize_schema( $property );

				if ( isset( $property['required'] ) && true === $property['required'] ) {
					$required[] = $key;
				}
			}

			if ( isset( $schema['required'] ) && is_array( $schema['required'] ) ) {
				$required = array_merge( $schema['required'], $required );
			}

			if ( ! empty( $required ) ) {
				$sanitized['required'] = array_values( array_unique( $required ) );
			}
		}

		if ( isset( $schema['additionalProperties'] ) && is_array( $schema['additionalProperties'] ) ) {
			$sanitized['additionalProperties'] = self::sanitize_schema( $schema['additionalProperties'] );
		}

		return $sanitized;
	}

	/**
	 * Normalize a schema `type` value to valid JSON Schema types.
	 *
	 * @since 10.1.0
	 *
	 * @param array        $schema The schema node being built.
	 * @param string|array $type   The type value to normalize.
	 * @return array Schema with a normalized type (or no type when invalid).
	 */
	private static function normalize_type( $schema, $type ) {

		if ( is_string( $type ) ) {
			if ( in_array( $type, self::$valid_types, true ) ) {
				$schema['type'] = $type;
			}
			return $schema;
		}

		if ( is_array( $type ) ) {

			$normalized = array_values( array_unique( array_intersect( $type, self::$valid_types ) ) );

			if ( 1 === count( $normalized ) ) {
				$schema['type'] = $normalized[0];
			} elseif ( $normalized ) {
				$schema['type'] = $normalized;
			}
		}

		return $schema;
	}

	/**
	 * Relax an output schema so it accepts the shapes controllers actually return.
	 *
	 * The Abilities API strictly validates execution output against the output schema.
	 * REST item schemas occasionally disagree with real response shapes (nullable fields,
	 * date strings without timezone suffixes, etc.), so on output we widen scalar types
	 * to a permissive union and strip `format` constraints. Input schemas keep their
	 * tighter constraints.
	 *
	 * @since 10.1.0
	 *
	 * @param array $schema A sanitized JSON Schema node.
	 * @return array Relaxed schema node.
	 */
	private static function relax_output_schema( $schema ) {

		unset( $schema['format'] );

		if ( isset( $schema['type'] ) && self::should_widen_type( $schema['type'] ) ) {
			$schema['type'] = self::$valid_types;
			unset( $schema['enum'] );
		}

		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $key => $property ) {
				$schema['properties'][ $key ] = self::relax_output_schema( $property );
			}
		}

		if ( isset( $schema['items'] ) && is_array( $schema['items'] ) ) {
			$schema['items'] = self::relax_output_schema( $schema['items'] );
		}

		if ( isset( $schema['additionalProperties'] ) && is_array( $schema['additionalProperties'] ) ) {
			$schema['additionalProperties'] = self::relax_output_schema( $schema['additionalProperties'] );
		}

		unset( $schema['required'] );

		return $schema;
	}

	/**
	 * Determine whether an output-schema type should be widened to the permissive union.
	 *
	 * Single scalar types and unions composed solely of scalars and/or `null` are widened.
	 * Types declaring compound members (`object`, `array`) are left alone.
	 *
	 * @since 10.1.0
	 *
	 * @param string|array $type Schema `type` value.
	 * @return boolean
	 */
	private static function should_widen_type( $type ) {

		if ( is_string( $type ) ) {
			return in_array( $type, self::$scalar_types, true );
		}

		if ( ! is_array( $type ) || ! $type ) {
			return false;
		}

		$widenable = array_merge( self::$scalar_types, array( 'null' ) );

		return ! array_diff( $type, $widenable );
	}
}
