<?php
/**
 * Builder video explainer view
 *
 * @since 7.2.0
 * @version 7.2.0
 */
?>
<script type="text/html" id="tmpl-llms-video-explainer-template">

	<span class="llms-video-explainer-trigger">
		<img
			src="<?php echo esc_url( plugin_dir_url( LLMS_PLUGIN_FILE ) . 'assets/images/course-builder-video-thumbnail.jpg' ); ?>"
			alt="<?php esc_attr_e( 'How to Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
		/>
	</span>

	<div class="llms-video-explainer-wrapper">
		<iframe
			src="https://www.youtube.com/embed/kMd37cOsPIg"
			class="llms-video-explainer-iframe"
			title="<?php esc_attr_e( 'Build Your Course Outline with the LifterLMS Course Builder', 'lifterlms' ); ?>"
			allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share"
			allowfullscreen
		>
		</iframe>
		<button
			class="llms-video-explainer-close"
			title="<?php esc_attr_e( 'Close', 'lifterlms' ); ?>"
		>
			✕
		</button>
	</div>

</script>
