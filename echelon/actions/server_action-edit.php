<?php
$auth_name = 'server_action';
require '../inc.php';

# some default data to write to the "Restart" files
$map= "text";
if (!empty(filter_input(INPUT_POST, 'map'))) {
    $map = (cleanvar(filter_input(INPUT_POST, 'map')));
    //$map = (filter_input(INPUT_POST, 'map'));
}

$action = "";
if (!empty(filter_input(INPUT_POST, 't'))) {
    $action = (cleanvar(filter_input(INPUT_POST, 't')));
    //$action = (filter_input(INPUT_POST, 't'));
}

//echlog('warning', $variableName);

if ($action !== "") {
    $fname = "";
    if ($action === "restartb3") {
        $fname = $server_action_path . "restartb3.txt";
    } else if ($action === "restarturt") {
        $fname = $server_action_path . "restarturt.txt";
    } else if ($action === "restartts3") {
        $fname = $server_action_path . "restartts3.txt";
    } else if ($action === "removemap") {
        $fname = $server_action_path . "removemap.txt";
    }
    try {

        $file = fopen($fname, 'w'); //creates new file
        fwrite($file, $map);
        fclose($file);

        sendGood('The file was created. The job will pick it up.');
    } catch (Exception $ex) {            
        sendBack('Error: ' . $ex->getMessage());
    }
    
//    if (!file_put_contents($mapcycleFile, $data)) :
//        sendBack('There is a problem. The mapcycle.txt has not been written.');
//    endif;
}
else {
    sendBack('There is a problem. There was no action specified.');  
}
        
?>
