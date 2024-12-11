<?php
/**
 * bbPress Integration
 *
 * @package LifterLMS/Integrations/Classes
 *
 * @since 3.0.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * bbPress Integration
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.35.0 Sanitize input data.
 * @since 3.37.11 Don't update saved forum values during course quick edits.
 * @since 3.38.1 When looking for forum course restrictions make sure to run a more generic query
 *               so that it matches forum ids whether they've been save as integers or strings.
 * @since 4.0.0 Added MySQL 8.0 compatibility.
 */
class LLMS_Integration_BBPress extends LLMS_Abstract_Integration {

	/**
	 * Integration ID
	 *
	 * @var string
	 */
	public $id = 'bbpress';

	/**
	 * Display order on Integrations tab
	 *
	 * @var integer
	 */
	protected $priority = 5;

	/**
	 * Configure the integration
	 *
	 * @since 3.8.0
	 * @since 3.30.3 Fixed spelling errors.
	 *
	 * @return void
	 */
	protected function configure() {

		add_action( 'init', array( $this, 'set_title_and_description' ) );

		if ( $this->is_available() ) {

			// Custom engagements.
			add_filter( 'lifterlms_engagement_triggers', array( $this, 'register_engagement_triggers' ) );

			add_action( 'bbp_new_topic', array( llms()->engagements(), 'maybe_trigger_engagement' ), 10, 4 );
			add_action( 'bbp_new_reply', array( llms()->engagements(), 'maybe_trigger_engagement' ), 10, 5 );

			add_filter( 'lifterlms_external_engagement_query_arguments', array( $this, 'engagement_query_args' ), 10, 3 );

			// Register shortcode.
			add_filter( 'llms_load_shortcodes', array( $this, 'register_shortcodes' ) );

			// Add memberships restriction metabox.
			add_filter( 'llms_membership_restricted_post_types', array( $this, 'add_membership_restrictions' ) );

			// Check forum/bbp template restrictions.
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks_memberships' ), 40, 1 );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks_courses' ), 50, 1 );

