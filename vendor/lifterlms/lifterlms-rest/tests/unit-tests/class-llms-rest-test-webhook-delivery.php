<?php
/**
 * Test the webhook model class
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group webhooks
 * @group webhook_delivery
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Webhook_Delivery extends LLMS_REST_Unit_Test_Case_Base {

	private $hook = null;
	private $res = null;

	private $endpoint = 'https://enahg60xdmapr.x.pipedream.net/';

	public function watch_for_delivery( $http_args, $res, $duration, $args, $webhook ) {

		$body = json_decode( $http_args['body'], true );

		$this->assertEquals( 200, wp_remote_retrieve_response_code( $res ) );
		$this->assertEquals( $this->hook->get( 'id' ), $webhook->get( 'id' ) );
		$this->res = compact( 'http_args', 'res', 'duration', 'args', 'webhook', 'body' );

	}

	private function setup_hook( $topic ) {

		$this->hook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => $this->endpoint,
			'topic' => $topic,
			'status' => 'active',
		) );
		$this->hook->enqueue();

	}

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		add_filter( 'llms_rest_webhook_deliver_async', '__return_false' );
		add_action( 'llms_rest_webhook_delivery', array( $this, 'watch_for_delivery' ), 10, 5 );
	}

	/**
	 * Tear down the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		remove_filter( 'llms_rest_webhook_deliver_async', '__return_false' );
		remove_action( 'llms_rest_webhook_delivery', array( $this, 'watch_for_delivery' ), 10 );
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_webhooks" );

	}

	/*
		  /$$$$$$
		 /$$__  $$
		| $$  \__/  /$$$$$$  /$$   /$$  /$$$$$$   /$$$$$$$  /$$$$$$   /$$$$$$$
		| $$       /$$__  $$| $$  | $$ /$$__  $$ /$$_____/ /$$__  $$ /$$_____/
		| $$      | $$  \ $$| $$  | $$| $$  \__/|  $$$$$$ | $$$$$$$$|  $$$$$$
		| $$    $$| $$  | $$| $$  | $$| $$       \____  $$| $$_____/ \____  $$
		|  $$$$$$/|  $$$$$$/|  $$$$$$/| $$       /$$$$$$$/|  $$$$$$$ /$$$$$$$/
		 \______/  \______/  \______/ |__/      |_______/  \_______/|_______/
	*/
	public function test_deliver_course_created() {

		$this->setup_hook( 'course.created' );
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}

	public function test_deliver_course_updated() {

		$this->setup_hook( 'course.updated' );
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		wp_update_post( array(
			'ID' => $id,
			'post_title' => 'New Title',
		) );
		$this->assertEquals( $id, $this->res['body']['id'] );
		$this->assertEquals( 'New Title', $this->res['body']['title']['rendered'] );

	}

	public function test_deliver_course_deleted() {

		$this->setup_hook( 'course.deleted' );
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		wp_trash_post( $id );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}

	public function test_deliver_course_deleted_force() {

		$this->setup_hook( 'course.deleted' );
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		wp_delete_post( $id, true );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}

	public function test_deliver_course_restored() {

		$this->setup_hook( 'course.restored' );
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		wp_trash_post( $id );
		wp_untrash_post( $id );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}

	/*
		  /$$$$$$                        /$$     /$$
		 /$$__  $$                      | $$    |__/
		| $$  \__/  /$$$$$$   /$$$$$$$ /$$$$$$   /$$  /$$$$$$  /$$$$$$$   /$$$$$$$
		|  $$$$$$  /$$__  $$ /$$_____/|_  $$_/  | $$ /$$__  $$| $$__  $$ /$$_____/
		 \____  $$| $$$$$$$$| $$        | $$    | $$| $$  \ $$| $$  \ $$|  $$$$$$
		 /$$  \ $$| $$_____/| $$        | $$ /$$| $$| $$  | $$| $$  | $$ \____  $$
		|  $$$$$$/|  $$$$$$$|  $$$$$$$  |  $$$$/| $$|  $$$$$$/| $$  | $$ /$$$$$$$/
		 \______/  \_______/ \_______/   \___/  |__/ \______/ |__/  |__/|_______/
	*/
	public function test_deliver_section_created() {

		$this->setup_hook( 'section.created' );
		$id = $this->factory->post->create( array( 'post_type' => 'section' ) );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}

	public function test_deliver_section_updated() {

		$this->setup_hook( 'section.updated' );
		$id = $this->factory->post->create( array( 'post_type' => 'section' ) );
		wp_update_post( array(
			'ID' => $id,
			'post_title' => 'New Title',
		) );
		$this->assertEquals( $id, $this->res['body']['id'] );
		$this->assertEquals( 'New Title', $this->res['body']['title']['rendered'] );

	}

	public function test_deliver_section_deleted() {

		$this->setup_hook( 'section.deleted' );
		$id = $this->factory->post->create( array( 'post_type' => 'section' ) );
		wp_trash_post( $id );
		$this->assertEquals( $id, $this->res['body']['id'] );

	}



}
