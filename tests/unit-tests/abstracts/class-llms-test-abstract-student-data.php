<?php
/**
 * Test LLMS_Abstract_Student_Data
 *
 * @package LifterLMS_Tests/Abstracts
 *
 * @group abstracts
 * @group student_data
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Abstract_Student_Data extends LLMS_Unit_Test_Case {

	/**
	 * Get a mocked stub of an object extending the LLMS_Abstract_Student_Data class
	 *
	 * @since [version]
	 *
	 * @param int $id an int.
	 * @return obj
	 */
	public function get_stub( $id = null ) {

		$id = $id ? $id : $this->factory->student->create();
		return new class( $id ) extends LLMS_Abstract_Student_Data {};

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_object() {

		$student = $this->factory->student->create_and_get();
		$stub = $this->get_stub( $student->get( 'id' ) );
		$this->assertEquals( $student, $stub->get_object() );

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_object_id() {

		$id = $this->factory->student->create();
		$stub = $this->get_stub( $id );
		$this->assertEquals( $id, $stub->get_object_id() );

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_student() {

		$student = $this->factory->student->create_and_get();
		$stub = $this->get_stub( $student->get( 'id' ) );
		$this->assertEquals( $student, $stub->get_student() );

	}

	/**
	 * Test constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_student_id() {

		$id = $this->factory->student->create();
		$stub = $this->get_stub( $id );
		$this->assertEquals( $id, $stub->get_student_id() );

	}

}
