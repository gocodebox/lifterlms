<?php
if (!defined('ABSPATH')) exit;
if (!defined('LLMS_Admin_Metabox')) {
    // Include the file for the parent class
    include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
 * Meta Box Builder
 *
 * Generates main metabox and builds forms
 */
class LLMS_Meta_Box_Voucher extends LLMS_Admin_Metabox
{

    public static $prefix = '_';

    /**
     * Function to field WP::output() method call
     * Passes output instruction to parent
     *
     * @param object $post WP global post object
     * @return void
     */
    public static function output($post)
    {
        global $post;
        parent::new_output($post, self::metabox_options());
    }

    /**
     * Builds array of metabox options.
     * Array is called in output method to display options.
     * Appropriate fields are generated based on type.
     *
     * @return array [md array of metabox fields]
     */
    public static function metabox_options()
    {
        global $post;

        $voucher = new LLMS_Voucher($post->ID);
        $selectedProducts = $voucher->get_products();

        $courses = LLMS_Analytics::get_posts('course');

        $coursesSelect = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $coursesSelect[] = array(
                    'key' => $course->ID,
                    'title' => $course->post_title
                );
            }
        }

        $memberships = LLMS_Analytics::get_posts('llms_membership');

        $membershipsSelect = array();
        if (!empty($memberships)) {
            foreach ($memberships as $membership) {
                $membershipsSelect[] = array(
                    'key' => $membership->ID,
                    'title' => $membership->post_title
                );
            }
        }

        $meta_fields_voucher = array(
            array(
                'title' => 'General',
                'fields' => array(
                    array(
                        'type' => 'select',
                        'label' => 'Courses',
                        'id' => self::$prefix . 'llms_voucher_courses',
                        'class' => 'input-full llms-meta-select',
                        'value' => $coursesSelect,
                        'multi' => true,
                        'selected' => $selectedProducts
                    ),
                    array(
                        'type' => 'select',
                        'label' => 'Membership',
                        'id' => self::$prefix . 'llms_voucher_membership',
                        'class' => 'input-full llms-meta-select',
                        'value' => $membershipsSelect,
                        'multi' => true,
                        'selected' => $selectedProducts
                    ),
                    array(
                        'type' => 'custom-html',
                        'label' => 'Codes',
                        'id' => '',
                        'class' => '',
                        'value' => self::codes_section_html()
                    ),
                )
            ),
            array(
                'title' => 'Redemptions',
                'fields' => array(
                    array(
                        'type' => 'custom-html',
                        'label' => 'Redemptions',
                        'id' => '',
                        'class' => '',
                        'value' => self::redemption_section_html()
                    ),
                )
            )
        );

        if (has_filter('llms_meta_fields_voucher')) {
            $meta_fields_voucher = apply_filters('llms_meta_fields_voucher', $meta_fields_voucher);
        }

        return $meta_fields_voucher;
    }

    private static function codes_section_html()
    {
        global $post;
        $voucher = new LLMS_Voucher($post->ID);
        $codes = $voucher->get_voucher_codes();

        ob_start(); ?>
        <div class="llms-voucher-codes-wrapper" id="llms-form-wrapper">
            <table>

                <thead>
                <tr>
                    <th></th>
                    <th>Code</th>
                    <th>Uses</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <?php $deleteIcon = LLMS_Svg::get_icon( 'llms-icon-close', 'Delete Section', 'Delete Section', 'button-icon' ); ?>
                <script>var deleteIcon = '<?= $deleteIcon ?>';</script>

                <tbody id="llms_voucher_tbody">
                <?php if (!empty($codes)):
                    $cnt = 1;
                    foreach ($codes as $code): ?>
                        <tr>
                            <td><?php echo $cnt++;?>.</td>
                            <td>
                                <input type="text" maxlength="20" placeholder="Code" value="<?= $code->code ?>"
                                       name="llms_voucher_code[]">
                                <input type="hidden" name="llms_voucher_code_id[]" value="<?= $code->id ?>">
                            </td>
                            <td><span><?= $code->used ?> / </span><input type="text" value="<?= $code->redemption_count ?>"
                                                        placeholder="Uses" class="llms-voucher-uses"
                                                        name="llms_voucher_uses[]"></td>
                            <td>
                                <a href="#" data-id="<?= $code->id ?>" class="llms-voucher-delete">
                                    <?php echo $deleteIcon; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach;
                endif; ?>
                </tbody>

            </table>

            <div class="llms-voucher-add-codes">
                <p>Add <input type="text" placeholder="#" id="llms_voucher_add_quantity"> new code(s) with <input
                        type="text" placeholder="#" id="llms_voucher_add_uses"> use(s) per code
                    <button id="llms_voucher_add_codes" class="button-primary">Add</button>
                </p>
            </div>

            <input type="hidden" name="delete_ids" id="delete_ids">
        </div>

        <?php
        return ob_get_clean();
    }

    private static function redemption_section_html()
    {
        global $post;

        $pid = $post->ID;
        $voucher = new LLMS_Voucher($pid);
        $redeemedCodes = $voucher->get_redeemed_codes();
        ob_start(); ?>

        <div class="llms-voucher-redemption-wrapper">
            <table>

                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Redemption Date</th>
                    <th>Code</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!empty($redeemedCodes)):
                    foreach ($redeemedCodes as $redeemedCode):

                        $user = get_user_by('id', $redeemedCode->user_id);
                        ?>
                        <tr>
                            <td><?= $user->data->display_name ?></td>
                            <td><?= $user->data->user_email ?></td>
                            <td><?= $redeemedCode->redemption_date ?></td>
                            <td><?= $redeemedCode->code ?></td>
                        </tr>
                    <?php endforeach;
                endif;
                ?>
                </tbody>

            </table>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Static save method
     *
     * cleans variables and saves using update_post_meta
     *
     * @param  int $post_id [id of post object]
     * @param  object $post [WP post object]
     *
     * @return void
     */
    public static function save($post_id, $post)
    {
        if (empty($_POST['lifterlms_meta_nonce']) || !wp_verify_nonce($_POST['lifterlms_meta_nonce'], 'lifterlms_save_data')) {
            return false;
        }

        // CODES SAVE
        $codes = array();

        $llms_codes = $_POST['llms_voucher_code'];
        $llms_uses = $_POST['llms_voucher_uses'];
        $llms_voucher_code_id = $_POST['llms_voucher_code_id'];

        $voucher = new LLMS_Voucher($post_id);

        if (isset($llms_codes) && !empty($llms_codes) && isset($llms_uses) && !empty($llms_uses)) {

            foreach ($llms_codes as $k => $code) {
                if (isset($code) && !empty($code) && isset($llms_uses[$k]) && !empty($llms_uses[$k])) {

                    if (isset($llms_voucher_code_id[$k])) {

                        $data = array(
                            'code' => $code,
                            'redemption_count' => intval($llms_uses[$k])
                        );

                        if (!empty(intval($llms_voucher_code_id[$k]))) {
                            $data['id'] = intval($llms_voucher_code_id[$k]);
                        }

                        $codes[] = $data;
                    }
                }
            }
        }

        if (!empty($codes)) {
            foreach ($codes as $code) {

                if (isset($code['id'])) {
                    $voucher->update_voucher_code($code);
                } else {
                    $voucher->save_voucher_code($code);
                }
            }
        }

        // Courses and membership save

        $courses = $_POST['_llms_voucher_courses'];
        $memberships = $_POST['_llms_voucher_membership'];
        $products = array();

        if (isset($courses) && !empty($courses)) {
            foreach ($courses as $item) {
                $products[] = intval($item);
            }
        }

        if (isset($memberships) && !empty($memberships)) {
            foreach ($memberships as $item) {
                $products[] = intval($item);
            }
        }

        // remove old products
        $voucher->delete_products();

        // save new ones
        if (!empty($products)) {
            foreach ($products as $item) {
                $voucher->save_product($item);
            }
        }

        // set old codes as deleted
        if (isset($_POST['delete_ids']) && !empty($_POST['delete_ids'])) {
            $delete_ids = explode(',', $_POST['delete_ids']);

            if (!empty($delete_ids)) {
                foreach ($delete_ids as $id) {
                    $voucher->delete_voucher_code($id);
                }
            }
        }
    }
}