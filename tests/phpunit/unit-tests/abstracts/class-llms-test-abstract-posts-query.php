<?php
/**
 * Tests for the LLMS_Abstract_Posts_Query class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group query
 * @group abstract_posts_query
 *
 * @since [version]
 */
class LLMS_Test_Abstract_Posts_Query extends LLMS_UnitTestCase {

	/**
	 * Set up the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = $this->get_stub();

	}

	/**
	 * Retrieve a mocked abstract.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Abstract_Posts_Query
	 */
	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Abstract_Posts_Query' );

		LLMS_Unit_Test_Util::set_private_property( $stub, 'id', 'mock' );

		return $stub;

	}

	/**
	 * Test count_results(), get_number_results(), get_found_results(), get_max_pages(), and has_results().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_count_results() {

		// No results.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wp_query', (object) array(
			'post_count'    => 0,
			'max_num_pages' => 0,
			'found_posts'   => 0,
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'count_results' );

		$this->assertEquals( 0, $this->main->get_number_results() );
		$this->assertEquals( 0, $this->main->get_found_results() );
		$this->assertEquals( 0, $this->main->get_max_pages() );
		$this->assertFalse( $this->main->has_results() );

		// 52 Results.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'wp_query', (object) array(
			'post_count'    => 10,
			'max_num_pages' => 3,
			'found_posts'   => 25,
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'count_results' );

		$this->assertEquals( 10, $this->main->get_number_results() );
		$this->assertEquals( 25, $this->main->get_found_results() );
		$this->assertEquals( 3, $this->main->get_max_pages() );
		$this->assertTrue( $this->main->has_results() );

	}

	/**
	 * Test default_arguments()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_default_arguments() {

		LLMS_Unit_Test_Util::set_private_property( $this->main, 'allowed_post_types', array( 'post' ) );

		$defaults = LLMS_Unit_Test_Util::call_method( $this->main, 'default_arguments' );

		$this->assertEquals( 1, $defaults['page'] );
		$this->assertEquals( 10, $defaults['per_page'] );
		$this->assertEquals( 'all', $defaults['fields'] );
		$this->assertEquals( 'publish', $defaults['status'] );
		$this->assertEquals( array( 'post' ), $defaults['post_types'] );
		$this->assertEquals(
			array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
			$defaults['sort']
		);

	}

	/**
	 * Test prepare_query()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_prepare_query() {

		$defaults = LLMS_Unit_Test_Util::call_method( $this->main, 'default_arguments' );
		$query    = $this->main->get_query();

		$this->assertEquals( $defaults['page'], $query['paged'] );
		$this->assertEquals( $defaults['per_page'], $query['posts_per_page'] );
		$this->assertEquals( $defaults['post_types'], $query['post_type'] );
		$this->assertEquals( $defaults['search'], $query['s'] );
		$this->assertEquals( $defaults['sort'], $query['orderby'] );
		$this->assertEquals( $defaults['status'], $query['post_status'] );

	}

	/**
	 * Test get() and set() and additionally test sanitize_post_types().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_set() {

		// Make sure post type sanitization works.
		$post_types = array( 'post' );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'allowed_post_types', $post_types );

		$this->main->set( 'post_types', array( 'fake', 'post' ) );
		$this->assertEquals( $post_types, $this->main->get( 'post_types' ) );

		$this->main->set( 'post_types', 'fake' );
		$this->assertEquals( array(), $this->main->get( 'post_types' ) );

		// Test something else/
		$this->main->set( 'status', 'draft' );
		$this->assertEquals( 'draft', $this->main->get( 'status' ) );

	}

}
