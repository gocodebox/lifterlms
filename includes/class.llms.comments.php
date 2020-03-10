<?php
/**
 * Custom filters & actions for LifterLMS Comments
 *
 * This class owes a great debt to WooCommerce.
 *
 * @since 3.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Comments class
 *
 * @since 3.0.0
 * @since [version] Use strict comparisons.
 *                Handle empty array from `wp_count_comments` filter.
 *                Properly exclude "llms_order_note" comment types from comment counts..
 */
class LLMS_Comments {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		// Secure order notes.
		add_filter( 'comments_clauses', array( __CLASS__, 'exclude_order_comments' ), 10, 1 );
		add_action( 'comment_feed_join', array( __CLASS__, 'exclude_order_comments_from_feed_join' ) );
		add_action( 'comment_feed_where', array( __CLASS__, 'exclude_order_comments_from_feed_where' ) );

		// Remove order notes when counting comments.
		add_filter( 'wp_count_comments', array( __CLASS__, 'wp_count_comments' ), 999, 2 );

		// Delete comments count cache whenever there is a new comment or a comment status changes.
		add_action( 'wp_insert_comment', array( __CLASS__, 'delete_comments_count_cache' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'delete_comments_count_cache' ) );

	}

	/**
	 * Delete transient data when inserting new comments or updating comment status
	 *
	 * Next time wp_count_comments is called it'll be automatically regenerated
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function delete_comments_count_cache() {
		delete_transient( 'llms_count_comments' );
	}

	/**
	 * Exclude order comments from queries and RSS.
	 *
	 * @since 3.0.0
	 * @since [version] Use strict comparison for `in_array()`.
	 *
	 * @param array $clauses Array of SQL clauses.
	 * @return array
	 */
	public static function exclude_order_comments( $clauses ) {

		global $wpdb, $typenow;

		// Allow queries when in the admin.
		if ( is_admin() && in_array( $typenow, array( 'llms_order' ), true ) && current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_options' ) ) ) {
			return $clauses;
		}

		if ( ! $clauses['join'] ) {
			$clauses['join'] = '';
		}

		if ( ! strstr( $clauses['join'], "JOIN $wpdb->posts" ) ) {
			$clauses['join'] .= " LEFT JOIN $wpdb->posts ON comment_post_ID = $wpdb->posts.ID ";
		}

		if ( $clauses['where'] ) {
			$clauses['where'] .= ' AND ';
		}

		$clauses['where'] .= " $wpdb->posts.post_type NOT IN ('" . implode( "','", array( 'llms_order' ) ) . "') ";

		return $clauses;

	}

	/**
	 * Exclude order comments from queries and RSS.
	 *
	 * @since 3.0.0
	 *
	 * @param string $join SQL join clause.
	 * @return string
	 */
	public static function exclude_order_comments_from_feed_join( $join ) {
		global $wpdb;
		if ( ! strstr( $join, $wpdb->posts ) ) {
			$join = " LEFT JOIN $wpdb->posts ON $wpdb->comments.comment_post_ID = $wpdb->posts.ID ";
		}
		return $join;
	}

	/**
	 * Exclude order comments from queries and RSS.
	 *
	 * @since 3.0.0
	 *
	 * @param string $where SQL where clause.
	 * @return string
	 */
	public static function exclude_order_comments_from_feed_where( $where ) {
		global $wpdb;
		if ( $where ) {
			$where .= ' AND ';
		}
		$where .= " $wpdb->posts.post_type NOT IN ('" . implode( "','", array( 'llms_order' ) ) . "') ";
		return $where;
	}

	/**
	 * Remove order notes from the count when counting comments
	 *
	 * @since 3.0.0
	 * @since [version] Use strict comparisons.
	 *                Fix issue encountered when $stats is an empty array.
	 *                Properly exclude "llms_order_note" comment types.
	 *
	 * @param obj $stats   Original comment stats.
	 * @param int $post_id WP Post ID
	 * @return obj
	 */
	public static function wp_count_comments( $stats, $post_id ) {

		if ( 0 === $post_id ) {

			$stats = get_transient( 'llms_count_comments' );

			if ( ! $stats ) {

				$stats = array(
					'total_comments' => 0,
					'all'            => 0,
				);

				global $wpdb;
				$count = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					"
					SELECT comment_approved, COUNT( * ) AS num_comments
					  FROM {$wpdb->comments}
					 WHERE comment_type != 'llms_order_note'
				  GROUP BY comment_approved;
					",
					ARRAY_A
				);

				$approved = array(
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				);

				foreach ( (array) $count as $row ) {

					if ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash', 'spam' ), true ) ) {
						$stats['all']            += $row['num_comments'];
						$stats['total_comments'] += $row['num_comments'];
					} elseif ( ! in_array( $row['comment_approved'], array( 'post-trashed', 'trash' ), true ) ) {
						$stats['total_comments'] += $row['num_comments'];
					}
					if ( isset( $approved[ $row['comment_approved'] ] ) ) {
						$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
					}

				}

				// Fill in remaining items with 0.
				foreach ( $approved as $key ) {
					if ( empty( $stats[ $key ] ) ) {
						$stats[ $key ] = 0;
					}
				}

				// Cast to an object the way WP expects.
				$stats = (object) $stats;

				set_transient( 'llms_count_comments', $stats );

			}

		}

		return $stats;
	}

}
return new LLMS_Comments();
