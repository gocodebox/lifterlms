<?php
/**
 * Test LLMS_Blocks_Visibility class & methods.
 *
 * @package LifterLMS_Blocks/Tests
 *
 * @since 1.0.0
 * @since 1.6.0 Add tests for `logged_out` and `logged_in` visiblity settings.
 */
class LLMS_Blocks_Test_Visibility extends LLMS_Blocks_Unit_Test_Case {

	/**
	 * Custom assertion for this test case
	 *
	 * @since 1.0.0
	 *
	 * @param string $expected    Expected content.
	 * @param string $raw_content Raw content to check against, will be run through `the_content` filter.
	 * @return void
	 */
	private function assertPostContentEquals( $expected, $raw_content ) {

		$this->assertEquals( $this->clean_content( $expected ), $this->clean_content( apply_filters( 'the_content', $raw_content ) ) );

	}

	/**
	 * Normalize content, used by assertPostContentEquals to strip spaces and newlines resulting from hidden content gaps.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Text/html content.
	 * @return string
	 */
	private function clean_content( $content ) {
		$content = preg_replace( '/<!--(.|\s)*?-->/', '', $content );
		// WP 6.9+ adds a `wp-block-paragraph` class when rendering core paragraph blocks; normalize it away so stored vs. rendered markup compare equal.
		$content = preg_replace( '/\sclass="wp-block-paragraph"/', '', $content );
		return trim( $content );
	}

	/**
	 * Creates a new post with a paragaph block configured with the desired visibility settings.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Unknown.
	 *
	 * @param array $block_settings Desired block settings attributes.
	 * @param string $post_type     Post type of the created post.
	 * @return WP_Post
	 */
	private function create_post( $block_settings = array(), $post_type = 'post' ) {

		$block_settings = json_encode( $block_settings );
		ob_start();
		?>
		<!-- wp:paragraph <?php echo $block_settings; ?> -->
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
		<!-- /wp:paragraph -->
		<?php
		$content = ob_get_clean();

		global $post;
		$post = $this->factory->post->create_and_get( array(
			'post_content' => $content,
			'post_type' => $post_type,
		) );

		update_post_meta( $post->ID, '_llms_blocks_migrated', 'yes' );

		return $post;

	}

	/**
	 * Encode and stringify posts array used in block settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $array Array of post data as returned by `get_posts_array()`.
	 * @return string
	 */
	private function encode_posts_array( $array ) {
		return str_replace( '"', '\u0022', json_encode( $array ) );
	}

	/**
	 * Create a number of posts and return an array of data to be used in block settings.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $count Number of posts to create.
	 * @param string $type  Post type.
	 * @return array
	 */
	private function get_posts_array( $count = 1, $type = 'course' ) {

		$posts = array();
		$i = 1;
		while ( $i <= $count ) {

			$post = $this->factory->post->create_and_get( array(
				'post_type' => $type,
			) );

			$posts[] = array(
				'id' => $post->ID,
				'type' => $post->post_type,
				'value' => $post->ID,
				'label' => sprintf( '%1$s (ID# %2$d)', $post->post_title, $post->ID ),
			);

			$i++;

		}

		return $posts;

	}

