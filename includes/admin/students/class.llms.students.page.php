<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin students Page Base Class
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Students_Page {

	public function get_student_tabs() {

		if ( empty( $_GET['student'] ) || ! get_user_by( 'id', sanitize_title( $_GET['student'] ) ) ) {
			return;
		} else {
			$search = LLMS()->session->get( 'llms_students_search' );

			if ( ! empty( $search ) ) {
				$search->last_student_viewed = $_GET['student'];
				LLMS()->session->set( 'llms_students_search', $search );
			}

		}

	}


	/**
	 * Add the students page
	 *
	 * @return array
	 */
	public function add_students_page( $pages ) {
		$this->get_student_tabs();
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
	 * Output students sections as tabs and set post href
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
	 * Wraps students content in parent div and applies title
	 * @param  string $title    [tab title]
	 * @param  string $contents [html contents of page]
	 * @return $html
	 */
	public function get_page_contents( $title = '', $contents = '' ) {

		$html = '<div id="llms-options-page-contents">';

		$html .= '<h2>' . sprintf( __( '%s', 'lifterlms' ), $title ) . '</h2>';

		$html .= $contents;

		$html .= '</div>';

		return $html;

	}

	public static function full_width_widget( $content, $class = '' ) {

		$html = '<div class="llms-widget-full ' . $class . '">';
		$html .= '<div class="llms-widget">';

		$html .= $content;

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function quarter_width_widget( $content, $class = '' ) {

		$html = '<div class="llms-widget-1-4 ' . $class . '">';
		$html .= '<div class="llms-widget">';

		$html .= $content;

		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

	public static function third_width_widget( $content, $class = '' ) {

		$html = '<div class="llms-widget-1-3 ' . $class . '">';
		$html .= '<div class="llms-widget">';

		$html .= $content;

		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

	/**
	 * Output the students fields
	 *
	 * @return LLMS_Admin_Students::output_fields
	 */
	public function output() {
		$students = $this->get_students();

		LLMS_Admin_Students::output_fields( $students );
	}

	/**
	 * Save the students field values
	 *
	 * @return void
	 */
	public function save() {
		global $current_section;

		$students = $this->get_students();
		LLMS_Admin_Students::save_fields( $students );

		if ( $current_section ) {
	    	do_action( 'lifterlms_update_options_' . $this->id . '_' . $current_section ); }

	}

}
