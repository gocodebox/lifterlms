<?php
/**
 * Voucher Class
 *
 * @since    2.0.0
 * @version  3.27.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Voucher class,
 */
class LLMS_Voucher {

	// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	/**
	 * ID of the voucher
	 * This will be a LifterLMS Voucher custom post type Post ID
	 *
	 * @var      int
	 */
	protected $id;


	/**
	 * Unprefixed name of the vouchers codes table
	 *
	 * @var string
	 */
	protected $codes_table_name = 'lifterlms_vouchers_codes';

	/**
	 * Unprefixed name of the product to voucher xref table
	 *
	 * @var string
	 */
	protected $product_to_voucher_table = 'lifterlms_product_to_voucher';

	/**
	 * Unprefixed name of the voucher redemptions table
	 *
	 * @var string
	 */
	protected $redemptions_table = 'lifterlms_voucher_code_redemptions';


	/**
	 * Constructor
	 *
	 * @param id
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function __construct( $id = null ) {
		$this->id = $id;
	}


	/**
	 * Retrieve the prefixed database table name for the table where voucher codes are stored
	 *
	 * @return string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	protected function get_codes_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->codes_table_name;

	}

	/**
	 * Retrieve the prefixed database table name where voucher to product relationships are stored
	 *
	 * @return [type] [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	protected function get_product_to_voucher_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->product_to_voucher_table;

	}

	/**
	 * Retrieve the prefixed database table name where voucher redemptions are stored
	 *
	 * @return string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	protected function get_redemptions_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->redemptions_table;

	}


	/**
	 * Get voucher title
	 *
	 * @return   string
	 * @since    2.0.0
	 * @version  3.6.2
	 */
	public function get_voucher_title() {

		return get_the_title( $this->id );

	}



	/**
	 * Get a single voucher code by id
	 *
	 * @return   obj
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_voucher_by_voucher_id() {

		global $wpdb;

		$table = $this->get_codes_table_name();

		$query = "SELECT * FROM $table WHERE `voucher_id` = $this->id AND `is_deleted` = 0 LIMIT 1";
		return $wpdb->get_row( $query );
	}

	/**
	 * Get a single voucher code by string
	 *
	 * @param    string $code  voucher code string
	 * @return   obj
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_voucher_by_code( $code ) {

		global $wpdb;

		$table          = $this->get_codes_table_name();
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

	/**
	 * Get a list of voucher codes
	 *
	 * @param    string $format  [description]
	 * @return   [type]              [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_voucher_codes( $format = 'OBJECT' ) {

		global $wpdb;

		$table          = $this->get_codes_table_name();
		$redeemed_table = $this->get_redemptions_table_name();

		$query = "SELECT c.*, count(r.id) as used
                  FROM $table as c
                  LEFT JOIN $redeemed_table as r
                  ON c.`id` = r.`code_id`
                  WHERE `voucher_id` = $this->id AND `is_deleted` = 0
                  GROUP BY c.id";
		return $wpdb->get_results( $query, $format );
	}

	/**
	 * [get_voucher_code_by_code_id description]
	 *
	 * @param    [type] $code_id  [description]
	 * @return   [type]               [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_voucher_code_by_code_id( $code_id ) {

		global $wpdb;

		$table = $this->get_codes_table_name();

		$query = "SELECT * FROM $table WHERE `id` = $code_id AND `is_deleted` = 0 LIMIT 1";
		return $wpdb->get_row( $query );
	}

	/**
	 * [save_voucher_code description]
	 *
	 * @param    [type] $data  [description]
	 * @return   [type]            [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function save_voucher_code( $data ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['created_at'] = date( 'Y-m-d H:i:s' );
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_codes_table_name(), $data );
	}

	/**
	 * [update_voucher_code description]
	 *
	 * @param    [type] $data  [description]
	 * @return   [type]            [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function update_voucher_code( $data ) {

		global $wpdb;

		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		$where = array(
			'id' => $data['id'],
		);
		unset( $data['id'] );
		return $wpdb->update( $this->get_codes_table_name(), $data, $where );
	}

	/**
	 * [delete_voucher_code description]
	 *
	 * @param    [type] $id  [description]
	 * @return   [type]          [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function delete_voucher_code( $id ) {

		global $wpdb;

		$data['updated_at'] = date( 'Y-m-d H:i:s' );
		$data['is_deleted'] = 1;

		$where = array(
			'id' => $id,
		);
		unset( $data['id'] );
		return $wpdb->update( $this->get_codes_table_name(), $data, $where );
	}

	/**
	 * Determine if a voucher is valid
	 *
	 * @param    string $code  voucher code
	 * @return   WP_Error|object     WP_Error if invalid or not redeemable OR a voucher data object
	 * @since    2.0.0
	 * @version  3.0.0
	 */
	public function check_voucher( $code ) {

		$voucher = $this->get_voucher_by_code( $code );

		if ( empty( $voucher ) ) {

			return new WP_Error( 'not-found', sprintf( __( 'Voucher code "%s" could not be found.', 'lifterlms' ), $code ) );

		} elseif ( $voucher->redemption_count <= $voucher->used ) {

			return new WP_Error( 'max', sprintf( __( 'Voucher code "%s" has already been redeemed the maximum number of times.', 'lifterlms' ), $code ) );

		} elseif ( '1' === $voucher->is_deleted ) {

			return new WP_Error( 'deleted', sprintf( __( 'Voucher code "%s" is no longer valid.', 'lifterlms' ), $code ) );

		}

		return $voucher;
	}

