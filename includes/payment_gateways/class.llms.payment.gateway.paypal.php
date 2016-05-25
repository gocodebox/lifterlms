<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* PayPal Payment Gateway class
*
* Class for managing Paypal API transactions
*
* @author codeBOX
* @project LifterLMS
*
* @version  3.0.0
*/
class LLMS_Payment_Gateway_Paypal extends LLMS_Payment_Gateway {

	/**
	 * Can be set for debugging. print_r()
	 */
	public $debug_info = array();

	/**
	 * Saves the full response once a request succeed
	 * @mixed
	 */
	public $full_response = false;

	/**
	 * Determine of PayPal is in Debug Mode
	 * @var boolean
	 */
	private $is_debug = false;

	/**
	 * Number of orders to query during each check
	 * @var integer
	 * @since  3.0.0
	 */
	public $orders_per_sync = 100;

	/**
	 * PayPal account password
	 * @string
	 */
	public $password;

	/**
	 * PayPal API Redirect URL
	 * @string
	 */
	private $redirect_url;

	/**
	 * Read world PayPal redirect URL
	 * @string
	 */
	private $real_redirect_url = 'https://www.paypal.com/cgi-bin/webscr';

	/**
	 * Real world PayPal API Server
	 * @string
	 */
	private $real_server = 'https://api-3t.paypal.com/nvp';

	/**
	 * Sandbox PayPal redirect URL
	 * @string
	 */
	private $sandbox_redirect_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

	/**
	 * Sandbox PayPal Server
	 * @string
	 */
	private $sandbox_server = 'https://api-3t.sandbox.paypal.com/nvp';

	/**
	 * PayPal API Server
	 * @string
	 */
	private $server;

	/**
	 * Array representing the supported short-terms
	 * @array
	 */
	private $short_term = array(
		'amount' => 'PAYMENTREQUEST_0_AMT',
		'currency_code' => 'PAYMENTREQUEST_0_CURRENCYCODE',
		'return_url' => 'RETURNURL',
		'cancel_url' => 'CANCELURL',
		'payment_action' => 'PAYMENTREQUEST_0_PAYMENTACTION',
		'token' => 'TOKEN',
		'payer_id' => 'PAYERID',
		'product_name' => 'L_PAYMENTREQUEST_0_NAME0',
		'product_price' => 'L_PAYMENTREQUEST_0_AMT0',
		'product_sku' => 'L_PAYMENTREQUEST_0_NUMBER0',
		'profile_id' => 'PROFILEID',
		'item_category' => 'L_PAYMENTREQUEST_0_ITEMCATEGORY0',
		'billing_type' => 'L_BILLINGTYPE0',
		'billing_agreement_desc' => 'L_BILLINGAGREEMENTDESCRIPTION0',
		'profile_start_date' => 'PROFILESTARTDATE',
		'billing_period' => 'BILLINGPERIOD',
		'billing_freq' => 'BILLINGFREQUENCY',
		'recurring_amount' => 'AMT',
		'description' => 'DESC',
		'init_payment' => 'INITAMT',
		'init_payment_fail' => 'FAILEDINITAMTACTION',
		'max_failed_payments' => 'MAXFAILEDPAYMENTS',
		'total_billing_cycles' => 'TOTALBILLINGCYCLES',
	);

	/**
	 * PayPal account signature
	 * @string
	 */
	public $signature;

	/**
	 * Requires SSL Verification
	 * @boolean
	 */
	public $ssl_verify;

	/**
	 * Features supported by PayPal
	 * @var array
	 *
	 * @since  3.0.0
	 */
	public $supports = array(
		'refunds' => true,
		'recurring' => true,
		'recurring_sync' => true,
	);

	/**
	 * Frequency to run the check
	 * Accepts any valid recurrance that can be passed to wp_schdule_event
	 * Defaults are hourly, twicedaily, or daily but custom recurrances
	 * can be defined by using the `cron_schedules` filter
	 * @var string
	 * @since  3.0.0
	 */
	public $sync_frequency = 'hourly';

	/**
	 * Period of time (in seconds) after which the connection ends
	 * @integer
	 */
	public $time_out = 60;

	/**
	 * PayPal account username as defined in LifterLMS PayPal Settings
	 * @string
	 */
	public $user;

	/**
	 * PayPal API Version
	 * @string
	 */
	public $version;

