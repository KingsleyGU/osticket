<?php 
require_once(INCLUDE_DIR.'class.mailparse.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.email.php');
require_once(INCLUDE_DIR.'class.filter.php');
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

        // $form = UserForm::getInstance();
        // if(($form = UserForm::getInstance()))
        //     foreach ($form->getFields() as $field)
                // echo $field;
                // $supported[] = $field->get('id');
        // echo $info.;
         // echo json_encode($supported);

        $data =array();
        $data['emailId'] = 0;
        $data['topicId'] = "2";
        $data['recipients'] = array();
        $data['subject'] = "From the API test";
        $data['header'] = "";
        $data['mid'] = 1;
        $data['priorityId'] = 1;
        $data['flags'] = new ArrayObject();
        $data['email'] = "12345678@test.dk";
        $data['name'] = "Mike Black";
        $data['message'] =  "life sucks";
        $data['phone'] = "50233011";
        $data['thread-type'] = 'N';
        $data['flags']['bounce'] = true;
         // $data['attachments'] = $parser->getAttachments();
         echo "successfully 11111";
        $api = new TicketApiController();
         echo "successfully 22222222";
        $api->createTicket($data);
         echo "successfully 33333333333333";
?>