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

		$available = get_option('lifterlms_gateway_enable_' . $this->id);
		if ($available == 'yes') {
			return true;
		}
		else {
			return false;
		}
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
    public function validate_card() {

    }

	public function process_payment($order) {
	}

	public function confirm_payment($response) {
	}

	public function complete_payment($request, $order) {
	}

	public function update_order() {
    }

    public function get_accepted_cards() {
    }

    public function get_exp_months() {

    	$months = array(
    		0 => '01',
    		1 => '02',
    		2 => '03',
    		3 => '04',
    		4 => '05',
    		5 => '06',
    		6 => '07',
    		7 => '08',
    		8 => '09',
    		9 => '10',
    		10 => '11',
    		11 => '12',
    	);
    	return $months;
    }

    public function get_exp_years() {
    	$years = array();
    	$current_year = date("Y");

    	for ($i = 0; $i < 10; $i++) {
    		$years[$i] = ($current_year);
    		$current_year++;
    	}
    	return $years;
    }

}