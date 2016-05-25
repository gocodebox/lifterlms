<?php

/**
* Person base class.
*
* Class used for instantiating course object
*/

class LLMS_Person {

	/**
	* person data array
	* @access private
	* @var array
	*/
	protected $_data;

	/**
	* Has data been changed?
	* @access private
	* @var bool
	*/
	private $_changed = false;

	/**
	 * Constructor
	 *
	 * Initializes person data
	 */
	public function __construct() {

		if ( empty( LLMS()->session->person ) ) {

			$this->_data = LLMS()->session->person;
		}

		// When leaving or ending page load, store data
		add_action( 'shutdown', array( $this, 'save_data' ), 10 );
		add_action( 'wp_login', array( $this, 'set_user_login_timestamp' ), 10, 2 );
		add_action( 'user_register', array( $this, 'set_user_login_timestamp_on_register' ), 10, 2 );
	}

	/**
	 * save_data function.
	 *
	 * @return void
	 */
	public function save_data() {
		if ( $this->_changed ) {
			$GLOBALS['lifterlms']->session->person = $this->_data;
		}
	}

	/**
	 * Set user login timestamp on login
	 * Update login timestamp on user login
	 *
	 * @param string $user_login [User login id]
	 * @param object $user       [User data object]
	 */
	public function set_user_login_timestamp( $user_login, $user ) {
		$now = current_time( 'timestamp' );
		update_user_meta( $user->ID, 'llms_last_login', $now );
	}

	/**
	 * Set user login timestamp on registration
	 * Update login timestamp on user registration
	 *
	 * @param int $user_id
	 */
	public function set_user_login_timestamp_on_register( $user_id ) {
		$now = current_time( 'timestamp' );
		update_user_meta( $user_id, 'llms_last_login', $now );
	}


