<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* PayPal Payment Gateway class
*
* Class for managing Paypal API transactions
*
* @version 1.0
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
        'profile_start_date' =>  'PROFILESTARTDATE',
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
    public $debug_info;
 
    /**
     * Saves the full response once a request succeed
     * @mixed
     */
    public $full_response = false;
 
    /**
     * Creates a new PayPal gateway object
     * @param boolean $sandbox Set to true if you want to enable the Sandbox mode
     */
    public function __construct() {
        // Set the SSL Verification
        $this->ssl_verify   = apply_filters('https_local_ssl_verify', false);

        $this->id           = 'paypal';
        $this->title        = 'PayPal';

        $this->version      = '117.00';

        $this->sandbox      = get_option( 'lifterlms_gateways_paypal_enable_sandbox' ) == 'yes' ? true : false; 
        $this->user         = get_option( 'lifterlms_gateways_paypal_email' ); 
        $this->password     = get_option( 'lifterlms_gateways_paypal_password' ); 
        $this->signature    = get_option( 'lifterlms_gateways_paypal_signature' ); 

         // Set the Server and Redirect URL
        if ($this->sandbox) {
            $this->server = $this->sandbox_server;
            $this->redirect_url = $this->sandbox_redirect_url;
        } else {
            $this->server = $this->real_server;
            $this->redirect_url = $this->real_redirect_url;
        }

    }

    /**
     * Process method. 
     * @param boolean $sandbox Set to true if you want to enable the Sandbox mode
     */
    public function process_payment($order) {

        // Create a new PayPal class instance, and set the sandbox mode to true
        $paypal = new LLMS_Payment_Gateway_Paypal ();



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
                'recurring_amount' => $order->total,
                'currency_code' => $order->currency,
                'return_url' => $order->return_url,
                'cancel_url' => $order->cancel_url,
                'product_name' => $order->product_title,
                'product_sku' => $order->product_sku,
                'product_price' => $order->first_payment,
                //'item_category' => 'Digital',
                'billing_type' => 'RecurringPayments',
                'billing_agreement_desc' => trim($order->product_title),
                'profile_start_date' => $order->billing_start_date,
                'billing_period' => $order->billing_period,
                'billing_freq' => $order->billing_freq,
                'total_billing_cycles' => $order->billing_freq,
                'max_failed_payments' => '1',
                'total_billing_cycles' => $order->billing_cycle
            );
        }

        if ($paypal->setExpressCheckout($param)) {

            $redirect_url = $paypal->getRedirectURL();
            do_action( 'lifterlms_order_process_begin', $redirect_url );

        } 

    }

    public function confirm_payment($response) {

        $paypal = new LLMS_Payment_Gateway_Paypal ();

        $param = array(
            'token' => $response['token'],
        );
        if ($paypal->getExpressCheckout($param)) {

           return $paypal->getResponse();

        } 
         
    }

    public function complete_payment($request, $order) {
        $paypal = new LLMS_Payment_Gateway_Paypal ();

        if ($order->payment_option == 'recurring' ){

            $billing_period = $this->get_billing_period($order->billing_period);

            //only do initial billing if 1st payment is not 0
            if ($request['AMT'] > 0) {

                $init_param = array(
                    'amount' => $order->first_payment,
                    'currency_code' => $request['CURRENCYCODE'],
                    'payer_id' => $request['PAYERID'],
                    'token' => $request['TOKEN'],
                );

                if ($paypal->doExpressCheckout($init_param)) {
                    $init_response = $paypal->getResponse();

                }
            }

           // if ($init_response['ACK'] == 'Success' || $request['AMT'] <= 0) {

                $param = array(
                    'profile_start_date' => strtotime($order->billing_start_date),
                    'description' => trim($order->product_title),
                    'billing_period' => $billing_period,
                    'billing_freq' => $order->billing_freq,
                    'recurring_amount' => $order->total,
                    'currency_code' => $request['CURRENCYCODE'],
                    'token' => $request['TOKEN'],
                    'payer_id' => $request['PAYERID'],
                    'total_billing_cycles' => $order->billing_cycle,
                    'max_failed_payments' => '1',
                );

                if ($paypal->createRecurringPaymentsProfile($param)) {
                    $response = $paypal->getResponse();
                }
            //}

        }

        if ( $order->payment_option == 'single' ) {
      
            $param = array(
                'amount' => $request['AMT'],
                'currency_code' => $request['CURRENCYCODE'],
                'payer_id' => $request['PAYERID'],
                'token' => $request['TOKEN'],
            );

            if ($paypal->doExpressCheckout($param)) {
                $response = $paypal->getResponse();

            }
        }


        if ($response['ACK'] == 'Success') {

            $lifterlms_checkout = LLMS()->checkout();
            $result = $lifterlms_checkout->update_order($order);
            update_post_meta($result,'_llms_order_paypal_profile_id', $response['PROFILEID']);

            do_action( 'lifterlms_order_process_success', $order );
        }
        else {
       
            do_action( 'lifterlms_order_process_error', $order->user_id);
        }
        
    }

    public function update_order() {

    }

    public function get_billing_period($billing_period) {

        $paypal_codes = array (
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'year' => 'Year'
        );

        return $paypal_codes[$billing_period];


    }

    /**
     * Executes a setExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function setExpressCheckout($param) {
        return $this->requestExpressCheckout('SetExpressCheckout', $param);
    }
 
    /**
     * Executes a getExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function getExpressCheckout($param) {
        return $this->requestExpressCheckout('GetExpressCheckoutDetails', $param);
    }
 
    /**
     * Executes a doExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function doExpressCheckout($param) {
        return $this->requestExpressCheckout('DoExpressCheckoutPayment', $param);
    }

    /**
     * Executes a doExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function createRecurringPaymentsProfile($param) {
        return $this->requestExpressCheckout('CreateRecurringPaymentsProfile', $param);
    }
 
    /**
     * @param string $type
     * @param array $param
     * @return boolean Specifies if the request is successful and the response property
     *                 is filled
     */
    private function requestExpressCheckout($type, $param) {
        // Construct the request array
        $param = $this->replace_short_terms($param);

      
        $request = $this->build_request($type, $param);
 
        // Makes the HTTP request
        $response = wp_remote_post($this->server, $request);
 
        // HTTP Request fails
        if (is_wp_error($response)) {
            $this->debug_info = $response;
            return false;
        }
 
        // Status code returned other than 200
        if ($response['response']['code'] != 200) {
            $this->debug_info = 'Response code different than 200';
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
    private function replace_short_terms($param) {
        foreach ($this->short_term as $short_term => $long_term)
        {
            if (array_key_exists($short_term, $param)) {
                $param[$long_term] = $param[$short_term];
                unset($param[$short_term]);
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
    private function build_request($type, $param) {
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
            'method' => 'POST',
            'body' => $body,
            'timeout' => $this->time_out,
            'sslverify' => $this->ssl_verify
        );

        return $request;

    }
 
    /**
     * Returns the PayPal Body response
     * @return array $reponse
     */
    public function getResponse() {
        if ($this->full_response) {
            parse_str(urldecode($this->full_response['body']), $output);
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
                'token' => $output['TOKEN']
            );
            $url = $this->redirect_url . '?' . http_build_query($query_data);
            return $url;
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
        }
        return false;
    }

}