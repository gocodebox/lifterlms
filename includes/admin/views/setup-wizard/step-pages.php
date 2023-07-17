<?php
/**
 * Setup Wizard step: Page Setup
 *
 * @since 4.4.4
 * @since [version] Using the `LLMS_Install::get_pages()` method now.
 * @version [version]
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;
?>
<h1><?php _e( 'Page Setup', 'lifterlms' ); ?></h1>

<p><?php _e( 'LifterLMS has a few essential pages. The following will be created automatically if they don\'t already exist.', 'lifterlms' ); ?>

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

<p><?php printf( __( 'After setup, you can manage these pages from the admin dashboard on the %1$sPages screen%2$s and you can control which pages display on your menu(s) via %3$sAppearance > Menus%4$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">', '</a>' ); ?></p>
