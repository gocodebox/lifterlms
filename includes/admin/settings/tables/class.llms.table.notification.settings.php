<?php
/**
 * Student Management table on Courses and Memberships
 *
 * @since   ??
 * @version ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_NotificationSettings extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'notifications';

	/**
	 * Retrieve data for the columns
	 * @param    string     $key        the column id / key
	 * @param    int        $user_id    WP User ID
	 * @return   mixed
	 * @since    ??
	 * @version  ??
	 */
	public function get_data( $key, $data ) {

		switch ( $key ) {

			case 'configure':
				$url = esc_url( add_query_arg( array(
					'notification' => $data['id'],
					'type' => $data['type'],
				) ) );
				$value = '<a class="llms-button-secondary small" href="' . $url . '"><span class="dashicons dashicons-admin-generic"></span></a>';
			break;

			default:
				$value = $data[ $key ];

		}

		return $this->filter_get_data( $value, $key, $data );

	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function get_results( $args = array() ) {

		$rows = array();

		foreach ( LLMS()->notifications()->get_controllers() as $controller ) {

			$base = array(
				'id' => $controller->id,
				'name' => $controller->get_title(),
				'subscribers' => '',
				'type' => '',
			);

			foreach ( $controller->get_supported_types() as $type ) {
				$base['type'] = $type;
				// $base['subscribers'] = $controller->get_subscriber_options( $type );
				$rows[] = $base;
			}

		}

		$this->tbody_data = $rows;
	}


	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    ??
	 * @version  ??
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    ??
	 * @version  ??
	 */
	public function set_columns() {
		$cols = array(
			'name' => __( 'Name', 'lifterlms' ),
			'type' => __( 'Type', 'lifterlms' ),
			'subscribers' => __( 'Subscribers', 'lifterlms' ),
			'configure' => __( 'Configure', 'lifterlms' )
		);

		return $cols;
	}

}
