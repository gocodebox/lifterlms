<?php
/**
 * Add, Customize, and Manage LifterLMS Coupon Post Table Columns
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Coupons class
 *
 * @since 3.0.0
 */
class LLMS_Admin_Post_Table_Coupons {

	/**
	 * Constructor
	 *
	 * @return void
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_filter( 'manage_llms_coupon_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_coupon_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
	}

	/**
	 * Add Custom Coupon Columns
	 *
	 * @param array $columns array of default columns
	 * @return  array
	 * @since  3.0.0
	 */
	public function add_columns( $columns ) {

		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => __( 'Code', 'lifterlms' ),
			'amount' => __( 'Coupon Amount', 'lifterlms' ),
			'desc'   => __( 'Description', 'lifterlms' ),
			'usage'  => __( 'Usage / Limit', 'lifterlms' ),
			'expiry' => __( 'Expiration Date', 'lifterlms' ),
		);

		return $columns;
	}


	/**
	 * Manage content of custom coupon columns
	 *
	 * @param  string $column  column key/name
	 * @param  int    $post_id WP Post ID of the coupon for the row
	 * @return void
	 */
	public function manage_columns( $column, $post_id ) {

		global $post;
		$c = new LLMS_Coupon( $post );

		switch ( $column ) {

			case 'amount':
				esc_html_e( 'Discount: ', 'lifterlms' );
				echo wp_kses( $c->get_formatted_amount(), LLMS_ALLOWED_HTML_PRICES );
				echo '<br>';

				if ( $c->has_trial_discount() ) {
					esc_html_e( 'Trial Discount: ', 'lifterlms' );
					echo wp_kses( $c->get_formatted_amount( 'trial_amount' ), LLMS_ALLOWED_HTML_PRICES );
					echo '<br>';
				}

				break;

			case 'desc':
				echo esc_html( $c->get( 'description' ) );
				break;

			case 'usage':
				echo esc_html( $c->get_uses() );
				echo ' / ';
				echo ( $c->get( 'usage_limit' ) ) ? esc_html( $c->get( 'usage_limit' ) ) : '&infin;';
				break;

			case 'expiry':
				echo $c->get( 'expiration_date' ) ? esc_html( $c->get_date( 'expiration_date', 'F d, Y' ) ) : '&ndash;';
				break;

		}
	}
}
return new LLMS_Admin_Post_Table_Coupons();
