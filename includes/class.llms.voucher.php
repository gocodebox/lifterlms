<?php
if ( ! defined( 'ABSPATH' )) { exit; }

/**
 * Voucher Class
 *
 * @author codeBOX
 * @project lifterLMS
 */
class LLMS_Voucher
{

	/**
	 * ID of the voucher
	 * This will be a LifterLMS Voucher custom post type Post ID
	 * @var int
	 */
	protected $id;


	/**
	 * Unprefixed name of the vouchers codes table
	 * @var string
	 */

	protected $codes_table_name = 'lifterlms_vouchers_codes';

	/**
	 * Unprefixed name of the product to voucher xref table
	 * @var string
	 */
	protected $product_to_voucher_table = 'lifterlms_product_to_voucher';

	/**
	 * Unprefixed name of the voucher redemptions table
	 * @var string
	 */
	protected $redemptions_table = 'lifterlms_voucher_code_redemptions';


	/**
	 * Constructor
	 * @param id
	 */
	public function __construct( $id = null ) {
		$this->id = $id;
	}


	/**
	 * Retrieve the prefixed database table name for the table where voucher codes are stored
	 * @return string
	 */
	protected function get_codes_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->codes_table_name;

	}

	/**
	 * Retrieve the prefixed database table name where voucher to product relationships are stored
	 * @return [type] [description]
	 */
	protected function get_product_to_voucher_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->product_to_voucher_table;

	}

	/**
	 * Retrieve the prefixed database table name where voucher redemptions are stored
	 * @return string
	 */
	protected function get_redemptions_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->redemptions_table;

	}



	public function get_voucher_title() {

		return get_title( $this->id );

	}



	// Get single voucher code
	public function get_voucher_by_voucher_id() {

		global $wpdb;

		$table = $this->get_codes_table_name();

		$query = "SELECT * FROM $table WHERE `voucher_id` = $this->id AND `is_deleted` = 0 LIMIT 1";
		return $wpdb->get_row( $query );
	}

	// Get single voucher code by code
	public function get_voucher_by_code( $code ) {

		global $wpdb;

		$table = $this->get_codes_table_name();
		$redeemed_table = $this->get_redemptions_table_name();

		$query = "SELECT c.*, count(r.id) as used
                  FROM $table as c
                  LEFT JOIN $redeemed_table as r
                  ON c.`id` = r.`code_id`
                  WHERE `code` = '$code' AND `is_deleted` = 0
                  GROUP BY c.id
                  LIMIT 1";
		return $wpdb->get_row( $query );
	}

	public function get_voucher_codes( $format = 'OBJECT' ) {

		global $wpdb;

		$table = $this->get_codes_table_name();
		$redeemed_table = $this->get_redemptions_table_name();

		$query = "SELECT c.*, count(r.id) as used
                  FROM $table as c
                  LEFT JOIN $redeemed_table as r
                  ON c.`id` = r.`code_id`
                  WHERE `voucher_id` = $this->id AND `is_deleted` = 0
                  GROUP BY c.id";
		return $wpdb->get_results( $query, $format );
	}

	public function get_voucher_code_by_code_id( $code_id ) {

		global $wpdb;

		$table = $this->get_codes_table_name();

		$query = "SELECT * FROM $table WHERE `id` = $code_id AND `is_deleted` = 0 LIMIT 1";
		return $wpdb->get_row( $query );
	}

	public function save_voucher_code( $data ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['created_at'] = date( 'Y-m-d H:i:s' );
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_codes_table_name(), $data );
	}

	public function update_voucher_code( $data ) {

		global $wpdb;

		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		$where = array( 'id' => $data['id'] );
		unset( $data['id'] );
		return $wpdb->update( $this->get_codes_table_name(), $data, $where );
	}

	public function delete_voucher_code( $id ) {

		global $wpdb;

		$data['updated_at'] = date( 'Y-m-d H:i:s' );
		$data['is_deleted'] = 1;

		$where = array( 'id' => $id );
		unset( $data['id'] );
		return $wpdb->update( $this->get_codes_table_name(), $data, $where );
	}

	public function check_voucher( $code ) {

		$voucher = $this->get_voucher_by_code( $code );

		if (empty( $voucher ) || $voucher->redemption_count <= $voucher->used) {
			$voucher = false;
		}

		return $voucher;
	}

	/**
	 * Attempt to redeem a voucher for a user with a code
	 * @param  string  $code    voucher code of the voucher being redeemd
	 * @param  int     $user_id user id of the redeeming user
	 * @param  boolean $notices if true, output llms notices
	 * @return mixed
	 */
	public function use_voucher( $code, $user_id, $notices = true ) {

		$voucher = $this->check_voucher( $code );

		if ( $voucher ) {

			$this->id = $voucher->voucher_id;

			// ensure the user hasn't already redeemed this voucher
			if ( $this->get_redemptions_for_code_by_user( $voucher->id, $user_id ) ) {

				if ( $notices ) {

					llms_add_notice( __( 'You have already used this voucher.', 'lifterlms' ), 'error' );

				}

				return $voucher->voucher_id;

			}

			// get products linked to the voucher
			$products = $this->get_products();

			if ( ! empty( $products ) ) {

				// loop through all of them and attempt enrollment
				foreach ( $products as $product ) {

					// if enrollment was sucessfull, create an order
					if ( llms_enroll_student( $user_id, $product ) ) {

						$checkout = LLMS()->checkout();
						$checkout->create( $user_id, $product, 'Voucher' );

					}

				}

				do_action( 'llms_voucher_used', $voucher->id, $user_id );

				if ( $notices ) {

					llms_add_notice( __( 'Voucher redeemed successfully!', 'lifterlms' ) );

				}

				// use voucher code
				$data = array(
					'user_id' => $user_id,
					'code_id' => $voucher->id,
				);
				$this->save_redeemed_code( $data );

			}

		} else {

			if ($notices) {

				llms_add_notice( __( 'Voucher could not be used. Please check that you have a valid voucher.', 'lifterlms' ), 'error' );

			}

		}

		return $voucher;
	}

	/**
	 * Redeemed Codes
	 */
	public function get_redeemed_codes( $format = 'OBJECT' ) {

		global $wpdb;

		$table = $this->get_codes_table_name();
		$redeemed_table = $this->get_redemptions_table_name();
		$users_table = $wpdb->prefix . 'users';

		$query = "SELECT r.`id`, c.`id` as code_id, c.`voucher_id`, c.`code`, c.`redemption_count`, r.`user_id`, u.`user_email`, r.`redemption_date`
                  FROM $table as c
                  JOIN $redeemed_table as r
                  ON c.`id` = r.`code_id`
                  JOIN $users_table as u
                  ON r.`user_id` = u.`ID`
                  WHERE c.`is_deleted` = 0 AND c.`voucher_id` = $this->id";

		return $wpdb->get_results( $query, $format );
	}

	/**
	 * Retrieve the number of times a voucher was redeemed by a specific user
	 * Hint, it should always be 1 or 0
	 *
	 * @param  int $code_id Voucher Code ID from wp_lifterlms_vouchers_codes table
	 * @param  int $user_id User ID from wp_users tables
	 * @return int
	 */
	public function get_redemptions_for_code_by_user( $code_id, $user_id ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare(
			"SELECT count(id) FROM {$this->get_redemptions_table_name()} WHERE user_id = %d and code_id = %d",
			array( $user_id, $code_id )
		) );

	}

	public function save_redeemed_code( $data ) {

		global $wpdb;

		$data['redemption_date'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_redemptions_table_name(), $data );
	}

	/**
	 * Product 2 Voucher
	 */
	public function get_products() {

		global $wpdb;

		$table = $this->get_product_to_voucher_table_name();

		$query = "SELECT * FROM $table WHERE `voucher_id` = $this->id";

		$results = $wpdb->get_results( $query );

		$products = array();
		if ( ! empty( $results )) {
			foreach ($results as $item) {
				$products[] = intval( $item->product_id );
			}
		}

		return $products;
	}

	public function is_product_to_voucher_link_valid( $code, $product_id ) {

		$voucher = $this->check_voucher( $code );

		if ($voucher) {
			$this->id = $voucher->voucher_id;

			$products = $this->get_products();

			if ( ! empty( $products ) && in_array( $product_id, $products )) {
				return true;
			}
		}

		return false;
	}

	public function is_code_duplicate( $codes ) {

		global $wpdb;
		$table = $this->get_codes_table_name();

		$codes_as_string = join( '","' , $codes );

		$query = 'SELECT code
                  FROM ' . $table . '
                  WHERE code IN ("' . $codes_as_string . '")
                  AND voucher_id != ' . $this->id;
		$codes = $wpdb->get_results( $query, ARRAY_A );

		if (count( $codes )) {
			return $codes;
		}

		return false;
	}

	public function save_product( $product_id ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['product_id'] = $product_id;

		return $wpdb->insert( $this->get_product_to_voucher_table_name(), $data );
	}

	public function delete_products() {

		global $wpdb;

		return $wpdb->delete( $this->get_product_to_voucher_table_name(), array( 'voucher_id' => $this->id ) );
	}
}
