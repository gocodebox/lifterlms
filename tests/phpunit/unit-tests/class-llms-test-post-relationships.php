<?php
/**
 * Tests for LLMS_Post_Instructors model & functions
 *
 * @group post_relationships
 *
 * @since 3.16.12
 * @since 3.37.8 Added tests to remove quiz attempts upon quiz deletion.
 * @since 4.15.0 Added tests on access plans deletion upon quiz deletion.
 * @since 5.4.0 Added tests for static methods delete_product_with_active_subscriptions_error_message() and maybe_prevent_product_deletion().
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
	 * @since 6.0.0 Don't access `LLMS_Query_Quiz_Attempt` properties directly.
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
		$this->assertEquals( 0, $query->get_found_results() );

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
	 * When deleting courses, dependencies attached to it should be cleaned up:
	 *
	 *        A) Prerequisites on other courses / lessons that point at this course should be removed.
	 *        B) Auto-enrollment lists on memberships should drop the course ID.
	 *        C) Child sections and their orphaned lessons should be forced-deleted or unlinked.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function delete_course() {

		$courses   = $this->generate_mock_courses( 1, 1, 4, 3, 1 );
		$course_id = absint( $courses[0] );

		// Create a course that references the mock course as prerequisite.
		$other_course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$other_course    = llms_get_post( $other_course_id );
		$other_course->set( 'has_prerequisite', 'yes' );
		$other_course->set( 'prerequisite', $course_id );

		// Create a membership that auto-enrolls students into the mock course.
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$membership    = llms_get_post( $membership_id );
		$membership->add_auto_enroll_courses( array( $course_id ) );

		$new_sections = array();
		for ( $i = 0; $i < 2; $i++ ) {
			$new_sections[] = $this->factory->post->create(
				array(
					'post_type'   => 'section',
					'post_parent' => $course_id,
				)
			);
		}
		foreach ( $new_sections as $section_id ) {
			update_post_meta( $section_id, '_llms_parent_course', $course_id );
		}

		wp_delete_post( $course_id );

		$this->assertEmpty( get_post( $course_id ), 'Course should be force-deleted.' );

		// Other course's prereq should have been cleared.
		$this->assertFalse( $other_course->has_prerequisite() );
		$this->assertEquals( 0, $other_course->get( 'prerequisite' ) );

		// Membership auto-enroll should have dropped the course ID.
		$this->assertNotContains( $course_id, $membership->get_auto_enroll_courses() );

		// Child sections should have been force-deleted.
		foreach ( $new_sections as $section_id ) {
			$this->assertNull( get_post( $section_id ), "Section {$section_id} should have been force-deleted." );
		}
	}

	/**
	 * When deleting sections, parent_section meta on lessons in the section should be unset.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function delete_section() {

		// Build a simple course + section + lesson tree.
		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 1 );
		$course_id = absint( $courses[0] );

		$sections = get_posts(
			array(
				'post_type'      => 'section',
				'meta_key'       => '_llms_parent_course',
				'meta_value'     => $course_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		$this->assertNotEmpty( $sections, 'Test fixture did not create a section.' );
		$section_id = absint( $sections[0] );
		$section    = new LLMS_Section( $section_id );

		$lesson_id = $section->get_lessons()[0]->get( 'id' );
		$lesson    = llms_get_post( $lesson_id );

		$this->assertEquals( $section_id, (int) get_post_meta( $lesson_id, '_llms_parent_section', true ), 'Pre-condition: lesson has a parent section.' );

		wp_delete_post( $section_id );

		$this->assertEquals(
			0,
			(int) get_post_meta( $lesson_id, '_llms_parent_section', true ),
			"Section delete should have unset the _llms_parent_section meta on the lesson."
		);
	}

	/**
	 * When deleting a membership, restricted posts and availability restriction arrays should drop the membership ID.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function delete_membership_cleanups() {

		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$membership    = new LLMS_Membership( $membership_id );

		// A page that references the membership in its restricted levels.
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $page_id, '_llms_is_restricted', 'yes' );
		update_post_meta( $page_id, '_llms_restricted_levels', array( $membership_id ) );

		// An access plan restricted to members of this membership.
		$access_plan_id = llms_insert_access_plan(
			array(
				'title'                    => 'Members only',
				'product_id'               => $this->factory->post->create( array( 'post_type' => 'course' ) ),
				'availability'             => 'members',
				'availability_restrictions' => array( $membership_id ),
			)
		)->get( 'id' );

		wp_delete_post( $membership_id );

		// Restricted levels should no longer contain the membership ID.
		$this->assertEmpty( get_post_meta( $page_id, '_llms_restricted_levels', true ) );

		// Access plan restrictions should no longer reference this membership.
		$plan_restriction = get_post_meta( $access_plan_id, '_llms_availability_restrictions', true );
		$this->assertEmpty( $plan_restriction );
	}

	/**
	 * When deleting the sitewide-required membership, the option that points at it should be cleared.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_clear_sitewide_membership_restriction() {

		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		update_option( 'lifterlms_membership_required', $membership_id );

		LLMS_Post_Relationships::maybe_clear_sitewide_membership_restriction( $membership_id );

		$this->assertEmpty( get_option( 'lifterlms_membership_required' ) );

		// Non-memberships should not clear the option.
		update_option( 'lifterlms_membership_required', $membership_id );

		LLMS_Post_Relationships::maybe_clear_sitewide_membership_restriction( $this->factory->post->create( array( 'post_type' => 'post' ) ) );

		$this->assertNotEmpty( get_option( 'lifterlms_membership_required' ) );

		// Unrelated membership should not clear the option.
		update_option( 'lifterlms_membership_required', 999 );

		LLMS_Post_Relationships::maybe_clear_sitewide_membership_restriction( $membership_id );

		$this->assertEquals( 999, (int) get_option( 'lifterlms_membership_required' ) );
	}

	/**
	 * Test all relationships based on post types
	 *
	 * @since 3.16.12
	 * @since 4.15.0 Added tests on course on membership deletion.
	 * @since [version] Added tests for course prerequisites, section children, section lessons, and membership array metas.
	 *
	 * @return void
	 */
	public function test_maybe_update_relationships() {

		$funcs = array(
			'delete_quiz',
			'delete_lesson',
			'delete_product',
			'delete_course',
			'delete_section',
			'delete_membership_cleanups',
		);
		foreach ( $funcs as $func ) {
			$this->{$func}();
		}

	}

	/**
	 * Test delete_product_with_active_subscriptions_error_message().
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_delete_product_with_active_subscriptions_error_message() {

		$post_types = array(
			'post',
			'course',
			'llms_membership',
		);

		foreach ( $post_types as $post_type ) {

			// Create post/product.
			$post_id  = $this->factory->post->create( array( 'post_type' => $post_type ) );

			$post_type_object = get_post_type_object( $post_type );
			$post_type_name   = $post_type_object->labels->name;

			$this->assertEquals(
				'post' !== $post_type ?
					sprintf(
						'Sorry, you are not allowed to delete %s with active subscriptions.',
						$post_type_name
					):
					''
				,
				LLMS_Post_Relationships::delete_product_with_active_subscriptions_error_message( $post_id )
			);
		}

	}


	/**
	 * Test maybe_prevent_product_deletion()
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_product_deletion() {

		$post_types = array(
			'post',
			'course',
			'llms_membership',
		);

		foreach ( $post_types as $post_type ) {

			// Create post/product.
			$post_id  = $this->factory->post->create( array( 'post_type' => $post_type ) );

			wp_delete_post( $post_id, true );

			$this->assertEmpty(
				get_post( $post_id ),
				$post_type
			);

		}

		unset( $post_types[0] );

		// Courses and Memberships are deletable if associated to a single-payment order.
		foreach ( $post_types as $post_type ) {

			// Create product.
			$post_id  = $this->factory->post->create( array( 'post_type' => $post_type ) );

			// Create an active subscription per product.
			$order   = $this->get_mock_order();
			$order->set( 'product_id', $post_id );
			$order->set( 'order_type', 'single' );

			wp_delete_post( $post_id );

			$this->assertEmpty(
				get_post( $post_id ),
				$post_type
			);

		}

		// Courses and Memberships are deletable if associated to a recurring payment order depending on whether there are active subscriptions.
		foreach ( array_keys( llms_get_order_statuses( 'recurring' ) ) as $status ) {
			foreach ( $post_types as $post_type ) {

				// Create product.
				$post_id  = $this->factory->post->create( array( 'post_type' => $post_type ) );

				// Create an active subscription per product.
				$order   = $this->get_mock_order();
				$order->set( 'product_id', $post_id );
				$order->set( 'order_type', 'recurring' );
				$order->set( 'status', $status );

				$expected_error_message = LLMS_Post_Relationships::delete_product_with_active_subscriptions_error_message( $post_id );

				try {
					wp_delete_post( $post_id );
				} catch( WPDieException $e ) {
					$this->assertEquals(
						$expected_error_message,
						$e->getMessage()
					);
				}
				// Test if subscription active no deletion occurred.
				$_test = in_array( $status, array( 'llms-active', 'llms-pending-cancel', 'llms-on-hold' ), true ) ? 'assertNotEmpty' : 'assertEmpty';
				$this->$_test(
					get_post( $post_id ),
					"{$post_type} : {$status}"
				);

			}
		}

	}

	/**
	 * Test maybe_prevent_product_deletion() via REST API.
	 *
	 * @since 5.4.0
	 *
	 * @return void
	 */
	public function test_maybe_prevent_product_deletion_rest_api() {

		$post_types = array(
			'course'          => 'courses',
			'llms_membership' => 'memberships',
		);

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Force llms_is_rest.
		add_filter( 'llms_is_rest', '__return_true' );

		// Courses and Memberships are deletable if associated to a recurring payment order depending on whether there are active subscriptions.
		foreach ( array_keys( llms_get_order_statuses( 'recurring' ) ) as $status ) {

			foreach ( $post_types as $post_type => $endpoint ) {

				// Create product.
				$post_id  = $this->factory->post->create( array( 'post_type' => $post_type ) );

				// Create an active subscription per product.
				$order = $this->get_mock_order();

				$order->set( 'product_id', $post_id );
				$order->set( 'order_type', 'recurring' );
				$order->set( 'status', $status );

				$expected_error_message = LLMS_Post_Relationships::delete_product_with_active_subscriptions_error_message( $post_id );

				$request = new WP_REST_Request(
					'DELETE',
					"/llms/v1/{$endpoint}/{$post_id}"
				);

				$request->set_param( 'force', 'true' );
				$res = rest_get_server()->dispatch( $request );

				if ( in_array( $status, array( 'llms-active', 'llms-pending-cancel', 'llms-on-hold' ), true ) ) {
					// Not deleted.
					$this->assertNotEmpty(
						get_post( $post_id ),
						"{$post_type} : {$status}"
					);

					$this->assertEquals( 500, $res->get_status(), "{$post_type} : {$status}" );
					$this->assertEquals( $expected_error_message, $res->get_data()['message'], "{$post_type} : {$status}" );
				} else {
					// Deleted.
					$this->assertEmpty(
						get_post( $post_id ),
						"{$post_type} : {$status}"
					);
				}

			}

		}

		remove_filter( 'llms_is_rest', '__return_true' );

	}
}
