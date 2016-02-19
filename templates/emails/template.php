<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php do_action( 'lifterlms_email_header', $email_heading ); ?>

<p><?php echo $email_message; ?></p>

<?php do_action( 'lifterlms_email_footer' ); ?>
