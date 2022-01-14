<?php
/**
 * Tests for {@see LLMS_Trait_Student_Awards}.
 *
 * @group traits
 * @group student
 * @group student_awards
 *
 * @since [version]
 */
class LLMS_Test_Trait_Student_awards extends LLMS_UnitTestCase {

	/**
	 * Test all trait methods.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_all_the_methods() {

		$tests = array(
			'achievement',
			'certificate',
		);

		$student    = $this->factory->student->create_and_get();
		$all_awards = array();

		foreach ( $tests as $type ) {

			$get    = "get_{$type}s";
			$earn   = "earn_{$type}";
			$create = "create_{$type}_template";
			$obj    = sprintf( 'LLMS_User_%s', ucwords( $type ) );

			$earned = $this->$earn( $student->get( 'id' ), $this->$create(), $this->factory->post->create() );

			$awards = $student->$get( array() );
			$this->assertInstanceOf( 'LLMS_Awards_Query', $awards );

			$post = $awards->get_results()[0];
			$this->assertEquals( $earned[1], $post->ID );
			$this->assertEquals( $student->get( 'id' ), $post->post_author );

			$award = $awards->get_awards()[0];
			$this->assertInstanceOf( $obj, $award );
			$this->assertEquals( $earned[1], $award->get( 'id' ) );
			$this->assertEquals( $student->get( 'id' ), $award->get_user_id() );

			$this->assertEquals( 1, $student->get_awards_count( $type ) );

			$all_awards[] = $award;

		}

		// Test mixed awards return.
		$awards_query = $student->get_awards();
		$this->assertEquals( array_reverse( $all_awards ), $awards_query->get_awards() ); // Sorted in rev. chron by default.
		$this->assertEquals( 2, $student->get_awards_count() );

	}

	/**
	 * Test get_achievements & get_certificates() deprecated method signature.
	 *
	 * @since [version]
	 *
	 * @expectedDeprecated LLMS_Student::get_achievements()
	 * @expectedDeprecated LLMS_Student::get_certificates()
	 *
	 * @return void
	 */
	public function test_get_methods_deprecated() {

		$tests = array(
			'achievement',
			'certificate',
		);

		foreach ( $tests as $type ) {

			$get    = "get_{$type}s";
			$earn   = "earn_{$type}";
			$create = "create_{$type}_template";
			$obj    = sprintf( 'LLMS_User_%s', ucwords( $type ) );
			$id     = "{$type}_id";
			$load   = function( $item ) use ( $obj ) {
				return new $obj( $item );
			};

			$student = $this->factory->student->create_and_get();

			$this->assertEquals( array(), $student->$get() );

			$expect = array();
			$i = 0;
			while ( $i < 5 ) {

				$ts   = time() - WEEK_IN_SECONDS * rand( 5, 15 );
				$date = date( 'Y-m-d H:i:s', $ts );

				$related = $this->factory->post->create();

				llms_tests_mock_current_time( $ts );
				$earned = $this->$earn( $student->get( 'id' ), $this->$create(), $related );

				$obj              = new stdClass();
				$obj->post_id     = $related;
				$obj->$id         = $earned[1];
				$obj->earned_date = $date;

				$expect[] = $obj;
				$i++;
			}

			$sort_opts = array(
				'earned_date'  => 'earned_date',    // Sort by the AS value.
				'updated_date' => 'earned_date',    // Sort by the actual meta key name.
				'post_id'      => 'post_id',        // Related post id.
				$id            => $id, // Earned ID.
				'meta_value'   => $id, // actual meta value name.
			);

			foreach ( $sort_opts as $input_sort => $expect_sort ) {

				// Descending (default).
				$expected = wp_list_sort( $expect, $expect_sort, 'DESC' );
				$this->assertEquals(
					$expected,
					$student->$get( $input_sort, 'DESC', 'obj' )
				);
				$this->assertEquals(
					array_map( $load, wp_list_pluck( $expected, $id ) ),
					$student->$get( $input_sort, 'DESC', "{$type}s" )
				);

				// Ascending.
				$expected = wp_list_sort( $expect, $expect_sort, 'ASC' );
				$this->assertEquals(
					$expected,
					$student->$get( $input_sort, 'ASC', 'obj' )
				);
				$this->assertEquals(
					array_map( $load, wp_list_pluck( $expected, $id ) ),
					$student->$get( $input_sort, 'ASC', "{$type}s" )
				);

			}

		}

	}

}
