<?php
require_once dirname(__FILE__).'/../suggest.php';

class suggest_test extends \PHPUnit_Framework_TestCase {
	/**
	 * Can't use setUp/tearDown because __invoke doesn't work with $this->
	 */
	private function init() {
		return new Suggest(
			array( 'fun', 'fun', 'fun', 'gun', 'gun', 'sun', 'run' )
		);
	}
	
    public function test_suggests_highest_probability_word() {
        $s = $this->init();
        $this->assertEquals( "fun", $s("kun") );
		$this->assertEquals( "fun", $s("iun") );
    }
	
	public function test_suggests_known_word_instead_of_probabilistic_word() {
		$s = $this->init();
		$this->assertEquals( "run", $s("run") );
		$this->assertEquals( "sun", $s("sun") );
		$this->assertEquals( "gun", $s("gun") );
	}
}