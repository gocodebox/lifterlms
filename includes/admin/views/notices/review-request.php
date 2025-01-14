<?php
/**
 * Review Request
 *
 * We're needy. Please tell us you like us, it means a lot.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 3.24.0
 * @since 4.14.0 Added nonce to AJAX request.
 * @version 4.14.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice notice-info is-dismissible llms-admin-notice llms-review-notice">
	<div class="llms-admin-notice-icon"></div>
	<div class="llms-admin-notice-content">
		<?php // Translators: %s = number of active students. ?>
		<p><?php printf( esc_html__( 'Hey there, we noticed you have more than %s active students on your site - that’s really awesome!', 'lifterlms' ), esc_html( number_format_i18n( $enrollments ) ) ); ?></p>
		<p><?php esc_html_e( 'Could you please do us a BIG favor and give LifterLMS a 5-star rating on WordPress to help us grow?', 'lifterlms' ); ?></p>
		<p>&ndash; <?php esc_html_e( 'Chris Badgett, CEO of LifterLMS', 'lifterlms' ); ?></p>
		<p>
			<a href="https://wordpress.org/support/plugin/lifterlms/reviews/?filter=5#new-post" class="llms-button-primary small llms-review-notice-dismiss llms-review-notice-out" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it', 'lifterlms' ); ?></a>
			<button class="llms-button-secondary small llms-review-notice-dismiss"><?php esc_html_e( 'Nope, maybe later', 'lifterlms' ); ?></button>
			<button class="llms-button-secondary small llms-review-notice-dismiss"><?php esc_html_e( 'I already did', 'lifterlms' ); ?></button>
		</p>
	</div>
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
				nonce: '<?php echo esc_js( wp_create_nonce( 'llms-admin-review-request-dismiss' ) ); ?>',
			} );
			$( '.llms-review-notice' ).remove();
		} );
	} );
</script>
