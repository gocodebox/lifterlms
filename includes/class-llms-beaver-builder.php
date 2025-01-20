<?php
/**
 * BeaverBuilder Integration
 *
 * Lets you do all them sweet BeaverBuilder things to Courses, Lessons, and Memberships.
 *
 * @package LifterLMS/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'LLMS_BB_MODULES_DIR', plugin_dir_path( __FILE__ ) . 'beaver-builder/modules/' );
define( 'LLMS_BB_MODULES_URL', plugins_url( '/', __FILE__ ) . 'beaver-builder/modules/' );

class LLMS_Beaver_Builder {

	use LLMS_Trait_Singleton;

	public function __construct() {
		$this->init();
	}

	public function is_available() {
		return class_exists( 'FLBuilder' );
	}

	protected function init() {

		// Break early if the LifterLMS Labs is installed and enabled.
		if ( class_exists( 'LifterLMS_Labs' ) && llms_parse_bool( get_option( 'llms_lab_beaver-builder_enabled' ) ) ) {
			return;
		}

		if ( ! class_exists( 'FLBuilder' ) || ! class_exists( 'FLBuilderModel' ) || ! class_exists( 'FLBUilderModule' ) ) {
			return;
		}

		// Prevent uneditable llms post types from being enabled for page building.
		add_filter( 'fl_builder_admin_settings_post_types', array( $this, 'remove_uneditable_post_types' ) );

		// Add migrateable post types to the builder by default.
		add_filter( 'fl_builder_post_types', array( $this, 'enable_post_types_by_default' ) );

		add_action( 'wp', array( $this, 'load_modules' ), 1 );
		add_action( 'init', array( $this, 'load_templates' ) );

		add_filter( 'fl_builder_register_module', array( $this, 'register_module' ), 10, 2 );

		add_filter( 'llms_page_restricted', array( $this, 'mod_page_restrictions' ), 999, 2 );

		add_filter( 'fl_builder_register_settings_form', array( $this, 'add_visibility_settings' ), 999, 2 );

		add_filter( 'fl_builder_is_node_visible', array( $this, 'is_node_visible' ), 10, 2 );

		// Hide editors when builder is enabled for a post.
		add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'mod_metabox_fields' ) );
		add_filter( 'llms_metabox_fields_lifterlms_membership', array( $this, 'mod_metabox_fields' ) );

		add_filter( 'fl_builder_upgrade_url', array( $this, 'upgrade_url' ) );

		// LifterLMS Private Areas.
		add_action( 'llms_pa_before_do_area_content', array( $this, 'llms_pa_before_content' ) );
		add_action( 'llms_pa_after_do_area_content', array( $this, 'llms_pa_after_content' ) );
	}

	public function enable_post_types_by_default( $types ) {
		$types[] = 'course';
		$types[] = 'lesson';
		$types[] = 'llms_membership';

		return $types;
	}

	/**
	 * Add LLMS post types to the enabled builder post types.
	 *
	 * Stub function called during install.
	 *
	 * @since 8.0.0
	 *
	 * @return void
	 */
	public function install() {
		if ( ! $this->is_available() ) {
			return;
		}

		$existing = get_option( '_fl_builder_post_types', array( 'page' ) );
		$types    = array_unique( array_merge( $existing, array( 'course', 'lesson', 'llms_membership' ) ) );
		update_option( '_fl_builder_post_types', $types );
	}

	/**
	 * This function should return array of settings fields.
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	protected function settings() {
		return array();
	}

	/**
	 * Add custom visibility settings for enrollments to the BB "Visibility" section.
	 *
	 * @since 1.5.0
	 * @since 1.5.3 Fixed localization textdomain.
	 * @since 1.7.0 Escaped strings.
	 *
	 * @param array  $form Settings form array.
	 * @param string $id   ID of the row/module/col/etc.
	 * @return array
	 */
	public function add_visibility_settings( $form, $id ) {

		$options = array(
			'llms_enrolled'     => esc_html__( 'Enrolled Students', 'lifterlms' ),
			'llms_not_enrolled' => esc_html__( 'Non-Enrolled Students and Visitors', 'lifterlms' ),
		);

		$toggle = array(
			'llms_enrolled'     => array(
				'fields' => array( 'llms_enrollment_type' ),
			),
			'llms_not_enrolled' => array(
				'fields' => array( 'llms_enrollment_type' ),
			),
		);

		$fields = array(
			'llms_enrollment_type'  => array(
				'type'    => 'select',
				'label'   => esc_html__( 'In', 'lifterlms' ),
				'options' => array(
					''         => esc_html__( 'Current Course or Membership', 'lifterlms' ),
					'any'      => esc_html__( 'Any Course(s) or Membership(s)', 'lifterlms' ),
					'specific' => esc_html__( 'Specific Course(s) and/or Membership(s)', 'lifterlms' ),
				),
				'toggle'  => array(
					'specific' => array(
						'fields' => array( 'llms_enrollment_match', 'llms_course_ids', 'llms_membership_ids' ),
					),
				),
				'help'    => esc_html__( 'Select how to check the enrollment status of the current student.', 'lifterlms' ),
				'preview' => array(
					'type' => 'none',
				),
			),
			'llms_enrollment_match' => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Match', 'lifterlms' ),
				'options' => array(
					''    => esc_html__( 'Any of the following', 'lifterlms' ),
					'all' => esc_html__( 'All of the following', 'lifterlms' ),
				),
				'help'    => esc_html__( 'Select how to check the enrollment status of the current student.', 'lifterlms' ),
				'preview' => array(
					'type' => 'none',
				),
			),
			'llms_course_ids'       => array(
				'type'    => 'suggest',
				'action'  => 'fl_as_posts',
				'data'    => 'course',
				'label'   => esc_html__( 'Courses', 'lifterlms' ),
				'help'    => esc_html__( 'Choose which course(s) the student must be enrolled (or not enrolled) in to view this element.', 'lifterlms' ),
				'preview' => array(
					'type' => 'none',
				),
			),
			'llms_membership_ids'   => array(
				'type'    => 'suggest',
				'action'  => 'fl_as_posts',
				'data'    => 'llms_membership',
				'label'   => esc_html__( 'Memberships', 'lifterlms' ),
				'help'    => esc_html__( 'Choose which membership(s) the student must be enrolled (or not enrolled) in to view this element.', 'lifterlms' ),
				'preview' => array(
					'type' => 'none',
				),
			),
		);

		// Rows.
		if (
			isset( $form['tabs'] ) &&
			isset( $form['tabs']['advanced'] ) &&
			isset( $form['tabs']['advanced']['sections'] ) &&
			isset( $form['tabs']['advanced']['sections']['visibility'] )
		) {

			$form['tabs']['advanced']['sections']['visibility']['fields']['visibility_display']['options'] = array_merge( $form['tabs']['advanced']['sections']['visibility']['fields']['visibility_display']['options'], $options );
			$form['tabs']['advanced']['sections']['visibility']['fields']['visibility_display']['toggle']  = array_merge( $form['tabs']['advanced']['sections']['visibility']['fields']['visibility_display']['toggle'], $toggle );
			$form['tabs']['advanced']['sections']['visibility']['fields']                                  = array_merge( $form['tabs']['advanced']['sections']['visibility']['fields'], $fields );

			// Modules.
		} elseif (
			isset( $form['sections'] ) &&
			isset( $form['sections']['visibility'] )
		) {

			$form['sections']['visibility']['fields']['visibility_display']['options'] = array_merge( $form['sections']['visibility']['fields']['visibility_display']['options'], $options );
			$form['sections']['visibility']['fields']['visibility_display']['toggle']  = array_merge( $form['sections']['visibility']['fields']['visibility_display']['toggle'], $toggle );
			$form['sections']['visibility']['fields']                                  = array_merge( $form['sections']['visibility']['fields'], $fields );

		}

		return $form;
	}

	/**
	 * Create a single array of course & membership IDs from a BB node settings object.
	 *
	 * @since 1.3.0
	 *
	 * @param obj $settings BB Node Settings.
	 * @return array
	 */
	private function get_related_posts_from_settings( $settings ) {

		$post_ids = array();

		foreach ( array( 'llms_course_ids', 'llms_membership_ids' ) as $key ) {

			if ( ! empty( $settings->$key ) ) {

				$ids      = explode( ',', $settings->$key );
				$post_ids = array_merge( $post_ids, $ids );

			}
		}

		return $post_ids;
	}

	/**
	 * Determine if a node is visible based on llms enrollments status visibility settings.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Unknown.
	 * @since 1.5.3 Fixed visibility conditional logic for `'specific'` enrollment type.
	 * @since 1.7.0 Use `in_array()` strict comparisons.
	 *
	 * @param bool $visible Default visibility.
	 * @param obj  $node    BB node object.
	 * @return boolean
	 */
	public function is_node_visible( $visible, $node ) {

		if ( isset( $node->settings ) && isset( $node->settings->visibility_display ) && false !== strpos( $node->settings->visibility_display, 'llms_' ) ) {

			$status = $node->settings->visibility_display;

			$uid  = get_current_user_id();
			$type = ! empty( $node->settings->llms_enrollment_type ) ? $node->settings->llms_enrollment_type : null;

			llms_log( $type );

			if ( ! $type || 'any' === $type ) {

				// No type means current course/membership.
				if ( ! $type ) {

					$current_id = get_the_ID();
					// Cascade up for lessons & quizzes.
					if ( in_array( get_post_type( $current_id ), array( 'lesson', 'llms_quiz' ), true ) ) {
						$course     = llms_get_post_parent_course( $current_id );
						$current_id = $course->get( 'id' );
					}

					// If the current id isn't a course or membership don't proceed.
					if ( ! in_array( get_post_type( $current_id ), array( 'course', 'llms_membership' ), true ) ) {
						return $visibility;
					}

					// Get the enrollment status.
					$enrollment_status = llms_is_user_enrolled( $uid, $current_id );
				} elseif ( 'any' === $type ) { // Check if they're enrolled/not enrolled in anything.
					$enrollment_status = $this->is_student_enrolled_in_one_thing( $uid );
				}

				if ( 'llms_enrolled' === $status ) {
					return $enrollment_status;
				} elseif ( 'llms_not_enrolled' === $status ) {
					return ( ! $enrollment_status );
				}
			} elseif ( 'specific' === $type ) { // Check if they're enrolled / not enrolled in the specific courses/memberships.

				$match = $node->settings->llms_enrollment_match ? $node->settings->llms_enrollment_match : 'any';
				$ids   = $this->get_related_posts_from_settings( $node->settings );

				if ( empty( $ids ) ) {
					return true;
				}

				if ( 'llms_enrolled' === $status ) {

					if ( ! $uid ) {
						return false;
					}

					return llms_is_user_enrolled( $uid, $ids, $match );

				} elseif ( 'llms_not_enrolled' === $status ) {

					if ( ! $uid ) {
						return true;
					}

					return ! llms_is_user_enrolled( $uid, $ids, $match );

				}
			}
		}

		return $visible;
	}

	/**
	 * Detemine if a student is enrolled in at least one course or membership.
	 *
	 * @since 1.3.0
	 *
	 * @param int $uid WP_User ID.
	 * @return boolean
	 */
	private function is_student_enrolled_in_one_thing( $uid ) {

		if ( ! $uid ) {
			return false;
		}

		$student = llms_get_student( $uid );
		if ( ! $student->exists() ) {
			return false;
		}

		$courses = $student->get_courses(
			array(
				'limit'  => 1,
				'status' => 'enrolled',
			)
		);

		if ( $courses['results'] ) {
			return true;
		}

		$memberships = $student->get_membership_levels();
		if ( $memberships ) {
			return true;
		}

		return false;
	}

	/**
	 * Replace the BB filter after we've rendered our content.
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function llms_pa_after_content() {
		add_filter( 'the_content', 'FLBuilder::render_content' );
	}

	/**
	 * BB will replace PA Post content with course/membership pagebuilder content
	 * so remove the filter and replace when we're done with our output.
	 *
	 * @since 1.3.1
	 *
	 * @return void
	 */
	public function llms_pa_before_content() {
		remove_filter( 'the_content', 'FLBuilder::render_content' );
	}

	/**
	 * Loads LifterLMS modules.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function load_modules() {
		if ( ! class_exists( 'FLBUilderModule' ) ) {
			return;
		}

		if ( file_exists( LLMS_BB_MODULES_DIR ) ) {
			foreach ( glob( LLMS_BB_MODULES_DIR . '**/*.php', GLOB_NOSORT ) as $file ) {
				require_once $file;
			}
		}
	}

	/**
	 * Filter out LifterLMS modules if it's not the right post type.
	 *
	 * @param $enabled
	 * @param $instance
	 *
	 * @return bool
	 */
	public function register_module( $enabled, $instance ) {
		$post_type = get_post_type();

		if ( 'course' !== $post_type && in_array( $instance->slug, array( 'class.llms.lab.course.instructors.module', 'class.llms.lab.course.syllabus.module', 'class.llms.lab.course.progress.bar.module' ) ) ) {
			return false;
		}

		if ( 'llms_membership' !== $post_type && in_array( $instance->slug, array( 'class.llms.lab.membership.instructors.module' ) ) ) {
			return false;
		}

		if ( 'lesson' !== $post_type && in_array( $instance->slug, array( 'class.llms.lab.lesson.mark.complete.module' ) ) ) {
			return false;
		}

		return $enabled;
	}


	/**
	 * Load LifterLMS layout templates.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function load_templates() {

		if ( ! class_exists( 'FLBuilderModel' ) ) {
			return;
		}

		FLBuilderModel::register_templates( LLMS_PLUGIN_DIR . 'includes/beaver-builder/templates/course-template.dat' );
	}

	/**
	 * Modify LifterLMS metabox Fields to show the page builder is active.
	 *
	 * @since 1.3.0
	 * @since 1.5.2 Unknown.
	 * @since 1.7.0 Use strict comparison for `in_array`.
	 *
	 * @param array $fields Metabox fields.
	 * @return array
	 */
	public function mod_metabox_fields( $fields ) {

		global $post;

		$post_types = array( 'course', 'lesson', 'llms_membership' );

		if ( in_array( $post->post_type, $post_types, true ) && FLBuilderModel::is_builder_enabled() ) {

			unset( $fields[0]['fields'][0]['value']['content'] );

		}

		return $fields;
	}

	/**
	 * Bypass restriction checks for courses and memberships when the builder is active.
	 *
	 * Allows the builder to use custom LifterLMS visibility settings when a student is not enrolled.
	 *
	 * @since 1.3.0
	 * @since 1.7.0 Use `in_array` with strict comparison.
	 *
	 * @param array $results Restriction results data.
	 * @param int   $post_id Current post id.
	 * @return array
	 */
	public function mod_page_restrictions( $results, $post_id ) {

		if (
			FLBuilderModel::is_builder_enabled() &&
			$results['is_restricted'] &&
			in_array( get_post_type( $post_id ), array( 'course', 'llms_membership' ), true )
		) {
			$results['is_restricted'] = false;
			$results['reason']        = 'bb-lab';
		}

		return $results;
	}

	/**
	 * Prevent page building of LifterLMS Post Types that can't actually be pagebuilt despite what the settings may assume.
	 *
	 * @since 1.5.0
	 * @since 1.7.0 Removed unset parameters for `llms_certificate` and `llms_my_certificate` from the filter.
	 *
	 * @param array $post_types Post type objects as an array.
	 * @return array
	 */
	public function remove_uneditable_post_types( $post_types ) {

		unset( $post_types['llms_quiz'] );
		unset( $post_types['llms_question'] );

		return $post_types;
	}

	/**
	 * Upgrade url.
	 *
	 * @since 1.3.0
	 *
	 * @param string $url Default upgrade url.
	 * @return string
	 */
	public function upgrade_url( $url ) {
		return 'https://www.wpbeaverbuilder.com/?fla=968';
	}
}

return new LLMS_Beaver_Builder();
