<?php
/**
 * LLMS_Engagements class file
 *
 * @package LifterLMS/Classes
 *
 * @since 2.3.0
 * @version 6.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagements Class
 *
 * @since 2.3.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Changes:
 *              - Deprecated the `LLMS_Engagements::handle_achievement()` method.
 *                Use the {@see LLMS_Engagement_Handler::handle_achievement()} method instead.
 *              - Deprecated the `LLMS_Engagements::handle_certificate()` method.
 *                Use the {@see LLMS_Engagement_Handler::handle_certificate()} method instead.
 *              - Deprecated the `LLMS_Engagements::handle_email()` method.
 *                Use the {@see LLMS_Engagement_Handler::handle_email()} method instead.
 *              - Deprecated the `LLMS_Engagements::init()` method with no replacement.
 *              - Deprecated the `LLMS_Engagements::log()` method.
 *                Engagement debug logging is removed. Use the {@see llms_log()} function directly instead.
 *              - Removed the deprecated `LLMS_Engagements::$_instance` property.
 */
class LLMS_Engagements {

	use LLMS_Trait_Singleton;

	/**
	 * Enable debug logging
	 *
	 * @since 2.7.9
	 * @var boolean
	 */
	private $debug = false;

	/**
	 * Constructor
	 *
	 * Adds actions to events that trigger engagements.
	 *
	 * @since 2.3.0
	 * @since 6.0.0 Added deprecation warning when using constant `LLMS_ENGAGEMENT_DEBUG`.
	 *              Don't call deprecated `init()` method.
	 *
	 * @return void
	 */
	private function __construct() {

		if ( defined( 'LLMS_ENGAGEMENT_DEBUG' ) && LLMS_ENGAGEMENT_DEBUG ) {
			_deprecated_function( 'Constant: LLMS_ENGAGEMENT_DEBUG', '6.0.0' );
			$this->debug = true;
		}

		$this->add_actions();
	}

	/**
	 * Register all actions that trigger engagements
	 *
	 * @since 2.3.0
	 * @since 3.11.0 Unknown.
	 * @since 3.39.0 Added `llms_rest_student_registered` as action hook.
	 * @since 6.0.0 Moved the list of hooks to the `get_trigger_hooks()` method.
	 *
	 * @return void
	 */
	private function add_actions() {

		foreach ( $this->get_trigger_hooks() as $action ) {
			add_action( $action, array( $this, 'maybe_trigger_engagement' ), 777, 3 );
		}

		// Handlers are in charge of processing (awarding/sending) the email/cert/achievement.
		$handlers = array(
			'lifterlms_engagement_send_email'        => 'handle_email',
			'lifterlms_engagement_award_achievement' => 'handle_achievement',
			'lifterlms_engagement_award_certificate' => 'handle_certificate',
		);
		foreach ( $handlers as $action => $method ) {

			/**
			 * Adds an action for the deprecated method so that `remove_action()` calls
			 * on the old method will continue to remove the new method.
			 *
			 * When we *remove* the deprecated methods we can remove this logic.
			 */
			add_action( $action, array( $this, $method ) );

			// If the above action has been completely removed this will be false and we won't add the new method callback.
			$priority = has_action( $action, array( $this, $method ) );
			if ( false !== $priority ) {
				// Remove the deprecated action.
				remove_action( $action, array( $this, $method ) );
				// Call the new action at the specified priority. If the old action was restored at a different priority this will retain that customization.
				add_action( $action, array( 'LLMS_Engagement_Handler', $method ), $priority );
			}
		}

		add_action( 'deleted_post', array( $this, 'unschedule_delayed_engagements' ), 20, 2 );
	}

	/**
	 * Retrieve a group id used when scheduling delayed engagement action triggers.
	 *
	 * @since 6.0.0
	 *
	 * @param int $engagement_id WP_Post ID of the `llms_engagement` post type.
	 * @return string
	 */
	private function get_delayed_group_id( $engagement_id ) {
		return sprintf( 'llms_engagement_%d', $engagement_id );
	}

