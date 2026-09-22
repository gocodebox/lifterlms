<?php
/**
 * Test registration of routes added with the quizzes/orders/certificates REST expansion.
 *
 * @package LifterLMS_REST/Tests
 *
 * @group REST
 * @group rest_routes
 *
 * @since 10.2.0
 */
class LLMS_REST_Test_New_Routes_Registration extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Test all new routes are registered.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_new_routes_registered() {

		$routes = rest_get_server()->get_routes();

		$expected = array(
			'/llms/v1/orders',
			'/llms/v1/orders/(?P<id>[\d]+)',
			'/llms/v1/orders/(?P<id>[\d]+)/transactions',
			'/llms/v1/quizzes',
			'/llms/v1/quizzes/(?P<id>[\d]+)',
			'/llms/v1/quizzes/(?P<quiz_id>[\d]+)/questions',
			'/llms/v1/questions',
			'/llms/v1/questions/(?P<id>[\d]+)',
			'/llms/v1/quiz-attempts',
			'/llms/v1/quiz-attempts/(?P<id>[\d]+)',
			'/llms/v1/quiz-attempts/(?P<id>[\d]+)/grade',
			'/llms/v1/certificates',
			'/llms/v1/certificates/(?P<id>[\d]+)',
			'/llms/v1/awarded-certificates',
			'/llms/v1/awarded-certificates/(?P<id>[\d]+)',
			'/llms/v1/students/(?P<id>[\d]+)/grades',
		);

		foreach ( $expected as $route ) {
			$this->assertArrayHasKey( $route, $routes, $route );
		}
	}

	/**
	 * Test orders routes are read-only.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_orders_routes_read_only() {

		$routes = rest_get_server()->get_routes();

		foreach ( array( '/llms/v1/orders', '/llms/v1/orders/(?P<id>[\d]+)' ) as $route ) {
			foreach ( $routes[ $route ] as $endpoint ) {
				$this->assertEquals( array( 'GET' => true ), $endpoint['methods'], $route );
			}
		}
	}
}
