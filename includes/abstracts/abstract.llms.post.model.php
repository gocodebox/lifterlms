<?php
/**
 * LLMS_Post_Model abstract class file
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.0.0
 * @version 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Model abstract class.
 *
 * Defines base methods and properties for programmatically interfacing with LifterLMS Custom Post Types.
 *
 * @property      string  $author           ID of post author.
 * @property      string  $content          The post's content.
 * @property      string  $date             The post's local publication time.
 * @property-read string  $db_post_type     Name of the post type as stored in the database
 *                                          This will be prefixed (where applicable)
 *                                          ie: "llms_order" for the "llms_order" post type
 * @property      string  $excerpt          The post's excerpt.
 * @property-read int     $id               Post ID.
 * @property      int     $menu_order       A field used for ordering posts.
 * @property-read string  $meta_prefix      A prefix to add to all meta properties
 *                                          Child classes can redefine this
 * @property-read string  $model_post_type  Define this in extending classes
 *                                          Allows models to use unprefixed post type names for filters and more
 *                                          ie: "order" for the "llms_order" post type
 * @property      string  $modified         The post's local modified time.
 * @property      string  $name             The post's slug.
 * @property      int     $parent           WP_Post ID of the post's parent post.
 * @property-read WP_Post $post             Instance of WP_Post
 * @property      string  $status           The post's status.
 * @property      string  $title            The post's title.
 * @property      string  $type             The post's type, like post or page.
 *
 * @since 3.0.0
 * @since 3.30.0 Improve handling of custom field data to `toArrayCustom()`.
 * @since 3.30.2 Add filter to allow 3rd parties to prevent a field from being added to the custom field array.
 * @since 3.30.3 Use `wp_slash()` when creating new posts.
 * @since 3.31.0 Treat `post_excerpt` fields as HTML instead of plain text.
 * @since 3.34.0 Add parameter to the `get()` method in order to get raw properties.
 * @since 3.34.0 Add `comment_status`, `ping_status`, `date_gmt`, `modified_gmt`, `menu_order`, 'post_password` as gettable\settable post properties.
 * @since 3.34.0 Add `set_bulk()` method that will allow to update an object at once given an array of properties.
 * @since 3.34.0 Refresh the whole $post property with the just updated instance of WP_Post after updating it.
 * @since 3.36.1 In `set_bulk()` method, use WP_Error::$errors in place of WP_Error::has_errors() to support WordPress version prior to 5.1.
 */
abstract class LLMS_Post_Model implements JsonSerializable {

	/**
	 * Name of the post type as stored in the database
	 * This will be prefixed (where applicable)
	 * ie: "llms_order" for the "llms_order" post type
	 *
	 * @var string
	 * @since 3.0.0
	 */
	protected $db_post_type;

	/**
	 * WP Post ID
	 *
	 * @var int
	 * @since 3.0.0
	 */
	protected $id;

	/**
	 * Define this in extending classes
	 *
	 * Allows models to use unprefixed post type names for filters and more
	 * ie: "order" for the "llms_order" post type.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	protected $model_post_type;

	/**
	 * A prefix to add to all meta properties
	 *
	 * Child classes can redefine this.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	protected $meta_prefix = '_llms_';

	/**
	 * Instance of WP_Post
	 *
	 * @var WP_Post
	 * @since 3.0.0
	 */
	protected $post;

	/**
	 * Array of meta properties and their property type
	 *
	 * @var array
	 * @since 3.3.0
	 */
	protected $properties = array();

	/**
	 * Array of default property values
	 *
	 * In the form of key => default value.
	 *
	 * @var array
	 * @since 3.24.0
	 */
	protected $property_defaults = array();

	/**
	 * Constructor
	 *
	 * Setup ID and related post property.
	 *
	 * @since 3.0.0
	 * @since 3.13.0 Unknown.
	 *
	 * @param string|int|LLMS_Post_Model|WP_Post $model 'new', WP post id, instance of an extending class, instance of WP_Post.
	 * @param array                              $args  Args to create the post, only applies when $model is 'new'.
	 * @return void
	 */
	public function __construct( $model, $args = array() ) {

		if ( 'new' === $model ) {
			$model = $this->create( $args );
			if ( ! is_wp_error( $model ) ) {
				$created = true;
			}
		} else {
			$created = false;
		}

		if ( empty( $model ) || is_wp_error( $model ) ) {
			return;
		}

		if ( is_numeric( $model ) ) {

			$this->id   = absint( $model );
			$this->post = get_post( $this->id );

		} elseif ( is_subclass_of( $model, 'LLMS_Post_Model' ) ) {

			$this->id   = absint( $model->id );
			$this->post = $model->post;

		} elseif ( $model instanceof WP_Post && isset( $model->ID ) ) {

			$this->id   = absint( $model->ID );
			$this->post = $model;

		}

		if ( $created ) {
			$this->after_create();
		}
	}


	/**
	 * Magic Getter
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Key to retrieve.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->___get( $key );
	}

	/**
	 * Magic Isset
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Check if a key exists in the database.
	 * @return boolean
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $this->meta_prefix . $key );
	}

	/**
	 * Magic Setter
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Key of the property.
	 * @param mixed  $val Value to set the property with.
	 * @return void
	 */
	public function __set( $key, $val ) {
		$this->$key = $val;
	}

	/**
	 * Allow extending classes to add custom meta properties to the object
	 *
	 * @since 3.16.0
	 *
	 * @param array $props Key val array of prop key => prop type (see $this->properties).
	 */
	protected function add_properties( $props = array() ) {

		$this->properties = array_merge( $this->properties, $props );
	}

	/**
	 * Modify allowed post tags for wp_kses for this post
	 *
	 * @since 3.19.2
	 *
	 * @return void
	 */
	protected function allowed_post_tags_set() {
		global $allowedposttags;
		$allowedposttags['iframe'] = array(
			'allowfullscreen' => true,
			'frameborder'     => true,
			'height'          => true,
			'src'             => true,
			'width'           => true,
			'style'           => true,
		);
	}

	/**
	 * Remove modified allowed post tags for wp_kses for this post
	 *
	 * @since 3.19.2
	 *
	 * @return void
	 */
	protected function allowed_post_tags_unset() {
		global $allowedposttags;
		unset( $allowedposttags['iframe'] );
	}

