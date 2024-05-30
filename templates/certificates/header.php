<?php
/**
 * Single certificate header file.
 *
 * This is used in favor of the theme's header.php file in order to reduce theme compatibility issues
 * which arise on certificate templates.
 *
 * Certificates are meant to be minimal and should not display navigation, headers, logos, sidebars,
 * footers, and so on. Certificates are print-first with a limited on-screen user interface containing
 * actions related to the currently-viewed certificate (such as printing, exporting, etc...).
 *
 * Note: the viewport declaration commonly found in the <head> element is intentionally excluded since
 * certificates are print-first and non-responsive to device width.
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<link rel="profile" href="https://gmpg.org/xfn/11" />
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php echo esc_html( wp_get_document_title() ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();
