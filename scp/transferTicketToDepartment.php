<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
            // foreach ($_REQUEST['tids'] as $tid) {
            //     if (($ticket=Ticket::lookup($tid))
            //             && $ticket->getStatusId() != $status->getId()
            //             && $ticket->checkStaffAccess($thisstaff)
            //             && $ticket->setStatus($status, $comments))
            //         $i++;
            // }
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
        //         && $ticket->setDeptId($_REQUEST['transferDeptId'])
        //         && $ticket->setStaffId(0)
        //         && $ticket->setTeamId(0))
        //     $i++;
        // if (($ticket=Ticket::lookup($tid)))
        // 	$i="look up yes; ";
        // if($ticket->setDeptId($_REQUEST['transferDeptId']))
        // 	$i.="department yes; ";
        // if($ticket->setStaffId(0))
        // 	$i.="staff yes; ";
        // if($ticket->setTeamId(0))
        // 	$i.="team yes; ";
    }
    // if (!$i)
    // 	echo "not transferring successfully";
    // // else if ($i==$count)
    // // 	echo "transferring successfully";
    // else
    	echo "Transferred ".$i." tickets successfully";
}
?>