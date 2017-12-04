<?php
/**
 * Load, access, and manage LifterLMS Processors
 * @since    3.15.0
 * @version  3.15.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Processors {

	/**
	 * Processor classes that should be loaded
	 * this should match the classname of a proccessor
	 * @var  array
	 */
	private $classes = array(
		'course_data',
		'membership_bulk_enroll',
		'table_to_csv',
	);

	/**
	 * Array of available processors loaded via $this->load_all()
	 * @var  array
	 */
	private $processors = array();

	/**
	 * Singleton instance of the class
	 * @var  null
	 */
	protected static $_instance = null;

	/**
	 * Main instance
	 * @return   LLMS_Processors
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function __construct() {

		$this->includes();
		$this->load_all();

	}

	/**
	 * Access a single loaded processor instance
	 * @param    string     $name  name of the processor
	 * @return   obj|false         instance of the proccesor if found, otherwise false
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get( $name ) {

		if ( isset( $this->processors[ $name ] ) ) {
			return $this->processors[ $name ];
		}

		return false;
	}

	/**
	 * Include classes required by proccessors
	 * @return   [type]     [description]
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function includes() {

		require_once LLMS_PLUGIN_DIR . 'includes/libraries/wp-background-processing/wp-async-request.php';
		require_once LLMS_PLUGIN_DIR . 'includes/libraries/wp-background-processing/wp-background-process.php';

	}

	/**
	 * Load all processors
	 * @return   [type]     [description]
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function load_all() {

		// allow loading of 3rd party processors
		$classes = apply_filters( 'llms_load_processors', $this->classes );

		foreach ( $this->classes as $name ) {

			$class = $this->load_processor( $name );

			if ( $class ) {

				$this->processors[ $name ] = $class;

			}
		}

	}

	/**
	 * Load a single processor
	 * @param    string     $name  name of the processor
	 * @return   obj|false         instance of the proccesor if found, otherwise false
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function load_processor( $name ) {

		$path = apply_filters( 'llms_load_processor_path', $name );

		if ( false === strpos( $path, '.php' ) ) {

			$file = str_replace( '_', '.', $path );
			$path = LLMS_PLUGIN_DIR . 'includes/processors/class.llms.processor.' . $file . '.php';

		}

		if ( file_exists( $path ) ) {
			return require_once $path;
		}

		return false;

	}

}
