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


    // if (!file_exists(CLIENTINC_DIR.'remote.xml')) {
    //     echo "The file remote.xml does not exist \n";
    // }
    // $fileName = CLIENTINC_DIR.'remote.xml';
    // $fileName = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_xml";
    // $response = getRequestFromUrl($fileName);
    // $nodes = $response->xpath('/contacts/contact');
    // if(!empty($response->xpath('/contacts/contact'))&&($nodes = $response->xpath('/contacts/contact'))&& count($nodes)>0)
    //     createTicketByWebService($response);
    // else
    //     echo "no content provided from the web service <br/>";
    parseSubject1XML();
    // parseSubject2XML();

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
                    $data =array();
                    $data['recipients'] = array();
                    $data['subject'] = removeLineBreaker($nodes[$i]->title);
                    if(empty( $data['subject']))
                         $data['subject'] = "no title";
                    $data['header'] = "";
                    $data['mid'] = 1;
                    $data['topicId'] = 2;
                    $data['priorityId'] = 2;
                    $data['flags'] = new ArrayObject();
                    $data['email'] = trim(removeLineBreaker($nodes[$i]->email));
                    if(empty($data['email']))
                        $data['email'] = "gumin@spitzeco.dk";
                    $data['phone'] = removeLineBreaker($nodes[$i]->phone);
                    if(empty($data['phone']))
                        $data['phone'] = "12345678";
                    $data['name'] = trim(removeLineBreaker($nodes[$i]->name));
                    if(empty($data['name']))
                        $data['name'] = "Anonymous User";
                    $data['orderNumber'] = trim(removeLineBreaker($nodes[$i]->ordernumber));
                    $data['message'] =  removeLineBreaker($nodes[$i]->content);
                    $data['thread-type'] = 'N';
                    $data['activityCode'] = removeLineBreaker($nodes[$i]->activitycode);
                    $data['activityDescription'] = removeLineBreaker($nodes[$i]->activitydescription);
                    $data['useragent'] = removeLineBreaker($nodes[$i]->useragent);
                    $crmsubject1_id = trim(removeLineBreaker($nodes[$i]->crmsubject_id));
                    if (is_numeric($crmsubject1_id)) {
                        $data['crmsubject1_id'] = intval($crmsubject1_id);
                        $data['crmsubject1_text'] = removeLineBreaker($nodes[$i]->crmsubject_text);
                    } else {
                        die("crmsubject1_id is not numeric");
                    }
                    $crmsubject2_id = trim(removeLineBreaker($nodes[$i]->crmsubject2_id));
                    if (is_numeric($crmsubject2_id)) {
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
                    // $data['uid'] = $user->getId();
                    // echo json_encode($data);
                    $api = new TicketApiController();
                    $api->createTicket($data);
                    echo "ticket has been generated successfully <br/>";
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

     $response = simplexml_load_string(file_get_contents($url), null, LIBXML_NOCDATA);
     return $response;
    }
    function parseSubject1XML()
    {
        $url = "https://w2l.dk/pls/wopdprod/erstcrm_pck.subject_xml?i_id=";
        // echo $url;
        echo file_get_contents($url);
        // $xml = getRequestFromUrl($url);
        // echo json_encode($xml);
        // if(!empty($xml->xpath('/crmsubjects/crmsubject'))&&($nodes = $xml->xpath('/crmsubjects/crmsubject'))&& count($nodes)>0)
        // {
        //     echo $nodes[0]->attributes()->id;           
            // for($i=0;$i<count($nodes);$i++)
            // {
            //     $referenceId = $nodes[$i]->attributes()->id;
            //     $text = $nodes[$i]->crmsubject_text;
            //     $url = $nodes[$i]->url;
            //     // Ticket::updateCRMSubject1($referenceId,$text,$url);
            //     if(Ticket::updateCRMSubject1($referenceId,$text,$url))
            //     {
            //         echo "updata the subject1 table successfully <br/>";
            //     }
            //     else
            //     {
            //         echo  $referenceId." can not be added into the table or it has already exists <br/>";
            //     }
            //     parseSubject2XML($referenceId);
            // }
        // }
        // else
        //     echo "no content provided from the subject web service <br/>";
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
                    echo  $referenceId." can not be added into the table or it has already exists <br/>";
                }
            }
        }
        else
            echo "no content provided from the subject web service <br/>";
    }

?>