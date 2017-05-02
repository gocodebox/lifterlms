<?php
/**
 * Individual Student's Memberships Table
 *
 * @since   3.2.0
 * @version 3.7.5
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Student_Memberships extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'student-memberships';

	/**
	 * Instance of LLMS_Student
	 * @var  null
	 */
	protected $student = null;

	/**
	 * Retrieve data for the columns
	 * @param    string     $key            the column id / key
	 * @param    int        $membership_id  ID of the membership
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.7.5
	 */
	public function get_data( $key, $membership_id ) {

		switch ( $key ) {

			case 'id':
				$value = $this->get_post_link( $membership_id );
			break;

			case 'name':
				$value = get_the_title( $membership_id );
			break;

			case 'status':
				$value = llms_get_enrollment_status_name( $this->student->get_enrollment_status( $membership_id ) );
			break;

			case 'enrolled':
				$value = $this->student->get_enrollment_date( $membership_id, 'enrolled' );
			break;

			default:
				$value = $key;

		}

		return $this->filter_get_data( $value, $key, $membership_id );

	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_results( $args = array() ) {

		$args = $this->clean_args( $args );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		$this->tbody_data = $this->student->get_membership_levels();

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    2.3.0
	 * @version  2.3.0
	 */
	public function set_args() {
		return array(
			'student' => ! empty( $this->student ) ? $this->student->get_id() : absint( $_GET['student_id'] ),
		);
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function set_columns() {
		return array(
			'id' => array(
				'title' => __( 'ID', 'lifterlms' ),
			),
			'name' => array(
				'title' => __( 'Name', 'lifterlms' ),
			),
			'status' => array(
				'title' => __( 'Status', 'lifterlms' ),
			),
			'enrolled' => array(
				'title' => __( 'Enrolled', 'lifterlms' ),
			),
		);
	}

	/**
	 * Empty message displayed when no results are found
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function set_empty_message() {
		return __( 'This student is not enrolled in any memberships.', 'lifterlms' );
	}

}
