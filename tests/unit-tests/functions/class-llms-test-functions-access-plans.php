<?php
/**
 * Test Order Functions
 *
 * @group    LLMS_Access_Plan
 * @since    [version]
 * @version  [version]
 *
 */
class LLMS_Test_Functions_Access_Plans extends LLMS_UnitTestCase {

	/**
	 * Test the llms_get_access_plan_period_options() method
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_get_access_plan_period_options() {

		$options = llms_get_access_plan_period_options();
		$this->assertEquals(  array( 'year', 'month', 'week', 'day' ), array_keys( $options ) );

	}

	/**
	 * Test the llms_get_access_plan_visibility_options() method
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_get_access_plan_visibility_options() {

		$options = llms_get_access_plan_visibility_options();
		$this->assertEquals( array( 'visible', 'hidden', 'featured' ), array_keys( $options ) );

	}

	/**
	 * Test default props for llms_insert_access_plan() function.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_default() {

		$props = array();
		$props['product_id'] = $this->factory->course->create( array( 'sections' => 0 ) );

		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Default properties.
		$this->assertEquals( 0, $plan->get( 'price' ) );
		$this->assertEquals( 'yes', $plan->get( 'is_free' ) );
		$this->assertEquals( 'no', $plan->get( 'on_sale' ) );
		$this->assertEquals( 0, $plan->get( 'frequency' ) );
		$this->assertEquals( 'Access Plan', $plan->get( 'title' ) );

		// No possible trial.
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) );
		$this->assertEmpty( $plan->get( 'trial_price' ) );
		$this->assertEmpty( $plan->get( 'trial_length' ) );
		$this->assertEmpty( $plan->get( 'trial_period' ) );

		// Expiration.
		$this->assertEquals( 'lifetime', $plan->get( 'access_expiration' ) );

	}

	/**
	 * Test the default paramaters that will be automatically "fixed" or overridden for the llms_insert_access_plan() function.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_free_default_overrides() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price' => 0,
			'is_free' => 'no',
			'frequency' => 0,
			'on_sale' => 'yes',
			'trial_offer' => 'yes',
		);

		$plan = llms_insert_access_plan( $props );

		// Success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		$this->assertEquals( 0, $plan->get( 'price' ) );
		$this->assertEquals( 'yes', $plan->get( 'is_free' ) );
		$this->assertEquals( 'no', $plan->get( 'on_sale' ) );
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) );
		$this->assertEquals( 0, $plan->get( 'frequency' ) );

	}

	/**
	 * Test recurring payment props for llms_insert_access_plan() funcion.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_payment_recurring() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price' => 5,
			'frequency' => 1,
			'length' => 1,
			'period' => 'week',
		);

		$plan = llms_insert_access_plan( $props );

		// Success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Props.
		$this->assertEquals( 5, $plan->get( 'price' ) );
		$this->assertEquals( 1, $plan->get( 'frequency' ) );
		$this->assertEquals( 1, $plan->get( 'length' ) );
		$this->assertEquals( 'week', $plan->get( 'period' ) );

	}

	/**
	 * Test one-time payment props for llms_insert_access_plan() funcion.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_payment_single() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price' => 5,
			'frequency' => 0,
			'length' => 1,
			'period' => 'week',
		);

		$plan = llms_insert_access_plan( $props );

		// Success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Props.
		$this->assertEquals( 5, $plan->get( 'price' ) );
		$this->assertEquals( 0, $plan->get( 'frequency' ) );
		$this->assertEmpty( $plan->get( 'length' ) );
		$this->assertEmpty( $plan->get( 'period' ) );

	}

	/**
	 * Test sale-related props on the llms_insert_access_plan() function
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_props_sale() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'on_sale' => 'yes',
			'price' => 50,
		);

		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Default sale.
		$this->assertEquals( 'yes', $plan->get( 'on_sale' ) );
		$this->assertEquals( 0, $plan->get( 'sale_price' ) );

		// Othe props.
		$props['sale_price'] = 25;
		$props['on_sale'] = 'yes';
		$props['sale_end'] = '2019-05-05';
		$props['sale_start'] = '2019-05-05';

		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Test props.
		$this->assertEquals( $props['sale_price'], $plan->get( 'sale_price' ) );
		$this->assertEquals( $props['on_sale'], $plan->get( 'on_sale' ) );
		$this->assertEquals( $props['sale_end'], $plan->get( 'sale_end' ) );
		$this->assertEquals( $props['sale_start'], $plan->get( 'sale_start' ) );

	}

	/**
	 * Test expiration-related props on the llms_insert_access_plan() function
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_props_expiration() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'access_expiration' => 'lifetime',
		);

		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Props.
		$this->assertEquals( 'lifetime', $plan->get( 'access_expiration' ) );
		$this->assertEmpty( $plan->get( 'access_expires' ) );
		$this->assertEmpty( $plan->get( 'access_length' ) );
		$this->assertEmpty( $plan->get( 'access_period' ) );

		// Limited Date.
		$props['access_expiration'] = 'limited-date';
		$props['access_expires'] = '2019-02-14'; // naw.... so much <3.
		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Props.
		$this->assertEquals( 'limited-date', $plan->get( 'access_expiration' ) );
		$this->assertEquals( $props['access_expires'], $plan->get( 'access_expires' ) );
		$this->assertEmpty( $plan->get( 'access_length' ) );
		$this->assertEmpty( $plan->get( 'access_period' ) );

		// Limited Period.
		$props['access_expiration'] = 'limited-period';
		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// Props.
		$this->assertEquals( 'limited-period', $plan->get( 'access_expiration' ) );
		$this->assertEquals( 1, $plan->get( 'access_length' ) );
		$this->assertEquals( 'year', $plan->get( 'access_period' ) );
		$this->assertEmpty( $plan->get( 'access_expires' ) );

	}

	/**
	 * Test trial-related props on the llms_insert_access_plan() function
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_props_trial() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'trial_offer' => 'yes',
			'trial_length' => 1,
			'trial_period' => 'year',
		);

		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// No trial on a free plan.
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) ) ;

		$props['price'] = 1;
		$plan = llms_insert_access_plan( $props );

		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		// No trial for one-time payments.
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) ) ;

		$props['frequency'] = 1;
		$plan = llms_insert_access_plan( $props );
		// Creation success.
		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

		$this->assertEquals( 'yes', $plan->get( 'trial_offer' ) ) ;
		$this->assertEquals( 0, $plan->get( 'trial_price' ) );
		$this->assertEquals( 1, $plan->get( 'trial_length' ) );
		$this->assertEquals( 'year', $plan->get( 'trial_period' ) );

	}


	/**
	 * Test updating existing llms_insert_access_plan() function.
	 *
	 * @runInSeparateProcess
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_update() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price' => 1,
		);

		// Create.
		$plan = llms_insert_access_plan( $props );

		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );
		$this->assertEquals( 1, did_action( 'llms_access_plan_after_create' ) );
		$this->assertEquals( 1, $plan->get( 'price' ) );

		// Attempt update with set but empty ID.
		$props['id'] = '';
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'invalid-plan', llms_insert_access_plan( $props ) );

		// Update with a a fake ID.
		$props['id'] = 'fake';
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'invalid-plan', llms_insert_access_plan( $props ) );

		// Update with a valid post ID (but not the access plan post type).
		$props['id'] = $this->factory->post->create();
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'invalid-plan', llms_insert_access_plan( $props ) );

		// plan before props.
		$plan_before = $plan->toArray();

		// Real plan.
		$props['id'] = $plan->get( 'id' );
		$props['price'] = 2;
		$plan = llms_insert_access_plan( $props );

		$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );
		$this->assertEquals( 1, did_action( 'llms_access_plan_after_update' ) );
		$this->assertEquals( 2, $plan->get( 'price' ) );

		// Price is the only property that should have changed.
		foreach ( $plan->toArray() as $key => $val ) {
			if ( 'price' === $key ) {
				$this->assertFalse( $plan_before[ $key ] === $val );
			} else {
				$this->assertEquals( $plan_before[ $key ], $val );
			}
		}

	}

	/**
	 * Test period field validators for the llms_insert_access_plan_validation() function.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_validation_period() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
			'price' => 1,
			'frequency' => 1,
			'trial_offer' => 'yes',
			'access_expiration' => 'limited-period',
		);

		foreach ( array( 'period', 'access_period', 'trial_period' ) as $period_prop ) {

			foreach ( array( 'year', 'month', 'week', 'day' ) as $period ) {

				$props[ $period_prop ] = $period;

				$plan = llms_insert_access_plan( $props );

				// Success.
				$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

				// Getter matches set value.
				$this->assertEquals( $period, $plan->get( $period_prop ) );

			}

			// Doesn't work with an invalid visibility.
			$props[ $period_prop ] = 'fake';
			$plan = llms_insert_access_plan( $props );
			$this->assertIsWPError( $plan );
			$this->assertWPErrorCodeEquals( 'invalid-' . $period_prop, $plan );
			unset( $props[ $period_prop ] );

		}

	}

	/**
	 * Test product related conditions for llms_insert_access_plan() function.
	 *
	 * @runInSeparateProcess
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_validation_product() {

		$props = array();

		// Missing Product ID.
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_insert_access_plan( $props ) );

		// Set but empty.
		$props['product_id'] = '';
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_insert_access_plan( $props ) );

		// Not an ID.
		$props['product_id'] = 'fake';
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_insert_access_plan( $props ) );

		// Real Product.
		$props['product_id'] = $this->factory->course->create( array( 'sections' => 0 ) );
		$this->assertTrue( is_a( llms_insert_access_plan( $props ), 'LLMS_Access_Plan' ) );
		$this->assertEquals( 1, did_action( 'llms_access_plan_after_create' ) );

	}

	/**
	 * Test plan visibility validation for the llms_insert_access_plan() function
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_insert_access_plan_validation_visibility() {

		$props = array(
			'product_id' => $this->factory->course->create( array( 'sections' => 0 ) ),
		);

		// Invalid visibility.
		$props['visibility'] = 'fake';
		$this->assertIsWPError( llms_insert_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'invalid-visibility', llms_insert_access_plan( $props ) );

		// Valid visibilities.
		foreach ( array_keys( llms_get_access_plan_visibility_options() ) as $visibility ) {

			$props['visibility'] = $visibility;
			$plan = llms_insert_access_plan( $props );

			// Success.
			$this->assertTrue( is_a( $plan, 'LLMS_Access_Plan' ) );

			// Getter.
			$this->assertEquals( $visibility, $plan->get_visibility() );

		}

	}



}
