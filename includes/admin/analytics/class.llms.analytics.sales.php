<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin analytics Page, sales Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Analytics_Sales extends LLMS_Analytics_Page {

	/**
	* Constructor
	*
	* executes analytics tab actions
	*/
	public function __construct() {
		$this->id    = 'sales';
		$this->label = __( 'Sales', 'lifterlms' );

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
		$search = LLMS()->session->get( 'llms_analytics_sales' );

		$title = __( 'Sales Analytics', 'lifterlms' );

		//search form
		$html = $this->search_form();

		if ( $search ) {

			//WIDGET ROW
			$html .= '<div class="llms-widget-row">';
			//total sold
			$html .= self::quarter_width_widget( $this->total_sales( $search ) );

			//total sales
			$html .= self::quarter_width_widget( $this->total_units_sold( $search ) );

			//total coupons used
			$html .= self::quarter_width_widget( $this->total_coupons_used( $search ) );

			//total coupon amount
			$html .= self::quarter_width_widget( $this->total_coupon_amount( $search ) );

			$html .= '</div>'; //end widget row

			//sales volumn line chart
			$html .= self::full_width_widget( $this->sales_chart( $search ) );

		}

		//return contents
		return $this->get_page_contents( $title, $html );
	}

	/** Builds Search Form */
	public function search_form() {

		//get session data if it exists
		$search = LLMS()->session->get( 'llms_analytics_sales' );

		$product_id = isset( $search->product_id ) ? $search->product_id : '';
		$date_filter = isset( $search->date_filter ) ? $search->date_filter : 'none';
		$start_date = isset( $search->start_date ) ? LLMS_Date::pretty_date( $search->start_date ) : '';
		$end_date = isset( $search->end_date ) ? LLMS_Date::pretty_date( $search->end_date ) : '';
		$inc_coupons = isset( $search->exclude_coupons ) ? $search->exclude_coupons : false;

		//get products
		$products = LLMS_Analytics::get_products();
		//get date filters
		$date_filters = LLMS_Date::date_filters();

		//var_dump( $search);

		//start building html
		$html = '<div class="llms-search-form-wrapper">';

		//Product Select ( Courses and Memberships )
		$html .= '<div class="llms-select">';
		$html .= '<label>' . __( 'Select a product', 'lifterlms' ) . '</label>';
		$html .= '<select id="llms-product-select" name="llms_product_select" class="chosen-select-width">';

		//all products option
		$html .= '<option value="all_products" ' . ( 'all_products' == $product_id ? 'selected' : '' ) . '>' . __( 'All Products', 'lifterlms' ) . '</option>';
		//$html .= '<option value="all_courses" ' . ( $product_id == 'all_courses' ? 'selected' : '' ) . '>' . __( 'All Courses', 'lifterlms' ) . '</option>';
		//$html .= '<option value="all_memberships" ' . ( $product_id == 'all_memberships' ? 'selected' : '' ) . '>' . __( 'All Memberships', 'lifterlms' ) . '</option>';

		//loop through posts
		if ( $products ) {
			$html .= '<optgroup label="' . __( 'Courses', 'lifterlms' ) . '">';
			foreach ( $products as $key => $product ) {
				if ( 'course' === $product->post_type ) {
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
				if ( 'llms_membership' === $product->post_type ) {
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

		//Date filters ( Courses and Memberships )
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

		// Removing this option as it isnt needed right now
		// // filter checkboxes
		// $html .= '<div class="llms-filter-options">';

		// // Exclude Coupons
		// $html .= '<div class="llms-checkbox">';
		// $html .= '<input type="checkbox" name="llms_exclude_coupons" ' .  ( $inc_coupons === 'on' ? 'checked' : '' ) . '>' . __( 'Exclude Coupons', 'lifterlms' );
		// $html .= '</div>';

		// $html .= '</div>'; //end filter options

		$html .= wp_nonce_field( 'search_analytics_sales', '_wpnonce', true, false );
		$html .= '<input type="hidden" name="action" value="llms-analytics-sales" />';

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

		$headers = array( 'Date', 'Sales' );

		if ( $search ) {

			$total_sold_by_day = LLMS_Analytics::get_total_sold_by_day( $search->results, $search->start_date, $search->end_date );

			if ( ! empty( $total_sold_by_day ) ) {
				array_unshift( $total_sold_by_day, $headers );
			} else {
				$total_sold_by_day = array();
			}
		} else {
			$total_sold_by_day = array();
		}

		$html = '<p class="llms-label">' . __( 'Sales Volume', 'lifterlms' ) . '</p>';

		$html .= '<script>var myJson = ' . json_encode( $total_sold_by_day ) . '</script>';

		$html .= '<div id="chart_div" class="llms-chart"></div>';

		return $html;
	}

	/**
	 * Total sales html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_sales( $search ) {

		$html = '<p class="llms-label">' . __( 'Total Sold', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Number::format_money_no_decimal( LLMS_Analytics::get_total_sales( $search->results ) ) . '</h1>';

		return $html;
	}

	/**
	 * Total units sold html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_units_sold( $search ) {

		$html = '<p class="llms-label">' . __( 'Total Sales', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_units_sold( $search->results ) . '</h1>';

		return $html;
	}

	/**
	 * Total coupons used html block
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_coupons_used( $search ) {

		$html = '<p class="llms-label">' . __( 'Coupons Used', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Analytics::get_total_coupons_used( $search->results ) . '</h1>';

		return $html;
	}

	/**
	 * Total coupon $ used
	 * @param  [array] $search [array of order objects]
	 * @return [html]
	 */
	public function total_coupon_amount( $search ) {

		$html = '<p class="llms-label">' . __( 'Total Coupons', 'lifterlms' ) . '</p>';
		$html .= '<h1>' . LLMS_Number::format_money_no_decimal( LLMS_Analytics::get_total_coupon_amount( $search->results ) ) . '</h1>';

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

return new LLMS_Analytics_Sales();
