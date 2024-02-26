<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_lesson
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since 3.36.2
 * @version 3.36.2
 */
class LLMS_Test_Meta_Box_Lesson extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since 3.36.2
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Lesson();

	}

	/**
	 * Test get fields.
	 *
	 * @since 3.36.2
	 *
	 * @return void
	 */
	public function test_get_fields() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );
		$post   = $lesson->get( 'post' );
		$this->metabox->post = $post;

		// check the lessons Drip Settings methods list does not cointain 'start',
		// as the course has no start date set.
		foreach ( $this->metabox->get_fields() as $index => $f ) {
			if ( 'Drip Settings' === $f['title'] ) {
				$this->assertFalse( array_key_exists( 'start', $f['fields'][1]['value'] ) );
				break;
			}
		}

		// set a course start date.
		$course->set( 'start_date', current_time( 'm/d/Y' ) );
		// check the lessons Drip Settings methods list contains 'start',
		// as the course now has a start date set.
		foreach ( $this->metabox->get_fields() as $index => $f ) {
			if ( 'Drip Settings' === $f['title'] ) {
				$this->assertTrue( array_key_exists( 'start', $f['fields'][1]['value'] ) );
				break;
			}
		}

	}

}
