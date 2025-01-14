<?php
/**
 * Core LifterLMS functions file
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'functions/llms-functions-l10n.php';

require_once 'functions/llms-functions-access-plans.php';
require_once 'functions/llms-functions-deprecated.php';
require_once 'functions/llms-functions-forms.php';
require_once 'functions/llms-functions-locale.php';
require_once 'functions/llms-functions-options.php';
require_once 'functions/llms-functions-progression.php';
require_once 'functions/llms-functions-user-information-fields.php';
require_once 'functions/llms-functions-wrappers.php';

require_once 'functions/llms.functions.access.php';
require_once 'functions/llms.functions.certificate.php';
require_once 'functions/llms.functions.course.php';
require_once 'functions/llms.functions.currency.php';
require_once 'functions/llms.functions.log.php';
require_once 'functions/llms.functions.notice.php';
require_once 'functions/llms.functions.order.php';
require_once 'functions/llms.functions.page.php';
require_once 'functions/llms.functions.person.php';
require_once 'functions/llms.functions.privacy.php';
require_once 'functions/llms.functions.quiz.php';
require_once 'functions/llms.functions.template.php';
require_once 'functions/llms.functions.user.postmeta.php';
require_once 'functions/llms.functions.favorite.php';

if ( ! function_exists( 'llms_anonymize_string' ) ) {
	/**
	 * Anonymize a string.
	 *
	 * Masks the characters in a string with the specified character leaving a small number
	 * of characters visible. For example `llms_anonymize_string( 'MY_SECRET_STRING' ) will return
	 * 'MY************NG'.
	 *
	 * The number of retained original characters is dependent on the string's length:
	 *
	 * | Length        | At start | At end | Example      |
	 * | ------------- | -------- | ------ | ------------ |
	 * | 1             | 0        | 0      | *            |
	 * | >= 2 && <= 6  | 0        | 1      | *****A       |
	 * | >= 7 && <= 10 | 0        | 2      | ********AA   |
	 * | >= 11         | 2        | 2      | AA*******AA  |
	 *
	 * Any string that validates as an email address using `is_email()` will be split at the `@` symbol
	 * and each part of the email address will be anonymized separately, for example:
	 * `llms_anonymize_string( 'help@lifterlms.com' )` will return '***p@li*********om'.
	 *
	 * @since 6.4.0
	 *
	 * @param string $string The input string to be anonymized.
	 * @param string $char   The character used to mask the string.
	 * @return string
	 */
	function llms_anonymize_string( $string, $char = '*' ) {

		if ( is_email( $string ) ) {
			$parts = explode( '@', $string );
			return llms_anonymize_string( $parts[0] ) . '@' . llms_anonymize_string( $parts[1] );
		}

		$len = strlen( $string );

		$at_front = 2;
		$at_back  = 2;
		if ( 1 === $len ) {
			return $char;
		} elseif ( $len <= 6 ) {
			$at_front = 0;
			$at_back  = 1;
		} elseif ( $len <= 10 ) {
			$at_front = 0;
		}

		$start = substr( $string, 0, $at_front );
		$body  = str_repeat( $char, strlen( $string ) - ( $at_front + $at_back ) );
		$end   = substr( $string, - $at_back );

		return "{$start}{$body}{$end}";
	}
}


/**
 * Insert elements into an associative array after a specific array key
 *
 * If the requested key doesn't exit, the new item will be added to the end of the array.
 * If you need to insert at the beginning of an array use array_merge( $new_item, $orig_item ).
 *
 * @since 3.21.0
 *
 * @param array  $array       Original associative array.
 * @param string $after_key   Key name in original array to insert new item after.
 * @param string $insert_key  Key name of the item to be inserted.
 * @param mixed  $insert_item Value to be inserted.
 * @return array
 */
function llms_assoc_array_insert( $array, $after_key, $insert_key, $insert_item ) {

	$res = array();

	$new_item = array(
		$insert_key => $insert_item,
	);

	$index = array_search( $after_key, array_keys( $array ) );
	if ( false !== $index ) {
		++$index;

		$res = array_merge(
			array_slice( $array, 0, $index, true ),
			$new_item,
			array_slice( $array, $index, count( $array ) - 1, true )
		);
	} else {
		$res = array_merge( $array, $new_item );
	}

	return $res;
}

/**
 * Do apply_filters( 'the_content', $content ) without actions adding their own content onto us...
 *
 * @param string $content Optional. The content. Default is empty string.
 * @return string
 * @since 3.16.10
 * @version 3.19.2
 */
if ( ! function_exists( 'llms_content' ) ) {
	function llms_content( $content = '' ) {
		$content = do_shortcode( shortcode_unautop( wpautop( convert_chars( wptexturize( $content ) ) ) ) );
		global $wp_embed;
		if ( $wp_embed && method_exists( $wp_embed, 'autoembed' ) ) {
			$content = $wp_embed->autoembed( $content );
		}
		return $content;
	}
}

