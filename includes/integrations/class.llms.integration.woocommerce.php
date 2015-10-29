<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* WooCommerce Integration
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Integration_Woocommerce {
	public $id = 'wc';
	public $title = 'WooCommerce';

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->available = $this->is_available();
		$this->installed = $this->is_installed();

		$this->enabled = ($this->available && $this->installed) ? true : false;

		if ($this->enabled) {
			add_action('woocommerce_order_status_completed',array($this,'process_order'));
			add_action('woocommerce_after_my_account',array($this,'my_courses_content'));

			add_filter( 'lifterlms_registration_redirect',array($this, 'woocommerce_login_redirect'), 10, 1);
		}
		
	}

	/**
	 * Checks checks if the LLMS WooCommerce integration is enabled
	 * @return boolean
	 */
	public function is_available() {
		if(get_option('lifterlms_woocommerce_enabled') == 'yes') {
			return true;
		}
		return false;
	}


	/**
	 * Checks if the WooCommerce plugin is installed & activated
	 * @return boolean
	 */
	public function is_installed() {

		if(class_exists('WooCommerce')) {
			return true;
		}
		return false;
	}

	public function my_courses_content() {
		llms_get_template( 'myaccount/my-courses.php' );
		llms_get_template( 'myaccount/my-certificates.php' );
		llms_get_template( 'myaccount/my-achievements.php' );
	}



	/**
	 * Overrides lifterLMS account login redirect to send users to WooCommerce account page. 
	 * 
	 * @param  [type] $redirect_to [url of page to redirect to]
	 * 
	 * @return string $redirect_to
	 */
	function woocommerce_login_redirect($redirect_to) {
		$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );

		if ( $myaccount_page_id ) {
			$myaccount_page_url = get_permalink( $myaccount_page_id );
		}

	     $redirect_to = $myaccount_page_url;

	     return $redirect_to;
	}

	/**
	 * Processes lifterLMS order when WooCommerce order is marked complete
	 * 
	 * @param  int $order_id [ID of the WooCommerce order]
	 * 
	 * @return void
	 */
	public function process_order($order_id) {
		global $post;

		$wc_order = new WC_Order( $order_id );
		$items = $wc_order->get_items();


		$order = new stdClass();

		foreach ( $items as $item ) {

			$wc_product_id    		= $item['product_id']; 

			$order->payment_method	= 'woocommerce';
			$order->product_title	= $item['name']; 
			$order->product_price	= $item['line_total']; //$_POST['product_price'];
			

			$order->order_completed = 'yes';
			$order->total 			= $item['line_total'];
			$order->currency 		= get_lifterlms_currency();

		}

		//get user id
		$wc_order_meta = get_post_meta($order_id);
		$order->user_id     		= $wc_order_meta['_customer_user'][0];

		//if no user id exists we do nothing. Gotta have a user to assign the course to. 
		if (empty($order->user_id)) {

			return false;
		}

		//get postmeta for product
		$product = get_post_meta($wc_product_id);
		$wc_sku = $product['_sku'][0];//$_POST['product_sku'];

		$args = array(
		  'posts_per_page' => 1,
		  'post_type' => 'llms_membership',
		  'meta_query'  => array(
		    	array(
		      		'key' => '_sku',
		      		'value' => $wc_sku,
		      		'compare' => '='
	    		)
	  		)
		);
		$memberships = get_posts($args);

		if ($memberships)
		{
			foreach ($memberships as $membership)
			{
				$sku = get_post_meta($membership->ID, '_sku', true);
				$order->product_id 		= $membership->ID;
			    $order->product_sku 	= $sku;
			}
		}
		else
		{
			$args = array(
			  'posts_per_page' => 1,
			  'post_type' => 'course',
			  'meta_query'  => array(
			    	array(
			      		'key' => '_sku',
			      		'value' => $wc_sku,
			      		'compare' => '='
		    		)
		  		)
			);
			$courses = get_posts($args);

			foreach ($courses as $course)
			{
				$sku = get_post_meta($course->ID, '_sku', true);
				$order->product_id 		= $course->ID;
			    $order->product_sku 	= $sku;
                $order->payment_type    = 'woocommerce';
                $order->payment_option  = 'single';
			}
		}

		// exit if there is not matching sku
		if ( empty($order->product_sku) || empty($order->product_id)) {

			return false;
		}

		//check if user is already a student in the course
		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( $order->user_id, $order->product_id );

		$user_previously_enrolled = false;
		if ( isset($user_postmetas['_status']) ) {
			$user_previously_enrolled = true ? $user_postmetas['_status']->meta_value == 'Enrolled' : false;
	
		}

		//if there is an sku match and the user isn't already enrolled then create the order
		if ( ! $user_previously_enrolled ) {

			$lifterlms_checkout = LLMS()->checkout();
			$lifterlms_checkout->process_order($order);
			$lifterlms_checkout->update_order($order);

			do_action( 'lifterlms_order_process_success', $order );
		}

	}

}
