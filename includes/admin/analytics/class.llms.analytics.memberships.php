<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Admin analytics Page, sales Tab
 *
 * @author codeBOX
 * @project lifterLMS
 *
 * @deprecated 3.35.0
 */
class LLMS_Analytics_Memberships extends LLMS_Analytics_Page {

	/**
	 * Constructor
	 *
	 * executes analytics tab actions
	 */
	public function __construct() {
		$this->id    = 'memberships';
		$this->label = __( 'Memberships', 'lifterlms' );

		add_filter( 'lifterlms_analytics_tabs_array', array( $this, 'add_analytics_page' ), 20 );
		add_action( 'lifterlms_analytics_' . $this->id, array( $this, 'output' ) );
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
		$search = LLMS()->session->get( 'llms_analytics_membership' );
		// var_dump( $search);
		$title = __( 'Membership Analytics', 'lifterlms' );

		// search form
		$html = $this->search_form();

		if ( $search ) {

			// WIDGET ROW
			$html .= '<div class="llms-widget-row">';
			// total Members ever
			$html .= self::quarter_width_widget( $this->total_members_all_time( $search ) );

			// total currently enrolled members
			$html .= self::quarter_width_widget( $this->total_current_members( $search ) );

			// membership retention
			$html .= self::quarter_width_widget( $this->membership_retention( $search ) );

			// Total Expired Members
			$html .= self::quarter_width_widget( $this->expired_members( $search ) );

			$html .= '</div>'; // end widget row

			// sales volume line chart
			$html .= self::full_width_widget( $this->sales_chart( $search ) );

			if ( 'all_memberships' !== $search->product_id ) {
				// $html .= self::full_width_widget( $this->lesson_completion_chart( $search ) );
				$html .= self::full_width_widget( $this->membership_member_table( $search ) );
			}
		}

		// return contents
		return $this->get_page_contents( $title, $html );
	}

