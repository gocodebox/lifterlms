<?php
/**
 * Assertions related to checking for LifterLMS Notices
 *
 * @since 1.10.0
 */
trait LLMS_Unit_Test_Assertions_Notices {

	/**
	 * Retrieve raw LifterLMS notice data.
	 *
	 * @since 1.10.0
	 *
	 * @return array[]
	 */
	private function get_notices() {
		return LLMS()->session->get( 'llms_notices', array() );
	}

	/**
	 * Assert that LifterLMS has at least one notice.
	 *
	 * Optionally check for a specific notice type.
	 *
	 * @since 1.10.0
	 *
	 * @param  string $type Specify a specific notice type, eg "error".
	 * @return void
	 */
	public function assertHasNotices( $type = '' ) {

		$notices = $this->get_notices();

		if ( ! $type ) {

			$this->assertTrue( count( $notices ) > 0, 'Failed asserting that LifterLMS notices exist.' );

		} else {

			$notices = ! empty( $notices[ $type ] ) ? $notices[ $type ] : array();
			$this->assertTrue( count( $notices ) > 0, sprintf( 'Failed asserting that LifterLMS %s notices exist.', $type ) );

		}

	}

	/**
	 * Assert that LifterLMS has a specific notice by message and optionally type.
	 *
	 * @since 1.10.0
	 *
	 * @param string $msg  Notice message.
	 * @param string $type Optionally check for the notice by notice type.
	 * @return void
	 */
	public function assertHasNotice( $msg, $type = '' ) {

		$to_check = array();

		$notices  = $this->get_notices();
		if ( ! $type ) {
			foreach ( $notices as $type => $messages ) {
				$to_check = array_merge( $to_check, $messages );
			}
		} elseif ( $type && ! empty( $notices[ $type ] ) ) {
			$to_check = $notices[ $type ];
		}

		$this->assertTrue( in_array( $msg, $to_check, true ), sprintf( 'Failed asserting that the notice "%s" exists.', $msg ) );

	}

	/**
	 * Assert that the number of LifterLMS notices equals an expected count.
	 *
	 * @since 1.10.0
	 *
	 * @param int    $expect Expected number of notices.
	 * @param string $type   Optionally specify a notice type.
	 * @return void
	 */
	public function assertNoticeCountEquals( $expect, $type = '' ) {

		$notices = $this->get_notices();

		if ( $type ) {
			$notices = ! empty( $notices[ $type ] ) ? $notices[ $type ] : array();
		}

		$this->assertEquals( $expect, count( $notices ) );

	}

}
