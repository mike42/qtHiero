#!/usr/bin/env php
<html>
<head>
	<style type="text/css">
		body {
			background: #ccc;
		}

		td {
			padding: 1em;
			border: 1px solid #eee;
		}
	</style>
</head>
<body>
<table>
<tr>
	<th>File</th>
	<th>Old</th>
	<th>New</th>
</tr>
<?php
	$conf = array(
		'orig' => dirname(__FILE__)."/glyph/original",
		'new' => dirname(__FILE__)."/../../vendor/wikihiero/img");

	$new = scandir($conf['new']);
	$orig = scandir($conf['orig']);

	$comp = array();
	foreach($orig as $fn) {
		if(substr($fn, 0, 1) != ".") {
			$comp[$fn]['orig'] = true;
		}
	}

	foreach($new as $fn) {
		if(substr($fn, 0, 1) != ".") {
			$comp[$fn]['new'] = true;
		}
	}

	foreach($comp as $fn => $entry) {
		echo "<tr>\n";
		echo "\t<th>".htmlspecialchars($fn) . "</th>\n";
		if(isset($entry['orig'])) {
			echo "\t<td><img src=\"original/".urlencode("$fn")."\" /></td>\n";
		} else {
			echo "\t<td>&mdash;</td>\n";
		}
		if(isset($entry['new'])) {
			echo "\t<td><img src=\"../../../vendor/wikihiero/img/".urlencode("$fn")."\" /></td>\n";
		} else {
			echo "\t<td>&mdash;</td>\n";
		}
		echo "</tr>\n";
	}
?>
</table>
</body>
</html>
