<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Lesson {

	// Post Id
	public $id;

	/** @var object The actual post object. */
	public $post;


	
	public function __construct( $lesson ) {
		if ( is_numeric( $lesson ) ) {
			$this->id   = absint( $lesson );
			$this->post = get_post( $this->id );
		} elseif ( $lesson instanceof LLMS_Lesson ) {
			$this->id   = absint( $lesson->id );
			$this->post = $lesson;
		} elseif ( $lesson instanceof LLMS_Post || isset( $lesson->ID ) ) {
			$this->id   = absint( $lesson->ID );
			$this->post = $lesson;
		}
	}

	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, '_' . $key );
	}

	public function __get( $key ) {
		$value = get_post_meta( $this->id, '_' . $key, true );
		return $value;
	}


	/**
	 * Get Parent Course
	 *
	 * @return string
	 */
	public function get_parent_course() {
		$terms = get_the_terms($this->id, '_parent_course'); 

		foreach ( $terms as $term ) {
        	return $term->name;
        }

	}
}

