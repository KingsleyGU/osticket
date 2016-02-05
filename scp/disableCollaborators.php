<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
if($tid=$_REQUEST['ticketID'])
{
	echo 'true';
}
else
	echo 'false';

?>