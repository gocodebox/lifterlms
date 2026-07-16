<?php
/**
 * Test API Keys Query class.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group api_keys
 * @group api_keys_query
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_API_Keys_Query extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup test.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.8 Added call to parent::setup() so that $this->factory is set.
	 *
	 * @return void
	 */
	public function set_up() {

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_api_keys" );
		parent::set_up();
	}

	/**
	 * Test the get_keys() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_keys() {

		$query = new LLMS_REST_API_Keys_Query();
		$this->assertEquals( array(), $query->get_keys() );

		$this->create_many_api_keys( 3 );

		foreach ( array( true, false ) as $with_filters ) {

			$query = new LLMS_REST_API_Keys_Query( array( 'suppress_filters' => $with_filters ) );
			$keys = $query->get_keys();
			foreach ( $keys as $key ) {
				$this->assertTrue( is_a( $key, 'LLMS_REST_API_Key' ) );
			}

		}


	}

	/**
	 * Test the include and exclude arguments.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta-24 Fixed access of protected LLMS_Abstract_Query properties.
	 *
	 * @return void
	 */
	public function test_query_args_include_and_exclude() {

		$this->create_many_api_keys( 5 );
		$query = new LLMS_REST_API_Keys_Query( array() );
		$ids = range( 1, 5 );

		$this->create_many_api_keys( 5 );

		$query = new LLMS_REST_API_Keys_Query( array(
			'include' => $ids,
		) );
		$this->assertEquals( 5, $query->get_found_results() );
		$this->assertEquals( $ids, wp_list_pluck( $query->get_results(), 'id' ) );

		$query = new LLMS_REST_API_Keys_Query( array(
			'exclude' => $ids,
		) );
		$this->assertEquals( 5, $query->get_found_results() );
		$this->assertEquals( range( 6, 10 ), wp_list_pluck( $query->get_results(), 'id' ) );

	}

	/**
	 * Test pagination of query results.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta-24 Fixed access of protected LLMS_Abstract_Query properties.
	 *
	 * @return void
	 */
	public function test_query_args_pagination() {

		$this->create_many_api_keys( 25 );

		$query = new LLMS_REST_API_Keys_Query( array() );
		$this->assertEquals( 10, count( $query->get_results() ) );
		$this->assertEquals( 25, $query->get_found_results() );
		$this->assertEquals( 3, $query->get_max_pages() );
		$this->assertEquals( range( 1, 10 ), wp_list_pluck( $query->get_results(), 'id' ) );
		$this->assertTrue( $query->is_first_page() );

		$query = new LLMS_REST_API_Keys_Query( array( 'page' => 2 ) );
		$this->assertEquals( range( 11, 20 ), wp_list_pluck( $query->get_results(), 'id' ) );

		$query = new LLMS_REST_API_Keys_Query( array( 'page' => 3 ) );
		$this->assertEquals( range( 21, 25 ), wp_list_pluck( $query->get_results(), 'id' ) );
		$this->assertTrue( $query->is_last_page() );

	}

	/**
	 * Test the permissions arguments.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta-24 Fixed access of protected LLMS_Abstract_Query properties.
	 *
	 * @return void
	 */
	public function test_query_args_permissions() {

		$this->create_many_api_keys( 5, 'read' );
		$this->create_many_api_keys( 5, 'read_write' );
		$this->create_many_api_keys( 5, 'write' );

		$query = new LLMS_REST_API_Keys_Query( array() );
		$this->assertEquals( 15, $query->get_found_results() );

		foreach ( array_keys( LLMS_REST_API()->keys()->get_permissions() ) as $pem ) {

			$query = new LLMS_REST_API_Keys_Query( array( 'permissions' => $pem ) );
			$this->assertEquals( 5, $query->get_found_results() );

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

		$query = new LLMS_REST_API_Keys_Query();

		$args = array(
			'include' => array(),
			'exclude' => array(),
			'permissions' => '',
			'user' => array(),
			'user_not_in' => array(),
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
			'permissions' => 'read',
			'user' => array( 3 ),
			'user_not_in' => array( 234, 432 ),
			'page' => 5,
			'per_page' => 500,
			'sort' => array(
				'description' => 'ASC',
				'id' => 'DESC',
			),
		);

		$query = new LLMS_REST_API_Keys_Query( $args );

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
				'description' => 'ASC',
				'id' => 'DESC',
			),
		);

		$query = new LLMS_REST_API_Keys_Query( $args );

		$args['page'] = 1;
		$args['per_page'] = 10;
		$args['permissions'] = '';
		$args['user'] = array();
		$args['user_not_in'] = array();

		foreach ( $args as $arg => $expect ) {
			$this->assertEquals( $expect, $query->get( $arg ), $arg );
		}

	}

	/**
	 * Test the users include and exclude arguments.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta-24 Fixed access of protected LLMS_Abstract_Query properties.
	 *
	 * @return void
	 */
	public function test_query_args_users_in_and_not_in() {

		$uid = $this->factory->user->create();

		$this->create_many_api_keys( 5 );
		$this->create_many_api_keys( 5, 'read', $uid );

		$query = new LLMS_REST_API_Keys_Query( array(
			'user' => $uid,
		) );
		$res_1 = wp_list_pluck( $query->get_results(), 'id' );
		$this->assertEquals( 5, $query->get_found_results() );

		$query = new LLMS_REST_API_Keys_Query( array(
			'user_not_in' => $uid,
		) );
		$res_2 = wp_list_pluck( $query->get_results(), 'id' );
		$this->assertEquals( 5, $query->get_found_results() );
		$this->assertTrue( $res_1 !== $res_2 );

	}

}
