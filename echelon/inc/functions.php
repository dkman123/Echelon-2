<?php
#### FUNCTIONS.PHP ####
## Basic functions that help run all pages on this site ##
## This page is included on all pages in this project ##

/**
 * Checks if a password contains any unwanted characters
 *
 * @param string $pw - password string
 * @return bool
 */
function testPW($pw) {

    // no space
    if(preg_match('# #', $pw)) {
        return false;
    }

    // no dbl quote
    if(preg_match('#"#', $pw)) {
        return false;
    }

    // no single quote
    if(preg_match("#'#", $pw)) {
        return false;
    }

    // no equals signs
    if(preg_match("#=#", $pw)) {
        return false;
    }

    return true;
}

/**
 * Checks that the supplied id matches the required criteria 
 *
 * @param string $id - the id to check. The id is MySQL auto_increment id check
 * @return bool
 */
function isID($id) {

    // not empty
    if(empty($id)) {
        return false;
    }

    // stops first number of id being a zero
    $fc = substr($id, 0, 1);
    if($fc == 0) {
        return false;
    }

    if(!is_numeric($id)) {
        return false;
    }

    return true;
}

function delUserLink($id, $token) {

    if($_SESSION['user_id'] == $id) { // user cannot delete themselves
        return NULL;
    }
    else {
        return '<form action="actions/user-edit.php" method="post" class="user-del">
                    <input type="hidden" value="'.$token.'" name="token" />
                    <input type="hidden" value="'.$id.'" name="id" />
                    <input type="hidden" value="del" name="t" />
                    <div class="row ml-auto"><!-- find better solution, like resizing table cell/column -->
                    <input class="harddel" type="image" src="images/user_del.png" alt="Delete" title="Delete this user forever" />
                </form>';
    }
}

function editUserLink($id, $name) {

    return '<a href="sa.php?t=edituser&amp;id='.$id.'" title="Edit '. $name .'"><img src="images/user_edit.png" alt="edit" /></a>';
	
}

function displayEchLog($array, $style = 'client') {

    global $tformat; // import the time format varible for use in this function

    foreach((array)$array as $ech_log) :

        ## get vars
        $id = $ech_log['id'];
        $type = $ech_log['type'];
        $msg = tableClean($ech_log['msg']);
        $time_add = $ech_log['time_add'];
        $time_add_read = date($tformat, $time_add);
        $game_id = $ech_log['game_id'];

        ## Page row color alternate
        $alter = alter();

        if($style == 'admin') :

            $cid = $ech_log['client_id'];
            $client_link = clientLink($cid, $cid, $game_id);

            $table = <<<EOD
            <tr class="$alter">
                <td>$id</td>
                <td>$type</td>
                <td>$msg</td>
                <td><em>$time_add_read</em></td>
                <td>$client_link</td>
            </tr>
EOD;

        else: // if client
		
            $user_name = tableClean($ech_log['user_name']);
            $user_link = echUserLink($ech_log['user_id'], $user_name);

            $table = <<<EOD
            <tr class="$alter">
                <td>$id</td>
                <td>$type</td>
                <td>$msg</td>
                <td><em>$time_add_read</em></td>
                <td>$user_link</td>
            </tr>
EOD;
        endif;
		
        echo $table; // echo out the formated data
			
    endforeach;

}

function alter() {

    static $alt = false;

    $alt = !$alt;

    if($alt) {
        return 'odd';
    }
    else {
        return 'even';
    }
}

/**
 * Sends an rcon command to a server
 *
 * @param string $rcon_ip - IP for rcon connections
 * @param string $rcon_port - Port for rcon connection
 * @param string $rcon_pass - Server rcon Password
 * @param string $command - The rcon command being sent
 * @return string
 */
