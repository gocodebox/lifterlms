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
}
