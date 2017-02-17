<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Course_Data {

	public function __construct( $course_id ) {

		$this->course_id = $course_id;
		$this->course = llms_get_post( $this->course_id );

	}

	private function strtotime( $date ) {
		if ( ! is_numeric( $date ) ) {
			$date = date( 'U', strtotime( $date ) );
		}
		return $date;
	}




	public function enrollments_on_date( $date ) {

		global $wpdb;

		$date = $this->strtotime( $date );

		$start = strtotime( 'midnight', $date );
		$end = strtotime( 'tomorrow', $start ) - 1;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_start_date'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			date( 'Y-m-d H:i:s', $start ),
			date( 'Y-m-d H:i:s', $end )
		) );

	}

	public function course_completed_on_date( $date ) {

		global $wpdb;

		$date = $this->strtotime( $date );

		$start = strtotime( 'midnight', $date );
		$end = strtotime( 'tomorrow', $start ) - 1;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT DISTINCT COUNT( user_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id = %d
			  AND updated_date BETWEEN %s AND %s
			",
			$this->course_id,
			date( 'Y-m-d H:i:s', $start ),
			date( 'Y-m-d H:i:s', $end )
		) );

	}

	public function lessons_completed_on_date( $date ) {

		global $wpdb;

		$date = $this->strtotime( $date );

		$start = strtotime( 'midnight', $date );
		$end = strtotime( 'tomorrow', $start ) - 1;

		$lessons = implode( ',', $this->course->get_lessons( 'ids' ) );

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( * )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE meta_value = 'yes'
			  AND meta_key = '_is_complete'
			  AND post_id IN ( {$lessons} )
			  AND updated_date BETWEEN %s AND %s
			",
			date( 'Y-m-d H:i:s', $start ),
			date( 'Y-m-d H:i:s', $end )
		) );

	}

}
