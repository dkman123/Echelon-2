<?php
$page = "editban";
$page_title = "Edit Ban";
$auth_name = 'edit_ban';
$b3_conn = true; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
require 'inc.php';

## Do Stuff ##
if(filter_input(INPUT_GET, 'penid')) {
    $penid = filter_input(INPUT_GET, 'penid');
}

if(!isID($penid)) :
    set_error('The ban id that you have supplied is invalid. Please supply a valid ban id.');
    send('bans.php');
    exit;
endif;
	
if($penid == '') {
    set_error('No ban record specified, please select one');
    send('bans.php');
}

## Get Client information ##
$query = "SELECT p.id, p.type, p.client_id, p.admin_id, p.duration, p.inactive, p.reason, p.data, FROM_UNIXTIME(p.time_edit), FROM_UNIXTIME(p.time_expire) FROM penalties p WHERE p.id = ? LIMIT 1";
$stmt = $db->mysql->prepare($query) or die('Database Error '. $db->mysql->error);
$stmt->bind_param('i', $penid);
$stmt->execute();
$stmt->bind_result($id, $type, $client_id, $admin_id, $duration, $inactive, $reason, $data, $time_edit, $time_expire);
$stmt->fetch();
$stmt->close();

## Do Stuff ##

require 'inc/header.php';
?>

<script type="text/javascript">

function doUpdate(){
    if ($("#is_pb_ban").val() === "1") {
        $("#pb").val("on");
        $("#type").val("Ban");
    }
    else {
        $("#pb").val("off");
        $("#type").val("TempBan");        
    }
    $("#eb-sub").val("edit");
    document.forms["editban"].submit();
}

function goBack() {
    window.history.back();
}

</script>

<div class="container">
<div class="card my-2">
    
    <form action="actions/b3/edit-ban.php" method="post" id="editban">
	<input type="hidden" name="pb" id="pb" value="off" />
	<input type="hidden" name="type" id="type" value="TempBan" />
        <!-- <input type="hidden" name="pb" id="pb" value="on" /> -->
        <!-- <input type="hidden" name="type" id="type" value="Ban" /> -->
	<input type="hidden" name="eb-sub" id="eb-sub" value="" />
	<input type="hidden" name="pen_id" id="pen_id" value="<?php echo $id; ?>" />
        
        <div class="card-header">
            <h5 class="my-auto">Edit Ban Information</h5>
        </div>
        <div class="card-body table table-hover table-sm table-responsive">
            <table width="100%">
                <tbody>
                    <tr>
                        <th>Is Permanent Ban</th>
                        <td>
                            <input type="number" name="is_pb_ban" id="is_pb_ban" value="<?php if ($type == "Ban") { echo "1"; } else { echo "0"; } ?>" maxlength="1" pattern="0|1" />
                        </td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                            <td>
                                <input type="text" name="reason" id="reason" value="<?php echo $reason; ?>" maxlength="50" />
                            </td>
                    </tr>
                    <tr>
                        <th>Time</th>
                        <td>
                            <select name="time" id="time">
                                <option value="m">Minutes</option>
                                <option value="h">Hours</option>
                                <option value="d">Days</option>
                                <option value="w">Weeks</option>
                                <option value="mn">Months</option>
                                <option value="y">Years</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>
                            <input type="text" name="duration" id="duration" value="<?php echo $duration; ?>" maxlength="5" />
                        </td>
                    </tr>
                    <tr>
                        <th>Inactive</th>
                        <td>
                            <input type="number" name="inactive" id="inactive" value="<?php echo $inactive; ?>" maxlength="1" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                    <tr>
                        <th>Time Edit</th>
                        <td>
                            <input type="text" name="time_edit" id="time_edit" value="<?php echo $time_edit; ?>" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                    <tr>
                        <th>Time Expire</th>
                        <td>
                            <input type="text" name="time_expire" value="<?php echo $time_expire; ?>" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                    <tr>
                        <th>Data</th>
                        <td>
                            <input type="text" name="data" value="<?php echo $data; ?>" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                    <tr>
                        <th>Client ID</th>
                        <td>
                            <input type="number" name="client_id" value="<?php echo $client_id; ?>" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                    <tr>
                        <th>Admin ID</th>
                        <td>
                            <input type="number" name="admin_id" value="<?php echo $admin_id; ?>" readonly="readonly" class="disabled" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    	        
        <table style="width:90%; margin:5px">
            <tr>
                <td style="text-align:right">
                    <button class="btn btn-primary my-2" type="button" onclick="goBack()">Go Back</button>
                    &nbsp; &nbsp; &nbsp; &nbsp;
                    <button id="update" class="btn btn-primary my-2" value="Update" type="button" onclick="doUpdate()">Update</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php
    // Close page off with the footer
    require 'inc/footer.php'; 
?>
