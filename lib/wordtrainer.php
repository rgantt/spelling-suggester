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
			$this->freq = $this->train( $dict_path );
			return;
		}

		if( is_string( $dict_path ) && preg_match( "/\s/", $dict_path ) && !preg_match( "/\\\/", $dict_path ) ) {
			$this->freq = $this->train( $this->words( $dict_path ) );
			return;
		}
		
		$path_serialized = $dict_path . ".freq";
		if ( file_exists( $path_serialized ) && !$force ) {
			$this->freq = unserialize( file_get_contents( $path_serialized ) );
			return;
		}

		$this->load_dict( $dict_path );

		if ( is_writable( dirname( $path_serialized ) ) ) {
			file_put_contents( $path_serialized, serialize( $this->freq ) );
		}
	}
	
	public function &getFrequencies() {
		return $this->freq;
	}
	
	private function train( &$features ) {
		$model = array();
		foreach( $features as $f ) {
			$model[ $f ] = !isset( $model[ $f ] ) ? 1 : $model[ $f ] + 1;
		}
		return $model;
	}
	
	/**
	 * Going to do the loading and training in one step
	 */
	private function load_dict( $path ) {
		if( !file_exists( $path ) || !is_readable( $path ) ) throw new Exception("cannot read {$path}");
		$file = fopen( $path, 'r' );
		while( !feof( $file ) ) {
			$words = $this->words( fgets( $file ) );
			foreach( $words as $w ) {
				if( strlen( $w = trim( $w ) ) > 0 ) {
					$this->freq[ $w ] = !isset( $this->freq[ $w ] ) ? 1 : $this->freq[ $w ] + 1;
				}
			}
		}
	}
	
	public function words( $text ) {
		return preg_split( '/(\w+)/', strtolower( $text ), null, PREG_SPLIT_DELIM_CAPTURE );
	}
}