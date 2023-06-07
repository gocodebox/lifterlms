<?php
/**
 * Assertions related to checking for WP Registered and Enqueue Assets
 *
 * @since 1.8.0
 */
trait LLMS_Unit_Test_Assertions_Assets {

	/**
	 * Retrieve the WP core global variable based on the asset type
	 *
	 * @since 1.8.0
	 * @since 1.13.0 Use global initializer functions in favor the direct globals to ensure the return is `null`.
	 *
	 * @param  string $type Asset type (script or style).
	 * @return obj|null
	 */
	private function get_asset_global_obj( $type ) {

		if ( 'script' === $type ) {
			return wp_scripts();
		} elseif ( 'style' === $type ) {
			return wp_styles();
		} else {
			$this->markTestSkipped( "Asset type '{$type}' does not exit." );
		}

	}

	/**
	 * Assert that a script or style is registered with WordPress
	 *
	 * @since 1.8.0
	 *
	 * @param  string $type   Asset type (script or style)
	 * @param  string $handle Asset handle/id.
	 * @return void
	 */
	public function assertAssetIsRegistered( $type, $handle ) {

		$this->assertArrayHasKey( $handle, $this->get_asset_global_obj( $type )->registered );

	}

	/**
	 * Assert that a script or style is not registered with WordPress
	 *
	 * @since 1.8.0
	 *
	 * @param  string $type   Asset type (script or style)
	 * @param  string $handle Asset handle/id.
	 * @return void
	 */
	public function assertAssetNotRegistered( $type, $handle ) {

		$this->assertArrayNotHasKey( $handle, $this->get_asset_global_obj( $type )->registered );

	}

	/**
	 * Assert that a script or style is enqueued with WordPress
	 *
	 * @since 1.8.0
	 *
	 * @param  string $type   Asset type (script or style)
	 * @param  string $handle Asset handle/id.
	 * @return void
	 */
	public function assertAssetIsEnqueued( $type, $handle ) {

		$this->assertTrue( in_array( $handle, $this->get_asset_global_obj( $type )->queue, true ) );

	}

	/**
	 * Assert that a script or style is not enqueued with WordPress
	 *
	 * @since 1.8.0
	 *
	 * @param  string $type   Asset type (script or style)
	 * @param  string $handle Asset handle/id.
	 * @return void
	 */
	public function assertAssetNotEnqueued( $type, $handle ) {

		$this->assertFalse( in_array( $handle, $this->get_asset_global_obj( $type )->queue, true ) );

	}


}
