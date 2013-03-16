#!/usr/bin/php
<?php
/**
 * render.php: Get HTML from glyphs
 **/

/* Some dummy MediaWiki code to keep wikihiero happy */
define('MEDIAWIKI', true);
$wgExtensionAssetsPath = "file://".dirname(__FILE__)."/../vendor";
class MWInit {
	function isHipHop() {
		return false;
	}
}
require_once(dirname(__FILE__)."/../vendor/wikihiero/wikihiero.body.php");

/* Get MdC and render it */
$code = file_get_contents("php://stdin");
$hiero = new WikiHiero();
$stylesheet = $wgExtensionAssetsPath."/wikihiero/modules/ext.wikihiero.css";
echo "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"".htmlspecialchars($stylesheet)."\"></head><body>\n";
echo $hiero -> render($code);
echo "\t</body>\n</html>";
?>
