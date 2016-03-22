<?php
	global $logFilePath;
	error_reporting(~0); ini_set('display_errors', 1);
    // $fileName = CLIENTINC_DIR.'remote.xml';
    $fileName = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_xml";
    $response = getRequestFromUrl($fileName);
    if($response->xpath('/contacts/contact')&&!empty($response->xpath('/contacts/contact'))&&($nodes = $response->xpath('/contacts/contact'))&& count($nodes)>0)
    {  
        createTicketByWebService($response);
    }
	echo $logFilePath;
	error_log("it is", 3, $logFilePath);
//     else
//     {
//        echo "can not create a ticket or no contents provided by the web service <br/>";
//        $fileName = CLIENTINC_DIR.'remote.xml';
//        $response = getRequestFromUrl($fileName);
//        createTicketByWebService($response);
//        echo "a test case has been generated <br/>";
//	    }

?>
