<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.=__('Unable to obtain a lock on the ticket');

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('Current ticket status (%s) does not allow the end user to reply.'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('Ticket is assigned to %s'),
                implode('/', $ticket->getAssignees())
                ));

if (!$errors['err']) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
    {    // $errors['err'] = sprintf(__('This ticket is currently locked by %s'),
        //         $lock->getStaffName());
        
        $overviewPageUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        echo "<script>   
                window.alert('This case has been locked by ".$lock->getStaffName()."');
                window.location = '".$overviewPageUrl."';
               </script>"; 
        // header('Location: '.$overviewPageUrl);

    }
    elseif (($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
        $errors['err'] = __('Email is in banlist! Must be removed before any reply/response');
    elseif (!Validator::is_valid_email($ticket->getEmail()))
        $errors['err'] = __('EndUser email address is not valid! Consider updating it before responding');
}

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';
// if(isset($_SESSION['previousPageUrl'])&&!empty($_SESSION['previousPageUrl']))
//      echo "<h3>111111111".$_SESSION['previousPageUrl']."</h3>";
// echo "<h3>HTTP_REFERER: ".$_SERVER['HTTP_REFERER']."</h3>";
// echo "<h3>previousPageUrl: ".$_SESSION['previousPageUrl']."</h3>";
?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="20%" class="has_bottom_border">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="<?php echo __('Reload'); ?>"><i class="icon-refresh"></i>
             <?php echo sprintf(__('Ticket #%s'), $ticket->getNumber()); ?></a></h2>
        </td>
        <td width="auto" class="flush-right has_bottom_border">
            <?php
            if ($thisstaff->canBanEmails()
                    || $thisstaff->canEditTickets()
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button pull-right" data-dropdown="#action-dropdown-more">
                <i class="icon-caret-down pull-right"></i>
                <span ><i class="icon-cog"></i> <?php echo __('More');?></span>
            </span>
            <?php
            }
            // Status change options
            echo TicketStatus::status_options();

            if ($thisstaff->canEditTickets()) { ?>
                <a class="action-button pull-right" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i> <?php
                    echo __('Edit'); ?></a>
            <?php
            }
            if ($ticket->isOpen()
                    && !$ticket->isAssigned()
                    && $thisstaff->canAssignTickets()
                    && $ticket->getDept()->isMember($thisstaff)) {?>
                <a id="ticket-claim" class="action-button pull-right confirm-action" href="#claim"><i class="icon-user"></i> <?php
                    echo __('Claim'); ?></a>

            <?php
            }?>
            <span class="action-button pull-right" data-dropdown="#action-dropdown-print">
                <i class="icon-caret-down pull-right"></i>
                <a id="ticket-print" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print" ><i class="icon-print"></i> <?php
                    echo __('Print'); ?></a>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> <?php echo __('Ticket Thread'); ?></a>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> <?php echo __('Thread + Internal Notes'); ?></a>
                 <li><a class="no-pjax"  href="#" onclick="chooseAttachment(0)"><i
                 class="icon-file-alt"></i> <?php echo __('Thread + Attachments'); ?></a>
                 <li><a class="no-pjax"  href="#" onclick="chooseAttachment(1)"><i
                 class="icon-file-text-alt"></i> <?php echo __('Thread + Internal Notes + Attachments'); ?></a>
              </ul>
            </div>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if($thisstaff->canEditTickets()) { ?>
                    <li><a class="change-user" href="#tickets/<?php
                    echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> <?php
                    echo __('Change Owner'); ?></a></li>
                <?php
                 }
                 if($thisstaff->canDeleteTickets()) {
                     ?>
                    <li><a class="ticket-action" href="#tickets/<?php
                    echo $ticket->getId(); ?>/status/delete"
                    data-href="tickets.php"><i class="icon-trash"></i> <?php
                    echo __('Delete Ticket'); ?></a></li>
                <?php
                 }
                if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

                    if($ticket->isAssigned()) { ?>
                        <li><a  class="confirm-action" id="ticket-release" href="#release"><i class="icon-user"></i> <?php
                            echo __('Release (unassign) Ticket'); ?></a></li>
                    <?php
                    }

                    if(!$ticket->isOverdue()) { ?>
                        <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> <?php
                            echo __('Mark as Overdue'); ?></a></li>
                    <?php
                    }

                    if($ticket->isAnswered()) { ?>
                    <li><a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> <?php
                            echo __('Mark as Unanswered'); ?></a></li>
                    <?php
                    } else { ?>
                    <li><a class="confirm-action" id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> <?php
                            echo __('Mark as Answered'); ?></a></li>
                    <?php
                    }
                } ?>
                <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i> <?php echo __('Manage Forms'); ?></a></li>

