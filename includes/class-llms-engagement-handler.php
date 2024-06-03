<?php
/**
 * LLMS_Engagement_Handler class file.
 *
 * @package LifterLMS/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validate and generate or send engagement posts.
 *
 * Handles validation, dupchecking, and etc...
 *
 * For certificates and achievements the earned ("_my_") post type is created.
 *
 * For emails, the email is triggered and sending recorded in the user postmeta table.
 *
 * @since 6.0.0
 */
class LLMS_Engagement_Handler {

	/**
	 * Create a new earned achievement or certificate.
	 *
	 * This method is called by handler callback functions run when engagements are triggered.
	 *
	 * Before arriving here the input data ($user_id, $template_id, etc...) has already been validated to ensure
	 * that it exists and the engagement can be processed using this data.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $type          The engagement type, either "achievement" or "certificate".
	 * @param int      $user_id       WP_User ID of the student earning the engagement.
	 * @param int      $template_id   WP_Post ID of the template post (llms_achievement or llms_certificate).
	 * @param string   $related_id    WP_Post ID of the triggering related post (course, lesson, etc...) or an empty string for user registration.
	 * @param null|int $engagement_id WP_Post ID of the engagement post used to configure the trigger. A `null` value maybe be passed for legacy
	 *                                delayed engagements which were created without an engagement ID or when manually awarding via the admin UI.
	 * @return boolean|WP_Error[] $can_process An array of WP_Errors or true if the engagement can be processed.
	 */
	private static function can_process( $type, $user_id, $template_id, $related_id = '', $engagement_id = null ) {

		/**
		 * Skip engagement processing checks and force engagements to process.
		 *
		 * This filter is used internally to skip running checks for immediate engagements which cannot
		 * suffer from the issues that these checks seek to avoid.
		 *
		 * @since 6.0.0
		 *
		 * @param boolean  $skip_checks   Whether or not to skip checks.
		 * @param string   $type          The engagement type, either "achievement" or "certificate".
		 * @param int      $user_id       WP_User ID of the student earning the engagement.
		 * @param int      $template_id   WP_Post ID of the template post (llms_achievement or llms_certificate).
		 * @param string   $related_id    WP_Post ID of the triggering related post (course, lesson, etc...) or an empty string for user registration.
		 * @param null|int $engagement_id WP_Post ID of the engagement post used to configure the trigger. A `null` value maybe be passed for legacy
		 *                                delayed engagements which were created without an engagement ID or when manually awarding via the admin UI.
		 * }
		 */
		$skip_checks = apply_filters( 'llms_skip_engagement_processing_checks', false, $type, $user_id, $template_id, $related_id, $engagement_id );
		if ( $skip_checks ) {
			return true;
		}

		$checks = array();

		// User must exist.
		$user_check = get_userdata( $user_id ) ? true : new WP_Error( 'llms-engagement-check-user--not-found', sprintf( __( 'User "%d" not found.', 'lifterlms' ), $user_id ) );
		$checks[]   = $user_check;

		// Template must be published and of the expected post type.
		$checks[] = self::check_post( $template_id, "llms_{$type}" );

		// Check related post (if one is passed).
		if ( ! empty( $related_id ) ) {
			$check_related = self::check_post( $related_id );
			$checks[]      = $check_related;
			// Check post enrollment if the check passed and there's no user issues.
			if ( ! is_wp_error( $check_related ) && ! is_wp_error( $user_check ) ) {
				$checks[] = self::check_post_enrollment( $related_id, $user_id );
			}
		}

		// Ensure we have an argument to check, engagements created prior to v6.0.0 will not have this argument.
		if ( ! empty( $engagement_id ) ) {
			$checks[] = self::check_post( $engagement_id, 'llms_engagement' );
		}

		// Find all the failed checks.
		$errors = array_values( array_filter( $checks, 'is_wp_error' ) );

		/**
		 * Filters whether or not an engagement should be processed immediately prior to it being sent or awarded.
		 *
		 * The dynamic portion of this hook, `{$type}` refers to the type of engagement being processed, either "email",
		 * "certificate", or "achievement".
		 *
		 * @since 6.0.0
		 *
		 * @param boolean|WP_Error[] $can_process   An array of WP_Errors or true if the engagement can be processed.
		 * @param int                $user_id       WP_User ID of the student earning the engagement.
		 * @param int                $template_id   WP_Post ID of the template post (llms_achievement or llms_certificate).
		 * @param string             $related_id    WP_Post ID of the triggering related post (course, lesson, etc...) or an empty string for user registration.
		 * @param null|int           $engagement_id WP_Post ID of the engagement post used to configure the trigger. A `null` value maybe be passed for legacy
		 *                                          delayed engagements which were created without an engagement ID or when manually awarding via the admin UI.
		 * }
		 */
		return apply_filters( "llms_proccess_{$type}_engagement", count( $errors ) ? $errors : true, $user_id, $template_id, $related_id, $engagement_id );

	}

