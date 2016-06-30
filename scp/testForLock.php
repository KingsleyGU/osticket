<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.spent_time.php');


$now = new DateTime();
$currentTime = $now->format('Y-m-d H:i:s');
Spent_time::create(1,1,$currentTime);


?>