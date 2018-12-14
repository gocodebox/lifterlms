<?php
/**
 * Tests for LifterLMS Product Model
 * @group    LLMS_Product
 * @group    LLMS_Post_Model
 * @since    [version]
 * @version  [version]
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
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function get_properties() {
		return array();
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	protected function get_data() {
		return array();
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
	 * test get_access_plan_limit() method
	 * @return  void
	 * @since   [version]
	 * @version [version]
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
	 * test has_free_access_plan() method
	 * @return  void
	 * @since   [version]
	 * @version [version]
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
	 * Test the is_purchaseable() method
	 * @return  void
	 * @since   [version]
	 * @version [version]
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
