<?php
/**
 * Course stream helper functions.
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a course argument to an LLMS_Course.
 *
 * `llms_get_post()` cannot be passed an existing LLMS post model.
 *
 * @since [version]
 *
 * @param LLMS_Course|WP_Post|int $course Course object, WP_Post, or post ID.
 * @return LLMS_Course|false
 */
function llms_get_course_for_streams( $course ) {

	if ( $course instanceof LLMS_Course ) {
		return $course;
	}

	$course = llms_get_post( $course );
	return ( $course instanceof LLMS_Course ) ? $course : false;
}

/**
 * Resolve a lesson argument to an LLMS_Lesson.
 *
 * `llms_get_post()` cannot be passed an existing LLMS post model.
 *
 * @since [version]
 *
 * @param LLMS_Lesson|WP_Post|int $lesson Lesson object, WP_Post, or post ID.
 * @return LLMS_Lesson|false
 */
function llms_get_lesson_for_streams( $lesson ) {

	if ( $lesson instanceof LLMS_Lesson ) {
		return $lesson;
	}

	$lesson = llms_get_post( $lesson );
	return ( $lesson instanceof LLMS_Lesson ) ? $lesson : false;
}

/**
 * Determine whether streams are enabled and defined for a course.
 *
 * @since [version]
 *
 * @param LLMS_Course|int $course Course object or WP_Post ID.
 * @return bool
 */
function llms_course_streams_enabled( $course ) {

	$course = llms_get_course_for_streams( $course );
	if ( ! $course ) {
		return false;
	}

	return llms_parse_bool( $course->get( 'streams_enabled' ) ) && ! empty( llms_sanitize_course_streams( $course->get( 'streams' ) ) );
}

/**
 * Retrieve the defined streams for a course.
 *
 * @since [version]
 *
 * @param LLMS_Course|int $course Course object or WP_Post ID.
 * @return array[] List of streams, each with `id` and `name` keys.
 */
function llms_get_course_streams( $course ) {

	$course = llms_get_course_for_streams( $course );
	if ( ! $course ) {
		return array();
	}

	return llms_sanitize_course_streams( $course->get( 'streams' ) );
}

/**
 * Retrieve the default stream id for a course.
 *
 * Falls back to the first defined stream when the stored default is empty or invalid.
 *
 * @since [version]
 *
 * @param LLMS_Course|int $course Course object or WP_Post ID.
 * @return string Stream id, or empty string when no streams are defined.
 */
function llms_get_course_default_stream( $course ) {

	$streams = llms_get_course_streams( $course );
	if ( empty( $streams ) ) {
		return '';
	}

	$ids     = wp_list_pluck( $streams, 'id' );
	$course  = llms_get_course_for_streams( $course );
	$default = $course ? sanitize_title( $course->get( 'streams_default' ) ) : '';

	if ( $default && in_array( $default, $ids, true ) ) {
		return $default;
	}

	return $ids[0];
}

/**
 * Retrieve the stream ids defined for a course.
 *
 * @since [version]
 *
 * @param LLMS_Course|int $course Course object or WP_Post ID.
 * @return string[]
 */
function llms_get_course_stream_ids( $course ) {
	return wp_list_pluck( llms_get_course_streams( $course ), 'id' );
}

/**
 * Retrieve a student's selected stream for a course.
 *
 * Validates the stored selection against the current stream list and falls back to the course default.
 *
 * @since [version]
 *
 * @param LLMS_Student|int|null $student Student object, WP User ID, or null for the current user.
 * @param LLMS_Course|int       $course  Course object or WP_Post ID.
 * @return string Stream id, or empty string when streams are not enabled.
 */
function llms_get_student_stream( $student, $course ) {

	if ( ! llms_course_streams_enabled( $course ) ) {
		return '';
	}

	$default = llms_get_course_default_stream( $course );
	$ids     = llms_get_course_stream_ids( $course );
	$student = llms_get_student( $student );

	if ( ! $student || ! $student->exists() ) {
		return $default;
	}

	$course    = llms_get_course_for_streams( $course );
	$course_id = $course ? $course->get( 'id' ) : 0;
	$selected  = $course_id ? sanitize_title( llms_get_user_postmeta( $student->get_id(), $course_id, '_stream' ) ) : '';

	if ( $selected && in_array( $selected, $ids, true ) ) {
		return $selected;
	}

	return $default;
}

/**
 * Persist a student's selected stream for a course.
 *
 * Busts progress caches and re-evaluates section/course completion for the new stream.
 *
 * @since [version]
 *
 * @param LLMS_Student|int $student   Student object or WP User ID.
 * @param LLMS_Course|int  $course    Course object or WP_Post ID.
 * @param string           $stream_id Stream id.
 * @return bool True on success, false on failure.
 */
function llms_set_student_stream( $student, $course, $stream_id ) {

	$student = llms_get_student( $student );
	$course  = llms_get_course_for_streams( $course );

	if ( ! $student || ! $student->exists() || ! $course ) {
		return false;
	}

	if ( ! llms_course_streams_enabled( $course ) ) {
		return false;
	}

	$stream_id = sanitize_title( $stream_id );
	if ( ! in_array( $stream_id, llms_get_course_stream_ids( $course ), true ) ) {
		return false;
	}

	$updated = llms_update_user_postmeta( $student->get_id(), $course->get( 'id' ), '_stream', $stream_id );
	if ( ! $updated ) {
		$current = sanitize_title( llms_get_user_postmeta( $student->get_id(), $course->get( 'id' ), '_stream' ) );
		if ( $current !== $stream_id ) {
			return false;
		}
	}

	llms_reset_student_stream_progress( $student, $course );

	return true;
}

