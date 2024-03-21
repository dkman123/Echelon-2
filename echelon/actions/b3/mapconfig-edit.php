<?php
$auth_name = 'mapconfig';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

// set vars
$id = cleanvar(filter_input(INPUT_POST, 'id'));
$mapname = cleanvar(filter_input(INPUT_POST, 'mapname'));

## check numeric id ##
if(!is_numeric($id)) {
    sendBack('Invalid data sent, request aborted');
}
        
$capturelimit = cleanvar(filter_input(INPUT_POST, 'capturelimit'));
$g_suddendeath = cleanvar(filter_input(INPUT_POST, 'g_suddendeath'));
$g_gear = cleanvar(filter_input(INPUT_POST, 'g_gear'));
$g_gravity = cleanvar(filter_input(INPUT_POST, 'g_gravity'));
$g_friendlyfire = cleanvar(filter_input(INPUT_POST, 'g_friendlyfire'));
$timelimit = cleanvar(filter_input(INPUT_POST, 'timelimit'));


if(isset(filter_input(INPUT_POST, 'startmessage'))) {
    $startmessage = cleanvar(filter_input(INPUT_POST, 'startmessage'));
}
if($startmessage == null) {
    $startmessage = "";
}
$skiprandom = cleanvar(filter_input(INPUT_POST, 'skiprandom'));
$datelastadd = cleanvar(filter_input(INPUT_POST, 'datelastadd'));

## check numeric ##
if(!is_numeric($g_suddendeath)) {
    $g_suddendeath = 0;
}
if(!is_numeric($g_gravity)) {
    $g_gravity = 800;
}
if(!is_numeric($g_friendlyfire)) {
    $g_friendlyfire = 0;
}
if(!is_numeric($skiprandom)) {
    $skiprandom = 0;
}
if(!is_numeric($timelimit)) {
    $timelimit = 20;
}
//if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $datelastadd)) {
//    $datelastadd = "2000-01-01";
//}
$dt = DateTime::createFromFormat("Y-m-d", $datelastadd);
// logic will check for errors, so negate it
if(!($dt !== false && !array_sum($dt::getLastErrors()))) {
    $datelastadd = "2000-01-01";
}

if (is_null($g_gear) || $g_gear == "") {
    $g_gear = "0";
}

// check for empty inputs
emptyInput($mapname, 'map name');

## Query Section ##

if(filter_input(INPUT_POST, 't') == 'del') : // delete mapconfig
    $result = $db->delMapconfig($id);
    if($result) {
        sendGood('Mapconfig has been deleted');
    }
    else {
        sendBack('There is a problem. The mapconfig has not been deleted'); 
    }
    exit;
elseif(filter_input(INPUT_POST, 't') == 'edit') :  // edit/update a mapconfig
    $result = $db->editMapconfig($id, $mapname, $capturelimit, $g_suddendeath, $g_gear, $g_gravity, $g_friendlyfire, $startmessage, $skiprandom, $datelastadd, $timelimit);
    if($result) {
        sendGood($mapname."'s information has been updated");
    }
    else {
        sendBack('There is a problem. The mapconfig information has not been changed');
    }
    exit;
elseif(filter_input(INPUT_POST, 't') == 'add') :  // add a new mapconfig
    $result = $db->addMapconfig($mapname, $capturelimit, $g_suddendeath, $g_gear, $g_gravity, $g_friendlyfire, $startmessage, $skiprandom, $datelastadd, $timelimit);
    if($result) {
        sendGood($mapname."'s information has been saved");
    }
    else {
        sendBack('There is a problem. The mapconfig has not been saved');
    }
    exit;
else :
    sendBack('There is a problem. Unknown command received.');
endif;

## return good ##
sendGood('Your mapconfig information has been successfully updated');
