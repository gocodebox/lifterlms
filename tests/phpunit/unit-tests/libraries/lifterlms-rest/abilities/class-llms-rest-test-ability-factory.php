<?php
/**
 * Tests for LLMS_REST_Ability_Factory.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_abilities
 *
 * @since [version]
 */
class LLMS_REST_Test_Ability_Factory extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Nested ability config with a single non-id path param.
	 *
	 * @return array
	 */
	private function nested_config() {
		return array(
			'method'    => 'GET',
			'route'     => '/llms/v1/quizzes/{quiz_id}/questions',
			'operation' => 'list',
			'controller' => 'LLMS_REST_Questions_Controller',
		);
	}

	/**
	 * Test that `id` is copied onto a unique non-id path parameter.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_build_request_aliases_id_to_parent_path_param() {

		$request = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'build_request',
			array(
				$this->nested_config(),
				array( 'id' => 897 ),
			)
		);

		$this->assertEquals( 897, $request['quiz_id'] );
		$this->assertSame( '/llms/v1/quizzes/897/questions', $request->get_route() );
		$this->assertArrayNotHasKey( 'id', $request->get_query_params() );
	}

	/**
	 * Test that an explicit parent path param is not overwritten by `id`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_build_request_prefers_named_parent_path_param() {

		$request = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'build_request',
			array(
				$this->nested_config(),
				array(
					'quiz_id' => 897,
					'id'      => 1,
				),
			)
		);

		$this->assertEquals( 897, $request['quiz_id'] );
	}

	/**
	 * Test input schema accepts either the named parent param or `id`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_input_schema_accepts_id_alias() {

		$schema = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'get_input_schema',
			array( $this->nested_config() )
		);

		$this->assertArrayHasKey( 'id', $schema['properties'] );
		$this->assertArrayHasKey( 'quiz_id', $schema['properties'] );
		$this->assertArrayHasKey( 'anyOf', $schema );
	}

	/**
	 * Test that read operations default the context to edit.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_read_operations_default_to_edit_context() {

		$list_config = array(
			'method'     => 'GET',
			'route'      => '/llms/v1/courses',
			'operation'  => 'list',
			'controller' => 'LLMS_REST_Courses_Controller',
		);

		$schema = LLMS_Unit_Test_Util::call_method( 'LLMS_REST_Ability_Factory', 'get_input_schema', array( $list_config ) );
		$this->assertEquals( 'edit', $schema['properties']['context']['default'] );

		$defaults = LLMS_Unit_Test_Util::call_method( 'LLMS_REST_Ability_Factory', 'get_default_params', array( $list_config ) );
		$this->assertEquals( 'edit', $defaults['context'] );

		$get_config = array_merge( $this->get_item_config() );

		$schema = LLMS_Unit_Test_Util::call_method( 'LLMS_REST_Ability_Factory', 'get_input_schema', array( $get_config ) );
		$this->assertEquals( 'edit', $schema['properties']['context']['default'] );

		$defaults = LLMS_Unit_Test_Util::call_method( 'LLMS_REST_Ability_Factory', 'get_default_params', array( $get_config ) );
		$this->assertEquals( 'edit', $defaults['context'] );

		// Explicit input still wins over the default.
		$request = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'build_request',
			array(
				array_merge( $get_config, array( 'default_params' => $defaults ) ),
				array(
					'id'      => 123,
					'context' => 'view',
				),
			)
		);
		$this->assertEquals( 'view', $request['context'] );
	}

	/**
	 * Item ability config used by permission tests.
	 *
	 * @return array
	 */
	private function get_item_config() {
		return array(
			'method'     => 'GET',
			'route'      => '/llms/v1/courses/{id}',
			'operation'  => 'get',
			'controller' => 'LLMS_REST_Courses_Controller',
		);
	}

	/**
	 * Test that a REST 404 from the permission check is treated as allowed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_check_permission_allows_not_found() {

		wp_set_current_user( $this->user_allowed );

		$allowed = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'check_permission',
			array(
				$this->get_item_config(),
				array( 'id' => 99999999 ),
			)
		);

		$this->assertTrue( $allowed );
	}

	/**
	 * Test that authorization failures from the permission check remain denied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_check_permission_denies_unauthorized() {

		wp_set_current_user( 0 );

		$allowed = LLMS_Unit_Test_Util::call_method(
			'LLMS_REST_Ability_Factory',
			'check_permission',
			array(
				array(
					'method'     => 'POST',
					'route'      => '/llms/v1/courses',
					'operation'  => 'create',
					'controller' => 'LLMS_REST_Courses_Controller',
				),
				array(
					'title'   => 'Nope',
					'content' => 'Nope',
				),
			)
		);

		$this->assertFalse( $allowed );
	}
}