	/**
	 * Wrapper for $this-get() which allows translation of the database value before outputting on screen
	 *
	 * Extending classes should define this and translate any possible strings
	 * with a switch statement or something.
	 * This will return the untranslated string if a translation isn't defined.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Key to retrieve.
	 * @return string
	 */
	public function translate( $key ) {
		$val = $this->get( $key );
		// ******* example *******
		// switch( $key ) {
		// case 'example_key':
		// if ( 'example-val' === $val ) {
		// return translate( 'Example Key', 'lifterlms' );
		// }
		// break;
		// default:
		// return $val;
		// }
		// ******* example *******
		return $val;
	}

	/**
	 * Wrapper for the $this->translate() that echos the result rather than returning it
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Key to translate.
	 * @return void
	 */
	public function _e( $key ) { // phpcs:ignore -- This is to mimic localization functions.
		echo esc_html( $this->translate( $key ) );
	}

	/**
	 * Called immediately after creating / inserting a new post into the database
	 *
	 * This stub can be overwritten by child classes.
	 *
	 * @since 3.0.0
	 *
	 * @return  void
	 */
	protected function after_create() {}

	/**
	 * Create a new post of the Instantiated Model
	 *
	 * This can be called by instantiating an instance with "new"
	 * as the value passed to the constructor.
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Use `wp_slash()` for the post title.
	 *
	 * @param string $title Title to create the post with.
	 * @return int WP Post ID of the new Post on success or 0 on error.
	 */
	private function create( $title = '' ) {
		return wp_insert_post(
			wp_slash(
				/**
				 * Filters the creation arguments used to create a new post.
				 *
				 * The return array is passed through {@see wp_slash} and ultimately
				 * passed directly to {@see wp_insert_post}.
				 *
				 * The dynamic portion of this hook, `{$this->model_post_type}`, refers to the post
				 * model's `$model_post_type` property.
				 *
				 * @since 3.0.0
				 *
				 * @param array $creation_args An array of arguments passed.
				 */
				apply_filters(
					"llms_new_{$this->model_post_type}",
					$this->get_creation_args( $title )
				)
			),
			true
		);
	}

	/**
	 * Clones the Post if the post is cloneable
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Use `LLMS_Generator::get_generated_content()` in favor of deprecated `LLMS_Generator::get_generated_posts()`.
	 *
	 * @return WP_Error|int|null WP_Error, WP Post ID of the clone (new) post, or null if post is not cloneable.
	 */
	public function clone_post() {

		// If post type doesn't support cloning, don't proceed.
		if ( ! $this->is_cloneable() ) {
			return null;
		}

		$this->allowed_post_tags_set();

		$generator = new LLMS_Generator( $this->toArray() );
		$generator->set_generator( 'LifterLMS/Single' . ucwords( $this->model_post_type ) . 'Cloner' );
		if ( ! $generator->is_error() ) {
			$generator->generate();
		}

		$this->allowed_post_tags_unset();

		$generated = $generator->get_generated_content();
		if ( isset( $generated[ $this->db_post_type ] ) ) {
			return $generated[ $this->db_post_type ][0];
		}

		return new WP_Error( 'generator-error', __( 'An unknown error occurred during post cloning. Please try again.', 'lifterlms' ) );
	}

