<?php
/**
 * bbPress Integration
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * bbPress Integration
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Integration_BBPress extends LLMS_Abstract_Integration {

	/**
	 * Integration ID
	 *
	 * @var  string
	 */
	public $id = 'bbpress';

	/**
	 * Display order on Integrations tab
	 *
	 * @var  integer
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

		$this->title       = __( 'bbPress', 'lifterlms' );
		$this->description = sprintf( __( 'Restrict forums and topics to memberships, add forums to courses, and %1$smore%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-and-bbpress/" target="_blank">', '</a>' );

		if ( $this->is_available() ) {

			// custom engagements
			add_filter( 'lifterlms_engagement_triggers', array( $this, 'register_engagement_triggers' ) );

			add_action( 'bbp_new_topic', array( LLMS()->engagements(), 'maybe_trigger_engagement' ), 10, 4 );
			add_action( 'bbp_new_reply', array( LLMS()->engagements(), 'maybe_trigger_engagement' ), 10, 5 );

			add_filter( 'lifterlms_external_engagement_query_arguments', array( $this, 'engagement_query_args' ), 10, 3 );

			// register shortcode
			add_filter( 'llms_load_shortcodes', array( $this, 'register_shortcodes' ) );

			// add memberships restriction metabox
			add_filter( 'llms_membership_restricted_post_types', array( $this, 'add_membership_restrictions' ) );

			// check forum/bbp template restrictions
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks_memberships' ), 40, 1 );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'restriction_checks_courses' ), 50, 1 );

			// add and save custom fields
			add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'course_settings_fields' ) );
			add_action( 'llms_metabox_after_save_lifterlms-course-options', array( $this, 'save_course_settings' ) );
			add_filter( 'llms_get_course_properties', array( $this, 'add_course_props' ), 10, 2 );

			add_action( 'llms_content_restricted_by_bbp_course_forum', array( $this, 'handle_course_forum_restriction' ), 10, 1 );

		}

	}

	/**
	 * Register the custom course property with the LLMS_Course Model
	 *
	 * @param    array $props   default properties
	 * @param    obj   $course  instance of the LLMS_Course
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function add_course_props( $props, $course ) {
		$props['bbp_forum_ids'] = 'array';
		return $props;
	}

	/**
	 * Add the membership restrictions metabox to bbPress forums on admin panel
	 *
	 * @param    array $post_types    array of existing post types
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_membership_restrictions( $post_types ) {
		$post_types[] = bbp_get_forum_post_type();
		return $post_types;
	}

	/**
	 * Register custom bbPress tab with the LLMS Course metabox
	 *
	 * @param    array $fields  existing fields
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
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
	 * @param    array  $query_args  query args for handler
	 * @param    string $action      triggering action name
	 * @param    array  $orig_args   original arguments from the action (indexed array)
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function engagement_query_args( $query_args, $action, $orig_args ) {

		if ( in_array( $action, array( 'bbp_new_reply', 'bbp_new_topic' ) ) ) {

			$query_args['trigger_type']    = $action;
			$query_args['related_post_id'] = '';

			if ( 'bbp_new_reply' === $action ) {

				$query_args['user_id'] = $orig_args[4]; // $reply_author

			} elseif ( 'bbp_new_topic' === $action ) {

				$query_args['user_id'] = $orig_args[3]; // $topic_author

			}
		}

		return $query_args;

	}

	/**
	 * Handle course forum restrictions
	 * Add a notice and redirect to the course
	 *
	 * @param    array $restriction  restriction results from llms_page_restricted()
	 * @return   void
	 * @since    3.12.0
	 * @version  3.13.0
	 */
	public function handle_course_forum_restriction( $restriction ) {
		llms_add_notice( apply_filters( 'llms_bbp_course_forum_restriction_msg', __( 'You must be enrolled in this course to access the course forum', 'lifterlms' ), $restriction ), 'error' );
		wp_redirect( get_permalink( $restriction['restriction_id'] ) );
		exit;

	}

	/**
	 * Retrieve course ids restricted to a LifterLMS course
	 *
	 * @param    mixed $course  WP_Post, LLMS_Course, or WP_Post ID
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
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

		return apply_filters( 'llms_bbp_get_course_forum_ids', $ids, $course );

	}

	/**
	 * Check if a forum is restricted to a course(s)
	 *
	 * @param    int $forum_id  WP_Post ID of the forum
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function get_forum_course_restrictions( $forum_id ) {

		global $wpdb;
		$query = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT metas.post_id
			 FROM {$wpdb->postmeta} AS metas
			 JOIN {$wpdb->posts} AS posts on posts.ID = metas.post_id
			 WHERE metas.meta_key = '_llms_bbp_forum_ids'
			   AND metas.meta_value LIKE %s
			   AND posts.post_status = 'publish';",
				'%' . sprintf( 'i:%d;', absint( $forum_id ) ) . '%'
			)
		);

		$query = array_map( 'absint', $query );

		return $query;

	}

	/**
	 * Determine if bbPress is installed and activated
	 *
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_installed() {
		return ( class_exists( 'bbPress' ) );
	}

	/**
	 * Register shortcodes via LifterLMS core registration methods
	 *
	 * @param    array $classes  existing shortcode classes
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function register_shortcodes( $classes ) {
		$classes[] = 'LLMS_BBP_Shortcode_Course_Forums_List';
		return $classes;
	}

	/**
	 * Check forum restrictions for course restrictions
	 *
	 * @param    array $results  array of restriction results
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.2
	 */
	public function restriction_checks_courses( $results ) {

		$post_id = null;

		if ( bbp_is_forum( $results['content_id'] ) ) {

			$user_id = get_current_user_id();
			$courses = $this->get_forum_course_restrictions( $results['content_id'] );

			// no user and at least one course restriction, return the first
			if ( $courses && ! $user_id ) {

				$post_id = $courses[0];

				// courses and a user, find at least one enrollment
			} elseif ( $courses && $user_id ) {

				foreach ( $courses as $course_id ) {
					// not enrolled, use this for the restriction
					// but dont break because we may find an enrollment later
					if ( ! llms_is_user_enrolled( $user_id, $course_id ) ) {
						$post_id = $course_id;
						// enrolled in one, reset the post id and break
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
	 * @param    array $results  array of restriction results
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function restriction_checks_memberships( $results ) {

		$post_id = null;

		// forum archive, grab the page (if set)
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
	 * @param    array $triggers  existing triggers
	 * @return   array
	 * @since    3.12.0
	 * @version  3.12.0
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
	 *
	 * @param    int $post_id  WP_Post ID of the course
	 * @return   void
	 */
	public function save_course_settings( $post_id ) {

		$ids = array();

		if ( isset( $_POST['_llms_bbp_forum_ids'] ) ) {  // phpcs:disable WordPress.Security.NonceVerification.Missing

			$ids = llms_filter_input( INPUT_POST, '_llms_bbp_forum_ids', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
			if ( ! is_array( $ids ) ) {
				$ids = array( $ids );
			}

			$ids = array_map( 'absint', $ids );

		}

		update_post_meta( $post_id, '_llms_bbp_forum_ids', $ids );

	}

}
