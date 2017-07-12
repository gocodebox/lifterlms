<?php
/**
 * Notification Background Processor: Emails
 *
 * @since    3.8.0
 * @version  3.10.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Processor_Email extends LLMS_Abstract_Notification_Processor {

	/**
	 * action name
	 * @var  string
	 */
	protected $action = 'llms_notification_processor_email';

	/**
	 * Processes an item in the queue
	 * @param    int     $notification_id  ID of an LLMS_Notification
	 * @return   boolean                   false removes item from the queue
	 *                                     true leaves it in the queue for further processing
	 * @since    3.8.0
	 * @version  3.10.1
	 */
	protected function task( $notification_id ) {

		$this->log( sprintf( 'sending email notification ID #%d', $notification_id ) );

		$notification = new LLMS_Notification( $notification_id );

		$view = $notification->get_view();

		if ( ! $view ) {
			$this->log( 'ID#' . $notification_id );
			return false;
		}

		// setup the email
		$mailer = LLMS()->mailer()->get_email( 'notification' );

		if ( ! $mailer->add_recipient( $notification->get( 'subscriber' ), 'to' ) ) {
			$this->log( sprintf( 'error sending email notification ID #%d - subscriber does not exist', $notification_id ) );
			$notification->set( 'status', 'error' );
			return false;
		}

		$mailer->set_subject( $view->get_subject() )->set_heading( $view->get_title() )->set_body( $view->get_html() );

		// log when wp_mail fails
		if ( $mailer->send() ) {
			$notification->set( 'status', 'sent' );
		} else {
			$this->log( sprintf( 'error sending email notification ID #%d', $notification_id ) );
		}

		return false;

	}

}

return new LLMS_Notification_Processor_Email();

