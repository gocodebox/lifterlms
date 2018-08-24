<?php
/**
 * Tests for LifterLMS User Postmeta functions
 * @group    functions
 * @group    template_functinos
 * @group    pricing_tables
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Functions_Templates_Pricing_Tables extends LLMS_UnitTestCase {

	/**
	 * Retrieve output buffer for a given template function and access plan
	 * @param    string     $func       template function name
	 * @param    array      $plan_args  plan arguments, passed to $this->get_mock_plan()
	 * @param    obj        $plan       optionally pass a plan (ignores $plan_args)
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_ob( $func, $plan_args = array(), $plan = null ) {

		if ( is_null( $plan ) ) {
			$plan = call_user_func_array( array( $this, 'get_mock_plan' ), $plan_args );
		}

		ob_start();
		call_user_func( $func, $plan );
		return array(
			'plan' => $plan,
			'html' => trim( ob_get_clean() ),
		);

	}

	/**
	 * test the llms_get_access_plan_classes metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_get_access_plan_classes() {

		$expect = 'llms-access-plan llms-access-plan-%d';

		$plan = $this->get_mock_plan();
		$this->assertEquals( sprintf( $expect, $plan->get( 'id' ) ), llms_get_access_plan_classes( $plan ) );

		// on sale
		$plan = $this->get_mock_plan( 1, 1, 'liftetime', true );
		$this->assertEquals( sprintf( $expect . ' on-sale', $plan->get( 'id' ) ), llms_get_access_plan_classes( $plan ) );

		// featured
		$plan = $this->get_mock_plan();
		$plan->set_visibility( 'featured' );
		$this->assertEquals( sprintf( $expect . ' featured', $plan->get( 'id' ) ), llms_get_access_plan_classes( $plan ) );

		// featured & on sale
		$plan = $this->get_mock_plan( 1, 1, 'liftetime', true );
		$plan->set_visibility( 'featured' );
		$this->assertEquals( sprintf( $expect . ' featured on-sale', $plan->get( 'id' ) ), llms_get_access_plan_classes( $plan ) );

	}

	/**
	 * test the llms_template_access_plan metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan() {

		$ob = $this->get_ob( 'llms_template_access_plan' );

		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan' ) );
		$this->assertTrue( strlen( $ob['html'] ) - 6 === strrpos( $ob['html'], '</div>' ) );
		$this->assertTrue( false !== strpos( $ob['html'], sprintf( 'id="llms-access-plan-%d"', $ob['plan']->get( 'id' ) ) ) );
		$this->assertEquals( 1, did_action( 'llms_before_access_plan' ) );
		$this->assertEquals( 1, did_action( 'llms_acces_plan_content' ) );
		$this->assertEquals( 1, did_action( 'llms_acces_plan_footer' ) );

	}

	/**
	 * test the llms_template_access_plan_button metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_button() {

		LLMS_Install::create_pages();
		$ob = $this->get_ob( 'llms_template_access_plan_button', array( 0 ) );

		// purchase button link
		$this->assertTrue( false !== strpos( $ob['html'], '<a class="llms-button-action button"' ) );
		$this->assertTrue( false !== strpos( $ob['html'], sprintf( 'href="%s"', $ob['plan']->get_checkout_url() ) ) );

		// check free enroll form
		$student = $this->get_mock_student();
		wp_set_current_user( $student->get_id() );
		$ob['plan']->set( 'is_free', 'yes' );
		$ob = $this->get_ob( 'llms_template_access_plan_button', array(), $ob['plan'] );
		$this->assertTrue( 0 === strpos( $ob['html'], '<form' ) );

	}

	/**
	 * test the llms_template_access_plan_description metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_description() {

		$plan = $this->get_mock_plan();
		$plan->set( 'content', '<p>mock description</p>' );

		$ob = $this->get_ob( 'llms_template_access_plan_description', array(), $plan );

		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan-description">' ) );
		$this->assertTrue( false !== strpos( $ob['html'], '<p>mock description</p>' ) );

	}

	/**
	 * test the llms_template_access_plan_feature metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_feature() {

		$ob = $this->get_ob( 'llms_template_access_plan_feature', array() );

		// not featured
		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan-featured">' ) );
		$this->assertTrue( false === strpos( $ob['html'], 'FEATURED' ) );

		// featured
		$ob['plan']->set_visibility( 'featured' );
		$ob = $this->get_ob( 'llms_template_access_plan_feature', array(), $ob['plan'] );
		$this->assertTrue( false !== strpos( $ob['html'], 'FEATURED' ) );

	}

	/**
	 * test the llms_template_access_plan_pricing metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_pricing() {

		// single, not on sale, no expiration, no recurring
		$ob = $this->get_ob( 'llms_template_access_plan_pricing', array( 1, 0 ) );
		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan-pricing regular">' ) );
		$this->assertTrue( false !== strpos( $ob['html'], llms_price( 1 ) ) );
		$this->assertTrue( false === strpos( $ob['html'], 'SALE' ) );
		$this->assertTrue( false === strpos( $ob['html'], 'class="llms-access-plan-schedule"' ) );
		$this->assertTrue( false === strpos( $ob['html'], 'class="llms-access-plan-expiration"' ) );

		// on sale
		$ob = $this->get_ob( 'llms_template_access_plan_pricing', array( 1, 0, 'liftetime', true ) );
		$this->assertTrue( false !== strpos( $ob['html'], 'SALE' ) );

		// expires
		$ob = $this->get_ob( 'llms_template_access_plan_pricing', array( 1, 0, 'limited-date' ) );
		$this->assertTrue( false !== strpos( $ob['html'], 'class="llms-access-plan-expiration"' ) );

		// recurring
		$ob = $this->get_ob( 'llms_template_access_plan_pricing' );
		$this->assertTrue( false !== strpos( $ob['html'], 'class="llms-access-plan-schedule"' ) );

	}

	/**
	 * test the llms_template_access_plan_restrictions metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_restrictions() {

		$ob = $this->get_ob( 'llms_template_access_plan_restrictions' );
		$this->assertEmpty( $ob['html'] );

		// has restriction
		$mid = $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) );
		$ob['plan']->set( 'availability', 'members' );
		$ob['plan']->set( 'availability_restrictions', array( $mid ) );

		$ob = $this->get_ob( 'llms_template_access_plan_restrictions', array(), $ob['plan'] );
		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan-restrictions">' ) );
		$this->assertTrue( false !== strpos( $ob['html'], get_the_title( $mid ) ) );

	}

	/**
	 * test the llms_template_access_plan_title metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_title() {

		$ob = $this->get_ob( 'llms_template_access_plan_title' );
		$this->assertEquals( sprintf( '<h4 class="llms-access-plan-title">%s</h4>', $ob['plan']->get( 'title' ) ), $ob['html'] );

	}

	/**
	 * test the llms_template_access_plan_trial metho
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_template_access_plan_trial() {

		// no trial
		$ob = $this->get_ob( 'llms_template_access_plan_trial' );
		$this->assertTrue( 0 === strpos( $ob['html'], '<div class="llms-access-plan-pricing trial">' ) );
		$this->assertTrue( false === strpos( $ob['html'], 'TRIAL' ) );

		// has trial
		$ob = $this->get_ob( 'llms_template_access_plan_trial', array( 1, 1, 'lifetime', false, true ) );
		$this->assertTrue( false !== strpos( $ob['html'], 'TRIAL' ) );

	}

	/**
	 * test test_lifterlms_template_pricing_table method
	 * @todo     add tests to test logic in template
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_lifterlms_template_pricing_table() {

		$plan = $this->get_mock_plan();
		$course = $plan->get_product();

		$plan = $this->get_mock_plan( 15 );
		$plan->set( 'product_id', $course->get( 'id' ) );

		$plan = $this->get_mock_plan( 1 );
		$plan->set( 'product_id', $course->get( 'id' ) );

		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), 'no' );

		// no gateways available
		ob_start();
		lifterlms_template_pricing_table( $course->get( 'id' ) );
		$html = trim( ob_get_clean() );

		$this->assertEmpty( $html );

		// gateways available
		update_option( $manual->get_option_name( 'enabled' ), 'yes' );

		ob_start();
		lifterlms_template_pricing_table( $course->get( 'id' ) );
		$html = trim( ob_get_clean() );

		$this->assertTrue( 0 === strpos( $html, '<section class="llms-access-plans cols-3">' ) );
		$this->assertEquals( 3, did_action( 'llms_access_plan' ) );

	}

}
