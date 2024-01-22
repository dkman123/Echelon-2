<?php
/**
*
* Credit goes to phpBB Forum Software.
*
* but I gutted it to dumb it down quite a bit
*
*/

require_once 'utf_tools.php';  // require pre-req for email

// ----------------------------------------------------------------- 

//// to make email work
//// add this at the bottom of your inc.php file
//// obvisouly set the values
//if(!isset($email_config)) {
//    $email_config = array();
//    $email_config['server_name'] = "development server";        // for anti-abuse and message id
//    $email_config['userid'] = 0;                                // for anti-abuse
//    $email_config['username'] = "development page";             // for anti-abuse header
//    $email_config['userip'] = "1.2.3.4";                        // for anti-abuse header
//    $email_config['board_email'] = 'email@address.com';         // for return-path and sender
//    $email_config['email_enable'] = true;                       // turn email on/off  (true/false)
//    $email_config['board_contact_name'] = 'email@address.com';  // for reply-to and from
//    $email_config['smtp_delivery'] = true;                      // must be true
//
//    $email_config['smtp_host'] = 'smtp.gmail.com';              // the email server address as a string (such as 'smtp.gmail.com')
//    $email_config['smtp_port'] = '587';                         // the email port (likely 587) as a string
//    $email_config['smtp_username'] = 'login_username';          // the email login name
//    $email_config['smtp_password'] = 'PASSWORD';                // the email password
//    $email_config['smtp_auth_method'] = 'LOGIN';                // 'LOGIN'
//    $email_config['smtp_verify_peer'] = false;                  // false
//    $email_config['smtp_verify_peer_name'] = false;             // false
//    $email_config['smtp_allow_self_signed'] = false;            // false;
//    $email_config['host_ip'] = 'xxx.yyy.zzz.111';                     // the server's IP as a string
//
//}

// ----------------------------------------------------------------- pieces needed from other function files

// Email Priority Settings
define('MAIL_LOW_PRIORITY', 4);
define('MAIL_NORMAL_PRIORITY', 3);
define('MAIL_HIGH_PRIORITY', 2);

/**
* Generates an alphanumeric random string of given length
*
* @param int $num_chars Length of random string, defaults to 8.
* This number should be less or equal than 64.
*
* @return string
*/
function gen_rand_string($num_chars = 8)
{
    $range = array_merge(range('A', 'Z'), range(0, 9));
    $size = count($range);

    $output = '';
    for ($i = 0; $i < $num_chars; $i++)
    {
        $rand = random_int(0, $size-1);
        $output .= $range[$rand];
    }

    return $output;
}

/**
* Return unique id
*/
function unique_id()
{
    return strtolower(gen_rand_string(16));
}

// -------------------------------------------------------------------

/**
* Messenger
*/
class messenger
{
    var $msg, $replyto, $from, $subject;
    var $addresses = array();
    var $extra_headers = array();

    var $mail_priority = MAIL_NORMAL_PRIORITY;
    //var $use_queue = true;
    var $email_config;

    //protected $template;

    /**
    * Constructor
    */
    function __construct($use_queue = true)
    {
        $this->subject = '';
    }

    function setconfigvalues($email_config)
    {        
        /// --- config values

//        $this->server_name = "development server";        // for anti-abuse and message id
//        $this->userid = 0;                                // for anti-abuse
//        $this->username = "development page";             // for anti-abuse header
//        $this->userip = "1.2.3.4";                        // for anti-abuse header
//        $this->board_email = 'email@address.com';         // for return-path and sender
//        $this->email_enable = true;                       // turn email on/off  (true/false)
//        $this->board_contact_name = 'email@address.com';  // for reply-to and from
//        $this->smtp_delivery = true;                      // must be true
//
//        $smtp_host = 'smtp.gmail.com';                    // the email server address as a string (such as 'smtp.gmail.com')
//        $smtp_port = '587';                               // the email port (likely 587) as a string
//        $smtp_username = 'login_username';                // the email login name
//        $smtp_password = 'PASSWORD';                      // the email password
//        $smtp_auth_method = 'LOGIN';                      // 'LOGIN'
//        $smtp_verify_peer = false;                        // false
//        $smtp_verify_peer_name = false;                   // false
//          $smtp_allow_self_signed = false;                  // false
//        $host_ip = 'xxx.yyy.zzz.111';                     // the server's IP as a string
           
        $this->email_config = $email_config;

        $this->server_name = $email_config['server_name'];  // for anti-abuse and message id
        $this->userid = $email_config['userid'];            // for anti-abuse
        $this->username = $email_config['username'];        // for anti-abuse header
        $this->userip = $email_config['userip'];            // for anti-abuse header
        $this->board_email = $email_config['board_email'];  // for return-path and sender
        $this->email_enable = filter_var($email_config['email_enable'], FILTER_VALIDATE_BOOLEAN);   // turn email on/off  (true/false)
        $this->board_contact_name = $email_config['board_contact_name'];                            // for reply-to and from
        $this->smtp_delivery = filter_var($email_config['smtp_delivery'], FILTER_VALIDATE_BOOLEAN); // must be true

        //$this->email_enable = false;
                
//        $file = '/var/www/html/echelonv1/files/error.txt';
//        $data = array ($this->email_enable, $this->server_name, $this->board_email, $this->smtp_delivery);
//        //array_push($data, );
//        file_put_contents($file, implode("\n", $data), FILE_APPEND);
//        file_put_contents($file, implode("\n", $email_config), FILE_APPEND);                
    }

