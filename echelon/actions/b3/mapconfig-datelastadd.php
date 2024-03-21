<?php
$auth_name = 'mapconfig';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

// set vars
$id = cleanvar(filter_input(INPUT_POST, 'id'));

## check numeric id ##
if(!is_numeric($id)) {
    sendBack('Invalid data sent, request aborted');
}
   
## Query Section ##
$result = $db->datelastaddMapconfig($id);
if($result) {
    sendGood($id."'s date has been updated");
}
else {
    sendBack('There is a problem. The mapconfig date has not been updated');
}
exit;

## return good ##
sendGood('Your mapconfig date has been successfully updated');