	/**
	 * Apply deprecated creation filters based on the engagement type.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $args Array of creation arguments.
	 * @param string $type The engagement type, accepts "achievement" or "certificate".
	 * @return array
	 */
	private static function do_deprecated_creation_filters( $args, $type ) {

		$hooks = array(
			'achievement' => array( 'lifterlms_new_achievement', 'llms_achievement_get_creation_args' ),
			'certificate' => array( 'lifterlms_new_page', 'llms_certificate_get_creation_args' ),
		);

		$hook = $hooks[ $type ] ?? null;
		if ( ! $hook ) {
			return $args;
		}

		return apply_filters_deprecated( $hook[0], array( $args ), '6.0.0', $hook[1] );

	}

	/**
	 * Handles deprecated filters which have additional parameters from now deprecated classes.
	 *
	 * If there are no callbacks attached to the deprecated hook the original $args is returned and no
	 * warnings will be emitted.
	 *
	 * This instantiates an initialized instance of the deprecated class and passes it with the original filtered
	 * argument through `apply_filters_deprecated`. This results in several deprecation warnings being emitted
	 * but ensures that these filters can continue to work in a backwards compatible manner.
	 *
	 * This method is a public method but it is intentionally marked as private to denote its temporary lifespan. It will
	 * be removed alongside the deprecated filters it calls as it will no longer be necessary when the deprecated
	 * hooks are fully removed. As such, this method is considered private for the purposes of semantic versioning and
	 * will removed in the next major release without being officially deprecated.
	 *
	 * @since 6.0.0
	 *
	 * @access private
	 *
	 * @param mixed  $args         The filtered argument (not an array of arguments).
	 * @param array  $init_args    {
	 *      An array of arguments used to initialize the old object.
	 *
	 *     @type int        $0 WP_Post ID of the template post, either an `llms_certificate` or `llms_achievement`.
	 *     @type int        $1 WP_User ID of the user.
	 *     @type int|string $2 WP_Post ID of the related post or an empty string during user registration.
	 * }
	 * @param string $type        The engagement type, either "achievement" or "certificate".
	 * @param string $deprecated  The deprecated filter to call.
	 * @param string $replacement The replacement hook.
	 * @return mixed
	 */
	public static function do_deprecated_filter( $args, $init_args, $type, $deprecated, $replacement ) {

		if ( has_filter( $deprecated ) ) {

			$old_class = sprintf( 'LLMS_%s_User', strtoupper( $type ) );

			/**
			 * Retains deprecated functionality where an instance of LLMS_Certificate_User is passed as a parameter to the filter.
			 *
			 * Since there's no good way to recreate that functionality we'll handle it in this manner
			 * until `LLMS_Certificate_User` is removed.
			 */
			$old_obj = new $old_class();
			$old_obj->init( ...$init_args );
			$args = apply_filters_deprecated( $deprecated, array( $args, $old_obj ), '6.0.0', $replacement );
		}

		return $args;

	}

