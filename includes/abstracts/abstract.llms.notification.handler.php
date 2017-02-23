<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Notification_Handler {

	public $id = '';

	abstract public function handle( $notification );

	public function __construct() {}


	protected function create( $user_id, $metas = array() ) {

		$time = current_time( 'mysql' );

		global $wpdb;
		$insert = $wpdb->insert( $wpdb->prefix . 'lifterlms_notifications', array(
			'created' => $time,
			'updated' => $time,
			'status' => 0,
			'user_id' => $user_id,
			'type' => $this->id,
		),
		array(
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
		) );

		if ( 1 !== $insert ) {
			return false;
		}

		$notification_id = $wpdb->insert_id;

		if ( $metas ) {
			foreach ( $metas as $key => $val ) {
				if ( $val ) {
					$this->create_meta( $notification_id, $key, $val );
				}
			}
		}

		return $notification_id;

	}

	protected function create_meta( $notification_id, $meta_key, $meta_value ) {

		global $wpdb;
		$insert = $wpdb->insert( $wpdb->prefix . 'lifterlms_notifications_meta', array(
			'notification_id' => $notification_id,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value,
		),
		array(
			'%d',
			'%s',
			'%s',
		) );

		return ( 1 === $insert ) ? $wpdb->insert_id : false;

	}

}

