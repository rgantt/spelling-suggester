<?php
require_once dirname(__FILE__).'/../suggest.php';

class suggest_test extends \PHPUnit_Framework_TestCase {
	private $s;
	
	public function setUp() {
		$this->s = new Suggest("fun fun fun gun gun sun run");
	}
	
	public function tearDown() {
		unset( $this->s );
	}
	
    public function test_suggests_highest_probability_word() {
        $s = $this->s;
        $this->assertEquals( "fun", $s("kun") );
		$this->assertEquals( "fun", $s("iun") );
    }
	
	public function test_suggests_known_word_instead_of_probabilistic_word() {
		$s = $this->s;
		$this->assertEquals( "run", $s("run") );
		$this->assertEquals( "sun", $s("sun") );
		$this->assertEquals( "gun", $s("gun") );
	}
}