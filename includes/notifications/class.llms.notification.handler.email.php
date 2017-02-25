<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Handler_Email extends LLMS_Notification_Handler {

	public $id = 'email';

	public function __construct() {
		parent::__construct();
		add_action( 'llms_send_email_notifications', array( $this, 'send_emails' ) );
	}

	public function handle( $notification ) {

		$notifications = parent::handle( $notification );
		foreach ( $notifications as $nid ) {

			$data = new LLMS_Notification_Data( $nid );

		}

	}



	public function send() {



	}



}