function rcon($rcon_ip, $rcon_port, $rcon_pass, $command) {

    if (!isset($errno)) {
        $errno = 0;
    }
    if (!isset($errstr)) {
        $errstr = "";
    }    
    
    $fp = fsockopen("udp://$rcon_ip",$rcon_port, $errno, $errstr, 2);
    @socket_set_timeout($fp, 2); // if error, ignore because some servers block this command

    if(!$fp) {
        return "$errstr ($errno)<br>\n";
        //sendBack('Could not send RCON Command.');
    } else {
        $query = "\xFF\xFF\xFF\xFFrcon \"" . $rcon_pass . "\" " . $command;
        fwrite($fp,$query);
    }

    $data = '';
    while($d = fread($fp, 10000)) :
        $data .= $d;
    endwhile;

    fclose($fp);
    $data = preg_replace("/....print\n/", "", $data);
    return $data;
}

/**
 * Spits out the unban/remove penalty button
 *
 * @param string $pen_id - id of the penalty to remove
 * @param string $cid - client_id of the client the penalty is against
 * @param string $type - the type of penalty it is
 * @param string $inactive - whether the penalty is active or not
 * @return string
 */
function unbanButton($pen_id, $cid, $type, $inactive) {
	
    $token = genFormToken('unban'.$pen_id); // gen form token with appened penalty id in order to make all the tokens unique

    // if pen is a tempban, ban or warning and it is still active then show unban
    if( ($type == 'TempBan' || $type == 'Ban' || $type == 'Warning') && ($inactive == 0) ) {
        return '<form method="post" action="actions/b3/unban.php" class="unban-form">
                    <input type="hidden" name="token" value="'.$token.'" />
                    <input type="hidden" name="cid" value="'.$cid.'" />
                    <input type="hidden" name="banid" value="'.$pen_id.'" />
                    <input type="hidden" name="type" value="'.$type.'" />
                    <button style="padding: 0; border: none; background: none; cursor: pointer;" type="submit" value="Unban" name="unban-sub" title="De-Activate / Unban"><img src="images/delete.png"></button>
                </form>';
    } else {
        return null;
    }

}

/**
 * Spits out the edit ban button
 *
 * @param string $type - the type of penalty it is
 * @param string $pen_id - id of the penalty to remove
 * @param string $inactive - whether the penalty is active or not
 * @return string
 */
function editBanButton($type, $pen_id, $inactive) {
    if( ($inactive == 0) && ($type == 'TempBan' || $type == 'Ban') ) { // if ban is active and the penalty is a Ban or Tempban show link
        return '<a href="editban.php?penid=' . $pen_id . '" class="edit-ban" style="cursor: pointer;" title="Edit ban id &ldquo;' . $pen_id . '&rdquo;"><img src="images/edit.png" alt="[EB]" /></a>';
    } else { // else show nothing
        return NULL;
    }
}

/**
 * Generates a general hash with sha1 and md5
 *
 * @param string $unhashed_text - the text you would like to hash
 * @return string
 */
function genHash($unhashed_text) {
    $md5 = md5($unhashed_text); // get md5
    $hashed = sha1(SALT.$md5); // get hash of text plus salt in sha1

    return $hashed; // return the inputted text
}

/**
 * Generates a password
 *
 * @param string $input - the actual clear text password
 * @param string $salt - the salt with which to hash the password
 * @return string $pw - hashed form of salt and inputted text
 */
function genPW($input, $salt) {
    $data = $input.$salt;
    $pw = hash("sha256", $data); // sha256 hash the passsword and the salt for an irrevrsible hash
    return $pw;
}

/**
 * Generates a new password salt
 *
 * @return string $salt
 */
function genSalt($length = 12) {
    $salt = randPass($length);
    return $salt;
}

/**
 * Generate a random password or string
 *
 * @param int $count - length of the string
 * @return string
 */
function randPass($count) {  
    $pass = str_shuffle('abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@#%$*'); //shuffle

    $rand_num = mt_rand(0,5); // get rand num for the rand start of substr

    return substr($pass, $rand_num, $count); //returns the password  
}

