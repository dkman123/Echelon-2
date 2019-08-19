<?php
$page = "reports";
$page_title = "Reports";
$auth_name = 'reports';
$b3_conn = true; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
$query_normal = true;
require 'inc.php';

##########################
######## Varibles ########

$reportArray = [ "select a value" => "",
    "Weapon Kills" => "weapon_kills",
    "Kill Shots" => "kill_shots",
    "Body Part Deaths" => "body_part_deaths",
    "Map Stats" => "map_stats",
    "Flag Actions" => "flag_actions"];
$report = "";


if (isset($_GET['report'])) {
    $report = ($_GET['report']);
}


###########################
######### QUERIES #########

$query_limit = "";
            
switch ($report) {

    case "weapon_kills":
        $query_limit = "select ws.kills, ws.teamkills, ws.suicides, iw.description, iw.id as weapon_id from xlr_weaponstats ws inner join iso_weapon iw on iw.id = ws.name order by ws.kills desc, ws.name;";
        break;

    case "kill_shots": // weapons that killed a player (should store kills as well)
        $query_limit = "select c.name, c.connections, wu.kills, wu.deaths, wu.kills / wu.deaths AS kdratio, wu.teamkills, wu.teamdeaths, wu.suicides, iw.description, wu.weapon_id from xlr_weaponusage wu inner join xlr_weaponstats ws on ws.id = wu.weapon_id inner join iso_weapon iw on iw.id = ws.name inner join clients c on c.id = wu.player_id order by 5 desc, c.name limit 100;";
        break;

    case "body_part_deaths": // body part deaths (kill shots only, not hits)
        $query_limit = "select ib.description, c.name, c.connections, pb.kills, pb.deaths, pb.teamkills, pb.teamdeaths, pb.suicides from xlr_playerbody pb inner join xlr_bodyparts bp on bp.id = pb.bodypart_id inner join iso_body ib on ib.id = bp.name inner join clients c on c.id = pb.player_id order by pb.kills desc, c.name, ib.description limit 100;";
        break;

    case "map_stats":
        $query_limit = "select m.name, m.rounds, m.kills, m.teamkills, m.suicides, round(m.kills / m.rounds, 2) as kills_per_round, round(m.teamkills / m.rounds, 2) as teamkills_per_round, round(m.suicides / m.rounds, 2) as suicides_per_round from xlr_mapstats m order by 6, m.name;";
        break;
    
    case "flag_actions":
        $query_limit = "select c.name, c.connections, g.count as flag_grabbed, d.count as flag_dropped, s.count as flag_captured, round(s.count / g.count, 2) as capture_per_grab from clients c left join ( select pa.player_id, sum(pa.count) as count from xlr_playeractions pa inner join xlr_actionstats a on a.id = pa.action_id where a.name in ('team_CTF_redflag', 'team_CTF_blueflag') group by pa.player_id ) as g on g.player_id = c.id left join ( select pa.player_id, sum(pa.count) as count from xlr_playeractions pa inner join xlr_actionstats a on a.id = pa.action_id where a.name in ('flag_dropped') group by pa.player_id ) as d on d.player_id = c.id left join ( select pa.player_id, sum(pa.count) as count from xlr_playeractions pa inner join xlr_actionstats a on a.id = pa.action_id where a.name in ('flag_captured') group by pa.player_id ) as s on s.player_id = c.id where g.count > 1 order by 6 desc, s.count desc, c.name limit 100;";
        break;
    
    case "":
    default:
        $query_limit = "select 1=1;";     
}//switch

## Require Header ##	
require 'inc/header.php';

if(!$db->error) :
?>

<div class="container my-2" style="max-width:95%">
<div class="card">
<div class="card-header">
    <h5 class="my-auto">Reports
    <small class="my-1 float-sm-right"><?php echo $game_name; ?></small>
    </h5>
