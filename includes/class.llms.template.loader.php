<?php
/**
 * Template loader
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template loader class.
 *
 * @since 1.0.0
 * @since 3.20.0 Unknown.
 * @since 3.37.2 Notices are printed on sales pages too.
 * @since 3.37.10 Notices are printed on pages configured as a membership restriction redirect page.
 * @since 3.41.1 Fixed content membership restricted post's content not restricted in REST requests.
 * @since 4.0.0 Don't pass objects by reference because it's unnecessary.
 */
class LLMS_Template_Loader {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.20.0 Unknown.
	 * @since 3.41.1 Predispose posts content restriction in REST requests.
	 * @since 5.8.0 Handle block templates loading.
	 * @since 6.2.0 Added 'llms_template_loader_priority' filter.
	 * @since 6.4.0 Reverted back the priority of the `$this->template_loader()` callback
	 *              (`template_include` hook's callback) from 100 to 10.
	 */
	public function __construct() {

		// Template loading for FSE themes.
		add_action( 'template_redirect', array( $this, 'hook_block_template_loader' ) );

		/**
		* Filters the template loading priority.
		*
		* Callback for the WP core filter `template_include`.
		*
		* @since 6.2.0
		*
		* @param int $priority The filter callback priority.
		*/
		$template_loader_cb_priority = apply_filters( 'llms_template_loader_priority', 10 );
		/**
		 * Do template loading.
		 *
		 * The default priority is 10, so to allow theme builders, like Divi and Elementor (Pro),
		 * to override our templates (except single content restricted).
		 * see https://github.com/gocodebox/lifterlms/issues/2111
		 */
		add_filter( 'template_include', array( $this, 'template_loader' ), $template_loader_cb_priority );

		add_action( 'rest_api_init', array( $this, 'maybe_prepare_post_content_restriction' ) );

		// Restriction actions for each kind of restriction.
		$reasons = apply_filters(
			'llms_restriction_reasons',
			array(
				'course_prerequisite',
				'course_track_prerequisite',
				'course_time_period',
				'enrollment_lesson',
				'lesson_drip',
				'lesson_prerequisite',
				'membership',
				'sitewide_membership',
				'quiz',
			)
		);

		foreach ( $reasons as $reason ) {
			add_action( 'llms_content_restricted_by_' . $reason, array( $this, 'restricted_by_' . $reason ), 10, 1 );
		}

		add_action( 'wp', array( $this, 'maybe_redirect_to_sales_page' ) );
	}

	/**
	 * Add a notice and/or redirect during restriction actions.
	 *
	 * @since 3.0.0
	 * @since 7.4.0 Added `nocache_headers()` to prevent caching of redirects.
	 *
	 * @param string $msg      Notice message to display.
	 * @param string $redirect Optional. Url to redirect to after setting a notice. Default empty string.
	 * @param string $msg_type Optional. Type of message to display [notice|success|error|debug]. Default 'notice'.
	 * @return void
	 */
	private function handle_restriction( $msg = '', $redirect = '', $msg_type = 'notice' ) {

		if ( $msg ) {
			llms_add_notice( do_shortcode( $msg ), $msg_type );
		}

		if ( $redirect ) {
			nocache_headers();
			wp_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Handle sales page redirects for courses & memberships
	 *
	 * @since 3.20.0
	 * @since 3.37.2 Flag to print notices, if there are, when landing on the redirected sales page.
	 *
	 * @return void
	 */
	public function maybe_redirect_to_sales_page() {

		// Only proceed for courses and memberships.
		if ( ! in_array( get_post_type(), array( 'course', 'llms_membership' ), true ) ) {
			return;
		}

		$page_restricted = llms_page_restricted( get_the_id() );

		// Only proceed if the page isn't restricted.
		if ( ! $page_restricted['is_restricted'] ) {
			return;
		}

		/** @var LLMS_Trait_Sales_Page $post */
		$post = llms_get_post( get_the_ID() );

		if ( ! $post->has_sales_page_redirect() ) {
			return;
		}

		llms_redirect_and_exit(
			llms_notice_count() ?
				add_query_arg(
					array(
						'llms_print_notices' => 1,
					),
					$post->get_sales_page_url()
				) : $post->get_sales_page_url(),
			array(
				'safe' => false,
			)
		);
	}

	/**
	 * Handle redirects and messages when a user attempts to access an item
	 * restricted by a course track prerequisite.
	 *
	 * Redirect to parent course and display message.
	 * If course do nothing.
	 *
	 * @since 3.7.3
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_course_track_prerequisite( $info ) {

		if ( 'course' === get_post_type( $info['content_id'] ) ) {
			return;
		}

		$msg      = llms_get_restriction_message( $info );
		$course   = llms_get_post_parent_course( $info['content_id'] );
		$redirect = get_permalink( $course->get( 'id' ) );
		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_course_track_prerequisite_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_course_track_prerequisite_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle redirects and messages when a user attempts to access an item
	 * restricted by a course prerequisite.
	 *
	 * Redirect to parent course and display message.
	 * If course do nothing.
	 *
	 * @since 3.7.3
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_course_prerequisite( $info ) {

		if ( 'course' === get_post_type( $info['content_id'] ) ) {
			return;
		}

		$msg      = llms_get_restriction_message( $info );
		$course   = llms_get_post_parent_course( $info['content_id'] );
		$redirect = get_permalink( $course->get( 'id' ) );
		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_course_prerequisite_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_course_prerequisite_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle redirects and messages when a course or associated quiz or lesson has time period
	 * date restrictions placed upon it.
	 *
	 * Quizzes & Lessons redirect to the parent course.
	 * Courses display a notice until the course opens and an error once the course closes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_course_time_period( $info ) {

		$post_type = get_post_type( $info['content_id'] );

		// If this restriction occurs when attempting to view a lesson,
		// redirect the user to the course, course restriction will handle display of the
		// message once we get there.
		// This prevents duplicate messages from being displayed.
		if ( 'lesson' === $post_type || 'llms_quiz' === $post_type ) {
			$msg      = '';
			$redirect = get_permalink( $info['restriction_id'] );
		}

		if ( ! $msg && ! $redirect ) {
			return;
		}

		// Handle the restriction action & allow developers to filter the results.
		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_course_time_period_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_course_time_period_redirect', $redirect, $info ),
			'notice'
		);
	}

	/**
	 * Handle redirects and messages when a user attempts to access a lesson
	 * for a course they're not enrolled in.
	 *
	 * Redirect to parent course and display message.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 Moved message generation to `llms_get_restriction_message()`
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_enrollment_lesson( $info ) {

		$msg      = llms_get_restriction_message( $info );
		$redirect = get_permalink( $info['restriction_id'] );

		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_enrollment_lesson_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_enrollment_lesson_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle redirects and messages when a user attempts to access a lesson
	 * for that is restricted by lesson drip settings.
	 *
	 * Redirect to parent course and display message.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 Moved message generation to `llms_get_restriction_message()`
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_lesson_drip( $info ) {

		$lesson = new LLMS_Lesson( $info['restriction_id'] );

		$msg      = llms_get_restriction_message( $info );
		$redirect = get_permalink( $lesson->get( 'parent_course' ) );

		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_lesson_drip_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_lesson_drip_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle redirects and messages when a user attempts to access a lesson
	 * for that is restricted by prerequisite lesson.
	 *
	 * Redirect to parent course and display message.
	 *
	 * @since 3.0.0
	 * @since 3.2.4 Moved message generation to `llms_get_restriction_message()`
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_lesson_prerequisite( $info ) {

		$msg      = llms_get_restriction_message( $info );
		$redirect = get_permalink( $info['restriction_id'] );
		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_lesson_prerequisite_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_lesson_prerequisite_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle content restricted to a membership.
	 *
	 * Parses and obeys Membership "Restriction Behavior" settings.
	 *
	 * @since 3.0.0
	 * @since 3.37.10 Added Flag to print notices when landing on the redirected page.
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_membership( $info ) {

		$membership_id = $info['restriction_id'];

		// Do nothing if we don't have a membership id.
		if ( ! empty( $membership_id ) && is_numeric( $membership_id ) ) {

			// Instantiate the membership.
			$membership = new LLMS_Membership( $membership_id );

			$msg      = '';
			$redirect = '';

			if ( 'yes' === $membership->get( 'restriction_add_notice' ) ) {

				$msg = $membership->get( 'restriction_notice' );

			}

			// Get the redirect based on the redirect type (if set).
			switch ( $membership->get( 'restriction_redirect_type' ) ) {

				case 'custom':
					$redirect = $membership->get( 'redirect_custom_url' );
					break;

				case 'membership':
					$redirect = get_permalink( $membership->get( 'id' ) );
					break;

				case 'page':
					$redirect = get_permalink( $membership->get( 'redirect_page_id' ) );
					// Make sure to print notices in wp pages.
					$redirect = empty( $msg ) ? $redirect : add_query_arg(
						array(
							'llms_print_notices' => 1,
						),
						$redirect
					);
					break;

			}

			// Handle the restriction action & allow developers to filter the results.
			$this->handle_restriction(
				apply_filters( 'llms_restricted_by_membership_message', $msg, $info ),
				apply_filters( 'llms_restricted_by_membership_redirect', $redirect, $info )
			);

		}
	}

	/**
	 * Handle attempts to access quizzes.
	 *
	 * @since 3.1.6
	 * @since 3.16.1 Unknown.
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_quiz( $info ) {

		$msg      = '';
		$redirect = '';

		if ( get_current_user_id() ) {

			$msg  = __( 'You must be enrolled in the course to access this quiz.', 'lifterlms' );
			$quiz = llms_get_post( $info['restriction_id'] );
			if ( $quiz ) {
				$course = $quiz->get_course();
				if ( $course ) {
					$redirect = get_permalink( $course->get( 'id' ) );
				}
			}
		} else {

			$msg      = __( 'You must be logged in to take quizzes.', 'lifterlms' );
			$redirect = llms_person_my_courses_url();

		}

		$this->handle_restriction(
			apply_filters( 'llms_restricted_by_membership_message', $msg, $info ),
			apply_filters( 'llms_restricted_by_membership_redirect', $redirect, $info ),
			'error'
		);
	}

	/**
	 * Handle content restricted to a membership
	 *
	 * Parses and obeys Membership "Restriction Behavior" settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $info Array of restriction info from `llms_page_restricted()`.
	 * @return void
	 */
	public function restricted_by_sitewide_membership( $info ) {
		$this->restricted_by_membership( $info );
	}

	/**
	 * Hooks the callback to load FSE block templates.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public function hook_block_template_loader() {
		add_filter( 'pre_get_block_templates', array( $this, 'block_template_loader' ), 99, 3 );
	}

	/**
	 * Filter blocks templates.
	 *
	 * @since 5.8.0
	 * @since 6.0.0 Remove LifterLMS 6.0 version check about the certificate template.
	 *              Use `llms_is_block_theme()` in favor of `wp_is_block_theme()`.
	 *
	 * @param WP_Block_Template[] $result        Array of found block templates.
	 * @param array               $query {
	 *     Optional. Arguments to retrieve templates.
	 *
	 *     @type array  $slug__in List of slugs to include.
	 *     @type int    $wp_id Post ID of customized template.
	 * }
	 * @param array               $template_type wp_template or wp_template_part.
	 * @return array Templates.
	 */
	public function block_template_loader( $result, $query, $template_type ) {

		// Bail if it's not a block theme, or is being retrieved a non wp_template file.
		if ( ! llms_is_block_theme() || 'wp_template' !== $template_type ) {
			return $result;
		}

		$template_name = $this->get_maybe_forced_template();

		/**
		 * Since LifterLMS 6.0.0 certificates have their own PHP template that do no depend on the theme.
		 * This means that we can use the PHP template loaded in the method `LLMS_Template_Loader::template_loader()` below.
		 */
		$template_name = is_singular( array( 'llms_certificate', 'llms_my_certificate' ) ) ? '' : $template_name;

		/**
		 * Filters the block template to be loded forced.
		 *
		 * @since 5.8.0
		 *
		 * @param string $template_slug The template slug to be force loaded.
		 * @param string $template      The name of template to be force loaded.
		 */
		$template_slug = apply_filters( 'llms_forced_block_template_slug', $template_name ? LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . $template_name : '', $template_name );

		if ( empty( $template_slug ) ) {
			return $result;
		}

		// Prevent template_loader to load a php template.
		add_filter( 'llms_force_php_template_loading', '__return_false' );

		return llms()->block_templates()->add_llms_block_templates(
			array(),
			array( 'slug__in' => array( $template_slug ) )
		);
	}

	/**
	 * Check if content should be restricted and include overrides where appropriate.
	 *
	 * Triggers actions based on content restrictions.
	 *
	 * @since 1.0.0
	 * @since 3.16.11 Unknown.
	 * @since 3.37.2 Make sure to print notices on sales page redirect.
	 * @since 4.10.1 Refactor to reduce code duplication and replace usage of `llms_shop` with `courses` for catalog check.
	 * @since 5.8.0 Refactor: moved the template guessing in a specific method.
	 * @since 6.4.0 Defer single content restricted template loading.
	 *
	 * @param string $template The template to load.
	 * @return string
	 */
	public function template_loader( $template ) {

		$page_restricted = llms_page_restricted( get_the_ID() );

		$this->maybe_print_notices_on_sales_page_redirect();

		if ( $page_restricted['is_restricted'] ) {
			/**
			 * Generic action triggered when content is restricted.
			 *
			 * @since Unknown
			 *
			 * @see llms_content_restricted_by_{$page_restricted['reason']} A specific hook triggered by a specific restriction reason.
			 *
			 * @param array $page_restricted Restriction information from `llms_page_restricted()`.
			 */
			do_action( 'lifterlms_content_restricted', $page_restricted );

			/**
			 * Action triggered when content is restricted for the specified reason.
			 *
			 * The dynamic portion of this hook, `{$page_restricted['reason']}` refers to the restriction reason
			 * code generated by `llms_page_restricted()`.
			 *
			 * @since Unknown
			 *
			 * @see llms_content_restricted A generic hook triggered at the same time.
			 *
			 * @param array $page_restricted Restriction information from `llms_page_restricted()`.
			 */
			do_action( "llms_content_restricted_by_{$page_restricted['reason']}", $page_restricted );

			if ( is_home() && 'sitewide_membership' === $page_restricted['reason'] ) {
				// Prints notices on the blog page when there's not redirects setup.
				add_action( 'loop_start', 'llms_print_notices', 5 );
			}
		}

		$forced_template = $this->maybe_force_php_template( $template );

		/**
		 * When restricting single content use a lower priority so to always override
		 * theme builders like Divi and Elementor (Pro).
		 * see https://github.com/gocodebox/lifterlms/issues/2063.
		 */
		if ( llms_template_file_path( 'single-no-access.php' ) === $forced_template ) {

			/**
			 * Filters the template loading priority for single restricted content.
			 *
			 * @since 6.4.0
			 *
			 * @param int $priority The filter callback priority.
			 */
			$template_loader_restricted_cb_priority = apply_filters( 'llms_template_loader_restricted_priority', 100 );
			add_filter( 'template_include', array( $this, 'maybe_force_php_template' ), $template_loader_restricted_cb_priority );

		} else {
			$template = $forced_template;
		}

		return $template;
	}

	/**
	 * Force the PHP template to be loaded.
	 *
	 * @since 6.4.0
	 *
	 * @param string $template The original template to load.
	 * @return string
	 */
	public function maybe_force_php_template( $template ) {

		/**
		 * Filters whether or not forcing a LifterLMS php template to be loaded.
		 *
		 * @since 5.8.0
		 *
		 * @param bool $force Whether or not forcing a LifterLMS PHP template to be loaded.
		 */
		$forced_template = apply_filters( 'llms_force_php_template_loading', true ) ? $this->get_maybe_forced_template() : false;
		return $forced_template ? llms_template_file_path( "{$forced_template}.php" ) : $template;
	}

	/**
	 * Retrieve the hierarchical template to be loaded.
	 *
	 * @since 5.8.0
	 *
	 * @return null|string
	 */
	private function get_maybe_forced_template() {

		$page_restricted = llms_page_restricted( get_the_ID() );
		$template        = null;

		if ( $page_restricted['is_restricted'] ) {

			// Blog should bypass checks, except when sitewide restrictions are enabled.
			if ( ( is_home() && 'sitewide_membership' === $page_restricted['reason'] ) ||
					// Course and membership content restrictions are handled by conditional elements in the editor.
					( in_array( get_post_type(), array( 'course', 'llms_membership' ), true ) ) ) {
				return;
			}

			// Content is restricted.
			$template = 'single-no-access';

		} elseif ( is_post_type_archive( 'course' ) || is_page( llms_get_page_id( 'courses' ) ) ) {

			$template = 'archive-course';

		} elseif ( is_post_type_archive( 'llms_membership' ) || is_page( llms_get_page_id( 'memberships' ) ) ) {

			$template = 'archive-llms_membership';

		} elseif ( is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {

			global $wp_query;
			$obj      = $wp_query->get_queried_object();
			$template = 'taxonomy-' . $obj->taxonomy;

		} elseif ( is_singular( array( 'llms_certificate', 'llms_my_certificate' ) ) ) {

			$template = 'single-certificate';

		}

		/**
		 * Filters the template to be loded forced.
		 *
		 * @since 5.8.0
		 *
		 * @param string $template The template slug to be loaded forced.
		 */
		return apply_filters( 'llms_forced_template', $template );
	}

	/**
	 * Maybe print notices after redirection.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	private function maybe_print_notices_on_sales_page_redirect() {

		if ( llms_filter_input( INPUT_GET, 'llms_print_notices' ) ) {
			// Prints notices on the page at loop start.
			add_action( 'loop_start', 'llms_print_notices', 5 );
		}
	}

	/**
	 * Maybe restrict the post content in REST requets
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function maybe_prepare_post_content_restriction() {
		// Fired on `setup_postdata()` see `WP_REST_Posts_Controller::prepare_item_for_response()`.
		add_action( 'the_post', array( $this, 'maybe_restrict_post_content' ), 9999, 2 );
	}

	/**
	 * Maybe restrict the post content in the REST loop
	 *
	 * @since 3.41.1
	 * @since 4.0.0 Don't pass by reference because it's unnecessary.
	 * @since 4.10.1 Fixed incorrect position of `true` in `in_array()`.
	 *
	 * @param WP_Post  $post  Post Object.
	 * @param WP_Query $query Query object.
	 * @return void
	 */
	public function maybe_restrict_post_content( $post, $query ) {
		/**
		 * Filters the post types that must be skipped.
		 *
		 * The LifterLMS post types content restriction should be handled by the LifterLMS rest-api.
		 *
		 * @since 3.41.1
		 *
		 * @param string[] $post_types The array of post types to skip.
		 */
		$skip = apply_filters(
			'llms_in_rest_restrict_content_skip_post_types',
			array(
				'course',
				'lesson',
				'llms_quiz',
				'llms_membership',
				'llms_question',
				'llms_certificate',
				'llms_my_certificate',
			)
		);

		if ( in_array( get_post_type( $post ), $skip, true ) ) {
			return;
		}

		// Needed by `llms_page_restricted()` to work as expected.
		$is_singular        = $query->is_singular;
		$query->is_singular = true;

		$page_restricted = llms_page_restricted( get_the_ID() );

		if ( $page_restricted['is_restricted'] ) {

			$msg    = __( 'This content is restricted', 'lifterlms' );
			$reason = $page_restricted['reason'];

			if ( in_array( $reason, array( 'membership', 'sitewide_membership' ), true ) ) {

				$membership_id = $page_restricted['restriction_id'];

				if ( ! empty( $membership_id ) && is_numeric( $membership_id ) ) {

					$membership = new LLMS_Membership( $membership_id );

					if ( 'yes' === $membership->get( 'restriction_add_notice' ) ) {
						$msg = $membership->get( 'restriction_notice' );
					}
				}
			}

			/**
			 * Filters the restriction message.
			 *
			 * The dynamic portion of the hook name, `$reason`, refers to the restriction reason.
			 *
			 * @since 3.41.1
			 *
			 * @param string $message     Restriction message.
			 * @param array  $restriction Array of restriction info from `llms_page_restricted()`.
			 */
			$msg = apply_filters( "llms_in_rest_restricted_by_{$reason}_message", $msg, $page_restricted );

			$post->post_content = $msg;
			$post->post_excerpt = $msg;
		}

		$query->is_singular = $is_singular;
	}
}

new LLMS_Template_Loader();