/**
 * Mark a function as deprecated and inform when it is used.
 *
 * This function uses WP core's `_deprecated_function()`, logging to the LifterLMS log file
 * located at `wp-content/updloads/llms-logs/llms-{$hash}.log` instead of `wp-content/debug.log`.
 *
 * @since 2.6.0
 * @since 3.6.0 Unknown.
 * @since 4.4.0 Uses WP `_deprecated_function()` instead of duplicating its logic.
 *
 * @param string $function    Name of the deprecated function.
 * @param string $version     LifterLMS version that deprecated the function.
 * @param string $replacement Optional. Replacement function. Default is `null`.
 * @return void
 */
function llms_deprecated_function( $function, $version, $replacement = null ) {

	_deprecated_function( esc_html( $function ), esc_html( $version ), esc_html( $replacement ) );
}

/**
 * Cron function to cleanup files in the LLMS_TMP_DIR
 *
 * Removes any files that are more than a day old.
 *
 * @since 3.18.0
 * @since 4.10.1 Use strict type comparisons.
 *
 * @return void
 */
function llms_cleanup_tmp() {

	$max_age = llms_current_time( 'timestamp' ) - apply_filters( 'llms_tmpfile_max_age', DAY_IN_SECONDS );

	$exclude = array( '.htaccess', 'index.html' );

	foreach ( glob( LLMS_TMP_DIR . '*' ) as $file ) {

		// Don't cleanup index and .htaccess.
		if ( in_array( basename( $file ), $exclude, true ) ) {
			continue;
		}

		if ( filemtime( $file ) < $max_age ) {
			wp_delete_file( $file );
		}
	}
}
add_action( 'llms_cleanup_tmp', 'llms_cleanup_tmp' );

/**
 * Escape and add quotes to a string, useful for array mapping when building queries.
 *
 * @since 6.0.0
 *
 * @param string $str Input string.
 * @return string Escaped string wrapped in quotation marks.
 */
function llms_esc_and_quote_str( $str ) {
	return "'" . esc_sql( $str ) . "'";
}

/**
 * Retrieve an array of post types which can be completed by students
 *
 * @since 4.2.0
 *
 * @return string[]
 */
function llms_get_completable_post_types() {

	/**
	 * Filter the list of post types which can be completed by students.
	 *
	 * @since Unknown
	 *
	 * @param string[] $post_types WP_Post post type names.
	 */
	return apply_filters( 'llms_completable_post_types', array( 'course', 'section', 'lesson' ) );
}

/**
 * Retrieve an array of taxonomies which can be completed by students
 *
 * @since 4.2.0
 *
 * @return string[]
 */
function llms_get_completable_taxonomies() {

	/**
	 * Filter the list of taxonomies which can be completed by students.
	 *
	 * @since 4.2.0
	 *
	 * @param string[] $taxonomies Taxonomy names.
	 */
	return apply_filters( 'llms_completable_taxonomies', array( 'course_track' ) );
}

/**
 * Retrieve an array of post types whose name doesn't start with the prefix 'llms_'.
 *
 * @since 4.10.1
 *
 * @return string[]
 */
function llms_get_unprefixed_post_types() {

	/**
	 * Filter the list of post types whose name doesn't start with the prefix 'llms_'.
	 *
	 * @since 4.10.1
	 *
	 * @param string[] $post_types WP_Post post type names.
	 */
	return apply_filters( 'llms_unprefixed_post_types', array( 'course', 'section', 'lesson' ) );
}

/**
 * Get themes natively supported by LifterLMS
 *
 * @since 3.0.0
 *
 * @return array
 */
function llms_get_core_supported_themes() {
	return array(
		'canvas',
		'Divi',
		'genesis',
		'twentyseventeen',
		'twentysixteen',
		'twentyfifteen',
		'twentyfourteen',
		'twentythirteen',
		'twentyeleven',
		'twentytwelve',
		'twentyten',
	);
}

/**
 * Get human readable time difference between 2 dates
 *
 * Return difference between 2 dates in year, month, hour, minute or second
 * The $precision caps the number of time units used: for instance if
 * $time1 - $time2 = 3 days, 4 hours, 12 minutes, 5 seconds
 * - with precision = 1 : 3 days
 * - with precision = 2 : 3 days, 4 hours
 * - with precision = 3 : 3 days, 4 hours, 12 minutes.
 *
 * @since Unknown
 * @since 3.24.0 Unknown.
 *
 * @source http://www.if-not-true-then-false.com/2010/php-calculate-real-differences-between-two-dates-or-timestamps/
 *
 * @param mixed   $time1     A time (string or timestamp).
 * @param mixed   $time2     A time (string or timestamp).
 * @param integer $precision Optional precision. Default is 2.
 * @return string time difference
 */