	/**
	 * Attempt to redeem a voucher for a user with a code
	 *
	 * @param  string $code     voucher code of the voucher being redeemed
	 * @param  int    $user_id  user id of the redeeming user
	 * @return bool|WP_Error     true on success or WP_Error on failure
	 * @since    2.0.0
	 * @version  3.27.0
	 */
	public function use_voucher( $code, $user_id ) {

		$code = sanitize_text_field( $code );

		$voucher = $this->check_voucher( $code );

		if ( ! is_wp_error( $voucher ) ) {

			$this->id = $voucher->voucher_id;

			// ensure the user hasn't already redeemed this voucher
			if ( $this->get_redemptions_for_code_by_user( $voucher->id, $user_id ) ) {

				return new WP_Error( 'error', __( 'You have already redeemed this voucher.', 'lifterlms' ) );

			}

			// get products linked to the voucher
			$products = $this->get_products();

			if ( ! empty( $products ) ) {

				// loop through all of them and attempt enrollment
				foreach ( $products as $product ) {

					llms_enroll_student( $user_id, $product, 'voucher' );

				}

				do_action( 'llms_voucher_used', $voucher->id, $user_id, $voucher->code );

				// use voucher code
				$data = array(
					'user_id' => $user_id,
					'code_id' => $voucher->id,
				);
				$this->save_redeemed_code( $data );

				return true;

			}
		} else {

			return $voucher;

		}// End if().

	}

	/**
	 * Redeemed Codes
	 *
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_redeemed_codes( $format = 'OBJECT' ) {

		global $wpdb;

		$table          = $this->get_codes_table_name();
		$redeemed_table = $this->get_redemptions_table_name();
		$users_table    = $wpdb->prefix . 'users';

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
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_redemptions_for_code_by_user( $code_id, $user_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(id) FROM {$this->get_redemptions_table_name()} WHERE user_id = %d and code_id = %d", // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				array( $user_id, $code_id )
			)
		);

	}

	/**
	 * [save_redeemed_code description]
	 *
	 * @param    [type] $data  [description]
	 * @return   [type]            [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function save_redeemed_code( $data ) {

		global $wpdb;

		$data['redemption_date'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_redemptions_table_name(), $data );
	}

	/**
	 * Get an  array of IDs for products associated with this voucher
	 *
	 * @param  string $post_type  allows filtering of products by post type
	 * @return array
	 * @since   2.0.0
	 * @version 3.24.0
	 */
	public function get_products( $post_type = 'any' ) {

		global $wpdb;

		$table = $this->get_product_to_voucher_table_name();

		$products = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$table} WHERE `voucher_id` = %d;", $this->id ) ); //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! empty( $products ) ) {

			// filter any products that don't match the supplied post type
			if ( 'any' !== $post_type ) {
				foreach ( $products as $i => $id ) {
					if ( get_post_type( $id ) !== $post_type ) {
						unset( $products[ $i ] );
					}
				}
			}

			// convert all elements to ints
			$products = array_map( 'intval', $products );

		}

		return $products;
	}

	/**
	 * [is_product_to_voucher_link_valid description]
	 *
	 * @param    [type] $code        [description]
	 * @param    [type] $product_id  [description]
	 * @return   boolean                 [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function is_product_to_voucher_link_valid( $code, $product_id ) {

		$voucher = $this->check_voucher( $code );

		if ( $voucher ) {
			$this->id = $voucher->voucher_id;

			$products = $this->get_products();

			if ( ! empty( $products ) && in_array( $product_id, $products ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * [is_code_duplicate description]
	 *
	 * @since 2.0.0
	 * @since 3.35.0 Prepare SQL.
	 *
	 * @param    [type] $codes  [description]
	 * @return   boolean            [description]
	 */
	public function is_code_duplicate( $codes ) {

		global $wpdb;
		$codes_as_string = join( '","', $codes );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$codes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT code
             FROM {$this->get_codes_table_name()}
             WHERE code IN ( {$codes_as_string} )
               AND voucher_id != %d",
				array( $this->id )
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( count( $codes ) ) {
			return $codes;
		}

		return false;
	}

	/**
	 * [save_product description]
	 *
	 * @param    [type] $product_id  [description]
	 * @return   [type]                  [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function save_product( $product_id ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['product_id'] = $product_id;

		return $wpdb->insert( $this->get_product_to_voucher_table_name(), $data );
	}

	/**
	 * [delete_products description]
	 *
	 * @return   [type]     [description]
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function delete_products() {

		global $wpdb;

		return $wpdb->delete(
			$this->get_product_to_voucher_table_name(),
			array(
				'voucher_id' => $this->id,
			)
		);
	}
}

// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