	/**
	 * Get user postmeta achievements
	 * @param  int    $user_id    user id
	 * @return array              associative array of users achievement data
	 */
	public function get_user_achievements( $count = 1000, $user_id = 0 ) {
		global $wpdb;

		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;

		$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'lifterlms_user_postmeta WHERE user_id = %s and meta_key = "%s" ORDER BY updated_date DESC LIMIT %d', $user_id, '_achievement_earned', $count ) );

		$achievements = array();

		foreach ( $results as $key => $val ) {

			$achievement = array();

			$meta = get_post_meta( $val->meta_value );
			$post = get_post( $val->meta_value );

			$achievement['title'] = $meta['_llms_achievement_title'][0];
			$achievement['content'] = $post->post_content;

			$image_id = $meta['_llms_achievement_image'][0];

			$achievement['image'] = wp_get_attachment_image_src( $image_id, 'achievement' );

			if ( ! $achievement['image'] ) {
				$achievement['image'] = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' );
			} else {
				$achievement['image'] = $achievement['image'][0];
			}

			$achievement['date'] = date( get_option( 'date_format' ), strtotime( $val->updated_date ) );

			$achievements[] = $achievement;

		}

		return apply_filters( 'lifterlms_user_achievements', $achievements );

	}


	/**
	 * Get data about a specific users memberships
	 *
	 * @param  int $user_id user id
	 *
	 * @return array / array of objects containing details about users memberships
	 */
	public function get_user_memberships_data( $user_id ) {

		$memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

		$r = array();

		if ($memberships) {

			foreach ($memberships as $membership_id) {

				$info = $this->get_user_postmeta_data( $user_id, $membership_id );

				if ( $info ) {

					$r[ $membership_id ] = $info;

				}
			}
		}

		return $r;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmeta_data( $user_id, $post_id ) {
		global $wpdb;

		if ( empty( $user_id ) || empty( $post_id ) ) {
			return;
		}

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM '.$table_name.' WHERE user_id = %s and post_id = %d', $user_id, $post_id) );

		if ( empty( $results ) ) {
			return;
		}

		for ($i = 0; $i < count( $results ); $i++) {
			$results[ $results[ $i ]->meta_key ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		return $results;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmetas_by_key( $user_id, $meta_key ) {
		global $wpdb;

		if ( empty( $user_id ) || empty( $meta_key ) ) {
			return;
		}

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM '.$table_name.' WHERE user_id = %s and meta_key = "%s" ORDER BY updated_date DESC', $user_id, $meta_key ) );

		if ( empty( $results ) ) {
			return;
		}

		for ($i = 0; $i < count( $results ); $i++) {
			$results[ $results[ $i ]->post_id ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		return $results;
	}

	/**
	 * Register new user and return his id
	 *
	 * @return int | Error
	 */
	public static function create_new_person() {

		if ('no' === get_option( 'lifterlms_registration_generate_username' )) {

			$_username = $_POST['username'];

		} else {

			$_username = '';
		}

		if ('yes' === get_option( 'lifterlms_registration_require_name' )) {
			$_firstname = $_POST['firstname'];
			$_lastname = $_POST['lastname'];

		} else {
			$_firstname = '';
			$_lastname = '';
		}

		if ('yes' === get_option( 'lifterlms_registration_require_address' )) {
			$_billing_address_1 = $_POST['billing_address_1'];
			$_billing_address_2 = $_POST['billing_address_2'];
			$_billing_city = $_POST['billing_city'];
			$_billing_state = $_POST['billing_state'];
			$_billing_zip = $_POST['billing_zip'];
			$_billing_country = $_POST['billing_country'];

		} else {
			$_billing_address_1 = '';
			$_billing_address_2 = '';
			$_billing_city = '';
			$_billing_state = '';
			$_billing_zip = '';
			$_billing_country = '';
		}

		if ('yes' == get_option( 'lifterlms_registration_add_phone' )) {
			$_phone = $_POST['phone'];
		}

		$_password = $_POST['password'];
		$_password2 = $_POST['password_2'];

		$_agree_to_terms = ( ! empty( $_POST['agree_to_terms'] )) ? true : false;

		try {

			$validation_error = new WP_Error();
			$validation_error = apply_filters('lifterlms_user_registration_errors',
				$validation_error,
				$_username,
				$_firstname,
				$_lastname,
				$_password,
				$_password2,
				$_POST['email'],
				$_billing_address_1,
				$_billing_city,
				$_billing_state,
				$_billing_zip,
				$_billing_country,
				$_agree_to_terms
			);

			if ($validation_error->get_error_code()) {

				throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . $validation_error->get_error_message() );

			}

		} catch (Exception $e) {

			llms_add_notice( $e->getMessage(), 'error' );
			return;

		}

		$username = ! empty( $_username ) ? llms_clean( $_username ) : '';
		$firstname = ! empty( $_firstname ) ? llms_clean( $_firstname ) : '';
		$lastname = ! empty( $_lastname ) ? llms_clean( $_lastname ) : '';
		$email = ! empty( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		if ('yes' === get_option( 'lifterlms_registration_confirm_email' )) {
			$email2 = ! empty( $_POST['email_confirm'] ) ? sanitize_email( $_POST['email_confirm'] ) : '';
		} else {
			$email2 = $email;
		}
		$password = $_password;
		$password2 = $_password2;

		$billing_address_1 = ! empty( $_billing_address_1 ) ? llms_clean( $_billing_address_1 ) : '';
		$billing_address_2 = ! empty( $_billing_address_2 ) ? llms_clean( $_billing_address_2 ) : '';
		$billing_city = ! empty( $_billing_city ) ? llms_clean( $_billing_city ) : '';
		$billing_state = ! empty( $_billing_state ) ? llms_clean( $_billing_state ) : '';
		$billing_zip = ! empty( $_billing_zip ) ? llms_clean( $_billing_zip ) : '';
		$billing_country = ! empty( $_billing_country ) ? llms_clean( $_billing_country ) : '';
		$phone = ! empty( $_phone ) ? llms_clean( $_phone ) : '';
		$agree_to_terms = $_agree_to_terms;

		// Anti-spam trap
		if ( ! empty( $_POST['email_2'] )) {

			llms_add_notice( '<strong>' . __( 'ERROR', 'lifterlms' ) . '</strong>: ' . __( 'Anti-spam field was filled in.', 'lifterlms' ), 'error' );
			return;

		}

		$new_person = llms_create_new_person(
			$email,
			$email2,
			$username,
			$firstname,
			$lastname,
			$password,
			$password2,
			$billing_address_1,
			$billing_address_2,
			$billing_city,
			$billing_state,
			$billing_zip,
			$billing_country,
			$agree_to_terms,
			$phone
		);

		do_action( 'lifterlms_user_registered', $new_person );

		return $new_person;
	}

	/**
	 * Login user and return user data
	 *
	 * @return object | Error
	 */
	public static function login_user() {
		$creds = array();

		$validation_error = new WP_Error();

		$validation_error = apply_filters( 'lifterlms_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

		if ($validation_error->get_error_code()) {

			throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . $validation_error->get_error_message() );

		}

		if (empty( $_POST['username'] )) {

			throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'Username is required.', 'lifterlms' ) );

		}

		if (empty( $_POST['password'] )) {

			throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'Password is required.', 'lifterlms' ) );

		}

		if (is_email( $_POST['username'] ) && apply_filters( 'lifterlms_get_username_from_email', true )) {

			$user = get_user_by( 'email', $_POST['username'] );

			if (isset( $user->user_login )) {

				$creds['user_login'] = $user->user_login;

			} else {

				throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'lifterlms' ) );

			}

		} else {

			$creds['user_login'] = $_POST['username'];

		}

		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = isset( $_POST['rememberme'] );
		$secure_cookie = is_ssl() ? true : false;

		return wp_signon( apply_filters( 'lifterlms_login_credentials', $creds ), $secure_cookie );
	}

}
