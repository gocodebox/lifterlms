<?php
//require_once "PHPUnit/Autoload.php";
//require_once "includes/admin/meta-boxes/class.llms.meta.box.access.php";

class QuizTest extends WP_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_sample_string() {

		$string = 'Unit tests are sweet';

		$this->assertEquals( 'Unit tests are sweet', $string);
	}

	public function testTalk() {
		$user = new LLMS_Meta_Box_Access();

		$expected = "Hello world!";
		$actual = $user->talk();
		$this->assertEquals($expected, $actual);
	}
	
	public function testPushAndPop() {
		$stack = array();
		$this->assertEquals(0, count($stack));

		array_push($stack, 'foo');
		$this->assertEquals('foo', $stack[count($stack)-1]);
		$this->assertEquals(1, count($stack));
		
		$this->assertEquals('foo', array_pop($stack));
		$this->assertEquals(0, count($stack));
	}

	public function testEmpty() {
		$stack = array();
		$this->assertEmpty($stack);

		return $stack;
	}

	/**
     * @depends testEmpty
     */
	public function testPush(array $stack) {
		array_push($stack, 'foo');
		$this->assertEquals('foo', $stack[count($stack)-1]);
		$this->assertNotEmpty($stack);

		return $stack;
	}

	/**
     * @depends testPush
     */
    public function testPop(array $stack) {
    	$this->assertEquals('foo', array_pop($stack));
    	$this->assertEmpty($stack);
    }

    /**
     * @dataProvider additionProvider
     */
    public function testAdd($a, $b, $expected)
    {
        $this->assertEquals($expected, $a + $b);
    }

    public function additionProvider()
    {
        return array(
          array(0, 0, 0),
          array(0, 1, 1),
          array(1, 0, 1),
          array(1, 2, 3)
        );
    }
}

