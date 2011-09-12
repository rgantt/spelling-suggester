<?php

/**
 * Factored out the wordlist and frequency-generating portion
 * of the code. Moving toward inversion of control between the
 * word set and the suggestion of spelling alternatives.
 */
class WordTrainer {
	private $words;
	private $freq;
	
	/**
	 * Want to be able to accept collections of words in one of 
	 * three formats:
	 * 
	 *    1. Arrays of words
	 *    2. Strings
	 *    3. A path to a document
	 *
	 * The third is the most time consuming to process, so we'd like
	 * to process it and save the results for later consumption.
	 */
	public function __construct( $dict_path, $force = false ) {
		if( is_array( $dict_path ) ) {
			$this->words = $dict_path;
			$this->freq = $this->train( $this->words );
			return;
		}

		if( is_string( $dict_path ) && preg_match( "/\s/", $dict_path ) && !preg_match( "/\\\/", $dict_path ) ) {
			$this->words = $this->words( $dict_path );
			$this->freq = $this->train( $this->words );
			return;
		}
		
		$path_serialized = $dict_path . ".freq";
		if ( file_exists( $path_serialized ) && !$force ) {
			$this->freq = unserialize( file_get_contents( $path_serialized ) );
			return;
		}

		$this->words = $this->load_dict( $dict_path );
		$this->freq = $this->train( $this->words );

		if ( is_writable( dirname( $path_serialized ) ) ) {
			file_put_contents( $path_serialized, serialize( $this->freq ) );
		}
	}
	
	public function &getFrequencies() {
		return $this->freq;
	}
	
	public function &getWords() {
		return $this->words;
	}
	
	private function train( &$features ) {
		$model = array();
		foreach( $features as $f ) {
			$model[ $f ] = !isset( $model[ $f ] ) ? 1 : $model[ $f ] + 1;
		}
		return $model;
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
	
	public function words( $text ) {
		return preg_split( '/(\w+)/', strtolower( $text ), null, PREG_SPLIT_DELIM_CAPTURE );
	}
}