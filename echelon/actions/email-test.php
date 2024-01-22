<?php
#error_reporting(-1);
#ini_set('display_errors', 'On');
#set_error_handler("var_dump");

echo ('<html><body>DK:t1<br />');

$auth_name = 'add_user';
require '../inc.php';
require '../inc/functions_messenger.php';

## if form is submitted ##	
if(!isset(filter_input(INPUT_POST, 'email-test'))) { // if this was not a post request then send back with error 
    sendBack('Please do not access that page directly');
}

## check that the sent form token is corret
//if(!verifyFormToken('adduser', $tokens)) // verify token
//	ifTokenBad('Add User');

// set email and comment and clean
$email = cleanvar(filter_input(INPUT_POST, 'email'));

// check the new email address is a valid email address
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
    sendBack('That email is not valid');
}

$user_key = "blabla";

//$file = '/var/www/html/echelonv1/files/test1.txt';
//$data = array ('test1', 'test');
//////array_push($data, );
//file_put_contents($file, implode("\n", $data));

## email user about the key ##
$body = '<html><body>';
$body .= '<h2>Echelon Test Email</h2>';
$body .= $config['cosmos']['email_header'];
$body .= 'This is what a registration email for Echelon would look like. 
    <a href="http://'.filter_input(INPUT_SERVER, 'SERVER_NAME').PATH.'register.php?key='.$user_key.'&amp;email='.$email.'">Register here</a>.<br />';
$body .= 'Registration Key: '.$user_key . '<br />';
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

//$file = '/var/www/html/echelonv1/files/test2.txt';
//$data = array ('test2', $email, $body);
////array_push($data, );
//file_put_contents($file, implode("\n", $data));

$mgr = new messenger(); 
$mgr->to($email);
$mgr->replyto($email_config['board_email']);
$mgr->from($email_config['board_email']);
$mgr->subject($subject);
$mgr->msg($body);
//$mgr->extra_headers($headers);
$mgr->anti_abuse_headers();
$mgr->setconfigvalues($email_config);
//$mgr->set_mail_priority();
try {
    $mgr->send();
} catch(Exception $e) {
    sendBack('Caught Exception: ', $e->getMessage(), ".");
}
    
## run query to add key to the DB ##
$add_user = $dbl->addEchKey($user_key, $email, $comment, $group, $mem->id);
if(!$add_user) {
    sendBack('There was a problem adding the key into the database');
}

// all good send back good message
sendGood('The Email has been sent to user');
//sendGood('Send this link to user: "http://'.$_SERVER['SERVER_NAME'].PATH.'register.php?key='.$user_key.'&amp;email='.$email.'"');
