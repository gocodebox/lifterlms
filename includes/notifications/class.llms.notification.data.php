<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Data {

	public function __construct( $notification_id ) {

		if ( $notification_id ) {
			$this->id = $notification_id;
		}

	}

	private function get_table() {
		global $wpdb;
		return $wpdb->prefix . 'lifterlms_notifications';
	}

	private function get_table_meta() {
		return $this->get_table() . '_meta';
	}



	public function get( $key ) {

		global $wpdb;

		switch ( $key ) {

			case 'id':
				return $this->id;
			break;

			case 'created':
			case 'updated':
			case 'status':
			case 'user_id':
			case 'type':
				$query = $wpdb->prepare( "SELECT {$key} FROM {$this->get_table()} WHERE id = %d", $this->id );
			break;

			default:
				$query = $wpdb->prepare( "SELECT meta_value FROM {$this->get_table_meta()} WHERE notification_id = %d AND meta_key = %s", $this->id, $key );

		}

		return $wpdb->get_var( $query );

	}


	public function set( $key, $val ) {

		global $wpdb;

		switch ( $key ) {

			case 'created':
			case 'updated':
				return false;
			break;

			case 'id':
				$this->id = $val;
				return true;
			break;

			case 'status':
			case 'user_id':
			case 'type':
				$query = $wpdb->prepare( "UPDATE {$this->get_table()} SET {$key} = %s, updated = %s WHERE id = %d", $val, current_time( 'mysql' ), $this->id );
			break;

			default:
				$query = $wpdb->prepare( "UPDATE {$this->get_table_meta()} SET meta_value=%s WHERE notification_id = %d AND meta_key = %s", $val, $this->id, $key );

		}

		return $wpdb->get_var( $query );

	}

}
