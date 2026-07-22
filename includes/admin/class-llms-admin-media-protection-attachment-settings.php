<?php
/**
 * LifterLMS Admin Media Protection Attachment Settings.
 *
 * @package LifterLMS/Classes/Admin
 *
 * @since 9.0.0
 * @version [version]
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

		$selected_product_html = $protection_warning_html = $original_location_html = $unprotect_warning_html = '';
		$selected_product_id   = get_post_meta( $post->ID, '_llms_media_protection_product_id', true );
		if ( $selected_product_id && ( $selected_product      = get_post( $selected_product_id ) ) ) {
			$selected_product_html = sprintf( '<option value="%d" selected="selected">%s</option>', $selected_product->ID, $selected_product->post_title );
		}

		$protector = new LLMS_Media_Protector();
		if ( ! $protector->is_media_protected( $post->ID ) ) {
			// translators: %s is a link to the LifterLMS documentation.
			$protection_warning_html = '<div class="llms-media-protection-warning">' . sprintf( __( 'This media is not protected. If you select a product here, the media will be moved to the protected uploads directory and existing links to the media will no longer work. %1$sLearn More%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page">', '</a>' ) . '</div>';
		} else {
			$original_file = $protector->get_original_attached_file( $post->ID );
			if ( $original_file ) {
				$original_location_html = '<div class="llms-media-protection-original-location">' . sprintf(
					// translators: %s is the original public location of the media file before it was protected.
					esc_html__( 'Originally located in: %s', 'lifterlms' ),
					'<code>wp-content/uploads/' . esc_html( $original_file ) . '</code>'
				) . '</div>';

				$unprotect_warning_html = '<div class="llms-media-protection-unprotect-warning" style="display:none;">' . sprintf(
					// translators: %s is a link to the LifterLMS documentation.
					esc_html__( 'Clearing the selected product will move this file back to its original public location. Existing protected links to this media will no longer work. %1$sLearn More%2$s', 'lifterlms' ),
					'<a target="_blank" href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page">',
					'</a>'
				) . '</div>';
			}
		}

		$form_fields['llms_media_protection_post'] = array(
			'label' => __( 'LifterLMS Media Protection:', 'lifterlms' ),
			'input' => 'html',
			// TODO: Add selected course/membership to the select2 dropdown if known for this attachment post.
			'html'  => "$protection_warning_html$original_location_html$unprotect_warning_html<select id='attachments-" . $post->ID . "-llms_media_protection_post' class='llms-posts-select2' data-no-view-button='true' data-allow_clear='true' data-post-type='course,llms_membership' name='attachments[" . $post->ID . "][llms_media_protection_post]'>$selected_product_html</select>",
			'helps' => $protector->is_media_protected( $post->ID ) ? sprintf( __( 'Access is restricted to the selected course/membership. %1$sLearn More%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-protected-media-files-work/?utm_source=LifterLMS%20Plugin&utm_medium=Media&utm_campaign=Backend%20Help%20Page">', '</a>' ) : '',
		);

		/**
		 * Filter the LifterLMS media protection attachment field.
		 *
		 * @since 10.0.0
		 *
		 * @param array                $field     Attachment field definition.
		 * @param WP_Post              $post      Attachment post object.
		 * @param LLMS_Media_Protector $protector Media protector instance.
		 */
		$form_fields['llms_media_protection_post'] = apply_filters( 'llms_media_protection_attachment_field', $form_fields['llms_media_protection_post'], $post, $protector );

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

		$protector = new LLMS_Media_Protector();

		if ( empty( $attachment['llms_media_protection_post'] ) ) {
			// Empty value: unprotect the file if it is currently protected.
			if ( $protector->is_media_protected( $post['ID'] ) ) {
				$result = $this->move_attachment_to_public_dir( $post['ID'] );
				if ( true === $result ) {
					delete_post_meta( $post['ID'], '_llms_media_protection_product_id' );
				}
			}
		} else {
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

				// Multiple registered sizes can share the same physical file.
				$moved_files = array();

				foreach ( $metadata['sizes'] as $size => $size_info ) {
					if ( in_array( $size_info['file'], $moved_files, true ) ) {
						continue;
					}

					$old_thumb = $base_dir . '/' . $size_info['file'];
					$new_thumb = $new_base_dir . '/' . $size_info['file'];
					if ( ! $wp_filesystem->exists( $old_thumb ) ) {
						error_log( 'Registered metadata thumbnail file does not exist. Skipping. ' . $old_thumb );
						continue;
					}
					if ( ! $wp_filesystem->move( $old_thumb, $new_thumb ) ) {
						error_log( 'Unable to move protected file. Thumbnail moving failed: ' . $old_thumb . ' to ' . $new_thumb );

						// Move the file back along with any thumbnails we already moved.
						$wp_filesystem->move( $new_file, $file );
						foreach ( $moved_files as $moved_file ) {
							$old_thumb = $base_dir . '/' . $moved_file;
							$new_thumb = $new_base_dir . '/' . $moved_file;
							if ( $wp_filesystem->exists( $new_thumb ) ) {
								$wp_filesystem->move( $new_thumb, $old_thumb );
							}
						}

						return false;
					}

					$moved_files[] = $size_info['file'];
				}
			}

			// Record the original public location before the metadata is updated, so that
			// it can be used to restore the file to its original public path if it is later unprotected.
			$this->store_original_attached_file( $attachment_id, $metadata, $file );

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

	/**
	 * Move a protected media attachment back to its original public location.
	 *
	 * Mirrors {@see LLMS_Admin_Media_Protection_Attachment_Settings::move_attachment_to_protected_dir()}.
	 * Requires the original `_wp_attached_file` relative path to have been recorded by
	 * {@see LLMS_Admin_Media_Protection_Attachment_Settings::store_original_attached_file()} at the time of protection.
	 *
	 * @since [version]
	 *
	 * @param int $attachment_id The attachment post ID.
	 * @return bool|WP_Error True on success, false on failure, or WP_Error on conflict.
	 */
	public function move_attachment_to_public_dir( $attachment_id ) {
		if ( ! is_numeric( $attachment_id ) || ! intval( $attachment_id ) ) {
			return false;
		}

		$protector = new LLMS_Media_Protector();

		// Only protected files can be unprotected.
		if ( ! $protector->is_media_protected( $attachment_id ) ) {
			return false;
		}

		// The original public location must have been recorded when the file was protected.
		$original_relative = $protector->get_original_attached_file( $attachment_id );
		if ( ! $original_relative ) {
			error_log( 'Unable to unprotect attachment ' . $attachment_id . ': original location is not recorded.' );
			return false;
		}

		$metadata    = wp_get_attachment_metadata( $attachment_id );
		$current_file = get_attached_file( $attachment_id );

		$uploads     = wp_upload_dir();
		$target_file = $uploads['basedir'] . DIRECTORY_SEPARATOR . $original_relative;
		$target_dir  = dirname( $target_file );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Ensure the target public directory exists.
		if ( ! $wp_filesystem->is_dir( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		// Handle a conflict if a file already exists at the public path.
		if ( $wp_filesystem->exists( $target_file ) ) {
			if ( ! $this->files_are_identical( $current_file, $target_file ) ) {
				// translators: %s is the conflicting public file path.
				error_log( sprintf( 'Unable to unprotect attachment %1$d: a different file already exists at %2$s.', $attachment_id, $target_file ) );
				return new WP_Error(
					'llms_media_unprotect_conflict',
					sprintf(
						// translators: %s is the conflicting public file path.
						__( 'A different file already exists at the original location (%s). Please rename or remove it before unprotecting this media.', 'lifterlms' ),
						$target_file
					)
				);
			}

			// Same file: delete the protected copy. The public copy is already in place.
			$wp_filesystem->delete( $current_file );
		} else {
			if ( ! $wp_filesystem->move( $current_file, $target_file ) ) {
				// translators: %1$s is the current protected file, %2$s is the target public file.
				error_log( sprintf( 'Unable to move protected file back to public dir: %1$s to %2$s', $current_file, $target_file ) );
				return false;
			}
		}

		// Move thumbnails back, if any.
		if ( ! empty( $metadata['sizes'] ) ) {
			$current_base_dir = dirname( $current_file );
			$target_base_dir  = dirname( $target_file );

			// Track files that have already been moved or deleted so we can roll back on failure.
			$restored_files = array();

			foreach ( $metadata['sizes'] as $size => $size_info ) {
				if ( in_array( $size_info['file'], $restored_files, true ) ) {
					continue;
				}

				$protected_thumb = $current_base_dir . DIRECTORY_SEPARATOR . $size_info['file'];
				$public_thumb    = $target_base_dir . DIRECTORY_SEPARATOR . $size_info['file'];

				if ( ! $wp_filesystem->exists( $protected_thumb ) ) {
					error_log( 'Registered metadata thumbnail file does not exist. Skipping. ' . $protected_thumb );
					continue;
				}

				if ( $wp_filesystem->exists( $public_thumb ) ) {
					if ( $this->files_are_identical( $protected_thumb, $public_thumb ) ) {
						$wp_filesystem->delete( $protected_thumb );
					} else {
						// Roll back: move the main file back to the protected dir.
						$wp_filesystem->move( $target_file, $current_file );
						foreach ( $restored_files as $restored ) {
							$pt = $current_base_dir . DIRECTORY_SEPARATOR . $restored;
							$tt = $target_base_dir . DIRECTORY_SEPARATOR . $restored;
							if ( $wp_filesystem->exists( $tt ) ) {
								$wp_filesystem->move( $tt, $pt );
							}
						}
						// translators: %1$s is the conflicting thumbnail file, %2$d is the attachment ID.
						error_log( sprintf( 'Unable to unprotect attachment %2$d: a different file already exists at %1$s.', $public_thumb, $attachment_id ) );
						return new WP_Error(
							'llms_media_unprotect_conflict',
							sprintf(
								// translators: %s is the conflicting public thumbnail path.
								__( 'A different file already exists at the original thumbnail location (%s). Please rename or remove it before unprotecting this media.', 'lifterlms' ),
								$public_thumb
							)
						);
					}
				} else {
					if ( ! $wp_filesystem->move( $protected_thumb, $public_thumb ) ) {
						// Roll back: move the main file back to the protected dir.
						$wp_filesystem->move( $target_file, $current_file );
						foreach ( $restored_files as $restored ) {
							$pt = $current_base_dir . DIRECTORY_SEPARATOR . $restored;
							$tt = $target_base_dir . DIRECTORY_SEPARATOR . $restored;
							if ( $wp_filesystem->exists( $tt ) ) {
								$wp_filesystem->move( $tt, $pt );
							}
						}
						// translators: %1$s is the protected thumbnail, %2$s is the target public thumbnail.
						error_log( sprintf( 'Unable to move protected thumbnail back to public dir: %1$s to %2$s', $protected_thumb, $public_thumb ) );
						return false;
					}
				}

				$restored_files[] = $size_info['file'];
			}
		}

		// Update attachment location in database.
		update_attached_file( $attachment_id, $target_file );

		// Restore the original metadata file path, if it was prefixed with the protected directory.
		if ( ! empty( $metadata ) && array_key_exists( 'file', $metadata ) ) {
			$protected_dir = $protector->get_upload_basedir();
			$prefix        = ltrim( $protected_dir, '/' );
			if ( 0 === strpos( $metadata['file'], $prefix ) ) {
				$metadata['file'] = substr( $metadata['file'], strlen( $prefix ) );
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		}

		// Remove the authorization meta and clear the recorded original location.
		$protector->remove_authorization_meta_from_media_post( $attachment_id );
		delete_post_meta( $attachment_id, LLMS_Media_Protector::ORIGINAL_FILE_META_KEY );

		return true;
	}

	/**
	 * Record the original public location of a media attachment before it is moved to the protected directory.
	 *
	 * @since [version]
	 *
	 * @param int   $attachment_id The attachment post ID.
	 * @param array $metadata      The current attachment metadata.
	 * @param string $file         The current `_wp_attached_file` absolute path.
	 * @return void
	 */
	protected function store_original_attached_file( $attachment_id, $metadata, $file ) {
		$relative = '';

		// Prefer the relative path stored in `_wp_attached_file` so it stays accurate even
		// if the upload base directory has been customized.
		$attached = get_post_meta( $attachment_id, '_wp_attached_file', true );
		if ( $attached ) {
			$relative = ltrim( $attached, '/' );
		}

		if ( ! $relative ) {
			return;
		}

		update_post_meta( $attachment_id, LLMS_Media_Protector::ORIGINAL_FILE_META_KEY, $relative );
	}

	/**
	 * Returns true if the two files exist and have the same size and md5 hash.
	 *
	 * @since [version]
	 *
	 * @param string $file_a First file path.
	 * @param string $file_b Second file path.
	 * @return bool
	 */
	protected function files_are_identical( $file_a, $file_b ) {
		if ( ! file_exists( $file_a ) || ! file_exists( $file_b ) ) {
			return false;
		}

		if ( filesize( $file_a ) !== filesize( $file_b ) ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_md5
		return md5_file( $file_a ) === md5_file( $file_b );
	}
}

new LLMS_Admin_Media_Protection_Attachment_Settings();
