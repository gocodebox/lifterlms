<?php
/**
 * Tests for LLMS_REST_Fields class
 *
 * @package LifterLMS/Tests
 *
 * @group rest
 * @group rest_fields
 *
 * @since [version]
 */
class LLMS_Test_REST_Fields extends LLMS_REST_Unit_Test_Case {

	/**
	 * Test certificate rest fields
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_fields_for_certificates() {

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			unregister_post_type( $post_type );
			wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
			LLMS_Post_Types::register_post_types();
			do_action( 'rest_api_init' );

			$route = "/wp/v2/{$post_type}";

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
			$get = $this->perform_mock_request( 'GET', $route . '/' . $create->get_data()['id'], array(
				'certificate_size'        => 'A3',
				'certificate_orientation' => 'landscape',
				'certificate_background'  => '#000000',
				'certificate_margins'     => array( 15, 25, 0, 5.501 ),
			) );
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

}
