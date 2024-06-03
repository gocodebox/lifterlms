<?php
/**
 * Voucher Class
 *
 * @package LifterLMS/Classes
 *
 * @since 2.0.0
 * @version 3.37.17
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Voucher class
 *
 * @since 2.0.0
 * @since 3.27.0 Unknown.
 * @since 3.37.17 Only allow vouchers to be used if the voucher post is "published".
 */
class LLMS_Voucher {

	// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	/**
	 * ID of the voucher
	 * This will be a LifterLMS Voucher custom post type Post ID
	 *
	 * @var int
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
	 * @since 2.0.0
	 *
	 * @param int $id WP_Post ID of the voucher.
	 * @return void
	 */
	public function __construct( $id = null ) {
		$this->id = $id;
	}

	/**
	 * Retrieve the prefixed database table name for the table where voucher codes are stored
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_codes_table_name() {

		global $wpdb;

		return $wpdb->prefix . $this->codes_table_name;

	}

	/**
	 * Retrieve the prefixed database table name where voucher to product relationships are stored
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_product_to_voucher_table_name() {

		global $wpdb;
		return $wpdb->prefix . $this->product_to_voucher_table;

	}

	/**
	 * Retrieve the prefixed database table name where voucher redemptions are stored
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_redemptions_table_name() {

		global $wpdb;
		return $wpdb->prefix . $this->redemptions_table;

	}

	/**
	 * Get voucher title
	 *
	 * @since 2.0.0
	 * @since 3.6.2 Unknown.
	 *
	 * @return string
	 */
	public function get_voucher_title() {
		return get_the_title( $this->id );
	}

	/**
	 * Get a single voucher code by id
	 *
	 * @since 2.0.0
	 *
	 * @return obj
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
	 * @since 2.0.0
	 *
	 * @param string $code Voucher code string.
	 * @return obj
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
	 * @since 2.0.0
	 *
	 * @param string $format Return format.
	 * @return array
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
	 * Retrieve a voucher by ID.
	 *
	 * @since 2.0.0
	 *
	 * @param int $code_id Voucher code ID.
	 * @return object
	 */
	public function get_voucher_code_by_code_id( $code_id ) {

		global $wpdb;

		$table = $this->get_codes_table_name();

		$query = "SELECT * FROM $table WHERE `id` = $code_id AND `is_deleted` = 0 LIMIT 1";
		return $wpdb->get_row( $query );
	}

	/**
	 * Save a voucher code
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Voucher data.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function save_voucher_code( $data ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['created_at'] = date( 'Y-m-d H:i:s' );
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_codes_table_name(), $data );
	}

	/**
	 * Update a voucher code.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of voucher data.
	 * @return int|bool The number of rows updated, or false on error.
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
	 * Delete a voucher code.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id Voucher code id.
	 * @return int}bool The number of rows updated, or false on error.
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
	 * @since 2.0.0
	 * @since 3.0.0 Unknown.
	 * @since 3.37.17 Ensure the code's parent post is published.
	 *
	 * @param string $code Voucher code.
	 * @return WP_Error|object WP_Error if invalid or not redeemable OR a voucher data object.
	 */
	public function check_voucher( $code ) {

		$voucher = $this->get_voucher_by_code( $code );

		if ( empty( $voucher ) ) {

			return new WP_Error( 'not-found', sprintf( __( 'Voucher code "%s" could not be found.', 'lifterlms' ), $code ) );

		} elseif ( $voucher->redemption_count <= $voucher->used ) {

			return new WP_Error( 'max', sprintf( __( 'Voucher code "%s" has already been redeemed the maximum number of times.', 'lifterlms' ), $code ) );

		} elseif ( '1' === $voucher->is_deleted || 'publish' !== get_post_status( $voucher->voucher_id ) ) { // @todo because get_voucher_code() adds `is_deleted=0` we should never get here, I think.

			return new WP_Error( 'deleted', sprintf( __( 'Voucher code "%s" is no longer valid.', 'lifterlms' ), $code ) );

		}

		return $voucher;
	}

