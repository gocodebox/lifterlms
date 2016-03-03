<?php

/**
* Checkout Shortcode
*
* Sets functionality associated with shortcode [llms_checkout]
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Shortcode_Checkout {

	/**
	* Get shortcode content
	*
	* @param array $atts
	* @return array $messages
	*/
	public static function get( $atts ) {
		return LLMS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	* Determines what content to output to user absed on status
	*
	* @param array $atts
	* @return array $messages
	*/
	public static function output( $atts ) {
		global $lifterlms, $wp;

		if ( ! is_user_logged_in() ) {

			$message = apply_filters( 'lifterlms_checkout_message', '' );

			if ( ! empty( $message ) ) {
			}

			$product_id = get_query_var( 'product-id' );
			$account_url = get_permalink( llms_get_page_id( 'myaccount' ) );

			$account_redirect = add_query_arg( 'product-id', $product_id, $account_url );

			echo apply_filters('lifterlms_checkout_user_not_logged_in_output', sprintf(
				__( '<a href="%1$s">Login or create an account to purchase this course</a>.', 'lifterlms' ) . ' ',
				$account_redirect
			));

		} else {

			if ( isset( $wp->query_vars['confirm-payment'] ) ) {

				self::confirm_payment();
			} else {

				apply_filters( 'lifterlms_checkout_user_logged_in_output', self::checkout( $atts ) );

			}
		}

	}

	/**
	* My Checkout page template
	*
	* @param array $atts
	* @return void
	*/
	private static function checkout( $atts ) {

		llms_get_template( 'checkout/form-checkout.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
		) );
	}

	/**
	* Edit Checkout template
	*
	* @return void
	*/
	private static function confirm_payment() {
		llms_get_template( 'checkout/form-confirm-payment.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
	}

}