	/**
	 * Retrieve engagements based on the trigger type
	 *
	 * Joins rather than nested loops and sub queries ftw.
	 *
	 * @since 2.3.0
	 * @since 3.13.1 Unknown.
	 * @since 6.0.0 Removed engagement debug logging & moved filter onto the return instead of calling in `maybe_trigger_engagement()`.
	 *
	 * @param string     $trigger_type    Name of the trigger to look for.
	 * @param int|string $related_post_id The WP_Post ID of the related post or an empty string.
	 * @return object[] {
	 *     Array of objects from the database.
	 *
	 *     @type int    $engagement_id WP_Post ID of the engagement post (email, certificate, achievement).
	 *     @type int    $trigger_id    WP_Post ID of the llms_engagement post.
	 *     @type string $trigger_event The triggering action (user_registration, course_completed, etc...).
	 *     @type string $event_type    The engagement event action (certificate, achievement, email).
	 *     @type int    $delay         The engagement send delay (in days).
	 * }
	 */
	private function get_engagements( $trigger_type, $related_post_id = '' ) {

		global $wpdb;

		$related_select = '';
		$related_join   = '';
		$related_where  = '';

		if ( $related_post_id ) {

			$related_select = ', relation_meta.meta_value AS related_post_id';
			$related_join   = "LEFT JOIN $wpdb->postmeta AS relation_meta ON triggers.ID = relation_meta.post_id";
			$related_where  = $wpdb->prepare( "AND relation_meta.meta_key = '_llms_engagement_trigger_post' AND relation_meta.meta_value = %d", $related_post_id );

		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				  DISTINCT triggers.ID AS trigger_id
				, triggers_meta.meta_value AS engagement_id
				, engagements_meta.meta_value AS trigger_event
				, event_meta.meta_value AS event_type
				, delay.meta_value AS delay
				$related_select

			FROM $wpdb->postmeta AS engagements_meta

			LEFT JOIN $wpdb->posts AS triggers ON triggers.ID = engagements_meta.post_id
			LEFT JOIN $wpdb->postmeta AS triggers_meta ON triggers.ID = triggers_meta.post_id
			LEFT JOIN $wpdb->posts AS engagements ON engagements.ID = triggers_meta.meta_value
			LEFT JOIN $wpdb->postmeta AS event_meta ON triggers.ID = event_meta.post_id
			LEFT JOIN $wpdb->postmeta AS delay ON triggers.ID = delay.post_id
			$related_join

			WHERE
				    triggers.post_type = 'llms_engagement'
				AND triggers.post_status = 'publish'
				AND triggers_meta.meta_key = '_llms_engagement'

				AND engagements_meta.meta_key = '_llms_trigger_type'
				AND engagements_meta.meta_value = %s
				AND engagements.post_status = 'publish'

				AND event_meta.meta_key = '_llms_engagement_type'

				AND delay.meta_key = '_llms_engagement_delay'

				$related_where
			",
				// Prepare variables.
				$trigger_type
			),
			OBJECT
		); // no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		/**
		 * Filters the list of engagements to be triggered for a given trigger type and related post.
		 *
		 * @since 6.0.0
		 *
		 * @param object[] $results         Array of engagement objects.
		 * @param string   $trigger_type    Name of the engagement trigger.
		 * @param int      $related_post_id WP_Post ID of the related post.
		 */
		return apply_filters( 'lifterlms_get_engagements', $results, $trigger_type, $related_post_id );
	}

	/**
	 * Retrieve a list of hooks that trigger engagements to be awarded.
	 *
	 * @since 6.0.0
	 *
	 * @return string[]
	 */
	protected function get_trigger_hooks() {

		$hooks = array(
			'lifterlms_access_plan_purchased',
			'lifterlms_course_completed',
			'lifterlms_course_track_completed',
			'lifterlms_lesson_completed',
			'lifterlms_product_purchased',
			'lifterlms_quiz_completed',
			'lifterlms_quiz_failed',
			'lifterlms_quiz_passed',
			'lifterlms_section_completed',
			'lifterlms_user_registered',
			'llms_rest_student_registered',
			'llms_user_added_to_membership_level',
			'llms_user_enrolled_in_course',
		);

		// If there are any actions registered to this deprecated hook, add it to the list.
		if ( has_action( 'lifterlms_created_person' ) ) {
			$hooks[] = 'lifterlms_created_person';
		}

		/**
		 * Filters the list of hooks which can trigger engagements to be sent/awarded.
		 *
		 * @since 2.3.0
		 *
		 * @param string[] $hooks List of hook names.
		 */
		return apply_filters( 'lifterlms_engagement_actions', $hooks );
	}

	/**
	 * Include engagement types (excluding email)
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Engagements::init()` is deprecated with no replacement.
	 *
	 * @return void
	 */
	public function init() {
		_deprecated_function( 'LLMS_Engagements::init()', '6.0.0' );
	}

	/**
	 * Award an achievement
	 *
	 * @since 2.3.0
	 * @deprecated 6.0.0 `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
	 *
	 * @param array $args {
	 *     Indexed array of arguments.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the achievement template post.
	 *     @type int|string $2 WP_Post ID of the related post that triggered the award or an empty string.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return void
	 */
	public function handle_achievement( $args ) {
		_deprecated_function( 'LLMS_Engagements::handle_achievement', '6.0.0', 'LLMS_Engagement_Handler::handle_achievement' );
		LLMS_Engagement_Handler::handle_achievement( $args );
	}

	/**
	 * Award a certificate
	 *
	 * @since 2.3.0
	 * @deprecated 6.0.0 `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
	 *
	 * @param array $args {
	 *     Indexed array of arguments.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the certificate template post.
	 *     @type int|string $2 WP_Post ID of the related post that triggered the award or an empty string.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return void
	 */
	public function handle_certificate( $args ) {
		_deprecated_function( 'LLMS_Engagements::handle_certificate', '6.0.0', 'LLMS_Engagement_Handler::handle_certificate' );
		LLMS_Engagement_Handler::handle_certificate( $args );
	}

	/**
	 * Send an email engagement
	 *
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class.
	 *
	 * @since 2.3.0
	 * @since 3.8.0 Unknown.
	 * @since 4.4.1 Use postmeta helpers for dupcheck and postmeta insertion.
	 *              Add a return value in favor of `void`.
	 *              Log successes and failures to the `engagement-emails` log file instead of the main `llms` log.
	 * @since 4.4.3 Fixed different emails triggered by the same related post not sent because of a wrong duplicate check.
	 *              Fixed dupcheck log message and error message which reversed the email and person order.
	 * @deprecated 6.0.0 `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
	 *
	 * @param mixed[] $args {
	 *     An array of arguments from the triggering hook.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the email.
	 *     @type int|string $2 WP_Post ID of the related triggering post or an empty string for engagements with no related post.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return bool|WP_Error Returns `true` on success, `false` when the email is skipped, and a `WP_Error` when
	 *                       the email has failed or is prevented.
	 */
	public function handle_email( $args ) {
		_deprecated_function( 'LLMS_Engagements::handle_email', '6.0.0', 'LLMS_Engagement_Handler::handle_email' );
		$res = LLMS_Engagement_Handler::handle_email( $args );
		if ( true === $res ) {
			return $res;
		}
		// The new handler returns an array of errors in favor of a single error. Retain the initial return type for this deprecated version.
		return $res[0];
	}

	/**
	 * Parse incoming hook / callback data to determine if an engagement should be triggered from a given hook.
	 *
	 * @since 6.0.0
	 * @since 6.6.0 Fixed an issue where the `lifterlms_external_engagement_query_arguments` filter
	 *              would not trigger if a 3rd party registered a trigger hook.
	 *
	 * @param string $action Action hook name.
	 * @param array  $args   Array of arguments passed to the callback function.
	 * @return array {
	 *     An associative array of parsed data used to trigger the engagement.
	 *
	 *     @type string $trigger_type    The name of the engagement trigger. See `llms_get_engagement_triggers()` for a list of valid triggers.
	 *     @type int    $user_id         The WP_User ID of the user who the engagement is being awarded or sent to.
	 *     @type int    $related_post_id The WP_Post ID of a related post.
	 *  }
	 */
	private function parse_hook( $action, $args ) {

		$parsed = array(
			'trigger_type'    => null,
			'user_id'         => null,
			'related_post_id' => null,
		);

		/**
		 * Allows 3rd parties to hook into the core engagement system by parsing data passed to the hook.
		 *
		 * @since 2.3.0
		 *
		 * @param array $parsed {
		 *     An associative array of parsed data used to trigger the engagement.
		 *
		 *     @type string $trigger_type    (Required) The name of the engagement trigger. See `llms_get_engagement_triggers()` for a list of valid triggers.
		 *     @type int    $user_id         (Required) The WP_User ID of the user who the engagement is being awarded or sent to.
		 *     @type int    $related_post_id (Optional) The WP_Post ID of a related post.
		 *  }
		 *  @param string $action The name of the hook which triggered the engagement.
		 *  @param array  $args   The original arguments provided by the triggering hook.
		 */
		$filtered_parsed = apply_filters(
			'lifterlms_external_engagement_query_arguments',
			$parsed,
			$action,
			$args
		);
		// If valid, return the filtered parsed data.
		if ( isset( $filtered_parsed['trigger_type'] ) && isset( $filtered_parsed['user_id'] ) ) {
			return $filtered_parsed;
		}

		// Verify that the action is a supported hook.
		if ( ! in_array( $action, $this->get_trigger_hooks(), true ) ) {
			return $parsed;
		}

		// The user registration action doesn't have a related post id.
		$related_post_id = isset( $args[1] ) && is_numeric( $args[1] ) ? absint( $args[1] ) : '';

		$parsed['user_id']         = absint( $args[0] );
		$parsed['trigger_type']    = $this->parse_hook_find_trigger_type( $action, $related_post_id );
		$parsed['related_post_id'] = $related_post_id;

		return $parsed;
	}

	/**
	 * Get the engagement trigger type based on the action and related post id
	 *
	 * @since 6.0.0
	 *
	 * @param string     $action          Name of the triggering action hook.
	 * @param int|string $related_post_id WP_Post ID of the related post or an empty string.
	 * @return string
	 */
	private function parse_hook_find_trigger_type( $action, $related_post_id ) {

		$trigger_type = '';

		switch ( $action ) {
			case 'llms_rest_student_registered':
			case 'lifterlms_created_person':
			case 'lifterlms_user_registered':
				$trigger_type = 'user_registration';
				break;

			case 'lifterlms_course_completed':
			case 'lifterlms_course_track_completed':
			case 'lifterlms_lesson_completed':
			case 'lifterlms_section_completed':
			case 'lifterlms_quiz_completed':
			case 'lifterlms_quiz_passed':
			case 'lifterlms_quiz_failed':
				$trigger_type = str_replace( 'lifterlms_', '', $action );
				break;

			case 'llms_user_added_to_membership_level':
			case 'llms_user_enrolled_in_course':
				$trigger_type = str_replace( 'llms_', '', get_post_type( $related_post_id ) ) . '_enrollment';
				break;

			case 'lifterlms_access_plan_purchased':
			case 'lifterlms_product_purchased':
				$trigger_type = str_replace( 'llms_', '', get_post_type( $related_post_id ) ) . '_purchased';
				break;
		}

		return $trigger_type;
	}

	/**
	 * Handles all actions that could potentially trigger an engagement
	 *
	 * It will fire or schedule the actions after gathering all necessary data.
	 *
	 * @since 2.3.0
	 * @since 3.11.0 Unknown.
	 * @since 3.39.0 Treat also `llms_rest_student_registered` action.
	 * @since 6.0.0 Major refactor to reduce code complexity.
	 *
	 * @return void
	 */
	public function maybe_trigger_engagement() {

		// Parse incoming hook data.
		$hook = $this->parse_hook( current_filter(), func_get_args() );

		// We need a user and a trigger to proceed, related_post is optional though.
		if ( ! $hook['user_id'] || ! $hook['trigger_type'] ) {
			return;
		}

		// Gather triggerable engagements matching the supplied criteria.
		$engagements = $this->get_engagements( $hook['trigger_type'], $hook['related_post_id'] );

		// Loop through the retrieved engagements and trigger them.
		foreach ( $engagements as $engagement ) {

			$handler = $this->parse_engagement( $engagement, $hook );
			$this->trigger_engagement( $handler, $engagement->delay );

		}
	}

	/**
	 * Parse engagement objects from the DB and return data needed to trigger the engagements.
	 *
	 * @since 6.0.0
	 * @since 6.6.0 Fixed an issue where the `lifterlms_external_engagement_handler_arguments` filter
	 *              would not trigger if a 3rd party registered an engagement type.
	 *
	 * @param object $engagement   The engagement object from the `get_engagements()` query.
	 * @param array  $trigger_data Parsed hook data from `parse_hook()`.
	 * @return array {
	 *     An associative array of parsed data used to trigger the engagement.
	 *
	 *     @type string $handler_action Hook name of the action that will handle awarding the sending the engagement.
	 *     @type array  $handler_args   Arguments passed to the `$handler_action` callback.
	 *  }
	 */
	private function parse_engagement( $engagement, $trigger_data ) {

		$parsed = array(
			'handler_action' => null,
			'handler_args'   => null,
		);

		/**
		 * Enable 3rd parties to parse custom engagement types.
		 *
		 * @since 2.3.0
		 *
		 * @param array $parsed {
		 *     An associative array of parsed data used to trigger the engagement.
		 *
		 *     @type string $handler_action (Required) Hook name of the action that will handle awarding the sending the engagement.
		 *     @type array  $handler_args   (Required) Arguments passed to the `$handler_action` callback.
		 * }
		 * @param object $engagement      The engagement object from the `get_engagements()` query.
		 * @param int    $user_id         WP_User ID who will be awarded the engagement.
		 * @param int    $related_post_id WP_Post ID of the related post.
		 * @param string $event_type      The type of engagement event.
		 */
		$filtered_parsed = apply_filters(
			'lifterlms_external_engagement_handler_arguments',
			$parsed,
			$engagement,
			$trigger_data['user_id'],
			$trigger_data['related_post_id'],
			$engagement->event_type
		);
		// If valid, return the filtered parsed data.
		if ( isset( $filtered_parsed['handler_action'] ) && isset( $filtered_parsed['handler_args'] ) ) {
			return $filtered_parsed;
		}

		// Verify that the engagement event type is supported.
		if ( ! array_key_exists( $engagement->event_type, llms_get_engagement_types() ) ) {
			return $parsed;
		}

		$parsed['handler_args'] = array(
			$trigger_data['user_id'],
			$engagement->engagement_id,
			$trigger_data['related_post_id'],
			absint( $engagement->trigger_id ),
		);

		/**
		 * @todo Fix this
		 *
		 * If there's no related post id we have to send one anyway for certs to work.
		 *
		 * This would only be for registration events @ version 2.3.0 so we pass the engagement_id twice until we find a better solution.
		 */
		if ( 'certificate' === $engagement->event_type && empty( $parsed['handler_args'][2] ) ) {
			$parsed['handler_args'][2] = $parsed['handler_args'][1];
		}

		$parsed['handler_action'] = sprintf(
			'lifterlms_engagement_%1$s_%2$s',
			'email' === $engagement->event_type ? 'send' : 'award',
			$engagement->event_type
		);

		return $parsed;
	}

	/**
	 * Triggers or schedules an engagement
	 *
	 * @since 6.0.0
	 *
	 * @param array $data  Handler data from `parse_engagement()`.
	 * @param int   $delay The engagement send delay (in days).
	 * @return void
	 */
	private function trigger_engagement( $data, $delay ) {

		// Can't proceed without an action and a handler.
		if ( empty( $data['handler_action'] ) || empty( $data['handler_args'] ) ) {
			return;
		}

		// If we have a delay, schedule the engagement handler.
		$delay = absint( $delay );
		if ( $delay ) {

			as_schedule_single_action(
				current_datetime()->modify( "+{$delay} days" )->getTimestamp(),
				$data['handler_action'],
				array( $data['handler_args'] ),
				! empty( $data['handler_args'][3] ) ? $this->get_delayed_group_id( $data['handler_args'][3] ) : null
			);

		} else {

			/**
			 * Skip processing checks for immediate engagements.
			 *
			 * We know the user exists (because they're currently logged in) and we don't have to run
			 * publish/existence checks on all the related posts because the `get_engagement()` query takes care
			 * of that already.
			 */
			add_filter( 'llms_skip_engagement_processing_checks', '__return_true' );

			do_action( $data['handler_action'], $data['handler_args'] );

			remove_filter( 'llms_skip_engagement_processing_checks', '__return_true' );

		}
	}

	/**
	 * Unschedule all scheduled actions for a delayed engagement
	 *
	 * This is the callback function for deleted engagement posts.
	 *
	 * The `deleted_post` action param `$post` has been added since WordPress 5.5.0.
	 *
	 * @since 6.0.0
	 *
	 * @param int          $post_id WP_Post ID.
	 * @param WP_Post|null $post    Post object of the deleted post.
	 * @return void
	 */
	public function unschedule_delayed_engagements( $post_id, $post = null ) {

		// @todo Remove compatibility with WP < 5.5 when bumping the minimum WP required version to 5.5+
		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		if ( 'llms_engagement' === $post_type ) {
			as_unschedule_all_actions( '', array(), $this->get_delayed_group_id( $post_id ) );
		}
	}

	/**
	 * Log debug data to the WordPress debug.log file
	 *
	 * @since 2.7.9
	 * @since 3.12.0 Unknown.
	 * @deprecated 6.0.0 Engagement debug logging is removed. Use `llms_log()` directly instead.
	 *
	 * @param mixed $log Data to write to the log.
	 * @return void
	 */
	public function log( $log ) {

		_deprecated_function( 'LLMS_Engagements::log()', '6.0.0', 'llms_log()' );

		if ( $this->debug ) {
			llms_log( $log, 'engagements' );
		}
	}
}