	/**
	 * Test that blocks which have been run through `the_content` filter are displayed/hidden based on block visibility settings.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Unknown.
	 *
	 * @return void
	 */
	public function test_visibility() {

		// Make no template hooks are attached.
		remove_all_actions( 'lifterlms_single_course_before_summary' );
		remove_all_actions( 'lifterlms_single_course_after_summary' );

		global $current_user;

		// Get a student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Default Settings: show to everyone.
		 */
		$post = $this->create_post();

		// Logged out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );

		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {

			/**
			 * Enrollment in current course/membership.
			 */
			$post = $this->create_post( array(
				'llms_visibility' => 'enrolled',
				'llms_visibility_in' => 'this'
			), $post_type );

			// Logged out.
			$this->assertPostContentEquals( '', $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( '', $post->post_content );
			// Enrolled.
			llms_enroll_student( $student_id, $post->ID );
			$this->assertPostContentEquals( $post->post_content, $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );


			/**
			 * Enrollment in multiple courses/memberships (all).
			 */
			$list = $this->get_posts_array( 2, $post_type );
			$post = $this->create_post( array(
				'llms_visibility' => 'enrolled',
				'llms_visibility_in' => 'list_all',
				'llms_visibility_posts' => $this->encode_posts_array( $list ),
			), $post_type );

			// Logged Out.
			$this->assertPostContentEquals( '', $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( '', $post->post_content );
			// Enrolled in 1 of 2.
			llms_enroll_student( $student_id, $list[0]['id'] );
			$this->assertPostContentEquals( '', $post->post_content );
			// Enrolled in both.
			llms_enroll_student( $student_id, $list[1]['id'] );
			$this->assertPostContentEquals( $post->post_content, $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );


			/**
			 * Enrollment in multiple courses/memberships (any).
			 */
			$list = $this->get_posts_array( 2, $post_type );
			$post = $this->create_post( array(
				'llms_visibility' => 'enrolled',
				'llms_visibility_in' => 'list_any',
				'llms_visibility_posts' => $this->encode_posts_array( $list ),
			), $post_type );

			// Logged Out.
			$this->assertPostContentEquals( '', $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( '', $post->post_content );
			// Enrolled in 1 of 2.
			llms_enroll_student( $student_id, $list[1]['id'] );
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Enrolled in both.
			llms_enroll_student( $student_id, $list[0]['id'] );
			$this->assertPostContentEquals( $post->post_content, $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );


			/**
			 * Not enrolled in current course/membership.
			 */
			$post = $this->create_post( array(
				'llms_visibility' => 'not_enrolled',
				'llms_visibility_in' => 'this'
			), $post_type );

			// Logged out.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Enrolled.
			llms_enroll_student( $student_id, $post->ID );
			$this->assertPostContentEquals( '', $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );


			/**
			 * Not enrolled in multiple courses/memberships (all).
			 */
			$list = $this->get_posts_array( 2, $post_type );
			$post = $this->create_post( array(
				'llms_visibility' => 'not_enrolled',
				'llms_visibility_in' => 'list_all',
				'llms_visibility_posts' => $this->encode_posts_array( $list ),
			), $post_type );

			// Logged Out.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Enrolled in 1 of 2.
			llms_enroll_student( $student_id, $list[0]['id'] );
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Enrolled in both.
			llms_enroll_student( $student_id, $list[1]['id'] );
			$this->assertPostContentEquals( '', $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );


			/**
			 * Not enrolled in multiple courses/memberships (any).
			 */
			$list = $this->get_posts_array( 2, $post_type );
			$post = $this->create_post( array(
				'llms_visibility' => 'not_enrolled',
				'llms_visibility_in' => 'list_any',
				'llms_visibility_posts' => $this->encode_posts_array( $list ),
			), $post_type );

			// Logged Out.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Logged in.
			wp_set_current_user( $student_id );
			// Not enrolled.
			$this->assertPostContentEquals( $post->post_content, $post->post_content );
			// Enrolled in 1 of 2.
			llms_enroll_student( $student_id, $list[1]['id'] );
			$this->assertPostContentEquals( '', $post->post_content );
			// Enrolled in both.
			llms_enroll_student( $student_id, $list[0]['id'] );
			$this->assertPostContentEquals( '', $post->post_content );

			// Log out & start over.
			wp_set_current_user( null );

		}


		/**
		 * Enrollment in multiple courses & memberships, mixed (all).
		 */
		$list = array_merge( $this->get_posts_array( 1, 'course' ), $this->get_posts_array( 1, 'llms_membership' ) );
		$post = $this->create_post( array(
			'llms_visibility' => 'enrolled',
			'llms_visibility_in' => 'list_all',
			'llms_visibility_posts' => $this->encode_posts_array( $list ),
		) );

		// Logged Out.
		$this->assertPostContentEquals( '', $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in 1 of 2.
		llms_enroll_student( $student_id, $list[0]['id'] );
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in both.
		llms_enroll_student( $student_id, $list[1]['id'] );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		/**
		 * Enrollment in multiple courses & memberships, mixed (any).
		 */
		$list = array_merge( $this->get_posts_array( 1, 'course' ), $this->get_posts_array( 1, 'llms_membership' ) );
		$post = $this->create_post( array(
			'llms_visibility' => 'enrolled',
			'llms_visibility_in' => 'list_any',
			'llms_visibility_posts' => $this->encode_posts_array( $list ),
		) );

		// Logged Out.
		$this->assertPostContentEquals( '', $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in 1 of 2.
		llms_enroll_student( $student_id, $list[1]['id'] );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in both.
		llms_enroll_student( $student_id, $list[0]['id'] );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Enrollment in anything.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'enrolled',
			'llms_visibility_in' => 'any',
		) );
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Logged Out.
		$this->assertPostContentEquals( '', $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in a membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		llms_unenroll_student( $student_id, $membership_id );
		// Enrolled in a course.
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in course & membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Enrollment in any course.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'enrolled',
			'llms_visibility_in' => 'any_course',
		) );

		// Logged Out.
		$this->assertPostContentEquals( '', $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in a membership (doesn't grant access).
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( '', $post->post_content );
		llms_unenroll_student( $student_id, $membership_id );
		// Enrolled in a course.
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in course & membership.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Enrollment in any membership.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'enrolled',
			'llms_visibility_in' => 'any_membership',
		) );

		// Logged Out.
		$this->assertPostContentEquals( '', $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in a course (doesn't grant access).
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( '', $post->post_content );
		llms_unenroll_student( $student_id, $course_id );
		// Enrolled in a membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		llms_enroll_student( $student_id, $course_id );
		// Enrolled in course & membership.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		/**
		 * Not enrolled in multiple courses & memberships, mixed (all).
		 */
		$list = array_merge( $this->get_posts_array( 1, 'course' ), $this->get_posts_array( 1, 'llms_membership' ) );
		$post = $this->create_post( array(
			'llms_visibility' => 'not_enrolled',
			'llms_visibility_in' => 'list_all',
			'llms_visibility_posts' => $this->encode_posts_array( $list ),
		) );

		// Logged Out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in 1 of 2.
		llms_enroll_student( $student_id, $list[0]['id'] );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in both.
		llms_enroll_student( $student_id, $list[1]['id'] );
		$this->assertPostContentEquals( '', $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		/**
		 * Not enrolled in multiple courses & memberships, mixed (any).
		 */
		$list = array_merge( $this->get_posts_array( 1, 'course' ), $this->get_posts_array( 1, 'llms_membership' ) );
		$post = $this->create_post( array(
			'llms_visibility' => 'not_enrolled',
			'llms_visibility_in' => 'list_any',
			'llms_visibility_posts' => $this->encode_posts_array( $list ),
		) );

		// Logged Out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in 1 of 2.
		llms_enroll_student( $student_id, $list[1]['id'] );
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in both.
		llms_enroll_student( $student_id, $list[0]['id'] );
		$this->assertPostContentEquals( '', $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Not enrolled in anything.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'not_enrolled',
			'llms_visibility_in' => 'any',
		) );
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Logged Out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in a membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( '', $post->post_content );
		llms_unenroll_student( $student_id, $membership_id );
		// Enrolled in a course.
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in course & membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( '', $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Not enrolled in any course.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'not_enrolled',
			'llms_visibility_in' => 'any_course',
		) );

		// Logged Out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in a membership (doesn't grant access).
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		llms_unenroll_student( $student_id, $membership_id );
		// Enrolled in a course.
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( '', $post->post_content );
		// Enrolled in course & membership.
		$this->assertPostContentEquals( '', $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );


		// Get a new student account to test against.
		$student_id = $this->factory->student->create();

		/**
		 * Not enrolled in any membership.
		 */
		$post = $this->create_post( array(
			'llms_visibility' => 'not_enrolled',
			'llms_visibility_in' => 'any_membership',
		) );

		// Logged Out.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Logged in.
		wp_set_current_user( $student_id );
		// Not enrolled.
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		// Enrolled in a course (doesn't grant access).
		llms_enroll_student( $student_id, $course_id );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );
		llms_unenroll_student( $student_id, $course_id );
		// Enrolled in a membership.
		llms_enroll_student( $student_id, $membership_id );
		$this->assertPostContentEquals( '', $post->post_content );
		llms_enroll_student( $student_id, $course_id );
		// Enrolled in course & membership.
		$this->assertPostContentEquals( '', $post->post_content );

		// Log out & start over.
		wp_set_current_user( null );

	}

	/**
	 * Test block visibility for the "logged_in" setting
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_visibility_show_to_logged_in_only() {

		$post = $this->create_post( array(
			'llms_visibility' => 'logged_in',
		) );

		// empty with no logged in user.
		wp_set_current_user( null );
		$this->assertPostContentEquals( '', $post->post_content );

		// displays to logged in user.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

	}

	/**
	 * Test block visibility for the "logged_out" setting
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function test_visibility_show_to_logged_out_only() {

		$post = $this->create_post( array(
			'llms_visibility' => 'logged_out',
		) );

		// displays content when no user.
		wp_set_current_user( null );
		$this->assertPostContentEquals( $post->post_content, $post->post_content );

		// show nothing to logged in user.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertPostContentEquals( '', $post->post_content );

	}

	/**
	 * Test summary
	 *
	 * @since 2.0.0
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_should_filter_block() {

		$main = new LLMS_Blocks_Visibility();

		// Not REST.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $main, 'should_filter_block', array( array() ) ) );

		define( 'REST_REQUEST', true );

		// Is REST but no context, user, etc...
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $main, 'should_filter_block', array( array() ) ) );

		$this->mockGetRequest( array(
			'context' => 'edit',
			// 'post_id' => $this->factory->post->create(),
		) );

		// Context exists but no post.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $main, 'should_filter_block', array( array() ) ) );

		// All required params but no user to validate.
		$this->mockGetRequest( array(
			'context' => 'edit',
			'post_id' => $this->factory->post->create(),
		) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $main, 'should_filter_block', array( array() ) ) );

		// We have a valid user now.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $main, 'should_filter_block', array( array() ) ) );

	}

}
