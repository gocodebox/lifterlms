<?php
/**
 * Test the Add-ons screen update notice.
 *
 * @package LifterLMS_Helper/Tests
 *
 * @group add_ons
 *
 * @since [version]
 */
class LLMS_Helper_Test_Admin_Add_Ons extends LLMS_Helper_Unit_Test_Case {

	/**
	 * Test that bulk management no longer includes the update action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_filter_manage_actions_excludes_update() {

		$admin   = new LLMS_Helper_Admin_Add_Ons();
		$actions = $admin->filter_manage_actions( array( 'activate', 'deactivate' ) );

		$this->assertContains( 'install', $actions );
		$this->assertNotContains( 'update', $actions );

	}

	/**
	 * Test the add-on card links an available plugin update to the Plugins screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_addon_item_links_plugin_update_to_plugins_screen() {

		$html = $this->render_addon_item( $this->get_plugin_addon( '99.0.0' ) );

		$this->assertStringContainsString( 'llms-addon-update-available', $html );
		$this->assertStringContainsString( 'plugins.php?s=Akismet', $html );
		$this->assertStringContainsString( 'Update Available:', $html );
		$this->assertStringNotContainsString( 'name="llms_update[]"', $html );
		$this->assertStringNotContainsString( 'data-action="update"', $html );

	}

	/**
	 * Test the add-on card omits the update link when the installed version is current.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_addon_item_hides_update_link_when_current() {

		$html = $this->render_addon_item( $this->get_plugin_addon( '0.0.1' ) );

		$this->assertStringNotContainsString( 'llms-addon-update-available', $html );
		$this->assertStringNotContainsString( 'name="llms_update[]"', $html );

	}

	/**
	 * Test a theme update links to the Themes screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_addon_item_links_theme_update_to_themes_screen() {

		$slug = '';
		foreach ( array_keys( wp_get_themes() ) as $theme ) {
			if ( 0 === strpos( $theme, 'twenty' ) ) {
				$slug = $theme;
				break;
			}
		}

		$this->assertNotEmpty( $slug );

		$addon = new LLMS_Add_On(
			array(
				'id'          => 'test-theme',
				'title'       => 'Test Theme',
				'description' => 'A theme.',
				'permalink'   => 'https://example.com/theme',
				'author'      => array(
					'name'  => 'LifterLMS',
					'image' => '',
				),
				'type'        => 'theme',
				'update_file' => $slug,
				'version'     => '99.0.0',
			)
		);

		$html = $this->render_addon_item( $addon );

		$this->assertStringContainsString( 'themes.php', $html );
		$this->assertStringNotContainsString( 'plugins.php', $html );

	}

	/**
	 * Build an Akismet-backed plugin add-on with the given latest version.
	 *
	 * @since [version]
	 *
	 * @param string $version Latest version advertised for the add-on.
	 * @return LLMS_Add_On
	 */
	private function get_plugin_addon( $version ) {

		return new LLMS_Add_On(
			array(
				'id'          => 'test-plugin',
				'title'       => 'Akismet',
				'description' => 'Spam protection.',
				'permalink'   => 'https://example.com/akismet',
				'author'      => array(
					'name'  => 'Automattic',
					'image' => '',
				),
				'type'        => 'plugin',
				'update_file' => 'akismet/akismet.php',
				'version'     => $version,
			)
		);

	}

	/**
	 * Render the single add-on card view.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon Add-on to render.
	 * @return string
	 */
	private function render_addon_item( $addon ) {

		$current_tab = 'all';

		ob_start();
		include LLMS_PLUGIN_DIR . 'includes/admin/views/addons/addon-item.php';
		return ob_get_clean();

	}

}