</div>
    <div class="card-body table table-hover table-sm table-responsive">
        
    <form id="listmgmtform" method="get" action="reports.php">
    
    <table width="100%">
	<tbody>
            <tr>
                <td>
                    <select id="report" name="report">
                        <?php

                            foreach($reportArray as $key => $value):
                                if ($report === $value) { $selected = "selected"; }
                                else { $selected = ""; }
                                echo '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
                            endforeach;
                        ?>
                    </select>
                </td>
                <td>
                    <button id="runreport" type="submit">Run Report</button>
                    &nbsp; &nbsp;
                </td>
            </tr>
        </tbody>
    </table>
    </form>
        
    <table width="100%">
	<thead>
            <tr>
                <?php 

                switch ($report) {

                    case "weapon_kills":
                        printf("<th>%s</th>", "kills");
                        printf("<th>%s</th>", "teamkills");
                        printf("<th>%s</th>", "suicides");
                        printf("<th>%s</th>", "description");
                        printf("<th>%s</th>", "weapon_id");
                        break;

                    case "kill_shots":
                        printf("<th>%s</th>", "name");
                        printf("<th>%s</th>", "connections");
                        printf("<th>%s</th>", "kills");
                        printf("<th>%s</th>", "deaths");
                        printf("<th>%s</th>", "kdratio");
                        printf("<th>%s</th>", "teamkills");
                        printf("<th>%s</th>", "teamdeaths");
                        printf("<th>%s</th>", "suicides");
                        printf("<th>%s</th>", "description");
                        printf("<th>%s</th>", "weapon_id");
                        break;

                    case "body_part_deaths":
                        printf("<th>%s</th>", "description");
                        printf("<th>%s</th>", "name");
                        printf("<th>%s</th>", "connections");
                        printf("<th>%s</th>", "kills");
                        printf("<th>%s</th>", "deaths");
                        printf("<th>%s</th>", "teamkills");
                        printf("<th>%s</th>", "teamdeaths");
                        printf("<th>%s</th>", "suicides");
                        break;

                    case "map_stats":
                        printf("<th>%s</th>", "name");
                        printf("<th>%s</th>", "rounds");
                        printf("<th>%s</th>", "kills");
                        printf("<th>%s</th>", "teamkills");
                        printf("<th>%s</th>", "suicides");
                        printf("<th>%s</th>", "kills_per_round");
                        printf("<th>%s</th>", "teamkills_per_round");
                        printf("<th>%s</th>", "suicides_per_round");
                        break;

                    case "flag_actions":
                        printf("<th>%s</th>", "name");
                        printf("<th>%s</th>", "connections");
                        printf("<th>%s</th>", "flag_grabbed");
                        printf("<th>%s</th>", "flag_dropped");
                        printf("<th>%s</th>", "flag_captured");
                        printf("<th>%s</th>", "capture_per_grab");
                        break;

                    case "":
                    default:
                        printf("<th>%s</th>", "&nbsp;");   
                }//switch
                ?>
		</tr>
	</thead>
	<tbody>
            
        <?php 

	if($num_rows > 0) : // query contains stuff
                switch ($report) {

                    case "weapon_kills":
                        foreach($data_set as $row): // get data from query and loop
                            $kills = $row['kills'];
                            $teamkills = $row['teamkills'];
                            $suicides = $row['suicides'];
                            $description = $row['description'];
                            $weapon_id = $row['weapon_id'];

                            $alter = alter();

                            // setup heredoc (table data)			
                            $data = <<<EOD
                            <tr class="$alter">
                            <td>$kills</td>
                            <td>$teamkills</td>
                            <td>$suicides</td>
                            <td>$description</td>
                            <td>$weapon_id</td>
                            </tr>
EOD;

                        echo $data;
                        endforeach;
                        break;

                    case "kill_shots":
                        foreach($data_set as $row): // get data from query and loop
                            $name = $row['name'];
                            $connections = $row['connections'];
                            $kills = $row['kills'];
                            $deaths = $row['deaths'];
                            $kdratio = $row['kdratio'];
                            $teamkills = $row['teamkills'];
                            $teamdeaths = $row['teamdeaths'];
                            $suicides = $row['suicides'];
                            $description = $row['description'];
                            $weapon_id = $row['weapon_id'];

                            $alter = alter();

                            // setup heredoc (table data)			
                            $data = <<<EOD
                            <tr class="$alter">
                            <td>$name</td>
                            <td>$connections</td>
                            <td>$kills</td>
                            <td>$deaths</td>
                            <td>$kdratio</td>
                            <td>$teamkills</td>
                            <td>$teamdeaths</td>
                            <td>$suicides</td>
                            <td>$description</td>
                            <td>$weapon_id</td>
                            </tr>
EOD;

                        echo $data;
                        endforeach;
                        break;

                    case "body_part_deaths":
                        foreach($data_set as $row): // get data from query and loop
                            $description = $row['description'];
                            $name = $row['name'];
                            $connections = $row['connections'];
                            $kills = $row['kills'];
                            $deaths = $row['deaths'];
                            $teamkills = $row['teamkills'];
                            $teamdeaths = $row['teamdeaths'];
                            $suicides = $row['suicides'];

                            $alter = alter();

                            // setup heredoc (table data)			
                            $data = <<<EOD
                            <tr class="$alter">
                            <td>$description</td>
                            <td>$name</td>
                            <td>$connections</td>
                            <td>$kills</td>
                            <td>$deaths</td>
                            <td>$teamkills</td>
                            <td>$teamdeaths</td>
                            <td>$suicides</td>
                            </tr>
EOD;

                        echo $data;
                        endforeach;
                        break;

                    case "map_stats":
                        foreach($data_set as $row): // get data from query and loop
                            $name = $row['name'];
                            $rounds = $row['rounds'];
                            $kills = $row['kills'];
                            $teamkills = $row['teamkills'];
                            $suicides = $row['suicides'];
                            $kills_per_round = $row['kills_per_round'];
                            $teamkills_per_round = $row['teamkills_per_round'];
                            $suicides_per_round = $row['suicides_per_round'];

                            $alter = alter();

                            // setup heredoc (table data)			
                            $data = <<<EOD
                            <tr class="$alter">
                            <td>$name</td>
                            <td>$rounds</td>
                            <td>$kills</td>
                            <td>$teamkills</td>
                            <td>$suicides</td>
                            <td>$kills_per_round</td>
                            <td>$teamkills_per_round</td>
                            <td>$suicides_per_round</td>
                            </tr>
EOD;
                 
                        echo $data;
                        endforeach;
                        break;

                    case "flag_actions":
                        foreach($data_set as $row): // get data from query and loop
                            $name = $row['name'];
                            $connections = $row['connections'];
                            $flag_grabbed = $row['flag_grabbed'];
                            $flag_dropped = $row['flag_dropped'];
                            $flag_captured = $row['flag_captured'];
                            $capture_per_grab = $row['capture_per_grab'];

                            $alter = alter();

                            // setup heredoc (table data)			
                            $data = <<<EOD
                            <tr class="$alter">
                            <td>$name</td>
                            <td>$connections</td>
                            <td>$flag_grabbed</td>
                            <td>$flag_dropped</td>
                            <td>$flag_captured</td>
                            <td>$capture_per_grab</td>
                            </tr>
EOD;

                        echo $data;
                        endforeach;
                        break;

                    case "":
                    default:
                        printf("<td>%s</td>", "&nbsp;");   
                }//switch
            else :
            $no_data = true;

            echo '<tr class="odd"><td>No data.</td></tr>';
            endif; // no records
        ?>
	
	</tbody>
    </table>
    

</div></div></div>

<?php
	else:
		
	endif; // db error

	require 'inc/footer.php'; 
?>