<?php
require_once dirname(__FILE__).'/../suggest.php';

class wordtrainer_test extends \PHPUnit_Framework_TestCase {
	private $w;
	
	public function setUp() {
		$this->w = new WordTrainer("gun gun fun fun fun sun");
	}
	
	public function tearDown() {
		unset( $this->w );
	}
	
    public function test_can_train_with_string() {
        $w = new WordTrainer("this is a string of words");
		$this->assertTrue( in_array( "string", array_keys( $w->getFrequencies() ) ) );
		$this->assertTrue( in_array( "this", array_keys( $w->getFrequencies() ) ) );
		$this->assertTrue( in_array( "words", array_keys( $w->getFrequencies() ) ) );
    }
	
	public function test_can_train_with_array() {
		$w = new WordTrainer( array( "this", "is", "a", "string", "of", "words" ) );
		$this->assertTrue( in_array( "string", array_keys( $w->getFrequencies() ) ) );
		$this->assertTrue( in_array( "this", array_keys( $w->getFrequencies() ) ) );
		$this->assertTrue( in_array( "words", array_keys( $w->getFrequencies() ) ) );
	}
	
	public function test_correct_frequency_calculation() {
		$freq = $this->w->getFrequencies();
		$this->assertEquals( null, @$freq['bun'] );
		$this->assertEquals( 3, $freq['fun'] );
		$this->assertEquals( 2, $freq['gun'] );
	}
}