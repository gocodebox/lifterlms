<?php
/**
 * LLMS_Transaction model class file
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.0.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS order transactions
 *
 * @since 3.0.0
 *
 * @property string $api_mode                   API Mode of the gateway when the transaction was made [test|live].
 * @property float  $amount                     Transaction charge amount.
 * @property string $currency                   Transaction's currency code.
 * @property string $gateway_completed_date     Datetime string when the transaction was completed by the gateway (if gateway supports).
 * @property string $gateway_customer_id        Gateway's unique ID for the customer who placed the order.
 * @property float  $gateway_fee_amount         Fee charged to the user by the gateway for the transaction (if gateway supports).
 * @property string $gateway_source_id          Source Identifier from the gateway -- eg: credit card id or account id.
 * @property string $gateway_source_description Short description of the source from the gateway. EG: Visa 1234.
 * @property string $gateway_transaction_id     Gateway's unique ID for the transaction.
 * @property int    $order_id                   ID of the related LLMS_Order.
 * @property string $payment_type               Type of payment. [recurring|single|trial].
 * @property string $payment_gateway            LifterLMS Payment Gateway ID (eg "paypal" or "stripe").
 * @property float  $refund_amount              Amount refunded, will always be 0 until a refund is actually recorded.
 * @property array  $refund_data                Array of arrays. Contains refund data for each refund recorded for this transaction.
 */
class LLMS_Transaction extends LLMS_Post_Model {

	/**
	 * DB Post Type.
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_transaction';

	/**
	 * Model Name/Type.
	 *
	 * @var string
	 */
	protected $model_post_type = 'transaction';

	/**
	 * Post model properties.
	 *
	 * @var array
	 */
	protected $properties = array(
		'api_mode'                   => 'text',
		'amount'                     => 'float',
		'currency'                   => 'text',
		'gateway_completed_date'     => 'text',
		'gateway_customer_id'        => 'text',
		'gateway_fee_amount'         => 'float',
		'gateway_source_id'          => 'text',
		'gateway_source_description' => 'text',
		'gateway_transaction_id'     => 'text',
		'order_id'                   => 'absint',
		'payment_type'               => 'text',
		'payment_gateway'            => 'text',
		'refund_amount'              => 'float',
		'refund_data'                => 'array',
	);

	/**
	 * Determines if the transaction can be refunded.
	 *
	 * Status must not be "failed" and total refunded amount must be less than order amount.
	 *
	 * @since 3.0.0
	 * @since 7.0.0 Made the return value filterable via the `llms_transaction_can_be_refunded` hook.
	 *
	 * @return boolean
	 */
	public function can_be_refunded() {

		$can_be_refunded = true;

		if ( in_array( $this->get( 'status' ), array( 'llms-txn-failed', 'llms-txn-pending' ), true ) ) {
			$can_be_refunded = false;
		} elseif ( $this->get_refundable_amount( array(), 'float' ) <= 0 ) {
			$can_be_refunded = false;
		}

		/**
		 * Filters whether or not a transaction can be refunded.
		 *
		 * @since 7.0.0
		 *
		 * @param boolean          $can_be_refunded Whether the transaction can be refunded.
		 * @param LLMS_Transaction $transaction     The transaction object.
		 */
		return apply_filters( 'llms_transaction_can_be_refunded', $can_be_refunded, $this );

	}

	/**
	 * Generates a refund ID based on the refund method.
	 *
	 * When manually processing a refund an ID is generated, if processing via a gateway the data
	 * is passed to the {@see LLMS_Transaction::process_refund_via_gateway()} and ultimately passed
	 * to the gateway for processing (via the gateway's API). For custom methods processing is handled
	 * via the `llms_{$method}_refund_id` filter.
	 *
	 * @since 7.0.0
	 *
	 * @param string $method Refund processing method ID.
	 * @param float  $amount The amount to refund.
	 * @param string $note   Refund notes.
	 * @return string|boolean|WP_Error Returns the generated refund ID string or an error object. If a falsy value
	 *                                 is returned the refund processing will fail with a generic error message.
	 */
	protected function generate_refund_id( $method, $amount, $note = '' ) {

		if ( 'manual' === $method ) {
			/**
			 * Filters the refund id for manual refunds.
			 *
			 * The default refund ID is a microtime string generated by `uniqid()`.
			 *
			 * @since 3.0.0
			 *
			 * @param string $refund_id The refund ID.
			 */
			$refund_id = apply_filters( 'llms_manual_refund_id', (string) uniqid() );
		} elseif ( 'gateway' === $method ) {
			$refund_id = $this->process_refund_via_gateway( $amount, $note );
		} else {
			/**
			 * Filters the refund ID for custom refund methods.
			 *
			 * This filter should return a string representing the refund ID as generated by the custom refund method.
			 *
			 * The dynamic portion of this hook, `{$method}`, represents the ID of the custom refund method.
			 *
			 * @since 3.0.0
			 *
			 * @param string|boolean|WP_Error $refund_id   The generated refund ID or an error object. Returning a falsy value
			 *                                             will result in the default error handling and no refund being recorded.
			 * @param string                  $method      The method ID.
			 * @param LLMS_Transaction        $transaction The transaction object.
			 * @param float                   $amount      The refund amount.
			 * @param string                  $note        The user-submitted refund note.
			 */
			$refund_id = apply_filters( "llms_{$method}_refund_id", false, $method, $this, $amount, $note );
		}

		return $refund_id;

	}