/**
 * Reset cached progress and re-evaluate completion after a stream change.
 *
 * @since [version]
 *
 * @param LLMS_Student $student Student object.
 * @param LLMS_Course  $course  Course object.
 * @return void
 */
function llms_reset_student_stream_progress( $student, $course ) {

	foreach ( $course->get_sections( 'ids' ) as $section_id ) {
		$student->set( sprintf( 'section_%d_progress', $section_id ), '' );
	}

	$student->set( sprintf( 'course_%d_progress', $course->get( 'id' ) ), '' );

	foreach ( $course->get_sections( 'ids' ) as $section_id ) {
		llms_sync_stream_completion( $student, $section_id, 'section' );
	}

	llms_sync_stream_completion( $student, $course->get( 'id' ), 'course' );
}

/**
 * Mark an object complete or incomplete to match its current stream-filtered progress.
 *
 * @since [version]
 *
 * @param LLMS_Student $student     Student object.
 * @param int          $object_id  WP_Post ID.
 * @param string       $object_type Object type (`section` or `course`).
 * @return void
 */
function llms_sync_stream_completion( $student, $object_id, $object_type ) {

	$progress = $student->get_progress( $object_id, $object_type, false );
	$trigger  = 'stream_switch';

	if ( 100.0 === (float) $progress ) {
		$student->mark_complete( $object_id, $object_type, $trigger );
	} else {
		$student->mark_incomplete( $object_id, $object_type, $trigger );
	}
}

/**
 * Determine whether a lesson belongs to a stream.
 *
 * Lessons with no stream assignments belong to every stream.
 *
 * @since [version]
 *
 * @param LLMS_Lesson|int $lesson    Lesson object or WP_Post ID.
 * @param string          $stream_id Stream id.
 * @return bool
 */
function llms_lesson_in_stream( $lesson, $stream_id ) {

	$lesson = llms_get_lesson_for_streams( $lesson );
	if ( ! $lesson ) {
		return false;
	}

	if ( ! $stream_id ) {
		return true;
	}

	$assigned = array_filter( array_map( 'sanitize_title', (array) $lesson->get( 'streams' ) ) );
	if ( empty( $assigned ) ) {
		return true;
	}

	return in_array( sanitize_title( $stream_id ), $assigned, true );
}

/**
 * Filter a list of lessons to those visible in a student's stream.
 *
 * Preserves the original item type (id, WP_Post, or LLMS_Lesson).
 *
 * @since [version]
 *
 * @param array                 $lessons List of lesson ids, WP_Post objects, or LLMS_Lesson objects.
 * @param LLMS_Course|int|null  $course  Optional. Course object or ID. Derived from the first lesson when omitted.
 * @param LLMS_Student|int|null $student Optional. Student object or ID. Defaults to the current user.
 * @return array
 */
function llms_filter_lessons_by_stream( $lessons, $course = null, $student = null ) {

	if ( empty( $lessons ) || ! is_array( $lessons ) ) {
		return $lessons;
	}

	if ( ! $course ) {
		$first  = reset( $lessons );
		$lesson = llms_get_lesson_for_streams( $first );
		if ( $lesson ) {
			$course = $lesson->get_course();
		}
	}

	if ( ! llms_course_streams_enabled( $course ) ) {
		return $lessons;
	}

	$stream_id = llms_get_student_stream( $student, $course );
	$filtered  = array();

	foreach ( $lessons as $lesson ) {
		if ( llms_lesson_in_stream( $lesson, $stream_id ) ) {
			$filtered[] = $lesson;
		}
	}

	return $filtered;
}

/**
 * Sanitize a list of course stream definitions.
 *
 * Generates stable ids from names when missing, and enforces unique ids.
 *
 * @since [version]
 *
 * @param mixed $streams Raw stream list.
 * @return array[]
 */
function llms_sanitize_course_streams( $streams ) {

	if ( empty( $streams ) || ! is_array( $streams ) ) {
		return array();
	}

	$sanitized = array();
	$used_ids  = array();

	foreach ( $streams as $stream ) {
		if ( is_string( $stream ) ) {
			$stream = array(
				'name' => $stream,
			);
		}

		if ( ! is_array( $stream ) ) {
			continue;
		}

		$name = isset( $stream['name'] ) ? sanitize_text_field( $stream['name'] ) : '';
		if ( '' === $name ) {
			continue;
		}

		$id = isset( $stream['id'] ) ? sanitize_title( $stream['id'] ) : '';
		if ( ! $id ) {
			$id = sanitize_title( $name );
		}
		if ( ! $id ) {
			continue;
		}

		$base = $id;
		$i    = 2;
		while ( in_array( $id, $used_ids, true ) ) {
			$id = $base . '-' . $i;
			++$i;
		}

		$used_ids[]  = $id;
		$sanitized[] = array(
			'id'   => $id,
			'name' => $name,
		);
	}

	return $sanitized;
}

/**
 * Sanitize a list of lesson stream ids, optionally constrained to a course.
 *
 * @since [version]
 *
 * @param mixed                $streams Raw stream id list.
 * @param LLMS_Course|int|null $course  Optional. When provided, ids not defined on the course are dropped.
 * @return string[]
 */
function llms_sanitize_lesson_streams( $streams, $course = null ) {

	if ( empty( $streams ) ) {
		return array();
	}

	if ( ! is_array( $streams ) ) {
		$streams = array( $streams );
	}

	$streams = array_values( array_unique( array_filter( array_map( 'sanitize_title', $streams ) ) ) );

	if ( $course && llms_course_streams_enabled( $course ) ) {
		$allowed = llms_get_course_stream_ids( $course );
		$streams = array_values( array_intersect( $streams, $allowed ) );
	}

	return $streams;
}
