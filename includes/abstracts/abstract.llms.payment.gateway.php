<?php
/**
 * LifterLMS Payment Gateways Abstract
 * @since    3.0.0
 * @version  3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Payment_Gateway {

	/**
	 * Optional gateway description for the admin panel
	 * @var string
	 * @since  3.0.0
	 */
	public $admin_description = '';

	/**
	 * Fields the gateway uses on the admin panel when displaying/editing an order
	 * @var    array
	 * @since  3.10.0
	 */
	public $admin_order_fields = array(
		'customer' => false,
		'subscription' => false,
		'source' => false,
	);

	/**
	 * Optional gateway title for the admin panel
	 * @var string
	 * @since  3.0.0
	 */
	public $admin_title = '';

	/**
	 * Optional gateway description for the frontend
	 * Can be modified by User on Settings Page
	 * @var string
	 * @since  3.0.0
	 */
	public $description = '';

	/**
	 * Order to display the gateway in on the frontend
	 * Can be modified by User on Settings Page
	 * @var integer
	 */
	public $display_order = 1;

	/**
	 * Is the gateway enabled for payment processing?
	 * Can be modified by User on Settings Page
	 * @var string
	 * @since  3.0.0
	 */
	public $enabled = 'no';

	/**
	 * Optional icon displayed on the frontend
	 * @var string
	 * @since  3.0.0
	 */
	public $icon = '';

	/**
	 * ID of the Payment Gateway, used internally
	 * @var string
	 * @since  3.0.0
	 */
	public $id;

	/**
	 * Logging status
	 * @var      string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public $logging_enabled = '';

	/**
	 * Array of supported gateway features
	 * @var      array
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public $supports = array(
		'checkout_fields' => false,
		'cc_save' => false,
		'refunds' => false,
		'single_payments' => false,
		'recurring_payments' => false,
		'recurring_retry' => false,
		'test_mode' => false,
	);

	/**
	 * Description of the gateway's test mode (if supported)
	 * @var string
	 * @since  3.0.0
	 */
	public $test_mode_description = '';

	/**
	 * Is test mode enabled?
	 * Can be modified by user on settings page if gateway supports "test_mode"
	 * @var string
	 * @since  3.0.0
	 */
	public $test_mode_enabled = 'no';

	/**
	 * Title of the gateway's test mode (if supported)
	 * @var string
	 * @since  3.0.0
	 */
	public $test_mode_title = '';

	/**
	 * Gateway title for the frontend
	 * Can be modified by User on Settings Page
	 * @var string
	 * @since  3.0.0
	 */
	public $title = '';

	/**
	 * This should be called by the gateway after verifying the transaction was completed successfully
	 *
	 * @param    obj        $order       Instance of an LLMS_Order object
	 * @param    string     $deprecated  (deprecated) optional message to display on the redirect screen
	 * @return   void
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public function complete_transaction( $order, $deprecated = '' ) {

		$this->log( $this->get_admin_title() . ' `complete_transaction()` started', $order );

		// redirect to the product's permalink
		$redirect = get_permalink( $order->get( 'product_id' ) );

		// fallback to the account page if we don't have a url for some reason
		if ( ! $redirect ) {
			$redirect = get_permalink( llms_get_page_id( 'myaccount' ) );
		}

		$redirect = add_query_arg( array(
			'order-complete' => $order->get( 'order_key' ),
		), $redirect );

		$redirect = apply_filters( 'lifterlms_completed_transaction_redirect', $redirect, $order );

		// deprecated msg if supplied, will be removed in a future release
		if ( $deprecated ) {

			llms_deprecated_function( 'LLMS_Payment_Gateway::complete_transaction() with message', '3.8.0', 'LifterLMS enrollment notices' );
			$deprecated = apply_filters( 'lifterlms_completed_transaction_message', $deprecated, $order );
			llms_add_notice( $deprecated, 'success' );

		}

		$this->log( $this->get_admin_title() . ' `complete_transaction()` finished', $redirect, $order );

		// ensure notification processors get dispatched since shutdown wont be called
		do_action( 'llms_dispatch_notification_processors' );

		// execute a redirect
		wp_redirect( $redirect );
		exit();

	}

	/**
	 * Confirm a Payment
	 * Called by LLMS_Controller_Orders->confirm_pending_order() on confirm form submission
	 * Some validation is performed before passing to this function, as it's not required
	 * gateways will likely doing further validations as are needed
	 *
	 * Not required if a confirmation isn't required by the Gateway
	 * Stripe doesn't require this whereas PayPal does
	 * @param   obj       $order   Instance LLMS_Order for the order being processed
	 * @return  void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function confirm_pending_order( $order ) {}

	/**
	 * Get admin desrcription for the gateway
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_admin_description() {
		return apply_filters( 'llms_get_gateway_admin_description', $this->admin_description, $this->id );
	}

	/**
	 * Get the admin title for the gateway
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_admin_title() {
		return apply_filters( 'llms_get_gateway_admin_title', $this->admin_title, $this->id );
	}

	/**
	 * Get data about the fields displayed on the admin panel when viewing an order
	 * processed via this gateway
	 * @return   array
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function get_admin_order_fields() {
		$fields = array(
			'customer' => array(
				'label' => __( 'Customer ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['customer'],
				'name' => 'gateway_customer_id',
			),
			'source' => array(
				'label' => __( 'Source ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['source'],
				'name' => 'gateway_source_id',
			),
			'subscription' => array(
				'label' => __( 'Subscription ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['subscription'],
				'name' => 'gateway_subscription_id',
			),
		);
		return apply_filters( 'llms_get_gateway_admin_order_fields', $fields, $this->id );
	}

	/**
	 * Get default dageway admin settinds fields
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_admin_settings_fields() {

		$fields = array();

		$fields[] = array(
			'type'  => 'custom-html',
			'value' => '
				<h1>' . $this->get_admin_title() . '</h1>
				<p>' . $this->get_admin_description() . '</p>
			',
		);

		$fields[] = array(
			'autoload'      => true,
			'id'            => $this->get_option_name( 'enabled' ),
			'desc'          => sprintf( _x( 'Enable %s', 'Payment gateway title' ,'lifterlms' ), $this->get_admin_title() ),
			'desc_tooltip'  => __( 'Checking this box will allow users to use this payment gateway.', 'lifterlms' ),
			'default'       => $this->get_enabled(),
			'title'         => __( 'Enable / Disable', 'lifterlms' ),
			'type'          => 'checkbox',
		);

		$fields[] = array(
			'id' 		=> $this->get_option_name( 'title' ),
			'desc' 		=> '<br>' . __( 'The title the user sees during checkout.', 'lifterlms' ),
			'default'	=> $this->get_title(),
			'title'     => __( 'Title', 'lifterlms' ),
			'type' 		=> 'text',
		);

		$fields[] = array(
			'id' 		=> $this->get_option_name( 'description' ),
			'desc' 		=> '<br>' . __( 'The description the user sees during checkout.', 'lifterlms' ),
			'default'	=> $this->get_description(),
			'title'     => __( 'Description', 'lifterlms' ),
			'type' 		=> 'text',
		);

		$fields[] = array(
			'id' 		=> $this->get_option_name( 'display_order' ),
			'desc' 		=> '<br>' . __( 'This determines the order gateways are displayed on the checkout page. Lowest number will display first.', 'lifterlms' ),
			'default'	=> $this->get_display_order(),
			'title'     => __( 'Display Order', 'lifterlms' ),
			'type' 		=> 'number',
		);

		if ( $this->supports( 'test_mode' ) ) {

			$fields[] = array(
				'id'            => $this->get_option_name( 'test_mode_enabled' ),
				'desc'          => sprintf( _x( 'Enable %s', 'Payment gateway test mode title' ,'lifterlms' ), $this->get_test_mode_title() ),
				'desc_tooltip'  => $this->get_test_mode_description(),
				'default'       => $this->get_test_mode_enabled(),
				'title'         => $this->get_test_mode_title(),
				'type'          => 'checkbox',
			);

		}

		$fields[] = array(
			'id'            => $this->get_option_name( 'logging_enabled' ),
			'desc'          => __( 'Enable debug logging', 'lifterlms' ),
			'desc_tooltip'  => sprintf( __( 'When enabled, debugging information will be logged to "%s"', 'lifterlms' ), llms_get_log_path( $this->get_id() ) ),
			'title'         => __( 'Debug Log' ,'lifterlms' ),
			'type'          => 'checkbox',
		);

		// gateways should use this filter to add gateway specific settings fields to the admin panel
		return apply_filters( 'llms_get_gateway_settings_fields', $fields, $this->id );

	}

	/**
	 * Get API mode
	 * if test is not surropted will return "live"
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_api_mode() {
		if ( $this->supports( 'test_mode' ) && $this->is_test_mode_enabled() ) {
			return 'test';
		}
		return 'live';
	}

	/**
	 * Gateways can override this to return a URL to a customer permalink on the gateway's website
	 * If this is not defined, it will just return the supplied ID
	 * @param    string     $customer_id  Gateway's customer ID
	 * @param    string     $api_mode     Link to either the live or test site for the gateway, where applicabale
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_customer_url( $customer_id, $api_mode = 'live' ) {
		return $customer_id;
	}

	/**
	 * Get the frontend desrcription setting
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_description() {
		return $this->get_option( 'description' );
	}

	/**
	 * Get the display order setting
	 * @return   int
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_display_order() {
		return absint( $this->get_option( 'display_order' ) );
	}

	/**
	 * Get the value of the enabled setting
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_enabled() {
		return $this->get_option( 'enabled' );
	}

	/**
	 * Get fields displayed on the checkout form
	 * Gateways rhould define this function if the gateway supports fields
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {
		return apply_filters( 'llms_get_gateway_fields', '', $this->id );
	}

	/**
	 * Get the icon displayed on the checkout form
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_icon() {
		return apply_filters( 'llms_get_gateway_icon', $this->icon, $this->id );
	}

	/**
	 * Get the gateway's ID
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve an HTML link to a customer, subscription, or source URL
	 * If no URL provided returns the item value as string
	 * @param    string     $item_key    the key of the item to retrieve a URL for
	 * @param    string     $item_value  the value of the item to retrieve
	 * @param    string     $api_mode    the current api mode to retrieve the URL for
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function get_item_link( $item_key, $item_value, $api_mode = 'live' ) {

		switch ( $item_key ) {

			case 'customer':
				$url = $this->get_customer_url( $item_value, $api_mode );
			break;

			case 'subscription':
				$url = $this->get_subscription_url( $item_value, $api_mode );
			break;

			case 'source':
				$url = $this->get_source_url( $item_value, $api_mode );
			break;

			default :
				$url = $item_value;

		}

	 	if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
	 		return $item_value;
	 	} else {
	 		return sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $url, $item_value );
	 	}

	}

	/**
	 * Get the value of the logging setting
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_logging_enabled() {
		return $this->get_option( 'logging_enabled' );
	}

	/**
	 * Gateways can override this to return a URL to a source permalink on the gateway's website
	 * If this is not defined, it will just return the supplied ID
	 * @param    string     $source_id   Gateway's source ID
	 * @param    string     $api_mode    Link to either the live or test site for the gateway, where applicabale
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_source_url( $source_id, $api_mode = 'live' ) {
		return $source_id;
	}

	/**
	 * Gateways can override this to return a URL to a subscription permalink on the gateway's website
	 * If this is not defined, it will just return the supplied ID
	 * @param    string     $subscription_id  Gateway's subscription ID
	 * @param    string     $api_mode         Link to either the live or test site for the gateway, where applicabale
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_subscription_url( $subscription_id, $api_mode = 'live' ) {
		return $subscription_id;
	}

	/**
	 * Get an array of features the gateway supports
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_supported_features() {
		return apply_filters( 'llms_get_gateway_supported_features', $this->supports, $this->id );
	}

	/**
	 * Get the desrcription of test mode displayed on the admin panel
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_test_mode_description() {
		return $this->test_mode_description;
	}

	/**
	 * Get value of the test mode enabled setting
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_test_mode_enabled() {
		return $this->get_option( 'test_mode_enabled' );
	}

	/**
	 * Get the title of test mode displayed on the admin panel
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_test_mode_title() {
		return $this->test_mode_title;
	}

	/**
	 * Get gateway title setting
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_title() {
		return $this->get_option( 'title' );
	}

	/**
	 * Gateways can override this to return a URL to a transaction permalink on the gateway's website
	 * If this is not defined, it will just return the supplied ID
	 * @param    string     $transaction_id  Gateway's transaction ID
	 * @param    string     $api_mode        Link to either the live or test site for the gateway, where applicabale
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_transaction_url( $transaction_id, $api_mode = 'live' ) {
		return $transaction_id;
	}

	/**
	 * Called when the Update Payment Method form is submitted from a single order view on the student dashboard
	 *
	 * Gateways should do whatever the gateway needs to do to validate the new payment method and save it to the order
	 * so that future payments on the order will use this new source
	 *
	 * This should be an abstract function but experience has taught me that no one will upgrade follow our instructions
	 * and they'll end up with 500 errors and debug mode disabled and send me giant frustrated question marks
	 *
	 * @param    obj     $order      Instance of the LLMS_Order
	 * @param    array   $form_data  Additional data passed from the submitted form (EG $_POST)
	 * @return   void
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function handle_payment_source_switch( $order, $form_data = array() ) {
		return llms_add_notice( sprintf( esc_html__( 'The selected payment Gateway "%s" does not support payment method switching.', 'lifterlms' ), $this->get_title() ), 'error' );
	}

	/**
	 * Handle a Pending Order
	 * Called by LLMS_Controller_Orders->create_pending_order() on checkout form submission
	 * All data will be validated before it's passed to this function
	 *
	 * @param   obj       $order   Instance LLMS_Order for the order being processed
	 * @param   obj       $plan    Instance LLMS_Access_Plan for the order being processed
	 * @param   obj       $person  Instance of LLMS_Student for the purchasing customer
	 * @param   obj|false $coupon  Instance of LLMS_Coupon applied to the order being processed, or false when none is being used
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	abstract public function handle_pending_order( $order, $plan, $person, $coupon = false );

	/**
	 * Called by scheduled actions to charge an order for a scheduled recurring transaction
	 * This function must be defined by gateways which support recurring transactions
	 * @param    obj       $order   Instance LLMS_Order for the order being processed
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function handle_recurring_transaction( $order ) {}

	/**
	 * Determine if the gateway is the default gateway
	 * This will be the FIRST gateway in the gateways that are enabled
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_default_gateway() {
		return ( $this->get_id() === LLMS()->payment_gateways()->get_default_gateway() );
	}

	/**
	 * Determine if the gateway is enabled according to admin settings checkbox
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_enabled() {
		return ( 'yes' === $this->get_enabled() ) ? true : false;
	}

	/**
	 * Determine if test mode is enabled
	 * Returns false if gateway doesn't support test mode
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_test_mode_enabled() {
		return ( 'yes' === $this->get_test_mode_enabled() ) ? true : false;
	}

	/**
	 * Log messages if logging is enabled
	 * @param    mixed     $data  data to log (accepts any number of $data to be logged)
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function log() {
		if ( 'yes' === $this->get_logging_enabled() ) {
			foreach ( func_get_args() as $data ) {
				llms_log( $data, $this->get_id() );
			}
		}
	}

	/**
	 * Get the value of an option from the database & fallback to default value if none found
	 * @param  string $key option key, ie "title"
	 * @return mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_option( $key ) {

		$name = $this->get_option_name( $key );
		$val = get_option( $name, $this->$key );

		return apply_filters( 'llms_get_gateway_' . $key, $val, $this->id );

	}

	/**
	 * Retrieve an option name specific to the gateway
	 * Used to retrieve options from the wp_options table where applicable
	 * @param  string $key option key, ie "title"
	 * @return string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_option_name( $key ) {
		return 'llms_gateway_' . $this->id . '_' . $key;
	}

	/**
	 * Called when refunding via a Gateway
	 * This function must be defined by gateways which support refunds
	 * This function is called by LLMS_Transaction->process_refund()
	 * @param    obj     $transaction  Instance of the LLMS_Transaction
	 * @param    float   $amount       Amount to refund
	 * @param    string  $note         Optional refund note to pass to the gateway
	 * @return   mixed
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function process_refund( $transaction, $amount = 0, $note = '' ) {}

	/**
	 * Determine if a feature is supported by the gateway
	 * Looks at the $this->supports and ensures the submitted feature exists and is true
	 * @param  string $feature name of the supported feature
	 * @return boolean
	 * @since  3.0.0
	 */
	public function supports( $feature ) {

		$supports = $this->get_supported_features();

		if ( isset( $supports[ $feature ] ) && $supports[ $feature ] ) {
			return true;
		}

		return false;

	}

}
