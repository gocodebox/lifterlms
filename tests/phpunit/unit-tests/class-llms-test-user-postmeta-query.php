<?php
/**
 * Tests for LLMS_Query_User_Postmeta found_results / count_only behavior.
 *
 * @package LifterLMS/Tests
 *
 * @group query
 * @group dbquery
 *
 * @since 10.0.0
 */
class LLMS_Test_User_Postmeta_Query extends LLMS_UnitTestCase {

	/**
	 * Teardown.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_user_postmeta" );
	}

	/**
	 * Insert mock user postmeta rows.
	 *
	 * @since 10.0.0
	 *
	 * @param int $count   Number of rows.
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function insert_rows( $count, $user_id, $post_id ) {

		global $wpdb;

		for ( $i = 0; $i < $count; $i++ ) {
			$wpdb->insert(
				"{$wpdb->prefix}lifterlms_user_postmeta",
				array(
					'user_id'      => $user_id,
					'post_id'      => $post_id,
					'meta_key'     => '_status',
					'meta_value'   => 'enrolled',
					'updated_date' => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%s', '%s' )
			);
		}
	}

	/**
	 * Test found_results and max_pages with pagination.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_found_results_with_pagination() {

		$uid = $this->factory->user->create();
		$pid = $this->factory->post->create();
		$this->insert_rows( 9, $uid, $pid );

		$query = new LLMS_Query_User_Postmeta(
			array(
				'user_id'  => $uid,
				'post_id'  => $pid,
				'per_page' => 4,
			)
		);

		$this->assertSame( 9, $query->get_found_results() );
		$this->assertSame( 3, $query->get_max_pages() );
		$this->assertSame( 4, $query->get_number_results() );
	}

	/**
	 * Test no_found_rows skips counting.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_no_found_rows_skips_count() {

		$uid = $this->factory->user->create();
		$pid = $this->factory->post->create();
		$this->insert_rows( 3, $uid, $pid );

		$query = new LLMS_Query_User_Postmeta(
			array(
				'user_id'       => $uid,
				'post_id'       => $pid,
				'no_found_rows' => true,
			)
		);

		$this->assertTrue( $query->has_results() );
		$this->assertSame( 0, $query->get_found_results() );
		$this->assertSame( 0, $query->get_max_pages() );
	}

	/**
	 * Test count_only returns accurate count.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_count_only() {

		$uid = $this->factory->user->create();
		$pid = $this->factory->post->create();
		$this->insert_rows( 6, $uid, $pid );

		$query = new LLMS_Query_User_Postmeta(
			array(
				'user_id'    => $uid,
				'post_id'    => $pid,
				'count_only' => true,
			)
		);

		$this->assertSame( 6, $query->get_count_only_result() );
	}
}
