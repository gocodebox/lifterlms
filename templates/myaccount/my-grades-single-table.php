<?php
/**
 * My Grades Single Course Table Template
 *
 * @since 3.24.0
 * @since 6.0.0 Wrap each section in a <tbody> element.
 * @since [version] Render each section as its own table with a caption.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;
?>

<?php foreach ( $course->get_sections() as $section ) : ?>
<table class="llms-table llms-single-course-grades">
	<caption class="llms-section_title">
		<?php echo esc_html( sprintf( __( 'Section %1$d: %2$s', 'lifterlms' ), $section->get( 'order' ), $section->get( 'title' ) ) ); ?>
	</caption>
	<thead>
		<tr>
			<th class="llms-lesson_title" scope="col"><?php esc_html_e( 'Lesson', 'lifterlms' ); ?></th>
			<?php foreach ( $section_headings as $id => $content ) : ?>
				<th class="llms-<?php echo esc_attr( $id ); ?>" scope="col">
					<?php echo wp_kses_post( $content ); ?>
				</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $section->get_lessons() as $lesson ) :
			$restricted = llms_page_restricted( $lesson->get( 'id' ) );
			$title = $lesson->get( 'title' );
			$url   = $restricted['is_restricted'] ? '#' : get_permalink( $lesson->get( 'id' ) );
			$href  = ( '#' === $url ) ? '#' : esc_url( $url );
			$title = sprintf( '<a href="%1$s">%2$s</a>', $href, esc_html( $title ) );
			?>
			<tr>
				<th class="llms-lesson_title" scope="row">
					<?php echo wp_kses_post( sprintf( __( 'Lesson %1$d: %2$s', 'lifterlms' ), $lesson->get( 'order' ), $title ) ); ?>
					<?php if ( $restricted['is_restricted'] ) : ?>
						<a data-tooltip-msg="<?php echo esc_attr( wp_strip_all_tags( llms_get_restriction_message( $restricted ) ) ); ?>" href="#llms-lesson-locked">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</a>
					<?php endif; ?>
				</th>

				<?php foreach ( $section_headings as $id => $data ) : ?>
					<td class="llms-<?php echo esc_attr( $id ); ?>">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in template.
						echo llms_sd_my_grades_table_content( $id, $lesson, $student, $restricted );
						?>
					</td>
				<?php endforeach; ?>

			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach; ?>
