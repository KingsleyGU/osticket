<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
if($tid=$_POST['ticketId'])
{
	echo $tid;
}
else
	echo 'false';

?>