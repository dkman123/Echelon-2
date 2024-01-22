<?php
$page = "listmgmt";
$page_title = "List Management";
$auth_name = 'listmgmt';
$b3_conn = false; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
$query_normal = false;
require 'inc.php';

##########################
######## Varibles ########


/// NOTE: these need to correspond to the names in the listmgmt-edit.php file
$listArray = [
    "select a value" => "",
    "banlistvpn" => "banlistvpn",
    "banlistrfa" => "banlistrfa",
    "ipwhitelist" => "ipwhitelist",
    "guidbanlist" => "guidbanlist",
    "banlist" => "banlist"
];

$list = "";
//$list = "banlistrfa";
//$data = "192.255.30.0";

if (filter_input(INPUT_POST, 'list')) {
    $list = (filter_input(INPUT_POST, 'list'));
}

if (!isset($data)){
    $data = "";
}

## Require Header ##	
require 'inc/header.php';

?>

<div class="container my-2" style="max-width:95%">
<div class="card">
<div class="card-header">
    <h5 class="my-auto">Game Maps
    <small class="my-1 float-sm-right"><?php echo $game_name; ?></small>
    </h5>			
</div>
    
<div class="card-body table table-hover table-sm table-responsive">
    <form id="listmgmtform" method="post" action="actions/listmgmt-edit.php">
    <input id="t" name="t" type="hidden" value="" />
    
    <table width="100%">
	<tbody>
            <tr>
                <td>
                    <select id="list" name="list">
                        <?php

                            foreach($listArray as $key => $value):
                                if ($list === $value) { $selected = "selected"; }
                                else { $selected = ""; }
                                echo '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
                            endforeach;
                        ?>
                    </select>
                </td>
                <td>ip or guid</td>
                <td>
                    <input id="data" name="data" type="text" value="<?php echo $data ?>" maxlength="35" />
                </td>
                <td>
                    <button id="search" type="button" onclick="doSearch()">Search</button>
                    &nbsp; &nbsp;
                    <button id="add"  type="button" onclick="doAdd()">Add</button>
                    &nbsp; &nbsp;
                    <button id="remove"  type="button" onclick="doDelete()">Delete</button>
                </td>
            </tr>
            <tr>
                <td colspan="2">You can use this site to test a range</td>
                <td><a href="https://www.ipaddressguide.com/cidr" target="_blank">CIDR Test</a></td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    </form>
    		
</div></div></div>

<script language="JavaScript">
    
function doAdd(){
    $("#t").val("A");
    $("#listmgmtform").submit();
}

function doDelete(id){
    $("#t").val("D");
    $("#listmgmtform").submit();
}

function doSearch(){
    $("#t").val("S");
    $("#listmgmtform").submit();
}

</script>
<?php
    require 'inc/footer.php'; 
?>