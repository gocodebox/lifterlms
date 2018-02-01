<?php
/**
 * Builder sidebar course elements list
 * @since   3.16.0
 * @version 3.16.0
 */
?>
<script type="text/html" id="tmpl-llms-elements-template">

	<h2 class="llms-sidebar-headline"><?php _e( 'Add Elements', 'lifterlms' ); ?></h2>
	<ul class="llms-elements-list llms-add-items">

		<li>
			<button class="llms-element-button" id="llms-new-section">
				<span class="fa fa-puzzle-piece"></span> <?php _e( 'Section', 'lifterlms' ); ?>
			</button>
		</li>

		<li>
			<button class="llms-element-button" id="llms-new-lesson">
				<span class="fa fa-file"></span> <?php _e( 'New Lesson', 'lifterlms' ); ?>
			</button>
		</li>

		<li>
			<button class="llms-element-button" id="llms-existing-lesson">
				<span class="fa fa-file-text"></span> <?php _e( 'Existing Lesson', 'lifterlms' ); ?>
			</button>
		</li>

	</ul>

</script>
