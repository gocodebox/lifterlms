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

}
