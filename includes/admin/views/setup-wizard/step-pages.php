<?php
/**
 * Setup Wizard step: Page Setup
 *
 * @since 4.4.4
 * @since 7.3.0 Using the `LLMS_Install::get_pages()` method now.
 * @since 7.4.0 Escape remaining strings.
 * @version 7.4.0
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;
?>
<h1><?php esc_html_e( 'Page Setup', 'lifterlms' ); ?></h1>

<p><?php esc_html_e( 'LifterLMS has a few essential pages. The following will be created automatically if they don\'t already exist.', 'lifterlms' ); ?>

<table>
	<?php
	$pages = LLMS_Install::get_pages();
	foreach ( $pages as $page ) {
		// Skip pages that don't have all the info we want to show.
		if ( empty( $page['docs_url'] ) || empty( $page['description'] ) || empty( $page['wizard_title'] ) ) {
			continue;
		}
		?>
		<tr>
		<td><a href="<?php echo esc_url( $page['docs_url'] ); ?>" target="_blank"><?php echo esc_html( $page['wizard_title'] ); ?></a></td>
		<td><p><?php echo esc_html( $page['description'] ); ?></p></td>
		</tr>
		<?php
	}
	?>
</table>

<p>
	<?php
	printf(
		/* Translators: 1: Link to the Pages screen in the WordPress admin 2: Closing link tag 3: Link to the Appearance > Menus screen in the WordPress admin 4: Closing link tag. */
		esc_html__( 'After setup, you can manage these pages from the admin dashboard on the %1$sPages screen%2$s and you can control which pages display on your menu(s) via %3$sAppearance > Menus%4$s.', 'lifterlms' ),
		'<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">',
		'</a>',
		'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>
