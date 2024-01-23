<?php
$page = "server_action";
$page_title = "Server Action";
$auth_name = 'server_action';
$b3_conn = false; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
$query_normal = false;
require 'inc.php';

##########################
######## Varibles ########

# from config.php
#$server_action_path = "SOMEPATH/";

$map = "";
if (filter_input(INPUT_POST, 'map')) {
    $map = (filter_input(INPUT_POST, 'map'));
}

## Require Header ##	
require 'inc/header.php';

?>

<div class="container my-2" style="max-width:95%">
<div class="card">
<div class="card-header">
    <h5 class="my-auto">Server Actions
    <small class="my-1 float-sm-right"><?php echo $game_name; ?></small>
    </h5>			
</div>
    
<div class="card-body table table-hover table-sm table-responsive">
    <form id="server_actionform" method="post" action="actions/server_action-edit.php">
    <input id="t" name="t" type="hidden" value="" />
    
    <table width="100%">
	<tbody>
            <tr>
                <td>
                    <button id="restartB3" type="button" onclick="doRestartB3()">Restart B3</button>
                </td>
                <td>
                    <button id="restartUrT" type="button" onclick="doRestartUrT()">Restart UrT</button>
                </td>
                <td>
                    <button id="restartTS3" type="button" onclick="doRestartTS3()">Restart TS3</button>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>
            <tr>
                <td>Remove Map from game directory</td>
                <td>Map Full Name</td>
                <td>
                    <input id="map" name="map" type="text" value="<?php echo $map ?>" maxlength="50" placeholder="ut4_mapname.pk3" />
                </td>
                <td>
                    <button id="remove" type="button" onclick="doRemoveMap()">Remove Map</button>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
    		
</div></div></div>

<script language="JavaScript">
    
function doRestartB3(){
    $("#t").val("restartb3");
    $("#server_actionform").submit();
}

function doRestartUrT(id){
    $("#t").val("restarturt");
    $("#server_actionform").submit();
}

function doRestartTS3(){
    $("#t").val("restartts3");
    $("#server_actionform").submit();
}

function doRemoveMap(){
    if ($("#map").val() === "") {
        alert("A map Name must be entered");
        return false;
    }
    $("#t").val("removemap");
    $("#server_actionform").submit();
}

</script>
<?php
    require 'inc/footer.php'; 
?>