<?php
/**
 * Test LLMS_Forms Singleton
 *
 * @package LifterLMS/Tests
 *
 * @group forms
 *
 * @since 5.0.0
 * @version [version]
 */
class LLMS_Test_Forms extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Forms
	 */
	private LLMS_Forms $forms;

	/**
	 * Serializes checkboxes attributes and appends a 'llms/form-field-checkboxes' block markup to the form.
	 *
	 * @since [version]
	 *
	 * @param int   $form_id    WP post ID of the form to append to.
	 * @param array $checkboxes Attributes, {@see LLMS_Test_Forms::get_checkboxes_attributes()}.
	 * @return void
	 */
	private function append_checkboxes_to_form( $form_id, $checkboxes ) {

		$form_post               = get_post( $form_id );
		$form_post->post_content .= '<!-- wp:llms/form-field-checkboxes ' . wp_json_encode( $checkboxes ) . ' /-->';
		wp_update_post( $form_post );
	}

	/**
	 * Setup the test
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->forms = LLMS_Forms::instance();

	}

	/**
	 * Teardown the test.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		global $wpdb;
		$wpdb->delete( $wpdb->posts, array( 'post_type' => 'llms_form' ) );

	}

	/**
	 * Returns an array of attributes for a 'llms/form-field-checkboxes' block to be serialized into
	 * a form's `post_content`.
	 *
	 * @since [version]
	 *
	 * @param int $form_id WP post ID of the form.
	 * @return array
	 */
	private function get_checkboxes_attributes( $form_id ) {

		$checkboxes = array(
			'field' => 'checkbox',
			'id'    => "checkbox-{$form_id}-1",
			'label' => 'Do you like coffee?',
			'options' => array(
				array(
					'text'    => 'Yes',
					'key'     => 'like_coffee_yes',
				),
				array(
					'text'    => 'No',
					'key'     => 'like_coffee_no',
				),
				array(
					'text'    => 'No, but I like the smell of it.',
					'key'     => 'like_coffee_smell',
				),
			),
		);

		return $checkboxes;
	}

	/**
	 * Retrieve an array of form locations to run tests against.
	 *
	 * @since 5.0.0
	 *
	 * @return string[]
	 */
	private function get_form_locs() {

		return array( 'checkout', 'registration', 'account' );

	}

	/**
	 * Assert that an array looks like a WordPress block array.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->assertClassHasStaticAttribute( 'instance', 'LLMS_Forms' );

	}

	/**
	 * Test are_requirements_met()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_are_requirements_met() {

		global $wp_version;
		$temp = $wp_version;

		$versions = array(
			'5.3.1' => false,
			'5.6.0' => false,
			'5.6.5' => false,
			'5.7.0' => true,
			'5.7.2' => true,
			'5.8.0' => true,
		);

		foreach ( $versions as $wp_version => $expect ) {

			$this->assertEquals( $expect, LLMS_Forms::instance()->are_requirements_met(), $wp_version );

		}

		// Restore the version.
		$wp_version = $temp;

	}

	/**
	 * Test are_usernames_enabled() when at least one form with a username block exists.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_create_invalid_location() {

		$this->assertFalse( $this->forms->create( 'fake' ) );

	}

	/**
	 * Test convert_settings_to_block_attrs()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_convert_settings_to_block_attrs() {

		$in = array(
			'id' => 'mock',
			'type' => 'text',
			'classes' => 'test',
			'attributes' => array(),
		);

		$out = array(
			'id' => 'mock',
			'field' => 'text',
			'className' => 'test',
			'html_attrs' => array(),
		);

		$this->assertEquals( $out, $this->forms->convert_settings_to_block_attrs( $in ) );

	}

	/**
	 * Test convert_settings_format() for a block -> field transformation
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_convert_settings_format_to_field() {

		$in = array(
			'id' => 'mock',
			'field' => 'text',
			'className' => 'test',
			'html_attrs' => array(),
		);

		$out = array(
			'id' => 'mock',
			'type' => 'text',
			'classes' => 'test',
			'attributes' => array(),
		);

		$this->assertEquals( $out, LLMS_Unit_Test_Util::call_method( $this->forms, 'convert_settings_format', array( $in, 'block' ) ) );

	}

	/**
	 * Test creating/updating forms.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_capability() {
		$this->assertEquals( 'manage_lifterlms', $this->forms->get_capability() );
	}


	/**
	 * Test the get_fields_settings_from_blocks() method.
	 *
	 * @since 5.0.0
	 * @since [version] Added checkboxes.
	 *
	 * @return void
	 */
	public function test_get_fields_settings_from_blocks() {

		$form_id    = $this->forms->create( 'checkout', true );
		$checkboxes = $this->get_checkboxes_attributes( $form_id );
		$this->append_checkboxes_to_form( $form_id, $checkboxes );

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
			$checkboxes['id'],
		);
		$this->assertEquals( $expect, wp_list_pluck( $fields, 'name' ) );

	}

	/**
	 * Test get_free_enroll_form_fields().
	 *
	 * @since 5.0.0
	 * @since [version] Added checkboxes.
	 *
	 * @return void
	 */
	public function test_get_free_enroll_form_fields() {

		$plan = $this->get_mock_plan();

		$form_id = $this->forms->create( 'checkout', true );

		// Add a checkboxes block to the form.
		$checkboxes = $this->get_checkboxes_attributes( $form_id );
		$this->append_checkboxes_to_form( $form_id, $checkboxes );
		$checkboxes_id    = $checkboxes['id'];
		$checkboxes_key_2 = $checkboxes['options'][2]['key'];

		// The user has checked the 2nd checkbox.
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		add_user_meta( $user_id, $checkboxes_id, array( $checkboxes_key_2 ) );

		// Expected field IDs and names.
		$expected_fields = array(
			array( 'id' => 'first_name', 'name' => 'first_name' ),
			array( 'id' => 'last_name', 'name' => 'last_name' ),
			array( 'id' => 'llms_billing_address_1', 'name' => 'llms_billing_address_1' ),
			array( 'id' => 'llms_billing_address_2', 'name' => 'llms_billing_address_2' ),
			array( 'id' => 'llms_billing_city', 'name' => 'llms_billing_city' ),
			array( 'id' => 'llms_billing_country', 'name' => 'llms_billing_country' ),
			array( 'id' => 'llms_billing_state', 'name' => 'llms_billing_state' ),
			array( 'id' => 'llms_billing_zip', 'name' => 'llms_billing_zip' ),
			array( 'id' => 'llms_phone', 'name' => 'llms_phone' ),
			array( 'id' => "{$checkboxes_id}--{$checkboxes_key_2}", 'name' => "{$checkboxes_id}[]" ),
			array( 'id' => null, 'name' => 'free_checkout_redirect' ),
			array( 'id' => 'llms-plan-id', 'name' => 'llms_plan_id' ),
		);

		$fields = $this->forms->get_free_enroll_form_fields( $plan );
		$this->assertCount( count( $expected_fields ), $fields );

		foreach ( $fields as $index => $field ) {
			$actual = array( 'id' => $field['id'] ?? null, 'name' => $field['name'] );
			$this->assertEquals( $expected_fields[ $index ], $actual );
		}

		// Only hidden fields.
		$this->assertEquals( array( 'hidden' ), array_unique( wp_list_pluck( $fields, 'type' ) ) );

	}

	/**
	 * Can't retrieve blocks for an invalid location.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_form_blocks_invalid_location() {

		$this->assertFalse( $this->forms->get_form_blocks( 'fake' ) );

	}

	/**
	 * Can't retrieve blocks for a location that hasn't been installed yet.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_form_fields_invalid_loc() {
		$this->assertFalse( $this->forms->get_form_fields( 'fake' ) );
	}

	/**
	 * Can't retrieve fields for a location that hasn't been installed yet.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_form_html_invalid() {

		$this->assertEquals( '', $this->forms->get_form_html( 'fake' ) );

	}

	/**
	 * Can't get form html for a form that hasn't been installed.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_form_post_invalid() {

		$this->assertFalse( $this->forms->get_form_post( 'fake' ) );

	}

	/**
	 * Test get_form_post() for forms when they're not installed.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_post_type() {
		$this->assertEquals( 'llms_form', $this->forms->get_post_type() );
	}

	/**
	 * test the install() method.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * Test is_block_visible_in_list()
	 *
	 * This additionally covers conditions in get_block_path().
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	public function test_is_block_visible_in_list() {

		$hidden_json = '{"llms_visibility":"logged_in","llms_visibility_in":"any_course"}';

		$visible = '<!-- wp:paragraph -->\n<p>Test</p>\n<!-- /wp:paragraph -->';
		$hidden  = sprintf( '<!-- wp:paragraph %s -->\n<p>Test</p>\n<!-- /wp:paragraph -->', $hidden_json );

		/**
		 * List of tests to run
		 *
		 * @param array[] {
		 *     @type string $0 Test description / message. Passed to the assertion for debugging failed tests.
		 *     @type string $1 Block markup for the block being tested.
		 *     @type string $2 List of blocks for use as second parameter. The HTML from $1 must be found in this list!
		 *     @type bool   $3 The expected result of `is_block_visible_in_list()`.
		 * }
		 */
		$tests = array(

			array(
				'Block not found in the list',
				$visible,
				$hidden,
				false,
			),

			array(
				'Empty list falls back to `is_block_visible()`: is visible',
				$visible,
				'',
				true,
			),

			array(
				'Empty list falls back to `is_block_visible()`: not visible',
				$hidden,
				'',
				false,
			),

			array(
				'Flat list: is visible',
				$visible,
				$hidden . $visible,
				true,
			),

			array(
				'Flat list: not visible',
				$hidden,
				$visible . $hidden,
				false,
			),

			array(
				'Visible in a group',
				$visible,
				sprintf( '<!-- wp:group -->\n<div class="wp-block-group">%s</div>\n<!-- /wp:group -->', $visible ),
				true,
			),

			array(
				'Hidden in a group',
				$hidden,
				sprintf( '<!-- wp:group -->\n<div class="wp-block-group">%s</div>\n<!-- /wp:group -->', $hidden ),
				false,
			),

			array(
				'Visible in a hidden group',
				$visible,
				sprintf( '<!-- wp:group %1$s -->\n<div class="wp-block-group">%2$s</div>\n<!-- /wp:group -->', $hidden_json, $visible ),
				false,
			),

			array(
				'Hidden in a hidden group',
				$hidden,
				sprintf( '<!-- wp:group %1$s -->\n<div class="wp-block-group">%2$s</div>\n<!-- /wp:group -->', $hidden_json, $hidden ),
				false,
			),

			array(
				'Multiple parents: visible -> visible -> visible',
				$visible,
				sprintf( '<!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column -->\n<div class="wp-block-column">%s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $visible ),
				true,
			),

			array(
				'Multiple parents: hidden -> hidden -> hidden',
				$hidden,
				sprintf( '<!-- wp:columns %2$s -->\n<div class="wp-block-columns"><!-- wp:column %2$s -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $hidden, $hidden_json ),
				false,
			),

			array(
				'Multiple parents: visible -> visible -> hidden',
				$hidden,
				sprintf( '<!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column -->\n<div class="wp-block-column">%s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $hidden ),
				false,
			),

			array(
				'Multiple parents: visible -> hidden -> hidden',
				$hidden,
				sprintf( '<!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column %2$s -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $hidden, $hidden_json ),
				false,
			),

			array(
				'Multiple parents: hidden -> hidden -> visible',
				$visible,
				sprintf( '<!-- wp:columns %2$s -->\n<div class="wp-block-columns"><!-- wp:column %2$s -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $visible, $hidden_json ),
				false,
			),

			array(
				'Multiple parents: hidden -> visible -> visible',
				$visible,
				sprintf( '<!-- wp:columns %2$s -->\n<div class="wp-block-columns"><!-- wp:column -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $visible, $hidden_json ),
				false,
			),

			array(
				'Multiple parents: hidden -> visible -> hidden',
				$hidden,
				sprintf( '<!-- wp:columns %2$s -->\n<div class="wp-block-columns"><!-- wp:column -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $hidden, $hidden_json ),
				false,
			),

			array(
				'Multiple parents: visible -> hidden -> visible',
				$visible,
				sprintf( '<!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column %2$s -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $visible, $hidden_json ),
				false,
			),

			array(
				'Break Stuff',
				$visible,
				sprintf( '<!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column %2$s -->\n<div class="wp-block-column">%1$s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->', $visible, $hidden_json ),
				false,
			),

		);

		foreach ( $tests as $data ) {

			$msg    = $data[0];
			$block  = parse_blocks( $data[1] )[0];
			$list   = parse_blocks( $data[2] );
			$expect = $data[3];

			$this->assertEquals( $expect, $this->forms->is_block_visible_in_list( $block, $list ), $msg );

		}


	}

	/**
	 * Test get_block_tree()
	 *
	 * @since 5.1.1
	 *
	 * @return void
	 */
	public function test_get_block_tree() {

		$test_block_json  = '<!-- wp:paragraph -->\n<p>Test</p>\n<!-- /wp:paragraph -->';
		$group_block_json = '<!-- wp:group -->\n<div class="wp-block-group">%1$s</div>\n<!-- /wp:group -->';

		$test_block_as_parent_json  = sprintf( $group_block_json, $test_block_json );

		/**
		 * List of tests to run
		 *
		 * @param array[] {
		 *     @type string $0 Test description / message. Passed to the assertion for debugging failed tests.
		 *     @type string $1 Block markup for the block being tested.
		 *     @type string $2 List of blocks for use as second parameter. The HTML from $1 must be found in this list!
		 *     @type bool   $3 The expected result of `get_block_tree()`.
		 * }
		 */
		$tests = array(

			array(
				'Block in a tree with two levels, with the leaf\'s parent branch having one sibling',
				$test_block_json,
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						$test_block_json
					) .
					sprintf(
						$group_block_json,
						'Suppressed'
					)
				),
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						$test_block_json
					)
				)
			),

			array(
				'Block in a tree with two levels, with the leaf\'s gran parent\'s branch having one sibling',
				$test_block_json,
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						'Suppressed'
					) .
					sprintf(
						$group_block_json,
						sprintf(
							$group_block_json,
							$test_block_json
						)
					)
				),
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						sprintf(
							$group_block_json,
							$test_block_json
						)
					)
				),
			),

			array(
				'No block found',
				$test_block_json,
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						'Something'
					) .
					sprintf(
						$group_block_json,
						sprintf(
							$group_block_json,
							'Something Else'
						)
					)
				),
				''
			),

			array(
				'Block as first of the list',
				$test_block_json,
				$test_block_json,
				$test_block_json
			),

			array(
				'Block\'s children preserved',
				$test_block_as_parent_json,
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						$test_block_as_parent_json
					) .
					sprintf(
						$group_block_json,
						'Suppressed'
					)
				),
				sprintf(
					$group_block_json,
					sprintf(
						$group_block_json,
						$test_block_as_parent_json
					)
				),
			),

		);

		foreach ( $tests as $data ) {

			$msg    = $data[0];
			$block  = parse_blocks( $data[1] )[0];
			$list   = parse_blocks( $data[2] );
			$expect = parse_blocks( $data[3] );

			$this->assertEquals(
				$expect,
				LLMS_Unit_Test_Util::call_method( $this->forms, 'get_block_tree', array( $block, $list ), $msg )
			);

		}

	}

	/**
	 * Test is_location_valid()
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
		$expected = '<!-- wp:llms/form-field-confirm-group {"fieldLayout":"columns","llms_visibility":"logged_out"} --><!-- wp:llms/form-field-user-email {"required":true,"id":"email_address","llms_visibility":"logged_out","name":"email_address","label":"Email Address","data_store":"users","data_store_key":"user_email","field":"email","columns":6,"last_column":false,"isConfirmationControlField":true,"match":"email_address_confirm"} /--><!-- wp:llms/form-field-text {"required":true,"id":"email_address_confirm","llms_visibility":"logged_out","name":"email_address_confirm","label":"Confirm Email Address","data_store":false,"data_store_key":false,"field":"email","columns":6,"last_column":true,"isConfirmationField":true,"match":"email_address"} /--><!-- /wp:llms/form-field-confirm-group --><!-- wp:llms/form-field-user-address --><!-- wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-street-primary {"id":"llms_billing_address_1","required":true,"columns":8,"last_column":false,"name":"llms_billing_address_1","label":"Address","data_store":"usermeta","data_store_key":"llms_billing_address_1","field":"text"} /--><!-- wp:llms/form-field-user-address-street-secondary {"id":"llms_billing_address_2","required":false,"columns":4,"last_column":true,"name":"llms_billing_address_2","label":"","label_show_empty":true,"data_store":"usermeta","data_store_key":"llms_billing_address_2","placeholder":"Apartment, suite, etc...","field":"text"} /--><!-- /wp:llms/form-field-user-address-street --><!-- wp:llms/form-field-user-address-city {"id":"llms_billing_city","required":true,"name":"llms_billing_city","label":"City","data_store":"usermeta","data_store_key":"llms_billing_city","field":"text"} /--><!-- wp:llms/form-field-user-address-country {"id":"llms_billing_country","required":true,"name":"llms_billing_country","label":"Country","data_store":"usermeta","data_store_key":"llms_billing_country","options_preset":"countries","placeholder":"Select a Country","field":"select","className":"llms-select2"} /--><!-- wp:llms/form-field-user-address-region --><!-- wp:llms/form-field-user-address-state {"id":"llms_billing_state","required":true,"columns":6,"last_column":false,"name":"llms_billing_state","label":"State \/ Region","data_store":"usermeta","data_store_key":"llms_billing_state","options_preset":"states","placeholder":"Select a State \/ Region","field":"select","className":"llms-select2"} /--><!-- wp:llms/form-field-user-address-postal-code {"id":"llms_billing_zip","required":true,"columns":6,"last_column":true,"name":"llms_billing_zip","label":"Postal \/ Zip Code","data_store":"usermeta","data_store_key":"llms_billing_zip","field":"text"} /--><!-- /wp:llms/form-field-user-address-region --><!-- /wp:llms/form-field-user-address -->';
		$this->assertEquals( parse_blocks( $expected ), parse_blocks( serialize_blocks( $load ) ) );

	}

	/**
	 * Test load_reusable_blocks(): a non-existent block is passed in
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * Test maybe_load_preview() when no post is found
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_load_preview_no_post() {
		$this->assertFalse( $this->forms->maybe_load_preview( false ) );
	}

	/**
	 * Test maybe_load_preview() when not previewing
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_load_preview_not_preview() {
		$post = $this->factory->post->create_and_get();
		$this->assertEquals( $post, $this->forms->maybe_load_preview( $post ) );
	}

	/**
	 * Test maybe_load_preview() when current user can't preview
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_load_preview_user_cant_preview() {
		global $wp_query;
		$post = $this->factory->post->create_and_get();
		$save = (array) $post;
		$save['post_ID'] = $save['ID'];
		$save['post_content'] = 'autosave content';
		wp_create_post_autosave( $save );
		$wp_query->is_preview();
		$this->assertEquals( $post, $this->forms->maybe_load_preview( $post ) );
	}

	/**
	 * Test maybe_load_preview() when there is a preview
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_maybe_load_preview_user_can_preview() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		global $wp_query;
		$post = $this->factory->post->create_and_get();
		$save = (array) $post;
		$save['post_ID'] = $save['ID'];
		$save['post_content'] = 'autosave content';
		wp_create_post_autosave( $save );
		$wp_query->is_preview();
		$this->assertEquals( $post, $this->forms->maybe_load_preview( $post ) );
	}

	/**
	 * Test block field render function for non-field blocks.
	 *
	 * @since 5.0.0
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
	 * @since 5.0.0
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
	 * @since 5.0.0
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
