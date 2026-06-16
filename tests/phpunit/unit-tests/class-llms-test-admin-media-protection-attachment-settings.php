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
 * @version [version]
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
	 * Test that the select2 markup allows clearing and shows the original location when the file is protected.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attachment_fields_to_edit_allows_clearing_when_protected() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		$attachment    = get_post( $attachment_id );

		// Mark the attachment as protected with a recorded original location.
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
		update_post_meta( $attachment_id, LLMS_Media_Protector::ORIGINAL_FILE_META_KEY, '2024/05/test.jpg' );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$fields   = $settings->attachment_fields_to_edit( array(), $attachment );

		$this->assertStringContainsString( "data-allow_clear='true'", $fields['llms_media_protection_post']['html'] );
		$this->assertStringContainsString( 'llms-media-protection-original-location', $fields['llms_media_protection_post']['html'] );
		$this->assertStringContainsString( '2024/05/test.jpg', $fields['llms_media_protection_post']['html'] );
		$this->assertStringContainsString( 'llms-media-protection-unprotect-warning', $fields['llms_media_protection_post']['html'] );

	}

	/**
	 * Test that the select2 markup does not include the original-location note when the file is not protected.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attachment_fields_to_edit_omits_original_location_when_unprotected() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		$attachment    = get_post( $attachment_id );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$fields   = $settings->attachment_fields_to_edit( array(), $attachment );

		$this->assertStringNotContainsString( 'llms-media-protection-original-location', $fields['llms_media_protection_post']['html'] );
		$this->assertStringNotContainsString( 'llms-media-protection-unprotect-warning', $fields['llms_media_protection_post']['html'] );

	}

	/**
	 * Test that the unprotect method refuses to operate on an unprotected file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_move_attachment_to_public_dir_returns_false_for_unprotected_file() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$this->assertFalse( $settings->move_attachment_to_public_dir( $attachment_id ) );

	}

	/**
	 * Test that the unprotect method refuses to operate when no original location is recorded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_move_attachment_to_public_dir_returns_false_without_original_location() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$this->assertFalse( $settings->move_attachment_to_public_dir( $attachment_id ) );

	}

	/**
	 * Test the get_original_attached_file helper on the media protector class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_original_attached_file_returns_recorded_path() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		update_post_meta( $attachment_id, LLMS_Media_Protector::ORIGINAL_FILE_META_KEY, '2023/01/file.png' );

		$protector = new LLMS_Media_Protector();
		$this->assertEquals( '2023/01/file.png', $protector->get_original_attached_file( $attachment_id ) );

	}

	/**
	 * Test the get_original_attached_file helper returns an empty string when no path is recorded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_original_attached_file_returns_empty_when_missing() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );

		$protector = new LLMS_Media_Protector();
		$this->assertEquals( '', $protector->get_original_attached_file( $attachment_id ) );

	}

	/**
	 * Test that the remove helper clears the authorization meta for a valid post ID.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_remove_authorization_meta_clears_meta() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );

		$protector = new LLMS_Media_Protector();
		$this->assertTrue( $protector->is_media_protected( $attachment_id ) );

		$protector->remove_authorization_meta_from_media_post( $attachment_id );
		$this->assertFalse( $protector->is_media_protected( $attachment_id ) );

	}

	/**
	 * Test that saving with an empty value unprotects the file when it was protected.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attachment_fields_to_save_unprotects_when_value_cleared() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );

		// Pre-mark the attachment as protected and set the original location meta.
		update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
		update_post_meta( $attachment_id, LLMS_Media_Protector::ORIGINAL_FILE_META_KEY, '2024/01/file.jpg' );
		update_post_meta( $attachment_id, '_llms_media_protection_product_id', 1 );

		// Stub the unprotect method so we do not need an actual file on disk.
		$stub = $this->getMockBuilder( 'LLMS_Admin_Media_Protection_Attachment_Settings' )
			->onlyMethods( array( 'move_attachment_to_public_dir' ) )
			->disableOriginalConstructor()
			->getMock();
		$stub->expects( $this->once() )
			->method( 'move_attachment_to_public_dir' )
			->with( $attachment_id )
			->willReturn( true );

		$post       = array( 'ID' => $attachment_id );
		$attachment = array( 'llms_media_protection_post' => '' );
		$stub->attachment_fields_to_save( $post, $attachment );

		$product_meta = get_post_meta( $attachment_id, '_llms_media_protection_product_id', true );
		$this->assertEmpty( $product_meta );

	}

	/**
	 * Test that saving with an empty value is a no-op for an already-unprotected file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attachment_fields_to_save_does_nothing_when_unprotected_and_cleared() {

		$attachment_id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );

		$settings = new LLMS_Admin_Media_Protection_Attachment_Settings();
		$post     = array( 'ID' => $attachment_id );
		$result   = $settings->attachment_fields_to_save( $post, array( 'llms_media_protection_post' => '' ) );

		// Returns the post data untouched.
		$this->assertEquals( $post, $result );

	}

}
