<?php
/**
 * Single certificate content.
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 4.21.0 Make certificate background alt localizable.
 * @since 6.0.0 Moved HTML content to `templates/certificates/content.php` and `templates/certificates/actions.php`.
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

$certificate = llms_get_certificate( get_the_ID(), true );

/**
 * Action triggered to display a single certificate.
 *
 * @since 6.0.0
 *
 * @hooked llms_certificate_content - 10.
 * @hooked llms_certificate_actions - 20.
 *
 * @param LLMS_User_Certificate $certificate The certificate object.
 */
do_action( 'llms_display_certificate', $certificate );
