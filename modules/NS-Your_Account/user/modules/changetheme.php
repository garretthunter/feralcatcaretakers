<?php // $Id: changetheme.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $

modules_get_language();

function chgtheme()
{
   if (!pnUserLoggedIn()) {
        return;
    }

    if(pnConfigGetVar('theme_change') == 1){
        return;
    }
    
    $Default_Theme = pnConfigGetVar('Default_Theme');
    
    include ("header.php");
    OpenTable();
    echo "<center><font class=\"pn-title\">"._THEMESELECTION."</center></font>";
    CloseTable();

    OpenTable();
    echo "<center>"
        ."<form action=\"user.php\" method=\"post\">"
        ."<font class=\"pn-title\">"._SELECTTHEME."</font><br>"
        ."<select name=\"theme\">";
    $handle=opendir('themes');
    $themelist="";
    while ($file = readdir($handle)) {
        if ((!ereg("[.]",$file)) ) {
            if($file != "CVS") {
           $themelist .= "$file ";
            }
        }
    }
    closedir($handle);
// modif sebastien multi sites
    $cWhereIsPerso = WHERE_IS_PERSO;
    if ( !(empty($cWhereIsPerso)) )
        { 
        include("modules/NS-Multisites/chgtheme.inc.php"); 
        }
// fin modif sebastien multi sites
    $themelist = explode(" ", $themelist);
    sort($themelist);
    $usertheme = pnUserGetTheme();
    for ($i = 0; $i < sizeof($themelist); $i++) {
        if($themelist[$i] != "") {
            echo "<option value=\"" . pnVarPrepForDisplay($themelist[$i]) . "\" ";
            if ($themelist[$i] == $usertheme) {
                echo "selected";
            }
            echo ">" . pnVarPrepForDisplay($themelist[$i]) . "\n";
        }
    }
    echo "</select><br>"
        ."<font class=\"pn-normal\">"._THEMETEXT1."<br>"
        .""._THEMETEXT2."<br>"
        .""._THEMETEXT3."<br><br></font>"

        ."<input type=\"hidden\" name=\"op\" value=\"savetheme\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function savetheme()
{

    if (!pnSecConfirmAuthKey()) {
        die('Not allowed to directly update theme');
    }

    $theme = pnVarCleanFromInput('theme');

    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');

        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();
        $column = &$pntable['users_column'];
        $dbconn->Execute("UPDATE $pntable[users]
                          SET $column[theme]='" . pnVarPrepForStore($theme) . "'
                          WHERE $column[uid]=" . pnVarPrepForStore($uid));
        pnRedirect('user.php');
    }
}


switch ($op) {
        case "chgtheme":
                chgtheme();
                break;
        case "savetheme":
        savetheme();
                break;

}

?>