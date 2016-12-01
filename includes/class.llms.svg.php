<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Dates Class
*
* Manages formatting dates for I/O and display
*/
class LLMS_Svg {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Get SVG
	 * Returns svg icon from svg sprite file
	 *
	 * @param  string $id    [svg id value]
	 * @param  string $title [optional: title]
	 * @param  string $desc  [optional: description]
	 * @param  string $class [optional: css classes]
	 * @return [string]        [returns sprite html]
	 */
	public static function get_icon( $id, $title = '', $desc = '', $class = '' ) {

		$html = '';

		if ( isset( $id ) ) {

			$html .= '<svg class="icon ' . $class . '" role="img" aria-labelledby="title desc">';

			$html .= '<title id="title">' . $title . '</title>';

		  	$html .= '<desc id="desc">' . $desc . '</desc>';

		  	$html .= '<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' . LLMS_SVG_DIR . '#' . $id . '"></use>';

			$html .= '</svg>';

		}

		return $html;

	}

}

return new LLMS_Svg;
