<?php
/**
 * LifterLMS Notification Backgroung Processor Abstract
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
	 * Instance of the current LLMS_Notification
	 * @var  null
	 */
	private $the_notification = null;

	/**
	 * Get an email address for the "to" field for the current subscriber
	 * @return   string|false
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_to_address() {

		$subscriber = $this->the_notification->get( 'subscriber' );

		$email = $subscriber;
		$name = false;

		if ( is_numeric( $subscriber ) ) {

			$student = new LLMS_Student( $subscriber );
			$name = $student->get_name();
			$email = $student->get( 'user_email' );

		}

		$email = filter_var( $email, FILTER_VALIDATE_EMAIL );

		// not a valid email...
		if ( ! $email ) {
			return false;
		}

		// send a name & email instead of just an email
		if ( $name ) {
			$email = sprintf( '%1$s <%2$s>', $name, $email );
		}

		return apply_filters( $this->action . '_get_to_address', $email );

	}

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

		$this->the_notification = new LLMS_Notification( $notification_id );

		$view = $this->the_notification->get_view();

		$to = $this->get_to_address();

		if ( $to ) {

			$subject = $view->get_title();
			// $message = $view->get_html();
			$headers = array(
				'Content-Type: text/html',
			);

			ob_start();
			llms_get_template( 'emails/header-new.php', array( 'content' => $view->get_html() ) );
			$message = ob_get_clean();

			// log when wp_mail fails
			if ( wp_mail( $to, $subject, $message, $headers ) ) {
				$this->the_notification->set( 'status', 'sent' );
			} else {
				$this->log( sprintf( 'error sending email notification ID #%d', $notification_id ) );
			}

		}

		sleep( 5 );

		return false;

	}

}

return new LLMS_Notification_Processor_Email();

