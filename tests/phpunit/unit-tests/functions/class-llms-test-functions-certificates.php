<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests
 *
 * @group functions
 * @group functions_certificates
 * @group certificates
 *
 * @since 6.0.0
 */
class LLMS_Test_Functions_Certificates extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_certificate().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate() {

		$post = $this->factory->post->create();

		// Invalid post type.
		$this->assertFalse( llms_get_certificate( $post ) );

		// Non-existent post.
		$this->assertFalse( llms_get_certificate( $post + 1 ) );

		// Template post without preview flag.
		$template_post = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		$this->assertFalse( llms_get_certificate( $template_post ) );

		// Template post with preview flag.
		$preview = llms_get_certificate( $template_post, true );
		$this->assertInstanceOf( 'LLMS_User_Certificate', $preview );
		$this->assertEquals( $template_post, $preview->get( 'id' ) );

		// Earned cert.
		$earned_post = $this->factory->post->create( array( 'post_type' => 'llms_my_certificate' ) );
		$earned = llms_get_certificate( $earned_post );
		$this->assertInstanceOf( 'LLMS_User_Certificate', $earned );
		$this->assertEquals( $earned_post, $earned->get( 'id' ) );

		// From global.
		global $post;
		$post = get_post( $earned_post );
		$global = llms_get_certificate();
		$this->assertInstanceOf( 'LLMS_User_Certificate', $global );
		$this->assertEquals( $earned_post, $global->get( 'id' ) );

