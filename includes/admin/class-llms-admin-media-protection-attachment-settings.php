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
	}


	/**
	 * Add the media protection settings to the attachment edit screen
	 *
	 * @param   array  $form_fields  Array of form fields
	 * @param   object $post         WP_Post object
	 * @return  array
	 */
	public function attachment_fields_to_edit( $form_fields, $post ) {

		$selected_product_html = $protection_warning_html = '';
		$selected_product_id   = get_post_meta( $post->ID, '_llms_media_protection_product_id', true );
		if ( $selected_product_id && ( $selected_product      = get_post( $selected_product_id ) ) ) {
			$selected_product_html = sprintf( '<option value="%d" selected="selected">%s</option>', $selected_product->ID, $selected_product->post_title );
		}

		$protector = new LLMS_Media_Protector();
		if ( ! $protector->is_media_protected( $post->ID ) ) {
			// translators: %s is a link to the LifterLMS documentation.
			$protection_warning_html = '<div class="llms-media-protection-warning">' . sprintf( __( 'This media is not protected. If you select a product here, the media will be moved to the protected uploads directory and existing links to the media will no longer work. %1$sLearn More%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page">', '</a>' ) . '</div>';
		}

		$form_fields['llms_media_protection_post'] = array(
			'label' => __( 'LifterLMS Media Protection:', 'lifterlms' ),
			'input' => 'html',
			// TODO: Add selected course/membership to the select2 dropdown if known for this attachment post.
			'html'  => "$protection_warning_html<select id='attachments-" . $post->ID . "-llms_media_protection_post' class='llms-posts-select2' data-no-view-button='true' data-allow_clear='false' data-post-type='course,llms_membership' name='attachments[" . $post->ID . "][llms_media_protection_post]'>$selected_product_html</select>",
			'helps' => $protector->is_media_protected( $post->ID ) ? sprintf( __( 'Access is restricted to the selected course/membership. %1$sLearn More%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page">', '</a>' ) : '',
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

		if ( ! empty( $attachment['llms_media_protection_post'] ) ) {
			if ( $this->move_attachment_to_protected_dir( $post['ID'] ) ) {
				update_post_meta( $post['ID'], '_llms_media_protection_product_id', absint( $attachment['llms_media_protection_post'] ) );
			}
		}

		return $post;
	}

	/**
	 * Move an existing media attachment over to the protected folder.
	 *
	 * @param $attachment_id
	 * @since 9.0.0
	 *
	 * @return bool
	 */
	function move_attachment_to_protected_dir( $attachment_id ) {
		// Get attachment metadata.
		$metadata = wp_get_attachment_metadata( $attachment_id );
		$file     = get_attached_file( $attachment_id );

		// Get the protected upload directory.
		$protector = new LLMS_Media_Protector();

		// We could check that the file is in the protected folder, but currently there's no "unprotect" method.
		if ( $protector->is_media_protected( $attachment_id ) ) {
			return false;
		}

		$protected_dir = $protector->get_upload_basedir();

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$new_file = str_replace( wp_upload_dir()['basedir'], wp_upload_dir()['basedir'] . untrailingslashit( $protected_dir ), $file );
		if ( ! $wp_filesystem->is_dir( dirname( $new_file ) ) ) {
			wp_mkdir_p( dirname( $new_file ) );
		}
		if ( $wp_filesystem->move( $file, $new_file ) ) {
			// Move thumbnails if they exist.
			if ( ! empty( $metadata['sizes'] ) ) {
				$base_dir     = dirname( $file );
				$new_base_dir = dirname( $new_file );

				foreach ( $metadata['sizes'] as $size => $size_info ) {
					$old_thumb = $base_dir . '/' . $size_info['file'];
					$new_thumb = $new_base_dir . '/' . $size_info['file'];
					if ( ! $wp_filesystem->move( $old_thumb, $new_thumb ) ) {
						error_log( 'Unable to move protected file. Thumbnail moving failed: ' . $new_thumb );

						// Move the file back along with any thumbnails we already moved.
						$wp_filesystem->move( $new_file, $file );
						foreach ( $metadata['sizes'] as $s => $size_info ) {
							if ( $s === $size ) {
								// We've reached the spot where we failed, so we can stop.
								return false;
							}

							$old_thumb = $base_dir . '/' . $size_info['file'];
							$new_thumb = $new_base_dir . '/' . $size_info['file'];
							if ( $wp_filesystem->exists( $new_thumb ) ) {
								$wp_filesystem->move( $new_thumb, $old_thumb );
							}
						}

						return false;
					}
				}
			}

			// Update attachment location in database.
			update_attached_file( $attachment_id, $new_file );

			// This only exists with images it seems.
			if ( array_key_exists( 'file', $metadata ) ) {
				$metadata['file'] = ltrim( $protected_dir, '/' ) . $metadata['file'];
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}

			$protector->add_authorization_meta_to_media_post( $attachment_id );

			return true;
		}

		error_log( 'Unable to move protected file, check permissions on the protected directory or existing file with the same name: ' . $file );
		return false;
	}
}

new LLMS_Admin_Media_Protection_Attachment_Settings();
