	<table class="llms-table">
	<?php foreach ( $course->get_sections() as $section ) : ?>

		<tr class="llms-section">
			<th class="llms--section_title" colspan="2">
				<?php printf( __( 'Section %1$d: %2$s', 'lifterlms' ), $section->get( 'order' ), $section->get( 'title' ) ); ?>
			</th>
			<?php foreach ( $section_headings as $id => $content ) : ?>
				<th class="llms--<?php echo esc_attr( $id ); ?>">
					<?php echo $content; ?>
				</th>
			<?php endforeach; ?>
		</tr>

		<?php foreach ( $section->get_lessons() as $lesson ) : ?>
			<tr>
				<td class="llms-spacer"></td>
				<td>
					<?php printf( __( 'Lesson %1$d: %2$s', 'lifterlms' ), $lesson->get( 'order' ), $lesson->get( 'title' ) ); ?>
				</td>

				<?php foreach ( $section_headings as $id => $data ) : ?>
					<td class="llms--<?php echo esc_attr( $id ); ?>">
						<?php echo llms_sd_my_grades_table_content( $id, $lesson, $student ); ?>
					</td>
				<?php endforeach; ?>

			</tr>
		<?php endforeach; ?>

	<?php endforeach; ?>
	</table>
