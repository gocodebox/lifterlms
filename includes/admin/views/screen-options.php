<?php

defined( 'ABSPATH' ) || exit;
?>

<?php foreach ( $groups as $group_id => $group ) : ?>

	<fieldset class="llms-screen-options">
		<legend><?php echo $group['label']; ?></legend>

		<?php foreach ( $group['options'] as $field_id => $field ) : ?>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field['label']; ?></label><br />
			<input
				type="<?php echo esc_attr( $field['type'] ); ?>"
				name="<?php echo esc_attr( $field_id ); ?>"
				id="<?php echo esc_attr( $field_id ); ?>"
				value="<?php echo esc_attr( $field['value'] ); ?>"
			/>
		<?php endforeach; ?>
	</fieldset>

<?php endforeach; ?>
