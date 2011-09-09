<?php
require_once dirname(__FILE__).'/../suggest.php';

class suggest_test extends \PHPUnit_Framework_TestCase {
    public function test_suggests_highest_probability_word() {
        $s = new Suggest( array( 'fun', 'fun', 'fun', 'gun', 'gun', 'sun', 'run' ) );
        $this->assertEquals( "fun", $s("kun") );
		$this->assertEquals( "fun", $s("iun") );
    }
}