		$post = null;

	}

	/**
	 * Test llms_get_certificate_content().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_content() {

		// Invalid post.
		$post = $this->factory->post->create();
		$this->assertEquals( '', llms_get_certificate_content( $post ) );

		// Template: merge the stored content.
		$template_id = $this->factory->post->create( array(
			'post_type' => 'llms_certificate',
			'post_content' => 'Cert ID: {certificate_id}',
		) );
		$this->assertEquals( "<p>Cert ID: {$template_id}</p>\n", llms_get_certificate_content( $template_id ) );

		// Earned cert: return the stored content.
		$earned_id = $this->factory->post->create( array(
			'post_type' => 'llms_certificate',
			'post_content' => 'Just some content.',
		) );
		$this->assertEquals( "<p>Just some content.</p>\n", llms_get_certificate_content( $earned_id ) );

	}

	/**
	 * Test llms_get_certificate_content() with reusable blocks and merge codes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_content_reusable_blocks() {

		// Define reusable blocks.
		$reusable_blocks = array(
			// Multiple blocks.
			1 => "<!-- wp:paragraph --><p>reusable block 1 paragraph 1</p><!-- /wp:paragraph -->\n" .
			     "<!-- wp:paragraph --><p>reusable block 1 paragraph 2</p><!-- /wp:paragraph -->",

			// Multiple blocks containing different LifterLMS merge codes.
			2 => "<!-- wp:paragraph --><p>reusable block 2 Certificate ID: {certificate_id}</p><!-- /wp:paragraph -->\n" .
			     "<!-- wp:paragraph --><p>reusable block 2 Sequential ID: {sequential_id}</p><!-- /wp:paragraph -->",

			// One block with two levels of inner blocks.
			3 => "<!-- wp:columns --><div class=\"wp-block-columns\">\n" .
			     "    <!-- wp:column --><div class=\"wp-block-column\">\n" .
			     "        <!-- wp:paragraph --><p>reusable block 3 column 1</p><!-- /wp:paragraph -->\n" .
			     "    <!-- /wp:column --></div>\n" .
			     "    <!-- wp:column --><div class=\"wp-block-column\">\n" .
			     "        <!-- wp:paragraph --><p>reusable block 3 column 2</p><!-- /wp:paragraph -->\n" .
			     "    <!-- /wp:column --></div>\n" .
			     "<!-- /wp:columns -->",

			// One block and multiple references to different reusable blocks.
			4 => "<!-- wp:paragraph --><p>reusable block 4 paragraph 1</p><!-- /wp:paragraph -->\n" .
			     "<!-- wp:block {\"ref\":{REUSABLE_BLOCK_1}} /-->\n" .
			     "<!-- wp:block {\"ref\":{REUSABLE_BLOCK_2}} /-->",

			// One block with two levels of inner blocks that reference different reusable blocks.
			5 => "<!-- wp:columns --><div class=\"wp-block-columns\">\n" .
			     "    <!-- wp:column --><div class=\"wp-block-column\">\n" .
			     "        <!-- wp:block {\"ref\":{REUSABLE_BLOCK_1}} /-->\n" .
			     "    <!-- /wp:column --></div>\n" .
			     "    <!-- wp:column --><div class=\"wp-block-column\">\n" .
			     "        <!-- wp:block {\"ref\":{REUSABLE_BLOCK_4}} /-->\n" .
			     "    <!-- /wp:column --></div>\n" .
			     "<!-- /wp:columns -->",
		);

		$reusable_posts   = array();
		$template_blocks  = array();
		$template_posts   = array();
		$reusable_pattern = '/{REUSABLE_BLOCK_(\d+?)}/';

		foreach ( $reusable_blocks as $key => $reusable_block ) {

			// Replace reusable block merge codes with their reusable post ID.
			while ( preg_match_all( $reusable_pattern, $reusable_block, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$reusable_block = str_replace( $match[0], $reusable_posts[ $match[1] ]->ID, $reusable_block );
				}
			}

			// Create reusable block.
			$reusable_posts[ $key ] = $this->factory->post->create_and_get( array(
				'post_type'    => 'wp_block',
				'post_content' => $reusable_block,
			) );

			// Create template with only the reference to a reusable block.
			$template_blocks[ $key ] = null;
			$template_posts[ $key ]  = $this->factory->post->create_and_get( array(
				'post_type'    => 'llms_certificate',
				'post_content' => "<!-- wp:block {\"ref\":{$reusable_posts[ $key ]->ID}} /-->",
			) );

			// Create template with a paragraph block and a reference to a reusable block.
			$template_blocks[ $key + 100 ] = "<!-- wp:paragraph --><p>template 10$key reusable block $key</p>" .
			                                 "<!-- /wp:paragraph -->";
			$template_posts[ $key + 100 ]  = $this->factory->post->create_and_get( array(
				'post_type'    => 'llms_certificate',
				'post_content' => $template_blocks[ $key + 100 ] .
				                  "<!-- wp:block {\"ref\":{$reusable_posts[ $key ]->ID}} /-->",
			) );
		}

		$reusable_pattern = '/<!-- wp:block {"ref":{REUSABLE_BLOCK_(\d+?)}} \/-->/';

		foreach ( $template_posts as $key => $template_post ) {

			$reusable_key = $key < 100 ? $key : $key - 100;
			$expected     = $template_blocks[ $key ] . $reusable_blocks[ $reusable_key ];

			// Replace reusable block merge codes with their reusable block content.
			while ( preg_match_all( $reusable_pattern, $expected, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$expected = str_replace( $match[0], $reusable_blocks[ $match[1] ], $expected );
				}
			}
			$expected = preg_replace( '/<!--.+?-->/', '', $expected );

			$sequence_id = llms_get_certificate_sequential_id( $template_post->ID, false );
			$sequence_id = str_pad( $sequence_id, 6, '0', STR_PAD_LEFT );
			$expected    = str_replace( '{certificate_id}', $template_post->ID, $expected );
			$expected    = str_replace( '{sequential_id}', $sequence_id, $expected );

			$this->assertEquals(
				$expected,
				llms_get_certificate_content( $template_post->ID ),
				"template #$key, reusable block #$reusable_key"
			);
		}
	}

	/**
	 * Test llms_get_certificate_fonts().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_fonts() {

		$this->assertIsArray( llms_get_certificate_fonts() );

	}

	public function test_llms_get_certificate_image() {}

	/**
	 * Test llms_get_certificate_merge_codes()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_merge_codes() {

		$ret = llms_get_certificate_merge_codes();
		$this->assertIsArray( $ret );
		foreach ( $ret as $code => $desc ) {

			$this->assertEquals( '{', $code[0] );
			$this->assertEquals( '}', $code[strlen( $code ) - 1] );
			$this->assertIsString( $desc );

		}

	}

	/**
	 * Test llms_get_certificate_sequential_id()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_sequential_id() {

		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );

		$id = 1;
		while ( $id <= 100 ) {

			// Just get the next ID, don't increment.
			$this->assertEquals( $id, llms_get_certificate_sequential_id( $template_id, false ) );
			$this->assertEquals( $id, absint( get_post_meta( $template_id, '_llms_sequential_id', true ) ) );

			// Increment the ID.
			$this->assertEquals( $id, llms_get_certificate_sequential_id( $template_id, true ) );
			$this->assertEquals( $id + 1, absint( get_post_meta( $template_id, '_llms_sequential_id', true ) ) );

			$id++;

		}

		// Big numbers.
		$id = 923409;
		update_post_meta( $template_id, '_llms_sequential_id', $id );
		while ( $id <= 923512 ) {

			// Just get the next ID, don't increment.
			$this->assertEquals( $id, llms_get_certificate_sequential_id( $template_id, false ) );
			$this->assertEquals( $id, absint( get_post_meta( $template_id, '_llms_sequential_id', true ) ) );

			// Increment the ID.
			$this->assertEquals( $id, llms_get_certificate_sequential_id( $template_id, true ) );
			$this->assertEquals( $id + 1, absint( get_post_meta( $template_id, '_llms_sequential_id', true ) ) );

			$id++;

		}

	}

	/**
	 * Test llms_get_certificate_title().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_title() {

		// Invalid post type.
		$post = $this->factory->post->create();
		$this->assertEquals( '', llms_get_certificate_title( $post ) );

		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate', 'post_title' => 'Not Returned' ) );
		update_post_meta( $template_id, '_llms_certificate_title', 'Cert Title!' );

		$this->assertEquals( 'Cert Title!', llms_get_certificate_title( $template_id ) );

		$earned_id = $this->factory->post->create( array( 'post_type' => 'llms_my_certificate', 'post_title' => 'A Title' ) );
		$this->assertEquals( 'A Title', llms_get_certificate_title( $earned_id ) );

	}

	/**
	 * Test llms_is_block_editor_supported_for_certificates()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_is_block_editor_supported_for_certificates() {

		global $wp_version;
		$orig = $wp_version;

		$tests = array(
			// Unsupported versions.
			array( '5.5.0', false ),
			array( '5.6.0', false ),
			array( '5.6.1', false ),
			array( '5.7', false ),
			array( '5.7.0', false ),
			array( '5.7.5', false ),
			// Supported versions.
			array( '5.8', true ),
			array( '5.8.0', true ),
			array( '5.8.1', true ),
			array( '5.8.3', true ),
			array( '5.9', true ),
			array( '5.9-alpha', true ),
			array( '5.9-RC1', true ),
			// Future versions?
			array( '6.0', true ),
			array( '6.0.1', true ),
		);

		foreach ( $tests as $test ) {
			list( $wp_version, $expect ) = $test;
			$this->assertEquals( $expect, llms_is_block_editor_supported_for_certificates(), $wp_version );

			// Test "-src" version.
			$wp_version .= '-src';
			$this->assertEquals( $expect, llms_is_block_editor_supported_for_certificates(), $wp_version);
		}

		$wp_version = $orig;

	}

}
