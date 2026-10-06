<?php
/**
 * Test LLMS_Blocks_Migrate class & methods.
 *
 * @package LifterLMS_Blocks/Tests
 *
 * @since 1.3.1
 * @since 1.4.0 Add tests for `add_template_to_post()` and `remove_template_from_post()` methods.
 * @since 1.7.0 Add tests for membership post type migrations.
 * @since 1.8.0 Add test on old course progress bar block removal.
 */
class LLMS_Blocks_Test_Migrate extends LLMS_Blocks_Unit_Test_Case {

	/**
	 * Assertion to compare post content while removing all returns and tabs.
	 *
	 * @since 1.4.0
	 *
	 * @param string $expected Expected string.
	 * @param string $actual Actual string.
	 * @return void
	 */
	private function assertContentEquals( $expected, $actual ) {

		$dirty = array( "\r", "\n", "\t" );
		$this->assertSame( str_replace( $dirty, '', $expected ), str_replace( $dirty, '', $actual ) );

	}

	private function get_post_content( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( "SELECT post_content FROM {$wpdb->posts} WHERE ID = {$post_id};" );
	}

	/**
	 * Read post_modified straight from the posts table.
	 *
	 * @since [version]
	 *
	 * @param int $post_id Post ID.
	 * @return string|null
	 */
	private function get_post_modified( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_modified FROM {$wpdb->posts} WHERE ID = %d", $post_id ) );
	}

	/**
	 * Test the add_template_to_post() and remove_template_from_post() methods.
	 *
	 * @since 1.4.0
	 * @since 1.8.0 Add test on old course progress bar block removal.
	 *
	 * @return void
	 */
	public function test_add_remove_templates() {

		$migrate = new LLMS_Blocks_Migrate();

		$blocks = '<!-- wp:heading -->
<h2>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';

		// Posts to create and test against.
		$args = array(
			array(
				'post_type' => 'course',
			),
			array(
				'post_type' => 'course',
				'post_content' => $blocks,
			),
			array(
				'post_type' => 'lesson',
			),
			array(
				'post_type' => 'lesson',
				'post_content' => $blocks,
			),
			array(
				'post_type' => 'llms_membership',
			),
			array(
				'post_type' => 'llms_membership',
				'post_content' => $blocks,
			),
		);
		foreach ( $args as $args ) {

			// Add to post.
			$post = $this->factory->post->create_and_get( $args );
			$orig_content = $post->post_content;
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $migrate, 'add_template_to_post', array( $post ) ) );
			$this->assertContentEquals( $post->post_content . "\r\r" . LLMS_Unit_Test_Util::call_method( $migrate, 'get_template', array( $post->post_type ) ), $this->get_post_content( $post->ID ) );
			$this->assertSame( 'yes', get_post_meta( $post->ID, '_llms_blocks_migrated', true ) );

			clean_post_cache( $post->ID );