    /**
    * Resets all the data (address, template file, etc etc) to default
    */
    function reset()
    {
        $this->addresses = $this->extra_headers = array();
        $this->msg = $this->replyto = $this->from = '';
        $this->mail_priority = MAIL_NORMAL_PRIORITY;

        //$this->setconfigvalues();
    }

    /**
    * Set addresses for to/im as available
    *
    * @param array $user User row
    */
    function set_addresses($user_email, $username = '')
    {
        $this->to($user_email, $username);
            
//	if (isset($user['user_email']) && $user['user_email'])
//	{
//          $this->to($user['user_email'], (isset($user['username']) ? $user['username'] : ''));
//	}

//	if (isset($user['user_jabber']) && $user['user_jabber'])
//	{
//          $this->im($user['user_jabber'], (isset($user['username']) ? $user['username'] : ''));
//      }
    }

    /**
    * Sets an email address to send to
    */
    function to($address, $realname = '')
    {
        if (!trim($address))
        {
            return;
        }

        $pos = isset($this->addresses['to']) ? count($this->addresses['to']) : 0;

        $this->addresses['to'][$pos]['email'] = trim($address);
        $this->addresses['to'][$pos]['name'] = '';

        // If empty sendmail_path on windows, PHP changes the to line
//	if (!$config['smtp_delivery'] && DIRECTORY_SEPARATOR == '\\')
//	{
//          $this->addresses['to'][$pos]['name'] = '';
//	}
//	else
//	{
//          $this->addresses['to'][$pos]['name'] = trim($realname);
//	}
    }

    /**
    * Sets an cc address to send to
    */
    function cc($address, $realname = '')
    {
        if (!trim($address))
        {
            return;
        }

        $pos = isset($this->addresses['cc']) ? count($this->addresses['cc']) : 0;
        $this->addresses['cc'][$pos]['email'] = trim($address);
        $this->addresses['cc'][$pos]['name'] = trim($realname);
    }

    /**
    * Set the reply to address
    */
    function replyto($address)
    {
        $this->replyto = trim($address);
    }

    /**
    * Set the from address
    */
    function from($address)
    {
        $this->from = trim($address);
    }

    /**
    * set up subject for mail
    */
    function subject($subject = '')
    {
        $this->subject = trim($subject);
    }

    /**
    * set up msg body for mail
    */
    function msg($msg = '')
    {
        $this->msg = trim($msg);
    }

    /**
    * set up extra mail headers
    */
    function headers($headers)
    {
        $this->extra_headers[] = trim($headers);
    }

    /**
    * Adds X-AntiAbuse headers
    *
    * @param \phpbb\config\config	$config		Config object
    * @param \phpbb\user			$user		User object
    * @return void
    */
    function anti_abuse_headers()
    {
        //params $config, $user
//      $this->headers('X-AntiAbuse: Board servername - ' . mail_encode($config['server_name']));
//      $this->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
//      $this->headers('X-AntiAbuse: Username - ' . mail_encode($user->data['username']));
//      $this->headers('X-AntiAbuse: User IP - ' . $user->ip);
            
        $this->headers('X-AntiAbuse: Board servername - ' . $this->server_name);
        $this->headers('X-AntiAbuse: User_id - ' . $this->userid);
        $this->headers('X-AntiAbuse: Username - ' . $this->username);
        $this->headers('X-AntiAbuse: User IP - ' . $this->userip);
    }

    /**
    * Set the email priority
    */
    function set_mail_priority($priority = MAIL_NORMAL_PRIORITY)
    {
        $this->mail_priority = $priority;
    }

    function send()
    {
        $subject = $this->subject;
        $message = trim($this->msg);
        unset($subject, $message);

        // Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
        $this->msg = str_replace("\r\n", "\n", $this->msg);

        $result = $this->msg_email();

        $this->reset();
        return $result;
    }

    /**
    * Generates a valid message id to be used in emails
    *
    * @return string message id
    */
    function generate_message_id()
    {
        $domain = $this->server_name;
        return md5(unique_id(time())) . '@' . $domain;
    }

