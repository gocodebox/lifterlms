<?php
/**
 * Builder sidebar model editor view
 *
 * @since   3.16.0
 * @version 3.17.0
 */
?>
<script type="text/html" id="tmpl-llms-editor-template">

	<nav class="llms-editor-nav">

		<ul class="llms-editor-menu">

<!-- 			<li class="llms-editor-menu-item<# if ( 'course' === data.state ) { print( ' active' ); } #>">
				<a href="#llms-editor-course" data-view="course"><?php _e( 'Course', 'lifterlms' ); ?></a>
			</li> -->

			<li class="llms-editor-menu-item<# if ( 'lesson' === data.state ) { print( ' active' ); } #>">
				<a href="#llms-editor-lesson" data-view="lesson"><?php _e( 'Lesson', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-editor-menu-item<# if ( 'assignment' === data.state ) { print( ' active' ); } #>">
				<a href="#llms-editor-assignment" data-view="assignment"><?php _e( 'Assignment', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-editor-menu-item<# if ( 'quiz' === data.state ) { print( ' active' ); } #>">
				<a href="#llms-editor-quiz" data-view="quiz"><?php _e( 'Quiz', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-editor-menu-item right">
				<a href="#llms-editor-close">
					<span class="screen-reader-text"><?php _e( 'Close', 'lifterlms' ); ?></span>
					<i class="fa fa-close" aria-hidden="true"></i>
				</a>
			</li>

		</ul>

	</nav>

	<section id="llms-editor-lesson" class="llms-editor-tab tab--lesson<# if ( 'lesson' === data.state ) { print( ' active' ); } #>"></section>
	<section id="llms-editor-quiz" class="llms-editor-tab tab--quiz<# if ( 'quiz' === data.state ) { print( ' active' ); } #>"></section>
	<section id="llms-editor-assignment" class="llms-editor-tab tab--assignment<# if ( 'assignment' === data.state ) { print( ' active' ); } #>"></section>

</script>
