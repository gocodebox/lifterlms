<?php
/**
 * Engagements Class
 *
 * Finds and triggers the appropriate engagement
 *
 * @package LifterLMS/Classes
 *
 * @since 2.3.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagements Class
 *
 * @since 2.3.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.39.0 Added `llms_rest_student_registered` as action hook.
 * @since [version] Deprecated protected variable `LLMS_Engagements::$_instance` in favor of PSR2 compliant `$instance`.
 */
class LLMS_Engagements {

	/**
	 * Enable debug logging
	 *
	 * @var boolean
	 */
	private $debug = false;

	/**
	 * Class instance.
	 *
	 * @var LLMS_Engagements
	 */
	protected static $instance = null;

	/**
	 * Deprecated class instance.
	 *
	 * @deprecated [version] Use `$instance` instead.
	 *
	 * @var LLMS_Engagements
	 */
	protected static $_instance = null; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore -- Deprecated.

	/**
	 * Create instance of class
	 *
	 * @since 2.3.0
	 * @since [version] Use `self::$instance` in favor of deprecated `self::$_instance` and preserve the reference for backwards compatibility.
	 *
	 * @return LLMS_Engagements
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance  = new self();
			self::$_instance = self::$instance; // Preserve for backwards compatibility.
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * Adds actions to events that trigger engagements.
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	private function __construct() {

		if ( defined( 'LLMS_ENGAGEMENT_DEBUG' ) && LLMS_ENGAGEMENT_DEBUG ) {
			$this->debug = true;
		}

		$this->add_actions();
		$this->init();

	}

	/**
	 * Register all actions that trigger engagements
	 *
	 * @since 2.3.0
	 * @since 3.11.0 Unknown.
	 * @since 3.39.0 Added `llms_rest_student_registered` as action hook.
	 *
	 * @return void
	 */
	private function add_actions() {

		/**
		 * Filter the list of actions which might be used to trigger an engagement.
		 *
		 * @since 2.3.0
		 *
		 * @param string[] $actions An array of action hooks.
		 */
		$actions = apply_filters(
			'lifterlms_engagement_actions',
			array(
				'lifterlms_access_plan_purchased',
				'lifterlms_course_completed',
				'lifterlms_course_track_completed',
				'lifterlms_created_person',
				'llms_rest_student_registered',
				'lifterlms_lesson_completed',
				'lifterlms_product_purchased',
				'lifterlms_quiz_completed',
				'lifterlms_quiz_passed',
				'lifterlms_quiz_failed',
				'lifterlms_section_completed',
				'llms_user_enrolled_in_course',
				'llms_user_added_to_membership_level',
			)
		);

		foreach ( $actions as $action ) {
			add_action( $action, array( $this, 'maybe_trigger_engagement' ), 777, 3 );
		}

		add_action( 'lifterlms_engagement_send_email', array( $this, 'handle_email' ), 10, 1 );
		add_action( 'lifterlms_engagement_award_achievement', array( $this, 'handle_achievement' ), 10, 1 );
		add_action( 'lifterlms_engagement_award_certificate', array( $this, 'handle_certificate' ), 10, 1 );

	}


	/**
	 * Include engagement types (excluding email)
	 *
	 * @since 2.3.0
	 *
	 * @return void
	 */
	public function init() {

		include 'class.llms.certificates.php';
		include 'class.llms.achievements.php';

	}

	/**
	 * Award an achievement
	 *
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class.
	 *
	 * @since 2.3.0
	 *
	 * @param mixed[] $args {
	 *     An array of arguments from the triggering hook.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the email.
	 *     @type int|string $2 WP_Post ID of the related triggering post or an empty string for engagements with no related post.
	 * }
	 * @return void
	 */
	public function handle_achievement( $args ) {
		$this->log( '======== handle_achievement() =======' );
		$this->log( $args );
		$a = LLMS()->achievements();
		$a->trigger_engagement( $args[0], $args[1], $args[2] );
	}

