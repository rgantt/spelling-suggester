<?php
// shouldn't enable, since there are in-loop-body notices due to known closure issues
// error_reporting( E_ALL | E_STRICT );
error_reporting( E_ALL );
ini_set( "memory_limit", "256M" );

require_once dirname(__FILE__).'/lib/wordtrainer.php';

/**
 * spelling-suggester, a simple spelling corrector written for PHP 5.3, based on
 * Peter Norvig's python implementation.
 *
 * see the following article for more information on PHP's memory usage for arrays:
 * http://www.pankaj-k.net/weblog/2008/03/did_you_know_that_each_integer.html
 *
 * see the following article for Peter Norvig's original implementation in python:
 * http://norvig.com/spell-correct.html
 *
 * at the expense of proper encapsulation, several members are public where otherwise 
 * i would prefer they be private. this is to accomodate shortcomings in PHP 5.3's 
 * handling of closures (namely that they don't close over $this in object context).
 */

class Suggest {
	public $freq = array();
	private $alphabet = "abcdefghijklmnopqrstuvwxyz";
	
	/**
	 * decoupled from the type of dictionary provided at the expense
	 * of no longer being a "one-file" program
	 */
	public function __construct( $dict_path, $force = false ) {
		$this->alphabet = str_split( $this->alphabet );
		$words = new WordTrainer( $dict_path, $force );
		$this->freq = $words->getFrequencies();
	}
	
	public function __invoke( $word ) {
		$candidates = $this->known( array( $word ) );
		if( empty( $candidates ) ) $candidates = $this->known( $this->edits( $word ) );
		if( empty( $candidates ) ) $candidates = $this->known_edits( $word );
		if( empty( $candidates ) ) $candidates = array( $word );
		$that = $this;
		return $this->argmax( $candidates, function ( $w ) use ( $that ) {
			return $that->freq[ $w ];
		});
	}
	
	private function argmax( array $array, $cbe ) {
		$tmp = array();
		foreach( $array as $a ) {
			$tmp[ $cbe( $a ) ] = $a;
		}
		return $tmp[ max( array_flip( $tmp ) ) ];
	}
	
	private function edits( $word ) {
		$splits = $deletes = $transposes = $inserts = array();
		for( $i = 0; $i < strlen( $word ) + 1; $i++ ) {
			$splits[ substr( $word, 0, $i ) ] = substr( $word, $i );
		}
		foreach( $splits as $a => $b ) {
			if( !empty( $b ) ) {
				$deletes[] = $a.substr( $b, 1 );
				foreach( $this->alphabet as $c ) {
					$replaces[] = $a.$c.substr( $b, 1 );
				}
			}
			if( strlen( $b ) > 1 ) {
				$transposes[] = $a.$b{1}.$b{0}.substr( $b, 2 );
			}
			foreach( $this->alphabet as $c ) {
				$inserts[] = $a.$c.$b;
			}
		}
		return array_merge( $deletes, $transposes, $replaces, $inserts );
	}
	
	private function known_edits( $word ) {
		$edits = array();
		$ew1 = $this->edits( $word );
		foreach( $ew1 as $e1 ) {
			$ew2 = $this->edits( $e1 );
			foreach( $ew2 as $e2 ) {
				if( array_key_exists( $e2, $this->freq ) ) $edits[] = $e2;
			}
		}
		return $edits;		
	}
	
	private function known( $words ) {
		$that = $this;
		return array_filter( $words, function( $w ) use ( $that ){ 
			return array_key_exists( $w, $that->freq );
		});
	}
}