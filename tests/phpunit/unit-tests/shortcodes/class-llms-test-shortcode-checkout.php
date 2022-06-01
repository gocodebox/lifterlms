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

	/**
	 * Tests the is_checkout_form_displayable() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_is_checkout_form_displayable() {

		// Set up.
		$course     = $this->factory->course->create_and_get();
		$membership = $this->factory->membership->create_and_get();
		$product    = $course->get_product();
		$plan       = new LLMS_Access_Plan( 'new' );
		$plan->set( 'product_id', $course->get( 'id' ) );
		$student = $this->factory->student->create_and_get();
		$args    = array( $plan, &$product, $student->get( 'id' ) );
		llms_clear_notices();

		// Checkout form can be displayed.
		$this->assertTrue(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'is_checkout_form_displayable', $args )
		);
		$this->assertEquals( 0, llms_notice_count() );

		// User is enrolled.
		$student->enroll( $course->get( 'id' ) );
		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'is_checkout_form_displayable', $args )
		);
		$this->assertEquals( 1, llms_notice_count() );
		llms_clear_notices();
		$student->delete_enrollment( $course->get( 'id' ) );

		// Access plan's product does not exist.
		$product = new LLMS_Product( 0 );
		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'is_checkout_form_displayable', $args )
		);
		$this->assertEquals( 1, llms_notice_count() );
		llms_clear_notices();
		$product->set( 'id', $course->get( 'id' ) );

		// User not in required membership.
		$plan->set( 'availability', 'members' );
		$plan->set( 'availability_restrictions', array( $membership->get( 'id' ) ) );
		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'is_checkout_form_displayable', $args )
		);
		$this->assertEquals( 1, llms_notice_count() );
		llms_clear_notices();
		$plan->set( 'availability', '' );
		$plan->set( 'availability_restrictions', array() );

		// Enrollment is restricted.
		$course->set( 'enrollment_period', 'yes' );
		$course->set( 'enrollment_start_date', wp_date( 'Y-m-d H:i:s', strtotime( 'tomorrow' ) ) );
		$this->assertFalse(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Shortcode_Checkout', 'is_checkout_form_displayable', $args )
		);
		$this->assertEquals( 1, llms_notice_count() );
		llms_clear_notices();
		$course->set( 'enrollment_period', 'no' );
		$course->set( 'enrollment_start_date', '' );
	}
}
