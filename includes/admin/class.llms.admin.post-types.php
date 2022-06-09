<?php
/**
 * LLMS_Admin_Post_Types class.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since Unknown
 * @version 6.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Post Types.
 *
 * Sets up post type custom messages and includes base metabox class.
 *
 * @since Unknown.
 * @since 6.0.0 Removed LLMS_Admin_Post_Types::meta_metabox_init() in favor of autoloading.
 */
class LLMS_Admin_Post_Types {

	/**
	 * Constructor
	 *
	 * Adds functions to actions and sets filter on post_updated_messages.
	 *
	 * @since Unknown
	 * @since 6.0.0 Disable the block editor for legacy certificates.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'use_block_editor_for_post', array( $this, 'use_block_editor_for_post' ), 20, 2 );

		add_action( 'admin_init', array( $this, 'include_post_type_metabox_class' ) );

		add_filter( 'post_updated_messages', array( $this, 'llms_post_updated_messages' ) );

	}

	/**
	 * Admin Menu
	 *
	 * Includes base metabox class
	 *
	 * @return void
	 */
	public function include_post_type_metabox_class() {
		include 'post-types/class.llms.meta.boxes.php';
	}

	/**
	 * Disables the block editor for legacy certificates.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $use_block_editor Whether or not to use the block editor.
	 * @param WP_Post $post             Post object.
	 * @return boolean
	 */
	public function use_block_editor_for_post( $use_block_editor, $post ) {
		$cert = llms_get_certificate( $post, true );
		if ( $cert && 1 === $cert->get_template_version() ) {
			$use_block_editor = false;
		}

		return $use_block_editor;

	}

	/**
	 * Initializes core for metaboxes.
	 *
	 * @since Unknown
	 * @deprecated 6.0.0 `LLMS_Admin_Post_Types::meta_metabox_init()` is deprecated with no replacement.
	 *
	 * @return void
	 */
	public function meta_metabox_init() {

		llms_deprecated_function( __METHOD__, '6.0.0' );

		include_once 'llms.class.admin.metabox.php';
	}

	/**
	 * Customize post type messages.
	 *
	 * @since Unknown.
	 * @since 3.35.0 Fix l10n calls.
	 * @since 4.7.0 Added `publicly_queryable` check for permalink and preview.
	 * @since 6.0.0 Handle `llms_my_certificate` and `llms_my_achievement` post types.
	 * @since 6.7.0 Fixed too few arguments passed to sprintf, when building restore from revision message.
	 *
	 * @return array $messages Post updated messages.
	 */
	public function llms_post_updated_messages( $messages ) {

		global $post;

		$llms_post_types = array(
			'course',
			'section',
			'lesson',
			'llms_order',
			'llms_email',
			'llms_email',
			'llms_certificate',
			'llms_my_certificate',
			'llms_achievement',
			'llms_my_achievement',
			'llms_engagement',
			'llms_quiz',
			'llms_question',
			'llms_coupon',
		);

		foreach ( $llms_post_types as $type ) {

			$obj  = get_post_type_object( $type );
			$name = $obj->labels->singular_name;

			$permalink_html    = '';
			$preview_link_html = '';

			if ( $obj->publicly_queryable ) {

				$permalink    = get_permalink( $post->ID );
				$preview_link = add_query_arg( 'preview', 'true', $permalink );

				$link_format = ' <a href="%1$s">%2$s</a>.';

				$permalink_html    = sprintf( $link_format, $permalink, sprintf( __( 'View %s', 'lifterlms' ), $name ) );
				$preview_link_html = sprintf( $link_format, $permalink, sprintf( __( 'Preview %s', 'lifterlms' ), $name ) );
			}

			$messages[ $type ] = array(
				0  => '',
				1  => sprintf( __( '%s updated.', 'lifterlms' ), $name ) . $permalink_html,
				2  => __( 'Custom field updated.', 'lifterlms' ),
				3  => __( 'Custom field deleted.', 'lifterlms' ),
				4  => sprintf( __( '%s updated.', 'lifterlms' ), $name ),
				5  => isset( $_GET['revision'] ) ? // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need to verify the nonce here.
					sprintf(
						__( '%1$s restored to revision from %2$s.', 'lifterlms' ),
						$name,
						wp_post_revision_title( llms_filter_input( INPUT_GET, 'revision', FILTER_SANITIZE_NUMBER_INT ), false )
					)
					:
					false,
				6  => sprintf( __( '%s published.', 'lifterlms' ), $name ) . $permalink_html,
				7  => sprintf( __( '%s saved.', 'lifterlms' ), $name ),
				8  => sprintf( __( '%s submitted.', 'lifterlms' ), $name ) . $preview_link_html,
				9  => sprintf(
					__( '%1$s scheduled for: <strong>%2$s</strong>.', 'lifterlms' ),
					$name,
					date_i18n( __( 'M j, Y @ G:i', 'lifterlms' ), strtotime( $post->post_date ) )
				) . $preview_link_html,
				10 => sprintf( __( '%1$s draft updated.', 'lifterlms' ), $name ) . $preview_link_html,
			);

		}

		return $messages;
	}

}

return new LLMS_Admin_Post_Types();
