<?php
$auth_name = 'edit_user';
$b3_conn = true; // this page needs to connect to the B3 database
require '../inc.php';

if(filter_input(INPUT_POST, 't') == 'del') : // delete user

    ## get and clean vars ##
    $token = cleanvar(filter_input(INPUT_POST, 'token'));
    $id = cleanvar(filter_input(INPUT_POST, 'id'));

    ## check numeric id ##
    if(!is_numeric($id)) {
        sendBack('Invalid data sent, request aborted');
    }

    # verify token #
    if(!verifyFormToken('del'.$id, $tokens)) {
        ifTokenBad('Delete Echelon User');
    }

    $result = $dbl->delUser($id);
    if($result) {
        sendGood('User has been deleted');
    }
    else {
        sendBack('There is a problem. The user has not been deleted');
    }

    exit;

elseif(filter_input(INPUT_POST, 'ad-edit-user')): // admin edit user

    ## get and clean vars ##
    $username = cleanvar(filter_input(INPUT_POST, 'username'));
    $display = cleanvar(filter_input(INPUT_POST, 'display'));
    $email = cleanvar(filter_input(INPUT_POST, 'email'));
    $group = cleanvar(filter_input(INPUT_POST, 'group'));
    $resetpassword = 'false';
    // it only comes through if it was checked, then the value is the "value" set on the checkbox
    if((filter_input(INPUT_POST, 'resetpassword'))) {
        $resetpassword = cleanvar(filter_input(INPUT_POST, 'resetpassword'));
    }
    $id = cleanvar(filter_input(INPUT_POST, 'id'));

    ## check numeric id ##
    if(!is_numeric($id)) {
        sendBack('Invalid data sent, request aborted');
    }

    # verify token #
    if(!verifyFormToken('adedituser', $tokens)) {
        ifTokenBad('Edit Echelon User');
    }

//echlog("debug", "User-Edit " . $id . ', ' . $username . ', ' . $display . ', ' . $email . ', ' . $group . ', ' . $resetpassword);
    $result = $dbl->editUser($id, $username, $display, $email, $group, $resetpassword);

    if($result) {
        sendGood($display."'s information has been updated.");
    }
    else {
        sendBack('There is a problem. The user information has not been changed.');
    }

    exit;
	        
else :
    set_error('You cannot view this page directly');
    send('sa.php');

endif;
