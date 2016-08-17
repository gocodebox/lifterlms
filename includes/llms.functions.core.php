<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Core functions file
*
* Misc functions used by lifterLMS core.
*/

//include other function files
include( 'functions/llms.functions.access.php' );
include( 'functions/llms.functions.certificate.php' );
include( 'functions/llms.functions.course.php' );
include( 'functions/llms.functions.currency.php' );
include( 'functions/llms.functions.notice.php' );
include( 'functions/llms.functions.page.php' );
include( 'functions/llms.functions.person.php' );
include( 'functions/llms.functions.template.php' );

/**
 * Check Course Capacity
 *
 * @return bool [is course at capacity?]
 *
 * @todo  rename or add to a class
 */
function check_course_capacity() {
	global $post, $wpdb;

	$lesson_max_user = (int) get_post_meta( $post->ID, '_lesson_max_user', true );
	$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE post_id = '.$post->ID .' AND meta_value = "Enrolled"' );

	if ($lesson_max_user === 0) {
		return true;
	} else {
		return count( $results ) < $lesson_max_user;
	}
}

/**
 * Get Section Id
 *
 * @param  int $course_id [course post ID]
 * @param  int $lesson_id [leson Post ID]
 * @return int $section [section post ID]
 *
 * @todo  possibly unused maybe deprecate
 */
function get_section_id( $course_id, $lesson_id ) {

	$course = new LLMS_Course( $course_id );
	$syllabus = $course->get_syllabus();
	$sections = array();
	$section;

	foreach ($syllabus as $key => $value) {

		$sections[ $value['section_id'] ] = $value['lessons'];
		foreach ($value['lessons'] as $keys => $values) {
			if ($values['lesson_id'] == $lesson_id) {
				$section = $value['section_id'];
			}
		}
	}
	return $section;
}


/**
 * Add product-id to WP query variables
 *
 * @param array $vars [WP query variables]
 * @return array $vars [WP query variables]
 */
function llms_add_query_var_product_id( $vars ) {
	$vars[] = 'product-id';
	return $vars;
}
add_filter( 'query_vars', 'llms_add_query_var_product_id' );

/**
 * Get url for when user cancels payment
 * @return string [url to redirect user to on form post]
 */
function llms_cancel_payment_url() {

	$cancel_payment_url = esc_url( get_permalink( llms_get_page_id( 'checkout' ) ) );

	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $cancel_payment_url );
}

/**
 * Get url for redirect when user confirms payment
 * @return string [url to redirect user to on form post]
 */
function llms_confirm_payment_url( $order_key = null ) {

	$confirm_payment_url = llms_get_endpoint_url( 'confirm-payment', '', get_permalink( llms_get_page_id( 'checkout' ) ) );

	$confirm_payment_url = add_query_arg( 'order', $order_key, $confirm_payment_url );

	return apply_filters( 'lifterlms_checkout_confirm_payment_url', $confirm_payment_url );
}

/**
 * Provide deprecation warnings
 *
 * Very similar to https://developer.wordpress.org/reference/functions/_deprecated_function/
 *
 * @param  string $function    name of the deprecated class or function
 * @param  string $version     version deprecation ocurred
 * @param  string $replacement function to use in it's place (optional)
 * @return void
 */
function llms_deprecated_function( $function, $version, $replacement = null ) {

	// only warn if debug is enabled
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

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
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {

			llms_log( $string );

		}

	}

}

/**
 * Sanitize text field
 * @param  string $var [raw text field input]
 * @return string [clean string]
 */
function llms_clean( $var ) {
	return sanitize_text_field( $var );
}

/**
 * Schedule expired membership cron
 * @return void
 */
function llms_expire_membership_schedule() {
	if ( ! wp_next_scheduled( 'llms_check_for_expired_memberships' )) {
		  wp_schedule_event( time(), 'daily', 'llms_check_for_expired_memberships' );
	}
}
add_action( 'wp', 'llms_expire_membership_schedule' );

/**
 * Expire Membership
 * @return void
 */
