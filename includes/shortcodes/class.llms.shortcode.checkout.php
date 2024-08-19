<?php
/**
 * LifterLMS Checkout Page Shortcode
 *
 * Controls functionality associated with shortcode [llms_checkout].
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 1.0.0
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Checkout class.
 *
 * @since 1.0.0
 * @since 3.30.1 Added check via llms_locate_order_for_user_and_plan() to automatically resume an existing pending order for logged in users if one exists.
 * @since 3.33.0 Checkout form not displayed to users already enrolled in the product being purchased, a notice informing them of that is displayed instead.
 * @since 3.35.0 Sanitize input data.
 * @since 3.36.3 Added l10n function to membership restriction error message.
 * @since 4.2.0 Added filter to control the displaying of the notice informing the students they're already enrolled in the product being purchased.
 * @since 5.0.0 Add support for LLMS_Form field management.
 */
class LLMS_Shortcode_Checkout {

	/**
	 * Current User ID.
	 *
	 * @var int
	 */
	public static $uid;

	/**
	 * Renders the checkout template.
	 *
	 * @since 1.0.0
	 * @since 3.33.0 Do not display the checkout form but a notice to a logged in user enrolled in the product being purchased.
	 * @since 3.36.3 Added l10n function to membership restriction error message.
	 * @since 4.2.0 Added filter to control the displaying of the notice informing the students they're already enrolled in the product being purchased.
	 *
	 * @param array $atts Shortcode attributes array.
	 * @return void
	 */
	private static function checkout( $atts ) {

		// if there are membership restrictions, check the user is in at least one membership.
		// this is to combat CHEATERS.
		if ( $atts['plan']->has_availability_restrictions() ) {
			$access = false;
			foreach ( $atts['plan']->get_array( 'availability_restrictions' ) as $mid ) {

				// once we find a membership, exit.
				if ( llms_is_user_enrolled( self::$uid, $mid ) ) {
					$access = true;
					break;
				}
			}
			if ( ! $access ) {
				llms_print_notice( __( 'You must be a member in order to purchase this access plan.', 'lifterlms' ), 'error' );
				return;
			}
		}

		if ( self::$uid ) {
			// ensure the user isn't enrolled in the product being purchased.
			if ( isset( $atts['product'] ) && llms_is_user_enrolled( self::$uid, $atts['product']->get( 'id' ) ) ) {

				/**
				 * Filter the displaying of the checkout form notice for already enrolled in the product being purchased.
				 *
				 * @since 4.2.0
				 *
				 * @param bool $display_notice Whether or not displaying the checkout form notice for already enrolled students in the product being purchased.
				 */
				if ( apply_filters( 'llms_display_checkout_form_enrolled_students_notice', true ) ) {
					llms_print_notice(
						sprintf(
							// Translators: %2$s = The product type (course/membership); %1$s = product permalink.
							__( 'You already have access to this %2$s! Visit your dashboard <a href="%1$s">here.</a>', 'lifterlms' ),
							llms_get_page_url( 'myaccount' ),
							$atts['product']->get_post_type_label()
						),
						'notice'
					);
				}

				return;
			}

			$user = get_userdata( self::$uid );
			llms_print_notice( sprintf( __( 'You are currently logged in as <em>%1$s</em>. <a href="%2$s">Click here to logout</a>', 'lifterlms' ), $user->user_email, wp_logout_url( $atts['plan']->get_checkout_url() ) ), 'notice' );
		} else {
			llms_get_login_form( sprintf( __( 'Already have an account? <a href="%s">Click here to login</a>', 'lifterlms' ), '#llms-show-login' ), $atts['plan']->get_checkout_url() );
		}

		llms_get_template( 'checkout/form-checkout.php', $atts );
	}

	/**
	 * Renders the confirm payment checkout template.
	 *
	 * @since 1.0.0
	 * @version 3.0.0
	 *
	 * @param array $atts shortcode attributes.
	 * @return void
	 */
	private static function confirm_payment( $atts ) {

		llms_get_template( 'checkout/form-confirm-payment.php', $atts );
	}

