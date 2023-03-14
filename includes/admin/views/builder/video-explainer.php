<?php
/**
 * Builder video explainer view
 *
 * @since [version]
 * @version [version]
 */
?>
<script type="text/html" id="tmpl-llms-video-explainer-template">

	<span class="llms-video-explainer-trigger">
		<img
			src="<?php echo plugin_dir_url( LLMS_PLUGIN_FILE ); ?>assets/images/course-builder-video-thumbnail.jpg"
			alt="<?php _e( 'How to Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
		/>
	</span>

	<div class="llms-video-explainer-wrapper">
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
			title="<?php _e( 'Close', 'lifterlms' ); ?>"
		>
			✕
		</button>
	</div>

</script>
