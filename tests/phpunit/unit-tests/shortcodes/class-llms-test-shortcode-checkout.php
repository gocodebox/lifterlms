<?php
/**
 * Test the [lifterlms_checkout] shortcode.
 *
 * @group shortcodes
 *
 * @since 5.1.0
 */
class LLMS_Test_Shortcode_Checkout extends LLMS_ShortcodeTestCase {

	/**
	 * Tests the does_product_not_purchasable_template_print_error_notices() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_does_product_not_purchasable_template_print_error_notices() {

		$course  = $this->factory->course->create_and_get();
		$product = new LLMS_Product( $course );
		ob_start();

		// Course is purchasable.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method(
			'LLMS_Shortcode_Checkout',
			'does_product_not_purchasable_template_print_error_notices',
			array( $product )
		) );

		// Course is not purchasable.
		$course->set( 'enrollment_end_date', '2022-05-04' );
		$course->set( 'enrollment_period', 'yes' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method(
			'LLMS_Shortcode_Checkout',
			'does_product_not_purchasable_template_print_error_notices',
			array( $product )
		) );

		ob_end_clean(); // Do not output the template.
	}

	/**
	 * Test shortcode registration
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_registration() {
		$this->assertTrue( shortcode_exists( 'lifterlms_checkout' ) );
	}

	/**
	 * Test clean_form_fields
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
			$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'clean_form_fields', array( $check ) ), $check );
		}

	}
}
