<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Payment Gateway class
*
* Class for managing payment gateways
*/
class LLMS_Payment_Gateway {

	/**
	 * Payment gateway id
	 * @var int
	 */
	var $id;

	/**
	 * Title
	 * @var string
	 */
	var $title;

	/**
	 * Chosen payment gateway
	 * @var int
	 */
	var $chosen;

	/**
	 * Gateway enabled check
	 * @var bool
	 */
	var $enabled;

	/**
	 * Checks if payment gateway is enabled
	 * REFACTOR - stubbed out for paypal only.
	 *
	 * @return boolean [description]
	 */
	public function is_available() {

		$available = get_option( 'lifterlms_gateway_enable_' . $this->id );
		if ($available == 'yes') {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Get Payment Gateway
	 * @return void
	 */
	public function get() {
		get_option( $option, $default );
	}

	/**
	 * Set current chosen payment gateway
	 *
	 * @return bool [is payment gateway chosen]
	 */
	public function set_current() {
		$this->chosen = true;
	}

	/**
	 * Get title
	 * @return string [title of the payemnt gateway for display]
	 */
	public function get_title() {
		return apply_filters( 'lifterlms_gateway_title', $this->title, $this->id );
	}

	/**
	 * Validate Credit Card
	 * Overridable by child classes
	 *
	 * @return bool [If card valid]
	 */
	public function validate_card() {

	}

	/**
	 * Process payment
	 * Overridable by child class
	 *
	 * @param  object $order [Order data object]
	 *
	 * @return void
	 */
	public function process_payment( $order ) {
	}

	/**
	 * Confirm Payment
	 * Overridable by child class
	 *
	 * @param  array $response [array of payment gateway process payment response]
	 *
	 * @return void
	 */
	public function confirm_payment( $response ) {
	}

	/**
	 * Complete Payment
	 * Overridable by child class
	 *
	 * @param  array $request [Payment gateway API request array]
	 * @param  object $order   [order data object]
	 *
	 * @return [type]          [description]
	 */
	public function complete_payment( $request, $order ) {
	}

	/**
	 * Update Order
	 * Overridable by child class
	 *
	 * @return void
	 */
	public function update_order() {
	}

	/**
	 * Get Cards available for processing
	 * Overridable by child class
	 *
	 * @return array [card ids available for payment gateway]
	 */
	public function get_accepted_cards() {
	}

	/**
	 * Get CC Expirations Months
	 * Overridable by child class
	 *
	 * @return array $months [exp months]
	 */
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

	/**
	 * Get CC Expirations Years
	 * @return array $years [array of years. Next 10 years]
	 */
	public function get_exp_years() {
		$years = array();
		$current_year = date( 'Y' );

		for ($i = 0; $i < 10; $i++) {
			$years[ $i ] = ($current_year);
			$current_year++;
		}
		return $years;
	}

}