function llms_get_date_diff( $time1, $time2, $precision = 2 ) {
	// If not numeric then convert timestamps.
	if ( ! is_numeric( $time1 ) ) {
		$time1 = strtotime( $time1 );
	}
	if ( ! is_numeric( $time2 ) ) {
		$time2 = strtotime( $time2 );
	}
	// If time1 > time2 then swap the 2 values.
	if ( $time1 > $time2 ) {
		list( $time1, $time2 ) = array( $time2, $time1 );
	}
	// Set up intervals and diffs arrays.
	$intervals     = array( 'year', 'month', 'day', 'hour', 'minute', 'second' );
	$l18n_singular = array(
		'year'   => __( 'year', 'lifterlms' ),
		'month'  => __( 'month', 'lifterlms' ),
		'day'    => __( 'day', 'lifterlms' ),
		'hour'   => __( 'hour', 'lifterlms' ),
		'minute' => __( 'minute', 'lifterlms' ),
		'second' => __( 'second', 'lifterlms' ),
	);
	$l18n_plural   = array(
		'year'   => __( 'years', 'lifterlms' ),
		'month'  => __( 'months', 'lifterlms' ),
		'day'    => __( 'days', 'lifterlms' ),
		'hour'   => __( 'hours', 'lifterlms' ),
		'minute' => __( 'minutes', 'lifterlms' ),
		'second' => __( 'seconds', 'lifterlms' ),
	);
	$diffs         = array();
	foreach ( $intervals as $interval ) {
		// Create temp time from time1 and interval.
		$ttime = strtotime( '+1 ' . $interval, $time1 );
		// Set initial values.
		$add    = 1;
		$looped = 0;
		// Loop until temp time is smaller than time2.
		while ( $time2 >= $ttime ) {
			// Create new temp time from time1 and interval.
			++$add;
			$ttime = strtotime( '+' . $add . ' ' . $interval, $time1 );
			++$looped;
		}
		$time1              = strtotime( '+' . $looped . ' ' . $interval, $time1 );
		$diffs[ $interval ] = $looped;
	}
	$count = 0;
	$times = array();
	foreach ( $diffs as $interval => $value ) {
		// Break if we have needed precision.
		if ( $count >= $precision ) {
			break;
		}
		// Add value and interval if value is bigger than 0.
		if ( $value > 0 ) {
			if ( 1 != $value ) {
				$text = $l18n_plural[ $interval ];
			} else {
				$text = $l18n_singular[ $interval ];
			}
			// Add value and interval to times array.
			$times[] = $value . ' ' . $text;
			++$count;
		}
	}
	// Return string with times.
	return implode( ', ', $times );
}

/**
 * Instantiate an instance of DOMDocument with an HTML string
 *
 * This function suppresses PHP warnings that would be thrown by DOMDocument when
 * loading a partial string or an HTML string with errors.
 *
 * @see LLMS_DOM_Document->load().
 *
 * @since 4.7.0
 * @since 4.8.0 Remove reliance on `mb_convert_encoding()`.
 * @since 4.13.0 Add back partial reliance on `mb_convert_encoding()` but keep the previous implementation as a fall-back.
 *               Also fix a potential fatal in the fall-back which tried to manipulate a non existent node.
 *               Wrapper for `LLMS_Dom_Document:load()`.
 *
 * @param string $string An HTML string, either a full HTML document or a partial string.
 * @return DOMDocument|WP_Error Returns an instance of DOMDocument with `$string` loaded into it
 *                              or an error object when DOMDocument isn't available or an error is encountered during loading.
 */
function llms_get_dom_document( $string ) {

	$llms_dom = new LLMS_DOM_Document( $string );
	$load     = $llms_dom->load();

	return is_wp_error( $load ) ? $load : $llms_dom->dom();
}

/**
 * Retrieve the HTML for a donut chart
 *
 * Note that this must be used in conjunction with some JS to initialize the chart!
 *
 * @since 3.9.0
 * @since 3.24.0 Unknown.
 *
 * @param mixed  $percentage Percentage to display
 * @param string $text       Optional. Text/caption to display (short). Default is empty string.
 * @param string $size       Optional. Size of the chart (mini, small, default, large). Default is 'default'.
 * @param array  $classes    Optional. Additional custom css classes to add to the chart element. Default is empty array.
 * @return string
 */
function llms_get_donut( $percentage, $text = '', $size = 'default', $classes = array() ) {
	$percentage = is_numeric( $percentage ) ? $percentage : 0;
	$classes    = array_merge( array( 'llms-donut', $size ), $classes );
	$classes    = implode( ' ', $classes );
	$percentage = 'mini' === $size ? round( $percentage, 0 ) : llms()->grades()->round( $percentage );
	return '
		<div class="' . esc_attr( $classes ) . '" data-perc="' . esc_attr( $percentage ) . '">
			<div class="inside">
				<div class="percentage">
					' . esc_html( $percentage ) . '<small>%</small>
					<div class="caption">' . esc_html( $text ) . '</div>
				</div>
			</div>
		</div>';
}

/**
 * Get a list of registered engagement triggers
 *
 * @return array
 * @since 3.1.0
 * @since 3.24.1
 */
function llms_get_engagement_triggers() {
	/**
	 * Filter the engagement triggers
	 *
	 * @since Unknown
	 *
	 * @param array $engagement_triggers An associative array of engagement triggers. Keys are the engagement trigger slugs, values are their description.
	 */
	return apply_filters(
		'lifterlms_engagement_triggers',
		array(
			'user_registration'      => __( 'Student creates a new account', 'lifterlms' ),
			'access_plan_purchased'  => __( 'Student Purchases an Access Plan', 'lifterlms' ),
			'course_enrollment'      => __( 'Student enrolls in a course', 'lifterlms' ),
			'course_purchased'       => __( 'Student purchases a course', 'lifterlms' ),
			'course_completed'       => __( 'Student completes a course', 'lifterlms' ),
			// 'days_since_login' => __( 'Days since user last logged in', 'lifterlms' ), // @todo.
			'lesson_completed'       => __( 'Student completes a lesson', 'lifterlms' ),
			'quiz_completed'         => __( 'Student completes a quiz', 'lifterlms' ),
			'quiz_passed'            => __( 'Student passes a quiz', 'lifterlms' ),
			'quiz_failed'            => __( 'Student fails a quiz', 'lifterlms' ),
			'section_completed'      => __( 'Student completes a section', 'lifterlms' ),
			'course_track_completed' => __( 'Student completes a course track', 'lifterlms' ),
			'membership_enrollment'  => __( 'Student enrolls in a membership', 'lifterlms' ),
			'membership_purchased'   => __( 'Student purchases a membership', 'lifterlms' ),
		)
	);
}

