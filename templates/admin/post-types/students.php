<?php
/**
 * Students Metabox on admin panel
 * @since    3.0.0
 * @version  3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! is_admin() ) { exit; }

$table = new LLMS_Table_StudentManagement();
$table->get_results();
?>
<div class="llms-metabox llms-metabox-students">

	<?php do_action( 'lifterlms_before_students_metabox' ); ?>

	<div class="llms-metabox-section llms-metabox-students-enrollments no-top-margin">
		<?php echo $table->get_table_html(); ?>
	</div>

	<?php if ( current_user_can( 'enroll' ) ) : ?>

		<div class="llms-metabox-section llms-metabox-students-add-new">
			<h2><?php echo __( 'Enroll New Students', 'lifterlms' ) ?></h2>

			<div class="llms-metabox-field d-all">
				<select id="llms-add-student-select" multiple="multiple" name="_llms_add_student"></select>
			</div>

			<div class="llms-metabox-field d-all d-right">
				<button class="llms-button-primary" id="llms-enroll-students" type="button"><?php _e( 'Enroll Students', 'lifterlms' ); ?></button>
			</div>

			<div class="clear"></div>
		</div>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_students_metabox' ); ?>

</div>
