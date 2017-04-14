<?php
/**
 * Notification Background Processor: Emails
 *
 * @since    [version]
 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
	 */
	protected function task( $notification_id ) {

		$this->log( sprintf( 'sending email notification ID #%d', $notification_id ) );

		$notification = new LLMS_Notification( $notification_id );

		$view = $notification->get_view();

		// setup the email
		$mailer = LLMS()->mailer()->get_email( 'notification' );
		$mailer->add_recipient( $notification->get( 'subscriber' ), 'to' );
		$mailer->set_body( $view->get_html() )->set_subject( $view->get_title() );

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

