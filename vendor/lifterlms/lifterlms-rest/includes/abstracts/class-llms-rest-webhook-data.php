<?php
/**
 * Webhook Getters & Setters
 *
 * @package  LifterLMS_REST/Abstracts
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Webhook class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.6 Retrieve proper payload for enrollment and progress resources.
 */
abstract class LLMS_REST_Webhook_Data extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table column name => format
	 *
	 * @var string[]
	 */
	protected $columns = array(

		'status'           => '%s',
		'name'             => '%s',
		'delivery_url'     => '%s',
		'secret'           => '%s',
		'topic'            => '%s',
		'user_id'          => '%d',
		'created'          => '%s',
		'updated'          => '%s',
		'failure_count'    => '%d',
		'pending_delivery' => '%d',

	);

	/**
	 * Database Table Name
	 *
	 * @var  string
	 */
	protected $table = 'webhooks';

	/**
	 * The record type
	 *
	 * Used for filters/actions.
	 *
	 * @var  string
	 */
	protected $type = 'webhook';

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int  $id API Key ID.
	 * @param bool $hydrate If true, hydrates the object on instantiation if an ID is supplied.
	 */
	public function __construct( $id = null, $hydrate = true ) {

		$this->id = $id;
		if ( $this->id && $hydrate ) {
			$this->hydrate();
		}

		// Adds created and updated dates on instantiation.
		parent::__construct();

	}


	/**
	 * Retrieve an admin nonce url for deleting an API key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_delete_link() {

		return add_query_arg(
			array(
				'section'              => 'webhooks',
				'delete-webhook'       => $this->get( 'id' ),
				'delete-webhook-nonce' => wp_create_nonce( 'delete' ),
			),
			LLMS_REST_API()->keys()->get_admin_url()
		);

	}

	/**
	 * Generate a delivery signature from a delivery payload.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $payload JSON-encoded payload.
	 * @return string
	 */
	public function get_delivery_signature( $payload ) {

		/**
		 * Allow overriding of signature generation.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param string $signature Custom signature. Return a string to replace the default signature.
		 * @param string $payload JSON-encoded body to be delivered.
		 * @param int $id Webhook id.
		 */
		$signature = apply_filters( 'llms_rest_webhook_signature_pre', null, $payload, $this->get( 'id' ) );
		if ( $signature && is_string( $signature ) ) {
			return $signature;
		}

		/**
		 * Customize the hash algorithm used to generate the webhook delivery signature.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param string $algo Hash algorithm. Defaults to 'sha256'. List of supported algorithms available at https://www.php.net/manual/en/function.hash-hmac-algos.php.
		 * @param string $payload JSON-encoded body to be delivered.
		 * @param int $id Webhook ID.
		 */
		$hash_algo = apply_filters( 'llms_rest_webhook_hash_algorithm', 'sha256', $payload, $this->get( 'id' ) );
		$ts        = llms_current_time( 'timestamp' );
		$message   = $ts . '.' . $payload;
		$hash      = hash_hmac( $hash_algo, $message, $this->get( 'secret' ) );

		return sprintf( 't=%1$d,v1=%2$s', $ts, $hash );

	}

	/**
	 * Retrieve the admin URL where the api key is managed.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_edit_link() {
		return add_query_arg(
			array(
				'section'      => 'webhooks',
				'edit-webhook' => $this->get( 'id' ),
			),
			LLMS_REST_API()->keys()->get_admin_url()
		);
	}

	/**
	 * Retrieve the topic event
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_event() {

		$topic = explode( '.', $this->get( 'topic' ) );
		return apply_filters( 'llms_rest_webhook_get_event', isset( $topic[1] ) ? $topic[1] : '', $this->get( 'id' ) );

	}

	/**
	 * Retrieve an array of hooks for the webhook topic.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string[]
	 */
	public function get_hooks() {

		if ( 'action' === $this->get_resource() ) {
			$hooks = array( $this->get_event() => 1 );
		} else {
			$all_hooks = LLMS_REST_API()->webhooks()->get_hooks();
			$topic     = $this->get( 'topic' );
			$hooks     = isset( $all_hooks[ $topic ] ) ? $all_hooks[ $topic ] : array();
		}

		return apply_filters( 'llms_rest_webhook_get_hooks', $hooks, $this->get( 'id' ) );

	}

	/**
	 * Retrieve a payload for webhook delivery.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.6 Retrieve proper payload for enrollment and progress resources.
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return array
	 */
	protected function get_payload( $args ) {

		// Switch current user to the user who created the webhook.
		$current_user = get_current_user_id();
		wp_set_current_user( $this->get( 'user_id' ) );

		$resource = $this->get_resource();
		$event    = $this->get_event();

		$payload = array();
		if ( 'deleted' === $event ) {

			if ( in_array( $this->get_resource(), array( 'enrollment', 'progress' ), true ) ) {
				$payload['student_id'] = $args[0];
				$payload['post_id']    = $args[1];
			} else {
				$payload['id'] = $args[0];
			}
		} elseif ( 'action' === $resource ) {

			$payload['action'] = current( $this->get_hooks() );
			$payload['args']   = $args;

		} else {

			if ( 'enrollment' === $resource ) {
				$endpoint = sprintf( '/llms/v1/students/%1$d/enrollments/%2$d', $args[0], $args[1] );
			} elseif ( 'progress' === $resource ) {
				$endpoint = sprintf( '/llms/v1/students/%1$d/progress/%2$d', $args[0], $args[1] );
			} else {
				$endpoint = sprintf( '/llms/v1/%1$ss/%2$d', $resource, $args[0] );
			}

			$payload = llms_rest_get_api_endpoint_data( $endpoint );

		}

		// Restore the current user.
		wp_set_current_user( $current_user );

		/**
		 * Filter the webhook payload prior to delivery
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $payload Webhook payload.
		 * @param string $resource Webhook resource.
		 * @param string $event Webhook event.
		 * @param array $args Numeric array of arguments from the originating hook.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 */
		return apply_filters( 'llms_rest_webhook_get_payload', $payload, $resource, $event, $args, $this );

	}

	/**
	 * Retrieve the topic resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_resource() {

		$topic = explode( '.', $this->get( 'topic' ) );
		return apply_filters( 'llms_rest_webhook_get_resource', $topic[0], $this->get( 'id' ) );

	}

	/**
	 * Retrieve a user agent string to use for delivering webhooks.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function get_user_agent() {
		global $wp_version;
		return sprintf( 'LifterLMS/%1$s Hookshot (WordPress/%2$s)', LLMS()->version, $wp_version );
	}

	/**
	 * Increment delivery failures and after max allowed failures are reached, set status to disabled.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_Webhook
	 */
	protected function set_delivery_failure() {

		$failures = absint( $this->get( 'failure_count' ) );

		$this->set( 'failure_count', ++$failures );

		/**
		 * Filter the number of times a webhook is allowed to fail before it is automatically disabled.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param int $num Number of allowed failures. Default: 5.
		 */
		$max_allowed = apply_filters( 'llms_rest_webhook_max_delivery_failures', 5 );

		if ( $failures > $max_allowed ) {

			$this->set( 'status', 'disabled' );

			/**
			 * Fires immediately after a webhook has been disabled due to exceeding its maximum allowed failures.
			 *
			 * @since 1.0.0-beta.1
			 *
			 * @param int $webhook_id ID of the webhook.
			 */
			do_action( 'llms_rest_webhook_disabled_by_delivery_failures', $this->get( 'id' ) );

		}

		return $this;

	}

}
