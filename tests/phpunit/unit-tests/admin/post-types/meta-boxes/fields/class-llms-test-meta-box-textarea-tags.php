<?php
/**
 * Tests for LifterLMS Order Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_textarea_w_tags
 * @group admin
 * @group metaboxes
 * @group metaboxes_fields
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Metabox_Textarea_W_Tags_Field extends LLMS_Unit_Test_Case {


	/**
	 * Setup before class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.textarea.tags.php';

	}

	/**
	 * Test output when passing a custom value.
	 *
	 * @return void
	 */
	public function test_output_without_custom_value() {
		// Set-up global post.
		global $post;
		$original_post = $post;

		$post = $this->factory->post->create_and_get();
		update_post_meta( $post->ID, 'without_custom_value', 'This should show' );

		$field = new LLMS_Metabox_Textarea_W_Tags_Field(
			array(
				'type'       => 'textarea_w_tags',
				'label'      => __( 'Test', 'lifterlms' ),
				'id'         => 'without_custom_value',
				'class'      => 'code input-full',
				'value'      => '',
			),
		);

		$this->assertOutputContains(
			'>This should show</textarea>',
			array(
				$field,
				'output'
			)
		);

		delete_post_meta( $post->ID, 'without_custom_value' );

		$field = new LLMS_Metabox_Textarea_W_Tags_Field(
			array(
				'type'       => 'textarea_w_tags',
				'label'      => __( 'Test', 'lifterlms' ),
				'id'         => 'without_custom_value',
				'class'      => 'code input-full',
				'value'      => '',
			),
		);
		$this->assertOutputContains(
			'></textarea>',
			array(
				$field,
				'output'
			)
		);

		// Reset global post.
		$post = $original_post;

	}

	/**
	 * Test output when passing a custom value.
	 *
	 * @return void
	 */
	public function test_output_with_custom_value() {

		// Set-up global post.
		global $post;
		$original_post = $post;

		$post = $this->factory->post->create_and_get();
		update_post_meta( $post->ID, 'with_custom_value', 'This should not show' );

		$field = new LLMS_Metabox_Textarea_W_Tags_Field(
			array(
				'type'       => 'textarea_w_tags',
				'label'      => __( 'Test', 'lifterlms' ),
				'id'         => 'with_custom_value',
				'class'      => 'code input-full',
				'value'      => 'Custom Value',
			),
		);

		$this->assertOutputContains(
			'>Custom Value</textarea>',
			array(
				$field,
				'output'
			)
		);

		// Reset global post.
		$post = $original_post;

	}

}
