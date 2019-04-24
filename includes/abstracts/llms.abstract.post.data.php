<?php
/**
 * Defines base methods and properties for querying data about LifterLMS Custom Post Types
 *
 * @package LifterLMS/Abstracts
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Data abstract
 *
 * @since   [version]
 * @version [version]
 */
abstract class LLMS_Abstract_Post_Data {

	/**
	 * @var obj LLMS_Post_Model
	 * @since [version]
	 */
	public $post;

	/**
	 * @var int
	 * @since [version]
	 */
	public $post_id;

	/**
	 * @var array
	 * @since [version]
	 */
	protected $dates = array();

	/**
	 * @var int
	 * @since [version]
	 */
	protected $recent_events_per_page = 10;

	/**
	 * @var string
	 * @since [version]
	 */
	protected $recent_events_types = '';


	/**
	 * Constructor
	 * @param    int     $post_id  WP Post ID of the LLMS Post
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $post_id ) {

		$this->post_id = $post_id;
		$this->post    = llms_get_post( $this->post_id );

	}

	/**
	 * Allow dates and timestamps to be passed into various data functions
	 * @param    mixed     $date  date string or timestamp
	 * @return   int
	 * @since    [version]
	 * @version  [version]
	 */
	protected function strtotime( $date ) {
		if ( ! is_numeric( $date ) ) {
			$date = date( 'U', strtotime( $date ) );
		}
		return $date;
	}

	/**
	 * Retrieve a start or end date based on the period
	 * @param    string     $period  period [current|previous]
	 * @param    string     $date    date type [start|end]
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_date( $period, $date ) {

		return date( 'Y-m-d H:i:s', $this->dates[ $period ][ $date ] );

	}

	/**
	 * Set the dates passed on a date range period
	 * @param    string     $period  date range period
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_period( $period = 'today' ) {

		$now = current_time( 'timestamp' );

		switch ( $period ) {

			case 'all_time':
				$curr_start = 0;
				$curr_end   = $now;

				$prev_start = 0;
				$prev_end   = $now;
			break;

			case 'last_year':
				$curr_start = strtotime( 'first day of january last year', $now );
				$curr_end   = strtotime( 'last day of december last year', $now );

				$prev_start = strtotime( 'first day of january last year', $curr_start );
				$prev_end   = strtotime( 'last day of december last year', $curr_start );
			break;

			case 'year':
				$curr_start = strtotime( 'first day of january this year', $now );
				$curr_end   = strtotime( 'last day of december this year', $now );

				$prev_start = strtotime( 'first day of january last year', $now );
				$prev_end   = strtotime( 'last day of december last year', $now );
			break;

			case 'last_month':
				$curr_start = strtotime( 'first day of previous month', $now );
				$curr_end   = strtotime( 'last day of previous month', $now );

				$prev_start = strtotime( 'first day of previous month', $curr_start );
				$prev_end   = strtotime( 'last day of previous month', $curr_start );
			break;

			case 'month':
				$curr_start = strtotime( 'first day of this month', $now );
				$curr_end   = strtotime( 'last day of this month', $now );

				$prev_start = strtotime( 'first day of previous month', $now );
				$prev_end   = strtotime( 'last day of previous month', $now );
			break;

			case 'last_week':
				$curr_start = strtotime( 'monday this week', $now - WEEK_IN_SECONDS );
				$curr_end   = $now;

				$prev_start = strtotime( 'monday previous week', $curr_start - WEEK_IN_SECONDS );
				$prev_end   = $curr_start - DAY_IN_SECONDS;
			break;

			case 'week':
				$curr_start = strtotime( 'monday this week', $now );
				$curr_end   = $now;

				$prev_start = strtotime( 'monday previous week', $now );
				$prev_end   = $curr_start - DAY_IN_SECONDS;
			break;

			case 'yesterday':
				$curr_start = $now - DAY_IN_SECONDS;
				$curr_end   = $curr_start;

				$prev_start = $curr_start - DAY_IN_SECONDS;
				$prev_end   = $prev_start;
			break;

			case 'today':
			default:

				$curr_start = $now;
				$curr_end   = $now;

				$prev_start = $now - DAY_IN_SECONDS;
				$prev_end   = $prev_start;

		}// End switch().

		$this->dates = array(
			'current'  => array(
				'start' => strtotime( 'midnight', $curr_start ),
				'end'   => strtotime( 'tomorrow', $curr_end ) - 1,
			),
			'previous' => array(
				'start' => strtotime( 'midnight', $prev_start ),
				'end'   => strtotime( 'tomorrow', $prev_end ) - 1,
			),
		);

	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the quiz
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function recent_events() {

		$query_args = array(
			'per_page' => $this->recent_events_per_page,
			'post_id'  => $this->post_id,
		);

		if ( $this->recent_events_types ) {
			$query_args['types'] = $this->recent_events_types;
		}

		$query = new LLMS_Query_User_Postmeta( $query_args );

		return $query->get_metas();

	}

}
