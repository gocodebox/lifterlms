<?php
/**
 * Registration Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
llms_print_notices();
?>

<?php do_action( 'lifterlms_before_person_register_form' ); ?>

<div class="col-2 llms-new-person-form-wrapper">

    <h2 class="llms-title llms-h2"><?php _e( 'Register', 'lifterlms' ); ?></h2>

    <form method="post" class="llms-new-person-form register">

        <?php do_action( 'lifterlms_register_form_start' ); ?>

        <?php llms_get_template( 'global/form-registration-inner.php' ); ?>

        <?php do_action( 'lifterlms_register_form_end' ); ?>

	</form>

</div>
