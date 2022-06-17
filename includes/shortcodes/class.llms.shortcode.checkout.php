<?php
/**
 * LifterLMS Checkout Page Shortcode.
 *
 * Controls functionality associated with shortcode [llms_checkout].
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Checkout
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
	 * Adds a notice to the session that the user already has access to the product.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Product $product The product object.
	 * @param int          $user_id The user ID.
	 * @return void
	 */
	private static function add_is_enrolled_notice( $product, $user_id ) {
		/**
		 * Filter if the "user already enrolled" checkout notice should be displayed.
		 *
		 * @since 4.2.0
		 * @since [version] Added `$product` and `$user_id` parameters.
		 *
		 * @param bool         $display_notice Whether or not to display the notice.
		 * @param LLMS_Product $product        The product object.
		 * @param int          $user_id        The user ID.
		 */
		if ( ! apply_filters( 'llms_display_checkout_form_enrolled_students_notice', true, $product, $user_id ) ) {
			return;
		}

		llms_add_notice(
			sprintf(
				/* translators: %1$s: product permalink, %2$s: the product type (course/membership) */
				__( 'You already have access to this <a href="%1$s">%2$s</a>!', 'lifterlms' ),
				get_permalink( $product->get( 'id' ) ),
				$product->get_post_type_label()
			),
			'notice'
		);
	}

	/**
	 * Adds a notice to the session that the user must be a member to purchase the plan.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Access_Plan $plan The access plan object.
	 * @return void
	 */
	private static function add_membership_required_notice( $plan ) {

		/**
		 * Filter if the "user must be a member" checkout notice should be displayed.
		 *
		 * @since [version]
		 *
		 * @param bool             $display_notice Whether or not to display the notice.
		 * @param LLMS_Access_Plan $plan           The access plan object.
		 */
		if ( ! apply_filters( 'llms_display_checkout_membership_required_notice', true, $plan ) ) {
			return;
		}

		llms_add_notice(
			__( 'You must be a member in order to purchase this access plan.', 'lifterlms' ),
			'error'
		);
	}

	/**
	 * Adds a notice to the session that the access plan's product was not found.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Product $product The product object.
	 * @return void
	 */
	private static function add_product_not_found_notice( $product ) {

		/**
		 * Filter if the "product not found" checkout notice should be displayed.
		 *
		 * @since [version]
		 *
		 * @param bool         $display_notice Whether or not to display the notice.
		 * @param LLMS_Product $product        The product object.
		 */
		if ( ! apply_filters( 'llms_display_checkout_product_not_found_notice', true, $product ) ) {
			return;
		}

		llms_add_notice(
			__( 'The product that this access plan is for could not be found.', 'lifterlms' ),
			'error'
		);
	}

	/**
	 * Renders the checkout template.
	 *
	 * @since 1.0.0
	 * @since 3.33.0 Do not display the checkout form but a notice to a logged in user enrolled in the product being purchased.
	 * @since 3.36.3 Added l10n function to membership restriction error message.
	 * @since 4.2.0 Added filter to control the displaying of the notice informing the students they're already enrolled in the product being purchased.
	 * @since [version] Moved verification logic and error notices to
	 *              `LLMS_Shortcode_Checkout::is_checkout_form_displayable()`.
	 *
	 * @param array $atts An array of shortcode attributes documented in `templates/checkout/form-checkout.php`.
	 * @return void
	 */
	private static function checkout( $atts ) {

		if ( self::$uid ) {

			self::print_logged_in_notice( $atts['plan'], self::$uid );

		} else {
			llms_get_login_form(
				sprintf(
					/* translators: %s: URL anchor to show the login form */
					__( 'Already have an account? <a href="%s">Click here to login.</a>', 'lifterlms' ),
					'#llms-show-login'
				),
				$atts['plan']->get_checkout_url()
			);
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

		echo apply_filters( 'llms_checkout_error_output', $message );

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
	 * Returns true if they checkout form can be displayed,
	 * else adds the appropriate notice to the session and returns false.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Access_Plan $plan    The access plan object.
	 * @param LLMS_Product     $product The product object.
	 * @param int              $user_id The user ID.
	 * @return bool True if checkout form can be displayed, else false.
	 */
	private static function is_checkout_form_displayable( $plan, $product, $user_id ) {

		// Ensure the user isn't already enrolled in the product being purchased.
		if ( llms_is_user_enrolled( $user_id, $product->get( 'id' ) ) ) {
			self::add_is_enrolled_notice( $product, $user_id );
			return false;
		}

		// Make sure the access plan's product exists.
		if ( ! $product->exists() ) {
			self::add_product_not_found_notice( $product );
			return false;
		}

		// If there are membership restrictions, check that the user is in at least one membership.
		// This is to combat CHEATERS.
		if ( ! $plan->is_available_to_user( $user_id ) ) {
			self::add_membership_required_notice( $plan );
			return false;
		}

		// Is the course restricted because enrollment capacity has been reached or we're not in the enrollment period?
		if ( 'course' === $product->get( 'type' ) ) {

			$course             = new LLMS_Course( $product->get( 'id' ) );
			$restricted_message = $course->is_enrollment_restricted();
			if ( $restricted_message ) {
				llms_add_notice( $restricted_message, 'error' );
				return false;
			}
		}

		return true;
	}

	/**
	 * Gather a bunch of information and output the actual content for the shortcode.
	 *
	 * @since 1.0.0
	 * @since 3.30.1 Added check via llms_locate_order_for_user_and_plan() to automatically resume an existing pending order for logged in users if one exists.
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.0.0 Organize attribute configuration and add new dynamic attributes related to the LLMS_Form post.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since [version] Prevented the display of the checkout form if the product is not purchasable.
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

		echo '<div class="llms-checkout-wrapper">';

		// allow gateways to throw errors before outputting anything else.
		// useful if you need to check for extra session or query string data.
		$err = apply_filters( 'lifterlms_pre_checkout_error', false );
		if ( $err ) {
			return self::error( $err );
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

				if ( self::is_checkout_form_displayable( $atts['plan'], $atts['product'], self::$uid ) ) {
					self::checkout( $atts );
				} else {
					llms_print_notices();
				}
			} else {

				self::error( __( 'Invalid access plan.', 'lifterlms' ) );

			}
		} elseif ( isset( $wp->query_vars['confirm-payment'] ) ) {

			if ( ! isset( $_GET['order'] ) ) {

				return self::error( __( 'Could not locate an order to confirm.', 'lifterlms' ) );

			}

			$order = llms_get_order_by_key( llms_filter_input_sanitize_string( INPUT_GET, 'order' ) );
			$atts  = self::setup_plan_and_form_atts( $order->get( 'plan_id' ), $atts );

			if ( $order->get( 'coupon_id' ) ) {
				$atts['coupon'] = new LLMS_Coupon( $order->get( 'coupon_id' ) );
			} else {
				$atts['coupon'] = false;
			}

			$atts['selected_gateway'] = llms()->payment_gateways()->get_gateway_by_id( $order->get( 'payment_gateway' ) );

			self::confirm_payment( $atts );

		} else {

			return self::error( sprintf( __( 'Your cart is currently empty. Click <a href="%s">here</a> to get started.', 'lifterlms' ), llms_get_page_url( 'courses' ) ) );

		}

		echo '</div><!-- .llms-checkout-wrapper -->';

	}

	/**
	 * Prints a notice that the user is logged in.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Access_Plan $plan    The access plan object.
	 * @param int              $user_id The user ID.
	 * @return void
	 */
	private static function print_logged_in_notice( $plan, $user_id ) {

		/**
		 * Filter if the "user is logged in" checkout notice should be displayed.
		 *
		 * @since [version]
		 *
		 * @param bool             $display_notice Whether or not to display the notice.
		 * @param LLMS_Access_Plan $plan           The access plan object.
		 * @param int              $user_id        The user ID.
		 */
		if ( ! apply_filters( 'llms_display_checkout_logged_in_notice', true, $plan, $user_id ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		llms_print_notice(
			sprintf(
				/* translators: %1$s: user email address, %2$s: logout URL with a redirect to the checkout URL */
				__( 'You are currently logged in as <em>%1$s</em>. <a href="%2$s">Click here to logout</a>', 'lifterlms' ),
				$user->user_email,
				wp_logout_url( $plan->get_checkout_url() )
			),
			'notice'
		);
	}

	/**
	 * Setup attributes for plan and form information
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Properly detect empty form fields when the html is only composed of blanks and empty paragraphs.
	 *
	 * @param int   $plan_id LLMS_Access_Plan post id.
	 * @param array $atts Existing attributes.
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
}