    /**
    * Return email header
    */
    function build_header($to, $cc, $bcc)
    {
        // We could use keys here, but we won't do this for 3.0.x to retain backwards compatibility
        $headers = array();

        $headers[] = 'From: ' . $this->from;

        if ($cc)
        {
            $headers[] = 'Cc: ' . $cc;
        }

        if ($bcc)
        {
            $headers[] = 'Bcc: ' . $bcc;
        }

        $headers[] = 'Reply-To: ' . $this->replyto;
        $headers[] = 'Return-Path: <' . $this->board_email . '>';
        $headers[] = 'Sender: <' . $this->board_email . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Message-ID: <' . $this->generate_message_id() . '>';
        $headers[] = 'Date: ' . date('r', time());
        //$headers[] = 'Content-Type: text/plain; charset=UTF-8'; // format=flowed
        $headers[] = 'Content-Type: text/html; charset=UTF-8'; // html
        $headers[] = 'Content-Transfer-Encoding: 8bit'; // 7bit

        $headers[] = 'X-Priority: ' . $this->mail_priority;
        $headers[] = 'X-MSMail-Priority: ' . (($this->mail_priority == MAIL_LOW_PRIORITY) ? 'Low' : (($this->mail_priority == MAIL_NORMAL_PRIORITY) ? 'Normal' : 'High'));
        $headers[] = 'X-Mailer: phpBB3';
        $headers[] = 'X-MimeOLE: phpBB3';
        //$headers[] = 'X-phpBB-Origin: phpbb://' . str_replace(array('http://', 'https://'), array('', ''), generate_board_url());

        /**
        * Event to modify email header entries
        *
        * @event core.modify_email_headers
        * @var	array	headers	Array containing email header entries
        * @since 3.1.11-RC1
        */
        $vars = array('headers');
        //extract($phpbb_dispatcher->trigger_event('core.modify_email_headers', compact($vars)));

        if (count($this->extra_headers))
        {
            $headers = array_merge($headers, $this->extra_headers);
        }

        return $headers;
    }

    /**
    * Send out emails
    */
    function msg_email()
    {
        if (!$this->email_config || empty($this->email_enable))
        {
            return false;
        }

        // Addresses to send to?
        if (empty($this->addresses) || (empty($this->addresses['to']) && empty($this->addresses['cc']) && empty($this->addresses['bcc'])))
        {
            // Send was successful. ;)
            return true;
        }

        $use_queue = false;
//      if ($config['email_package_size'] && $this->use_queue)
//	{
//          if (empty($this->queue))
//          {
//              $this->queue = new queue();
//              $this->queue->init('email', $config['email_package_size']);
//          }
//          $use_queue = true;
//      }

        $contact_name = htmlspecialchars_decode($this->board_contact_name);
        $board_contact = (($contact_name !== '') ? '"' . mail_encode($contact_name) . '" ' : '') . '<' . $this->board_contact . '>';

        $break = false;
        $addresses = $this->addresses;
        $subject = $this->subject;
        $msg = $this->msg;
        /**
        * Event to send message via external transport
        *
        * @event core.notification_message_email
        * @var	bool	break		Flag indicating if the function return after hook
        * @var	array	addresses 	The message recipients
        * @var	string	subject		The message subject
        * @var	string	msg			The message text
        * @since 3.2.4-RC1
        */
        $vars = array(
            'break',
            'addresses',
            'subject',
            'msg',
        );
        //extract($phpbb_dispatcher->trigger_event('core.notification_message_email', compact($vars)));

//	if ($break)
//	{
//          return true;
//	}

        if (empty($this->replyto))
        {
            $this->replyto = $board_contact;
        }

        if (empty($this->from))
        {
            $this->from = $board_contact;
        }

        $encode_eol = ($this->smtp_delivery) ? "\r\n" : PHP_EOL;

        // Build to, cc and bcc strings
        $to = $cc = $bcc = '';
        foreach ($this->addresses as $type => $address_ary)
        {
            if ($type == 'im')
            {
                continue;
            }

            foreach ($address_ary as $which_ary)
            {
                ${$type} .= ((${$type} != '') ? ', ' : '') . (($which_ary['name'] != '') ? mail_encode($which_ary['name'], $encode_eol) . ' <' . $which_ary['email'] . '>' : $which_ary['email']);
            }
        }

        // Build header
        $headers = $this->build_header($to, $cc, $bcc);

//      $file = '/var/www/html/echelonv1/files/test3.txt';
//      $data = array ('test3', $to, $subject, $msg, $this->smtp_delivery, $use_queue);
//      ////array_push($data, );
//      file_put_contents($file, implode("\n", $data));
                        
        // Send message ...
        if (!$use_queue)
        {
            $mail_to = ($to == '') ? 'undisclosed-recipients:;' : $to;
            $err_msg = '';
            $result = smtpmail($this->addresses, mail_encode($this->subject), wordwrap(utf8_wordwrap($this->msg), 997, "\n", true), $err_msg, $this->email_config, $headers);

//          if ($this->smtp_delivery)
//          {
//		$result = smtpmail($this->addresses, mail_encode($this->subject), wordwrap(utf8_wordwrap($this->msg), 997, "\n", true), $err_msg, $this->email_config, $headers);
//          }
//          else
//          {
//		$result = phpbb_mail($mail_to, $this->subject, $this->msg, $headers, PHP_EOL, $err_msg);
//          }

            if (!$result)
            {
                //$this->error('EMAIL', $err_msg);

                // TO CAPTURE AN ERROR MESSAGE
                //$file = '/home/urt/EchelonEmailError.txt';
                //$data = array ('Error', $to, $subject, $msg, $this->smtp_delivery, $use_queue);
                //////array_push($data, );
                //file_put_contents($file, implode("\n", $data));
                return false;
            }
        }
        else
        {
            $this->queue->put('email', array(
                'to'		=> $to,
                'addresses'	=> $this->addresses,
                'subject'	=> $this->subject,
                'msg'		=> $this->msg,
                'headers'	=> $headers)
            );
        }

        return true;
    }
}

