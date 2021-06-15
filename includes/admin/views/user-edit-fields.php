<?php
/**
 * Display LifterLMS Profile fields in admin user screen
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $fields ) ) {
	return;
}
?>
<div id="llms-profile-fields">
	<h2><?php esc_html_e( 'LifterLMS Profile', 'lifterlms' ); ?></h2>
	<?php array_map( 'llms_form_field', $fields, array_fill( 0, count( $fields ), true ), array_fill( 0, count( $fields ), $user ) ); ?>
</div>
