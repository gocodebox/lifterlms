<?php
/**
 * Defines base methods and properties for querying data about LifterLMS Custom Post Types.
 *
 * @package LifterLMS/Abstracts
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Object_Data abstract.
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Object_Data {

	/**
	 * Object ID
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	protected $object_id;

	/**
	 * Object
	 *
	 * @since [version]
	 *
	 * @var obj
	 */
	protected $object;

	/**
	 * Dates for the current period/range.
	 *
	 * Migrated from LLMS_Abstract_Post_Data.
	 *
	 * @since [version]
	 *
	 * @var array
	 */
	protected $dates = array();

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @param int $object_id Object ID.
	 */
	public function __construct( $object_id ) {
		$this->object_id = $object_id;
		$this->object    = $this->set_object( $object_id );
	}

	/**
	 * Retrieve an object from the Object ID passed to the constructor
	 *
	 * @since [version]
	 *
	 * @param int $object_id Object ID.
	 * @return obj
	 */
	abstract protected function set_object( $object_id );

	/**
	 * Retrieve a start or end date based on the period.
	 *
	 * Migrated from LLMS_Abstract_Post_Data.
	 *
	 * @since [version]
	 *
	 * @param  string $period Period [current|previous].
	 * @param  string $date   The date type [start|end].
	 * @return string The start or end date in the format 'Y-m-d H:i:s'.
	 */
	public function get_date( $period, $date ) {

		return date( 'Y-m-d H:i:s', $this->dates[ $period ][ $date ] );

	}

	/**
	 * Retrieve the instance's object.
	 *
	 * @since [version]
	 *
	 * @return obj
	 */
	public function get_object() {
		return $this->object;
	}

	/**
	 * Retrieve the instance's object id.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Set the dates passed on a date range period
	 *
	 * Migrated from LLMS_Abstract_Post_Data.
	 *
	 * @since [version]
	 * @since [version] Fixed date calculation error when displaying results for "Last Week".
	 *
	 * @param  string $period Date range period.
	 * @return void
	 */
	public function set_period( $period = 'today' ) {

		$now = llms_current_time( 'timestamp' );

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
				$curr_start = strtotime( 'monday previous week', $now );
				$curr_end   = strtotime( 'sunday previous week', $now );

				$prev_start = strtotime( 'monday previous week', $curr_start );
				$prev_end   = strtotime( 'sunday previous week', $curr_start );
				break;

			case 'week':
				$curr_start = strtotime( 'monday this week', $now );
				$curr_end   = strtotime( 'sunday this week', $now );

				$prev_start = strtotime( 'monday previous week', $now );
				$prev_end   = strtotime( 'sunday previous week', $now );
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

		}

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
	 * Allow dates and timestamps to be passed into various data functions.
	 *
	 * Migrated from LLMS_Abstract_Post_Data.
	 *
	 * @since [version]
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

}