/**
* Replacement or substitute for PHP's mail command
*/
function smtpmail($addresses, $subject, $message, &$err_msg, $email_config, $headers = false)
{    
    // --- config values

    $smtp_host = $email_config['smtp_host'];               // the email server address as a string (such as 'smtp.gmail.com')
    $smtp_port = $email_config['smtp_port'];               // the email port (likely 587) as a string
    $smtp_username = $email_config['smtp_username'];       // the email login name
    $smtp_password = $email_config['smtp_password'];       // the email password
    $smtp_auth_method = $email_config['smtp_auth_method']; // 'LOGIN'
    $board_email = $email_config['board_email'];           // for mail-from
    $smtp_verify_peer = $email_config['smtp_verify_peer']; // false
    $smtp_verify_peer_name = $email_config['smtp_verify_peer_name'];   // false
    $smtp_allow_self_signed = $email_config['smtp_allow_self_signed']; // false

//        $file = '/var/www/html/echelonv1/files/test4.txt';
//        $data = array ('test4', $smtp_host, $smtp_port, $smtp_username, $board_email);
//        ////array_push($data, );
//        file_put_contents($file, implode("\n", $data));
    // ---

    // Fix any bare linefeeds in the message to make it RFC821 Compliant.
    $message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

    if ($headers !== false)
    {
        if (!is_array($headers))
        {
            // Make sure there are no bare linefeeds in the headers
            $headers = preg_replace('#(?<!\r)\n#si', "\n", $headers);
            $headers = explode("\n", $headers);
        }

        // Ok this is rather confusing all things considered,
        // but we have to grab bcc and cc headers and treat them differently
        // Something we really didn't take into consideration originally
        $headers_used = array();

        foreach ($headers as $header)
        {
            if (strpos(strtolower($header), 'cc:') === 0 || strpos(strtolower($header), 'bcc:') === 0)
            {
                continue;
            }
            $headers_used[] = trim($header);
        }

        $headers = chop(implode("\r\n", $headers_used));
    }

    if (trim($subject) == '')
    {
        $err_msg = 'No email subject specified';
        return false;
    }

    if (trim($message) == '')
    {
        $err_msg = 'Email message was blank';
        return false;
    }

    $mail_rcpt = $mail_to = $mail_cc = array();

    // Build correct addresses for RCPT TO command and the client side display (TO, CC)
    if (isset($addresses['to']) && count($addresses['to']))
    {
        foreach ($addresses['to'] as $which_ary)
        {
            $mail_to[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
            $mail_rcpt['to'][] = '<' . trim($which_ary['email']) . '>';
        }
    }

    if (isset($addresses['bcc']) && count($addresses['bcc']))
    {
        foreach ($addresses['bcc'] as $which_ary)
        {
            $mail_rcpt['bcc'][] = '<' . trim($which_ary['email']) . '>';
        }
    }

    if (isset($addresses['cc']) && count($addresses['cc']))
    {
        foreach ($addresses['cc'] as $which_ary)
        {
            $mail_cc[] = ($which_ary['name'] != '') ? mail_encode(trim($which_ary['name'])) . ' <' . trim($which_ary['email']) . '>' : '<' . trim($which_ary['email']) . '>';
            $mail_rcpt['cc'][] = '<' . trim($which_ary['email']) . '>';
        }
    }

    $smtp = new smtp_class();

    $errno = 0;
    $errstr = '';

    $smtp->add_backtrace('Connecting to ' . $smtp_host . ':' . $smtp_port);

    // Ok we have error checked as much as we can to this point let's get on it already.
//	if (!class_exists('\phpbb\error_collector'))
//	{
//		global $phpbb_root_path, $phpEx;
//		include($phpbb_root_path . 'includes/error_collector.' . $phpEx);
//	}
//	$collector = new \phpbb\error_collector;
//	$collector->install();

    $options = array();
    $verify_peer = (bool) $smtp_verify_peer;
    $verify_peer_name = (bool) $smtp_verify_peer_name;
    $allow_self_signed = (bool) $smtp_allow_self_signed;
    $remote_socket = $smtp_host . ':' . $smtp_port;

    // Set ssl context options, see http://php.net/manual/en/context.ssl.php
    $options['ssl'] = array('verify_peer' => $verify_peer, 'verify_peer_name' => $verify_peer_name, 'allow_self_signed' => $allow_self_signed);
    $socket_context = stream_context_create($options);

    $smtp->socket = @stream_socket_client($remote_socket, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $socket_context);
    //$collector->uninstall();
    //$error_contents = $collector->format_errors();

    if ($errstr != null) {
        // can only write if this file exists and it has enough rights
        // IN CONSOLE:
        ////cd /var/www/html/echelonv1/files$ 
        //sudo touch error.txt
        //sudo chgrp www-data error.txt
        //chmod 664 error.txt

        $file = '/var/www/html/echelonv1/files/error.txt';
        $data = array ('Error sending email to', $addresses, 'Subject:', $subject, 'Message:', $message, 'Error No:', $errno, 'Error Str:', $errstr);
        //array_push($data, );
        file_put_contents($file, implode("\n", $data), FILE_APPEND);
    }

    if (!$smtp->socket)
    {
        if ($errstr)
        {
            $errstr = utf8_convert_message($errstr);
        }

        $err_msg = "Could not connect to smtp host : $errno : $errstr";
        $err_msg .= ($error_contents) ? '<br /><br />' . htmlspecialchars($error_contents) : '';
        return false;
    }

    // Wait for reply
    if ($err_msg = $smtp->server_parse('220', __LINE__))
    {
        $smtp->close_session($err_msg);
        return false;
    }

    // Let me in. This function handles the complete authentication process
    if ($err_msg = $smtp->log_into_server($smtp_host, $smtp_username, htmlspecialchars_decode($smtp_password), $smtp_auth_method, $email_config))
    {
        $smtp->close_session($err_msg);
        return false;
    }

    // From this point onward most server response codes should be 250
    // Specify who the mail is from....
    $smtp->server_send('MAIL FROM:<' . $board_email . '>');
    if ($err_msg = $smtp->server_parse('250', __LINE__))
    {
        $smtp->close_session($err_msg);
        return false;
    }

    // Specify each user to send to and build to header.
    $to_header = implode(', ', $mail_to);
    $cc_header = implode(', ', $mail_cc);

    // Now tell the MTA to send the Message to the following people... [TO, BCC, CC]
    $rcpt = false;
    foreach ($mail_rcpt as $type => $mail_to_addresses)
    {
        foreach ($mail_to_addresses as $mail_to_address)
        {
            // Add an additional bit of error checking to the To field.
            if (preg_match('#[^ ]+\@[^ ]+#', $mail_to_address))
            {
                $smtp->server_send("RCPT TO:$mail_to_address");
                if ($err_msg = $smtp->server_parse('250', __LINE__))
                {
                    // We continue... if users are not resolved we do not care
                    if ($smtp->numeric_response_code != 550)
                    {
                        $smtp->close_session($err_msg);
                        return false;
                    }
                }
                else
                {
                    $rcpt = true;
                }
            }
        }
    }

    // We try to send messages even if a few people do not seem to have valid email addresses, but if no one has, we have to exit here.
    if (!$rcpt)
    {
        $user->session_begin();
        $err_msg .= '<br /><br />';
        $err_msg .= '<strong>' . htmlspecialchars($mail_to_address) . '</strong> possibly an invalid email address?';
        $smtp->close_session($err_msg);
        return false;
    }

    // Ok now we tell the server we are ready to start sending data
    $smtp->server_send('DATA');

    // This is the last response code we look for until the end of the message.
    if ($err_msg = $smtp->server_parse('354', __LINE__))
    {
        $smtp->close_session($err_msg);
        return false;
    }

    // Send the Subject Line...
    $smtp->server_send("Subject: $subject");

    // Now the To Header.
    $to_header = ($to_header == '') ? 'undisclosed-recipients:;' : $to_header;
    $smtp->server_send("To: $to_header");

    // Now the CC Header.
    if ($cc_header != '')
    {
        $smtp->server_send("CC: $cc_header");
    }

    // Now any custom headers....
    if ($headers !== false)
    {
        $smtp->server_send("$headers\r\n");
    }

    // Ok now we are ready for the message...
    $smtp->server_send($message);

    // Ok the all the ingredients are mixed in let's cook this puppy...
    $smtp->server_send('.');
    if ($err_msg = $smtp->server_parse('250', __LINE__))
    {
        $smtp->close_session($err_msg);
        return false;
    }

    // Now tell the server we are done and close the socket...
    $smtp->server_send('QUIT');
    $smtp->close_session($err_msg);

    return true;
}

/**
* SMTP Class
* Auth Mechanisms originally taken from the AUTH Modules found within the PHP Extension and Application Repository (PEAR)
* See docs/AUTHORS for more details
*/
class smtp_class
{
    var $server_response = '';
    var $socket = 0;
    protected $socket_tls = false;
    var $responses = array();
    var $commands = array();
    var $numeric_response_code = 0;

    var $backtrace = false;
    var $backtrace_log = array();

    function __construct()
    {
        // Always create a backtrace for admins to identify SMTP problems
        $this->backtrace = true;
        $this->backtrace_log = array();
    }

    /**
    * Add backtrace message for debugging
    */
    function add_backtrace($message)
    {
        if ($this->backtrace)
        {
            $this->backtrace_log[] = utf8_htmlspecialchars($message);
        }
    }

    /**
    * Send command to smtp server
    */
    function server_send($command, $private_info = false)
    {
        fputs($this->socket, $command . "\r\n");

        (!$private_info) ? $this->add_backtrace("# $command") : $this->add_backtrace('# Omitting sensitive information');

        // We could put additional code here
    }

    /**
    * We use the line to give the support people an indication at which command the error occurred
    */
    function server_parse($response, $line)
    {
        $this->server_response = '';
        $this->responses = array();
        $this->numeric_response_code = 0;

        while (substr($this->server_response, 3, 1) != ' ')
        {
            if (!($this->server_response = fgets($this->socket, 256)))
            {
                return 'Could not get mail server response codes';
            }
            $this->responses[] = substr(rtrim($this->server_response), 4);
            $this->numeric_response_code = (int) substr($this->server_response, 0, 3);

            $this->add_backtrace("LINE: $line <- {$this->server_response}");
        }

        if (!(substr($this->server_response, 0, 3) == $response))
        {
            $this->numeric_response_code = (int) substr($this->server_response, 0, 3);
            return "Ran into problems sending Mail at <strong>Line $line</strong>. Response: $this->server_response";
        }

        return 0;
    }

    /**
    * Close session
    */
    function close_session(&$err_msg)
    {
        fclose($this->socket);

        if ($this->backtrace)
        {
            $message = '<h1>Backtrace</h1><p>' . implode('<br />', $this->backtrace_log) . '</p>';
            $err_msg .= $message;
        }
    }

    /**
    * Log into server and get possible auth codes if necessary
    */
    function log_into_server($hostname, $username, $password, $default_auth_method, $email_config)
    {
        /// --- config values

        $smtp_host = $email_config['smtp_host'];               // the email server address as a string (such as 'smtp.gmail.com')
        $smtp_port = $email_config['smtp_port'];               // the email port (likely 587) as a string
        $host_ip = $email_config['host_ip'];                   // the server's IP as a string

        // Here we try to determine the *real* hostname (reverse DNS entry preferrably)
        $local_host = $host_ip;

        if (function_exists('php_uname'))
        {
            $local_host = php_uname('n');

            // Able to resolve name to IP
            if (($addr = @gethostbyname($local_host)) !== $local_host)
            {
                // Able to resolve IP back to name
                if (($name = @gethostbyaddr($addr)) !== $addr)
                {
                    $local_host = $name;
                }
            }
        }

        // If we are authenticating through pop-before-smtp, we
        // have to login ones before we get authenticated
        // NOTE: on some configurations the time between an update of the auth database takes so
        // long that the first email send does not work. This is not a biggie on a live board (only
        // the install mail will most likely fail) - but on a dynamic ip connection this might produce
        // severe problems and is not fixable!
        if ($default_auth_method == 'POP-BEFORE-SMTP' && $username && $password)
        {
            $errno = 0;
            $errstr = '';

            $this->server_send("QUIT");
            fclose($this->socket);

            $this->pop_before_smtp($hostname, $username, $password);
            $username = $password = $default_auth_method = '';

            // We need to close the previous session, else the server is not
            // able to get our ip for matching...
            if (!$this->socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10))
            {
                if ($errstr)
                {
                    $errstr = utf8_convert_message($errstr);
                }

                $err_msg = "Could not connect to smtp host : $errno : $errstr";
                return $err_msg;
            }

            // Wait for reply
            if ($err_msg = $this->server_parse('220', __LINE__))
            {
                $this->close_session($err_msg);
                return $err_msg;
            }
        }

        $hello_result = $this->hello($local_host);
        if (!is_null($hello_result))
        {
            return $hello_result;
        }

        // SMTP STARTTLS (RFC 3207)
        if (!$this->socket_tls)
        {
            $this->socket_tls = $this->starttls();

            if ($this->socket_tls)
            {
                // Switched to TLS
                // RFC 3207: "The client MUST discard any knowledge obtained from the server, [...]"
                // So say hello again
                $hello_result = $this->hello($local_host);

                if (!is_null($hello_result))
                {
                    return $hello_result;
                }
            }
        }

        // If we are not authenticated yet, something might be wrong if no username and passwd passed
        if (!$username || !$password)
        {
            return false;
        }

        if (!isset($this->commands['AUTH']))
        {
            return 'SMTP server does not support authentication';
        }

        // Get best authentication method
        $available_methods = explode(' ', $this->commands['AUTH']);

        // Define the auth ordering if the default auth method was not found
        $auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5');
        $method = '';

        if (in_array($default_auth_method, $available_methods))
        {
            $method = $default_auth_method;
        }
        else
        {
            foreach ($auth_methods as $_method)
            {
                if (in_array($_method, $available_methods))
                {
                    $method = $_method;
                    break;
                }
            }
        }

        if (!$method)
        {
            return 'No supported authentication methods';
        }

        $method = strtolower(str_replace('-', '_', $method));
        return $this->$method($username, $password);
    }

    /**
    * SMTP EHLO/HELO
    *
    * @return mixed	Null if the authentication process is supposed to continue
    *			False if already authenticated
    *			Error message (string) otherwise
    */
    protected function hello($hostname)
    {
        // Try EHLO first
        $this->server_send("EHLO $hostname");
        if ($err_msg = $this->server_parse('250', __LINE__))
        {
            // a 503 response code means that we're already authenticated
            if ($this->numeric_response_code == 503)
            {
                return false;
            }

            // If EHLO fails, we try HELO
            $this->server_send("HELO $hostname");
            if ($err_msg = $this->server_parse('250', __LINE__))
            {
                return ($this->numeric_response_code == 503) ? false : $err_msg;
            }
        }

        foreach ($this->responses as $response)
        {
            $response = explode(' ', $response);
            $response_code = $response[0];
            unset($response[0]);
            $this->commands[$response_code] = implode(' ', $response);
        }
    }

    /**
    * SMTP STARTTLS (RFC 3207)
    *
    * @return bool	Returns true if TLS was started
    *			Otherwise false
    */
    protected function starttls()
    {
        if (!function_exists('stream_socket_enable_crypto'))
        {
            return false;
        }

        if (!isset($this->commands['STARTTLS']))
        {
            return false;
        }

        $this->server_send('STARTTLS');

        if ($err_msg = $this->server_parse('220', __LINE__))
        {
            return false;
        }

        $result = false;
        $stream_meta = stream_get_meta_data($this->socket);

        if (socket_set_blocking($this->socket, 1))
        {
            $result = stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            socket_set_blocking($this->socket, (int) $stream_meta['blocked']);
        }

        return $result;
    }

    /**
    * Pop before SMTP authentication
    */
    function pop_before_smtp($hostname, $username, $password)
    {
        if (!$this->socket = @fsockopen($hostname, 110, $errno, $errstr, 10))
        {
            if ($errstr)
            {
                $errstr = utf8_convert_message($errstr);
            }

            return "Could not connect to smtp host : $errno : $errstr";
        }

        $this->server_send("USER $username", true);
        if ($err_msg = $this->server_parse('+OK', __LINE__))
        {
            return $err_msg;
        }

        $this->server_send("PASS $password", true);
        if ($err_msg = $this->server_parse('+OK', __LINE__))
        {
            return $err_msg;
        }

        $this->server_send('QUIT');
        fclose($this->socket);

        return false;
    }

    /**
    * Plain authentication method
    */
    function plain($username, $password)
    {
        $this->server_send('AUTH PLAIN');
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return ($this->numeric_response_code == 503) ? false : $err_msg;
        }

        $base64_method_plain = base64_encode("\0" . $username . "\0" . $password);
        $this->server_send($base64_method_plain, true);
        if ($err_msg = $this->server_parse('235', __LINE__))
        {
            return $err_msg;
        }

        return false;
    }

    /**
    * Login authentication method
    */
    function login($username, $password)
    {
        $this->server_send('AUTH LOGIN');
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return ($this->numeric_response_code == 503) ? false : $err_msg;
        }

        $this->server_send(base64_encode($username), true);
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return $err_msg;
        }

        $this->server_send(base64_encode($password), true);
        if ($err_msg = $this->server_parse('235', __LINE__))
        {
            return $err_msg;
        }

        return false;
    }

    /**
    * cram_md5 authentication method
    */
    function cram_md5($username, $password)
    {
        $this->server_send('AUTH CRAM-MD5');
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return ($this->numeric_response_code == 503) ? false : $err_msg;
        }

        $md5_challenge = base64_decode($this->responses[0]);
        $password = (strlen($password) > 64) ? pack('H32', md5($password)) : ((strlen($password) < 64) ? str_pad($password, 64, chr(0)) : $password);
        $md5_digest = md5((substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64)) . (pack('H32', md5((substr($password, 0, 64) ^ str_repeat(chr(0x36), 64)) . $md5_challenge))));

        $base64_method_cram_md5 = base64_encode($username . ' ' . $md5_digest);

        $this->server_send($base64_method_cram_md5, true);
        if ($err_msg = $this->server_parse('235', __LINE__))
        {
            return $err_msg;
        }

        return false;
    }

    /**
    * digest_MD5 authentication method
    * A real pain in the ***
    */
    function digest_md5($username, $password, $email_config)
    {
        /// --- config values

        $smtp_host = $email_config['smtp_host'];               // the email server address as a string (such as 'smtp.gmail.com')

        $this->server_send('AUTH DIGEST-MD5');
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return ($this->numeric_response_code == 503) ? false : $err_msg;
        }

        $md5_challenge = base64_decode($this->responses[0]);

        // Parse the md5 challenge - from AUTH_SASL (PEAR)
        $dtokens = array();
        while (preg_match('/^([a-z-]+)=("[^"]+(?<!\\\)"|[^,]+)/i', $md5_challenge, $matches))
        {
            // Ignore these as per rfc2831
            if ($matches[1] == 'opaque' || $matches[1] == 'domain')
            {
                $md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
                continue;
            }

            // Allowed multiple "realm" and "auth-param"
            if (!empty($dtokens[$matches[1]]) && ($matches[1] == 'realm' || $matches[1] == 'auth-param'))
            {
                if (is_array($dtokens[$matches[1]]))
                {
                    $dtokens[$matches[1]][] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
                }
                else
                {
                    $dtokens[$matches[1]] = array($dtokens[$matches[1]], preg_replace('/^"(.*)"$/', '\\1', $matches[2]));
                }
            }
            else if (!empty($dtokens[$matches[1]])) // Any other multiple instance = failure
            {
                $dtokens = array();
                break;
            }
            else
            {
                $dtokens[$matches[1]] = preg_replace('/^"(.*)"$/', '\\1', $matches[2]);
            }

            // Remove the just parsed directive from the challenge
            $md5_challenge = substr($md5_challenge, strlen($matches[0]) + 1);
        }

        // Realm
        if (empty($dtokens['realm']))
        {
            $dtokens['realm'] = (function_exists('php_uname')) ? php_uname('n') : $user->host;
        }

        // Maxbuf
        if (empty($dtokens['maxbuf']))
        {
            $dtokens['maxbuf'] = 65536;
        }

        // Required: nonce, algorithm
        if (empty($dtokens['nonce']) || empty($dtokens['algorithm']))
        {
            $dtokens = array();
        }
        $md5_challenge = $dtokens;

        if (!empty($md5_challenge))
        {
            $str = '';
            for ($i = 0; $i < 32; $i++)
            {
                $str .= chr(mt_rand(0, 255));
            }
            $cnonce = base64_encode($str);

            $digest_uri = 'smtp/' . $smtp_host;

            $auth_1 = sprintf('%s:%s:%s', pack('H32', md5(sprintf('%s:%s:%s', $username, $md5_challenge['realm'], $password))), $md5_challenge['nonce'], $cnonce);
            $auth_2 = 'AUTHENTICATE:' . $digest_uri;
            $response_value = md5(sprintf('%s:%s:00000001:%s:auth:%s', md5($auth_1), $md5_challenge['nonce'], $cnonce, md5($auth_2)));

            $input_string = sprintf('username="%s",realm="%s",nonce="%s",cnonce="%s",nc="00000001",qop=auth,digest-uri="%s",response=%s,%d', $username, $md5_challenge['realm'], $md5_challenge['nonce'], $cnonce, $digest_uri, $response_value, $md5_challenge['maxbuf']);
        }
        else
        {
            return 'Invalid digest challenge';
        }

        $base64_method_digest_md5 = base64_encode($input_string);
        $this->server_send($base64_method_digest_md5, true);
        if ($err_msg = $this->server_parse('334', __LINE__))
        {
            return $err_msg;
        }

        $this->server_send(' ');
        if ($err_msg = $this->server_parse('235', __LINE__))
        {
            return $err_msg;
        }

        return false;
    }
}

