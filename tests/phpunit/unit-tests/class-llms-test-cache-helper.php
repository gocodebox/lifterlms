<?php
/**
 * Test LLMS_Cache_Helper
 *
 * @package LifterLMS/Tests
 *
 * @group cache
 * @group cache_helper
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Cache_Helper extends LLMS_Unit_Test_Case {

	/**
	 * Test get_prefix() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_prefix() {

		$group = 'mock_prefix';

		// Cache miss.
		wp_cache_delete( 'llms_mock_cache_prefix', $group );

		$prefix = LLMS_Cache_Helper::get_prefix( $group );

		// Looks right.
		$this->assertEquals( 1, preg_match( '/llms_cache_0.[0-9]{8} [0-9]{10}_/', $prefix ) );

		// Cache hit.
		$this->assertEquals( $prefix, LLMS_Cache_Helper::get_prefix( $group ) );

	}

	/**
	 * Test invalidate_group() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_invalidate_group() {

		$group = 'mock_invalidate';

		$prefix = LLMS_Cache_Helper::get_prefix( $group );

		// Cache an item with the prefix.
		wp_cache_set( sprintf( 'fake_%s', $prefix ), 'mock_val', $group );

		$prefix = LLMS_Cache_Helper::invalidate_group( $group );

		// New prefix should not match the original prefix.
		$this->assertNotEquals( $prefix, LLMS_Cache_Helper::get_prefix( $group ) );

		// Cached item is gone.
		$this->assertFalse( wp_cache_get( sprintf( 'fake_%s', $prefix ), $group ) );

	}

}
