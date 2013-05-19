#!/usr/bin/env php
<?php
/* This accounts for filenames in Wikihiero which don't follow good naming
	conventions. The keys are the filename, the values are a better (parse-able) name. */
$wh_map = array(
	'Y1V' => 'Y1v', // should be lowercase 'v' for vertical
	'Aa30A'=>'Aa30h', // Should be lowercase 'h' for horizontal
	'O29V' => 'O29v', // Lowercase v again
	'G7AA' => 'G7B', // Just named differently
	'N33C' => 'N33s', // Smaller version
	'N33B' => 'N33ss', // Smaller again
	'V10A' => 'V11A', // Named differently
	'V11A' => 'V11B', // Named differently
	'P8H'  => 'P8h', // Lowercase h for horizontal
	'T8B'  => 'T8Ah', // Lowercase h for horizontal (glyoh is not in unicode)
);

$conf = array(
	'img' => dirname(__FILE__)."/../../vendor/wikihiero/img",
	'font'=> '/usr/share/fonts/truetype/ttf-ancient-scripts/Aegyptus313.ttf', // Default Debian path
	'size'=>"38x38",		// Glyph size
	'reference'=>'A1_Aegyptus', // Reference glyph for sizing
	'darken' => true,		// Add dark blur to avoid very light glyphs at low resolutions
	'optimise' => true,	// Pass through optipng (hasn't managed to optimise any of the output files I've seen)
	'run' => '.generate.run',
	'small-scale' => '70%'); // How much smaller a 's' glyph is

if(!extension_loaded('imagick')) {
	die("This script requires the Imagick extension, which is not loaded.");
}

/* Dump font */
$fontdir = dirname(__FILE__)."/glyph/eps/";
if(!file_exists($fontdir)) {
	dump_font($conf['font'], $fontdir);
}

/* Make some directories */
$pngdir = dirname(__FILE__)."/glyph/png/";
if(!file_exists($pngdir)) {
	mkdir($pngdir);
}

$originaldir = dirname(__FILE__)."/glyph/original/";
if(!file_exists($originaldir)) {
	mkdir($originaldir);
}

$smallpngdir = dirname(__FILE__)."/glyph/smallpng/";
if(!file_exists($smallpngdir)) {
	mkdir($smallpngdir);
}

/* Identify each file */
$wh = scandir($conf['img']);
$glyph = array(
	"prefab" => array(),
	"gardiner" => array(),
	"special" => array(),
	"unknown" => array());
foreach($wh as $fname) {
	if(str_begins_with($fname, ".")) {
		// Ignore hidden files
	} else if(str_contains($fname, "&")
			|| $fname == "hiero_H8W.png") { // H8W is prefab!
		$glyph['prefab'][] = $fname;
	} else if(str_begins_with($fname, "hiero_Ba") || str_begins_with($fname, "hiero_Ca")) {
		$glyph['special'][] = $fname;
	} elseif($code = parse_gardiner_code(fname_get_gardiner_code($fname))) {
		$code['fname'] = $fname;
		$glyph['gardiner'][] = $code;
	} else {
		$glyph['unknown'][] = $fname;
	}
}

/* Add extra requested glyphs */
foreach($argv as $key => $a) {
	if($key > 0 && $code = parse_gardiner_code($a)) {
		$code['fname'] = "hiero_$a.png";
		$glyph['gardiner'][] = $code;
	}
}

/* Process glyphs */
$conf['scale'] = get_scale($fontdir, $conf);
foreach($glyph['gardiner'] as $key => $code) {
	if(!file_exists($originaldir . $code['fname'])) {
		echo " * " . $code['fname'] . " (" . ($key + 1) . " / " . count($glyph['gardiner']) .")\n";
		process_glyph($code, $fontdir, $pngdir, $smallpngdir, $originaldir, $conf);
	}
}

/* Process oddities */
echo "Processing unknown oddities: \n";
$done = 0;
foreach($glyph['unknown'] as $fname) {
	if(!file_exists($originaldir . $fname)) {
		echo " * $fname\n";
		$src = $conf['img'] . "/" . $fname;
		copy($src, $originaldir . $fname);
		lazy_process($conf['img'] . "/" . $fname);
		if($conf['optimise']) {
			optimise($src);
		}
		$done++;
	}
}
echo " ($done processed)\n";

/* Process cartouches and formatting marks */
echo "Processing special glyphs: \n";
$done = 0;
foreach($glyph['special'] as $fname) {
	if(!file_exists($originaldir . $fname)) {
		echo " * $fname\n";
		$src = $conf['img'] . "/" . $fname;
		copy($src, $originaldir . $fname);
		lazy_process($src);
		if($conf['optimise']) {
			optimise($src);
		}
		$done++;
	}
}
echo " ($done) processed)\n";

/* Delete prefabs */
echo "Removing prefabricated glyphs: ";
foreach($glyph['prefab'] as $fname) {
	$src = $conf['img'] . "/" . $fname;
	rename($src, $originaldir . $fname);
	echo ".";
}
echo " (" .  count($glyph['prefab']) ." removed)\n";

echo "Regenerating tables .. ";
system("php " . escapeshellarg(dirname(__FILE__) . "/script/wikihiero-generateTables.php"));
echo "Done!\n";

function dump_font($font, $dest) {
	mkdir($dest);
	chdir($dest);
	system("fontforge -lang=ff -script ../../script/export.pe " . escapeshellarg($font));
}