/**
* Encodes the given string for proper display in UTF-8.
*
* This version is using base64 encoded data. The downside of this
* is if the mail client does not understand this encoding the user
* is basically doomed with an unreadable subject.
*
* Please note that this version fully supports RFC 2045 section 6.8.
*
* @param string $eol End of line we are using (optional to be backwards compatible)
*/
function mail_encode($str, $eol = "\r\n")
{
    // define start delimimter, end delimiter and spacer
    $start = "=?UTF-8?B?";
    $end = "?=";
    $delimiter = "$eol ";

    // Maximum length is 75. $split_length *must* be a multiple of 4, but <= 75 - strlen($start . $delimiter . $end)!!!
    $split_length = 60;
    $encoded_str = base64_encode($str);

    // If encoded string meets the limits, we just return with the correct data.
    if (strlen($encoded_str) <= $split_length)
    {
        return $start . $encoded_str . $end;
    }

    // If there is only ASCII data, we just return what we want, correctly splitting the lines.
    if (strlen($str) === utf8_strlen($str))
    {
        return $start . implode($end . $delimiter . $start, str_split($encoded_str, $split_length)) . $end;
    }

    // UTF-8 data, compose encoded lines
    $array = utf8_str_split($str);
    $str = '';

    while (count($array))
    {
        $text = '';

        while (count($array) && intval((strlen($text . $array[0]) + 2) / 3) << 2 <= $split_length)
        {
            $text .= array_shift($array);
        }

        $str .= $start . base64_encode($text) . $end . $delimiter;
    }

    return substr($str, 0, -strlen($delimiter));
}