			// Add and save custom fields.
			add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'course_settings_fields' ) );
			add_action( 'llms_metabox_after_save_lifterlms-course-options', array( $this, 'save_course_settings' ) );
			add_filter( 'llms_get_course_properties', array( $this, 'add_course_props' ), 10, 2 );

			add_action( 'llms_content_restricted_by_bbp_course_forum', array( $this, 'handle_course_forum_restriction' ), 10, 1 );

		}
	}

	public function set_title_and_description() {
		$this->title       = __( 'bbPress', 'lifterlms' );
		$this->description = sprintf( __( 'Restrict forums and topics to memberships, add forums to courses, and %1$smore%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-bbpress/" target="_blank">', '</a>' );
	}

	/**
	 * Register the custom course property with the LLMS_Course Model
	 *
	 * @since 3.12.0
	 *
	 * @param array       $props  Default properties.
	 * @param LLMS_Course $course Course object.
	 * @return array
	 */
	public function add_course_props( $props, $course ) {
		$props['bbp_forum_ids'] = 'array';
		return $props;
	}

	/**
	 * Add the membership restrictions metabox to bbPress forums on admin panel
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $post_types Array of existing post types.
	 * @return string[]
	 */
	public function add_membership_restrictions( $post_types ) {
		$post_types[] = bbp_get_forum_post_type();
		return $post_types;
	}

	/**
	 * Register custom bbPress tab with the LLMS Course metabox
	 *
	 * @since 3.12.0
	 *
	 * @param array $fields Existing fields.
	 * @return array
	 */
	public function course_settings_fields( $fields ) {

		global $post;

		$selected = $this->get_course_forum_ids( $post );

		$fields[] = array(
			'title'  => __( 'bbPress', 'lifterlms' ),
			'fields' => array(
				array(
					'allow_null'      => false,
					'data_attributes' => array(
						'post-type'   => 'forum',
						'allow-clear' => true,
						'placeholder' => __( 'Select forums', 'lifterlms' ),
					),
					'desc'            => __( 'Add forums which will only be available to students currently enrolled in this course.', 'lifterlms' ),
					'class'           => 'llms-select2-post',
					'id'              => '_llms_bbp_forum_ids',
					'type'            => 'select',
					'label'           => __( 'Private Course Forums', 'lifterlms' ),
					'multi'           => true,
					'value'           => llms_make_select2_post_array( $selected ),
				),
			),
		);

		return $fields;
	}

	/**
	 * Parse action arguments for bbPress engagements and pass them back to the LLMS Engagements handler
	 *
	 * @since 3.12.0
	 * @since 3.37.11 Use strict comparison for `in_array()`.
	 *
	 * @param array  $query_args Query args for handler.
	 * @param string $action     Triggering action name.
	 * @param array  $orig_args  Original arguments from the action (indexed array).
	 * @return array
	 */
	public function engagement_query_args( $query_args, $action, $orig_args ) {

		if ( in_array( $action, array( 'bbp_new_reply', 'bbp_new_topic' ), true ) ) {

			$query_args['trigger_type']    = $action;
			$query_args['related_post_id'] = '';

			if ( 'bbp_new_reply' === $action ) {

				$query_args['user_id'] = $orig_args[4]; // Reply Author.

			} elseif ( 'bbp_new_topic' === $action ) {

				$query_args['user_id'] = $orig_args[3]; // Topic Author.

			}
		}

		return $query_args;
	}

	/**
	 * Handle course forum restrictions
	 *
	 * Add a notice and redirect to the course
	 *
	 * @since 3.12.0
	 * @since 3.13.0 Unknown.
	 * @since 3.37.11 Use `llms_redirect_and_exit()` in favor of `wp_redirect()`.
	 *
	 * @param array $restriction Restriction Results from `llms_page_restricted()`.
	 * @return void
	 */
	public function handle_course_forum_restriction( $restriction ) {

		/**
		 * Customize the restriction notice message displayed when a forum is restricted to a course.
		 *
		 * @since 3.37.11
		 *
		 * @param string $msg         Default message.
		 * @param array  $restriction Results from `llms_page_restricted()`.
		 */
		$msg = apply_filters( 'llms_bbp_course_forum_restriction_msg', __( 'You must be enrolled in this course to access the course forum.', 'lifterlms' ), $restriction );

		llms_add_notice( $msg, 'error' );
		llms_redirect_and_exit( get_permalink( $restriction['restriction_id'] ) );
	}

	/**
	 * Retrieve course ids restricted to a LifterLMS course
	 *
	 * @since 3.12.0
	 *
	 * @param mixed $course WP_Post, LLMS_Course, or WP_Post ID.
	 * @return array
	 */
	public function get_course_forum_ids( $course ) {

		$course = llms_get_post( $course );
		if ( ! $course ) {
			$ids = array();
		} else {
			$ids = $course->get( 'bbp_forum_ids' );
			if ( '' === $ids ) {
				$ids = array();
			}
		}

		/**
		 * Customize the bbPress forum IDs associated with a course.
		 *
		 * @since 3.37.11
		 *
		 * @param int[]       $ids    Array of WP_Post IDs of the bbPress forums restricted to the course.
		 * @param LLMS_Course $course LifterLMS course object.
		 */
		return apply_filters( 'llms_bbp_get_course_forum_ids', $ids, $course );
	}

	/**
	 * Check if a forum is restricted to a course(s)
	 *
	 * @since 3.12.0
	 * @since 3.38.1 Make the query more generic so that it matches forum ids whether they've been saved as integers or strings.
	 * @since 4.0.0 Escape `{` character in SQL query to add MySQL 8.0 support.
	 *
	 * @param int $forum_id WP_Post ID of the forum.
	 * @return int[]
	 */
	public function get_forum_course_restrictions( $forum_id ) {

		global $wpdb;
		$query = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT metas.post_id
			 FROM {$wpdb->postmeta} AS metas
			 JOIN {$wpdb->posts} AS posts on posts.ID = metas.post_id
			 WHERE metas.meta_key = '_llms_bbp_forum_ids'
			   AND metas.meta_value REGEXP %s
			   AND posts.post_status = 'publish';",
				'a:[0-9][0-9]*:\{(i:[0-9][0-9]*;(i|s:[0-9][0-9]*):"?[0-9][0-9]*"?;)*(i:[0-9][0-9]*;(i|s:[0-9][0-9]*):"?' . sprintf( '%d', absint( $forum_id ) ) . '"?;)'
			)
		);

		$query = array_map( 'absint', $query );

		return $query;
	}

	/**
	 * Determine if bbPress is installed and activated
	 *
	 * @since 3.0.0
	 *
	 * @return boolean
	 */
	public function is_installed() {
		return class_exists( 'bbPress' );
	}

	/**
	 * Register shortcodes via LifterLMS core registration methods
	 *
	 * @since 3.12.0
	 *
	 * @param string[] $classes Existing shortcode classes.
	 * @return array
	 */
	public function register_shortcodes( $classes ) {
		$classes[] = 'LLMS_BBP_Shortcode_Course_Forums_List';
		return $classes;
	}

	/**
	 * Check forum restrictions for course restrictions
	 *
	 * @since 3.12.0
	 * @since 3.12.2 Unknown.
	 *
	 * @param array $results Array of restriction results.
	 * @return array
	 */
	public function restriction_checks_courses( $results ) {

		$post_id = null;

		if ( bbp_is_forum( $results['content_id'] ) ) {

			$user_id = get_current_user_id();
			$courses = $this->get_forum_course_restrictions( $results['content_id'] );

			// No user and at least one course restriction, return the first.
			if ( $courses && ! $user_id ) {

				$post_id = $courses[0];

				// Courses and a user, find at least one enrollment.
			} elseif ( $courses && $user_id ) {

				foreach ( $courses as $course_id ) {
					// Not enrolled, use this for the restriction but dont break because we may find an enrollment later.
					if ( ! llms_is_user_enrolled( $user_id, $course_id ) ) {
						$post_id = $course_id;
						// Enrolled in one, reset the post id and break.
					} else {
						$post_id = null;
						break;
					}
				}
			}
		} elseif ( bbp_is_topic( $results['content_id'] ) ) {

			$results['content_id'] = bbp_get_topic_forum_id( $results['content_id'] );
			return $this->restriction_checks_courses( $results );

		}

		if ( $post_id ) {

			$results['restriction_id'] = $post_id;
			$results['reason']         = 'bbp_course_forum';

		}

		return $results;
	}

	/**
	 * Check membership restrictions for Topics and Forum Archive pages
	 *
	 * @since 3.12.0
	 *
	 * @param array $results Array of restriction results.
	 * @return array
	 */
	public function restriction_checks_memberships( $results ) {

		$post_id = null;

		// Forum archive, grab the page (if set).
		if ( bbp_is_forum_archive() ) {

			$page    = bbp_get_page_by_path( bbp_get_root_slug() );
			$post_id = ( $page && $page->ID ) ? $page->ID : null;
			$reason  = 'membership';

		} elseif ( bbp_is_topic( $results['content_id'] ) ) {

			$post_id = bbp_get_topic_forum_id( $results['content_id'] );
			$reason  = 'membership';

		}

		if ( $post_id ) {

			$restriction_id = llms_is_post_restricted_by_membership( $post_id, get_current_user_id() );

			if ( $restriction_id ) {

				$results['restriction_id'] = $restriction_id;
				$results['reason']         = 'membership';

			}
		}

		return $results;
	}

	/**
	 * Register engagement triggers
	 *
	 * @since 3.12.0
	 *
	 * @param string[] $triggers Existing triggers.
	 * @return array
	 */
	public function register_engagement_triggers( $triggers ) {
		$triggers['bbp_new_topic'] = __( 'Student creates a new forum topic', 'lifterlms' );
		$triggers['bbp_new_reply'] = __( 'Student creates a new forum reply', 'lifterlms' );
		return $triggers;
	}

	/**
	 * Save course metabox custom fields
	 *
	 * @since 3.12.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 3.37.11 Don't update saved forum values during course quick edits & remove redundant sanitization.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param int $post_id WP_Post ID of the course.
	 * @return null|int[]
	 */
	public function save_course_settings( $post_id ) {

		// Return early on quick edits.
		$action = llms_filter_input( INPUT_POST, 'action' );
		if ( 'inline-save' === $action ) {
			return null;
		}

		$ids = array();

		if ( isset( $_POST['_llms_bbp_forum_ids'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$ids = llms_filter_input( INPUT_POST, '_llms_bbp_forum_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		}

		update_post_meta( $post_id, '_llms_bbp_forum_ids', $ids );

		return $ids;
	}
}
