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

	private function generate_certs( $count = 5 ) {

		$generated = array();
		$i = 0;
		while ( $i < $count ) {

			$ts   = time() - WEEK_IN_SECONDS * rand( 5, 15 );
			$date = date( 'Y-m-d H:i:s', $ts );

			$related = $this->factory->post->create();

			llms_mock_current_time( $ts );
			$earned = $this->earn_certificate( $this->factory->user->create(), $this->create_certificate_template(), $related );

			$generated[] = llms_get_certificate( $earned[1] );

			$i++;
		}

		return $generated;

	}

	/**
	 * Test clean_args() and clean_sort().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_clean_args() {

		$dirty = array(
			'sort'                   => array(
				'date' => 'asc',
				'user' => 'fake',
				'ID'   => 'DESC',
				'fake' => 'DESC',
			),
			'users'                  => 1,
			'users__exclude'         => "2",
			'related_posts'          => array( "3", 4 ),
			'related_posts__exclude' => "fake",
			'engagements'            => array( 5, "nope", 6 ),
			'engagements__exclude'   => "1,2,3",
			'templates'              => 9999,
			'templates__exclude'     => -1,
			'manual'                 => 'fake',
			'page'                   => "200",
			'per_page'               => -1,
			'no_found_rows'          => true,
		);

		$clean = array(
			'sort'                   => array(
				'date' => 'ASC',
				'user' => 'DESC',
				'ID'   => 'DESC',
			),
			'users'                  => array( 1 ),
			'users__exclude'         => array( 2 ),
			'related_posts'          => array( 3, 4 ),
			'related_posts__exclude' => array(),
			'engagements'            => array( 5, 6 ),
			'engagements__exclude'   => array( 1 ),
			'templates'              => array( 9999 ),
			'templates__exclude'     => array( 1 ),
			'manual'                 => false,
			'page'                   => 200,
			'per_page'               => -1,
			'no_found_rows'          => true,
		);

		$obj = new LLMS_Awards_Query( 'certificates' );
		$this->assertEquals( $clean, LLMS_Unit_Test_Util::call_method( $obj, 'clean_args', array( $dirty ) ) );

	}

	/**
	 * Test get_number_results(), get_found_results(), get_total_pages(), has_results(), and get_results().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_query() {

		$tests = array(
			'llms_my_achievement' => 'achievements',
			'llms_my_certificate' => 'certificates',
		);

		$expected_num_results = array(
			1 => 10,
			2 => 10,
			3 => 5,
			4 => 0,
		);

		foreach ( $tests as $post_type => $query_type ) {

			$awards = $this->factory->post->create_many( 25, compact( 'post_type' ) );

			$page = 1;
			while ( $page <= 4 ) {

				$query = new LLMS_Awards_Query( $query_type, compact( 'page' ) );

				$this->assertEquals( $expected_num_results[ $page ], $query->get_number_results() );
				$this->assertEquals( 4 !== $page ? 25 : 0, $query->get_found_results() );
				$this->assertEquals( 4 !== $page ? 3 : 0, $query->get_total_pages() );
				$this->assertEquals( 4 !== $page, $query->has_results() );

				foreach ( $query->get_results() as $result ) {
					$this->assertTrue( in_array( $result->get( 'id' ), $awards, true ) );
					$this->assertInstanceOf( strtoupper( str_replace( '_my_', '_user_', $post_type ) ), $result );
				}

				foreach ( $query->get_results( 'POSTS' ) as $result ) {
					$this->assertTrue( in_array( $result->ID, $awards, true ) );
					$this->assertInstanceOf( 'WP_Post', $result );

					$index = array_search( $result->ID, $awards, true );
					unset( $awards[ $index ] );

				}

				$page++;
			}


		}

	}

	public function test_query_by_user() {

		$tests = array(
			'llms_my_achievement' => 'achievements',
			'llms_my_certificate' => 'certificates',
		);

		$post_author = $this->factory->user->create();
		foreach ( $tests as $post_type => $query_type ) {

			$included = $this->factory->post->create_many( 3, compact( 'post_type', 'post_author' ) );
			$excluded = $this->factory->post->create_many( 2, compact( 'post_type' ) );

			// Included for a specified user.
			$users_query = new LLMS_Awards_Query( $query_type, array( 'users' => $post_author ) );
			$this->assertEquals( 3, $users_query->get_number_results() );
			foreach ( $users_query->get_results( 'POSTS' ) as $post ) {
				$this->assertEquals( $post_author, $post->post_author );
				$this->assertTrue( in_array( $post->ID, $included, true ) );
			}

			// Excluded for a specified user.
			$users_exclude_query = new LLMS_Awards_Query( $query_type, array( 'users__exclude' => $post_author ) );
			$this->assertEquals( 2, $users_exclude_query->get_number_results() );
			foreach ( $users_exclude_query->get_results( 'POSTS' ) as $post ) {
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
			'llms_my_achievement' => 'achievements',
			'llms_my_certificate' => 'certificates',
		);

		foreach ( $tests as $post_type => $query_type ) {

			$metas = array(
				'related_posts' => '_llms_related',
				'engagements'   => '_llms_engagement',
				'templates'     => LLMS_Unit_Test_Util::call_method( new LLMS_Awards_Query( $query_type ), 'get_template_meta_key' )
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
				$include_query = new LLMS_Awards_Query( $query_type, array( $arg => 123 ) );
				$this->assertEquals( 3, $include_query->get_number_results() );
				foreach ( $include_query->get_results( 'POSTS' ) as $post ) {
					$this->assertEquals( 123, get_post_meta( $post->ID, $meta_key, true ) );
					$this->assertTrue( in_array( $post->ID, $included, true ) );
				}

				// Excluded.
				$exclude_query = new LLMS_Awards_Query( $query_type, array( "{$arg}__exclude" => 123 ) );
				foreach ( $exclude_query->get_results( 'POSTS' ) as $post ) {
					$this->assertNotEquals( 123, get_post_meta( $post->ID, $meta_key, true ) );
					$this->assertTrue( ! in_array( $post->ID, $included, true ) );
				}

			}

		}

	}

	public function test_query_manual() {

		$tests = array(
			'llms_my_achievement' => 'achievements',
			'llms_my_certificate' => 'certificates',
		);
		foreach ( $tests as $post_type => $query_type ) {

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

			$include_query = new LLMS_Awards_Query( $query_type, array( 'manual' => true ) );
			$this->assertEquals( 3, $include_query->get_number_results() );
			foreach ( $include_query->get_results( 'POSTS' ) as $post ) {
				$this->assertEmpty( get_post_meta( $post->ID, '_llms_engagement', true ) );
				$this->assertTrue( in_array( $post->ID, $included, true ) );
			}

		}


	}

}
