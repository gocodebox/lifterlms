<?php
/**
 * Tests for LifterLMS Lesson Model
 * @since     [version]
 * @version   [version]
 * @group     post_models
 */
class LLMS_Test_LLMS_Lesson extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Lesson';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'lesson';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_properties() {
		return array(
			'assigned_quiz' => 'absint',
			'audio_embed' => 'text',
			'date_available' => 'text',
			'days_before_available' => 'absint',
			'drip_method' => 'text',
			'free_lesson' => 'yesno',
			'has_prerequisite' => 'yesno',
			'order' => 'absint',
			'parent_course' => 'absint',
			'parent_section' => 'absint',
			'prerequisite' => 'absint',
			'require_passing_grade' => 'yesno',
			'time_available' => 'text',
			'video_embed' => 'text',
		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_data() {
		return array(
			'assigned_quiz' => 123,
			'audio_embed' => 'http://example.tld/audio_embed',
			'date_available' => '11/21/2018',
			'days_before_available' => '24',
			'drip_method' => 'date',
			'free_lesson' => 'no',
			'has_prerequisite' => 'yes',
			'order' => 1,
			'parent_course' => 85,
			'parent_section' => 32,
			'prerequisite' => 344,
			'require_passing_grade' => 'yes',
			'time_available' => '12:34 PM',
			'video_embed' => 'http://example.tld/video_embed',
		);
	}


	/*
		   /$$                           /$$
		  | $$                          | $$
		 /$$$$$$    /$$$$$$   /$$$$$$$ /$$$$$$   /$$$$$$$
		|_  $$_/   /$$__  $$ /$$_____/|_  $$_/  /$$_____/
		  | $$    | $$$$$$$$|  $$$$$$   | $$   |  $$$$$$
		  | $$ /$$| $$_____/ \____  $$  | $$ /$$\____  $$
		  |  $$$$/|  $$$$$$$ /$$$$$$$/  |  $$$$//$$$$$$$/
		   \___/   \_______/|_______/    \___/ |_______/
	*/

	/**
	 * Test Audio and Video Embeds
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_embeds() {

		$audio_url = 'https://open.spotify.com/track/1rNUOtuCWv1qswqsMFvzvz';
		$video_url = 'https://www.youtube.com/watch?v=MhQlNwxn5oo';

		$lesson = new LLMS_Lesson( 'new', 'Lesson With Embeds' );

		// empty string when none set
		$this->assertEmpty( $lesson->get_audio() );
		$this->assertEmpty( $lesson->get_video() );

		$lesson->set( 'audio_embed', $audio_url );
		$lesson->set( 'video_embed', $video_url );

		$audio_embed = $lesson->get_audio();
		$video_embed = $lesson->get_video();

		// string
		$this->assertTrue( is_string( $audio_embed ) );
		$this->assertTrue( is_string( $video_embed ) );

		// should be an iframe for valid embeds
		$this->assertEquals( 0, strpos( $audio_embed, '<iframe' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<iframe' ) );

		// fallbacks should be a link to the URL
		$lesson->set( 'audio_embed', 'http://lifterlms.com/not/embeddable' );
		$lesson->set( 'video_embed', 'http://lifterlms.com/not/embeddable' );
		$this->assertEquals( 0, strpos( $audio_embed, '<a' ) );
		$this->assertEquals( 0, strpos( $video_embed, '<a' ) );

	}

	/**
	 * Test has_modified_slug function
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_has_modified_slug() {

		$lesson = new LLMS_Lesson( 'new', 'New Lesson' );

		// default unmodified slug
		$this->assertFalse( $lesson->has_modified_slug() );

		// default unmodifed slug with a unique int at the end
		$lesson->set( 'name', 'new-lesson-123' );

		$this->assertFalse( $lesson->has_modified_slug() );

		// renamed slug
		$lesson->set( 'name', 'modified-slug' );

		$this->assertTrue( $lesson->has_modified_slug() );

	}

}
