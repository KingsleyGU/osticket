<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.attachment.php');
require_once(INCLUDE_DIR.'class.thread.php');

error_reporting(~0); ini_set('display_errors', 1);
echo "1111";
if(!isset($_REQUEST['thread_id']))
{
	echo ("Thread ID not provided");
}
elseif(is_numeric($_REQUEST['thread_id'])&&($thread = ThreadEntry::lookup($_REQUEST['thread_id'])))
{
    foreach ($_REQUEST['attach:response'] as $fileID) {
    	$thread->saveAttachment($fileID);
    }
	header("Location: /scp/tickets.php?id=".$_REQUEST['ticket_id']);
}
if($_REQUEST['thread_content']!=null&&$_REQUEST['thread_content']!=""&&$thread->setBody($_REQUEST['thread_content']))
{
	header("Location: /scp/tickets.php?id=".$_REQUEST['ticket_id']);
}


?>