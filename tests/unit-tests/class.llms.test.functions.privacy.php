<?php
/**
 * Tests for LifterLMS Privacy Functions
 * @group    functions
 * @group    functions_privacy
 * @group    privacy
 * @since    3.19.0
 * @version  3.19.0
 */
class LLMS_Test_Functions_Privacy extends LLMS_UnitTestCase {

	/**
	 * Test llms_are_terms_and_conditions_required()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_are_terms_and_conditions_required() {

		// terms true & page id numeric
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', '1' );
		$this->assertTrue( llms_are_terms_and_conditions_required() );

		// terms true & page id non-numeric
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', 'brick' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms true & no page id
		update_option( 'lifterlms_terms_page_id', '' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms true & page id 0
		update_option( 'lifterlms_terms_page_id', '0' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms false and page id good
		update_option( 'lifterlms_registration_require_agree_to_terms', 'no' );
		update_option( 'lifterlms_terms_page_id', '1' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		update_option( 'lifterlms_registration_require_agree_to_terms', 'no' );
		update_option( 'lifterlms_terms_page_id', 'brick' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

	}

	function test_llms_get_privacy_notice() {

		$this->assertEquals( 'Your personal data will be used to process your enrollment, support your experience on this website, and for other purposes described in our {{policy}}.', llms_get_privacy_notice() );

		update_option( 'llms_privacy_notice', 'The {{policy}} says things' );

		$this->assertEquals( 'The {{policy}} says things', llms_get_privacy_notice() );

		// empty b/c no page set
		$this->assertEmpty( llms_get_terms_notice( true ) );

		// set a page
		$page_id = $this->factory->post->create( array(
			'post_title' => 'The Page Title',
			'post_type' => 'page',
		) );
		update_option( 'wp_page_for_privacy_policy', $page_id );

		// merging works
		$this->assertEquals( 'The ' . llms_get_option_page_anchor( 'wp_page_for_privacy_policy' ) . ' says things', llms_get_privacy_notice( true ) );

		// empty the option
		update_option( 'llms_privacy_notice', '' );

		// empty b/c there's no option anymore
		$this->assertEmpty( llms_get_terms_notice( true ) );

	}

	/**
	 * test llms_get_terms_notice()
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	function test_llms_get_terms_notice() {

		// default
		$this->assertEquals( 'I have read and agree to the {{terms}}.', llms_get_terms_notice() );

		update_option( 'llms_terms_notice', 'I agree to {{terms}}' );

		$this->assertEquals( 'I agree to {{terms}}', llms_get_terms_notice() );

		// returns empty string when no page set
		$this->assertEmpty( llms_get_terms_notice( true ) );

		// set the page
		$page_id = $this->factory->post->create( array(
			'post_title' => 'The Page Title',
			'post_type' => 'page',
		) );
		update_option( 'lifterlms_terms_page_id', $page_id );

		// test the merged get
		$this->assertEquals( 'I agree to ' . llms_get_option_page_anchor( 'lifterlms_terms_page_id' ), llms_get_terms_notice( true ) );


	}

}
