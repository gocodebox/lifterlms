<?php
/**
 * LifterLMS Unit Test Case Base class
 *
 * @since 3.37.3
 */
class LLMS_Settings_Page_Test_Case extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.3
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->page = new $this->classname();

	}

	/**
	 * Stub to be overridden in extending classes.
	 *
	 * This function should return an array of arrays.
	 *
	 * The array key is the option id and the value is an array of possible values to store.
	 *
	 * @since 3.37.3
	 *
	 * @return array[]
	 */
	protected function get_mock_settings() {
		return array();
	}

	/**
	 * Retrieve an indexed array of ids for the page's registered settings.
	 *
	 * @since 3.37.3
	 *
	 * @param bool $save_only If `true`, only return fields that can be saved to the database.
	 * @return string[]
	 */
	protected function get_settings_ids( $save_only = true ) {

		$saveable = array( 'checkbox', 'textarea', 'wpeditor', 'password', 'text', 'email', 'number', 'select', 'single_select_page', 'single_select_membership', 'radio', 'hidden', 'image', 'multiselect' );

		$ids = array();
		foreach ( $this->page->get_settings() as $setting ) {
			if ( empty( $setting['id'] ) || ( $save_only && ! in_array( $setting['type'], $saveable, true ) ) ) {
				continue;
			}
			$ids[] = $setting['id'];
		}
		return $ids;

	}

	/**
	 * Test the settings page ID matches the expected ID.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_id() {
		$this->assertEquals( $this->class_id, $this->page->id );
	}

	/**
	 * Test the settings page label matches the expected label.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_label() {
		$this->assertEquals( $this->class_label, $this->page->label );
	}

	/**
	 * Ensure all editable settings exist in the settings array.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_get_settings() {

		$settings = $this->get_mock_settings();

		if ( ! $settings ) {
			$this->markTestSkipped( 'No mock setting registered to test.' );
		}

		$mock   = array_keys( $settings );
		$actual = $this->get_settings_ids();
		$this->assertEquals( $mock, $actual );

	}

	/**
	 * Ensure no duplicate values exist in the settings array.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_get_settings_dupcheck() {

		$actual   = $this->get_settings_ids( false );
		$no_dupes = array_unique( $actual );
		$this->assertEquals( $no_dupes, $actual );

	}

	/**
	 * Test the save() method.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_save() {

		$settings = $this->get_mock_settings();

		if ( ! $settings ) {
			$this->markTestSkipped( 'No mock setting registered to test.' );
		}

		$post = array();
		foreach ( $settings as $key => $vals ) {
			$post[ $key ] = $vals[0];

			foreach ( $vals as $val ) {

				$this->mockPostRequest( array(
					$key => $val,
				) );
				$this->page->save();
				$this->assertEquals( $val, get_option( $key ), $key );

			}

		}

		// Bulk save all of them at once.
		$this->mockPostRequest( $post );
		$this->page->save();
		foreach ( $post as $key => $val ) {
			$this->assertEquals( $val, get_option( $key ), $key );
		}

	}

	/**
	 * Test the set_label() method.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function test_set_label() {
		$this->assertEquals( $this->class_label, LLMS_Unit_Test_Util::call_method( $this->page, 'set_label' ) );
	}

}
