<?php
/**
 * LLMS_REST_Abilities class file
 *
 * @package LifterLMS_REST/Abilities
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers LifterLMS abilities with the WordPress Abilities API (WP 6.9+).
 *
 * Exposes the `llms/v1` REST API surface (courses, sections, lessons, memberships,
 * access plans, students, enrollments, and student progress) as first-class WordPress
 * abilities so abilities-aware AI clients can discover and execute LifterLMS
 * functionality natively.
 *
 * @since [version]
 */
class LLMS_REST_Abilities {

	/**
	 * Register Abilities API hooks.
	 *
	 * A no-op on WordPress versions without the Abilities API.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function init() {

		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_abilities' ) );
	}

	/**
	 * Register the LifterLMS ability category.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function register_category() {

		wp_register_ability_category(
			'lifterlms',
			array(
				'label'       => __( 'LifterLMS', 'lifterlms' ),
				'description' => __( 'Abilities for managing LifterLMS courses, memberships, students, enrollments, and student progress.', 'lifterlms' ),
			)
		);
	}

	/**
	 * Register all LifterLMS abilities.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function register_abilities() {

		// Controller classes are normally loaded on `rest_api_init`, which may not have fired yet.
		LLMS_REST_API()->rest_api_includes();

		foreach ( self::get_ability_configs() as $config ) {
			LLMS_REST_Ability_Factory::register( $config );
		}
	}

	/**
	 * Retrieve the configuration for every ability to be registered.
	 *
	 * @since [version]
	 *
	 * @return array[] List of ability configuration arrays. See {@see LLMS_REST_Ability_Factory::register()}.
	 */
	private static function get_ability_configs() {

		$configs = array();

		foreach ( self::get_crud_resources() as $resource ) {
			$configs = array_merge( $configs, self::get_crud_configs( $resource ) );
		}

		$configs = array_merge( $configs, self::get_custom_configs() );

		/**
		 * Filters the list of LifterLMS ability configurations.
		 *
		 * Add-ons providing their own `llms/v1` REST controllers can append configurations
		 * here to have their endpoints registered as WordPress abilities.
		 *
		 * @since [version]
		 *
		 * @param array[] $configs List of ability configuration arrays. See {@see LLMS_REST_Ability_Factory::register()}.
		 */
		return apply_filters( 'llms_rest_ability_configs', $configs );
	}

