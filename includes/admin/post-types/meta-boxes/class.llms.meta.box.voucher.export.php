<?php
if (!defined('ABSPATH')) exit;

/**
 * Meta Box Voucher Export
 */
class LLMS_Meta_Box_Voucher_Export
{

    public static $prefix = '_';

    public function __construct()
    {

    }

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
        ob_start();
        ?>
        <div class="llms-voucher-export-wrapper" id="llms-form-wrapper">

            <div class="llms-voucher-export-type">
                <input type="radio" name="llms_voucher_export_type" id="vouchers_only_type" value="vouchers">
                <label for="vouchers_only_type"><strong>Vouchers only</strong></label>

                <p>Generates a CSV of voucher codes, uses, and remaining uses.</p>
            </div>

            <div class="llms-voucher-export-type">
                <input type="radio" name="llms_voucher_export_type" id="redeemed_codes_type" value="redeemed">
                <label for="redeemed_codes_type"><strong>Redeemed codes</strong></label>

                <p>Generated a CSV of student emails, redemption date, and used code.</p>
            </div>


            <div class="llms-voucher-email-wrapper">
                <input type="checkbox" name="llms_voucher_export_send_email" id="llms_voucher_export_send_email"
                       value="true">
                <label for="llms_voucher_export_send_email">Email CSV</label>

                <input type="text" placeholder="Email" name="llms_voucher_export_email">
                <p>Send to multiple emails by separating emails addresses with commas.</p>
            </div>

            <button type="submit" name="llms_generate_export" value="generate" class="button-primary">Generate Export</button>
            <?php wp_nonce_field('lifterlms_csv_export_data', 'lifterlms_export_nonce'); ?>
        </div>
        <?php

        echo ob_get_clean();
    }

    public static function export()
    {
        if (empty($_POST['llms_generate_export']) || empty($_POST['lifterlms_export_nonce']) || !wp_verify_nonce($_POST['lifterlms_export_nonce'], 'lifterlms_csv_export_data')) {
            return false;
        }

        $type = $_POST['llms_voucher_export_type'];
        if (isset($type) && !empty($type)) {

            if ($type === 'vouchers' || $type === 'redeemed') {

                // export CSV

                $csv = array();
                $fileName = '';

                global $post;
                $voucher = new LLMS_Voucher($post->ID);

                switch ($type) {
                    case 'vouchers':

                        $voucher = new LLMS_Voucher($post->ID);
                        $codes = $voucher->get_voucher_codes('ARRAY_A');

                        foreach ( $codes as $k=>$v )
                        {
                            unset($codes[$k]['id']);
                            unset($codes[$k]['voucher_id']);
                            $codes[$k]['count'] = $codes[$k]['redemption_count'];
                            $codes[$k]['used'] = $codes[$k]['used'];
                            $codes[$k]['created'] = $codes[$k]['created_at'];
                            $codes[$k]['updated'] = $codes[$k]['updated_at'];
                            $codes[$k]['deleted'] = $codes[$k]['is_deleted'];
                            unset($codes[$k]['redemption_count']);
                            unset($codes[$k]['created_at']);
                            unset($codes[$k]['updated_at']);
                            unset($codes[$k]['is_deleted']);

                        }
                        $csv = self::array_to_csv($codes);

                        $fileName = 'vouchers.csv';
                        break;
                    case 'redeemed':

                        $redeemedCodes = $voucher->get_redeemed_codes('ARRAY_A');

                        $csv = self::array_to_csv($redeemedCodes);

                        $fileName = 'redeemed_codes.csv';
                        break;
                }

                $sendEmail = $_POST['llms_voucher_export_send_email'];

                if (isset($sendEmail) && !empty($sendEmail) && $sendEmail == true) {

                    // send email
                    $emailText = trim($_POST['llms_voucher_export_email']);
                    if (isset($emailText) && !empty($emailText)) {

                        $emails = explode(',', $emailText);

                        if (!empty($emails)) {

                            $voucher = new LLMS_Voucher($post->ID);

                            self::send_email($csv, $emails, $voucher->get_voucher_title());
                        }
                    }

                    return false;
                }

                self::download_csv($csv, $fileName);
            }
        }

    }

    public static function array_to_csv($data, $delimiter = ',', $enclosure = '"')
    {
        $handle = fopen('php://temp', 'r+');
        $contents = '';

        $names = array();

        foreach ($data[0] as $name => $item) {
            $names[] = $name;
        }

        fputcsv($handle, $names, $delimiter, $enclosure);

        foreach ($data as $line) {
            fputcsv($handle, $line, $delimiter, $enclosure);
        }
        rewind($handle);
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }

    public static function download_csv($csv, $name)
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="' . $name . '";');

        echo $csv;
        exit;
    }

    public static function send_email($csv, $emails)
    {
        $subject = 'Your LifterLMS Voucher Export';
        $message = 'Please find the attached voucher csv export for ' . $title . '.';

        // create temp file
        $temp = tempnam('/tmp', 'vouchers');

        // write csv
        $handle = fopen($temp, 'w');
        fwrite($handle, $csv);

        // prepare filename
        $tempData = stream_get_meta_data($handle);
        $tempFilename = $tempData['uri'];

        $newFilename = substr_replace($tempFilename, '', 13) . '.csv';
        rename($tempFilename, $newFilename);

        // send email/s
        $mail = wp_mail($emails, $subject, $message, '', $newFilename);

        // and remove it
        fclose($handle);
        unlink($newFilename);

        return $mail;
    }

}