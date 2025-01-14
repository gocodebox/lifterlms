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

<h2><?php echo esc_html( $section_title ); ?></h2>

<table class="form-table">
	<tbody>

		<?php foreach ( $fields as $field => $data ) : ?>

			<tr class="user-<?php echo esc_attr( $field ); ?>-wrap">
				<th>
					<label for="<?php echo esc_attr( $field ); ?>">
						<?php echo esc_html( $data['label'] ); ?>
						<?php echo ( $data['required'] ) ? '<span class="description">(' . esc_html__( 'required', 'lifterlms' ) . ')</span>' : ''; ?>
					</label>
				</th>
				<td>
					<input type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $data['value'] ); ?>" class="regular-text">
					<?php echo ( $data['description'] ) ? '<span class="description">' . wp_kses_post( $data['description'] ) . '</span>' : ''; ?>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
