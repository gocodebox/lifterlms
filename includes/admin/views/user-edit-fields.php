<?php
/**
 * Display LifterLMS Profile fields in admin user screen
 *
 * @since 5.0.0
 * @version 5.0.0
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
</div>
