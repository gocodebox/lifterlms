<?php
/**
 * Test Admin Notices Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group metaboxes
 *
 * @since [version]
 */
class LLMS_Test_Admin_Meta_Boxes extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/class.llms.meta.boxes.php';
	}

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Meta_Boxes();

	}

	/**
	 * Test maybe_modify_post_thumbnail_html().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_modify_post_thumbnail_html() {

		$types = array(
			'post'             => false,
			'llms_achievement' => true,
			'llms_certificate' => true,
		);

		foreach ( $types as $post_type => $modified ) {

			$post = $this->factory->post->create( compact( 'post_type' ) );

			// Without image.
			$res = $this->main->maybe_modify_post_thumbnail_html( 'Content', $post, '' );

			if ( $modified ) {
				$this->assertStringContainsString( 'Using the global default.', $res );
				$this->assertStringContainsString( '<img ', $res );
				$this->assertStringContainsString( 'Content', $res );
			} else {
				$this->assertEquals( 'Content', $res );
			}

			// With an image.
			$res = $this->main->maybe_modify_post_thumbnail_html( 'Content', $post, 123 );
			$this->assertEquals( 'Content', $res );

		}

	}

	/**
	 * Test maybe_modify_title_placeholder().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_modify_title_placeholder() {

		$types = array(
			'post'             => 'Default Placeholder',
			'llms_achievement' => 'Default Placeholder (for internal use only)',
			'llms_certificate' => 'Default Placeholder (for internal use only)',
		);

		foreach ( $types as $post_type => $expect ) {
			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertEquals( $expect, $this->main->maybe_modify_title_placeholder( 'Default Placeholder', $post ) );
		}

	}

	/**
	 * Test sync awarded certificates action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */

	public function test_sync_awarded_certificates_action() {

		$post   = $this->factory->post->create_and_get();
		$action = 'action=sync_awarded_certificates';
		// No certificate post type.
		$this->assertOutputNotContains(
			$action,
			array(
				$this->main,
				'sync_awarded_certificates_action',
			),
			array( $post )
		);

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );

		// llms_certificate post type but no awarded certificates.
		$this->assertOutputNotContains(
			$action,
			array(
				$this->main,
				'sync_awarded_certificates_action',
			),
			array( $post )
		);

		$awarded_certificates = array();

		// Create various awarded certificates but with a different template.
		foreach ( get_available_post_statuses( 'llms_my_certificate' ) as $status ) {
			$awarded_certificates[] = $this->factory->post->create_and_get(
				array(
					'post_type'   => 'llms_my_certificate',
					'post_parent' => 999,
					'post_status' => $status,
				)
			);
		}
		$this->assertOutputNotContains(
			$action,
			array(
				$this->main,
				'sync_awarded_certificates_action',
			),
			array( $post )
		);

		// Create various awarded certificates: only 2 of them have the required post_status (publish and future).
		foreach ( get_available_post_statuses( 'llms_my_certificate' ) as $status ) {
			$awarded_certificates[] = $this->factory->post->create_and_get(
				array(
					'post_type'   => 'llms_my_certificate',
					'post_parent' => $post->ID,
					'post_status' => $status,
				)
			);
		}

		$this->assertOutputContains(
			$action,
			array(
				$this->main,
				'sync_awarded_certificates_action',
			),
			array( $post )
		);

		$this->assertOutputContains(
			'2 awarded certificates',
			array(
				$this->main,
				'sync_awarded_certificates_action',
			),
			array( $post )
		);

	}

}
