<?php
/**
 * Localization functions
 *
 * @package LifterLMS/Functions
 *
 * @since 4.9.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the current plugin locale.
 *
 * @since 4.9.0
 * @since [version] Remove fall-backs for WP versin less than 5.0.
 *
 * @param string $domain Text domain.
 * @return string
 */
function llms_get_locale( $domain = 'lifterlms' ) {

	$locale = determine_locale();

	/**
	 * Filter the plugin's locale
	 *
	 * @since Unknown
	 *
	 * @link https://developer.wordpress.org/reference/hooks/plugin_locale/
	 *
	 * @param string $locale The plugin's current locale.
	 * @param string $domain The textdomain.
	 */
	return apply_filters( 'plugin_locale', $locale, $domain );

}

/**
 * Get LifterLMS l10n safe directory.
 *
 * By default the safe directory is `wp-content/languages/lifterlms`.
 *
 * @since 4.9.0
 *
 * @return string
 */
function llms_l10n_get_safe_directory() {

	/**
	 * Filter the LifterLMS language file "safe" directory.
	 *
	 * This safe directory exists to provide a place where custom translations can be placed
	 * which will not be automatically overridden by l10n files automatically pulled into
	 * the default language directory from the WP GlotPress server during plugin updates.
	 *
	 * By default the safe directory is `wp-content/languages/lifterlms`.
	 *
	 * @since 4.9.0
	 *
	 * @param string $path Full server path to the safe directory.
	 */
	return apply_filters( 'llms_l10n_safe_directory', WP_LANG_DIR . '/lifterlms' );

}

/**
 * Load MO format localization files for the given text domain
 *
 * This function localizes using the WP Core's default language file directories as a after
 * checking in the LifterLMS-defined "safe" directory.
 *
 * Language files files can be found in the following locations (The first loaded file takes priority):
 *
 *   1. wp-content/languages/{$domain}/{$domain}-{$locale}.mo
 *
 *      This is recommended "safe" location where custom language files can be stored. A file
 *      stored in this directory will never be automatically overwritten.
 *
 *   2. wp-content/languages/plugins/{$domain}-{$locale}.mo
 *
 *      This is the default directory where WordPress will download language files from the
 *      WordPress GlotPress server during updates. If you store a custom language file in this
 *      directory it will be overwritten during updates.
 *
 *   3. wp-content/plugins/{$domain}/languages/{$domain}-{$locale}.mo
 *
 *      This is the the LifterLMS plugin directory. A language file stored in this directory will
 *      be removed from the server during a LifterLMS plugin update.
 *
 * @since 4.9.0
 *
 * @param string      $domain       Textdomain being loaded.
 * @param string|null $plugin_dir   Full path to the plugin directory, if none supplied `LLMS_PLUGIN_DIR` is used.
 * @param string|null $language_dir Relative path to the language directory within the plugin. If none supplied, `languages` is used.
 * @return void
 */
function llms_load_textdomain( $domain, $plugin_dir = null, $language_dir = null ) {

	$plugin_dir   = $plugin_dir ? $plugin_dir : LLMS_PLUGIN_DIR;
	$language_dir = $language_dir ? $language_dir : 'languages';

	/**
	 * Load from the custom LifterLMS "safe" directory (if it exists).
	 *
	 * Example path: wp-content/languages/lifterlms/lifterlms-en_US.mo
	 */
	load_textdomain( $domain, sprintf( '%1$s/%2$s-%3$s.mo', llms_l10n_get_safe_directory(), $domain, llms_get_locale( $domain ) ) );

	/**
	 * Load from default plugin locations specified by the WP core.
	 *
	 * 1. wp-content/languages/plugins/lifterlms-en_US.mo
	 * 2. wp-content/plugins/lifterlms/languages/lifterlms-en_US.mo
	 */
	load_plugin_textdomain( $domain, false, sprintf( '%1$s/%2$s', basename( $plugin_dir ), $language_dir ) );

}

/**
 * Switch plugin to site language if the current locale is different than the site locale.
 *
 * @since [version]
 *
 * @return void
 */
function llms_maybe_switch_to_site_locale() {

	if (
		function_exists( 'switch_to_locale' )
		&& ( is_locale_switched() ) || ( get_locale() !== determine_locale() )
	) {

		/**
		 * Fired before switching the current locale to the site locale.
		 *
		 * @since [version]
		 */
		do_action( 'llms_before_switching_to_site_locale' );

		switch_to_locale( get_locale() );

		// Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
		add_filter( 'plugin_locale', 'get_locale' );

		// Init plugin locale.
		llms_init_locale();

		/**
		 * Fired after switching the current locale to the site locale.
		 *
		 * At this stage the LifterLMS textdomain has ben already loaded with the site locale.
		 *
		 * @since [version]
		 */
		do_action( 'llms_after_switching_to_site_locale' );

	}

}

/**
 * Switch plugin language to original.
 *
 * @since [version]
 *
 * @return void
 */
function llms_maybe_restore_previous_locale() {

	if (
		function_exists( 'restore_previous_locale' )
		&& ( is_locale_switched() ) || ( get_locale() !== determine_locale() )
	) {


		/**
		 * Fired before restoring the previous locale.
		 *
		 * @since [version]
		 */
		do_action( 'llms_before_restoring_previous_locale' );

		restore_previous_locale();

		// Remove filter.
		remove_filter( 'plugin_locale', 'get_locale' );

		// Init plugin locale.
		llms_init_locale();

		/**
		 * Fired after restoring the previous locale.
		 *
		 * At this stage the LifterLMS textdomain has ben already loaded with the previous locale.
		 *
		 * @since [version]
		 */
		do_action( 'llms_after_restoring_previous_locale' );

	}

}

/**
 * Init plugin locale.
 *
 * Will unload the domain if already loaded before loading it again.
 *
 * @since [version]
 *
 * @param string      $domain       Textdomain being loaded.
 * @param string|null $plugin_dir   Full path to the plugin directory, if none supplied `LLMS_PLUGIN_DIR` is used.
 * @param string|null $language_dir Relative path to the language directory within the plugin. If none supplied, `languages` is used.
 * @return void
 */
function llms_init_locale( $domain = 'lifterlms', $plugin_dir = null, $language_dir = null ) {

	if ( is_textdomain_loaded( $domain ) ) {
		unload_textdomain( $domain );
	}

	llms_load_textdomain( $domain, $plugin_dir, $language_dir );

}
