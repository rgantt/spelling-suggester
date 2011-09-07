<?php
// error_reporting( E_ALL | E_STRICT ); // shouldn't enable, since there are in-loop-body notices
error_reporting( E_ALL );
ini_set("memory_limit","256M"); // can't seem to do this without ~250MB of peak memory on big.txt

class Suggest {
	public $freq = array();
	private $alphabet = "abcdefghijklmnopqrstuvwxyz";
	
	public function __construct( $dict_path ) {
		$this->alphabet = str_split( $this->alphabet );
		$words = $this->load_dict( $dict_path );
		$this->freq = $this->train( $words );
	}
	
	public function __invoke( $word ) {
		$candidates = $this->known( array( $word ) );
		if( empty( $candidates ) ) $candidates = $this->known( $this->edits( $word ) );
		if( empty( $candidates ) ) $candidates = $this->known_edits( $word );
		if( empty( $candidates ) ) $candidates = array( $word );
		$that = $this;
		return $this->argmax( $candidates, function ( $w ) use ( $that ) {
			$that->freq[ $w ];
		});
	}
	
	private function argmax( array $array, $cbe ) {
		$tmp = array();
		foreach( $array as $a ) {
			$tmp[ $cbe( $a ) ] = $a;
		}
		return $tmp[ max( array_flip( $tmp ) ) ];
	}
	
	private function load_dict( $path ) {
		if( !file_exists( $path ) || !is_readable( $path ) ) throw new Exception("cannot read {$path}");
		$file = file( $path );
		$that = $this;
		$allwords = array();
		array_walk( $file, function( $line ) use ( &$allwords, $that ) {
			// E_STRICT doesn't like us using &$allwords in this closure
			array_walk( $that->words( $line ), function( $word ) use ( &$allwords ) {
				$allwords[] = $word;
			});
		});
		return $allwords;
	}
	
	// must be public so that closures can call it... ugh
	public function words( $text ) {
		return preg_split( '/(\w+)/', strtolower( $text ), null, PREG_SPLIT_DELIM_CAPTURE );
	}
	
	// passing this by reference saves a lot of memory at peak
	private function train( &$features ) {
		$model = array();
		foreach( $features as $f ) {
			$model[ $f ] = !isset( $model[ $f ] ) ? 0 : $model[ $f ] + 1;
		}
		return $model;
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