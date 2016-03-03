<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin students Page, sales Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Students_Dashboard extends LLMS_Students_Page {

	/**
	* Constructor
	*
	* executes students tab actions
	*/
	public function __construct() {
		$this->id    = 'dashboard';
		$this->label = __( 'Dashboard', 'lifterlms' );

		add_filter( 'lifterlms_students_tabs_array', array( $this, 'add_students_page' ), 20 );
		add_action( 'lifterlms_students_' . $this->id, array( $this, 'output' ) );
		//add_action( 'admin_init', array( $this, 'search_students_sales' ) );
		add_action( 'lifterlms_students_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Main students page builder
	 * Collects elements and calls get_page_contents to wrap html
	 * Called from html.admin.students to build page.
	 *
	 * @return [html]
	 */
	public function get_students() {
		$search = LLMS()->session->get( 'llms_students_search' );
		//var_dump( $search);
		$title = __( 'Students', 'lifterlms' );

		//search form
		$html = $this->search_form();

		if ( $search ) {
			$html .= self::full_width_widget( $this->student_search_results( $search ) );
		}

		//return contents
		return $this->get_page_contents( $title, $html );
	}

	/** Builds Search Form */
	public function search_form() {

		//get session data if it exists
		$search = LLMS()->session->get( 'llms_students_search' );

		$product_id = isset( $search->product_id ) ? $search->product_id : '';
		$include_expired = isset( $search->include_expired ) ? $search->include_expired : false;

		//get products
		$products = LLMS_Analytics::get_products();

		//start building html
		$html = '<div class="llms-search-form-wrapper">';

		//Product Select ( Courses and Memberships )
		$html .= '<div class="llms-select">';
		$html .= '<label>' . __( 'Select a product', 'lifterlms' ) . '</label>';
		$html .= '<select id="llms-product-select" name="llms_product_select" class="chosen-select-width">';

		//all products option
		$html .= '<option value="all_products" ' . ( $product_id == 'all_products' ? 'selected' : '' ) . '>' . __( 'All Products', 'lifterlms' ) . '</option>';

		//loop through posts
		if ( $products ) {
			$html .= '<optgroup label="' . __( 'Courses', 'lifterlms' ) . '">';
			foreach ( $products as $key => $product ) {
				if ( $product->post_type === 'course' ) {
						$html .= '<option value="' . $product->ID . '"
						' . ( $product_id == $product->ID  ? 'selected' : '' ) . '>
						' . $product->post_title . '</option>';
					//unset the objects so I don't loop over them again
					unset( $products[ $key ] );
				}
			}
			$html .= '</optgroup>';
			$html .= '<optgroup label="' . __( 'Memberships', 'lifterlms' ) . '">';
			foreach ( $products as $key => $product ) {
				if ( $product->post_type === 'llms_membership' ) {
					$html .= '<option value="' . $product->ID . '" 
						' . ( $product_id == $product->ID ? 'selected' : '' ) . '>
						' . $product->post_title . '</option>';
					//no real reason except the array means nothing anymore.
					unset( $products[ $key ] );
				}
			}
			$html .= '</optgroup>';
		}

		$html .= '</select>';
		$html .= '</div>';

		// Exclude Coupons
		$html .= '<div id="include_expired_users" class="llms-checkbox">';
		$html .= '<input id="exp_users_filter" type="checkbox" name="llms_include_expired_users" ' .  ( $include_expired ? 'checked' : '' ) . '>' . __( 'Include Expired Students', 'lifterlms' );
		$html .= '</div>';

		$html .= wp_nonce_field( 'search_analytics_sales', '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="llms-students-search" />';

		//search button
		$html .= '<div class="llms-search-button">';
		$html .= get_submit_button( 'Search Students', 'primary', 'llms_search', true, array( 'id' => 'llms_analytics_search' ) );
		$html .= '</div>';

		$html .= '</div>';

		return $html;

	}



	public function student_search_results( $search ) {

		//$headers = array( 'Last', 'First', 'Enrolled', 'Completion', 'Last Lesson Completed' );

		if ( $search ) {

			$student_arrays = $search->students;

			//array_unshift($students, $headers);
		}

		$html = '<p class="llms-label">' . __( 'Students', 'lifterlms' ) . '</p>';

		if ( ! empty( $student_arrays ) ) {

			$html .= '<script>
				var students_search_result_large = ' . json_encode( $student_arrays['large'] ) . '
				</script>';
			$html .= '<div id="student_search_results" class="llms-chart"></div>';

		}

		return $html;

	}

	/**
	 * Total sales html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_students_all_time( $search ) {

		$html = '<p class="llms-label">' . __( 'All Students', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_users( $search->students ) . '</h1>';

		return $html;
	}

	/**
	 * Total units sold html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_current_students( $search ) {

		$html = '<p class="llms-label">' . __( 'Current Students', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_current_enrolled_users( $search->students ) . '</h1>';

		return $html;
	}

	/**
	 * Total coupons used html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function completion_percentage( $search ) {

		$html = '<p class="llms-label">' . __( 'Completion %', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Number::whole_number( LLMS_Analytics::course_completion_percentage( $search->students ) ) . '%</h1>';

		return $html;
	}

	/**
	 * Total coupon $ used
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_certificates_issued( $search ) {

		$html = '<p class="llms-label">' . __( 'Certificates Issued', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_certs_issued( $search->product_id ) . '</h1>';

		return $html;
	}





	/**
	 * save students to the database
	 *
	 * @return LLMS_Admin_students::save_fields
	 */
	public function save() {
		$students = $this->get_students();

		LLMS_Admin_Students::save_search_fields( $students );

	}

	/**
	 * get students from the database
	 *
	 * @return array
	 */
	public function output() {
		$students = $this->get_students( );

			LLMS_Admin_Students::output_html( $students );
	}

}

return new LLMS_Students_Dashboard();
