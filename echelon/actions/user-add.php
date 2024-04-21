<?php
#error_reporting(-1);
#ini_set('display_errors', 'On');
#set_error_handler("var_dump");

$auth_name = 'add_user';
$b3_conn = true; // this page needs to connect to the B3 database
require '../inc.php';

## if form is submitted ##	
if(!filter_input(INPUT_POST, 'add-user')) { // if this was not a post request then send back with error 
    sendBack('Please do not access that page directly');
}

## check that the sent form token is corret
if(!verifyFormToken('adduser', $tokens)) { // verify token
    ifTokenBad('Add User');
}

// set email and comment and clean
$email = cleanvar(filter_input(INPUT_POST, 'email'));
$comment = cleanvar(filter_input(INPUT_POST, 'comment'));
$group = cleanvar(filter_input(INPUT_POST, 'group'));

// check the new email address is a valid email address
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
    sendBack('That email is not valid');
}

// Create a unique key for the user
$text = $email . uniqid(microtime(), true) . $group; // take sent data and some random data to create a random string
$rand_text = str_shuffle($text); // shuffle the string to make more random
$user_key = genHash($rand_text); // hash the random string to get the user hash

## email user about the key ##
$body = '<html><body>';
$body .= '<h2>Echelon User Key</h2>';
$body .= $config['cosmos']['email_header'];
$body .= 'This is the key you will need to use to register on Echelon. 
    <a href="http://' . filter_input(INPUT_SERVER, 'SERVER_NAME') . PATH . 'register.php?key=' . $user_key . '&amp;email=' . $email . '">Register here</a>.<br />';
$body .= 'Registration Key: ' . $user_key . '<br />';
$body .= $config['cosmos']['email_footer'];
$body .= '</body></html>';

// replace %ech_name% in body of email with var from config
$body = preg_replace('#%ech_name%#', $config['cosmos']['name'], $body);
// replace %name%
$body = preg_replace('#%name%#', 'new user', $body);

$headers =  'MIME-Version: 1.0' . "\r\n"; 
$headers = "From: echelon@".filter_input(INPUT_SERVER, 'HTTP_HOST')."\r\n";
$headers .= "Reply-To: ". EMAIL ."\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
$subject = "Echelon User Registration";


// send email
$mgr = new messenger(); 
$mgr->to($email);
$mgr->replyto($email_config['board_email']);
$mgr->from($email_config['board_email']);
$mgr->subject($subject);
$mgr->msg($body);
//$mgr->extra_headers($headers);
$mgr->anti_abuse_headers();
//echlog("debug", "User-Add email_config " .print_r($email_config));
//var_dump($email_config);
$mgr->setconfigvalues($email_config);
//$mgr->set_mail_priority();
try {
//echlog("debug", "User-Add email " . $email . ', ' . $email_config['board_email'] . ', ' . $subject . ', ' . $body);
    $mgr->send();
} catch(Exception $e) {
    sendBack('Caught Exception: ', $e->getMessage(), ".");
}

//echlog("debug", "User-Add " . $user_key . ', ' . $email . ', ' . $comment . ', ' . $group . ', ' . $mem->id);
## run query to add key to the DB ##
$add_user = $dbl->addEchKey($user_key, $email, $comment, $group, $mem->id);
if(!$add_user) {
    sendBack('There was a problem adding the key into the database');
}

// all good send back good message
#sendGood('Key Setup and Email has been sent to user');
sendGood('Send this link to user: "http://' . filter_input(INPUT_SERVER, 'SERVER_NAME') . PATH . 'register.php?key=' . $user_key . '&amp;email=' . $email . '"');
