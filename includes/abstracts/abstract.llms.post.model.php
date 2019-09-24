<?php
/**
 * Defines base methods and properties for programmatically interfacing with LifterLMS Custom Post Types
 *
 * @package LifterLMS/Abstracts
 *
 * @since 3.0.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Model abstract
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
	 * @since  3.0.0
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
	 * Allows models to use unprefixed post type names for filters and more
	 * ie: "order" for the "llms_order" post type
	 *
	 * @var string
	 * @since 3.0.0
	 */
	protected $model_post_type;

	/**
	 * A prefix to add to all meta properties
	 * Child classes can redefine this
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
	 * @var     array
	 * @since   3.3.0
	 * @version 3.3.0
	 */
	protected $properties = array();

	/**
	 * Array of default property values
	 * key => default value
	 *
	 * @var  array
	 * @since   3.24.0
	 * @version 3.24.0
	 */
	protected $property_defaults = array();

	/**
	 * Constructor
	 * Setup ID and related post property
	 *
	 * @param   string|int|LLMS_Post_Model|WP_Post $model 'new', WP post id, instance of an extending class, instance of WP_Post
	 * @param   array                              $args  args to create the post, only applies when $model is 'new'
	 * @return  void
	 * @since   3.0.0
	 * @version 3.13.0
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
	 * @param  string $key  check if a key exists in the database
	 * @return boolean
	 * @since  3.0.0
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $this->meta_prefix . $key );
	}

	/**
	 * Magic Setter
	 *
	 * @param string $key  key of the property
	 * @param mixed  $val  value to set the property with
	 * @return  void
	 * @since  3.0.0
	 */
	public function __set( $key, $val ) {
		$this->$key = $val;
	}

	/**
	 * Allow extending classes to add custom meta properties to the object
	 *
	 * @param    array $props  key val array of prop key => prop type (see $this->properties)
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function add_properties( $props = array() ) {

		$this->properties = array_merge( $this->properties, $props );

	}

	/**
	 * Modify allowed post tags for wp_kses for this post
	 *
	 * @return   void
	 * @since    3.19.2
	 * @version  3.19.2
	 */
	protected function allowed_post_tags_set() {
		global $allowedposttags;
		$allowedposttags['iframe'] = array(
			'allowfullscreen' => true,
			'frameborder'     => true,
			'height'          => true,
			'src'             => true,
			'width'           => true,
		);
	}

	/**
	 * Remove modified allowed post tags for wp_kses for this post
	 *
	 * @return   void
	 * @since    3.19.2
	 * @version  3.19.2
	 */
	protected function allowed_post_tags_unset() {
		global $allowedposttags;
		unset( $allowedposttags['iframe'] );
	}

	/**
	 * Wrapper for $this-get() which allows translation of the database value before outputting on screen
	 *
	 * Extending classes should define this and translate any possible strings
	 * with a switch statement or something
	 * this will return the untranslated string if a translation isn't defined
	 *
	 * @param    string $key  key to retrieve
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
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
	 * @param    string $key  key to retrieve
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function _e( $key ) {
		echo $this->translate( $key );
	}

	/**
	 * Called immediately after creating / inserting a new post into the database
	 * This stub can be overwritten by child classes
	 *
	 * @since    3.0.0
	 * @version  3.0.0
	 * @return  void
	 */
	protected function after_create() {}

	/**
	 * Create a new post of the Instantiated Model
	 * This can be called by instantiating an instance with "new"
	 * as the value passed to the constructor
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Use `wp_slash()` for the post title.
	 *
	 * @param string $title Title to create the post with.
	 * @return int WP Post ID of the new Post on success or 0 on error.
	 */
	private function create( $title = '' ) {
		return wp_insert_post( wp_slash( apply_filters( 'llms_new_' . $this->model_post_type, $this->get_creation_args( $title ) ) ), true );
	}

	/**
	 * Clones the Post if the post is cloneable
	 *
	 * @return   WP_Error|int|null WP_Error, WP Post ID of the clone (new) post, or null if post is not cloneable.
	 * @since    3.3.0
	 */
	public function clone_post() {

		// if post type doesn't support cloning, don't proceed
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

		$generated = $generator->get_generated_posts();
		if ( isset( $generated[ $this->db_post_type ] ) ) {
			return $generated[ $this->db_post_type ][0];
		}

		return new WP_Error( 'generator-error', __( 'An unknown error occurred during post cloning. Please try again.', 'lifterlms' ) );

	}

	/**
	 * Trigger an export download of the given post type
	 *
	 * @return   void
	 * @since    3.3.0
	 * @version  3.19.2
	 */
	public function export() {

		// if post type doesnt support exporting don't proceed
		if ( ! $this->is_exportable() ) {
			return;
		}

		$title = str_replace( ' ', '-', $this->get( 'title' ) );
		$title = preg_replace( '/[^a-zA-Z0-9-]/', '', $title );

		$filename = apply_filters( 'llms_post_model_export_filename', $title . '_' . current_time( 'Ymd' ), $this );

		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$this->allowed_post_tags_set();

		$arr = $this->toArray();

		$arr['_generator'] = 'LifterLMS/Single' . ucwords( $this->model_post_type ) . 'Exporter';
		$arr['_source']    = get_site_url();
		$arr['_version']   = LLMS()->version;

		ksort( $arr );

		echo json_encode( $arr );

		$this->allowed_post_tags_unset();

		die();

	}

	/**
	 * Private getter.
	 *
	 * @since 3.34.0
	 *
	 * @param string  $key The property key.
	 * @param boolean $raw Optional. Whether or not we need to get the raw value. Default false.
	 * @return mixed
	 */
	private function ___get( $key, $raw = false ) {

		// force numeric id and prevent filtering on the id.
		if ( 'id' === $key ) {

			return absint( $this->$key );

		} elseif ( in_array( $key, array_keys( $this->get_post_properties() ) ) ) {
			$post_key = 'post_' . $key;

			// ensure post is set globally for filters below.
			global $post;
			$temp = $post;
			$post = $this->post;

			switch ( $key ) {

				case 'content':
					$val = $raw ? $this->post->$post_key : llms_content( $this->post->$post_key );
					break;

				case 'excerpt':
					$val = $raw ? $this->post->$post_key : apply_filters( 'get_the_excerpt', $this->post->$post_key );
					break;

				case 'ping_status':
				case 'comment_status':
				case 'menu_order':
					$val = $this->post->$key;
					break;

				case 'title':
					$val = $raw ? $this->post->$post_key : apply_filters( 'the_title', $this->post->$post_key, $this->get( 'id' ) );
					break;

				default:
					$val = $this->post->$post_key;

			}

			// return the original global.
			$post = $temp;

		} elseif ( ! in_array( $key, $this->get_unsettable_properties() ) ) {

			if ( metadata_exists( 'post', $this->id, $this->meta_prefix . $key ) ) {
				$val = get_post_meta( $this->id, $this->meta_prefix . $key, true );
			} else {
				$val = $this->get_default_value( $key );
			}
		} else {

			return $this->$key;
		}// End if().

		// if we found a valid, apply default llms get get filter and return the value.
		if ( isset( $val ) ) {

			if ( ! $raw && 'content' !== $key ) {
				$val = $this->scrub( $key, $val );
			}

			return apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key, $val, $this );

		}

		// shouldn't ever get here.
		return false;

	}

	/**
	 * Getter
	 *
	 * @since  3.0.0
	 *
	 * @param string  $key The property key.
	 * @param boolean $raw Optional. Whether or not we need to get the raw value. Default false.
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
	 * Ensures that even empty values return an array
	 *
	 * @param  string $key  property key
	 * @return array
	 * @since  3.0.0 [<description>]
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
	 * If no format is supplied, the default format available via $this->get_date_format() will be used
	 *
	 * @param  string $key     property key
	 * @param  string $format  any valid date format that can be passed to date()
	 * @return string
	 * @since  3.0.0
	 */
	public function get_date( $key, $format = null ) {
		$format = ( ! $format ) ? $this->get_date_format() : $format;
		$raw    = $this->get( $key );
		// only convert the date if we actually have something stored, otherwise we'll return the current date, which we probably aren't expecting
		$date = $raw ? date_i18n( $format, strtotime( $raw ) ) : '';
		return apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key . '_date', $date, $this );
	}

	/**
	 * Retrieve the default date format for the post model
	 * This *can* be overridden by child classes if the post type requires a different default date format
	 * If no format is supplied by the child class, the default WP date & time formats available
	 * via General Settings will be combined and used
	 *
	 * @return string
	 * @since  3.0.0
	 */
	protected function get_date_format() {
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		return apply_filters( 'llms_get_' . $this->model_post_type . '_date_format', $format );
	}

	/**
	 * Get the default value of a property
	 * If defaults don't exist returns an empty string in accordance with the return of get_post_meta() when no metadata exists
	 *
	 * @param    string $key  property key/name
	 * @return   mixed
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_default_value( $key ) {
		$defaults = $this->get_property_defaults();
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	/**
	 * Retrieve URL for an image associated with the post
	 * Currently only retrieves the featured image if the post type supports it
	 * in the future this will allow retrieval of custom post images as well
	 *
	 * @param    string|array $size  registered image size or a numeric array with width/height
	 * @param    string       $key   currently unused but here for forward compatibility if
	 *                               additional custom images are added
	 * @return   string                empty string if no image or not supported
	 * @since    3.3.0
	 * @version  3.8.0
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
	 * @return WP_Post_Type|null
	 * @since  3.0.0
	 */
	public function get_post_type_data() {
		return get_post_type_object( $this->get( 'type' ) );
	}

	/**
	 * Retrieve a label from the post type data object's labels object
	 *
	 * @param    string $label key for the label
	 * @return   string
	 * @since    3.0.0
	 * @version  3.8.0
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
	 * @param    string $key         property key
	 * @param    array  $price_args  optional array of arguments that can be passed to llms_price()
	 * @param    string $format      optional format conversion method [html|raw|float]
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public function get_price( $key, $price_args = array(), $format = 'html' ) {

		$price = $this->get( $key );

		// handle empty or unset values gracefully
		if ( '' === $price ) {
			$price = 0;
		}

		if ( 'html' == $format || 'raw' === $format ) {
			$price = llms_price( $price, $price_args );
			if ( 'raw' === $format ) {
				$price = strip_tags( $price );
			}
		} elseif ( 'float' === $format ) {
			$price = floatval( number_format( $price, get_lifterlms_decimals(), '.', '' ) );
		} else {
			$price = apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key . '_' . $format, $price, $key, $price_args, $format, $this );
		}

		return apply_filters( 'llms_get_' . $this->model_post_type . '_' . $key . '_price', $price, $key, $price_args, $format, $this );

	}

	/**
	 * Retrieve the default values for properties
	 *
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_property_defaults() {
		return apply_filters( 'llms_get_' . $this->model_post_type . '_property_defaults', $this->property_defaults, $this );
	}

	/**
	 * An array of default arguments to pass to $this->create()
	 * when creating a new post
	 * This *should* be overridden by child classes
	 *
	 * @param    array $args   args of data to be passed to wp_insert_post
	 * @return   array
	 * @since    3.0.0
	 * @version  3.18.0
	 */
	protected function get_creation_args( $args = null ) {

		// allow nothing to be passed in
		if ( empty( $args ) ) {
			$args = array();
		}

		// backwards compat to original 3.0.0 format when just a title was passed in
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

		return apply_filters( 'llms_' . $this->model_post_type . '_get_creation_args', $args, $this );
	}

	/**
	 * Get media embeds
	 *
	 * @param    string $type  embed type [video|audio]
	 * @param    string $prop  postmeta property name, defaults to {$type}_embed
	 * @return   string
	 * @since    3.17.0
	 * @version  3.17.5
	 */
	protected function get_embed( $type = 'video', $prop = '' ) {

		$ret = '';

		$prop = $prop ? $prop : $type . '_embed';
		$url  = $this->get( $prop );
		if ( $url ) {

			$ret = wp_oembed_get( $url );

			if ( ! $ret ) {

				$ret = do_shortcode( sprintf( '[%1$s src="%2$s"]', $type, $url ) );

			}
		}

		return apply_filters( sprintf( 'llms_%1$s_get_%2$s', $this->model_post_type, $type ), $ret, $this, $type, $prop );

	}

	/**
	 * Get a property's data type for scrubbing
	 * used by $this->scrub() to determine how to scrub the property
	 *
	 * @param   string $key  property key
	 * @return  string
	 * @since   3.3.0
	 * @version 3.3.0
	 */
	protected function get_property_type( $key ) {

		$props = $this->get_properties();

		// check against the properties array
		if ( in_array( $key, array_keys( $props ) ) ) {
			$type = $props[ $key ];
		} else {
			$type = 'text';
		}

		return $type;

	}

	/**
	 * Retrieve an array of post properties
	 * These properties need to be get/set with alternate methods
	 *
	 * @since 3.0.0
	 * @since 3.31.0 Treat excerpts as HTML instead of plain text.
	 * @since 3.34.0 Add date and modified dates GMT version, comment and ping status, post password and menu_order.
	 *
	 * @return array
	 */
	protected function get_post_properties() {
		return apply_filters(
			'llms_post_model_get_post_properties',
			array(
				'author'         => 'absint',
				'content'        => 'html',
				'date'           => 'text',
				'date_gmt'       => 'text',
				'excerpt'        => 'html',
				'password'       => 'text',
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
	 * @return   array
	 * @since    3.3.0
	 * @version  3.16.0
	 */
	public function get_properties() {
		$props = array_merge( $this->get_post_properties(), $this->properties );
		return apply_filters( 'llms_get_' . $this->model_post_type . '_properties', $props, $this );
	}

	/**
	 * Retrieve the registered Label of the posts current status
	 *
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_status_name() {
		$obj = get_post_status_object( $this->get( 'status' ) );
		return apply_filters( 'llms_get_' . $this->model_post_type . '_status_name', $obj->label );
	}

	/**
	 * Get an array of terms for a given taxonomy for the post
	 *
	 * @param    string  $tax     taxonomy name
	 * @param    boolean $single  return only one term as an int, useful for taxes which
	 *                            can only have one term (eg: visibilities and difficulties and such)
	 * @return   mixed               when single a single term object or null
	 *                               when not single an array of term objects
	 * @since    3.8.0
	 * @version  3.8.0
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
	 * If a child class adds any properties which should not be settable
	 * the class should override this property and add their custom
	 * properties to the array
	 *
	 * @return array
	 * @since 3.0.0
	 */
	protected function get_unsettable_properties() {
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
	 * @return   boolean
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function is_cloneable() {
		return post_type_supports( $this->db_post_type, 'llms-clone-post' );
	}

	/**
	 * Determine if the associated post is exportable
	 *
	 * @return   boolean
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function is_exportable() {
		return post_type_supports( $this->db_post_type, 'llms-export-post' );
	}

	/**
	 * Format the object for json serialization
	 * encodes the results of $this->toArray()
	 *
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function jsonSerialize() {
		return apply_filters( 'llms_post_model_json_serialize', $this->toArray(), $this );
	}

	/**
	 * Scrub field according to it's type
	 * This is automatically called by set() method before anything is actually set
	 *
	 * @param    string $key  property key
	 * @param    mixed  $val  property value
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.16.0
	 */
	protected function scrub( $key, $val ) {

		$type = apply_filters( 'llms_get_' . $this->model_post_type . '_property_type', $this->get_property_type( $key ), $this );

		return apply_filters( 'llms_scrub_' . $this->model_post_type . '_field_' . $key, $this->scrub_field( $val, $type ), $this, $key, $val );

	}

	/**
	 * Scrub fields according to datatype
	 *
	 * @param    mixed  $val   property value to scrub
	 * @param    string $type  data type
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.19.2
	 */
	protected function scrub_field( $val, $type ) {

		if ( 'html' !== $type && 'array' !== $type ) {
			$val = strip_tags( $val );
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
				$val = wp_kses_post( $val );
				$this->allowed_post_tags_unset();
				break;

			case 'int':
				$val = intval( $val );
				break;

			case 'yesno':
				$val = 'yes' === $val ? 'yes' : 'no';
				break;

			case 'text':
			case 'string':
			default:
				$val = sanitize_text_field( $val );

		}// End switch().

		return $val;

	}

	/**
	 * Setter
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Use `wp_slash()` when setting properties.
	 * @since 3.34.0 Turned to be only a wrapper for the set_bulk() method.
	 *
	 * @param string|array $key_or_array Key of the property or a an associative array of key/val pairs.
	 * @param mixed        $val          Optional. Value to set the property with. Default empty string.
	 *                                   This parameter will be ignored when the first parameter is an associative array of key/val pairs.
	 * @return boolean true on success, false on error or if the submitted value is the same as what's in the database
	 */
	public function set( $key_or_array, $val = '' ) {

		$model_array = array();
		if ( ! is_array( $key_or_array ) ) {
			$model_array = array(
				$key_or_array => $val,
			);
		} else {
			$model_array = $key_or_array;
		}
		return $this->set_bulk( $model_array );

	}


	/**
	 * Bulk setter.
	 *
	 * @since 3.34.0
	 * @since 3.36.1 Use WP_Error::$errors in place of WP_Error::has_errors() to support WordPress version prior to 5.1.
	 *
	 * @param array $model_array Associative array of key/val pairs.
	 * @param array $wp_error    Optional. Whether or not return a WP_Error. Default false.
	 * @return boolean|WP_Error True on success. If the param $wp_error is set to false this will be false on error or if there was nothing to update.
	 *                          Otherwise this will be a WP_Error object collecting all the errors encountered along the way.
	 */
	public function set_bulk( $model_array, $wp_error = false ) {

		if ( empty( $model_array ) ) {
			if ( ! $wp_error ) {
				return false;
			} else {
				return new WP_Error( 'empty_data', __( 'Empty data', 'lifterlms' ) );
			}
		}

		$llms_post = array(
			'post' => array(),
			'meta' => array(),
		);

		$post_properties       = array_keys( $this->get_post_properties() );
		$unsettable_properties = $this->get_unsettable_properties();

		foreach ( $model_array as $key => $val ) {

			// sanitize the post properties keys by removing the 'post_' prefix.
			if ( 'post_' === substr( $key, 0, 5 ) ) {
				$_key = substr( $key, 5 );
				if ( in_array( $_key, $post_properties ) ) {
					$key = $_key;
				}
			}

			$val = $this->scrub( $key, $val );

			// update WordPress Post Properties using the wp_insert_post() function
			/**
			 * The 'edit_date' must be passed to the wp_update_post() function in order
			 * to allow 'drafty' posts' creation date to be modified.
			 */
			if ( in_array( $key, $post_properties ) || 'edit_date' === $key ) {

				$type          = 'post';
				$llms_post_key = "post_{$key}";

				switch ( $key ) {

					case 'content':
						$val = apply_filters( 'content_save_pre', $val );
						break;

					case 'excerpt':
						$val = apply_filters( 'excerpt_save_pre', $val );
						break;

					case 'edit_date':
					case 'ping_status':
					case 'comment_status':
					case 'menu_order':
						$llms_post_key = $key;
						break;

					case 'title':
						$val = apply_filters( 'title_save_pre', $val );
						break;
				}
			} elseif ( ! in_array( $key, $unsettable_properties ) ) {
				$type          = 'meta';
				$llms_post_key = $key;
			} else {
				continue;
			}

			$llms_post[ $type ][ $llms_post_key ] = apply_filters( 'llms_set_' . $this->model_post_type . '_' . $key, $val, $this );

		}// End foreach().

		if ( empty( $llms_post['post'] ) && empty( $llms_post['meta'] ) ) {
			if ( ! $wp_error ) {
				return false;
			} else {
				return new WP_Error( 'invalid_data', __( 'Invalid data', 'lifterlms' ) );
			}
		}

		$error = new WP_Error();

		if ( ! empty( $llms_post['post'] ) ) {

			$args = array_merge(
				$llms_post['post'],
				array(
					'ID' => $this->get( 'id' ),
				)
			);

			$update_post = wp_update_post( wp_slash( $args ), true );

			if ( ! is_wp_error( $update_post ) ) {
				// update this post.
				$this->post = get_post( $this->get( 'id' ) );
			} else {
				$error = $update_post;
			}
		}

		if ( ! empty( $llms_post['meta'] ) ) {
			foreach ( $llms_post['meta'] as $key => $val ) {
				$u = update_post_meta( $this->id, $this->meta_prefix . $key, $val );
				if ( ! ( is_numeric( $u ) || true === $u ) ) {
					$error->add( 'invalid_meta', sprintf( __( 'Cannot insert/update the %s meta', 'lifterlms' ), $key ) );
				}
			}
		}

		if ( ! empty( $error->errors ) ) {
			return $wp_error ? $error : false;
		}

		return true;
	}


	/**
	 * Update terms for the post for a given taxonomy
	 *
	 * @param    array   $terms   array of terms (name or ids)
	 * @param    string  $tax     the name of the tax
	 * @param    boolean $append  if true, will append the terms, false will replace existing terms
	 *
	 * @return bool
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_terms( $terms, $tax, $append = false ) {
		$set = wp_set_object_terms( $this->get( 'id' ), $terms, $tax, $append );
		// wp_set_object_terms has 3 options when unsuccessful and only 1 for success
		// an array of terms when successful, let's keep it simple...
		return is_array( $set );
	}

	/**
	 * Coverts the object to an associative array
	 * Any property returned by $this->get_properties() will be retrieved
	 * via $this->get() and added to the array
	 *
	 * Extending classes can add additional properties to the array
	 * by overriding $this->toArrayAfter()
	 *
	 * This function is also utilized to serialize the object to json
	 *
	 * @return   array
	 * @since    3.3.0
	 * @version  3.17.0
	 */
	public function toArray() {

		$arr = array(
			'id' => $this->get( 'id' ),
		);

		$props = array_diff( array_keys( $this->get_properties() ), array( 'content', 'excerpt', 'title' ) );
		$props = apply_filters( 'llms_get_' . $this->model_post_type . '_to_array_properties', $props, $this );

		foreach ( $props as $prop ) {
			$arr[ $prop ] = $this->get( $prop );
		}

		$arr['content'] = $this->post->post_content;
		$arr['excerpt'] = $this->post->post_excerpt;
		$arr['title']   = $this->post->post_title;

		// add the featured image if the post type supports it
		if ( post_type_supports( $this->db_post_type, 'thumbnail' ) ) {
			$arr['featured_image'] = $this->get_image( 'full', 'thumbnail' );
		}

		// expand instructors if instructors are supported
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

		// add custom fields
		$arr = $this->toArrayCustom( $arr );

		// allow extending classes to add properties easily without overriding the class
		$arr = $this->toArrayAfter( $arr );

		$cpt_data = $this->get_post_type_data();
		if ( $cpt_data->public ) {
			$arr['permalink'] = get_permalink( $this->get( 'id' ) );
		}

		ksort( $arr ); // because i'm anal...

		return apply_filters( 'llms_' . $this->model_post_type . '_to_array', $arr, $this );

	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json
	 *
	 * @param    array $arr   array of data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	protected function toArrayAfter( $arr ) {
		return $arr;
	}

	/**
	 * Called by toArray to add custom fields via get_post_meta()
	 * Removes all custom props registered to the $this->properties automatically
	 * Also removes some fields used by the WordPress core that don't hold necessary data
	 * Extending classes may override this class to exclude, extend, or modify the custom fields for a post type
	 *
	 * @since 3.16.11
	 * @since 3.30.0 Use `maybe_unserialize()` to ensure array data is accessible as an array.
	 * @since 3.30.2 Add filter to allow 3rd parties to prevent a field from being added to the custom field array.
	 *
	 * @param    array $arr  existing post array
	 * @return   array
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
			if ( in_array( $key, $props, true ) || apply_filters( 'llms_' . $this->model_post_type . '_skip_custom_field', false, $key, $this ) ) {
				continue;
			}

			// add it.
			$custom[ $key ] = array_map( 'maybe_unserialize', $vals );

		}

		// add the compiled custom array.
		$arr['custom'] = $custom;

		return $arr;
	}

}
