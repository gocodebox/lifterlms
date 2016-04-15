<?php
/**
 * Registration Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$product_id = get_query_var( 'product-id' );
global $wpdb;
llms_print_notices();
?>

<?php do_action( 'lifterlms_before_person_register_form' ); ?>

<div class="col-2 llms-new-person-form-wrapper">

    <h2 class="llms-title llms-h2"><?php _e( 'Register', 'lifterlms' ); ?></h2>

    <form method="post" class="llms-new-person-form register">

        <?php do_action( 'lifterlms_register_form_start' ); ?>

        <?php llms_get_template( 'global/form-registration-inner.php' ); ?>

        <?php do_action( 'lifterlms_register_form_end' ); ?>

        <?php do_action( 'lifterlms_after_person_register_form' ); ?>

        <div class="llms-form-item-wrapper llms-submit-wrapper">
            <?php wp_nonce_field( 'lifterlms-register', 'register' ); ?>
            <input type="submit" class="button" name="register" value="<?php _e( 'Register', 'lifterlms' ); ?>" />
        </div>
    </form>

</div>

