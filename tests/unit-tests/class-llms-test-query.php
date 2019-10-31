<?php
/**
 * Test LLMS_Query class
 *
 * @package LifterLMS/Tests
 *
 * @group query
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Query extends LLMS_Unit_Test_Case {

	/**
	 * Retrieve a WP_Query with the given arguments
	 *
	 * @since [version]
	 *
	 * @param array $args
	 * @param bool $is_main
	 * @return [type]
	 */
	private function get_query( $args = array(), $is_main = true ) {

		global $wp_query;
		$wp_query = new WP_Query();

		if ( $is_main ) {
			global $wp_the_query;
			$wp_the_query = $wp_query;
		}

		$wp_query->query( $args );
		return $wp_query;

	}

	private function assertQueryHasProductVisibilityModifications( $query, $is_search = false ) {

		$this->assertTrue( 0 !== count( $query->tax_query->queries ) );

		$terms = wp_list_pluck(
			get_terms(
				array(
					'taxonomy'   => 'llms_product_visibility',
					'hide_empty' => false,
				)
			),
			'term_taxonomy_id',
			'name'
		);

		$not_in = $is_search ? array( $terms['hidden'], $terms['catalog'] ) : array( $terms['hidden'], $terms['search'] );

		$item = array_pop( $query->tax_query->queries );
		$this->assertEquals( 'llms_product_visibility', $item['taxonomy'] );
		$this->assertEquals( 'term_taxonomy_id', $item['field'] );
		$this->assertEquals( 'NOT IN', $item['operator'] );
		$this->assertEquals( $not_in, $item['terms'] );

	}

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * No modifications made to the query if it's no the main query.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_pre_get_posts_not_main_query() {

		$args = array( 'post_type' => 'course' );

		$query = $this->get_query( $args, false );
		$this->assertEquals( $args, $query->query );
		$this->assertEquals( array(), $query->tax_query->queries );

	}

	/**
	 * Excludes hidden and non-searchable courses/memberships during searches.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_pre_get_posts_hide_products_during_search() {

		$this->go_to( add_query_arg( 's', 'mock', get_bloginfo( 'url' ) ) );

		global $wp_query;
		$this->assertQueryHasProductVisibilityModifications( $wp_query, true );

	}

	public function test_pre_get_posts_tax_archives() {

		// $llms_query = new LLMS_Query();

		$taxonomies = array(
			'course_cat',
			'course_tag',
			'course_difficulty',
			'course_track',
			'membership_tag',
			'membership_cat',
		);
		foreach ( $taxonomies as $tax ) {

			$term = wp_create_term( sprintf( 'mock-%s-term', $tax ), $tax );

			$args = array(
				$tax => $term['term_id']
			);

			$query = $this->get_query( $args );
			var_dump( is_tax( $tax ) );
			$this->assertQueryHasProductVisibilityModifications( $query );

		}

	}

}
