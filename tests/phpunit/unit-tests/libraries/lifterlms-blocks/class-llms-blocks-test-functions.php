<?php
/**
 * Test LLMS_Blocks_Page_Builders class & methods.
 *
 * @package LifterLMS_Blocks/Tests
 * @since   1.3.0
 * @version 1.3.3
 */
class LLMS_Blocks_Test_Functions extends LLMS_Blocks_Unit_Test_Case {

	/**
	 * Test the classic editor enabled check function.
	 *
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.3
	 */
	public function test_llms_blocks_is_classic_enabled_for_post() {

		$post_id = $this->factory->post->create();

		// Classic editor defaults (classic ed. enabled for all users).
		$this->assertTrue( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Enable block ed for all users.
		$this->update_classic_settings( array( 'editor' => 'block' ) );
		$this->assertFalse( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Classic default, users can choose.
		$this->update_classic_settings( array( 'editor' => 'classic', 'allow-users' => 'allow' ) );

		// No postmeta set (block editor is used by default despite what the setting seems to indicate).
		$this->assertFalse( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// classic explicitly enabled.
		update_post_meta( $post_id, 'classic-editor-remember', 'classic-editor' );
		$this->assertTrue( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Block explicitly enabled.
		update_post_meta( $post_id, 'classic-editor-remember', 'block-editor' );
		$this->assertFalse( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Enable block by default.
		$this->update_classic_settings( array( 'editor' => 'block', 'allow-users' => 'allow' ) );
		delete_post_meta( $post_id, 'classic-editor-remember' );

		// No postmeta set (block editor is used by default).
		$this->assertFalse( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// classic explicitly enabled.
		update_post_meta( $post_id, 'classic-editor-remember', 'classic-editor' );
		$this->assertTrue( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Block explicitly enabled.
		update_post_meta( $post_id, 'classic-editor-remember', 'block-editor' );
		$this->assertFalse( llms_blocks_is_classic_enabled_for_post( $post_id ) );

		// Enable block ed for all users, even with block-editor meta classic will still be used.
		$this->update_classic_settings( array( 'editor' => 'classic' ) );
		$this->assertTrue( llms_blocks_is_classic_enabled_for_post( $post_id ) );

	}

	/**
	 * Test the llms_blocks_is_post_migrated() method
	 * @return  void
	 * @since   1.3.1
	 * @version 1.3.3
	 */
	public function test_llms_blocks_is_post_migrated() {

		// Block editor on.
		$this->update_classic_settings( array( 'editor' => 'block' ) );

		// Post to test against.
		$post_id = $this->factory->post->create();

		// No postemta.
		$this->assertFalse( llms_blocks_is_post_migrated( $post_id ) );

		// Postmeta Explicitly on.
		update_post_meta( $post_id, '_llms_blocks_migrated', 'yes' );
		$this->assertTrue( llms_blocks_is_post_migrated( $post_id ) );

		// Postmeta Explicitly off.
		update_post_meta( $post_id, '_llms_blocks_migrated', 'no' );
		$this->assertFalse( llms_blocks_is_post_migrated( $post_id ) );

		// Classic editor on.
		$this->update_classic_settings( array( 'editor' => 'classic' ) );

		// Classic editor enabled & not migrated.
		$this->assertFalse( llms_blocks_is_post_migrated( $post_id ) );

		// Classic editor enabled & migrated.
		update_post_meta( $post_id, '_llms_blocks_migrated', 'yes' );
		$this->assertFalse( llms_blocks_is_post_migrated( $post_id ) );

		// Classic Editor enabled & not migrated.
		update_post_meta( $post_id, '_llms_blocks_migrated', 'no' );
		$this->assertFalse( llms_blocks_is_post_migrated( $post_id ) );

	}


}