function llms_expire_membership() {
	global $wpdb;

	//find all memberships wth an expiration date
	$args = array(
	'post_type'     => 'llms_membership',
	'posts_per_page'  => 500,
	'meta_query'    => array(
	  'key' => '_llms_expiration_interval',
	  ),
	);

	$posts = get_posts( $args );

	if ( empty( $posts ) ) {
		return;
	}

	foreach ($posts as $post) {

		//make sure interval and period exist before continuing.
		$interval = get_post_meta( $post->ID, '_llms_expiration_interval', true );
		$period = get_post_meta( $post->ID, '_llms_expiration_period', true );

		if ( empty( $interval ) || empty( $period ) ) {
			return;
		}

		// query postmeta table and find all users enrolled
		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$meta_key_status = '_status';
		$meta_value_status = 'Enrolled';

		$results = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM '.$table_name.' WHERE post_id = %d AND meta_key = "%s" AND meta_value = %s ORDER BY updated_date DESC', $post->ID, $meta_key_status, $meta_value_status ) );

		for ($i = 0; $i < count( $results ); $i++) {
			$results[ $results[ $i ]->post_id ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		$enrolled_users = $results;

		foreach ( $enrolled_users as $user ) {

			$user_id = $user->user_id;
			$meta_key_start_date = '_start_date';
			$meta_value_start_date = 'yes';

			$start_date = $wpdb->get_results( $wpdb->prepare(
			'SELECT updated_date FROM '.$table_name.' WHERE user_id = %d AND post_id = %d AND meta_key = %s AND meta_value = %s ORDER BY updated_date DESC', $user_id, $post->ID, $meta_key_start_date, $meta_value_start_date) );

			//add expiration terms to start date
			$exp_date = date( 'Y-m-d',strtotime( date( 'Y-m-d', strtotime( $start_date[0]->updated_date ) ) . ' +'.$interval. ' ' . $period ) );

			// get current datetime
			$today = current_time( 'mysql' );
			$today = date( 'Y-m-d', strtotime( $today ) );

			//if a date parse causes exp date to be unmodified then return.
			if ( $exp_date == $start_date[0]->updated_date ) {
				LLMS_log( 'An error occured modifying the date value. Function: llms_expire_membership, interval: ' .  $interval . ' period: ' . $period );
				continue;
			}

			//compare expiration date to current date.
			if ( $exp_date < $today ) {
				$set_user_expired = array(
					'post_id' => $post->ID,
					'user_id' => $user_id,
					'meta_key' => '_status',
				);

				$status_update = array(
					'meta_value' => 'Expired',
					'updated_date' => current_time( 'mysql' ),
				);

				// change enrolled to expired in user_postmeta
				$update_user_meta = $wpdb->update( $table_name, $status_update, $set_user_expired );

				// remove membership id from usermeta array
				$users_levels = get_user_meta( $user_id, '_llms_restricted_levels', true );
				if ( in_array( $post->ID, $users_levels ) ) {
					$key = array_search( $post->ID, $users_levels );
					unset( $users_levels[ $key ] );

					update_user_meta( $user_id, '_llms_restricted_levels', $users_levels );
				}
			}

		}

	}

}
add_action( 'llms_check_for_expired_memberships', 'llms_expire_membership' );

/**
 * Get a coupon
 * @todo  deprecate (maybe...)
 * @return object [coupon session object]
 */
function llms_get_coupon() {
	$coupon = LLMS()->session->get( 'llms_coupon', array() );
	return $coupon;
}

/**
 * Get a list of available course / membership enrollment statuses
 * @return   array
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_enrollment_statuses() {
	return apply_filters( 'llms_get_enrollment_statuses', array(
		'cancelled' => __( 'Cancelled', 'lifterlms' ),
		'enrolled' => __( 'Enrolled', 'lifterlms' ),
		'expired' => __( 'Expired', 'lifterlms' ),
	) );
}

/**
 * Get the human readable (and translated) name of an enrollment status
 * @param    string     $status  enrollment status key
 * @return   string
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_enrollment_status_name( $status ) {
	$status = strtolower( $status ); // backwards compatibility
	$statuses = llms_get_enrollment_statuses();
	if ( is_array( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	}
	return apply_filters( 'lifterlms_get_enrollment_status_name ', $status );
}

/**
 * Get the most recently created coupon ID for a given code
 * @since   3.0.0
 * @version 3.0.0
 * @param   string $code        the coupon's code (title)
 * @param   int    $dupcheck_id an optional coupon id that can be passed which will be excluded during the query
 *                              this is used to dupcheck the coupon code during coupon creation
 * @return  int
 */
function llms_find_coupon( $code = '', $dupcheck_id = 0 ) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare(
		"SELECT id
		 FROM {$wpdb->posts}
		 WHERE post_title = %s
		 AND post_type = 'llms_coupon'
		 AND post_status = 'publish'
		 AND ID != %d
		 ORDER BY ID desc;
		",
		array( $code, $dupcheck_id )
	) );
}

