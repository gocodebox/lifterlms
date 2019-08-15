<?php
/**
 * Test API Keys methods.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group api_keys
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_API_Keys extends LLMS_REST_Unit_Test_Case_Base {

	public function setUp() {

		parent::setUp();
		$this->keys = LLMS_REST_API()->keys();

	}

	/**
	 * Test all potential errors encountered during API Key creation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_errors() {

		// Can't create when an ID is supplied.
		$ret = $this->keys->create( array(
			'id' => 1,
		) );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_exists', $ret );

		$data = array();

		// No description.
		$ret = $this->keys->create( $data );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_missing_description', $ret );

		$data['description'] = 'Mock key description';

		// No user_ids.
		foreach( array( '0', 0, '' ) as $uid ) {
			$data['user_id'] = $uid;
			$ret = $this->keys->create( $data );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_key_missing_user_id', $ret );
		}

		// Invalid user_id.
		$data['user_id'] = 92349234;
		$ret = $this->keys->create( $data );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_user_id', $ret );

		$data['user_id'] = $this->factory->user->create();

		// Invalid permissions.
		$data['permissions'] = 'fake';
		$ret = $this->keys->create( $data );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_permissions', $ret );

	}

	/**
	 * Test the success of the create() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_success() {

		$expected_actions = did_action( 'llms_rest_api_key_created' ) + 1;

		// Use default permissions.
		$data = array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		);
		$ret = $this->keys->create( $data );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_api_key_created' ) );
		$this->assertTrue( is_a( $ret, 'LLMS_REST_API_Key' ) );
		$this->assertTrue( $ret->exists() );
		$this->assertTrue( ! empty( $ret->get( 'consumer_key_one_time' ) ) );
		$this->assertEquals( llms_rest_api_hash( $ret->get( 'consumer_key_one_time' ) ), $ret->get( 'consumer_key' ) );
		$this->assertEquals( 'read', $ret->get( 'permissions' ) );
		$this->assertEquals( substr( $ret->get( 'consumer_key_one_time' ), -7 ), $ret->get( 'truncated_key' ) );
		$this->assertEquals( $data['description'], $ret->get( 'description' ) );
		$this->assertEquals( $data['user_id'], $ret->get( 'user_id' ) );
		$this->assertTrue( 0 === strpos( $ret->get( 'consumer_secret' ), 'cs_' ) );

		// Create a key for each valid permission.
		foreach ( array_keys( $this->keys->get_permissions() ) as $permission ) {

			$expected_actions++;

			$data = array(
				'description' => 'Test Key',
				'user_id' => $this->factory->user->create(),
				'permissions' => $permission,
			);
			$ret = $this->keys->create( $data );
			$this->assertEquals( $expected_actions, did_action( 'llms_rest_api_key_created' ) );
			$this->assertTrue( is_a( $ret, 'LLMS_REST_API_Key' ) );
			$this->assertTrue( $ret->exists() );
			$this->assertTrue( ! empty( $ret->get( 'consumer_key_one_time' ) ) );
			$this->assertEquals( llms_rest_api_hash( $ret->get( 'consumer_key_one_time' ) ), $ret->get( 'consumer_key' ) );
			$this->assertEquals( $permission, $ret->get( 'permissions' ) );
			$this->assertEquals( substr( $ret->get( 'consumer_key_one_time' ), -7 ), $ret->get( 'truncated_key' ) );
			$this->assertEquals( $data['description'], $ret->get( 'description' ) );
			$this->assertEquals( $data['user_id'], $ret->get( 'user_id' ) );
			$this->assertTrue( 0 === strpos( $ret->get( 'consumer_secret' ), 'cs_' ) );

		}

	}

	/**
	 * Test the delete() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_delete() {

		$expected_actions = did_action( 'llms_rest_api_key_deleted' ) + 1;

		// Invalid key.
		$this->assertFalse( $this->keys->delete( 99993423423 ) );

		// Create a mock key to work with.
		$orig = $this->keys->create( array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		) );

		// Returns true.
		$this->assertTrue( $this->keys->delete( $orig->get( 'id' ) ) );

		// Action was run.
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_api_key_deleted' ) );

		// Can't find via new get.
		$this->assertFalse( $this->keys->get( $orig->get( 'id' ) ) );

	}

	/**
	 * Test the get() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get() {

		// Invalid Key.
		$this->assertFalse( $this->keys->get( 99993423423 ) );

		// Create a mock key to work with.
		$orig = $this->keys->create( array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		) );

		// Key exists.
		$ret = $this->keys->get( $orig->get( 'id' ) );
		$this->assertTrue( is_a( $ret, 'LLMS_REST_API_Key' ) );
		$this->assertEquals( $orig->get( 'consumer_key' ), $ret->get( 'consumer_key' ) );
		$this->assertEquals( $orig->get( 'consumer_secret' ), $ret->get( 'consumer_secret' ) );
		$this->assertEquals( llms_rest_api_hash( $orig->get( 'consumer_key_one_time' ) ), $ret->get( 'consumer_key' ) );

	}

	/**
	 * Test the `get_admin_url()` method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_admin_url() {

		$this->assertEquals( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=keys', $this->keys->get_admin_url() );

	}

	/**
	 * Test the get_permissions() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_permissions() {

		$permissions = $this->keys->get_permissions();
		$this->assertEquals( array( 'read', 'write', 'read_write' ), array_keys( $permissions ) );
		$this->assertEquals( array( 'Read', 'Write', 'Read / Write' ), array_values( $permissions ) );

	}

	/**
	 * Test update() error conditions.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_errors() {

		// No ID.
		$ret = $this->keys->update( array() );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_missing_id', $ret );


		// Invalid Key.
		$ret = $this->keys->update( array(
			'id' => 99993423423,
		) );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_key', $ret );

		// Create a mock key to work with.
		$orig = $this->keys->create( array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		) );

		$data = array(
			'id' => $orig->get( 'id' ),
		);

		// Empty and invalid user ids.
		foreach( array( '', 0, '0', 92349234 ) as $uid ) {
			// Invalid user_id.
			$data['user_id'] = $uid;
			$ret = $this->keys->update( $data );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_user_id', $ret );
		}

		$data['user_id'] = $this->factory->user->create();

		// Invalid description.
		$data['description'] = '';
		$ret = $this->keys->update( $data );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_description', $ret );

		$data['description'] = 'Okay description';

		// Invalid permissions.
		$data['permissions'] = 'fake';
		$ret = $this->keys->update( $data );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_key_invalid_permissions', $ret );

	}

	/**
	 * Test update() method success.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_success() {

		// Create a mock key to work with.
		$orig = $this->keys->create( array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		) );

		// New data.
		$data = array(
			'id' => $orig->get( 'id' ),
			'description' => 'Update key description',
			'user_id' => $this->factory->user->create(),
			'permissions' => 'read_write',
			'last_access' => current_time( 'mysql' ),
		);
		$ret = $this->keys->update( $data );
		$this->assertTrue( is_a( $ret, 'LLMS_REST_API_Key' ) );
		$this->assertEquals( $data['description'], $ret->get( 'description' ) );
		$this->assertEquals( $data['user_id'], $ret->get( 'user_id' ) );
		$this->assertEquals( $data['permissions'], $ret->get( 'permissions' ) );
		$this->assertEquals( $data['last_access'], $ret->get( 'last_access' ) );

		// Don't allow write-protected keys to be updated.
		$data = array_merge( array(
			'consumer_key' => 'ast',
			'consumer_secret' => 'ast',
			'truncated_key' => 'ast',
		), $data );
		$ret = $this->keys->update( $data );
		$this->assertEquals( $orig->get( 'consumer_key' ), $ret->get( 'consumer_key' ) );
		$this->assertEquals( $orig->get( 'consumer_secret' ), $ret->get( 'consumer_secret' ) );
		$this->assertEquals( $orig->get( 'truncated_key' ), $ret->get( 'truncated_key' ) );

	}

}
