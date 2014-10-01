<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Payment Gateway class
*
* Class for managing payment gateways
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Payment_Gateway {

	var $id;

	var $title;

	var $chosen;

	var $enabled;

	//REFACTOR - stubbed out for paypal only. 
	public function is_available() {
		$is_available = true;
		return $is_available;
	}


    public function get() {
    	get_option( $option, $default );
    }
	
	public function set_current() {
		$this->chosen = true;
	}

	public function get_title() {
		return apply_filters( 'lifterlms_gateway_title', $this->title, $this->id );
	}

	public function process_payment($order) {
	}

	public function confirm_payment($response) {
	}

	public function complete_payment($order) {
	}

}