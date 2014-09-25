<?php

/**
* Shortcode base class
*
* Shortcode logic
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Shortcodes {

	/**
	* init shortcodes array
	*
	* @return void
	*/
	public static function init() {

		$shortcodes = array(
			'lifterlms_my_account' => __CLASS__ . '::my_account',
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );

		}

	}

	/**
	* Creates a wrapper for shortcode. 
	*
	* @return void
	*/
	public static function shortcode_wrapper(

		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'lifterlms',
			'before' => null,
			'after'  => null
	) ) {

		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	* My account shortcode.
	*
	* Used for displaying account.
	*
	* @return self::shortcode_wrapper
	*/
	public static function my_account( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_My_Account', 'output' ), $atts );

	}

}

