<?php
/**
 * Display an MCE editor merge code button (and drop down).
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 6.0.0
 * @version 6.0.0
 *
 * @see llms_merge_code_button()
 *
 * @param array[]        $codes  Associative array of merge codes where the array key is the merge code and the array value is a name / description of the merge code.
 * @param WP_Screen|null $screen The screen object from `get_current_screen().
 * @param string         $target Target to add the merge code to. Accepts the ID of a tinymce editor or a DOM ID (#element-id).
 */

defined( 'ABSPATH' ) || exit;
$svg = file_get_contents( LLMS_PLUGIN_DIR . '/assets/images/lifterlms-icon-grey.svg' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
?>

<div class="llms-merge-code-wrapper">

	<button class="button llms-merge-code-button" type="button">
		<?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'Merge Codes', 'lifterlms' ); ?>
	</button>

	<div class="llms-merge-codes" data-target="<?php echo esc_attr( $target ); ?>">
		<ul>
			<?php foreach ( $codes as $code => $desc ) : ?>
				<li data-code="<?php echo esc_attr( $code ); ?>"><?php echo wp_kses_post( $desc ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>

</div><!-- .llms-merge-code-wrapper -->
