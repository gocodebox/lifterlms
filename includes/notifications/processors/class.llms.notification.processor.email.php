<?php
/**
 * Notification Background Processor: Emails
 *
 * @package LifterLMS/Notifications/Processors/Classes
 *
 * @since 3.8.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Background Processor: Emails
 *
 * @since 3.8.0
 * @since 3.10.1 Unknown.
 * @since 3.33.2 Improve data logged during errors.
 */
class LLMS_Notification_Processor_Email extends LLMS_Abstract_Notification_Processor {

	/**
	 * action name
	 *
	 * @var  string
	 */
	protected $action = 'llms_notification_processor_email';

	/**
	 * Processes an item in the queue.
	 *
	 * @since 3.8.0
	 * @since 3.10.1 Unknown.
	 * @since 3.33.2 Log additional data during errors.
	 * @since 7.1.0 Catch possible fatals and in that case remove from the queue the item that produced them.
	 *
	 * @param int $notification_id ID of an LLMS_Notification.
	 * @return bool `false` removes item from queue, `true` retain for further processing.
	 */
	protected function task( $notification_id ) {

		$this->log( sprintf( 'sending email notification ID #%d', $notification_id ) );
		try {

			$notification = new LLMS_Notification( $notification_id );

			$view = $notification->get_view();

			if ( ! $view ) {
				$this->log( 'ID#' . $notification_id );
				return false;
			}

			// Setup the email.
			$mailer = llms()->mailer()->get_email( 'notification' );

			if ( ! $mailer->add_recipient( $notification->get( 'subscriber' ), 'to' ) ) {
				$this->log( sprintf( 'Error sending email notification ID #%d - subscriber does not exist', $notification_id ) );
				$this->log( $notification->toArray() );
				$notification->set( 'status', 'error' );
				return false;
			}

			$mailer->set_subject( $view->get_subject() )->set_heading( $view->get_title() )->set_body( $view->get_html() );

		} catch ( Error $e ) {
			$this->log( sprintf( 'Error sending email notification ID #%d', $notification_id ) );
			$this->log( sprintf( 'Error caught %1$s in %2$s on line %3$s', $e->getMessage(), $e->getFile(), $e->getLine() ) );
			$notification->set( 'status', 'error' );
			return false;
		}

		// Log when wp_mail fails.
		if ( $mailer->send() ) {
			$notification->set( 'status', 'sent' );
		} else {
			$this->log( sprintf( 'Error sending email notification ID #%d', $notification_id ) );
			$this->log( $notification->toArray() );
		}

		return false;

	}

}

return new LLMS_Notification_Processor_Email();
