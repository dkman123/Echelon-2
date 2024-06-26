<?php
$auth_name = 'siteadmin';
require '../inc.php';


if(filter_input(INPUT_POST, 'deact')) { // if this is a deactivation request

    $bl_id = filter_input(INPUT_POST, 'id');
    if(!verifyFormToken('act'.$bl_id, $tokens)) { // verify token
        ifTokenBad('BL De-activate'); // if bad log and send error
    }

    $dbl->BLactive($bl_id, false); // run query to deact BL ban
    sendGood('This blacklist ban has been de-activated');
    exit; // no need to continue

} elseif(filter_input(INPUT_POST, 'react')) { // if this is a re-activation request

    $bl_id = filter_input(INPUT_POST, 'id');
    if(!verifyFormToken('act'.$bl_id, $tokens)) { // verify token
        ifTokenBad('BL De-activate'); // if bad log and send error
    }

    $dbl->BLactive($bl_id, true); // run query to reactivate BL ban
    sendGood('This blacklist ban has been re-activiated');
    exit; // no need to continue

} elseif(filter_input(INPUT_POST, 'ip')) { // if this is an add request

    if(!verifyFormToken('addbl', $tokens)) { // verify token
        ifTokenBad('BL Add'); // if bad log, add hack counter and throw error
    }

    // set and clean vars
    $reason = cleanvar(filter_input(INPUT_POST, 'reason'));
    $ip = cleanvar(filter_input(INPUT_POST, 'ip'));

    // check for empty inputs
    emptyInput($reason, 'the reason');
    emptyInput($ip, 'IP Address');

    // if reason is default comment msg, send back with error
    if($reason == "Enter a reason for this ban...") {
        sendBack('You must add a reason as to why this IP ban is being added');
    }

    // check if it is a valid IP address
    if(!filter_var($ip, FILTER_VALIDATE_IP)) {
        sendBack('That IP address is not valid');
    }

    $whitelist = array('token','reason','ip'); // allow form fields to be sent

    // Building an array with the $_POST-superglobal 
    foreach (filter_input_array(INPUT_POST) as $key=>$item) {
        if(!in_array($key, $whitelist)) {
            hack(1); // plus 1 to hack counter
            writeLog('Add BL - Unknown form fields provided'); // make note of event
            sendBack('Unknown Information sent.');
            exit;
        }
    } // end foreach
	
    ## Query Section ##
    $result = $dbl->blacklist($ip, $reason, $mem->id);
    if(!$result) { // if false
        sendBack('That IP was not added to the blacklist.');
    }
	
    // if got this far we are doing well so lets send back a good message
    sendGood('The IP has been added to the banlist.');
    exit; // no need to continue

} else { // if this page was not posted and a user indirectly ends up on this page then sent to SA page with error
    send('../sa.php');
    set_error('Please do not load that page without submitting the ban IP address form.');
}
