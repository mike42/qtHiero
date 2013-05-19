#!/usr/bin/env php
<?php
$a = explode("\n", file_get_contents("php://stdin"));

$everything = "";
$missing = "";
$d = 1;
foreach($a as $b) {
	$c = explode("\t", $b);
	if(count($c) == 4) {
		/* Add to everything */
		$everything .= $c[1].(($d % 20 == 0) ? "!\n" : "-");
		$d++;

		/* Check */
		if(!file_exists(dirname(__FILE__) . "/../../vendor/wikihiero/img/hiero_".$c[1].".png")) {
			$missing .= $c[1] . ' ';
		}
	}
}
$everything .= "\n";
file_put_contents("everything-mdc.txt", $everything);
file_put_contents("missing.txt", $missing);

?>
