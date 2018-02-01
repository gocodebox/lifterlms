<?php
/**
 * Get template part
 * @param  string $slug [url slug of template]
 * @param  string $name [name of template]
 *
 * @return string [name of file]
 */
function llms_get_template_part( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = llms_locate_template( "{$slug}-{$name}.php", LLMS()->template_path() . "{$slug}-{$name}.php" );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = llms_locate_template( "{$slug}.php", LLMS()->template_path() . "{$slug}.php" );
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters( 'llms_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get Template part contents
 *
 * @param  string $slug [url slug]
 * @param  string $name [name of template]
 *
 * @return string [naem of file]
 */
function llms_get_template_part_contents( $slug, $name = '' ) {
	  $template = '';

	if ( $name ) {
		$template = llms_locate_template( "{$slug}-{$name}.php", LLMS()->template_path() . "{$slug}-{$name}.php" );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = llms_locate_template( "{$slug}.php", LLMS()->template_path() . "{$slug}.php" );
	}

	// Allow 3rd party plugin filter template file from their plugin
	if ( $template ) {
		return $template;
	}
}

/**
 * Get Template Part
 *
 * @param    string  $template_name [name of template]
 * @param    array   $args          [array of pst args]
 * @param    string  $template_path [file path to template]
 * @param    string  $default_path  [default file path]
 * @return   void
 * @since    1.0.0
 * @version  3.16.0
 */
function llms_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		  extract( $args );
	}

	  $located = llms_locate_template( $template_name, $template_path, $default_path );

	  do_action( 'lifterlms_before_template_part', $template_name, $template_path, $located, $args );

	if ( file_exists( $located ) ) {
		include( $located );
	}

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
 * @param   string  $template_name name of template
 * @param   string  $template_path dir path to template
 * @param   string  $default_path  default path
 * @return  string
 * @since   1.0.0
 * @version 3.0.0 - only returns path if template exists
 */
function llms_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = LLMS()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = LLMS()->plugin_path() . '/templates/';
	}

	// check theme and template directories for the template
	$override_path = llms_get_template_override( $template_name );

	// Get default template
	$path = ($override_path) ? $override_path : $default_path;

	$template = $path . $template_name;

	if ( ! file_exists( $template ) ) {

		$template = '';

	}

	// Return template
	return apply_filters( 'lifterlms_locate_template', $template, $template_name, $template_path );
}

/**
 * Get Template Override
 *
 * @param  string $template [template file]
 * @return mixed [template file or false if none exists.]
 */
function llms_get_template_override( $template = '' ) {

	/**
	* Allow themes and plugins to determine which folders to look in for theme overrides
	*/
	$dirs = apply_filters( 'lifterlms_theme_override_directories', array(
		get_stylesheet_directory() . '/lifterlms',
		get_template_directory() . '/lifterlms',
	) );

	foreach ( $dirs as $dir ) {

		$path = $dir . '/';

	 	if ( file_exists( $path . $template ) ) {
			return $path;
		}
	}

	return false;
}

