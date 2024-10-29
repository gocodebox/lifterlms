<?php
/**
 * LifterLMS CLI Restful Command file
 *
 * Forked from wp-cli/restful (by Daniel Bachhuber, released under the MIT license https://opensource.org/licenses/MIT).
 * https://github.com/wp-cli/restful
 *
 * @package LifterLMS_CLI/Classes
 *
 * @since 0.0.1
 * @version 0.0.1
 *
 * @link https://github.com/wp-cli/restful/blob/master/inc/RestCommand.php
 * @link https://github.com/wp-cli/restful/commit/021f1731c737fc1cb36ee06f0c34b73eb0d6aabb
 */

namespace LifterLMS\CLI\Commands\Restful;

/**
 * LifterLMS CLI Restful Commands
 *
 * @since 0.0.1
 */
class Command {

	private $scope   = 'internal';
	private $api_url = '';
	private $auth    = array();
	private $name;
	private $route;
	private $resource_identifier;
	private $schema;
	private $default_context      = '';
	private $output_nesting_level = 0;

	public function __construct( $name, $route, $schema ) {
		$this->name                = $name;
		$parsed_args               = preg_match_all( '#\([^\)]+\)#', $route, $matches );
		$this->resource_identifier = ! empty( $matches[0] ) ? array_pop( $matches[0] ) : null;
		$this->route               = rtrim( $route );
		$this->schema              = $schema;
	}

