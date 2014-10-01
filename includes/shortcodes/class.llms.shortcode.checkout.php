<?php

/**
* My Account Shortcode
*
* TODO: description
*
* @version 1.0
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

				llms_add_notice( $message );
			}

		}

		else {

			self::checkout( $atts );

		}



		
	}

		/**
	* My Account page template
	*
	* @param array $atts
	* @return void
	*/
	private static function checkout( $atts ) {

		llms_get_template( 'checkout/form-checkout.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
		) );
	}

}