/**
 * Get a list of registered engagement types
 *
 * @return array
 * @since 3.1.0
 * @version 3.24.0
 */
function llms_get_engagement_types() {
	/**
	 * Filter the engagement types
	 *
	 * @since Unknown
	 *
	 * @param array $engagement_types An associative array of engagement types. Keys are the engagement type slugs, values are their description.
	 */
	return apply_filters(
		'lifterlms_engagement_types',
		array(
			'achievement' => __( 'Award an Achievement', 'lifterlms' ),
			'certificate' => __( 'Award a Certificate', 'lifterlms' ),
			'email'       => __( 'Send an Email', 'lifterlms' ),
		)
	);
}

/**
 * Retrieve a list of post types which users can be enrolled into.
 *
 * @since 4.4.1
 *
 * @return string[] A list of post type names.
 */
function llms_get_enrollable_post_types() {

	/**
	 * Customize the post types which users can be enrolled into.
	 *
	 * This filter differs slightly from `llms_user_enrollment_status_allowed_post_types`. This filter
	 * determines which post types a user can be physically associated with through enrollment while
	 * `llms_user_enrollment_status_allowed_post_types` allows checking of user enrollment based on
	 * posts which are associated with a post type.
	 *
	 * @since 3.37.9
	 *
	 * @see llms_user_enrollment_status_allowed_post_types
	 *
	 * @param string[] $post_types Array of post type names.
	 */
	return apply_filters( 'llms_user_enrollment_allowed_post_types', array( 'course', 'llms_membership' ) );
}

/**
 * Retrieve a list of post types that can be used to check a users enrollment status in an enroll-able post type.
 *
 * @since 4.4.1
 *
 * @return string[] A list of post type names.
 */
function llms_get_enrollable_status_check_post_types() {

	/**
	 * Customize the post types that can be used to check a user's enrollment status.
	 *
	 * This filter differs slightly from `llms_user_enrollment_allowed_post_types`. The difference is that
	 * a user can be enrolled into a course but we can check their course enrollment status using the ID of a child (section or lesson).
	 *
	 * When adding a new post type for custom enrollment functionality the post type should be registered with
	 * both of these filters.
	 *
	 * @since 3.37.9
	 *
	 * @see llms_user_enrollment_allowed_post_types
	 *
	 * @param string[] $post_types List of allowed post types names.
	 */
	return apply_filters( 'llms_user_enrollment_status_allowed_post_types', array( 'course', 'section', 'lesson', 'llms_membership' ) );
}

/**
 * Retrieve an HTML anchor for an option page
 *
 * @since 3.18.0
 *
 * @param string $option_name Option name.
 * @param string $target      Optional. HTML target attribute. Defaults to _blank.
 * @return string
 */
function llms_get_option_page_anchor( $option_name, $target = '_blank' ) {

	$page_id = get_option( $option_name );

	if ( ! $page_id ) {
		return '';
	}

	$target = $target ? ' target="' . esc_attr( $target ) . '"' : '';

	return sprintf(
		'<a href="%1$s"%2$s>%3$s</a>',
		get_the_permalink( $page_id ),
		$target,
		get_the_title( $page_id )
	);
}

/**
 * Get a list of available product (course & membership) catalog visibility options
 *
 * @since 3.6.0
 *
 * @return array
 */
function llms_get_product_visibility_options() {
	/**
	 * Filter the product visibility options
	 *
	 * @since 3.6.0
	 *
	 * @param array $product_visibility_options. An associative array representing of visibility options. Keys are the engagement type slugs, values are their description.
	 */
	return apply_filters(
		'lifterlms_product_visibility_options',
		array(
			'catalog_search' => __( 'Catalog &amp; Search', 'lifterlms' ),
			'catalog'        => __( 'Catalog only', 'lifterlms' ),
			'search'         => __( 'Search only', 'lifterlms' ),
			'hidden'         => __( 'Hidden', 'lifterlms' ),
		)
	);
}

/**
 * Get an array of student IDs based on enrollment status a course or membership
 *
 * @since 3.0.0
 * @since 3.8.0 Unknown.
 * @since 4.10.2 Instantiate the student query passing `no_found_rows` arg as `true`,
 *               as we don't need (and do not return) pagination info, e.g. max_pages.
 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
 *
 * @param int          $post_id  WP_Post id of a course or membership.
 * @param string|array $statuses List of enrollment statuses to query by status query is an OR relationship. Default is 'enrolled'.
 * @param integer      $limit    Number of results.
 * @param integer      $skip     Number of results to skip (for pagination).
 * @return array
 */
