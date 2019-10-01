<?php
/**
 * Register and manage LifterLMS user forms.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Forms class..
 *
 * @since [version]
 */
class LLMS_Forms {

	/**
	 * Instance of LLMS_Fields.
	 *
	 * @var null
	 */
	protected $fields = null;

	/**
	 * Array of registered forms.
	 *
	 * @var array
	 */
	protected $forms = array();

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $instance = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Forms
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		$this->fields = LLMS_Fields::instance();

		$this->register( 'checkout', array(
			'sanitize_callback' => array( $this, 'sanitize_form' ),
			'validate_callback' => array( $this, 'validate_form' ),
			'nonce' => array(
				'action' => 'create_pending_order',
				'name' => '_llms_checkout_nonce',
			),
			'fields' => $this->get_account_fields( 'checkout' ),
		) );

		if ( llms_parse_bool( get_option( 'lifterlms_enable_myaccount_registration', 'no' ) ) ) {
			$this->register( 'registration', $this->get_account_fields( 'registration' ) );
		}

	}

	public function register( $form_id, $settings = array() ) {
		$this->forms[ $form_id ] = array(
			'submit_handler' => array( $this, '')
			'fields' => $fields,
		);
	}

	public function add_field( $form_id, $field_id, $field_settings = array() ) {
		$this->forms[ $form_id ][ $field_id ] = array_merge(  );
	}

	public function setup_field( $id, $settings = array() ) {

		$defaults = array(
			'columns' => 12,
			'last_column' => true,
			'order' => 1,
			'required' => false,
		);

		$field = array_merge( $this->fields->get( $field_id ), wp_parse_args( $field_settings, $defaults ) );
		return $field;

	}

	protected function get_account_fields( $screen ) {

		$order  = 1;
		$fields = array();

		if ( llms_parse_bool( get_option( 'lifterlms_registration_generate_username', 'no' ) ) ) {
			$fields[] = $this->setup_field( 'user_login', array(
				'required' => true,
				'order' => $order++,
			) );
		}

		$email_con = llms_parse_bool( get_option( 'lifterlms_user_info_field_email_confirmation' . $screen .  'visibility', 'yes' ) );
		$fields[] = $this->setup_field( 'email_address', array(
			'columns'     => $email_con ? 6 : 12,
			'last_column' => $email_con ? false : true,
			'match'       => $email_con ? 'email_address_confirm' : false,
			'required'    => true,
			'order'       => $order++,
		);

		if ( $email_con ) {
			$fields[]  = $this->setup_field( 'email_address_confirm', array(
				'columns'     => 6,
				'match'       => 'email_address',
				'required'    => true,
				'order'       => $order++,
			);
		}

		$names = get_option( 'lifterlms_user_info_field_names' . $screen .  'visibility', 'required' );
		if ( 'required' === $names || 'optional' === $names ) {
			$fields[] = $this->setup_field( 'llms_first_name', array(
				'columns' => 6,
				'last_column' => false,
				'required' => ( 'required' === $names ),
				'order' => $order++,
			) );
			$fields[] = $this->setup_field( 'llms_last_name', array(
				'columns' => 6,
				'last_column' => false,
				'required' => ( 'required' === $names ),
				'order' => $order++,
			) );
		}

		$address = get_option( 'lifterlms_user_info_field_address' . $screen .  'visibility' );
		if ( 'required' === $address || 'optional' === $address ) {

			$required = ( 'required' === $address );

			$fields[] = $this->setup_field( 'llms_billing_address_1', array(
				'columns'     => 8,
				'last_column' => false,
				'required'    => $required,
				'order'       => $order++,
			);

			$fields[] = $this->setup_field( 'llms_billing_address_2', array(
				'columns'     => 4,
				'order'       => $order++,
			);

			$fields[] = $this->setup_field( 'llms_billing_city', array(
				'columns'     => 6,
				'last_column' => false,
				'required'    => $required,
				'order'       => $order++,
			);

			$fields[] = $this->setup_field( 'llms_billing_state', array(
				'columns'     => 3,
				'last_column' => false,
				'required'    => $required,
				'order'       => $order++,
			);

			$fields[] = $this->setup_field( 'llms_billing_zip', array(
				'columns'     => 3,
				'required'    => $required,
				'order'       => $order++,
			);

			$fields[] = $this->setup_field( 'llms_billing_country', array(
				'columns'     => 12,
				'required'    => $required,
				'order'       => $order++,
			);

		}

		$phone = get_option( 'lifterlms_user_info_field_phone' . $screen .  'visibility' );
		if ( 'required' === $phone || 'optional' === $phone ) {
			$fields[] = array(
				'required'    => ( 'required' === $phone ),
				'order'       => $order++,
			);
		}

		return $fields;

	}

	public function get_forms() {
		return apply_filters( 'llms_get_registered_forms', $this->forms );
	}

}
