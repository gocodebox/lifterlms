<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS User Postmeta Query
 * @since    3.15.0
 * @version  3.15.0
 */
class LLMS_Query_User_Postmeta extends LLMS_Database_Query {


	/**
	 * Identify the extending query
	 * @var  string
	 */
	protected $id = 'user_postmeta';

	/**
	 * Retrieve default arguments for a student query
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function get_default_args() {

		$args = array(
			'include_post_children' => true,
			'query' => array(),
			'query_compare' => 'OR',
			'post_id' => array(),
			'sort' => array(
				'updated_date' => 'DESC',
				'meta_id' => 'ASC',
			),
			'types' => array(),
			'user_id' => array(),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_User_Postmetas for the given set of results
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_metas() {

		$metas = array();
		$results = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$metas[] = new LLMS_User_Postmeta( $result->meta_id );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $metas;
		}

		return apply_filters( $this->get_filter( 'get_metas' ), $metas );

	}

	/**
	 * Parses data passed to $statuses
	 * Convert strings to array and ensure resulting array contains only valid statuses
	 * If no valid statuses, returns to the default
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function parse_args() {

		// sanitize post & user ids
		foreach ( array( 'post_id', 'user_id' ) as $key ) {

			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );

		}

		if ( $this->arguments['include_post_children'] ) {

			foreach ( $this->arguments['post_id'] as $id ) {

				if ( 'course' !== get_post_type( $id ) ) {
					continue;
				}

				$course = llms_get_post( $id );
				$this->arguments['post_id'] = array_merge(
					$this->arguments['post_id'],
					$this->sanitize_id_array( $course->get_sections( 'ids' ) ),
					$this->sanitize_id_array( $course->get_lessons( 'ids' ) ),
					$this->sanitize_id_array( $course->get_quizzes() )
				);

			}
		}

		if ( $this->arguments['types'] ) {

			$all_events = array(
				'completion' => array(
					'key' => '_is_complete',
					'value' => 'yes',
				),
				'status' => array(
					'compare' => 'IS NOT NULL',
					'key' => '_status',
				),
				'achievement' => array(
					'compare' => 'IS NOT NULL',
					'key' => '_achievement_earned',
				),
				'certificate' => array(
					'compare' => 'IS NOT NULL',
					'key' => '_certificate_earned',
				),
				'email' => array(
					'compare' => 'IS NOT NULL',
					'key' => '_email_sent',
				),
				'purchase' => array(
					'compare' => 'LIKE',
					'key' => '_enrollment_trigger',
					'value' => 'order_%',
				),
			);

			if ( is_string( $this->arguments['types'] ) && 'all' === $this->arguments['types'] ) {

				$this->arguments['query'] = array_values( $all_events );

			} else {

				$this->arguments['query'] = array();

				if ( ! is_array( $this->arguments['types'] ) ) {
					$this->arguments['types'] = array( $this->arguments['types'] );
				}

				foreach ( $this->arguments['types'] as $type ) {
					if ( ! isset( $all_events[ $type ] ) ) {
						continue;
					}
					$this->arguments['query'][] = $all_events[ $type ];
				}
			}
		}// End if().

		if ( $this->arguments['query'] ) {

			foreach ( $this->arguments['query'] as $i => &$query ) {

				// ensure that each query has a compare operator
				$query = wp_parse_args( $query, array(
					'compare' => '=',
					'key' => '',
					'value' => '',
				) );

				$operators = array( '=', '!=', 'LIKE', 'IN', 'NOT IN', 'IS NOT NULL' );
				if ( ! in_array( $query['compare'], $operators ) ) {
					unset( $this->arguments['query'][ $i ] );
				}
			}
		}

		if ( ! in_array( $this->arguments['query_compare'], array( 'AND', 'OR' ) ) ) {
			$this->arguments['query_compare'] = 'OR';
		}

	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function preprare_query() {

		global $wpdb;

		$vars = array();

		$vars[] = $this->get_skip();
		$vars[] = $this->get( 'per_page' );

		$sql = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS meta_id
			 FROM {$wpdb->prefix}lifterlms_user_postmeta
			 {$this->sql_where()}
			 {$this->sql_orderby()}
			 LIMIT %d, %d;",
			$vars
		);

		return $sql;

	}

	/**
	 * SQL "where" clause for the query
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		foreach ( array( 'post_id', 'user_id' ) as $key ) {

			$ids = $this->get( $key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql .= " AND {$key} IN ({$prepared})";
			}
		}

		if ( $this->get( 'query' ) ) {

			$sql .= ' AND ( ';

			foreach ( $this->get( 'query' ) as $i => $query ) {

				if ( 0 !== $i ) {
					$sql .= " {$this->get( 'query_compare' )} ";
				}

				switch ( $query['compare'] ) {

					case '=':
					case '!=':
					case 'LIKE':
						$sql .= $wpdb->prepare( "( meta_key = %s AND meta_value {$query['compare']} %s )", $query['key'], $query['value'] );
					break;

					case 'IN';
					case 'NOT IN';
						$query['value'] = array_map( array( $this, 'escape_and_quote_string' ), $query['value'] );
						$vals = implode( ',', $query['value'] );
						$sql .= $wpdb->prepare( "( meta_key = %s AND meta_value {$query['compare']} ( {$vals} ) )", $query['key'] );
					break;

					case 'IS NOT NULL':
						$sql .= $wpdb->prepare( "( meta_key = %s AND meta_value {$query['compare']} )", $query['key'] );
					break;

				}
			}

			$sql .= ' )';

		}// End if().

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
