<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Lesson_Simple extends LLMS_Lesson {

	// Post Id
	public $id;

	public $post;

	public function __construct( $lesson ) {
		$this->lesson_type = 'simple';
		parent::__construct( $lesson );
	}

}