<?php
/**
 * Generate LMS Content from export files or raw arrays of data
 *
 * @package LifterLMS/Classes
 *
 * @since 3.3.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Generator class.
 *
 * @since 3.3.0
 * @since 3.30.2 Added hooks and made numerous private functions public to expand extendability.
 * @since 3.36.3 New method: is_generator_valid()
 *               Bugfix: Fix return of `set_generator()`.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Generator::add_custom_values()` method
 *              - `LLMS_Generator::format_date()` method
 *              - `LLMS_Generator::get_author_id_from_raw()` method
 *              - `LLMS_Generator::get_default_post_status()` method
 *              - `LLMS_Generator::get_generated_posts()` method
 *              - `LLMS_Generator::increment()` method
 */
class LLMS_Generator {

	/**
	 * Courses generator subclass instance
	 *
	 * @var LLMS_Generator_Courses
	 */
	protected $courses_generator;

	/**
	 * Instance of WP_Error
	 *
	 * @var obj
	 */
	public $error;

	/**
	 * Array of generated objects.
	 *
	 * @var array
	 */
	protected $generated = array();

	/**
	 * Name of the Generator to use for generation
	 *
	 * @var string
	 */
	protected $generator = '';

	/**
	 * Raw contents passed into the generator's constructor
	 *
	 * @var array
	 */
	protected $raw = array();

	/**
	 * Construct a new generator instance with data
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Move most logic into helper functions.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @param array|string $raw Array or a JSON string of raw content.
	 * @return void
	 */
	public function __construct( $raw ) {

		// Load generator class.
		$this->courses_generator = new LLMS_Generator_Courses();

		// Parse raw data.
		$this->raw = $this->parse_raw( $raw );

		// Instantiate an empty error object.
		$this->error = new WP_Error();

		// Add hooks.
		$this->add_hooks();

	}

	/**
	 * Add actions and filters used by the class.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	protected function add_hooks() {

		// Watch creation of things, used on generation completion to return results of created objects.
		foreach ( array( 'access_plan', 'course', 'section', 'lesson', 'quiz', 'question', 'term', 'user' ) as $type ) {
			add_action( 'llms_generator_new_' . $type, array( $this, 'object_created' ) );
		}

	}

	/**
	 * When called, generates raw content based on the defined generator
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Add before and after generation hooks.
	 * @since 4.7.0 Return early if not generator is set.
	 *
	 * @return void
	 */
	public function generate() {

		if ( empty( $this->generator ) ) {
			return $this->error->add( 'missing-generator', __( 'No generator supplied.', 'lifterlms' ) );
		}

		global $wpdb;

		$wpdb->hide_errors();

		$wpdb->query( 'START TRANSACTION' ); // db call ok; no-cache ok.

		/**
		 * Action run immediately prior to a LifterLMS Generator running.
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Generator $generator The generator instance.
		 */
		do_action( 'llms_generator_before_generate', $this );

		try {
			call_user_func( $this->generator, $this->raw );
		} catch ( Exception $exception ) {
			$this->error->add( $this->get_error_code( $exception->getCode(), $this->generator[0] ), $exception->getMessage(), $exception->getTrace() );
		}

		/**
		 * Action run immediately after a LifterLMS Generator running.
		 *
		 * @since 3.30.2
		 *
		 * @param LLMS_Generator $generator The generator instance.
		 */
		do_action( 'llms_generator_after_generate', $this );

		if ( $this->is_error() ) {
			$wpdb->query( 'ROLLBACK' ); // db call ok; no-cache ok.
		} else {
			$wpdb->query( 'COMMIT' ); // db call ok; no-cache ok.
		}

	}