	/**
	 * Attempt to redeem a voucher for a user with a code
	 *
	 * @since 2.0.0
	 * @since 3.27.0 Unknown.
	 *
	 * @param string $code    Voucher code of the voucher being redeemed.
	 * @param int    $user_id WP_User ID of the user redeeming the voucher.
	 * @return bool|WP_Error Error object on failure, `true` when successful.
	 */
	public function use_voucher( $code, $user_id ) {

		$code = sanitize_text_field( $code );

		$voucher = $this->check_voucher( $code );

		if ( ! is_wp_error( $voucher ) ) {

			$this->id = $voucher->voucher_id;

			// Ensure the user hasn't already redeemed this voucher.
			if ( $this->get_redemptions_for_code_by_user( $voucher->id, $user_id ) ) {

				return new WP_Error( 'error', __( 'You have already redeemed this voucher.', 'lifterlms' ) );

			}

			// Get products linked to the voucher.
			$products = $this->get_products();

			if ( ! empty( $products ) ) {

				// Loop through all of them and attempt enrollment.
				foreach ( $products as $product ) {

					llms_enroll_student( $user_id, $product, 'voucher' );

				}

				/**
				 * Perform action before voucher redeemed.
				 *
				 * Action to perform before the voucher redeemed.
				 *
				 * @since 2.2.1
				 * @since 3.24.1 Added $voucher_title parameter.
				 * @since 3.27.0 Changed $voucher_title to $voucher_code to fix undefined property notice.
				 *
				 * @param int    $voucher_id   Voucher id of the voucher being redeemed.
				 * @param int    $user_id      WP_User ID of the user redeeming the voucher.
				 * @param string $voucher_code Voucher code of the voucher being redeemed.
				 */
				do_action( 'llms_voucher_used', $voucher->id, $user_id, $voucher->code );

				// Use voucher code.
				$data = array(
					'user_id' => $user_id,
					'code_id' => $voucher->id,
				);
				$this->save_redeemed_code( $data );

				return true;

			}
		} else {

			return $voucher;

		}

	}

	/**
	 * Redeemed Codes
	 *
	 * @since 2.0.0
	 *
	 * @return array
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
	 *
	 * Hint, it should always be 1 or 0
	 *
	 * @since 2.0.0
	 *
	 * @param int $code_id Voucher Code ID from wp_lifterlms_vouchers_codes table.
	 * @param int $user_id User ID from wp_users tables.
	 * @return int
	 */
	public function get_redemptions_for_code_by_user( $code_id, $user_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(id) FROM {$this->get_redemptions_table_name()} WHERE user_id = %d and code_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				array( $user_id, $code_id )
			)
		);

	}

	/**
	 * Save redeemed code
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Voucher data.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function save_redeemed_code( $data ) {

		global $wpdb;

		$data['redemption_date'] = date( 'Y-m-d H:i:s' );

		return $wpdb->insert( $this->get_redemptions_table_name(), $data );
	}

	/**
	 * Get an  array of IDs for products associated with this voucher
	 *
	 * @since 2.0.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $post_type Allows filtering of products by post type.
	 * @return array
	 */
	public function get_products( $post_type = 'any' ) {

		global $wpdb;

		$table = $this->get_product_to_voucher_table_name();

		$products = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$table} WHERE `voucher_id` = %d;", $this->id ) ); //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! empty( $products ) ) {

			// Filter any products that don't match the supplied post type.
			if ( 'any' !== $post_type ) {
				foreach ( $products as $i => $id ) {
					if ( get_post_type( $id ) !== $post_type ) {
						unset( $products[ $i ] );
					}
				}
			}

			// Convert all elements to ints.
			$products = array_map( 'intval', $products );

		}

		return $products;
	}

	/**
	 * Determine if the product is linked to a voucher by code
	 *
	 * @since 2.0.0
	 *
	 * @param string $code       Voucher code string.
	 * @param int    $product_id WP_Post ID of the product (course or membership).
	 * @return boolean
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
	 * Dupcheck generated voucher codes.
	 *
	 * @since 2.0.0
	 * @since 3.35.0 Prepare SQL.
	 *
	 * @param string[] $codes Array of voucher code strings.
	 * @return boolean
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
	 * Save products to a voucher
	 *
	 * @since 2.0.0
	 *
	 * @param int $product_id WP_Post ID of the product (course or membership).
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function save_product( $product_id ) {

		global $wpdb;

		$data['voucher_id'] = $this->id;
		$data['product_id'] = $product_id;

		return $wpdb->insert( $this->get_product_to_voucher_table_name(), $data );
	}

	/**
	 * Delete products from a voucher
	 *
	 * @since 2.0.0
	 *
	 * @return int
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
