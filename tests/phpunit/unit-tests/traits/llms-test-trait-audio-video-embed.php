<?php

/**
 * Tests for {@see LLMS_Trait_Audio_Video_Embed}.
 *
 * @group Traits
 * @group LLMS_Post_Model
 *
 * @since 5.3.0
 */
class LLMS_Test_Audio_Video_Embed_Trait extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Trait_Audio_Video_Embed
	 */
	protected $mock;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 */
	public function set_up() {

		parent::set_up();

		$args       = array(
			'post_title' => 'Mock Post with the Audio Video Embed Trait',
		);
		$this->mock = new class( 'new', $args ) extends LLMS_Post_Model {

			use LLMS_Trait_Audio_Video_Embed;

			protected $db_post_type = 'course'; # Limited to 20 characters.
			protected $model_post_type = 'course'; # Limited to 20 characters.

			public function __construct( $model, $args = array() ) {

				$this->construct_audio_video_embed();
				parent::__construct( $model, $args );
			}
		};
	}

	/**
	 * Test the {@see LLMS_Trait_Audio_Video_Embed::get_audio()} method.
	 *
	 * @since 5.3.0
	 */
	public function test_get_audio() {

		$url = 'http://example.tld/audio_embed';
		$this->mock->set( 'audio_embed', $url );
		$expected = do_shortcode( sprintf( '[%1$s src="%2$s"]', 'audio', $url ) );
		$actual   = $this->mock->get_audio();
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test the {@see LLMS_Trait_Audio_Video_Embed::get_embed()} method.
	 *
	 * @since 5.3.0
	 * @throws ReflectionException
	 */
	public function test_get_embed() {

		# Setup this test.
		$audio_url = 'http://example.tld/audio_embed';
		$this->mock->set( 'audio_embed', $audio_url );
		$video_url = 'http://example.tld/video_embed';
		$this->mock->set( 'video_embed', $video_url );
		$expected_audio = wp_audio_shortcode( array( 'src' => $audio_url ) );
		$expected_video = wp_video_shortcode( array( 'src' => $video_url ) );

		# Test all optional arguments.
		$actual = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_embed' );
		$this->assertEquals( $expected_video, $actual );

		# Test optional $prop argument.
		$actual = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_embed', array( 'type' => 'video' ) );
		$this->assertEquals( $expected_video, $actual );
		$actual = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_embed', array( 'type' => 'audio' ) );
		$this->assertEquals( $expected_audio, $actual );

		# Test with all arguments.
		$actual = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_embed', array(
			'type' => 'audio',
			'prop' => 'audio_embed',
		) );
		$this->assertEquals( $expected_audio, $actual );
		$actual = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_embed', array(
			'type' => 'video',
			'prop' => 'video_embed',
		) );
		$this->assertEquals( $expected_video, $actual );
	}

	/**
	 * Test that `youtube.com/embed/` and `youtube-nocookie.com/embed/` URLs are embedded via the watch URL.
	 *
	 * @since [version]
	 */
	public function test_get_embed_youtube_embed_urls() {

		$requested = array();
		$handler   = function ( $result, $url ) use ( &$requested ) {
			$requested[] = $url;
			return 0 === strpos( $url, 'https://www.youtube.com/watch' )
				? '<iframe src="https://www.youtube.com/embed/8S0FDjFBj8o?feature=oembed"></iframe>'
				: $result;
		};
		add_filter( 'pre_oembed_result', $handler, 10, 2 );

		// Privacy Enhanced Mode URL keeps the nocookie domain; `start` maps to `t`.
		$this->mock->set( 'video_embed', 'https://www.youtube-nocookie.com/embed/8S0FDjFBj8o?start=5' );
		$this->assertEquals( '<iframe src="https://www.youtube-nocookie.com/embed/8S0FDjFBj8o?feature=oembed"></iframe>', $this->mock->get_video() );
		$this->assertEquals( 'https://www.youtube.com/watch?v=8S0FDjFBj8o&t=5', end( $requested ) );

		// A youtube.com embed URL stays on youtube.com.
		$this->mock->set( 'video_embed', 'https://www.youtube.com/embed/8S0FDjFBj8o' );
		$this->assertEquals( '<iframe src="https://www.youtube.com/embed/8S0FDjFBj8o?feature=oembed"></iframe>', $this->mock->get_video() );
		$this->assertEquals( 'https://www.youtube.com/watch?v=8S0FDjFBj8o', end( $requested ) );

		remove_filter( 'pre_oembed_result', $handler, 10 );
	}

	/**
	 * Test the {@see LLMS_Trait_Audio_Video_Embed::get_video()} method.
	 *
	 * @since 5.3.0
	 */
	public function test_get_video() {

		$url = 'http://example.tld/video_embed';
		$this->mock->set( 'video_embed', $url );
		$expected = do_shortcode( sprintf( '[%1$s src="%2$s"]', 'video', $url ) );
		$actual   = $this->mock->get_video();
		$this->assertEquals( $expected, $actual );
	}
}
