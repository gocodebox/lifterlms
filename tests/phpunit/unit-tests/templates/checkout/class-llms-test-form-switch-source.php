<?php
/**
 * Test Switch Source Form
 *
 * @package LifterLMS/Templates/Checkout
 *
 * @group templates
 * @group checkout
 * @group form-switch-source
 *
 * @since [version]
 */
class LLMS_Test_Form_Switch_Source extends LLMS_UnitTestCase {

	/**
	 * Test switch source form on recurring orders with pending payments and trial paid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_form_switch_source_recurring_with_paid_trial() {
		$plan = $this->get_mock_plan(
			25.99, // Price.
			3, // Frequency.
			'lifetime', // Expiration.
			false, // On sale.
			true // Trial
		);

		$order = $this->get_mock_order( $plan );
		// Store trial payment.
		$txn = $order->record_transaction( array(
			'amount' => 1,
			'status' => 'llms-txn-succeeded',
			'payment_type' => 'trial',
		) );

		$form = $this->get_output(
			'llms_get_template',
			array(
				'checkout/form-switch-source.php',
				array(
					'confirm' => '',
					'order'   => $order,
				)
			)
		);
		$this->assertStringContainsString(
			'Due Now: <span class="price-regular"><span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>25.99</span></span>',
			$form
		);
	}

	/**
	 * Test switch source form on recurring orders with pending payments and trial still to be paid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_form_switch_source_recurring_with_unpaid_trial() {
		$plan = $this->get_mock_plan(
			25.99, // Price.
			3, // Frequency.
			'lifetime', // Expiration.
			false, // On sale.
			true // Trial
		);

		$order = $this->get_mock_order( $plan );

		$form = $this->get_output(
			'llms_get_template',
			array(
				'checkout/form-switch-source.php',
				array(
					'confirm' => '',
					'order'   => $order,
				)
			)
		);
		$this->assertStringContainsString(
			'Due Now: <span class="price-regular price-trial"><span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>1.00</span></span>',
			$form
		);
	}

	/**
	 * Test switch source form on recurring orders with pending payments and no trials.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_form_switch_source_recurring_without_trial() {
		$plan = $this->get_mock_plan(
			25.99, // Price.
			3, // Frequency.
			'lifetime', // Expiration.
			false, // On sale.
			false // Trial
		);

		$order = $this->get_mock_order( $plan );

		$form = $this->get_output(
			'llms_get_template',
			array(
				'checkout/form-switch-source.php',
				array(
					'confirm' => '',
					'order'   => $order,
				)
			)
		);
		$this->assertStringContainsString(
			'Due Now: <span class="price-regular"><span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>25.99</span></span>',
			$form
		);
	}
}
