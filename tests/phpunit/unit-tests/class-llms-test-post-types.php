<?php
/**
 * Tests for LifterLMS Custom Post Types.
 *
 * @group LLMS_Post_Types
 *
 * @since 3.13.0
 * @since 5.5.0 Addedd tests for deprecated filters of the type "lifterlms_register_post_type_${prefixed_post_type_name}".
 */
class LLMS_Test_Post_Types extends LLMS_UnitTestCase {

	/**
	 * LifterLMS Custom Post Types.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private $post_types = array(
		'course',
		'section',
		'lesson',
		'llms_membership',
		'llms_engagement',
		'llms_order',
		'llms_transaction',
		'llms_achievement',
		'llms_my_achievement',
		'llms_certificate',
		'llms_my_certificate',
		'llms_email',
		'llms_quiz',
		'llms_question',
		'llms_coupon',
		'llms_voucher',
		'llms_review',
		'llms_access_plan',
		'llms_form'
	);

	/**
	 * LifterLMS Custom Post Types for earned engagements.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private $earned_engagements_post_types = array(
		'llms_my_achievement',
		'llms_my_certificate',
	);

	/**
	 * LifterLMS Custom Taxonomies.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private $taxonomies = array(
		'course_cat',
		'course_difficulty',
		'course_tag',
		'course_track',
		'membership_cat',
		'membership_tag',
		'llms_product_visibility',
		'llms_access_plan_visibility',
	);

	/**
	 * LifterLMS Custom Post Statuses.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	private $post_statuses = array(
		'llms-completed',
		'llms-active',
		'llms-expired',
		'llms-on-hold',
		'llms-pending',
		'llms-cancelled',
		'llms-refunded',
		'llms-failed',
		'llms-txn-failed',
		'llms-txn-pending',
		'llms-txn-refunded',
		'llms-txn-succeeded',
	);

	/**
	 * Test LLMS_Post_Types::deregister_sitemap_post_types( $mock ).
	 *
	 * @since 4.3.2
	 *
	 * @return void
	 */
	public function test_deregister_sitemap_post_types() {

		$mock = array(
			'post' => true,
			'page' => true,
			'course' => true,
			'lesson' => true,
			'llms_quiz' => true,
			'llms_certificate' => true,
			'llms_my_certificate' => true
		);

		$expect = array(
			'post' => true,
			'page' => true,
			'course' => true,
		);

		$this->assertEquals( $expect, LLMS_Post_Types::deregister_sitemap_post_types( $mock ) );

	}

	/**
	 * Test get_template().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_template() {

		LLMS_Unit_Test_Util::set_private_property( 'LLMS_Post_Types', 'templates', array() );

		// No template exists.
		$this->assertNull( LLMS_Unit_Test_Util::call_method( 'LLMS_Post_Types', 'get_template', array( 'fake' ) ) );

		// Templates are loaded.
		$this->assertNotEmpty( LLMS_Unit_Test_Util::get_private_property_value( 'LLMS_Post_Types', 'templates' ) );

		// Template is returned.
		$this->assertNotEmpty( LLMS_Unit_Test_Util::call_method( 'LLMS_Post_Types', 'get_template', array( 'llms_my_certificate' ) ) );

	}

	/**
	 * Test register taxonomies.
	 *
	 * @since 3.13.0
	 * @since [version] Use `$this->taxonomies` member.
	 *
	 * @return void
	 */
	public function test_register_post_taxonomies() {

		LLMS_Post_Types::register_taxonomies();

		foreach ( $this->taxonomies as $name ) {
			// var_dump( sprintf( '%s: %s', $name, taxonomy_exists( $name ) ) );
			$this->assertTrue( taxonomy_exists( $name ) );
		}

	}

