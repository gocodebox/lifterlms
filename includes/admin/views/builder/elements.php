<?php
/**
 * Builder sidebar course elements list
 *
 * @since   3.16.0
 * @version 3.16.0
 */
?>
<script type="text/html" id="tmpl-llms-elements-template">

	<h2 class="llms-sidebar-headline"><?php esc_html_e( 'Build Your Course', 'lifterlms' ); ?></h2>
	<p class="llms-sidebar-description">
		<?php
			/* translators: %s: link to course builder tutorial */
			echo wp_kses(
				sprintf(
					__( 'Drag or click on the different course elements below to build your course syllabus. <a href="%s" target="_blank">Visit the course builder tutorial here</a>.', 'lifterlms' ),
					'https://lifterlms.com/docs/using-course-builder/'
				),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			);
			?>
	</p>
	<ul class="llms-elements-list llms-add-items">

		<li>
			<button class="llms-element-button" id="llms-new-section">
				<span class="fa fa-puzzle-piece"></span>
				<span class="llms-element-button-text"><?php esc_html_e( 'Add New Section', 'lifterlms' ); ?></span>
			</button>
		</li>

		<li>
			<button class="llms-element-button" id="llms-new-lesson">
				<span class="fa fa-file"></span>
				<span class="llms-element-button-text"><?php esc_html_e( 'Add New Lesson', 'lifterlms' ); ?></span>
			</button>
		</li>

		<li>
			<button class="llms-element-button" id="llms-existing-lesson">
				<span class="fa fa-file-text"></span>
				<span class="llms-element-button-text"><?php esc_html_e( 'Add Existing Lesson', 'lifterlms' ); ?></span>
			</button>
		</li>

	</ul>

</script>