function llms_get_enrolled_students( $post_id, $statuses = 'enrolled', $limit = 50, $skip = 0 ) {

	$query = new LLMS_Student_Query(
		array(
			'post_id'       => $post_id,
			'statuses'      => $statuses,
			'page'          => ( 0 === $skip ) ? 1 : ( $skip / $limit ) + 1,
			'per_page'      => $limit,
			'sort'          => array(
				'id' => 'ASC',
			),
			'no_found_rows' => true,
		)
	);

	if ( $query->has_results() ) {
		return wp_list_pluck( $query->get_results(), 'id' );
	}

	return array();
}

/**
 * Retrieve default instructor data structure.
 *
 * @since 3.25.0
 *
 * @return array
 */
function llms_get_instructors_defaults() {
	/**
	 * Filter the instructor's default data structure.
	 *
	 * @since 3.25.0
	 *
	 * @param array $product_visibility_options. An associative array representing the instructor's default data structure.
	 */
	return apply_filters(
		'llms_post_instructors_get_defaults',
		array(
			'label'      => __( 'Author', 'lifterlms' ),
			'visibility' => 'visible',
			'id'         => '',
		)
	);
}

/**
 * Function used to sanitize user input in a manner similar to the (deprecated) FILTER_SANITIZE_STRING.
 *
 * This function retrieves the raw user input via `llms_filter_input()` using the FILTER_UNSAFE_RAW filter, strips
 * all tags, and then encodes single and double quotes with the relevant HTML entity codes.
 *
 * In many cases, the usage of `FILTER_SANITIZE_STRING` can be easily replaced with `FILTER_SANITIZE_FULL_SPECIAL_CHARS` but
 * in some cases, especially when storing the user input, encoding all special characters can result in an stored XSS injection
 * so this function can be used to preserve the pre PHP 8.1 behavior where sanitization is expected during the retrieval
 * of user input.
 *
 * @since 5.9.0
 *
 * @param int    $type          One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
 * @param string $variable_name Name of a variable to retrieve.
 * @param int[]  $flags         Array of supported filter options and flags.
 *                              Accepts `FILTER_REQUIRE_ARRAY` in order to require the input to be an array.
 *                              Accepts `FILTER_FLAG_NO_ENCODE_QUOTES` to prevent encoding of quotes.
 * @return string|string[]|null|boolean Value of the requested variable on success, `false` if the filter fails, or `null` if the `$variable_name` variable is not set.
 */
function llms_filter_input_sanitize_string( $type, $variable_name, $flags = array() ) {

	$require_array = in_array( FILTER_REQUIRE_ARRAY, $flags, true );

	$string = llms_filter_input( $type, $variable_name, FILTER_UNSAFE_RAW, $require_array ? FILTER_REQUIRE_ARRAY : array() );

	// If we have an empty string or the input var isn't found we can return early.
	if ( empty( $string ) ) {
		return $string;
	}

	$string = $require_array ? array_map( 'wp_strip_all_tags', $string ) : wp_strip_all_tags( $string );

	if ( ! in_array( FILTER_FLAG_NO_ENCODE_QUOTES, $flags, true ) ) {
		$string = str_replace(
			array( "'", '"' ),
			array( '&#39;', '&#34;' ),
			$string
		);
	}

	return $string;
}


/**
 * Get the most recently created coupon ID for a given code
 *
 * @param string $code        Optional. The coupon's code (title). Default is empty string.
 * @param int    $dupcheck_id Optional. Coupon id that can be passed which will be excluded during the query
 *                            this is used to dupcheck the coupon code during coupon creation. Default is 0.
 * @return int
 * @since   3.0.0
 * @version 3.0.0
 */
function llms_find_coupon( $code = '', $dupcheck_id = 0 ) {

	global $wpdb;
	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID
		 FROM {$wpdb->posts}
		 WHERE post_title = %s
		 AND post_type = 'llms_coupon'
		 AND post_status = 'publish'
		 AND ID != %d
		 ORDER BY ID desc;
		",
			array( $code, $dupcheck_id )
		)
	); // no-cache ok.
}

/**
 * Get a list of available course / membership enrollment statuses
 *
 * @since 3.0.0
 *
 * @return array
 */
function llms_get_enrollment_statuses() {
	/**
	 * Filter the enrollment statuses
	 *
	 * @since 3.0.0
	 *
	 * @param array $enrollment_statuses An associative array representing the enrollment statuses. Keys are the statuses, values are their human readable labels (names).
	 */
	return apply_filters(
		'llms_get_enrollment_statuses',
		array(
			'cancelled' => __( 'Cancelled', 'lifterlms' ),
			'enrolled'  => __( 'Enrolled', 'lifterlms' ),
			'expired'   => __( 'Expired', 'lifterlms' ),
		)
	);
}

/**
 * Get the human readable (and translated) name of an enrollment status
 *
 * @since 3.0.0
 * @since 3.6.0 Unknown.
 *
 * @param string $status Enrollment status key.
 * @return string
 */