/**
 * Retrive an IP Address for the current user
 * @source  WooCommerce WC_Geolocation::get_ip_address(), thank you <3
 *
 * @return string
 *
 * @since  3.0.0 [<description>]
 */
function llms_get_ip_address() {
	if ( isset( $_SERVER['X-Real-IP'] ) ) {
		return $_SERVER['X-Real-IP'];
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
		// Make sure we always only send through the first IP in the list which should always be the client IP.
		return trim( current( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		return $_SERVER['REMOTE_ADDR'];
	}
	return '';
}

/**
 * Retrieve an array of registered and available LifterLMS Order Post Statuses
 * @param  string  $order_type  filter stauses which are specific to the supplied order type, defaults to any statuses
 * @return array
 * @since  3.0.0
 */
function llms_get_order_statuses( $order_type = 'any' ) {

	$statuses = array(
		'llms-active'    => __( 'Active', 'lifterlms' ),
		'llms-cancelled' => __( 'Cancelled', 'lifterlms' ),
		'llms-completed' => __( 'Completed', 'lifterlms' ),
		'llms-expired'   => __( 'Expired', 'lifterlms' ),
		'llms-failed'    => __( 'Failed', 'lifterlms' ),
		'llms-pending'   => __( 'Pending', 'lifterlms' ),
		'llms-refunded'  => __( 'Refunded', 'lifterlms' ),
	);

	// remove types depending on order type
	switch ( $order_type ) {
		case 'recurring':
			unset( $statuses['llms-completed'] );
		break;

		case 'single':
			unset( $statuses['llms-active'] );
			unset( $statuses['llms-expired'] );
		break;
	}

	return apply_filters( 'llms_get_order_statuses', $statuses, $order_type );
}

/**
 * Get the human readable status for a LifterLMS status
 * @param  string $status LifterLMS Order Status
 * @return string
 *
 * @since  3.0.0
 */
function llms_get_order_status_name( $status ) {
	$statuses = llms_get_order_statuses();
	if ( is_array( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	}
	return apply_filters( 'lifterlms_get_order_status_name ', $status );
}

/**
 * Retrive an LLMS Order ID by the associated order_key
 * @param  string $key     the order key
 * @param  string $return  type of return, "order" for an instance of the LLMS_Order or "id" to return only the order ID
 * @return null|int        null if none found, order id if found
 *
 * @since  3.0.0
 */
function llms_get_order_by_key( $key, $return = 'order' ) {

	global $wpdb;

	$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_llms_order_key' AND meta_value = %s", $key ) );

	if ( 'order' === $return ) {
		return new LLMS_Order( $id );
	}

	return $id;

}


/**
 * Retrieve the WordPress Page ID of a LifterLMS Page
 * EG: 'checkout', 'memberships', etc...
 * @param  string $page name of the page
 * @return int
 */
function llms_get_page_id( $page ) {

	// normalize some pages to make more sense without having to migrate options
	if ( 'courses' === $page ) {
		$page = 'shop';
	}

	$page = apply_filters( 'lifterlms_get_' . $page . '_page_id', get_option( 'lifterlms_' . $page . '_page_id' ) );
	return $page ? absint( $page ) : -1;
}

/**
 * Retrive the URL for a LifterLMS Page
 * EG: 'checkout', 'memberships', etc...
 * @param  string $page name of the page
 * @param  array  $args optional array of query arguments that can be passed to add_query_arg()
 * @return string
 * @since  3.0.0
 */
function llms_get_page_url( $page, $args = array() ) {
	$url = add_query_arg( $args, get_permalink( llms_get_page_id( $page ) ) );
	return $url ? $url : '';
}

/**
 * Retrieve an array of existing transaction statuses
 * @return   array
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_transaction_statuses() {
	return apply_filters( 'llms_get_transaction_statuses', array(
		'llms-txn-failed',
		'llms-txn-pending',
		'llms-txn-refunded',
		'llms-txn-succeeded',
	) );
}

/**
 * Trim a string and append a suffix.
 * @param  string  $string  input string
 * @param  int     $chars   max number of characters
 * @param  string  $suffix  optionally append a suffix
 * @return string
 * @since  3.0.0
 * @version  3.0.0
 * @source thank you WooCommerce <3
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


function llms_are_terms_and_conditions_required() {

	$enabled = get_option( 'lifterlms_registration_require_agree_to_terms' );
	$page_id = get_option( 'lifterlms_terms_page_id', false );

	return ( $enabled && $page_id );

}


if ( ! function_exists( 'llms_agree_to_terms_form_field' ) ) {

	function llms_agree_to_terms_form_field( $echo = true ) {

		if ( llms_are_terms_and_conditions_required() ) {

			$page_id = get_option( 'lifterlms_terms_page_id', false );

			llms_form_field( array(
				'columns' => 12,
				'description' => '',
				'default' => 'no',
				'id' => 'llms_agree_to_terms',
				'label' => wp_kses( sprintf( _x( 'I have read and agree to the <a href="%s" target="_blank">%s</a>.', 'terms and conditions checkbox', 'lifterlms' ), get_the_permalink( $page_id ), get_the_title( $page_id ) ), array(
					'a' => array(
						'href' => array(),
						'target' => array(),
					),
					'b' => array(),
					'em' => array(),
					'i' => array(),
					'strong' => array(),
				) ),
				'last_column' => true,
				'required' => true,
				'type'  => 'checkbox',
				'value' => 'yes',
			), $echo );

		}
	}

}




function llms_form_field( $field = array(), $echo = true ) {

	$field = array_merge( array(
		'columns' => 12,
		'classes' => '',
		'description' => '',
		'default' => '',
		'id' => '',
		'label' => '',
		'last_column' => true,
		'match' => '',
		'name' => '',
		'options' => array(),
		'placeholder' => '',
		'required' => false,
		'selected' => '',
		'type'  => 'text',
		'value' => '',
		'wrapper_classes' => '',
	), $field );

	// setup the field value (if one exists)
	if ( '' !== $field['value'] ) {
		$field['value'] = $field['value'];
	} elseif ( '' !== $field['default'] ) {
		$field['value'] = $field['default'];
	}
	$value_attr = ( '' !== $field['value'] ) ? ' value="' . $field['value'] . '"' : '';

	// use id as the name if name isn't specified
	$field['name'] = ! $field['name'] ? $field['id'] : $field['name'];

	// duplicate label to placeholder if none is specified
		$field['placeholder'] = ! $field['placeholder'] ? $field['label'] : $field['placeholder'];
		$field['placeholder'] = wp_strip_all_tags( $field['placeholder'] );

	// add space to classes
	$field['wrapper_classes'] = ( $field['wrapper_classes'] ) ? ' ' . $field['wrapper_classes'] : '';
	$field['classes'] = ( $field['classes'] ) ? ' ' . $field['classes'] : '';

	// add column information to the warpper
	$field['wrapper_classes'] .= ' llms-cols-' . $field['columns'];
	$field['wrapper_classes'] .= ( $field['last_column'] ) ? ' llms-cols-last' : '';

	$desc = $field['description'] ? '<span class="llms-description">' . $field['description'] . '</span>' : '';

	// required attributes and content
	$required_char = apply_filters( 'lifterlms_form_field_required_character', '*', $field );
	$required_span = $field['required'] ? ' <span class="llms-required">' . $required_char . '</span>' : '';
	$required_attr = $field['required'] ? ' required="required"' : '';

	// setup the label
	$label = $field['label'] ? '<label for="' . $field['id'] . '">' . $field['label'] . $required_span. '</label>' : '';

	$r  = '<div class="llms-form-field type-' . $field['type'] . $field['wrapper_classes'] . '">';

	if ( 'hidden' !== $field['type'] && 'checkbox' !== $field['type'] && 'radio' !== $field['type'] ) {
		$r .= $label;
	}

	switch ( $field['type'] ) {

		case 'button':
		case 'reset':
		case 'submit':
			$r .= '<button class="llms-field-button' . $field['classes'] . '"id="' . $field['id'] . '" name="' . $field['name'] . '" type="' . $field['type'] . '">' . $field['value'] . '</button>';
			break;

		case 'checkbox':
		case 'radio':
			$checked = ( true === $field['selected'] ) ? ' checked="checked"' : '';
			$r .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" name="' . $field['name'] . '" type="' . $field['type'] . '"' . $checked . $required_attr . $value_attr . '>';
			$r .= $label;
			break;

		case 'html':
			$r .= '<div class="llms-field-html' . $field['classes'] . '" id="' . $field['id'] . '"></div>';
			break;

		case 'select':
			$r .= '<select class="llms-field-select' . $field['classes'] . '" id="' . $field['id'] . '" name="' . $field['name'] . '"' . $required_attr . '>';
			foreach ( $field['options'] as $k => $v ) {
				$r .= '<option value="' . $k . '"' . selected( $k, $field['value'], false ) . '>' . $v . '</option>';
			}
			$r .= '</select>';
			break;

		case 'textarea':
			$r .= '<textrea class="llms-field-textarea' . $field['classes'] . '" id="' . $field['id'] . '" name="' . $field['name'] . '" placeholder="' . $field['placeholder'] . '"' . $required_attr . '>' . $field['value'] . '</textarea>';
			break;

		default:
			$r .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" name="' . $field['name'] . '" placeholder="' . $field['placeholder'] . '" type="' . $field['type'] . '"' . $required_attr . $value_attr . '>';

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
 * Retrive the full path to the log file for a given log handle
 * @param    string  $handle  log handle
 * @return   string
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_log_path( $handle ) {
	return trailingslashit( LLMS_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';
}

/**
 * Log arbitrary messages to a log file
 * @param  mixed   $message   data to log
 * @param  string  $handle    allow creation of multiple log files by handle
 * @return boolean
 * @since  1.0.0
 * @version  3.0.0
 */
function llms_log( $message, $handle = 'llms' ) {

	$r = false;

	// open the file (creates it if it doesn't already exist)
	if ( $fh = fopen( llms_get_log_path( $handle ), 'a' ) ) {

		// print array or objects with print_r
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$r = fwrite( $fh, date_i18n( 'm-d-Y @ H:i:s -' ) . ' ' . $message . "\n" );

		fclose( $fh );

	}

	return $r;

}

/**
 * Create an array that can be passed to metabox select elements
 * configured as an llms-select2-post query-ier
 * @param    array      $post_ids  indexed array of WordPress Post IDs
 * @param    string     $template  an optional template to customize the way the results look
 *                                 {title} and {id} can be passed into the template
 *                                 and will be replaced with the post title and post id respectively
 * @return   array
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_make_select2_post_array( $post_ids = array(), $template = '' ) {

	if ( ! $template ) {
		$template = '{title} (' . __( 'ID#', 'lifterlms' ) . ' {id})';
	}

	if ( ! is_array( $post_ids ) ) {
		$post_ids = array( $post_ids );
	}

	$r = array();
	foreach ( $post_ids as $id ) {

		$title = str_replace( array( '{title}', '{id}' ), array( get_the_title( $id ), $id ), $template );

		$r[] = array(
			'key' => $id,
			'title' => $title,
		);
	}
	return apply_filters( 'llms_make_select2_post_array', $r, $post_ids );

}
