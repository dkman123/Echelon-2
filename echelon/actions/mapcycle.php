<?php
$auth_name = 'mapconfig';
require '../inc.php';

/// TODO: move to config
#$mapcycleFile = "../../echelonv1/files/mapcycle.txt";

$data = "";
if (filter_input(INPUT_POST, 'data')) {
    $data = (filter_input(INPUT_POST, 'data'));
}

if ($data !== "") {
    $data = str_replace("&nbsp;", " ", $data);
    $data = str_replace("<br>", "\n", $data);
    $data = str_replace("<br />", "\n", $data);
    $data = str_replace("\\\"", "\"", $data);
    if (!file_put_contents($mapcycleFile, $data)) {
        sendBack('There is a problem. The mapcycle.txt has not been written.');
    }
}
else {
    sendBack('There is a problem. There was no data to write.');  
}
        
sendGood('The mapcycle.txt has been written.');

?>
