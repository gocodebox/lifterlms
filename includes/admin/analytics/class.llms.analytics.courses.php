<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'LLMS_Analytics_Courses' ) ) :

	/**
* Admin analytics Page, sales Tab
*
* @author codeBOX
* @project lifterLMS
*/
	class LLMS_Analytics_Courses extends LLMS_Analytics_Page {

		/**
	* Constructor
	*
	* executes analytics tab actions
	*/
		public function __construct() {
			$this->id    = 'courses';
			$this->label = __( 'Courses', 'lifterlms' );

			add_filter( 'lifterlms_analytics_tabs_array', array( $this, 'add_analytics_page' ), 20 );
			add_action( 'lifterlms_analytics_' . $this->id, array( $this, 'output' ) );
			//add_action( 'admin_init', array( $this, 'search_analytics_sales' ) );
			add_action( 'lifterlms_analytics_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
	 * Main Analytics page builder
	 * Collects elements and calls get_page_contents to wrap html
	 * Called from html.admin.analytics to build page.
	 *
	 * @return [html]
	 */
		public function get_analytics() {
			$search = LLMS()->session->get( 'llms_analytics_course' );
			//var_dump($search);

			$title = __( 'Course Analytics', 'lifterlms' );

			//search form
			$html = $this->search_form();

			if ( $search ) {
				//WIDGET ROW
				$html .= '<div class="llms-widget-row">';
				//total Students ever
				$html .= self::quarter_width_widget( $this->total_students_all_time( $search ) );

				// total currently enrolled students
				$html .= self::quarter_width_widget( $this->total_current_students( $search ) );

				//course completion percentage
				$html .= self::quarter_width_widget( $this->completion_percentage( $search ) );

				//total certificates issued
				$html .= self::quarter_width_widget( $this->total_certificates_issued( $search ) );

				$html .= '</div>'; //end widget row

				//sales volumn line chart
				$html .= self::full_width_widget( $this->sales_chart( $search ) );

				if ( 'all_courses' !== $search->product_id ) {
					$html .= self::full_width_widget( $this->lesson_completion_chart( $search ) );
					$html .= self::full_width_widget( $this->lesson_student_table( $search ) );
				}
			}

			//return contents
			return $this->get_page_contents( $title, $html );
		}

		/** Builds Search Form */
		public function search_form() {

			//get session data if it exists
			$search = LLMS()->session->get( 'llms_analytics_course' );

			$product_id = isset( $search->product_id ) ? $search->product_id : '';
			$date_filter = isset( $search->date_filter ) ? $search->date_filter : 'none';
			$start_date = isset( $search->start_date ) ? LLMS_Date::pretty_date( $search->start_date ) : '';
			$end_date = isset( $search->end_date ) ? LLMS_Date::pretty_date( $search->end_date ) : '';

			//get products
			$products = LLMS_Analytics::get_products();
			//get date filters
			$date_filters = LLMS_Date::date_filters();

			//start building html
			$html = '<div class="llms-search-form-wrapper">';

			//Product Select ( Courses and Memberships )
			$html .= '<div class="llms-select">';
			$html .= '<label>' . __( 'Select a Course', 'lifterlms' ) . '</label>';
			$html .= '<select id="llms-product-select" name="llms_product_select" class="chosen-select-width">';

			//all products option
			$html .= '<option value="all_courses" ' . ( 'all_courses' == $product_id ? 'selected' : '' ) . '>' . __( 'All Courses', 'lifterlms' ) . '</option>';

			//loop through posts
			if ( $products ) {
				foreach ( $products as $key => $product ) {
					if ( 'course' === $product->post_type ) {
						$html .= '<option value="' . $product->ID . '"
						' . ( $product_id == $product->ID  ? 'selected' : '' ) . '>
						' . $product->post_title . '</option>';
						//unset the objects so I don't loop over them again
					}
				}
			}

			$html .= '</select>';
			$html .= '</div>';

			//Date filters
			$html .= '<div class="llms-select">';
			$html .= '<label>' . __( 'Filter Date Range', 'lifterlms' ) . '</label>';
			$html .= '<select id="llms-date-filter-select" name="llms_date_filter" class="chosen-select-width">';

			foreach ( $date_filters as $key => $value ) {
				$html .= '<option value="' . $key . '"
				' . ( $date_filter == $key ? 'selected' : '' ) . '>
				' . $value . '</option>';

			}

			$html .= '</select>';
			$html .= '</div>';

			//start date
			$html .= '<div class="llms-filter-options date-filter">';
			$html .= '<div class="llms-date-select">';
			$html .= '<label>' . __( 'Start date', 'lifterlms' ) . '</label>';
			$html .= '<input type="text" name="llms-start-date" class="llms-date-range-select-start" value="' . $start_date . '">';
			$html .= '</div>';

			//end date
			$html .= '<div class="llms-date-select">';
			$html .= '<label>' . __( 'End date', 'lifterlms' ) . '</label>';
			$html .= '<input type="text" name="llms-end-date" class="llms-date-range-select-end" value="' . $end_date . '">';
			$html .= '</div>';
			$html .= '</div>'; //end date filters

			$html .= wp_nonce_field( 'search_analytics_course', '_wpnonce', true, false );
			$html .= '<input type="hidden" name="action" value="llms-analytics-course" />';

			//search button
			$html .= '<div class="llms-search-button">';
			//$html .= '<input type="submit" name="llms_search" class="button button-primary" id="llms_analytics_search" value="Filter Results" />';
			$html .= get_submit_button( 'Filter Results', 'primary', 'llms_search', true, array(
				'id' => 'llms_analytics_search',
			) );
			$html .= '</div>';

			$html .= '</div>';

			return $html;

		}



		public function sales_chart( $search ) {

			$headers = array( 'Date' );

			if ( $search ) {

				//add each course name to headers
				foreach ( $search->courses as $course ) {
					array_push( $headers, $course->post_title );
				}

				$total_students_by_day = LLMS_Analytics::get_total_enrolled_by_day( $search );

				array_unshift( $total_students_by_day, $headers );
			}

			$html = '<p class="llms-label">' . __( 'Student Enrollment', 'lifterlms' ) . '</p>';
			$html .= '<script>var enrolled_students = ' . json_encode( $total_students_by_day ) . '</script>';
			$html .= '<div id="curve_chart" class="llms-chart"></div>';

			return $html;
		}

		public function lesson_completion_chart( $search ) {

			$headers = array( 'Lesson', 'Completion Percentage' );

			if ( $search ) {

				$lesson_completion_percent = LLMS_Analytics::get_lesson_completion_avg( $search );

				if ( ! empty( $lesson_completion_percent ) ) {
					array_unshift( $lesson_completion_percent, $headers );
				} else {
					$lesson_completion_percent = array();
				}
			} else {
				$lesson_completion_percent = array();
			}

			$html = '<p class="llms-label">' . __( 'Lesson Completion Percentage', 'lifterlms' ) . '</p>';
			$html .= '<script>var lesson_completion_percent = ' . json_encode( $lesson_completion_percent ) . '</script>';
			$html .= '<div id="lesson-completion-chart" class="llms-chart"></div>';

			return $html;
		}

		public function lesson_student_table( $search ) {

			//$headers = array( 'Last', 'First', 'Enrolled', 'Completion', 'Last Lesson Completed' );

			if ( $search ) {

				$student_arrays = LLMS_Analytics::get_students( $search );

				//array_unshift($students, $headers);
			}

			$html = '<p class="llms-label">' . __( 'Enrolled Students', 'lifterlms' ) . '</p>';
			$html .= '<script>
			var students_result_large = ' . json_encode( $student_arrays['large'] ) . '
			var students_result_small = ' . json_encode( $student_arrays['small'] ) . '
			</script>';
			$html .= '<div id="table_div" class="llms-chart"></div>';

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
	 * save analytics to the database
	 *
	 * @return LLMS_Admin_analytics::save_fields
	 */
		public function save() {
			$analytics = $this->get_analytics();

			LLMS_Admin_Analytics::save_search_fields( $analytics );

		}

		/**
	 * get analytics from the database
	 *
	 * @return array
	 */
		public function output() {
			$analytics = $this->get_analytics( );

			LLMS_Admin_Analytics::output_html( $analytics );
		}

	}

endif;

return new LLMS_Analytics_Courses();