function llms_get_enrollment_status_name( $status ) {

	$status   = strtolower( $status ); // Backwards compatibility.
	$statuses = llms_get_enrollment_statuses();
	if ( is_array( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	}
	/**
	 * Filter the enrollment status name
	 *
	 * @since Unknown
	 *
	 * @param array $enrollment_status The enrollment status name.
	 */
	return apply_filters( 'lifterlms_get_enrollment_status_name', $status );
}

/**
 * Retrieve an IP Address for the current user
 *
 * @since 3.0.0
 * @since 3.35.0 Sanitize superglobal input.
 *
 * @return string
 */
function llms_get_ip_address() {

	$ip = '';

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Look below you.
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Look below you.
	if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2.
		// Make sure we always only send through the first IP in the list which should always be the client IP.
		$ip = trim( current( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash

	$ip = sanitize_text_field( wp_unslash( $ip ) );

	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return '';
	}

	return $ip;
}

/**
 * Retrieves and filters the value open registration option
 *
 * @since 5.0.0
 *
 * @return string The value of the open registration status. Either "yes" for enabled or "no" for disabled.
 */
function llms_get_open_registration_status() {

	$status = get_option( 'lifterlms_enable_myaccount_registration', 'no' );

	/**
	 * Filter the value of the open registration setting
	 *
	 * @since 3.37.10
	 *
	 * @param string $status The current value of the open registration option. Either "yes" for enabled or "no" for disabled.
	 */
	return apply_filters( 'llms_enable_open_registration', $status );
}

/**
 * Retrieve the LLMS Post Model for a give post by ID or WP_Post Object
 *
 * @since 3.3.0
 * @since 3.16.11 Unknown.
 * @since 4.10.1 Made sure to only instantiate LifterLMS classes.
 *
 * @param WP_Post|int $post  Instance of WP_Post or a WP Post ID.
 * @param mixed       $error Determine what to return if the LLMS class isn't found.
 *                           post  = WP_Post
 *                           falsy = false.
 * @return LLMS_Post_Model|WP_Post|null|false LLMS_Post_Model extended object,
 *                                            null if WP get_post() fails,
 *                                            WP_Post if LLMS_Post_Model extended class isn't found and $error = 'post'
 *                                            false if LLMS_Post_Model extended class isn't found and $error != 'post'.
 */
function llms_get_post( $post, $error = false ) {

	$post = get_post( $post );
	if ( ! $post ) {
		return $post;
	}

	$class = '';

	// Check whether it's an llms post candidate: `post_type` starts with the 'llms_' prefix, or is one of the unprefixed ones.
	if ( 0 === strpos( $post->post_type, 'llms_' ) || in_array( $post->post_type, llms_get_unprefixed_post_types(), true ) ) {
		$post_type = explode( '_', str_replace( 'llms_', '', $post->post_type ) );
		$class     = 'LLMS';
		foreach ( $post_type as $part ) {
			$class .= '_' . ucfirst( $part );
		}
	}

	if ( $class && class_exists( $class ) ) {
		return new $class( $post );
	} elseif ( 'post' === $error ) {
		return $post;
	}

	return false;
}

/**
 * Retrieve the parent course for a section, lesson, or quiz
 *
 * @since 3.6.0
 * @since 3.17.7 Unknown.
 * @since 3.37.14 Bail if `$post` is not an istance of `LLMS_Post_Model`.
 *                Use strict comparison.
 *
 * @param WP_Post|int $post WP Post ID or instance of WP_Post.
 * @return LLMS_Course|null Instance of the LLMS_Course or null.
 */
function llms_get_post_parent_course( $post ) {

	$post = llms_get_post( $post );

	if ( ! $post || ! is_a( $post, 'LLMS_Post_Model' ) ) {
		return null;
	}

	/**
	 * Filter the course children post types
	 *
	 * @since Unknown
	 *
	 * @param $post_type string[] Names of the post types that can be children of a course.
	 */
	$post_types = apply_filters( 'llms_course_children_post_types', array( 'section', 'lesson', 'llms_quiz' ) );
	if ( ! in_array( $post->get( 'type' ), $post_types, true ) ) {
		return null;
	}

	/** @var LLMS_Section|LLMS_Lesson|LLMS_Quiz $post */
	return $post->get_course();
}


/**
 * Retrieve an array of existing transaction statuses
 *
 * @since 3.0.0
 *
 * @return array
 */
function llms_get_transaction_statuses() {
	/**
	 * Filter the transaction statuses
	 *
	 * @since Unknown
	 *
	 * @param $statuses string[] Names of the possible transaction statuses.
	 */
	return apply_filters(
		'llms_get_transaction_statuses',
		array(
			'llms-txn-failed',
			'llms-txn-pending',
			'llms-txn-refunded',
			'llms-txn-succeeded',
		)
	);
}

/**
 * Determine is request is an ajax request
 *
 * @since 3.0.1
 * @since 4.0.0 Use WP core `wp_doing_ajax()`.
 *
 * @return bool
 */
function llms_is_ajax() {
	return wp_doing_ajax();
}

/**
 * Determine if request is a REST request
 *
 * @since 3.27.0
 *
 * @return bool
 */
function llms_is_rest() {
	/**
	 * Filters whether the current request is a REST request.
	 *
	 * @since 5.4.0
	 *
	 * @param $is_rest Whether the current request is a REST request.
	 */
	return apply_filters( 'llms_is_rest', ( defined( 'REST_REQUEST' ) && REST_REQUEST ) );
}

/**
 * Determine whether the current theme is a block theme.
 *
 * Just a wrapper for WordPress core `wp_is_block_theme()` so to filter for testing purposes.
 *
 * @since 6.0.0
 *
 * @return string
 */
function llms_is_block_theme() {
	/**
	 * Filters whether the current theme is a block theme.
	 *
	 * @since 6.0.0
	 *
	 * @param $is_block_theme Whether the current theme is a block theme.
	 */
	return apply_filters( 'llms_is_block_theme', function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );
}

/**
 * Checks if the current admin page is the block editor.
 *
 * @since 7.2.0
 *
 * @return bool
 */
function llms_is_block_editor(): bool {
	if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
		return true;
	}

	$current_screen = get_current_screen();

	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		return true;
	}

	return false;
}

