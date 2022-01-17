<?php
/**
 * Generic loop template
 *
 * Utilized by both courses and memberships.
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since [version] Moved the main part in loop-main.php
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header( 'llms_loop' ); ?>

<?php llms_get_template_part( 'loop', 'main' ); ?>

<?php get_footer(); ?>
