<?php
/**
 * Core LifterLMS functions file
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @since 3.30.1 Moved order-related functions to order functions file.
 * @version 3.29.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'functions/llms-functions-access-plans.php';
require_once 'functions/llms-functions-deprecated.php';
require_once 'functions/llms-functions-options.php';
require_once 'functions/llms-functions-progression.php';

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

/**
 * Insert elements into an associative array after a specific array key
 * If the requested key doesn't exit, the new item will be added to the end of the array
 * If you need to insert at the beginning of an array use array_merge( $new_item, $orig_item );
 *
 * @param    array  $array        original associative array
 * @param    string $after_key    key name in original array to insert new item after
 * @param    string $insert_key   key name of the item to be inserted
 * @param    mixed  $insert_item  value to be inserted
 * @return   array
 * @since    3.21.0
 * @version  3.21.0
 */
function llms_assoc_array_insert( $array, $after_key, $insert_key, $insert_item ) {

	$res = array();

	$new_item = array(
		$insert_key => $insert_item,
	);

	$index = array_search( $after_key, array_keys( $array ) );
	if ( false !== $index ) {
		$index++;

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
 * Retrieve the current time based on specified type.
 *
 * This is a wrapper for the WP Core current_time which can be plugged
 * We plug this during unit testing to allow mocking the current time
 *
 * The 'mysql' type will return the time in the format for MySQL DATETIME field.
 * The 'timestamp' type will return the current timestamp.
 * Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
 *
 * If $gmt is set to either '1' or 'true', then both types will use GMT time.
 * if $gmt is false, the output is adjusted with the GMT offset in the WordPress option.
 *
 * @param  string       $type   Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param  int|bool     $gmt    Optional. Whether to use GMT timezone. Default false.
 * @return int|string           Integer if $type is 'timestamp', string otherwise.
 *
 * @since    3.4.0
 * @version  3.4.0
 */
if ( ! function_exists( 'llms_current_time' ) ) {
	function llms_current_time( $type, $gmt = 0 ) {
		return current_time( $type, $gmt );
	}
}

/**
 * Do apply_filters( 'the_content', $content ) without actions adding their own content onto us...
 *
 * @param    string     $content  [description]
 * @return   string
 * @since    3.16.10
 * @version  3.19.2
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
 * Provide deprecation warnings
 *
 * Very similar to https://developer.wordpress.org/reference/functions/_deprecated_function/
 *
 * @param   string $function    name of the deprecated class or function
 * @param   string $version     version deprecation occurred
 * @param   string $replacement function to use in it's place (optional)
 * @return  void
 * @since   2.6.0
 * @version 3.6.0
 */
function llms_deprecated_function( $function, $version, $replacement = null ) {

	// only warn if debug is enabled
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	if ( function_exists( '__' ) ) {

		if ( ! is_null( $replacement ) ) {
			$string = sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'lifterlms' ), $function, $version, $replacement );
		} else {
			$string = sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s!', 'lifterlms' ), $function, $version );
		}
	} else {

		if ( ! is_null( $replacement ) ) {
			$string = sprintf( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $function, $version, $replacement );
		} else {
			$string = sprintf( '%1$s is <strong>deprecated</strong> since version %2$s!', $function, $version );
		}
	}

	// warn on screen
	if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
		echo '<br>' . $string . '<br>';
	}

	// log to the error logger
	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		llms_log( $string );
	}

}

/**
 * Cron function to cleanup files in the LLMS_TMP_DIR
 * Removes any files that are more than a day old
 *
 * @return   void
 * @since    3.18.0
 * @version  3.18.0
 */
function llms_cleanup_tmp() {

	$max_age = llms_current_time( 'timestamp' ) - apply_filters( 'llms_tmpfile_max_age', DAY_IN_SECONDS );

	$exclude = array( '.htaccess', 'index.html' );

	foreach ( glob( LLMS_TMP_DIR . '*' ) as $file ) {

		// dont cleanup index and .htaccess
		if ( in_array( basename( $file ), $exclude ) ) {
			continue;
		}

		if ( filemtime( $file ) < $max_age ) {
			wp_delete_file( $file );
		}
	}

}
add_action( 'llms_cleanup_tmp', 'llms_cleanup_tmp' );

