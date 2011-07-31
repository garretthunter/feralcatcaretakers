<?php // $Id: changehome.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $

modules_get_language();

function edithome() {
    $Default_Theme = pnConfigGetVar('Default_Theme');

    if (!pnUserLoggedIn()) {
        return;
    }

    include ("header.php");
    OpenTable();
    echo "<center><font class=\"pn-title\">"._HOMECONFIG."</font></center>";
    CloseTable();
    
    OpenTable();
    echo "<form action=\"user.php\" method=\"post\">"
    ."<font class=\"pn-normal\">"._NEWSINHOME." "._MAX127."</font> "
    ."<input type=\"text\" name=\"storynum\" size=\"3\" maxlength=\"3\" value=\"" . pnVarPrepForDisplay(pnUserGetVar('storynum')) . "\">"
    ."<br /><br />";
    if (pnUserGetVar('ublockon')==1) {
        $sel = " checked";
    } else {
        $sel = "";
    }
    echo "<input type=\"checkbox\" name=\"ublockon\"$sel>"
    ." <font class=\"pn-normal\">"._ACTIVATEPERSONAL."</font>"
    ."<br /><font class=\"pn-normal\">"._CHECKTHISOPTION."</font>"
    ."<br /><font class=\"pn-normal\">"._YOUCANUSEHTML."</font><br />"
    ."<textarea cols=\"55\" rows=\"5\" name=\"ublock\">" . pnVarPrepForDisplay(pnUserGetVar('ublock')) . "</textarea>"
    ."<br /><br />"
    ."<input type=\"hidden\" name=\"op\" value=\"savehome\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
    ."</form>";
    CloseTable();
    include ("footer.php");
}

function savehome()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($storynum,
         $ublockon,
         $ublock) = pnVarCleanFromInput('storynum',
                                        'ublockon',
                                        'ublock');

    if (!pnSecConfirmAuthKey()) {
        die('Attempt to directly update user information - denied');
        exit;
    }

    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');

        if (!empty($ublockon)) {
            $ublockon=1;
        } else {
            $ublockon=0;
        }
        $column = &$pntable['users_column'];
        $dbconn->Execute("UPDATE $pntable[users]
                          SET $column[storynum]='" . pnVarPrepForStore($storynum) . "',
                              $column[ublockon]='" . pnVarPrepForStore($ublockon) . "',
                              $column[ublock]='" . pnVarPrepForStore($ublock) . "'
                          WHERE $column[uid]=" . pnVarPrepForStore($uid));
        pnRedirect('user.php');
    }
}

switch($op) 
{
    case "edithome":
        edithome();
        break;
    case "savehome":
        savehome();
        break;
}

?>