	/**
	 * Retrieve definitions for resources exposing standard CRUD abilities.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	private static function get_crud_resources() {

		return array(
			array(
				'controller' => 'LLMS_REST_Courses_Controller',
				'route'      => '/llms/v1/courses',
				'slugs'      => array( 'course', 'courses' ),
				'labels'     => array( __( 'Course', 'lifterlms' ), __( 'Courses', 'lifterlms' ) ),
				'nouns'      => array( __( 'course', 'lifterlms' ), __( 'courses', 'lifterlms' ) ),
			),
			array(
				'controller' => 'LLMS_REST_Sections_Controller',
				'route'      => '/llms/v1/sections',
				'slugs'      => array( 'section', 'sections' ),
				'labels'     => array( __( 'Section', 'lifterlms' ), __( 'Sections', 'lifterlms' ) ),
				'nouns'      => array( __( 'course section', 'lifterlms' ), __( 'course sections', 'lifterlms' ) ),
			),
			array(
				'controller' => 'LLMS_REST_Lessons_Controller',
				'route'      => '/llms/v1/lessons',
				'slugs'      => array( 'lesson', 'lessons' ),
				'labels'     => array( __( 'Lesson', 'lifterlms' ), __( 'Lessons', 'lifterlms' ) ),
				'nouns'      => array( __( 'lesson', 'lifterlms' ), __( 'lessons', 'lifterlms' ) ),
			),
			array(
				'controller' => 'LLMS_REST_Memberships_Controller',
				'route'      => '/llms/v1/memberships',
				'slugs'      => array( 'membership', 'memberships' ),
				'labels'     => array( __( 'Membership', 'lifterlms' ), __( 'Memberships', 'lifterlms' ) ),
				'nouns'      => array( __( 'membership', 'lifterlms' ), __( 'memberships', 'lifterlms' ) ),
			),
			array(
				'controller' => 'LLMS_REST_Access_Plans_Controller',
				'route'      => '/llms/v1/access-plans',
				'slugs'      => array( 'access-plan', 'access-plans' ),
				'labels'     => array( __( 'Access Plan', 'lifterlms' ), __( 'Access Plans', 'lifterlms' ) ),
				'nouns'      => array( __( 'access plan (pricing plan for a course or membership)', 'lifterlms' ), __( 'access plans (pricing plans for courses and memberships)', 'lifterlms' ) ),
			),
			array(
				'controller' => 'LLMS_REST_Students_Controller',
				'route'      => '/llms/v1/students',
				'slugs'      => array( 'student', 'students' ),
				'labels'     => array( __( 'Student', 'lifterlms' ), __( 'Students', 'lifterlms' ) ),
				'nouns'      => array( __( 'student', 'lifterlms' ), __( 'students', 'lifterlms' ) ),
			),
		);
	}

	/**
	 * Build the five standard CRUD ability configurations for a resource.
	 *
	 * @since [version]
	 *
	 * @param array $resource Resource definition. See {@see LLMS_REST_Abilities::get_crud_resources()}.
	 * @return array[]
	 */
	private static function get_crud_configs( $resource ) {

		list( $slug_singular, $slug_plural ) = $resource['slugs'];
		list( $label_singular, $label_plural ) = $resource['labels'];
		list( $noun_singular, $noun_plural ) = $resource['nouns'];

		$base = array(
			'controller' => $resource['controller'],
		);

		return array(
			$base + array(
				'name'        => "list-{$slug_plural}",
				// Translators: %s = plural resource label (e.g. "Courses").
				'label'       => sprintf( __( 'List %s', 'lifterlms' ), $label_plural ),
				// Translators: %s = plural resource noun (e.g. "courses").
				'description' => sprintf( __( 'Retrieves a paginated list of %s from this LifterLMS site. Supports filtering, ordering, and pagination parameters.', 'lifterlms' ), $noun_plural ),
				'operation'   => 'list',
				'method'      => 'GET',
				'route'       => $resource['route'],
			),
			$base + array(
				'name'        => "get-{$slug_singular}",
				// Translators: %s = singular resource label (e.g. "Course").
				'label'       => sprintf( __( 'Get %s', 'lifterlms' ), $label_singular ),
				// Translators: %s = singular resource noun (e.g. "course").
				'description' => sprintf( __( 'Retrieves the details of a single %s by its ID.', 'lifterlms' ), $noun_singular ),
				'operation'   => 'get',
				'method'      => 'GET',
				'route'       => $resource['route'] . '/{id}',
			),
			$base + array(
				'name'        => "create-{$slug_singular}",
				// Translators: %s = singular resource label (e.g. "Course").
				'label'       => sprintf( __( 'Create %s', 'lifterlms' ), $label_singular ),
				// Translators: %s = singular resource noun (e.g. "course").
				'description' => sprintf( __( 'Creates a new %s on this LifterLMS site.', 'lifterlms' ), $noun_singular ),
				'operation'   => 'create',
				'method'      => 'POST',
				'route'       => $resource['route'],
			),
			$base + array(
				'name'        => "update-{$slug_singular}",
				// Translators: %s = singular resource label (e.g. "Course").
				'label'       => sprintf( __( 'Update %s', 'lifterlms' ), $label_singular ),
				// Translators: %s = singular resource noun (e.g. "course").
				'description' => sprintf( __( 'Updates an existing %s by its ID.', 'lifterlms' ), $noun_singular ),
				'operation'   => 'update',
				'method'      => 'PATCH',
				'route'       => $resource['route'] . '/{id}',
			),
			$base + array(
				'name'        => "delete-{$slug_singular}",
				// Translators: %s = singular resource label (e.g. "Course").
				'label'       => sprintf( __( 'Delete %s', 'lifterlms' ), $label_singular ),
				// Translators: %s = singular resource noun (e.g. "course").
				'description' => sprintf( __( 'Deletes a %s by its ID.', 'lifterlms' ), $noun_singular ),
				'operation'   => 'delete',
				'method'      => 'DELETE',
				'route'       => $resource['route'] . '/{id}',
			),
		);
	}

