<?php
/**
 * LifterLMS Payment Gateways Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Payment Gateways abstract class
 *
 * @since 3.0.0
 * @since 4.0.0 Removed deprecated completed transaction message parameter output.
 * @since 5.3.0 Extend LLMS_Abstract_Options_Data for improved options interactions.
 */
abstract class LLMS_Payment_Gateway extends LLMS_Abstract_Options_Data {

	/**
	 * Optional gateway description for the admin panel
	 *
	 * @var string
	 */
	public $admin_description = '';

	/**
	 * Fields the gateway uses on the admin panel when displaying/editing an order
	 *
	 * @var array
	 */
	public $admin_order_fields = array(
		'customer'     => false,
		'subscription' => false,
		'source'       => false,
	);

	/**
	 * Optional gateway title for the admin panel
	 *
	 * @var string
	 */
	public $admin_title = '';

	/**
	 * Optional gateway description for the frontend
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * Order to display the gateway in on the frontend
	 *
	 * @var integer
	 */
	public $display_order = 1;

	/**
	 * Is the gateway enabled for payment processing?
	 *
	 * @var string
	 */
	public $enabled = 'no';

	/**
	 * Optional icon displayed on the frontend
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * ID of the Payment Gateway, used internally
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Logging status
	 *
	 * @var string
	 */
	public $logging_enabled = '';

	/**
	 * Option name prefix.
	 *
	 * @var string
	 */
	protected $option_prefix = 'llms_gateway_';

	/**
	 * Array of supported gateway features.
	 *
	 * @var array
	 */
	public $supports = array(
		'checkout_fields'           => false,
		'cc_save'                   => false,
		'refunds'                   => false,
		'single_payments'           => false,
		'recurring_payments'        => false,
		'recurring_retry'           => false,
		'test_mode'                 => false,
		'modify_recurring_payments' => null,
	);

	/**
	 * Description of the gateway's test mode (if supported)
	 *
	 * @var string
	 */
	public $test_mode_description = '';

	/**
	 * Is test mode enabled?
	 *
	 * Can be modified by user on settings page if gateway supports "test_mode".
	 *
	 * @var string
	 */
	public $test_mode_enabled = 'no';

	/**
	 * Title of the gateway's test mode (if supported)
	 *
	 * @var string
	 */
	public $test_mode_title = '';

	/**
	 * Gateway title for the frontend
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Strings to mask when writing debug logs.
	 *
	 * @var string[]
	 */
	protected $secure_strings = array();

	/**
	 * Option's data version
	 *
	 * @var integer
	 */
	protected $version = 2;

	/**
	 * Adds a string to the gateway's list of secure strings.
	 *
	 * @since 7.0.0
	 *
	 * @param string $string The string to add.
	 * @return void
	 */
	public function add_secure_string( $string ) {
		$this->secure_strings[] = (string) $string;
	}

	/**
	 * This should be called by the gateway after verifying the transaction was completed successfully
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Added access plan and query string checkout redirect settings.
	 * @since 3.34.3 Use `llms_redirect_and_exit()` instead of `wp_redirect()` and `exit()`.
	 * @since 3.37.18 Allow redirection to external domains by disabling "safe" redirects.
	 *
	 * @param LLMS_Order $order      Instance of an LLMS_Order object.
	 * @param null       $deprecated Deprecated.
	 * @return void
	 */
	public function complete_transaction( $order, $deprecated = null ) {

		$this->log( $this->get_admin_title() . ' `complete_transaction()` started', $order );

		$redirect = $this->get_complete_transaction_redirect_url( $order );

		$this->log( $this->get_admin_title() . ' `complete_transaction()` finished', $redirect, $order );

		// Ensure notification processors get dispatched since shutdown wont be called.
		do_action( 'llms_dispatch_notification_processors' );

		// Execute a redirect.
		llms_redirect_and_exit(
			$redirect,
			array(
				'safe' => false,
			)
		);

	}

