<?php
$page = "settings-game";
$page_title = "Games Settings";
$auth_name = 'manage_settings';
$b3_conn = false; // this page needs to connect to the B3 database
$pagination = false; // this page requires the pagination part of the footer
require 'inc.php';

// if you get errors about chats_table_3 in /lib/plugins/chatlogs/class.php
// (where 3 is the game ID number)
// get into mysql and run:
//  insert into ech_config (name, value) select 'chats_table_3', 'chatlog';
//  insert into ech_config (name, value) select 'chats_names_3', 'ChatLog 3';

if (!isset($no_games)){
    $no_games = false;
}
if($no_games && filter_input(INPUT_GET, 't') != 'add') {
    send('settings-games.php?t=add');
}

if(filter_input(INPUT_GET, 't') == 'add') : // if add game type page

    $is_add = true;
    $add_game_token = genFormToken('addgame');

else : // if edit current game settings

    $is_add = false;
    // We are using the game information that was pulled in setup.php
    $game_token = genFormToken('gamesettings');

    if(filter_input(INPUT_GET, 'w') == 'game') {
        set_warning('You have changed game/DB since the last page!');
    }
		
endif;

require 'inc/header.php';

if($is_add) : ?>

<a href="settings-games.php" class="float-left">&laquo; Go Back</a>

<div class="container my-2">
<div class="card card-signin my-2">
<h5 class="card-header">Add Game</h5>
<div class="card-body">
    
    <form action="actions/settings-game.php" method="post">

        <h6>Names</h6>
        <div class="col justify-center">
            <div class="form-group row">
                <label class="col-sm-4 col-form-label" for="name">Full Name</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="name" id="name"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label" for="name-short">Short Name</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="name-short" id="name-short"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label" for="game-type">Game</label>
                <div class="col-sm-8">
                    <select class="form-control" name="game-type" id="game-type">
                        <?php
                            foreach($supported_games as $key => $value) :

                                echo '<option value="'.$key.'">'.$value.'</option>';

                            endforeach;
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <h6 class="my-4">B3 Database Information</h6>
        <div class="col justify-center">
            <div class="form-group row">                
                <label class="col-sm-4 col-form-label" for="db-host">Hostname</label>
                <div class="col-sm-8"><input type="text" class="form-control" name="db-host" id="db-host"></div>
            </div>
            <div class="form-group row">
                <label for="db-user" class="col-sm-4 col-form-label">User</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="db-user" id="db-user"></div>
            </div>        
            <div class="form-group row">
                <label for="db-name" class="col-sm-4 col-form-label">Database Name</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="db-name" id="db-name"></div>
            </div>        
            <div class="form-group row" id="change-pw-box">                
                <label class="col-sm-4 col-form-label" for="db-pw">DB Password</label>
                <div class="col-sm-8"><input class="form-control" type="password" name="db-pw" id="db-pw"></div>
            </div>
        </div>

        <hr>


        <input type="hidden" name="cng-pw" value="on" />
        <input type="hidden" name="type" value="add" />
        <input type="hidden" name="token" value="<?php echo $add_game_token; ?>" />
        <button class="btn btn-primary float-right" type="submit" name="game-settings-sub">Add Game</button>

    </form>
</div></div></div>
<?php else: ?>

<span class="float-left">
    <?php
    $this_cur_page = basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME'));
    $games_list = $dbl->getGamesList();
    $idx = 0;
    $count = count($games_list);
    $count--; // minus 1
    while($idx <= $count) :

        if($game == $games_list[$idx]['id']) {
            $selected = 'game-cur';
            $warning_game = NULL;
        } else {
            $selected = NULL;
            $warning_game = '&amp;w=game';
        }

        echo '<a href="'.PATH . $this_cur_page .'?game='. $games_list[$idx]['id'] . $warning_game .'" title="Switch to this game" class="'. $selected .'">'. $games_list[$idx]['name_short'] .'</a>';

        if($count != $idx) {
            echo ' - ';
        }

        $idx++;
    endwhile;
    ?>
</span>
<br />

<div class="container my-2">
<div class="card card-signin my-2">
<h5 class="card-header">Game Settings for <?php echo $game_name; ?>
<small><a href="settings-games.php?t=add" class="float-right" title="Add a Game (DB) to Echelon">Add Game &raquo;</a></small>
</h5>


<div class="card-body">

