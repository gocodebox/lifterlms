<?php
/**
 * Test updates functions when updating to 11.1.1.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_1111
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_1111 extends LLMS_UnitTestCase {

	/**
	 * Setup before class.
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-1111.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Number of items per update batch.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function per_page() {
		return 2;
	}

	/**
	 * Create an attachment, optionally protected.
	 *
	 * @since [version]
	 *
	 * @param bool $protected Whether to protect the attachment.
	 * @return int
	 */
	private function create_image( $protected = true ) {
		$id = $this->factory->post->create( array( 'post_type' => 'attachment' ) );
		if ( $protected ) {
			update_post_meta( $id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
		}
		return $id;
	}

	/**
	 * Test that protected question images are linked to their quiz across batches, including inside a question group,
	 * and unprotected images and existing links are left alone.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_link_question_images_to_quizzes() {

		add_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );

		$quiz_id  = $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) );
		$quiz     = llms_get_post( $quiz_id );
		$group_id = $quiz->questions()->create_question( array( 'title' => 'Group', 'question_type' => 'group' ) );

		$protected   = $this->create_image();
		$in_group    = $this->create_image();
		$linked      = $this->create_image();
		$unprotected = $this->create_image( false );
		update_post_meta( $linked, '_llms_quiz_id', array( 98765 ) );

		foreach ( array( $protected, $linked, $unprotected ) as $image_id ) {
			$quiz->questions()->create_question( array( 'title' => 'Q', 'image' => array( 'enabled' => 'yes', 'id' => $image_id, 'src' => '' ) ) );
		}
		llms_get_post( $group_id )->questions()->create_question( array( 'title' => 'Q', 'image' => array( 'enabled' => 'yes', 'id' => $in_group, 'src' => '' ) ) );

		// 4 rows in batches of 2: two full pages, then an empty one ends the update.
		$this->assertTrue( \LLMS\Updates\Version_11_1_1\link_question_images_to_quizzes() );
		$this->assertTrue( \LLMS\Updates\Version_11_1_1\link_question_images_to_quizzes() );
		$this->assertFalse( \LLMS\Updates\Version_11_1_1\link_question_images_to_quizzes() );
		$this->assertFalse( get_option( 'llms_question_image_quiz_link_cursor' ) );

		$this->assertEquals( array( $quiz_id ), get_post_meta( $protected, '_llms_quiz_id', true ) );
		$this->assertEquals( array( $quiz_id ), get_post_meta( $in_group, '_llms_quiz_id', true ) );
		$this->assertEquals( array( 98765, $quiz_id ), get_post_meta( $linked, '_llms_quiz_id', true ) );
		$this->assertEmpty( get_post_meta( $unprotected, '_llms_quiz_id', true ) );

		remove_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
	}
}
