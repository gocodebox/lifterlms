<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Course_Simple extends LLMS_Course {

	// Post Id
	public $id;

	public $post;

	public function __construct( $course ) {
		$this->course_type = 'simple';
		parent::__construct( $course );
	}

}