	/** Builds Search Form */
	public function search_form() {

		// get session data if it exists
		$search = LLMS()->session->get( 'llms_analytics_membership' );

		$product_id  = isset( $search->product_id ) ? $search->product_id : '';
		$date_filter = isset( $search->date_filter ) ? $search->date_filter : 'none';
		$start_date  = isset( $search->start_date ) ? LLMS_Date::pretty_date( $search->start_date ) : '';
		$end_date    = isset( $search->end_date ) ? LLMS_Date::pretty_date( $search->end_date ) : '';

		// get products
		$products = LLMS_Analytics::get_products();
		// get date filters
		$date_filters = LLMS_Date::date_filters();

		// start building html
		$html = '<div class="llms-search-form-wrapper">';

		// Product Select ( Memberships )
		$html .= '<div class="llms-select">';
		$html .= '<label>' . __( 'Select a Membership', 'lifterlms' ) . '</label>';
		$html .= '<select id="llms-product-select" name="llms_product_select" class="chosen-select-width">';

		// all products option
		$html .= '<option value="all_memberships" ' . ( 'all_memberships' == $product_id ? 'selected' : '' ) . '>' . __( 'All Memberships', 'lifterlms' ) . '</option>';

		// loop through posts
		if ( $products ) {
			foreach ( $products as $key => $product ) {
				if ( 'llms_membership' === $product->post_type ) {
						$html .= '<option value="' . $product->ID . '"
						' . ( $product_id == $product->ID ? 'selected' : '' ) . '>
						' . $product->post_title . '</option>';
				}
			}
		}

		$html .= '</select>';
		$html .= '</div>';

		// Date filters
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

		// start date
		$html .= '<div class="llms-filter-options date-filter">';
		$html .= '<div class="llms-date-select">';
		$html .= '<label>' . __( 'Start date', 'lifterlms' ) . '</label>';
		$html .= '<input type="text" name="llms-start-date" class="llms-date-range-select-start" value="' . $start_date . '">';
		$html .= '</div>';

		// end date
		$html .= '<div class="llms-date-select">';
		$html .= '<label>' . __( 'End date', 'lifterlms' ) . '</label>';
		$html .= '<input type="text" name="llms-end-date" class="llms-date-range-select-end" value="' . $end_date . '">';
		$html .= '</div>';
		$html .= '</div>'; // end date filters

		$html .= wp_nonce_field( 'search_analytics_membership', '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="llms-analytics-membership" />';

		// search button
		$html .= '<div class="llms-search-button">';
		// $html .= '<input type="submit" name="llms_search" class="button button-primary" id="llms_analytics_search" value="Filter Results" />';
		$html .= get_submit_button(
			'Filter Results',
			'primary',
			'llms_search',
			true,
			array(
				'id' => 'llms_analytics_search',
			)
		);
		$html .= '</div>';

		$html .= '</div>';

		return $html;

	}



	public function sales_chart( $search ) {

		$headers = array( 'Date' );

		if ( $search && isset( $search->members ) ) {

			// add each course name to headers
			if ( ! empty( $search->memberships ) ) {

				foreach ( $search->memberships as $membership ) {
					array_push( $headers, $membership->post_title );
				}

				$total_members_by_day = LLMS_Analytics::get_total_enrolled_by_day( $search );

				array_unshift( $total_members_by_day, $headers );

			}
		} else {
			$total_members_by_day = array();
		}

		$html  = '<p class="llms-label">' . __( 'Membership Enrollment by Day', 'lifterlms' ) . '</p>';
		$html .= '<script>var enrolled_members = ' . json_encode( $total_members_by_day ) . '</script>';
		$html .= '<div id="enrolled_members_chart" class="llms-chart"></div>';

		return $html;
	}

	public function lesson_completion_chart( $search ) {

		$headers = array( 'Lesson', 'Completion Percentage' );

		if ( $search ) {

			$lesson_completion_percent = LLMS_Analytics::get_lesson_completion_avg( $search );

			array_unshift( $lesson_completion_percent, $headers );
		}

		$html  = '<p class="llms-label">' . __( 'Lesson Completion Percentage', 'lifterlms' ) . '</p>';
		$html .= '<script>var lesson_completion_percent = ' . json_encode( $lesson_completion_percent ) . '</script>';
		$html .= '<div id="lesson-completion-chart" class="llms-chart"></div>';

		return $html;
	}

	public function membership_member_table( $search ) {

		// $headers = array( 'Last', 'First', 'Enrolled', 'Completion', 'Last Lesson Completed' );

		if ( $search ) {

			$members_arrays = LLMS_Analytics::get_members( $search );

			// array_unshift($students, $headers);
		}

		$html  = '<p class="llms-label">' . __( 'Members', 'lifterlms' ) . '</p>';
		$html .= '<script>
				var members_result_large = ' . json_encode( $members_arrays['large'] ) . '
				var members_result_small = ' . json_encode( $members_arrays['small'] ) . '
			</script>';
		$html .= '<div id="members_table" class="llms-chart"></div>';

		return $html;

	}

	/**
	 * Total sales html block
	 *
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_members_all_time( $search ) {
		$total = isset( $search->members ) ? LLMS_Analytics::get_total_users( $search->members ) : '0';
		$html  = '<p class="llms-label">' . __( 'All Members', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $total . '</h1>';

		return $html;
	}

	/**
	 * Total units sold html block
	 *
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_current_members( $search ) {
		$total = isset( $search->members ) ? LLMS_Analytics::get_total_current_enrolled_users( $search->members ) : '0';
		$html  = '<p class="llms-label">' . __( 'Current Members', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $total . '</h1>';

		return $html;
	}

	/**
	 * Total coupons used html block
	 *
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function membership_retention( $search ) {
		$total = isset( $search->members ) ? LLMS_Number::whole_number( LLMS_Analytics::get_membership_retention( $search->members ) ) : '0';
		$html  = '<p class="llms-label">' . __( 'Retention %', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $total . '%</h1>';

		return $html;
	}

	/**
	 * Total coupon $ used
	 *
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function expired_members( $search ) {

		$total = isset( $search->members ) ? LLMS_Analytics::get_total_current_expired_users( $search->members ) : '0';
		$html  = '<p class="llms-label">' . __( 'Expired Members', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . $total . '</h1>';

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
		$analytics = $this->get_analytics();

			LLMS_Admin_Analytics::output_html( $analytics );
	}

}

return new LLMS_Analytics_Memberships();