/**
 * Detect an AJAX request
 *
 * @return bool
 */
function detectAJAX() {
    /* AJAX check  */
    if(!empty(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) && strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest') {
        return true;
    }
    else {
        return false;
    }

    // This method is not fool proof since all servers do not support the filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') varible.
}

/**
 * Detect an AJAX MS Internet Explorer
 *
 * @return bool
 */
function detectIE() {
    if (null !== filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') && (strpos(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'), 'MSIE') !== false)) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Checks if a user has attempted to login to many times or has been caught hacking the site
 */
function locked() {
    if($_SESSION['wrong'] >= 3 || $_SESSION['hack'] >= 3) : // if the user has three wrongs or three hack attempts 
            // logout the user, then add the IP of the user to the Blacklist

        global $dbl;
        global $mem;

        if($mem->loggedIn()) {
            session::logout(); // if they are logged in log them out
        }

        $ip = getRealIp(); // get users ip
        $dbl->blacklist($ip); // add top blacklist
        writeLog('Locked out automatically.');
        sendLocked();

    endif;
}

/**
 * Checks Blacklist for the users IP address and if banned send to locked
 */
function checkBL() {
    global $dbl;
    $ip = getRealIp(); // find real IP
    $result = $dbl->checkBlacklist($ip); // query db and check if ip is on list

    if($result) {// if on blacklist
        sendLocked(); // send to locked page
        exit;
    }
}

/**
 * Find how many login attempts the user has made
 */
function trys() { //
    echo '<div class="form-group row"><small class="mx-auto">';

    if (!isset($_SESSION['wrong'])) {
        $_SESSION['wrong'] = 0;
    }
    if($_SESSION['wrong'] != 0) {
        echo 'You have used '.$_SESSION['wrong'].' of 3 attempts to login.';
    }

    echo '</small></div>';
}

/**
 * Add a number to the wrong login attempt counter
 *
 * @param string $num - num to add to the wrong counter
 */
function wrong($num) { // add $num to number of already recorded wrong attempts
    $_SESSION['wrong'] = $_SESSION['wrong'] + $num;
}

/**
 * Add a number to the hacking attempt counter
 *
 * @param string $num - num to add to the hacking attempt counter
 */
function hack($num) {
    $_SESSION['hack'] = $_SESSION['hack'] + $num;
}

/**
 * Set an error message that is to be sent to the user
 *
 * @param string $msg - the error message
 */
function set_error($msg) {
    $_SESSION['error'] = $msg;
}

/**
 * Set a success message to be sent to the user
 *
 * @param string $msg - the message
 */
function set_good($msg) {
    $_SESSION['good'] = $msg;
}

/**
 * Set a warning message to be sent to the user
 *
 * @param string $msg - the warning message
 */
function set_warning($msg) {
    $_SERVER['warning'] = $msg;
}

function css_file($name) {
    echo '<link href="css/'. $name. '.css" rel="stylesheet" type="text/css" />';
}

/**
 * Get the IP address of the current user
 *
 * @return string $ip - IP address of the user
 */
