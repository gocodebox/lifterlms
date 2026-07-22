<?php
/**
 * Define post and record relationships to automate cleanup of information when posts are deleted from the DB.
 *
 * @package LifterLMS/Classes
 *
 * @since 3.16.12
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hooks and actions related to post relationships.
 *
 * @since 3.16.12
 * @since 3.24.0 Unknown.
 * @since 3.37.8 Delete student quiz attempts when a quiz is deleted.
 * @since 4.15.0 Delete access plans related to courses/memberships on their deletion.
 * @since [version] Clean up course prereqs, child sections, membership auto-enroll,
 *               section parent links, membership restriction arrays, and the
 *               sitewide membership option on deletion.
 */
class LLMS_Post_Relationships {

	/**
	 * Configure relationships.
	 *
	 * Supported actions:
	 * - `delete` / `trash`: force-delete or trash related WP posts (or custom table rows).
	 * - `unset`: delete a scalar meta value equal to the deleted post ID.
	 * - `remove_from_meta`: remove the deleted post ID from a serialized array meta value.
	 *
	 * @since Unknown.
	 * @since 7.6.2 Added `llms_voucher` relationship.
	 * @since [version] Added course prereq/section/auto-enroll cleanup, section parent
	 *               unset, and membership restriction/array cleanup.
	 * @var array
	 */
	private $relationships = array(
		'course'          => array(
			array(
				'action'    => 'delete',
				'meta_key'  => '_llms_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_access_plan',
			),
			// Other courses that list this course as a prerequisite.
			array(
				'action'               => 'unset',
				'meta_key'             => '_llms_prerequisite', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_keys_additional' => array( '_llms_has_prerequisite' ),
				'post_type'            => 'course',
			),
			// Memberships that auto-enroll students into this course.
			array(
				'action'    => 'remove_from_meta',
				'meta_key'  => '_llms_auto_enroll', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_membership',
			),
			// Child sections of this course. Force-deleted (section has no trash support).
			array(
				'action'    => 'delete',
				'meta_key'  => '_llms_parent_course', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'section',
			),
			// Lessons that still point at this course as parent.
			array(
				'action'    => 'unset',
				'meta_key'  => '_llms_parent_course', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'lesson',
			),
		),

		'llms_membership' => array(
			array(
				'action'    => 'delete',
				'meta_key'  => '_llms_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_access_plan',
			),
			// Access plans restricted to this membership.
			array(
				'action'    => 'remove_from_meta',
				'meta_key'  => '_llms_availability_restrictions', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_access_plan',
			),
			// Posts restricted to this membership via the membership-restrictions feature.
			// post_type is resolved at runtime via get_post_types_by_support().
			array(
				'action'              => 'remove_from_meta',
				'meta_key'            => '_llms_restricted_levels', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_types_support'  => 'llms-membership-restrictions',
			),
		),

		'lesson'          => array(
			array(
				'action'               => 'unset',
				'meta_key'             => '_llms_prerequisite', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_keys_additional' => array( '_llms_has_prerequisite' ),
				'post_type'            => 'lesson',
			),
			array(
				'action'    => 'unset',
				'meta_key'  => '_llms_lesson_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_quiz',
			),
		),

		// Bare post type is `section` (see LLMS_Section::$db_post_type).
		'section'         => array(
			array(
				'action'    => 'unset',
				'meta_key'  => '_llms_parent_section', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'lesson',
			),
		),

		'llms_order'      => array(
			array(
				'action'    => 'delete',
				'meta_key'  => '_llms_order_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_transaction',
			),
		),

		'llms_quiz'       => array(
			array(
				'action'    => 'delete', // Delete = force delete; trash = move to trash.
				'meta_key'  => '_llms_parent_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'post_type' => 'llms_question',
			),
			array(
				'action'               => 'unset',
				'meta_key'             => '_llms_quiz', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_keys_additional' => array( '_llms_quiz_enabled' ),
				'post_type'            => 'lesson',
			),
			array(
				'action'     => 'delete',
				'table_name' => 'lifterlms_quiz_attempts',
				'table_key'  => 'quiz_id',
			),
		),

		'llms_voucher'    => array(
			array(
				'action'     => 'delete',
				'table_name' => 'lifterlms_vouchers_codes',
				'table_key'  => 'voucher_id',
			),
		),

	);

