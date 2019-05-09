<?php
/**
 * Test AJAX Handler
 *
 * @package LifterLMS/Tests
 *
 * @group AJAX
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_AJAX_Handler extends LLMS_UnitTestCase {

	public function setUp() {
		parent::setUp();
		add_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	public function tearDown() {
		parent::tearDown();
		remove_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	/**
	 * Call a method for the LLMS_AJAX_Handler class that calls wp_die()
	 *
	 * @since [version]
	 *
	 * @param string $function Method name.
	 * @param array $args $_REQUEST args.
	 * @return array
	 */
	protected function do_ajax( $function, $args = array() ) {

		ob_start();
		$this->mockPostRequest( $args );
		try {
			call_user_func( array( 'LLMS_AJAX_Handler', $function ) );
		} catch ( WPAjaxDieContinueException $e ) {}
		return json_decode( $this->last_response, true );

	}

	/**
	 * Test the select2_query_posts() ajax method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_select2_query_posts() {

		$args = array(
			'post_type' => 'course',
		);

		// No results.
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		$this->factory->post->create_many( 50, array( 'post_type' => 'course' ) );

		// Full result list.
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 30, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $res['more'] );

		// Second page
		$args['page'] = 1;
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 20, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// Term not found
		unset( $args['page'] );
		$args['term'] = 'arstarstarst';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );

		// Term found.
		$args['term'] = 'title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( count( $res['items'] ) >= 1 );

		$this->factory->post->create_many( 5, array( 'post_title' => 'search title' ) );
		$this->factory->post->create_many( 5, array( 'post_type' => 'course', 'post_title' => 'search title' ) );

		// multiple post types
		$args['post_type'] .= ',post';
		$args['term'] = 'search title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'post', $res['items'] ) );
		$this->assertSame( 'Posts', $res['items']['post']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['post'] ) );
		$this->assertTrue( array_key_exists( 'course', $res['items'] ) );
		$this->assertSame( 'Courses', $res['items']['course']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['course'] ) );

	}

	/**
	 * Catch wp_die() called by ajax methods & store the output buffer contents for use later.
	 *
	 * @since [version]
	 *
	 * @param string $msg Die msg.
	 * @return void
	 */
	public function _wp_die_handler( $msg ) {
		$this->last_response = ob_get_clean();
		throw new WPAjaxDieContinueException( $msg );
	}

}