	/**
	 * Create a new item.
	 *
	 * @subcommand create
	 */
	public function create_item( $args, $assoc_args ) {
		list( $status, $body ) = $this->do_request( 'POST', $this->get_base_route(), $assoc_args );
		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			\WP_CLI::line( $body['id'] );
		} else {
			\WP_CLI::success( "Created {$this->name} {$body['id']}." );
		}
	}

	/**
	 * Generate some items.
	 *
	 * @subcommand generate
	 */
	public function generate_items( $args, $assoc_args ) {

		$count = $assoc_args['count'];
		unset( $assoc_args['count'] );
		$format = $assoc_args['format'];
		unset( $assoc_args['format'] );

		$notify = false;
		if ( 'progress' === $format ) {
			$notify = \WP_CLI\Utils\make_progress_bar( 'Generating items', $count );
		}

		for ( $i = 0; $i < $count; $i++ ) {

			list( $status, $body ) = $this->do_request( 'POST', $this->get_base_route(), $assoc_args );

			if ( 'progress' === $format ) {
				$notify->tick();
			} elseif ( 'ids' === $format ) {
				echo esc_html( $body['id'] );
				if ( $i < $count - 1 ) {
					echo ' ';
				}
			}
		}

		if ( 'progress' === $format ) {
			$notify->finish();
		}
	}

	/**
	 * Delete an existing item.
	 *
	 * @subcommand delete
	 */
	public function delete_item( $args, $assoc_args ) {
		list( $status, $body ) = $this->do_request( 'DELETE', $this->get_filled_route( $args ), $assoc_args );
		$id                    = isset( $body['previous'] ) ? $body['previous']['id'] : $body['id'];
		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			\WP_CLI::line( $id );
		} else {
			if ( empty( $assoc_args['force'] ) ) {
				\WP_CLI::success( "Trashed {$this->name} {$id}." );
			} else {
				\WP_CLI::success( "Deleted {$this->name} {$id}." );
			}
		}
	}

	/**
	 * Get a single item.
	 *
	 * @subcommand get
	 */
	public function get_item( $args, $assoc_args ) {
		list( $status, $body, $headers ) = $this->do_request( 'GET', $this->get_filled_route( $args ), $assoc_args );

		if ( ! empty( $assoc_args['fields'] ) ) {
			$body = self::limit_item_to_fields( $body, $fields );
		}

		if ( 'headers' === $assoc_args['format'] ) {
			echo json_encode( $headers );
		} elseif ( 'body' === $assoc_args['format'] ) {
			echo json_encode( $body );
		} elseif ( 'envelope' === $assoc_args['format'] ) {
			echo json_encode(
				array(
					'body'    => $body,
					'headers' => $headers,
					'status'  => $status,
					'api_url' => $this->api_url,
				)
			);
		} else {
			$formatter = $this->get_formatter( $assoc_args );
			$formatter->display_item( $body );
		}
	}

	/**
	 * List all items.
	 *
	 * @subcommand list
	 */
	public function list_items( $args, $assoc_args ) {
		if ( ! empty( $assoc_args['format'] ) && 'count' === $assoc_args['format'] ) {
			$method = 'HEAD';
		} else {
			$method = 'GET';
		}
		list( $status, $body, $headers ) = $this->do_request( $method, $this->get_base_route(), $assoc_args );
		if ( ! empty( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			$items = array_column( $body, 'id' );
		} else {
			$items = $body;
		}

		if ( ! empty( $assoc_args['fields'] ) ) {
			foreach ( $items as $key => $item ) {
				$items[ $key ] = self::limit_item_to_fields( $item, $fields );
			}
		}

		if ( ! empty( $assoc_args['format'] ) && 'count' === $assoc_args['format'] ) {
			echo (int) $headers['X-WP-Total'];
		} elseif ( 'headers' === $assoc_args['format'] ) {
			echo json_encode( $headers );
		} elseif ( 'body' === $assoc_args['format'] ) {
			echo json_encode( $body );
		} elseif ( 'envelope' === $assoc_args['format'] ) {
			echo json_encode(
				array(
					'body'    => $body,
					'headers' => $headers,
					'status'  => $status,
					'api_url' => $this->api_url,
				)
			);
		} else {
			$formatter = $this->get_formatter( $assoc_args );
			$formatter->display_items( $items );
		}
	}

	/**
	 * Compare items between environments.
	 *
	 * <alias>
	 * : Alias for the WordPress site to compare to.
	 *
	 * [<resource>]
	 * : Limit comparison to a specific resource, instead of the collection.
	 *
	 * [--fields=<fields>]
	 * : Limit comparison to specific fields.
	 *
	 * @subcommand diff
	 */
	public function diff_items( $args, $assoc_args ) {

		list( $alias ) = $args;
		if ( ! array_key_exists( $alias, \WP_CLI::get_runner()->aliases ) ) {
			\WP_CLI::error( "Alias '{$alias}' not found." );
		}
		$resource = isset( $args[1] ) ? $args[1] : null;
		$fields   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'fields', null );

		list( $from_status, $from_body, $from_headers ) = $this->do_request( 'GET', $this->get_base_route(), array() );

		$php_bin          = \WP_CLI::get_php_binary();
		$script_path      = $GLOBALS['argv'][0];
		$other_args       = implode( ' ', array_map( 'escapeshellarg', array( $alias, 'rest', $this->name, 'list' ) ) );
		$other_assoc_args = \WP_CLI\Utils\assoc_args_to_str( array( 'format' => 'envelope' ) );
		$full_command     = "{$php_bin} {$script_path} {$other_args} {$other_assoc_args}";
		$process          = \WP_CLI\Process::create(
			$full_command,
			null,
			array(
				'HOME'                => getenv( 'HOME' ),
				'WP_CLI_PACKAGES_DIR' => getenv( 'WP_CLI_PACKAGES_DIR' ),
				'WP_CLI_CONFIG_PATH'  => getenv( 'WP_CLI_CONFIG_PATH' ),
			)
		);
		$result           = $process->run();
		$response         = json_decode( $result->stdout, true );
		$to_headers       = $response['headers'];
		$to_body          = $response['body'];
		$to_api_url       = $response['api_url'];

		if ( ! is_null( $resource ) ) {
			$field    = is_numeric( $resource ) ? 'id' : 'slug';
			$callback = function( $value ) use ( $field, $resource ) {
				if ( isset( $value[ $field ] ) && $resource == $value[ $field ] ) {
					return true;
				}
				return false;
			};
			foreach ( array( 'to_body', 'from_body' ) as $response_type ) {
				$$response_type = array_filter( $$response_type, $callback );
			}
		}

		$display_items = array();
		do {
			$from_item = $to_item = array();
			if ( ! empty( $from_body ) ) {
				$from_item = array_shift( $from_body );
				if ( ! empty( $to_body ) && ! empty( $from_item['slug'] ) ) {
					foreach ( $to_body as $i => $item ) {
						if ( ! empty( $item['slug'] ) && $item['slug'] === $from_item['slug'] ) {
							$to_item = $item;
							unset( $to_body[ $i ] );
							break;
						}
					}
				}
			} elseif ( ! empty( $to_body ) ) {
				$to_item = array_shift( $to_body );
			}

			if ( ! empty( $to_item ) ) {
				foreach ( array( 'to_item', 'from_item' ) as $item ) {
					if ( isset( $$item['_links'] ) ) {
						unset( $$item['_links'] );
					}
				}
				$display_items[] = array(
					'from' => self::limit_item_to_fields( $from_item, $fields ),
					'to'   => self::limit_item_to_fields( $to_item, $fields ),
				);
			}
		} while ( count( $from_body ) || count( $to_body ) );

		\WP_CLI::line( \cli\Colors::colorize( "%R(-) {$this->api_url} %G(+) {$to_api_url}%n" ) );
		foreach ( $display_items as $display_item ) {
			$this->show_difference(
				$this->name,
				array(
					'from' => $display_item['from'],
					'to'   => $display_item['to'],
				)
			);
		}
	}

	/**
	 * Update an existing item.
	 *
	 * @subcommand update
	 */
	public function update_item( $args, $assoc_args ) {
		list( $status, $body ) = $this->do_request( 'POST', $this->get_filled_route( $args ), $assoc_args );
		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'porcelain' ) ) {
			\WP_CLI::line( $body['id'] );
		} else {
			\WP_CLI::success( "Updated {$this->name} {$body['id']}." );
		}
	}

	/**
	 * Open an existing item in the editor
	 *
	 * @subcommand edit
	 */
	public function edit_item( $args, $assoc_args ) {
		$assoc_args['context']         = 'edit';
		list( $status, $options_body ) = $this->do_request( 'OPTIONS', $this->get_filled_route( $args ), $assoc_args );
		if ( empty( $options_body['schema'] ) ) {
			\WP_CLI::error( 'Cannot edit - no schema found for resource.' );
		}
		$schema                           = $options_body['schema'];
		list( $status, $resource_fields ) = $this->do_request( 'GET', $this->get_filled_route( $args ), $assoc_args );
		$editable_fields                  = array();
		foreach ( $resource_fields as $key => $value ) {
			if ( ! isset( $schema['properties'][ $key ] ) || ! empty( $schema['properties'][ $key ]['readonly'] ) ) {
				continue;
			}
			$properties = $schema['properties'][ $key ];
			if ( isset( $properties['properties'] ) ) {
				$parent_key = $key;
				$properties = $properties['properties'];
				foreach ( $value as $key => $value ) {
					if ( isset( $properties[ $key ] ) && empty( $properties[ $key ]['readonly'] ) ) {
						if ( ! isset( $editable_fields[ $parent_key ] ) ) {
							$editable_fields[ $parent_key ] = array();
						}
						$editable_fields[ $parent_key ][ $key ] = $value;
					}
				}
				continue;
			}
			if ( empty( $properties['readonly'] ) ) {
				$editable_fields[ $key ] = $value;
			}
		}
		if ( empty( $editable_fields ) ) {
			\WP_CLI::error( 'Cannot edit - no editable fields found on schema.' );
		}
		$ret = \WP_CLI\Utils\launch_editor_for_input( \Spyc::YAMLDump( $editable_fields ), sprintf( 'Editing %s %s', $schema['title'], $args[0] ) );
		if ( false === $ret ) {
			\WP_CLI::warning( 'No edits made.' );
		} else {
			list( $status, $body ) = $this->do_request( 'POST', $this->get_filled_route( $args ), \Spyc::YAMLLoadString( $ret ) );
			\WP_CLI::success( "Updated {$schema['title']} {$args[0]}." );
		}
	}

	/**
	 * Do a REST Request
	 *
	 * @param string $method
	 */
	private function do_request( $method, $route, $assoc_args ) {
		if ( 'internal' === $this->scope ) {
			if ( ! defined( 'REST_REQUEST' ) ) {
				define( 'REST_REQUEST', true );
			}
			$request = new \WP_REST_Request( $method, $route );
			if ( in_array( $method, array( 'POST', 'PUT' ) ) ) {
				$request->set_body_params( $assoc_args );
			} else {
				foreach ( $assoc_args as $key => $value ) {
					$request->set_param( $key, $value );
				}
			}
			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				$original_queries = is_array( $GLOBALS['wpdb']->queries ) ? array_keys( $GLOBALS['wpdb']->queries ) : array();
			}
			$response = rest_do_request( $request );
			if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
				$performed_queries = array();
				foreach ( (array) $GLOBALS['wpdb']->queries as $key => $query ) {
					if ( in_array( $key, $original_queries ) ) {
						continue;
					}
					$performed_queries[] = $query;
				}
				usort(
					$performed_queries,
					function( $a, $b ) {
						if ( $a[1] === $b[1] ) {
							return 0;
						}
						return ( $a[1] > $b[1] ) ? -1 : 1;
					}
				);

				$query_count      = count( $performed_queries );
				$query_total_time = 0;
				foreach ( $performed_queries as $query ) {
					$query_total_time += $query[1];
				}
				$slow_query_message = '';
				if ( $performed_queries && 'rest' === \WP_CLI::get_config( 'debug' ) ) {
					$slow_query_message .= '. Ordered by slowness, the queries are:' . PHP_EOL;
					foreach ( $performed_queries as $i => $query ) {
						$i++;
						$bits                = explode( ', ', $query[2] );
						$backtrace           = implode( ', ', array_slice( $bits, 13 ) );
						$seconds             = round( $query[1], 6 );
						$slow_query_message .= <<<EOT
{$i}:
  - {$seconds} seconds
  - {$backtrace}
  - {$query[0]}
EOT;
						$slow_query_message .= PHP_EOL;
					}
				} elseif ( 'rest' !== \WP_CLI::get_config( 'debug' ) ) {
					$slow_query_message = '. Use --debug=rest to see all queries.';
				}
				$query_total_time = round( $query_total_time, 6 );
				\WP_CLI::debug( "REST command executed {$query_count} queries in {$query_total_time} seconds{$slow_query_message}", 'rest' );
			}
			if ( $error = $response->as_error() ) {
				\WP_CLI::error( $error );
			}
			return array( $response->get_status(), $response->get_data(), $response->get_headers() );
		} elseif ( 'http' === $this->scope ) {
			$headers = array();
			if ( ! empty( $this->auth ) && 'basic' === $this->auth['type'] ) {
				$headers['Authorization'] = 'Basic ' . base64_encode( $this->auth['username'] . ':' . $this->auth['password'] );
			}
			if ( 'OPTIONS' === $method ) {
				$method                = 'GET';
				$assoc_args['_method'] = 'OPTIONS';
			}
			$response = \WP_CLI\Utils\http_request( $method, rtrim( $this->api_url, '/' ) . $route, $assoc_args, $headers );
			$body     = json_decode( $response->body, true );
			if ( $response->status_code >= 400 ) {
				if ( ! empty( $body['message'] ) ) {
					\WP_CLI::error( $body['message'] . ' ' . json_encode( array( 'status' => $response->status_code ) ) );
				} else {
					switch ( $response->status_code ) {
						case 404:
							\WP_CLI::error( "No {$this->name} found." );
							break;
						default:
							\WP_CLI::error( 'Could not complete request.' );
							break;
					}
				}
			}
			return array( $response->status_code, json_decode( $response->body, true ), $response->headers->getAll() );
		}
		\WP_CLI::error( 'Invalid scope for REST command.' );
	}

	/**
	 * Get Formatter object based on supplied parameters.
	 *
	 * @param array $assoc_args Parameters passed to command. Determines formatting.
	 * @return \WP_CLI\Formatter
	 */
	protected function get_formatter( &$assoc_args ) {
		if ( ! empty( $assoc_args['fields'] ) ) {
			if ( is_string( $assoc_args['fields'] ) ) {
				$fields = explode( ',', $assoc_args['fields'] );
			} else {
				$fields = $assoc_args['fields'];
			}
		} else {
			if ( ! empty( $assoc_args['context'] ) ) {
				$fields = $this->get_context_fields( $assoc_args['context'] );
			} else {
				$fields = $this->get_context_fields( 'view' );
			}
		}
		return new \WP_CLI\Formatter( $assoc_args, $fields );
	}

	/**
	 * Get a list of fields present in a given context
	 *
	 * @param string $context
	 * @return array
	 */
	private function get_context_fields( $context ) {
		$fields = array();
		foreach ( $this->schema['properties'] as $key => $args ) {
			if ( empty( $args['context'] ) || in_array( $context, $args['context'] ) ) {
				$fields[] = $key;
			}
		}
		return $fields;
	}

	/**
	 * Get the base route for this resource
	 *
	 * @return string
	 */
	private function get_base_route() {
		return substr( $this->route, 0, strlen( $this->route ) - strlen( $this->resource_identifier ) );
	}

	/**
	 * Fill the route based on provided $args
	 */
	private function get_filled_route( $args ) {
		return rtrim( $this->get_base_route(), '/' ) . '/' . $args[0];
	}

	/**
	 * Visually depict the difference between "dictated" and "current"
	 *
	 * @param array
	 */
	private function show_difference( $slug, $difference ) {
		$this->output_nesting_level = 0;
		$this->nested_line( $slug . ': ' );
		$this->recursively_show_difference( $difference['to'], $difference['from'] );
		$this->output_nesting_level = 0;
	}

	/**
	 * Recursively output the difference between "dictated" and "current"
	 */
	private function recursively_show_difference( $dictated, $current = null ) {

		$this->output_nesting_level++;

		if ( $this->is_assoc_array( $dictated ) ) {

			foreach ( $dictated as $key => $value ) {

				if ( $this->is_assoc_array( $value ) || is_array( $value ) ) {

					$new_current = isset( $current[ $key ] ) ? $current[ $key ] : null;
					if ( $new_current ) {
						$this->nested_line( $key . ': ' );
					} else {
						$this->add_line( $key . ': ' );
					}

					$this->recursively_show_difference( $value, $new_current );

				} elseif ( is_string( $value ) ) {

					$pre = $key . ': ';

					if ( isset( $current[ $key ] ) && $current[ $key ] !== $value ) {

						$this->remove_line( $pre . $current[ $key ] );
						$this->add_line( $pre . $value );

					} elseif ( ! isset( $current[ $key ] ) ) {

						$this->add_line( $pre . $value );

					}
				}
			}
		} elseif ( is_array( $dictated ) ) {

			foreach ( $dictated as $value ) {

				if ( ! $current
					|| ! in_array( $value, $current ) ) {
					$this->add_line( '- ' . $value );
				}
			}
		} elseif ( is_string( $value ) ) {

			$pre = $key . ': ';

			if ( isset( $current[ $key ] ) && $current[ $key ] !== $value ) {

				$this->remove_line( $pre . $current[ $key ] );
				$this->add_line( $pre . $value );

			} elseif ( ! isset( $current[ $key ] ) ) {

				$this->add_line( $pre . $value );

			} else {

				$this->nested_line( $pre );

			}
		}

		$this->output_nesting_level--;

	}

	/**
	 * Output a line to be added
	 *
	 * @param string
	 */
	private function add_line( $line ) {
		$this->nested_line( $line, 'add' );
	}

	/**
	 * Output a line to be removed
	 *
	 * @param string
	 */
	private function remove_line( $line ) {
		$this->nested_line( $line, 'remove' );
	}

	/**
	 * Output a line that's appropriately nested
	 */
	private function nested_line( $line, $change = false ) {

		if ( 'add' == $change ) {
			$color = '%G';
			$label = '+ ';
		} elseif ( 'remove' == $change ) {
			$color = '%R';
			$label = '- ';
		} else {
			$color = false;
			$label = false;
		}

		$spaces = ( $this->output_nesting_level * 2 ) + 2;
		if ( $color && $label ) {
			$line   = \cli\Colors::colorize( "{$color}{$label}" ) . $line . \cli\Colors::colorize( '%n' );
			$spaces = $spaces - 2;
		}
		\WP_CLI::line( str_pad( ' ', $spaces ) . $line );
	}

	/**
	 * Whether or not this is an associative array
	 *
	 * @param array
	 * @return bool
	 */
	private function is_assoc_array( $array ) {

		if ( ! is_array( $array ) ) {
			return false;
		}

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}

	/**
	 * Reduce an item to specific fields.
	 *
	 * @param array $item
	 * @param array $fields
	 * @return array
	 */
	private static function limit_item_to_fields( $item, $fields ) {
		if ( empty( $fields ) ) {
			return $item;
		}
		if ( is_string( $fields ) ) {
			$fields = explode( ',', $fields );
		}
		foreach ( $item as $i => $field ) {
			if ( ! in_array( $i, $fields ) ) {
				unset( $item[ $i ] );
			}
		}
		return $item;
	}

}
