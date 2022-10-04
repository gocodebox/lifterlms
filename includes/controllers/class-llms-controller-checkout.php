<?php
/**
 * LLMS_Controller_Checkout
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 7.0.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout form controller.
 *
 * Processes orders and interacts with payment gateway classes during checkout.
 *
 * @since 7.0.0
 */
class LLMS_Controller_Checkout {

	use LLMS_Trait_Singleton;

	/**
	 * Action for creating a pending order.
	 *
	 * Used as both the nonce and the posted `action` field.
	 */
	public const ACTION_CREATE_PENDING_ORDER = 'create_pending_order';

	/**
	 * Action for confirming a pending order.
	 *
	 * Used as both the nonce and the posted `action` field.
	 */
	public const ACTION_CONFIRM_PENDING_ORDER = 'confirm_pending_order';

	/**
	 * Action for switching the payment source for an order.
	 *
	 * Used as the nonce action.
	 */
	public const ACTION_SWITCH_PAYMENT_SOURCE = 'llms_switch_order_source';

	/**
	 * Query string variable used to identify AJAX order requests.
	 */
	public const AJAX_QS_VAR = 'llms-checkout';

	/**
	 * Constructor.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	private function __construct() {

		$actions = array(
			'create_pending_order',
			'confirm_pending_order',
			'switch_payment_source',
		);
		foreach ( $actions as $action ) {
			add_action( 'init', array( $this, "{$action}_ajax" ), 5 );
			add_action( 'init', array( $this, $action ) );
		}

	}

	/**
	 * Checkout confirm order controller.
	 *
	 * Called via the confirm order form (via user form submission) or programmatically by payment gateways which
	 * require an order confirmation step. PayPal is a two-step checkout that requires confirmation
	 * whereas Stripe is a one-step checkout without a confirmation step.
	 *
	 * Validates all submitted data and passes the validated `LLMS_Order` object to the payment gateway's
	 * `confirm_pending_order()` method for further processing.
	 *
	 * If an error is encountered the method short circuits and adds an error notice via {@see llms_add_notice()},
	 * during gateway processing the same pattern should be observed.
	 *
	 * Upon success, gateways should perform a redirect to the appropriate URL (course, membership, etc...).
	 *
	 * Note that this method is widely used but the AJAX equivalent, {@see LLMS_Controller_Checkout::confirm_pending_order_ajax()},
	 * is preferred when implementing a new gateway.
	 *
	 * @since 7.0.0 Relocated from `LLMS_Controller_Orders`.
	 *
	 * @return null|boolean|void Returns `null` when the form isn't submitted or there's a nonce verification issue.
	 *                           Returns `false` when the the request is missing the action parameter or the action doesn't match
	 *                           the expected action. Otherwise there is no/void return.
	 */
	public function confirm_pending_order() {

		// Verify form submission.
		$verify = $this->verify_request( '_wpnonce', self::ACTION_CONFIRM_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		// Ensure we have an order key we can locate the order with.
		$key = llms_filter_input_sanitize_string( INPUT_POST, 'llms_order_key' );
		if ( ! $key ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		// Lookup the order & return error if not found.
		$order = llms_get_order_by_key( $key );
		if ( ! $order || ! $order instanceof LLMS_Order ) {
			return llms_add_notice( __( 'Could not locate an order to confirm.', 'lifterlms' ), 'error' );
		}

		// Can the order be confirmed?
		if ( ! $order->can_be_confirmed() ) {
			return llms_add_notice( __( 'Only pending orders can be confirmed.', 'lifterlms' ), 'error' );
		}

		// Get the gateway.
		$gateway = llms()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

		// Pass the order to the gateway.
		$gateway->confirm_pending_order( $order );

	}

	/**
	 * AJAX checkout confirm order controller.
	 *
	 * Verifies the AJAX request, passes `$_POST` data to the `LLMS_Order_Generator`, and outputs a JSON response.
	 *
	 * Initiated via the confirm order form (via user form submission) or programmatically by payment gateways which
	 * require an order confirmation step. PayPal is a two-step checkout that requires confirmation
	 * whereas Stripe is a one-step checkout without a confirmation step.
	 *
	 * @since 7.0.0
	 *
	 * @return null|boolean|void Returns `null` when the form isn't submitted or there's a nonce verification issue.
	 *                           Returns `false` when the the request is missing the action parameter or the action doesn't match
	 *                           the expected action. Otherwise there is no return and a JSON response is output.
	 */
	public function confirm_pending_order_ajax() {

		$verify = $this->verify_request( self::AJAX_QS_VAR, self::ACTION_CONFIRM_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		$this->start_ajax( 'confirm_pending_order' );

		// Confirm the order.
		$generator = new LLMS_Order_Generator( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.
		$this->send_json( $generator->confirm() );

	}

	/**
	 * Checkout new order controller.
	 *
	 * Handles form submission of the checkout form for new (or pending) orders.
	 *
	 * Verifies the request, validates request data, creates/updates the user, and creates/updates
	 * the order post.
	 *
	 * If the order is created successfully the order, access plan, student, and coupon data
	 * is passed to the payment gateway's `handle_pending_order()` method for further processing.
	 *
	 * If errors are encountered they are displayed to the user via {@see llms_add_notice()} and execution
	 * of the method is halted early. Gateways should do the same if they encounter errors during processing.
	 *
	 * This method also handles free enrollment form submission from the access plan button (on pricing tables, etc...).
	 * In the event of validation issues during free enrollment form submission the user is automatically redirect to checkout
	 * where the validation issues will be displayed.
	 *
	 * Upon success the gateway should redirect the user to the relevant next step. For multi-step checkout that
	 * requires payment confirmation, the user should be redirected to the order confirmation page, for one-step
	 * gateways assuming the order is moved to active or completed status and enrollment takes place, the user
	 * should be redirected to the relevant course or membership URL.
	 *
	 * @since 7.0.0 Moved from `LLMS_Controller_Orders.
	 *
	 * @return null|boolean|void Returns `null` when the form isn't submitted or there's a nonce verification issue.
	 *                           Returns `false` when the the request is missing the action parameter or the action doesn't match
	 *                           the expected action. Otherwise there is no/void return.
	 */
	public function create_pending_order() {

		$verify = $this->verify_request( '_llms_checkout_nonce', self::ACTION_CREATE_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		/**
		 * Allow 3rd parties to perform their own validation prior to standard validation.
		 *
		 * If this returns a truthy, we'll stop processing.
		 *
		 * The extension should add a notice in addition to returning the truthy.
		 *
		 * @since Unknown
		 *
		 * @param boolean $valid Validation status. If `true` ceases checkout execution. If `false` checkout proceeds.
		 */
		if ( apply_filters( 'llms_before_checkout_validation', false ) ) {
			return false;
		}

		$setup_data = $this->extract_setup_data( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.

		// Setup the pending order.
		$setup = llms_setup_pending_order( $setup_data );
		if ( is_wp_error( $setup ) ) {

			llms_add_notice( $setup->get_error_message(), 'error' );

			/*
			 * If the free enroll form is being submitted and there were validation issues this will redirect
			 * to the checkout page in favor of returning an error.
			 */
			$this->maybe_redirect_from_free_enroll_form( $setup_data['plan_id'], llms_filter_input( INPUT_POST, 'form' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.

			return $setup;

		}

		/**
		 * Allow gateways, extensions, etc to do their own validation.
		 *
		 * After all standard validations are successfully.
		 *
		 * If this returns a truthy, we'll stop processing.
		 * The extension should add a notice in addition to returning the truthy.
		 *
		 * @since Unknown
		 *
		 * @param boolean $stop_processing When a `true`, we'll stop processing. Default is `false`.
		 */
		if ( apply_filters( 'llms_after_checkout_validation', false ) ) {
			return false;
		}

		$order_id = 'new';

		// Get order ID by Key if it exists.
		if ( ! empty( $_POST['llms_order_key'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.
			$locate = llms_get_order_by_key( llms_filter_input_sanitize_string( INPUT_POST, 'llms_order_key' ), 'id' );
			if ( $locate ) {
				$order_id = $locate;
			}
		}

		// Instantiate the order.
		$order = new LLMS_Order( $order_id );

		// If there's no id we can't proceed, return an error.
		if ( ! $order->get( 'id' ) ) {
			return llms_add_notice( __( 'There was an error creating your order, please try again.', 'lifterlms' ), 'error' );
		}

		// Add order key to globals so the order can be retried if processing errors occur.
		$_POST['llms_order_key'] = $order->get( 'order_key' );

		$order->init( $setup['person'], $setup['plan'], $setup['gateway'], $setup['coupon'] );

		// Pass to the gateway to start processing.
		$setup['gateway']->handle_pending_order( $order, $setup['plan'], $setup['person'], $setup['coupon'] );

	}

	/**
	 * AJAX checkout new order controller.
	 *
	 * Handles AJAX form submission of the checkout form for new (or pending) orders.
	 *
	 * Verifies the AJAX request, passes the `$_POST` data to {@see LLMS_Order_Generator::generate},
	 * hands the resulting order and data to the gateway's `handle_pending_order()` method and then
	 * outputs a JSON response object.
	 *
	 * @since 7.0.0
	 *
	 * @return null|boolean|void Returns `null` when the form isn't submitted or there's a nonce verification issue.
	 *                           Returns `false` when the the request is missing the action parameter or the action doesn't match
	 *                           the expected action. Otherwise there is no return and a JSON response is output.
	 */
	public function create_pending_order_ajax() {

		$verify = $this->verify_request( self::AJAX_QS_VAR, self::ACTION_CREATE_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		$this->start_ajax( 'create_pending_order' );

		// Generate the order.
		$generator = new LLMS_Order_Generator( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.
		$order     = $generator->generate( $generator::UA_VALIDATE );
		if ( is_wp_error( $order ) ) {
			$this->send_json( $order );
		}

		// Pending order creation success, pass it over to the gateway.
		$handle = $generator->get_gateway()->handle_pending_order(
			$order,
			$generator->get_plan(),
			$generator->get_user_data(),
			$generator->get_coupon()
		);

		// Automatically add the order key to non-error return arrays.
		if ( ! is_wp_error( $handle ) ) {
			$handle['order_key'] = $order->get( 'order_key' );
		}

		$this->send_json( $handle );

	}

	/**
	 * Extracts data from `$_POST` into an array that can be passed into `llms_setup_pending_order()`.
	 *
	 * @since 7.0.0
	 *
	 * @param array $posted_data Data array, from `$_POST`.
	 * @return array
	 */
	private function extract_setup_data( $posted_data ) {

		$plan_id = absint( $posted_data['llms_plan_id'] ?? 0 );

		$data = array(
			'plan_id'         => $plan_id,
			'agree_to_terms'  => llms_parse_bool( $posted_data['llms_agree_to_terms'] ?? '' ),
			'coupon_code'     => sanitize_text_field( $posted_data['llms_coupon_code'] ?? '' ),
			'customer'        => $this->extract_user_data( $posted_data, $plan_id ),
			'payment_gateway' => sanitize_text_field( $posted_data['llms_payment_gateway'] ?? '' ),
		);

		return $data;

	}

	/**
	 * Extracts user registration / update information from a posted data array.
	 *
	 * @since 7.0.0
	 *
	 * @param array $posted_data Raw $_POST (or similar) data.
	 * @return array
	 */
	private function extract_user_data( $posted_data, $plan_id ) {

		$user_data = array();
		$plan      = $plan_id ? llms_get_post( $plan_id ) : false;

		$uid = get_current_user_id();
		if ( $uid ) {
			$user_data['user_id'] = $uid;
		}

		foreach ( LLMS_Forms::instance()->get_form_fields( 'checkout', compact( 'plan' ) ) as $field ) {
			if ( isset( $posted_data[ $field['name'] ] ) ) {
				$user_data[ $field['name'] ] = $posted_data[ $field['name'] ];
			}
		}

		return $user_data;

	}

	/**
	 * Retrieves the AJAX URL for the requested action.
	 *
	 * @since 7.0.0
	 *
	 * @param string $action A checkout action. Expects a class action constant: `LLMS_Controller_Checkout::ACTION_*`.
	 * @return string
	 */
	public function get_url( $action ) {
		return add_query_arg(
			self::AJAX_QS_VAR,
			wp_create_nonce( $action ),
			get_site_url()
		);
	}

	/**
	 * Handles redirection during {@see LLMS_Controller_Checkout::create_pending_order()} if validation errors are encountered
	 * via the free checkout/enrollment form.
	 *
	 * @since 7.0.0
	 *
	 * @param int    $plan_id WP_Post ID of the access plan.
	 * @param string $form    Value of the posted `form`, should be `free_enroll`.
	 * @return null|bool|void Returns `null` when called in an invalid context, `false` if the supplied access plan ID is invalid,
	 *                        and `void` when a redirect is performed to the checkout page.
	 */
	private function maybe_redirect_from_free_enroll_form( $plan_id, $form ) {

		// Not the free enroll form.
		if ( ! get_current_user_id() || 'free_enroll' !== $form || ! $plan_id ) {
			return null;
		}

		// Invalid plan submitted.
		$plan = llms_get_post( $plan_id );
		if ( ! is_a( $plan, 'LLMS_Access_Plan' ) ) {
			return false;
		}

		// Redirect to the checkout screen.
		llms_redirect_and_exit( $plan->get_checkout_url() );

	}

	/**
	 * Sends a JSON response.
	 *
	 * @since 7.0.0
	 *
	 * @param array|WP_Error $data Response data.
	 * @return void
	 */
	private function send_json( $data ) {
		wp_send_json( $data, is_wp_error( $data ) ? 400 : 200 );
	}

	/**
	 * Denotes an AJAX request in this method has started.
	 *
	 * This method "alerts" WordPress that an AJAX request is being processed. This is important, primarily, for
	 * testing purposes as `wp_send_json()` will call `wp_die()` when doing ajax as opposed to `die()` when not
	 * doing ajax. This helps us unit test better.
	 *
	 * Secondly, this will remove the non-ajax action callback of the method's name ensuring that the non-ajax version doesn't
	 * run immediately behind the ajax version.
	 *
	 * @since 7.0.0
	 *
	 * @param string $method Name of the non-ajax method to remove.
	 * @return void
	 */
	private function start_ajax( $method ) {

		// Tell WP we're doing AJAX.
		add_filter( 'wp_doing_ajax', '__return_true' );

		// Don't process the non-ajax method.
		remove_action( 'init', array( $this, $method ) );

	}

	/**
	 * Handle form submission of the "Update Payment Method" form on the student dashboard when viewing a single order.
	 *
	 * @since 7.0.0 Relocated from `LLMS_Controller_Orders`.
	 *
	 * @return void
	 */
	public function switch_payment_source() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_switch_source_nonce', self::ACTION_SWITCH_PAYMENT_SOURCE ) ) {
			return;
		}

		$data = $this->switch_payment_source_setup();
		if ( is_wp_error( $data ) ) {
			return llms_add_notice( $data->get_error_message(), 'error' );
		}

		// Handoff to the gateway.
		llms()->payment_gateways()->get_gateway_by_id( $data['new_gateway'] )->handle_payment_source_switch( $data['order'], $_POST );

		if ( ! llms_notice_count( 'error' ) ) {
			$this->switch_payment_source_success( $data );
		}

	}

	/**
	 * Handle ajax payment method switching from the student dashboard.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function switch_payment_source_ajax() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( self::AJAX_QS_VAR, self::ACTION_SWITCH_PAYMENT_SOURCE ) ) {
			return null;
		}

		$this->start_ajax( 'switch_payment_source' );

		$data = $this->switch_payment_source_setup();
		if ( is_wp_error( $data ) ) {
			$this->send_json( $data );
		}

		// Handoff to the gateway.
		$gateway_res = llms()->payment_gateways()->get_gateway_by_id( $data['new_gateway'] )->handle_payment_source_switch( $data['order'], $_POST );

		$next_action = is_wp_error( $gateway_res ) ?
			false :
			/**
			 * Filters the next action when switching payment sources.
			 *
			 * Defaults to `COMPLETE` when gateways don't return a value via `next_action`
			 * in the response array.
			 *
			 * The `COMPLETE` action records the switch, updates the payment method, and changes
			 * `pending-cancel` to `active` status.
			 *
			 * Any other status will do nothing and the gateway should provide it's necessary logic in the
			 * {@see LLMS_Payment_Gateway::handle_payment_source_switch()} method.
			 *
			 * This is used by gateways such as PayPal that require a creation and approval step on PayPal as opposed
			 * to a gateway like Stripe that doesn't require end-user approval on the Stripe platform.
			 *
			 * @since 7.0.0
			 *
			 * @param type $arg Description.
			 */
			apply_filters(
				'llms_switch_payment_source_next_action',
				$gateway_res['next_action'] ?? 'COMPLETE',
				$gateway_res,
				$data
			);

		if ( 'COMPLETE' === $next_action ) {
			$this->switch_payment_source_success( $data, true );
		}

		$this->send_json( $gateway_res );

	}

	/**
	 * Validates and parses user-submitted `$_POST` data during payment source switching.
	 *
	 * @since 7.0.0
	 *
	 * @return WP_Error|array {
	 *     An error object or an associative array on success.
	 *
	 *     @type string     $old_gateway The ID of the order's previous payment gateway.
	 *     @type string     $new_gateway The ID of the order's new payment gateway.
	 *     @type LLMS_Order $order       The order object.
	 * }
	 */
	private function switch_payment_source_setup() {

		$order_id = llms_filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $order_id ) {
			return new WP_Error( 'switch-source-order-missing', __( 'Missing order information.', 'lifterlms' ), 'error' );
		}

		$order = llms_get_post( $order_id );
		if ( ! is_a( $order, 'LLMS_Order' ) || get_current_user_id() !== $order->get( 'user_id' ) ) {
			return new WP_Error( 'switch-source-order-invalid', __( 'Invalid order.', 'lifterlms' ), 'error' );
		}

		$new_gateway = llms_filter_input_sanitize_string( INPUT_POST, 'llms_payment_gateway' );
		if ( empty( $new_gateway ) ) {
			return new WP_Error( 'switch-source-gateway-missing', __( 'Missing gateway information.', 'lifterlms' ), 'error' );
		}

		$old_gateway = $order->get( 'payment_gateway' );
		$can_process = llms_can_gateway_be_used_for_plan_or_order( $new_gateway, $order, true );
		if ( is_wp_error( $can_process ) ) {
			return $can_process;
		}

		// Prevent tampering with the form action and ensure the submitted action matches the expected action for the order.
		$action = llms_filter_input( INPUT_POST, 'llms_switch_action' );
		if ( empty( $action ) || $order->get_switch_source_action() !== $action ) {
			return new WP_Error( 'switch-source-action-invalid', __( 'Invalid action.', 'lifterlms' ), 'error' );
		}

		// Temporarily store the gateway IDs so the previous values are accessible to the old gateway after the source switch.
		$order->set(
			'temp_gateway_ids',
			/**
			 * Filters the gateway IDs that are temporarily stored during a payment source switch.
			 *
			 * @since 7.0.0
			 *
			 * @param array      $temp_ids {
			 *     An array of gateway-related IDs to be temporarily cached.
			 *
			 *     @type string customer     The value of the `gateway_customer_id` property.
			 *     @type string source       The value of the `gateway_source_id` property.
			 *     @type string subscription The value of the `gateway_subscription_id` property.
			 * }
			 * @param LLMS_Order $order     The order object.
			 */
			apply_filters(
				'llms_order_set_temp_gateway_ids',
				array(
					'customer'     => $order->get( 'gateway_customer_id' ),
					'source'       => $order->get( 'gateway_source_id' ),
					'subscription' => $order->get( 'gateway_subscription_id' ),
				),
				$order
			)
		);

		return compact( 'old_gateway', 'new_gateway', 'order' );

	}

	/**
	 * Action run following a successful payment source switch.
	 *
	 * @since 7.0.0
	 *
	 * @param array $args Payment switch arguments from {@see LLMS_Controller_Checkout::switch_payment_source_setup()}.
	 * @param bool  $note If `true`, automatically records an order note for the source the switch.
	 * @return void
	 */
	private function switch_payment_source_success( $args, $note = false ) {

		$order       = $args['order'];
		$old_gateway = $args['old_gateway'];
		$new_gateway = $args['new_gateway'];

		$order->set( 'payment_gateway', $new_gateway );

		if ( $note ) {
			$order->add_note(
				sprintf(
					// Translators: %1$s = old payment gateway ID; %2$s = new payment gateway ID.
					__( 'Payment source updated by customer. Payment gateway changed from "%1$s" to "%2$s".', 'lifterlms' ),
					$old_gateway,
					$new_gateway
				)
			);
		}

		// If the order is pending-cancel, reactivate it.
		if ( 'llms-pending-cancel' === $order->get( 'status' ) ) {
			$order->set_status( 'active' );
		}

		/**
		 * Action run after an order's payment source is switched.
		 *
		 * @since 7.0.0
		 *
		 * @param LLMS_Order $order       Order object.
		 * @param string     $new_gateway The payment gateway ID of the new gateway.
		 * @param string     $old_gateway The payment gateway ID of the previous gateway.
		 */
		do_action( 'llms_order_payment_source_switched', $order, $new_gateway, $old_gateway );

		// Cleanup temp data.
		delete_post_meta( $order->get( 'id' ), '_llms_temp_gateway_ids' );

	}

	/**
	 * Verifies an incoming request nonce and posted action field.
	 *
	 * @since 7.0.0
	 *
	 * @param string $field The nonce field.
	 * @param string $nonce The nonce & action value.
	 * @return null|bool Returns `null` if the nonce isn't submitted or can't be verified, `false` if the
	 *                   action isn't submitted or doesn't match the intended action, and `true` if
	 *                   the request is verified successfully.
	 */
	private function verify_request( $field, $nonce ) {

		if ( ! llms_verify_nonce( $field, $nonce, 'POST' ) ) {
			return null;
		}

		if ( llms_filter_input( INPUT_POST, 'action' ) !== $nonce ) {
			return false;
		}

		return true;

	}

}

return LLMS_Controller_Checkout::instance();
