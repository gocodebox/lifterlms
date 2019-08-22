<?php
/**
 * Webhook Model.
 *
 * @package  LifterLMS_REST/Models
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Webhook class.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Webhook extends LLMS_REST_Webhook_Data {

	/**
	 * Delivers the webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return void
	 */
	public function deliver( $args ) {

		$start   = microtime( true );
		$payload = $this->get_payload( $args );

		$http_args = array(
			'method'      => 'POST',
			'timeout'     => 60,
			'redirection' => 0,
			'user-agent'  => $this->get_user_agent(),
			'body'        => trim( wp_json_encode( $payload ) ),
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
		);

		/**
		 * Modify HTTP args used to deliver the webhook.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $http_args HTTP request args suitable for `wp_remote_request()`.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 * @param mixed $args First argument passed to the action triggering the webhook.
		 */
		$http_args = apply_filters( 'llms_rest_webhook_delivery_args', $http_args, $this, $args );

		$delivery_id = wp_hash( $this->get( 'id' ) . strtotime( 'now' ) );

		$http_args['headers'] = array_merge(
			$http_args['headers'],
			array(
				'X-LLMS-Webhook-Source'    => home_url( '/' ),
				'X-LLMS-Webhook-Topic'     => $this->get( 'topic' ),
				'X-LLMS-Webhook-Resource'  => $this->get_resource(),
				'X-LLMS-Webhook-Event'     => $this->get_event(),
				'X-LLMS-Webhook-Signature' => $this->get_delivery_signature( $http_args['body'] ),
				'X-LLMS-Webhook-ID'        => $this->get( 'id' ),
				'X-LLMS-Delivery-ID'       => $delivery_id,
			)
		);

		$res = wp_safe_remote_request( $this->get( 'delivery_url' ), $http_args );

		$duration = round( microtime( true ) - $start, 5 );

		$this->delivery_after( $delivery_id, $http_args, $res, $duration );

		/**
		 * Fires after a webhook is delivered.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $http_args HTTP request args.
		 * @param WP_Error|array $res Remote response.
		 * @param int $duration Executing time.
		 * @param array $args Numeric array of arguments from the originating hook.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 */
		do_action( 'llms_rest_webhook_delivery', $http_args, $res, $duration, $args, $this );

	}

	/**
	 * Fires after delivery.
	 *
	 * Logs data when loggind enabled and updates state data.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $delivery_id Webhook delivery id (for logging).
	 * @param array  $req_args HTTP Request Arguments used to deliver the webhook.
	 * @param array  $res Results from `wp_safe_remote_request()`.
	 * @param float  $duration Time (in microseconds) it took to generate and deliver the webhook.
	 * @return void
	 */
	protected function delivery_after( $delivery_id, $req_args, $res, $duration ) {

		// Parse response.
		if ( is_wp_error( $res ) ) {
			$res_code    = $res->get_error_code();
			$res_message = $res->get_error_message();
			$res_headers = array();
			$res_body    = '';
		} else {
			$res_code    = wp_remote_retrieve_response_code( $res );
			$res_message = wp_remote_retrieve_response_message( $res );
			$res_headers = wp_remote_retrieve_headers( $res );
			$res_body    = wp_remote_retrieve_body( $res );
		}

		if ( defined( 'LLMS_REST_WEBHOOK_DELIVERY_LOGGING' ) && LLMS_REST_WEBHOOK_DELIVERY_LOGGING ) {

			$message = array(
				'Delivery ID' => $delivery_id,
				'Date'        => date_i18n( __( 'M j, Y @ H:i', 'woocommerce' ), strtotime( 'now' ), true ),
				'URL'         => $this->get( 'delivery_url' ),
				'Duration'    => $duration,
				'Request'     => array(
					'Method'  => $req_args['method'],
					'Headers' => array_merge(
						array(
							'User-Agent' => $req_args['user-agent'],
						),
						$req_args['headers']
					),
				),
				'Body'        => wp_slash( $req_args['body'] ),
				'Response'    => array(
					'Code'    => $res_code,
					'Message' => $res_message,
					'Headers' => $res_headers,
					'Body'    => $res_body,
				),
			);

			if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
				$message['Webhook Delivery']['Body']             = 'Webhook body is not logged unless WP_DEBUG mode is turned on.';
				$message['Webhook Delivery']['Response']['Body'] = 'Webhook body is not logged unless WP_DEBUG mode is turned on.';
			}

			llms_log( $message, sprintf( 'webhook-%d', $this->get( 'id' ) ) );

		}

		// Check for a success, which is a 2xx, 301 or 302 Response Code.
		if ( absint( $res_code ) >= 200 && absint( $res_code ) <= 302 ) {
			$this->set( 'failure_count', 0 );
		} else {
			$this->set_delivery_failure();
		}

		$this->set( 'pending_delivery', 0 )->save();

	}

	/**
	 * Add actions for all the webhooks hooks.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function enqueue() {

		foreach ( $this->get_hooks() as $hook => $args ) {
			add_action( $hook, array( $this, 'process_hook' ), 10, $args );
		}

	}

	/**
	 * Determine if the webhook is currently pending delivery.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return bool
	 */
	public function is_pending() {

		return llms_parse_bool( $this->get( 'pending_delivery' ) );

	}

	/**
	 * Determine if the current action is valid for the webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return bool
	 */
	protected function is_valid_action( $args ) {

		$ret = true;
		switch ( current_action() ) {

			case 'wp_trash_post':
			case 'delete_post':
			case 'untrashed_post':
				$ret = $this->is_valid_post_action( $args[0] );
				break;

			case 'user_register':
			case 'profile_update':
			case 'delete_user':
				$ret = $this->is_valid_user_action( $args[0] );
				break;

		}

		/**
		 * Determine if the current action is valid for the webhook.
		 *
		 * @param bool $ret Whether or not the action is valid.
		 * @param array $args Numeric array of arguments from the originating hook.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 */
		return apply_filters( 'llms_rest_webhook_is_valid_action', $ret, $args, $this );

	}

	/**
	 * Determine if the current post-related action is valid for the webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $post_id WP Post ID.
	 * @return bool
	 */
	protected function is_valid_post_action( $post_id ) {

		$post_type = get_post_type( $post_id );

		// Check the post type is a supported post type.
		if ( ! in_array( get_post_type( $post_id ), LLMS_REST_API()->webhooks()->get_post_type_resources(), true ) ) {
			return false;
		}

		// Ensure the current action matches the resource for the current webhook.
		if ( str_replace( 'llms_', '', $post_type ) !== $this->get_resource() ) {
			return false;
		}

		return true;

	}

	/**
	 * Determine if the the resource is valid for the webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return bool
	 */
	protected function is_valid_resource( $args ) {

		if ( in_array( $this->get_resource(), LLMS_REST_API()->webhooks()->get_post_type_resources(), true ) ) {

			// Ignore auto-drafts.
			if ( in_array( get_post_status( absint( $args[0] ) ), array( 'new', 'auto-draft' ), true ) ) {
				return false;
			}

			// Evaluate the 3rd arg of `save_post` hooks to only trigger the hook during the appropriate events.
			if ( false !== strpos( current_action(), 'save_post' ) ) {
				$event = $this->get_event();

				if ( 'created' === $event && ( isset( $args[2] ) && true === $args[2] ) ) {
					// If 3rd arg is "true" the hook is triggerd because of an update, don't trigger the hook.
					return false;
				}
			}
		}

		return true;

	}

	/**
	 * Determine if the current user-related action is valid for the webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $user_id WP User ID.
	 * @return bool
	 */
	protected function is_valid_user_action( $user_id ) {

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$resource = $this->get_resource();
		if ( 'student' === $resource && ! in_array( 'student', (array) $user->roles, true ) ) {
			return false;
		} elseif ( 'instructor' === $resource && ! user_can( $user_id, 'lifterlms_instructor' ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Processes information from the origination action hook.
	 *
	 * Determines if the webhook should be delivered and whether or not it should be scheduled or delivered immediately.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param mixed ...$args Aguments from the hook.
	 * @return int|false Timestamp of the scheduled event when the webhook is successfully scheduled.
	 *                   false if the webhook should not be delivered or has already been delivered in the last 5 minutes.
	 */
	public function process_hook( ...$args ) {

		if ( ! $this->should_deliver( $args ) ) {
			return false;
		}

		/**
		 * Disable background processing of webhooks by returning a falsy.
		 *
		 * Note: disabling async processing may create delays for users of your site.
		 *
		 * @param bool $async Whether async processing is enabled or not.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 * @param array $args Numeric array of arguments from the originating hook.
		 */
		if ( apply_filters( 'llms_rest_webhook_deliver_async', true, $this, $args ) ) {
			return $this->schedule( $args );
		}

		$this->set( 'pending_delivery', 1 )->save();
		return $this->deliver( $args );

	}

	/**
	 * Perform a test ping to the delivery url.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return true|WP_Error
	 */
	public function ping() {

		$pre = apply_filters( 'llms_rest_webhook_pre_ping', false, $this->get( 'id' ) );
		if ( false !== $pre ) {
			return $pre;
		}

		$ping = wp_safe_remote_post(
			$this->get( 'delivery_url' ),
			array(
				'user-agent' => $this->get_user_agent(),
				'body'       => sprintf( 'webhook_id=%d', $this->get( 'id' ) ),
			)
		);

		$res_code = wp_remote_retrieve_response_code( $ping );

		if ( is_wp_error( $ping ) ) {
			// Translators: %s = Error message.
			return new WP_Error( 'llms_rest_webhook_ping_unreachable', sprintf( __( 'Could not reach the delivery url: "%s".', 'lifterlms' ), $ping->get_error_message() ) );
		}

		if ( 200 !== $res_code ) {
			// Translators: %d = Response code.
			return new WP_Error( 'llms_rest_webhook_ping_not_200', sprintf( __( 'The delivery url returned the response code "%d".', 'lifterlms' ), absint( $res_code ) ) );
		}

		return true;

	}

	/**
	 * Determines if an originating action qualifies for webhook delivery.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return bool
	 */
	protected function should_deliver( $args ) {

		$deliver = ( 'active' === $this->get( 'status' ) ) // Must be active.
			&& ! $this->is_pending() // Not already pending.
			&& $this->is_valid_action( $args ) // Valid action.
			&& $this->is_valid_resource( $args ); // Valid resource.

		/**
		 * Skip or hijack webhook delivery scheduling
		 *
		 * @param bool $deliver Whether or not to deliver webhook delivery.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 * @param array $args Numeric array of arguments from the originating hook.
		 */
		return apply_filters( 'llms_rest_webhook_should_deliver', $deliver, $this, $args );

	}

	/**
	 * Schedule the webhook for async delivery.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Numeric array of arguments from the originating hook.
	 * @return bool
	 */
	protected function schedule( $args ) {

		// Remove object & array arguments before scheduling to avoid hitting column index size issues imposed by the ActionScheduler lib.
		foreach ( $args as $index => &$arg ) {
			if ( is_array( $arg ) || is_object( $arg ) ) {
				$arg = null;
			}
		}

		$schedule_args = array(
			'webhook_id' => $this->get( 'id' ),
			'args'       => $args,
		);

		$next = as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' );

		/**
		 * Determines the time period required to wait between delivery of the webhook.
		 *
		 * If the webhook has already been scheduled within this time period it will not be sent again
		 * until the period expires. For example, the default time period is 300 seconds (5 minutes).
		 * If the webhook is triggered at 12:00pm it will be scheduled. If it is triggered again at 12:03pm the
		 * second occurrence will not be scheduled. If it is triggerd again at 12:06pm this third occurrence will
		 * again be scheduled.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param int $delay Time (in seconds).
		 * @param array $args Numeric array of arguments from the originating hook.
		 * @param LLMS_REST_Webhook $this Webhook object.
		 */
		$delay = apply_filters( 'llms_rest_webhook_repeat_delay', 300, $args, $this );

		if ( ! $next || $next >= ( $delay + gmdate( 'U' ) ) ) {

			$this->set( 'pending_delivery', 1 )->save();
			return as_schedule_single_action( time(), 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ) ? true : false;

		}

		return false;

	}

}
