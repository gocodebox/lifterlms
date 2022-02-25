<?php
/**
 * Test LLMS_Controller_Quizzes
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group controller_awards
 *
 * @since [version]
 */
class LLMS_Test_Controller_Awards extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = 'LLMS_Controller_Awards';

	}

	/**
	 * Test __construct()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_constructor() {

		$actions = array(
			// Hook, Callback, Priority.
			array( 'llms_user_earned_certificate', 'on_earn', 20 ),
			array( 'llms_user_earned_achievement', 'on_earn', 20 ),
			array( 'save_post_llms_my_certificate', 'on_save', 20 ),
			array( 'save_post_llms_my_achievement', 'on_save', 20 ),
			array( 'rest_after_insert_llms_my_certificate', 'on_rest_insert', 20 ),
		);

		foreach ( $actions as $data ) {
			remove_action( $data[0], array( $this->main, $data[1] ), $data[2] );
			$this->assertFalse( has_action( $data[0], array( $this->main, $data[1] ) ) );
		}

		// Reinstantiate.
		$this->main::init();

		foreach ( $actions as $data ) {
			$this->assertEquals( $data[2], has_action( $data[0], array( $this->main, $data[1] ) ) );
		}

	}

	/**
	 * Test on_earn() when an invalid post type is passed in.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_earn_invalid_post_type() {

		$post = $this->factory->post->create();
		$this->assertFalse( $this->main::on_earn( 1, $post ) );

	}

	/**
	 * Test on_earn()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_earn() {

		foreach ( array( 'llms_my_achievement', 'llms_my_certificate' ) as $post_type ) {

			$post = $this->factory->post->create( compact( 'post_type' ) );

			$ts = '2021-12-23 11:45:55';
			llms_tests_mock_current_time( $ts );

			$this->assertEquals( $ts, $this->main::on_earn( 1, $post ) );
			$this->assertEquals( $ts, get_post_meta( $post, '_llms_awarded', true ) );

		}

	}

	/**
	 * Test on_rest_insert() during a rest update.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_rest_insert_update() {
		$this->assertSame( 0, $this->main::on_rest_insert( null, null, false ) );
	}

	/**
	 * Test on_rest_insert() for a cert with no parent template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_rest_insert_no_parent() {
		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_certificate' ) );
		$this->assertSame( 1, $this->main::on_rest_insert( $post, null, true ) );
	}

	/**
	 * Test on_rest_insert() during an insertion with a parent.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_rest_insert() {

		$actions = did_action( 'llms_certificate_synchronized' );

		$template_id = $this->create_certificate_template();
		$post        = $this->factory->post->create_and_get( array(
			'post_type'   => 'llms_my_certificate',
			'post_parent' => $template_id,
		) );

		$this->assertSame( 2, $this->main::on_rest_insert( $post, null, true ) );
		$this->assertEquals( ++$actions, did_action( 'llms_certificate_synchronized' ) );

		$cert = llms_get_certificate( $post->ID );
		$this->assertNotEquals( $post->post_name, $cert->get( 'name' ) );

	}

	/**
	 * Test on_save() with an invalid post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_save_award_invalid_post_type() {

		$post = $this->factory->post->create();
		$this->assertFalse( $this->main::on_save( $post ) );

	}

	/**
	 * Test on_save() for a achievement.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_save_achievement() {

		$content = 'post content';

		$actions = did_action( 'llms_user_earned_achievement' );

		$achievement_id = $this->factory->post->create( array(
			'post_type'    => 'llms_my_achievement',
			'post_content' => $content,
		) );

		$this->assertTrue( $this->main::on_save( $achievement_id ) );

		$cert = new LLMS_User_Achievement( $achievement_id );
		$this->assertEquals( $content, $cert->get( 'content', true ) );

		$this->assertEquals( ++$actions, did_action( 'llms_user_earned_achievement' ) );

		// Action shouldn't run again.
		$this->assertTrue( $this->main::on_save( $achievement_id ) );
		$this->assertEquals( $actions, did_action( 'llms_user_earned_achievement' ) );

	}

	/**
	 * Test on_save() for a certificate.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_save_certificate() {

		$first_name = 'Sarah';

		$actions = did_action( 'llms_user_earned_certificate' );

		$cert_id = $this->factory->post->create( array(
			'post_type'    => 'llms_my_certificate',
			'post_content' => '{first_name}',
			'post_author'  => $this->factory->user->create( compact( 'first_name' ) ),
		) );

		$this->assertTrue( $this->main::on_save( $cert_id ) );

		$cert = llms_get_certificate( $cert_id );
		$this->assertEquals( $first_name, $cert->get( 'content', true ) );

		$this->assertEquals( ++$actions, did_action( 'llms_user_earned_certificate' ) );

		// Action shouldn't run again.
		$this->assertTrue( $this->main::on_save( $cert_id ) );
		$this->assertEquals( $actions, did_action( 'llms_user_earned_certificate' ) );

	}

}