	/**
	 * Award a certificate
	 *
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class.
	 *
	 * @since 2.3.0
	 *
	 * @param mixed[] $args {
	 *     An array of arguments from the triggering hook.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the email.
	 *     @type int|string $2 WP_Post ID of the related triggering post or an empty string for engagements with no related post.
	 * }
	 * @return void
	 */
	public function handle_certificate( $args ) {
		$this->log( '======== handle_certificate() =======' );
		$this->log( $args );
		$c = LLMS()->certificates();
		$c->trigger_engagement( $args[0], $args[1], $args[2] );
	}

	/**
	 * Send an email engagement
	 *
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class.
	 *
	 * @since 2.3.0
	 * @since 3.8.0 Unknown.
	 * @since [version] Use postmeta helpers for dupcheck and postmeta insertion.
	 *              Add a return value in favor of `void`.
	 *              Log successes and failures to the `engagement-emails` log file instead of the main `llms` log.
	 *
	 * @param mixed[] $args {
	 *     An array of arguments from the triggering hook.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the email.
	 *     @type int|string $2 WP_Post ID of the related triggering post or an empty string for engagements with no related post.
	 * }
	 * @return bool|WP_Error Returns `true` on success or a WP_Error when the email has failed or is prevented.
	 */
	public function handle_email( $args ) {

		$this->log( '======== handle_email() =======' );
		$this->log( $args );

		$person_id  = $args[0];
		$email_id   = $args[1];
		$related_id = $args[2];
		$meta_key   = '_email_sent';

		$msg = sprintf( __( 'Email #%1$d to user #%2$d triggered by %3$s', 'lifterlms' ), $person_id, $email_id, $related_id ? '#' . $related_id : 'N/A' );

		if ( $related_id ) {

			if ( in_array( get_post_type( $related_id ), llms_get_enrollable_status_check_post_types(), true ) && ! llms_is_user_enrolled( $person_id, $related_id ) ) {

				// User is no longer enrolled in the triggering post. We should skip the send.
				llms_log( $msg . ' ' . __( 'not sent due to user enrollment issues.', 'lifterlms' ), 'engagement-emails' );
				return new WP_Error( 'llms_engagement_email_not_sent_enrollment', $msg, $args );
			} elseif ( llms_get_user_postmeta( $person_id, $related_id, $meta_key ) ) {

				// User has already received this email, don't send it again.
				llms_log( $msg . ' ' . __( 'not sent due to user enrollment issues.', 'lifterlms' ), 'engagement-emails' );
				return new WP_Error( 'llms_engagement_email_not_sent_dupcheck', $msg, $args );
			}
		}

		// Setup the email.
		$email = LLMS()->mailer()->get_email( 'engagement', compact( 'person_id', 'email_id', 'related_id' ) );
		if ( $email && $email->send() ) {

			if ( $related_id ) {
				llms_update_user_postmeta( $person_id, $related_id, $meta_key, $email_id );
			}

			llms_log( $msg . ' ' . __( 'sent successfully.', 'lifterlms' ), 'engagement-emails' );
			return true;
		}

		// Error sending email.
		llms_log( $msg . ' ' . __( 'not sent due to email sending issues.', 'lifterlms' ), 'engagement-emails' );
		return new WP_Error( 'llms_engagement_email_not_sent_error', $msg, $args );

	}

	/**
	 * Log debug data to the WordPress debug.log file
	 *
	 * @since 2.7.9
	 * @since 3.12.0 Unknown.
	 *
	 * @param mixed $log Data to write to the log.
	 * @return void
	 */
	public function log( $log ) {

		if ( $this->debug ) {
			llms_log( $log, 'engagements' );
		}

	}

