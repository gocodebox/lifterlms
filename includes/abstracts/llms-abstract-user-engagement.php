<?php
/**
 * LLMS_Abstract_User_Engagement class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for shared functionality for earned engagements (certificates and achievements).
 *
 * @since [version]
 */
abstract class LLMS_Abstract_User_Engagement extends LLMS_Post_Model {

	/**
	 * Delete the engagement
	 *
	 * @since 3.18.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return void
	 */
	public function delete() {

		/**
		 * Action fired immediately prior to the deletion of a user's earned engagement.
		 *
		 * They dynamic portion of this hook, `{$this->model_post_type}`, refers to the engagement type,
		 * either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( "llms_before_delete_{$this->model_post_type}", $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => $this->get_user_post_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		/**
		 * Action fired immediately after the deletion of a user's earned engagement.
		 * They dynamic portion of this hook, `{$this->model_post_type}`, refers to the engagement type,
		 * either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_User_Certificate $certificate Certificate class object.
		 */
		do_action( "llms_delete_{$this->model_post_type}", $this );

	}

	/**
	 * Retrieve the date the achievement was earned (created)
	 *
	 * @since 3.14.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @param string $format Date format string.
	 * @return string
	 */
	public function get_earned_date( $format = null ) {
		$format = $format ? $format : get_option( 'date_format' );
		return $this->get_date( 'date', $format );
	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the certificate
	 *
	 * This would be a lesson, course, section, track, etc...
	 *
	 * @since 3.8.0
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_related_post_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->post_id ) ? absint( $meta->post_id ) : $this->get( 'related' );
	}

	/**
	 * Retrieves the LLMS_Abstract_User_Engagement instance for a given post.
	 *
	 * Based on {@see llms_get_certificate()}.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post             A WP_Post object or a WP_Post ID. A falsy value will use the current
	 *                                           global `$post` object (if one exists).
	 * @param bool             $preview_template If `true`, allows loading for previewing the template.
	 * @return LLMS_Abstract_User_Engagement|bool
	 */
	protected function get_template_object( $post, $preview_template ) {

		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}

		if (
			"llms_my_$this->model_post_type" === $post->post_type ||
			( "llms_$this->model_post_type" === $post->post_type && $preview_template )
		) {
			return new static( $post );
		}

		return false;

	}

	/**
	 * Retrieves the earned engagement's template version.
	 *
	 * Since LifterLMS 6.0.0, earned engagements are created using the block editor.
	 *
	 * Earned engagements created in the classic editor will use template version 1 while any earned engagements
	 * created in the block editor use template version 2. Therefore an earned engagement that has content
	 * and no blocks will use template version 1 and any empty earned engagements or those containing blocks
	 * will use template version 2.
	 *
	 * @since [version]
	 *
	 * @return integer
	 */
	public function get_template_version() {

		$version = empty( $this->get( 'content', true ) ) || has_blocks( $this->get( 'id' ) ) ? 2 : 1;

		/**
		 * Filters an earned engagement's template version.
		 *
		 * @since [version]
		 *
		 * @param int $version The template version.
		 */
		return apply_filters( "llms_{$this->model_post_type}_template_version", $version, $this );

	}

	/**
	 * Retrieve the user id of the user who earned the certificate
	 *
	 * @since 3.8.0
	 * @since 3.9.0 Unknown.
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_user_id() {
		$meta = $this->get_user_postmeta();
		return isset( $meta->user_id ) ? absint( $meta->user_id ) : $this->get( 'author' );
	}

	/**
	 * Retrieve user postmeta data for the certificate
	 *
	 * @since 3.8.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return stdClass
	 */
	public function get_user_postmeta() {
		global $wpdb;
		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = %s",
				$this->get( 'id' ),
				$this->get_user_post_meta_key()
			)
		);
	}

	/**
	 * Retrieve the user postmeta key recorded when the engagement is earned.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_user_post_meta_key() {
		return sprintf( '_%s_earned', $this->model_post_type );
	}

	/**
	 * Determines if the certificate has been awarded.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_awarded() {

		if ( 'publish' !== $this->get( 'status' ) ) {
			return false;
		}

		return $this->get( 'awarded' ) ? true : false;

	}

	/**
	 * Allow child classes to merge the post content based on content from the template.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function merge_content() {
		return $this->get( 'content', true );
	}

	/**
	 * Update the earned engagement by regenerating it from its template.
	 *
	 * @since [version]
	 *
	 * @param string $context Sync context. Either "update" for an update to an existing earned engagement
	 *                        or "create" when the earned engagement is being created.
	 * @return boolean Returns false if the parent doesn't exist, otherwise returns true.
	 */
	public function sync( $context = 'update' ) {

		$template_id = $this->get( 'parent' );
		$template    = $this->get_template_object( $template_id, true );
		if ( ! $template ) {
			return false;
		}

		$this->set( 'title', get_post_meta( $template_id, "_llms_{$this->model_post_type}_title", true ) );
		if ( get_post_thumbnail_id( $template_id ) !== get_post_thumbnail_id( $this->get( 'post' ) ) &&
			! set_post_thumbnail( $this->get( 'post' ), get_post_thumbnail_id( $template_id ) )
		) {
			delete_post_thumbnail( $this->get( 'post' ) );
		}

		$props = array(
			'content',
		);

		// If using the block editor, also sync all layout properties.
		if ( 2 === $template->get_template_version() ) {
			$props = array_merge(
				$props,
				array(
					'background',
					'height',
					'margins',
					'orientation',
					'size',
					'unit',
					'width',
				)
			);
		}

		foreach ( $props as $prop ) {
			$raw = 'content' === $prop;
			$this->set( $prop, $template->get( $prop, $raw ) );
		}

		// Merge content.
		$this->set( 'content', $this->merge_content() );

		/**
		 * Action run after an awarded engagement is synchronized with its template.
		 *
		 * @since [version]
		 *
		 * @param LLMS_Abstract_User_Engagement $engagement Awarded engagement object.
		 * @param LLMS_Abstract_User_Engagement $template   Engagement template object.
		 * @param string                        $context    The context within which the synchronization is run.
		 *                                                  Either "create" or "update".
		 */
		do_action( "llms_{$this->model_post_type}_synchronized", $this, $template, $context );

		return true;

	}

}
