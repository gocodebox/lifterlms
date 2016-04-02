<?php
/**
* Analytics Widget Abstract
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Analytics_Widget {



	public $success = false;
	public $message = '';
	public $response;

	protected $date_start;
	protected $date_end;

	protected $query;
	protected $query_vars;
	protected $query_function;
	protected $prepared_query;

	private $results = array();


	abstract protected function format_response();
	abstract protected function set_query();

	public function __construct() {}

	protected function get_posted_dates() {

		return ( isset( $_POST['dates'] ) ) ? $_POST['dates'] : '';


	}

	protected function get_prepared_query() {
		return $this->prepared_query;
	}

	protected function get_query() {
		return $this->query;
	}

	protected function get_query_vars() {
		return $this->query_vars;
	}

	protected function get_results() {
		return $this->results;
	}



	protected function format_date( $date, $type ) {

		switch ( $type ) {

			case 'start':

				$date .= ' 00:00:00';

			break;

			case 'end':

				$date .= ' 23:23:59';

			break;

		}

		return $date;

	}


	protected function is_error() {

		return ( $this->success ) ? false : true;

	}


	private function query() {

		global $wpdb;

		$this->results = $wpdb->{$this->query_function}( $wpdb->prepare( $this->query, $this->query_vars ) );

		$this->prepared_query = $wpdb->last_query;

		if ( ! $wpdb->last_error ) {

			$this->success = true;
			$this->message = 'success';

		} else {

			$this->message = $wpdb->last_error;

		}

	}


	public function output() {

		$this->set_query();
		$this->query();
		$this->response = $this->format_response();

		header( 'Content-Type: application/json' );
		echo json_encode( $this );
		wp_die();

	}


}
