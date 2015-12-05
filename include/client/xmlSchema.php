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

$form = null;
$info['topicId'] = "2";

// echo $info['topicId'];
// echo json_encode(Topic::lookup($info['topicId']));
// if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
//     $form = $topic->getForm();
//     if ($form) {
//         $form = $form->instanciate();
//         $form->isValidForClient();
//     }
// }
// echo json_encode($form);
        // $uform = UserForm::getUserForm()->getForm();
        // echo json_encode($uform);
        // if ($_POST) $uform->isValid();
        // $uform->render(false, 'Your Information');

        // echo  json_encode($uform);
        // echo json_encode($uform);

        $form = UserForm::getInstance();
        // if(($form = UserForm::getInstance()))
            foreach ($form->getFields() as $field)
                // echo $field;
                $supported[] = $field->get('name');
        // echo $info.;
         echo json_encode($supported);

        // $data =array();
        // $data['emailId'] = 0;
        // $data['recipients'] = array();
        // $data['subject'] = "From the API test";
        // $data['header'] = "";
        // $data['mid'] = 1;
        // $data['priorityId'] = 1;
        // $data['flags'] = new ArrayObject();
        // $data['email'] = "12345678@test.dk";
        // $data['phone'] = "50233011X45";
        // $data['name'] = "Mike Black";
        // $data['message'] =  "life sucks";
        // $data['thread-type'] = 'N';
        // $data['crmsubject1_id'] = 11;
        // $data['crmsubject1_text'] = "test contents for subject 1";
        // $data['crmsubject2_id'] = 22;
        // $data['crmsubject2_text'] = "test contents for subject 2";
        // $data['flags']['bounce'] = true;
        //  echo "successfully 11111";
        // $api = new TicketApiController();
        //  echo "successfully 22222222";
        // $api->createTicket($data);
        //  echo "successfully 33333333333333";
        //  echo json_encode($data);
        // Ticket::create($data,array(),"web",false,false);
    function removeLineBreaker($string)
    {
        return preg_replace('/\s\s+/', '', $string);
    }
    if (!file_exists(CLIENTINC_DIR.'contact.xml')) {
        echo "The file $filename does not exist \n";
    }
    $xml = simplexml_load_file(CLIENTINC_DIR.'contact.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
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
        $data['header'] = "";
        $data['mid'] = 1;
        $data['topicId'] = 2;
        $data['priorityId'] = 2;
        $data['flags'] = new ArrayObject();
        $data['email'] = trim(removeLineBreaker($nodes[$i]->email));
        // removeLineBreaker($nodes[$i]->email);
        $data['phone'] = removeLineBreaker($nodes[$i]->phone);
        $data['name'] = trim(removeLineBreaker($nodes[$i]->name));
        $data['orderNumber'] = trim(removeLineBreaker($nodes[$i]->ordernumber));
        $data['message'] = "new test from the dummy data 222";
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
        echo "successfully 33333333333333";
        $user = null;
        $acct = null;
        if (!$user && $data['email'])
            $user = User::lookupByEmail($data['email']);
        if (!$user) {
            $user_form = UserForm::getUserForm()->getForm($data);
            // $user_form->getField('email')->value = $data['email'];
            // $user_form->getField('name')->value = $data['name'];
            // $user_form->getField('phone')->value = $data['phone'];
            if($user = User::fromVars($user_form->getClean()))
                echo 'Unable to register account. See messages below';
            if (!($acct = ClientAccount::createForUser($user)))
             echo ('Internal error. Unable to create new account');
        echo "successfully 44444";
        }   

       // $data['emailId'] =  Email::getIdByEmail($data['email']);
       $data['uid'] = $user->getId();
       echo json_encode($data);
    $api = new TicketApiController();
    $api->createTicket($data);
        

    }


?>