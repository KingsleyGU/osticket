<form method="POST" action="/osticket/scp/uploadFilesToThread.php">
    <input type="hidden" name="ticket_id" value="<?php echo $_REQUEST['ticket_id'];?>">
    <input type="hidden" name="thread_id" value="<?php echo $_REQUEST['thread_id'];?>">
    <div id="reply_form_attachments" class="attachments">
    <?php
    print $response_form->getField('attachments')->render();
    ?>
    <input type='submit' value="Save">
</form>