<?php
require_once "../suggest.php";

$misspells = array( 
	"crav", "cavet", "fone", "phon", "televisio", "tlevision", "definately", "cnjunctin", "persue"
);

$s = new Suggest("notsobig.txt");
foreach( $misspells as $word ) {
	echo "you said '{$word}'. did you mean '{$s( $word )}'?\n";
}

echo memory_get_peak_usage();
