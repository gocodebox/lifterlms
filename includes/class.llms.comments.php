<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Custom filters & actions for LifterLMS Comments
 *
 * @since  3.0.0
 * @version  3.0.0
 */
class LLMS_Comments {

	public function __construct() {

		// secure order notes
		add_filter( 'comments_clauses', array( __CLASS__, 'exclude_order_comments' ), 10, 1 );
		add_action( 'comment_feed_join', array( __CLASS__, 'exclude_order_comments_from_feed_join' ) );
		add_action( 'comment_feed_where', array( __CLASS__, 'exclude_order_comments_from_feed_where' ) );

		// remove order notes when counting comments
		add_filter( 'wp_count_comments', array( __CLASS__, 'wp_count_comments' ), 777, 2 );

		// Delete comments count cache whenever there is a new comment or a comment status changes
		add_action( 'wp_insert_comment', array( __CLASS__, 'delete_comments_count_cache' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'delete_comments_count_cache' ) );

	}

	/**
	 * Delete transient data when inserting new comments or updating comment status
	 * Next time wp_count_comments is called it'll be automatically regenerated
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 * @note thanks WooCommerce :-D
	 */
	public static function delete_comments_count_cache() {
		delete_transient( 'llms_count_comments' );
	}

	/**
	 * Exclude order comments from queries and RSS.
	 *
	 * @param  array $clauses
	 * @return array
	 * @since  3.0.0
	 * @version  3.0.0
	 * @see thanks WooCommerce :-D
	 */
	public static function exclude_order_comments( $clauses ) {

		global $wpdb, $typenow;

		// allow queries when in the admin
		if ( is_admin() && in_array( $typenow, array( 'llms_order' ) ) && current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_options' ) ) ) {
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
	 * @param  string $join
	 * @return string
	 * @since  3.0.0
	 * @version  3.0.0
	 * @see thanks WooCommerce :-D
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
	 * @param  string $where
	 * @return string
	 * @since  3.0.0
	 * @version  3.0.0
	 * @see thanks WooCommerce :-D
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
	 * @param    object $stats    original comment stats
	 * @param    int    $post_id  WP Post ID
	 * @return   object
	 * @since    3.0.0
	 * @version  3.0.0
	 * @see thanks WooCommerce :-D
	 */
	public static function wp_count_comments( $stats, $post_id ) {
		global $wpdb;
		if ( 0 === $post_id ) {
			$trans = get_transient( 'llms_count_comments' );
			if ( ! $trans ) {
				$count    = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} WHERE comment_type = 'llms_order_note' GROUP BY comment_approved;", ARRAY_A );
				$approved = array(
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				);
				foreach ( $count as $row ) {
					// Don't count post-trashed toward totals
					if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
						$stats->total_comments -= $row['num_comments'];
					}

					if ( isset( $approved[ $row['comment_approved'] ] ) ) {
						$var          = $approved[ $row['comment_approved'] ];
						$stats->$var -= $row['num_comments'];
					}
				}
				set_transient( 'llms_count_comments', $stats );
			} else {
				$stats = $trans;
			}
		}
		return $stats;
	}


}
return new LLMS_Comments();
