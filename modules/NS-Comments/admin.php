<?PHP
// File: $Id: admin.php,v 1.4 2002/11/25 18:52:10 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

if (pnSecAuthAction(0, 'Comments::', '::', ACCESS_ADMIN)) {
modules_get_language();
modules_get_manual();

/**
 * Comments Delete Function
 */

// Thanks to Oleg [Dark Pastor] Martos from http://www.rolemancer.ru
// to code the comments childs deletion function!

function removeSubComments($tid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $count = 0;
    $column = &$pntable['comments_column'];
    $result = $dbconn->Execute("SELECT $column[tid]
                                FROM $pntable[comments] WHERE $column[pid]='".pnVarPrepForStore($tid)."'");

    while (list($stid) = $result->fields) {
        $result->MoveNext();
        $count += removeSubComments($stid);
    }

    $dbconn->Execute("DELETE FROM $pntable[comments]
                      WHERE {$pntable['comments_column']['tid']}=".pnVarPrepForStore($tid)."");
    return $count + 1;
}

function removeComment ($tid, $sid, $ok = 0)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if ($ok) {
        // Call recursive delete function to delete the comment and all its childs.
        // Returns total number of comments deleted.
        $num_deleted = removeSubComments($tid);
        // Update the number of comments in stories table
        $column = &$pntable['stories_column'];
        $dbconn->Execute("UPDATE $pntable[stories]
                          SET $column[comments]=$column[comments]-'$num_deleted'
                          WHERE $column[sid]='".pnVarPrepForStore($sid)."'");
        pnRedirect("modules.php?op=modload&name=News&file=article&sid=".$sid);
    } else {
        include("header.php");
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._REMOVECOMMENTS."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center>"._SURETODELCOMMENTS."";
        echo "<br><br>"._GOBACK." | <a href=\"admin.php?module=NS-Comments&amp;op=RemoveComment&amp;tid=$tid&amp;sid=$sid&amp;ok=1\">"
        ._YES."</a> ]</center>";
        CloseTable();
        include("footer.php");
    }
}

function removePollSubComments($tid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['pollcomments_column'];
    $result = $dbconn->Execute("SELECT $column[tid]
                                FROM $pntable[pollcomments]
                                WHERE $column[pid]='".pnVarPrepForStore($tid)."'");

    while (list($stid) = $result->fields) {
        removePollSubComments($stid);
         $result->MoveNext();
    }
    $dbconn->Execute("DELETE FROM $pntable[pollcomments]
                      WHERE {$pntable['pollcomments_column']['tid']}=".pnVarPrepForStore($tid)."");
}

function RemovePollComment ($tid, $pollID, $ok)
{
	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();

	if ($ok == 1) {

	// Call recursive delete function to delete the comment and all its childs.
	// Returns total number of comments deleted.
	$num_deleted = removePollSubComments($tid);
	pnRedirect("modules.php?op=modload&name=NS-Polls&file=index&req=results&pollID=".$pollID);

	} else {

        include("header.php");

        GraphicAdmin();
        OpenTable();

        echo "<center><font class=\"pn-title\"><b>"._REMOVECOMMENTS."</b></font></center>";

        CloseTable();
        OpenTable();

        echo "<center>"._SURETODELCOMMENTS."";
        echo "<br><br>";
        echo "<table><tr><td>\n";
        echo _GOBACK;
        echo "</td><td>\n";
        echo myTextForm("admin.php?module=NS-Comments&amp;op=RemovePollComment&tid=$tid&pollID=$pollID&ok=1", _YES);
        echo "</td></tr></table></center>\n";

        CloseTable();
        include("footer.php");
        }
}

function comments_admin_getConfig()
{
    include 'header.php';
    $bgcolor2 = $GLOBALS['bgcolor2'];

    // prepare vars
    $sel_moderate['0'] = '';
    $sel_moderate['1'] = '';
    $sel_moderate['2'] = '';
    $sel_moderate[pnConfigGetVar('moderate')] = ' selected';
    $sel_anonpost['0'] = '';
    $sel_anonpost['1'] = '';
    $sel_anonpost[pnConfigGetVar('anonpost')] = ' checked';

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._COMMENTSCONFIG."</b></font></center>";
    CloseTable();

    OpenTable();
    print '<center><font size="3" class="pn-title">'._COMMENTSMOD.'</font></center>'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._MODTYPE.':</td><td>'
        .'<select name="xmoderate" size="1" class="pn-normal">'
        ."<option value=\"1\"".$sel_moderate['1'].">"._MODADMIN.'</option>'
        ."<option value=\"2\"".$sel_moderate['2'].">"._MODUSERS.'</option>'
        ."<option value=\"0\"".$sel_moderate['0'].">"._NOMOD.'</option>'
        .'</select>'
        .'</td></tr></table>'
    ;
    CloseTable();

    OpenTable();
    print '<center><font size="3" class="pn-title">'._COMMENTSOPT.'</font></center>'
        .'<table border="0"><tr><td class="pn-normal">'
         ._ALLOWANONPOST.' </td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xanonpost\" value=\"1\"".$sel_anonpost['1']." class=\"pn-normal\">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xanonpost\" value=\"0\"".$sel_anonpost['0']." class=\"pn-normal\">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._COMMENTSLIMIT.":</td><td><input type=\"text\" name=\"xcommentlimit\" value=\"".pnVarPrepForDisplay(pnConfigGetVar('commentlimit'))."\" size=\"11\" maxlength=\"10\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._ANONYMOUSNAME.":</td><td><input type=\"text\" name=\"xanonymous\" value=\"".pnVarPrepForDisplay(pnConfigGetVar('anonymous'))."\" size=\"15\" class=\"pn-normal\">"
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    ;
    CloseTable();

    include ("footer.php");

}

function comments_admin_setConfig()
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    // Get configuration variables from input
    list($var['anonymous'],
         $var['anonpost'],
         $var['commentlimit'],
         $var['moderate']) = pnVarCleanFromInput('xanonymous',
                            			 'xanonpost',
						 'xcommentlimit',
						 'xmoderate');

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array ('anonpost',
		      'commentlimit',
		      'moderate');
    foreach ($fixvars as $v) {
        if (!isset($var[$v])) {
            $var[$v] = 0;
        }
    }

    // Set configuration variables
    while (list ($key, $val) = each ($var)) {
	pnConfigSetVar($key, $val);
    }
    pnRedirect('admin.php');
}

function comments_admin_main($var)
{
   list($op,
   	$ok,
   	$tid,
   	$sid,
   	$pollID) = pnVarCleanFromInput ('op',
   					'ok',
   					'tid',
   					'sid',
   					'pollID');
   	
   switch ($op) {

    case "RemoveComment":
            removeComment ($tid, $sid, $ok);
        break;

    case "removeSubComments":
            removeSubComments($tid);
        break;

    case "removePollSubComments":
            removePollSubComments($tid);
        break;

    case "RemovePollComment":
        RemovePollComment($tid, $pollID, $ok);
        break;

    case "getConfig":
        comments_admin_getConfig();
        break;

    case "setConfig":
        comments_admin_setConfig();
        break;

    default:
        comments_admin_getConfig();
        break;
   }
}

} else {
    echo "Access Denied";
}
?>
