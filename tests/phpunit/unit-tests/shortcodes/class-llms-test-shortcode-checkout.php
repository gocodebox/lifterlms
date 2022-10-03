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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */

	public function test_checkout_wrapper_on_empty_cart() {

		$this->assertOutputContains(
			'Your cart is currently empty.',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'<div class="llms-checkout-wrapper">',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'</div><!-- .llms-checkout-wrapper -->',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

	}

	/**
	 * Test checkout wrapper on pre checkout error.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_on_pre_checkout_error() {

		$pre_checkout_error = function() {
			return 'Pre checkout error.';
		};
		add_filter( 'lifterlms_pre_checkout_error', $pre_checkout_error );

		$this->assertOutputContains(
			'Pre checkout error.',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'<div class="llms-checkout-wrapper">',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'</div><!-- .llms-checkout-wrapper -->',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		remove_filter( 'lifterlms_pre_checkout_error', $pre_checkout_error );

	}


	/**
	 * Test checkout wrapper when invalid access plan is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_confirm_payment_invalid_plan() {

		$this->mockGetRequest( array( 'plan' => $this->factory->post->create() ) );

		$this->assertOutputContains(
			'Invalid access plan.',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'<div class="llms-checkout-wrapper">',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'</div><!-- .llms-checkout-wrapper -->',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

	}

	/**
	 * Test checkout wrapper on confirm payment when no order is supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_checkout_wrapper_confirm_payment_no_order() {
		global $wp;
		$wpt = $wp;
		$wp->query_vars['confirm-payment'] = true;

		$this->assertOutputContains(
			'Could not locate an order to confirm.',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'<div class="llms-checkout-wrapper">',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$this->assertOutputContains(
			'</div><!-- .llms-checkout-wrapper -->',
			array(
				'LLMS_Shortcode_Checkout',
				'output',
				array( null )
			)
		);

		$wp = $wpt;

	}

}
