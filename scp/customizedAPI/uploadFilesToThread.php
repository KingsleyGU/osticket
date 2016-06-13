<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.attachment.php');
require_once(INCLUDE_DIR.'class.thread.php');


if(!isset($_REQUEST['thread_id']))
{
	echo ("Thread ID not provided");
}
elseif(is_numeric($_REQUEST['thread_id'])&&$thread = ThreadEntry::lookup($_REQUEST['thread_id']))
{
	$thread->saveAttachments($_REQUEST['attach:response[]']);
	header("Location: /osticket/scp/tickets.php?id=".$_REQUEST['ticket_id']);
}



?>