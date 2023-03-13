<?php
/**
 * Builder video explainer view
 *
 * @since [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-video-explainer-template">

	<a
		href="javascript:void(0);"
		class="llms-video-explainer-trigger"
		onclick="jQuery( '.llms-video-explainer-wrapper' ).css( {
			'display': 'flex',
			'opacity': '1',
		} );"
		title="<?php _e( 'How to Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
	>
	</a>

	<div
		class="llms-video-explainer-wrapper"
		onclick="jQuery( '.llms-video-explainer-wrapper' ).css( {
			'display': 'none',
			'opacity': '0',
		} );jQuery( '.llms-video-explainer-iframe' ).attr( 'src', '' ).attr( 'src', 'https://www.youtube.com/embed/kMd37cOsPIg' );"
	>
		<iframe
			src="https://www.youtube.com/embed/kMd37cOsPIg"
			class="llms-video-explainer-iframe"
			title="<?php _e( 'Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
			allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share"
			allowfullscreen
		>
		</iframe>
		<button
			class="llms-video-explainer-close"
			onclick="jQuery( '.llms-video-explainer-wrapper' ).css( {
				'display': 'none',
				'opacity': '0',
			} );jQuery( '.llms-video-explainer-iframe' ).attr( 'src', '' ).attr( 'src', 'https://www.youtube.com/embed/kMd37cOsPIg' );"
			title="<?php _e( 'Close', 'lifterlms' ); ?>"
		>
			✕
		</button>
	</div>

</script>