if ( ! function_exists( 'llms_filter_input' ) ) {

	/**
	 * Gets a specific external variable by name and optionally filters it
	 *
	 * This is a pluggable wrapper around native `filter_input` which is plugged in the testing framework
	 * to allow easy mocking of form variables when testing form controller functions and methods
	 *
	 * @param   int    $type           One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
	 * @param   string $variable_name  Name of a variable to get.
	 * @param   int    $filter         The ID of the filter to apply.
	 * @param   mixed  $options        Associative array of options or bitwise disjunction of flags. If filter accepts options, flags can be provided in "flags" field of array.
	 * @return  mixed  Value of the requested variable on success, FALSE if the filter fails, or NULL if
	 *                 the variable_name variable is not set. If the flag FILTER_NULL_ON_FAILURE is used,
	 *                 it returns FALSE if the variable is not set and NULL if the filter fails.
	 * @since   3.29.0
	 * @version 3.29.0
	 */
	function llms_filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = array() ) {
		return filter_input( $type, $variable_name, $filter, $options );
	}
}

/**
 * Get themes natively supported by LifterLMS
 *
 * @return array
 * @since 3.0.0
 * @version 3.0.1
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
 * - with precision = 3 : 3 days, 4 hours, 12 minutes
 *
 * @param mixed   $time1 a time (string or timestamp)
 * @param mixed   $time2 a time (string or timestamp)
 * @param integer $precision Optional precision
 * @return string time difference
 * @source http://www.if-not-true-then-false.com/2010/php-calculate-real-differences-between-two-dates-or-timestamps/
 *
 * @since    ??
 * @version  3.24.0
 */
function llms_get_date_diff( $time1, $time2, $precision = 2 ) {
	// If not numeric then convert timestamps
	if ( ! is_numeric( $time1 ) ) {
		$time1 = strtotime( $time1 );
	}
	if ( ! is_numeric( $time2 ) ) {
		$time2 = strtotime( $time2 );
	}
	// If time1 > time2 then swap the 2 values
	if ( $time1 > $time2 ) {
		list( $time1, $time2 ) = array( $time2, $time1 );
	}
	// Set up intervals and diffs arrays
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
		// Create temp time from time1 and interval
		$ttime = strtotime( '+1 ' . $interval, $time1 );
		// Set initial values
		$add    = 1;
		$looped = 0;
		// Loop until temp time is smaller than time2
		while ( $time2 >= $ttime ) {
			// Create new temp time from time1 and interval
			$add++;
			$ttime = strtotime( '+' . $add . ' ' . $interval, $time1 );
			$looped++;
		}
		$time1              = strtotime( '+' . $looped . ' ' . $interval, $time1 );
		$diffs[ $interval ] = $looped;
	}
	$count = 0;
	$times = array();
	foreach ( $diffs as $interval => $value ) {
		// Break if we have needed precision
		if ( $count >= $precision ) {
			break;
		}
		// Add value and interval if value is bigger than 0
		if ( $value > 0 ) {
			if ( 1 != $value ) {
				$text = $l18n_plural[ $interval ];
			} else {
				$text = $l18n_singular[ $interval ];
			}
			// Add value and interval to times array
			$times[] = $value . ' ' . $text;
			$count++;
		}
	}
	// Return string with times
	return implode( ', ', $times );
}

/**
 * Retrieve the HTML for a donut chart
 * Note that this must be used in conjunction with some JS to initialize the chart!
 *
 * @param    mixed  $percentage  percentage to display
 * @param    string $text        optional text/caption to display (short)
 * @param    string $size        size of the chart (mini, small, default, large)
 * @param    array  $classes     additional custom css classes to add to the chart element
 * @return   string
 * @since    3.9.0
 * @version  3.24.0
 */