	/**
	 * Test register_post_type().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_type() {

		$slug = 'a_fake_post_type';
		$post_type = LLMS_Post_Types::register_post_type( $slug, array() );
		$this->assertInstanceOf( 'WP_Post_Type', $post_type );
		$this->assertEquals( $slug, $post_type->name );
		unregister_post_type( $slug );

	}

	/**
	 * Test register_post_type() for a post type that's already been registered.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_type_already_registered() {

		$this->assertTrue( post_type_exists( 'course' ) );
		$post_type = LLMS_Post_Types::register_post_type( 'course', array( 'menu_postion' => 10 ) );
		$this->assertInstanceOf( 'WP_Post_Type', $post_type );
		$this->assertEquals( 52, $post_type->menu_position );

	}

	/**
	 * Test register_post_type() for a post that has a defined block template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_type_with_template() {

		unregister_post_type( 'llms_certificate' );
		$post_type = LLMS_Post_Types::register_post_type( 'llms_certificate', array() );
		$this->assertInstanceOf( 'WP_Post_Type', $post_type );
		$this->assertNotEmpty( $post_type->template );
		unregister_post_type( 'llms_certificate' );

		LLMS_Post_Types::register_post_types();

	}

	/**
	 * Test register post types.
	 *
	 * @since 3.13.0
	 * @since [version] Use `$this->post_types` member.
	 *
	 * @return void
	 */
	public function test_register_post_types() {

		LLMS_Post_Types::register_post_types();

		foreach ( $this->post_types as $name ) {
			$this->assertTrue( post_type_exists( $name ) );
		}

	}

	/**
	 * Test register post statusess.
	 *
	 * @since 3.13.0
	 * @since [version] Use `$this->post_statuses` member.
	 *
	 * @return void
	 */
	public function test_register_post_statuses() {

		LLMS_Post_Types::register_post_statuses();

		foreach ( $this->post_statuses as $name ) {
			$this->assertTrue( ! is_null( get_post_status_object( $name ) ) );
		}

	}

