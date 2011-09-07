h1. Spelling suggester

a probabilistic misspelling corrector based on "peter norvig's":http://norvig.com/spell-correct.html

h2. Usage

To use, simply instantiate the @Suggest@ class by feeding it a dictionary. The format of the dictionary is pretty forgiving; @Suggest@ will strip out the formatting characters and then break up all of the words according to their space-delimited boundaries.

Once instantiated, run @Suggest::correct( $misspelled_word )@ to generate some suggestions. Training the object with the dictionary will take the majority of the time; each suggestion should be quick.

pre. require_once "suggester.php";

$misspells = array( 
	"crav", "cavet", "sphagetti", "fone", "phon", "televisio", "tlevision", "definately", "cnjunctin", "persue"
);

$s = new Suggest("notsobig.txt");

foreach( $misspells as $word ) {
	echo "you said '{$word}'. did you mean '{$s( $word )}'?\n";
}

h3. Notes

I had difficulty finding a way to quickly load a large dictionary using PHP without increasing the memory limit well above the 128Mb default for peak load. For best results (when using the included @big.txt@ fixture), aim for ~256Mb of memory capacity for PHP.