	/**
	 * Trigger an export download of the given post type
	 *
	 * @since 3.3.0
	 * @since 3.19.2 Unknown.
	 * @since 4.8.0 Made sure extra data are added to the posts model array representation during export.
	 *
	 * @return void
	 */
	public function export() {
		// If post type doesn't support exporting don't proceed.
		if ( ! $this->is_exportable() ) {
			return;
		}

		$title = str_replace( ' ', '-', $this->get( 'title' ) );
		$title = preg_replace( '/[^a-zA-Z0-9-]/', '', $title );

		/**
		 * Filters the export file name
		 *
		 * @since Unknown
		 *
		 * @param string          $title     The exported file name. Doesn't include the extension.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		$filename = apply_filters( 'llms_post_model_export_filename', $title . '_' . current_time( 'Ymd' ), $this );

		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$this->allowed_post_tags_set();

		add_filter( 'llms_post_model_to_array_add_extras', '__return_true', 99 );
		$arr = $this->toArray();
		remove_filter( 'llms_post_model_to_array_add_extras', '__return_true', 99 );

		$arr['_generator'] = 'LifterLMS/Single' . ucwords( $this->model_post_type ) . 'Exporter';
		$arr['_source']    = get_site_url();
		$arr['_version']   = llms()->version;

		ksort( $arr );

		echo json_encode( $arr );

		$this->allowed_post_tags_unset();

		die();
	}

	/**
	 * Private getter.
	 *
	 * @since 3.34.0
	 * @since 4.10.0 Add `post_name` as a property to skip scrubbing and add a filter on the list of properties to skip scrubbing.
	 * @since 5.1.2 Pass second parameter to the `get_the_excerpt` filter hook (the WP_Post object), introduced in WordPress 4.5.0.
	 *
	 * @param string  $key The property key.
	 * @param boolean $raw Optional. Whether or not we need to get the raw value. Default false.
	 * @return mixed
	 */
	private function ___get( $key, $raw = false ) {

		// Force numeric id and prevent filtering on the id.
		if ( 'id' === $key ) {

			return absint( $this->$key );

		} elseif ( in_array( $key, array_keys( $this->get_post_properties() ) ) ) {
			$post_key = 'post_' . $key;

			// Ensure post is set globally for filters below.
			global $post;
			$temp = $post;
			$post = $this->post;

			switch ( $key ) {

				case 'content':
					$val = $raw ? $this->post->$post_key : llms_content( $this->post->$post_key );
					break;

				case 'excerpt':
					/* This is a WordPress filter. */
					$val = $raw ? $this->post->$post_key : apply_filters( 'get_the_excerpt', $this->post->$post_key, $this->post );
					break;

				case 'ping_status':
				case 'comment_status':
				case 'menu_order':
					$val = $this->post->$key;
					break;

				case 'title':
					/* This is a WordPress filter. */
					$val = $raw ? $this->post->$post_key : apply_filters( 'the_title', $this->post->$post_key, $this->get( 'id' ) );
					break;

				default:
					$val = $this->post->$post_key;

			}

			// Return the original global.
			$post = $temp;

		} elseif ( ! in_array( $key, $this->get_unsettable_properties() ) ) {

			if ( metadata_exists( 'post', $this->id, $this->meta_prefix . $key ) ) {
				$val = get_post_meta( $this->id, $this->meta_prefix . $key, true );
			} else {
				$val = $this->get_default_value( $key );
			}
		} else {

			return $this->$key;
		}

		// If we found a value, apply default llms get filter and return the value.
		if ( isset( $val ) ) {

			/**
			 * Filters the list of properties which should be excluded from scrubbing during a property read.
			 *
			 * The dynamic portion of this hook, `{$this->model_post_type}`, refers to the post's model type,
			 * for example "course" for an `LLMS_Course`, "membership" for an `LLMS_Membership`, etc...
			 *
			 * @since 4.10.0
			 *
			 * @param string[]        $props An array of property keys to be excluded from scrubbing.
			 * @param LLMS_Post_Model $this  Instance of the post object.
			 */
			$exclude = apply_filters( "llms_get_{$this->model_post_type}_no_scrub_props", array( 'content', 'name' ), $this );
			if ( ! $raw && ! in_array( $key, $exclude, true ) ) {
				$val = $this->scrub( $key, $val );
			}

			/**
			 * Filters the property value
			 *
			 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
			 * "lesson", "membership", etc...
			 * The second dynamic part of this hook, `$key`, refers to the property name.
			 *
			 * @since Unknown
			 *
			 * @param mixed           $val       The property value.
			 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
			 */
			return apply_filters( "llms_get_{$this->model_post_type}_{$key}", $val, $this );

		}

		// Shouldn't ever get here.
		return false;
	}

	/**
	 * Getter
	 *
	 * @since 3.0.0
	 *
	 * @param string  $key The property key.
	 * @param boolean $raw Optional. Whether or not we need to get the raw value. Default is `false`.
	 * @return mixed
	 */
	public function get( $key, $raw = false ) {

		if ( $raw ) {
			return $this->___get( $key, $raw );
		}

		return $this->$key;
	}

	/**
	 * Getter for array values
	 *
	 * Ensures that even empty values return an array.
	 *
	 * @since 3.0.0 Unknown.
	 *
	 * @param string $key Property key.
	 * @return array
	 */
	public function get_array( $key ) {
		$val = $this->get( $key );
		if ( ! is_array( $val ) ) {
			$val = array( $val );
		}
		return $val;
	}

	/**
	 * Getter for date strings with optional date format conversion
	 *
	 * If no format is supplied, the default format available via $this->get_date_format() will be used.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key    Property key.
	 * @param string $format Any valid date format that can be passed to date().
	 * @return string
	 */
	public function get_date( $key, $format = null ) {
		$format = ( ! $format ) ? $this->get_date_format() : $format;
		$raw    = $this->get( $key );
		// Only convert the date if we actually have something stored, otherwise we'll return the current date, which we probably aren't expecting.
		$date = $raw ? date_i18n( $format, strtotime( $raw ) ) : '';

		/**
		 * Filters the date(s)
		 *
		 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 * The second dynamic part of this hook, `$key`, refers to the date property name.
		 *
		 * @since 3.0.0
		 *
		 * @param string          $date      The formatted date.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_{$key}_date", $date, $this );
	}

	/**
	 * Retrieve the default date format for the post model
	 *
	 * This *can* be overridden by child classes if the post type requires a different default date format.
	 *
	 * If no format is supplied by the child class, the default WP date & time formats available
	 * via General Settings will be combined and used.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	protected function get_date_format() {
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		/**
		 * Filters the date format
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 3.0.0
		 *
		 * @param string $format The date format.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_date_format", $format );
	}

	/**
	 * Get the default value of a property
	 *
	 * If defaults don't exist returns an empty string in accordance with the return of get_post_meta() when no metadata exists.
	 *
	 * @since 3.24.0
	 *
	 * @param string $key Property key/name.
	 * @return mixed
	 */
	public function get_default_value( $key ) {
		$defaults = $this->get_property_defaults();
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * Retrieve URL for an image associated with the post
	 *
	 * Currently, only retrieves the featured image if the post type supports it.
	 * In the future, this will allow retrieval of custom post images as well.
	 *
	 * @since 3.3.0
	 * @since 3.8.0 Unknown.
	 *
	 * @param string|array $size Registered image size or a numeric array with width/height.
	 * @param string       $key  Currently unused but here for forward compatibility if
	 *                           additional custom images are added
	 * @return string Empty string if no image or not supported.
	 */
	public function get_image( $size = 'full', $key = '' ) {
		if ( 'thumbnail' === $key && post_type_supports( $this->db_post_type, 'thumbnail' ) ) {
			$url = get_the_post_thumbnail_url( $this->get( 'id' ), $size );
		} else {
			$id = $this->get( $key );
			if ( is_numeric( $id ) ) {
				$src = wp_get_attachment_image_src( $id, $size );
				if ( $src ) {
					$url = $src[0];
				}
			}
		}
		return ! empty( $url ) ? $url : '';
	}

	/**
	 * Retrieve the Post's post type data object
	 *
	 * @since 3.0.0
	 *
	 * @return WP_Post_Type|null
	 */
	public function get_post_type_data() {
		return get_post_type_object( $this->get( 'type' ) );
	}

	/**
	 * Retrieve a label from the post type data object's labels object
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @param string $label Key for the label.
	 * @return string
	 */
	public function get_post_type_label( $label = 'singular_name' ) {
		$obj = $this->get_post_type_data();
		if ( property_exists( $obj, 'labels' ) && property_exists( $obj->labels, $label ) ) {
			return $obj->labels->$label;
		}
		return '';
	}

	/**
	 * Getter for price strings with optional formatting options
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Unknown.
	 * @since 4.8.0 Use strict type comparison where possible.
	 *
	 * @param string $key        Property key.
	 * @param array  $price_args Optional. Array of arguments that can be passed to llms_price(). Default is empty array.
	 * @param string $format     Optional. Format conversion method [html|raw|float]. Default is 'html'.
	 * @return mixed
	 */
	public function get_price( $key, $price_args = array(), $format = 'html' ) {

		$price = $this->get( $key );

		// Handle empty or unset values gracefully.
		if ( '' === $price ) {
			$price = 0;
		}

		/**
		 * Filter the price before formatting the price for display.
		 *
		 * @since 7.8.0
		 */
		$price = apply_filters( "llms_{$this->model_post_type}_get_price_before_formatting", $price, $key, $price_args, $this );

		if ( 'html' === $format || 'raw' === $format ) {
			$price = llms_price( $price, $price_args );
			if ( 'raw' === $format ) {
				$price = strip_tags( $price );
			}
		} elseif ( 'float' === $format ) {
			$price = floatval( number_format( $price, get_lifterlms_decimals(), '.', '' ) );
		} else {
			/**
			* Allows applying custom formatting to price(s).
			*
			* This is only fired when the `get_price()`'s `$format` passed param is not one of html|raw|float.
			*
			* @since Unknown
			*
			* The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
			* "lesson", "membership", etc...
			* The second dynamic part of this hook, `$key`, refers to the price property name.
			* The third dynamic part of this hook, `$format`, refers to the custom format conversion method.
			*/
			$price = apply_filters( "llms_get_{$this->model_post_type}_{$key}_{$format}", $price, $key, $price_args, $format, $this );
		}

		/**
		 * Filters the price(s)
		 *
		 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 * The second dynamic part of this hook, `$key`, refers to the price property name.
		 *
		 * @since Unknown
		 *
		 * @param string          $price      The maybe formatted price.
		 * @param string          $key        The price property name.
		 * @param array           $price_args Array of arguments that can be passed to llms_price().
		 * @param string          $format     Format conversion method.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_{$key}_price", $price, $key, $price_args, $format, $this );
	}

	/**
	 * Retrieve the default values for properties
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	public function get_property_defaults() {
		/**
		 * Filters the defaults properties.
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 3.24.0
		 *
		 * @param array           $property_defaults Array of default property values.
		 * @param LLMS_Post_Model $llms_post         The LLMS_Post_Model instance.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_property_defaults", $this->property_defaults, $this );
	}

	/**
	 * An array of default arguments to pass to $this->create() when creating a new post
	 *
	 * This *should* be overridden by child classes.
	 *
	 * @since 3.0.0
	 * @since 3.18.0 Unknown.
	 *
	 * @param array $args Args of data to be passed to wp_insert_post.
	 * @return array
	 */
	protected function get_creation_args( $args = null ) {

		// Allow nothing to be passed in.
		if ( empty( $args ) ) {
			$args = array();
		}

		// Backwards compat to original 3.0.0 format when just a title was passed in.
		if ( is_string( $args ) ) {
			$args = array(
				'post_title' => $args,
			);
		}

		$args = wp_parse_args(
			$args,
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_content'   => '',
				'post_excerpt'   => '',
				'post_status'    => 'draft',
				'post_title'     => '',
				'post_type'      => $this->get( 'db_post_type' ),
			)
		);

		/**
		 * Filters the llms post creation args
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 3.24.0
		 *
		 * @param array           $args      Array of default creation args to be passed to `wp_insert_post()`.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		return apply_filters( "llms_{$this->model_post_type}_get_creation_args", $args, $this );
	}

	/**
	 * Get media embeds.
	 *
	 * @since 3.17.0
	 * @since 3.17.5 Unknown.
	 * @since 7.7.0 Added function to get provider support.
	 *
	 * @param string $type Optional. Embed type [video|audio]. Default is 'video'.
	 * @param string $prop Optional. Postmeta property name. Default is empty string.
	 *                     If not supplied it will default to {$type}_embed.
	 * @return string
	 */
	protected function get_embed( $type = 'video', $prop = '' ) {

		$ret = '';

		$prop = $prop ? $prop : $type . '_embed';
		$url  = $this->get( $prop );
		if ( trim( $url ) && parse_url( $url ) ) {
			$this->get_provider_support( $url );

			$ret = wp_oembed_get( sanitize_url( $url ) );

			if ( ! $ret ) {

				$ret = do_shortcode( sprintf( '[%1$s src="%2$s"]', $type, $url ) );

			}
		}
		/**
		 * Filters the embed html
		 *
		 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 * The second dynamic portion of this hook, `$type`, refers to the embed type [video|audio].
		 *
		 * @since Unknown
		 *
		 * @param array           $embed     The embed html.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 * @param string          $type      Embed type [video|audio].
		 * @param string          $prop      Postmeta property name.
		 */
		return apply_filters( "llms_{$this->model_post_type}_{$type}", $ret, $this, $type, $prop );
	}

	/**
	 * Get a property's data type for scrubbing
	 *
	 * Used by $this->scrub() to determine how to scrub the property.
	 *
	 * @since 3.3.0
	 *
	 * @param string $key Property key.
	 * @return string
	 */
	protected function get_property_type( $key ) {

		$props = $this->get_properties();

		// Check against the properties array.
		if ( in_array( $key, array_keys( $props ) ) ) {
			$type = $props[ $key ];
		} else {
			$type = 'text';
		}

		return $type;
	}

	/**
	 * Retrieve an array of post properties
	 *
	 * These properties need to be get/set with alternate methods.
	 *
	 * @since 3.0.0
	 * @since 3.31.0 Treat excerpts as HTML instead of plain text.
	 * @since 3.34.0 Add date and modified dates GMT version, comment and ping status, post password and menu_order.
	 *
	 * @return array
	 */
	protected function get_post_properties() {
		/**
		 * Filters the properties of the model that are properties of WP_Post.
		 *
		 * @since Unknown
		 *
		 * @param array           $post_properties Associative array of the type $post_property_name => type.
		 * @param LLMS_Post_Model $llms_post       The LLMS_Post_Model instance.
		 */
		return apply_filters(
			'llms_post_model_get_post_properties',
			array(
				'author'         => 'absint',
				'content'        => 'html',
				'date'           => 'text',
				'date_gmt'       => 'text',
				'excerpt'        => 'html',
				'password'       => 'text',
				'parent'         => 'absint',
				'menu_order'     => 'absint',
				'modified'       => 'text',
				'modified_gmt'   => 'text',
				'name'           => 'text',
				'status'         => 'text',
				'title'          => 'text',
				'type'           => 'text',
				'comment_status' => 'text',
				'ping_status'    => 'text',
			),
			$this
		);
	}

	/**
	 * Retrieve an array of properties defined by the model
	 *
	 * @since 3.3.0
	 * @since 3.16.0 Unknown.
	 *
	 * @return array
	 */
	public function get_properties() {
		$props = array_merge( $this->get_post_properties(), $this->properties );
		/**
		 * Filters the llms post properties
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since Unknown
		 *
		 * @param array           $properties Array of properties defined by the model
		 * @param LLMS_Post_Model $llms_post  The LLMS_Post_Model instance.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_properties", $props, $this );
	}

	/**
	 * Get the properties that will be used to generate the array representation of the model.
	 *
	 * @since 5.4.1
	 *
	 * @return string[] Array of property keys to be used by {@see toArray}.
	 */
	protected function get_to_array_properties() {

		$all_props = array_keys( $this->get_properties() );

		/**
		 * Filters the properties which will excluded form the array representation of the model
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since Unknown
		 *
		 * @param string[]        $excluded  Array of property names.
		 * @param string[]        $all_props The full property list without the applied exclusions.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		$excluded = apply_filters(
			"llms_get_{$this->model_post_type}_excluded_to_array_properties",
			$this->get_to_array_excluded_properties(),
			$all_props,
			$this
		);

		$props = array_diff(
			$all_props,
			$excluded
		);

		/**
		 * Filters the properties which will populate the array representation of the model.
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since Unknown
		 *
		 * @param string[]        $props     Array of property names.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		return apply_filters(
			"llms_get_{$this->model_post_type}_to_array_properties",
			$props,
			$this
		);
	}

	/**
	 * Get the properties that will be explicitly excluded from the array representation of the model.
	 *
	 * This stub can be overloaded by an extending class and the property list is filterable via the
	 * {@see llms_get_{$this->model_post_type}_excluded_to_array_properties} filter.
	 *
	 * @since 5.4.1
	 *
	 * @return string[]
	 */
	protected function get_to_array_excluded_properties() {
		return array();
	}

	/**
	 * Retrieve the registered Label of the post's current status
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_status_name() {
		$obj = get_post_status_object( $this->get( 'status' ) );
		/**
		 * Filters the registered label of the post's current status.
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 3.0.0
		 *
		 * @param string $label The registered label of the post's current status.
		 */
		return apply_filters( "llms_get_{$this->model_post_type}_status_name", $obj->label );
	}

	/**
	 * Get an array of terms for a given taxonomy for the post
	 *
	 * @since 3.8.0
	 *
	 * @param string  $tax    Taxonomy name.
	 * @param boolean $single Return only one term as an int, useful for taxes which
	 *                        Can only have one term (eg: visibilities and difficulties and such)
	 * @return mixed When single a single term object or null.
	 *               When not single an array of term objects.
	 */
	public function get_terms( $tax, $single = false ) {

		$terms = get_the_terms( $this->get( 'id' ), $tax );

		if ( $single ) {
			return $terms ? $terms[0] : null;
		}

		return $terms ? $terms : array();
	}

	/**
	 * Array of properties which *cannot* be set
	 *
	 * If a child class adds any properties which should not be settable
	 * the class should override this property and add their custom
	 * properties to the array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_unsettable_properties() {
		/**
		 * Filters the properties of the model that *cannot* be set
		 *
		 * @since Unknown
		 *
		 * @param array           $unsettable_properties Array of property names.
		 * @param LLMS_Post_Model $llms_post             The LLMS_Post_Model instance.
		 */
		return apply_filters(
			'llms_post_model_get_unsettable_properties',
			array(
				'db_post_type',
				'id',
				'meta_prefix',
				'model_post_type',
				'post',
			),
			$this
		);
	}

	/**
	 * Determine if the associated post is exportable
	 *
	 * @since 3.3.0
	 *
	 * @return boolean
	 */
	public function is_cloneable() {
		return post_type_supports( $this->db_post_type, 'llms-clone-post' );
	}

	/**
	 * Determine if the associated post is exportable
	 *
	 * @since 3.3.0
	 *
	 * @return boolean
	 */
	public function is_exportable() {
		return post_type_supports( $this->db_post_type, 'llms-export-post' );
	}

	/**
	 * Format the object for json serialization
	 *
	 * Encodes the results of $this->toArray().
	 *
	 * @todo The `mixed` return type declared by the parent method, which should be defined here as well,
	 *       is not available until PHP 8.0. Once support is dropped for 7.4 we can add the return type declaration
	 *       and remove the `#[ReturnTypeWillChange]` attribute. This *must* happen before the release of PHP 9.0.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		/**
		 * Filters the properties of the model that *cannot* be set
		 *
		 * @since 3.3.0
		 *
		 * @param array           $model     Array representation of the LLMS_Post_Model object.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		return apply_filters( 'llms_post_model_json_serialize', $this->toArray(), $this );
	}

	/**
	 * Scrub field according to it's type
	 *
	 * This is automatically called by set() method before anything is actually set.
	 *
	 * @since 3.0.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param string $key Property key.
	 * @param mixed  $val Property value.
	 * @return mixed
	 */
	protected function scrub( $key, $val ) {
		/**
		 * Filters the property type being scrubbed.
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since Unknown
		 *
		 * @param string          $type      The property type.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 */
		$type = apply_filters( "llms_get_{$this->model_post_type}_property_type", $this->get_property_type( $key ), $this );

		/**
		 * Filters the scrubbed property.
		 *
		 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 * The second dynamic part of this hook, `$key`, refers to the property name.
		 *
		 * @since Unknown
		 *
		 * @param mixed           $scrubbed  The scrubbed property value.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 * @param string          $key       The property name.
		 * @param mixed           $val       The original property value.
		 */
		return apply_filters( "llms_scrub_{$this->model_post_type}_field_{$key}", $this->scrub_field( $val, $type ), $this, $key, $val );
	}

	/**
	 * Scrub fields according to datatype
	 *
	 * @since 3.0.0
	 * @since 3.19.2 Unknown.
	 * @since 5.9.0 Use `wp_strip_all_tags()` in favor of `strip_tags()`.
	 *              Only strip tags from string values.
	 *              Coerce `null` html input to an empty string.
	 *
	 * @param mixed  $val  Property value to scrub.
	 * @param string $type Data type.
	 * @return mixed
	 */
	protected function scrub_field( $val, $type ) {

		if ( is_string( $val ) && 'html' !== $type ) {
			$val = wp_strip_all_tags( $val );
		}

		switch ( $type ) {

			case 'absint':
				$val = absint( $val );
				break;

			case 'array':
				if ( '' === $val ) {
					$val = array();
				}
				$val = (array) $val;
				break;

			case 'bool':
			case 'boolean':
				$val = boolval( $val );
				break;

			case 'float':
				$val = floatval( $val );
				break;

			case 'html':
				$this->allowed_post_tags_set();
				$val = wp_kses_post( $val ?? '' );
				$this->allowed_post_tags_unset();
				break;

			case 'int':
				$val = intval( $val );
				break;

			case 'yesno':
				$val = 'yes' === $val ? 'yes' : 'no';
				break;

			case 'url':
				$val = sanitize_url( $val );
				break;

			case 'text':
			case 'string':
			default:
				$val = sanitize_text_field( $val );

		}

		return $val;
	}

	/**
	 * Setter.
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Use `wp_slash()` when setting properties.
	 * @since 3.34.0 Turned to be only a wrapper for the set_bulk() method.
	 * @since 6.5.0 Introduced `$allow_same_meta_value` param.
	 *
	 * @param string|array $key_or_array          Key of the property or an associative array of key/val pairs.
	 * @param mixed        $val                   Value to set the property with.
	 *                                            This parameter will be ignored when the first parameter is an associative array of key/val pairs.
	 * @param boolean      $allow_same_meta_value Whether or not updating a meta with the same value as stored in the db is allowed.
	 * @return boolean true on success, false on error or if the submitted value is the same as what's in the database and `$allow_same_meta_value` is `false`.
	 */
	public function set( $key_or_array, $val = '', $allow_same_meta_value = false ) {

		$model_array = $key_or_array;

		if ( ! is_array( $key_or_array ) ) {
			$model_array = array(
				$key_or_array => $val,
			);
		}

		return $this->set_bulk( $model_array, false, $allow_same_meta_value );
	}


	/**
	 * Bulk setter.
	 *
	 * @since 3.34.0
	 * @since 3.36.1 Use WP_Error::$errors in place of WP_Error::has_errors() to support WordPress version prior to 5.1.
	 * @since 5.3.1 Fix quote slashing when the user is not an admin.
	 * @since 6.5.0 Introduced `$allow_same_meta_value` param.
	 *               Code reorganization.
	 *
	 * @param array   $model_array           Associative array of key/val pairs.
	 * @param array   $wp_error              Whether or not return a WP_Error.
	 * @param boolean $allow_same_meta_value Whether or not updating a meta with the same value as stored in the db is allowed.
	 * @return boolean|WP_Error True on success. If the param $wp_error is set to false this will be false on error or if there was nothing to update.
	 *                          Otherwise, this will be a WP_Error object collecting all the errors encountered along the way.
	 */
	public function set_bulk( $model_array, $wp_error = false, $allow_same_meta_value = false ) {

		if ( empty( $model_array ) ) {
			return $wp_error ? new WP_Error( 'empty_data', __( 'Empty data', 'lifterlms' ) ) : false;
		}

		$llms_post = $this->parse_properties_to_set( $model_array );

		if ( empty( $llms_post ) ) {
			return $wp_error ? new WP_Error( 'invalid_data', __( 'Invalid data', 'lifterlms' ) ) : false;
		}

		$update_post_properties = $this->update_post_properties( $llms_post['post'] );
		$update_meta_properties = $this->update_meta_properties( $llms_post['meta'], $allow_same_meta_value );

		$error = is_wp_error( $update_post_properties ) ? $update_post_properties : new WP_Error();
		if ( is_wp_error( $update_meta_properties ) ) {
			foreach ( $update_meta_properties->get_error_messages( 'invalid_meta' ) as $message ) {
				$error->add( 'invalid_meta', $message );
			}
		}

		if ( ! empty( $error->has_errors() ) ) {
			return $wp_error ? $error : false;
		}

		return true;
	}

	/**
	 * Parse the LifterLMS post properties to set.
	 *
	 * Logic moved from `set_bulk()` method.
	 *
	 * @since 6.5.0
	 *
	 * @param array $model_array Associative array of key/val pairs.
	 * @return array|bool Returns `false` if nothing to set or an array that contains all the post properties and all the metas to set.
	 */
	private function parse_properties_to_set( $model_array ) {

		$llms_post = array(
			'post' => array(),
			'meta' => array(),
		);

		$post_properties       = array_keys( $this->get_post_properties() );
		$unsettable_properties = $this->get_unsettable_properties();

		foreach ( $model_array as $key => $val ) {

			// Sanitize the post properties keys by removing the 'post_' prefix.
			if ( 'post_' === substr( $key, 0, 5 ) ) {
				$_key = substr( $key, 5 );
				if ( in_array( $_key, $post_properties, true ) ) {
					$key = $_key;
				}
			}

			$val = $this->scrub( $key, $val );

			/**
			 * WordPress Post properties to be updated using the wp_insert_post() function.
			 *
			 * The 'edit_date' must be passed to the wp_update_post() function in order
			 * to allow 'drafty' posts' creation date to be modified.
			 */
			if ( in_array( $key, $post_properties, true ) || 'edit_date' === $key ) {

				$type          = 'post';
				$llms_post_key = "post_{$key}";

				switch ( $key ) {

					case 'content':
						/** This is a WordPress core filter. {@see kses_init_filters()}*/
						$val = stripslashes( apply_filters( 'content_save_pre', addslashes( $val ) ) );
						break;

					case 'excerpt':
						/** This is a WordPress core filter. {@see kses_init_filters()}*/
						$val = stripslashes( apply_filters( 'excerpt_save_pre', addslashes( $val ) ) );
						break;

					case 'edit_date':
					case 'ping_status':
					case 'comment_status':
					case 'menu_order':
						$llms_post_key = $key;
						break;

					case 'title':
						/** This is a WordPress core filter. {@see kses_init_filters()}*/
						$val = stripslashes( apply_filters( 'title_save_pre', addslashes( $val ) ) );
						break;
				}
			} elseif ( ! in_array( $key, $unsettable_properties, true ) ) {
				$type          = 'meta';
				$llms_post_key = $key;
			} else {
				continue;
			}

			/**
			 * Filters the property value prior to be set.
			 *
			 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
			 * "lesson", "membership", etc...
			 * The second dynamic part of this hook, `$key`, refers to the property name.
			 *
			 * @since Unknown
			 *
			 * @param mixed           $val       The property value.
			 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
			 */
			$llms_post[ $type ][ $llms_post_key ] = apply_filters( "llms_set_{$this->model_post_type}_{$key}", $val, $this );

		}

		return empty( $llms_post['post'] ) && empty( $llms_post['meta'] ) ? false : $llms_post;
	}

	/**
	 * Update post properties.
	 *
	 * Logic moved from `set_bulk()` method.
	 *
	 * @since 6.5.0
	 *
	 * @param array $post_properties Array of post properties to set.
	 * @return void|WP_Error
	 */
	private function update_post_properties( $post_properties ) {

		if ( empty( $post_properties ) ) {
			return;
		}

		$args = array_merge(
			$post_properties,
			array(
				'ID' => $this->get( 'id' ),
			)
		);

		$update_post = wp_update_post( wp_slash( $args ), true );

		if ( is_wp_error( $update_post ) ) {
			return $update_post;
		}

		// Update this post.
		$this->post = get_post( $this->get( 'id' ) );
	}


	/**
	 * Update post meta properties.
	 *
	 * Logic moved from `set_bulk()` method.
	 *
	 * @param array   $post_meta_properties  Array of post meta properties to set.
	 * @param boolean $allow_same_meta_value Whether or not updating a meta with the same value as stored in the db is allowed.
	 *                                       By default `update_post_meta` doesn't allow that.
	 * @return void|WP_Error
	 */
	private function update_meta_properties( $post_meta_properties, $allow_same_meta_value ) {

		if ( empty( $post_meta_properties ) ) {
			return;
		}

		$error = new WP_Error();

		foreach ( $post_meta_properties as $key => $val ) {

			if ( $allow_same_meta_value ) {
				/**
				 * Do pretty much(*) the same check for a duplicate value as in `update_metadata()`
				 * to avoid `update_post_meta()` returning false.
				 * {@see WP_REST_Meta_Fields::update_meta_value()}.
				 *
				 * If the new value to be set equals the old one don't update it.
				 *
				 * (*) This is not exactly the same check you can find in `update_metadata()` as that
				 * account for multiple meta values for the same key, while we don't.
				 */
				$old_value = get_post_meta( $this->id, $this->meta_prefix . $key, true );
				if ( $this->is_meta_value_same_as_stored_value( $key, $old_value, $val ) ) {
					continue;
				}
			}

			$u = update_post_meta( $this->id, $this->meta_prefix . $key, wp_slash( $val ) );

			if ( ! ( is_numeric( $u ) || true === $u ) ) {
				$error->add( 'invalid_meta', sprintf( __( 'Cannot insert/update the %s meta', 'lifterlms' ), $key ) );
			}
		}

		if ( $error->has_errors() ) {
			return $error;
		}
	}

	/**
	 * Checks if the user provided value is equivalent to a stored value for the given meta key.
	 *
	 * {@see WP_REST_Meta_Fields::is_meta_value_same_as_stored_value()}.
	 *
	 * @param string $key          The un-prefixed meta key being checked.
	 * @param mixed  $stored_value The currently stored value retrieved from get_metadata().
	 * @param mixed  $new_value    The new value.
	 * @return bool
	 */
	private function is_meta_value_same_as_stored_value( $key, $stored_value, $new_value ) {

		$sanitized = sanitize_meta( $this->meta_prefix . $key, $new_value, 'post', $this->db_post_type );

		// The return value of get_metadata will always be a string for scalar types.
		$scalar_types = array(
			'string',
			'text',
			'absint',
			'yesno',
			'html',
			'float',
			'int',
			'bool',
			'boolean',
		);

		if ( in_array( $this->get_property_type( $key ), $scalar_types, true ) ) {
			$sanitized = (string) $sanitized;
		}

		return $sanitized === $stored_value;
	}

	/**
	 * Update terms for the post for a given taxonomy
	 *
	 * @since 3.8.0
	 *
	 * @param array   $terms  Array of terms (name or ids).
	 * @param string  $tax    The name of the tax.
	 * @param boolean $append Optional. If true, will append the terms, false will replace existing terms. Default is `false`.
	 * @return bool
	 */
	public function set_terms( $terms, $tax, $append = false ) {
		$set = wp_set_object_terms( $this->get( 'id' ), $terms, $tax, $append );
		// wp_set_object_terms has 3 options when unsuccessful and only 1 for success
		// an array of terms when successful, let's keep it simple...
		return is_array( $set );
	}

	/**
	 * Coverts the object to an associative array
	 *
	 * Any property returned by $this->get_properties() will be retrieved
	 * via $this->get() and added to the array.
	 *
	 * Extending classes can add additional properties to the array
	 * by overriding $this->toArrayAfter().
	 *
	 * This function is also utilized to serialize the object to JSON.
	 *
	 * @since 3.3.0
	 * @since 3.17.0 Unknown.
	 * @since 4.7.0 Add exporting of extra data (images and blocks).
	 * @since 4.8.0 Exclude extra data by default. Added `llms_post_model_to_array_add_extras` filter.
	 * @since 5.4.1 Load properties to be used to generate the array from the new `get_to_array_properties()` method.
	 *
	 * @return array
	 */
	public function toArray() {

		$arr = array(
			'id' => $this->get( 'id' ),
		);

		foreach ( $this->get_to_array_properties() as $prop ) {

			if ( in_array( $prop, array( 'content', 'excerpt', 'title' ), true ) ) {
				$post_prop    = "post_{$prop}";
				$arr[ $prop ] = $this->post->$post_prop;
			} else {
				$arr[ $prop ] = $this->get( $prop );
			}
		}

		// Add the featured image if the post type supports it.
		if ( post_type_supports( $this->db_post_type, 'thumbnail' ) ) {
			$arr['featured_image'] = $this->get_image( 'full', 'thumbnail' );
		}

		// Expand instructors if instructors are supported.
		if ( ! empty( $arr['instructors'] ) && method_exists( $this, 'instructors' ) ) {

			foreach ( $arr['instructors'] as &$data ) {
				$instructor = llms_get_instructor( $data['id'] );
				if ( $instructor ) {
					$data = array_merge( $data, $instructor->toArray() );
				}
			}
		} elseif ( ! empty( $arr['author'] ) ) {

			$instructor = llms_get_instructor( $arr['author'] );
			if ( $instructor ) {
				$arr['author'] = $instructor->toArray();
			}
		}

		/**
		 * Filter whether or not "extra" content should be included in the post array
		 *
		 * `__return_true` (with priority 99) is used to force the filter on during exports.
		 *
		 * @since 4.8.0
		 *
		 * @param boolean         $include Whether or not to include extra data. Default is `false`, except on during exports.
		 * @param LLMS_Post_Model $model   Post model instance.
		 */
		$add_array_extra = apply_filters( 'llms_post_model_to_array_add_extras', false, $this );

		/**
		 * Filter whether or not "extra" content should be included in the post array
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 4.7.0
		 *
		 * @param boolean         $include Whether or not to include extra data.
		 * @param LLMS_Post_Model $model   Post model instance.
		 */
		$add_array_extra = apply_filters( "llms_{$this->model_post_type}_to_array_add_extras", $add_array_extra, $this );

		if ( $add_array_extra ) {
			$arr = $this->to_array_extra( $arr );
		}

		// Add custom fields.
		$arr = $this->toArrayCustom( $arr );

		// Allow extending classes to add properties easily without overriding the class.
		$arr = $this->toArrayAfter( $arr );

		$cpt_data = $this->get_post_type_data();
		if ( $cpt_data->public ) {
			$arr['permalink'] = get_permalink( $this->get( 'id' ) );
		}

		ksort( $arr ); // Because i'm anal...

		/**
		 * Filter the final post array created when converting the object to an array
		 *
		 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
		 * "lesson", "membership", etc...
		 *
		 * @since 4.7.0
		 *
		 * @param array           $arr   Associative array of the model.
		 * @param LLMS_Post_Model $model Post model instance.
		 */
		return apply_filters( "llms_{$this->model_post_type}_to_array", $arr, $this );
	}

	/**
	 * Enqueues provider scripts for the URL.
	 *
	 * @since 7.7.0
	 *
	 * @param string $url URL to check.
	 * @return null If no provider is found.
	 */
	public function get_provider_support( $url ) {

		$host = wp_parse_url( $url, PHP_URL_HOST );

		// VideoPress Provider.
		if ( is_plugin_active( 'jetpack-videopress/jetpack-videopress.php' ) ) {
			if ( strpos( $host, 'videopress.com' ) !== false || strpos( $host, 'video.wordpress.com' ) !== false ) {
				wp_enqueue_script( 'videopress-token-bridge', plugins_url() . '/jetpack-videopress/jetpack_vendor/automattic/jetpack-videopress/src/../build/lib/token-bridge.js', array(), llms()->version, true );

				wp_localize_script( 'videopress-token-bridge', 'videopressAjax', array() );
			}
		}

		return null;
	}



	/**
	 * Called before data is sorted and returned by $this->toArray()
	 *
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json.
	 *
	 * @since 3.3.0
	 *
	 * @param array $arr Array of data to be serialized.
	 * @return array
	 */
	protected function toArrayAfter( $arr ) {
		return $arr;
	}

	/**
	 * Add "extra" data to the post array during export/serialization
	 *
	 * This method adds two arrays of data, "blocks" and "images".
	 *
	 * The "blocks" array is an array of reusable blocks used in the post's content. During
	 * an import these blocks will be imported into the site.
	 *
	 * The "images" array is an array of image element source URLs found in the post's content. During
	 * an import these images will be imported into the new site via media sideloading.
	 *
	 * @since 4.7.0
	 *
	 * @param array $arr Post array from `toArray()`.
	 * @return array[]
	 */
	protected function to_array_extra( $arr ) {

		$arr['_extras'] = array(
			'blocks' => empty( $arr['content'] ) ? array() : $this->to_array_extra_blocks( $arr['content'] ),
			'images' => empty( $arr['content'] ) ? array() : $this->to_array_extra_images( $arr['content'] ),
		);

		return $arr;
	}

	/**
	 * Add reusable blocks found in the post's content to the post's array
	 *
	 * @since 4.7.0
	 *
	 * @param string $content Raw `post_content` string.
	 * @return array[] {
	 *     Array of reusable block information arrays. The array key is the WP_Post ID of the reusable block.
	 *
	 *     @type string $title   Reusable block title.
	 *     @type string $content Reusable block content.
	 * }
	 */
	protected function to_array_extra_blocks( $content ) {

		$blocks = array();

		foreach ( parse_blocks( $content ) as $block ) {

			if ( 'core/block' !== $block['blockName'] ) {
				continue;
			}

			$post = get_post( $block['attrs']['ref'] );
			if ( ! $post ) {
				continue;
			}

			$blocks[ $post->ID ] = array(
				'title'   => $post->post_title,
				'content' => $post->post_content,
			);
		}

		return $blocks;
	}

	/**
	 * Add images found in the post's content to the post's array
	 *
	 * @since 4.7.0
	 *
	 * @param string $content Raw `post_content` string.
	 * @return string[] Array of image source URLs.
	 */
	protected function to_array_extra_images( $content ) {

		$images = array();
		$dom    = llms_get_dom_document( $content );
		if ( is_wp_error( $dom ) ) {
			return $images;
		}

		$site_url = get_site_url();
		foreach ( $dom->getElementsByTagName( 'img' ) as $img ) {
			$src = $img->getAttribute( 'src' );
			// Only include images stored in this site's media library.
			if ( 0 !== strpos( $src, $site_url ) ) {
				continue;
			}
			$images[] = $src;
		}

		return array_values( array_unique( $images ) );
	}

	/**
	 * Called by toArray to add custom fields via get_post_meta()
	 *
	 * Removes all custom props registered to the $this->properties automatically.
	 * Also removes some fields used by the WordPress core that don't hold necessary data.
	 * Extending classes may override this class to exclude, extend, or modify the custom fields for a post type.
	 *
	 * @since 3.16.11
	 * @since 3.30.0 Use `maybe_unserialize()` to ensure array data is accessible as an array.
	 * @since 3.30.2 Add filter to allow 3rd parties to prevent a field from being added to the custom field array.
	 *
	 * @param array $arr Existing post array.
	 * @return array
	 */
	protected function toArrayCustom( $arr ) {

		// Build an array of keys that are registered or can be excluded as a custom field.
		$props = array_keys( $this->get_properties() );
		foreach ( $props as &$prop ) {
			$prop = $this->meta_prefix . $prop;
		}
		$props[] = '_edit_lock';
		$props[] = '_edit_last';

		// Get all meta data.
		$custom = array();
		foreach ( get_post_meta( $this->get( 'id' ) ) as $key => $vals ) {

			// Skip registered fields or fields 3rd parties want to skip.
			/**
			 * Filters whether the custom field should be excluded in the array representation of the post model
			 *
			 * The dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type. For example "course",
			 * "lesson", "membership", etc...
			 *
			 * @since 3.30.2
			 *
			 * @param boolean         $exclude   Whether the custom field should be excluded. Default is `false`.
			 * @param string          $key       The custom field name.
			 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
			 */
			if ( in_array( $key, $props, true ) || apply_filters( "llms_{$this->model_post_type}_skip_custom_field", false, $key, $this ) ) {
				continue;
			}

			// Add it.
			$custom[ $key ] = array_map( 'maybe_unserialize', $vals );

		}

		// Add the compiled custom array.
		$arr['custom'] = $custom;

		return $arr;
	}
}
