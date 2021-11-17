<?php
/**
 * Test LLMS_Awards_Query
 *
 * @package LifterLMS/Tests
 *
 * @group awards_query
 *
 * @since [version]
 */
class LLMS_Test_Awards_Query extends LLMS_UnitTestCase {

	/**
	 * Test get_number_results(), get_found_results(), get_max_pages(), has_results(), and get_results().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query() {

		$tests = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);

		$expected_num_results = array(
			1 => 10,
			2 => 10,
			3 => 5,
			4 => 0,
		);

		foreach ( $tests as $post_type ) {

			$awards = $this->factory->post->create_many( 25, compact( 'post_type' ) );

			$page = 1;
			while ( $page <= 4 ) {

				$query = new LLMS_Awards_Query( array( 'page' => $page, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );

				$this->assertEquals( $expected_num_results[ $page ], $query->get_number_results() );
				$this->assertEquals( 4 !== $page ? 25 : 0, $query->get_found_results() );
				$this->assertEquals( 4 !== $page ? 3 : 0, $query->get_max_pages() );
				$this->assertEquals( 4 !== $page, $query->has_results() );

				foreach ( $query->get_awards() as $result ) {
					$this->assertTrue( in_array( $result->get( 'id' ), $awards, true ) );
					$this->assertInstanceOf( strtoupper( str_replace( '_my_', '_user_', $post_type ) ), $result );
				}

				foreach ( $query->get_results() as $result ) {
					$this->assertTrue( in_array( $result->ID, $awards, true ) );
					$this->assertInstanceOf( 'WP_Post', $result );

					$index = array_search( $result->ID, $awards, true );
					unset( $awards[ $index ] );

				}

				$page++;
			}


		}

	}

	/**
	 * Run a query for awards earned or not earned by a specific user
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_by_user() {

		$tests = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);

		$post_author = $this->factory->user->create();
		foreach ( $tests as $post_type ) {

			$included = $this->factory->post->create_many( 3, compact( 'post_type', 'post_author' ) );
			$excluded = $this->factory->post->create_many( 2, compact( 'post_type' ) );

			// Included for a specified user.
			$users_query = new LLMS_Awards_Query( array( 'users' => $post_author, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
			$this->assertEquals( 3, $users_query->get_number_results() );
			foreach ( $users_query->get_results() as $post ) {
				$this->assertEquals( $post_author, $post->post_author );
				$this->assertTrue( in_array( $post->ID, $included, true ) );
			}

			// Excluded for a specified user.
			$users_exclude_query = new LLMS_Awards_Query( array( 'users__exclude' => $post_author, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
			$this->assertEquals( 2, $users_exclude_query->get_number_results() );
			foreach ( $users_exclude_query->get_results() as $post ) {
				$this->assertNotEquals( $post_author, $post->post_author );
				$this->assertTrue( in_array( $post->ID, $excluded, true ) );
			}

		}

	}

	/**
	 * Test query by related_posts or engagements
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_by_relationships() {

		$tests = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);

		foreach ( $tests as $post_type ) {

			$metas = array(
				'related_posts' => '_llms_related',
				'engagements'   => '_llms_engagement',
			);

			foreach ( $metas as $arg => $meta_key ) {

				$meta_input = array(
					$meta_key => 123,
				);

				$included = $this->factory->post->create_many( 3, compact( 'post_type', 'meta_input' ) );
				$excluded = $this->factory->post->create_many( 2, compact( 'post_type' ) );
				$excluded[] = $this->factory->post->create( array(
					'post_type' => $post_type,
					'meta_input' => array( $meta_key => 456 ),
				) );

				// Included.
				$include_query = new LLMS_Awards_Query( array( $arg => 123, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
				$this->assertEquals( 3, $include_query->get_number_results() );
				foreach ( $include_query->get_results() as $post ) {
					$this->assertEquals( 123, get_post_meta( $post->ID, $meta_key, true ) );
					$this->assertTrue( in_array( $post->ID, $included, true ) );
				}

				// Excluded.
				$exclude_query = new LLMS_Awards_Query( array( "{$arg}__exclude" => 123, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
				foreach ( $exclude_query->get_results() as $post ) {
					$this->assertNotEquals( 123, get_post_meta( $post->ID, $meta_key, true ) );
					$this->assertTrue( ! in_array( $post->ID, $included, true ) );
				}

			}

		}

	}

	/**
	 * Test query() for parent template relationships
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_template() {

		$tests = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);

		foreach ( $tests as $post_type ) {

			$post_parent = $this->factory->post->create();

			$included = $this->factory->post->create_many( 3, compact( 'post_type', 'post_parent' ) );
			$excluded = $this->factory->post->create_many( 2, compact( 'post_type' ) );
			$excluded[] = $this->factory->post->create( array(
				'post_type' => $post_type,
				'post_parent' => $this->factory->post->create(),
			) );

			// Included.
			$include_query = new LLMS_Awards_Query( array( 'templates' => $post_parent, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
			$this->assertEquals( 3, $include_query->get_number_results() );
			foreach ( $include_query->get_results() as $post ) {
				$this->assertEquals( $post_parent, $post->post_parent );
				$this->assertTrue( in_array( $post->ID, $included, true ) );
			}

			// Excluded.
			$exclude_query = new LLMS_Awards_Query( array( "templates__exclude" => $post_parent, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
			foreach ( $exclude_query->get_results() as $post ) {
				$this->assertNotEquals( $post_parent, $post->post_parent );
				$this->assertTrue( ! in_array( $post->ID, $included, true ) );
			}


		}

	}

	/**
	 * Run a query for manual awards only
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query_manual() {

		$tests = array(
			'llms_my_achievement',
			'llms_my_certificate',
		);
		foreach ( $tests as $post_type ) {

			$excluded = $this->factory->post->create_many( 1, array(
				'post_type' => $post_type,
				'meta_input' => array( '_llms_engagement' => 456 ),
			) );

			$included = $this->factory->post->create_many( 1, array(
				'post_type' => $post_type,
			) );
			$included[] = $this->factory->post->create( array(
				'post_type' => $post_type,
				'meta_input' => array( '_llms_engagement' => 0 ),
			) );
			$included[] = $this->factory->post->create( array(
				'post_type' => $post_type,
				'meta_input' => array( '_llms_engagement' => '' ),
			) );

			$include_query = new LLMS_Awards_Query( array( 'manual_only' => true, 'type' => str_replace( 'llms_my_', '', $post_type ) ) );
			$this->assertEquals( 3, $include_query->get_number_results() );
			foreach ( $include_query->get_results() as $post ) {
				$this->assertEmpty( get_post_meta( $post->ID, '_llms_engagement', true ) );
				$this->assertTrue( in_array( $post->ID, $included, true ) );
			}

		}


	}

}
