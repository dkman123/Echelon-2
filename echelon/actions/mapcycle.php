<?php
$page = "maps";
$page_title = "Map Config";
$auth_name = 'mapconfig';
$b3_conn = false; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
$query_normal = true;
require '../inc.php';

/// TODO: move to config (then moved to server settings in db)
#$mapcycleFile = "../../echelonv1/files/mapcycle.txt";

$servers = $dbl->getServers($game);
if(!empty($servers)) {
    $mapcycleFile = $servers[0]['mapcyclefile'];
}
#echlog("debug", "Mapcycle File " . $mapcycleFile . " for Game ID " . $game);

//$mapcycleFile = "";
//if (filter_input(INPUT_POST, 'mapcyclefile')) {
//    $mapcycleFile = (filter_input(INPUT_POST, 'mapcyclefile'));
//}
emptyInput($mapcycleFile, 'Map Cycle File in Server Settings');

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
            sendBack('There is a problem. The mapcycle.txt has not been written. (Write permission?)');
        }
}
else {
    sendBack('There is a problem. There was no data to write.');  
}
        
sendGood('The mapcycle.txt has been written.');

?>