	/**
	 * Retrieves the amount of the transaction that can be refunded.
	 *
	 * @since 3.0.0
	 * @return float
	 */
	public function get_refundable_amount() {
		$amount   = $this->get_price( 'amount', array(), 'float' );
		$refunded = $this->get_price( 'refund_amount', array(), 'float' );
		return $amount - $refunded;
	}

	/**
	 * Retrieves the array of default arguments to pass to {@see LLMS_Transaction::create()} when creating a new post.
	 *
	 * @since 3.0.0
	 * @since 3.37.6 Add a default date information using `llms_current_time()`.
	 *               Remove ordering placeholders from strftime().
	 * @since 5.9.0 Remove usage of deprecated `strftime()`.
	 *
	 * @param int $order_id LLMS_Order ID of the related order.
	 * @return array
	 */
	protected function get_creation_args( $order_id = 0 ) {

		$date = llms_current_time( 'mysql' );

		$title = sprintf(
			// Translators: %1$d = Order ID; %2$s = Transaction creation date.
			__( 'Transaction for Order #%1$d &ndash; %2$s', 'lifterlms' ),
			$order_id,
			date_format( date_create( $date ), 'M d, Y @ h:i A' )
		);

		// This filter is documented in includes/abstracts/abstract.llms.post.model.php.
		return apply_filters(
			"llms_{$this->model_post_type}_get_creation_args",
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_author'    => 0,
				'post_content'   => '',
				'post_date'      => $date,
				'post_excerpt'   => '',
				'post_password'  => uniqid( 'order_' ),
				'post_status'    => 'llms-' . apply_filters( 'llms_default_order_status', 'txn-pending' ),
				'post_title'     => $title,
				'post_type'      => $this->get( 'db_post_type' ),
			),
			$this
		);
	}

	/**
	 * Get the total amount of the transaction after deducting refunds
	 *
	 * @param  array  $price_args  optional array of arguments that can be passed to llms_price()
	 * @param  string $format      optional format conversion method [html|raw|float]
	 * @return mixed
	 * @since  3.0.0
	 */
	public function get_net_amount( $price_args = array(), $format = 'html' ) {
		$amount = $this->get_price( 'amount', array(), 'float' );
		$refund = $this->get_price( 'refund_amount', array(), 'float' );
		return llms_price( $amount - $refund, $price_args, $format );
	}

	/**
	 * Retrieves an instance of LLMS_Order for the transaction's parent order.
	 *
	 * @since 3.0.0
	 *
	 * @return LLMS_Order
	 */
	public function get_order() {
		return new LLMS_Order( $this->get( 'order_id' ) );
	}

	/**
	 * Retrieves the payment gateway instance for the transactions payment gateway.
	 *
	 * @since 3.0.0
	 *
	 * @return LLMS_Payment_Gateway|WP_Error
	 */
	public function get_gateway() {

		$gateways = llms()->payment_gateways();
		$gateway  = $gateways->get_gateway_by_id( $this->get( 'payment_gateway' ) );
		if ( $gateway && $gateway->is_enabled() || is_admin() ) {
			return $gateway;
		}

		// Translators: %s = The payment gateway ID.
		return new WP_Error(
			'error',
			sprintf(
				__( 'Payment gateway %s could not be located or is no longer enabled', 'lifterlms' ),
				$this->get( 'payment_gateway' )
			)
		);

	}

	/**
	 * Retrieves the title of the refund method using during refund processing.
	 *
	 * This method records the method used to process a refund in the refund order note.
	 *
	 * @since 7.0.0
	 *
	 * @param string $method The refund method ID.
	 * @return string
	 */
	protected function get_refund_method_title( $method ) {

		switch ( $method ) {

			case 'manual':
				$method_title = __( 'manual refund', 'lifterlms' );
				break;

			case 'gateway':
				$method_title = $this->get_gateway()->get_admin_title();
				break;

			default:
				/**
				 * Filters the refund method title for custom refund methods.
				 *
				 * The dynamic portion of this hook, `{$method}`, represents the ID of the custom refund method.
				 *
				 * @since 3.0.0
				 * @deprecated 7.0.0 Replaced with `llms_{$method}_refund_title`.
				 *
				 * @param string $method The method ID.
				 */
				$method_title = apply_filters_deprecated( "llms_{$method}_title", array( $method ), '7.0.0', "llms_{$method}_refund_title" );
				if ( $method_title !== $method ) {
					return $method_title;
				}

				/**
				 * Filters the refund method title for custom refund methods.
				 *
				 * The dynamic portion of this hook, `{$method}`, represents the ID of the custom refund method.
				 *
				 * @since Unknown
				 *
				 * @param string           $method      The method ID.
				 * @param LLMS_Transaction $transaction The transaction object.
				 */
				$method_title = apply_filters( "llms_{$method}_refund_title", $method, $this );

		}

		return $method_title;

	}

	/**
	 * Retrieves a single refund by ID.
	 *
	 * @since 7.0.0
	 *
	 * @param string $id The refund ID.
	 * @return array|boolean {
	 *     An array of refund data. Returns `false` if the ID isn't found.
	 *
	 *     @type string id     The refund ID
	 *     @type string date   The date the refund was recorded in MySQL date format `Y-m-d H:i:s`.
	 *     @type string method The processing method ID. Defaults are "manual" or "gateway". Custom values can be implemented via hooks.
	 *     @type float  amount The amount of the refund.
	 * }
	 */
	public function get_refund( $id ) {
		$refunds = $this->get_refunds();
		return $refunds[ $id ] ?? false;
	}

	/**
	 * Retrieves a list of refunds against the transaction.
	 *
	 * @since 7.0.0
	 *
	 * @return array[] An array of refund arrays as described by {@see LLMS_Transaction::get_refund()}.
	 */
	public function get_refunds() {
		return $this->get_array( 'refund_data' );
	}

	/**
	 * Processes a refund against the transaction.
	 *
	 * This method is called called from the admin panel by clicking a refund (manual or gateway) button.
	 *
	 * @since 3.0.0
	 * @since 7.0.0 Refactored code into multiple methods.
	 *
	 * @see LLMS_Meta_Box_Order_Transactions::save_refund()
	 *
	 * @param float  $amount Amount to refund.
	 * @param string $note   Optional note to record as an order note. This is passed to the gateway to do store in the gateway if available.
	 * @param string $method Method used to refund, either "manual" (available for all transactions) or "gateway" (when supported by the gateway that processed the transaction).
	 * @return string|WP_Error A refund ID on success or an error object.
	 */
	public function process_refund( $amount, $note = '', $method = 'manual' ) {

		// Ensure the transaction is still eligible for a refund.
		if ( ! $this->can_be_refunded() ) {
			return new WP_Error(
				'llms-txn-refund-not-eligible',
				__( 'The selected transaction is not eligible for a refund.', 'lifterlms' )
			);
		}

		$amount = floatval( $amount );

		// Ensure we can refund the requested amount.
		$refundable = $this->get_refundable_amount();
		if ( $amount > $refundable ) {
			return new WP_Error(
				'llms-txn-refund-amount-too-high',
				sprintf(
					// Translators: %1$s = The requested refund amount; %2$s = the available refundable amount.
					__( 'Requested refund amount was %1$s, the maximum possible refund for this transaction is %2$s.', 'lifterlms' ),
					llms_price( $amount ),
					llms_price( $refundable )
				)
			);
		}

		$id = $this->generate_refund_id( $method, $amount, $note );
		if ( is_string( $id ) ) {
			$this->record_refund( compact( 'amount', 'id', 'method' ), $note );
		} elseif ( ! is_wp_error( $id ) ) {
			$id = new WP_Error( 'llms-txn-refund-unknown-error', __( 'An unknown error occurred while processing the refund.', 'lifterlms' ) );
		}

		return $id;

	}

	/**
	 * Processes a refund via the gateway that processed the transaction.
	 *
	 * @since 7.0.0
	 *
	 * @param float  $amount The refund amount.
	 * @param string $note   Refund order note.
	 * @return string|WP_Error The refund ID or an error object.
	 */
	protected function process_refund_via_gateway( $amount, $note = '' ) {

		$gateway = $this->get_gateway();
		if ( is_wp_error( $gateway ) ) {
			return new WP_Error(
				'llms-txn-refund-gateway-invalid',
				sprintf(
					// Translators: %s = the payment gateway ID.
					__( 'Selected gateway "%s" is inactive or invalid.', 'lifterlms' ),
					$this->get( 'payment_gateway' )
				)
			);
		}

		if ( ! $gateway->supports( 'refunds' ) ) {
			return new WP_Error(
				'llms-txn-refund-gateway-support',
				sprintf(
					// Translators: %s = the payment gateway admin title.
					__( 'Selected gateway "%s" does not support refunds.', 'lifterlms' ),
					$gateway->get_admin_title()
				)
			);
		}

		return $gateway->process_refund( $this, $amount, $note );

	}

	/**
	 * Records a refund against the transaction.
	 *
	 * This method performs no validations and assumes that the refund has already been verified against
	 * the refund method and current transaction restrictions.
	 *
	 * If the refund data isn't validated, try using {@see LLMS_Transaction::process_refund()} instead.
	 *
	 * @since 7.0.0
	 *
	 * @param array  $refund {
	 *      Refund arguments.
	 *
	 *     @type float  $amount The refund amount.
	 *     @type string $id     The generated refund ID.
	 *     @type string $method The refund processing method ID.
	 *     @type string $date   The refund date in MySQL date format. If not supplied, the current time is used.
	 * }
	 * @param string $note User-submitted refund note to add to the order alongside the refund.
	 * @return void
	 */
	public function record_refund( $refund, $note = '' ) {

		$refund = wp_parse_args(
			$refund,
			array(
				'amount' => 0.00,
				'id'     => '',
				'method' => '',
				'date'   => llms_current_time( 'mysql' ),
			)
		);

		// Record the note.
		$this->record_refund_note( $note, $refund['amount'], $refund['id'], $refund['method'] );

		// Update the refunded amount.
		$refund_amount = $this->get( 'refund_amount' );
		$new_amount    = ! $refund_amount ? $refund['amount'] : $refund_amount + $refund['amount'];
		$this->set( 'refund_amount', $new_amount );

		// Record refund metadata.
		$refund_data = $this->get_refunds();

		/**
		 * Filters the stored refund data before saving it.
		 *
		 * @since Unknown
		 *
		 * @param array            $refund {
		 *     An associative array of refund data.
		 *
		 *     @type float  $amount The refund amount.
		 *     @type string $date   The refund date in MySQL date format: `Y-m-d H:i:s`.
		 *     @type string $id     The refund ID.
		 *     @type string $method The refund method ID.
		 * }
		 * @param LLMS_Transaction $transaction The transaction object.
		 * @param float            $amount      The refund amount.
		 * @param string           $method      The refund method ID
		 */
		$refund_data[ $refund['id'] ] = apply_filters( 'llms_transaction_refund_data', $refund, $this, $refund['amount'], $refund['method'] );
		$this->set( 'refund_data', $refund_data );

		// Update status.
		$this->set( 'status', 'llms-txn-refunded' );

	}

	/**
	 * Records an order note associated with a refund.
	 *
	 * @since 7.0.0
	 *
	 * @param string $note      User-submitted refund note data to add to the order alongside the refund.
	 * @param float  $amount    The refund amount.
	 * @param string $refund_id The generated refund ID.
	 * @param string $method    The refund processing method ID.
	 * @return int The WP_Comment ID of the recorded order note.
	 */
	private function record_refund_note( $note, $amount, $refund_id, $method ) {

		/**
		 * Filters user-submitted transaction refund order note.
		 *
		 * @since Unknown.
		 *
		 * @param string           $note        The user-submitted order note text.
		 * @param LLMS_Transaction $transaction The transaction object.
		 * @param float            $amount      The refund amount.
		 * @param string           $method      The ID of the refund method.
		 */
		$orig_note = apply_filters( 'llms_transaction_refund_note', $note, $this, $amount, $method );

		$note = sprintf(
			// Translators: %1$s = The refund amount; %2$d the transaction ID; %3$s The refund method name; %4$s = the refund ID.
			__( 'Refunded %1$s for transaction #%2$d via %3$s [Refund ID: %4$s]', 'lifterlms' ),
			wp_strip_all_tags( llms_price( $amount ) ),
			$this->get( 'id' ),
			$this->get_refund_method_title( $method ),
			$refund_id
		);

		if ( $orig_note ) {
			$note .= "\r\n";
			$note .= __( 'Refund Notes: ', 'lifterlms' );
			$note .= "\r\n";
			$note .= $orig_note;
		}

		// Record the note.
		return $this->get_order()->add_note( $note, true );

	}

	/**
	 * Translation wrapper for {@see LLMS_Transaction::get()` which enables l10n of database values.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key Key to retrieve.
	 * @return string
	 */
	public function translate( $key ) {

		$val = $this->get( $key );

		switch ( $key ) {

			case 'payment_type':
				if ( 'single' === $val ) {
					$val = __( 'Single', 'lifterlms' );
				} elseif ( 'recurring' === $val ) {
					$val = __( 'Recurring', 'lifterlms' );
				} elseif ( 'trial' === $val ) {
					$val = __( 'Trial', 'lifterlms' );
				}
				break;

			default:
				$val = $val;
		}

		return $val;

	}

}