<form id="frm" action="actions/settings-game.php" method="post">
    
    <h6>Names</h6>
    <div class="col justify-center">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="name">Full Name</label>
            <div class="col-sm-8"><input class="form-control" type="text" name="name" id="name" value="<?php echo $game_name; ?>"></div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="name-short">Short Name</label>
            <div class="col-sm-8"><input class="form-control" type="text" name="name-short" id="name-short" value="<?php echo $game_name_short; ?>"></div>
        </div>
    </div>

    <h6 class="my-4">B3 Database Information</h6>
    <div class="col justify-center">
        <div class="form-group row">                
            <label class="col-sm-4 col-form-label" for="db-host">Hostname</label>
            <div class="col-sm-8"><input type="text" class="form-control" name="db-host" id="db-host" value="<?php echo $game_db_host; ?>"></div>
        </div>
        <div class="form-group row">
            <label for="db-user" class="col-sm-4 col-form-label">User</label>
            <div class="col-sm-8"><input class="form-control" type="text" name="db-user" id="db-user"value="<?php echo $game_db_user; ?>"></div>
        </div>        
        <div class="form-group row">
            <label for="db-name" class="col-sm-4 col-form-label">Database Name</label>
            <div class="col-sm-8"><input class="form-control" type="text" name="db-name" id="db-name" value="<?php echo $game_db_name; ?>"></div>
        </div>        
            <div class="form-group row">
            <label class="col-sm-4" for="cng-pw">Change Database Password?</label>

            <div class="col">
            <label class="my-1 switch">
                <input type="checkbox" name="cng-pw" id="cng-pw">
                <span class="slider round"></span>
            </label>
            </div>
        </div>  

        <div class="form-group row" id="change-pw-box">                
            <label class="col-sm-4 col-form-label" for="db-pw">DB Password</label>
            <div class="col-sm-8"><input class="form-control" type="password" name="db-pw" id="db-pw" value="<?php echo $game_db_pw; ?>"></div>
        </div>
    </div>

    <h6 class="my-4">Echelon Plugins</h6>
    <div class="col justify-center">
        <?php
            $plugins_enabled = $config['game']['plugins'];

            foreach(glob(getenv("DOCUMENT_ROOT").PATH.'lib/plugins/*') as $name) :

                $name = basename($name);
                $check = '';
                
                if(!empty($plugins_enabled)) {
                        if(in_array($name, $plugins_enabled)) {
                            $check = 'checked="checked" ';
                        }
                }
                else {
                    ## we need this now because it is not in the inc because of no active plugins
                    require_once 'classes/plugins-class.php'; // require the plugins base class
                }

                $file = getenv("DOCUMENT_ROOT").PATH.'lib/plugins/'.$name.'/class.php'; // abolsute path - needed because this page is include in all levels of this site
                if(file_exists($file)) {
                    include_once $file;
                    $plugin = call_user_func(array($name, 'getInstance'), 'name');
                    $title = $plugin->getTitle();
                } else {
                    $title = $name;
                }

                echo '<div class="form-group row">'
                    . '<label class="col-sm-4" for="'. $name .'">'. $title .'</label>'
                    . '<div class="col">'
                    . '<label class="my-1 switch">'
                    . '<input id="'. $name .'" type="checkbox" name="plugins" value="'. $name .'" '. $check .'/>'
                    . '<span class="slider round"></span>'
                    . '</label></div></div>';	

            endforeach; 
        ?>

    </div>
    <hr>
    <h6 class="my-4">Verify Yourself</h6>
    <div class="col justify-center">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="verify-pw">Current Password</label>
            <div class="col-sm-8"><input class="form-control" type="password" name="password" id="verify-pw"></div>
        </div>
    </div>                


    <input type="hidden" name="type" value="edit" />
    <input type="hidden" name="token" value="<?php echo $game_token; ?>" />
    <input type="hidden" name="game" value="<?php echo $game; ?>" />
    <input type="hidden" id="active-plugins" name="active-plugins" value="" />
    <input type="hidden" name="game-settings-sub" value="game-settings-sub" />
    <button class="btn btn-primary float-right" type="button" onclick="doGo()" name="game-settings-sub" value="Game Settings">Save Settings</button>

</form>
</div></div></div>     

<script type="text/javascript">
    function doGo() {
        var str = "";
        $("input[name*='plugins']").each(function(){
            if ($(this).is(':checked')) {
                str = str + "," + $(this).val();
            }
        });
        if (str === "") {
            str = ",";
        }
        $("#active-plugins").val(str.substr(1));
        console.log($("#active-plugins").val());
        $("#frm").submit();
    }
</script>

<?php endif;

    require 'inc/footer.php'; 
?>