<?php
/**
 * DocsCommand class file
 *
 * Forked with love from wp-cli/handbook.
 *
 * @package LifterLMS/CLI/Docs;
 *
 * @since 0.0.1
 * @version 0.0.1
 *
 * @link https://github.com/wp-cli/handbook/blob/master/bin/command.php
 */
namespace LifterLMS\CLI\Docs;

use WP_CLI;
use Mustache_Engine;
use WP_CLI\Utils;

/**
 * WP-CLI commands to generate docs from the codebase.
 */

define( 'LLMS_CLI_ROOT_DIR', dirname( dirname( __FILE__ ) ) );
define( 'LLMS_CLI_DOCS_PATH', LLMS_CLI_ROOT_DIR . '/docs' );

// Bootstrap additional files necessary
\WP_CLI::add_hook( 'before_run_command', function() {

	// Load WordPress (so that the REST API is available for generating restful command docs).
	require_once ABSPATH . 'wp-load.php';

	// Include commands.
	require_once LLMS_CLI_ROOT_DIR . '/src/commands.php';

} );

/**
 * Generate documentation for LifterLMS CLI commands
 *
 * @since 0.0.1
 *
 * @when before_wp_load
 */
class Command {

	/**
	 * Regenerates all doc pages.
	 *
	 * ## OPTIONS
	 *
	 * [--verbose]
	 * : If set will list command pages as they are generated.
	 *
	 * @subcommand gen-all
	 */
	public function gen_all( $args, $assoc_args ) {

		self::gen_commands( $args, $assoc_args );
		self::gen_commands_manifest();

		WP_CLI::success( 'Generated all doc pages.' );
	}

	/**
	 * Generates all command pages.
	 *
	 * ## OPTIONS
	 *
	 * [--verbose]
	 * : If set will list command pages as they are generated.
	 *
	 * @subcommand gen-commands
	 */
	public function gen_commands( $args, $assoc_args ) {

		self::empty_dir( LLMS_CLI_DOCS_PATH . '/commands/' );

		$wp = WP_CLI::runcommand( 'cli cmd-dump', array( 'launch' => false, 'return' => 'stdout', 'parse' => 'json' ) );

		$verbose = Utils\get_flag_value( $assoc_args, 'verbose', false );

		foreach ( $wp['subcommands'] as $cmd ) {
			if ( ! in_array( $cmd['name'], array( 'llms' ), true ) ) {
				continue;
			}
			self::gen_cmd_pages( $cmd, array() /*parent*/, $verbose );
		}

		WP_CLI::success( 'Generated all command pages.' );
	}

	/**
	 * Update the commands data array with new data
	 */
	private static function update_commands_data( $command, &$commands_data, $full ) {
		$reflection = new \ReflectionClass( $command );
		$repo_url = '';
		if ( 'help' === substr( $full, 0, 4 )
			|| 'cli' === substr( $full, 0, 3 ) ) {
			$repo_url = 'https://github.com/wp-cli/wp-cli';
		}
		if ( $reflection->hasProperty( 'when_invoked' ) ) {
			$filename = '';
			$when_invoked = $reflection->getProperty( 'when_invoked' );
			$when_invoked->setAccessible( true );
			$closure = $when_invoked->getValue( $command );
			$closure_reflection = new \ReflectionFunction( $closure );
			// PHP stores use clause arguments of closures as static variables internally - see https://bugs.php.net/bug.php?id=71250
			$static = $closure_reflection->getStaticVariables();
			if ( is_array( $static ) && isset( $static['callable'] ) ) {
				// See `CommandFactory::create_subcommand()`.
				if ( is_array( $static['callable'] ) && isset( $static['callable'][0] ) ) {
					$reflection_class = new \ReflectionClass( $static['callable'][0] );
					$filename = $reflection_class->getFileName();
				} elseif ( is_callable( $static['callable'] ) ) {
					$reflection_func = new \ReflectionFunction( $static['callable'] );
					$filename = $reflection_func->getFileName();
				}
			}
			if ( $filename ) {
				preg_match( '#vendor/([^/]+/[^/]+)#', $filename, $matches );
				if ( ! empty( $matches[1] ) ) {
					$repo_url = 'https://github.com/' . $matches[1];
				}
			} else {
				WP_CLI::error( 'No callable for: ' . var_export( $static, true ) );
			}
		}
		foreach ( $command->get_subcommands() as $subcommand ) {
			$sub_full = trim( $full . ' ' . $subcommand->get_name() );
			self::update_commands_data( $subcommand, $commands_data, $sub_full );
			if ( '' === $repo_url && isset( $commands_data[ $sub_full ]['repo_url'] ) ) {
				$repo_url = $commands_data[ $sub_full ]['repo_url'];
			}
		}
		if ( $repo_url ) {
			$commands_data[ $full ] = array(
				'repo_url' => $repo_url,
			);
		}
	}