	/**
	 * Creates a new PayPal gateway object
	 */
	public function __construct() {

		// Set the SSL Verification
		$this->ssl_verify   = apply_filters( 'https_local_ssl_verify', false );

		$this->id           = 'paypal';
		$this->title        = 'PayPal';
		$this->payment_type = 'paypal';

		$this->version      = '117.00';

		$this->sandbox      = get_option( 'lifterlms_gateways_paypal_enable_sandbox' ) == 'yes' ? true : false;
		$this->user         = get_option( 'lifterlms_gateways_paypal_email' );
		$this->password     = get_option( 'lifterlms_gateways_paypal_password' );
		$this->signature    = get_option( 'lifterlms_gateways_paypal_signature' );

		// get the debug status for displaying error messages.
		$this->is_debug = $this->get_debug_status();

		// allow filtering of short to long-term mapping
		$this->short_term = apply_filters( 'lifterlms_paypal_short_terms', $this->short_term );

		// allow filtering of sync frequency
		$this->sync_frequency = apply_filters( 'lifterlms_paypal_order_sync_frequency', $this->sync_frequency );

		// allow filtering of orders / sync
		$this->orders_per_sync = absint( apply_filters( 'lifterlms_paypal_orders_per_sync', $this->orders_per_sync ) );

		 // Set the Server and Redirect URL
		if ( $this->sandbox ) {

			$this->server = $this->sandbox_server;
			$this->redirect_url = $this->sandbox_redirect_url;

		} else {

			$this->server = $this->real_server;
			$this->redirect_url = $this->real_redirect_url;

		}

	}

	/**
	 * Builds the request array from the object, param and type parameters
	 * @param string $type
	 * @param array $param
	 * @return array $body
	 */
	private function build_request( $type, $param ) {
		// Request Body

		$body = $param;
		$body['METHOD'] = $type;
		$body['VERSION'] = $this->version;
		$body['USER'] = $this->user;
		$body['PWD'] = $this->password;
		$body['SIGNATURE'] = $this->signature;
		//$body['CUSTOM'] = 'a custom field.come back to me';

		// Request Array
		$request = array(
			'body' => $body,
			'httpversion' => '1.1',
			'method' => 'POST',
			'sslverify' => $this->ssl_verify,
			'timeout' => $this->time_out,
		);

		return $request;

	}

	/**
	 * Complete payment cleanup
	 * Sets all variables needed to create lifterLMS order
	 * Updates required tables to associate user with course or membership purchased
	 *
	 * @param  array $request [Paypal getExpressCheckout response]
	 * @param  object $order   [order object that stores all details of order]
	 *
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function complete_payment( $request, $order ) {

		if ( 'llms-failed' !== $order->get_status() && 'llms-pending' !== $order->get_status() ) {

			return $this->return_error( 'This order has already been completed and cannot be purchased again.' );

		}

		if( isset( $request['PAYERID'] ) ) {
			$order->transaction_customer_id = $request['PAYERID'];
		}
		$order->transaction_api_mode = ( $this->sandbox ) ? 'test' : 'live';


		if ( 'single' === $order->get_type() && strcmp( $request['ACK'], 'Failure' ) !== 0 ) {

			$param = array(
				'amount' => $request['AMT'],
				'currency_code' => $request['CURRENCYCODE'],
				'payer_id' => $request['PAYERID'],
				'token' => $request['TOKEN'],
			);

			if ( $this->doExpressCheckout( $param ) ) {

				$response = $this->getResponse();

			}

		}
		// recurring
		elseif ( 'recurring' === $order->get_type() && strcmp( $request['ACK'], 'Failure' ) !== 0 ) {

			$param = array(
				'init_payment' => $order->get_first_payment_total(),
				'profile_start_date' => gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $order->get_billing_start_date() ) ),
				'description' => $this->get_subscription_description( $order ),
				'billing_agreement_desc' => $this->get_subscription_description( $order ),
				'billing_period' => $this->get_billing_period( $order->get_billing_period() ),
				'billing_freq' => $order->get_billing_frequency(),
				'recurring_amount' => $order->get_recurring_payment_total(),
				'currency_code' => $request['CURRENCYCODE'],
				'token' => $request['TOKEN'],
				'payer_id' => $request['PAYERID'],
				'total_billing_cycles' => $order->get_billing_cycle(),
				'max_failed_payments' => '1',
			);

			if ( $this->createRecurringPaymentsProfile( $param ) ) {

				$response = $this->getResponse();

			}
		}

		if ( isset( $response ) && 'Success' == $response['ACK'] ) {

			// mark order as completed
			switch( $order->get_type() ) {
				case 'single' :
					$order->update_status( 'completed' );
					// save fee to metadata if available
					if ( isset( $response['PAYMENTINFO_0_FEEAMT'] ) ) {
						$order->transaction_fee = $response['PAYMENTINFO_0_FEEAMT'];
					}
					// save transaction id if available
					if( isset( $response['PAYMENTINFO_0_TRANSACTIONID'] ) ) {
						$order->transaction_id = $response['PAYMENTINFO_0_TRANSACTIONID'];
					}
				break;

				case 'recurring' :
					$order->update_status( 'active' );
					// save transaction id if available
					if( isset( $response['PROFILEID'] ) ) {
						$order->subscription_id = $response['PROFILEID'];
					}
				break;
			}

			do_action( 'lifterlms_order_process_success', $order );

		} else {

			$order->update_status( 'failed' );
			do_action( 'lifterlms_order_process_error', $order->user_id );
			return $this->return_error( 'There was an error processing your payment.' );

		}

	}

	/**
	 * Executes paypal purchase request
	 *
	 * @param  array $response [paypal return response from user payment approval]
	 *
	 * @return array           [success or fail response from getExpressCheckout]
	 */
	public function confirm_payment( $response ) {

		$param = array(
			'token' => $response['token'],
		);

		if ( $this->getExpressCheckout( $param ) ) {

			return $this->getResponse();

		} else {

			return $this->return_error( 'There was an error connecting to the payment gateway.', 'lifterlms' );

		}

	}

