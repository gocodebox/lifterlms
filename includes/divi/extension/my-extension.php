<?php

if ( ! function_exists( 'lifterlms_initialize_divi_extension' ) ) :
	/**
	 * Creates the extension's main class instance.
	 *
	 * @since 1.0.0
	 */
	function lifterlms_initialize_divi_extension() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/LifterLMS_Divi_Extension.php';
	}
	add_action( 'divi_extensions_init', 'lifterlms_initialize_divi_extension' );
endif;
