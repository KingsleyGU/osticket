<?php 
require_once(INCLUDE_DIR.'class.mailparse.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.email.php');
require_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.user.php');
require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'tnef_decoder.php');
require_once(INCLUDE_DIR.'api.tickets.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');
// error_reporting(~0); ini_set('display_errors', 1);
    // if (!file_exists(CLIENTINC_DIR.'remote.xml')) {
    //     echo "The file remote.xml does not exist \n";
    // }
    // $fileName = CLIENTINC_DIR.'contact.xml';
    // $fileName = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_xml";
    // $response = getRequestFromUrl($fileName);
    // $nodes = $response->xpath('/contacts/contact');
    // if(!empty($response->xpath('/contacts/contact'))&&($nodes = $response->xpath('/contacts/contact'))&& count($nodes)>0)
    //     createTicketByWebService($response);
    // else
    //     echo "no content provided from the web service <br/>";

    function createTicketByWebService($xml)
    {
        try {
            if(!empty($xml))
                {
                    $nodes = $xml->xpath('/contacts/contact');
                }
                else
                {
                    echo "The xml file can not be loaded \n";
                }
                for($i=0;$i<count($nodes);$i++)
                {
                    // echo json_encode($nodes[$i]);
                    $data =array();
                    // $data['recipients'] = array();
                    $data['subject'] = removeLineBreaker($nodes[$i]->title);
                    if(empty( $data['subject']))
                         $data['subject'] = "no title";
                    $data['header'] = "";
                    $data['mid'] = 1;
                    $data['topicId'] = 2;
                    $data['priorityId'] = 2;
                    $data['crm_contact_id'] = $nodes[$i]->attributes()->id;
                    $data['flags'] = new ArrayObject();
                    $data['email'] = trim(removeLineBreaker($nodes[$i]->email));
                    if(empty($data['email']))
                        $data['email'] = "gumin@spitzeco.dk";
                    $data['phone'] = removeLineBreaker($nodes[$i]->phone);
                    if(empty($data['phone']))
                        $data['phone'] = "";
                    $data['name'] = trim(removeLineBreaker($nodes[$i]->name));
                    if(empty($data['name']))
                        $data['name'] = "Anonymous User";
                    $data['orderNumber'] = trim(removeLineBreaker($nodes[$i]->ordernumber));
                    $data['cvr'] = trim(removeLineBreaker($nodes[$i]->cvr));
                    $data['message'] =  removeLineBreaker($nodes[$i]->content);
                    $data['companyName'] = removeLineBreaker($nodes[$i]->companyname);
                    $data['company'] = removeLineBreaker($nodes[$i]->companyname);
                    $data['business_form_id'] = removeLineBreaker($nodes[$i]->business_form_id);
                    $data['activityCode'] = removeLineBreaker($nodes[$i]->activitycode);
                    $data['activityDescription'] = removeLineBreaker($nodes[$i]->activitydescription);
                    $data['useragent'] = removeLineBreaker($nodes[$i]->useragent);
                    $crmsubject1_id = trim(removeLineBreaker($nodes[$i]->crmsubject_id));
                    if (is_numeric($crmsubject1_id)) {
                        $data['CRM_filter_subject1_Id'] = intval($crmsubject1_id);
                        $data['crmsubject1_id'] = intval($crmsubject1_id);
                        $data['crmsubject1_text'] = removeLineBreaker($nodes[$i]->crmsubject_text);
                    } else {
                        die("crmsubject1_id is not numeric");
                    }
                    $crmsubject2_id = trim(removeLineBreaker($nodes[$i]->crmsubject2_id));
                    if (is_numeric($crmsubject2_id)) {
                        $data['CRM_filter_subject2_Id'] = intval($crmsubject2_id);
                        $data['crmsubject2_id'] = intval($crmsubject2_id);
                        $data['crmsubject2_text'] = removeLineBreaker($nodes[$i]->crmsubject2_text);
                    } else {
                        die("crmsubject2_id is not numeric");
                    }
                    $data['flags']['bounce'] = true;
                    $user = null;
                    $acct = null;
                    if (!$user && $data['email'])
                        $user = User::lookupByEmail($data['email']);
                    if (!$user) {
                        $user_form = UserForm::getUserForm()->getForm($data);
                        if(!($user = User::fromVars($user_form->getClean())))
                            echo 'Unable to register account.';
                        if (!($acct = ClientAccount::createForUser($user)))
                         echo ('Internal error. Unable to create new account');
                    }   
                    $fileContent = $nodes[$i]->files->file;
                    $data['fileContent'] = $fileContent;
                    $tform = TicketForm::objects()->one()->getForm();
                    $messageField = $tform->getField('message');
                    $fileField = $messageField->getWidget()->getAttachments();
                    for ($j=0; $j<count($fileContent);$j++)
                    {
                        $fileId = $fileContent[$j]->attributes()->id;
                        $file['name'] = $fileContent[$j]->name;
                        $file['type'] = $fileContent[$j]->mime;
                        $file['encoding'] = 'base64';
                        // $file['cid'] = false;
                        $url = $fileContent[$j]->url;
                        // $file['data'] = base64_encode(file_get_contents($url));
                        $file['data'] = getFileContentsSSL($url);
                        // try {
                        //     $file['id'] = $fileField->uploadAttachment($file);
                        // }
                        // catch (FileUploadError $ex) {
                        //     $file['error'] = $file['name'] . ': ' . $ex->getMessage();
                        //     echo $file['error'];
                        // }  
                        $data['attachments'][] = $file;    
                        // echo $file['data'];
                        // echo "<br/>";
                    }
                    // echo "22222";
                    // echo json_encode($data);
                    if(Ticket::lookupForContactId($data['crm_contact_id']))
                    {
                        $api = new TicketApiController();
                        $api->createTicket($data);
                        echo "ticket has been generated successfully <br/>";
                    }
                    else
                    {
                        echo "ticket with id ".$data['crm_contact_id']." has already exists <br/>";
                    }
                    // if(DELETE_ERST_SERVICE_QUEUE)
                    // {
                    //    deleteContactsFromQueue($data['crm_contact_id']);
                    // }
                    // else
                    // {
                    //     echo "please go to include/ost-config to make the DELETE_ERST_SERVICE_QUEUE to true";
                    // }
                }
            } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    function removeLineBreaker($string)
    {
        return preg_replace('/\s\s+/', '', $string);
    }
    function getRequestFromUrl($url)
    {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );  
     $response = simplexml_load_string(getFileContentsSSL($url), null, LIBXML_NOCDATA);
     return $response;
    }
    function getFileContentsSSL($url)
    {
       $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 
       return file_get_contents($url, false, stream_context_create($arrContextOptions));
    }
    function parseSubject1XML()
    {
        $url = "https://w2l.dk/pls/wopdprod/erstcrm_pck.subject_xml?i_id=";
        $xml = getRequestFromUrl($url);
        if(!empty($xml->xpath('/crmsubjects/crmsubject'))&&($nodes = $xml->xpath('/crmsubjects/crmsubject'))&& count($nodes)>0)
        {
            // echo $nodes[0]->attributes()->id;    
            echo  count($nodes)."    number<br/>";      
            for($i=0;$i<count($nodes);$i++)
            {
                $referenceId = $nodes[$i]->attributes()->id;
                $text = $nodes[$i]->crmsubject_text;
                $url = $nodes[$i]->url;
                // Ticket::updateCRMSubject1($referenceId,$text,$url);
                if(Ticket::updateCRMSubject1($referenceId,$text,$url))
                {
                    echo "updata the subject1 table successfully <br/>";
                }
                else
                {
                    echo  $referenceId." can not be added into the subject1 table or it has already exists <br/>";
                }
                parseSubject2XML($referenceId);
            }
        }
        else
            echo "no content provided from the subject1 web service <br/>".$url;
    }
    function parseSubject2XML($subject1Id)
    {
        $url = "https://w2l.dk/pls/wopdprod/erstcrm_pck.subsubject_xml?i_id=".$subject1Id;
        $xml = getRequestFromUrl($url);
        if(!empty($xml->xpath('/crmsubjects/crmsubject'))&&($nodes = $xml->xpath('/crmsubjects/crmsubject'))&& count($nodes)>0)
        {
            // echo $nodes[0]->attributes()->id;           
            for($i=0;$i<count($nodes);$i++)
            {
                $referenceId = $nodes[$i]->attributes()->id;
                $text = $nodes[$i]->crmsubject_text;
                $cvrRule = $nodes[$i]->cvrrule;
                $orderRule = $nodes[$i]->orderrule;
                $titleRule = $nodes[$i]->titlerule;
                if(Ticket::updateCRMSubject2($referenceId,$text,$cvrRule,$orderRule,$titleRule))
                {
                    echo "updata the subject2 table successfully <br/>";
                }
                else
                {
                    echo  $referenceId." can not be added into the subject2 table or it has already exists <br/>";
                }
            }
            echo $url."<br/>";
        }
        else
            echo "no content provided from the subject2 web service <br/>".$url."<br/>";
    }
    function deleteContactsFromQueue($contactId)
    {
        $url = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_delete?id=".$contactId;
        $xml = getRequestFromUrl($url);
        if(!empty($xml->xpath('/status'))&&($status = $xml->xpath('/status'))&& count($status)>0)
        {
            if(!empty($status->contact_deleted == $contactId))
            {
                echo "contact with the id= ".$contactId." has been deleted"."<br/>";
            }
            if(!empty($status->contact_file_deleted))
            {
                echo "file with the id= ".$status->contact_file_deleted." has been deleted"."<br/>";
            }
        }
        else
        {
            echo "can not delete the contact with its id=".$contactId;
        }
    }
    function parseFileXML($fileContent,$ticketId)
    {

        return true;
    }


?>