	/**
	 * Create a new earned achievement or certificate.
	 *
	 * This method is called by handler callback functions run when engagements are triggered.
	 *
	 * Before arriving here the input data ($user_id, $template_id, etc...) has already been validated to ensure
	 * that it exists and the engagement can be processed using this data.
	 *
	 * @since 6.0.0
	 *
	 * @param string   $type          The engagement type, either "achievement" or "certificate".
	 * @param int      $user_id       WP_User ID of the student earning the engagement.
	 * @param int      $template_id   WP_Post ID of the template post (llms_achievement or llms_certificate).
	 * @param string   $related_id    WP_Post ID of the triggering related post (course, lesson, etc...) or an empty string for user registration.
	 * @param null|int $engagement_id WP_Post ID of the engagement post used to configure the trigger. A `null` value maybe be passed for legacy
	 *                                delayed engagements which were created without an engagement ID or when manually awarding via the admin UI.
	 * @return WP_Error|LLMS_User_Certificate|LLMS_User_Achievement
	 */
	private static function create( $type, $user_id, $template_id, $related_id = '', $engagement_id = null ) {

		$title    = get_post_meta( $template_id, "_llms_{$type}_title", true );
		$template = get_post( $template_id );

		// Setup args, ultimately passed to `wp_insert_post()`.
		$post_args = array(
			'post_author'  => $user_id,
			'post_content' => $template->post_content,
			'post_date'    => llms_current_time( 'mysql' ),
			'post_name'    => 'certificate' === $type ? llms()->certificates()->get_unique_slug( $title ) : null,
			'post_parent'  => $template_id,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'meta_input'   => array(
				'_thumbnail_id'    => self::get_image_id( $type, $template_id ),
				'_llms_engagement' => $engagement_id,
				'_llms_related'    => $related_id,
			),
		);

		// Do deprecated filters. No direct replacement added, instead use `LLMS_Post_Model` creation filters.
		$post_args = self::do_deprecated_creation_filters( $post_args, $type );

		$model_class = sprintf( 'LLMS_User_%s', ucwords( $type ) );
		$generated   = new $model_class( 'new', $post_args );
		if ( ! $generated || ! $generated->get( 'id' ) ) {
			return new WP_Error( 'llms-engagement-init--create', __( 'An error was encountered during post creation.', 'lifterlms' ), compact( 'user_id', 'template_id', 'related_id', 'engagement_id', 'post_args', 'type', 'model_class' ) );
		}

		// Reinstantiate the class so the merged post_content will be retrieved if accessed immediately.
		return new $model_class( $generated->get( 'id' ) );

	}

	/**
	 * Runs post-creation actions when creating/awarding an achievement or certificate to a user.
	 *
	 * @param string          $type          The engagement type, either "achievement" or "certificate".
	 * @param int             $user_id       WP_User ID of the student who earned the engagement.
	 * @param int             $generated_id  WP_Post ID of the generated engagement post.
	 * @param string|int|null $related_id    WP_Post ID of the related post triggering generation, an empty string (in the event of a user registration trigger) or null if not supplied.
	 * @param int|null        $engagement_id WP_Post ID of the engagement post used to configure engagement triggering.
	 *
	 * @return void
	 */
	public static function create_actions( $type, $user_id, $generated_id, $related_id = '', $engagement_id = null ) {

		// I think this should be removed but there's a lot of places where queries to _certificate_earned or _achievement_earned exist and it's the documented way of retrieving this data.
		// Internally we should switch to stop relying on this and figure out a way to phase out the usage of the user postmeta data but for now I think we'll continue storing it.
		llms_update_user_postmeta(
			$user_id,
			$related_id,
			"_{$type}_earned",
			$generated_id,
			// The earned engagement must be unique if a `$related_id` is present, otherwise it must be not.
			// Manual awarding have no `$related_id`, and if we force the uniquiness we will end up updating always the same earned engagement
			// every time we manually award a new one for the same user.
			(bool) $related_id
		);

		/**
		 * Action run after a student has successfully earned an engagement.
		 *
		 * The dynamic portion of this hook, `{$type}`, refers to the engagement type,
		 * either "achievement" or "certificate".
		 *
		 * @since 1.0.0
		 * @since 6.0.0 Added the `$engagement_id` parameter.
		 *
		 * @param int             $user_id       WP_User ID of the student who earned the engagement.
		 * @param int             $generated_id  WP_Post ID of the generated engagement post.
		 * @param string|int|null $related_id    WP_Post ID of the related post triggering generation, an empty string (in the event of a user registration trigger) or null if not supplied.
		 * @param int|null        $engagement_id WP_Post ID of the engagement post used to configure engagement triggering.
		 */
		do_action(
			"llms_user_earned_{$type}",
			$user_id,
			$generated_id,
			$related_id,
			$engagement_id
		);

	}

	/**
	 * Validates a post id submitted to an engagement handler callback function.
	 *
	 * This ensures the following is true:
	 *   + The post must exist
	 *   + It must be published
	 *   + Optionally, it must match the specified post type.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $post_id   WP_Post ID.
	 * @param string $post_type The expected post type.
	 * @return WP_Error|boolean Returns `true` if all checks pass, otherwise returns a `WP_Error`.
	 */
	public static function check_post( $post_id, $post_type = null ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			// Translators: %d = the WP_Post ID.
			return new WP_Error( 'llms-engagement-post--not-found', sprintf( __( 'Post "%d" not found.', 'lifterlms' ), $post_id ), compact( 'post_id' ) );
		}

		if ( 'publish' !== $post->post_status ) {
			// Translators: %d = the WP_Post ID.
			return new WP_Error( 'llms-engagement-post--status', sprintf( __( 'Post "%d" is not published.', 'lifterlms' ), $post_id ), compact( 'post' ) );
		}