	/**
	 * Parse engagement data from a hook and it's arguments.
	 *
	 * @since [version]
	 *
	 * @param string  $hook Name of an action hook.
	 * @param mixed[] $args Arguments passed to the hook.
	 * @return mixed[] {
	 *     An associative array of structured data.
	 *
	 *     @type int|string $related_post_id WP_Post ID of the related (triggering) post or an empty string when no related post exists.
	 *     @type string     $trigger_type    The name of the engagement type.
	 *     @type int        $user_id         WP_User ID of the user triggering the engagement.
	 * }
	 */
	protected function get_engagement_query_args( $hook, $hook_args = array() ) {

		$args = array(
			'related_post_id' => isset( $hook_args[1] ) && is_numeric( $hook_args[1] ) ? absint( $hook_args[1] ) : '',
			'trigger_type'    => '',
			'user_id'         => isset( $hook_args[0] ) ? absint( $hook_args[0] ) : 0,
		);

		switch ( $hook ) {

			/**
			 * Possible values for trigger_type:
			 *
			 * + user_registration
			 */
			case 'llms_rest_student_registered': // @todo: Ideally we should move this to the REST repo and use the `lifterlms_external_engagement_query_arguments` filter to register the hook.
			case 'lifterlms_created_person':
				$args['trigger_type'] = 'user_registration';
				break;
			/**
			 * Possible values for trigger_type:
			 *
			 * + course_completed
			 * + course_track_completed
			 * + lesson_completed
			 * + quiz_completed
			 * + quiz_failed
			 * + quiz_passed
			 * + section_completed
			 */
			case 'lifterlms_course_completed':
			case 'lifterlms_course_track_completed':
			case 'lifterlms_lesson_completed':
			case 'lifterlms_quiz_completed':
			case 'lifterlms_quiz_failed':
			case 'lifterlms_quiz_passed':
			case 'lifterlms_section_completed':
				$args['trigger_type'] = str_replace( 'lifterlms_', '', $hook );
				break;

			/**
			 * Possible values for trigger_type:
			 *
			 * + course_enrollment
			 * + membership_enrollment
			 */
			case 'llms_user_added_to_membership_level':
			case 'llms_user_enrolled_in_course':
				$args['trigger_type'] = str_replace( 'llms_', '', get_post_type( $args['related_post_id'] ) ) . '_enrollment';
				break;

			/**
			 * Possible values for trigger_type:
			 *
			 * + access_plan_purchased
			 * + course_purchased
			 * + membership_purchased
			 */
			case 'lifterlms_access_plan_purchased':
			case 'lifterlms_product_purchased':
				$args['trigger_type'] = str_replace( 'llms_', '', get_post_type( $args['related_post_id'] ) ) . '_purchased';
				break;

			default:
				/**
				 * Determine engagement data from a custom hook
				 *
				 * 3rd parties should use this hook to parse argruments passed to a custom hook and prepare
				 * the data used by the LifterLMS core to perform an engagement query used to lookup
				 * engagements that should be triggered or scheduled when the hook is called.
				 *
				 * @since 2.3.0
				 *
				 * @param mixed[] $args      {
				 *     An associative array of structured data.
				 *
				 *     @type int|string $related_post_id WP_Post ID of the related (triggering) post or an empty string when no related post exists.
				 *     @type string     $trigger_type    The name of the engagement type.
				 *     @type int        $user_id         WP_User ID of the user triggering the engagement.
				 * @param string  $hook      Name of an action hook.
				 * @param mixed[] $hook_args Arguments passed to the hook.
				 * }
				 */
				$args = apply_filters(
					'lifterlms_external_engagement_query_arguments',
					array(
						'related_post_id' => null,
						'trigger_type'    => null,
						'user_id'         => null,
					),
					$hook,
					$hook_args
				);

		}

		/**
		 * Filter engagement query data before it's used to lookup engagements.
		 *
		 * This data is used to perform a query to lookup engagements that should be
		 * triggered or scheduled when a qualifying hook is called.
		 *
		 * @since [version]
		 *
		 * @param mixed[] $args      {
		 *     An associative array of structured data.
		 *
		 *     @type int|string $related_post_id WP_Post ID of the related (triggering) post or an empty string when no related post exists.
		 *     @type string     $trigger_type    The name of the engagement type.
		 *     @type int        $user_id         WP_User ID of the user triggering the engagement.
		 * }
		 * @param string  $hook      Name of an action hook.
		 * @param mixed[] $hook_args Arguments passed to the hook.
		 */
		$args = apply_filters( 'llms_engagement_query_arguments', $args, $hook, $hook_args );

		return $args;

	}