	/**
	 * Executes a doExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function createRecurringPaymentsProfile( $param ) {
		return $this->requestExpressCheckout( 'CreateRecurringPaymentsProfile', $param );
	}

	/**
	 * Executes a doExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function doExpressCheckout( $param ) {
		return $this->requestExpressCheckout( 'DoExpressCheckoutPayment', $param );
	}

	/**
	 * Determine if CLI is being used
	 * @return boolean
	 */
	public static function is_cli() {
		return (PHP_SAPI == 'cli' && empty( $_SERVER['REMOTE_ADDR'] ));
	}

	/**
	 * Executes a getExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function getExpressCheckout( $param ) {
		return $this->requestExpressCheckout( 'GetExpressCheckoutDetails', $param );
	}

	/**
	 * Executes a GetRecurringPaymentsProfileDetails commad
	 * @param  array $param pameters to pass to the command
	 * @return boolean
	 */
	public function getRecurringPaymentsProfileDetails( $param ) {
		return $this->requestExpressCheckout( 'GetRecurringPaymentsProfileDetails', $param );
	}

	/**
	 * Returns the redirect URL
	 * @return string $url
	 */
	public function getRedirectURL() {
		$output = $this->getResponse();
		if ($output['ACK'] === 'Success') {
			$query_data = array(
				'cmd' => '_express-checkout',
				'token' => $output['TOKEN'],
			);
			$url = $this->redirect_url . '?' . http_build_query( $query_data );
			return $url;
		} else {
			$this->debug_info = $output;
		}

		return false;
	}

	/**
	 * Returns the PayPal Body response
	 * @return array $reponse
	 */
	public function getResponse() {
		if ($this->full_response) {
			parse_str( urldecode( $this->full_response['body'] ), $output );

			if ($output && strcmp( $output['ACK'], 'Failure' ) === 0) {
				$this->debug_info = $output;
			}

			return $output;
		}
		return false;
	}

	/**
	 * Returns the response Token
	 * @return string $token
	 */
	public function getToken() {
		$output = $this->getResponse();
		if ($output['ACK'] === 'Success') {
			return $output['TOKEN'];
		} else {
			$this->debug_info = $output;
		}

		return false;
	}

	/**
	 * Queries billing period for product (course or membership post)
	 *
	 * @param  string $billing_period [string id of billing period stored in post metadata]
	 *
	 * @return string [paypal string id of billing period]
	 */
	public function get_billing_period( $billing_period ) {

		$paypal_codes = array(
			'day' => 'Day',
			'week' => 'Week',
			'month' => 'Month',
			'year' => 'Year',
		);

		return $paypal_codes[ $billing_period ];
	}

	/**
	 * Determine whether or not the debug mode option is enabled from the gateway
	 * @return bool
	 */
	private function get_debug_status() {

		$debug = get_option( 'lifterlms_gateways_paypal_enable_debug', 'no' );

		return strcmp( $debug, 'yes' ) === 0 ? true : false;
	}

