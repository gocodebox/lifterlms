<?php
/**
 * Single Student View: Achievements Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

var_dump( $student->get_achievements() );
?>

