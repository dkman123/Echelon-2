<?php
//$page = "settings-server";
//$page_title = "Server Settings";
$auth_name = 'manage_settings';
$b3_conn = true; // needed to test the B3 DB for a successful connection
//$pagination = false; // this page requires the pagination part of the footer
require '../inc.php';

## delete server
if(filter_input(INPUT_GET, 't') == 'del') :

    // get and check the id sent
    if(!empty(filter_input(INPUT_GET, 'id')) || isID(filter_input(INPUT_GET, 'id'))) {
        $sid = filter_input(INPUT_GET, 'id');
    }
    else {
        sendBack('Vital information needed to delete the server was not sent.');
    }

    ## check that the form token is corret
    if(!verifyFormToken('del-server'.$sid, $tokens)) { // verify token
        ifTokenBad('Deleting a server');
    }

    $result = $dbl->delServer($sid);
    if(!$result) {
        sendBack('There was a problem with deleting the server.');
    }

    $result = $dbl->delServerUpdateGames($game_id);
    if(!$result) {
        sendBack('There was a problem with updating the games list after deleting the server.');
    }

    sendGood('The server has been deleted.');
	
    exit; // stop - no need to load the rest of the page

endif;

## Check that the form was posted and that the user did not just stumble here ##
if(empty(filter_input(INPUT_POST, 'server-settings-sub'))) :
    set_error('Please do not call that page directly, thank you.' . filter_input(INPUT_POST, 'server-settings-sub'));
    send('../index.php');
endif;

## What type of request is it ##
if(filter_input(INPUT_POST, 'type') == 'add') {
    $is_add = true;
}
elseif(filter_input(INPUT_POST, 'type') == 'edit') {
    $is_add = false;
}
else {
    sendBack('Missing Data');
}

## Check Token ##
if($is_add) { // if add server request
    if(verifyFormToken('addserver', $tokens) == false) { // verify token
        ifTokenBad('Add Server');
    }
} else { // if edit server settings
    if(verifyFormToken('editserversettings', $tokens) == false) { // verify token
        ifTokenBad('Server Settings Edit');
    }
}

## Get Vars ##
$name = cleanvar(filter_input(INPUT_POST, 'name'));
$ip = cleanvar(filter_input(INPUT_POST, 'ip'));
$pb = cleanvar(filter_input(INPUT_POST, 'pb'));
// DB Vars
$rcon_ip = cleanvar(filter_input(INPUT_POST, 'rcon-ip'));
$rcon_port = cleanvar(filter_input(INPUT_POST, 'rcon-port'));
$rcon_pw_cng = cleanvar(filter_input(INPUT_POST, 'cng-pw'));
$rcon_pw = cleanvar(filter_input(INPUT_POST, 'rcon-pass'));
$server_id = cleanvar(filter_input(INPUT_POST, 'server'));
$mapcyclefile = cleanvar(filter_input(INPUT_POST, 'mapcyclefile'));
$mapcycleurl = cleanvar(filter_input(INPUT_POST, 'mapcycleurl'));

if($is_add) {
    $game_id = cleanvar(filter_input(INPUT_POST, 'game-id'));
}

// Whether to change RCON PW or not
if($rcon_pw_cng == 'on') {
    $change_rcon_pw = true;
}
else {
    $change_rcon_pw = false;
}
	
// Whether to change DB PW or not
if($pb == 'on') {
    $pb = 1;
}
else {
    $pb = 0;
}

## Check for empty vars ##
emptyInput($name, 'server name');
emptyInput($ip, 'server IP');
emptyInput($rcon_ip, 'Rcon IP');
emptyInput($rcon_port, 'Rcon Port');
if($change_rcon_pw == true) {
    emptyInput($rcon_pw, 'Rcon password');
}

// check that the rcon_ip is valid
if(!filter_var($rcon_ip, FILTER_VALIDATE_IP)) {
    sendBack('That RCON IP Address is not valid.');
}
	
// check that the rcon_ip is valid
if( (!filter_var($ip, FILTER_VALIDATE_IP))) {
    sendBack('That server IP Address is not valid.');
}
	
// Check Port is a number between 4-5 digits
if( (!is_numeric($rcon_port)) || (!preg_match('/^[0-9]{4,5}$/', $rcon_port)) ) {
    sendBack('Rcon Port must be a number between 4-5 digits');
}

if($is_add) : // if is add server request
    if(!is_numeric($game_id)) { // game_id is a digit
        sendBack('Invalid data sent.');
    }
endif;
	
## Update DB ##
if($is_add) :
    //echlog("debug", "Adding Server" . $game_id . ', ' . $name . ', ' . $ip, $pb . ', ' . $rcon_ip . ', ' . $rcon_port . ', ' . $rcon_pw);
    $result = $dbl->addServer($game_id, $name, $ip, $pb, $rcon_ip, $rcon_port, $mapcyclefile, $mapcycleurl, $rcon_pw);
    $dbl->addServerUpdateGames($game_id);
else :
    //echlog("debug", "Updating Server" . $server_id . ', ' . $name . ', ' . $ip . ', ' . $pb . ', ' . $rcon_ip . ', ' . $rcon_port . ', "' . $mapcyclefile . '", ' . $rcon_pw . ', ' . $change_rcon_pw);
    $result = $dbl->setServerSettings($server_id, $name, $ip, $pb, $rcon_ip, $rcon_port, $mapcyclefile, $mapcycleurl, $rcon_pw, $change_rcon_pw); // update the settings in the DB
endif;

if(!$result) {
    sendBack('Something did not update');
}

## Return ##
if($is_add) {
    set_good('Server '. $name .' has been added to the database records.');
    send('../settings-server.php');
} else {
    sendGood('Your settings have been updated.');
}