	/**
	 * This should be called by AJAX-powered gateways after verifying that a transaction was completed successfully.
	 *
	 * @since 7.0.0
	 *
	 * @param LLMS_Order $order The order being processed.
	 * @param array      $data  Data to add to the default success return array.
	 * @return array {
	 *     An array of return data. The actual return array may include additional data from the payment gateway.
	 *
	 *     @type string $redirect The complete transaction redirect URL.
	 *     @type string $status   The status code, always 'SUCCESS'.
	 * }
	 */
	public function complete_transaction_ajax( $order, $data = array() ) {

		$data = wp_parse_args(
			$data,
			array(
				'redirect' => $this->get_complete_transaction_redirect_url( $order ),
				'status'   => 'SUCCESS',
			)
		);

		// Ensure notification processors get dispatched since shutdown won't be called.
		do_action( 'llms_dispatch_notification_processors' );

		return $data;

	}

	/**
	 * Confirms a Payment.
	 *
	 * Called by {@see LLMS_Controller_Orders::confirm_pending_order} on confirm form submission.
	 *
	 * Some validation is performed before passing to this function, gateways should do further validation
	 * on their own.
	 *
	 * This stub is not necessary to implement if the gateway doesn't have a payment confirmation step.
	 *
	 * For gateways which implement AJAX order processing, this function should return either a WP_Error or
	 * a success array from {@see LLMS_Payment_Gateway::complete_transaction_ajax()}.
	 *
	 * For gateways which implement synchronous order processing through form submission, this function should
	 * not return and should instead perform a redirect and / or output notices using {@see llms_add_notice()}.
	 *
	 * @since 3.0.0
	 *
	 * @param LLMS_Order $order Instance of the order being processed.
	 * @return void|WP_Error|array
	 */
	public function confirm_pending_order( $order ) {}

