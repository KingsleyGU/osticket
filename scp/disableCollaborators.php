<?php

// this is an ajax API to remove the collaborators for a ticket
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
if(($tid=$_POST['ticketId'])&&($ticket=Ticket::lookup($tid))&&($ticket->deleteCollaborators()))
{
	echo $tid;
}
else
	echo 'false';

?>