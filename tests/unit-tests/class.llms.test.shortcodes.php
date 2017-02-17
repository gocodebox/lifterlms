<?php
/**
 * Test LifterLMS Shortcodes
 */

class LLMS_Test_Shortcodes extends LLMS_UnitTestCase {

	/**
	 * Generic tests and a few tests on the abstract
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public function test_shortcodes() {

		$shortcodes = array(
			'LLMS_Shortcode_Registration',
			'LLMS_Shortcode_Membership_Link',
		);

		foreach ( $shortcodes as $class ) {

			$obj = $class::instance();
			$this->assertTrue( shortcode_exists( $obj->tag ) );
			$this->assertTrue( is_a( $obj, 'LLMS_Shortcode' ) );
			$this->assertTrue( ! empty( $obj->tag ) );
			$this->assertTrue( is_string( $obj->output() ) );
			$this->assertTrue( is_array( $obj->get_attributes() ) );
			$this->assertTrue( is_string( $obj->get_content() ) );


		}

		$this->assertClassHasStaticAttribute( '_instances', 'LLMS_Shortcode' );

	}

	/**
	 * Test the registration shortcode
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public function test_registration() {

		// our output should enqueue this
		wp_dequeue_script( 'password-strength-meter' );

		$obj = LLMS_Shortcode_Registration::instance();

		// when logged out, there should be html content
		$this->assertContains( 'llms-new-person-form-wrapper', $obj->output() );

		// no html when logged in
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$this->assertEmpty( $obj->output() );

		// ensure required scripts are enqueued
		$this->assertTrue( wp_script_is( 'password-strength-meter', 'enqueued' ) );
		$this->assertTrue( LLMS_Frontend_Assets::is_inline_script_enqueued( 'llms-pw-strength' ) );

	}

	/**
	 * Test lifterlms_membership_link shortcode
	 * @return   void
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	public function test_membership_link() {

		// create a membership that we can use for linking
		$mid = $this->factory->post->create( array(
			'post_title' => 'Test Membership',
			'post_type' => 'llms_membership',
		) );

		$obj = LLMS_Shortcode_Membership_Link::instance();

		// test default settings
		$this->assertContains( get_permalink( $mid ), $obj->output( array( 'id' => $mid ) ) );
		$this->assertContains( get_the_title( $mid ), $obj->output( array( 'id' => $mid ) ) );

		$this->assertEquals( $mid, $obj->get_attribute( 'id' ) );

		// check non default content
		$this->assertContains( 'Alternate Text', $obj->output( array( 'id' => $mid ), 'Alternate Text' ) );
		$this->assertEquals( 'Alternate Text', $obj->get_content( 'Alternate Text' ) );

	}

}
