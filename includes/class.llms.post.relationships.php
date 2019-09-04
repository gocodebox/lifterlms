<?php
defined( 'ABSPATH' ) || exit;

/**
 * Hooks and actions related to post relationships
 *
 * @since    3.16.12
 * @version  3.24.0
 */
class LLMS_Post_Relationships {

	/**
	 * Configure relationships
	 *
	 * @var  array
	 */
	private $relationships = array(

		'lesson'     => array(
			array(
				'action'               => 'unset',
				'meta_key'             => '_llms_prerequisite',
				'meta_keys_additional' => array( '_llms_has_prerequisite' ),
				'post_type'            => 'lesson',
			),
			array(
				'action'    => 'unset',
				'meta_key'  => '_llms_lesson_id',
				'post_type' => 'llms_quiz',
			),
		),

		'llms_order' => array(
			array(
				'action'    => 'delete',
				'meta_key'  => '_llms_order_id',
				'post_type' => 'llms_transaction',
			),
		),

		'llms_quiz'  => array(
			array(
				'action'    => 'delete', // delete = force delete; trash = move to trash
				'meta_key'  => '_llms_parent_id',
				'post_type' => 'llms_question',
			),
			array(
				'action'               => 'unset',
				'meta_key'             => '_llms_quiz',
				'meta_keys_additional' => array( '_llms_quiz_enabled' ),
				'post_type'            => 'lesson',
			),
		),

	);

	public function __construct() {

		add_action( 'delete_post', array( $this, 'maybe_update_relationships' ) );

	}

	/**
	 * Delete / Trash posts related to the deleted post
	 *
	 * @param    obj   $post  WP Post that's been deleted
	 * @param    array $data  relationship data array
	 * @return   void
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	private function delete_relationships( $post, $data ) {

		$relationships = $this->get_related_posts( $post->ID, $data['post_type'], $data['meta_key'] );

		$force = ( 'delete' === $data['action'] );

		foreach ( $relationships as $id ) {

			wp_delete_post( $id, $force );

		}

	}

	/**
	 * Get a list of post types with relationships that should be checked
	 *
	 * @return   array
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	private function get_post_types() {
		return array_keys( $this->get_relationships() );
	}

	/**
	 * Retrieve filtered LifterLMS post relationships array
	 *
	 * @return   array
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	private function get_relationships() {
		return apply_filters( 'llms_get_post_relationships', $this->relationships );
	}

	/**
	 * Retrieve an array of post ids related to the deleted post by post type and meta key
	 *
	 * @param    int    $post_id    WP Post ID of the deleted post
	 * @param    string $post_type  WP Post type of the related post(s)
	 * @param    string $meta_key   meta_key to check for relations by
	 * @return   array
	 * @since    3.16.12
	 * @version  3.16.12
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
		);

	}

	/**
	 * Check relationships and delete / update related posts when a post is deleted
	 * Called on `delete_post` hook (before a post is deleted)
	 *
	 * @param    int $post_id  WP Post ID of the deleted post
	 * @return   void
	 * @since    3.16.12
	 * @version  3.24.0
	 */
	public function maybe_update_relationships( $post_id ) {

		$post = get_post( $post_id );
		if ( ! in_array( $post->post_type, $this->get_post_types() ) ) {
			return;
		}

		foreach ( $this->get_relationships() as $post_type => $relationships ) {

			if ( $post->post_type !== $post_type ) {
				continue;
			}

			foreach ( $relationships as $data ) {

				if ( in_array( $data['action'], array( 'delete', 'trash' ) ) ) {

					$this->delete_relationships( $post, $data );

				} elseif ( 'unset' === $data['action'] ) {

					$this->unset_relationships( $post, $data );

				}
			}
		}

	}

	/**
	 * Unsets relationship data from post_meta when a post is deleted
	 *
	 * @param    obj   $post  WP Post that's been deleted
	 * @param    array $data  relationship data array
	 * @return   void
	 * @since    3.16.12
	 * @version  3.24.0
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
