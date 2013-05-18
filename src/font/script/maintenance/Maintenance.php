<?php
define('RUN_MAINTENANCE_IF_MAIN', dirname(__FILE__) . "/run.php");

/* A pretend class so that we can run the generateTables script */
class Maintenance {
	function __construct() {
		
	}
}