	/**
	 * Generates a manifest document of all command pages.
	 *
	 * @subcommand gen-commands-manifest
	 */
	public function gen_commands_manifest() {
		$manifest = array();
		$paths = array(
			LLMS_CLI_DOCS_PATH . '/commands/*.md',
			LLMS_CLI_DOCS_PATH . '/commands/*/*.md',
			LLMS_CLI_DOCS_PATH . '/commands/*/*/*.md'
		);
		$commands_data = array();
		foreach( WP_CLI::get_root_command()->get_subcommands() as $command ) {
			self::update_commands_data( $command, $commands_data, $command->get_name() );
		}
		foreach( $paths as $path ) {
			foreach( glob( $path ) as $file ) {
				$slug = basename( $file, '.md' );
				$cmd_path = str_replace( array( LLMS_CLI_DOCS_PATH . '/commands/', '.md' ), '', $file );
				$title = '';
				$contents = file_get_contents( $file );
				if ( preg_match( '/^#\swp\s(.+)/', $contents, $matches ) ) {
					$title = $matches[1];
				}
				$parent = null;
				if ( stripos( $cmd_path, '/' ) ) {
					$bits = explode( '/', $cmd_path );
					array_pop( $bits );
					$parent = implode( '/', $bits );
				}
				$manifest[ $cmd_path ] = array(
					'title'           => $title,
					'slug'            => $slug,
					'cmd_path'        => $cmd_path,
					'parent'          => $parent,
					'markdown_source' => sprintf( 'https://github.com/gocodebox/lifterlms-cli/blob/trunk/docs/commands/%s.md', $cmd_path ),
				);
				if ( ! empty( $commands_data[ $title ] ) ) {
					$manifest[ $cmd_path ] = array_merge( $manifest[ $cmd_path ], $commands_data[ $title ] );
				}
			}
		}
		file_put_contents( LLMS_CLI_DOCS_PATH . '/commands-manifest.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		$count = count( $manifest );
		WP_CLI::success( "Generated bin/commands-manifest.json of {$count} commands" );
	}

	private static function gen_cmd_pages( $cmd, $parent = array(), $verbose = false ) {
		$parent[] = $cmd['name'];

		static $params;
		if ( ! isset( $params ) ) {
			$params = WP_CLI::runcommand( 'cli param-dump', array( 'launch' => false, 'return' => 'stdout', 'parse' => 'json' ) );
			// Preserve positioning of 'url' param.
			$url_param = $params['url'];
			unset( $params['url'] );
			$new_params = array();
			foreach( $params as $param => $meta ) {
				$new_params[ $param ] = $meta;
				if ( 'path' === $param ) {
					$new_params['url'] = $url_param;
				}
			}
			$params = $new_params;
		}

		$binding = $cmd;
		$binding['synopsis'] = implode( ' ', $parent );
		$binding['path'] = implode( '/', $parent );
		$path = '/commands/';
		$binding['breadcrumbs'] = '[Commands](' . $path . ')';
		foreach( $parent as $i => $p ) {
			$path .= $p . '/';
			if ( $i < ( count( $parent ) - 1 ) ) {
				$binding['breadcrumbs'] .= " &raquo; [{$p}]({$path})";
			} else {
				$binding['breadcrumbs'] .= " &raquo; {$p}";
			}
		}
		$binding['has-subcommands'] = isset( $cmd['subcommands'] ) ? array(true) : false;

		if ( $cmd['longdesc'] ) {
			$docs = $cmd['longdesc'];
			$docs = htmlspecialchars( $docs, ENT_COMPAT, 'UTF-8' );

			// Decrease header level.
			$docs = preg_replace( '/^## /m', '### ', $docs );

			// Escape `--` so that it doesn't get converted into `&mdash;`.
			$docs = preg_replace( '/^(\[?)--/m', '\1\--', $docs );
			$docs = preg_replace( '/^\s\s--/m', '  \1\--', $docs );

			// Remove word wrapping from docs
			// Match words, '().,;', and --arg before/after the newline.
			$bits = explode( "\n", $docs );
			$in_yaml_doc = $in_code_bloc = false;
			for ( $i=0; $i < count( $bits ); $i++ ) {
				if ( ! isset( $bits[ $i ] ) || ! isset( $bits[ $i + 1 ] ) ) {
					continue;
				}
				if ( '---' === $bits[ $i ] || '\---' === $bits[ $i ] ) {
					$in_yaml_doc = ! $in_yaml_doc;
				}
				if ( '```' === $bits[ $i ] ) {
					$in_code_bloc = ! $in_code_bloc;
				}
				if ( $in_yaml_doc || $in_code_bloc ) {
					continue;
				}

				if ( preg_match( '#([\w\(\)\.\,\;]|[`]{1})$#', $bits[ $i ] )
					&& preg_match( '#^([\w\(\)\.\,\;`]|\\\--[\w]|[`]{1})#', $bits[ $i + 1 ] ) ) {
					$bits[ $i ] .= ' ' . $bits[ $i + 1 ];
					unset( $bits[ $i + 1 ] );
					--$i;
					$bits = array_values( $bits );
				}
			}
			$docs = implode( "\n", $bits );

			// Hack to prevent double encoding in code blocks.
			$docs = preg_replace( '/ &lt; /', ' < ', $docs );
			$docs = preg_replace( '/ &gt; /', ' > ', $docs );
			$docs = preg_replace( '/ &lt;&lt;/', ' <<', $docs );
			$docs = preg_replace( '/&quot;/', '"', $docs );
			$docs = preg_replace( '/wp&gt; /', 'wp> ', $docs );
			$docs = preg_replace( '/=&gt;/', '=>', $docs );

			$global_parameters = <<<EOT
These [global parameters](https://make.wordpress.org/cli/handbook/config/) have the same behavior across all commands and affect how WP-CLI interacts with WordPress.

| **Argument**    | **Description**              |
|:----------------|:-----------------------------|
EOT;
			foreach( $params as $param => $meta ) {
				if ( false === $meta['runtime']
					|| empty( $meta['desc'] )
					|| ! empty( $meta['deprecated'] ) ) {
					continue;
				}
				$param_arg = '--' . $param;
				if ( ! empty( $meta['runtime'] ) && true !== $meta['runtime'] ) {
					$param_arg .= $meta['runtime'];
				}
				if ( 'color' === $param ) {
					$param_arg = '--[no-]color';
				}
				$global_parameters .= PHP_EOL . '| `' . str_replace( '|', '\\|', $param_arg ) . '` | ' . str_replace( '|', '\\|', $meta['desc'] ) . ' |';
			}

			// Replace Global parameters with a nice table.
			if ( $binding['has-subcommands'] ) {
				$replace_global = '';
			} else {
				$replace_global = '$1' . PHP_EOL . PHP_EOL . $global_parameters;
			}
			$docs = preg_replace( '/(#?## GLOBAL PARAMETERS).+/s', $replace_global, $docs );

			$binding['docs'] = $docs;
		}

		$path = LLMS_CLI_DOCS_PATH . "/commands/" . $binding['path'];
		if ( !is_dir( dirname( $path ) ) ) {
			mkdir( dirname( $path ) );
		}
		file_put_contents( "$path.md", self::render( 'subcmd-list.mustache', $binding ) );
		if ( $verbose ) {
			WP_CLI::log( 'Generated commands/' . $binding['path'] . '/' );
		}

		if ( !isset( $cmd['subcommands'] ) )
			return;

		foreach ( $cmd['subcommands'] as $subcmd ) {
			self::gen_cmd_pages( $subcmd, $parent, $verbose );
		}
	}

	/**
	 * Get a simple representation of a function or method
	 *
	 * @param Reflection
	 * @return array
	 */
	private static function get_simple_representation( $reflection ) {
		$signature = $reflection->getName();
		$parameters = array();
		foreach( $reflection->getParameters() as $parameter ) {
			$parameter_signature = '$' . $parameter->getName();
			if ( $parameter->isOptional() ) {
				$default_value = $parameter->getDefaultValue();
				if ( false === $default_value ) {
					$parameter_signature .= ' = false';
				} else if ( array() === $default_value ) {
					$parameter_signature .= ' = array()';
				} else if ( '' === $default_value ) {
					$parameter_signature .= " = ''";
				} else if ( null === $default_value ) {
					$parameter_signature .= ' = null';
				} else if ( true === $default_value ) {
					$parameter_signature .= ' = true';
				} else {
					$parameter_signature .= ' = ' . $default_value;
				}
			}
			$parameters[] = $parameter_signature;
		}
		if ( ! empty( $parameters ) ) {
			$signature = $signature . '( ' . implode( ', ', $parameters ) . ' )';
		} else {
			$signature = $signature . '()';
		}
		$phpdoc = $reflection->getDocComment();
		$type = strtolower( str_replace( 'Reflection', '', get_class( $reflection ) ) );
		$class = '';
		switch ( $type ) {
			case 'function':
				$full_name = $reflection->getName();
				break;
			case 'method':
				$separator = $reflection->isStatic() ? '::' : '->';
				$class = $reflection->class;
				$full_name = $class . $separator . $reflection->getName();
				$signature = $class . $separator . $signature;
				break;
		}
		return array(
			'phpdoc'       => self::parse_docblock( $phpdoc ),
			'type'         => $type,
			'signature'    => $signature,
			'short_name'   => $reflection->getShortName(),
			'full_name'    => $full_name,
			'class'        => $class,
		);
	}

	/**
	 * Parse PHPDoc into a structured representation.
	 * 
	 * @param string $docblock
	 * @return array
	 */
	private static function parse_docblock( $docblock ) {
		$ret = array(
			'description' => '',
			'parameters'  => array(),
		);
		$extra_line = '';
		$in_param = false;
		foreach( preg_split("/(\r?\n)/", $docblock ) as $line ){
			if ( preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches ) ) {
				$info = trim( $matches[1] );
				$info = preg_replace( '/^(\*\s+?)/', '', $info );
				if ( $in_param ) {
					list( $param, $key ) = $in_param;
					$ret['parameters'][ $param_name ][ $key ][2] .= PHP_EOL . $info;
					if ( '}' === substr( $info, -1 ) ) {
						$in_param = false;
					}
				} else if ( $info[0] !== "@" ) {
					$ret['description'] .= PHP_EOL . "{$extra_line}{$info}";
				} else {
					preg_match( '/@(\w+)/', $info, $matches );
					$param_name = $matches[1];
					$value = str_replace( "@$param_name ", '', $info );
					if ( ! isset( $ret['parameters'][ $param_name ] ) ) {
						$ret['parameters'][ $param_name ] = array();
					}
					$ret['parameters'][ $param_name ][] = preg_split( '/[\s]+/', $value, 3 );
					end( $ret['parameters'][ $param_name ] );
					$key = key( $ret['parameters'][ $param_name ] );
					reset( $ret['parameters'][ $param_name ] );
					if ( ! empty( $ret['parameters'][ $param_name ][ $key ][ 2 ] )
						&& '{' === substr( $ret['parameters'][ $param_name ][ $key ][ 2 ] , -1 ) ) {
						$in_param = array( $param_name, $key );
					}
				}
				$extra_line = '';
			} else {
				$extra_line .= PHP_EOL;
			}
		}
		$ret['description'] = str_replace( '\/', '/', trim( $ret['description'], PHP_EOL ) );
		$bits = explode( PHP_EOL, $ret['description'] );
		$short_desc = array( array_shift( $bits ) );
		while ( isset( $bits[0] ) && ! empty( $bits[0] ) ) {
			$short_desc[] = array_shift( $bits );
		}
		$ret['short_description'] = trim( implode( ' ', $short_desc ) );
		$long_description = trim( implode( PHP_EOL, $bits ), PHP_EOL );
		$ret['long_description'] = $long_description;
		return $ret;
	}

	private static function render( $path, $binding ) {
		$m = new Mustache_Engine;
		$template = file_get_contents( LLMS_CLI_ROOT_DIR . "/bin/templates/$path" );
		return $m->render( $template, $binding );
	}

	/**
	 * Removes existing contents of given directory.
	 *
	 * @param string $dir Name of directory to empty.
	 */
	private static function empty_dir( $dir ) {
		$cmd = Utils\esc_cmd( 'rm -rf %s', $dir );
		$pr = WP_CLI::launch( $cmd, false /*exit_on_error*/, true /*return_detailed*/ ); // Won't fail if directory doesn't exist.
		if ( $pr->return_code ) {
			WP_CLI::error( sprintf( "Failed to `%s`: (%d) %s", $cmd, $pr->return_code, $pr->stderr ) );
		}
		if ( ! mkdir( $dir ) ) {
			$error = error_get_last();
			WP_CLI::error( sprintf( "Failed to create '%s' directory: %s", $dir, $error['message'] ) );
		}
		WP_CLI::log( sprintf( "Removed existing contents of '%s'", $dir ) );
	}
}

WP_CLI::add_command( 'llms-docs', '\LifterLMS\CLI\Docs\Command' );

