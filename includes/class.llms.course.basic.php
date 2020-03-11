<?php
/**
 * Basic course class
 *
 * Extends LLMS_Course
 *
 * Basic course is the "standard" single purchase course.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Course_Basic
 *
 * @since 1.0.0
 * @deprecated 3.30.3
 */
class LLMS_Course_Basic extends LLMS_Course {

	/**
	 * Course Type
	 *
	 * @var string
	 */
	public $course_type;

	/**
	 * Course Post ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Course post object
	 *
	 * @var object
	 */
	public $post;

	/**
	 * Constructor
	 *
	 * Inherits from LLMS_Course
	 *
	 * @since 1.0.0
	 * @deprecated 3.30.3
	 *
	 * @param object $course Course object.
	 * @return void
	 */
	public function __construct( $course ) {

		llms_deprecated_function( 'LLMS_Course_Basic::__construct()', '3.30.3' );

		$this->course_type = 'basic';
		parent::__construct( $course );

	}

}
