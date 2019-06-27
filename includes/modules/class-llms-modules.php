<?php

class LLMS_Modules {

	public $loaded = array();

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self(); }
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function __construct() {
		$this->load();
	}

	/**
	 * Loads Modules.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function load() {

		/**
		 * Filters list of LifterLMS modules to load.
		 *
		 * @since	[version]
		 * @version	[version]
		 */
		$final_modules = apply_filters( 'lifterlms_modules', $this->info() );

		foreach ( $final_modules as $module ) {

			// define the constant as true if it hasn't been defined by the user in wp-config.php or similar.
			if ( ! defined( $module['constant'] ) ) {
				define( $module['constant'] , true );
			}

			// if the constant's value is true and the class file exists, include the module class
			if ( true === constant( $module['constant'] ) && file_exists( $module['file_path'] ) ) {
				include_once $module['file_path'];
			}

			$this->loaded[ $module['name'] ] = $module;

		}

		/**
		 * Fires after all modules are loaded
		 *
		 * @since	[version]
		 * @version	[version]
		 */
		do_action( 'lifterlms_modules_loaded', $this->loaded );

	}

	/**
	 * Loads Module Information.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function info() {

		// get a list of directories inside the modules directory.
		$directories = glob( LLMS_PLUGIN_DIR . 'includes/modules/*' , GLOB_ONLYDIR );

		$modules = array();

		// loop through every directory
		foreach ( $directories as $module ) {

			// the name of the module is the same as the name of the directory. eg "certificate-builder"
			$module_name = basename( $module );

			// the name of the class file is similar. eg "class-llms-certificate-builder.php"
			$module_class_file_path = "$module/class-llms-$module_name.php";

			// the constant name also uses similar conventions. eg "LLMS_CERTIFICATE_BUILDER"
			$module_constant_name = 'LLMS_' . strtoupper( str_replace( '-', '_', $module_name ) );

			$modules[ $module_name ] = array(
				'name' => $module_name,
				'file_path' => $module_class_file_path,
				'constant' => $module_constant_name,
			);

			unset( $module_name );
			unset( $module_class_file_path );
			unset( $module_constant_name );

		}

		return $modules;

	}

}
