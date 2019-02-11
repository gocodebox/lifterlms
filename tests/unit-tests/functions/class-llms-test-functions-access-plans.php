<?php
/**
 * Test Order Functions
 * @since    [version]
 * @version  [version]
 *
 */
class LLMS_Test_Functions_Access_Plans extends LLMS_UnitTestCase {

	public function test_llms_create_access_plan() {

		$action_count = 1;

		$props = array();

		// Missing Product ID.
		$this->assertIsWPError( llms_create_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_create_access_plan( $props ) );

		// Set but empty.
		$props['product_id'] = '';
		$this->assertIsWPError( llms_create_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_create_access_plan( $props ) );

		// Not an ID.
		$props['product_id'] = 'fake';
		$this->assertIsWPError( llms_create_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'missing-product-id', llms_create_access_plan( $props ) );

		// Real Product.
		$props['product_id'] = $this->factory->course->create( array( 'sections' => 0 ) );
		$this->assertTrue( is_a( llms_create_access_plan( $props ), 'LLMS_Access_Plan' ) );
		$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );

		// Invalid visibility.
		$props['visibility'] = 'fake';
		$this->assertIsWPError( llms_create_access_plan( $props ) );
		$this->assertWPErrorCodeEquals( 'invalid-visibility', llms_create_access_plan( $props ) );

		// Create a visible plan
		$props['visibility'] = 'visible';
		$plan = llms_create_access_plan( $props );
		$action_count++;
		$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );
		$this->assertTrue( $plan->is_visible() );

		// defaults are a free plan.
		$this->assertEquals( 0, $plan->get( 'price' ) );

		// Period validation.
		$props['price'] = 5;
		$props['frequency'] = 1;
		$props['trial_offer'] = 'yes';
		foreach ( array( 'period', 'access_period', 'trial_period' ) as $period_prop ) {
			foreach ( array( 'year', 'month', 'week', 'day' ) as $period ) {
				$props[ $period_prop ] = $period;
				$plan = llms_create_access_plan( $props );
				$action_count++;
				$this->assertEquals( $period, $plan->get( $period_prop ) );
				$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );
			}
			$props[ $period_prop ] = 'fake';
			$plan = llms_create_access_plan( $props );
			$this->assertIsWPError( $plan );
			$this->assertWPErrorCodeEquals( 'invalid-' . $period_prop, $plan );
			unset( $props[ $period_prop ] );
		}

		// Free plan default overrides.
		$props['price'] = 0;
		$props['is_free'] = 'no';
		$props['frequency'] = 0;
		$props['on_sale'] = 'yes';
		$props['trial_offer'] = 'yes';
		$plan = llms_create_access_plan( $props );
		$action_count++;
		$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );
		$this->assertEquals( 0, $plan->get( 'price' ) );
		$this->assertEquals( 'yes', $plan->get( 'is_free' ) );
		$this->assertEquals( 'no', $plan->get( 'on_sale' ) );
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) );
		$this->assertEquals( 0, $plan->get( 'frequency' ) );

		// One-time payment.
		$props['price'] = 5;
		$plan = llms_create_access_plan( $props );
		$action_count++;
		$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );
		$this->assertEquals( $props['price'], $plan->get( 'price' ) );
		$this->assertEquals( 0, $plan->get( 'frequency' ) );

		// No possible trial.
		$this->assertEquals( 'no', $plan->get( 'trial_offer' ) );
		$this->assertEmpty( $plan->get( 'trial_price' ) );
		$this->assertEmpty( $plan->get( 'trial_length' ) );
		$this->assertEmpty( $plan->get( 'trial_period' ) );

		// Default sale.
		$this->assertEquals( $props['on_sale'], $plan->get( 'on_sale' ) );
		$this->assertEquals( 0, $plan->get( 'sale_price' ) );

		// More sale stuff.
		$props['sale_price'] = 25;
		$props['on_sale'] = 'yes';
		$props['sale_end'] = '2019-05-05';
		$props['sale_start'] = '2019-05-05';
		$plan = llms_create_access_plan( $props );
		$action_count++;
		$this->assertEquals( $action_count, did_action( 'llms_access_plan_created' ) );
		$this->assertEquals( $props['sale_price'], $plan->get( 'sale_price' ) );
		$this->assertEquals( $props['on_sale'], $plan->get( 'on_sale' ) );
		$this->assertEquals( $props['sale_end'], $plan->get( 'sale_end' ) );
		$this->assertEquals( $props['sale_start'], $plan->get( 'sale_start' ) );

		// Default Trial stuff.
		// $this->assertEquals( 0, $plan->get( 'trial_price' ) );
		// $this->assertEquals( 1, $plan->get( 'trial_length' ) );
		// $this->assertEquals( 'year', $plan->get( 'trial_period' ) );


	}

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

}
