<?php // File: $Id: tools.php,v 1.3 2002/10/31 18:19:53 tanis Exp $ $Name:  $

include 'modules/NS-Admin/admin/database.php';
include 'modules/NS-Admin/admin/menu.php';

function GraphicAdmin($help='')
{
    if ($help!='') {
	menu_help($help,_ONLINEMANUAL);
    }
    menu_detail(false);
    menu_draw();
}

function admin_menu($help_file='')
{
    $pntable = pnDBGetTables();
    list($newsubs) = db_select_one_row("SELECT count(*) FROM $pntable[queue]");

    if(!pnSecAuthAction(0, "::", '::', ACCESS_EDIT)) {
	// suppress admin display - return to index.
	pnRedirect('index.php');
    } else {
        menu_title('admin.php',_ADMINMENU);
        menu_graphic(pnConfigGetVar('admingraphic'));

        if($help_file!='') {
	    menu_help($help_file,_ONLINEMANUAL);
        }
	$mods = pnModGetAdminMods();
	if($mods == false) { // there aren't admin modules
	    return;
	}
	foreach ($mods as $mod) {
            // Hack until the new news module comes into being
            // TODO - remove this at appropriate time
            if ($mod['name'] == 'AddStory') {
                $mod['name'] = 'Stories';
            }
	    if (pnSecAuthAction(0, "$mod[name]::", '::', ACCESS_EDIT)) {
		if(file_exists("modules/".pnVarPrepForOS($mod['directory'])."/pnadmin.php")) {
		    $file = "modules/".pnVarPrepForOS($mod['directory'])."/pnimages/admin.";
			if (file_exists($file.'gif'))
			    $imgfile = $file.'gif';
			elseif (file_exists($file.'jpg'))
			    $imgfile = $file.'jpg';
			elseif (file_exists($file.'png'))
			    $imgfile = $file.'png';
			else $imgfile = 'modules/NS-Admin/images/default.gif';

                    menu_add_option(pnModURL($mod['name'], 'admin'), $mod['displayname'], $imgfile);
                } else {
		    $file = "modules/".pnVarPrepForOS($mod['directory'])."/images/admin.";
		    if (file_exists($file.'gif'))
			$imgfile = $file.'gif';
		    elseif (file_exists($file.'jpg'))
			$imgfile = $file.'jpg';
		    elseif (file_exists($file.'png'))
			$imgfile = $file.'png';
		    else $imgfile = 'modules/NS-Admin/images/default.gif';

                menu_add_option("admin.php?module=$mod[directory]&op=main", $mod['displayname'], $imgfile);
                }
            }
        }
    }
}

function admin_title($title)
{
    OpenTable();
	echo "<center><font class=\"pn-title\"><b>".pnVarPrepForDisplay($title)."</b></font></center>";
    CloseTable();
}

function admin_submit($module,$op,$text)
{
    echo  '<input type="hidden" name="module" value="'.$module.'">'."\n"
         .'<input type="hidden" name="op" value="'.$op.'">'."\n"
         .'<input type="submit" value="'.$text.'">'."\n";
}
?>