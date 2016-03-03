<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* PayPal Payment Gateway class
*
* Class for managing Paypal API transactions
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Payment_Gateway_Paypal extends LLMS_Payment_Gateway {

	/**
	 * PayPal API Version
	 * @string
	 */
	public $version;

	/**
	 * PayPal account username
	 * @string
	 */
	public $user;

	/**
	 * PayPal account password
	 * @string
	 */
	public $password;

	/**
	 * PayPal account signature
	 * @string
	 */
	public $signature;

	/**
	 * Period of time (in seconds) after which the connection ends
	 * @integer
	 */
	public $time_out = 60;

	/**
	 * Requires SSL Verification
	 * @boolean
	 */
	public $ssl_verify;

	/**
	 * PayPal API Server
	 * @string
	 */
	private $server;

	/**
	 * PayPal API Redirect URL
	 * @string
	 */
	private $redirect_url;

	/**
	 * Real world PayPal API Server
	 * @string
	 */
	private $real_server = 'https://api-3t.paypal.com/nvp';

	/**
	 * Read world PayPal redirect URL
	 * @string
	 */
	private $real_redirect_url = 'https://www.paypal.com/cgi-bin/webscr';

	/**
	 * Sandbox PayPal Server
	 * @string
	 */
	private $sandbox_server = 'https://api-3t.sandbox.paypal.com/nvp';

	/**
	 * Sandbox PayPal redirect URL
	 * @string
	 */
	private $sandbox_redirect_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

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
	 *Can be set for debugging. print_r()
	 */
	public $debug_info = array();

	/**
	 * Saves the full response once a request succeed
	 * @mixed
	 */
	public $full_response = false;

	private $is_debug = false;

	/**
	 * Creates a new PayPal gateway object
	 * @param boolean $sandbox Set to true if you want to enable the Sandbox mode
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

		 // Set the Server and Redirect URL
		if ($this->sandbox) {
			$this->server = $this->sandbox_server;
			$this->redirect_url = $this->sandbox_redirect_url;
		} else {
			$this->server = $this->real_server;
			$this->redirect_url = $this->real_redirect_url;
		}

	}

	private function get_debug_status() {

		$debug = get_option( 'lifterlms_gateways_paypal_enable_debug', 'no' );

		return strcmp( $debug, 'yes' ) === 0 ? true : false;
	}

	/**
	 * Process method.
	 * @param boolean $sandbox Set to true if you want to enable the Sandbox mode
	 */
	public function process_payment( $order ) {

		// Create a new PayPal class instance, and set the sandbox mode to true
		// $paypal = new LLMS_Payment_Gateway_Paypal ();

		//apply coupon to order total
		$coupon = LLMS()->session->get( 'llms_coupon' );
		if ( $coupon ) {
			$product = new LLMS_Product( $order->product_id );
			$order->total = ( $order->total - $product->get_coupon_discount_total( $order->total ) );

			if ( $order->payment_option == 'recurring' ) {
				if ($coupon->type == 'percent') {
					$order->first_payment = ( $order->first_payment - $product->get_coupon_discount_total( $order->first_payment ) );
					$order->product_price = ( $order->product_price - $product->get_coupon_discount_total( $order->product_price ) );
				} else {
					return llms_add_notice( __( 'You cannot apply dollar based discounts to recurring orders.', 'lifterlms' ) );
				}
			} elseif ( $order->payment_option == 'single' ) {
				$order->product_price = ( $order->product_price - $product->get_coupon_discount_total( $order->product_price ) );
			}
		}

		if ( $order->payment_option == 'single' ) {

			$param = array(
				'amount' => $order->total,
				'currency_code' => $order->currency,
				'return_url' => $order->return_url,
				'cancel_url' => $order->cancel_url,
				'product_name' => $order->product_title,
				'product_sku' => $order->product_sku,
				'product_price' => $order->product_price,
			);
		}

		if ( $order->payment_option == 'recurring' ) {

			$param = array(
				'amount' => $order->first_payment,
				'recurring_amount' => $order->product_price,
				'currency_code' => $order->currency,
				'return_url' => $order->return_url,
				'cancel_url' => $order->cancel_url,
				'product_name' => $order->product_title,
				'product_sku' => $order->product_sku,
				'product_price' => $order->first_payment,
				//'item_category' => 'Digital',
				'billing_type' => 'RecurringPayments',
				'billing_agreement_desc' => trim( $order->product_title ),
				'profile_start_date' => $order->billing_start_date,
				'billing_period' => $order->billing_period,
				'billing_freq' => $order->billing_freq,
				'total_billing_cycles' => $order->billing_freq,
				'max_failed_payments' => '1',
				'total_billing_cycles' => $order->billing_cycle,
			);
		}

		if ($this->setExpressCheckout( $param )) {

			$redirect_url = $this->getRedirectURL();
			if ($redirect_url) {
				do_action( 'lifterlms_order_process_begin', $redirect_url );
			} else {
				return $this->return_error( 'There was an error connecting to the payment gateway.' );
			}

		} else {
			return $this->return_error( 'There was an error connecting to the payment gateway.' );
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

		//$paypal = new LLMS_Payment_Gateway_Paypal ();

		$param = array(
			'token' => $response['token'],
		);
		if ($this->getExpressCheckout( $param )) {

			return $this->getResponse();
		} else {
			return $this->return_error( 'There was an error connecting to the payment gateway.' );
		}

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
	 */
	public function complete_payment( $request, $order ) {
		//$this = new LLMS_Payment_Gateway_Paypal ();

		if ($order->payment_option == 'recurring' ) {

			$billing_period = $this->get_billing_period( $order->billing_period );

			//only do initial billing if 1st payment is not 0
			if ($request['AMT'] > 0) {

				$init_param = array(
					'amount' => $request['AMT'],
					'currency_code' => $request['CURRENCYCODE'],
					'payer_id' => $request['PAYERID'],
					'token' => $request['TOKEN'],
				);

				if ($this->doExpressCheckout( $init_param )) {

					$init_response = $this->getResponse();

					if ( ! $init_response || $init_response['ACK'] === 'Failure') {
						return $this->return_error( 'There was an error connecting to the payment gateway.' );
					}

				} else {
					return $this->return_error( 'There was an error connecting to the payment gateway.' );
				}
			}

			$coupon = LLMS()->session->get( 'llms_coupon' );
			if ( $coupon ) {
				$product = new LLMS_Product( $order->product_id );

				if ( $order->payment_option == 'recurring' ) {
					if ($coupon->type == 'percent') {
						$order->product_price = ( $order->product_price - $product->get_coupon_discount_total( $order->product_price ) );
					}
				}
			}

			$param = array(
				'profile_start_date' => strtotime( $order->billing_start_date ),
				'description' => trim( $order->product_title ),
				'billing_period' => $billing_period,
				'billing_freq' => $order->billing_freq,
				'recurring_amount' => $order->product_price,
				'currency_code' => $request['CURRENCYCODE'],
				'token' => $request['TOKEN'],
				'payer_id' => $request['PAYERID'],
				'total_billing_cycles' => $order->billing_cycle,
				'max_failed_payments' => '1',
			);

			if ($this->createRecurringPaymentsProfile( $param )) {
				$response = $this->getResponse();

				if ( ! $response || $response['ACK'] === 'Failure') {
					return $this->return_error( 'There was an error connecting to the payment gateway.' );
				}
			} else {
				return $this->return_error( 'There was an error connecting to the payment gateway.' );
			}
		}

		if ( $order->payment_option == 'single' && strcmp( $request['ACK'], 'Failure' ) !== 0) {

			$param = array(
				'amount' => $request['AMT'],
				'currency_code' => $request['CURRENCYCODE'],
				'payer_id' => $request['PAYERID'],
				'token' => $request['TOKEN'],
			);

			if ($this->doExpressCheckout( $param )) {

				$response = $this->getResponse();

				if ( ! $response || $response['ACK'] === 'Failure') {
					return $this->return_error( 'There was an error connecting to the payment gateway.' );
				}

			} else {
				return $this->return_error( 'There was an error connecting to the payment gateway.' );
			}
		}

		if (isset( $response ) && $response['ACK'] == 'Success') {

			$lifterlms_checkout = LLMS()->checkout();
			$result = $lifterlms_checkout->update_order( $order );

			if ( $order->payment_option == 'recurring' ) {
				update_post_meta( $result,'_llms_order_paypal_profile_id', $response['PROFILEID'] );
			}

			do_action( 'lifterlms_order_process_success', $order );
		} else {

			do_action( 'lifterlms_order_process_error', $order->user_id );
			return $this->return_error( 'There was an error connecting to the payment gateway.' );
		}

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
	 * Executes a setExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function setExpressCheckout( $param ) {
		return $this->requestExpressCheckout( 'SetExpressCheckout', $param );
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
	 * Executes a doExpressCheckout command
	 * @param array $param
	 * @return boolean
	 */
	public function doExpressCheckout( $param ) {
		return $this->requestExpressCheckout( 'DoExpressCheckoutPayment', $param );
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

	public function return_error( $message ) {

		if ($this->is_debug) {
			return llms_add_notice( $this->outputError( $this->debug_info ) );
		} else {
			return llms_add_notice( $message, 'error' );
		}
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

	public static function is_cli() {
		return (PHP_SAPI == 'cli' && empty( $_SERVER['REMOTE_ADDR'] ));
	}

	/**
	 * Outputs Error messages
	 * @param  [obj] $XeroOAuth [Xero API call object]
	 * @return [obj]            [Prints errors using pr method]
	 */
	public function outputError( $debug_info ) {

		return 'Paypal Gateway Error: ' . $this->pr( $debug_info ) . PHP_EOL;

	}

	public function sprint_r( $var ) {
		ob_start();
		print_r( $var );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}
