<?php
/**
 * LifterLMS Admin Media Protection Attachment Settings.
 *
 * @package LifterLMS/Classes/Admin
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Admin_Media_Protection_Attachment_Settings {

	public function __construct() {

		add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	public function admin_scripts() {
		wp_enqueue_script( 'llms-admin-media-protection-attachment-settings', LLMS_PLUGIN_URL . 'assets/js/llms-admin-media-protection-attachment-settings' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'media-views', 'wp-i18n', 'llms-admin-scripts' ), LLMS_ASSETS_VERSION, true );
	}

	/**
	 * Add the media protection settings to the attachment edit screen
	 *
	 * @param   array  $form_fields  Array of form fields
	 * @param   object $post         WP_Post object
	 * @return  array
	 */
	public function attachment_fields_to_edit( $form_fields, $post ) {

		$form_fields['llms_media_protection'] = array(
			'label' => __( 'LifterLMS Media Protection', 'lifterlms' ),
			'input' => 'html',
			// TODO: Add selected course/membership to the select2 dropdown if known for this attachment post.
			'html'  => "<select id='attachments-" . $post->ID . "-llms_media_protection' class='llms-posts-select2' data-no-view-button='true' data-allow_clear='false' data-post-type='course,llms_membership' name='llms_media_protection_post'></select>",
		);

		return $form_fields;
	}

	/**
	 * Save the media protection settings
	 *
	 * @param   array $post     Array of post data
	 * @param   array $attachment  Array of attachment data
	 * @return  array
	 */
	public function attachment_fields_to_save( $post, $attachment ) {


		// TODO: Save the data.

		if ( isset( $attachment['llms_media_protection'] ) && is_numeric( $attachment['llms_media_protection'] ) ) {
			update_post_meta( $post['ID'], '_llms_media_protection', 'yes' );
		} else {
			delete_post_meta( $post['ID'], '_llms_media_protection' );
		}

		return $post;
	}
}

new LLMS_Admin_Media_Protection_Attachment_Settings();
