<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Language Class
*
* Manages formatting languange translation strings
*
* @todo  officially deprecate this function
* @deprecated 2.5.1   use of this class is deprecated, developers should you WordPress translation functions instead, __(), _e(), etc..
*/
class LLMS_Language {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	public static function output( $string ) {

		llms_deprecated_function( '`LLMS_Language::output()`', '2.5.1', '__()' );

		return sprintf( __( '%s', 'lifterlms' ), $string );

	}

}

return new LLMS_Language;
