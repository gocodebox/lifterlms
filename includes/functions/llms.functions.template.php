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
 * Determine if Focus Mode is enabled for a specific post (lesson, quiz, or other post types via filter).
 *
 * For lessons and quizzes, uses the parent course focus mode setting (or global setting).
 * Add-ons (e.g. LifterLMS Assignments) can use the filter to enable focus mode for their post types.
 *
 * @since 10.0.0
 *
 * @param int $post_id The ID of the post (lesson, quiz, etc.).
 * @return bool
 */
function llms_is_focus_mode_enabled( $post_id ) {
	$post = llms_get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	$result = false;
	$type   = $post->get( 'type' );

	if ( 'lesson' === $type || 'llms_quiz' === $type ) {
		$course = llms_get_post_parent_course( $post_id );
		if ( ! $course ) {
			return apply_filters( 'llms_is_focus_mode_enabled', false, $post_id );
		}

		$course_focus_mode = $course->get( 'focus_mode' );
		if ( 'enable' === $course_focus_mode ) {
			$result = true;
		} elseif ( 'disable' === $course_focus_mode ) {
			$result = false;
		} else {
			$result = 'yes' === get_option( 'lifterlms_enable_focus_mode', 'no' );
		}

		if ( $result && ! current_user_can( 'manage_lifterlms' ) ) {
			$student = llms_get_student();
			if ( ! $student || ! $student->is_enrolled( $course->get( 'id' ) ) ) {
				$result = false;
			}
		}
	}

	/**
	 * Filters whether focus mode is enabled for a given post.
	 *
	 * Used by add-ons (e.g. LifterLMS Assignments) to enable focus mode for their post types
	 * when focus mode is enabled globally and/or for the course.
	 *
	 * @since 10.0.0
	 *
	 * @param bool $result  Whether focus mode is enabled.
	 * @param int  $post_id The post ID.
	 */
	return apply_filters( 'llms_is_focus_mode_enabled', $result, $post_id );
}

/**
 * Retrieve the effective focus mode content width for a post.
 *
 * Checks the parent course setting first (for lesson/quiz), then falls back to global.
 *
 * @since 10.0.0
 *
 * @param int $post_id The ID of the post (lesson, quiz, or other focus-mode post type).
 * @return string Width value: 'full', '1600', '1180', '960', or '768'.
 */
function llms_get_focus_mode_content_width( $post_id ) {
	$course = llms_get_post_parent_course( $post_id );
	if ( $course ) {
		$value = $course->get( 'focus_mode_content_width' );
		if ( $value && 'inherit' !== $value ) {
			return $value;
		}
	}
	return get_option( 'lifterlms_focus_mode_content_width', '960' );
}

/**
 * Retrieve the effective focus mode sidebar position for a post.
 *
 * Checks the parent course setting first (for lesson/quiz), then falls back to global.
 *
 * @since 10.0.0
 *
 * @param int $post_id The ID of the post (lesson, quiz, or other focus-mode post type).
 * @return string 'left' or 'right'.
 */
function llms_get_focus_mode_sidebar_position( $post_id ) {
	$course = llms_get_post_parent_course( $post_id );
	if ( $course ) {
		$value = $course->get( 'focus_mode_sidebar_position' );
		if ( $value && 'inherit' !== $value ) {
			return $value;
		}
	}
	return get_option( 'lifterlms_focus_mode_sidebar_position', 'left' );
}

/**
 * Get focus mode content width select options for course-level settings.
 *
 * @since 10.0.0
 *
 * @param bool $include_inherit Whether to include the "Inherit" option with the current global value.
 * @return array Array of key/title option arrays.
 */
function llms_get_focus_mode_content_width_options( $include_inherit = false ) {
	$widths = array(
		'full' => __( 'Full Width', 'lifterlms' ),
		'1600' => __( 'Extra Wide (1600px)', 'lifterlms' ),
		'1180' => __( 'Wide (1180px)', 'lifterlms' ),
		'960'  => __( 'Default (960px)', 'lifterlms' ),
		'768'  => __( 'Narrow (768px)', 'lifterlms' ),
	);

	$options = array();

	if ( $include_inherit ) {
		$global_value = get_option( 'lifterlms_focus_mode_content_width', '960' );
		$global_label = isset( $widths[ $global_value ] ) ? $widths[ $global_value ] : $global_value;
		$options[]    = array(
			'key'   => 'inherit',
			'title' => sprintf(
				/* translators: %s: current global setting label */
				__( 'Inherit Global Setting (%s)', 'lifterlms' ),
				$global_label
			),
		);
	}

	foreach ( $widths as $key => $title ) {
		$options[] = array(
			'key'   => $key,
			'title' => $title,
		);
	}

	return $options;
}

