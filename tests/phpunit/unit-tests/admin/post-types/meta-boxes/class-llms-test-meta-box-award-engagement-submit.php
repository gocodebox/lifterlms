<?php
/**
 * Tests for LifterLMS Award Engagement Submit Meta Box.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_award_engagement
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since 6.0.0
 * @version 6.0.0
 */
class LLMS_Test_Meta_Box_Award_Engagement_Submit extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Award_Engagement_Submit();

	}

	/**
	 * Tear down test.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function tear_down() {
		// Reset current screen.
		llms_tests_reset_current_screen();
	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals(
			array( 'llms_my_achievement', 'llms_my_certificate' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);

	}

	/**
	 * Test get fields.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_fields() {

		$this->assertEquals(
			array(),
			$this->metabox->get_fields()
		);

	}

	/**
	 * Test current_student_id() method on creation passing no params.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_current_student_id_no_param_on_new_post() {

		foreach ( $this->metabox->screens as $post_type ) {
			$this->metabox->post = $this->factory->post->create_and_get(
				array(
					'post_type'   => $post_type,
					'post_author' =>  2, // Student.
				)
			);

			// Set current screen to new post.
			set_current_screen( 'post-new.php' );
			$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->metabox, 'current_student_id' ), $post_type );
		}

	}


	/**
	 * Test current_student_id() method passing `true` as `$creating` param.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_current_student_creating_param() {

		foreach ( $this->metabox->screens as $post_type ) {
			$this->metabox->post = $this->factory->post->create_and_get(
				array(
					'post_type'   => $post_type,
					'post_author' =>  2, // Student.
				)
			);

			// Set current screen to edit post.
			set_current_screen( 'edit.php' );

			// Pass creating=true.
			$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $this->metabox, 'current_student_id', array( true ) ), $post_type );
		}

	}

	/**
	 * Test current_student_id() when editing an awarded engagement.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_current_student_edit_awarded_engagement() {


		foreach ( $this->metabox->screens as $post_type ) {
			$this->metabox->post = $this->factory->post->create_and_get(
				array(
					'post_type'   => $post_type,
					'post_author' =>  2, // Student.
				)
			);

			// Set current screen to edit post.
			set_current_screen( 'edit.php' );

			// Edit a certificate with assigned student id.
			$this->assertEquals( 2, LLMS_Unit_Test_Util::call_method( $this->metabox, 'current_student_id' ), $post_type );
		}

	}

	/**
	 * Test current_student_id() when creating an awarded engagement passing the student id via GET.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_current_student_create_awarded_engagement_passing_student_id_via_get() {

		foreach ( $this->metabox->screens as $post_type ) {
			$this->metabox->post = $this->factory->post->create_and_get(
				array(
					'post_type' => $post_type,
				)
			);

			// Pass the ID of the student who's awarding the engagement.
			$this->mockGetRequest(
				array(
					'sid' => 12
				),
			);

			// Set current screen to create.
			set_current_screen( 'post-new.php' );
			$this->assertEquals( 12, LLMS_Unit_Test_Util::call_method( $this->metabox, 'current_student_id' ), $post_type );
		}

	}

	/**
	 * Test current_student_id() when editing an already awarded engagement passing the student id via GET.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_current_student_edit_awarded_engagement_passing_student_id_via_get() {

		foreach ( $this->metabox->screens as $post_type ) {
			$this->metabox->post = $this->factory->post->create_and_get(
				array(
					'post_type'   => $post_type,
					'post_author' => 23
				)
			);

			// Pass the ID of the student who's awarding the engagement.
			$this->mockGetRequest(
				array(
					'sid' => 12
				),
			);

			// Set current screen to edit post.
			set_current_screen( 'edit.php' );

			// Edit a certificate with assigned student id.
			$this->assertEquals( 23, LLMS_Unit_Test_Util::call_method( $this->metabox, 'current_student_id' ), $post_type );
		}

	}

}
