<?php
/**
 * Tests for the LLMS_Abstract_Notification_View.
 *
 * @group abstracts
 * @group notifications
 *
 * @since [version]
 */
class LLMS_Test_Abstract_Notification_View extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		global $wpdb;

		$this->main = $this->get_stub(
			new LLMS_Notification( $wpdb->insert_id )
		);

	}

	/**
	 * Retrieve the abstract class mock stub.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Abstract_Notification_View
	 */
	private function get_stub( $notification ) {

		return new class( $notification ) extends LLMS_Abstract_Notification_View {

			/**
			 * Replace merge codes with actual values.
			 *
			 * @param string $code The merge code to get merged data for.
			 * @return string
			 */
			protected function set_merge_data( $code ) {

				switch ( $code ) {
					case '{{MG_1}}' :
						return 'Merge Code expanded 1';

					case '{{MG_2}}' :
						return 'Merge Code expanded 2';

					case '{{MG_3}}' :
						return 'Merge Code expanded 3';

					case '{{MG_4}}' :
						return 'Merge Code expanded 4';
				}

			}

			/**
			 * Setup body content for output.
			 *
			 * @return string
			 */
			protected function set_body() {
			}

			/**
			 * Setup footer content for output.
			 *
			 * @return string
			 */
			protected function set_footer() {
			}

			/**
			 * Setup notification icon for output.
			 *
			 * @return string
			 */
			protected function set_icon() {
			}

			/**
			 * Setup merge codes that can be used with the notification
			 *
			 * @return array
			 */
			protected function set_merge_codes() {

				return array(
					'{{MG_1}}' => 'Merge code 1',
					'{{MG_2}}' => 'Merge code 2',
					'{{MG_3}}' => 'Merge code 3',
					'{{MG_4}}' => 'Merge code 4',
				);

			}

			/**
			 * Setup notification subject line for output.
			 *
			 * @return string
			 */
			protected function set_subject() {
			}

			/**
			 * Setup notification title for output
			 *
			 * On an email the title acts as the "heading" element.
			 *
			 * @return string
			 */
			protected function set_title() {
			}

		};

	}

	/**
	 * Test get_used_merge_codes().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_used_merge_codes() {

		$string_expected = array(
			''                                                         => array(),
			'Something in the way she moves... reminds me of {{MG_6}}' => array(),
			'{{MG_1}} and {{MG_4}}'                                    => array(
				'{{MG_1}}',
				'{{MG_4}}',
			),
		);

		foreach ( $string_expected as $string => $expected ) {
			$this->assertEquals(
				$expected,
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'get_used_merge_codes',
					array(
						$string,
					)
				),
				$string
			);
		}

	}


	/**
	 * Test get_merged_string().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_merged_string() {

		$string_expected = array(
			''                                                                           => '',
			'Something'                                                                  => 'Something',
			'Useless Merge Code {{MG_5}}'                                                => 'Useless Merge Code {{MG_5}}',
			'{{MG_1}} and {{MG_4}}|{{MG_3}}|{{MG_2}} but not {{MG_5}}; {{MG_2}}_reprise' => 'Merge Code expanded 1 and Merge Code expanded 4|Merge Code expanded 3|Merge Code expanded 2 but not {{MG_5}}; Merge Code expanded 2_reprise',
		);

		foreach ( $string_expected as $string => $expected ) {
			$this->assertEquals(
				$expected,
				LLMS_Unit_Test_Util::call_method(
					$this->main,
					'get_merged_string',
					array(
						$string,
					)
				),
				$string
			);
		}

	}

}
