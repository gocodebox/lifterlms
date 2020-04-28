<?php
/**
 * Tests for LifterLMS Product Model
 *
 * @package LifterLMS/Tests/Models
 *
 * @group LLMS_Product
 * @group LLMS_Post_Model
 *
 * @since 3.25.2
 * @since 3.37.12 Create a stub for the test_create_method() since this class doesn't need to test that.
 * @since [version] Add tests for the get_restrictions() and has_restrictions() methods.
 */
class LLMS_Test_LLMS_Product extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Product';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'product';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 This should match, exactly, the object's $properties array.
	 *
	 * @since 3.24.0
	 *
	 * @return array
	 */
	protected function get_properties() {
		return array();
	}

	/**
	 * Get data used to create the mock post.
	 *
	 * This is used by test_getters_setters().
	 *
	 * @since 3.24.0
	 * @since [version] Added fake data to ensure abstracts pass instead of being marked as skipped.
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'fake'  => 'fake', // This makes it so that abstracts that don't technically apply to this product will pass.
		);
	}

	private function add_plan( $product, $data = array() ) {

		$data = wp_parse_args( $data, array(
			'title' => 'mock plan',
			'is_free' => 'no',
			'price' => 100.00,
			'frequency' => 0,
			'visibility' => 'visible',
		) );

		$plan = new LLMS_Access_Plan( 'new', $data['title'] );

		$plan->set( 'product_id', $product->get( 'id' ) );
		$plan->set_visibility( $data['visibility'] );

		unset( $data['title'] );
		unset( $data['visibility'] );

		foreach ( $plan->get_properties() as $prop => $type ) {

			if ( array_key_exists( $prop, $data ) ) {
				$plan->set( $prop, $data[ $prop ] );
			} elseif ( 'yesno' === $type ) {
				$plan->set( $prop, 'no' );
			}
		}

		return $plan;

	}

	private function get_product() {

		$product = new LLMS_Product( $this->factory->post->create( array( 'post_type' => 'course' ) ) );
		return $product;

	}

	/**
	 * Override parent test
	 *
	 * This model has no properties of it's own so we can safely skip this test
	 * without outputting a warning.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_getters_setters() {
		$this->assertTrue( true );
	}


	/**
	 * Overwrite parent class method that tests model creation.
	 *
	 * This model shouldn't be created, instead the `LLMS_Course` or `LLMS_Membership` classes are used to create products.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_create_model() {

		$this->assertTrue( true );
	}


	/**
	 * test get_access_plan_limit() method
	 * @return  void
	 * @since   3.25.2
	 * @version 3.25.2
	 */
	public function test_get_access_plan_limit() {

		$product = $this->get_product();

		$this->assertTrue( is_int( $product->get_access_plan_limit() ) );
		$this->assertEquals( 6, $product->get_access_plan_limit() );

		// Test the filter
		add_filter( 'llms_get_product_access_plan_limit', function() {
			return 3;
		} );
		$this->assertEquals( 3, $product->get_access_plan_limit() );

	}

	public function test_get_access_plans() {

		$product = $this->get_product();

		// No plans.
		$this->assertEquals( 0, count( $product->get_access_plans() ) );

		// Add a plan.
		$this->add_plan( $product );
		// One plan returned.
		$this->assertEquals( 1, count( $product->get_access_plans() ) );

		// Add a free plan.
		$this->add_plan( $product, array( 'is_free' => 'yes' ) );
		// Two plans returned.
		$this->assertEquals( 2, count( $product->get_access_plans() ) );
		// Exclude free.
		$this->assertEquals( 1, count( $product->get_access_plans( true ) ) );

		// Add a hidden plan.
		$this->add_plan( $product, array( 'visibility' => 'hidden' ) );
		// Show all plans except hidden.
		$this->assertEquals( 2, count( $product->get_access_plans() ) );
		// Only show free & visible plans.
		$this->assertEquals( 1, count( $product->get_access_plans( true ) ) );
		// Show all plans.
		$this->assertEquals( 3, count( $product->get_access_plans( false, false ) ) );
		// Only show free (allow hidden plans).
		$this->assertEquals( 1, count( $product->get_access_plans( true, false ) ) );

	}

	/**
	 * Test get_restrictions(): no restrictions on product
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_restrictions_none() {

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		$this->assertEquals( array(), $product->get_restrictions() );

	}

	/**
	 * Test get_restrictions(): enrollment period
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_restrictions_period() {

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		$course->set( 'enrollment_period', 'yes' );
		$course->set( 'enrollment_start_date', date( 'Y-m-d h:i:s', strtotime( '+1 day' ) ) );
		$this->assertEquals( array( 'enrollment_period' ), $product->get_restrictions() );

	}

	/**
	 * Test get_restrictions(): max capacity
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_restrictions_capacity() {

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		$course->set( 'enable_capacity', 'yes' );
		$course->set( 'capacity', 1 );

		$this->assertEquals( array( 'student_capacity' ), $product->get_restrictions() );

	}

	/**
	 * Test get_restrictions(): multiple restrictions
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_restriction_multiple() {

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		// Enrollment period.
		$course->set( 'enrollment_period', 'yes' );
		$course->set( 'enrollment_start_date', date( 'Y-m-d h:i:s', strtotime( '+1 day' ) ) );
		$this->assertEquals( array( 'enrollment_period' ), $product->get_restrictions() );

		// No capacity.
		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		$course->set( 'enable_capacity', 'yes' );
		$course->set( 'capacity', 1 );

		$this->assertEquals( array( 'enrollment_period', 'student_capacity' ), $product->get_restrictions() );
	}

	/**
	 * test has_free_access_plan() method
	 * @return  void
	 * @since   3.25.2
	 * @version 3.25.2
	 */
	public function test_has_free_access_plan() {

		$product = $this->get_product();

		// Has no plans.
		$this->assertFalse( $product->has_free_access_plan() );

		// Has paid plan.
		$this->add_plan( $product );
		$this->assertFalse( $product->has_free_access_plan() );

		$this->add_plan( $product, array( 'is_free' => 'yes' ) );
		$this->assertTrue( $product->has_free_access_plan() );

	}

	/**
	 * Test the has_restrictions() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_has_restrictions() {

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		// No restrictions.
		$this->assertFalse( $product->has_restrictions() );

		// Add enrollment period restrictions.
		$course->set( 'enrollment_period', 'yes' );
		$course->set( 'enrollment_start_date', date( 'Y-m-d h:i:s', strtotime( '+1 day' ) ) );
		$this->assertEquals( array( 'enrollment_period' ), $product->get_restrictions() );

		$this->assertTrue( $product->has_restrictions() );

	}

	/**
	 * Test the is_purchasable() method
	 * @return  void
	 * @since   3.25.2
	 * @version 3.25.2
	 */
	public function test_is_purchasable() {

		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), 'no' );

		$product = $this->get_product();
		$course = llms_get_post( $product->get( 'id' ) );

		// Enrollment is closed.
		$course->set( 'enrollment_period', 'yes' );
		$course->set( 'enrollment_start_date', date( 'Y-m-d h:i:s', strtotime( '+1 day' ) ) );
		$this->assertFalse( $product->is_purchasable() );
		$course->set( 'enrollment_period', 'no' );

		// No capacity.
		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		$course->set( 'enable_capacity', 'yes' );
		$course->set( 'capacity', 1 );
		$this->assertFalse( $product->is_purchasable() );

		// Enrollment closed & no capacity.
		$course->set( 'enrollment_period', 'yes' );
		$this->assertFalse( $product->is_purchasable() );
		$course->set( 'enable_capacity', 'no' );
		$course->set( 'enrollment_period', 'no' );

		// No plans & no gateways.
		$this->assertFalse( $product->is_purchasable() );

		// Has a plan but no gateway.
		$plan = $this->add_plan( $product );
		$this->assertFalse( $product->is_purchasable() );

		// Has a plan and a gateway.
		update_option( $manual->get_option_name( 'enabled' ), 'yes' );
		$this->assertTrue( $product->is_purchasable() );

		// Only free plans.
		$plan->set( 'is_free', 'yes' );
		$this->assertTrue( $product->is_purchasable() );

		// No plans but has a gateway.
		wp_delete_post( $plan->get( 'id' ), true );
		$this->assertFalse( $product->is_purchasable() );

	}

}
