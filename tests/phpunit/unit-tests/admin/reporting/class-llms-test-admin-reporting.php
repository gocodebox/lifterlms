<?php
/**
 * Tests Admin Reporting Class
 *
 * @package LifterLMS/Tests/Admin/Reporting
 *
 * @group admin
 * @group admin_reporting
 *
 * @since [version]
 */
class LLMS_Test_Admin_Reporting extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Reporting();

	}

	/**
	 * Tests {@see LLMS_Admin_Reporting::output_widget}
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_output_widget() {

		$tests = array(
			// Simple numeric data with comparison.
			array(
				'id'           => 'a',
				'data'         => 123,
				'data_compare' => 456,
				'icon'         => 'mocking-bird',
			),
			// Percentage data.
			array(
				'id'           => 'b',
				'data'         => '1.235',
				'data_compare' => '5.920',
				'data_type'    => 'percentage',
				'icon'         => 'mocking-bird',
			),
			// Numeric with no compare.
			array(
				'id'   => 'c',
				'data' => 9328320.952,
				'icon' => 'mocking-bird',
			),
			// Text data with no icon.
			array(
				'id'   => 'd',
				'data' => 'Lorem ipsum dolor sit.',
			),
			// Monetary data with negative impact.
			array(
				'id'           => 'e',
				'data'         => '45.90',
				'data_compare' => '200.32',
				'data_type'    => 'monetary',
				'impact'       => 'negative',
			),
			// Date data.
			array(
				'id'           => 'f',
				'data'         => 'January 1, 2022',
				'data_type'    => 'date',
			),
			// Date with comparison (invalid but shouldn't error).
			array(
				'id'           => 'g',
				'data'         => 'January 1, 2022',
				'data_compare' => '200.32',
				'data_type'    => 'date',
			),
			/**
			 * Numeric divide by zero comparison.
			 *
			 * @link https://github.com/gocodebox/lifterlms/issues/2270
			 */
			array(
				'id'           => 'h',
				'data'         => '0.000',
				'data_compare' => '0.000',
			),
		);

		foreach ( $tests as $test ) {

			$test['text'] = 'Test Letter ' . strtoupper( $test['id'] );
			$test['id']   = "test-{$test['id']}";

			$output = trim(
				$this->get_output( 
					array( 'LLMS_Admin_Reporting', 'output_widget' ),
					array( $test )
				)
			);

			$snap_path = __DIR__ . "/__snapshots__/admin-reporting-output_widget-{$test['id']}.txt";

			// Quick snapshot generator, reenable if we add more tests to save copy/pasting.
			// if ( ! file_exists( $snap_path ) ) {
			// 	$fh = fopen( $snap_path, 'w' );
			// 	fwrite( $fh, $output );
			// 	fclose( $fh );
			// 	$this->markTestIncomplete( "Snapshot written for {$test['id']}." );
			// }

			$snap = file_get_contents( $snap_path );

			$this->assertEquals( trim( $snap ), trim( $output ) );

		}

	}

}