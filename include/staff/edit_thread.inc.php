   <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data" class="tab-response-block">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <input type="hidden" name="thread_list" class="thread_list" value="">
        <span class="error"></span>
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
           <tbody id="to_sec">
            <tr>
                <td width="120">
                    <label><strong><?php echo __('To'); ?>:</strong></label>
                </td>
                <td>
                    <?php
                    # XXX: Add user-to-name and user-to-email HTML ID#s
                    $to =sprintf('%s &lt;%s&gt;',
                            Format::htmlchars($ticket->getName()),
                            $ticket->getReplyToEmail());
                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                    ?>
                    <select id="emailreply" name="emailreply">
                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                        >&mdash; <?php echo __('Do Not Email Reply'); ?> &mdash;</option>
                        <option value="2" id="forward-email-option">Videresend</option>
                    </select>
                    <button id="disableCollaboratorsButton" onclick="disableCollaborators(<?php echo $ticket->getId();?>); return false;" style="display:none;">click me</button>
                    <span id="ticket-external-receivers" style="display:none;">
                        <?php
                        $recipients = __('Add Recipients');
                        if ($ticket->getNumCollaborators())
                            // $recipients = sprintf(__('Receivers (%d of %d)'),
                            //         $ticket->getNumActiveCollaborators(),
                            //         $ticket->getNumCollaborators());
                            $recipients = sprintf(__('Receivers'));
                        echo sprintf('<span><a class="collaborators preview"
                                href="#tickets/%d/collaborators"><span id="recipients">%s</span></a></span>',
                                $ticket->getId(),
                                $recipients);
                       ?>
                    </span>
                    <a id="choose_all_threads_btn" style="text-decoration:none;" href="#" class="button">Choose all threads to reply</a>
                </td>
                


            </tr>
            </tbody>
            <?php
            if(1) { //Make CC optional feature? NO, for now.
                ?>
            <tbody id="cc_sec"
                style="display:<?php echo $emailReply?  'table-row-group':'none'; ?>;">
             <tr>
                <td width="120">
                    <label><strong><?php echo __('Collaborators'); ?>:</strong></label>
                </td>
                <td>
                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $ticket->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    $recipients = __('Add Recipients');
                    if ($ticket->getNumCollaborators())
                        $recipients = sprintf(__('Recipients (%d of %d)'),
                                $ticket->getNumActiveCollaborators(),
                                $ticket->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#tickets/%d/collaborators"><span id="recipients">%s</span></a></span>',
                            $ticket->getId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <?php
            } ?>
            <tbody id="resp_sec">
            <?php
            if($errors['response']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Response');?>:</strong></label>
                </td>
                <td>
<?php if ($cfg->isCannedResponseEnabled()) { ?>
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
                        <option value='original'><?php echo __('Original Message'); ?></option>
                        <option value='lastmessage'><?php echo __('Last Message'); ?></option>
                        <?php
                        if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                            echo '<option value="0" disabled="disabled">
                                ------------- '.__('Premade Replies').' ------------- </option>';
                            foreach($cannedResponses as $id =>$title)
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    <br>
<?php } # endif (canned-resonse-enabled)
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-draft-namespace="ticket.response"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'Start writing your response here. Use canned responses from the drop-down above'
                        ); ?>"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        rows="9" wrap="soft"
                        class="richtext ifhtml draft draft-delete" onclick="this.focus();this.select()"><?php
                        echo $info['response'];?></textarea>
                <div id="reply_form_attachments" class="attachments">
<?php
print $response_form->getField('attachments')->render();
?>
                </div>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label for="signature" class="left"><?php echo __('Signature');?>:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        <?php echo sprintf(__('Department Signature (%s)'), Format::htmlchars($dept->getName())); ?></label>
                    <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label><strong><?php echo __('Ticket Status');?>:</strong></label>
                </td>
                <td>
                    <select name="reply_status_id">
                    <?php
                    $statusId = $info['reply_status_id'] ?: $ticket->getStatusId();
                    $states = array('open');
                    if ($thisstaff->canCloseTickets())
                        $states = array_merge($states, array('closed'));

                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $default = (3 == $s->getId());
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $s->getId(),
                                $default
                                 ? 'selected="selected"' : '',
                                __($s->getName()),
                                $selected
                                ? (' ('.__('current').')') : ''
                                );
                    }
                    ?>
                    </select>
                </td>
            </tr>
         </tbody>
        </table>
        <p  style="padding:0 165px;">
            <input class="btn_sm" type="submit" value="<?php echo __('Post Reply');?>" onClick="this.form.submit(); this.disabled=true;">
            <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>