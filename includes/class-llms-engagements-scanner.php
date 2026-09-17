<?php
/**
 * LLMS_Engagements_Scanner class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locates and fires scan-based (inactivity) engagement triggers.
 *
 * A single daily recurring Action Scheduler action fans out into per-engagement
 * batch actions, each of which processes one page of candidate students. No
 * per-student actions are ever scheduled: work is always chunked into pages
 * so large sites cannot be overloaded by inactivity checks.
 *
 * Add-ons can register additional scan-based triggers via the
 * `llms_scannable_engagement_triggers` filter.
 *
 * @since [version]
 */
class LLMS_Engagements_Scanner {

	/**
	 * Hook name of the daily recurring scan action.
	 *
	 * @var string
	 */
	const SCAN_HOOK = 'llms_engagements_scan';

	/**
	 * Hook name of the per-engagement batch action.
	 *
	 * @var string
	 */
	const BATCH_HOOK = 'llms_engagements_scan_batch';

	/**
	 * Action Scheduler group used by scan and batch actions.
	 *
	 * @var string
	 */
	const AS_GROUP = 'llms_engagements_scan';

	/**
	 * User postmeta key used to record the fired (re-arm) markers.
	 *
	 * Stored with `post_id` set to the `llms_engagement` post ID so markers work
	 * for site-wide triggers with no related post, join cheaply against candidate
	 * queries, and can never be miscounted as course activity.
	 *
	 * The value is an array mapping related post IDs (`0` when there is no related
	 * post) to the anchor recorded at fire time, so a single "any course" engagement
	 * tracks each of a student's courses independently.
	 *
	 * @var string
	 */
	const MARKER_KEY = '_llms_engagement_fired';

