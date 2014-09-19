<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Course_Factory {

	public function get_course( $the_course = false, $args = array() ) {
		global $post;

	 	$the_course = $post;

	 	$classname = 'LLMS_Course_Simple';

		return new LLMS_Course_Simple($the_course, $args );
	}

}

