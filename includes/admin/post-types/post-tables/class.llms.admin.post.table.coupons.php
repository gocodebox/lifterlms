<?php
/**
 * Add, Customize, and Manage LifterLMS Coupon Post Table Columns
 *
 * @since  3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class LLMS_Admin_Post_Table_Coupons {

	/**
	 * Constructor
	 * @return  void
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_filter( 'manage_llms_coupon_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_coupon_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

	}

	/**
	 * Add Custom Coupon Columns
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
	 * @param  string $column  column key/name
	 * @param  int $post_id WP Post ID of the coupon for the row
	 * @return void
	 */
	public function manage_columns( $column, $post_id ) {

		global $post;
		$c = new LLMS_Coupon( $post );

		switch ( $column ) {

			case 'amount':

				if ( $c->get_single_amount() ) {
					_e( 'Single Payments: ', 'lifterlms' );
					echo $c->get_formatted_single_amount();
					echo '<br>';
				}

				if ( $c->get_recurring_first_payment_amount() ) {
					_e( 'Recurring First Payment: ', 'lifterlms' );
					echo $c->get_formatted_recurring_first_payment_amount();
					echo '<br>';
				}

				if ( $c->get_recurring_payments_amount() ) {
					_e( 'Recurring Payments: ', 'lifterlms' );
					echo $c->get_formatted_recurring_payments_amount();
				}

			break;

			case 'desc':
				echo $c->get_description();
			break;

			case 'usage':
				echo $c->get_uses();
				echo ' / ';
				echo ( '' !== $c->get_usage_limit() ) ? $c->get_usage_limit() : '&infin;';
			break;

			case 'expiry':
				echo $c->get_expiration_date() ? $c->get_expiration_date() : '&ndash;';
			break;

		}

	}

}
return new LLMS_Admin_Post_Table_Coupons();
