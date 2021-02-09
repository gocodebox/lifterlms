<?php
/**
 * Tests for LLMS_Post_Instructors model & functions
 *
 * @group post_relationships
 *
 * @since 3.16.12
 * @since 3.37.8 Added tests to remove quiz attempts upon quiz deletion.
 * @since 4.15.0 Added tests on access plans deletion upon quiz deletion.
 */
class LLMS_Test_Post_Relationships extends LLMS_UnitTestCase {

	/**
	 * When deleting lessons
	 *
	 * 		A) Any lesson which has this lesson as a prereq should have that prereq removed
	 * 		   And the has_prereq metavalue should be unset returning "no"
	 * 		B) Any quiz attached to this lesson should be detached (making it an orphan)
	 *
	 * @since 3.16.12
	 * @return void
	 */
	private function delete_lesson() {

		$courses = $this->generate_mock_courses( 1, 1, 4, 3, 1 );
		$lessons = llms_get_post( $courses[0] )->get_lessons();

		// add prereqs to all the lessons except the first.
		foreach ( $lessons as $i => $lesson ) {

			if ( 0 === $i ) {
				continue;
			}

			$prev = $lessons[ $i - 1 ];

			$lesson->set( 'has_prerequisite', 'yes' );
			$lesson->set( 'prerequisite', $prev->get( 'id' ) );

		}

		// Delete posts and run tests.
		foreach ( $lessons as $i => $lesson ) {

			$quiz = $lesson->get_quiz();

			wp_delete_post( $lesson->get( 'id' ) );

			// Quizzes attached to the lesson should now be orphaned.
			if ( $quiz ) {
				$this->assertTrue( $quiz->is_orphan() );
			}

			if ( $i === count( $lessons ) - 1 ) {
				continue;
			}
			$next = $lessons[ $i + 1 ];

			// Prereqs should be removed.
			$this->assertEquals( 'no', $next->get( 'has_prerequisite' ) );
			$this->assertEquals( 0, $next->get( 'prerequisite' ) );
			$this->assertFalse( $next->has_prerequisite() );

		}

	}

	/**
	 * When a quiz is deleted, all the child questions should be deleted too
	 *
	 * Lesson should switch quiz_enabled to "no".
	 *
	 * All student attempts for the quiz should be deleted.
	 *
	 * @since 3.16.12
	 * @since 3.37.8 Add tests to remove quiz attempts upon quiz deletion.
	 *
	 * @return void
	 */
	private function delete_quiz() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 20 );
		$lesson = llms_get_post( llms_get_post( $courses[0] )->get_lessons( 'ids' )[0] );
		$quiz = $lesson->get_quiz();
		$quiz_id = $quiz->get( 'id' );

		$student_1 = $this->factory->student->create();
		$attempt_1 = $this->take_quiz( $quiz_id, $student_1 );
		$student_2 = $this->factory->student->create();
		$attempt_2 = $this->take_quiz( $quiz_id, $student_2, 50 );

		$questions = $quiz->get_questions( 'ids' );

		wp_delete_post( $quiz->get( 'id' ), true );

		// All question posts should be deleted.
		foreach ( $questions as $question_id ) {
			$this->assertNull( get_post( $question_id ) );
		}

		// The quiz will be disabled on the lesson because metadata is unset.
		$this->assertFalse( $lesson->is_quiz_enabled() );

		// Quiz attempts should be deleted.
		$this->assertFalse( $attempt_1->exists() );
		$this->assertFalse( $attempt_2->exists() );

		// Query for quiz attempts should return nothing.
		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'quiz_id'  => $quiz_id,
				'per_page' => 1,
			)
		);
		$this->assertEquals( 0, $query->found_results );

	}

	/**
	 * When a product is deleted all the related access plans should be deleted
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	private function delete_product() {

		$product_types = array(
			'course',
			'llms_membership',
		);

		foreach ( $product_types as $product_type ) {

			// Create product.
			$product_id  = $this->factory->post->create( array( 'post_type' => $product_type ) );
			$title       = sprintf( 'Access plan for %1$s', $product_id );

			// Create access plan and assign the related product to it.
			$access_plan    = llms_insert_access_plan( compact( 'product_id', 'title' ) );
			$access_plan_id = $access_plan->get( 'id' );

			// Get access plan properties (meta) to test.
			if ( ! isset( $access_plan_metas ) ) {
				$access_plan_metas = array_map(
					function( $prop ) use ( $access_plan ) {
						return LLMS_Unit_Test_Util::get_private_property_value( $access_plan, 'meta_prefix' ) . $prop;
					},
					array_keys(
						array_diff_key(
							$access_plan->get( 'properties' ),
							LLMS_Unit_Test_Util::call_method( $access_plan, 'get_post_properties' )
						)
					)
				);
			}
			// Trash product => do not remove access plans.
			wp_trash_post( $product_id );
			$this->assertNotNull( get_post( $product_id ), $product_type );

			// Delete the product (no trash, force deletion is true by default for non built-in post types).
			wp_delete_post( $product_id );

			// Check the access plan has been deleted.
			$this->assertNull( get_post( $product_id ), $product_type );

			// Check access plan's meta deletion
			foreach ( $access_plan_metas as $access_plan_meta ) {
				$this->assertFalse(
					metadata_exists( 'post', $access_plan_id, $access_plan_meta ),
					sprintf(
						'Test failing for meta %1$s of access plan with ID %2$s on %3$s deletion',
						$access_plan_meta,
						$access_plan_id,
						$product_type
					)
				);
			}

		}

	}

	/**
	 * Test all relationships based on post types
	 *
	 * @since 3.16.12
	 * @since 4.15.0 Added tests on course on membership deletion.
	 *
	 * @return void
	 */
	public function test_maybe_update_relationships() {

		$funcs = array(
			'delete_quiz',
			'delete_lesson',
			'delete_product',
		);
		foreach ( $funcs as $func ) {
			$this->{$func}();
		}

	}

}