function getRealIp() {
    if(!empty(filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP'))) {  // check ip from share internet
        $ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    }
    elseif(!empty(filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR'))) {  // to check ip is pass from proxy
        $ip = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
    }
    else {
        $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }

    return $ip;
}

/**
 * Check is a form var is empty if so set error and send back to referring page
 *
 * @param string $var - the variable to check
 * @param string $field - the name of the variable (used in the error message) eg. 'your new password
 */
function emptyInput($var, $field) {
    $var = trim($var);
    $ref = filter_input(INPUT_SERVER, 'HTTP_REFERER');
    if(empty($var)) {
        set_error('You must put something in the '.$field.' field.');
        send($ref); // send back to referering page
        exit;
    }
} // end function

/**
 * Cleans var of unwanted materials
 *
 * @param string $var - var to be cleaned
 * @return string
 */
function cleanvar($var) {
    if(empty($var)) {
        return $var;
    }
    $var = trim(htmlentities(strip_tags($var)));

    return $var;
} // end clean var

/**
 * Send a user back to the referring page with an error
 *
 * @param string $error - the error message that will be sent to the user
 */
function sendBack($error) {
    $ref = filter_input(INPUT_SERVER, 'HTTP_REFERER');
    set_error($error);
    send($ref); // send back to referering page
    exit; // end script
}

/**
 * Send a user back to the referring page with a success message
 *
 * @param string $good - success message to be sent to the user
 */
function sendGood($good) {
    $ref = filter_input(INPUT_SERVER, 'HTTP_REFERER');
    set_good($good);
    send($ref); // send back to referering page
    exit; // end script
}

/**
 * Send user to a given page
 *
 * @param string $where - page to send user to
 */
function send($where) {
    header("Location: {$where}");
}

/**
 * Send user to login page
 */
function sendLogin() { 
    header("Location: ".PATH."login.php");
}

/**
 * send to the locked page
 */
function sendLocked() {
    header("Location: ".PATH."error.php?t=locked");
}

/**
 * Send to home page
 */
function sendHome() {
    header("Location: ".PATH."index.php");
}

/**
 * Send to the error page
 */
function sendError($add = NULL) {
    if($add == NULL) {
        header("Location: ".PATH."error.php");
    }
    else {
        header("Location: ".PATH."error.php?t={$add}");
    }
}

/**
 * Handy tooltip creation function
 */
function tooltip($msg, $float = false) {
    if($float == true) {
        echo '<a class="tooltip" style="float: left; display: block;" title="'. $msg .'"></a>';
    }
    else {
        echo '<a class="tooltip" title="'. $msg .'"></a>';
    }
}

/**
 * Echo out simple clientdetails link
 */
function clientLink($name, $id, $game_id = NULL) {
    if(!empty($game_id)) {
        $href = '&amp;game='.$game_id;
    }
    else {
        $href= "";
    }

    return '<a href="clientdetails.php?id='.$id.$href.'" title="Check out '.$name.' client information profile">'.htmlentities($name).'</a>';
}

/**
 * Echo out simple cid details link
 */
function cidLink($name, $id, $game_id = NULL) {
    if(!empty($game_id)) {
        $href = '&amp;game='.$game_id;
    }
    else {
        $href= "";
    }

    return '<a href="clientdetails.php?id='.$id.$href.'" title="Check out '.$name.' client information profile">'.$id.'</a>';
}

/**
 * Echo out external link to punk-busted GUID banlist checker
 */
function guidCheckLink($guid) {
    echo '<a class="external" href="http://www.punksbusted.com/cgi-bin/membership/guidcheck.cgi?guid='.$guid.'" title="Check this guid is not banned by PunksBusted.com">'.$guid.'</a>';
}

/**
 * parse IP address into link to ip whois
 *
 * @param string $ip - ip address to use in link
 * @return string $msg - the link to whois the IP
 */
function ipLink($ip) {
    $msg = '<a href="http://whois.domaintools.com/'.$ip.'/" class="external" title="WhoIs IP Search this User">'.$ip.'</a>';
    return $msg;
}

/**
 * parse Email address into a mailto user link
 *
 * @param string $email - email address for link
 * @param string $name - name of the person
 * @return string $msg - the link to whois of IP
 */
function emailLink($email, $name) {
    if($name == '') { // if name is not set make name the same as email
        $name = $email;
    }

    $msg = '<a href="mailto:'.$email.'" title="Send '.$name.' an email">'.$email.'</a>';
    return $msg;
}

/**
 * Parse vars in a view user in more details link
 *
 * @param string $id - id of the user
 * @param string $name - name of the person
 * @return string $msg - the link to user
 */
function echUserLink($id, $name, $name_title = NULL, $name_box = NULL) {

    if(empty($name_title)) {
        $name_title = $name;
    }

    if(empty($name)) {
        $name = $id;
    }
    
    if(empty($name_box)) {
        $name_box = $name;
    }

    $msg = '<a href="sa.php?t=user&amp;id='.$id.'" title="View '.$name_title.' in more detail">'.$name_box.'</a>';
    return $msg;
}

function echGroupLink($id, $name) {
    $msg = '<a href="sa.php?t=perms-group&amp;id='.$id.'" title="View group '.$name.' in more detail">'.$name.'</a>';
    return $msg;
}

function ipintelLink($ip, $ipintel_email) {
    if (empty($ipintel_email))
    {
        $msg = '<a href="http://getipintel.net/#web" title="set $getipintel_email in inc.php" target="_blank"><img src="images/getipintel.png" width="16" height="16" alt="GetIPIntel" /></a>';
    }
    else {
	$msg = '<a href="http://check.getipintel.net/check.php?ip='.$ip.'&amp;contact='.$ipintel_email.'" title="Get IP Intel" target="_blank"><img src="images/getipintel.png" width="16" height="16" alt="GetIPIntel" /></a>';
    }
    return $msg;
}

function totalPages($total_rows, $max_rows) {
    $total_pages = ceil($total_rows/$max_rows)-1;
    return $total_pages;
}

function recordNumber($start_row, $max_rows, $total_rows) {

    echo 'Showing '.($start_row + 1).'&nbsp;to&nbsp;'.min($start_row + $max_rows, $total_rows).'&nbsp;entries out of&nbsp;'.$total_rows.'&nbsp;total entries.';
}

function queryStringPage() {
    $query_string = "";
    if (!empty(filter_input(INPUT_SERVER, 'QUERY_STRING'))) {

        $params = explode("&", filter_input(INPUT_SERVER, 'QUERY_STRING'));
        $newParams = array();

        foreach ($params as $param) {
            if (stristr($param, "p=") == false) {
                array_push($newParams, $param);
            }
        }

        if (count($newParams) != 0) {
            $query_string = "&" . implode("&", $newParams);
        }

    }

    /// DEBUG
    //echlog('warning', "QUERY_STRING " . filter_input(INPUT_SERVER, 'QUERY_STRING'));
    //echlog('warning', $query_string);
    return $query_string;
}

function linkSort($keyword, $title) {

    $this_p = cleanvar(filter_input(INPUT_SERVER, 'PHP_SELF'));

    echo '<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;
            <a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';

}

function linkSortBan($keyword, $title, $t) {

    $this_p = cleanvar(filter_input(INPUT_SERVER, 'PHP_SELF'));

    echo '<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC&amp;t='.$t.'"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;
            <a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC&amp;t='.$t.'"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';

}

function linkSortClients($keyword, $title, $is_search, $search_type, $search_string) {

    $this_p = cleanvar(filter_input(INPUT_SERVER, 'PHP_SELF'));

    if($is_search == false) :
        echo'<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;<a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';
    else:
        echo'<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC&amp;s='.urlencode($search_string).'&amp;t='.$search_type.'"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;<a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC&amp;s='.urlencode($search_string).'&amp;t='.$search_type.'"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';
    endif;

}

/**
 * Echo out simple map config details link
 */
function mapconfigLink($mapname, $id, $game_id = NULL) {
    $href = "";
    if(!empty($game_id)) {
        $href = '&amp;game='.$game_id;
    }

    return '<a href="mapconfigdetails.php?id='.$id.$href.'" title="Check out '.$mapname.' information">'.$mapname.'</a>';
}

function linkSortMaps($keyword, $title, $is_search, $search_type, $search_string) {

    $this_p = cleanvar(filter_input(INPUT_SERVER, 'PHP_SELF'));

    if($is_search == false) :
        echo'<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;
            <a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';
    else:
        echo'<a title="Sort information by '.$title.' ascending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=ASC&amp;s='.urlencode($search_string).'&amp;t='.$search_type.'"><img src="images/asc.png" width="10" height="6" alt="ASC" class="asc-img" /></a>
            &nbsp;
            <a title="Sort information by '.$title.' descending." href="'.$this_p.'?p=0&amp;ob='.$keyword.'&amp;o=DESC&amp;s='.urlencode($search_string).'&amp;t='.$search_type.'"><img src="images/desc.png" width="10" height="6" alt="DESC" class="desc-img" /></a>';
    endif;

}

/**
 * Removes color coding from a text string
 *
 * @param string $text - the text to clean
 * @return string - the cleaned text
 */
function removeColorCode($text) {
    $text = preg_replace('/\^[0-9]/', '', $text); #preg_replace('/\\^([0-9])/ie', '', $text);
    return $text;
}

/**
 * Cleans/Escapes data for use in tables
 *
 * @param string $text - the text to clean
 * @return string - the cleaned/escaped text
 */
function tableClean($text) {
    $text = htmlspecialchars($text);
    return $text;
}

function timeExpire($time_expire, $type, $inactive) {

    global $tformat;
    $time = time();

    if (($time_expire <= $time) && ($time_expire != -1)) {
        $msg = "<span class=\"p-expired\">".date($tformat, $time_expire)."</span>";

    } elseif ($time_expire == '-1') {
        $msg = "<span class=\"p-permanent\">Permanent</span>";

    } elseif ($time_expire > $time) {
        $msg = "<span class=\"p-active\">".date($tformat, $time_expire)."</span>";
    }

    if ($type == 'Kick') {
        $msg = "<em>(Kick Only)</em>";

    } elseif ($type == 'Notice'){
        $msg = "<span class=\"p-inactive\">Notice</span>";

    } elseif ($inactive == "1") {
        $msg = "<span class=\"p-inactive\">De-activated</span>";

    }

    if($msg == '') { // if we got nothing then return unknown
        $msg = '<em>(Unknwon)</em>';
    }

    return $msg;
}

function timeExpirePen($time_expire) {
    global $tformat;

    if (($time_expire <= time()) && ($time_expire != -1)) {
        $msg = "<span class=\"p-expired\">".date($tformat, $time_expire)."</span>"; 
    }

    if ($time_expire == -1) {
        $msg = "<span class=\"p-permanent\">Permanent</span>"; 
    }

    if ($time_expire > time()) {
        $msg = "<span class=\"p-active\">".date($tformat, $time_expire)."</span>"; 
    }

    return $msg;
}

/**
 * Get a penalty duration from your number and time frame
 *
 * @param string $time - time frame
 * @param int $duration - duration
 * @return int
 */
function penDuration($time, $duration) {

    if($time == 'h') { // if time is in hours
            $duration = $duration*60;
    }
    elseif($time == 'd') { // time in days
            $duration = $duration*60*24;
    }
    elseif($time == 'w') { // time in weeks
            $duration = $duration*60*24*7;
    }
    elseif($time == 'mn') { // time in months (lets just say 30 days to a month)
            $duration = $duration*60*24*30;
    }
    elseif($time == 'y') { // time in years
            $duration = $duration*60*24*365;
    }
    else { // default time to mintues
            $duration = $duration;
    }

    return $duration;

}

/**
 * Show a formatted version of a DB error
 */
function dbErrorShow($error) {
    echo '<h3>Database Error!</h3><p>'. $error .'</p>';
}

/**
 * Echelon logging function 
 */
function echLog($type, $message, $code = NULL, $traces = NULL) {

    if(empty($message)) {
        $message = 'There was an error of some sort';
    }

    // open the log file for appending
    if($f = @fopen(ECH_LOG,'a')) : // returns false on error
		
        switch($type) {
            case 'mysql':
                $type_msg = 'MYSQL ERROR';
                break;

            case 'mysqlconnect':
                $type_msg = 'MYSQL CONNECTION ERROR';
                break;

            case 'hack':
                $type_msg = 'HACK ATTEMPT';
                break;

            case 'warning':
                $type_msg = 'WARNING';
                break;

            case 'error':
            default:
                $type_msg = 'ERROR';
                break;
        }
		
        // construct the log message
        $log_msg = "-------\n".date("[Y-m-d H:i:s]") . $type_msg;

        if(isset($code) && !empty($code)) {
            $log_msg .=	" - Code: $code -" ;
        }

        $log_msg .=	" Message: $message\n";
        if(!empty($traces)) {
            $log_msg .= "#Trace: \n" . $traces. "\n";
        }

        // write the log message
        fwrite($f, $log_msg);

        // close the file connection
        fclose($f);

        return true;
    else:
        die('Couldn\'t find the Echelon Log at: '. ECH_LOG);

    endif;

}

/**
 * Send an email about a possible hack to the admin
 *
 * @param string $where - where the event happened
 */
function writeLog($where) {
    
    $ip = getRealIp(); // Get the IP from superglobal
    $host = gethostbyaddr($ip);    // Try to locate the host of the attack

    // create a logging message with php heredoc syntax
    $logging = <<<LOGMSGG
    There was a hacking attempt,.
    IP-Adress: {$ip}
    Host of Attacker: {$host}
    Point of Attack: {$where}
LOGMSGG;
// Awkward but LOG must be flush left
	
    // log the message
    echLog('hack', $logging); 
	
}

/**
 * Check if the supplied token is valid
 *
 * @param string $form - the form name
 * @param string $tokens - the server-side tokens array
 * @return bool
 */
function verifyFormToken($form, $tokens) {
        
    // check if a session is started and a token is transmitted, if not return an error
    if(!isset($tokens[$form])) {
        return false;
    }

    // check if the form is sent with token in it
    if(!isset($_POST['token'])) {
        return false;
    }

    // compare the tokens against each other if they are still the same
    if ($tokens[$form] !== $_POST['token']) {
        return false;
    }

    return true;
}

/**
 * Same as above function but slight change to account for some login form differences
 */
function verifyFormTokenLogin($form) {
        
    // check if a session is started and a token is transmitted, if not return an error
    if(!isset($_SESSION['tokens'][$form])) {
        return false;
    }

    // check if the form is sent with token in it
    if(!isset($_POST['token'])) {
        return false;
    }
        
    // compare the tokens against each other if they are still the same
    if ($_SESSION['tokens'][$form] !== $_POST['token']) {
        return false;
    }

    return true;
}

/**
 * Generate and set a form token
 *
 * @param string $form - the form name
 * @set session vars
 * @return bool
 */
function genFormToken($form) {
    
    // generate a token from an unique value, taken from microtime, you can also use salt-values, other crypting methods...
    $token = genHash(uniqid(microtime(), true));  

    // Write the generated token to the session variable to check it against the hidden field when the form is sent
    $_SESSION['tokens'][$form] = $token; 

    return $token;
}

/**
 * What to do if a bad token is found
 *
 * @param string $place - place this happened
 */
function ifTokenBad($place) {
    //hack(1); // plus 1 to hack counter
    writeLog($place.' - Bad Token'); // make note in log
    //sendBack('Hack Attempt Detected - If you continue you will be removed from this site');
    exit;
}

/**
 * Echos out all the different types of error/success/warning messages
 */
function errors() {
    $message = '';
    
    if (!isset($_SESSION['good'])) {
        $_SESSION['good'] = '';
    }
    if (!isset($_SESSION['error'])) {
        $_SESSION['error'] = '';
    }
    if (!isset($_SESSION['warning'])) {
        $_SESSION['warning'] = '';
    }
    
    if($_SESSION['good'] != '') {
        $message .= '<div class="alert alert-success alert-dismissible fade show m-2" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Success!</strong>  '.$_SESSION['good'].'</div>';
        $_SESSION['good'] = '';
    }
	
    if($_SESSION['error'] != '') {
        $message .= '<div class="alert alert-danger alert-dismissible fade show m-2" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Error!</strong> '.$_SESSION['error'].'</div>';
        $_SESSION['error'] = '';
    }
	
    if($_SESSION['warning'] != '') {
        $message .= '<div class="alert alert-warning alert-dismissible fade show m-2" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Warning!</strong> '.$_SESSION['warning'].'</div>';
        $_SESSION['warning'] = '';
    }
    
    echo $message;
}

/**
 * Detect an SSL connection
 *
 * @return bool
 */
function detectSSL(){
    if(filter_input(INPUT_SERVER, 'https') == "on") {
        return true;
    }
    elseif(filter_input(INPUT_SERVER, 'https') == 1) {
        return true;
    }
    elseif(filter_input(INPUT_SERVER, 'SERVER_PORT') == 443) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * A function for making time periods readable
 *
 * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
 * @param       int     number of seconds elapsed
 * @param       string  which time periods to display
 * @param       bool    whether to show zero time periods
 * @return 		string 	the human readable time
 */
function time_duration($seconds, $use = null, $zeros = false) {

    if($seconds == '') {
        return NULL;
    }

    // Define time periods
    $periods = array (
        'years'     => 31556926,
        'Months'    => 2629743,
        'weeks'     => 604800,
        'days'      => 86400,
        'hours'     => 3600,
        'minutes'   => 60,
        'seconds'   => 1
        );

    // Break into periods
    $seconds = (float) $seconds;
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }

    if ($segments) {
        // Build the string
        foreach ($segments as $key => $value) {
            $segment_name = substr($key, 0, -1);
            $segment = $value . ' ' . $segment_name;
            if ($value != 1) {
                $segment .= 's';
            }
            $array[] = $segment;
        }
    }

    if ($array) {
        $str = implode(', ', $array);
    }
    else {
        $str = "";
    }
    return $str;
}


/**
 * Read current version of Echelon from master server
 *
 * @return	string	contents of that page
 */
function getEchVer(){

    $c = @file_get_contents(VER_CHECK_URL);
    if(!$c) {
        return false;
    } else {
        $string = cleanvar($c);
        return $string;
    }
	
}

/**
 * Simple isPage() functions
 */
function isHome() {
    global $page;

    if($page == 'home') {
        return true;
    }
    else {
        return false;
    }
}

function isClients() {
    global $page;

    if($page == 'client') {
        return true;
    }
    else {
        return false;
    }
}

function isCD() {
    global $page;

    if($page == 'clientdetails') {
        return true;
    }
    else {
        return false;
    }
}

function isRegister() {
    global $page;

    if($page == 'register') {
        return true;
    }
    else {
        return false;
    }
}

function isLogin() {
    global $page;

    if($page == 'login') {
        return true;
    }
    else {
        return false;
    }
}

function isError() {
    global $page;

    if($page == 'error') {
        return true;
    }
    else {
        return false;
    }
}


function isSettings() {
    global $page;

    if($page == 'settings') {
        return true;
    }
    else {
        return false;
    }
}

function isSettingsGame() {
    global $page;

    if($page == 'settings-game') {
        return true;
    }
    else {
        return false;
    }
}

function isSettingsServer() {
    global $page;

    if($page == 'settings-server') {
        return true;
    }
    else {
        return false;
    }
}

function isSA() {
    global $page;

    if($page == 'sa') {
        return true;
    }
    else {
        return false;
    }
}

function isPerms() {
    global $page;

    if($page == 'perms') {
        return true;
    }
    else {
        return false;
    }
}

function isMe() {
    global $page;

    if($page == 'me') {
        return true;
    }
    else {
        return false;
    }
}

function isPubbans() {
    global $page;

    if($page == 'pubbans') {
        return true;
    }
    else {
        return false;
    }
}

function isMap() {
    global $page;

    if($page == 'map') {
        return true;
    }
    else {
        return false;
    }
}