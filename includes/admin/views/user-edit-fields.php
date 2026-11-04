<?php
/**
 * Display LifterLMS Profile fields in admin user screen
 *
 * @since 5.0.0
 * @since [version] Added the due date accommodation setting.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $fields ) ) {
	return;
}
?>
<div id="llms-profile-fields">
	<h2><?php esc_html_e( 'LifterLMS Profile', 'lifterlms' ); ?></h2>
	<?php array_map( 'llms_form_field', $fields, array_fill( 0, count( $fields ), true ), array_fill( 0, count( $fields ), $user ) ); ?>

	<?php
	$allow_unlimited = get_user_option( 'llms_allow_unlimited_quiz_time', is_numeric( $user ) ? $user : $user->ID );
	$allow_unlimited = empty( $allow_unlimited ) ? 'no' : $allow_unlimited;
	?>
	<div class="llms-allow-unlimited-quiz-time llms-allow-unlimited-quiz-time-wrap">
		<label for="llms_allow_unlimited_quiz_time">
			<input name="llms_allow_unlimited_quiz_time" type="checkbox" id="llms_allow_unlimited_quiz_time" value="yes"<?php checked( 'yes', $allow_unlimited ); ?>>
			<?php esc_html_e( 'Allow unlimited quiz time for quizzes that have a time limit set', 'lifterlms' ); ?>
		</label>
	</div>

	<?php
	$user_id           = is_numeric( $user ) ? $user : $user->ID;
	$due_accommodation = get_user_option( 'llms_due_date_accommodation', $user_id );
	$due_extension     = absint( get_user_option( 'llms_due_date_extension_days', $user_id ) );
	?>
	<fieldset class="llms-due-date-accommodation" style="margin-top: 16px;">
		<p style="margin: 0 0 6px;"><strong><?php esc_html_e( 'Quiz and assignment due dates', 'lifterlms' ); ?></strong></p>
		<label>
			<input name="llms_due_date_accommodation" type="radio" value=""<?php checked( ! in_array( $due_accommodation, array( 'extend', 'none' ), true ) ); ?>>
			<?php esc_html_e( 'Use the course due dates', 'lifterlms' ); ?>
		</label><br>
		<label>
			<input name="llms_due_date_accommodation" type="radio" value="extend"<?php checked( 'extend', $due_accommodation ); ?>>
			<?php
			printf(
				// Translators: %s = Number input.
				esc_html__( 'Extend every due date by %s days', 'lifterlms' ),
				'<input name="llms_due_date_extension_days" type="number" min="1" max="365" class="small-text" value="' . esc_attr( $due_extension ? $due_extension : '' ) . '">'
			);
			?>
		</label><br>
		<label>
			<input name="llms_due_date_accommodation" type="radio" value="none"<?php checked( 'none', $due_accommodation ); ?>>
			<?php esc_html_e( 'No due dates', 'lifterlms' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'For accessibility accommodations. Applies to every course; a due date changed for this student on a single quiz or assignment still takes precedence. Students see only their own due dates.', 'lifterlms' ); ?></p>
	</fieldset>
</div>