	/**
	 * Test deprecated filters of the type "lifterlms_register_post_type_${prefixed_post_type_name}".
	 *
	 * @expectedDeprecated lifterlms_register_post_type_llms_membership
	 * @expectedDeprecated lifterlms_register_post_type_llms_engagement
	 * @expectedDeprecated lifterlms_register_post_type_llms_order
	 * @expectedDeprecated lifterlms_register_post_type_llms_transaction
	 * @expectedDeprecated lifterlms_register_post_type_llms_achievement
	 * @expectedDeprecated lifterlms_register_post_type_llms_certificate
	 * @expectedDeprecated lifterlms_register_post_type_llms_my_certificate
	 * @expectedDeprecated lifterlms_register_post_type_llms_my_achievement
	 * @expectedDeprecated lifterlms_register_post_type_llms_email
	 * @expectedDeprecated lifterlms_register_post_type_llms_quiz
	 * @expectedDeprecated lifterlms_register_post_type_llms_question
	 * @expectedDeprecated lifterlms_register_post_type_llms_coupon
	 * @expectedDeprecated lifterlms_register_post_type_llms_voucher
	 * @expectedDeprecated lifterlms_register_post_type_llms_review
	 * @expectedDeprecated lifterlms_register_post_type_llms_access_plan
	 * @expectedDeprecated lifterlms_register_post_type_llms_form
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_deprecated_filters() {

		foreach ( $this->post_types as $post_type ) {

			unregister_post_type( $post_type );
			add_filter( "lifterlms_register_post_type_${post_type}", '__return_empty_array' );
			LLMS_Post_Types::register_post_type( $post_type, array() );
			remove_filter( "lifterlms_register_post_type_${post_type}", '__return_empty_array' );

		}

	}

	/**
	 * Test LLMS_Post_Types::get_post_type_caps() when argument is an array.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_post_type_caps_argument_as_array() {

		foreach ( array_diff( $this->post_types, $this->earned_engagements_post_types ) as $post_type ) {
			$post_type = str_replace( 'llms_', '', $post_type );
			$singular  = $post_type;
			$plural    = $post_type . '_plural';

			$post_type = array(
				$singular,
				$plural,
			);

			$this->assertEquals(
				LLMS_Post_Types::get_post_type_caps( $post_type ),
				array(
					'read_post'              => sprintf( 'read_%s', $singular ),
					'read_private_posts'     => sprintf( 'read_private_%s', $plural ),

					'edit_post'              => sprintf( 'edit_%s', $singular ),
					'edit_posts'             => sprintf( 'edit_%s', $plural ),
					'edit_others_posts'      => sprintf( 'edit_others_%s', $plural ),
					'edit_private_posts'     => sprintf( 'edit_private_%s', $plural ),
					'edit_published_posts'   => sprintf( 'edit_published_%s', $plural ),

					'publish_posts'          => sprintf( 'publish_%s', $plural ),

					'delete_post'            => sprintf( 'delete_%s', $singular ),
					'delete_posts'           => sprintf( 'delete_%s', $plural ), // This is the core bug issue here.
					'delete_private_posts'   => sprintf( 'delete_private_%s', $plural ),
					'delete_published_posts' => sprintf( 'delete_published_%s', $plural ),
					'delete_others_posts'    => sprintf( 'delete_others_%s', $plural ),

					'create_posts'           => sprintf( 'create_%s', $plural ),
				),
				$post_type[0]
			);
		}

		foreach ( $this->earned_engagements_post_types as $post_type ) {

			$post_type = str_replace( 'llms_', '', $post_type );

			$post_type = array(
				$post_type,
				$post_type . '_plural',
			);

			$this->assertEquals(
				LLMS_Post_Types::get_post_type_caps( $post_type ),
				LLMS_Post_Types::get_earned_engagements_post_type_caps(),
			);

		}
	}


	/**
	 * Test LLMS_Post_Types::get_post_type_caps() when argument is a string.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_post_type_caps_argument_as_string() {

		foreach ( array_diff( $this->post_types, $this->earned_engagements_post_types ) as $post_type ) {
			$post_type = str_replace( 'llms_', '', $post_type );
			$singular  = $post_type;
			$plural    = $post_type . 's';

			$this->assertEquals(
				LLMS_Post_Types::get_post_type_caps( $post_type ),
				array(
					'read_post'              => sprintf( 'read_%s', $singular ),
					'read_private_posts'     => sprintf( 'read_private_%s', $plural ),

					'edit_post'              => sprintf( 'edit_%s', $singular ),
					'edit_posts'             => sprintf( 'edit_%s', $plural ),
					'edit_others_posts'      => sprintf( 'edit_others_%s', $plural ),
					'edit_private_posts'     => sprintf( 'edit_private_%s', $plural ),
					'edit_published_posts'   => sprintf( 'edit_published_%s', $plural ),

					'publish_posts'          => sprintf( 'publish_%s', $plural ),

					'delete_post'            => sprintf( 'delete_%s', $singular ),
					'delete_posts'           => sprintf( 'delete_%s', $plural ), // This is the core bug issue here.
					'delete_private_posts'   => sprintf( 'delete_private_%s', $plural ),
					'delete_published_posts' => sprintf( 'delete_published_%s', $plural ),
					'delete_others_posts'    => sprintf( 'delete_others_%s', $plural ),

					'create_posts'           => sprintf( 'create_%s', $plural ),
				),
				$post_type[0]
			);
		}

		foreach ( $this->earned_engagements_post_types as $post_type ) {

			$post_type = str_replace( 'llms_', '', $post_type );

			$this->assertEquals(
				LLMS_Post_Types::get_post_type_caps( $post_type ),
				LLMS_Post_Types::get_earned_engagements_post_type_caps(),
			);

		}
	}

	/**
	 * Check actual post types capabilities.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_post_type_capabilities() {
		foreach (
				array(
					'course'              => 'course',
					'lesson'              => 'lesson',
					'llms_membership'     => 'membership',
					'llms_quiz'           => array( 'quiz', 'quizzes' ),
					'llms_question'       => 'question',
					'llms_my_achievement' => 'my_achievement',
					'llms_my_certificate' => 'my_certificate',
				) as $post_type => $post_type_name_for_caps ) {

			$post_type_object = get_post_type_object( $post_type );
			$caps = (array) $post_type_object->cap;
			unset( $caps['read'] );

			$this->assertEquals(
				$caps,
				LLMS_Post_Types::get_post_type_caps( $post_type_name_for_caps ),
			);

		}

	}

}
