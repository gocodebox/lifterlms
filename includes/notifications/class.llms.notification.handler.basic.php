<?php

class LLMS_Notification_Handler_Basic extends LLMS_Notification_Handler {

	public $id = 'basic';

	public function handle( $notification ) {

		foreach ( array_keys( $notification->get_subscribers( $this->id ) ) as $uid ) {

			$this->create( $uid, array(
				'notification' => $notification->id,
				'body' => $notification->get_body( $uid, $this->id ),
				'icon' => $notification->get_icon( $uid, $this->id ),
				'title' => $notification->get_title( $uid, $this->id ),
			) );

		}

	}

}
