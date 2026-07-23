<?php
/**
 * LLMS_Admin_Bundled_Plugins class
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 10.1.0
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notifies administrators about installed standalone copies of plugins which are bundled with LifterLMS.
 *
 * The blocks, CLI, helper, REST API, and banner notifications feature plugins are bundled with and
 * maintained within the LifterLMS core plugin. Standalone copies no longer receive updates and should
 * be deactivated and deleted.
 *
 * @since 10.1.0
 */
class LLMS_Admin_Bundled_Plugins {

	/**
	 * Admin notice ID.
	 *
	 * @var string
	 */
	const NOTICE_ID = 'bundled-plugins';

	/**
	 * Constructor.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_add_notices' ) );
	}

	/**
	 * Retrieves a list of bundled plugin basenames and their names.
	 *
	 * @since 10.1.0
	 *
	 * @return array Array mapping plugin basenames to plugin names.
	 */
	protected function get_bundled_plugins() {
		return array(
			'lifterlms-blocks/lifterlms-blocks.php' => 'LifterLMS Blocks',
			'lifterlms-cli/lifterlms-cli.php'       => 'LifterLMS CLI',
			'lifterlms-helper/lifterlms-helper.php' => 'LifterLMS Helper',
			'lifterlms-rest/lifterlms-rest.php'     => 'LifterLMS REST API',
			'banner-notifications/banner-notifications.php' => 'Banner Notifications',
		);
	}

	/**
	 * Retrieves installed standalone copies of bundled plugins.
	 *
	 * @since 10.1.0
	 *
	 * @return array Array mapping plugin basenames to plugin names for installed standalone copies.
	 */
	protected function get_installed_standalone_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed = get_plugins();

		return array_intersect_key( $this->get_bundled_plugins(), $installed );
	}

	/**
	 * Adds an admin notice and plugin row messages when standalone copies are installed.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function maybe_add_notices() {

		$plugins = $this->get_installed_standalone_plugins();

		if ( ! $plugins ) {
			if ( LLMS_Admin_Notices::has_notice( self::NOTICE_ID ) ) {
				LLMS_Admin_Notices::delete_notice( self::NOTICE_ID );
			}
			return;
		}

		$this->add_admin_notice( $plugins );

		foreach ( array_keys( $plugins ) as $file ) {
			add_action( "after_plugin_row_{$file}", array( $this, 'output_plugin_row_notice' ), 10, 2 );
		}
	}

	/**
	 * Adds the dismissable admin notice.
	 *
	 * @since 10.1.0
	 *
	 * @param array $plugins Array mapping plugin basenames to plugin names.
	 * @return void
	 */
	protected function add_admin_notice( $plugins ) {

		$html = sprintf(
			// Translators: %s = comma-separated list of plugin names.
			__( 'The following plugins are now bundled with LifterLMS and no longer receive standalone updates: %s. Please deactivate and delete them from the Plugins screen. The versions bundled with LifterLMS are used automatically.', 'lifterlms' ),
			'<strong>' . implode( '</strong>, <strong>', array_values( $plugins ) ) . '</strong>'
		);

		LLMS_Admin_Notices::add_notice(
			self::NOTICE_ID,
			$html,
			array(
				'type'             => 'warning',
				'dismissible'      => true,
				'dismiss_for_days' => 30,
			)
		);
	}

	/**
	 * Outputs a notice within a standalone plugin's row on the plugins screen.
	 *
	 * @since 10.1.0
	 *
	 * @param string $plugin_file Plugin basename.
	 * @param array  $plugin_data Plugin data.
	 * @return void
	 */
	public function output_plugin_row_notice( $plugin_file, $plugin_data ) {

		$colspan = 4;
		$table   = _get_list_table( 'WP_Plugins_List_Table' );
		if ( $table ) {
			$colspan = count( $table->get_columns() );
		}

		$active_class = is_plugin_active( $plugin_file ) ? ' active' : '';
		?>
		<tr class="plugin-update-tr<?php echo esc_attr( $active_class ); ?>">
			<td colspan="<?php echo esc_attr( $colspan ); ?>" class="plugin-update colspanchange">
				<div class="notice inline notice-warning notice-alt">
					<p>
					<?php
					printf(
						// Translators: %s = the plugin's name.
						esc_html__( '%s is now bundled with LifterLMS and this standalone copy no longer receives updates. Please deactivate and delete it; the version bundled with LifterLMS is used automatically.', 'lifterlms' ),
						'<strong>' . esc_html( $plugin_data['Name'] ?? $plugin_file ) . '</strong>'
					);
					?>
					</p>
				</div>
			</td>
		</tr>
		<?php
	}
}

return new LLMS_Admin_Bundled_Plugins();
