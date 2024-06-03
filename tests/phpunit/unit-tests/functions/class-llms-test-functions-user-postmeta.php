<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests
 *
 * @group functions
 * @group user_postmeta
 *
 * @since 3.21.0
 * @since 3.33.0 Add test for the `llms_bulk_delete_user_postmeta` function.
 * @since 4.5.1 Fix failing `test_delete_user_postmeta()` which was comparing based on array order when that doesn't strictly matter.
 * @version 5.4.1
 */
class LLMS_Test_Functions_User_Postmeta extends LLMS_UnitTestCase {

	/**
	 * Setup the test case
	 *
	 * @since Unknown
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->student = $this->get_mock_student();
		$this->student_id = $this->student->get( 'id' );
		$this->course_id = $this->generate_mock_courses( 1, 1, 3 )[0];
		$this->student->enroll( $this->course_id );

	}

	public function test__llms_query_user_postmeta() {

		// fake user, fake post, fake key
		$this->assertEquals( array(), _llms_query_user_postmeta( 123, 456, '_fake_val' ) );

		// real user, fake post, fake key
		$this->assertEquals( array(), _llms_query_user_postmeta( $this->student_id, 123, '_fake_val' ) );

		// fake user, real post, fake key
		$this->assertEquals( array(), _llms_query_user_postmeta( 123, $this->course_id, '_fake_val' ) );

		// fake user, fake post, real key
		$this->assertEquals( array(), _llms_query_user_postmeta( 123, 456, '_status' ) );

		// has a result
		$this->assertEquals( 1, count( _llms_query_user_postmeta( $this->student_id, $this->course_id, '_status' ) ) );

		// find a specific value
		$this->assertEquals( 1, count( _llms_query_user_postmeta( $this->student_id, $this->course_id, '_status', 'enrolled' ) ) );

		// no key has more results
		$this->assertEquals( 3, count( _llms_query_user_postmeta( $this->student_id, $this->course_id ) ) );

	}

	/**
	 * Test llms_delete_user_postmeta()
	 *
	 * @since Unknown
	 * @since 4.5.1 Compare data as equal sets in favor of strict order comparison.
	 *
	 * @return void
	 */
	public function test_delete_user_postmeta() {

		// with a value
		llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme' );
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' ) );

		// without a value
		llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme' );
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' ) );

		// delete all non-unique vals
		$i = 1;
		while( $i <= 3 ) {
			llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme' . $i, false );
			$i++;
		}
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' ) );

		// delete only a specific non unique value
		$i = 1;
		while( $i <= 3 ) {
			llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme' . $i, false );
			$i++;
		}
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', 'eraseme3' ) );
		$this->assertEqualSets( array( 'eraseme1', 'eraseme2' ), llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', false ) );

		// delete all user post meta for student & post
		$i = 1;
		while( $i <= 3 ) {
			llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' . $i, 'eraseme', false );
			$i++;
		}
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id ) );
		$this->assertEquals( array(), llms_get_user_postmeta( $this->student_id, $this->course_id ) );

	}

	/**
	 * Test the bulk_delete_user_postmeta() method.
	 *
	 * @since 3.33.0
	 *
	 * @return void
	 */
	public function test_bulk_delete_user_postmeta() {

		// delete the (key,value) matching pairs
		$data = array(
			'_bulk_test_data_to_erase1'         => 'bulk_eraseme_1',
			'_bulk_test_data_to_erase2'         => 'bulk_eraseme_2',
			'_bulk_test_data_to_preserve'       => 'bulk_saveme_1',
			'_bulk_test_data_to_preserve_value' => 'bulk_savemy_value_1',
		);
		llms_bulk_update_user_postmeta( $this->student_id, $this->course_id, $data );

		$data_to_erase = array(
			'_bulk_test_data_to_erase1'         => 'bulk_eraseme_1',
			'_bulk_test_data_to_erase2'         => 'bulk_eraseme_2',
			'_bulk_test_data_to_preserve_value' => 'bulk_eraseme_3',
		);

		$this->assertEquals( array(
			'_bulk_test_data_to_erase1'         => true,
			'_bulk_test_data_to_erase2'         => true,
			'_bulk_test_data_to_preserve_value' => false,
		), llms_bulk_delete_user_postmeta( $this->student_id, $this->course_id, $data_to_erase ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_bulk_test_data_to_erase1' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_bulk_test_data_to_erase2' ) );
		$this->assertEquals( $data['_bulk_test_data_to_preserve'], llms_get_user_postmeta( $this->student_id, $this->course_id, '_bulk_test_data_to_preserve' ) );
		$this->assertEquals( $data['_bulk_test_data_to_preserve_value'], llms_get_user_postmeta( $this->student_id, $this->course_id, '_bulk_test_data_to_preserve_value' ) );

		// delete all the metas for a student and course
		$data = array(
			'_bulk_test_data_to_erase1' => 'bulk_eraseme_1',
			'_bulk_test_data_to_erase2' => 'bulk_eraseme_2',
			'_bulk_test_data_to_erase3' => 'bulk_eraseme_3',
			'_bulk_test_data_to_erase4' => 'bulk_eraseme_4',
		);
		llms_bulk_update_user_postmeta( $this->student_id, $this->course_id, $data );

		$this->assertTrue( llms_bulk_delete_user_postmeta( $this->student_id, $this->course_id ) );
		$this->assertEquals( array(), llms_get_user_postmeta( $this->student_id, $this->course_id ) );

		// delete all the metas with the matching keys
		$data = array(
			'_bulk_test_data_to_erase1'   => 'bulk_eraseme_1',
			'_bulk_test_data_to_erase2'   => 'bulk_eraseme_2',
			'_bulk_test_data_to_erase3'   => 'bulk_eraseme_3',
			'_bulk_test_data_to_preserve' => 'bulk_saveme_1',
		);
		llms_bulk_update_user_postmeta( $this->student_id, $this->course_id, $data );

		$data_to_erase = array(
			'_bulk_test_data_to_erase1' => null,
			'_bulk_test_data_to_erase2' => null,
			'_bulk_test_data_to_erase3' => null,
		);
		$this->assertTrue( llms_bulk_delete_user_postmeta( $this->student_id, $this->course_id, $data_to_erase ) );
		$this->assertArrayHasKey( '_bulk_test_data_to_preserve', llms_get_user_postmeta( $this->student_id, $this->course_id ) );

	}


	/**
	 * Test llms_get_user_postmeta().
	 *
	 * @since 3.21.0
	 * @since 5.3.2 Add delta when comparing enrollment date with updated date.
	 * @since 5.4.1 Compare dates using UNIX timestamps.
	 */
	public function test_llms_get_user_postmeta() {

		$this->assertEquals( 'enrolled', llms_get_user_postmeta( $this->student_id, $this->course_id, '_status' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_fake' ) );
		$this->assertEquals( 3, count( llms_get_user_postmeta( $this->student_id, $this->course_id ) ) );

		// Test serialized values.
		$data = range( 1, 5 );
		llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_serialized_data', $data );
		$this->assertEquals( $data, llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_serialized_data' ) );

		// Test updated date.
		$enrollment_date = $this->student->get_enrollment_date( $this->course_id, 'enrolled', 'U' );
		$updated_date    = llms_get_user_postmeta( $this->student_id, $this->course_id, '_status', true, 'updated_date' );
		$this->assertEqualsWithDelta( $enrollment_date, strtotime( $updated_date ), 2 );

	}

	public function test_llms_update_user_postmeta() {

		// simple set and get
		$this->assertTrue( llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_key', 'testval' ) );
		$this->assertEquals( 'testval', llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_key' ) );

		// update that same val
		$this->assertTrue( llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_key', 'testval2' ) );
		$this->assertEquals( 'testval2', llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_key' ) );

		// should only have one for the key
		$this->assertEquals( 1, count( llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_key', false ) ) );

		// add another but non unique
		$this->assertTrue( llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_key', 'testval2-1', false ) );

		// should be 2 now
		$this->assertEquals( 2, count( llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_key', false ) ) );

	}

	public function test_llms_bulk_update_user_postmeta() {

		$data = array(
			'bulk_key1' => 'bulk_val1',
			'bulk_key2' => 'bulk_val2',
		);

		$this->assertTrue( llms_bulk_update_user_postmeta( $this->student_id, $this->course_id, $data ) );
		foreach ( $data as $key => $val ) {
			$this->assertEquals( $val, llms_get_user_postmeta( $this->student_id, $this->course_id, $key ) );
		}

	}

}
