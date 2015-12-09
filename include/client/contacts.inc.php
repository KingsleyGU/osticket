<?php

    // $fileName = CLIENTINC_DIR.'contact.xml';
    $fileName = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_xml";
    $response = getRequestFromUrl($fileName);
    if($response->xpath('/contacts/contact')&&!empty($response->xpath('/contacts/contact'))&&($nodes = $response->xpath('/contacts/contact'))&& count($nodes)>0)
        createTicketByWebService($response);
    else
        echo "can not create a ticket or no contents provided by the web service";

?>