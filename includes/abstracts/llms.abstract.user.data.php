<?php
/**
 * LifterLMS User Data Abstract
 *
 * @since   3.9.0
 * @version 3.17.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS User Data Abstract
 *
 * @since 3.9.0
 * @since 3.34.0 Allow `user_url` to be retrieved by `get()`.
 */
abstract class LLMS_Abstract_User_Data {

	/**
	 * Student's WordPress User ID
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * User postmeta key prefix
	 *
	 * @var  string
	 */
	protected $meta_prefix = 'llms_';

	/**
	 * Instance of the WP_User
	 *
	 * @var obj
	 */
	protected $user;

	/**
	 * Constructor
	 *
	 * If no user id provided, will attempt to use the current user id
	 *
	 * @param    mixed $user   WP_User ID, instance of WP_User, or instance of any student class extending this class
	 * @return   void
	 * @since    2.2.3
	 * @version  3.9.0
	 */
	public function __construct( $user = null ) {

		$user = $this->get_user_id( $user );
		if ( false !== $user ) {
			$this->id   = $user;
			$this->user = get_user_by( 'ID', $user );
		}

	}

	/**
	 * Magic Getter for User Data
	 *
	 * Mapped directly to the WP_User class.
	 *
	 * @since 3.0.0
	 * @since 3.10.1 Unknown.
	 * @since 3.34.0 Allow `user_url` to be retrieved.
	 *
	 * @param    string $key key of the property to get a value for
	 * @return   mixed
	 */
	public function __get( $key ) {

		// array of items we should *not* add the $this->meta_prefix to
		$unprefixed = apply_filters(
			'llms_student_unprefixed_metas',
			array(
				'description',
				'display_name',
				'first_name',
				'last_name',
				'nickname',
				'user_login',
				'user_nicename',
				'user_email',
				'user_registered',
				'user_url',
			),
			$this
		);

		// add the meta prefix to things that aren't in the above array
		// only if the meta prefix isn't already there
		// this means that the following will output the same data
		// $this->get( 'llms_billing_address_1')
		// $this->get( 'billing_address_1')
		if ( false === strpos( $key, $this->meta_prefix ) && ! in_array( $key, $unprefixed ) ) {
			$key = $this->meta_prefix . $key;
		}

		if ( ! $this->exists() ) {
			return '';
		}

		return apply_filters( 'llms_get_student_meta_' . $key, $this->user->get( $key ), $this );

	}

	/**
	 * Retrieve an item from the cache
	 *
	 * @param    string $key   cache key
	 * @return   false|mixed       false on failure
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	protected function cache_get( $key ) {
		return wp_cache_get( $key, $this->get_cache_group() );
	}

	/**
	 * Delete an item from the cache
	 *
	 * @param    string $key  cache key
	 * @return   bool
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	protected function cache_delete( $key ) {
		return wp_cache_delete( $key, $this->get_cache_group() );
	}

	/**
	 * Add an item to the cache cache
	 *
	 * @param    string $key  cache key
	 * @param    mixed  $val  value to cache
	 * @return   boolean
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	protected function cache_set( $key, $val ) {
		return wp_cache_set( $key, $val, $this->get_cache_group() );
	}

	/**
	 * Determine if the user exists
	 *
	 * @return   boolean
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function exists() {
		return ( $this->user && $this->user->exists() );
	}

	/**
	 * Allows direct access to WP_User object for retrieving user data from the user or usermeta tables
	 *
	 * @since   3.0.0
	 * @version 3.0.0
	 * @param   string $key key of the property to get a value for
	 * @return  mixed
	 */
	public function get( $key ) {
		return $this->$key;
	}

	/**
	 * Retrieve the group name used by cache functions
	 *
	 * @return   string
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	protected function get_cache_group() {
		return sprintf( 'llms_user_%d', $this->get( 'id' ) );
	}

	/**
	 * Retrieve the user id
	 *
	 * @return   int
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Allow extending classes to access the main student class
	 *
	 * @return   LLMS_Student|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	protected function get_student() {
		return llms_get_student( $this->get_id() );
	}

	/**
	 * Retrieve the instance of the WP User for the student
	 *
	 * @return   WP_User
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * Retrieve the User ID based on object
	 *
	 * @param    mixed $user  WP_User ID, instance of WP_User, or instance of any student class extending this class
	 * @return   mixed            int if a user id can be found, otherwise false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	protected function get_user_id( $user ) {

		if ( ! $user && get_current_user_id() ) {
			return get_current_user_id();
		} elseif ( is_numeric( $user ) ) {
			return $user;
		} elseif ( is_a( $user, 'WP_User' ) && isset( $user->ID ) ) {
			return $user->ID;
		} elseif ( $user instanceof LLMS_Abstract_User_Data ) {
			return $user->get_id();
		}

		return false;

	}

	/**
	 * Update a meta property for the user
	 *
	 * @param    string  $key     meta key
	 * @param    mixed   $value   meta value
	 * @param    boolean $prefix  include the meta prefix when setting
	 *                            passing false will allow 3rd parties to update fields with a custom prefix
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function set( $key, $value, $prefix = true ) {
		$key = $prefix ? $this->meta_prefix . $key : $key;
		update_user_meta( $this->get_id(), $key, $value );
	}

}
