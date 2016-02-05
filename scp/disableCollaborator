<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');

if($tid=$_REQUEST['ticketID']&&$ticket=Ticket::lookup($tid)&&$ticket->deActiveCollaborators())
{
	echo 'true';
}
else
	echo 'false';

?>