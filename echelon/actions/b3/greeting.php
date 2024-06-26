<?php
$auth_name = 'greeting';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

if(filter_input(INPUT_POST, 'greeting-sub')) : // if the form is submitted
    ## check that the sent form token is corret
    if(verifyFormToken('greeting', $tokens) == false) { // verify token
        ifTokenBad('Add comment');
    }

    $greeting = cleanvar(filter_input(INPUT_POST, 'greeting'));
    $client_id = cleanvar(filter_input(INPUT_POST, 'cid'));

    // NOTE: allow for an empty comment. An empty comment means no comment
    emptyInput($client_id, 'data not sent');

    if(!isID($client_id)) {
        sendBack('Invalid data sent, greeting not changed');
    }

    ## Add Log Message ##
    $comment = 'Greeting message changed';
    $dbl->addEchLog('Greeting', $comment, $client_id, $mem->id, $game);	

    ## Query ##
    $query = "UPDATE clients SET greeting = ? WHERE id = ? LIMIT 1";
    $stmt = $db->mysql->prepare($query) or sendBack('Database Error.');
    $stmt->bind_param('si', $greeting, $client_id);
    $stmt->execute();
    if($stmt->affected_rows) {
        sendGood('Greeting has been updated.');
    }
    else {
        sendBack('Greeting was not updated.');
    }

    $stmt->close(); // close connection

else :

    set_error('Please do not call that page directly, thank you.');
    send('../../index.php');

endif;
