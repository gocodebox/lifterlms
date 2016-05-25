<?php
/**
 * LifterLMS Coupon Class
 *
 * Handle all data related to a Coupon
 *
 * @package     LifterLMS/Classes
 * @category    Class
 * @author      LifterLMS
 * @since       3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Coupon {

	private $id = null;

	private $prefix = '_llms_';

	/**
	 * Constructor
	 *
	 * @param mixed  Coupon Code, Coupon Post ID, Instance of LLMS_Coupon, Instance of WP_Post
	 *
	 * @return  void
	 *
	 * @since  2.7.0
	 */
	public function __construct( $coupon = null ) {

		if ( is_string( $coupon ) ) {

			$this->id = $this->find_by_code( $coupon );
			$this->post = get_post( $this->id );

		} elseif ( is_numeric( $coupon ) ) {

			$this->id   = absint( $coupon );
			$this->post = get_post( $this->id );

		} elseif ( $coupon instanceof LLMS_Coupon ) {

			$this->id   = absint( $coupon->id );
			$this->post = $coupon->post;

		} elseif ( isset( $coupon->ID ) ) {

			$this->id   = absint( $coupon->ID );
			$this->post = $coupon;

		}

	}

	/**
	 * Getter
	 * @param  string $key key to retrieve
	 * @return mixed
	 */
	public function __get( $key ) {

		// can't update the ID
		if( 'id' === $key ) {

			$value = $this->id;

		} else {

			$value = get_post_meta( $this->id, $this->prefix . $key, true );

		}

		return apply_filters( 'llms_get_coupon_' . $key, $value, $this );

	}

	/**
	 * Isset
	 * @param  string  $key  check if a key exists in the database
	 * @return boolean
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, $this->prefix . $key );
	}

	/**
	 * Setter
	 *
	 * If an ID exists, will store data in the postmeta table
	 *
	 * @param string $key name of the property
	 * @param mixed  $val value of the property
	 *
	 * @return  void
	 */
	public function __set( $key, $val ) {

		// can't update ID
		if ( 'id' === $key ) {

			return;

		} else {

			$val = apply_filters( 'llms_set_coupon_' . $key, $val, $this );
			$this->$key = $val;

			// if we have an id, sync to the database
			if ( 'post' !== $key && $this->get_id() ) {

				update_post_meta( $this->id, $this->prefix . $key, $val );

			}

		}

	}


	/*
		  /$$$$$$              /$$     /$$
		 /$$__  $$            | $$    | $$
		| $$  \__/  /$$$$$$  /$$$$$$ /$$$$$$    /$$$$$$   /$$$$$$   /$$$$$$$
		| $$ /$$$$ /$$__  $$|_  $$_/|_  $$_/   /$$__  $$ /$$__  $$ /$$_____/
		| $$|_  $$| $$$$$$$$  | $$    | $$    | $$$$$$$$| $$  \__/|  $$$$$$
		| $$  \ $$| $$_____/  | $$ /$$| $$ /$$| $$_____/| $$       \____  $$
		|  $$$$$$/|  $$$$$$$  |  $$$$/|  $$$$/|  $$$$$$$| $$       /$$$$$$$/
		 \______/  \_______/   \___/   \___/   \_______/|__/      |_______/
	*/

	/**
	 * Get the Coupon ID
	 * @return int
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Get the discount amount
	 * @return int
	 */
	public function get_single_amount() {
		return floatval( $this->coupon_amount );
	}

	/**
	 * Get the coupon code
	 * @return string
	 */
	public function get_code() {
		return $this->post->post_title;
	}

	/**
	 * Get the description
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the discount type
	 * percent or dollar
	 * @return string
	 */
	public function get_discount_type() {
		/**
		 * Enforce a default value
		 */
		if ( empty( $this->discount_type ) ) {
			return 'percent';
		}
		return $this->discount_type;
	}

	/**
	 * Get the expiration date
	 * @return string
	 */
	public function get_expiration_date() {
		return $this->expiration_date;
	}

	/**
	 * Get the discount type for human reading
	 * and allow translation
	 * @return string
	 */
	public function get_formatted_discount_type() {
		switch( $this->get_discount_type() ) {
			case 'percent':
				return __( 'Percentage Discount', 'lifterlms' );
			break;
			case 'dollar':
				return sprintf( _x( '%s Discount', 'Dollar based coupon discount', 'lifterlm' ), get_lifterlms_currency_symbol() );
			break;
		}
	}

	/**
	 * Get the amount of the recurring first payment discount
	 * @return string
	 */
	public function get_formatted_recurring_first_payment_amount() {
		$amount = $this->get_recurring_first_payment_amount();
		if ( $amount ) {
			switch( $this->get_discount_type() ) {
				case 'percent':
					$amount += '%';
				break;
				case 'dollar':
					$amount = get_lifterlms_currency_symbol() . llms_format_decimal( $amount, 2 );
				break;
			}
		}
		return $amount;
	}

	/*
	 * Get the amount of the recurring payment discount
	 * @return string
	 */
	public function get_formatted_recurring_payments_amount() {
		$amount = $this->get_recurring_payments_amount();
		if ( $amount ) {
			switch( $this->get_discount_type() ) {
				case 'percent':
					$amount += '%';
				break;
				case 'dollar':
					$amount = get_lifterlms_currency_symbol() . llms_format_decimal( $amount, 2 );
				break;
			}
		}
		return $amount;
	}

	/**
	 * Get the amount of the recurring first payment discount
	 * @return string
	 */
	public function get_formatted_single_amount() {
		$amount = $this->get_single_amount();
		if ( $amount ) {
			switch( $this->get_discount_type() ) {
				case 'percent':
					$amount = $amount . '%';
				break;
				case 'dollar':
					$amount = get_lifterlms_currency_symbol() . llms_format_decimal( $amount, 2 );
				break;
			}
		}
		return $amount;
	}

	/**
	 * Get the WP_Post object for the related Order post
	 * @return obj   Instance of WP_Post
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Get an array of product ids that can coupon can discount
	 * @return array
	 */
	public function get_products() {
		return $this->coupon_products;
	}

	/**
	 * Get recurring first payment amount
	 * @return int
	 */
	public function get_recurring_first_payment_amount() {
		return floatval( $this->recurring_first_payment_amount );
	}

	/**
	 * Get recurring payments amount
	 * @return int
	 */
	public function get_recurring_payments_amount() {
		return floatval( $this->recurring_payments_amount );
	}

	/**
	 * Get the number of remaining uses
	 * calculated by substracting # of uses from the usage limit
	 * @return string|int
	 */
	public function get_remaining_uses() {

		$limit = $this->get_usage_limit();

		// if usage is unlimited
		if ( '' === $limit ) {

			return _x( 'Unlimited', 'Remaining coupon uses', 'lifterlms' );

		}
		// check usages against allowed uses
		else {

			return $limit - $this->get_uses();

		}

	}

	/**
	 * Get the usage limit
	 * If empty string, uses are unlimited
	 * If 0, out of uses
	 *
	 * @return int|empty string
	 */
	public function get_usage_limit() {
		return $this->usage_limit;
	}

	/**
	 * Get the number of times the coupon has been used
	 * @return int
	 */
	public function get_uses() {

		$q = new WP_Query( array(
			'meta_query' => array(
				array(
					'key' => $this->prefix . 'coupon_code',
					'value' => $this->get_code(),
				),
			),
			'post_status' => 'publish',
			'post_type' => 'llms_order',
			'posts_per_page' => 1,
			// @todo add statuses to this query, i think
		) );

		return $q->post_count;

	}


	/*
		  /$$$$$$                                /$$
		 /$$__  $$                              |__/
		| $$  \ $$ /$$   /$$  /$$$$$$   /$$$$$$  /$$  /$$$$$$   /$$$$$$$
		| $$  | $$| $$  | $$ /$$__  $$ /$$__  $$| $$ /$$__  $$ /$$_____/
		| $$  | $$| $$  | $$| $$$$$$$$| $$  \__/| $$| $$$$$$$$|  $$$$$$
		| $$/$$ $$| $$  | $$| $$_____/| $$      | $$| $$_____/ \____  $$
		|  $$$$$$/|  $$$$$$/|  $$$$$$$| $$      | $$|  $$$$$$$ /$$$$$$$/
		 \____ $$$ \______/  \_______/|__/      |__/ \_______/|_______/
		      \__/

	*/

	/**
	 * Find a coupon ID by the coupon code
	 * @param  string $code coupon code
	 * @return int|null
	 */
	public function find_by_code( $code, $dupcheck_id = 0 ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare(
			"SELECT id
			 FROM {$wpdb->posts}
			 WHERE post_title = %s
			 AND post_type = 'llms_coupon'
			 AND post_status = 'publish'
			 AND ID != %d
			 ORDER BY ID desc;
			",
			array( $code, $dupcheck_id )
		) );

	}


	/*
		 /$$    /$$          /$$ /$$       /$$             /$$     /$$
		| $$   | $$         | $$|__/      | $$            | $$    |__/
		| $$   | $$ /$$$$$$ | $$ /$$  /$$$$$$$  /$$$$$$  /$$$$$$   /$$  /$$$$$$  /$$$$$$$
		|  $$ / $$/|____  $$| $$| $$ /$$__  $$ |____  $$|_  $$_/  | $$ /$$__  $$| $$__  $$
		 \  $$ $$/  /$$$$$$$| $$| $$| $$  | $$  /$$$$$$$  | $$    | $$| $$  \ $$| $$  \ $$
		  \  $$$/  /$$__  $$| $$| $$| $$  | $$ /$$__  $$  | $$ /$$| $$| $$  | $$| $$  | $$
		   \  $/  |  $$$$$$$| $$| $$|  $$$$$$$|  $$$$$$$  |  $$$$/| $$|  $$$$$$/| $$  | $$
		    \_/    \_______/|__/|__/ \_______/ \_______/   \___/  |__/ \______/ |__/  |__/
	*/

	/**
	 * Determine if a coupon can be applied to a specific product
	 * @param  int  $product_id  WP Post ID of a LLMS Course or Membership
	 * @return boolean     true if it can be applied, false otherwise
	 */
	public function applies_to_product( $product_id ) {
		$products = $this->get_products();
		// no product restrictions
		if ( empty( $products ) ) {
			return true;
		}
		// check against the array of products
		else {
			return in_array( $product_id, $products );
		}
	}

	/**
	 * Determine whether or not a coupon exists
	 * To "exist" the instance must meet the following criteria
	 * 		+ Have an ID
	 * 		+ Be attached to a WordPress Post
	 * 		+ The post must be an `llms_coupon` post type
	 * 		+ The post must be published
	 *
	 * @return boolean   true if it exists, false otherwise
	 */
	public function exists() {
		if ( $this->get_id() && $this->post ) {
			if ( 'llms_coupon' === $this->post->post_type && 'publish' === $this->post->post_status ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Determine if a coupon has uses remaining
	 * @return boolean   true if uses are remaining, false otherwise
	 */
	public function has_remaining_uses() {
		$uses = $this->get_remaining_uses();
		if ( is_numeric( $uses ) ) {
			return ( $uses >= 1 ) ? true : false;
		}
		return true;
	}

	/**
	 * Determine if a coupon is expired
	 * @return boolean   true if expired, false otherwise
	 */
	public function is_expired() {
		// no expiration date, can't expire
		if ( ! $this->get_expiration_date() ) {
			return false;
		} else {
			$date = current_time( 'timestamp' );
			$expires = strtotime( $this->get_expiration_date(), $date );
			return $expires < $date;
		}
	}

	/**
	 * Perform all available validations and return a success or error message
	 * @param  int  $product_id  WP Post ID of a LLMS Course or Membership
	 * @return WP_Error|true     If true, the coupon is valid, if WP_Error, there was an error
	 */
	public function is_valid( $product_id ) {

		$msg = false;

		// does coupon exist?
		if ( ! $this->exists() ) {

			$msg = __( 'Coupon code not found.', 'lifterlms' );

		}
		// any uses remaining?
		elseif ( ! $this->has_remaining_uses() ) {

			$msg = __( 'This coupon has reached its usage limit and can no longer be used.', 'lifterlms' );

		}
		// expired?
		elseif ( $this->is_expired() ) {

			$msg = sprintf( __( 'This coupon expired on %s and can no longer be used.', 'lifterlms' ), $this->get_expiration_date() );

		}
		// can be applied to the submitted product?
		elseif ( ! $this->applies_to_product( $product_id ) ) {

			$msg = sprintf( __( 'This coupon cannot be used to purchase "%s".', 'lifterlms' ), get_the_title( $product_id ) );

		}

		// error encountered
		if ( $msg ) {

			$r = new WP_Error();
			$r->add( 'error', apply_filters( 'lifterlms_coupon_validation_error_message', $msg, $this ) );

		} else {

			$r = true;

		}

		return $r;

	}

}

		// $coupon = new stdClass();
		// $errors = new WP_Error();

		// $coupon->user_id = (int) get_current_user_id();
		// $coupon->product_id = $_POST['product_id'];

		// if ( empty( $coupon->user_id ) ) {
		// 	//return;
		// }

		// $coupon->coupon_code = llms_clean( $_POST['coupon_code'] );



		// if ( empty( $coupon_post ) ) {
		// 	return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> was not found.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
		// } else {
		// 	$products = get_post_meta( $coupon_post[0]->ID, '_llms_coupon_products', true );
		// 	if ( ! empty( $products ) && ! in_array( $coupon->product_id, $products )) {
		// 		return llms_add_notice( sprintf( __( "Coupon code <strong>%s</strong> can't be applied to this product.", 'lifterlms' ), $coupon->coupon_code ), 'error' );
		// 	}
		// }

		// foreach ($coupon_post as $cp) {
		// 	$coupon->id = $cp->ID;
		// }

		// //get coupon metadata
		// $coupon_meta = get_post_meta( $coupon->id );



		// if ($coupon->type == 'percent') {
		// 	$coupon->name = ($coupon->title . ': ' . $coupon->amount . '% coupon');
		// } elseif ($coupon->type == 'dollar') {
		// 	$coupon->name = ($coupon->title . ': ' . get_lifterlms_currency_symbol() . $coupon->amount . ' coupon');
		// }

		// //if coupon limit is not unlimited deduct 1 from limit
		// if (isset( $coupon->limit )) {
		// 	if ($coupon->limit !== 'unlimited') {

		// 		if ($coupon->limit <= 0) {
		// 			return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> cannot be applied to this order.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
		// 		}

		// 		//remove coupon limit
		// 		$coupon->limit = ($coupon->limit - 1);

		// 	}
		// }

		// return $coupon;
