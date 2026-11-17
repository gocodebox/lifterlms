<?php
/**
 * LifterLMS REST API Users Unit Test Case Bootstrap
 *
 * @package LifterLMS_REST_API/Tests
 *
 * @since 1.0.0-beta.27
 */

require_once 'class-llms-rest-unit-test-case-server.php';

class LLMS_REST_Unit_Test_Case_Users extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Test item schema with meta fields.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_schema_with_meta() {

		global $wp_meta_keys;
		$original_wp_meta_keys = $wp_meta_keys;

		// Create a user first.
		wp_set_current_user( $this->user_allowed );

		$user_id = $this->create_user();

		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $user_id );

		// Expect the 'meta' property to be added to the schema.
		$this->assertEquals(
			array(),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		/**
		 * Note:
		 * There are no subtypes for the user meta type.
		 * {@see WP_REST_User_Meta_Fields::get_meta_subtype()}
		 */
		// Register a meta, show it in rest.
		register_meta(
			'user',
			'meta_test',
			array(
				'description'  => 'Meta test',
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// Register a meta, do not show in rest.
		register_meta(
			'user',
			'meta_test_not_in_rest',
			array(
				'description'  => 'Meta test',
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => false,
			)
		);

		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $user_id );
		$this->assertEquals(
			array( 'meta_test' ),
			array_keys( $response->get_data()['meta'] ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Register meta which are not allowed because potentially covered by the schema.
		$disallowed_meta_fields = LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'disallowed_meta_fields' );

		foreach ( $disallowed_meta_fields as $meta_field ) {
			register_meta(
				'user',
				$meta_field,
				array(
					'description'  => 'Meta test',
					'type'         => 'string',
					'single'       => true,
					'show_in_rest' => true,
				)
			);
		}

		// Meta above not registered.
		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $user_id );
		$this->assertEquals(
			array( 'meta_test' ),
			array_keys( $response->get_data()['meta'] ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Unregister meta.
		$wp_meta_keys = $original_wp_meta_keys;

	}


	/**
	 * Test setting an unregistered meta.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_set_unregistered_meta() {

		wp_set_current_user( $this->user_allowed );

		// Set a meta which is not registered.
		$meta_key = uniqid();

		// On creation.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'email'   => 'mock@mock.mock',
				'meta' => array(
					$meta_key => 'whatever',
				),
			)
		);

		$this->assertEquals(
			array(),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check there's no user meta set.
		$this->assertEmpty(
			get_user_meta( $response->get_data()['id'], $meta_key ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Update.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'meta' => array(
					$meta_key => 'whatever',
				),
			)
		);

		$this->assertEquals(
			array(),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check there's no user meta set.
		$this->assertEmpty(
			get_user_meta( $response->get_data()['id'], $meta_key ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

	}

	/**
	 * Test setting an registered meta not available in rest.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_set_registered_meta_not_in_rest() {

		global $wp_meta_keys;
		$original_wp_meta_keys = $wp_meta_keys;

		wp_set_current_user( $this->user_allowed );

		// Register a meta, do not show in rest.
		$meta_key = uniqid();
		register_meta(
			'user',
			$meta_key,
			array(
				'description'       => 'Meta test',
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
			)
		);

		// On creation.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'email' => 'mock@mock.mock',
				'meta'  => array(
					$meta_key => 'whatever',
				),
			)
		);

		$this->assertEquals(
			array(),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check there's no user meta set.
		$this->assertEmpty(
			get_user_meta( $response->get_data()['id'], $meta_key ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Update.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'meta' => array(
					$meta_key => 'whatever update',
				),
			)
		);

		$this->assertEquals(
			array(),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check there's no user meta set.
		$this->assertEmpty(
			get_user_meta( $response->get_data()['id'], $meta_key, true ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Unregister meta.
		$wp_meta_keys = $original_wp_meta_keys;

	}

	/**
	 * Test setting a registered meta.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_set_registered_meta() {

		$this->markTestSkipped( 'Fails randomly.' );

		global $wp_meta_keys;
		$original_wp_meta_keys = $wp_meta_keys;

		wp_set_current_user( $this->user_allowed );

		// Register a meta and set it.
		$meta_key = uniqid();
		register_meta(
			'user',
			$meta_key,
			array(
				'description'    => 'Meta test',
				'type'           => 'string',
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => '__return_true',
			)
		);

		// On creation.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'email' => 'mock@mock.mock',
				'meta'  => array(
					$meta_key => 'whatever',
				),
			)
		);

		$this->assertEquals(
			array(
				$meta_key => 'whatever',
			),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check the meta.
		$this->assertEquals(
			'whatever',
			get_user_meta( $response->get_data()['id'], $meta_key, true ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Update.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'meta' => array(
					$meta_key => 'whatever update',
				),
			)
		);

		$this->assertEquals(
			array(
				$meta_key => 'whatever update',
			),
			$response->get_data()['meta'],
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Check the meta.
		$this->assertEquals(
			'whatever update',
			get_user_meta( $response->get_data()['id'], $meta_key, true ),
			LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
		);

		// Unregister meta.
		$wp_meta_keys = $original_wp_meta_keys;

	}

	/**
	 * Create resource.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return mixed The resource identifier.
	 */
	protected function create_resource() {
		return $this->create_user();
	}

	/**
	 * Get resource creation args.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	protected function get_creation_args() {
		return array(
			'email' => 'mock@mock.mock',
		);
	}

}