	/**
	 * Output error messages when they're encountered.
	 *
	 * @since 3.0.0
	 *
	 * @param string $message The error message.
	 * @return void
	 */
	private static function error( $message ) {
		/**
		 * Filters error messages displayed on the checkout screen.
		 *
		 * @since 3.0.0
		 *
		 * @param string $message The error message.
		 */
		echo wp_kses_post( apply_filters( 'llms_checkout_error_output', $message ) );
	}

	/**
	 * Retrieve the shortcode content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function get( $atts ) {
		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Gather a bunch of information and output the actual content for the shortcode.
	 *
	 * @since 1.0.0
	 * @since 3.30.1 Added check via llms_locate_order_for_user_and_plan() to automatically resume an existing pending order for logged in users if one exists.
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.0.0 Organize attribute configuration and add new dynamic attributes related to the LLMS_Form post.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.0.0 Fixed unclosed `div.llms-checkout-wrapper` on empty cart.
	 * @since 7.0.1 Fixed issue encountered when trying to confirm payment for a non-existent order.
	 *
	 * @param array $atts Shortcode atts from originating shortcode.
	 * @return void
	 */
	public static function output( $atts ) {

		global $wp;

		$atts = $atts ? $atts : array();

		$atts['cols'] = isset( $atts['cols'] ) ? $atts['cols'] : 2;

		self::$uid = get_current_user_id();

		$atts['gateways']         = llms()->payment_gateways()->get_enabled_payment_gateways();
		$atts['selected_gateway'] = llms()->payment_gateways()->get_default_gateway();

		$atts['order_key'] = '';

		$atts['field_data'] = array();
		if ( isset( $_POST ) && isset( $_POST['action'] ) && 'create_pending_order' === $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$atts['field_data'] = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( self::$uid ) {
			$atts['field_data'] = get_current_user_id();
		}

		self::checkout_wrapper_start();

		/**
		 * Allows gateways or third parties to output custom errors before
		 * any core logic is executed.
		 *
		 * This filter returns `false` by default. To output custom errors return
		 * the error message as a string that will be displayed on screen.
		 *
		 * @since Unknown
		 *
		 * @param bool|string $pre_error A custom error message.
		 */
		$err = apply_filters( 'lifterlms_pre_checkout_error', false );
		if ( $err ) {
			self::error( $err );
			self::checkout_wrapper_end();
			return;
		}

		llms_print_notices();

		// purchase step 1.
		if ( isset( $_GET['plan'] ) && is_numeric( $_GET['plan'] ) ) {

			$plan_id = llms_filter_input( INPUT_GET, 'plan', FILTER_SANITIZE_NUMBER_INT );

			// Only retrieve if plan is a llms_access_plan and is published.
			if ( 0 === strcmp( get_post_status( $plan_id ), 'publish' ) && 0 === strcmp( get_post_type( $plan_id ), 'llms_access_plan' ) ) {

				$coupon = llms()->session->get( 'llms_coupon' );

				if ( isset( $coupon['coupon_id'] ) && isset( $coupon['plan_id'] ) ) {
					if ( $coupon['plan_id'] == $_GET['plan'] ) {
						$atts['coupon'] = new LLMS_Coupon( $coupon['coupon_id'] );
					} else {
						llms()->session->set( 'llms_coupon', false );
						$atts['coupon'] = false;
					}
				} else {
					$atts['coupon'] = false;
				}

				// Use posted order key to resume a pending order.
				if ( isset( $_POST['llms_order_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$atts['order_key'] = llms_filter_input_sanitize_string( INPUT_POST, 'llms_order_key' );

					// Attempt to locate a pending order.
				} elseif ( self::$uid ) {
					$pending_order = llms_locate_order_for_user_and_plan( self::$uid, $plan_id );
					if ( $pending_order ) {
						$order             = llms_get_post( $pending_order );
						$atts['order_key'] = ( 'llms-pending' === $order->get( 'status' ) ) ? $order->get( 'order_key' ) : '';
					}
				}

				$atts = self::setup_plan_and_form_atts( $plan_id, $atts );

				/**
				 * Filter the number of columns used to render the checkout/enrollment form.
				 *
				 * @since Unknown.
				 * @since 5.0.0 Added `$form_location` parameter.
				 *
				 * @param int $cols Number of columns. Accepts 1 or 2.
				 * @param LLMS_Access_Plan $plan Access plan object.
				 * @param string $form_location Form location ID.
				 */
				$atts['cols'] = apply_filters( 'llms_checkout_columns', ( $atts['is_free'] || ! $atts['form_fields'] ) ? 1 : $atts['cols'], $atts['plan'], $atts['form_location'] );

				self::checkout( $atts );

			} else {

				self::error( __( 'Invalid access plan.', 'lifterlms' ) );

			}
		} elseif ( isset( $wp->query_vars['confirm-payment'] ) ) {

			$order_key = llms_filter_input_sanitize_string( INPUT_GET, 'order' );
			$order     = $order_key ? llms_get_order_by_key( $order_key ) : false;
			if ( ! $order ) {
				self::error( __( 'Could not locate an order to confirm.', 'lifterlms' ) );
				self::checkout_wrapper_end();
				return;
			}

			$atts = self::setup_plan_and_form_atts( $order->get( 'plan_id' ), $atts );

			if ( $order->get( 'coupon_id' ) ) {
				$atts['coupon'] = new LLMS_Coupon( $order->get( 'coupon_id' ) );
			} else {
				$atts['coupon'] = false;
			}

			$atts['selected_gateway'] = llms()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

			self::confirm_payment( $atts );

		} else {

			self::error( sprintf( __( 'Your cart is currently empty. Click <a href="%s">here</a> to get started.', 'lifterlms' ), llms_get_page_url( 'courses' ) ) );

		}

		self::checkout_wrapper_end();
	}

	/**
	 * Setup attributes for plan and form information.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Properly detect empty form fields when the html is only composed of blanks and empty paragraphs.
	 * @since 7.0.0 Add 'redirect' hidden field to be used on purchase completion.
	 *
	 * @param int   $plan_id LLMS_Access_Plan post id.
	 * @param array $atts    Existing attributes.
	 * @return array Modified attributes array.
	 */
	protected static function setup_plan_and_form_atts( $plan_id, $atts ) {

		$plan = new LLMS_Access_Plan( $plan_id );

		$atts['plan']    = $plan;
		$atts['product'] = $plan->get_product();
		$atts['is_free'] = $plan->has_free_checkout();

		$atts['form_location'] = 'checkout';
		$atts['form_title']    = llms_get_form_title( $atts['form_location'], array( 'plan' => $plan ) );
		$atts['form_fields']   = self::clean_form_fields( llms_get_form_html( $atts['form_location'], array( 'plan' => $plan ) ) );

		// Add 'redirect' URL hidden field to be used on purchase completion.
		$plan_redirection_url = $plan->get_redirection_url( false );
		if ( $plan_redirection_url ) {
			$atts['form_fields'] .= ( new LLMS_Form_Field(
				array(
					'id'             => 'llms-redirect',
					'name'           => 'redirect',
					'type'           => 'hidden',
					'value'          => $plan_redirection_url,
					'data_store_key' => false,
				)
			) )->get_html();
		}

		return $atts;
	}

	/**
	 * Clean form fields html
	 *
	 * Properly detects empty form fields when the html is only composed of blanks and empty paragraphs.
	 * In this case the form fields html is turned into an empty string.
	 *
	 * @since 5.1.0
	 *
	 * @param array $fields_html Form Fields.
	 * @return array
	 */
	private static function clean_form_fields( $fields_html ) {
		// If fields html has only blanks and emoty paragraphs (autop?), clean it.
		if ( empty( preg_replace( '/(\s)*(<p><\/p>)*/m', '', $fields_html ) ) ) {
			$fields_html = '';
		}
		return $fields_html;
	}

	/**
	 * Output the checkout wrapper opening tags.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	private static function checkout_wrapper_start() {
		echo '<div class="llms-checkout-wrapper">';
	}

	/**
	 * Output the checkout wrapper closing tags.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	private static function checkout_wrapper_end() {
		echo '</div><!-- .llms-checkout-wrapper -->';
	}
}