	/**
	 * Retrieve a human-readable error code from a machine-readable error number
	 *
	 * @since 4.7.0
	 * @since 4.9.0 Handle PHP core errors, warnings, notices, etc... with a human-readable error code.
	 *
	 * @param int $code  Error number.
	 * @param obj $class Generator class instance.
	 * @return string A human-readable error code.
	 */
	protected function get_error_code( $code, $class ) {

		// See if the error code is a native php exception code constant.
		$ret = llms_php_error_constant_to_code( $code );

		// Code is not a native PHP exception code.
		if ( is_numeric( $ret ) ) {

			$reflect   = new ReflectionClass( $class );
			$constants = array_flip( $reflect->getConstants() );
			$ret       = isset( $constants[ $code ] ) ? $constants[ $code ] : 'ERROR_UNKNOWN';

		}

		/**
		 * Filter the human-readable error retrieved from a given error code
		 *
		 * @since 4.9.0
		 *
		 * @param string $ret   The human-readable error code.
		 * @param int    $code  The initial error code as an integer.
		 * @param obj    $class Generator class instance.
		 */
		return apply_filters( 'llms_generator_get_error_code', $ret, $code, $class );

	}

	/**
	 * Retrieves a multi-dimensional array of content generated by the most class
	 *
	 * @since 4.7.0
	 *
	 * @return array Returns an associative array where the keys are the object type and the values are an array of integers representing the generated object IDs.
	 */
	public function get_generated_content() {
		return $this->generated;
	}

	/**
	 * Retrieve the array of generated course ids
	 *
	 * @since 3.7.3
	 * @since 3.14.8 Unknown.
	 * @since 4.7.0 Access generated posts from the `$generated` property in favor of the removed `$posts` property.
	 *
	 * @return array
	 */
	public function get_generated_courses() {
		if ( isset( $this->generated['course'] ) ) {
			return $this->generated['course'];
		}
		return array();
	}

	/**
	 * Get an array of valid LifterLMS generators
	 *
	 * @since 3.3.0
	 * @since 3.14.8 Unknown.
	 * @since 4.7.0 Load generators from `LLMS_Generator_Courses()`.
	 * @since 4.13.0 Use `clone_course()` method for cloning courses in favor of `genrate_course()`.
	 *
	 * @return array
	 */
	protected function get_generators() {

		/**
		 * Filter the list of available generators.
		 *
		 * @since Unknown
		 *
		 * @param array[] $generators Array of generators. Array key is the generator name and the array value is a callable function.
		 */
		return apply_filters(
			'llms_generators',
			array(
				'LifterLMS/BulkCourseExporter'    => array( $this->courses_generator, 'generate_courses' ),
				'LifterLMS/BulkCourseGenerator'   => array( $this->courses_generator, 'generate_courses' ),
				'LifterLMS/SingleCourseCloner'    => array( $this->courses_generator, 'clone_course' ),
				'LifterLMS/SingleCourseExporter'  => array( $this->courses_generator, 'generate_course' ),
				'LifterLMS/SingleCourseGenerator' => array( $this->courses_generator, 'generate_course' ),
				'LifterLMS/SingleLessonCloner'    => array( $this->courses_generator, 'clone_lesson' ),
			)
		);
	}

	/**
	 * Get the results of the generate function
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Return generated stats from `$this->stats()` instead of from removed `$stats` property.
	 *
	 * @return int[]|WP_Error Array of stats on success and an error object on failure.
	 */
	public function get_results() {

		if ( $this->is_error() ) {
			return $this->error;
		}

		return $this->get_stats();

	}

	/**
	 * Get "stats" about the generated content.
	 *
	 * @since 4.7.0
	 *
	 * @return array
	 */
	public function get_stats() {

		$stats = array();
		foreach ( $this->generated as $type => $ids ) {
			$stats[ $type ] = count( $ids );
		}

		// Add old plural keys that were guaranteed to exist.
		$backwards_compat = array(
			'course'      => 'courses',
			'section'     => 'sections',
			'lesson'      => 'lessons',
			'access_plan' => 'plans',
			'quiz'        => 'quizzes',
			'question'    => 'questions',
			'term'        => 'terms',
			'user'        => 'authors',
		);
		foreach ( $backwards_compat as $curr => $old ) {
			$stats[ $old ] = isset( $stats[ $curr ] ) ? $stats[ $curr ] : 0;
		}

		return $stats;

	}

	/**
	 * Determines if there was an error during the running of the generator
	 *
	 * @since 3.3.0
	 * @since 3.16.11 Unknown.
	 *
	 * @return boolean Returns `true` when there was an error and `false` if there's no errors.
	 */
	public function is_error() {
		return ( 0 !== count( $this->error->get_error_messages() ) );
	}

