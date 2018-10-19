<?php
/**
 * My Grades Single Course Table Template
 *
 * @since    3.24.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;
?>

<table class="llms-table llms-single-course-grades">
<?php foreach ( $course->get_sections() as $section ) : ?>

	<tr class="llms-section">
		<th class="llms-section_title" colspan="2">
			<?php printf( __( 'Section %1$d: %2$s', 'lifterlms' ), $section->get( 'order' ), $section->get( 'title' ) ); ?>
		</th>
		<?php foreach ( $section_headings as $id => $content ) : ?>
			<th class="llms-<?php echo esc_attr( $id ); ?>">
				<?php echo $content; ?>
			</th>
		<?php endforeach; ?>
	</tr>

	<?php foreach ( $section->get_lessons() as $lesson ) :
		$restricted = llms_page_restricted( $lesson->get( 'id' ) );
		$title = $lesson->get( 'title' );
		$url = $restricted['is_restricted'] ? '#' :get_permalink( $lesson->get( 'id' ) );
		$title = sprintf( '<a href="%1$s">%2$s</a>', $url, $title );
		?>
		<tr>
			<td class="llms-lesson_title" colspan="2">
				<?php printf( __( 'Lesson %1$d: %2$s', 'lifterlms' ), $lesson->get( 'order' ), $title ); ?>
				<?php if ( $restricted['is_restricted'] ) : ?>
					<a data-tooltip-msg="<?php echo esc_attr( strip_tags( llms_get_restriction_message( $restricted ) ) ); ?>" href="#llms-lesson-locked">
						<i class="fa fa-lock" aria-hidden="true"></i>
					</a>
				<?php endif; ?>
			</td>

			<?php foreach ( $section_headings as $id => $data ) : ?>
				<td class="llms-<?php echo esc_attr( $id ); ?>">
					<?php echo llms_sd_my_grades_table_content( $id, $lesson, $student, $restricted ); ?>
				</td>
			<?php endforeach; ?>

		</tr>
	<?php endforeach; ?>

<?php endforeach; ?>
</table>
