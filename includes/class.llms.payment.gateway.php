<?php
/**
* Payment Gateway class
*
* Class for managing payment gateways
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Payment_Gateway {

	/**
	 * Chosen payment gateway
	 * @var int
	 */
	public $chosen;

	/**
	 * Gateway enabled check
	 * @var bool
	 */
	public $enabled;

	/**
	 * Payment gateway id
	 * @var int
	 */
	public $id;

	/**
	 * List of features the gateway supports
	 * @var array
	 * @since 3.0.0
	 */
	public $supports = array(
		'refunds' => false,
		'recurring' => false,
		'recurring_sync' => false,
	);

	/**
	 * Title
	 * @var string
	 */
	public $title;

	/**
	 * Checks if payment gateway is enabled
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
	 * Get the Payment Gateway's ID
	 * @return string
	 *
	 * @since  3.0.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get title
	 * @return string [title of the payemnt gateway for display]
	 */
	public function get_title() {
		return apply_filters( 'lifterlms_gateway_title', $this->title, $this->id );
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
	public function complete_payment( $request, $order ) {}

	/**
	 * Confirm Payment
	 * Overridable by child class
	 *
	 * @param  array $response [array of payment gateway process payment response]
	 *
	 * @return void
	 */
	public function confirm_payment( $response ) {}

	/**
	 * This should return a URL that links to the specificed customer on the Gateway's website
	 *
	 * If the gateway doesn't implement a custom URL function simply return the customer_id
	 *
	 * @param  string $customer_id   Customer ID
	 * @return string
	 *
	 * @since  3.0.0
	 */
	public function get_customer_url( $customer_id ) {
		return $customer_id;
	}

	/**
	 * This should return a URL that links to the subscription on the Gateway's website
	 *
	 * If the gateway doesn't implement a custom URL function simply return the transaction_id
	 *
	 * @param  string $transaction_id   Transaction ID
	 * @return string
	 * @since  3.0.0
	 */
	public function get_subscription_url( $subscription_id ) {
		return $subscription_id;
	}

	/**
	 * This should return a URL that links to the transaction on the Gateway's website
	 *
	 * If the gateway doesn't implement a custom URL function simply return the transaction_id
	 *
	 * @param  string $transaction_id   Transaction ID
	 * @return string
	 * @since  3.0.0
	 */
	public function get_transaction_url( $transaction_id ) {
		return $transaction_id;
	}

	/**
	 * Process payment
	 * Overridable by child class
	 *
	 * @param  object $order [Order data object]
	 *
	 * @return void
	 */
	public function process_payment( $order ) {}

	/**
	 * Set current chosen payment gateway
	 *
	 * @return bool [is payment gateway chosen]
	 */
	public function set_current() {
		$this->chosen = true;
	}

	/**
	 * Determine whether or not the gateway supports a particular feature
	 *
	 * @see  $this->supports
	 *
	 * @param  string $feature key of the feature
	 * @return bool
	 *
	 * @since  3.0.0
	 */
	public function supports( $feature ) {
		if ( isset( $this->supports[$feature] ) ) {
			return $this->supports[$feature];
		}
		return false;
	}

	/**
	 * Update Order
	 * Overridable by child class
	 *
	 * @return void
	 */
	public function update_order() {}





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

	/**
	 * Get Cards available for processing
	 * Overridable by child class
	 *
	 * @return array [card ids available for payment gateway]
	 */
	public function get_accepted_cards() {}

	/**
	 * Validate Credit Card
	 * Overridable by child classes
	 *
	 * @return bool [If card valid]
	 */
	public function validate_card() {}

}
