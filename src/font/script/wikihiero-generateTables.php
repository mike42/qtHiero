<?
/* Same code as render.php to keep wikihiero happy */
define('MEDIAWIKI', true);
$wgExtensionAssetsPath = "";
class MWInit {
	function isHipHop() {
		return false;
	}
	function extCompiledPath() {
	}
}

require_once(dirname(__FILE__)."/../../../vendor/wikihiero/wikihiero.php");
require_once(dirname(__FILE__)."/../../../vendor/wikihiero/wikihiero.body.php");

putenv( 'MW_INSTALL_PATH='. dirname(__FILE__));
chdir($dir);
require_once(dirname(__FILE__)."/../../../vendor/wikihiero/generateTables.php");

?>
