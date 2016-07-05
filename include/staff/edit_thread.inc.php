<?php
    $threadContent = "";
    if(isset($_REQUEST['thread_id'])&&is_numeric($_REQUEST['thread_id']))
    {
        $thread = ThreadEntry::lookup($_REQUEST['thread_id']);
        $threadContent = $thread->getMessage();
    }

?>



<form method="POST" action="/scp/modifyThread.php">
    <?php csrf_token(); ?>
    <input type="hidden" name="ticket_id" value="<?php echo $_REQUEST['ticket_id'];?>">
    <input type="hidden" name="thread_id" value="<?php echo $_REQUEST['thread_id'];?>">
    <label>Original Content:</label>
    <?php echo $threadContent; ?><br><br><br>
    <label>Input the new content:</label>
    <textarea name="thread_content" style="width:100%;height:250px;"></textarea>

    <div id="reply_form_attachments" class="attachments">
    <?php
    print $response_form->getField('attachments')->render();
    ?>
    <input type='submit' value="Save">
</form>