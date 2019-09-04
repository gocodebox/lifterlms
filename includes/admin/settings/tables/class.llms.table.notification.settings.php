<?php
/**
 * Student Management table on Courses and Memberships
 *
 * @since   3.8.0
 * @version 3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Table_NotificationSettings extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'notifications';

	/**
	 * If true will be a table with a larger font size
	 *
	 * @var  bool
	 */
	protected $is_large = true;

	/**
	 * Retrieve data for the columns
	 *
	 * @param    string $key        the column id / key
	 * @param    int    $user_id    WP User ID
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_data( $key, $data ) {

		switch ( $key ) {

			case 'configure':
				$links = array();
				foreach ( $data['configure'] as $type => $name ) {
					$url     = esc_url(
						add_query_arg(
							array(
								'notification' => $data['id'],
								'type'         => $type,
							)
						)
					);
					$links[] = '<a href="' . $url . '">' . $name . '</a>';
				}
				$value = implode( ', ', $links );
				break;

			default:
				$value = $data[ $key ];

		}

		return $this->filter_get_data( $value, $key, $data );

	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @param    array $args  array of query args
	 * @return   void
	 * @since    3.8.0
	 * @version  3.10.0
	 */
	public function get_results( $args = array() ) {

		$rows = array();

		foreach ( LLMS()->notifications()->get_controllers() as $controller ) {

			$rows[] = array(
				'id'           => $controller->id,
				'notification' => $controller->get_title(),
				'configure'    => $controller->get_supported_types(),
			);

		}

		usort( $rows, array( $this, 'sort_rows' ) );

		$this->tbody_data = $rows;
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set_columns() {
		$cols = array(
			'notification' => __( 'Notification', 'lifterlms' ),
			'configure'    => __( 'Configure', 'lifterlms' ),
		);

		return $cols;
	}

	/**
	 * Sorting function to display all loaded notifications in alphabetical order
	 *
	 * @param    array $row_a  first row to compare
	 * @param    array $row_b  second row to compare
	 * @return   int
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function sort_rows( $row_a, $row_b ) {
		return strcmp( $row_a['notification'], $row_b['notification'] );
	}

}
