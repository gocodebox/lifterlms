<?php
/**
 * Factory for making LifterLMS data.
 */
class LLMS_Unit_Test_Factory extends WP_UnitTest_Factory {

	/**
	 * LLMS_Unit_Test_Factory_For_Course
	 *
	 * @var LLMS_Unit_Test_Factory_For_Course
	 */
	public $course;

	/**
	 * LLMS_Unit_Test_Factory_For_Instructor
	 *
	 * @var LLMS_Unit_Test_Factory_For_Instructor
	 */
	public $instructor;

	/**
	 * LLMS_Unit_Test_Factory_For_Membership
	 *
	 * @var LLMS_Unit_Test_Factory_For_Membership
	 */
	public $membership;


	/**
	 * LLMS_Unit_Test_Factory_For_Order
	 *
	 * @var LLMS_Unit_Test_Factory_For_Order
	 */
	public $order;

	/**
	 * LLMS_Unit_Test_Factory_For_Student
	 *
	 * @var LLMS_Unit_Test_Factory_For_Student
	 */
	public $student;

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->course  = new LLMS_Unit_Test_Factory_For_Course( $this );
		$this->instructor = new LLMS_Unit_Test_Factory_For_Instructor( $this );
		$this->membership = new LLMS_Unit_Test_Factory_For_Membership( $this );
		$this->student = new LLMS_Unit_Test_Factory_For_Student( $this );

		// Uses $this->student & $this->course
		$this->order = new LLMS_Unit_Test_Factory_For_Order( $this );

	}

}
