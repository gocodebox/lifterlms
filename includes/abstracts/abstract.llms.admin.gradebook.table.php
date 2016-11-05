<?php
/**
 * Admin GradeBook Tables
 *
 * @since   3.2.0
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Admin_GradeBook_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	public $id = '';

	/**
	 * Retrieve data for the columns
	 * @param    string     $key   the column id / key
	 * @param    mixed      $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	abstract public function get_data( $key, $data );

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	abstract protected function set_columns();



	/**
	 * Ensures that all data requested by $this->get_data if filterable
	 * before being output on screen
	 * @param    mixed     $value  value to be displayed
	 * @param    string    $key    column key / id
	 * @param    mixed     $data   original data object / array
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function filter_get_data( $value, $key, $data ) {
		return apply_filters( 'llms_gradebook_get_data_' . $this->id, $value, $key, $data );
	}

	/**
	 * Retrieve the array of columns defined by set_columns
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_columns() {
		return apply_filters( 'llms_gradebook_get_' . $this->id . '_columns', $this->set_columns() );
	}

	/**
	 * Get the HTML for a WP Post Link
	 * @param    int        $post_id  WP Post ID
	 * @param    string     $text     Optional text to display within the anchor, if none supplied $post_id if used
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_post_link( $post_id, $text = '' ) {
		if ( ! $text ) {
			$text = $post_id;
		}
		return '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . $text . '</a>';
	}

}
