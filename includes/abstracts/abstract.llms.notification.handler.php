<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Notification_Handler {

	public $id = '';

	public function __construct() {}

	public function handle( $notification ) {

		return $this->create_for_subscribers( $notification );

	}

	protected function create_for_subscribers( $notification ) {

		$created = array();

		foreach ( array_keys( $notification->get_subscribers( $this->id ) ) as $uid ) {

			$notification_id = $this->create_for_subscriber( $uid, array(
				'notification' => $notification->id,
				'body' => $notification->get_body( $uid, $this->id ),
				'icon' => $notification->get_icon( $uid, $this->id ),
				'title' => $notification->get_title( $uid, $this->id ),
			) );

			if ( is_numeric( $notification_id ) ) {

				$created[] = $notification_id;

			}

		}

		return $created;

	}

	protected function create_for_subscriber( $user_id, $metas = array() ) {

		$data = new LLMS_Notification_Data( array(
			'metas' => $metas,
			'type' => $this->id,
			'user_id' => $user_id,
		) );

		return $data->get( 'id' );

	}

}

