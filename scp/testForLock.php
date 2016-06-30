<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.spent_time.php');

error_reporting(~0); ini_set('display_errors', 1);
$currentTime = new DateTime('0000-00-00 00:00:00', new DateTimeZone('Europe/Paris'));
// $currentTime = $now->format('Y-m-d H:i:s');
Spent_time::create(1,1,$currentTime);


?>