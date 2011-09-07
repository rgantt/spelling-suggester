<?php
require_once "rolex/rolex.php";
require_once "suggester.php";

use rolex\r;

$misspells = array( 
	"crav", "cavet", "sphagetti", "fone", "phon", "televisio", "tlevision", "definately", "cnjunctin", "persue"
);

$s = null;
echo r::run( "instantiation", function() use ( &$s ){
	$s = new Suggest("notsobig.txt");
});

echo r::run( "corrections", function() use( $s, $misspells ) {
	foreach( $misspells as $word ) {
		echo "you said '{$word}'. did you mean '{$s( $word )}'?\n";
	}
});

foreach( $misspells as $word ) {
	echo "you said '{$word}'. did you mean '{$s( $word )}'?\n";
}

echo memory_get_peak_usage();