<?php
/**
 * Test product pricing table template functions
 *
 * @package  LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_template
 * @group functions_template_product
 *
 * @since 3.38.0
 */
class LLMS_Test_Functions_Templates_Pricing_Table extends LLMS_UnitTestCase {

	// Uncovered methods.

	// public function test_llms_get_access_plan_classes() {}
	// public function test_llms_template_access_plan() {}
	// public function test_llms_template_access_plan_button() {}
	// public function test_llms_template_access_plan_description() {}
	// public function test_llms_template_access_plan_feature() {}
	// public function test_llms_template_access_plan_pricing() {}
	// public function test_llms_template_access_plan_restrictions() {}
	// public function test_llms_template_access_plan_title() {}
	// public function test_llms_template_access_plan_trial() {}


	/**
	 * Test lifterlms_template_pricing_table(): gateways disabled so we should show only free plans.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_free_only() {

		$this->setManualGatewayStatus( 'no' );

		$plan = $this->get_mock_plan( 0 );

		$actions = did_action( 'llms_access_plan' );

		$output = $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) );

		// Check HTML output.
		$this->assertStringContains( sprintf( 'id="llms-access-plan-%d"', $plan->get( 'id' ) ), $output );
		$this->assertStringContains( sprintf( '<h4 class="llms-access-plan-title">%s</h4>', $plan->get( 'title' ) ), $output );
		$this->assertStringContains( 'FREE', $output );

		// Action ran.
		$this->assertEquals( ++$actions, did_action( 'llms_access_plan' ) );

	}

	/**
	 * Test lifterlms_template_pricing_table(): paid plan with gateways enabled.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_purchasable() {

		$this->setManualGatewayStatus( 'yes' );

		$plan = $this->get_mock_plan();

		$actions = did_action( 'llms_access_plan' );

		$output = $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) );

		// Check HTML output.
		$this->assertStringContains( sprintf( 'id="llms-access-plan-%d"', $plan->get( 'id' ) ), $output );
		$this->assertStringContains( sprintf( '<h4 class="llms-access-plan-title">%s</h4>', $plan->get( 'title' ) ), $output );
		$this->assertStringContains( (string) $plan->get( 'price' ), $output );

		// Action ran.
		$this->assertEquals( ++$actions, did_action( 'llms_access_plan' ) );

		$this->setManualGatewayStatus( 'no' );

	}

	/**
	 * Test lifterlms_template_pricing_table(): paid plan with no enabled gateways.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_not_purchasable() {

		$this->setManualGatewayStatus( 'no' );

		$plan = $this->get_mock_plan();

		$actions = did_action( 'lifterlms_product_not_purchasable' );

		// Empty output (just a bunch of new lines, actually).
		$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

		// Action ran.
		$this->assertEquals( ++$actions, did_action( 'lifterlms_product_not_purchasable' ) );

		$this->assertEquals( '', $output );

	}

	/**
	 * Test lifterlms_template_pricing_table(): course enrollment start is in future.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_err_enrollment_period_starts_in_future() {

		$plans = array(
			$this->get_mock_plan(),
			$this->get_mock_plan( 0 ),
		);

		foreach ( $plans as $plan ) {

			$course = llms_get_post( $plan->get( 'product_id' ) );
			$course->set( 'enrollment_period', 'yes' );
			$course->set( 'enrollment_start_date', date( 'Y-m-d h:i:s', strtotime( '+1 day' ) ) );
			$course->set( 'enrollment_opens_message', 'Enrollment closed.' );

			$actions = did_action( 'lifterlms_product_not_purchasable' );

			$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

			// Test HTML output.
			$this->assertStringContains( 'class="llms-notice llms-error"', $output );
			$this->assertStringContains( 'Enrollment closed.', $output );

			// Action ran.
			$this->assertEquals( ++$actions, did_action( 'lifterlms_product_not_purchasable' ) );

		}

	}

	/**
	 * Test lifterlms_template_pricing_table(): course enrollment start is in past.
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_err_enrollment_period_starts_in_past() {

		$plans = array(
			$this->get_mock_plan(),
			$this->get_mock_plan( 0 ),
		);

		foreach ( $plans as $plan ) {

			$course = llms_get_post( $plan->get( 'product_id' ) );
			$course->set( 'enrollment_period', 'yes' );
			$course->set( 'enrollment_end_date', date( 'Y-m-d h:i:s', strtotime( '-1 day' ) ) );
			$course->set( 'enrollment_closed_message', 'Enrollment closed.' );

			$actions = did_action( 'lifterlms_product_not_purchasable' );

			$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

			// Test HTML output.
			$this->assertStringContains( 'class="llms-notice llms-error"', $output );
			$this->assertStringContains( 'Enrollment closed.', $output );

			// Action ran.
			$this->assertEquals( ++$actions, did_action( 'lifterlms_product_not_purchasable' ) );

		}

	}

	/**
	 * Test lifterlms_template_pricing_table(): course capacity maxed error
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_err_capacity() {

		$plans = array(
			$this->get_mock_plan(),
			$this->get_mock_plan( 0 ),
		);

		foreach ( $plans as $plan ) {
			$course = llms_get_post( $plan->get( 'product_id' ) );
			$course->set( 'enable_capacity', 'yes' );
			$course->set( 'capacity', 1 );
			$course->set( 'capacity_message', 'No more room.' );

			$student = $this->get_mock_student();
			$student->enroll( $course->get( 'id' ) );

			$actions = did_action( 'lifterlms_product_not_purchasable' );

			$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

			// Test HTML output.
			$this->assertStringContains( 'class="llms-notice llms-error"', $output );
			$this->assertStringContains( 'No more room.', $output );

			// Action ran.
			$this->assertEquals( ++$actions, did_action( 'lifterlms_product_not_purchasable' ) );
		}

	}

	/**
	 * Test lifterlms_template_pricing_table(): user already enrolled in course
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_enrolled_course() {

		$plan    = $this->get_mock_plan();
		$course  = llms_get_post( $plan->get( 'product_id' ) );
		$student = $this->get_mock_student();

		$student->enroll( $course->get( 'id' ) );
		wp_set_current_user( $student->get( 'id' ) );

		// Empty output (just a bunch of new lines, actually).
		$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

		// No actions ran.
		$this->assertEquals( 0, did_action( 'lifterlms_product_not_purchasable' ) );
		$this->assertEquals( 0, did_action( 'llms_access_plan' ) );

		$this->assertEquals( '', $output );


	}

	/**
	 * Test lifterlms_template_pricing_table(): user already enrolled in membership
	 *
	 * @since 3.38.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_pricing_table_enrolled_membership() {

		$plan          = $this->get_mock_plan();
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$student       = $this->get_mock_student();

		$plan->set( 'product_id', $membership_id );
		$student->enroll( $membership_id );
		wp_set_current_user( $student->get( 'id' ) );

		// Empty output (just a bunch of new lines, actually).
		$output = trim( $this->get_output( 'lifterlms_template_pricing_table', array( $plan->get( 'product_id' ) ) ) );

		// No actions ran.
		$this->assertEquals( 0, did_action( 'lifterlms_product_not_purchasable' ) );
		$this->assertEquals( 0, did_action( 'llms_access_plan' ) );

		$this->assertEquals( '', $output );

	}

}
