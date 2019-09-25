<?php
/**
 * Defines base methods and properties for querying data about LifterLMS Students
 *
 * @package LifterLMS/Abstracts
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Student data abstract.
 *
 * @since [version]
 * @since [version] Move various methods into the LLMS_Abstract_Object_Data class.
 */
abstract class LLMS_Abstract_Student_Data extends LLMS_Abstract_Object_Data {

	/**
	 * Student instance.
	 *
	 * @since [version]
	 *
	 * @var LLMS_Student
	 */
	protected $student;

	/**
	 * WP_User ID of the Student.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	protected $student_id;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 * @version [version]
	 *
	 * @param int $student_id WP_User ID of the student.
	 *
	 * @return void
	 */
	public function __construct( $student_id ) {

		parent::__construct( $student_id );

		$this->student_id = $this->object_id;
		$this->student    = $this->object;

	}

	/**
	 * Retrieve student object from the post id passed to the constructor
	 *
	 * @since [version]
	 *
	 * @param int $object_id Object ID.
	 *
	 * @return LLMS_Post_Model
	 */
	protected function set_object( $object_id ) {
		return llms_get_student( $object_id );
	}

	/**
	 * Retrieve the instance of the LLMS_Student.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Post_Model
	 */
	public function get_student() {
		return $this->get_object();
	}

	/**
	 * Retrieve the User ID.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function get_student_id() {
		return $this->get_object_id();
	}

}
