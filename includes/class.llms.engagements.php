<?php
/**
 * Engagements Class
 * Finds and triggers the appropriate engagement
 *
 * @since 2.3.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Engagements Class
 *
 * @since 2.3.0
 * @since 3.30.3 Fixed spelling errors.
 */
class LLMS_Engagements {

	/**
	 * Enable debug logging
	 *
	 * @var  boolean
	 * @since  2.7.9
	 */
	private $debug = false;

	/**
	 * protected instance of class
	 *
	 * @var LLMS_Engagements
	 */
	protected static $_instance = null;

	/**
	 * Create instance of class
	 *
	 * @return LLMS_Engagements [Instance of engagements class]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 * Adds actions to events that trigger engagements
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
	 * @return  void
	 *
	 * @since    2.3.0
	 * @version  3.11.0
	 */
	private function add_actions() {

		$actions = apply_filters(
			'lifterlms_engagement_actions',
			array(

				'lifterlms_access_plan_purchased',
				'lifterlms_course_completed',
				'lifterlms_course_track_completed',
				'lifterlms_created_person',
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
	 * @return void
	 */
	public function init() {

		include 'class.llms.certificates.php';
		include 'class.llms.achievements.php';

	}


	/**
	 * Award an achievement
	 *
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class
	 *
	 * @param array $args  indexed array of args
	 *                     0 => WP User ID
	 *                     1 => WP Post ID of the email post
	 *                     2 => WP Post ID of the related post that triggered the award
	 *
	 * @return void
	 *
	 * @since 2.3.0
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
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class
	 *
	 * @param array $args  indexed array of args
	 *                     0 => WP User ID
	 *                     1 => WP Post ID of the email post
	 *                     2 => WP Post ID of the related post that triggered the award
	 *
	 * @return void
	 *
	 * @since 2.3.0
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
	 * This is called via do_action() by the 'maybe_trigger_engagement' function in this class
	 *
	 * @param array $args  indexed array of args
	 *                     0 => WP User ID
	 *                     1 => WP Post ID of the email post
	 *                     2 => WP Post ID of the triggering post
	 * @return void
	 *
	 * @since    2.3.0
	 * @version  3.8.0
	 */
	public function handle_email( $args ) {

		$this->log( '======== handle_email() =======' );
		$this->log( $args );

		$person_id  = $args[0];
		$email_id   = $args[1];
		$related_id = $args[2];

		// dupcheck
		global $wpdb;

		$res = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT count( meta_id )
			FROM {$wpdb->prefix}lifterlms_user_postmeta
			WHERE post_id = %d
			  AND user_id = %d
			  AND meta_value = %d
			  LIMIT 1
			;",
				array(
					$related_id,
					$person_id,
					$email_id,
				)
			)
		);

		if ( $res >= 1 ) {
			llms_log( sprintf( 'Email `#%d` was not sent because of dupcheck', $email_id ) );
			return;
		}

		// setup the email
		$email = LLMS()->mailer()->get_email(
			'engagement',
			array(
				'person_id'  => $args[0],
				'email_id'   => $args[1],
				'related_id' => $args[2],
			)
		);

		if ( $email ) {

			if ( $email->send() ) {

				$wpdb->insert(
					$wpdb->prefix . 'lifterlms_user_postmeta',
					array(
						'user_id'      => $person_id,
						'post_id'      => $related_id,
						'meta_key'     => '_email_sent',
						'meta_value'   => $email_id,
						'updated_date' => current_time( 'mysql' ),
					),
					array( '%d', '%d', '%s', '%d', '%s' )
				);
				llms_log( sprintf( 'Email `#%d` sent successfully', $email_id ) );

			} else {

				llms_log( sprintf( 'Error: email `#%d` was not sent', $email_id ) );
			}
		}

	}

	/**
	 * Log debug data to the WordPress debug.log file
	 *
	 * @param    mixed $log  data to write to the log
	 * @return   void
	 * @since    2.7.9
	 * @version  3.12.0
	 */
	public function log( $log ) {

		if ( $this->debug ) {

			llms_log( $log, 'engagements' );

		}

	}

	/**
	 * Handles all actions that could potentially trigger an engagement
	 *
	 * It will fire or schedule the actions after gathering all necessary data
	 *
	 * @return   void
	 *
	 * @since    2.3.0
	 * @version  3.11.0
	 */
	public function maybe_trigger_engagement() {

		$action = current_filter();
		$args   = func_get_args();

		$this->log( '======= start maybe_trigger_engagement ========' );
		$this->log( '$action: ' . $action );
		$this->log( '$args: ' . json_encode( $args ) );

		// setup variables used in queries and triggers based on the action
		switch ( $action ) {

			case 'lifterlms_created_person':
				$user_id         = intval( $args[0] );
				$trigger_type    = 'user_registration';
				$related_post_id = '';
				break;

			case 'lifterlms_course_completed':
			case 'lifterlms_course_track_completed':
			case 'lifterlms_lesson_completed':
			case 'lifterlms_section_completed':
				$user_id         = intval( $args[0] );
				$related_post_id = intval( $args[1] );
				$trigger_type    = str_replace( 'lifterlms_', '', $action );
				break;

			case 'lifterlms_quiz_completed':
			case 'lifterlms_quiz_passed':
			case 'lifterlms_quiz_failed':
				$user_id         = absint( $args[0] );
				$related_post_id = absint( $args[1] );
				$trigger_type    = str_replace( 'lifterlms_', '', $action );
				break;

			case 'llms_user_added_to_membership_level':
			case 'llms_user_enrolled_in_course':
				$user_id         = intval( $args[0] );
				$related_post_id = intval( $args[1] );
				$trigger_type    = str_replace( 'llms_', '', get_post_type( $related_post_id ) ) . '_enrollment';
				break;

			case 'lifterlms_access_plan_purchased':
			case 'lifterlms_product_purchased':
				$user_id         = intval( $args[0] );
				$related_post_id = intval( $args[1] );
				$trigger_type    = str_replace( 'llms_', '', get_post_type( $related_post_id ) ) . '_purchased';
				break;

			// allow extensions to hook into our engagements
			default:
				extract(
					apply_filters(
						'lifterlms_external_engagement_query_arguments',
						array(
							'related_post_id' => null,
							'trigger_type'    => null,
							'user_id'         => null,
						),
						$action,
						$args
					)
				);

		}// End switch().

		// we need a user and a trigger to proceed, related_post is optional though
		if ( ! $user_id || ! $trigger_type ) {
			return;
		}

		// gather triggerable engagements matching the supplied criteria
		$engagements = apply_filters( 'lifterlms_get_engagements', $this->get_engagements( $trigger_type, $related_post_id ), $trigger_type, $related_post_id );

		$this->log( '$engagements: ' . json_encode( $engagements ) );

		// only trigger engagements if there are engagements
		if ( $engagements ) {

			// loop through the engagements
			foreach ( $engagements as $e ) {

				$handler_action = null;
				$handler_args   = null;

				// do actions based on the event type
				switch ( $e->event_type ) {

					case 'achievement':
						$handler_action = 'lifterlms_engagement_award_achievement';
						$handler_args   = array( $user_id, $e->engagement_id, $related_post_id );

						break;

					case 'certificate':
						/**
						 * @todo  fix this
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

					// allow extensions to hook into our engagements
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

				}// End switch().

				// can't proceed without an action and a handler
				if ( ! $handler_action && ! $handler_args ) {
					continue;
				}

				// if we have a delay, schedule the engagement handler
				$delay = intval( $e->delay );
				$this->log( '$delay: ' . $delay );
				$this->log( '$handler_action: ' . $handler_action );
				$this->log( '$handler_args: ' . json_encode( $handler_args ) );
				if ( $delay ) {

					wp_schedule_single_event( time() + ( DAY_IN_SECONDS * $delay ), $handler_action, array( $handler_args ) );

				} else {

					do_action( $handler_action, $handler_args );

				}
			}// End foreach().
		}// End if().

		$this->log( '======= end maybe_trigger_engagement ========' );

	}




	/**
	 * Retrieve engagements based on the trigger type
	 *
	 * Joins rather than nested loops and sub queries ftw
	 *
	 * @param  string $trigger_type  name of the trigger to look for
	 * @return array of objects
	 *              Array(
	 *                  [0] => stdClass Object (
	 *                      [engagement_id] => 123, // WordPress Post ID of the event post (email, certificate, achievement, etc...)
	 *                      [trigger_id]    => 123, // this is the Post ID of the llms_engagement post
	 *                      [trigger_event] => 'user_registration', // triggering action
	 *                      [event_type]    => 'certificate', // engagement event action
	 *                      [delay]         => 0, // time in days to delay the engagement
	 *                   )
	 *              )
	 *
	 * @since    2.3.0
	 * @version  3.13.1
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

		$r = $wpdb->get_results(
			$wpdb->prepare(
				// the query
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
				// prepare variables
				$trigger_type
			),
			OBJECT
		);

		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$this->log( '$wpdb->last_query' . $wpdb->last_query );

		return $r;

	}

}
