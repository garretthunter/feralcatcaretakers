<?php
#   include 'modules/NS-User/user/database.php';
   include 'modules/NS-User/user/menu.php';
   include 'modules/NS-User/user/access.php';
#   include 'modules/NS-User/user/language.php';

function redirect_index($message, $url="index.php")
{
    $ThemeSel = pnConfigGetVar('Default_Theme');
    echo "<html><head><META HTTP-EQUIV=Refresh CONTENT=\"2; URL=$url\">\n";
    if (defined("_CHARSET") && _CHARSET != "") {
       echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset="._CHARSET."\">\n";
    }
    echo "<LINK REL=\"StyleSheet\" HREF=\"themes/$ThemeSel/style/styleNN.css\" TYPE=\"text/css\">\n";
    echo "<style type=\"text/css\">";
    echo "@import url(\"themes/$ThemeSel/style/style.css\"); ";
    echo "</style>\n";
    echo "</head><body bgcolor=#2F363F text=#DADEE3>\n";
    echo "<table align=center border=0 cellpadding=2 cellspacing=0><tr><td><font class=\"pn-title\">$message</font></td></tr></table></body></html>";

    if (function_exists('session_write_close')) {
        session_write_close();
    } else {
	    // Hack for old versions of PHP with bad session save
	    /* Credit to M.W.Herbert from Forum Post*/
        $sessvars = '';
        foreach ($GLOBALS as $k => $v) {
            if ((preg_match('/^PNSV/', $k)) && (isset($v))) {
            $sessvars .= "$k|" . serialize($v);
            }
        }
        pnSessionWrite(session_id(), $sessvars);
    }


}

function redirect_user()
{
    $ThemeSel = pnConfigGetVar('Default_Theme');
    echo "<html><head><META HTTP-EQUIV=Refresh CONTENT=\"2; URL=user.php\">\n";
    if (defined("_CHARSET") && _CHARSET != "") {
       echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset="._CHARSET."\">\n";
    }
    echo " <LINK REL=\"StyleSheet\" HREF=\"themes/$ThemeSel/style/style.css\" TYPE=\"text/css\">\n";
    echo "</head><body bgcolor=#2F363F text=#DADEE3>\n";
    echo "<table align=center border=0 cellpadding=2 "
         .'cellspacing=0><tr><td><font class=\"pn-title\">'._INFOCHANGED.'</font></td></tr></table></body></html>';
    if (function_exists('session_write_close')) {
        session_write_close();
    } else {
        // Hack for old versions of PHP with bad session save
        $sessvars = '';
        foreach ($GLOBALS as $k => $v) {
            if ((preg_match('/^PNSV/', $k)) &&
                (isset($v))) {
                $sessvars .= "$k|" . serialize($v);
            }
        }
        pnSessionWrite(session_id(), $sessvars);
    }
}

function docookie($setuid, $setuname, $setpass, $setstorynum, $setumode, $setuorder, $setthold, $setnoscore, $setublockon, $settheme, $setcommentmax)
{
    $info = base64_encode("$setuid:$setuname:$setpass:$setstorynum:$setumode:$setuorder:$setthold:$setnoscore:$setublockon:$settheme:$setcommentmax");
    setcookie("user","$info",time() + 15552000);
}

// for compatibility : use user_menu_add_option($url,$title,$image)
function usermenu($url,$title,$image)
{
    if (!ereg('/',$image)) $image = pnConfigGetVar('userimg')."/".$image;
        user_menu_add_option($url,$title,$image);
}

   function GraphicUser($help='')
   {
      //modified by Chris van de Steeg.. I guess this should display the menu
      //it didn't do that.
      //if ($help!='') user_menu_help($help,_ONLINEMANUAL);
      user_menu($help);
      //user_menu_detail(false);
      user_menu_draw();
   }

   function user_menu($help_file='')
   {
      $pntable = pnDBGetTables();

      user_menu_title('user.php',_THISISYOURPAGE);
      user_menu_graphic(pnConfigGetVar('usergraphic'));
      if ($help_file!='') user_menu_help($help_file,_ONLINEMANUAL);
      //include 'modules/NS-Modules/data.php';
      //foreach ($module_item as $k=>$item)
      //   user_menu_add_option('user.php?module='.$item['module'].'&op=main',$item['text'],$item['image']);
//    modules, old way
      $moddir = opendir('modules/');
      while ($modulename=readdir($moddir))
      {
         if (@is_dir($dir='modules/'.pnVarPrepForOS($modulename).'/user/links/'))
         {
            $linksdir = opendir("modules/".pnVarPrepForOS($modulename)."/user/links/");
            while ($func = readdir($linksdir))
            {
               if (eregi('^links.',$func))
               {
                  //modified by Chris van de Steeg to have $ModName available in the links file
                  //$menulist[$func] = "modules/$modulename/user/links";
                  $menulist[$func]["dir"] = 'modules/' . pnVarPrepForOS($modulename) . '/user/links';
                  $menulist[$func]["modname"] = $modulename;
                  //end mofication by Chris van de Steeg
               }
            }
            closedir($linksdir);
         }
      }
      closedir($moddir);
//    display
//gehSTART
	  if (empty($menulist)) { 
	    $menulist = array();
	  }
//gehEND	  
      ksort($menulist);
      foreach ($menulist as $k=>$v)
      {
         //modified by Chris van de Steeg to have $ModName available in the links file
         // $currMod = $GLOBALS["ModName"]; //moved a bit down by Andy Varganov
         $GLOBALS["ModName"] = $v["modname"];
         $currMod = $GLOBALS["ModName"];
         include $v['dir'] . '/' . pnVarPrepForOS($k);
         $GLOBALS["ModName"] = $currMod;
         //end mofication by Chris van de Steeg
      }
      user_menu_add_option('user.php?module=NS-User&op=logout', _LOGOUTEXIT, 'modules/NS-Your_Account/images/exit.gif');
   }

   function user_title($title)
   {
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>".pnVarPrepForDisplay($title)."</b></font></center>";
    CloseTable();
   }

   function user_submit($module,$op,$text)
   {
      echo  '<input type="hidden" NAME="module" value="'.$module.'">'."\n"
           .'<input type="hidden" NAME="op" value="'.$op.'">'."\n"
           .'<input type="submit" VALUE="'.$text.'">'."\n";
   }
?>