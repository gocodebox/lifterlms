<?php
/**
 * Display a single certificate.
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @since 6.0.0 Use custom header and footer templates in favor of the templates provided by the current theme.
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

llms_get_template( 'certificates/header.php' );

while ( have_posts() ) :

	the_post();
	llms_get_template_part( 'content', 'certificate' );

endwhile;

llms_get_template( 'certificates/footer.php' );