function get_scale($fontdir, $conf) {
	echo " * Calculating scale ...\n";
	$eps = $fontdir . $conf['reference'] . ".eps";
	$large = dirname(__FILE__)."/glyph/reference_large.png";
	$small = dirname(__FILE__)."/glyph/reference_small.png";
	generate_png($eps, $large);
	shrink($large, $small, $conf['size']);

	$image = new Imagick($large); 
	$d = $image->getImageGeometry(); 
	$w1 = $d['width']; 
	$h1 = $d['height']; 
	$image ->destroy();

	$image = new Imagick($small); 
	$d = $image->getImageGeometry(); 
	$w2 = $d['width']; 
	$h2 = $d['height']; 
	$image ->destroy();
	$scale = round(($h2 / $h1) * 100,2)."%";
	echo " * Scaling will be done to $scale\n";
	return $scale;
}

function process_glyph($code, $fontdir, $pngdir, $smallpngdir, $originaldir, $conf) {
	$eps = $fontdir . code_to_aegyptus_fn($code) . ".eps";
	$png = $pngdir . $code['fname'];
	$small = $smallpngdir . $code['fname'];
	$src = $conf['img'] . "/" . $code['fname'];

	if(!file_exists($eps)) {
		/* Unknown glyph! Just hit it with a transparency filter */
		if(!file_exists($originaldir . $code['fname'])) {
			copy($src, $originaldir . $code['fname']);
			lazy_process($src);
		}
		return;
	}

	/* Generate and shrink */
	generate_png($eps, $png);
	if(shrink($png, $small, $conf['scale'])) {
		if($code['small'] != 0) {
			for($i = $code['small']; $i > 0; $i--) {
				reshrink($small, $conf['small-scale']);
			}
		}

		if($conf['darken']) {
			darken($small);
		}

		if($conf['optimise']) {
			optimise($small);
		}

		if(file_exists($src)) {
			rename($src, $originaldir . $code['fname']);
		}
		rename($small, $src);
	}
}

function shrink($large, $small, $scale) {
	if(!file_exists($small)) {
		copy($large, $small);
		echo "	 * Resizing to $scale ...";
		$cmd = "mogrify -resize " . escapeshellarg($scale) . " " .
			escapeshellarg($small);
		system($cmd);
		echo " done\n";
		return true;
	}
	return false;
}

function reshrink($small, $scale) {
	echo "	 * Scaling again to $scale ...";
	$cmd = "mogrify -resize " . escapeshellarg($scale) . " " .
		escapeshellarg($small);
	system($cmd);
	echo " done\n";
}

function optimise($fname) {
	echo "	 * Optimising ...";
	$cmd = "optipng --quiet -o7 " . escapeshellarg($fname);
	system($cmd);
	echo " done\n";
}

function darken($fname) {
	echo "	 * Darkening ...";
	$cmd = "convert " . escapeshellarg($fname) . " \\(  +clone \\\n" .
		"-channel A -blur 0x0.001 -level 0,50% +channel \\\n" .
		"+level-colors black \\\n" .
		"\\) -compose DstOver  -composite " . escapeshellarg($fname);
	system($cmd);
	echo " done\n";
}

function generate_png($eps, $png) {
	if(!file_exists($png)) {
		echo "	 * Generating png ...";
		$cmd = "convert " . escapeshellarg($eps) .
				" " . escapeshellarg($png);
		system($cmd);
		echo " done\n";
	}
}

function lazy_process($fname) {
	echo "	 * Making transparent ...";
	$cmd = "convert " . escapeshellarg($fname) .
			" -background none " .
			"-fuzz 70% " . 
			"-transparent white " .
			"-flatten " . escapeshellarg($fname);
	system($cmd);
	echo " done\n";
}

function code_to_aegyptus_fn($code) {
	$letter = $code['letter'] == "Aa" ? "J" : $code['letter']; // Aa glyphs are under 'J' in Aegyptus
	$suff = $code['suff'] . ($code['v'] ? 'v' : '') . ($code['h'] ? 'h' : '');
	return $letter . $code['num'] . $suff . "_Aegyptus";
}

function str_contains($haystack, $needle) {
	return !(strpos($haystack, $needle) === false);
}

function str_begins_with($haystack, $needle) {
	return substr($haystack, 0, strlen($needle)) == $needle;
}

function fname_get_gardiner_code($fname) {
	global $wh_map;
	$prefix = "hiero_";
	if(str_begins_with($fname, $prefix)) {
		$fname = substr($fname, strlen($prefix), strlen($fname) - strlen($prefix)); 
	}
	$fname =  basename($fname, ".png");
	if(isset($wh_map[$fname])) {
		/* Corrects differences in naming */
		return $wh_map[$fname];
	}
	return $fname;
}

function parse_gardiner_code($code) {
	/* Letter */
	$letter = substr($code, 0, 1);
	if($letter != strtoupper($letter)) {
		return false;
	}

	/* Check those pesky Aa glyphs */
	if($letter == "A" && substr($code, 1, 1) == "a") {
		$letter = "Aa";
		$code = substr($code, 2, strlen($code) - 2); /* Trim first two characters */
	} else {
		$code = substr($code, 1, strlen($code) - 1); /* Trim first character */
	}

	/* Check for 's' */
	$small = 0;
	while(strtolower(substr($code, -1)) == "s") {
		$small++;
		$code = substr($code, 0, strlen($code) - 1); // Trim last character
	}

	/* Get number and suffix */
	$num = "";
	$suff = "";
	$horizontal = false;
	$vertical = false;
	for($i = 0; $i < strlen($code); $i++) {
		$c = substr($code, $i, 1);
		if(is_numeric($c)) {
			$num .= $c;
		} else {
			if($c == "h") {
				$horizontal = true;
			} elseif($c == "v") {
				$vertical = true;
			} else {
				$suff .= strtoupper($c);
			}
		}
	}
	
	if((int)$num == 0) {
		return false;
	}

	return array('letter' => $letter, 'num' => $num, "suff" => $suff, "small" => $small, "h" => $horizontal, "v" => $vertical);
}
?>