	/**
	 * Retrieve ability configurations for non-CRUD routes.
	 *
	 * Covers course content and course enrollments sub-resources, student enrollments,
	 * and student progress.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	private static function get_custom_configs() {

		$student_id_desc = __( 'Unique student identifier. The WordPress user ID.', 'lifterlms' );
		$course_id_desc  = __( 'Unique course identifier. The WordPress post ID.', 'lifterlms' );
		$post_id_desc    = __( 'Unique course or membership identifier. The WordPress post ID.', 'lifterlms' );

		return array(

			// Courses: sub-resources.
			array(
				'name'                  => 'get-course-content',
				'label'                 => __( 'Get Course Content', 'lifterlms' ),
				'description'           => __( 'Retrieves the sections that make up the content of a course, ordered as they appear in the course.', 'lifterlms' ),
				'controller'            => 'LLMS_REST_Courses_Controller',
				'schema_controller'     => 'LLMS_REST_Sections_Controller',
				'permission_controller' => 'LLMS_REST_Sections_Controller',
				'operation'             => 'list',
				'method'                => 'GET',
				'route'                 => '/llms/v1/courses/{id}/content',
				'path_params'           => array(
					'id' => $course_id_desc,
				),
			),
			array(
				'name'                  => 'get-course-enrollments',
				'label'                 => __( 'Get Course Enrollments', 'lifterlms' ),
				'description'           => __( 'Retrieves the enrollments for a course, listing the enrolled students and their enrollment status.', 'lifterlms' ),
				'controller'            => 'LLMS_REST_Courses_Controller',
				'schema_controller'     => 'LLMS_REST_Enrollments_Controller',
				'permission_controller' => 'LLMS_REST_Enrollments_Controller',
				'operation'             => 'list',
				'method'                => 'GET',
				'route'                 => '/llms/v1/courses/{id}/enrollments',
				'path_params'           => array(
					'id' => $course_id_desc,
				),
			),

			// Enrollments.
			array(
				'name'        => 'list-enrollments',
				'label'       => __( 'List Enrollments', 'lifterlms' ),
				'description' => __( 'Retrieves the list of course and membership enrollments for a student.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Enrollments_Controller',
				'operation'   => 'list',
				'method'      => 'GET',
				'route'       => '/llms/v1/students/{id}/enrollments',
				'path_params' => array(
					'id' => $student_id_desc,
				),
			),
			array(
				'name'        => 'enroll-student',
				'label'       => __( 'Enroll Student', 'lifterlms' ),
				'description' => __( 'Enrolls a student in a course or membership.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Enrollments_Controller',
				'operation'   => 'create',
				'method'      => 'POST',
				'args_method' => 'POST',
				'route'       => '/llms/v1/students/{id}/enrollments/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => $post_id_desc,
				),
			),
			array(
				'name'        => 'update-enrollment',
				'label'       => __( 'Update Enrollment', 'lifterlms' ),
				'description' => __( 'Updates the status of an existing enrollment of a student in a course or membership.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Enrollments_Controller',
				'operation'   => 'update',
				'method'      => 'PATCH',
				'args_method' => 'PATCH',
				'route'       => '/llms/v1/students/{id}/enrollments/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => $post_id_desc,
				),
			),
			array(
				'name'        => 'unenroll-student',
				'label'       => __( 'Unenroll Student', 'lifterlms' ),
				'description' => __( 'Removes the enrollment of a student in a course or membership.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Enrollments_Controller',
				'operation'   => 'delete',
				'method'      => 'DELETE',
				'args_method' => 'DELETE',
				'route'       => '/llms/v1/students/{id}/enrollments/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => $post_id_desc,
				),
			),

			// Student progress.
			array(
				'name'        => 'get-progress',
				'label'       => __( 'Get Student Progress', 'lifterlms' ),
				'description' => __( 'Retrieves the progress of a student in a course, section, or lesson.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Students_Progress_Controller',
				'operation'   => 'get',
				'method'      => 'GET',
				'route'       => '/llms/v1/students/{id}/progress/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => __( 'Unique course, section, or lesson identifier. The WordPress post ID.', 'lifterlms' ),
				),
			),
			array(
				'name'        => 'update-progress',
				'label'       => __( 'Update Student Progress', 'lifterlms' ),
				'description' => __( 'Updates the progress of a student in a course, section, or lesson, marking it complete or incomplete.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Students_Progress_Controller',
				'operation'   => 'update',
				'method'      => 'POST',
				'args_method' => 'POST',
				'route'       => '/llms/v1/students/{id}/progress/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => __( 'Unique course, section, or lesson identifier. The WordPress post ID.', 'lifterlms' ),
				),
			),
			array(
				'name'        => 'delete-progress',
				'label'       => __( 'Delete Student Progress', 'lifterlms' ),
				'description' => __( 'Deletes (resets) the progress of a student in a course, section, or lesson.', 'lifterlms' ),
				'controller'  => 'LLMS_REST_Students_Progress_Controller',
				'operation'   => 'delete',
				'method'      => 'DELETE',
				'route'       => '/llms/v1/students/{id}/progress/{post_id}',
				'path_params' => array(
					'id'      => $student_id_desc,
					'post_id' => __( 'Unique course, section, or lesson identifier. The WordPress post ID.', 'lifterlms' ),
				),
			),
		);
	}
}
