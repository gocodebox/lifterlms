<?php
/**
 * Tests for LLMS_Integration_Akismet class
 *
 * @package LifterLMS/Tests/Integrations
 *
 * @group integrations
 * @group integration_akismet
 *
 * @since [version]
 */
class LLMS_Test_Integration_Akismet extends LLMS_Unit_Test_Case {

	/**
	 * Instance of the bbPress integration class.
	 *
	 * @var LLMS_Integration_Akismet
	 */
	protected $main = null;

	/**
	 * Set to `true` in order to force a 'ham' or `false` response from comment-check endpoint.
	 *
	 * @var boolean
	 */
	private $is_ham_request = false;

	public static function setUpBeforeClass() {

		global $lifterlms_tests;
		$lifterlms_tests->load_plugin( 'akismet', 'akismet.php' );

	}

	/**
	 * Adds "is_test=1" to any requests made to the /comment-check API endpoint.
	 *
	 * This signals to Akismet that it's test data and should not be used for
	 * learning purposes.
	 *
	 * @since [version]
	 *
	 * @param array $body Body request params.
	 * @return array
	 */
	public function add_test_request_param( $body ) {

		$body['is_test'] = 1;

		// Force a ham (`false`) response from comment-check.
		if ( $this->is_ham_request ) {
			$body['user_role'] = 'administrator';
		}

		return $body;

	}

