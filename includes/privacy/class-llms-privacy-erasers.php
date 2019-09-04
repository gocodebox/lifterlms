<?php
/**
 * LifterLMS Privacy Eraser functions
 *
 * @since 3.18.0
 * @version 3.18.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Privacy Eraser functions
 *
 * @since 3.18.0
 * @since 3.30.3 Fixed spelling errors.
 */
class LLMS_Privacy_Erasers extends LLMS_Privacy {

	/**
	 * Erase student certificate data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function achievement_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$messages     = array();
		$achievements = self::get_student_achievements( $student );
		if ( $achievements ) {

			foreach ( $achievements as $achievement ) {
				$messages[] = sprintf( 'Achievement %d deleted.', $achievement->get( 'id' ) );
				$achievement->delete();
			}
		}

		return self::get_return( $messages, true, ( $messages ) );

	}

	/**
	 * Setup anonymous values for anonymized data
	 *
	 * @param    string $val   default anonymous value ('')
	 * @param    string $prop  key name of the property
	 * @param    obj    $obj   related object
	 * @return   mixed
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function anonymize_prop( $val, $prop, $obj = null ) {

		switch ( $prop ) {
			case 'user_id':
				$val = 0;
				break;
		}

		return $val;
	}

	/**
	 * Erase student certificate data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function certificate_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$messages = array();
		$certs    = self::get_student_certificates( $student );
		if ( $certs ) {

			foreach ( $certs as $cert ) {
				$messages[] = sprintf( 'Certificate %d deleted.', $cert->get( 'id' ) );
				$cert->delete();
			}
		}

		return self::get_return( $messages, true, ( $messages ) );

	}

	/**
	 * Return export data to an exporter
	 *
	 * @param    array $messages  array of messages
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function get_return( $messages = array(), $done = true, $removed = false, $retained = false ) {
		return array(
			'messages'       => $messages,
			'done'           => $done,
			'items_removed'  => $removed,
			'items_retained' => $retained,
		);
	}

	/**
	 * Erase notifications for a student
	 *
	 * @param    LLMS_Student $student
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function erase_notification_data( $student ) {

		$messages = array();
		global $wpdb;
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}lifterlms_notifications WHERE user_id = %d OR subscriber = %d",
				$student->get( 'id' ),
				$student->get( 'id' )
			)
		);

		if ( $deleted ) {

			/* Translators: %d = number of notifications */
			$messages[] = sprintf( __( 'Removed %d notifications.', 'lifterlms' ), $deleted );

		}

		return apply_filters( 'llms_privacy_erase_notification_data', $messages, $student );

	}

	/**
	 * Erase and anonymize an order
	 *
	 * @since 3.18.0
	 * @since 3.30.3 Fixed spelling error.
	 *
	 * @param LLMS_Order $order Order object.
	 * @return void
	 */
	private static function erase_order_data( $order ) {

		// cancel recurring orders
		if ( $order->is_recurring() && in_array( $order->get( 'status' ), array( 'llms-on-hold', 'llms-active', 'llms-pending-cancel' ) ) ) {
			$order->set_status( 'cancelled' );
			$order->add_note( __( 'Order cancelled during personal data erasure.', 'lifterlms' ) );
		}

		$props = array_keys( self::get_order_data_props( 'erasure' ) );
		foreach ( $props as $prop ) {

			$val = self::get_anon_prop_value( $prop );
			$order->set( $prop, $val );

		}

		$order->set( 'anonymized', 'yes' );
		$order->add_note( __( 'Personal data removed during personal data erasure.', 'lifterlms' ) );

	}

	/**
	 * Erase student data
	 *
	 * @param    LLMS_Student $student
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private static function erase_student_data( $student ) {

		$messages = array();

		$props = parent::get_student_data_props();

		foreach ( $props as $prop => $name ) {

			$erased = false;

			$val = $student->get( $prop );
			if ( $val ) {
				$student->set( $prop, '' );
				$erased = true;
			}

			if ( apply_filters( 'llms_privacy_erase_student_data_prop', $erased, $prop, $student ) ) {

				/* Translators: %s Prop name. */
				$messages[] = sprintf( __( 'Removed student "%s"', 'lifterlms' ), $name );

			}
		}

		return apply_filters( 'llms_privacy_erase_student_data', $messages, $student );

	}

	/**
	 * Erase student notification data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   [type]
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function notification_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$messages = self::erase_notification_data( $student );
		return self::get_return( $messages, true, ( $messages ) );

	}

	/**
	 * Erase student order data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function order_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$enabled = llms_parse_bool( get_option( 'llms_erasure_request_removes_order_data', 'no' ) );
		$orders  = self::get_student_orders( $student, $page );

		foreach ( $orders['orders'] as $order ) {

			if ( apply_filters( 'llms_privacy_erase_order_data', $enabled, $order ) ) {

				self::erase_order_data( $order );

				/* Translators: %d Order number. */
				$ret['messages'][]    = sprintf( __( 'Removed personal data from order #%d.', 'lifterlms' ), $order->get( 'id' ) );
				$ret['items_removed'] = true;

			} else {

				/* Translators: %d Order number. */
				$ret['messages'][]     = sprintf( __( 'Personal data within order #%d has been retained.', 'lifterlms' ), $order->get( 'id' ) );
				$ret['items_retained'] = true;

			}
		}

		$ret['done'] = isset( $orders['done'] ) ? $orders['done'] : true;

		return $ret;

	}

	/**
	 * Erase student postmeta data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   [type]
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function postmeta_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$messages = array();
		$enabled  = llms_parse_bool( get_option( 'llms_erasure_request_removes_lms_data', 'no' ) );

		if ( apply_filters( 'llms_privacy_erase_postmeta_data', $enabled, $attempt ) ) {

			global $wpdb;
			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %d",
					$student->get( 'id' )
				)
			);

			$ret['messages'][]    = __( 'Removed all student course and membership enrollment and activity data.', 'lifterlms' );
			$ret['items_removed'] = true;

		} else {

			$ret['messages'][]     = __( 'Retained all student course and membership enrollment and activity data.', 'lifterlms' );
			$ret['items_retained'] = true;

		}

		return $ret;

	}

	/**
	 * Erase student quiz attempt data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function quiz_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$enabled = llms_parse_bool( get_option( 'llms_erasure_request_removes_lms_data', 'no' ) );
		$query   = self::get_student_quizzes( $student, $page );

		foreach ( $query->get_attempts() as $attempt ) {

			if ( apply_filters( 'llms_privacy_erase_quiz_data', $enabled, $attempt ) ) {

				/* Translators: %d quiz attempt id. */
				$ret['messages'][]    = sprintf( __( 'Quiz attempt #%d removed.', 'lifterlms' ), $attempt->get_id() );
				$ret['items_removed'] = true;

				$attempt->delete();

			} else {

				/* Translators: %d quiz attempt id. */
				$ret['messages'][]     = sprintf( __( 'Quiz attempt #%d retained.', 'lifterlms' ), $attempt->get_id() );
				$ret['items_retained'] = true;

			}
		}

		$ret['done'] = $query->has_results() ? $query->is_last_page() : true;

		return $ret;

	}

	/**
	 * Erase student data by email address
	 *
	 * @param    string $email_address  email address of the user to retrieve data for
	 * @param    int    $page           process page number
	 * @return   [type]
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public static function student_data( $email_address, $page ) {

		$ret = self::get_return();

		$student = parent::get_student_by_email( $email_address );
		if ( ! $student ) {
			return $ret;
		}

		$messages = self::erase_student_data( $student );
		return self::get_return( $messages, true, ( $messages ) );

	}

}
