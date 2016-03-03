<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Language Class
*
* Manages formatting languange translation strings
*/
class LLMS_Language {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	public static function output( $string ) {

		return sprintf( __( '%s', 'lifterlms' ), $string );

	}

}

return new LLMS_Language;