	/**
	 * Determine if a generator is a valid generator.
	 *
	 * @since 3.36.3
	 *
	 * @param string $generator Generator name.
	 * @return bool
	 */
	protected function is_generator_valid( $generator ) {

		return in_array( $generator, array_keys( $this->get_generators() ), true );

	}

	/**
	 * Record the generation of an object
	 *
	 * @since 4.7.0
	 *
	 * @param LLMS_Post_Model|array|WP_User $object Created object or array.
	 * @return void
	 */
	public function object_created( $object ) {

		switch ( current_action() ) {

			case 'llms_generator_new_access_plan':
			case 'llms_generator_new_course':
			case 'llms_generator_new_section':
			case 'llms_generator_new_lesson':
			case 'llms_generator_new_quiz':
			case 'llms_generator_new_question':
				$this->record_generation( $object->get( 'id' ), $object->get( 'type' ) );
				break;

			case 'llms_generator_new_user':
				$this->record_generation( $object, 'user' );
				break;

			case 'llms_generator_new_term':
				$this->record_generation( $object['term_id'], 'term' );
				break;

		}

	}

	/**
	 * Parse raw data
	 *
	 * @since 4.7.0
	 *
	 * @param string|array|obj $raw Accepts a JSON string, array, or object of raw data to pass to a generator.
	 * @return array
	 */
	protected function parse_raw( $raw ) {

		if ( is_string( $raw ) ) {
			$raw = json_decode( $raw, true );
		}

		return (array) $raw;

	}

	/**
	 * Records a generated post id
	 *
	 * @since 3.14.8
	 * @since 4.7.0 Modified method access from `private` to `protected`.
	 *               Add IDs to the `generated` variable in favor of `posts`.
	 *
	 * @param int    $id  WP Post ID of the generated post.
	 * @param string $key Key of the stat to increment.
	 * @return void
	 */
	protected function record_generation( $id, $key ) {

		// Remove LifterLMS Prefix from the key (if it exists).
		$key = str_replace( 'llms_', '', $key );

		// Add an array if it doesn't already exist.
		if ( ! isset( $this->generated[ $key ] ) ) {
			$this->generated[ $key ] = array();
		}

		// Record the ID.
		$this->generated[ $key ][] = $id;

	}

	/**
	 * Configure the default post status for generated posts at runtime
	 *
	 * @since 3.7.3
	 * @since 4.7.0 Call `set_default_post_status()` from the configured generator.
	 *
	 * @param string $status Any valid WP Post Status.
	 * @return void
	 */
	public function set_default_post_status( $status ) {
		call_user_func( array( $this->generator[0], 'set_default_post_status' ), $status );
	}

	/**
	 * Sets the generator to use for the current instance
	 *
	 * @since 3.3.0
	 * @since 3.36.3 Fix error causing `null` to be returned instead of expected `WP_Error`.
	 *               Return the generator name on success instead of void.
	 *
	 * @param string $generator Generator string, eg: "LifterLMS/SingleCourseExporter"
	 * @return string|WP_Error Name of the generator on success, otherwise an error object.
	 */
	public function set_generator( $generator = null ) {

		// Interpret the generator from the raw data.
		if ( empty( $generator ) ) {

			// No generator can be interpreted.
			if ( ! isset( $this->raw['_generator'] ) ) {

				$this->error->add( 'missing-generator', __( 'The supplied file cannot be processed by the importer.', 'lifterlms' ) );
				return $this->error;

			}

			// Set the generator using the interpreted data.
			return $this->set_generator( $this->raw['_generator'] );

		}

		// Invalid generator.
		if ( ! $this->is_generator_valid( $generator ) ) {
			$this->error->add( 'invalid-generator', __( 'The supplied generator is invalid.', 'lifterlms' ) );
			return $this->error;
		}

		// Set the generator.
		$generators      = $this->get_generators();
		$this->generator = $generators[ $generator ];

		// Return the generator name.
		return $generator;

	}

}
