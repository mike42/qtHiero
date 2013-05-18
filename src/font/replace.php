#!/usr/bin/env php
<?php

# Get list of glyphs to use.
$img = dirname(__FILE__)."/../../vendor/wikihiero/img";
$wh = scandir($img);

foreach($wh as $fname) {
	if($fname != "." && $fname != "..") {
		replace($img, $fname);
	}
}

function replace($path, $fname) {
	$glyph = basename(str_replace("hiero_", "", $fname), ".png");
	if(substr($glyph, 0, 2) != "Ba" && substr($glyph, 0, 2) != "Ca") {
		if(substr($glyph, 0, 2) == "Aa") {
			/* Aegyptus puts these under 'J' */
			$glyph = "J" . substr($glyph, 2);
		}

		$letter = substr($glyph, 0, 1);
		$source = dirname(__FILE__) . "/glyph/small/$letter/$glyph"."_Aegyptus.png";
		$dest = $path . "/" . $fname;
		if(!file_exists($source) && substr($glyph, -1) == "s") {
			/* Scale down an image to 70% for "small" */
			$small = $source;
			$large = strtoupper(substr($glyph, 0, strlen($glyph) - 1));
			$source = dirname(__FILE__) . "/glyph/small/$letter/$large"."_Aegyptus.png";
			if(file_exists($source)) {
				/* Source glyph exists, generate smaller one */
				echo "Shrinking $large to make $glyph ...";
				system("cp " . escapeshellarg($source) . " " . escapeshellarg($small));
				system("mogrify -resize 70% " . escapeshellarg($small));
			} else {
				echo "Couldn't find $large to scale down\n";
			}
		}
		if(!file_exists($source)) {
			echo $letter . " " . $glyph  . " not found\n";
		} else {
			copy($source, $dest);
		}
	}
}

?>