function llms_get_donut( $percentage, $text = '', $size = 'default', $classes = array() ) {
	$percentage = is_numeric( $percentage ) ? $percentage : 0;
	$classes    = array_merge( array( 'llms-donut', $size ), $classes );
	$classes    = implode( ' ', $classes );
	$percentage = 'mini' === $size ? round( $percentage, 0 ) : LLMS()->grades()->round( $percentage );
	return '
		<div class="' . $classes . '" data-perc="' . $percentage . '">
			<div class="inside">
				<div class="percentage">
					' . $percentage . '<small>%</small>
					<div class="caption">' . $text . '</div>
				</div>
			</div>
		</div>';
}

/**
 * Get a list of registered engagement triggers
 *
 * @return   array
 * @since    3.1.0
 * @version  3.24.1
 */
function llms_get_engagement_triggers() {
	return apply_filters(
		'lifterlms_engagement_triggers',
		array(
			'user_registration'      => __( 'Student creates a new account', 'lifterlms' ),
			'access_plan_purchased'  => __( 'Student Purchases an Access Plan', 'lifterlms' ),
			'course_enrollment'      => __( 'Student enrolls in a course', 'lifterlms' ),
			'course_purchased'       => __( 'Student purchases a course', 'lifterlms' ),
			'course_completed'       => __( 'Student completes a course', 'lifterlms' ),
			// 'days_since_login' => __( 'Days since user last logged in', 'lifterlms' ), // @todo
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
 * @return   array
 * @since    3.1.0
 * @version  3.24.0
 */
function llms_get_engagement_types() {
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
 * Retrieve an HTML anchor for an option page
 *
 * @param    string $option_name
 * @param    string $target      HTML target attribute, defaults to _blank
 * @return   string
 * @since    3.18.0
 * @version  3.18.0
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
 * @return   array
 * @since    3.6.0
 * @version  3.6.0
 */
function llms_get_product_visibility_options() {
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
 * @param    int          $post_id   WP_Post id of a course or membership
 * @param    string|array $statuses  list of enrollment statuses to query by
 *                                   status query is an OR relationship
 * @param    integer      $limit        number of results
 * @param    integer      $skip         number of results to skip (for pagination)
 * @return   array
 * @since    3.0.0
 * @version  3.8.0
 */
function llms_get_enrolled_students( $post_id, $statuses = 'enrolled', $limit = 50, $skip = 0 ) {

	$query = new LLMS_Student_Query(
		array(
			'post_id'  => $post_id,
			'statuses' => $statuses,
			'page'     => ( 0 === $skip ) ? 1 : ( $skip / $limit ) + 1,
			'per_page' => $limit,
			'sort'     => array(
				'id' => 'ASC',
			),
		)
	);

	if ( $query->results ) {
		return wp_list_pluck( $query->results, 'id' );
	}

	return array();
}

/**
 * Retrieve default instructor data structure.
 *
 * @return  array
 * @since   3.25.0
 * @version 3.25.0
 */
function llms_get_instructors_defaults() {
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
 * Get the most recently created coupon ID for a given code
 *
 * @param   string $code        the coupon's code (title)
 * @param   int    $dupcheck_id an optional coupon id that can be passed which will be excluded during the query
 *                              this is used to dupcheck the coupon code during coupon creation
 * @return  int
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
	);

}

/**
 * Generate the HTML for a form field
 *
 * this function is used during AJAX calls so needs to be in a core file
 * loaded during AJAX calls!
 *
 * @param    array   $field  field data
 * @param    boolean $echo   echo the data if true, return otherwise
 * @return   void|string
 * @since    3.0.0
 * @version  3.19.4
 */
function llms_form_field( $field = array(), $echo = true ) {

	$field = wp_parse_args(
		$field,
		array(
			'columns'         => 12,
			'classes'         => '',
			'description'     => '',
			'default'         => '',
			'disabled'        => false,
			'id'              => '',
			'label'           => '',
			'last_column'     => true,
			'match'           => '',
			'max_length'      => '',
			'min_length'      => '',
			'name'            => '',
			'options'         => array(),
			'placeholder'     => '',
			'required'        => false,
			'selected'        => '',
			'style'           => '',
			'type'            => 'text',
			'value'           => '',
			'wrapper_classes' => '',
		)
	);

	// setup the field value (if one exists)
	if ( '' !== $field['value'] ) {
		$field['value'] = $field['value'];
	} elseif ( '' !== $field['default'] ) {
		$field['value'] = $field['default'];
	}
	$value_attr = ( '' !== $field['value'] ) ? ' value="' . $field['value'] . '"' : '';

	// use id as the name if name isn't specified
	$field['name'] = ( '' === $field['name'] ) ? $field['id'] : $field['name'];

	// allow items to not have a name attr (eg: not be posted via form submission)
	// example use case found in Stripe CC fields
	if ( false === $field['name'] ) {
		$name_attr = '';
	} else {
		$name_attr = ' name="' . $field['name'] . '"';
	}

	$field['placeholder'] = wp_strip_all_tags( $field['placeholder'] );

	// add inline css if set
	$field['style'] = ( $field['style'] ) ? ' style="' . $field['style'] . '"' : '';

	// add space to classes
	$field['wrapper_classes'] = ( $field['wrapper_classes'] ) ? ' ' . $field['wrapper_classes'] : '';
	$field['classes']         = ( $field['classes'] ) ? ' ' . $field['classes'] : '';

	// add column information to the wrapper
	$field['wrapper_classes'] .= ' llms-cols-' . $field['columns'];
	$field['wrapper_classes'] .= ( $field['last_column'] ) ? ' llms-cols-last' : '';

	$desc = $field['description'] ? '<span class="llms-description">' . $field['description'] . '</span>' : '';

	// required attributes and content
	$required_char = apply_filters( 'lifterlms_form_field_required_character', '*', $field );
	$required_span = $field['required'] ? ' <span class="llms-required">' . $required_char . '</span>' : '';
	$required_attr = $field['required'] ? ' required="required"' : '';

	// setup the label
	$label = $field['label'] ? '<label for="' . $field['id'] . '">' . $field['label'] . $required_span . '</label>' : '';

	$r = '<div class="llms-form-field type-' . $field['type'] . $field['wrapper_classes'] . '">';

	if ( 'hidden' !== $field['type'] && 'checkbox' !== $field['type'] && 'radio' !== $field['type'] ) {
		$r .= $label;
	}

	$disabled_attr = ( $field['disabled'] ) ? ' disabled="disabled"' : '';

	$min_attr = ( $field['min_length'] ) ? ' minlength="' . $field['min_length'] . '"' : '';
	$max_attr = ( $field['max_length'] ) ? ' maxlength="' . $field['max_length'] . '"' : '';

	switch ( $field['type'] ) {

		case 'button':
		case 'reset':
		case 'submit':
			$r .= '<button class="llms-field-button' . $field['classes'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '"' . $disabled_attr . $name_attr . $field['style'] . '>' . $field['value'] . '</button>';
			break;

		case 'checkbox':
		case 'radio':
			$checked = ( true === $field['selected'] ) ? ' checked="checked"' : '';
			$r      .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '"' . $checked . $disabled_attr . $name_attr . $required_attr . $value_attr . $field['style'] . '>';
			$r      .= $label;
			break;

		case 'html':
			$r .= '<div class="llms-field-html' . $field['classes'] . '" id="' . $field['id'] . '">' . $field['value'] . '</div>';
			break;

		case 'select':
			$r .= '<select class="llms-field-select' . $field['classes'] . '" id="' . $field['id'] . '" ' . $disabled_attr . $name_attr . $required_attr . $field['style'] . '>';
			foreach ( $field['options'] as $k => $v ) {
				$r .= '<option value="' . $k . '"' . selected( $k, $field['value'], false ) . '>' . $v . '</option>';
			}
			$r .= '</select>';
			break;

		case 'textarea':
			$r .= '<textarea class="llms-field-textarea' . $field['classes'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '"' . $disabled_attr . $name_attr . $required_attr . $field['style'] . '>' . $field['value'] . '</textarea>';
			break;

		default:
			$r .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '" type="' . $field['type'] . '"' . $disabled_attr . $name_attr . $min_attr . $max_attr . $required_attr . $value_attr . $field['style'] . '>';

	}

	if ( 'hidden' !== $field['type'] ) {
		$r .= $desc;
	}

	$r .= '</div>';

	if ( $field['last_column'] ) {
		$r .= '<div class="clear"></div>';
	}

	$r = apply_filters( 'llms_form_field', $r, $field );

	if ( $echo ) {

		echo $r;
		return;

	} else {

		return $r;

	}

}

/**
 * Get a list of available course / membership enrollment statuses
 *
 * @return   array
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_enrollment_statuses() {

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
 * @param    string $status  enrollment status key
 * @return   string
 * @since    3.0.0
 * @version  3.6.0
 */
function llms_get_enrollment_status_name( $status ) {

	$status   = strtolower( $status ); // backwards compatibility
	$statuses = llms_get_enrollment_statuses();
	if ( is_array( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	}
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
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
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
 * Retrieve the LLMS Post Model for a give post by ID or WP_Post Object
 *
 * @param    WP_Post|int $post  instance of WP_Post or a WP Post ID
 * @param    mixed       $error determine what to return if the LLMS class isn't found
 *                              post  = WP_Post
 *                              falsy = false
 * @return   LLMS_Post_Model|WP_Post|null|false LLMS_Post_Model extended object,
 *                                              null if WP get_post() fails,
 *                                              WP_Post if LLMS_Post_Model extended class isn't found and $error = 'post'
 *                                              false if LLMS_Post_Model extended class isn't found and $error != 'post'
 * @since    3.3.0
 * @version  3.16.11
 */
function llms_get_post( $post, $error = false ) {

	$post = get_post( $post );
	if ( ! $post ) {
		return $post;
	}

	$post_type = explode( '_', str_replace( 'llms_', '', $post->post_type ) );
	$class     = 'LLMS';
	foreach ( $post_type as $part ) {
		$class .= '_' . ucfirst( $part );
	}

	if ( class_exists( $class ) ) {
		return new $class( $post );
	} elseif ( 'post' === $error ) {
		return $post;
	}

	return false;

}

/**
 * Retrieve the parent course for a section, lesson, or quiz
 *
 * @param    WP_Post|int $post WP Post ID or instance of WP_Post
 * @return   LLMS_Course|null Instance of the LLMS_Course or null
 * @since    3.6.0
 * @version  3.17.7
 */
function llms_get_post_parent_course( $post ) {

	$post       = llms_get_post( $post );
	$post_types = apply_filters( 'llms_course_children_post_types', array( 'section', 'lesson', 'llms_quiz' ) );
	if ( ! $post || ! in_array( $post->get( 'type' ), $post_types ) ) {
		return null;
	}

	/** @var LLMS_Section|LLMS_Lesson|LLMS_Quiz $post */
	return $post->get_course();

}


/**
 * Retrieve an array of existing transaction statuses
 *
 * @return   array
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_transaction_statuses() {
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
 * @return   bool
 * @since    3.0.1
 * @version  3.0.1
 */
function llms_is_ajax() {
	return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
}

/**
 * Determine if request is a REST request
 *
 * @return   bool
 * @since    3.27.0
 * @version  3.27.0
 */
function llms_is_rest() {
	return ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/**
 * Check if the home URL is https. If it is, we don't need to do things such as 'force ssl'.
 *
 * @thanks woocommerce <3
 * @return   bool
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_is_site_https() {
	return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
 * Create an array that can be passed to metabox select elements
 * configured as an llms-select2-post query-ier
 *
 * @param    array  $post_ids  indexed array of WordPress Post IDs
 * @param    string $template  an optional template to customize the way the results look
 *                             {title} and {id} can be passed into the template
 *                             and will be replaced with the post title and post id respectively
 * @return   array
 * @since    3.0.0
 * @version  3.6.0
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
	return apply_filters( 'llms_make_select2_post_array', $ret, $post_ids );

}

/**
 * Create an array that can be passed to metabox select elements
 * configured as an llms-select2-student query-ier
 *
 * @param    array  $user_ids  indexed array of WordPress User IDs
 * @param    string $template  an optional template to customize the way the results look
 *                             %1$s = student name
 *                             %2$s = student email
 * @return   array
 * @since    3.10.1
 * @version  3.23.0
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
	return apply_filters( 'llms_make_select2_student_array', $ret, $user_ids );
}

/**
 * Define a constant if it's not already defined
 *
 * @param    string $name   constant name
 * @param    mixed  $value  constant values
 * @return   void
 * @since    3.15.0
 * @version  3.15.0
 */
function llms_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Parse booleans
 * Mostly used to parse yes/no bools stored in various meta data fields
 *
 * @param    mixed $val      value to parse
 * @return   bool
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_parse_bool( $val ) {
	return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Redirect and exit
 * Wrapper for WP core redirects which automatically calls `exit();`
 * and is pluggable (mainly for unit testing purposes)
 *
 * @param    string     $location  full URL to redirect to
 * @param    array      $options   array of options
 *                                 $status  int   HTTP status code of the redirect [default: 302]
 *                                 $safe    bool  If true, use `wp_safe_redirect()` otherwise use `wp_redirect()` [default: true]
 * @return   void
 * @since    3.19.4
 * @version  3.19.4
 */
if ( ! function_exists( 'llms_redirect_and_exit' ) ) {
	function llms_redirect_and_exit( $location, $options = array() ) {

		$options = wp_parse_args(
			$options,
			array(
				'status' => 302,
				'safe'   => true,
			)
		);

		$func = $options['safe'] ? 'wp_safe_redirect' : 'wp_redirect';
		call_user_func( $func, $location, $options['status'] );
		exit();

	}
}


/**
 * Wrapper for set_time_limit to ensure it's enabled before calling
 *
 * @param    int $limit  script time limit
 *                       0 = no time limit
 * @return   void
 * @source   thanks WooCommerce <3
 * @since    3.16.5
 * @version  3.16.5
 */
function llms_set_time_limit( $limit = 0 ) {

	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {

		@set_time_limit( $limit ); // @codingStandardsIgnoreLine

	}

}

/**
 * Trim a string and append a suffix.
 *
 * @source thank you WooCommerce <3
 * @param  string $string  input string
 * @param  int    $chars   max number of characters
 * @param  string $suffix  optionally append a suffix
 * @return string
 * @since  3.0.0
 * @version  3.0.0
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
 * Skips verification if the nonce is not set
 * Useful for checking nonce for various LifterLMS forms which check for the form submission on init actions
 *
 * @since 3.8.0
 * @since 3.35.0 Sanitize nonce field before verification.
 *
 * @param    string $nonce           name of the nonce field
 * @param    string $action          name of the action
 * @param    string $request_method  name of the intended request method
 * @return   null|false|int
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
 * Verifies a plain text password key for a user (by login) against the hashed key in the database
 *
 * @param    string $key    plain text activation key
 * @param    string $login  user login
 * @return   boolean
 * @since    3.8.0
 * @version  3.8.0
 */
function llms_verify_password_reset_key( $key = '', $login = '' ) {

	$key = preg_replace( '/[^a-z0-9]/i', '', $key );
	if ( empty( $key ) || ! is_string( $key ) ) {
		return false;
	}

	if ( empty( $login ) || ! is_string( $login ) ) {
		return false;
	}

	global $wpdb;
	$user_key = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s",
			$login
		)
	);

	if ( empty( $user_key ) ) {
		return false;
	}

	global $wp_hasher;

	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . 'wp-includes/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}

	$valid = $wp_hasher->CheckPassword( $key, $user_key );

	if ( empty( $valid ) ) {
		return false;
	}

	return true;

}