		if ( $post_type && $post_type !== $post->post_type ) {
			// Translators: %d = the WP_Post ID.
			return new WP_Error( 'llms-engagement-post--type', sprintf( __( 'Post "%d" is not the expected post type.', 'lifterlms' ), $post_id ), compact( 'post' ) );
		}

		return true;

	}

	/**
	 * Check that the specified user is enrolled in the given post.
	 *
	 * This check will return true when running against non-enrollable post types.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id WP_Post ID.
	 * @param int $user_id WP_User ID.
	 * @return WP_Error|boolean Returns `true` if the check passes, otherwise returns a `WP_Error`.
	 */
	private static function check_post_enrollment( $post_id, $user_id ) {

		$type  = get_post_type( $post_id );
		$types = llms_get_enrollable_status_check_post_types();

		// If the post type is an enrollable post type, check enrollment.
		if ( in_array( $type, $types, true ) && ! llms_is_user_enrolled( $user_id, $post_id ) ) {
			// Translators: %1$d = WP_User ID; %2$d = WP_Post ID.
			return new WP_Error( 'llms-engagement-check-post--enrollment', sprintf( __( 'User "%1$d" is not enrolled in "%2$d".', 'lifterlms' ), $user_id, $post_id ), compact( 'post_id', 'user_id' ) );
		}

		return true;

	}

	/**
	 * Check if the engagement for the specified template and related post has already been earned / awarded to a given user.
	 *
	 * @since 6.0.0
	 *
	 * @param string $type          Engagement type, either "certificate" or "achievement".
	 * @param int    $user_id       WP_User ID of the user earning the engagement.
	 * @param int    $template_id   WP_Post ID of the template post, either an `llms_certificate` or an `llms_achievement`.
	 * @param string $related_id    WP_Post ID of the related post or an empty string during user registration.
	 * @param int    $engagement_id WP_Post ID of the `llms_engagement` post type.
	 * @return WP_Error|boolean Returns `true` if the dupcheck passes otherwise returns an error object.
	 */
	private static function dupcheck( $type, $user_id, $template_id, $related_id = '', $engagement_id = null ) {

		$student = llms_get_student( $user_id );

		$query = new LLMS_Awards_Query(
			array(
				'type'          => $type,
				'users'         => $user_id,
				'templates'     => $template_id,
				'related_posts' => $related_id,
				'fields'        => 'ids',
				'no_found_rows' => true,
				'per_page'      => 1,
			)
		);

		$is_duplicate = self::do_deprecated_filter(
			$query->has_results(),
			array( $template_id, $user_id, $related_id ),
			$type,
			"llms_{$type}_has_user_earned",
			"llms_earned_{$type}_dupcheck"
		);

		/**
		 * Filters whether or not the given user has already earned a certificate or achievement.
		 *
		 * The dynamic portion of this hook, `{$type}`, refers to the type of engagement, either
		 * "achievement" or "certificate".
		 *
		 * This filter should return `true` or a `WP_Error` to denote the certificate has already been earned and
		 * `false` to denote that it has not.
		 *
		 * If `true` is returned the default error message will be used.
		 *
		 * @since 6.0.0
		 *
		 * @param boolean $is_duplicate Whether or not the engagement has already been earned.
		 */
		$is_duplicate = apply_filters(
			"llms_earned_{$type}_dupcheck",
			$is_duplicate,
			$user_id,
			$template_id,
			$related_id,
			$engagement_id
		);

		if ( true === $is_duplicate ) {
			$is_duplicate = new WP_Error(
				'llms-engagement--is-duplicate',
				// Translators: %s = the WP_User ID.
				sprintf( __( 'User "%s" has already earned this engagement.', 'lifterlms' ), $user_id ),
				compact( 'type', 'user_id', 'template_id', 'related_id', 'engagement_id' )
			);
		}

		return is_wp_error( $is_duplicate ) ? $is_duplicate : true;

	}

	/**
	 * Retrieve the attachment id to use for the earned engagement thumbnail.
	 *
	 * Retrieves the template's featured image ID and validates and then falls back to the site's
	 * global default image option.
	 *
	 * If no global option is found, returns `0`. During front-end display, the hardcoded image will be used
	 * in the template if the earned engagement's thumbnail is set to a fasly.
	 *
	 * @since 6.0.0
	 *
	 * @param string $type        Type of engagement, either "achievement" or "certificate".
	 * @param int    $template_id WP_Post ID of the template post.
	 * @return int WP_Post ID of the attachment or `0` when none found.
	 */
	public static function get_image_id( $type, $template_id ) {

		$img_id = get_post_meta( $template_id, '_thumbnail_id', true );

		if ( $img_id && get_post( $img_id ) ) {
			return absint( $img_id );
		}

		if ( 'achievement' === $type ) {
			return llms()->achievements()->get_default_image_id();
		}

		if ( 'certificate' === $type ) {
			return llms()->certificates()->get_default_image_id();
		}

		return 0;

	}

	/**
	 * Handle validation and creation of an earned achievement or certificate.
	 *
	 * @since 6.0.0
	 *
	 * @param string $type Type of engagement, either "achievement" or "certificate".
	 * @param array  $args {
	 *      Indexed array of arguments.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the achievement or certificate template post.
	 *     @type int|string $2 WP_Post ID of the related post that triggered the award or an empty string.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return WP_Error[]|LLMS_User_Achiemvent|LLMS_User_Certificate An array of errors or the earned engagement object
	 */
	private static function handle( $type, $args ) {

		$can_process = self::can_process( $type, ...$args );
		if ( true !== $can_process ) {
			return $can_process;
		}

		$dupcheck = self::dupcheck( $type, ...$args );
		if ( true !== $dupcheck ) {
			return array( $dupcheck );
		}

		return self::create( $type, ...$args );

	}

	/**
	 * Award an achievement
	 *
	 * @since 6.0.0
	 *
	 * @param array $args {
	 *     Indexed array of arguments.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the achievement template post.
	 *     @type int|string $2 WP_Post ID of the related post that triggered the award or an empty string.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return WP_Error[]|LLMS_User_Achievement Returns an array of error objects on failure or the generated achievement object on success.
	 */
	public static function handle_achievement( $args ) {
		return self::handle( 'achievement', $args );
	}

	/**
	 * Award an certificate
	 *
	 * @since 6.0.0
	 *
	 * @param array $args {
	 *     Indexed array of arguments.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the certificate template post.
	 *     @type int|string $2 WP_Post ID of the related post that triggered the award or an empty string.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return WP_Error[]|LLMS_User_Certificate Returns an array of error objects on failure or the generated certificate object on success.
	 */
	public static function handle_certificate( $args ) {
		return self::handle( 'certificate', $args );
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
	 * @since 6.0.0 Moved from `LLMS_Engagements` class.
	 *                Removed engagement debug logging.
	 *                Ensure related post, email template, and engagement all exist and are published before processing.
	 *
	 * @param mixed[] $args {
	 *     An array of arguments from the triggering hook.
	 *
	 *     @type int        $0 WP_User ID.
	 *     @type int        $1 WP_Post ID of the email.
	 *     @type int|string $2 WP_Post ID of the related triggering post or an empty string for engagements with no related post.
	 *     @type int        $3 WP_Post ID of the engagement post.
	 * }
	 * @return bool|WP_Error[] Returns `true` on success and array of error objects when the email has failed or is prevented.
	 */
	public static function handle_email( $args ) {

		$can_process = self::can_process( 'email', ...$args );
		if ( true !== $can_process ) {
			return $can_process;
		}

		list( $person_id, $email_id, $related_id ) = $args;

		$meta_key = '_email_sent';

		$msg = sprintf( __( 'Email #%1$d to user #%2$d triggered by %3$s', 'lifterlms' ), $email_id, $person_id, $related_id ? '#' . $related_id : 'N/A' );

		if ( $related_id && absint( $email_id ) === absint( llms_get_user_postmeta( $person_id, $related_id, $meta_key ) ) ) {

			// User has already received this email, don't send it again.
			llms_log( $msg . ' ' . __( 'not sent because of dupcheck.', 'lifterlms' ), 'engagement-emails' );
			return array( new WP_Error( 'llms_engagement_email_not_sent_dupcheck', $msg, $args ) );

		}

		// Setup the email.
		$email = llms()->mailer()->get_email( 'engagement', compact( 'person_id', 'email_id', 'related_id' ) );
		if ( $email && $email->send() ) {

			if ( $related_id ) {
				llms_update_user_postmeta( $person_id, $related_id, $meta_key, $email_id );
			}

			llms_log( $msg . ' ' . __( 'sent successfully.', 'lifterlms' ), 'engagement-emails' );
			return true;
		}

		// Error sending email.
		llms_log( $msg . ' ' . __( 'not sent due to email sending issues.', 'lifterlms' ), 'engagement-emails' );
		return array( new WP_Error( 'llms_engagement_email_not_sent_error', $msg, $args ) );

	}

}
