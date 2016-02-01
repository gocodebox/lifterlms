<?php
if (!defined('ABSPATH')) exit;

/**
 * Voucher Class
 *
 * @author codeBOX
 * @project lifterLMS
 */
class LLMS_Voucher
{
    protected $id;

    protected static $codes_table_name = 'lifterlms_vouchers_codes';
    protected static $redemptions_table = 'lifterlms_voucher_code_redemptions';
    protected static $product_to_voucher_table = 'lifterlms_product_to_voucher';

    protected function get_codes_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::$codes_table_name;
    }

    protected function get_redemptions_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::$redemptions_table;
    }

    protected function get_product_to_voucher_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::$product_to_voucher_table;
    }

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function get_voucher_title()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'posts';

        $query = "SELECT post_title FROM $table WHERE `ID` = $this->id LIMIT 1";
        return reset($wpdb->get_row($query));
    }

    // Get single voucher code
    public function get_voucher_by_voucher_id()
    {
        global $wpdb;

        $table = $this->get_codes_table_name();


        $query = "SELECT * FROM $table WHERE `voucher_id` = $this->id AND `is_deleted` = 0 LIMIT 1";
        return $wpdb->get_row($query);
    }

    // Get single voucher code by code
    public function get_voucher_by_code($code)
    {
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
        return $wpdb->get_row($query);
    }

    public function get_voucher_codes($format = 'OBJECT')
    {
        global $wpdb;

        $table = $this->get_codes_table_name();
        $redeemed_table = $this->get_redemptions_table_name();

        $query = "SELECT c.*, count(r.id) as used
                  FROM $table as c
                  LEFT JOIN $redeemed_table as r
                  ON c.`id` = r.`code_id`
                  WHERE `voucher_id` = $this->id AND `is_deleted` = 0
                  GROUP BY c.id";
        return $wpdb->get_results($query, $format);
    }

    public function get_voucher_code_by_code_id($code_id)
    {
        global $wpdb;

        $table = $this->get_codes_table_name();

        $query = "SELECT * FROM $table WHERE `id` = $code_id AND `is_deleted` = 0 LIMIT 1";
        return $wpdb->get_row($query);
    }

    public function save_voucher_code($data)
    {
        global $wpdb;

        $data['voucher_id'] = $this->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $wpdb->insert($this->get_codes_table_name(), $data);
    }

    public function update_voucher_code($data)
    {
        global $wpdb;

        $data['updated_at'] = date('Y-m-d H:i:s');

        $where = array('id' => $data['id']);
        unset($data['id']);
        return $wpdb->update($this->get_codes_table_name(), $data, $where);
    }

    public function delete_voucher_code($id)
    {
        global $wpdb;

        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_deleted'] = 1;

        $where = array('id' => $id);
        unset($data['id']);
        return $wpdb->update($this->get_codes_table_name(), $data, $where);
    }

    public function check_voucher($code)
    {
        $voucher = $this->get_voucher_by_code($code);

        if (empty($voucher) || $voucher->redemption_count <= $voucher->used) {
            $voucher = false;
        }

        return $voucher;
    }

    public function use_voucher($code, $user_id)
    {
        $voucher = $this->check_voucher($code);

        if ($voucher) {
            global $wpdb;

            $this->id = $voucher->voucher_id;

            $postmeta_table = $wpdb->prefix . 'lifterlms_user_postmeta';
            $select_vouchers = "SELECT meta_value FROM $postmeta_table
                WHERE $postmeta_table.user_id = $user_id
                AND $postmeta_table.meta_key = '_voucher'
                AND $postmeta_table.meta_value = $voucher->id
 			    LIMIT 1000";
            $used_voucher = $wpdb->get_results($select_vouchers, ARRAY_A);

            if( count( $used_voucher ) ) {
                llms_add_notice( 'You have already used this voucher.', 'error' );
                return $voucher->voucher_id;
            }

            // use voucher code
            $data = array(
                'user_id' => $user_id,
                'code_id' => $voucher->id
            );

            $this->save_redeemed_code($data);

            // create order for products linked to voucher
            $products = $this->get_products();

            if (!empty($products)) {

                $membership_levels = array();

                foreach ($products as $product) {
                    $order = new LLMS_Order();
                    $order->create($user_id, $product, 'Voucher');

                    if (get_post_type($product) === 'llms_membership') {
                        $membership_levels[] = $product;
                    }

                    // update user postmeta
                    $user_metadatas = array(
                        '_start_date' => 'yes',
                        '_status' => 'Enrolled',
                        '_voucher' => $voucher->id,
                    );

                    foreach ($user_metadatas as $key => $value) {
                        $wpdb->insert($wpdb->prefix . 'lifterlms_user_postmeta',
                            array(
                                'user_id' => $user_id,
                                'post_id' => $product,
                                'meta_key' => $key,
                                'meta_value' => $value,
                                'updated_date' => current_time('mysql'),
                            )
                        );
                    }
                }

                if (!empty($membership_levels)) {
                    update_user_meta($user_id, '_llms_restricted_levels', $membership_levels);
                }

                llms_add_notice( "Voucher used successfully!" );
            }
        } else {
            llms_add_notice( "Voucher could not be used. Please check that you have valid voucher.", 'error' );
        }

        return $voucher;
    }

    /**
     * Redeemed Codes
     */
    public function get_redeemed_codes($format = 'OBJECT')
    {
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

        return $wpdb->get_results($query, $format);
    }

    public function save_redeemed_code($data)
    {
        global $wpdb;

        $data['redemption_date'] = date('Y-m-d H:i:s');

        return $wpdb->insert($this->get_redemptions_table_name(), $data);
    }

    /**
     * Product 2 Voucher
     */

    public function get_products()
    {
        global $wpdb;

        $table = $this->get_product_to_voucher_table_name();

        $query = "SELECT * FROM $table WHERE `voucher_id` = $this->id";

        $results = $wpdb->get_results($query);

        $products = array();
        if (!empty($results)) {
            foreach ($results as $item) {
                $products[] = intval($item->product_id);
            }
        }

        return $products;
    }

    public function is_product_to_voucher_link_valid($code, $product_id)
    {
        $voucher = $this->check_voucher($code);

        if ($voucher) {
            $this->id = $voucher->voucher_id;

            $products = $this->get_products();

            if (!empty($products) && in_array($product_id, $products)) {
                return true;
            }
        }

        return false;
    }

    public function save_product($product_id)
    {
        global $wpdb;

        $data['voucher_id'] = $this->id;
        $data['product_id'] = $product_id;

        return $wpdb->insert($this->get_product_to_voucher_table_name(), $data);
    }

    public function delete_products()
    {
        global $wpdb;

        return $wpdb->delete($this->get_product_to_voucher_table_name(), array('voucher_id' => $this->id));
    }
}
