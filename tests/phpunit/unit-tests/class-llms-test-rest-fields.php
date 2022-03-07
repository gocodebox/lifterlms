<?php
/**
 * Tests for LLMS_REST_Fields class
 *
 * @package LifterLMS/Tests
 *
 * @group rest
 * @group rest_fields
 *
 * @since 6.0.0
 */
class LLMS_Test_REST_Fields extends LLMS_REST_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		if ( ! llms_is_block_editor_supported_for_certificates() ) {
			$this->markTestSkipped( 'REST endpoints are not supported for certificates on this version of WordPress.' );
		}

		parent::set_up();

		array_map( 'unregister_post_type', array( 'llms_certificate', 'llms_my_certificate' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		LLMS_Post_Types::register_post_types();

		do_action( 'rest_api_init' );

	}

	/**
	 * Retrieves a rest route for a given post type.
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type A WP_Post_Type name.
	 * @return string
	 */
	private function get_route( $post_type ) {
		return "/wp/v2/{$post_type}";
	}

	/**
	 * Test certificate rest fields
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_register_fields_for_certificates() {

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$route = $this->get_route( $post_type );

			// Test schema registration.
			$opts  = $this->perform_mock_request( 'OPTIONS', $route )->get_data();
			$props = $opts['schema']['properties'];
			$fields = LLMS_Unit_Test_Util::call_method( new LLMS_REST_Fields(), 'get_fields_for_certificates' );
			foreach ( $fields as $key => $schema ) {
				$this->assertArrayHasKey( "certificate_{$key}", $props );
				$schema['context'] = array( 'view', 'edit' );
				$this->assertEquals( $schema, $props["certificate_{$key}"] );
			}

			// Create a new certificate with field data: tests the update callback.
			$create = $this->perform_mock_request( 'POST', $route, array(
				'certificate_size'        => 'A3',
				'certificate_orientation' => 'landscape',
				'certificate_background'  => '#000000',
				'certificate_margins'     => array( 15, 25, 0, 5.501 ),
			) );
			$this->assertResponseStatusEquals( 201, $create );

			// Retrieve the field: tests the get callback.
			$get = $this->perform_mock_request( 'GET', $route . '/' . $create->get_data()['id'] );
			$this->assertResponseStatusEquals( 200, $get );

			$data = $get->get_data();
			$this->assertEquals( 'A3', $data['certificate_size'] );
			$this->assertEquals( 297, $data['certificate_width'] );
			$this->assertEquals( 420, $data['certificate_height'] );
			$this->assertEquals( 'mm', $data['certificate_unit'] );
			$this->assertEquals( 'landscape', $data['certificate_orientation'] );
			$this->assertEquals( array( 15, 25, 0, 5.501 ), $data['certificate_margins'] );
			$this->assertEquals( '#000000', $data['certificate_background'] );

		}

	}

	/**
	 * Test register_fields_for_certificate_awards()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_register_fields_for_certificate_awards() {

		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );

		$route = $this->get_route( 'llms_my_certificate' );

		// Test field registration.
		$opts  = $this->perform_mock_request( 'OPTIONS', $route )->get_data();
		$props = $opts['schema']['properties'];

		$this->assertArrayHasKey( 'certificate_template', $props );

		// Create a new certificate with field data: tests the update callback.
		$create = $this->perform_mock_request( 'POST', $route, array(
			'certificate_template' => $template_id,
		) );
		$this->assertResponseStatusEquals( 201, $create );
		$this->assertEquals( $template_id, $create->get_data()['certificate_template'] );

		$created_route = $route . '/' . $create->get_data()['id'];

		// Validation error.
		$create_with_error = $this->perform_mock_request( 'POST', $created_route, array(
			'certificate_template' => $this->factory->post->create(),
		) );
		$this->assertResponseStatusEquals( 400, $create_with_error );
		$this->assertResponseCodeEquals( 'rest_invalid_param', $create_with_error );
		$this->assertArrayHasKey( 'certificate_template', $create_with_error->get_data()['data']['params'] );

		// Remove the template (no error).
		$update = $this->perform_mock_request( 'POST', $created_route, array(
			'certificate_template' => 0,
		) );
		$this->assertResponseStatusEquals( 200, $update );

		// Retrieve the field: tests the get callback.
		$get = $this->perform_mock_request( 'GET', $created_route );
		$this->assertResponseStatusEquals( 200, $get );
		$this->assertEquals( 0, $get->get_data()['certificate_template'] );

	}

	/**
	 * Test register_fields_for_certificate_templates()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_register_fields_for_certificate_templates() {

		$route = $this->get_route( 'llms_certificate' );

		// Test field registration.
		$opts  = $this->perform_mock_request( 'OPTIONS', $route )->get_data();
		$props = $opts['schema']['properties'];

		$this->assertArrayHasKey( 'certificate_title', $props );
		$this->assertArrayHasKey( 'certificate_sequential_id', $props );

		// Create a new certificate with field data: tests the update callback.
		$create = $this->perform_mock_request( 'POST', $route, array(
			'certificate_title'         => 'Title',
			'certificate_sequential_id' => 25,
		) );
		$this->assertResponseStatusEquals( 201, $create );

		$created_route = $route . '/' . $create->get_data()['id'];

		// Validation error on sequential id.
		$create_with_error = $this->perform_mock_request( 'POST', $created_route, array(
			'certificate_sequential_id' => 10,
		) );
		$this->assertResponseStatusEquals( 400, $create_with_error );
		$this->assertResponseCodeEquals( 'rest_invalid_param', $create_with_error );
		$this->assertArrayHasKey( 'certificate_sequential_id', $create_with_error->get_data()['data']['params'] );

		// Retrieve the field: tests the get callback.
		$get = $this->perform_mock_request( 'GET', $created_route );
		$this->assertResponseStatusEquals( 200, $get );

		$data = $get->get_data();
		$this->assertEquals( 'Title', $data['certificate_title'] );
		$this->assertEquals( 25, $data['certificate_sequential_id'] );

	}

}