			// Remove from post.
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $migrate, 'remove_template_from_post', array( get_post( $post->ID ) ) ) );
			$this->assertContentEquals( $orig_content, $this->get_post_content( $post->ID ) );
			$this->assertSame( 'no', get_post_meta( $post->ID, '_llms_blocks_migrated', true ) );

			// do it again with more complicated post content (changes have been made to the default template, for example.)
			$post = $this->factory->post->create_and_get( $args );
			$orig_content = $post->post_content;
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $migrate, 'add_template_to_post', array( $post ) ) );
			if ( 'course' === $args['post_type'] ) {
				$content = str_replace(
					array(
						'<!-- wp:llms/course-information /-->',
						'[lifterlms_course_progress check_enrollment=1]' // replace the current course progress shortcode usage with the old one.
					),
					array(
						'<!-- wp:llms/course-information {"show_cats":false,"show_difficulty":false,"llms_visibility":"enrolled","llms_visibility_in":"this"} /-->',
						'[lifterlms_course_progress]' // old progress shortcode usage.
					),
					$this->get_post_content( $post->ID )
				);

				$content .= '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
				$expect = $orig_content . '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
			} elseif ( 'lesson' === $args['post_type'] ) {
				$content = str_replace( '<!-- wp:llms/lesson-progression /-->', '<!-- wp:llms/lesson-progression {"llms_visibility":"enrolled","llms_visibility_in":"this"} /-->', $this->get_post_content( $post->ID ) );
				$content .= '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
				$expect = $orig_content . '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
			} elseif ( 'llms_membership' === $args['post_type'] ) {
				$content = str_replace( '<!-- wp:llms/pricing-table /-->', '<!-- wp:llms/pricing-table {"llms_visibility":"enrolled","llms_visibility_in":"this"} /-->', $this->get_post_content( $post->ID ) );
				$content .= '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
				$expect = $orig_content . '
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hoc enim constituto in philosophia constituta sunt omnia.</p>
<!-- /wp:paragraph -->';
			}
			LLMS_Unit_Test_Util::call_method( $migrate, 'update_post_content', array( $post->ID, $content ) );

			clean_post_cache( $post->ID );

			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $migrate, 'remove_template_from_post', array( get_post( $post->ID ) ) ) );
			$this->assertContentEquals( $expect, $this->get_post_content( $post->ID ) );

		}

	}

	/**
	 * Test the check_sales_page() method
	 *
	 * @return  void
	 * @since   1.3.1
	 * @version 1.3.1
	 */
	public function test_check_sales_page() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->go_to( get_permalink( $post_id ) );

		// Post migrated & no sales page is not setup (legacy active).
		$this->assertFalse( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

		// Migrated & sales page explicitly on.
		update_post_meta( $post_id, '_llms_sales_page_content_type', 'content' );
		$this->assertFalse( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

		// No sales page
		update_post_meta( $post_id, '_llms_sales_page_content_type', 'none' );
		$this->assertTrue( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

		delete_post_meta( $post_id, '_llms_sales_page_content_type' );

		// Not restricted content.
		$student = $this->factory->student->create_and_get();
		$student->enroll( $post_id );
		wp_set_current_user( $student->get_id() );

		// Post migrated & no sales page is not setup (legacy active).
		$this->assertTrue( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

		// Migrated & sales page explicitly on.
		update_post_meta( $post_id, '_llms_sales_page_content_type', 'content' );
		$this->assertTrue( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

		// No sales page
		update_post_meta( $post_id, '_llms_sales_page_content_type', 'none' );
		$this->assertTrue( LLMS_Blocks_Migrate::check_sales_page( true, $post_id ) );

	}

	/**
	 * Test get_migrateable_post_types() method
	 *
	 * @since 1.3.3
	 * @since 1.7.0 Memberships are migrateable.
	 *
	 * @return  void
	 */
	public function test_get_migrateable_post_types() {

		$class = new LLMS_Blocks_Migrate();
		$this->assertEquals( array( 'course', 'lesson', 'llms_membership' ), $class->get_migrateable_post_types() );

	}

	/**
	 * Test should_migrate_post() method
	 *
	 * @since 1.3.3
	 * @since 1.7.0 Memberships should migrate.
	 *
	 * @return  void
	 */
	public function test_should_migrate_post() {

		$class = new LLMS_Blocks_Migrate();
		$this->update_classic_settings( array( 'editor' => 'block', 'allow-users' => false ) );

		// test various post types
		$types = array(
			'post' => false,
			'page' => false,
			'course' => true,
			'lesson' => true,
			'section' => false,
			'llms_membership' => true,
		);
		foreach ( $types as $type => $expect ) {

			$id = $this->factory->post->create( array( 'post_type' => $type ) );
			$this->assertEquals( $expect, $class->should_migrate_post( $id ) );

		}

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Classic ed enabled, don't migrate.
		$this->update_classic_settings( array( 'editor' => 'classic', 'allow-users' => false ) );
		$this->assertFalse( $class->should_migrate_post( $id ) );

		// Block enabled, go.
		$this->update_classic_settings( array( 'editor' => 'block', 'allow-users' => false ) );
		$this->assertTrue( $class->should_migrate_post( $id ) );

		// already migrated
		update_post_meta( $id, '_llms_blocks_migrated', 'yes' );
		$this->assertFalse( $class->should_migrate_post( $id ) );

	}

	/**
	 * migrate_post() writes only when the current user can edit that post.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_post_requires_edit_cap() {

		global $pagenow;

		$previous_pagenow = $pagenow;
		$pagenow          = 'post.php';

		$this->update_classic_settings(
			array(
				'editor'      => 'block',
				'allow-users' => false,
			)
		);

		$migrate    = new LLMS_Blocks_Migrate();
		$author     = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$other      = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$types = array(
			'course'          => 'Original instructor content only.',
			'lesson'          => 'Original lesson body.',
			'llms_membership' => 'Original llms_membership body.',
		);

		foreach ( $types as $type => $content ) {
			$post_id = $this->factory->post->create(
				array(
					'post_type'    => $type,
					'post_status'  => 'publish',
					'post_author'  => $author,
					'post_content' => $content,
				)
			);

			$modified_before  = $this->get_post_modified( $post_id );
			$revisions_before = count( wp_get_post_revisions( $post_id ) );

			foreach ( array( 0, $subscriber, $other ) as $user_id ) {
				wp_set_current_user( $user_id );
				$_GET['post'] = $post_id;

				$this->assertFalse( current_user_can( 'edit_post', $post_id ) );
				$this->assertFalse( $this->migrate_and_catch_redirect( $migrate ) );
				$this->assertSame( $content, $this->get_post_content( $post_id ) );
				$this->assertSame( '', get_post_meta( $post_id, '_llms_blocks_migrated', true ) );
			}

			wp_set_current_user( $author );
			$_GET['post'] = $post_id;

			$this->assertTrue( current_user_can( 'edit_post', $post_id ) );
			$this->assertTrue( $this->migrate_and_catch_redirect( $migrate ) );

			$updated = $this->get_post_content( $post_id );
			$this->assertStringStartsWith( $content, $updated );
			$this->assertStringContainsString( '<!-- wp:llms/', $updated );
			$this->assertSame( $updated, get_post( $post_id )->post_content );
			$this->assertSame( 'yes', get_post_meta( $post_id, '_llms_blocks_migrated', true ) );
			$this->assertSame( $modified_before, $this->get_post_modified( $post_id ) );
			$this->assertCount( $revisions_before, wp_get_post_revisions( $post_id ) );
		}

		$untouched = $this->factory->post->create(
			array(
				'post_type'    => 'course',
				'post_status'  => 'publish',
				'post_author'  => $author,
				'post_content' => 'Leave this alone.',
			)
		);
		$pagenow      = 'index.php';
		$_GET['post'] = $untouched;
		wp_set_current_user( $author );

		$this->assertFalse( $this->migrate_and_catch_redirect( $migrate ) );
		$this->assertSame( 'Leave this alone.', $this->get_post_content( $untouched ) );
		$this->assertSame( '', get_post_meta( $untouched, '_llms_blocks_migrated', true ) );

		unset( $_GET['post'] );
		$pagenow = $previous_pagenow;
	}

	/**
	 * Run migrate_post() and report whether it tried to redirect.
	 *
	 * The success path calls exit() after wp_safe_redirect(). The redirect filter
	 * throws before that exit so the process stays alive.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Blocks_Migrate $migrate Migrator.
	 * @return bool
	 */
	private function migrate_and_catch_redirect( $migrate ) {

		$callback = function () {
			throw new LLMS_Unit_Test_Exception_Exit( 'redirect' );
		};

		add_filter( 'wp_redirect', $callback, 0 );

		$redirected = false;
		try {
			$migrate->migrate_post();
		} catch ( LLMS_Unit_Test_Exception_Exit $exception ) {
			$redirected = ( 'redirect' === $exception->getMessage() );
		}

		remove_filter( 'wp_redirect', $callback, 0 );

		return $redirected;
	}

}
