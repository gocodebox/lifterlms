<?php
/**
 * Admin Achievements Table
 *
 * @since   3.2.0
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Achievements extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'achievements';

	/**
	 * Instance of LLMS_Student
	 * @var  null
	 */
	protected $student = null;

	/**
	 * Retrieve data for the columns
	 * @param    string     $key   the column id / key
	 * @param    mixed      $data  object of achievment data
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_data( $key, $data ) {

		switch ( $key ) {

			case 'related':
				if ( $data->post_id && 'llms_achievement' !== get_post_type( $data->post_id ) ) {
					if ( is_numeric( $data->post_id ) ) {
						$value = $this->get_post_link( $data->post_id, get_the_title( $data->post_id ) );
					} else {
						$value = $data->post_id;
					}
				} else {
					$value = '&ndash;';
				}
			break;

			case 'earned':
				$value = date_i18n( 'F j, Y', strtotime( $data->earned_date ) );
			break;

			case 'id':
				// prior to 3.2 this data wasn't recorded
				$template = get_post_meta( $data->achievement_id, '_llms_achievement_template', true );
				if ( $template ) {
					$value = $this->get_post_link( $template );
				} else {
					$value = $data->achievement_id;
				}
			break;

			case 'image':
				$value = wp_get_attachment_image( get_post_meta( $data->achievement_id, '_llms_achievement_image', true ), array( 64, 64 ) );
			break;

			case 'name':
				$value = get_post_meta( $data->achievement_id, '_llms_achievement_title', true );
			break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $data );

	}

	public function get_results( $args = array() ) {

		$args = $this->clean_args( $args );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		$this->tbody_data = $this->student->get_achievements();

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
	protected function set_columns() {
		return array(
			'id' => __( 'ID', 'lifterlms' ),
			'name' => __( 'Achievement Title', 'lifterlms' ),
			'image' => __( 'Image', 'lifterlms' ),
			'earned' => __( 'Earned Date', 'lifterlms' ),
			'related' => __( 'Related Post', 'lifterlms' ),
		);
	}

	/**
	 * Empty message displayed when no results are found
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function set_empty_message() {
		return __( 'This student has not yet earned any achievements.', 'lifterlms' );
	}

}
