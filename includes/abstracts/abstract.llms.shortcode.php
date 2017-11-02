<?php
/**
 * Base Shortcode Class
 * @since    3.4.3
 * @version  3.4.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Shortcode {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = '';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	abstract protected function get_output();

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 * @return   array
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_default_attributes() {
		return array();
	}

	/**
	 * Retrieves a string used for default content which is used if no content is supplied
	 * @return   string
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_default_content() {
		return '';
	}

	/**
	 * Holds singletons for extending classes
	 * @var  array
	 */
	private static $_instances = array();

	private $attributes = array();
	private $content = '';

	/**
	 * Get the singleton instance for the extending class
	 * @return   obj
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public static function instance() {

		$class = get_called_class();

		if ( ! isset( self::$_instances[ $class ] ) ) {
			self::$_instances[ $class ] = new $class();
		}

		return self::$_instances[ $class ];

	}

	/**
	 * Private constructor
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	private function __construct() {
		add_shortcode( $this->tag, array( $this, 'output' ) );
	}

	/**
	 * Allow shortcodes to enqueue scripts only when the shortcode is used
	 * Enqueues a registered script IF that script isn't already enqueued
	 * @param    string     $handle  script handle
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function enqueue_script( $handle ) {

		if ( wp_script_is( $handle, 'registered' ) && ! wp_script_is( $handle, 'enqueued' ) ) {

			wp_enqueue_script( $handle );

		}

	}

	/**
	 * Get the array of attributes
	 * @return   array
	 * @since    3.4.3
	 * @version  3.5.1
	 */
	public function get_attributes() {
		return apply_filters( $this->get_filter( 'get_attributes' ), $this->attributes, $this );
	}

	/**
	 * Get a specific attribute from the attributes array
	 * @param    string     $key      attribute key to retrieve
	 * @param    string     $default  if no attribute is set, this value will be used
	 * @return   mixed
	 * @since    3.4.3
	 * @version  3.5.1
	 */
	public function get_attribute( $key, $default = '' ) {
		$attributes = $this->get_attributes();
		if ( isset( $attributes[ $key ] ) ) {
			return $attributes[ $key ];
		}
		return $default;
	}

	/**
	 * Retrieve the content of the shortcode
	 * @return   string
	 * @since    3.4.3
	 * @version  3.5.1
	 */
	public function get_content() {
		return apply_filters( $this->get_filter( 'get_content' ), $this->content, $this );
	}

	/**
	 * Retrive a string that can be used for apply_filters()
	 * Ensures that all shortcode related filters follow the same naving convention
	 * @param    string     $filter  filter name / suffix
	 * @return   string
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_filter( $filter ) {
		return $this->tag . '_' . $filter;
	}

	/**
	 * Output the actual content of the shortcode
	 * This is the callback function used by add_shortcode
	 * and can also be used programattically, used in some widgets
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @param    array      $atts     user submitted shortcode attributes
	 * @param    string     $content  user submitted content
	 * @return   string
	 * @since    3.4.3
	 * @version  3.5.1
	 */
	public function output( $atts = array(), $content = '' ) {

		$this->attributes = shortcode_atts(
			apply_filters( $this->get_filter( 'get_default_attributes' ), $this->get_default_attributes(), $this ),
			$atts,
			$this->tag
		);

		if ( ! $content ) {
			$content = apply_filters( $this->get_filter( 'get_default_content' ), $this->get_default_content(), $this );
		}

		$this->content = $content;

		return apply_filters( $this->get_filter( 'output' ), $this->get_output(), $this );

	}

}