	/**
	 * Constructor.
	 *
	 * @since 3.16.12
	 * @since 5.4.0 Prevent course/membership with active subscriptions deletion.
	 * @since 6.0.0 Added hook to cleanup user post meta data when awarded certs and achievements are deleted.
	 * @since [version] Clear the sitewide membership restriction option on membership delete.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'delete_post', array( $this, 'maybe_update_relationships' ) );
		add_action( 'delete_post', array( __CLASS__, 'maybe_clear_sitewide_membership_restriction' ) );
		add_action( 'pre_delete_post', array( __CLASS__, 'maybe_prevent_product_deletion' ), 10, 2 );

		add_action( 'before_delete_post', array( __CLASS__, 'maybe_clean_earned_engagments_related_user_post_meta' ) );
	}

	/**
	 * Maybe delete LifterLMS user post meta related to earned engagements.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function maybe_clean_earned_engagments_related_user_post_meta( $post_id ) {

		$post_types = array(
			'llms_my_certificate',
			'llms_my_achievement',
		);
		$post_type  = get_post_type( $post_id );

		if ( ! in_array( $post_type, $post_types, true ) ) {
			return;
		}

		$earned_engagement = 'llms_my_certificate' === $post_type ? new LLMS_User_Certificate( $post_id ) : new LLMS_User_Achievement( $post_id );

		do_action_deprecated(
			'llms_before_delete_' . str_replace( 'llms_my_', '', $post_type ),
			array(
				$earned_engagement,
			),
			'6.0.0',
			'',
			__( 'Use WordPress core  `before_delete_post` action hook', 'lifterlms' )
		);

		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $earned_engagement->get_user_id(),
				'meta_key'   => '_' . str_replace( 'llms_my_', '', $post_type ) . '_earned',
				'meta_value' => $post_id,
			),
			array( '%d', '%s', '%d' )
		); // no-cache ok.

		add_action(
			'after_delete_post',
			function ( $post_id ) use ( $earned_engagement, $post_type ) {

				if ( $earned_engagement->get( 'id' ) === $post_id ) {
					do_action_deprecated(
						'llms_delete_' . str_replace( 'llms_my_', '', $post_type ),
						array(
							$earned_engagement,
						),
						'6.0.0',
						'',
						__( 'Use WordPress core `deleted_post` action hook.', 'lifterlms' )
					);
				}
			}
		);
	}

	/**
	 * Determine whether a product deletion should take place.
	 *
	 * @since 5.4.0
	 *
	 * @param bool|null $delete Whether to go forward with deletion.
	 * @param WP_Post   $post   Post object.
	 * @return bool|null
	 */
	public static function maybe_prevent_product_deletion( $delete, $post ) {

		if ( ! in_array( get_post_type( $post ), array( 'course', 'llms_membership' ), true ) ) {
			return $delete;
		}

		$product = llms_get_product( $post );

		if ( empty( $product ) || ! $product->has_active_subscriptions() ) {
			return $delete;
		}

		// If performing the deletion via REST API change the error message to reflect the reason for the prevention.
		if ( llms_is_rest() ) {
			// Filter the error message.
			add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'rest_filter_products_with_active_subscriptions_error_message' ), 10, 3 );
		} else { // Deleting via wp-admin.
			wp_die(
				esc_html( self::delete_product_with_active_subscriptions_error_message( $product->get( 'id' ) ) )
			);
		}

		return false;
	}

	/**
	 * Filter the error message returned when trying to delete a product with active subscription via REST API.
	 *
	 * The original message is a standard permission denied message.
	 *
	 * @since 5.4.0
	 *
	 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send to the client.
	 *                                                                   Usually a WP_REST_Response or WP_Error.
	 * @param array                                            $handler  Route handler used for the request.
	 * @param WP_REST_Request                                  $request  Request used to generate the response.
	 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
	 */
	public static function rest_filter_products_with_active_subscriptions_error_message( $response, $handler, $request ) {

		if ( is_wp_error( $response ) ) {
			foreach ( $response->errors as $code => &$data ) {
				// Error code can be produced by our rest-api or by wp core.
				if ( in_array( $code, array( 'llms_rest_cannot_delete', 'rest_cannot_delete' ), true ) ) {
					$data[0] = self::delete_product_with_active_subscriptions_error_message( $request['id'] );
					break;
				}
			}
		}

		return $response;
	}

	/**
	 * Returns the error message to display when deleting a product with active subscriptions.
	 *
	 * @since 5.4.0
	 *
	 * @param int $post_id The WP_Post ID of the product.
	 * @return string
	 */
	public static function delete_product_with_active_subscriptions_error_message( $post_id ) {

		$post_type = get_post_type( $post_id );

		if ( ! in_array( $post_type, array( 'course', 'llms_membership' ), true ) ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post_type );
		$post_type_name   = $post_type_object->labels->name;
		return sprintf(
			// Translators: %s = The post type plural name.
			__( 'Sorry, you are not allowed to delete %s with active subscriptions.', 'lifterlms' ),
			$post_type_name
		);
	}

	/**
	 * Delete / Trash posts related to the deleted post.
	 *
	 * @since 3.16.12
	 * @since 3.37.8 Allow for deletion of related items outside the WP core posts table.
	 *
	 * @param WP_Post $post WP Post that's been deleted.
	 * @param array   $data Relationship data array.
	 * @return void
	 */
	private function delete_relationships( $post, $data ) {

		if ( isset( $data['post_type'] ) && isset( $data['meta_key'] ) ) {

			$this->delete_wp_posts( $post, $data );

		} elseif ( isset( $data['table_name'] ) && isset( $data['table_key'] ) ) {

			$this->delete_table_records( $post, $data );

		}
	}

	/**
	 * Delete records from a table that are related to the deleted post.
	 *
	 * @since 3.37.8
	 *
	 * @param WP_Post $post WP Post that's been deleted.
	 * @param array   $data Relationship data array.
	 * @return void
	 */
	private function delete_table_records( $post, $data ) {

		global $wpdb;
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prefix . $data['table_name'],
			array(
				$data['table_key'] => $post->ID,
			),
			'%d'
		);
	}

	/**
	 * Delete or trash WP Posts related to the deleted post.
	 *
	 * @since 3.37.8
	 *
	 * @param WP_Post $post WP Post that's been deleted.
	 * @param array   $data Relationship data array.
	 * @return void
	 */
	private function delete_wp_posts( $post, $data ) {

		$relationships = $this->get_related_posts( $post->ID, $data['post_type'], $data['meta_key'] );

		$force = ( 'delete' === $data['action'] );

		foreach ( $relationships as $id ) {
			wp_delete_post( $id, $force );
		}
	}

	/**
	 * Get a list of post types with relationships that should be checked.
	 *
	 * @since 3.16.12
	 *
	 * @return array
	 */
	private function get_post_types() {
		return array_keys( $this->get_relationships() );
	}

	/**
	 * Retrieve filtered LifterLMS post relationships array.
	 *
	 * @since 3.16.12
	 *
	 * @return array
	 */
	private function get_relationships() {
		return apply_filters( 'llms_get_post_relationships', $this->relationships );
	}

	/**
	 * Retrieve an array of post ids related to the deleted post by post type and meta key.
	 *
	 * @since 3.16.12
	 *
	 * @param int    $post_id   WP Post ID of the deleted post.
	 * @param string $post_type WP Post type of the related post(s).
	 * @param string $meta_key  meta_key to check for relations by.
	 * @return array
	 */
	private function get_related_posts( $post_id, $post_type, $meta_key ) {

		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID
			 FROM {$wpdb->posts} AS p
			 LEFT JOIN {$wpdb->postmeta} AS pm
			        ON p.ID = pm.post_id
			       AND pm.meta_key = %s
			 WHERE p.post_type = %s
			   AND pm.meta_value = %d",
				$meta_key,
				$post_type,
				$post_id
			)
		); // db-call ok; no-cache ok.
	}

	/**
	 * Check relationships and delete / update related posts when a post is deleted.
	 *
	 * Called on `delete_post` hook (before a post is deleted).
	 *
	 * @since 3.16.12
	 * @since 3.24.0 Unknown.
	 * @since [version] Handle the `remove_from_meta` action for serialized array metas.
	 *
	 * @param int $post_id WP Post ID of the deleted post.
	 * @return void
	 */
	public function maybe_update_relationships( $post_id ) {

		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, $this->get_post_types(), true ) ) {
			return;
		}

		foreach ( $this->get_relationships() as $post_type => $relationships ) {

			if ( $post->post_type !== $post_type ) {
				continue;
			}

			foreach ( $relationships as $data ) {

				if ( in_array( $data['action'], array( 'delete', 'trash' ), true ) ) {

					$this->delete_relationships( $post, $data );

				} elseif ( 'unset' === $data['action'] ) {

					$this->unset_relationships( $post, $data );

				} elseif ( 'remove_from_meta' === $data['action'] ) {

					$this->remove_from_meta_relationships( $post, $data );

				}
			}
		}
	}

	/**
	 * Clear the sitewide membership restriction option when its membership is deleted.
	 *
	 * The option `lifterlms_membership_required` stores a single membership post ID.
	 *
	 * @since [version]
	 *
	 * @param int $post_id WP Post ID of the deleted post.
	 * @return void
	 */
	public static function maybe_clear_sitewide_membership_restriction( $post_id ) {

		if ( 'llms_membership' !== get_post_type( $post_id ) ) {
			return;
		}

		$option_id = absint( get_option( 'lifterlms_membership_required', '' ) );
		if ( $option_id && (int) $option_id === (int) $post_id ) {
			delete_option( 'lifterlms_membership_required' );
		}
	}

	/**
	 * Remove a deleted post's ID from a serialized array stored in post meta.
	 *
	 * Used for membership auto-enroll lists, availability restrictions on access
	 * plans, and membership-restricted posts. The DB lookup is a LIKE pre-filter;
	 * the authoritative check is the PHP-side `in_array()` after unserializing.
	 *
	 * @since [version]
	 *
	 * @param WP_Post $post WP Post that's been deleted.
	 * @param array   $data Relationship data array. Expected keys:
	 *                      - `meta_key` (string) Meta key holding the serialized array.
	 *                      - `post_type` (string, optional) Single target post type.
	 *                      - `post_types_support` (string, optional) Feature flag used
	 *                        with `get_post_types_by_support()` when multiple types apply.
	 * @return void
	 */
	private function remove_from_meta_relationships( $post, $data ) {

		$post_types = array();
		if ( ! empty( $data['post_type'] ) ) {
			$post_types = array( $data['post_type'] );
		} elseif ( ! empty( $data['post_types_support'] ) ) {
			$post_types = get_post_types_by_support( $data['post_types_support'] );
		}

		if ( empty( $post_types ) || empty( $data['meta_key'] ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			$candidate_ids = $this->get_posts_with_meta_containing( $post->ID, $post_type, $data['meta_key'] );

			foreach ( $candidate_ids as $id ) {
				$value = get_post_meta( $id, $data['meta_key'], true );
				if ( ! is_array( $value ) ) {
					continue;
				}

				// Coerce to ints so string/int mismatches don't leave stale IDs behind.
				$value   = array_map( 'absint', $value );
				$cleaned = array_values( array_diff( $value, array( (int) $post->ID ) ) );

				if ( count( $cleaned ) === count( $value ) ) {
					continue;
				}

				if ( empty( $cleaned ) ) {
					delete_post_meta( $id, $data['meta_key'] );
				} else {
					update_post_meta( $id, $data['meta_key'], $cleaned );
				}
			}
		}
	}

	/**
	 * Find posts of a type whose meta value (serialized array) may contain a post ID.
	 *
	 * Uses a LIKE pre-filter for speed; callers must still validate with
	 * `in_array()` after unserializing. See `remove_from_meta_relationships()`.
	 *
	 * @since [version]
	 *
	 * @param int    $post_id   Deleted post ID to search for inside the meta value.
	 * @param string $post_type Target post type.
	 * @param string $meta_key  Meta key holding the serialized array.
	 * @return int[]
	 */
	private function get_posts_with_meta_containing( $post_id, $post_type, $meta_key ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID
				 FROM {$wpdb->posts} AS p
				 INNER JOIN {$wpdb->postmeta} AS pm
				         ON p.ID = pm.post_id
				 WHERE p.post_type = %s
				   AND pm.meta_key = %s
				   AND pm.meta_value LIKE %s",
				$post_type,
				$meta_key,
				'%' . $wpdb->esc_like( (string) $post_id ) . '%'
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return array_map( 'absint', $ids );
	}

	/**
	 * Unsets relationship data from post_meta when a post is deleted.
	 *
	 * @since 3.16.12
	 * @since 3.24.0 Unknown.
	 *
	 * @param WP_Post $post WP Post that's been deleted.
	 * @param array   $data Relationship data array.
	 * @return void
	 */
	private function unset_relationships( $post, $data ) {

		$relationships = $this->get_related_posts( $post->ID, $data['post_type'], $data['meta_key'] );

		foreach ( $relationships as $id ) {

			delete_post_meta( $id, $data['meta_key'], $post->ID );

			if ( isset( $data['meta_keys_additional'] ) ) {
				foreach ( $data['meta_keys_additional'] as $key ) {
					delete_post_meta( $id, $key );
				}
			}
		}
	}
}

return new LLMS_Post_Relationships();
