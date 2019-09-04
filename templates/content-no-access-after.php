<?php
/**
 * The Template for displaying all single courses.
 *
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;
llms_print_notices();

?>
<?php
do_action( 'lifterlms_no_access_main_content' );
