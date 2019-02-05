<?php
/**
 * Review Request
 * We're needy. Please tell us you like us, it means a lot.
 *
 * @package LifterLMS/Admin/Views
 * @since   3.24.0
 * @version 3.24.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice notice-info is-dismissible llms-review-notice">
	<?php // Translators: %s = number of active students. ?>
	<p><?php printf( esc_html__( 'Hey there, we noticed you have more than %s active students on your site - that’s really awesome!', 'lifterlms' ), number_format_i18n( $enrollments ) ); ?></p>
	<p><?php esc_html_e( 'Could you please do us a BIG favor and give LifterLMS a 5-star rating on WordPress to help us grow?', 'lifterlms' ); ?></p>
	<p>&ndash; <?php esc_html_e( 'Chris Badgett & Thomas Patrick Levy, Co-Founders of LifterLMS', 'lifterlms' ); ?></p>
	<p>
		<a href="https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post" class="button-primary llms-review-notice-dismiss llms-review-notice-out" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it', 'lifterlms' ); ?></a>
		<a href="#" class="button llms-review-notice-dismiss"><?php esc_html_e( 'Nope, maybe later', 'lifterlms' ); ?></a>
		<a href="#" class="button llms-review-notice-dismiss"><?php esc_html_e( 'I already did', 'lifterlms' ); ?></a>
	</p>
</div>
<script>
	jQuery( document ).ready( function ( $ ) {
		$( document ).on( 'click', '.llms-review-notice-dismiss, .llms-review-notice .notice-dismiss', function ( event ) {
			var success = 'yes';
			if ( ! $( this ).hasClass( 'llms-review-notice-out' ) ) {
				event.preventDefault();
				success = 'no';
			}
			$.post( ajaxurl, {
				action: 'llms_review_dismiss',
				success: success,
			} );
			$( '.llms-review-notice' ).remove();
		} );
	} );
</script>
