<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Basic course class
* Extends LLMS_Course
*
* Basic course is the "standard" single purchase course.
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Course_Basic extends LLMS_Course {

	/**
	 * post id
	 * @var int
	 */
	public $id;

	/**
	 * post object
	 * @var object
	 */
	public $post;

	/**
	 * Constructor
	 * Inherits from LLMS_Course
	 *
	 * @param object $course [The course object]
	 */
	public function __construct( $course ) {

		$this->course_type = 'basic';
		parent::__construct( $course );

	}

}