	/**
	 * Per-request memo of course trees keyed by course ID.
	 *
	 * Action Scheduler processes many batch actions in a single request and a course's
	 * enrollments are not contiguous when paging by `meta_id`, so the same course tree
	 * is requested repeatedly within one queue run.
	 *
	 * @var array
	 */
	protected $tree_cache = array();

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'schedule_scan' ) );
		add_action( self::SCAN_HOOK, array( $this, 'do_scan' ) );
		add_action( self::BATCH_HOOK, array( $this, 'do_batch' ), 10, 2 );
		add_filter( 'llms_engagement_email_dupcheck', array( $this, 'maybe_bypass_email_dupcheck' ), 10, 5 );
		add_action( 'deleted_post', array( $this, 'delete_markers' ), 20, 2 );
	}

	/**
	 * Retrieve the list of scannable engagement trigger types and their candidate-query callbacks.
	 *
	 * Each callback receives `( WP_Post $engagement, int $cursor, int $per_page )` and must return
	 * an associative array:
	 *
	 *     array(
	 *         'candidates' => array(
	 *             array(
	 *                 'user_id'         => 123,          // WP_User ID of the candidate.
	 *                 'related_post_id' => 456,          // WP_Post ID of the related post or an empty string.
	 *                 'anchor'          => '2026-01-01 00:00:00', // Re-arm anchor: last-activity datetime or a source row ID.
	 *             ),
	 *         ),
	 *         'cursor' => 500, // Cursor for the next page or `null` when the scan of this engagement is complete.
	 *     )
	 *
	 * A candidate only fires when its anchor is newer than the previously recorded marker,
	 * implementing "fire once, re-arm on new activity" semantics.
	 *
	 * @since [version]
	 *
	 * @return array Associative array mapping trigger type slugs to callables.
	 */
	public function get_scannable_triggers() {

		$triggers = array(
			'days_since_login'           => array( $this, 'query_days_since_login' ),
			'course_inactivity'          => array( $this, 'query_course_inactivity' ),
			'course_never_started'       => array( $this, 'query_course_never_started' ),
			'course_completion_deadline' => array( $this, 'query_course_completion_deadline' ),
			'quiz_attempt_abandoned'     => array( $this, 'query_quiz_attempt_abandoned' ),
		);

		/**
		 * Filters the list of scan-based engagement triggers.
		 *
		 * Allows add-ons to register their own scan-based triggers which are located
		 * by the daily batched engagement scan without requiring their own cron.
		 *
		 * @since [version]
		 *
		 * @param array $triggers Associative array mapping trigger type slugs to candidate-query callables.
		 *                        See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the callback signature.
		 */
		return apply_filters( 'llms_scannable_engagement_triggers', $triggers );
	}

	/**
	 * Schedule the daily recurring scan action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function schedule_scan() {

		if ( ! function_exists( 'as_next_scheduled_action' ) ) {
			return;
		}

		if ( false === as_next_scheduled_action( self::SCAN_HOOK ) ) {
			as_schedule_recurring_action(
				time() + HOUR_IN_SECONDS,
				DAY_IN_SECONDS,
				self::SCAN_HOOK,
				array(),
				self::AS_GROUP
			);
		}
	}

	/**
	 * Fan the daily scan out into per-engagement batch actions.
	 *
	 * Bails immediately (a single indexed query) when no published engagements
	 * use a scannable trigger type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function do_scan() {

		global $wpdb;

		$types = array_keys( $this->get_scannable_triggers() );
		if ( ! $types ) {
			return;
		}

		$engagement_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT posts.ID
				 FROM {$wpdb->posts} AS posts
				 JOIN {$wpdb->postmeta} AS meta ON meta.post_id = posts.ID AND meta.meta_key = '_llms_trigger_type'
				 WHERE posts.post_type = 'llms_engagement'
				   AND posts.post_status = 'publish'
				   AND meta.meta_value IN ( " . implode( ',', array_fill( 0, count( $types ), '%s' ) ) . ' )',
				$types
			)
		); // db call ok; no-cache ok.

		foreach ( array_map( 'absint', $engagement_ids ) as $engagement_id ) {
			as_enqueue_async_action( self::BATCH_HOOK, array( $engagement_id, 0 ), self::AS_GROUP );
		}
	}

	/**
	 * Process a single page of candidates for a scan-based engagement.
	 *
	 * Schedules the next page as a new async action when the candidate query
	 * reports more results, keeping every batch short.
	 *
	 * @since [version]
	 *
	 * @param int $engagement_id WP_Post ID of the `llms_engagement` post.
	 * @param int $cursor        Keyset pagination cursor (last processed key) or `0` for the first page.
	 * @return void
	 */
	public function do_batch( $engagement_id, $cursor = 0 ) {

		$post = get_post( $engagement_id );
		if ( ! $post || 'llms_engagement' !== $post->post_type || 'publish' !== $post->post_status ) {
			return;
		}

		// The attached template must be published, mirroring the `get_engagements()` query.
		$template_id = get_post_meta( $engagement_id, '_llms_engagement', true );
		if ( ! $template_id || 'publish' !== get_post_status( $template_id ) ) {
			return;
		}

		$trigger_type = get_post_meta( $engagement_id, '_llms_trigger_type', true );
		$callbacks    = $this->get_scannable_triggers();
		$callback     = $callbacks[ $trigger_type ] ?? null;
		if ( ! is_callable( $callback ) ) {
			return;
		}

		/**
		 * Filters the number of candidates processed per engagement scan batch.
		 *
		 * @since [version]
		 *
		 * @param int    $per_page      Batch size. Default 200.
		 * @param int    $engagement_id WP_Post ID of the `llms_engagement` post.
		 * @param string $trigger_type  The engagement trigger type slug.
		 */
		$per_page = apply_filters( 'llms_engagements_scan_batch_size', 200, $engagement_id, $trigger_type );

		$result = call_user_func( $callback, $post, absint( $cursor ), $per_page );
		if ( ! is_array( $result ) ) {
			return;
		}

		$engagement = (object) array(
			'trigger_id'    => $engagement_id,
			'engagement_id' => $template_id,
			'trigger_event' => $trigger_type,
			'event_type'    => get_post_meta( $engagement_id, '_llms_engagement_type', true ),
			'delay'         => get_post_meta( $engagement_id, '_llms_engagement_delay', true ),
		);

		foreach ( $result['candidates'] ?? array() as $candidate ) {
			$this->maybe_fire( $engagement, $candidate );
		}

		if ( isset( $result['cursor'] ) && null !== $result['cursor'] ) {
			as_enqueue_async_action( self::BATCH_HOOK, array( $engagement_id, absint( $result['cursor'] ) ), self::AS_GROUP );
		}
	}

	/**
	 * Fire an engagement for a candidate unless its re-arm marker prevents it.
	 *
	 * A marker records the candidate's anchor (last-activity date or source row ID)
	 * at fire time, keyed by the candidate's related post so "any course" engagements
	 * track each course independently. The engagement only fires again when the current
	 * anchor is newer than the recorded one, i.e. the student became active again and
	 * then went idle again.
	 *
	 * @since [version]
	 *
	 * @param object $engagement An engagement object, see {@see LLMS_Engagements::get_engagements()} for the object shape.
	 * @param array  $candidate  {
	 *     Candidate data returned by a scannable trigger query callback.
	 *
	 *     @type int        $user_id         WP_User ID.
	 *     @type int|string $related_post_id WP_Post ID of the related post or an empty string.
	 *     @type string|int $anchor          Re-arm anchor value.
	 * }
	 * @return boolean `true` when the engagement was fired, `false` when skipped.
	 */
	public function maybe_fire( $engagement, $candidate ) {

		$user_id = absint( $candidate['user_id'] ?? 0 );
		$anchor  = $candidate['anchor'] ?? '';
		$related = absint( $candidate['related_post_id'] ?? 0 );

		if ( ! $user_id || '' === $anchor ) {
			return false;
		}

		$markers = llms_get_user_postmeta( $user_id, $engagement->trigger_id, self::MARKER_KEY, true );
		$markers = is_array( $markers ) ? $markers : array();
		$marker  = $markers[ $related ] ?? '';

		if ( ! empty( $marker ) && $anchor <= $marker ) {
			return false;
		}

		$markers[ $related ] = $anchor;
		llms_update_user_postmeta( $user_id, $engagement->trigger_id, self::MARKER_KEY, $markers, true );

		llms()->engagements()->trigger( $engagement, $user_id, $candidate['related_post_id'] ?? '' );

		return true;
	}

	/**
	 * Allow re-armed scan-based engagement emails to bypass the sent-email dupcheck.
	 *
	 * The re-arm marker in `maybe_fire()` is the dupcheck for scan-based triggers:
	 * when it allows a repeat fire the email should send again even though a
	 * previous send was recorded for the same email/related post combination.
	 *
	 * Each bypass is logged to the `engagement-emails` log, including the re-arm
	 * anchor recorded by `maybe_fire()`, so repeat sends can be traced back to the
	 * activity which re-armed the engagement.
	 *
	 * @since [version]
	 *
	 * @param boolean    $is_duplicate  Whether the email is considered a duplicate.
	 * @param int        $person_id     WP_User ID of the recipient.
	 * @param int        $email_id      WP_Post ID of the `llms_email` template.
	 * @param int|string $related_id    WP_Post ID of the related post or an empty string.
	 * @param int|null   $engagement_id WP_Post ID of the `llms_engagement` post, if known.
	 * @return boolean
	 */
	public function maybe_bypass_email_dupcheck( $is_duplicate, $person_id, $email_id, $related_id, $engagement_id ) {

		if ( ! $is_duplicate || ! $engagement_id ) {
			return $is_duplicate;
		}

		$trigger_type = get_post_meta( $engagement_id, '_llms_trigger_type', true );
		if ( ! $trigger_type || ! array_key_exists( $trigger_type, $this->get_scannable_triggers() ) ) {
			return $is_duplicate;
		}

		$markers = llms_get_user_postmeta( $person_id, $engagement_id, self::MARKER_KEY, true );
		$anchor  = is_array( $markers ) ? ( $markers[ absint( $related_id ) ] ?? '' ) : '';

		llms_log(
			sprintf(
				// Translators: %1$d = email template post ID; %2$d = user ID; %3$s = related post ID or "N/A"; %4$s = trigger type slug; %5$d = engagement post ID; %6$s = re-arm anchor value or "unknown".
				__( 'Email #%1$d to user #%2$d triggered by %3$s: dupcheck bypassed for re-armed "%4$s" engagement #%5$d (re-arm anchor: %6$s).', 'lifterlms' ),
				$email_id,
				$person_id,
				$related_id ? '#' . $related_id : 'N/A',
				$trigger_type,
				$engagement_id,
				'' === $anchor ? 'unknown' : $anchor
			),
			'engagement-emails'
		);

		return false;
	}

	/**
	 * Delete re-arm markers when an engagement post is deleted.
	 *
	 * @since [version]
	 *
	 * @param int          $post_id WP_Post ID.
	 * @param WP_Post|null $post    Post object of the deleted post.
	 * @return void
	 */
	public function delete_markers( $post_id, $post = null ) {

		$post_type = $post ? $post->post_type : get_post_type( $post_id );
		if ( 'llms_engagement' !== $post_type ) {
			return;
		}

		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'post_id'  => $post_id,
				'meta_key' => self::MARKER_KEY,
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Retrieve the configured inactivity period (in days) for an engagement.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @return int
	 */
	protected function get_period( $engagement ) {
		return absint( get_post_meta( $engagement->ID, '_llms_engagement_trigger_period', true ) );
	}

	/**
	 * Retrieve the datetime string for "N days before now" in the site's timezone.
	 *
	 * User postmeta `updated_date`, quiz attempt dates, and `llms_last_login` are all
	 * recorded via `llms_current_time()`, so cutoffs must use the same clock.
	 *
	 * @since [version]
	 *
	 * @param int $days Number of days.
	 * @return string MySQL datetime string.
	 */
	protected function get_cutoff( $days ) {
		return gmdate( 'Y-m-d H:i:s', llms_current_time( 'timestamp' ) - ( $days * DAY_IN_SECONDS ) );
	}

	/**
	 * Retrieve one keyset-paginated page of user IDs currently enrolled in a course or membership.
	 *
	 * Uses the same latest-`_status`-row pattern as {@see LLMS_Student::get_enrollments()}.
	 * Joins against the users table because deleting a WP user does not remove their
	 * LifterLMS user postmeta rows, and deleted users must never become candidates.
	 *
	 * @since [version]
	 *
	 * @param int $post_id  WP_Post ID of the course or membership.
	 * @param int $cursor   Last processed WP_User ID.
	 * @param int $per_page Number of users per page.
	 * @return int[]
	 */
	protected function get_enrolled_user_ids( $post_id, $cursor, $per_page ) {

		global $wpdb;

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT upm.user_id
				 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
				 JOIN {$wpdb->users} AS users ON users.ID = upm.user_id
				 WHERE upm.post_id = %d
				   AND upm.meta_key = '_status'
				   AND upm.user_id > %d
				   AND upm.meta_value = 'enrolled'
				   AND upm.updated_date = (
				       SELECT MAX( upm2.updated_date )
				       FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm2
				       WHERE upm2.user_id = upm.user_id
				         AND upm2.post_id = upm.post_id
				         AND upm2.meta_key = '_status'
				   )
				 ORDER BY upm.user_id ASC
				 LIMIT %d",
				$post_id,
				$cursor,
				$per_page
			)
		); // db call ok; no-cache ok.

		return array_map( 'absint', $ids );
	}

	/**
	 * Retrieve one keyset-paginated page of current course enrollments as (user, course) pairs.
	 *
	 * Pages over the latest `_status` row of each enrollment using the row's `meta_id`
	 * as the cursor, which allows a single query to cover either one course or every
	 * course on the site ("any course" engagements). Only posts of the `course` post
	 * type are considered, so membership rows in the user postmeta table are ignored.
	 * Joins against the users table because deleting a WP user does not remove their
	 * LifterLMS user postmeta rows, and deleted users must never become candidates.
	 *
	 * @since [version]
	 *
	 * @param int $course_id WP_Post ID of the course, or `0` for all courses.
	 * @param int $cursor    Last processed `meta_id` or `0` for the first page.
	 * @param int $per_page  Number of enrollment rows per page.
	 * @return object[] Array of objects with `meta_id`, `user_id`, and `post_id` properties.
	 */
	protected function get_enrollments( $course_id, $cursor, $per_page ) {

		global $wpdb;

		// When no specific course is configured `%d = 0` disables the course condition.
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT upm.meta_id, upm.user_id, upm.post_id
				 FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
				 JOIN {$wpdb->posts} AS posts ON posts.ID = upm.post_id AND posts.post_type = 'course'
				 JOIN {$wpdb->users} AS users ON users.ID = upm.user_id
				 WHERE upm.meta_key = '_status'
				   AND upm.meta_value = 'enrolled'
				   AND upm.meta_id > %d
				   AND ( %d = 0 OR upm.post_id = %d )
				   AND upm.updated_date = (
				       SELECT MAX( upm2.updated_date )
				       FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm2
				       WHERE upm2.user_id = upm.user_id
				         AND upm2.post_id = upm.post_id
				         AND upm2.meta_key = '_status'
				   )
				 ORDER BY upm.meta_id ASC
				 LIMIT %d",
				$cursor,
				$course_id,
				$course_id,
				$per_page
			)
		); // db call ok; no-cache ok.
	}

	/**
	 * Retrieve the list of WP_Post IDs comprising a course tree (course, sections, lessons, quizzes).
	 *
	 * @since [version]
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return int[]
	 */
	protected function get_course_tree( $course_id ) {

		if ( isset( $this->tree_cache[ $course_id ] ) ) {
			return $this->tree_cache[ $course_id ];
		}

		$course = llms_get_post( $course_id );
		if ( ! $course instanceof LLMS_Course ) {
			$this->tree_cache[ $course_id ] = array();
			return array();
		}

		$this->tree_cache[ $course_id ] = array_map(
			'absint',
			array_merge(
				array( $course_id ),
				$course->get_sections( 'ids' ),
				$course->get_lessons( 'ids' ),
				$course->get_quizzes()
			)
		);

		return $this->tree_cache[ $course_id ];
	}

	/**
	 * Compute the last activity date for a set of users within a course.
	 *
	 * Last activity is the greatest of: the latest `updated_date` across the user's
	 * user postmeta rows for the course tree, the latest quiz attempt `update_date`
	 * for quizzes in the course, and the user's enrollment date. Quiz attempts are
	 * included so a student actively attempting (but failing) quizzes is not
	 * mistaken for an inactive student.
	 *
	 * @since [version]
	 *
	 * @param int[] $user_ids  List of WP_User IDs.
	 * @param int   $course_id WP_Post ID of the course.
	 * @param int[] $tree      Course tree post IDs, see {@see LLMS_Engagements_Scanner::get_course_tree()}.
	 * @return array Associative array mapping user IDs to MySQL datetime strings.
	 */
	protected function get_last_activity( $user_ids, $course_id, $tree ) {

		global $wpdb;

		$user_ids = array_map( 'absint', $user_ids );
		$tree     = array_map( 'absint', $tree );

		$activity = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, MAX( updated_date ) AS last_activity
				 FROM {$wpdb->prefix}lifterlms_user_postmeta
				 WHERE user_id IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . ' )
				   AND post_id IN ( ' . implode( ',', array_fill( 0, count( $tree ), '%d' ) ) . ' )
				 GROUP BY user_id',
				array_merge( $user_ids, $tree )
			),
			OBJECT_K
		); // db call ok; no-cache ok.

		$quiz_activity = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT attempts.student_id AS user_id, MAX( attempts.update_date ) AS last_activity
				 FROM {$wpdb->prefix}lifterlms_quiz_attempts AS attempts
				 WHERE attempts.student_id IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . ' )
				   AND attempts.quiz_id IN ( ' . implode( ',', array_fill( 0, count( $tree ), '%d' ) ) . ' )
				 GROUP BY attempts.student_id',
				array_merge( $user_ids, $tree )
			),
			OBJECT_K
		); // db call ok; no-cache ok.

		$enrollments = $this->get_enrollment_dates( $user_ids, $course_id );

		$last = array();
		foreach ( $user_ids as $user_id ) {
			$dates = array_filter(
				array(
					$activity[ $user_id ]->last_activity ?? '',
					$quiz_activity[ $user_id ]->last_activity ?? '',
					$enrollments[ $user_id ] ?? '',
				)
			);
			if ( $dates ) {
				$last[ $user_id ] = max( $dates );
			}
		}

		/**
		 * Filters the computed last-activity dates used by scan-based engagement triggers.
		 *
		 * @since [version]
		 *
		 * @param array $last      Associative array mapping WP_User IDs to MySQL datetime strings.
		 * @param int[] $user_ids  The list of user IDs being scanned.
		 * @param int   $course_id WP_Post ID of the course.
		 */
		return apply_filters( 'llms_engagements_scan_last_activity', $last, $user_ids, $course_id );
	}

	/**
	 * Retrieve enrollment (start) dates for a set of users in a course or membership.
	 *
	 * @since [version]
	 *
	 * @param int[] $user_ids List of WP_User IDs.
	 * @param int   $post_id  WP_Post ID of the course or membership.
	 * @return array Associative array mapping user IDs to MySQL datetime strings.
	 */
	protected function get_enrollment_dates( $user_ids, $post_id ) {

		global $wpdb;

		$user_ids = array_map( 'absint', $user_ids );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, MAX( updated_date ) AS start_date
				 FROM {$wpdb->prefix}lifterlms_user_postmeta
				 WHERE user_id IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . " )
				   AND post_id = %d AND meta_key = '_start_date'
				 GROUP BY user_id",
				array_merge( $user_ids, array( $post_id ) )
			),
			OBJECT_K
		); // db call ok; no-cache ok.

		return array_map(
			function ( $row ) {
				return $row->start_date;
			},
			$results
		);
	}

	/**
	 * Retrieve the set of users (from a given list) who have started a course.
	 *
	 * A user has "started" when at least one `_is_complete` row exists for any
	 * post in the course tree.
	 *
	 * @since [version]
	 *
	 * @param int[] $user_ids List of WP_User IDs.
	 * @param int[] $tree     Course tree post IDs.
	 * @return int[] User IDs of users who have started.
	 */
	protected function get_started_user_ids( $user_ids, $tree ) {

		global $wpdb;

		$user_ids = array_map( 'absint', $user_ids );
		$tree     = array_map( 'absint', $tree );

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT user_id
				 FROM {$wpdb->prefix}lifterlms_user_postmeta
				 WHERE user_id IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . ' )
				   AND post_id IN ( ' . implode( ',', array_fill( 0, count( $tree ), '%d' ) ) . " )
				   AND meta_key = '_is_complete'
				   AND meta_value = 'yes'",
				array_merge( $user_ids, $tree )
			)
		); // db call ok; no-cache ok.

		return array_map( 'absint', $ids );
	}

	/**
	 * Retrieve the trigger post ID configured for an engagement, if it's a specific post.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @return int WP_Post ID or `0` when set to "any" or empty.
	 */
	protected function get_trigger_post_id( $engagement ) {
		$trigger_post = get_post_meta( $engagement->ID, '_llms_engagement_trigger_post', true );
		return is_numeric( $trigger_post ) ? absint( $trigger_post ) : 0;
	}

	/**
	 * Candidate query: students who haven't logged in for N days.
	 *
	 * When the engagement is scoped to a course or membership, only currently-enrolled
	 * students of that post are scanned. When unscoped ("any"), only users currently
	 * enrolled in at least one course or membership are scanned: accounts with no
	 * enrollments (staff, leads) are never candidates, matching the trigger's
	 * "student" labeling. Users who have never logged in fall back to their
	 * registration date.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed WP_User ID.
	 * @param int     $per_page   Batch size.
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	public function query_days_since_login( $engagement, $cursor, $per_page ) {

		global $wpdb;

		$done   = array(
			'candidates' => array(),
			'cursor'     => null,
		);
		$period = $this->get_period( $engagement );
		if ( ! $period ) {
			return $done;
		}

		$cutoff       = $this->get_cutoff( $period );
		$trigger_post = $this->get_trigger_post_id( $engagement );

		if ( $trigger_post ) {
			$user_ids = $this->get_enrolled_user_ids( $trigger_post, $cursor, $per_page );
		} else {
			// Require a current enrollment (latest `_status` row) in any course or membership.
			$user_ids = array_map(
				'absint',
				$wpdb->get_col(
					$wpdb->prepare(
						"SELECT u.ID
						 FROM {$wpdb->users} AS u
						 WHERE u.ID > %d
						   AND EXISTS (
						       SELECT 1
						       FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm
						       WHERE upm.user_id = u.ID
						         AND upm.meta_key = '_status'
						         AND upm.meta_value = 'enrolled'
						         AND upm.updated_date = (
						             SELECT MAX( upm2.updated_date )
						             FROM {$wpdb->prefix}lifterlms_user_postmeta AS upm2
						             WHERE upm2.user_id = upm.user_id
						               AND upm2.post_id = upm.post_id
						               AND upm2.meta_key = '_status'
						         )
						   )
						 ORDER BY u.ID ASC
						 LIMIT %d",
						$cursor,
						$per_page
					)
				)
			); // db call ok; no-cache ok.
		}

		if ( ! $user_ids ) {
			return $done;
		}

		$logins = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID AS user_id, COALESCE( um.meta_value, u.user_registered ) AS last_login
				 FROM {$wpdb->users} AS u
				 LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = u.ID AND um.meta_key = 'llms_last_login'
				 WHERE u.ID IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . ' )',
				$user_ids
			),
			OBJECT_K
		); // db call ok; no-cache ok.

		$candidates = array();
		foreach ( $user_ids as $user_id ) {
			$last_login = $logins[ $user_id ]->last_login ?? '';
			if ( $last_login && $last_login < $cutoff ) {
				$candidates[] = array(
					'user_id'         => $user_id,
					'related_post_id' => $trigger_post ? $trigger_post : '',
					'anchor'          => $last_login,
				);
			}
		}

		return array(
			'candidates' => $candidates,
			'cursor'     => count( $user_ids ) === $per_page ? end( $user_ids ) : null,
		);
	}

	/**
	 * Candidate query: enrolled students who started a course but have had no activity for N days.
	 *
	 * Mutually exclusive with `course_never_started` by construction, since only
	 * students with at least one completion row are considered.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed enrollment row `meta_id`.
	 * @param int     $per_page   Batch size.
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	public function query_course_inactivity( $engagement, $cursor, $per_page ) {
		return $this->query_course_activity( $engagement, $cursor, $per_page, true );
	}

	/**
	 * Candidate query: enrolled students who never started a course N days after enrolling.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed enrollment row `meta_id`.
	 * @param int     $per_page   Batch size.
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	public function query_course_never_started( $engagement, $cursor, $per_page ) {
		return $this->query_course_activity( $engagement, $cursor, $per_page, false );
	}

	/**
	 * Shared candidate query for `course_inactivity` and `course_never_started`.
	 *
	 * Pages over (user, course) enrollment pairs so "any course" engagements scan
	 * every enrollment independently: each pair produces its own candidate with the
	 * course as the related post, rather than one candidate per student. The batch
	 * is grouped by course so the course-tree and last-activity queries run once
	 * per course in the page.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed enrollment row `meta_id`.
	 * @param int     $per_page   Batch size.
	 * @param boolean $started    `true` to return started-but-stalled students (course_inactivity),
	 *                            `false` to return never-started students (course_never_started).
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	protected function query_course_activity( $engagement, $cursor, $per_page, $started ) {

		$done   = array(
			'candidates' => array(),
			'cursor'     => null,
		);
		$period = $this->get_period( $engagement );
		if ( ! $period ) {
			return $done;
		}

		$rows = $this->get_enrollments( $this->get_trigger_post_id( $engagement ), $cursor, $per_page );
		if ( ! $rows ) {
			return $done;
		}

		$cutoff     = $this->get_cutoff( $period );
		$candidates = array();

		foreach ( $this->group_enrollments_by_course( $rows ) as $course_id => $user_ids ) {

			$tree = $this->get_course_tree( $course_id );
			if ( ! $tree ) {
				continue;
			}

			$started_ids = $this->get_started_user_ids( $user_ids, $tree );

			if ( $started ) {

				$scan_ids = array_values( array_intersect( $user_ids, $started_ids ) );
				$activity = $scan_ids ? $this->get_last_activity( $scan_ids, $course_id, $tree ) : array();

				foreach ( $scan_ids as $user_id ) {
					$last = $activity[ $user_id ] ?? '';
					if ( $last && $last < $cutoff ) {
						$candidates[] = array(
							'user_id'         => $user_id,
							'related_post_id' => $course_id,
							'anchor'          => $last,
						);
					}
				}
			} else {

				$scan_ids    = array_values( array_diff( $user_ids, $started_ids ) );
				$enrollments = $scan_ids ? $this->get_enrollment_dates( $scan_ids, $course_id ) : array();

				foreach ( $scan_ids as $user_id ) {
					$enrolled = $enrollments[ $user_id ] ?? '';
					if ( $enrolled && $enrolled < $cutoff ) {
						$candidates[] = array(
							'user_id'         => $user_id,
							'related_post_id' => $course_id,
							'anchor'          => $enrolled,
						);
					}
				}
			}
		}

		$last_row = end( $rows );

		return array(
			'candidates' => $candidates,
			'cursor'     => count( $rows ) === $per_page ? absint( $last_row->meta_id ) : null,
		);
	}

	/**
	 * Group a page of enrollment rows into user ID lists keyed by course ID.
	 *
	 * @since [version]
	 *
	 * @param object[] $rows Enrollment rows, see {@see LLMS_Engagements_Scanner::get_enrollments()}.
	 * @return array Associative array mapping course IDs to lists of WP_User IDs.
	 */
	protected function group_enrollments_by_course( $rows ) {

		$grouped = array();
		foreach ( $rows as $row ) {
			$grouped[ absint( $row->post_id ) ][] = absint( $row->user_id );
		}

		return $grouped;
	}

	/**
	 * Candidate query: enrolled students who haven't completed a course N days after enrolling.
	 *
	 * Pages over (user, course) enrollment pairs so "any course" engagements check
	 * every enrollment independently.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed enrollment row `meta_id`.
	 * @param int     $per_page   Batch size.
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	public function query_course_completion_deadline( $engagement, $cursor, $per_page ) {

		global $wpdb;

		$done   = array(
			'candidates' => array(),
			'cursor'     => null,
		);
		$period = $this->get_period( $engagement );
		if ( ! $period ) {
			return $done;
		}

		$rows = $this->get_enrollments( $this->get_trigger_post_id( $engagement ), $cursor, $per_page );
		if ( ! $rows ) {
			return $done;
		}

		$cutoff     = $this->get_cutoff( $period );
		$candidates = array();

		foreach ( $this->group_enrollments_by_course( $rows ) as $course_id => $user_ids ) {

			$enrollments = $this->get_enrollment_dates( $user_ids, $course_id );

			$completed = array_map(
				'absint',
				$wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT user_id
						 FROM {$wpdb->prefix}lifterlms_user_postmeta
						 WHERE user_id IN ( " . implode( ',', array_fill( 0, count( $user_ids ), '%d' ) ) . " )
						   AND post_id = %d
						   AND meta_key = '_is_complete'
						   AND meta_value = 'yes'",
						array_merge( $user_ids, array( $course_id ) )
					)
				)
			); // db call ok; no-cache ok.

			foreach ( array_diff( $user_ids, $completed ) as $user_id ) {
				$enrolled = $enrollments[ $user_id ] ?? '';
				if ( $enrolled && $enrolled < $cutoff ) {
					$candidates[] = array(
						'user_id'         => $user_id,
						'related_post_id' => $course_id,
						'anchor'          => $enrolled,
					);
				}
			}
		}

		$last_row = end( $rows );

		return array(
			'candidates' => $candidates,
			'cursor'     => count( $rows ) === $per_page ? absint( $last_row->meta_id ) : null,
		);
	}

	/**
	 * Candidate query: incomplete quiz attempts untouched for N days.
	 *
	 * Paged by attempt ID; the attempt ID doubles as the re-arm anchor so a newer
	 * abandoned attempt re-fires while the same attempt never fires twice.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $engagement Engagement post object.
	 * @param int     $cursor     Last processed attempt ID.
	 * @param int     $per_page   Batch size.
	 * @return array See {@see LLMS_Engagements_Scanner::get_scannable_triggers()} for the return shape.
	 */
	public function query_quiz_attempt_abandoned( $engagement, $cursor, $per_page ) {

		global $wpdb;

		$done   = array(
			'candidates' => array(),
			'cursor'     => null,
		);
		$period = $this->get_period( $engagement );
		if ( ! $period ) {
			return $done;
		}

		$cutoff       = $this->get_cutoff( $period );
		$trigger_post = $this->get_trigger_post_id( $engagement );

		// When no specific quiz is configured `%d = 0` disables the quiz condition.
		// The users join excludes attempts orphaned by user deletion.
		$attempts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT attempts.id, attempts.student_id, attempts.quiz_id
				 FROM {$wpdb->prefix}lifterlms_quiz_attempts AS attempts
				 JOIN {$wpdb->users} AS users ON users.ID = attempts.student_id
				 WHERE attempts.status = 'incomplete'
				   AND attempts.id > %d
				   AND attempts.update_date < %s
				   AND ( %d = 0 OR attempts.quiz_id = %d )
				 ORDER BY attempts.id ASC
				 LIMIT %d",
				$cursor,
				$cutoff,
				$trigger_post,
				$trigger_post,
				$per_page
			)
		); // db call ok; no-cache ok.

		if ( ! $attempts ) {
			return $done;
		}

		$candidates = array();
		foreach ( $attempts as $attempt ) {
			$candidates[] = array(
				'user_id'         => absint( $attempt->student_id ),
				'related_post_id' => absint( $attempt->quiz_id ),
				'anchor'          => absint( $attempt->id ),
			);
		}

		$last = end( $attempts );

		return array(
			'candidates' => $candidates,
			'cursor'     => count( $attempts ) === $per_page ? absint( $last->id ) : null,
		);
	}
}
