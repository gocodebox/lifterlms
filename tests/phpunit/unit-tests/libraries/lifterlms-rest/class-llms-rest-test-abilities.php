<?php
/**
 * Test the WordPress Abilities API integration.
 *
 * @package LifterLMS_REST/Tests
 *
 * @group abilities
 * @group rest_abilities
 *
 * @since 10.1.0
 */
class LLMS_REST_Test_Abilities extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup the test case.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress version.' );
		}
	}

	/**
	 * Retrieve a registered LifterLMS ability, initializing the abilities registry.
	 *
	 * @since 10.1.0
	 *
	 * @param string $name Un-namespaced ability name.
	 * @return WP_Ability|null
	 */
	private function get_ability( $name ) {
		return wp_get_ability( "lifterlms/{$name}" );
	}

	/**
	 * Test all expected abilities are registered.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_abilities_registered() {

		$expected = array();

		foreach ( array( 'course', 'section', 'lesson', 'membership', 'access-plan', 'student' ) as $resource ) {
			$plural     = 'access-plan' === $resource ? 'access-plans' : "{$resource}s";
			$expected[] = "list-{$plural}";
			$expected[] = "get-{$resource}";
			$expected[] = "create-{$resource}";
			$expected[] = "update-{$resource}";
			$expected[] = "delete-{$resource}";
		}

		$expected = array_merge(
			$expected,
			array(
				'get-course-content',
				'get-course-enrollments',
				'list-enrollments',
				'enroll-student',
				'update-enrollment',
				'unenroll-student',
				'get-progress',
				'update-progress',
				'delete-progress',
			)
		);

		foreach ( $expected as $name ) {
			$this->assertNotNull( $this->get_ability( $name ), "Ability lifterlms/{$name} is not registered." );
		}

		$registered = array_filter(
			array_keys( wp_get_abilities() ),
			function ( $name ) {
				return 0 === strpos( $name, 'lifterlms/' );
			}
		);

		$this->assertEquals( count( $expected ), count( $registered ) );
	}

	/**
	 * Test the LifterLMS ability category is registered.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_category_registered() {

		// Initialize the abilities registry (which registers categories, too).
		wp_get_abilities();

		$category = wp_get_ability_category( 'lifterlms' );

		$this->assertNotNull( $category );
		$this->assertEquals( 'LifterLMS', $category->get_label() );
	}

	/**
	 * Test derived input schemas.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_input_schemas() {

		// List: collection params.
		$schema = $this->get_ability( 'list-courses' )->get_input_schema();
		$this->assertEquals( 'object', $schema['type'] );
		foreach ( array( 'page', 'per_page', 'order', 'orderby', 'include', 'exclude' ) as $param ) {
			$this->assertArrayHasKey( $param, $schema['properties'], $param );
		}

		// No PHP callbacks may leak into the schema.
		$this->assertArrayNotHasKey( 'validate_callback', $schema['properties']['order'] );
		$this->assertArrayNotHasKey( 'sanitize_callback', $schema['properties']['order'] );

		// Get: ID path param is required.
		$schema = $this->get_ability( 'get-course' )->get_input_schema();
		$this->assertArrayHasKey( 'id', $schema['properties'] );
		$this->assertContains( 'id', $schema['required'] );

		// Create: item schema args with required fields.
		$schema = $this->get_ability( 'create-course' )->get_input_schema();
		$this->assertArrayHasKey( 'title', $schema['properties'] );
		$this->assertContains( 'title', $schema['required'] );
		$this->assertContains( 'content', $schema['required'] );

		// Nested routes: both path params required.
		$schema = $this->get_ability( 'enroll-student' )->get_input_schema();
		$this->assertContains( 'id', $schema['required'] );
		$this->assertContains( 'post_id', $schema['required'] );
	}

	/**
	 * Test derived output schemas and annotations.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_output_schemas_and_annotations() {

		$list = $this->get_ability( 'list-courses' );
		$this->assertEquals( 'array', $list->get_output_schema()['type'] );
		$this->assertTrue( $list->get_meta_item( 'annotations' )['readonly'] );
		$this->assertTrue( $list->get_meta_item( 'show_in_rest' ) );

		$get = $this->get_ability( 'get-course' );
		$this->assertEquals( 'object', $get->get_output_schema()['type'] );
		$this->assertTrue( $get->get_meta_item( 'annotations' )['readonly'] );

		$delete = $this->get_ability( 'delete-course' );
		$this->assertArrayHasKey( 'deleted', $delete->get_output_schema()['properties'] );
		$this->assertTrue( $delete->get_meta_item( 'annotations' )['destructive'] );
	}

	/**
	 * Test course create/get/list/delete round trip through ability execution.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_execute_course_crud() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Create.
		$created = $this->get_ability( 'create-course' )->execute(
			array(
				'title'   => 'Abilities API Test Course',
				'content' => 'Course content.',
			)
		);

		$this->assertFalse( is_wp_error( $created ), is_wp_error( $created ) ? $created->get_error_message() : '' );
		$this->assertEquals( 'Abilities API Test Course', $created['title']['rendered'] );

		$course_id = $created['id'];

		// Get.
		$course = $this->get_ability( 'get-course' )->execute( array( 'id' => $course_id ) );
		$this->assertFalse( is_wp_error( $course ) );
		$this->assertEquals( $course_id, $course['id'] );

		// Update.
		$updated = $this->get_ability( 'update-course' )->execute(
			array(
				'id'    => $course_id,
				'title' => 'Abilities API Test Course (Updated)',
			)
		);
		$this->assertFalse( is_wp_error( $updated ), is_wp_error( $updated ) ? $updated->get_error_message() : '' );
		$this->assertEquals( 'Abilities API Test Course (Updated)', $updated['title']['rendered'] );

		// List.
		$list = $this->get_ability( 'list-courses' )->execute( array() );
		$this->assertFalse( is_wp_error( $list ) );
		$this->assertContains( $course_id, wp_list_pluck( $list, 'id' ) );

		// Delete.
		$deleted = $this->get_ability( 'delete-course' )->execute( array( 'id' => $course_id ) );
		$this->assertEquals( array( 'deleted' => true ), $deleted );
	}

	/**
	 * Test enrollment abilities round trip.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_execute_enrollments() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$course_id  = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );

		$enrollment = $this->get_ability( 'enroll-student' )->execute(
			array(
				'id'      => $student_id,
				'post_id' => $course_id,
			)
		);

		$this->assertFalse( is_wp_error( $enrollment ), is_wp_error( $enrollment ) ? $enrollment->get_error_message() : '' );
		$this->assertEquals( 'enrolled', $enrollment['status'] );

		$list = $this->get_ability( 'list-enrollments' )->execute( array( 'id' => $student_id ) );
		$this->assertFalse( is_wp_error( $list ) );
		$this->assertContains( $course_id, wp_list_pluck( $list, 'post_id' ) );

		// Enrollment status rows are ordered by timestamp: wait so the updated status is subsequent to the one set on creation.
		sleep( 1 );

		$updated = $this->get_ability( 'update-enrollment' )->execute(
			array(
				'id'      => $student_id,
				'post_id' => $course_id,
				'status'  => 'expired',
			)
		);
		$this->assertFalse( is_wp_error( $updated ), is_wp_error( $updated ) ? $updated->get_error_message() : '' );
		$this->assertEquals( 'expired', $updated['status'] );

		$deleted = $this->get_ability( 'unenroll-student' )->execute(
			array(
				'id'      => $student_id,
				'post_id' => $course_id,
			)
		);
		$this->assertEquals( array( 'deleted' => true ), $deleted );

		$this->assertFalse( llms_is_user_enrolled( $student_id, $course_id ) );
	}

	/**
	 * Test permission callbacks deny unauthorized users and allow authorized ones.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_permissions() {

		$input = array(
			'title'   => 'Denied Course',
			'content' => 'Content.',
		);

		$ability = $this->get_ability( 'create-course' );

		// Logged out.
		wp_set_current_user( 0 );
		$result = $ability->check_permissions( $input );
		$this->assertTrue( is_wp_error( $result ) || false === $result );

		// Subscriber.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$result = $ability->check_permissions( $input );
		$this->assertTrue( is_wp_error( $result ) || false === $result );

		// Admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( $ability->check_permissions( $input ) );
	}

	/**
	 * Test REST error responses are returned as WP_Error from execution.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_execute_error_passthrough() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$result = $this->get_ability( 'get-course' )->execute( array( 'id' => 99999999 ) );

		$this->assertTrue( is_wp_error( $result ) );
	}
}
