<?php
$auth_name = 'edit_ban';
$b3_conn = true; // this page needs to connect to the B3 database
require '../../inc.php';

if(!filter_input(INPUT_POST, 'eb-sub')) { // if the form was is not submitted
    set_error('Please do not call that page directly, thank you.');
    send('../../index.php');
}

## check that the sent form token is corret
//if(verifyFormToken('editban', $tokens) == false) { // verify token
//    ifTokenBad('Edit ban');
//}
	
$pen_id = cleanvar(filter_input(INPUT_POST, 'pen_id'));
$pbid = cleanvar(filter_input(INPUT_POST, 'pbid'));
$pb_ban = cleanvar(filter_input(INPUT_POST, 'pb'));
$reason = cleanvar(filter_input(INPUT_POST, 'reason'));
$cid = cleanvar(filter_input(INPUT_POST, 'client_id'));
$inactive = cleanvar(filter_input(INPUT_POST, 'inactive'));
if($pb_ban == 'on') {
    $is_pb_ban = true;
    $type = 'Ban';
    $duration = 0;
    $time_expire = '-1';
} else {
    $is_pb_ban = false;
    $type = 'TempBan';
    // duration_num is the number component of the penalty duration (20m is 20 minutes, duration_num = 20, time = m)
    $duration_num = cleanvar(filter_input(INPUT_POST, 'duration'));
    // time is the letter "block" (ex: h = hours) see functions.php penDuration
    $time = cleanvar(filter_input(INPUT_POST, 'time'));
    emptyInput($time, 'time frame');
    emptyInput($duration_num, 'penalty duration');

    // NOTE: the duration in the DB is done in MINUTES and the time_expire is written in unix timestamp (in seconds)
    $duration = penDuration($time, $duration_num);

    $duration_secs = $duration*60; // find the duration in seconds

    $time_expire = time() + $duration_secs; // time_expire is current time plus the duration in seconds
}

// check for empty reason
emptyInput($reason, 'ban reason');

if( !isID($pen_id) || !isID($cid) ) {
    echlog("debug", "Edit-Ban invalid info for cid=" . $cid . "; pen_id=" . $pen_id);
    sendBack('Some of the information sent by you is invalid, the ban was not edited');
}

## Query Section ##
$query = "UPDATE penalties SET type = ?, duration = ?, time_edit = UNIX_TIMESTAMP(), time_expire = ?, reason = ? WHERE id = ? LIMIT 1";
$stmt = $db->mysql->prepare($query) or die('DB Error');
$stmt->bind_param('siisi', $type, $duration, $time_expire, $reason, $pen_id);
$stmt->execute();

if($stmt->affected_rows > 0) {
    $results = true;
}
else {
    sendBack('Something went wrong');
}

## If a permaban send unban rcon command (the ban will still be enforced then by the B3 DB ##
if($type == 'Ban'  && $inactive == "1" && $pbid != "") :
	
    ## Loop through servers for this game and send unban command and update ban file
    $i = 1;
    while($i <= $game_num_srvs) :

        if($config['games'][$game]['servers'][$i]['pb_active'] == '1') {
            // get the rcon information from the massive config array
            $rcon_pass = $config['game']['servers'][$i]['rcon_pass'];
            $rcon_ip = $config['game']['servers'][$i]['rcon_ip'];
            $rcon_port = $config['game']['servers'][$i]['rcon_port'];

            // PB_SV_BanGuid [guid] [player_name] [IP_Address] [reason]
            $command = "pb_sv_unbanguid " . $pbid;
            rcon($rcon_ip, $rcon_port, $rcon_pass, $command); // send the ban command
            sleep(1); // sleep for 1 sec in ordere to the give server some time
            $command_upd = "pb_sv_updbanfile"; // we need to update the ban files
            rcon($rcon_ip, $rcon_port, $rcon_pass, $command_upd); // send the ban file update command
        }

        $i++;
    endwhile;

endif;

// set comment for the edit ban action
$comment = 'A ban for this user was edited';

## Query ##
$result = $dbl->addEchLog('Edit Ban', $comment, $cid, $mem->id, $game);

if($results) {
    sendGood('Ban edited');
}
else {
    sendBack('NO!');
}

echlog("debug", "Edit-Ban " . $cid);
//if ($cid > 0) {
//    send('../../clientdetails.php?id=' . $cid);
//}

send('../../index.php');
//exit;
