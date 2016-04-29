<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* WooCommerce Integration
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Integration_Woocommerce
{

	public $id = 'wc';
	public $title = 'WooCommerce';

	/**
	 * Constructor
	 * Ensure Integration is enabled and available
	 * Add actions and filters
	 */
	public function __construct() {

		$this->available = $this->is_available();
		$this->installed = $this->is_installed();

		$this->enabled = ( $this->available && $this->installed ) ? true : false;

		if ( $this->enabled ) {

			add_action( 'woocommerce_order_status_completed', array( $this, 'process_order' ) );

			// add llms content templates to the WC my account page template
			add_action( 'woocommerce_after_my_account', array( $this, 'wc_my_courses_content' ) );

			// redirect LLMS registration & login to the WC Account page
			add_filter( 'lifterlms_registration_redirect', array( $this, 'llms_login_redirect' ), 10, 1 );

			// redirect llms checkout requests to the wc cart
			add_action( 'wp', array( $this, 'llms_checkout_redirect' ) );

		}

	}



	/**
	 * Retrieve a WC Product Instance by
	 * @param  string $sku  product sku to search by
	 * @return mixed        false if not found OR a WC_Product instance
	 */
	function get_wc_product_by_sku( $sku ) {

		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $wpdb->posts AS p
			 LEFT JOIN $wpdb->postmeta AS pm ON p.id = pm.post_id
			 WHERE pm.meta_key = '_sku' AND pm.meta_value = '%s' AND p.post_type = 'product'
			 LIMIT 1",
			$sku
		) );

		if ( is_numeric( $id ) ) {
			return new WC_Product( $id );
		} else {
			return false;
		}

	}


	/**
	 * Retrieve a LLMS Product (course or membership) ID by sku
	 * @param  string $sku    sku to search by
	 * @return mixed          false if none found, post_id if found
	 */
	function get_llms_product_by_sku( $sku ) {

		$r = new WP_Query( array(
			'meta_query' => array(
			array(
					'compare' => '=',
					'key' => '_sku',
					'value' => $sku,
				),
			),
			'post_type' => array( 'course', 'llms_membership' ),
			'posts_per_page' => 1,
		) );

		if ( $r->have_posts() ) {

			return $r->posts[0]->ID;

		}

		return false;

	}


	/**
	 * Checks checks if the LLMS WooCommerce integration is enabled
	 * @return boolean
	 */
	public function is_available() {

		return ( get_option( 'lifterlms_woocommerce_enabled' ) == 'yes' ) ? true : false;

	}


	/**
	 * Checks if the WooCommerce plugin is installed & activated
	 * @return boolean
	 */
	public function is_installed() {

		return ( class_exists( 'WooCommerce' ) ) ? true : false;

	}


	/**
	 * Redirect applicable traffic to the LifterLMS Checkout page to the WooCommerce cart
	 *
	 * 		When a user requests the LifterLMS Checkout page, will locate a WC Product via LifterLMS Product SKU Matching
	 * 		If a WC Product is found, it will add it to the WC Cart and redirect to the WC cart
	 *
	 * @return void
	 */
	public function llms_checkout_redirect() {

		// only run this if we're on the is_llms_checkout page
		if ( ! is_llms_checkout() && ! is_llms_account_page() ) {
			return;
		}

		if ( ! isset( $_GET['product-id'] ) ) {
			return;
		}

		$type = get_post_type( $_GET['product-id'] );

		$course = new LLMS_Course( $_GET['product-id'] );
		$sku = $course->get_sku();

		if ( $sku ) {

			$product = $this->get_wc_product_by_sku( $sku );

			if ( $product ) {

				WC()->cart->add_to_cart( $product->id, 1 );

				wp_redirect( wc_get_page_permalink( 'cart' ) );
				exit;

			}

		}

	}

	/**
	 * Overrides LifterLMS account login redirect to send users to WooCommerce account page.
	 *
	 * @param  string $redirect_to 	url of page to redirect to
	 * @return string
	 */
	function llms_login_redirect( $redirect_to ) {

		$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );

		if ( $myaccount_page_id ) {
			$myaccount_page_url = get_permalink( $myaccount_page_id );
		}

	     $redirect_to = $myaccount_page_url;

	     return $redirect_to;
	}


	/**
	 * Creates and Processes LifterLMS orders when WooCommerce order is marked complete
	 *
	 *    1. loops through items in the WC Order
	 *    2. locates llms products by wc product sku
	 *    3. creates an LLMS order for each matched item
	 *    4. enrolls the purchaser in the course or membership
	 *
	 * @param  int $order_id [ID of the WooCommerce order]
	 * @return void
	 */
	public function process_order( $order_id ) {
		global $post;

		$wc_order = new WC_Order( $order_id );
		$user_id = $wc_order->get_user_id();

		// if no user id exists we do nothing. Gotta have a user to assign the course to.
		if ( empty( $user_id ) ) {
			return;
		}

		foreach ( $wc_order->get_items() as $item ) {
			$product = new WC_Product( $item['product_id'] );
			$sku = $product->get_sku();

			$llms_product_id = $this->get_llms_product_by_sku( $sku );

			// continue if no associated llms product
			if ( ! $llms_product_id ) {
				continue;
			}

			//check if user is already a student in the course
			$person = new LLMS_Person;
			$postmetas = $person->get_user_postmeta_data( $user_id, $llms_product_id );

			$enrolled = false;
			if ( isset( $postmetas['_status'] ) ) {

				$enrolled = ( $postmetas['_status']->meta_value == 'Enrolled' ) ? true : false;

			}

			// if user is enrolled, continue
			if ( $enrolled ) {
				continue;
			}

			// create the order
			$llms_order = new stdClass();
			$llms_order->user_id         = $user_id;
			$llms_order->payment_method	 = 'WooCommerce';
			$llms_order->payment_type    = 'woocommerce';
			$llms_order->product_title	 = $item['name'];
			$llms_order->product_price	 = $item['line_total'];
			$llms_order->order_completed = 'yes';
			$llms_order->payment_option  = 'single';
			$llms_order->total 			 = $item['line_total'];
			$llms_order->currency 		 = $wc_order->get_order_currency();
			$llms_order->product_id 	 = $llms_product_id;
			$llms_order->product_sku 	 = $sku;

			$llms_checkout = LLMS()->checkout();
			$llms_checkout->process_order( $llms_order );
			$llms_checkout->update_order( $llms_order );

		}

	}



	/**
	 * Add some LifterLMS content to the WC My Account Page
	 * @return void
	 */
	public function wc_my_courses_content() {

		llms_get_template( 'myaccount/my-courses.php' );
		llms_get_template( 'myaccount/my-certificates.php' );
		llms_get_template( 'myaccount/my-achievements.php' );

		if ( get_option( 'lifterlms_enable_myaccount_memberships_list', 'no' ) === 'yes' ) {

			llms_get_template( 'myaccount/my-memberships.php' );

		}

	}

}
