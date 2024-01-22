<?php
$auth_name = 'listmgmt';
require '../inc.php';

$lf = "\n";  /// NOTE: using unix LF of just \n.  Windows may need CRLF /r/n

$data = "";
if (!empty(filter_input(INPUT_POST, 'data'))) {
    //$data = (cleanvar(filter_input(INPUT_POST, 'data')));
    $data = (filter_input(INPUT_POST, 'data'));
}

$list = "";
if (!empty(filter_input(INPUT_POST, 'list'))) {
    //$list = (cleanvar(filter_input(INPUT_POST, 'list')));
    $list = (filter_input(INPUT_POST, 'list'));
}

$t = "";
if (!empty(filter_input(INPUT_POST, 't'))) {
    //$t = (cleanvar(filter_input(INPUT_POST, 't')));
    $t = (filter_input(INPUT_POST, 't'));
}

/// NOTE: these need to correspond to the names in the listmgmt.php file
$listArray = [
    "select a value" => "",
    "banlistvpn" => "../../echelonv1/files/banlistvpn.txt",
    "banlistrfa" => "../../echelonv1/files/banlistrfa.txt",
    "ipwhitelist" => "../../echelonv1/files/ipwhitelist.txt",
    "guidbanlist" => "../../echelonv1/files/guidbanlist.txt",
    "banlist" => "../../echelonv1/files/banlist.txt"
];

$file = $listArray[$list];
//echlog('warning', $file);

if ($list == "banlist" && substr($data, 3) !== ":-1") {
    $data = $data.":-1";
}

if ($data !== "" && $list !== "" && $t !== "") {
    if ($t === "S") {  // search
        try {
            $array = explode($lf, file_get_contents($file));
            $rec = 1;  // record count, aka row, line number
            // for each line
            foreach($array as $row){
                //// this method would catch 1.2.3.4 when the line was 1.2.3.40, so no good
                ////if (substr($row, 0, strlen($data)) === $data) {
                //
                //if (substr($row, 0, strlen($data)) === $data) {
                //    echlog('warning', $row . ' is like ' . $data);
                //}
                
                // separate off CIDR range value if present
                $rowip = explode("/", $row);
                //echlog('warning', $rowip[0]);
                if ($rowip[0] === $data) {
                    sendGood('The data was found: ' . $row . ' is record ' . $rec . ' in list ' . $list . ' of ' . strval(count($array)) . ' records.');
                }
                $rec++;
            }
            
            //echlog('warning', implode(";", $array));
            //echlog('warning', $array[0]);
            
            sendBack('The data was not found in ' . strval(count($array)) . ' records.');
        } catch (Exception $ex) {            
            sendBack('Error: ' . $ex->getMessage());
        }
    }
    elseif ($t === "A") {  // add
        try {
            $array = file_get_contents($file);
            // Append a new record to the file
            $array .= $data . $lf;
            // Write the contents back to the file
            file_put_contents($file, $array);
            sendGood('The data has been added.');
        } catch (Exception $ex) {            
            sendBack('Error: ' . $ex->getMessage());
        }
    }
    elseif ($t ==="D") {  // delete
        try {
            $array = explode($lf, file_get_contents($file));
            $found = false;
            $max = count($array);
            for($idx = 0; $idx < $max; $idx++){
                $rowip = explode("/", $array[$idx]);
                if ($rowip[0] === $data) {
                    $found = true;
                    unset($array[$idx]);
                }
            }            
            file_put_contents($file, implode($lf, $array));
            if ($found) {
                sendGood('The data has been deleted, now ' . strval(count($array)) . ' records.');
            }
            else {
                sendBack('The data has not been deleted from ' . strval(count($array)) . ' records.');
            }
        } catch (Exception $ex) {            
            sendBack('Error: ' . $ex->getMessage());
        }
    }
    
//    if (!file_put_contents($mapcycleFile, $data)) :
//        sendBack('There is a problem. The mapcycle.txt has not been written.');
//    endif;
}
else {
    sendBack('There is a problem. There was no data to work with.');  
}
        
?>