/**
 * Get focus mode sidebar position select options for course-level settings.
 *
 * @since 10.0.0
 *
 * @param bool $include_inherit Whether to include the "Inherit" option with the current global value.
 * @return array Array of key/title option arrays.
 */
function llms_get_focus_mode_sidebar_position_options( $include_inherit = false ) {
	$positions = array(
		'left'  => __( 'Left', 'lifterlms' ),
		'right' => __( 'Right', 'lifterlms' ),
	);

	$options = array();

	if ( $include_inherit ) {
		$global_value = get_option( 'lifterlms_focus_mode_sidebar_position', 'left' );
		$global_label = isset( $positions[ $global_value ] ) ? $positions[ $global_value ] : $global_value;
		$options[]    = array(
			'key'   => 'inherit',
			'title' => sprintf(
				/* translators: %s: current global setting label */
				__( 'Inherit Global Setting (%s)', 'lifterlms' ),
				$global_label
			),
		);
	}

	foreach ( $positions as $key => $title ) {
		$options[] = array(
			'key'   => $key,
			'title' => $title,
		);
	}

	return $options;
}

/**
 * Add body classes for focus mode.
 *
 * @since 10.0.0
 *
 * @param array $classes Body classes.
 * @return array
 */
function llms_focus_mode_body_class( $classes ) {
	/**
	 * Post types that can use the focus mode template when focus mode is enabled.
	 *
	 * Add-ons (e.g. LifterLMS Assignments) can add their post types here and use
	 * the `llms_is_focus_mode_enabled` filter to return true when appropriate.
	 *
	 * @since 10.0.0
	 *
	 * @param string[] $post_types Post type names. Default: [ 'lesson', 'llms_quiz' ].
	 */
	$focus_mode_post_types = apply_filters( 'llms_focus_mode_post_types', array( 'lesson', 'llms_quiz' ) );
	if ( is_singular( $focus_mode_post_types ) && llms_is_focus_mode_enabled( get_the_ID() ) ) {
		$classes[] = 'llms-focus-mode';

		$width = llms_get_focus_mode_content_width( get_the_ID() );
		if ( 'full' !== $width ) {
			$classes[] = 'llms-focus-mode-width-' . $width;
		}

		$position  = llms_get_focus_mode_sidebar_position( get_the_ID() );
		$classes[] = 'llms-focus-mode-sidebar-' . $position;
	}
	return $classes;
}
add_filter( 'body_class', 'llms_focus_mode_body_class' );

/**
 * Enqueue focus mode frontend scripts.
 *
 * @since 10.0.0
 *
 * @return void
 */
function llms_focus_mode_enqueue_scripts() {
	$focus_mode_post_types = apply_filters( 'llms_focus_mode_post_types', array( 'lesson', 'llms_quiz' ) );
	if ( is_singular( $focus_mode_post_types ) && llms_is_focus_mode_enabled( get_the_ID() ) ) {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'llms-focus-mode',
			llms()->plugin_url() . '/assets/css/llms-focus-mode' . $suffix . '.css',
			array(),
			llms()->version
		);

		wp_enqueue_script(
			'llms-focus-mode',
			llms()->plugin_url() . '/assets/js/llms-focus-mode.js',
			array(),
			llms()->version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'llms_focus_mode_enqueue_scripts' );

/**
 * Render focus mode post content.
 *
 * Themes can override by removing this action and adding their own:
 *   remove_action( 'llms_focus_mode_the_content', 'llms_focus_mode_render_content' );
 *   add_action( 'llms_focus_mode_the_content', 'my_theme_render_content' );
 *
 * @since 10.0.0
 *
 * @return void
 */
function llms_focus_mode_render_content() {
	the_content();
}
add_action( 'llms_focus_mode_the_content', 'llms_focus_mode_render_content' );

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
