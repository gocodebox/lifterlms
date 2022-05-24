<?php
/**
 * LLMS_Controller_Checkout
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout form controller.
 *
 * Processes orders and interacts with payment gateway classes during checkout.
 *
 * @since [version]
 */
class LLMS_Controller_Checkout {

	use LLMS_Trait_Singleton;

	/**
	 * Action for creating a pending order.
	 *
	 * Used as both the nonce and the posted `action` field.
	 */
	const ACTION_CREATE_PENDING_ORDER = 'create_pending_order';

	/**
	 * Action for confirming a pending order.
	 *
	 * Used as both the nonce and the posted `action` field.
	 */
	const ACTION_CONFIRM_PENDING_ORDER = 'confirm_pending_order';

	/**
	 * Query string variable used to identify AJAX order requests.
	 */
	const AJAX_QS_VAR = 'llms-checkout';

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		// Create.
		add_action( 'init', array( $this, 'create_pending_order_ajax' ), 5 );
		add_action( 'init', array( $this, 'create_pending_order' ) );

		// Confirm.
		add_action( 'init', array( $this, 'confirm_pending_order_ajax' ), 5 );
		add_action( 'init', array( $this, 'confirm_pending_order' ) );

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
	 * Note that this method is widely used by the AJAX equivalent, {@see LLMS_Controller_Checkout::confirm_pending_order_ajax()},
	 * is preferred when implementing a new gateway.
	 *
	 * @since [version] Relocated from `LLMS_Controller_Orders`.
	 *
	 * @return void
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
	 * Verifies the AJAX request, passes `$_POST` data to the `LLMS_Order_Generator`, and returns
	 * a JSON response.
	 *
	 * Initiated via the confirm order form (via user form submission) or programmatically by payment gateways which
	 * require an order confirmation step. PayPal is a two-step checkout that requires confirmation
	 * whereas Stripe is a one-step checkout without a confirmation step.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function confirm_pending_order_ajax() {

		$verify = $this->verify_request( self::AJAX_QS_VAR, self::ACTION_CONFIRM_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		// Ensure the non-ajax handler doesn't also trigger.
		remove_action( 'init', array( $this, 'confirm_pending_order' ) );

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
	 *
	 * If the order is created successfully the order, access plan, student, and coupon data
	 * is passed to the payment gateway's `handle_pending_order()` method for further processing.
	 *
	 * If errors are encountered they are displayed to the user via {@see llms_add_notice()} and execution
	 * of the method is halted early. Gateways should do the same if they encounter errors during processing.
	 *
	 * Upon success the gateway should redirect the user to the relevant next step. For multi-step checkout that
	 * requires payment confirmation, the user should be redirected to the order confirmation page, for one-step
	 * gateways assuming the order is moved to active or completed status and enrollment takes place, the user
	 * should be redirected to the relevant course or membership URL.
	 *
	 * @since [version] Moved from `LLMS_Controller_Orders.
	 *
	 * @return null|boolean|WP_Error
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

		// Setup the pending order.
		$setup = llms_setup_pending_order( $this->extract_setup_data( $_POST ) );  // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified via `verify_request()`.
		if ( is_wp_error( $setup ) ) {
			llms_add_notice( $setup->get_error_message(), 'error' );
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function create_pending_order_ajax() {

		$verify = $this->verify_request( self::AJAX_QS_VAR, self::ACTION_CREATE_PENDING_ORDER );
		if ( ! $verify ) {
			return $verify;
		}

		// Ensure the non-ajax handler doesn't also trigger.
		remove_action( 'init', array( $this, 'create_pending_order' ) );

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
	 * @since [version]
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
	 * @since [version]
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
	 * Retrieves the AJAX url for the requested action.
	 *
	 * @since [version]
	 *
	 * @param string $action A checkout action. Accepts class constant `ACTION_CREATE_PENDING_ORDER` or `ACTION_CONFIRM_PENDING_ORDER`.
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
	 * Sends a JSON response.
	 *
	 * @since [version]
	 *
	 * @param array|WP_Error $data Response data.
	 * @return void
	 */
	private function send_json( $data ) {

		// Tell WP we're doing AJAX.
		add_filter( 'wp_doing_ajax', '__return_true' );
		wp_send_json( $data, is_wp_error( $data ) ? 400 : 200 );

	}

	/**
	 * Verifies an incoming request nonce and posted action field.
	 *
	 * @since [version]
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