/**
 * Determine if the current request is a block rendering request in the editor.
 *
 * @since 7.2.0
 *
 * @return bool
 */
function llms_is_editor_block_rendering() {
	if ( ! defined( 'REST_REQUEST' ) || ! is_user_logged_in() ) {
		return false;
	}

	global $wp;

	if ( ! $wp instanceof WP || empty( $wp->query_vars['rest_route'] ) ) {
		return false;
	}

	$route = $wp->query_vars['rest_route'];

	return false !== strpos( $route, '/block-renderer/' );
}

/**
 * Check if the home URL is https. If it is, we don't need to do things such as 'force ssl'.
 *
 * @thanks woocommerce <3.
 *
 * @since 3.0.0
 *
 * @return bool
 */
function llms_is_site_https() {
	return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
 * Create an array that can be passed to metabox select elements configured as an llms-select2-post query-ier
 *
 * @since 3.0.0
 * @since 3.6.0 Unknown
 *
 * @param array  $post_ids  Optional. Indexed array of WordPress Post IDs. Defayult is empty array.
 * @param string $template  Optional. A template to customize the way the results look. Default is empty string.
 *                          {title} and {id} can be passed into the template
 *                          and will be replaced with the post title and post id respectively.
 * @return array
 */
function llms_make_select2_post_array( $post_ids = array(), $template = '' ) {

	if ( ! $template ) {
		$template = '{title} (' . __( 'ID#', 'lifterlms' ) . ' {id})';
	}

	if ( ! is_array( $post_ids ) ) {
		$post_ids = array( $post_ids );
	}

	$ret = array();
	foreach ( $post_ids as $id ) {

		$title = str_replace( array( '{title}', '{id}' ), array( get_the_title( $id ), $id ), $template );

		$ret[] = array(
			'key'   => $id,
			'title' => $title,
		);
	}
	/**
	 * Filter the select2 post array
	 *
	 * @since Unknown
	 *
	 * @param array Associative array of representing select2 post elements.
	 * @param array $post_ids  Optional. Indexed array of WordPress Post IDs.
	 */
	return apply_filters( 'llms_make_select2_post_array', $ret, $post_ids );
}

/**
 * Create an array that can be passed to metabox select elements configured as an llms-select2-student query-ier.
 *
 * @since 3.10.1
 * @version 3.23.0
 *
 * @param array  $user_ids Optional. Indexed array of WordPress User IDs. Default is empty array.
 * @param string $template Optional. A template to customize the way the results look. Default is empty string.
 *                         %1$s = student name
 *                         %2$s = student email.
 * @return array
 */
function llms_make_select2_student_array( $user_ids = array(), $template = '' ) {
	if ( ! $template ) {
		$template = '%1$s &lt;%2$s&gt;';
	}
	if ( ! is_array( $user_ids ) ) {
		$user_ids = array( $user_ids );
	}
	$ret = array();
	foreach ( $user_ids as $id ) {
		$student = llms_get_student( $id );
		if ( ! $student ) {
			continue;
		}
		$ret[] = array(
			'key'   => $id,
			'title' => sprintf( $template, $student->get_name(), $student->get( 'user_email' ) ),
		);
	}

	/**
	 * Filter the select2 student array
	 *
	 * @since Unknown
	 *
	 * @param array $elements  Associative array representing select2 student elements.
	 * @param array $post_ids  Optional. Indexed array of WordPress Post IDs.
	 */
	return apply_filters( 'llms_make_select2_student_array', $ret, $user_ids );
}

/**
 * Define a constant if it's not already defined
 *
 * @since 3.15.0
 *
 * @param string $name  Constant name.
 * @param mixed  $value Constant values.
 * @return void
 */
function llms_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Parse booleans
 *
 * Mostly used to parse yes/no bools stored in various meta data fields
 *
 * @since 3.16.0
 *
 * @param mixed $val Value to parse.
 * @return bool
 */
function llms_parse_bool( $val ) {
	return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Convert a PHP error constant to a human readable error code
 *
 * @since 4.9.0
 *
 * @link https://www.php.net/manual/en/errorfunc.constants.php
 *
 * @param int $code A predefined php error constant.
 * @return string A human readable string version of the constant.
 */
function llms_php_error_constant_to_code( $code ) {

	$codes = array(
		E_ERROR             => 'E_ERROR', // 1.
		E_WARNING           => 'E_WARNING', // 2.
		E_PARSE             => 'E_PARSE', // 4.
		E_NOTICE            => 'E_NOTICE', // 8.
		E_CORE_ERROR        => 'E_CORE_ERROR', // 16.
		E_CORE_WARNING      => 'E_CORE_WARNING', // 32.
		E_COMPILE_ERROR     => 'E_COMPILE_ERROR', // 64.
		E_COMPILE_WARNING   => 'E_COMPILE_WARNING', // 128.
		E_USER_ERROR        => 'E_USER_ERROR', // 256.
		E_USER_WARNING      => 'E_USER_WARNING', // 512.
		E_USER_NOTICE       => 'E_USER_NOTICE', // 1024.
		E_STRICT            => 'E_STRICT', // 2048.
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR', // 4096.
		E_DEPRECATED        => 'E_DEPRECATED', // 8192.
		E_USER_DEPRECATED   => 'E_USER_DEPRECATED', // 16384.
	);

	return isset( $codes[ $code ] ) ? $codes[ $code ] : $code;
}

/**
 * Wrapper for set_time_limit to ensure it's enabled before calling
 *
 * @since 3.16.5
 *
 * @source thanks WooCommerce <3
 *
 * @param int $limit  Optional. Script time limit. Default is 0 = no time limit.
 * @return void
 */
function llms_set_time_limit( $limit = 0 ) {

	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {

		@set_time_limit( $limit ); // @phpcs:ignore

	}
}

/**
 * Strips a list of prefixes from the start of a string.
 *
 * By default, strips `llms_`, `lifterlms_`, 'llms-', or 'lifterlms-'. Other prefixes may be provided.
 *
 * Will strip only the first prefix found from the list of supplied prefixes.
 *
 * @since 6.0.0
 * @since 7.0.0 Added `llms-` and `lifterlms-` as additional default prefixes to strip.
 *
 * @param string   $string   String to modify.
 * @param string[] $prefixes List of prefixs.
 * @return string The modified string. If no prefixes were found, the original string is returned without modification.
 */
function llms_strip_prefixes( $string, $prefixes = array() ) {

	$prefixes = empty( $prefixes ) ? array( 'llms_', 'lifterlms_', 'llms-', 'lifterlms-' ) : $prefixes;

	foreach ( $prefixes as $prefix ) {
		if ( 0 === strpos( $string, $prefix ) ) {
			$string = substr( $string, strlen( $prefix ) );

			/**
			 * Most of the time we'll be using this to replace `llms_` as we don't often use `lifterlms_` for
			 * prefixing (anymore).
			 *
			 * Also, while it's probably not ever in use, this will prevent double-stripping if, for example,
			 * the string was `llms_lifterlms_something`. If we did want to strip that, the `$prefixes` should
			 * be overwritten to have both these items stripped.
			 *
			 * So once we find a prefix, we'll break the loop and return the string with the stripped prefix.
			 */
			break;
		}
	}

	return $string;
}

/**
 * Trim a string and append a suffix
 *
 * @since 3.0.0
 *
 * @source thank you WooCommerce <3
 *
 * @param string $string Input string.
 * @param int    $chars  Optional. Max number of characters. Default is 200.
 * @param string $suffix Optional. A suffix to append. Default is '...'.
 * @return string
 */
function llms_trim_string( $string, $chars = 200, $suffix = '...' ) {
	if ( strlen( $string ) > $chars ) {
		if ( function_exists( 'mb_substr' ) ) {
			$string = mb_substr( $string, 0, ( $chars - mb_strlen( $suffix ) ) ) . $suffix;
		} else {
			$string = substr( $string, 0, ( $chars - strlen( $suffix ) ) ) . $suffix;
		}
	}
	return $string;
}

/**
 * Verify nonce with additional checks to confirm request method
 *
 * Skips verification if the nonce is not set
 * Useful for checking nonce for various LifterLMS forms which check for the form submission on init actions.
 *
 * @since 3.8.0
 * @since 3.35.0 Sanitize nonce field before verification.
 *
 * @param string $nonce          Name of the nonce field.
 * @param string $action         Name of the action.
 * @param string $request_method Optional. Name of the intended request method. Default is 'POST'.
 * @return null|false|int
 */
function llms_verify_nonce( $nonce, $action, $request_method = 'POST' ) {

	if ( strtoupper( getenv( 'REQUEST_METHOD' ) ) !== $request_method ) {
		return;
	}

	if ( empty( $_REQUEST[ $nonce ] ) ) {
		return;
	}

	return wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $nonce ] ) ), $action );
}

/**
 * Check that the test value is a member of a specific array for sanitization purposes.
 *
 * @param mixed $needle Value to be tested.
 * @param array $safelist Array of safelist values.
 * @param mixed $default Default value to return if the needle is not in the safelist. Defaults to the first value in the safelist array if not provided.
 * @since 7.6.0
 */
function llms_sanitize_with_safelist( $needle, $safelist, $default = null ) {
	if ( ! in_array( $needle, $safelist ) ) {
		if ( isset( $default ) ) {
			return $default;
		} else {
			return $safelist[0];
		}
	} else {
		return $needle;
	}
}
