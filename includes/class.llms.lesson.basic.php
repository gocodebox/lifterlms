<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Basic lesson class
*
* Basic lesson is the standard, single lesson. 
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Lesson_Basic extends LLMS_Lesson {

	public $id;

	public $post;

	public function __construct( $lesson ) {

		$this->lesson_type = 'basic';
		parent::__construct( $lesson );
		
	}

}