	/**
	 * Handles all actions that could potentially trigger an engagement
	 *
	 * It will fire or schedule the actions after gathering all necessary data.
	 *
	 * @since 2.3.0
	 * @since 3.11.0 Unknown.
	 * @since 3.39.0 Treat also `llms_rest_student_registered` action.
	 * @since [version] Refactored for testability.
	 *
	 * @return void
	 */
	public function maybe_trigger_engagement() {

		$hook = current_filter();
		$args = func_get_args();

		$this->log( '======= start maybe_trigger_engagement ========' );
		$this->log( '$hook: ' . $hook );
		$this->log( '$args: ' . wp_json_encode( $args ) );

		$query_args = $this->get_engagement_query_args( $hook, $args );

		// We need a user and a trigger to proceed, related_post is optional though.
		if ( empty( $query_args['user_id'] ) || empty( $query_args['trigger_type'] ) ) {
			return false;
		}

		$user_id         = $query_args['user_id'];
		$related_post_id = isset( $query_args['related_post_id'] ) ? $query_args['related_post_id'] : '';
		$trigger_type    = $query_args['trigger_type'];

		// Gather triggerable engagements matching the supplied criteria.
		$engagements = $this->get_engagements( $trigger_type, $related_post_id );

		$this->log( '$engagements: ' . wp_json_encode( $engagements ) );

		// Only trigger engagements if there are engagements.
		if ( $engagements ) {

			// Loop through the engagements.
			foreach ( $engagements as $e ) {

				$handler_action = null;
				$handler_args   = null;

				// Do actions based on the event type.
				switch ( $e->event_type ) {

					case 'achievement':
						$handler_action = 'lifterlms_engagement_award_achievement';
						$handler_args   = array( $user_id, $e->engagement_id, $related_post_id );

						break;

					case 'certificate':
						/**
						 * @todo Fix this
						 * if there's no related post id we have to send one anyway for certs to work
						 * this would only be for registration events @ version 2.3.0
						 * we'll just send the engagement_id twice until we find a better solution
						 */
						$related_post_id = ( ! $related_post_id ) ? $e->engagement_id : $related_post_id;

						$handler_action = 'lifterlms_engagement_award_certificate';
						$handler_args   = array( $user_id, $e->engagement_id, $related_post_id );

						break;

					case 'email':
						$handler_action = 'lifterlms_engagement_send_email';
						$handler_args   = array( $user_id, $e->engagement_id, $related_post_id );

						break;

					// Allow extensions to hook into our engagements.
					default:
						extract(
							apply_filters(
								'lifterlms_external_engagement_handler_arguments',
								array(
									'handler_action' => $handler_action,
									'handler_args'   => $handler_args,
								),
								$e,
								$user_id,
								$related_post_id,
								$trigger_type
							)
						);

				}

				// Can't proceed without an action and a handler.
				if ( ! $handler_action && ! $handler_args ) {
					continue;
				}

				// If we have a delay, schedule the engagement handler.
				$delay = intval( $e->delay );
				$this->log( '$delay: ' . $delay );
				$this->log( '$handler_action: ' . $handler_action );
				$this->log( '$handler_args: ' . wp_json_encode( $handler_args ) );
				if ( $delay ) {

					wp_schedule_single_event( time() + ( DAY_IN_SECONDS * $delay ), $handler_action, array( $handler_args ) );

				} else {

					do_action( $handler_action, $handler_args );

				}
			}
		}

		$this->log( '======= end maybe_trigger_engagement ========' );

	}