	/**
	 * Retrieve Details for a Recurring Profile by Profile ID
	 * @param  string $profile_id PayPal Recurring Billing Profile ID
	 * @return mixed              array or false if no results found
	 * @since  3.0.0
	 */
	public function get_recurring_profile_details( $profile_id ) {
		$params =  array( 'profile_id' => $profile_id );
		if ( $this->getRecurringPaymentsProfileDetails( $params ) ) {
			return $this->getResponse();
		} else {
			return false;
		}
	}

	/**
	 * Get the description that can be passed with the terms and title of the description
	 * @param  obj    $order Instance of an LLMS_Order
	 * @return string
	 *
	 * @since  3.0.0
	 */
	public function get_subscription_description( $order ) {
		$product = new LLMS_Product( $order->get_product_id() );
		$desc = sprintf( __( 'Subscription to %s', 'lifterlms' ), $order->get_product_title() );
		return trim( apply_filters( 'lifterlms_paypal_subscription_description', $desc, $order ) );
	}

	/**
	 * Output error messages
	 * @param  mixed $debug_info  debug info
	 * @return string
	 */
	public function outputError( $debug_info ) {
		return 'Paypal Gateway Error: ' . $this->pr( $debug_info ) . PHP_EOL;
	}

	/**
	 * Debug function for printing the content of an object or array
	 *
	 * @param [mixes] $obj
	 */
	public function pr( $obj ) {
		ob_start();
		$pr = '';
		if ( ! self::is_cli()) {
			$pr .= '<pre style="word-wrap: break-word">'; }
		if (is_object( $obj )) {
			$pr .= $this->sprint_r( $obj ); } elseif (is_array( $obj )) {
			$pr .= $this->sprint_r( $obj ); } else { 			$pr .= $obj; }
			if ( ! self::is_cli()) {
				$pr .= '</pre>'; }

			return $pr;
	}

	/**
	 * Setup payment processing and pass express checkout params to PayPal
	 * Redirects user to PayPal to login & do the PayPal Thing
	 * After login they'll be redirected back to LifterLMS Confirm Screen
	 *
	 * If an error is encountered the user will be returned to the purchase screen
	 * with the error message displayed
	 *
	 * @param  obj    $order Instance of the pending LLMS_Order
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public function process_payment( $order ) {

		// default parameters
		$params = array(
			'cancel_url' => llms_cancel_payment_url(),
			'currency_code' => $order->get_currency(),
			'return_url' => llms_confirm_payment_url(),
			'product_name' => $order->get_product_title(),
			'product_sku' => $order->get_product_sku(),
		);

		// additional parameters based on order type
		switch( $order->get_type() ) {
			case 'single':
				$additional_params = array(
					'amount' => $order->get_total(),
					'product_price' => $order->get_total(),
				);
			break;

			case 'recurring':
				$additional_params = array(
					'init_payment' => $order->get_first_payment_total(),
					'recurring_amount' => $order->get_recurring_payment_total(),
					'product_price' => $order->get_first_payment_total(),
					'billing_type' => 'RecurringPayments',
					'description' => $this->get_subscription_description( $order ),
					'billing_agreement_desc' => $this->get_subscription_description( $order ),
					'profile_start_date' => gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $order->get_billing_start_date() ) ),
					'billing_period' => $this->get_billing_period( $order->get_billing_period() ),
					'billing_freq' => $order->get_billing_frequency(),
					'max_failed_payments' => '1',
					'total_billing_cycles' => $order->get_billing_cycle(),
				);
			break;
		}

		// merge & filter
		$params = apply_filters( 'lifterlms_process_payment_parameters', array_merge( $params, $additional_params ), $order );

		if ( $this->setExpressCheckout( $params ) ) {

			$redirect_url = $this->getRedirectURL();

			if ( $redirect_url ) {

				do_action( 'lifterlms_payment_processing_redirect', $redirect_url );

			} else {

				return $this->return_error( 'There was an error connecting to the payment gateway.' );

			}

		} else {

			return $this->return_error( 'There was an error connecting to the payment gateway.' );

		}

	}

	/**
	 * Replace the Parameters short terms
	 * @param array $param The given parameters array
	 * @return array $param
	 */
	private function replace_short_terms( $param ) {
		foreach ($this->short_term as $short_term => $long_term) {
			if (array_key_exists( $short_term, $param )) {
				$param[ $long_term ] = $param[ $short_term ];
				unset( $param[ $short_term ] );
			}
		}
		return $param;
	}

