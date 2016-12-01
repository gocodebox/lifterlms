<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php do_action( 'lifterlms_email_header', $email_heading ); ?>

<?php echo apply_filters( 'the_content', $email_message ); ?>

<?php do_action( 'lifterlms_email_footer' );