	/**
	 * Retrieve engagements that should be triggered based on the trigger type and related post
	 *
	 * @since 2.3.0
	 * @since 3.13.1 Unknown.
	 *
	 * @param string     $trigger_type    Name of the trigger to look for.
	 * @param int|string $related_post_id WP_Post ID of the related (triggering) post. Can be an empty string if no related post.
	 * @return obj[] {
	 *     Array of objects representing the engagements matching the trigger type and related post.
	 *
	 *     @type int    $engagement_id WP_Post ID of the engagement post (an llms_email, llms_certificate, llms_achievement, etc...).
	 *     @type int    $trigger_id    WP_Post ID of the `llms_engagement` post used to create the engagement.
	 *     @type string $trigger_event The type of action which triggered the engagement. EG: "user_registration", "course_completed", etc...
	 *     @type string $event_type    The type of engagement. EG: "certificate", "achievement", "email", etc...
	 *     @type int    $delay         The time delay for the engagement (in days).
	 * }
	 */
	private function get_engagements( $trigger_type, $related_post_id = '' ) {

		global $wpdb;

		if ( $related_post_id ) {

			$related_select = ', relation_meta.meta_value AS related_post_id';
			$related_join   = "LEFT JOIN $wpdb->postmeta AS relation_meta ON triggers.ID = relation_meta.post_id";
			$related_where  = $wpdb->prepare( "AND relation_meta.meta_key = '_llms_engagement_trigger_post' AND relation_meta.meta_value = %d", $related_post_id );

		} else {

			$related_select = '';
			$related_join   = '';
			$related_where  = '';

		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$engagements = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				  DISTINCT triggers.ID        AS trigger_id
				, triggers_meta.meta_value    AS engagement_id
				, engagements_meta.meta_value AS trigger_event
				, event_meta.meta_value       AS event_type
				, delay.meta_value            AS delay
				$related_select

				FROM $wpdb->postmeta AS engagements_meta

				LEFT JOIN $wpdb->posts    AS triggers      ON triggers.ID    = engagements_meta.post_id
				LEFT JOIN $wpdb->postmeta AS triggers_meta ON triggers.ID    = triggers_meta.post_id
				LEFT JOIN $wpdb->posts    AS engagements   ON engagements.ID = triggers_meta.meta_value
				LEFT JOIN $wpdb->postmeta AS event_meta    ON triggers.ID    = event_meta.post_id
				LEFT JOIN $wpdb->postmeta AS delay         ON triggers.ID    = delay.post_id
				$related_join

				WHERE
					    triggers.post_type     = 'llms_engagement'
					AND triggers.post_status   = 'publish'
					AND triggers_meta.meta_key = '_llms_engagement'

					AND engagements_meta.meta_key   = '_llms_trigger_type'
					AND engagements_meta.meta_value = %s
					AND engagements.post_status     = 'publish'

					AND event_meta.meta_key = '_llms_engagement_type'

					AND delay.meta_key = '_llms_engagement_delay'

					$related_where
				",
				$trigger_type
			),
			OBJECT
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$this->log( '$wpdb->last_query' . $wpdb->last_query );

		/**
		 * Filter the list of engagements that should be triggered or scheduled.
		 *
		 * @since [version]
		 *
		 * @param obj[]      $engagements     {
		 *     Array of objects representing the engagements matching the trigger type and related post.
		 *
		 *     @type int    $engagement_id WP_Post ID of the engagement post (an llms_email, llms_certificate, llms_achievement, etc...).
		 *     @type int    $trigger_id    WP_Post ID of the `llms_engagement` post used to create the engagement.
		 *     @type string $trigger_event The type of action which triggered the engagement. EG: "user_registration", "course_completed", etc...
		 *     @type string $event_type    The type of engagement. EG: "certificate", "achievement", "email", etc...
		 *     @type int    $delay         The time delay for the engagement (in days).
		 * }
		 * @param string     $trigger_type    Name of the trigger to look for.
		 * @param int|string $related_post_id WP_Post ID of the related (triggering) post. Can be an empty string if no related post.
		 */
		return apply_filters( 'lifterlms_get_engagements', $engagements, $trigger_type, $related_post_id );

	}

}
