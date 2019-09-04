<?php
/**
 * Defines base methods and properties for querying data about LifterLMS Custom Post Types.
 *
 * @package LifterLMS/Abstracts
 *
 * @since   3.31.0
 * @version 3.31.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Data abstract.
 *
 * @since 3.31.0
 */
abstract class LLMS_Abstract_Post_Data {

	/**
	 * LLMS Post instance.
	 *
	 * @since 3.31.0
	 * @var LLMS_Post_Model
	 */
	protected $post;

	/**
	 * LLMS Post ID.
	 *
	 * @since 3.31.0
	 * @var int
	 */
	protected $post_id;

	/**
	 * @since 3.31.0
	 * @var array
	 */
	protected $dates = array();

	/**
	 * Constructor.
	 *
	 * @since 3.31.0
	 *
	 * @param int $post_id WP Post ID of the LLMS Post.
	 */
	public function __construct( $post_id ) {

		$this->post_id = $post_id;
		$this->post    = llms_get_post( $this->post_id );

	}

	/**
	 * Retrieve the instance of the LLMS_Post_Model.
	 *
	 * @since 3.31.0
	 *
	 * @return LLMS_Post_Model The instance of the LLMS_Post_Model.
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * Retrieve the LLMS_Post_Model ID.
	 *
	 * @since 3.31.0
	 *
	 * @return int The LLMS_Post_Model ID.
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Allow dates and timestamps to be passed into various data functions.
	 *
	 * @since 3.31.0
	 *
	 * @param  mixed $date A date string or timestamp.
	 * @return int The Unix timestamp of the given date.
	 */
	protected function strtotime( $date ) {
		if ( ! is_numeric( $date ) ) {
			$date = date( 'U', strtotime( $date ) );
		}
		return $date;
	}

	/**
	 * Retrieve a start or end date based on the period.
	 *
	 * @since 3.31.0
	 *
	 * @param  string $period Period [current|previous].
	 * @param  string $date   The date type [start|end].
	 * @return string The start or end date in the format 'Y-m-d H:i:s'.
	 */
	protected function get_date( $period, $date ) {

		return date( 'Y-m-d H:i:s', $this->dates[ $period ][ $date ] );

	}

	/**
	 * Set the dates passed on a date range period
	 *
	 * @since 3.31.0
	 *
	 * @param  string $period Date range period.
	 * @return void
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
	 *
	 * @since 3.31.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments to feed the LLMS_Query_User_Postmeta with.
	 *
	 *     @type int          $per_page The number of posts to query for. Default 10.
	 *     @type array|string $types    Array of strings for the type of events to fetch, or a string to fetch them all. Default 'all'.
	 *                                  @see LLMS_Query_User_Postmeta::parse_args()
	 * }
	 * @return array Array of LLMS_User_Postmetas.
	 */
	public function recent_events( $args = array() ) {

		$query_args = wp_parse_args(
			$args,
			array(
				'per_page' => 10,
				'types'    => 'all',
			)
		);

		$query_args['post_id'] = $this->post_id;

		$query = new LLMS_Query_User_Postmeta( $query_args );

		return $query->get_metas();

	}

}
