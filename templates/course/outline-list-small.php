<?php
/**
 * Course Outline Small List
 */
?>
<div class="llms-widget-syllabus">
	
	<ul>
		
		<?php //get section data
		foreach ( $sections as $section ) : ?>

			<li>

				<span class="section-title"><?php echo $section['title']; ?></span>

				<?php //loop through sections
				foreach ( $syllabus->lessons as $lesson ) :

					if ( $lesson['parent_id'] == $section['id'] ) : ?>
				
						<ul>
							
							<li>

								<span class="llms-lesson-complete <?php echo ( $lesson['is_complete'] ? 'done' : '' ); ?>">

									<i class="fa fa-check-circle"></i>

								</span>

								<span class="lesson-title '<?php echo ( $lesson['is_complete'] ? 'done' : '' ); ?>">

									<?php if ( LLMS_Course::check_enrollment( $course->id, get_current_user_id() ) ) : ?>

										<a href="<?php echo get_permalink( $lesson['id'] ); ?>"><?php echo $lesson['title']; ?></a>
			
									<?php else :

										echo $lesson['title'];

									endif; ?>

								</span>
					
							</li>
						
						</ul>
					
					<?php endif;

				endforeach; ?>
				
			</li>
		
		<?php endforeach; ?>

	</ul>

</div>
