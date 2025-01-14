<?php
/**
 * Legacy template used for generating the stored contend of an earned certificate.
 *
 * This file is loaded via `LLMS_User_Certificate::merge_content` when the deprecated
 * filter `llms_certificate_use_legacy_template` is used.
 *
 * Historically this template was (likely mistakenly) copied from the email engagement
 * functionality where an HTML email is constructed (and mailed). With certificates
 * the content of the saved certificate is much simpler and adding custom HTML can be
 * done using the certificate template editor, rendering the usage of a template
 * superfluous.
 *
 * The template is retained until the `llms_certificate_use_legacy_template` is removed
 * in the next major release, at which point this template will also be removed.
 *
 * @package LifterLMS/Templates/Certificates
 *
 * @since 1.0.0
 * @version 6.0.0
 *
 * @deprecated 6.0.0
 */
defined( 'ABSPATH' ) || exit; ?>

<p><?php echo wp_kses_post( $email_message ); ?></p>
