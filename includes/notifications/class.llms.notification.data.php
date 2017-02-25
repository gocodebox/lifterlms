<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Data {

	public function __construct( $notification ) {

		if ( is_numeric( $notification ) ) {
			$this->id = $notification;
		} elseif ( is_array( $notification ) && isset( $notification['user_id'] ) && isset( $notification['type'] ) && isset( $notification['metas'] ) ) {
			$this->id = $this->create( $notification['user_id'], $notification['type'], $notification['metas'] );
		}

	}

	public function create( $user_id, $type, $metas = array() ) {

		$time = current_time( 'mysql' );

		global $wpdb;
		$insert = $wpdb->insert( $this->get_table(),
			array(
				'created' => $time,
				'updated' => $time,
				'status' => 'new',
				'user_id' => $user_id,
				'type' => $type,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( 1 !== $insert ) {
			return false;
		}

		$this->id = $wpdb->insert_id;

		if ( $metas ) {
			$this->create_metas( $metas );
		}

		return $this->id;

	}

	public function create_metas( $metas = array() ) {

		global $wpdb;

		$values = array();
		foreach( $metas as $key => $val ) {
			$values[] = $wpdb->prepare( '( %d, %s, %s )', $this->id, $key, $val );
		}
		$values = implode( ', ', $values );

		return $wpdb->query( "INSERT INTO {$this->get_table_meta()} (notification_id, meta_key, meta_value) VALUES{$values};" );

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

	private function get_table() {
		global $wpdb;
		return $wpdb->prefix . 'lifterlms_notifications';
	}

	private function get_table_meta() {
		return $this->get_table() . '_meta';
	}

	public function meta_exists( $key ) {

		$query = $wpdb->prepare( "SELECT meta_value FROM {$this->get_table_meta()} WHERE notification_id = %d AND meta_key = %s", $this->id, $key );
		return ( $wpdb->get_var( $query ) );

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
				if ( ! $this->meta_exists( $key ) ) {
					return $this->create_metas( array(
						$key => $val,
					) );
				}

				$query = $wpdb->prepare( "UPDATE {$this->get_table_meta()} SET meta_value=%s WHERE notification_id = %d AND meta_key = %s", $val, $this->id, $key );

		}

		return $wpdb->query( $query );

	}



}
