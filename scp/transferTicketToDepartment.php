<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');

if($_REQUEST['transferDeptId']==0)
{
	echo ("not an available department");
}
else{
    $dept=Dept::lookup($_REQUEST['transferDeptId']);
    $count = count($_REQUEST['tids']);
    $i=0;
    foreach ($_REQUEST['tids'] as $tid) {
        if (($ticket=Ticket::lookup($tid)))
        {
        	$ticket->setDeptId($_REQUEST['transferDeptId']);
        	$ticket->setStaffId(0);
        	$ticket->setTeamId(0);
            $ticket->setSLAId($dept->getSLAId());

        	if($ticket->getTeamId()==0&&$ticket->getStaffId()==0)
        		$i++;
        }

    }

	echo "Transferred ".$i." tickets successfully";
}
?>