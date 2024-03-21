<?php
$auth_name = 'permmute';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

if(filter_input(INPUT_POST, 'permmute-sub')) : // if the form is submitted
	## check that the sent form token is correct
	if(verifyFormToken('permmute', $tokens) == false) { // verify token
            ifTokenBad('Unable to update PermMute');
        }

	$permmute = cleanvar(filter_input(INPUT_POST, 'permmute'));
	$client_id = cleanvar(filter_input(INPUT_POST, 'cid'));
	
	// NOTE: allow for an empty comment. An empty comment means no comment
	emptyInput($client_id, 'data not sent');

	if(!isID($client_id)) {
            sendBack('Invalid data sent, permmute not changed');
        }
	
	## Add Log Message ##
	$comment = 'PermMute changed';
	$dbl->addEchLog('PermMute', $comment, $client_id, $mem->id, $game);
		
	## Query ##
	$query = "UPDATE clients SET permmute = ? WHERE id = ? LIMIT 1";
	$stmt = $db->mysql->prepare($query) or sendBack('Database Error.');
        # reminder: param type list (s = string; i = int)
	$stmt->bind_param('ii', $permmute, $client_id);
	$stmt->execute();
	if($stmt->affected_rows) {
            sendGood('PermMute has been updated.');
        }
	else {
            sendBack('PermMute was not updated.');
        }
	
	$stmt->close(); // close connection

else :

    set_error('Please do not call that page directly, thank you.');
    send('../../index.php');

endif;
