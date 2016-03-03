<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Basic lesson class
*
* Basic lesson is the standard, single lesson.
*
* @author codeBOX
*/
class LLMS_Lesson_Basic extends LLMS_Lesson {

	/**
	 * Lesson id
	 * @var int
	 */
	public $id;

	/**
	 * post object
	 * @var object
	 */
	public $post;

	/**
	 * [__construct description]
	 * @param int $lesson [ID of lesson post]
	 */
	public function __construct( $lesson ) {

		$this->lesson_type = 'basic';
		parent::__construct( $lesson );

	}

}
