<?php

        $fileName = "https://w2l.dk/pls/wopdprod/erstcrm_pck.contact_xml";
        $response = getRequestFromUrl($fileName);
        if(!empty($response->xpath('/contacts/contact'))&&($nodes = $response->xpath('/contacts/contact'))&& count($nodes)>0)
            createTicketByWebService($response);

?>