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
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Setup the test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		add_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
		delete_option( 'lifterlms_has_legacy_certificates' );

	}

	/**
	 * Tear down the test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		remove_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
		delete_option( 'lifterlms_has_legacy_certificates' );

	}

	/**
	 * Callback function to reduce items per page for testing.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function per_page() {
		return 5;
	}

	/**
	 * Calls a namespaced function with the specified arguments.
	 *
	 * @since [version]
	 *
	 * @param [type] $func [description]
	 * @param array $args [description]
	 * @return [type] [description]
	 */
	private function call_ns_func( $func, $args = array() ) {
		return call_user_func( "LLMS\Updates\Version_6_0_0\\{$func}", ...$args );
	}

	/**
	 * Creates one or more awards of a given type using the pre-migration data structure.
	 *
	 * @since [version]
	 *
	 * @param int    $count Number of award posts to create.
	 * @param string $type  Type of award, either "achievement" or "certificate".
	 * @return array[] {
	 *     Array of data arrays describing the generated award.
	 *
	 *     @type int    $template_id WP_Post id of the template post.
	 *     @type int    $user_id     WP_User id of the user who earned the award.
	 *     @type int    $image_id    WP_Post id of the attachment post for the award's image.
	 *     @type int    $post_id     WP_Post id of the award post.
	 *     @type string $title       Title of the award.
	 * }
	 */
	private function create_legacy_awards( $count, $type ) {

		remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
		remove_action( "save_post_llms_my_{$type}", array( 'LLMS_Controller_Awards', 'on_save' ), 20 );

		$res = array();
		$i = 0;
		while ( $i < $count ) {
			$post_type   = "llms_my_{$type}";
			$image_id    = $attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
			$template_id = $this->factory->post->create( array( 'post_type' => "llms_{$type}" ) );
			$user_id     = $this->factory->user->create();
			$title       = sprintf( '%1$s Title %2$s', ucwords( $type ), wp_generate_password( 4, false ) );
			$meta_input  = array(
				"_llms_{$type}_template" => $template_id,
				"_llms_{$type}_image"    => $image_id,
				"_llms_{$type}_title"    => $title,
			);
			if ( 'achievement' === $type ) {
				$meta_input['_llms_achievement_content'] = 'Some content.';
			}
			$post_id     = $this->factory->post->create( array(
				'post_type'  => $post_type,
				'meta_input' => $meta_input,
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
		add_action( "save_post_llms_my_{$type}", array( 'LLMS_Controller_Awards', 'on_save' ), 20 );

		return $res;

	}

	/**
	 * Creates one or more award templates of a given type using the pre-migration data structure.
	 *
	 * @since [version]
	 *
	 * @param int    $count Number of award posts to create.
	 * @return array[] {
	 *     Array of data arrays describing the generated template.
	 *
	 *     @type int    $image_id    WP_Post id of the attachment post for the award's image.
	 *     @type int    $post_id     WP_Post id of the award post.
	 * }
	 */
	private function create_legacy_templates( $count ) {

		$res = array();
		$i = 0;
		while ( $i < $count ) {
			$post_type   = array( 'llms_achievement', 'llms_certificate' );
			shuffle( $post_type );
			$post_type   = $post_type[0];
			$type        = str_replace( 'llms_', '', $post_type );
			$image_id    = $attachment_id = $this->create_attachment( 'christian-fregnan-unsplash.jpg' );
			$meta_input  = array(
				"_llms_{$type}_image"    => $image_id,
			);
			if ( 'llms_achievement' === $post_type ) {
				$meta_input['_llms_achievement_content'] = 'Some content.';
			}
			$post_id     = $this->factory->post->create( array(
				'post_type'  => $post_type,
				'meta_input' => $meta_input,
			) );

			$res[] = compact( 'image_id', 'post_id', 'type' );

			$i++;
		}

		return $res;

	}

	/**
	 * Test update_db_version()
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

		$this->assertEquals( '6.0.0-beta.1', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

	/**
	 * Test the migrate_achievements() and migrate_certificates() functions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_award_metas() {

		$per_page = function() {
			return 5;
		};
		add_filter( 'llms_update_items_per_page', $per_page );

		$tests = array(
			'achievement',
			'certificate',
		);

		foreach ( $tests as $type ) {

			delete_option( 'lifterlms_has_legacy_certificates' );

			$awards = $this->create_legacy_awards( 12, $type );

			// Should run 3 times, the 3rd has fewer than max results so we're complete.
			$i = 1;
			while ( $i <= 3 ) {
				$this->assertEquals( $i !== 3, $this->call_ns_func( "migrate_{$type}s", array( $type ) ) );
				$i++;
			}

			foreach ( $awards as $i => $award ) {

				$post = get_post( $award['post_id'] );

				// Everything is migrated.
				$this->assertEquals( $award['user_id'], $post->post_author );
				$this->assertEquals( $award['template_id'], $post->post_parent );
				$this->assertEquals( $award['title'], $post->post_title );
				$this->assertEquals( $award['image_id'], get_post_thumbnail_id( $post ) );
				if ( 'achievement' === $type ) {
					$this->assertEquals( 'Some content.', $post->post_content );
				}

				// Metadata is deleted.
				remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_achievement_content" ) );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_title" ) );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_template" ) );
				$this->assertFalse( metadata_exists( 'post', $post->ID, "_llms_{$type}_image" ) );
				add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

			}

			$this->assertEquals( 'certificate' === $type ? 'yes' : 'no', get_option( 'lifterlms_has_legacy_certificates', 'no' ) );

		}

		remove_filter( 'llms_update_600_per_page', $per_page );

	}

	/**
	 * Test the migrate_achievements() and migrate_certificates() functions when none exist to migrate.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_award_metas_none_found() {

		$tests = array(
			'achievement',
			'certificate',
		);

		foreach ( $tests as $type ) {

			$this->assertFalse( $this->call_ns_func( "migrate_{$type}s", array( $type ) ) );
			$this->assertEquals( 'no', get_option( 'lifterlms_has_legacy_certificates', 'no' ) );

		}

	}

	/**
	 * Test migate_award_templates()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_award_templates() {

		$templates = $this->create_legacy_templates( 20 );

		// Should run 5 times, the 5th has no results and the migration is complete.
		$i = 1;
		while ( $i <= 5 ) {
			$this->assertEquals( $i !== 5, $this->call_ns_func( 'migrate_award_templates' ) );
			$i++;
		}

		foreach ( $templates as $template ) {
			$this->assertEquals( $template['image_id'], get_post_thumbnail_id( $template['post_id'] ) );
			$this->assertFalse( metadata_exists( 'post', $template['post_id'], "_llms_{$template['type']}_image" ) );

			if ( 'achievement' === $template['type'] ) {
				$post = get_post( $template['post_id'] );
				$this->assertEquals( 'Some content.', $post->post_content );
				remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
				$this->assertFalse( metadata_exists( 'post', $template['post_id'], '_llms_achievement_content' ) );
				add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
			}

		}

		$this->assertEquals( 'yes', get_option( 'lifterlms_has_legacy_certificates', 'no' ) );

	}

	/**
	 * Test show_notice()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_show_notice() {

		$notice = 'v600alpha1-welcome-msg';

		// require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		$this->assertFalse( LLMS_Admin_Notices::has_notice( $notice ) );

		$this->call_ns_func( 'show_notice' );

		$this->assertTrue( true, LLMS_Admin_Notices::has_notice( $notice ) );

		// Cleanup.
		LLMS_Admin_Notices::delete_notice( $notice );

	}

}
