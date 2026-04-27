<?php
/**
 * LLMS_Admin_Media_Protection_Attachment_Settings tests.
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group media_protection
 *
 * @since [version]
 */
class LLMS_Test_Admin_Media_Protection_Attachment_Settings extends LLMS_UnitTestCase {

	/**
	 * Load the class file.
	 *
	 * @since [version]
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
	 * @since [version]
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

}
