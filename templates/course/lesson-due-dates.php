<?php
/**
 * The student's due dates for a lesson's quiz, assignment, etc.
 *
 * Displayed above the lesson buttons.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @var LLMS_Post_Model[] $items      Quiz, assignment, etc., ordered by due date.
 * @var int               $student_id WP_User ID.
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="llms-lesson-due-dates" aria-label="<?php esc_attr_e( 'Due dates', 'lifterlms' ); ?>">
	<?php foreach ( $items as $item ) : ?>
		<li class="llms-lesson-due-date">
			<span class="llms-due-item-type"><?php echo esc_html( get_post_type_object( $item->get( 'type' ) )->labels->singular_name ); ?></span>
			<?php echo wp_kses_post( $item->get_due_date_html( $student_id ) ); ?>
		</li>
	<?php endforeach; ?>
</ul>
