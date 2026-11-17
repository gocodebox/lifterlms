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

	/**
	 * Test the drip date and time inputs show stored values in every stored format and save them in the builder's formats.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_drip_date_time() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson = llms_get_post( $course->get_lessons( 'ids' )[0] );
		$this->metabox->post = $lesson->get( 'post' );

		$get_drip_fields = function () {
			$fields = array();
			foreach ( $this->metabox->get_fields() as $f ) {
				if ( 'Drip Settings' === $f['title'] ) {
					foreach ( $f['fields'] as $field ) {
						$fields[ $field['id'] ] = $field;
					}
				}
			}
			return $fields;
		};

		// Stored value => value shown in the input.
		$dates = array(
			'2026-11-24' => '2026-11-24',
			'12/12/2026' => '2026-12-12',
			'3/5/2027'   => '2027-03-05',
			'23'         => '',
		);
		$times = array(
			'09:00 AM' => '09:00',
			'12:00 AM' => '00:00',
			'12:30 PM' => '12:30',
			'14:30:00' => '14:30',
			'9:05 pm'  => '21:05',
			'1'        => '',
			'13:00 PM' => '',
		);
		foreach ( array_map( null, array_keys( $dates ), array_keys( $times ) ) as list( $date, $time ) ) {
			$lesson->set( 'date_available', $date );
			$lesson->set( 'time_available', $time );
			$fields = $get_drip_fields();
			if ( null !== $date ) {
				$this->assertSame( 'date', $fields['_llms_date_available']['input_type'] );
				$this->assertSame( $dates[ $date ], $fields['_llms_date_available']['meta'], $date );
			}
			if ( null !== $time ) {
				$this->assertSame( 'time', $fields['_llms_time_available']['input_type'] );
				$this->assertSame( $times[ $time ], $fields['_llms_time_available']['meta'], $time );
			}
		}

		// Posted value => stored value.
		$saves = array(
			array( '2030-12-25', '17:15', '2030-12-25', '05:15 PM' ),
			array( '2030-12-25', '00:05', '2030-12-25', '12:05 AM' ),
			array( '23', '1', '', '' ),
			array( '12/25/2030', '05:15 PM', '', '' ),
			array( '2030-12-25', '', '2030-12-25', '' ),
		);
		foreach ( $saves as list( $post_date, $post_time, $stored_date, $stored_time ) ) {
			$this->mockPostRequest(
				$this->add_nonce_to_array(
					array(
						'_llms_drip_method'    => 'date',
						'_llms_date_available' => $post_date,
						'_llms_time_available' => $post_time,
					)
				)
			);
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $lesson->get( 'id' ) ) );
			$this->assertSame( $stored_date, get_post_meta( $lesson->get( 'id' ), '_llms_date_available', true ), $post_date );
			$this->assertSame( $stored_time, get_post_meta( $lesson->get( 'id' ), '_llms_time_available', true ), $post_time );
		}

		// An empty time makes the lesson available at 12:00 AM.
		$this->assertSame( '2030-12-25 00:00', llms_get_post( $lesson->get( 'id' ) )->get_available_date( 'Y-m-d H:i' ) );

	}

}
