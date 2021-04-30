<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Forms extends LLMS_UnitTestCase {

	/**
	 * Setup the test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->forms = LLMS_Forms::instance();

	}

	/**
	 * Teardown the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

	}

	/**
	 * Retrieve an array of form locations to run tests against.
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	private function get_form_locs() {

		return array( 'checkout', 'registration', 'account' );

	}

	/**
	 * Assert that an array looks like a WordPress block array.
	 *
	 * @since [version]
	 *
	 * @param array $block Block settings array.
	 * @return void
	 */
	protected function assertIsABlock( $block ) {

		foreach ( array( 'blockName', 'attrs', 'innerBlocks', 'innerHTML', 'innerContent' ) as $prop ) {
			$this->assertTrue( array_key_exists( $prop, $block ), "Block is missing property {$prop}." );
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $innerBlock ) {
				$this->assertIsABlock( $innerBlock );
			}
		}

	}

	/**
	 * Assert that an array looks like a LifterLMS Form Field settings array.
	 *
	 * @since [version]
	 *
	 * @param array $field Field settings array.
	 * @return void
	 */
	protected function assertIsAField( $field ) {

		foreach ( array( 'id', 'name', 'type' ) as $prop ) {
			$this->assertTrue( array_key_exists( $prop, $field ), "Field is missing property {$prop}." );
		}

	}

	/**
	 * Test singleton instance.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->assertClassHasStaticAttribute( 'instance', 'LLMS_Forms' );

	}

	/**
	 * Test are_usernames_enabled() when at least one form with a username block exists.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_are_usernames_enabled_one_form_with_usernames() {

		update_option( 'lifterlms_registration_generate_username', 'no' );
		$this->forms->create( 'registration', true );

		$this->assertTrue( $this->forms->are_usernames_enabled() );

		// Explicitly disabled by the filter.
		add_filter( 'llms_are_usernames_enabled', '__return_false' );
		$this->assertFalse( $this->forms->are_usernames_enabled() );
		remove_filter( 'llms_are_usernames_enabled', '__return_false' );

	}

	/**
	 * Test are_usernames_enabled() when no forms with usernames exist.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_are_usernames_enabled_no_forms_with_usernames() {

		update_option( 'lifterlms_registration_generate_username', 'yes' );
		$this->forms->create( 'registration', true );
		$this->forms->create( 'checkout', true );

		$this->assertFalse( $this->forms->are_usernames_enabled() );

		// Explicitly enabled by the filter.
		add_filter( 'llms_are_usernames_enabled', '__return_true' );
		$this->assertTrue( $this->forms->are_usernames_enabled() );
		remove_filter( 'llms_are_usernames_enabled', '__return_true' );

	}

	/**
	 * Test are_usernames_enabled() when there's a mixture of forms with and without usernames.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_are_usernames_enabled_some_forms_with_usernames() {

		// Has username.
		update_option( 'lifterlms_registration_generate_username', 'no' );
		$this->forms->create( 'checkout', true );

		// Doesn't have username.
		update_option( 'lifterlms_registration_generate_username', 'yes' );
		$this->forms->create( 'registration', true );

		$this->assertTrue( $this->forms->are_usernames_enabled() );

	}

	/**
	 * Test block_to_field_settings(): ensure keys are renamed properly.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_block_to_field_settings() {

		$attrs = array(
			'id'        => 'field_id',
			'className' => 'mock fake class-name',
			'field'     => 'text',
			'extra'     => 'remains',
		);
		$html   = sprintf( '<!-- wp:llms/form-field-text %s /-->', wp_json_encode( $attrs ) );
		$blocks = parse_blocks( $html );

		$parsed = LLMS_Unit_Test_Util::call_method( $this->forms, 'block_to_field_settings', array( $blocks[0] ) );
		$expect = array(
			'id'      => 'field_id',
			'classes' => 'mock fake class-name',
			'type'    => 'text',
			'extra'   => 'remains',
		);
		$this->assertEquals( $expect, $parsed );

	}

	/**
	 * Test block_to_field_settings(): no keys to rename so attributes don't change.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_block_to_field_settings_no_updates() {

		$attrs  = array(
			'id'    => 'field_id',
			'extra' => 'remains',
		);
		$html   = sprintf( '<!-- wp:llms/form-field-text %s /-->', wp_json_encode( $attrs ) );
		$blocks = parse_blocks( $html );

		$parsed = LLMS_Unit_Test_Util::call_method( $this->forms, 'block_to_field_settings', array( $blocks[0] ) );
		$this->assertEquals( $attrs, $parsed );

	}

	/**
	 * Test block_to_field_settings(): has visibility but the field isn't required so we don't do anything.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_block_to_field_settings_with_visiblity_no_required() {

		$attrs = array(
			'id'              => 'field_id',
			'llms_visibility' => 'logged_in',
			'extra'           => 'remains',
		);
		$html   = sprintf( '<!-- wp:llms/form-field-text %s /-->', wp_json_encode( $attrs ) );
		$blocks = parse_blocks( $html );

		$parsed = LLMS_Unit_Test_Util::call_method( $this->forms, 'block_to_field_settings', array( $blocks[0] ) );
		$this->assertEquals( $attrs, $parsed );

	}

	/**
	 * Test block_to_field_settings(): has visibility and field is required so the required should be switched to optional.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_block_to_field_settings_with_visiblity_is_required() {

		$attrs = array(
			'id'              => 'field_id',
			'llms_visibility' => 'logged_in',
			'extra'           => 'remains',
			'required'        => true,
		);
		$html   = sprintf( '<!-- wp:llms/form-field-text %s /-->', wp_json_encode( $attrs ) );
		$blocks = parse_blocks( $html );

		$parsed = LLMS_Unit_Test_Util::call_method( $this->forms, 'block_to_field_settings', array( $blocks[0] ) );
		$expect = $attrs;
		$expect['required'] = false;
		$this->assertEquals( $expect, $parsed );

	}

	/**
	 * Test cascade_visibility_attrs() for blocks with no innerBlocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_cascade_visibility_attrs_no_inner_blocks() {

		$blocks = parse_blocks( '<!-- wp:paragraph --><p>mock</p><!-- /wp:paragraph --><!-- wp:paragraph {"llms_visibility":"logged_out"} --><p>mock</p><!-- /wp:paragraph -->' );

		// No changes to make.
		$res = LLMS_Unit_Test_Util::call_method( $this->forms, 'cascade_visibility_attrs', array( $blocks ) );
		$this->assertEquals( $blocks, $res );

		// Add the visibility setting.
		$res = LLMS_Unit_Test_Util::call_method( $this->forms, 'cascade_visibility_attrs', array( $blocks, 'logged_in' ) );

		// Changed.
		$this->assertEquals( 'logged_in', $res[0]['attrs']['llms_visibility'] );

		// Unchanged.
		$this->assertEquals( 'logged_out', $res[1]['attrs']['llms_visibility'] );

	}

	/**
	 * Test cascade_visibility_attrs() for blocks with innerBlocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_cascade_visibility_attrs_with_inner_blocks() {

		$blocks = parse_blocks( '<!-- wp:columns {"className":"has-2-columns"} -->
			<div class="wp-block-columns has-2-columns"><!-- wp:column -->
			<div class="wp-block-column"><!-- wp:paragraph --><p>mock</p><!-- /wp:paragraph --></div>
			<!-- /wp:column -->

			<!-- wp:column -->
			<div class="wp-block-column"><!-- wp:paragraph {"llms_visibility":"logged_out"} --><p>mock</p><!-- /wp:paragraph --></div>
			<!-- /wp:column --></div>
			<!-- /wp:columns -->' );

		// No changes to make.
		$res = LLMS_Unit_Test_Util::call_method( $this->forms, 'cascade_visibility_attrs', array( $blocks ) );
		$this->assertEquals( $blocks, $res );

		// Add the visibility setting.
		$res = LLMS_Unit_Test_Util::call_method( $this->forms, 'cascade_visibility_attrs', array( $blocks, 'logged_in' ) );

		// Changed.
		$this->assertEquals( 'logged_in', $res[0]['attrs']['llms_visibility'] );
		$this->assertEquals( 'logged_in', $res[0]['innerBlocks'][0]['attrs']['llms_visibility'] );
		$this->assertEquals( 'logged_in', $res[0]['innerBlocks'][0]['innerBlocks'][0]['attrs']['llms_visibility'] );

		$this->assertEquals( 'logged_in', $res[0]['innerBlocks'][1]['attrs']['llms_visibility'] );

		// Already had visibility so this one doesn't change.
		$this->assertEquals( 'logged_out', $res[0]['innerBlocks'][1]['innerBlocks'][0]['attrs']['llms_visibility'] );

	}

	/**
	 * Test creation for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_invalid_location() {

		$this->assertFalse( $this->forms->create( 'fake' ) );

	}

	/**
	 * Test creating/updating forms.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create() {

		$locs = $this->forms->get_locations();
		$created = array();

		// Create new forms.
		foreach ( $locs as $loc => $data ) {
			$id = $this->forms->create( $loc );
			$this->assertTrue( is_numeric( $id ) );
			$post = get_post( $id );
			$this->assertEquals( 'llms_form', $post->post_type );
			$this->assertEquals( $loc, get_post_meta( $post->ID, '_llms_form_location', true ) );

			foreach ( $data['meta'] as $key => $val ) {
				$this->assertEquals( $val, get_post_meta( $post->ID, $key, true ) );
			}

			$created[ $loc ] = $id;

		}

		// Locs already exist.
		foreach ( array_keys( $locs ) as $loc ) {
			$this->assertFalse( $this->forms->create( $loc ) );
		}

		// Locs already exist and we want to update them.
		foreach ( array_keys( $locs ) as $loc ) {
			$this->assertEquals( $created[ $loc ], $this->forms->create( $loc, true ), $loc );
		}

	}

	/**
	 * Test forms author on install
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_forms_author_on_install() {

		// Clean user* tables.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE $wpdb->users" );
		$wpdb->query( "TRUNCATE TABLE $wpdb->usermeta" );

		// Create a subscriber.
		$subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$locs = $this->forms->get_locations();

		// Install forms
		$installed = $this->forms->install();

		foreach ( $installed as $loc => $id ) {
			// No admin users, expect 0.
			$this->assertEquals( 0, get_post( $id )->post_author, $id );
		}

		// Delete forms.
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

		// Create two admins.
		$admins = $this->factory->user->create_many( 2, array( 'role' => 'administrator' ) );

		// Install forms.
		$installed = $this->forms->install();

		foreach ( $installed as $loc => $id ) {
			// Expect the first admin to be the forms author.
			$this->assertEquals( $admins[0], get_post( $id )->post_author, $id );
		}

		// Delete forms.
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

		// Log in as subscriber.
		wp_set_current_user( $subscriber );

		// Install forms.
		$installed = $this->forms->install();

		foreach ( $installed as $loc => $id ) {
			// Expect the first admin to be the forms author.
			$this->assertEquals( $admins[0], get_post( $id )->post_author, $id );
		}

		// Delete forms.
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

		// Log in as first admin.
		wp_set_current_user( $admins[0] );

		// Install forms.
		$installed = $this->forms->install();

		foreach ( $installed as $loc => $id ) {
			// Expect the first admin to be the forms author.
			$this->assertEquals( $admins[0], get_post( $id )->post_author, $id );
		}

		// Delete forms.
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

		// Log in as second admin.
		wp_set_current_user( $admins[1] );

		// Install forms.
		$installed = $this->forms->install();

		foreach ( $installed as $loc => $id ) {
			// Expect the first admin to be the forms author.
			$this->assertEquals( $admins[1], get_post( $id )->post_author, $id );
		}

	}

	/**
	 * Test the get_capability() method
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_capability() {
		$this->assertEquals( 'manage_lifterlms', $this->forms->get_capability() );
	}


	/**
	 * Test the get_fields_settings_from_blocks() method
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields_settings_from_blocks() {

		$this->forms->create( 'checkout', true );

		$blocks = $this->forms->get_form_blocks( 'checkout' );

		$fields = $this->forms->get_fields_settings_from_blocks( $blocks );

		foreach ( $fields as $field ) {
 			$this->assertIsArray( $field );
			$this->assertTrue( ! empty( $field ) );
		}

		$expect = array(
			'email_address',
			'email_address_confirm',
			'password',
			'password_confirm',
			'llms-password-strength-meter',
			'first_name',
			'last_name',
			'llms_billing_address_1',
			'llms_billing_address_2',
			'llms_billing_city',
			'llms_billing_country',
			'llms_billing_state',
			'llms_billing_zip',
			'llms_phone',
		);
		$this->assertEquals( $expect, wp_list_pluck( $fields, 'name' ) );

	}

	/**
	 * Test get_free_enroll_form_fields()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_free_enroll_form_fields() {

		$plan = $this->get_mock_plan();
		wp_set_current_user( $this->factory->user->create() );

		$this->forms->create( 'checkout', true );

		$fields = $this->forms->get_free_enroll_form_fields( $plan );

		// Expected field list by name.
		$expect = array(
			'first_name',
			'last_name',
			'llms_billing_address_1',
			'llms_billing_address_2',
			'llms_billing_city',
			'llms_billing_country',
			'llms_billing_state',
			'llms_billing_zip',
			'llms_phone',
			'free_checkout_redirect',
			'llms_plan_id',
		);
		$this->assertEquals( $expect, wp_list_pluck( $fields, 'name' ) );

		// Only hidden fields.
		$this->assertEquals( array( 'hidden' ), array_unique( wp_list_pluck( $fields, 'type' ) ) );

	}

	/**
	 * Can't retrieve blocks for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks_invalid_location() {

		$this->assertFalse( $this->forms->get_form_blocks( 'fake' ) );

	}

	/**
	 * Can't retrieve blocks for a location that hasn't been installed yet.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_blocks( $loc ) );
		}

	}

	/**
	 * Test get_form_blocks() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_blocks() {

		foreach ( $this->get_form_locs() as $loc ) {

			$this->forms->create( $loc );
			$blocks = $this->forms->get_form_blocks( $loc );

			foreach ( $blocks as $block ) {
				$this->assertIsABlock( $block );
			}

		}


	}

	/**
	 * Can't retrieve fields for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_fields_invalid_loc() {
		$this->assertFalse( $this->forms->get_form_fields( 'fake' ) );
	}

	/**
	 * Can't retrieve fields for a location that hasn't been installed yet.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_fields_not_installed() {
		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_fields( $loc ) );
		}
	}

	/**
	 * Test get_form_fields() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_fields() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->forms->create( $loc );
			$fields = $this->forms->get_form_fields( $loc );

			foreach ( $fields as $field ) {
				$this->assertIsAField( $field );
			}
		}

	}

	/**
	 * Can't get form html for an invalid form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_html_invalid() {

		$this->assertEquals( '', $this->forms->get_form_html( 'fake' ) );

	}

	/**
	 * Can't get form html for a form that hasn't been installed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_html_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertEquals( '', $this->forms->get_form_html( $loc ) );
		}

	}

	/**
	 * Test get_form_html() method.
	 *
	 * @since [version]
	 *
	 * @todo  this test can assert a lot more and should.
	 *
	 * @return void
	 */
	public function test_get_form_html() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->forms->create( $loc );
			$html = $this->forms->get_form_html( $loc );

			$this->assertStringContains( '<div class="llms-form-field type-email', $html );
		}

	}

	/**
	 * Can't retrieve a post for an invalid location.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_post_invalid() {

		$this->assertFalse( $this->forms->get_form_post( 'fake' ) );

	}

	/**
	 * Test get_form_post() for forms when they're not installed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_post_not_installed() {

		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertFalse( $this->forms->get_form_post( $loc ) );
		}

	}

	/**
	 * Test get_form_post()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_form_post() {

		foreach ( $this->get_form_locs() as $loc ) {
			$id = $this->forms->create( $loc );
			$this->assertEquals( get_post( $id ), $this->forms->get_form_post( $loc ) );
		}

	}

	/**
	 * Test get_locations() method.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return void
	 */
	public function test_get_locations() {

		$locs = $this->forms->get_locations();
		foreach ( $this->get_form_locs() as $loc ) {
			$this->assertArrayHasKey( $loc, $locs );
			$this->assertArrayHasKey( 'name', $locs[ $loc ] );
			$this->assertArrayHasKey( 'description', $locs[ $loc ] );
			$this->assertArrayHasKey( 'title', $locs[ $loc ] );
			$this->assertArrayHasKey( 'meta', $locs[ $loc ] );
		}

	}

	/**
	 * Test the get_post_type() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_post_type() {
		$this->assertEquals( 'llms_form', $this->forms->get_post_type() );
	}

	/**
	 * test the install() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_install() {

		$installed = $this->forms->install();
		$this->assertEquals( 3, count( $installed ) );

		foreach( $installed as $id ) {
			$post = get_post( $id );
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
			$this->assertEquals( 'llms_form', $post->post_type );
		}

		// Already installed.
		$installed = $this->forms->install();
		foreach ( $installed as $id ) {
			$this->assertFalse( $id );
		}

	}

	/**
	 * Test is_block_visible() when no visibility settings exist on the block.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_block_visible_no_visibility() {

		$blocks = parse_blocks( '<!-- wp:paragraph --><p>Fake paragraph content</p><!-- /wp:paragraph -->' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->forms, 'is_block_visible', array( $blocks[0] ) ) );

	}

	/**
	 * Test is_block_visible() when there are visibility settings which would affect the visibility of the block.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_block_visible_with_visibility() {

		// Logged out users only.
		$blocks = parse_blocks( '<!-- wp:paragraph {"llms_visibility":"logged_out"} --><p>Fake paragraph content</p><!-- /wp:paragraph -->' );

		// No user, show the block.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->forms, 'is_block_visible', array( $blocks[0] ) ) );

		// Has a user, don't show.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->forms, 'is_block_visible', array( $blocks[0] ) ) );

	}

	/**
	 * Test is_location_valid()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_location_valid() {

		foreach ( array_keys( $this->forms->get_locations() ) as $loc ) {
			$this->assertTrue( $this->forms->is_location_valid( $loc ) );
		}

		$this->assertFalse( $this->forms->is_location_valid( 'fake' ) );

	}

	/**
	 * Test load_reusable_blocks() default successful behavior.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_load_reusable_blocks() {

		$blocks = array(
			LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_reusable_block', array( 'email' ) ),
			LLMS_Unit_Test_Util::call_method( 'LLMS_Form_Templates', 'get_reusable_block', array( 'address' ) ),
		);

		$load = LLMS_Unit_Test_Util::call_method( $this->forms, 'load_reusable_blocks', array( $blocks ) );

		// Make sure the loaded blocks match the following snapshot when serialized.
		$expected = '<!-- wp:llms/form-field-confirm-group {"fieldLayout":"columns","llms_visibility":"logged_out"} --><!-- wp:llms/form-field-user-email {"field":"email","required":true,"label":"Email Address","name":"email_address","id":"email_address","data_store":"users","data_store_key":"user_email","llms_visibility":"logged_out","columns":6,"last_column":false,"isConfirmationControlField":true,"match":"email_address_confirm"} /--><!-- wp:llms/form-field-text {"field":"email","required":true,"label":"Confirm Email Address","name":"email_address_confirm","id":"email_address_confirm","data_store":false,"data_store_key":false,"llms_visibility":"logged_out","columns":6,"last_column":true,"isConfirmationField":true,"match":"email_address"} /--><!-- /wp:llms/form-field-confirm-group --><!-- wp:llms/form-field-user-address --><!-- wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-street-primary {"field":"text","label":"Address","name":"llms_billing_address_1","id":"llms_billing_address_1","data_store":"usermeta","data_store_key":"llms_billing_address_1","columns":8,"last_column":false} /--><!-- wp:llms/form-field-user-address-street-secondary {"field":"text","label":"","label_show_empty":true,"placeholder":"Apartment, suite, etc...","name":"llms_billing_address_2","id":"llms_billing_address_2","data_store":"usermeta","data_store_key":"llms_billing_address_2","columns":4,"last_column":true} /--><!-- /wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-city {"field":"text","label":"City","name":"llms_billing_city","id":"llms_billing_city","data_store":"usermeta","data_store_key":"llms_billing_city"} /--><!-- wp:llms/form-field-user-address-country {"field":"select","label":"Country","name":"llms_billing_country","id":"llms_billing_country","data_store":"usermeta","data_store_key":"llms_billing_country","options_preset":"countries","placeholder":"Select a Country"} /--><!-- wp:llms/form-field-user-address-region --><!-- wp:llms/form-field-user-address-state {"field":"select","label":"State \/ Region","options_preset":"states","placeholder":"Select a State \/ Region","name":"llms_billing_state","id":"llms_billing_state","data_store":"usermeta","data_store_key":"llms_billing_state","columns":6,"last_column":false} /--><!-- wp:llms/form-field-user-address-postal-code {"field":"text","label":"Postal \/ Zip Code","name":"llms_billing_zip","id":"llms_billing_zip","data_store":"usermeta","data_store_key":"llms_billing_zip","columns":6,"last_column":true} /--><!-- /wp:llms/form-field-user-address-region --><!-- /wp:llms/form-field-user-address -->';
		$this->assertEquals( $expected, serialize_blocks( $load ) );

	}

	/**
	 * Test load_reusable_blocks(): a non-existent block is passed in
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_load_reusable_blocks_fake() {

		$blocks = array(
			array(
				'blockName'    => 'core/block',
				'attrs'        => array( 'ref' => $this->factory->post->create() + 1 ),
				'innerContent' => array(),
			),
		);

		$load = LLMS_Unit_Test_Util::call_method( $this->forms, 'load_reusable_blocks', array( $blocks ) );
		$this->assertEquals( array(), $load );

	}

	/**
	 * Test load_reusable_blocks(): when the reusable block is not published.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_load_reusable_blocks_draft() {

		$blocks = array(
			array(
				'blockName'    => 'core/block',
				'attrs'        => array( 'ref' => $this->factory->post->create( array( 'post_status' => 'draft' ) ) ),
				'innerContent' => array(),
			),
		);

		$load = LLMS_Unit_Test_Util::call_method( $this->forms, 'load_reusable_blocks', array( $blocks ) );
		$this->assertEquals( array(), $load );

	}

	/**
	 * Test block field render function for non-field blocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_render_field_block_non_field_block() {

		$html = '<p>Fake paragraph content</p>';
		$blocks = parse_blocks( '<!-- wp:paragraph -->' . $html . '<!-- /wp:paragraph -->' );
		$this->assertEquals( $html, $this->forms->render_field_block( $html, $blocks[0] ) );

	}

	/**
	 * Test rendering a field block as a field.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_render_field_block() {

		$atts = array(
			'id' => 'field_id',
		);

		$blocks = parse_blocks( '<!-- wp:llms/form-field-text {"id":"field_id"} /-->' );

		$this->assertEquals( llms_form_field( $atts, false ), $this->forms->render_field_block( '', $blocks[0] ) );

	}

	/**
	 * Test rendering a field block which contains fields in the inner blocks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_render_field_block_with_inner() {

		$blocks = parse_blocks( '<!-- wp:llms/form-field-confirm-group -->
<!-- wp:llms/form-field-user-email {"id":"one"} /-->

<!-- wp:llms/form-field-text {"id":"two"} /-->
<!-- /wp:llms/form-field-confirm-group -->' );

		ob_start();
		llms_form_field( array( 'id' => 'one' ) );
		echo "\n";
		llms_form_field( array( 'id' => 'two' ) );
		$expected = ob_get_clean();

		$this->assertEquals( $expected, $this->forms->render_field_block( '', $blocks[0] ) );

	}

}