	/**
	 * Get admin description for the gateway
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_admin_description() {

		/**
		 * Filters a payment gateway's admin description.
		 *
		 * @since 3.0.0
		 *
		 * @param string $admin_description The admin description.
		 * @param string $gateway_id        The payment gateway ID.
		 */
		return apply_filters( 'llms_get_gateway_admin_description', $this->admin_description, $this->id );

	}

	/**
	 * Get the admin title for the gateway
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_admin_title() {

		/**
		 * Filters a payment gateway's admin title.
		 *
		 * @since 3.0.0
		 *
		 * @param string $admin_title The admin title.
		 * @param string $gateway_id  The payment gateway ID.
		 */
		return apply_filters( 'llms_get_gateway_admin_title', $this->admin_title, $this->id );

	}

	/**
	 * Get data about the fields displayed on the admin panel when viewing an order
	 *
	 * @since 3.10.0
	 *
	 * @return array[]
	 */
	public function get_admin_order_fields() {

		$fields = array(
			'customer'     => array(
				'label'   => __( 'Customer ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['customer'],
				'name'    => 'gateway_customer_id',
			),
			'source'       => array(
				'label'   => __( 'Source ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['source'],
				'name'    => 'gateway_source_id',
			),
			'subscription' => array(
				'label'   => __( 'Subscription ID', 'lifterlms' ),
				'enabled' => $this->admin_order_fields['subscription'],
				'name'    => 'gateway_subscription_id',
			),
		);

		/**
		 * Filters a payment gateway's admin title.
		 *
		 * @since 3.10.0
		 *
		 * @param array[] $fields     Array of admin order fields.
		 * @param string  $gateway_id The payment gateway ID.
		 */
		return apply_filters( 'llms_get_gateway_admin_order_fields', $fields, $this->id );
	}

	/**
	 * Get default gateway admin settings fields
	 *
	 * @since 3.0.0
	 * @since 3.29.0 Unknown.
	 *
	 * @return array[]
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
			'autoload'     => true,
			'id'           => $this->get_option_name( 'enabled' ),
			'desc'         => sprintf( _x( 'Enable %s', 'Payment gateway title', 'lifterlms' ), $this->get_admin_title() ),
			'desc_tooltip' => __( 'Checking this box will allow users to use this payment gateway.', 'lifterlms' ),
			'default'      => $this->get_enabled(),
			'title'        => __( 'Enable / Disable', 'lifterlms' ),
			'type'         => 'checkbox',
		);

		$fields[] = array(
			'id'      => $this->get_option_name( 'title' ),
			'desc'    => '<br>' . __( 'The title the user sees during checkout.', 'lifterlms' ),
			'default' => $this->get_title(),
			'title'   => __( 'Title', 'lifterlms' ),
			'type'    => 'text',
		);

		$fields[] = array(
			'id'      => $this->get_option_name( 'description' ),
			'desc'    => '<br>' . __( 'The description the user sees during checkout.', 'lifterlms' ),
			'default' => $this->get_description(),
			'title'   => __( 'Description', 'lifterlms' ),
			'type'    => 'text',
		);

		if ( $this->supports( 'test_mode' ) ) {

			$fields[] = array(
				'id'           => $this->get_option_name( 'test_mode_enabled' ),
				'desc'         => sprintf( _x( 'Enable %s', 'Payment gateway test mode title', 'lifterlms' ), $this->get_test_mode_title() ),
				'desc_tooltip' => $this->get_test_mode_description(),
				'default'      => $this->get_test_mode_enabled(),
				'title'        => $this->get_test_mode_title(),
				'type'         => 'checkbox',
			);

		}

		$fields[] = array(
			'id'           => $this->get_option_name( 'logging_enabled' ),
			'desc'         => __( 'Enable debug logging', 'lifterlms' ),
			'desc_tooltip' => sprintf( __( 'When enabled, debugging information will be logged to "%s"', 'lifterlms' ), llms_get_log_path( $this->get_id() ) ),
			'title'        => __( 'Debug Log', 'lifterlms' ),
			'type'         => 'checkbox',
		);

		/**
		 * Filters the gateway's settings fields displayed on the admin panel
		 *
		 * @since 3.0.0
		 *
		 * @param array[] $fields     Array of settings fields.
		 * @param string  $gateway_id The payment gateway ID.
		 */
		return apply_filters( 'llms_get_gateway_settings_fields', $fields, $this->id );

	}

	/**
	 * Get API mode
	 *
	 * If test is not supported will return "live".
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_api_mode() {
		if ( $this->supports( 'test_mode' ) && $this->is_test_mode_enabled() ) {
			return 'test';
		}
		return 'live';
	}

	/**
	 * Calculates the url to redirect to on transaction completion.
	 *
	 * @since 3.30.0
	 * @since 7.0.0 Retrieve the redirect URL from the INPUT_POST if not passed via INPUT_GET.
	 *
	 * @param LLMS_Order $order The order object.
	 * @return string
	 */
	protected function get_complete_transaction_redirect_url( $order ) {

		// Get the redirect parameter from INPUT_GET.
		$redirect = urldecode( llms_filter_input( INPUT_GET, 'redirect', FILTER_VALIDATE_URL ) ?? '' );

		// Get the redirect parameter from INPUT_POST if not INPUT_GET redirect pased.
		$redirect = $redirect ? $redirect : llms_filter_input( INPUT_POST, 'redirect', FILTER_VALIDATE_URL );

		// Redirect to the product's permalink, if no redirect found yet.
		$redirect = $redirect ? $redirect : get_permalink( $order->get( 'product_id' ) );

		// Fallback to the account page if we don't have a url for some reason.
		$redirect = $redirect ? $redirect : get_permalink( llms_get_page_id( 'myaccount' ) );

		// Add order key to the url.
		$redirect = add_query_arg(
			array(
				'order-complete' => $order->get( 'order_key' ),
			),
			esc_url( $redirect )
		);

		// Redirection url on free checkout form.
		$quick_enroll_form      = llms_filter_input( INPUT_POST, 'form' );
		$free_checkout_redirect = llms_filter_input( INPUT_POST, 'free_checkout_redirect', FILTER_VALIDATE_URL );

		if ( get_current_user_id() && ( 'free_enroll' === $quick_enroll_form ) && $free_checkout_redirect ) {
			$redirect = $free_checkout_redirect;
		}

		/**
		 * Filters the redirect on order completion.
		 *
		 * @since 3.8.0
		 *
		 * @param string     $redirect The URL to redirect user to.
		 * @param LLMS_Order $order    The order object.
		 */
		return esc_url( apply_filters( 'lifterlms_completed_transaction_redirect', $redirect, $order ) );

	}

	/**
	 * Gateways can override this to return a URL to a customer permalink on the gateway's website
	 *
	 * If this is not defined, it will just return the supplied ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $customer_id Gateway's customer ID.
	 * @param string $api_mode    Link to either the live or test site for the gateway, where applicable.
	 * @return string
	 */
	public function get_customer_url( $customer_id, $api_mode = 'live' ) {
		return $customer_id;
	}

	/**
	 * Get the frontend description setting
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->get_option( 'description' );
	}

	/**
	 * Get the display order setting
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_display_order() {
		return absint( $this->get_option( 'display_order' ) );
	}

	/**
	 * Get the value of the enabled setting
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_enabled() {
		return $this->get_option( 'enabled' );
	}

	/**
	 * Get fields displayed on the checkout form
	 *
	 * Gateways should define this function if the gateway supports fields.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_fields() {
		/**
		 * Filters the HTML of the gateway's checkout fields
		 *
		 * @since 3.0.0
		 *
		 * @param string $fields     Fields HTML string.
		 * @param string $gateway_id The payment gateway's ID.
		 */
		return apply_filters( 'llms_get_gateway_fields', '', $this->id );
	}

	/**
	 * Get the icon displayed on the checkout form
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_icon() {
		/**
		 * Filters the HTML of the gateway's checkout icon
		 *
		 * @since 3.0.0
		 *
		 * @param string $icon       Icon HTML string.
		 * @param string $gateway_id The payment gateway's ID.
		 */
		return apply_filters( 'llms_get_gateway_icon', $this->icon, $this->id );
	}

	/**
	 * Get the gateway's ID
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Retrieve an HTML link to a customer, subscription, or source URL
	 *
	 * If no URL provided returns the item value as string.
	 *
	 * @since 3.10.0
	 *
	 * @param string $item_key   The key of the item to retrieve a URL for.
	 * @param string $item_value The value of the item to retrieve.
	 * @param string $api_mode   The current api mode to retrieve the URL for.
	 * @return string
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

			default:
				$url = $item_value;

		}

		if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return $item_value;
		}

		return sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $url, $item_value );

	}

	/**
	 * Get the value of the logging setting
	 *
	 * @since 3.0.0
	 * @since 7.0.0 Added the force filter, `llms_gateway_{$this->id}_logging_enabled`.
	 *
	 * @return string
	 */
	public function get_logging_enabled() {
		/**
		 * Enables forcing the logging status for the gateway on or off.
		 *
		 * The dynamic portion of this hook, `{$this->id}`, refers to the gateway's ID.
		 *
		 * @since 7.0.0
		 *
		 * @param null|bool $forced The forced status. If `null`, the default status derived from the gateway options will be used.
		 */
		$forced = apply_filters( "llms_gateway_{$this->id}_logging_enabled", null );
		if ( ! is_null( $forced ) ) {
			return $forced ? 'yes' : 'no';
		}
		return $this->get_option( 'logging_enabled' );
	}

	/**
	 * Adds the gateway's registered secured strings to the default list of site-wide secure strings.
	 *
	 * This is the callback for the `llms_secure_strings` filter (called via `llms_log()`).
	 *
	 * @since 6.4.0
	 * @since 7.0.0 Load strings from `retrieve_secure_strings()`.
	 *
	 * @param string[] $strings Array of secure strings.
	 * @param string   $handle  The log handle.
	 * @return string[]
	 */
	public function get_secure_strings( $strings, $handle ) {

		// Don't add our strings to other log files.
		if ( $this->id !== $handle ) {
			return $strings;
		}

		return array_merge( $strings, $this->retrieve_secure_strings() );

	}

	/**
	 * Gateways can override this to return a URL to a source permalink on the gateway's website
	 *
	 * If this is not defined, it will just return the supplied ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $source_id Gateway's source ID.
	 * @param string $api_mode  Link to either the live or test site for the gateway, where applicable.
	 * @return string
	 */
	public function get_source_url( $source_id, $api_mode = 'live' ) {
		return $source_id;
	}

	/**
	 * Gateways can override this to return a URL to a subscription permalink on the gateway's website
	 *
	 * If this is not defined, it will just return the supplied ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $subscription_id Gateway's subscription ID.
	 * @param string $api_mode        Link to either the live or test site for the gateway, where applicable.
	 * @return string
	 */
	public function get_subscription_url( $subscription_id, $api_mode = 'live' ) {
		return $subscription_id;
	}

	/**
	 * Get an array of features the gateway supports.
	 *
	 * @since 3.0.0
	 * @since 7.0.0 Handle `modify_recurring_payments` depending on `recurring_payments`.
	 *
	 * @return array
	 */
	public function get_supported_features() {

		if ( ! isset( $this->supports['modify_recurring_payments'] ) || is_null( $this->supports['modify_recurring_payments'] ) ) {
			$this->supports['modify_recurring_payments'] = $this->supports['recurring_payments'] ?? false;
		}

		/**
		 * Filters the gateway's supported features array
		 *
		 * @since 3.0.0
		 *
		 * @param array  $supports   Array of feature support.
		 * @param string $gateway_id The payment gateway's ID.
		 */
		return apply_filters( 'llms_get_gateway_supported_features', $this->supports, $this->id );
	}

	/**
	 * Get the description of test mode displayed on the admin panel
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_test_mode_description() {
		return $this->test_mode_description;
	}

	/**
	 * Get value of the test mode enabled setting
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_test_mode_enabled() {
		return $this->get_option( 'test_mode_enabled' );
	}

	/**
	 * Get the title of test mode displayed on the admin panel
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_test_mode_title() {
		return $this->test_mode_title;
	}

	/**
	 * Get gateway title setting
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->get_option( 'title' );
	}

	/**
	 * Gateways can override this to return a URL to a transaction permalink on the gateway's website
	 *
	 * If this is not defined, it will just return the supplied ID.
	 *
	 * @since 3.0.0
	 *
	 * @param string $transaction_id Gateway's transaction ID.
	 * @param string $api_mode       Link to either the live or test site for the gateway, where applicable.
	 *
	 * @return string
	 */
	public function get_transaction_url( $transaction_id, $api_mode = 'live' ) {
		return $transaction_id;
	}

	/**
	 * Called when the Update Payment Method form is submitted from a single order view on the student dashboard
	 *
	 * Gateways should do whatever the gateway needs to do to validate the new payment method and save it to the order
	 * so that future payments on the order will use this new source.
	 *
	 * This should be an abstract function but experience has taught me that no one will upgrade follow our instructions
	 * and they'll end up with 500 errors and debug mode disabled and send me giant frustrated question marks.
	 *
	 * @since 3.10.0
	 *
	 * @param LLMS_Order $order     Order object.
	 * @param array      $form_data Additional data passed from the submitted form (EG $_POST).
	 *
	 * @return null
	 */
	public function handle_payment_source_switch( $order, $form_data = array() ) {
		return llms_add_notice(
			sprintf(
				// Translatos: %s = the title of the payment gateway.
				esc_html__( 'The selected payment gateway "%s" does not support payment method switching.', 'lifterlms' ),
				$this->get_title()
			),
			'error'
		);
	}

	/**
	 * Handle a Pending Order
	 *
	 * Called by LLMS_Controller_Orders->create_pending_order() on checkout form submission.
	 *
	 * All data will be validated before it's passed to this function.
	 *
	 * @since 3.0.0
	 *
	 * @param LLMS_Order        $order  The order being processed.
	 * @param LLMS_Access_Plan  $plan   Access plan the order is built from.
	 * @param LLMS_Student      $person The purchasing customer.
	 * @param LLMS_Coupon|false $coupon Coupon used during order processing or `false` if none supplied.
	 * @return void
	 */
	abstract public function handle_pending_order( $order, $plan, $person, $coupon = false );

	/**
	 * Called by scheduled actions to charge an order for a scheduled recurring transaction
	 *
	 * This function must be defined by gateways which support recurring transactions.
	 *
	 * @since 3.0.0
	 *
	 * @param LLMS_Order $order The order being processed.
	 * @return mixed
	 */
	public function handle_recurring_transaction( $order ) {}

	/**
	 * Determine if the gateway is the default gateway
	 *
	 * This will be the FIRST gateway in the gateways that are enabled.
	 *
	 * @since 3.0.0
	 * @since 5.3.0 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return boolean
	 */
	public function is_default_gateway() {
		return ( $this->get_id() === llms()->payment_gateways()->get_default_gateway() );
	}

	/**
	 * Determine if the gateway is enabled according to admin settings checkbox
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return ( 'yes' === $this->get_enabled() ) ? true : false;
	}

	/**
	 * Determine if test mode is enabled
	 *
	 * Returns false if gateway doesn't support test mode.
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_test_mode_enabled() {
		return ( 'yes' === $this->get_test_mode_enabled() ) ? true : false;
	}

	/**
	 * Log messages if logging is enabled
	 *
	 * @since 3.0.0
	 * @since 6.4.0 Load the gateway's `secure_option` settings into `llms_secure_strings` hook when logging.
	 *
	 * @return void
	 */
	public function log() {

		if ( ! llms_parse_bool( $this->get_logging_enabled() ) ) {
			return;
		}

		add_filter( 'llms_secure_strings', array( $this, 'get_secure_strings' ), 10, 2 );

		foreach ( func_get_args() as $data ) {
			llms_log( $data, $this->get_id() );
		}

		remove_filter( 'llms_secure_strings', array( $this, 'get_secure_strings' ), 10, 2 );

	}

	/**
	 * Get the value of an option from the database & fallback to default value if none found
	 *
	 * Optionally attempts to retrieve a secure key first, if secure key is provided.
	 *
	 * The behavior of this function differs slightly from the parent method in that the second argument
	 * in this method allows lookup of the secure key value.
	 *
	 * Default options are autoloaded via the get_option_default_value() method.
	 *
	 * @since 3.0.0
	 * @since 3.29.0 Added secure option lookup via option second parameter.
	 *
	 * @param string $key option Option name / key, eg: "title".
	 * @param string $secure_key Secure option name / key, eg: "TITLE".
	 * @return mixed
	 */
	public function get_option( $key, $secure_key = false ) {

		if ( $secure_key ) {
			$secure_val = llms_get_secure_option( $secure_key );
			if ( false !== $secure_val ) {
				return $secure_val; // Intentionally not filtered here.
			}
		}

		$val = parent::get_option( $key );

		/**
		 * Filters the value of a gateway option
		 *
		 * The dynamic portion of the hook, `{$key}`, refers to the unprefixed
		 * option name.
		 *
		 * @since Unknown
		 *
		 * @param mixed  $val        Option value.
		 * @param string $gateway_id Payment gateway ID.
		 */
		return apply_filters( "llms_get_gateway_{$key}", $val, $this->id );

	}

	/**
	 * Option default value autoloader
	 *
	 * This is a callback function for the WP core filter `default_option_{$option}`.
	 *
	 * @since 5.3.0
	 *
	 * @param mixed  $default_value        The default value. If no value is passed to `get_option()`, this will be an empty string.
	 *                                     Otherwise it will be the default value passed to the method.
	 * @param string $full_option_name     The full (prefixed) option name.
	 * @param bool   $passed_default_value Whether or not a default value was passed to `get_option()`.
	 * @return mixed The default option value.
	 */
	public function get_option_default_value( $default_value, $full_option_name, $passed_default_value ) {
		$unprefixed = str_replace( $this->get_option_prefix(), '', $full_option_name );
		return isset( $this->$unprefixed ) ? $this->$unprefixed : '';
	}

	/**
	 * Retrieve a prefix for options
	 *
	 * Appends the gateway's ID to the default option prefix, eg: "llms_gateway_manual_".
	 *
	 * @since 5.3.0
	 *
	 * @return string
	 */
	protected function get_option_prefix() {
		return parent::get_option_prefix() . $this->id . '_';
	}

	/**
	 * Called when refunding via a Gateway
	 *
	 * This function must be defined by gateways which support refunds.
	 *
	 * This function is called by LLMS_Transaction->process_refund().
	 *
	 * @since 3.0.0
	 *
	 * @param LLMS_Transaction $transaction The transaction being refunded.
	 * @param float            $amount      Amount to refund.
	 * @param string           $note        Optional refund note to pass to the gateway.
	 * @return mixed
	 */
	public function process_refund( $transaction, $amount = 0, $note = '' ) {}

	/**
	 * Retrieves a list of "secure" strings which should be anonymized if they're found within debug logs.
	 *
	 * This method will load the values of any gateway options with a `secure_option` declaration. Additional
	 * strings can be added to the list using the `llms_get_gateway_secure_strings` filter or via the
	 * gateway's `add_secure_string()` method.
	 *
	 * @since 7.0.0
	 *
	 * @return string[]
	 */
	public function retrieve_secure_strings() {

		$gateway_strings = $this->secure_strings;
		foreach ( $this->get_admin_settings_fields() as $field ) {

			if ( empty( $field['id'] ) || empty( $field['secure_option'] ) ) {
				continue;
			}

			$string = llms_get_secure_option( $field['secure_option'], '', $field['id'] );
			if ( empty( $string ) ) {
				continue;
			}

			$gateway_strings[] = $string;

		}

		/**
		 * Filters the list of the gateway's secure strings.
		 *
		 * @since 6.4.0
		 *
		 * @param string[] $gateway_strings List of secure strings for the payment gateway.
		 * @param string   $id              The gateway ID.
		 */
		$gateway_strings = apply_filters( 'llms_get_gateway_secure_strings', $gateway_strings, $this->id );

		return array_values( array_unique( $gateway_strings ) );

	}

	/**
	 * Determine if a feature is supported by the gateway.
	 *
	 * Looks at the $this->supports and ensures the submitted feature exists and is true.
	 *
	 * @since 3.0.0
	 * @since 7.0.0 Added `$order` param, to be used when the feature also depends on an order property.
	 *
	 * @param string     $feature Name of the supported feature.
	 * @param LLMS_Order $order   Instance of an LLMS_Order.
	 * @return boolean
	 */
	public function supports( $feature, $order = null ) {

		$supports = $this->get_supported_features();

		if ( isset( $supports[ $feature ] ) && $supports[ $feature ] ) {
			return true;
		}

		return false;

	}

	/**
	 * Determine if an access plan can be processed by the gateway.
	 *
	 * @since 7.5.0
	 *
	 * @param LLMS_Access_Plan $plan  Instance of an LLMS_Access_Plan.
	 * @param LLMS_Order       $order Instance of an LLMS_Order. Used to check whether a payment can be switched using this gateway.
	 *                                In that case, in fact, we have to rely on the access plan information contained in the order
	 *                                at the moment of its creation.
	 * @return boolean
	 */
	public function can_process_access_plan( $plan, $order = null ) {
		/**
		 * Filters whether or not a gateway can process a specific access plan.
		 *
		 * @since 7.5.0
		 *
		 * @param bool             $can_process_plan Whether or not the gateway can process a specific access plan.
		 * @param LLMS_Access_Plan $plan             Access plan object.
		 * @param LLMS_Order       $plan             Order object.
		 * @param string           $id               The gateway ID.
		 */
		return apply_filters( 'llms_can_gateway_process_access_plan', $plan || $order, $plan, $order, $this->id );
	}

}
