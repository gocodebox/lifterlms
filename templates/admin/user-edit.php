<?php
/**
 * Add LifterLMS fields to the user-edit screen on the WordPress admin Panel
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 2.7.0
 * @version 3.13.0
 */

defined( 'ABSPATH' ) || exit;
?>

<h2><?php echo $section_title; ?></h2>

<table class="form-table">
	<tbody>

		<?php foreach ( $fields as $field => $data ) : ?>

			<tr class="user-<?php echo $field; ?>-wrap">
				<th>
					<label for="<?php echo $field; ?>">
						<?php echo $data['label']; ?>
						<?php echo ( $data['required'] ) ? '<span class="description">(' . __( 'required', 'lifterlms' ) . ')</span>' : ''; ?>
					</label>
				</th>
				<td>
					<input type="<?php echo $data['type']; ?>" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo $data['value']; ?>" class="regular-text">
					<?php echo ( $data['description'] ) ? '<span class="description">' . $data['description'] . '</span>' : ''; ?>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
