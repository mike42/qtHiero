#!/usr/bin/env php
<?php
/**
 * This script scales down an image and returns a percentage (the size of the
 * new image as a percentage of the old size). It is used to make sure all of
 * the glyphs are resized by the same amount */
if(count($argv) != 3) {
	die("Usage: ".$argv[0]." [path] [geometry]\n");
}

if(!extension_loaded('imagick')) {
	die("This script requires the Imagick extension, which is not loaded.");
}

$image_src = $argv[1];
$geometry = $argv[2];

$image = new Imagick($image_src); 
$d = $image->getImageGeometry(); 
$w1 = $d['width']; 
$h1 = $d['height']; 
$image ->destroy();

/* We could use $image -> scaleImage, but I chose not to as this command will
   be used for all later resizing */
$cmd = "mogrify -resize " . escapeshellarg($geometry) . " " .escapeshellarg($image_src);
system($cmd);

$image = new Imagick($image_src); 
$d = $image->getImageGeometry(); 
$w2 = $d['width']; 
$h2 = $d['height']; 
$image ->destroy();

echo round(($h2 / $h1) * 100,2)."%";
?>