	/**
	 * @param string $type
	 * @param array $param
	 * @return boolean Specifies if the request is successful and the response property
	 *                 is filled
	 */
	private function requestExpressCheckout( $type, $param ) {
		// Construct the request array
		$param = $this->replace_short_terms( $param );

		$request = $this->build_request( $type, $param );

		// Makes the HTTP request
		$response = wp_remote_post( $this->server, $request );

		// HTTP Request fails
		if (is_wp_error( $response )) {
			$this->debug_info = $response;
			return false;
		}

		// Status code returned other than 200
		if ($response['response']['code'] != 200) {
			$this->debug_info = $response;
			return false;
		}

		// Saves the full response
		$this->full_response = $response;

		// Request succeeded
		return true;
	}

	/**
	 * Return an error message
	 * @param  string $message error message
	 * @return void
	 */
	public function return_error( $message ) {
		llms_add_notice( $message, 'error' );
		if ( $this->is_debug ) {
			llms_add_notice( $this->outputError( $this->debug_info ) );
		}
		return;
	}

	/**
	 * Schedule cron actions for order syncing
	 * @return void
	 * @since  3.0.0
	 */
	public static function schedule_sync() {
		if ( ! wp_next_scheduled( 'lifterlms_paypal_order_sync' )) {
			wp_schedule_event( time(), $this->sync_frequency, 'lifterlms_paypal_order_sync' );
		}
	}

	/**
	 * Executes a setExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function setExpressCheckout( $param ) {
		return $this->requestExpressCheckout( 'SetExpressCheckout', apply_filters( 'lifterlms_set_express_checkout_params', $param ) );
	}

	/**
	 * Record the output of print_r for returning in a debug message
	 * @param  mixed $var a variable to print_r
	 * @return string
	 */
	public function sprint_r( $var ) {
		ob_start();
		print_r( $var );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}










	/**
	 * Query Active PayPal Subscriptions and check their status to ensure
	 * they're still active in PayPal
	 *
	 * Update statuses if status is not pending or active
	 *
	 * @param  int    $count   Optionally pass a number which determines how many orders to query
	 *                         This is used on the admin panel to force sync of an individual order
	 *                         in this scenario, the last sync is reset to 0
	 *                         and this function is called with a $count of 1 which
	 *                         ensures the order we're looking at will be synced immediately
	 * @return void
	 */
	public function sync_order_statuses( $count = null ) {

		// if gateway not enabled, skip
		if ( ! $this->is_available() ) {
			return;
		}

		// set the count if count supplied
		if ( $count ) {
			$this->orders_per_sync = $count;
		}

		global $wpdb;

		// query
		$orders = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				  p.ID AS id
				, m.meta_value AS profile_id
				, m2.meta_value AS last_sync
			 FROM {$wpdb->posts} AS p
			 JOIN {$wpdb->postmeta} AS m ON m.post_id = p.ID
			 LEFT JOIN {$wpdb->postmeta} AS m2 ON m2.post_id = p.ID
			 LEFT JOIN {$wpdb->postmeta} AS m3 ON m3.post_id = p.ID
			 WHERE p.post_status = 'llms-active'
			   AND m.meta_key = '_llms_subscription_id'
			   AND m2.meta_key = '_llms_subscription_last_sync'
			   AND m3.meta_key = '_llms_payment_gateway'
			   AND m3.meta_value = %s
			 ORDER BY last_sync ASC
			 LIMIT %d
			;",
			array( $this->id, $this->orders_per_sync )
		) );

		// loop through orders
		foreach( $orders as $order ) {

			// get details from API
			$res = $this->get_recurring_profile_details( $order->profile_id );

			// if successful
			if ( $res && isset( $res['ACK'] ) && 'Success' === $res['ACK'] ) {

				// update the last sync to now
				$order = new LLMS_Order( $order->id );
				$order->subscription_last_sync = current_time( 'timestamp' );

				// update the status
				if ( isset( $res['STATUS'] ) ) {

					switch( $res['STATUS'] ) {

						// leave it active
						case 'Active':
						// pending is before the transaction is complete
						// we can't really do much about this and paypal can be slow
						// to activate so we'll allow pending transactions access
						case 'Pending':
						break;

						case 'Expired':
						case 'Suspended':
							$order->update_status( 'expired' );
						break;

						case 'Cancelled':
							$order->update_status( 'cancelled' );
						break;

					}

				}

			}

		}

	}

}
