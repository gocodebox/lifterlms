<?php
/**
 * Test API Keys Query class.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group webhooks
 * @group webhooks_query
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Webhooks_Query extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup test.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function setUp() {

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_webhooks" );

	}

	/**
	 * Test the get_webhooks() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_webhooks() {

		$query = new LLMS_REST_Webhooks_Query();
		$this->assertEquals( array(), $query->get_webhooks() );

		$this->create_many_webhooks( 3 );

		foreach ( array( true, false ) as $with_filters ) {

			$query = new LLMS_REST_Webhooks_Query( array( 'suppress_filters' => $with_filters ) );
			$keys = $query->get_webhooks();
			foreach ( $keys as $key ) {
				$this->assertTrue( is_a( $key, 'LLMS_REST_Webhook' ) );
			}

		}


	}

	/**
	 * Test the include and exclude arguments.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_include_and_exclude() {

		$this->create_many_webhooks( 5 );
		$query = new LLMS_REST_Webhooks_Query( array() );
		$ids = range( 1, 5 );

		$this->create_many_webhooks( 5 );

		$query = new LLMS_REST_Webhooks_Query( array(
			'include' => $ids,
		) );
		$this->assertEquals( 5, $query->found_results );
		$this->assertEquals( $ids, wp_list_pluck( $query->get_results(), 'id' ) );

		$query = new LLMS_REST_Webhooks_Query( array(
			'exclude' => $ids,
		) );
		$this->assertEquals( 5, $query->found_results );
		$this->assertEquals( range( 6, 10 ), wp_list_pluck( $query->get_results(), 'id' ) );

	}

	/**
	 * Test pagination of query results.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_pagination() {

		$this->create_many_webhooks( 25 );

		$query = new LLMS_REST_Webhooks_Query( array() );
		$this->assertEquals( 10, count( $query->get_results() ) );
		$this->assertEquals( 25, $query->found_results );
		$this->assertEquals( 3, $query->max_pages );
		$this->assertEquals( range( 1, 10 ), wp_list_pluck( $query->get_results(), 'id' ) );
		$this->assertTrue( $query->is_first_page() );

		$query = new LLMS_REST_Webhooks_Query( array( 'page' => 2 ) );
		$this->assertEquals( range( 11, 20 ), wp_list_pluck( $query->get_results(), 'id' ) );

		$query = new LLMS_REST_Webhooks_Query( array( 'page' => 3 ) );
		$this->assertEquals( range( 21, 25 ), wp_list_pluck( $query->get_results(), 'id' ) );
		$this->assertTrue( $query->is_last_page() );

	}

	/**
	 * Test the status filter arguments.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_status() {

		$this->create_many_webhooks( 5, 'active' );
		$this->create_many_webhooks( 5, 'disabled' );
		$this->create_many_webhooks( 5, 'paused' );

		$query = new LLMS_REST_Webhooks_Query( array() );
		$this->assertEquals( 15, $query->found_results );

		foreach ( array_keys( LLMS_REST_API()->webhooks()->get_statuses() ) as $status ) {

			$query = new LLMS_REST_Webhooks_Query( array( 'status' => $status ) );
			$this->assertEquals( 5, $query->found_results );

		}

	}

	/**
	 * Test setting up default query args.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_setup_with_defaults() {

		$query = new LLMS_REST_Webhooks_Query();

		$args = array(
			'include' => array(),
			'exclude' => array(),
			'page' => 1,
			'per_page' => 10,
			'sort' => array(
				'id' => 'ASC'
			),
		);

		foreach ( $args as $arg => $expect ) {
			$this->assertEquals( $expect, $query->get( $arg ), $arg );
		}

	}

	/**
	 * Tests setting up all possible arguments with custom arguments.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_setup_with_custom() {

		$args = array(
			'include' => array( 1, 2, 3 ),
			'exclude' => array( 4, 5, 6 ),
			'page' => 5,
			'per_page' => 500,
			'sort' => array(
				'name' => 'ASC',
				'id' => 'DESC',
			),
		);

		$query = new LLMS_REST_Webhooks_Query( $args );

		foreach ( $args as $arg => $expect ) {
			$this->assertEquals( $expect, $query->get( $arg ), $arg );
		}

	}

	/**
	 * Merge default with custom arguments on setup.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_query_args_setup_with_merge() {

		$args = array(
			'include' => array( 1, 2, 3 ),
			'exclude' => array( 4, 5, 6 ),
			'sort' => array(
				'name' => 'ASC',
				'id' => 'DESC',
			),
		);

		$query = new LLMS_REST_Webhooks_Query( $args );

		$args['page'] = 1;
		$args['per_page'] = 10;

		foreach ( $args as $arg => $expect ) {
			$this->assertEquals( $expect, $query->get( $arg ), $arg );
		}

	}

}
