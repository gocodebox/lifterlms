<?php
/**
 * Processors
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since 3.15.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Processors class
 *
 * Load, access, and manage LifterLMS Processors
 *
 * @since 3.15.0
 * @since 5.0.0 Removed private method `includes()`.
 *              Stop loading removed processor "table_to_csv".
 */
class LLMS_Processors {

	/**
	 * Processor classes that should be loaded
	 *
	 * This should match the classname of a processor.
	 *
	 * @var array
	 */
	private $classes = array(
		'course_data',
		'membership_bulk_enroll',
	);

	/**
	 * Array of available processors loaded via $this->load_all()
	 *
	 * @var LLMS_Abstract_Processor[]
	 */
	private $processors = array();

	/**
	 * Singleton instance of the class
	 *
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * Main instance
	 *
	 * @since 3.15.0
	 *
	 * @return LLMS_Processors
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 3.15.0
	 * @since 5.0.0 Remove call to removed method `includes()`.
	 *
	 * @return void
	 */
	private function __construct() {

		$this->load_all();

	}

	/**
	 * Access a single loaded processor instance
	 *
	 * @since 3.15.0
	 *
	 * @param string $name Name of the processor.
	 * @return LLMS_Abstract_Processor|false Instance of the processor if found, otherwise false.
	 */
	public function get( $name ) {

		if ( isset( $this->processors[ $name ] ) ) {
			return $this->processors[ $name ];
		}

		return false;
	}

	/**
	 * Load all processors
	 *
	 * @since 3.15.0
	 *
	 * @return void
	 */
	private function load_all() {

		/**
		 * Filter the list of available processors to be loaded
		 *
		 * Third parties can use this filter to load custom processors.
		 *
		 * @since 5.0.0
		 *
		 * @see llms_load_processor_path To add a custom load path for the loaded processor.
		 *
		 * @param string[] $classes A list of processor class ids/slugs.
		 */
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
	 *
	 * @since 3.15.0
	 *
	 * @param string $name Name of the processor.
	 * @return LLMS_Abstract_Processor|boolean Instance of the processor if found and not yet included, `false` if
	 *                                         the processor can't be found, and `true` if it has already been included.
	 */
	public function load_processor( $name ) {

		/**
		 * Filter the path of a processor class
		 *
		 * If the returned path isn't the full path to a PHP file the file will be attempted to be
		 * loaded from the LifterLMS core's processor directory by replacing underscores with dots
		 * and prepending `class.llms.processor.` and appending `.php`.
		 *
		 * @since 5.0.0
		 *
		 * @see llms_load_processors For a filter used to register custom processors.
		 *
		 * @param string $name Processor classname id/slug.
		 */
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
