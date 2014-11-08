<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Basic course class
*
* Basic course is the "standard" single purchase course. 
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Course_Basic extends LLMS_Course {


	public $id;

	public $post;

	public function __construct( $course ) {

		$this->course_type = 'basic';
		parent::__construct( $course );
		
	}

	public function test() {
		LLMS_log('this should cause a conflict');
	}

}