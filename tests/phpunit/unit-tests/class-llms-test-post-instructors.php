<?php
/**
 * Tests for LLMS_Post_Instructors model & functions
 *
 * @package LifterLMS/Tests
 *
 * @group LLMS_Post_Instructors
 * @group LLMS_Course
 * @group LLMS_Membership
 *
 * @since 3.13.0
 */
class LLMS_Test_Post_Instructors extends LLMS_UnitTestCase {

	private $post_types = array( 'course', 'llms_membership' );

	public function test_interface() {

		foreach ( $this->post_types as $post_type ) {

			$post_id = $this->factory->post->create( array(
				'post_type' => $post_type,
			) );

			$post = llms_get_post( $post_id );

			$this->assertTrue( method_exists( $post, 'instructors' ) );
			$this->assertTrue( method_exists( $post, 'get_instructors' ) );
			$this->assertTrue( method_exists( $post, 'set_instructors' ) );

			$this->assertTrue( is_a( $post->instructors(), 'LLMS_Post_Instructors' ) );

		}

	}

	/**
	 * Test get and set methods.
	 *
	 * @since Unknown
	 * @since [version] Added check to ensure `name` is set when no instructor data is set.
	 *
	 * @return void
	 */
	public function test_getters_setters() {

		$user_ids = $this->factory->user->create_many( 3 );

		foreach ( $this->post_types as $post_type ) {

			$post_id = $this->factory->post->create( array(
				'post_type' => $post_type,
				'post_author' => $user_ids[0],
			) );

			$post = llms_get_post( $post_id );

			$defaults = llms_get_instructors_defaults();

			$this->assertTrue( is_array( $post->get_instructors() ) );

			$post->set_instructors( array(
				array( 'id' => $user_ids[0] ),
				array( 'id' => $user_ids[1] ),
				array( 'id' => $user_ids[2] ),
			) );

			foreach ( $post->get_instructors() as $instructor ) {

				$this->assertTrue( in_array( $instructor['id'], $user_ids ) );
				$this->assertEquals( $defaults['label'], $instructor['label'] );
				$this->assertEquals( $defaults['visibility'], $instructor['visibility'] );

			}

			$this->assertEquals( $post->get( 'author' ), $user_ids[0] );

			$update = array(
				array(
					'id' => $user_ids[1],
					'label' => 'mock label',
					'visibility' => 'visible',
				),
				array(
					'id' => $user_ids[0],
					'label' => 'mock label',
					'visibility' => 'hidden',
				),
			);
			$post->set_instructors( $update );
			$this->assertEquals( $update, $post->get_instructors() );

			// Check exclude hidden works right.
			unset( $update[1] );
			$this->assertEquals( $update, $post->get_instructors( true ) );


			// Clear instructors, should respond with a default of the post_author.
			$post->set_instructors();
			$expect = $defaults;
			$expect['id'] = $user_ids[1];
			$author = get_userdata( $user_ids[1] );
			$expect['name'] = $author->display_name;
			$this->assertEquals( array( $expect ), $post->get_instructors() );

		}

	}

}
