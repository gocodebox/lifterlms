<?php
/**
 * View for displaying the Beta Testing tab on the "Status" screen
 *
 * @package LifterLMS_Helper/Views
 *
 * @since 3.0.0
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<form action="" class="llms-beta-main" method="POST">

	<aside class="llms-beta-aside">

		<h1><?php esc_html_e( 'Beta Testing Warnings and FAQs', 'lifterlms' ); ?></h1>

		<h3><?php esc_html_e( 'Always test with caution!', 'lifterlms' ); ?></h3>
		<p><strong><?php esc_html_e( 'Beta releases may not be stable. We may not be able to fix issues caused by using a beta release. We urge you to only use beta versions in testing environments!', 'lifterlms' ); ?></strong></p>
		<p><?php esc_html_e( 'Subscribing to the <em>beta channel</em> for LifterLMS or any available add-ons will allow you to automatically update to the latest beta release for the given plugin or theme.', 'lifterlms' ); ?></p>
		<p><?php esc_html_e( 'When no beta versions are available, automatic updates will be to the latest stable version of the plugin or theme.', 'lifterlms' ); ?></p>

		<h3><?php esc_html_e( 'Rolling back and restoring data', 'lifterlms' ); ?></h3>
		<p><strong><?php esc_html_e( 'This plugin does not provide you with the ability to rollback from a beta version.', 'lifterlms' ); ?></strong></p>
		<p><?php esc_html_e( 'To rollback you should subscribe to the stable channel, delete the beta version of the plugin, and then re-install the latest version. If a database migration was run you should also restore your database from a backup.', 'lifterlms' ); ?></p>

		<h3><?php esc_html_e( 'Reporting bugs and contributing', 'lifterlms' ); ?></h3>
		<p>
			<?php
				// Translators: %1$s = Opening anchor link; %2$s = closing anchor link.
				printf( esc_html__( 'We welcome contributions of all kinds, review our contribution guidelines on %1$sGitHub%2$s to get started.', 'lifterlms' ), '<a href="https://github.com/gocodebox/lifterlms/blob/master/.github/CONTRIBUTING.md">', '</a>' );
			?>
		</p>
		<p>
			<?php
				// Translators: %s = Link to bug report.
				printf( esc_html__( 'If you encounter a bug while beta testing, please report it at %s.', 'lifterlms' ), wp_kses_post( make_clickable( 'https://github.com/gocodebox/lifterlms/issues' ) ) );
			?>
		</p>

		<h3><?php esc_html_e( 'Still have questions?', 'lifterlms' ); ?></h3>
		<p>
			<?php
				// Translators: %s = Link to guide.
				printf( esc_html__( 'Check out our Guide to Beta Testing at %s.', 'lifterlms' ), wp_kses_post( make_clickable( 'https://lifterlms.com/docs/beta-testing/' ) ) );
			?>
		</p>

	</aside>

	<table class="llms-table zebra text-left size-large llms-beta-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Channel', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Installed Version', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Beta Version', 'lifterlms' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $addons as $addon ) :
			$addon = llms_get_add_on( $addon );
			?>
			<tr>
				<td><?php echo $addon->get( 'title' ); ?></td>
				<td>
					<select name="llms_channel_subscriptions[<?php echo $addon->get( 'id' ); ?>]">
						<option value="stable" <?php selected( 'stable', $addon->get_channel_subscription() ); ?>><?php esc_html_e( 'Stable', 'lifterlms' ); ?></option>
						<option value="beta" <?php selected( 'beta', $addon->get_channel_subscription() ); ?>><?php esc_html_e( 'Beta', 'lifterlms' ); ?></option>
					</select>
				</td>
				<td><?php echo $addon->get_installed_version(); ?></td>
				<td><?php echo $addon->get( 'version_beta' ) ? $addon->get( 'version_beta' ) : __( 'N/A', 'lifterlms' ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="4"><button class="llms-button-primary" id="llms-channel-submit" type="submit"><?php esc_html_e( 'Save & Update', 'lifterlms' ); ?></button></th>
			</tr>
		</tfoot>
	</table>

	<script>
		document.getElementById( 'llms-channel-submit' ).onclick = function( e ) {
			if ( ! window.confirm( "<?php esc_attr_e( 'Are you sure you want to enable or disable beta testing for these plugins and themes?', 'lifterlms' ); ?>" ) ) {
				e.preventDefault();
			}
		}
	</script>

	<?php wp_nonce_field( 'llms_save_channel_subscriptions', '_llms_beta_sub_nonce' ); ?>

</form>
<?php
