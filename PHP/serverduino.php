<?php
/**
 * init file
 */

require_once "config.inc.php";

$serverduino = new ServerDuino($config);
$serverduino->run();
?>