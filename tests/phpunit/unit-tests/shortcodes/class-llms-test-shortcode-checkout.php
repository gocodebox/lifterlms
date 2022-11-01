<?php
/**
 * Test the [lifterlms_checkout] Shortcode
 *
 * @group shortcodes
 *
 * @since 5.1.0
 */
class LLMS_Test_Shortcode_Checkout extends LLMS_ShortcodeTestCase {

	/**
	 * Test shortcode registration.
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_registration() {
		$this->assertTrue( shortcode_exists( 'lifterlms_checkout' ) );
	}

	/**
	 * Retrieves the output of {@see LLMS_Shortcode_Checkout::output}.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_shortcode_output() {
		return $this->get_output(
			array(
				'LLMS_Shortcode_Checkout',
				'output'
			),
			array( null )
		);
	}

	/**
	 * Determines if the checkout opening wrapper markup is found in the given
	 * output string.
	 *
	 * @since [version]
	 *
	 * @param string $output The output string.
	 */
	private function assertContainsOpeningWrapper( $output ) {
		$this->assertStringContainsString(
			'<div class="llms-checkout-wrapper">',
			$output
		);
	}

	/**
	 * Determines if the checkout closing wrapper markup is found in the given
	 * output string.
	 *
	 * @since [version]
	 *
	 * @param string $output The output string.
	 */
	private function assertContainsClosingWrapper( $output ) {
		$this->assertStringContainsString(
			'</div><!-- .llms-checkout-wrapper -->',
			$output
		);
	}

	/**
	 * Test clean_form_fields.
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_clean_form_fields() {

		$checks = array(
			'<p></p>'               => '',
			'<p>a</p>'              => '<p>a</p>',
			"\n"                    => '',
			"\t"                    => '',
			"\n\r\t"                => '',
			"<p></p>\n<p>a</p>\r\t" => "<p></p>\n<p>a</p>\r\t",
		);

		foreach ( $checks as $check => $expect ) {
			$this->assertEquals(
				$expect,
				LLMS_Unit_Test_Util::call_method(
					'LLMS_Shortcode_Checkout',
					'clean_form_fields',
					array( $check )
				),
				$check
			);
		}

	}

	/**
	 * Test setup_plan_and_form_atts() method adds redirection hidden field when needed.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_setup_plan_and_form_atts_check_redirection() {

		$plan      = $this->get_mock_plan();
		$contained = '<input class="llms-field-input" id="llms-redirect"'; // Redirect field.

		$atts = LLMS_Unit_Test_Util::call_method(
			'LLMS_Shortcode_Checkout',
			'setup_plan_and_form_atts',
			array(
				$plan->get('id'),
				array(),
			)
		);

		$this->assertStringNotContainsString( $contained, $atts['form_fields'] );

		// Setup a redirect URL for the access plan.
		$plan->set( 'checkout_redirect_type', 'url' );
		$plan->set( 'checkout_redirect_url', 'https://example.com' );
		$atts = LLMS_Unit_Test_Util::call_method(
			'LLMS_Shortcode_Checkout',
			'setup_plan_and_form_atts',
			array(
				$plan->get('id'),
				array(),
			)
		);
		$contained = '<input class="llms-field-input" id="llms-redirect" name="redirect" type="hidden" value="https://example.com" />';
		$this->assertStringContainsString( $contained, $atts['form_fields'] );

		// INPUT_GET wins over plan's setting.
		$this->mockGetRequest(
			array(
				'redirect' => 'https://example-redirect-get.com',
			)
		);
		$atts = LLMS_Unit_Test_Util::call_method(
			'LLMS_Shortcode_Checkout',
			'setup_plan_and_form_atts',
			array(
				$plan->get('id'),
				array(),
			)
		);
		$contained = '<input class="llms-field-input" id="llms-redirect" name="redirect" type="hidden" value="https://example-redirect-get.com" />';
		$this->assertStringContainsString( $contained, $atts['form_fields'] );
	}

	/**
	 * Test setup_plan_and_form_atts() method adds redirection hidden field when needed.
	 * Test checkout wrapper on empty cart.
	 *
	 * @since 7.0.0
	 * @since [version] Updated to use new test utility methods.
	 *
	 * @return void
	 */

	public function test_checkout_wrapper_on_empty_cart() {

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'Your cart is currently empty.',
			$output
		);
		$this->assertContainsOpeningWrapper( $output );
		$this->assertContainsClosingWrapper( $output );

	}

	/**
	 * Test checkout wrapper on pre checkout error.
	 *
	 * @since 7.0.0
	 * @since [version] Updated to use new test utility methods.
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_on_pre_checkout_error() {

		$pre_checkout_error = function() {
			return 'Pre checkout error.';
		};
		add_filter( 'lifterlms_pre_checkout_error', $pre_checkout_error );

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'Pre checkout error.',
			$output
		);
		$this->assertContainsOpeningWrapper( $output );
		$this->assertContainsClosingWrapper( $output );

		remove_filter( 'lifterlms_pre_checkout_error', $pre_checkout_error );

	}


	/**
	 * Test checkout wrapper when invalid access plan is supplied.
	 *
	 * @since 7.0.0
	 * @since [version] Updated to use new test utility methods.
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_confirm_payment_invalid_plan() {

		$this->mockGetRequest( array( 'plan' => $this->factory->post->create() ) );

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'Invalid access plan.',
			$output
		);
		$this->assertContainsOpeningWrapper( $output );
		$this->assertContainsClosingWrapper( $output );

	}

	/**
	 * Test checkout wrapper on confirm payment when no order is supplied.
	 *
	 * @since 7.0.0
	 * @since [version] Updated to use new test utility methods.
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_confirm_payment_no_order() {
		global $wp;
		$wpt = $wp;
		$wp->query_vars['confirm-payment'] = true;

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'Could not locate an order to confirm.',
			$output
		);
		$this->assertContainsOpeningWrapper( $output );
		$this->assertContainsClosingWrapper( $output );

		$wp = $wpt;

	}

	/**
	 * Tests {@see LLMS_Shortcode_Checkout::output} when confirming a payment
	 * for an invalid order.
	 *
	 * @since [version]
	 */
	public function test_output_confirm_payment_invalid_order() {

		global $wp;
		$wpt = $wp;
		$wp->query_vars['confirm-payment'] = true;

		// Fake order.
		$this->mockGetRequest( array(
			'order' => 'order-' . wp_generate_password( 32, false ),
		) );

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'Could not locate an order to confirm.',
			$output
		);
		$this->assertContainsOpeningWrapper( $output );
		$this->assertContainsClosingWrapper( $output );

		$wp = $wpt;

	}

	/**
	 * Tests {@see LLMS_Shortcode_Checkout::output} when confirming a payment
	 * for an invalid order.
	 *
	 * @since [version]
	 */
	public function test_output_confirm_payment_real_order() {

		global $wp;
		$wpt = $wp;
		$wp->query_vars['confirm-payment'] = true;

		$order = $this->factory->order->create_and_get();

		// Fake order.
		$this->mockGetRequest( array(
			'order' => $order->get( 'order_key' ),
		) );

		$output = $this->get_shortcode_output();
		$this->assertStringContainsString(
			'id="llms-product-purchase-confirm-form"',
			$output
		);

		$wp = $wpt;

	}

}