<?php           if($thisstaff->canBanEmails()) {
                     if(!$emailBanned) {?>
                        <li><a class="confirm-action" id="ticket-banemail"
                            href="#banemail"><i class="icon-ban-circle"></i> <?php echo sprintf(
                                Format::htmlchars(__('Ban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a  class="confirm-action" id="ticket-banemail"
                            href="#unbanemail"><i class="icon-undo"></i> <?php echo sprintf(
                                Format::htmlchars(__('Unban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                    <?php
                     }
                }?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<div class="info-clickable-div scp-clickable-div">
    Info
</div>
<table class="ticket_info main_ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="150"><?php echo __('Status');?>:</th>
                    <td><?php echo $ticket->getStatus(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Priority');?>:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Department');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Create Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
                <?php 
                    $hashTableContent = $ticket-> getHashtable();
                    // echo json_encode($hashTableContent);
                    if(!empty($hashTableContent)&&!empty($hashTableContent['crm_subject1_id']))
                 { ?>
<!--                     <tr>
                        <th><?php echo __('CRM Subject1');?>:</th>
                        <td>
                            <ul class="scp-crm-ul">
                              <li><div class="crm-ul-title">ID:</div><?php echo $hashTableContent['crm_subject1_id']; ?></li>
                              <li><div class="crm-ul-title">Text:</div><?php echo $hashTableContent['crm_subject1_text']; ?></li>
                            </ul> 
                        </td>
                    </tr> -->
                <?php    }   
                ?>
                <?php 
                    if(!empty($hashTableContent)&&!empty($hashTableContent['crm_subject2_id']))
                 { ?>
<!--                     <tr>
                        <th><?php echo __('CRM Subject2');?>:</th>
                        <td>
                           <ul class="scp-crm-ul">
                              <li><div class="crm-ul-title">ID:</div><?php echo $hashTableContent['crm_subject2_id']; ?></li>
                              <li><div class="crm-ul-title">Text:</div><?php echo $hashTableContent['crm_subject2_text']; ?></li>
                            </ul>  
                        </td>
                    </tr> -->
                <?php    }   
                ?>
                <?php 
                    $urlFirstPart = "https://erst.virk.dk/sagsstyring/sagsstyring/soegning?organisationEntitetsId=&sorteringsraekkefoelge=FALDENDE&_hastesager=&_kladde=&kladde=on"
                    ."&_modtaget=&modtaget=on&_underBehandling=&underBehandling=on&_afgjort=&afgjort=on&_lukket=&lukket=on&_underTilpasning=&underTilpasning=on&procesfacettype=&emnefacettype=&soegeord=";
                    $urlLastPart = "&_action_soegning=S%C3%B8g";
                    if(!empty($hashTableContent)&&!empty($hashTableContent['orderNumber']))
                    {
                        $urlMiddlePart = $hashTableContent['orderNumber'];
                    }
                    else
                    {
                        $urlMiddlePart = $hashTableContent['cvr_number'];
                    }

                ?>
                <tr>
                    <th><?php echo __('Case Link');?>:</th>
                    <td>
                          <a href="<?php echo $urlFirstPart.$urlMiddlePart.$urlLastPart;?>" target="_blank">Case Management URL</a>
                    </td>
                </tr>                   
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('User'); ?>:</th>
                    <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if($user) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&uid=%d" title="%s" data-dropdown="#action-dropdown-stats">(<b>%d</b>)</a>',
                                    urlencode($user->getId()), __('Related Tickets'), $user->getNumTickets());
                        ?>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open));

                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i
                                                class="icon-folder-close-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed));
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
                                    <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i> <?php echo __('Manage User'); ?></a></li>
<?php if ($user->getOrgId()) { ?>
                                    <li><a href="orgs.php?id=<?php echo $user->getOrgId(); ?>"><i
                                        class="icon-building icon-fixed-width"></i> <?php
                                        echo __('Manage Organization'); ?></a></li>
<?php } ?>
                                </ul>
                            </div>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('Email'); ?>:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('Phone'); ?>:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-phone"><?php echo $ticket->getPhoneNumber(); ?></span>
                    </td>
                </tr>
                    <tr>
                        <th><?php echo __('Company');?>:</th>
                        <td>
                           <ul class="scp-crm-ul">
                              <li><div class="crm-ul-title">Name:</div><?php  if(!empty($hashTableContent)&&!empty($hashTableContent['company_name'])){ ?><?php echo $hashTableContent['company_name']; ?><?php    } ?>  </li>
                              <li><div class="crm-ul-title">CVR:</div><?php  if(!empty($hashTableContent)&&!empty($hashTableContent['cvr_number'])){ ?><?php echo $hashTableContent['cvr_number']; ?><?php    } ?></li>
                            </ul>  
                        </td>
                    </tr>
                  
  
                 <?php 
                    $fileContents = $ticket->getFileContents();
                    // echo json_encode($fileContents );
                    if(!empty($fileContents)&&count($fileContents)>0)
                 {  for($j=0;$j<count($fileContents);$j++){?>

<!--                     <tr>
                        <th><?php echo __('CRM Files'.($j+1));?>:</th>
                        <td>
                           <ul class="scp-crm-ul">
                              <li><div class="crm-ul-title">ID:</div><?php echo $fileContents[$j]['crm_file_reference_id']; ?></li>
                              <li><div class="crm-ul-title">Created:</div><?php echo  Format::db_datetime($fileContents[$j]['created']); ?></li>
                              <li><div class="crm-ul-title">url:</div><a href="https://w2l.dk<?php echo $fileContents[$j]['url']; ?>"><?php echo $fileContents[$j]['name']; ?></a></li>
                            </ul>  
                        </td>
                    </tr> -->
                <?php    }}   
                ?>                                          
                <tr>
                    <th><?php echo __('Source'); ?>:</th>
                    <td><?php
                        echo Format::htmlchars($ticket->getSource());

                        if($ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.$ticket->getIP().')</span>';
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<div class="additional-info-clickable-div scp-clickable-div">
    Additional Info
</div>
<table class="ticket_info additional_ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                $systemEmail = $ticket->getSystemEmail();
                if($systemEmail!=null)
                {
                ?>
                <tr>
                    <th width="100"><?php echo __('System Mail');?>:</th>
                    <td>
                        <?php
                                echo $systemEmail;
                        ?>
                    </td>
                </tr>               
                <?php
                }
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100"><?php echo __('Assigned To');?>:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                            // echo $ticket->getTeam();
                        else
                            echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100"><?php echo __('Closed By');?>:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th><?php echo __('SLA Plan');?>:</th>
                    <td><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th><?php echo __('Due Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getEstDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th><?php echo __('Close Date');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100"><?php echo __('Help Topic');?>:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Message');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Response');?>:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
<?php
$idx = 0;
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = array_filter($form->getAnswers(), function ($a) {
        return !in_array($a->getField()->get('name'),
                array('email','subject','name','priority'));
        });
    if (count($answers) == 0)
        continue;
    ?>
        <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
            <?php foreach($answers as $a) {
                if (!($v = $a->display())) continue; ?>
                <tr>
                    <th width="100"><?php
    echo $a->getField()->get('label');
                    ?>:</th>
                    <td><?php
    echo $v;
                    ?></td>
                </tr>
                <?php } ?>
            </table>
        </td>
        </tr>
    <?php
    $idx++;
    } ?>
</table>
<div class="clear"></div>
<h2 style="padding:10px 0 5px 0; font-size:11pt;"><?php echo Format::htmlchars($ticket->getSubject()); ?></h2>

<div id="response_options">
    <ul class="tabs">
        <?php
        if($thisstaff->canPostReply()) { ?>
        <li><a id="reply_tab" href="#reply"><?php echo __('Post Reply');?></a></li>
        <?php
        } ?>
        <li><a id="note_tab" href="#note"><?php echo __('Post Internal Note');?></a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer"><?php echo __('Department Transfer');?></a></li>
        <?php
        }
        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?__('Reassign Ticket'):__('Assign Ticket'); ?></a></li>
        <?php
        } ?>
        <li><a id="Responza_KB" href="#responza-Knowledge"><?php echo __('Svarbasen');?></a></li>
    </ul>
    <?php
    if($thisstaff->canPostReply()) { ?>
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
    <?php
    } ?>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data" class="tab-response-block">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Internal Note'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em"><?php
                        echo __('Note title - summary of the note (optional)'); ?></div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <div class="error"><?php echo $errors['note']; ?></div>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="<?php echo __('Note details'); ?>"
                        rows="9" wrap="soft" data-draft-namespace="ticket.note"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        class="richtext ifhtml draft draft-delete"><?php echo $info['note'];
                        ?></textarea>
                <div class="attachments">
<?php
print $note_form->getField('attachments')->render();
?>
                </div>
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120">
                    <label><?php echo __('Ticket Status');?>:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="note_status_id">
                        <?php
                        $statusId = $info['note_status_id'] ?: $ticket->getStatusId();
                        $states = array('open');
                        if ($thisstaff->canCloseTickets())
                            $states = array_merge($states, array('closed'));
                        foreach (TicketStatusList::getStatuses(
                                    array('states' => $states)) as $s) {
                            if (!$s->isEnabled()) continue;
                            $selected = $statusId == $s->getId();
                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                    $s->getId(),
                                    $selected ? 'selected="selected"' : '',
                                    __($s->getName()),
                                    $selected ? (' ('.__('current').')') : ''
                                    );
                        }
                        ?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['note_status_id']; ?></span>
                </td>
            </tr>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('Post Note');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data" class="tab-response-block">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['transfer']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120">
                    <label for="deptId"><strong><?php echo __('Department');?>:</strong></label>
                </td>
                <td>
                    <?php
                        echo sprintf('<span class="faded">'.__('Ticket is currently in <b>%s</b> department.').'</span>', $ticket->getDeptName());
                    ?>
                    <br>
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select Target Department');?> &mdash;</option>
                        <?php
                        if($depts=Dept::getDepartments()) {
                            foreach($depts as $id =>$name) {
                                if($id==$ticket->getDeptId()) continue;
                                echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['deptId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Comments'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="transfer_comments" id="transfer_comments"
                        placeholder="<?php echo __('Enter reasons for the transfer'); ?>"
                        class="richtext ifhtml no-bar" cols="80" rows="7" wrap="soft"><?php
                        echo $info['transfer_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['transfer_comments']; ?></span>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="<?php echo __('Transfer');?>">
           <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
    <?php
    if($thisstaff->canAssignTickets()) { ?>
    <form id="assign" action="tickets.php?id=<?php echo $ticket->getId(); ?>#assign" name="assign" method="post" enctype="multipart/form-data" class="tab-response-block">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="assign">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">

            <?php
            if($errors['assign']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['assign']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="assignId"><strong><?php echo __('Assignee');?>:</strong></label>
                </td>
                <td>
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select a Team');?> &mdash;</option>
                        <?php
                        if ($ticket->isOpen()
                                && !$ticket->isAssigned()
                                && $ticket->getDept()->isMember($thisstaff))
                            echo sprintf('<option value="%d">'.__('Claim Ticket (comments optional)').'</option>', $thisstaff->getId());

                        $sid=$tid=0;

                        if ($dept->assignMembersOnly())
                            $users = $dept->getAvailableMembers();
                        else
                            $users = Staff::getAvailableStaffMembers();
                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="'.sprintf(__('Teams (%d)'), count($teams)).'">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                // if($teamId && $teamId==$id)
                                //     continue;

                                $k="t$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }
                        if ($users) {
                            echo '<OPTGROUP label="'.sprintf(__('Agents (%d)'), count($users)).'">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    continue;

                                if (!is_object($name))
                                    $name = new PersonsName($name);

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''), $name);
                            }
                            echo '</OPTGROUP>';
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['assignId']; ?></span>
                    <?php
                    if ($ticket->isAssigned() && $ticket->isOpen()) { ?>
                        <div class="faded"><?php echo sprintf(__('Ticket is currently assigned to %s'),
                            sprintf('<b>%s</b>', $ticket->getAssignee())); ?></div> <?php
                    } elseif ($ticket->isClosed()) { ?>
                        <div class="faded"><?php echo __('Assigning a closed ticket will <b>reopen</b> it!'); ?></div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Comments');?>:</strong><span class='error'>&nbsp;</span></label>
                </td>
                <td>
                    <textarea name="assign_comments" id="assign_comments"
                        cols="80" rows="7" wrap="soft"
                        placeholder="<?php echo __('Enter reasons for the assignment or instructions for assignee'); ?>"
                        class="richtext ifhtml no-bar"><?php echo $info['assign_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['assign_comments']; ?></span><br>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?__('Reassign'):__('Assign'); ?>">
            <input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
     <div id="responza-Knowledge" class="tab-response-block">
        <iframe  id="responza-iframe" src="http://erst.spitzeco.dk//contentkb/1_1/1/1/Search?customer=Erhvervsstyrelse&title=1_1&page=1&sparam=<?php echo urlencode(Format::htmlchars($ticket->getSubject())); ?>">
        </iframe>
     </div>
<!--     <div id="responza-Knowledge" class="tab-response-block">
        <div class="responza-article-link-content">
        </div>
        <div class="responza-article-block" style="display:none;">
            <button type="button" class="responzaBtn goBackButton" onclick="goBackToArticleLink()">back</button>
            <div class="responza-article-content">
            </div>
        </div>
    </div> -->
</div>


<?php
$tcount = $ticket->getThreadCount();
// $tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#"><?php echo sprintf(__('Ticket Thread (%d)'), $tcount); ?></a></li>
    <li><a style="padding-left:0px;" onclick='window.open("tickets.php?thread_detail=1&id=<?php echo $ticket->getId(); ?>");return false;' id="ticket_thread_detail" target="_blank" href="javascript:void(0);"><?php echo sprintf(__('Ticket Thread detail')); ?></a></li>
</ul>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    $types = array('M', 'R', 'N');
    if(($thread=$ticket->getThreadEntries($types))) {
       foreach($thread as $entry) { ?>
        <table class="thread-entry <?php echo $threadTypes[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th colspan="4" width="100%">
                <div>
                    <span class="pull-left">
                    <span style="display:inline-block"><?php
                        echo Format::db_datetime($entry['created']);?></span>
                    <span style="display:inline-block;padding:0 1em" class="faded title"><?php
                        echo Format::truncate($entry['title'], 100); ?></span>
                    </span>  
                    <?php
                        $click_href = "tickets.php?edit_thread=1&ticket_id=".$_REQUEST['id']."&thread_id=".$entry['id']; 
                    ?>

                    <?php 
                        $typeText = "";
                        $entryBody = $entry['body']->toHtml();
                        $containComment = strpos($entryBody, '<p>');
                        if ($entry['name']&&$entry['thread_type'] == 'N'&&$containComment!== false) {
                            $typeText = "Internal Note";
                        }
                        if($entry['thread_type'] != 'N')
                        {
                    ?>
                    <a onclick='window.open("<?php echo $click_href; ?>")' target="_blank" href="javascript:void(0);" class="pull-right" style="margin:0px 10px 0px 15px;">Edit</a>
                    <div class="pull-right forward_thread_block" >
                    <input type="checkbox"  name="forward_thread_choice" class="forward_thread_choice" value="<?php echo $entry['id']; ?>" onclick='checkForwardThreadList();'> <span style="display:inline-block; margin-top:-8px;">Forward</span>
                    </div>
                    <?php 
                        }             
                    ?>   
                    <span class="pull-right" style="white-space:no-wrap;display:inline-block" >
                        <span style="vertical-align:middle; color:#ff0000; font-weight:bold;" class="tmeta faded title"><?php
                            echo Format::htmlchars($typeText); ?></span>  
                        <span style="vertical-align:middle;" class="textra"></span>
                        <span style="vertical-align:middle;"
                            class="tmeta faded title"><?php
                            echo Format::htmlchars($entry['name'] ?: $entry['poster']); ?></span>
                    </span>  
 
                </div>
                </th>
            </tr>
            <tr><td colspan="4" class="thread-body" id="thread-id-<?php
                echo $entry['id']; ?>"><div><?php
                echo $entry['body']->toHtml(); ?></div></td></tr>
            <?php
            if($entry['attachments']
                    && ($tentry = $ticket->getThreadEntry($entry['id']))
                    && ($urls = $tentry->getAttachmentUrls())
                    && ($links = $tentry->getAttachmentsLinks())) {?>
            <tr>
                <td class="info" colspan="4"><?php echo $tentry->getAttachmentsLinks(); ?></td>
            </tr> <?php
            }
            if ($urls) { ?>
                <script type="text/javascript">
                    $('#thread-id-<?php echo $entry['id']; ?>')
                        .data('urls', <?php
                            echo JsonDataEncoder::encode($urls); ?>)
                        .data('id', <?php echo $entry['id']; ?>);
                </script>
<?php
            } ?>
        </table>
        <?php
        if($entry['thread_type']=='M')
            $msgId=$entry['id'];
       }
    } else {
        echo '<p>'.__('Error fetching ticket thread - get technical help.').'</p>';
    }?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>


<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('Ticket Print Options');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form" target="_blank" >
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label class="fixed-size" for="notes"><?php echo __('Print Notes');?>:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('Print <b>Internal</b> Notes/Comments');?>
        </fieldset>
        <fieldset>
            <label class="fixed-size" for="psize"><?php echo __('Paper Size');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('Select Print Paper Size');?> &mdash;</option>
                <?php
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', __($v));
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Print');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>

<div style="display:none;" class="dialog" id="print-attachments">
    <h3><?php echo __('Ticket Print attachments');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="GET" id="print-ticket-with-attachments" name="print-ticket-with-attachments" target="_blank" >
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="notes" id="print-attachment-notes">
        <?php $printableAttachments = $ticket->getAllPrintableAttachments();
            foreach ($printableAttachments as  $attachment) { ?>
              <input type="checkbox"  name="printAttachments[]" value="<?php echo $attachment['file_id'];?>" checked>
              <!-- <a target="_blank" href="<?php echo $attachment['download_url']; ?>"> -->
                <?php echo $attachment['name']; ?>
           <!--  </a> -->
            <br> 
        <?php    }
        ?>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Print');?>" >
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo __('Are you sure you want to <b>claim</b> (self assign) this ticket?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>answered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be automatically rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>unassign</b> ticket from <b>%s</b>?'), $ticket->getAssigned()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf(Format::htmlchars(__('%s <%s> will longer have access to the ticket')),
            '<b>'.Format::htmlchars($ticket->getName()).'</b>', Format::htmlchars($ticket->getEmail())); ?>
        </span>
        <?php echo sprintf(__('Are you sure you want to <b>change</b> ticket owner to %s?'),
            '<b><span id="newuser">this guy</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo __('Are you sure you want to DELETE this ticket?');?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('OK');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>

<script type="text/javascript">
// var responzaArticleArray = [];
// $(document).ready(function(){
//     $.get( "http://localhost:49819//api/ArticleApi/SearchArticles?title=a&customer=1&portal_id=1&field=Date&order=ASC&number=10", function( data ) {
//         var articleSearchResultObject = jQuery.parseJSON( data );  
//         for(var i=0; i< articleSearchResultObject.length; i++)
//         {
//             var articleLinkString = "<button type='button' class='responza-article-link popup-modal' onclick='switchToArticleContect("+i+")'>"+articleSearchResultObject[i]["Title"]+"</button>";
//             $(".responza-article-link-content").append($.parseHTML(articleLinkString));
//             responzaArticleArray.push(articleSearchResultObject[i]["HTML"]);
//         }
//     });
//     $('.popup-modal').magnificPopup({
//       type: 'inline',
//       preloader: false,
//       focus: '#username',
//       modal: true
//     });
//     $(document).on('click', '.popup-modal-dismiss', function (e) {
//       e.preventDefault();
//       $.magnificPopup.close();
//     });
// });

// Click getContentBtn to get the content of iframe
// $("#getContentBtn").click(function(){
//     var contentWindow = $("#responza-Knowledge-iframe").contentWindow;
//     contentWindow.postMessage("hello to responza","http://localhost:49819/");
// })





$(function() {
     //this is for toggle the system thread and response parts
    $(".response .thread-body").css("display","none");
    $(".note .thread-body").css("display","none");
    $(".thread-entry th").click(function(){
        $(this).parents(".thread-entry").find(".thread-body").toggle();
    });  
    checkForwardThreadList();
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });

<?php
    // Set the lock if one exists
    if ($lock) { ?>
!function() {
  var setLock = setInterval(function() {
    if (typeof(window.autoLock) === 'undefined')
      return;
    clearInterval(setLock);
    autoLock.setLock({
      id:<?php echo $lock->getId(); ?>,
      time: <?php echo $cfg->getLockTime(); ?>}, 'acquire');
  }, 50);
}();
<?php } ?>
});
//this function is for displying the content of the relevant article
function switchToArticleContect(articleIndex)
{
    $( ".responza-article-link-content" ).css("display","none");
    $( ".responza-article-block" ).css("display","block");
    $( ".responza-article-content" ).html($.parseHTML(responzaArticleArray[articleIndex]));

    return false;
}
function checkForwardThreadList()
{
    var threadList = "";
    $( ".forward_thread_choice" ).each(function() {
      if($( this ).prop('checked'))
        threadList += $( this ).val() + ",";
    });
    // alert(threadList);
    $("#reply .thread_list").val(threadList);
}
function chooseAttachment(notes)
{
    $("#print-attachment-notes").val(notes);
    $('#overlay').show();
    $('.dialog#print-attachments').show();
    return false;
}

function printAttachmentValue()
{
    event.preventDefault();
    var formData = $("#print-ticket-with-attachments").serialize();
    alert(JSON.stringify(formData));
}

function goBackToArticleLink()
{
    $( ".responza-article-block" ).css("display","none");
    $( ".responza-article-link-content" ).css("display","block");
}
function unselectAllThreadList()
{
   $( ".forward_thread_choice" ).each(function() {
        $(this).prop('checked', false);
    }); 
   checkForwardThreadList();
}
// all the click event actions
$("#choose_all_threads_btn").click(function(){
        
       $( ".forward_thread_choice" ).each(function() {
            $(this).prop('checked', true);
        }); 
       checkForwardThreadList();
       return false;
})
$(".redactor_richtext").click(function(){
    document.execCommand('selectAll',false,null);
})
$(".additional-info-clickable-div").click(function(){
    $(".additional_ticket_info").toggle("fast");
})
$(".info-clickable-div").click(function(){
    $(".main_ticket_info").toggle("fast");
})
$(".pasteContentFromClip").click(function(){
    // var tempTextArea = document.createElement('textarea');
    // tempTextArea.addClass( "tempTextArea" );
    // tempTextArea.paste();
    // alert(document.queryCommandSupported('paste'));
    // unsafeWindow.netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");  
    // var clipboardContent = getContentFromClipboard();
    // alert(clipboardContent);
  //   var event;
  //    if (document.createEvent) {
  //   event = document.createEvent("HTMLEvents");
  //   event.initEvent("paste", true, true);
  // } else {
  //   event = document.createEventObject();
  //   event.eventType = "paste";
  // }

  // event.eventName = "paste";

    // $("#div").trigger("paste");
    // $("#div").select();
    $("#div").focus();
    // var press = jQuery.Event("keypress");
    // press.ctrlKey = true;
    // press.which = 86;
     // var event = document.createEvent("KeyboardEvent");
     //   event.initKeyEvent(
     //                 "keyup",           //  event,
     //                  true,             //  bubbleable,
     //                  true,             //  cancelable,
     //                  null,             //  window
     //                  true,             //  ctrlKey,
     //                  false,            //  altKey,
     //                  false,            //  shiftKey,
     //                  false,            //  metaKey,
     //                   86,              //  keyCodeArg (virtual key code),
     //                   0);              //  charCodeArg ;

     //  var canceled = !document.body.dispatchEvent(event);
     //  if(canceled) {
     //    // A handler called preventDefault
     //    alert("canceled");
     //  } else {
     //    // None of the handlers called preventDefault
     //    alert("not canceled");
     //  }
    
    // var e = jQuery.Event( "keydown", { keyCode: 86 } );
    // e.ctrlKey = true;
    // document.body.dispatchEvent(event);
    // $("#div").trigger(e);
    // $("#div").trigger({type: 'keydown', which: 17 && 86, keyCode: 17 && 86});
    // var event = document.createEvent('Event');
    // event.isTrusted = true;
    // event.initEvent('paste', true, true);
    // document.body.dispatchEvent(event);

    var pressEvent = document.createEvent ("KeyboardEvent");    
pressEvent.initKeyEvent ("keypress", true, true, window, true, false, false, false, 86, 0);
var accepted=aTarget.dispatchEvent (pressEvent);
    // $("#div").fireEvent("on" + event.eventType, event);
    // $("#div").dispatchEvent(event);
    // $("#div").trigger("paste");
})


</script>


<script type="text/javascript">
var disableCollaborators= function(ticketId)
{

    $.post( '/scp/disableCollaborators.php', { ticketId: ticketId })
      .done(function( data ) {
        console.log( "Data Loaded: " + data );
      });
    return false;
}
</script>