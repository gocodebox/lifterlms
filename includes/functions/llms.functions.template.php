<?php
/**
 * LifterLMS Template functions
 *
 * @package LifterLMS/Functions
 *
 * @since Unknown
 * @version 7.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get template part
 *
 * @since Unknown
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name Optional. The name of the specialised template. Default is empty string.
 * @return void
 */
function llms_get_template_part( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = llms_locate_template( "{$slug}-{$name}.php", llms()->template_path() . "{$slug}-{$name}.php" );
	}

	// Get default slug-name.php.
	if ( ! $template && $name && file_exists( llms()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = llms()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = llms_locate_template( "{$slug}.php", llms()->template_path() . "{$slug}.php" );
	}

	/**
	 * Filters the template file path
	 *
	 * Allow 3rd party plugin filter template file from their plugin.
	 *
	 * @since Unknown
	 *
	 * @param string $template The path to the template file.
	 * @param string $slug     The slug name for the generic template.
	 * @param stirng $name     The name of the specialised template.
	 */
	$template = apply_filters( 'llms_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get Template part contents
 *
 * @since Unknown
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name Optional. The name of the specialised template. Default is empty string.
 * @return string
 */
function llms_get_template_part_contents( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = llms_locate_template( "{$slug}-{$name}.php", llms()->template_path() . "{$slug}-{$name}.php" );
	}

	// Get default slug-name.php.
	if ( ! $template && $name && file_exists( llms()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = llms()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = llms_locate_template( "{$slug}.php", llms()->template_path() . "{$slug}.php" );
	}

	if ( $template ) {
		return $template;
	}
}

/**
 * Get Template Part
 *
 * @since 1.0.0
 * @since 3.16.0 Unknown
 *
 * @param string $template_name Name of template.
 * @param array  $args          Array of arguments accessible from the template.
 * @param string $template_path Optional. Dir path to template. Default is empty string.
 *                              If not supplied the one retrived from `llms()->template_path()` will be used.
 * @param string $default_path  Optional. Default path is empty string.
 *                              If not supplied the template path is `llms()->plugin_path() . '/templates/'`.
 * @return void
 */
function llms_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = llms_locate_template( $template_name, $template_path, $default_path );

	/**
	 * Fired before a template part is included
	 *
	 * @since Unknown
	 *
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template as passed to the `llms_get_template()` function.
	 * @param string $located       The full path of the template file to load.
	 * @param array  $args          Array of arguments accessible from the template.
	 */
	do_action( 'lifterlms_before_template_part', $template_name, $template_path, $located, $args );

	if ( file_exists( $located ) ) {
		include $located;
	}

	/**
	 * Fired after a template part is included
	 *
	 * @since Unknown
	 *
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template as passed to the `llms_get_template()` function.
	 * @param string $located       The full path of the (maybe) loaded template file.
	 * @param array  $args          Array of arguments accessible from the template.
	 */
	do_action( 'lifterlms_after_template_part', $template_name, $template_path, $located, $args );
}


function llms_get_template_ajax( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	ob_start();
	llms_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();

}

/**
 * Locate Template
 *
 * @param string $template_name Name of template.
 * @param string $template_path Optional. Dir path to template. Default is empty string.
 *                              If not supplied the one retrived from `llms()->template_path()` will be used.
 * @param string $default_path  Optional. Default path is empty string.
 *                              If not supplied the template path is `llms()->plugin_path() . '/templates/'`.
 * @return string
 *
 * @since 1.0.0
 * @since 3.0.0 Only returns path if template exists.
 */
function llms_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = llms()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = llms()->plugin_path() . '/templates/';
	}

	// Check theme and template directories for the template.
	$override_path = llms_get_template_override( $template_name );

	// Get default template.
	$path = ( $override_path ) ? $override_path : $default_path;

	$template = $path . $template_name;

	if ( ! file_exists( $template ) ) {

		$template = '';

	}

	/**
	 * Filters the maybe located template file path
	 *
	 * Allow 3rd party plugin filter template file from their plugin.
	 *
	 * @since Unknown
	 *
	 * @param string $template      The path to the template file. Empty string if no template found.
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template.
	 */
	return apply_filters( 'lifterlms_locate_template', $template, $template_name, $template_path );
}

/**
 * Get template override.
 *
 * @since Unknown
 * @since 4.8.0 Move template override directories logic into llms_get_template_override_directories.
 *
 * @param string $template Template file.
 * @return mixed Template file directory or false if none exists.
 */
function llms_get_template_override( $template = '' ) {

	$dirs = llms_get_template_override_directories();

	foreach ( $dirs as $dir ) {

		$path = $dir . '/';
		if ( file_exists( "{$path}{$template}" ) ) {
			return $path;
		}
	}

	return false;
}

/**
 * Get template override directories.
 *
 * Moved from `llms_get_template_override()`.
 *
 * @since 4.8.0
 *
 * @return string[]
 */
function llms_get_template_override_directories() {

	$dirs = wp_cache_get( 'theme-override-directories', 'llms_template_functions' );
	if ( false === $dirs ) {
		$dirs = array_filter(
			array_unique(
				array(
					get_stylesheet_directory() . '/lifterlms',
					get_template_directory() . '/lifterlms',
				)
			),
			'is_dir'
		);
		wp_cache_set( 'theme-override-directories', $dirs, 'llms_template_functions' );
	}

	/**
	 * Filters the theme override directories.
	 *
	 * Allow themes and plugins to determine which folders to look in for theme overrides.
	 *
	 * @since Unknown
	 *
	 * @param string[] $theme_override_directories List of theme override directory paths.
	 */
	return apply_filters( 'lifterlms_theme_override_directories', $dirs );

}

/**
 * Build the plugin's template file path.
 *
 * @since 5.8.0
 * @since 7.2.0 Do not add leading slash to absolute template directory.
 *
 * @param string $template                    Template file name.
 * @param string $template_directory          Template directory relative to the plugin base directory.
 * @param bool   $template_directory_absolute Whether the template directory is absolute or not.
 * @return string
 */
function llms_template_file_path( $template, $template_directory = 'templates', $template_directory_absolute = false ) {

	// We have reason to use a LifterLMS template, check if there's an override we should use from a theme / etc...
	$override           = llms_get_template_override( $template );
	$template_directory = $template_directory_absolute ? $template_directory : llms()->plugin_path() . "/{$template_directory}/";
	$template_path      = $override ? $override : $template_directory;

	return trailingslashit( $template_path ) . "{$template}";

}
