<?php
/**
 * Tests for LLMS_Hasher
 *
 * @group hasher
 *
 * @version 3.16.10
 */
class LLMS_Test_Hasher extends LLMS_UnitTestCase {

	private $ids = array();
	private $hashes = array();

	private function get_random_id( $max = 99999999 ) {

		$id = rand( 1, $max );
		while ( ! in_array( $id, $this->ids ) ) {
			array_push( $this->ids, $id );
			return $id;
		}
		return $max + 1;

	}

	/**
	 * Test the hashing/unhashing functions
	 *
	 * @since 3.16.10
	 *
	 * @return void
	 */
	public function test_hash_unhash() {

		foreach ( range( 1, 10000 ) as $i ) {
			$id = $this->get_random_id();
			$hash = LLMS_Hasher::hash( $id );
			$this->assertFalse( in_array( $hash, $this->hashes ) );
			$this->assertEquals( $id, LLMS_Hasher::unhash( $hash ) );
		}

	}

}
