<?php
/**
 * Admin Post Types Class
 *
 * Sets up post type custom messages and includes base metabox class
 *
 * @since Unknown
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Types
 *
 * @since Unknown
 * @version 3.35.0 Fix l10n calls.
 */
class LLMS_Admin_Post_Types {

	/**
	 * Constructor
	 *
	 * Adds functions to actions and sets filter on post_updated_messages
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'include_post_type_metabox_class' ) );
		add_action( 'metabox_init', array( $this, 'meta_metabox_init' ) );

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
	 * Initializes core for metaboxes
	 *
	 * @return void
	 */
	public function meta_metabox_init() {
		include_once 'llms.class.admin.metabox.php';
	}

	/**
	 * Customize post type messages.
	 *
	 * @since Unknown.
	 * @version 3.35.0 Fix l10n calls.
	 *
	 * @return array $messages
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
			'llms_achievement',
			'llms_engagement',
			'llms_quiz',
			'llms_question',
			'llms_coupon',
		);

		foreach ( $llms_post_types as $type ) {

			$obj  = get_post_type_object( $type );
			$name = $obj->labels->singular_name;

			$permalink    = get_permalink( $post->ID );
			$preview_link = add_query_arg( 'preview', 'true', $permalink );

			$link_format = ' <a href="%1$s">%2$s</a>.';

			$permalink_html    = sprintf( $link_format, $permalink, sprintf( __( 'View %s', 'lifterlms' ), $name ) );
			$preview_link_html = sprintf( $link_format, $permalink, sprintf( __( 'Preview %s', 'lifterlms' ), $name ) );

			$messages[ $type ] = array(
				0  => '',
				1  => sprintf( __( '%s updated.', 'lifterlms' ), $name ) . $permalink_html,
				2  => __( 'Custom field updated.', 'lifterlms' ),
				3  => __( 'Custom field deleted.', 'lifterlms' ),
				4  => sprintf( __( '%s updated.', 'lifterlms' ), $name ),
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s.', 'lifterlms' ), wp_post_revision_title( llms_filter_input( INPUT_GET, 'revision', FILTER_SANITIZE_NUMBER_INT ), false ) ) : false,
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
