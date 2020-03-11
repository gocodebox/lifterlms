<?php
/**
 * Basic lesson class
 *
 * Basic lesson is the standard, single lesson.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Lesson_Basic
 *
 * @since 1.0.0
 * @deprecated 3.30.3
 */
class LLMS_Lesson_Basic extends LLMS_Lesson {

	/**
	 * Lesson id
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Lesson Type
	 *
	 * @var string
	 */
	public $lesson_type;

	/**
	 * post object
	 *
	 * @var object
	 */
	public $post;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @deprecated 3.30.3
	 *
	 * @param int $lesson ID of lesson post.
	 *
	 * @return void
	 */
	public function __construct( $lesson ) {

		llms_deprecated_function( 'LLMS_Lesson_Basic::__construct', '3.30.3' );

		$this->lesson_type = 'basic';
		parent::__construct( $lesson );

	}

}
