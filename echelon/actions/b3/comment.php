<?php
$auth_name = 'comment';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

if(!filter_input(INPUT_POST, 'comment-sub')) : // if the form is submitted
    set_error('Please do not call that page directly');
    send('../../');
endif;

## check that the sent form token is corret
if(verifyFormToken('comment', $tokens) == false) { // verify token
    ifTokenBad('Add comment');
}

// Gets vars from form
$cid = cleanvar(filter_input(INPUT_POST, 'cid'));
$comment = cleanvar(filter_input(INPUT_POST, 'comment'));
$adminname = 'added by ' .$mem->name;

// Check for empties
emptyInput($comment, 'comment');
emptyInput($cid, 'client id not sent');

## Check sent client_id is a number ##
if(!isID($cid)) {
    sendBack('Invalid data sent, comment not added');
}

## Query ##
$result = $dbl->addEchLog('Comment', $comment, $cid, $mem->id, $game);

$query = $db->penClient('Notice', $cid, '0', $comment, $adminname, '-1');
if($query) {
    sendGood('Comment added.');
}
else {
    sendBack('There is a problem, your comment was not added to the database.');
}
