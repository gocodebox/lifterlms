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

		$form_fields['llms_media_protection_post'] = array(
			'label' => __( 'LifterLMS Media Protection', 'lifterlms' ),
			'input' => 'html',
			// TODO: Add selected course/membership to the select2 dropdown if known for this attachment post.
			'html'  => "<select id='attachments-" . $post->ID . "-llms_media_protection_post' class='llms-posts-select2' data-no-view-button='true' data-allow_clear='false' data-post-type='course,llms_membership' name='attachments[" . $post->ID . "][llms_media_protection_post]'></select>",
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
		error_log( print_r( $post, true ) );
		error_log( print_r( $attachment, true ) );

		if ( ! empty( $attachment['llms_media_protection_post'] ) ) {
			error_log( 'updating media protection product id' );
			update_post_meta( $post['ID'], '_llms_media_protection_product_id', absint( $attachment['llms_media_protection_post'] ) );

			// Move the attachment and any thumbnails to the protected folder (LLMS_Media_Protector::upload_dir()).

			// TODO: Verify this move is working correctly.

			$this->move_attachment_to_protected_dir( $post['ID'] );

		}

		return $post;
	}

	function move_attachment_to_protected_dir( $attachment_id ) {
		error_log( 'STARTING MOVE ATTACHMENT TO PROTECTED DIR: ' . $attachment_id );

		// Get attachment metadata.
		$metadata = wp_get_attachment_metadata( $attachment_id );
		error_log( 'metadata: ' . print_r( $metadata, true ) );
		$file = get_attached_file( $attachment_id );
		error_log( 'file: ' . $file );

		// Get the protected upload directory
		$protector = new LLMS_Media_Protector();

		if ( $protector->is_media_protected( $attachment_id ) ) {
			error_log( 'already protected!' );
			return false;
		}

		error_log( 'not already protected, proceeding...' );

		$protected_dir = $protector->get_upload_basedir();

		error_log( 'protected directory base: ' . $protected_dir );

		// Setup WP Filesystem
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Move main file.
		error_log( 'moving file: ' . $file );
		error_log( print_r( wp_upload_dir(), true ) );

		$new_file = str_replace( wp_upload_dir()['basedir'], wp_upload_dir()['basedir'] . untrailingslashit( $protected_dir ), $file );
		error_log( 'new file: ' . $new_file );
		if ( ! $wp_filesystem->is_dir( dirname( $new_file ) ) ) {
			error_log( 'making new folder...' );
			wp_mkdir_p( dirname( $new_file ) );
		}
		if ( $wp_filesystem->move( $file, $new_file, true ) ) {
			// Update attachment location in database.
			update_attached_file( $attachment_id, $new_file );

			// Move thumbnails if they exist
			if ( ! empty( $metadata['sizes'] ) ) {
				$base_dir     = dirname( $file );
				$new_base_dir = dirname( $new_file );

				foreach ( $metadata['sizes'] as $size => $size_info ) {
					$old_thumb = $base_dir . '/' . $size_info['file'];
					error_log( 'old thumb: ' . $old_thumb );
					$new_thumb = $new_base_dir . '/' . $size_info['file'];
					error_log( 'new thumb: ' . $new_thumb );
					// $size_info['file'] = basename($new_file_paths[array_search($existing_path . '/' . $size_info['file'], $file_paths)]);
					$wp_filesystem->move( $old_thumb, $new_thumb, true );
				}
			}

			// Update metadata with new path
			error_log( 'updating metadata...' );
			error_log( print_r( $metadata, true ) );
			$metadata['file'] = ltrim( $protected_dir, '/' ) . $metadata['file'];
			error_log( 'new metadata file: ' . $metadata['file'] );
			wp_update_attachment_metadata( $attachment_id, $metadata );

			// TODO: Add a different authorization hook?
			// TODO: Change the parent of the attachment ID to the product ID? Or leave it as is?

			$protector->add_authorization_meta_to_media_post( $attachment_id );

			return true;
		}

		return false;
	}
}

new LLMS_Admin_Media_Protection_Attachment_Settings();