	/**
	 * Loads a real API key from environment variables
	 *
	 * Copy phpunit.xml.dist to phpunit.xml and add an API key by adding the following before the
	 * closing </phpunit> tag:
	 *
	 * <php><env name="AKISMET_DEV_API_KEY" value="YOUR_API_KEY" force="true" /></php>
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function set_api_key( $enable = true ) {

		$key = getenv( 'AKISMET_DEV_API_KEY' );
		if ( ! $key ) {
			return '';
		}

		// Enable the integation when specified.
		if ( $enable ) {
			$this->main->set_option( 'verify_checkout', 'yes' );
			$this->main->set_option( 'verify_registration', 'yes' );
		}

		update_option( 'wordpress_api_key', $key );

		add_filter( 'llms_akismet_request_body', array( $this, 'add_test_request_param' ) );

		return $key;

	}

	/**
	 * Setup the test case.
	 *
	 * Loads the Akismet integration.
	 *
	 * If the integration can't be loaded, all tests are skipped.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();

		foreach( LLMS()->integrations()->get_integrations() as $int ) {

			if ( 'akismet' === $int->id ) {
				$this->main = $int;
				break;
			}

		}

		if ( ! $this->main ) {
			$this->markTestSkipped( 'The Akismet plugin must be installed to run this test.' );
		}

	}

	/**
	 * Tear down the test case.
	 *
	 * Removes stored API keys (if they exist).
	 *
	 * Disables the integration.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		delete_option( 'wordpress_api_key' );

		$this->main->set_option( 'verify_checkout', 'no' );
		$this->main->set_option( 'verify_registration', 'no' );

		remove_filter( 'llms_akismet_request_body', array( $this, 'add_test_request_param' ) );

	}

	/**
	 * Test that attributes are setup properly.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attributes() {

		$this->assertEquals( 'akismet', $this->main->id );
		$this->assertEquals( 'Akismet', $this->main->title );
		$this->assertTrue( ! empty( $this->main->description ) );
		$this->assertTrue( ! empty( $this->main->description_missing ) );

	}

	/**
	 * Test configure() when the integration is not available
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_configure_not_avaiable() {

		remove_filter( 'llms_integration_akismet_get_settings', array( $this->main, 'mod_default_settings' ) );

		// Integration is not available.
		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );
		$this->assertEquals( 1, has_filter( 'llms_integration_akismet_get_settings', array( $this->main, 'mod_default_settings' ) ) );
		$this->assertEquals( false, has_filter( 'lifterlms_user_registration_data', array( $this->main, 'verify_registration' ) ) );
		$this->assertEquals( false, has_filter( 'llms_akismet_spam_dectected', array( $this->main, 'on_spam_detected' ) ) );

	}

	/**
	 * Test configure() when the integration is available
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_configure_when_enabled_integration() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		remove_filter( 'llms_integration_akismet_get_settings', array( $this->main, 'mod_default_settings' ) );
		remove_filter( 'lifterlms_user_registration_data', array( $this->main, 'verify_registration' ) );
		remove_filter( 'llms_akismet_spam_dectected', array( $this->main, 'on_spam_detected' ) );

		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		$this->assertEquals( 1, has_filter( 'llms_integration_akismet_get_settings', array( $this->main, 'mod_default_settings' ) ) );
		$this->assertEquals( 20, has_filter( 'lifterlms_user_registration_data', array( $this->main, 'verify_registration' ) ) );
		$this->assertEquals( 10, has_filter( 'llms_akismet_spam_dectected', array( $this->main, 'on_spam_detected' ) ) );

	}

	/**
	 * Test get_error_message(): no option stored
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_error_message_no_option() {

		delete_option( $this->main->get_option_name( 'error_message' ) );
		$this->assertEquals( 'There was an error while creating your account. Please try again later.', $this->main->get_error_message() );

	}

	/**
	 * Test get_error_message(): empty option stored
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_error_message_empty_option() {

		$this->main->set_option( 'error_message', '' );
		$this->assertEquals( 'There was an error while creating your account. Please try again later.', $this->main->get_error_message() );

	}

	/**
	 * Test get_error_message(): custom message stored
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_error_message_custom() {

		// Custom message.
		$this->main->set_option( 'error_message', 'Custom message.' );
		$this->assertEquals( 'Custom message.', $this->main->get_error_message() );

		// Reset.
		delete_option( $this->main->get_option_name( 'error_message' ) );

	}

	/**
	 * Test get_name_from_datae() with both first and last name passed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_name_from_data_first_and_last() {

		$data = array(
			'first_name' => 'Thomas',
			'last_name' => 'Levy',
		);
		$this->assertEquals( 'Thomas Levy', LLMS_Unit_Test_Util::call_method( $this->main, 'get_name_from_data', array( $data ) ) );

	}

	/**
	 * Test get_name_from_datae() with both first name only
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_name_from_data_first_only() {

		$data = array(
			'first_name' => 'Thomas',
		);
		$this->assertEquals( 'Thomas', LLMS_Unit_Test_Util::call_method( $this->main, 'get_name_from_data', array( $data ) ) );

	}

	/**
	 * Test get_name_from_datae() with last name only
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_name_from_data_last_only() {

		$data = array(
			'last_name' => 'Levy',
		);
		$this->assertEquals( 'Levy', LLMS_Unit_Test_Util::call_method( $this->main, 'get_name_from_data', array( $data ) ) );

	}

	/**
	 * Test get_name_from_datae() with no names
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_name_from_data_none() {

		$data = array();
		$this->assertEquals( '', LLMS_Unit_Test_Util::call_method( $this->main, 'get_name_from_data', array( $data ) ) );

	}

	/**
	 * Test get_integration_settings() when integration is not available.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_integration_settings_not_available() {

		$prefix = LLMS_Unit_Test_Util::call_method( $this->main, 'get_option_prefix' );
		$ids    = array( 'verify_checkout', 'verify_registration', 'error_message', 'spam_action' );
		foreach ( LLMS_Unit_Test_Util::call_method( $this->main, 'get_integration_settings' ) as $setting ) {
			$this->assertTrue( $setting['disabled'] );
			$this->assertTrue( in_array( str_replace( $prefix, '', $setting['id'] ), $ids, true ) );
		}

	}

	/**
	 * Test get_integration_settings() when integration is available.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_integration_settings_is_available() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$prefix = LLMS_Unit_Test_Util::call_method( $this->main, 'get_option_prefix' );
		$ids    = array( 'verify_checkout', 'verify_registration', 'error_message', 'spam_action' );
		foreach ( LLMS_Unit_Test_Util::call_method( $this->main, 'get_integration_settings' ) as $setting ) {
			$this->assertFalse( $setting['disabled'] );
			$this->assertTrue( in_array( str_replace( $prefix, '', $setting['id'] ), $ids, true ) );
		}

	}

	/**
	 * Test is_enabled(): not enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_enabled_not_enabled() {

		$this->assertFalse( $this->main->is_enabled() );

	}

	/**
	 * Test is_enabled(): checkout enabled only
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_enabled_checkout_only() {

		$this->main->set_option( 'verify_checkout', 'yes' );
		$this->main->set_option( 'verify_registration', 'no' );
		$this->assertTrue( $this->main->is_enabled() );

	}

	/**
	 * Test is_enabled(): reg enabled only
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_enabled_reg_only() {

		$this->main->set_option( 'verify_checkout', 'no' );
		$this->main->set_option( 'verify_registration', 'yes' );
		$this->assertTrue( $this->main->is_enabled() );

	}

	/**
	 * Test is_enabled(): both enabled
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_enabled_both() {

		$this->main->set_option( 'verify_checkout', 'yes' );
		$this->main->set_option( 'verify_registration', 'yes' );
		$this->assertTrue( $this->main->is_enabled() );

	}

	/**
	 * Test is_installed(): no key supplied
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_installed_no_key() {

		$this->assertFalse( $this->main->is_installed() );

	}

	/**
	 * Test is_installed(): invalid api key supplied.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function is_installed_invalid_key() {

		update_option( 'wordpress_api_key', 'fakekey' );
		$this->assertFalse( $this->main->is_installed() );

	}

	/**
	 * Test is_installed(): valid key.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function is_installed_valid_key() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$this->assertTrue( $this->main->is_installed() );

	}

	/**
	 * Test is_spam() for a `true` response (is spam) from the comment-check endpoint.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_spam_yes() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$actions = did_action( 'llms_akismet_spam_dectected' );

		$data = array(
			'email_address' => 'akismet-guaranteed-spam@example.com',
			'first_name'    => 'Spam',
			'last_name'     => 'Test',
		);

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'is_spam', array( $data ) ) );
		$this->assertEquals( ++$actions, did_action( 'llms_akismet_spam_dectected' ) );

	}

	/**
	 * Test is_spam() for a `false` response (not spam) from the comment-check endpoint.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_spam_no() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$data = array(
			'email_address' => 'ham@example.com',
			'first_name'    => 'Ham',
			'last_name'     => 'Test',
		);

		$this->is_ham_request = true;
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'is_spam', array( $data ) ) );
		$this->is_ham_request = false;

	}

	/**
	 * Test the mark_user_as_spam() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mark_user_as_spam() {

		add_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) );

		$user = $this->factory->student->create();
		$this->main->mark_user_as_spam( $user );

		$this->assertTrue( llms_parse_bool( get_user_meta( $user, 'llms_akismet_spam', true ) ) );
		$this->assertFalse( has_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) ) );

		$this->markTestIncomplete( 'test the email' );

	}

	/**
	 * Test maybe_submit_spam() with invalid nonce and referrer
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_invalid_nonce() {

		$this->main->maybe_submit_spam( $this->factory->student->create() );

	}

	/**
	 * Test maybe_submit_spam() cannot report yourself.
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_self() {

		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
		) );

		$user = $this->factory->student->create();
		wp_set_current_user( $user );

		$this->main->maybe_submit_spam( $user );

	}

	/**
	 * Test maybe_submit_spam() user missing required capabilities.
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_no_caps() {

		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
		) );

		wp_set_current_user( $this->factory->student->create() );

		$this->main->maybe_submit_spam( $this->factory->student->create() );

	}

	/**
	 * Test maybe_submit_spam() with invalid nonce
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_but_dont() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// No submission value set.
		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
		) );

		$this->assertFalse( $this->main->maybe_submit_spam( $this->factory->student->create() ) );

		// Explicit no.
		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
			'llms_akismet_submit' => 'no',
		) );

		$this->assertFalse( $this->main->maybe_submit_spam( $this->factory->student->create() ) );

	}

	/**
	 * Test maybe_submit_spam() on a user that hadn't been previously checked.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_cannot_submit() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
			'llms_akismet_submit' => 'yes',
		) );

		$this->assertFalse( $this->main->maybe_submit_spam( $this->factory->student->create() ) );

	}

	/**
	 * Test maybe_submit_spam()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_submit_spam_success() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wp_http_referer' => admin_url(),
			'_wpnonce' => wp_create_nonce( 'delete-users' ),
			'llms_akismet_submit' => 'yes',
		) );

		$user = $this->factory->student->create_and_get();

		// Submit the user so it can be submitted later.
		$this->is_ham_request = true;
		LLMS_Unit_Test_Util::call_method( $this->main, 'is_spam', array( array(
			'email_address' => $user->user_email,
		) ) );
		$this->is_ham_request = false;
		$this->main->record_request_body( $user->get( 'id' ) );

		$res = $this->main->maybe_submit_spam( $user->get( 'id' ) );
		$this->assertTrue( is_array( $res ) );
		$this->assertEquals( 'Thanks for making the web a better place.', $res[1] );

	}

	/**
	 * Test mod_default_settings()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mod_default_settings() {

		$settings = array();
		$settings[] = array(
			'id' => $this->main->get_option_name( 'enabled' ),
		);

		$this->assertEquals( array(), $this->main->mod_default_settings( $settings ) );

	}

	/**
	 * Test mod_user_agent()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mod_user_agent() {

		$ua = $this->main->modify_user_agent( '' );

		$this->assertStringContains( 'WordPress/', $ua );
		$this->assertStringContains( ' | LifterLMS/', $ua );

		$this->assertStringNotContains( 'Akismet/', $ua );

	}

	/**
	 * Test on_spam_detected() when registration is still allowed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_spam_detected_allow() {

		$this->main->set_option( 'spam_action', 'allow' );

		remove_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) );

		$this->main->on_spam_detected( array( array( 'headers' ), true ), array( 'comment_author_email' => 'fake@example.com' ), array( 'email_address' => 'fake@example.com' ) );

		$this->assertEquals( 10, has_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) ) );

		$this->main->set_option( 'spam_action', 'block' );

	}

	/**
	 * Test on_spam_detected() when registration is blocked
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_on_spam_detected_block() {

		$this->main->set_option( 'spam_action', 'block' );

		remove_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) );

		$this->main->on_spam_detected( array( array( 'headers' ), true ), array( 'comment_author_email' => 'fake@example.com' ), array( 'email_address' => 'fake@example.com' ) );

		$this->assertFalse( has_action( 'lifterlms_user_registered', array( $this->main, 'mark_user_as_spam' ) ) );

	}

	/**
	 * Test record_request_body() when no request body is cached
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_record_request_body_no_cache() {

		add_action( 'lifterlms_user_registered', array( $this->main, 'record_request_body' ) );

		$user = $this->factory->student->create();

		$this->main->record_request_body( $user );
		$this->assertEmpty( get_user_meta( $user, 'llms_akismet_orig_req_body' ) );

		$this->assertFalse( has_action( 'lifterlms_user_registered', array( $this->main, 'record_request_body' ) ) );

	}

	/**
	 * Test record_request_body() when request body is cached
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_record_request_body_with_cache() {

		add_action( 'lifterlms_user_registered', array( $this->main, 'record_request_body' ) );

		$user   = $this->factory->student->create();
		$expect = array( 'mock_data' => 'whatever' );

		wp_cache_set( 'llms-akismet-comment-check-req-body', $expect );

		$this->main->record_request_body( $user );
		$this->assertEquals( $expect, get_user_meta( $user, 'llms_akismet_orig_req_body', true ) );
		$this->assertFalse( wp_cache_get( 'llms-akismet-comment-check-req-body' ) );

		$this->assertFalse( has_action( 'lifterlms_user_registered', array( $this->main, 'record_request_body' ) ) );

	}

	/**
	 * Test should_verify()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_should_verify() {

		// Fake screen.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_verify', array( 'fake' ) ) );

		// Reg disabled.
		$this->main->set_option( 'verify_registration', 'no' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_verify', array( 'registration' ) ) );

		// Reg enabled.
		$this->main->set_option( 'verify_registration', 'yes' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_verify', array( 'registration' ) ) );

		// Checkout disabled.
		$this->main->set_option( 'verify_checkout', 'no' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_verify', array( 'checkout' ) ) );

		// Checkout enabled.
		$this->main->set_option( 'verify_checkout', 'yes' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_verify', array( 'checkout' ) ) );

	}

	/**
	 * Test verify_registration(): form is already invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_verify_registration_invalid() {

		$this->assertFalse( $this->main->verify_registration( false, array(), 'doesntmatter' ) );

	}

	/**
	 * Test verify_registration(): not a form that we want to verify.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_verify_registration_should_not_verify() {

		$this->assertTrue( $this->main->verify_registration( true, array(), 'fakescreen' ) );

	}

	/**
	 * Test verify_registration(): verify and not spam
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_verify_registration_not_spam() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$this->main->set_option( 'verify_registration', 'yes' );

		$data = array(
			'email_address' => 'ham@example.com',
		);

		$this->is_ham_request = true;

		// Registration is valid.
		$this->assertTrue( $this->main->verify_registration( true, $data, 'registration' ) );

		// Test cache mechanism is working properly.
		$cached_req = wp_cache_get( 'llms-akismet-comment-check-req-body' );

		$this->assertEquals( $data['email_address'], $cached_req['comment_author_email'] );
		$this->assertArrayHasKey( 'blog', $cached_req );
		$this->assertArrayHasKey( 'blog_lang', $cached_req );
		$this->assertArrayHasKey( 'comment_type', $cached_req );

		$this->is_ham_request = false;

	}

	/**
	 * Test verify_registration(): verify and is spam
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_verify_registration_is_spam_allow() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$this->main->set_option( 'verify_registration', 'yes' );
		$this->main->set_option( 'spam_action', 'allow' );

		$data = array(
			'email_address' => 'akismet-guaranteed-spam@example.com',
		);

		// Registration is valid.
		$this->assertTrue( $this->main->verify_registration( true, $data, 'registration' ) );

	}

	/**
	 * Test verify_registration(): verify and is spam
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_verify_registration_is_spam_block() {

		if ( ! $this->set_api_key( true ) ) {
			$this->markTestSkipped( 'This test requires an Akismet developer API Key.' );
		}

		$this->main->set_option( 'verify_registration', 'yes' );
		$this->main->set_option( 'spam_action', 'block' );

		$data = array(
			'email_address' => 'akismet-guaranteed-spam@example.com',
		);

		$res = $this->main->verify_registration( true, $data, 'registration' );

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms-akismet-user-reg-spam-detected', $res );

	}

}
