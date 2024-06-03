<?php
/**
 * Processors.
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since 3.15.0
 * @version 6.0.0
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
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Added the awarded certificates bulk sync processor.
 *              Removed the deprecated `LLMS_Processors::$_instance` property.
 */
class LLMS_Processors {

	use LLMS_Trait_Singleton;

	/**
	 * Processor classes that should be loaded
	 *
	 * This should match the classname of a processor.
	 *
	 * @var array
	 */
	private $classes = array(
		'achievement_sync',
		'certificate_sync',
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
	 * Constructor.
	 *
	 * @since 3.15.0
	 * @since 5.0.0 Remove call to removed method `includes()`.
	 * @since 6.0.0 Made sure the admin notices file is required.
	 *
	 * @return void
	 */
	private function __construct() {

		// Processors may trigger a notice during a cron and notices might not be available.
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
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
	 * Load all processors.
	 *
	 * @since 3.15.0
	 * @since 5.8.0 Use the value from the `llms_load_processors` filter.
	 *
	 * @return void
	 */
	private function load_all() {

		/**
		 * Filter the list of available processors to be loaded.
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

		foreach ( $classes as $name ) {

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
	 * @since 6.0.0 Added the ability to load processor class files with dashes in their file name.
	 *
	 * @param string $name Name of the processor.
	 * @return LLMS_Abstract_Processor|boolean Instance of the processor if found and not yet included, `false` if
	 *                                         the processor can't be found, and `true` if it has already been included.
	 */
	public function load_processor( $name ) {

		/**
		 * Filter the path of a processor class.
		 *
		 * If the returned path isn't the full path to a PHP file the file will be attempted to be
		 * loaded from the LifterLMS core's processor directory by replacing underscores with dashes
		 * and prepending `class-llms-processor-` and appending `.php`.
		 *
		 * @since 5.0.0
		 *
		 * @see LLMS_Processors::load_all() For the `llms_load_processors` filter used to register custom processors.
		 *
		 * @param string $name Processor class name ID/slug.
		 */
		$path = apply_filters( 'llms_load_processor_path', $name );

		// Try loading the filtered processor path.
		if ( $path !== $name ) {
			return file_exists( $name ) ? require_once $name : false;
		}

		$file = 'class-llms-processor-' . str_replace( '_', '-', $name ) . '.php';
		$path = LLMS_PLUGIN_DIR . 'includes/processors/';

		// Try loading a LifterLMS processor with a dashed file name.
		if ( file_exists( $path . $file ) ) {
			return require_once $path . $file;
		}

		// Try loading a LifterLMS processor with a dotted file name.
		$file = str_replace( '-', '.', $file );
		if ( file_exists( $path . $file ) ) {
			return require_once $path . $file;
		}

		return false;
	}
}
