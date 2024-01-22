<?php
if (!empty(filter_input(INPUT_SERVER, "SCRIPT_FILENAME")) && "config.php" == basename(filter_input(INPUT_SERVER, "SCRIPT_FILENAME"))) {
    die ("Please do not load this page directly. Thanks!"); // do not edit
}

##### Start Editing From below here #####
define("DB_CON_ERROR_SHOW", TRUE); // show DB connection error if any (values: TRUE/FALSE)
define("DB_B3_ERROR_ON", TRUE); // show detailed error messages on B3 DB query failure (values TRUE/FALSE)
#############
## General ##
$path = "/echelon/"; // path to echelon from root of web directory. include starting and trailing (eg. "/echelon/" )
define("PATH", $path);

## Connection info to connect to the database containing the echelon tables
define("DBL_HOSTNAME", "127.0.0.1"); // hostname of where the server is located
define("DBL_USERNAME", "echelon"); // username that can connect to that DB
define("DBL_PASSWORD", "ECHELON_PASSWORD"); // Password for that user
define("DBL_DB", "echelon"); // database name

#############################
///// IGNORE BELOW HERE /////
## Echelon Version ##
define("ECH_VER", "v2.3");

define("SALT", '14_CHAR_GIBBERISH'); // do not change ever, this is salt for hashes

$supported_games = array( // supported games
    "q3a" => "Quake 3 Arena", 
    "cod" => "Call of Duty", 
    "cod2" => "Call of Duty 2", 
    "cod4" => "Call of Duty 4 MW", 
    "cod5" => "Call of Duty: World at War", 
    "iourt41" => "Urban Terror", 
    "wop" => "World of Padman"
);

// URL to check for updates with
define("VER_CHECK_URL", "https://raw.githubusercontent.com/dkman123/Echelon-2/master/echelon/version.txt");

// Do not touch this varible
define("INSTALLED", 'yes');

// Do not touch this varible either
define("SES_SALT", '6CHAR_GIBBERISH');

// NOTE: the echlog file needs to be writable by the website user
$ech_log_path = getenv("DOCUMENT_ROOT").PATH."lib/log.txt";
define("ECH_LOG", $ech_log_path); // location of the Echelon Log file
unset($ech_log_path);

// ProxyCheck.io Key
define("PROXYCHECKioKEY", "HASH_KEY_HERE");


if(!isset($email_config)) {
    $email_config = array();
    $email_config['server_name'] = "Test Server NAME";          // for anti-abuse and message id
    $email_config['userid'] = 0;                                // for anti-abuse
    $email_config['username'] = "Test Server page";             // for anti-abuse header
    $email_config['userip'] = "SERVERIPADDRESS";                // for anti-abuse header
    $email_config['board_email'] = 'SOMETHING@mail.com';        // for return-path and sender
    $email_config['email_enable'] = true;                       // turn email on/off  (true/false)
    $email_config['board_contact_name'] = 'SOMETHING@mail.com'; // for reply-to and from
    $email_config['smtp_delivery'] = true;                      // must be true

    $email_config['smtp_host'] = 'smtp.gmail.com';              // the email server address as a string (such as 'smtp.gmail.com')
    $email_config['smtp_port'] = '587';                         // the email port (likely 587) as a string
    $email_config['smtp_username'] = 'USERNAME';                // the email login name
    $email_config['smtp_password'] = 'EMAIL_PASSWORD';                // the email password
    $email_config['smtp_auth_method'] = 'LOGIN';                // 'LOGIN'
    $email_config['smtp_verify_peer'] = false;                  // false
    $email_config['smtp_verify_peer_name'] = false;             // false
    $email_config['smtp_allow_self_signed'] = false;            // false;
    $email_config['host_ip'] = 'SERVER_IPADDRESS';              // the server's IP as a string
    
    $email_config['admin_email'] = "SOMETHING@mail.com";        // admin email for error/hack attempts
}

if(!isset($getipintel_email)) {
    $getipintel_email = "SOMETHING@mail.com";
}
