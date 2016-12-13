<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin analytics Page Base Class
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Analytics_Page {

	/**
	 * Add the analytics page
	 *
	 * @return array
	 */
	public function add_analytics_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get the page sections
	 *
	 * @return array
	 */
	public function get_sections() {
		return array();
	}

	/**
	 * Output analytics sections as tabs and set post href
	 *
	 * @return array
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul>';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=' . $this->id . '&section=' . sanitize_title( $id ) )
			. '"class="' . ($current_section == $id ? 'current' : '' ) . '">' . ( end( $array_keys ) == $id ? '' : '|' ) . '</li>';

			echo '</ul><br class="clear" />';
		}

	}

	/**
	 * Wraps analytics content in parent div and applies title
	 * @param  string $title    [tab title]
	 * @param  string $contents [html contents of page]
	 * @return $html
	 */
	public function get_page_contents( $title = '', $contents = '' ) {

		$html = '<div id="llms-options-page-contents">';

		$html .= '<h2>' . $title . '</h2>';

		$html .= $contents;

		$html .= '</div>';

		return $html;

	}

	public static function full_width_widget( $content ) {

		$html = '<div class="llms-widget-full">';
		$html .= '<div class="llms-widget">';

		$html .= $content;

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function quarter_width_widget( $content ) {

		$html = '<div class="llms-widget-1-4">';
		$html .= '<div class="llms-widget">';

		$html .= $content;

		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

	/**
	 * Output the analytics fields
	 *
	 * @return LLMS_Admin_Analytics::output_fields
	 */
	public function output() {
		$analytics = $this->get_analytics();

		LLMS_Admin_Analytics::output_fields( $analytics );
	}

	/**
	 * Save the analytics field values
	 *
	 * @return void
	 */
	public function save() {
		global $current_section;

		$analytics = $this->get_analytics();
		LLMS_Admin_Analytics::save_fields( $analytics );

		if ( $current_section ) {
	    	do_action( 'lifterlms_update_options_' . $this->id . '_' . $current_section ); }

	}

}
