<?php
//$page = "settings-game";
//$page_title = "Game Settings";
$auth_name = 'manage_settings';
$b3_conn = true; // needed to test the B3 DB for a successful connection
//$pagination = false; // this page requires the pagination part of the footer
require '../inc.php';

## Check that the form was posted and that the user did not just stumble here ##
if(!filter_input(INPUT_POST, 'game-settings-sub')) :
    set_error('Please do not call that page directly, thank you.' . filter_input(INPUT_POST, 'game-settings-sub'));
    send('../index.php');
endif;

## Find Type ##
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
if($is_add) {
    //if(!verifyFormToken('addgame', $tokens)) // verify token
    //ifTokenBad('Add Game');
} else {
    if(!verifyFormToken('gamesettings', $tokens)) { // verify token
        ifTokenBad('Game Settings Edit');
    }
}

## Get Vars ##
$name = cleanvar(filter_input(INPUT_POST, 'name'));
$name_short = cleanvar(filter_input(INPUT_POST, 'name-short'));
if($is_add) {
    $game_type = cleanvar(filter_input(INPUT_POST, 'game-type'));
}
// DB Vars
$db_host = cleanvar(filter_input(INPUT_POST, 'db-host'));
$db_user = cleanvar(filter_input(INPUT_POST, 'db-user'));
$db_pw_cng = cleanvar(filter_input(INPUT_POST, 'cng-pw'));
$db_pw = cleanvar(filter_input(INPUT_POST, 'db-pw'));
$db_name = cleanvar(filter_input(INPUT_POST, 'db-name'));
// plugins enabled
#$g_plugins = explode(",", filter_input(INPUT_POST, 'active-plugins'));
#echlog("debug", "settings-game " . join(",", $g_plugins));
$g_plugins = filter_input(INPUT_POST, 'active-plugins');
// Verify Password
$password = filter_input(INPUT_POST, 'password'); // do not clean passwords

// Whether to change DB PW or not
if($db_pw_cng == 'on') {
    $change_db_pw = true;
}
else {
    $change_db_pw = false;
}
	
## Check for empty vars ##
emptyInput($name, 'game name');
emptyInput($name_short, 'short version of game name');
emptyInput($db_user, 'DB Username');
emptyInput($db_host, 'DB Host');
emptyInput($db_name, 'DB name');

if( ($change_db_pw == true) && (!$is_add) ) {
    emptyInput($db_pw, 'DB password');
}

if(!$is_add) {
    emptyInput($password, 'your current password');
}
	
if($is_add) :
    ## Check game is supported ##
    if(!array_key_exists($game_type, $supported_games)) {
        sendBack('That game type does not exist, please choose a game');
    }
endif;

#$enabled = '';
#if(!empty($g_plugins)) :
#    foreach($g_plugins as $plugin) :
#        $enabled .= $plugin.',';
#    endforeach;
#    $enabled = substr($enabled, 0, -1); // remove trailing comma
#endif;

$enabled = $g_plugins;

#echlog("debug", "DB test connection " . $db_host . ', ' . $db_user . ', ' . $db_pw . ', ' . $db_name);
## Check that the DB information supplied will make a connection to the B3 database.
$db_test = new mysqli($db_host, $db_user, $db_pw, $db_name);

if(($change_db_pw == true) && ($db_test->connect_error)) { // send back with a failed connection message
    sendBack('<strong>Database Connection Error</strong>
        <p>The connection information you supplied is incorrect.<br />'.$db_test->connect_error.'</p>'); 
}

## Update DB ##
if($is_add) : // add game queries
    //echlog("debug", "Adding Game " . $name . ', ' . $game_type . ', ' . $name_short . ', ' . $db_host . ', ' . $db_user . ', ' . $db_pw . ', ' . $db_name);
    $result = $dbl->addGame($name, $game_type, $name_short, $db_host, $db_user, $db_pw, $db_name);
    if(!$result) { // if everything is okay
        sendBack('There is a problem, the game information was not saved.');
    }

    $dbl->addGameCount(); // Add one to the game counter in config table	
	
else : // edit game queries
    //echlog("debug", "Updating Game " . $game . ', ' . $name . ', ' . $name_short . ', ' . $db_user . ', ' . $db_host . ', ' . $db_name . ', ' . $db_pw . ', ' . $change_db_pw . ', ' . $enabled);
    $mem->reAuthUser($password, $dbl);
    $result = $dbl->setGameSettings($game, $name, $name_short, $db_user, $db_host, $db_name, $db_pw, $change_db_pw, $enabled); // update the settings in the DB
    if(!$result) {
        sendBack('Something did not update. Did you edit anything?');
    }
endif;

## Return with result message
if($is_add) {
    set_good('Game Added');
    send('../settings-games.php');
} else {
    sendGood('Your settings have been updated');
}
