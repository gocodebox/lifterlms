<?php
/**
 * Tests for the Awarded Certificates controller.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_awarded_certificates
 *
 * @since [version]
 */
class LLMS_REST_Test_Awarded_Certificates_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/awarded-certificates';

	/**
	 * WP_User ID of the instructor teaching the student.
	 *
	 * @var int
	 */
	protected $user_instructor;

	/**
	 * WP_User ID of an instructor not teaching the student.
	 *
	 * @var int
	 */
	protected $user_other_instructor;

	/**
	 * WP_User ID of the student holding the awarded certificate.
	 *
	 * @var int
	 */
	protected $user_student;

	/**
	 * WP_Post ID of the certificate template.
	 *
	 * @var int
	 */
	protected $template_id;

	/**
	 * Setup test server, endpoint, users, and an awarded certificate.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->endpoint = new LLMS_REST_Awarded_Certificates_Controller();

		$this->user_instructor       = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$this->user_other_instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$this->user_student          = $this->factory->student->create();

		$course = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course->instructors()->set_instructors( array( array( 'id' => $this->user_instructor ) ) );

		$this->template_id = $this->factory->post->create(
			array(
				'post_type'    => 'llms_certificate',
				'post_content' => '{site_title}, {current_date}',
			)
		);
		update_post_meta( $this->template_id, '_llms_certificate_title', 'Mock Certificate Title' );

		llms_enroll_student( $this->user_student, $course->get( 'id' ) );
		LLMS_Engagement_Handler::handle_certificate(
			array( $this->user_student, $this->template_id, $course->get( 'id' ), null )
		);
	}

	/**
	 * Test list permissions for users without `view_students`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_permissions_unauthorized() {

		wp_set_current_user( 0 );
		$response = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseStatusEquals( 401, $response );

		wp_set_current_user( $this->user_forbidden );
		$response = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseStatusEquals( 403, $response );
	}

	/**
	 * Test admins can list awarded certificates, scoped or unscoped.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_as_admin() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 1, $response->get_headers()['X-WP-Total'] );

		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $this->user_student ) );
		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 1, $response->get_headers()['X-WP-Total'] );
	}

	/**
	 * Test instructors can list awarded certificates of their own students.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_as_instructor_of_student() {

		wp_set_current_user( $this->user_instructor );

		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $this->user_student ) );
		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 1, $response->get_headers()['X-WP-Total'] );
		$this->assertEquals( 1, count( $response->get_data() ) );
	}

	/**
	 * Test instructors cannot list certificates of students they don't teach.
	 *
	 * The request must be rejected outright: returning an empty body while the
	 * `X-WP-Total` header reflects the real count would still disclose how many
	 * certificates the student holds.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_as_instructor_of_other_student_forbidden() {

		wp_set_current_user( $this->user_other_instructor );

		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $this->user_student ) );
		$this->assertResponseStatusEquals( 403, $response );
		$this->assertArrayNotHasKey( 'X-WP-Total', $response->get_headers() );
	}

	/**
	 * Test instructors cannot list awarded certificates without a student filter.
	 *
	 * Unscoped and template-filtered collections span all students, so they require
	 * the `view_others_students` capability which instructors don't have.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_as_instructor_unscoped_forbidden() {

		wp_set_current_user( $this->user_other_instructor );

		$response = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseStatusEquals( 403, $response );

		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'template' => $this->template_id ) );
		$this->assertResponseStatusEquals( 403, $response );
	}
}
