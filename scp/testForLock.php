<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.spent_time.php');


$currentTime = new DateTime();
Spent_time::create(1,1,$currentTime);


?>