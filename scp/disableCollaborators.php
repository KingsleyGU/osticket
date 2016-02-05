<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
if(($tid=$_POST['ticketId'])&&($ticket=Ticket::lookup($tid))&&$ticket->deActiveCollaborators())
{
	echo $tid;
}
else
	echo 'false';

?>