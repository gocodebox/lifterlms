<?php
/**
 * LLMS_Admin_Media_Protection_Attachment_Settings tests.
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group media_protection
 *
 * @since 10.0.0
 */
class LLMS_Test_Admin_Media_Protection_Attachment_Settings extends LLMS_UnitTestCase {

	/**
	 * Load the class file.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-media-protection-attachment-settings.php';

	}

	/**
	 * Test the attachment field can be filtered.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_attachment_fields_to_edit_allows_media_protection_field_filter() {

		$attachment = get_post(
			$this->factory->post->create(
				array(
					'post_type' => 'attachment',
				)
			)
		);

		$filter = function( $field, $post, $protector ) use ( $attachment ) {
			$this->assertEquals( $attachment->ID, $post->ID );
			$this->assertInstanceOf( 'LLMS_Media_Protector', $protector );

			$field['html']  = '<p>Filtered media protection output.</p>';
			$field['helps'] = 'Filtered help text.';

			return $field;
		};

		add_filter( 'llms_media_protection_attachment_field', $filter, 10, 3 );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$fields   = $settings->attachment_fields_to_edit( array(), $attachment );

		remove_filter( 'llms_media_protection_attachment_field', $filter, 10 );

		$this->assertEquals( '<p>Filtered media protection output.</p>', $fields['llms_media_protection_post']['html'] );
		$this->assertEquals( 'Filtered help text.', $fields['llms_media_protection_post']['helps'] );

	}

	/**
	 * Test moving an attachment into a protected folder that doesn't exist yet writes the protection files.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_move_attachment_to_protected_dir_writes_protection_files() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$upload_path = wp_get_upload_dir()['basedir'] . '/lifterlms';
		$GLOBALS['wp_filesystem']->rmdir( $upload_path, true );

		// The daily check already ran (e.g. on an earlier admin request) while the folder didn't exist.
		set_transient( 'lifterlms_check_media_protection_files', true, DAY_IN_SECONDS );

		$file = wp_upload_bits( 'llms-move-test-' . uniqid() . '.txt', null, 'test' );
		$this->assertEmpty( $file['error'] );
		$attachment_id = $this->factory->attachment->create_object( $file['file'], 0, array( 'post_mime_type' => 'text/plain' ) );
		wp_update_attachment_metadata( $attachment_id, array( 'filesize' => 4 ) );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$this->assertTrue( $settings->move_attachment_to_protected_dir( $attachment_id ) );

		$this->assertStringStartsWith( $upload_path . '/', get_attached_file( $attachment_id ) );
		$this->assertFileExists( $upload_path . '/.htaccess' );
		$this->assertStringContainsString( 'deny from all', file_get_contents( $upload_path . '/.htaccess' ) );

		$GLOBALS['wp_filesystem']->rmdir( $upload_path, true );

	}

	/**
	 * Test moving a scaled image into the protected folder also moves the original full-size image.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_move_attachment_to_protected_dir_moves_original_image() {

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();

		$upload_path = wp_get_upload_dir()['basedir'] . '/lifterlms';
		$name        = 'llms-move-test-' . uniqid();

		// WordPress keeps the original upload next to the "-scaled" file it creates for big images.
		$scaled   = wp_upload_bits( $name . '-scaled.jpg', null, 'scaled' );
		$original = dirname( $scaled['file'] ) . '/' . $name . '.jpg';
		file_put_contents( $original, 'original' );

		$attachment_id = $this->factory->attachment->create_object( $scaled['file'], 0, array( 'post_mime_type' => 'image/jpeg' ) );
		wp_update_attachment_metadata(
			$attachment_id,
			array(
				'file'           => _wp_relative_upload_path( $scaled['file'] ),
				'original_image' => $name . '.jpg',
			)
		);

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$this->assertTrue( $settings->move_attachment_to_protected_dir( $attachment_id ) );

		$this->assertFileDoesNotExist( $original );
		$this->assertStringStartsWith( $upload_path . '/', wp_get_original_image_path( $attachment_id ) );
		$this->assertSame( 'original', file_get_contents( wp_get_original_image_path( $attachment_id ) ) );

		$GLOBALS['wp_filesystem']->rmdir( $upload_path, true );

	}

}
