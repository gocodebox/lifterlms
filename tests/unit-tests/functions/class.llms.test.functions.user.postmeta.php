<?php
/**
 * Tests for LifterLMS User Postmeta functions
 * @group    functions
 * @group    user_postmeta
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Functions_User_Postmeta extends LLMS_UnitTestCase {

	public function setUp() {

		parent::setUp();

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
		$this->assertEquals( array( 'eraseme1', 'eraseme2' ), llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase', false ) );

		// delete all user post meta for student & post
		$i = 1;
		while( $i <= 3 ) {
			llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_data_to_erase' . $i, 'eraseme', false );
			$i++;
		}
		$this->assertTrue( llms_delete_user_postmeta( $this->student_id, $this->course_id ) );
		$this->assertEquals( array(), llms_get_user_postmeta( $this->student_id, $this->course_id ) );

	}

	public function test_llms_get_user_postmeta() {

		$this->assertEquals( 'enrolled', llms_get_user_postmeta( $this->student_id, $this->course_id, '_status' ) );
		$this->assertEquals( '', llms_get_user_postmeta( $this->student_id, $this->course_id, '_fake' ) );
		$this->assertEquals( 3, count( llms_get_user_postmeta( $this->student_id, $this->course_id ) ) );

		// test serialized values
		$data = range( 1, 5 );
		llms_update_user_postmeta( $this->student_id, $this->course_id, '_test_serialized_data', $data );
		$this->assertEquals( $data, llms_get_user_postmeta( $this->student_id, $this->course_id, '_test_serialized_data' ) );

		// try updated date return
		$this->assertEquals( $this->student->get_enrollment_date( $this->course_id, 'enrolled', 'Y-m-d H:i:s' ), llms_get_user_postmeta( $this->student_id, $this->course_id, '_status', true, 'updated_date' ) );

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
