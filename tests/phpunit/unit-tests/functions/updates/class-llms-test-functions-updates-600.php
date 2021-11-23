<?php
/**
* Test updates functions when updating to 6.0.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_600
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_600 extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-600.php';
	}

	private function call_ns_func( $func, $args = array() ) {
		return call_user_func( "LLMS\Updates\Version_6_0_0\\{$func}", ...$args );
	}

	private function create_legacy_awards( $count, $type ) {

		remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

		$res = array();
		$i = 0;
		while ( $i < $count ) {
			$post_type = "llms_my_{$type}";
			$image_id    = $attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
			$template_id = $this->factory->post->create( array( 'post_type' => "llms_{$type}" ) );
			$user_id     = $this->factory->user->create();
			$title       = sprintf( '%1$s Title %2$s', ucwords( $type ), wp_generate_password( 4, false ) );
			$post_id     = $this->factory->post->create( array(
				'post_type'  => $post_type,
				'meta_input' => array(
					"_llms_{$type}_template" => $template_id,
					"_llms_{$type}_image"    => $image_id,
					"_llms_{$type}_title"    => $title,
				),
			) );

			llms_update_user_postmeta(
				$user_id,
				$this->factory->post->create(),
				"_{$type}_earned",
				$post_id
			);

			$res[] = compact( 'template_id', 'user_id', 'image_id', 'post_id', 'title' );

			$i++;
		}
		add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

		return $res;

	}

	/**
	 * Test llms_update_520_update_db_version()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		$this->call_ns_func( 'update_db_version' );

		$this->assertEquals( '6.0.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

	public function test_update_award_metas() {

		$per_page = function() {
			return 5;
		};
		add_filter( 'llms_update_600_per_page', $per_page );

		$tests = array(
			'achievement',
			'certificate',
		);

		foreach ( $tests as $type ) {

			$awards = $this->create_legacy_awards( 12, $type );

			$this->assertTrue( $this->call_ns_func( "migrate_{$type}s", array( $type ) ) );
			$this->assertTrue( $this->call_ns_func( "migrate_{$type}s", array( $type ) ) );
			$this->assertFalse( $this->call_ns_func( "migrate_{$type}s", array( $type ) ) );

			foreach ( $awards as $i => $award ) {
				$post = get_post( $award['post_id'] );

				// Everything is migrated.
				$this->assertEquals( $award['user_id'], $post->post_author );
				$this->assertEquals( $award['template_id'], $post->post_parent );
				$this->assertEquals( $award['title'], $post->post_title );
				$this->assertEquals( $award['image_id'], get_post_thumbnail_id( $post ) );

				// Metadata is deleted.
				remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_title" ) );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_template" ) );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_image" ) );
				add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

			}

		}

		remove_filter( 'llms_update_600_per_page', $per_page );

	}

}
