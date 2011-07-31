<?php // File: $Id: readpmsg.php,v 1.10 2002/10/17 16:53:23 skooter Exp $ $Name:  $
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
// Original Author of file: Francisco Burzi
// Purpose of file: Part of phpBB integration
//   Copyright (c) 2001 by
//   Richard Tirtadji AKA King Richard (rtirtadji@hotmail.com)
//   Hutdik Hermawan AKA hotFix (hutdik76@hotmail.com)
//   http://www.phpnuke.web.id
// ----------------------------------------------------------------------
// Changelog
// yyyy-mm-dd  username  description
// 2001-11-21  eugeniobaldi    Fixed brosing more than 2 messages 
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = $GLOBALS['name'];

include 'modules/'.$ModName.'/functions.php';

modules_get_language();

list($dbconn) = pnDBGetConn();
$pntable = pnDBGetTables();

//$forumpage = 1;

if (!pnUserLoggedIn()) {
    pnRedirect('user.php');
} else {
    include('header.php');

    list($start,$total_messages) = pnVarCleanFromInput('start','total_messages');

    $userdata = pnUserGetVar('uid');
    $column = &$pntable['priv_msgs_column'];
    $sql = buildSimpleQuery ('priv_msgs', array ('msg_id', 'msg_image', 'subject', 'from_userid', 'to_userid', 'msg_time', 'msg_text', 'read_msg'), "$column[to_userid]='" . pnVarPrepForStore($userdata) . "'", '', 1, pnVarPrepForStore($start));
    $resultID = $dbconn->Execute($sql);
    if($dbconn->ErrorNo()<>0) {
        error_log("DB Error: " . $dbconn->ErrorMsg());
        echo $dbconn->ErrorMsg() . "<br>";
        forumerror(0005);
    }

    $myrow = $resultID->GetRowAssoc(false);
    $resultID->MoveNext();
    // Turn this time string into a UNIX timestamp for use with GetUserTime
    $myrow['msg_time'] = mktime( substr($myrow['msg_time'], 11, 2),     // hour
                                        substr($myrow['msg_time'], 14, 2),    // minute
                                        '0',                                // second
                                        substr($myrow['msg_time'], 5, 2),     // month
                                        substr($myrow['msg_time'], 8, 2),     // day
                                    substr($myrow['msg_time'], 0, 4));    // year

    $column = &$pntable['priv_msgs_column'];
    $sql = "UPDATE $pntable[priv_msgs] 
            SET $column[read_msg]='1' 
            WHERE $column[msg_id]= '" .pnVarPrepForStore($myrow['msg_id']) ."'";
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo()<>0) {
        error_log("DB Error: " . $dbconn->ErrorMsg());
        echo $dbconn->ErrorMsg() . "<br>";
        forumerror(0005);
    }

    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._PRIVATEMESSAGE."</font>
           <br>
	   <br>
	    <font class=\"pn-normal\">
	    <b>
	     <a href=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\">"._INDEX."</a>
	    </b></font>
	  </center>";
    CloseTable();
    //Cleaned up display - skooter
    echo "<br>"
    ."<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" valign=\"top\" width=\"100%\"><tr><td>"
    ."<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">"
    ."<tr bgcolor=\"$bgcolor2\" align=\"left\">"
    ."<td width=\"20%\"><font class=\"pn-sub\"><b>"._FROM."</b></font></td>"
    ."<td width=\"80%\"><font class=\"pn-sub\"><b>"._MESSAGE."</b></font></td>"
    ."</tr>";

    if (!$resultID->EOF) {
        echo "<td bgcolor=\"$bgcolor3\" colspan=\"2\" align=\"center\"><font class=\"pn-normal\">"._DONTHAVEMESSAGES."</font></td></tr>\n";
    } else {
        echo "<tr bgcolor=\"$bgcolor3\" align=\"left\">\n";
        $posterdata = get_userdata_from_id($myrow['from_userid']);
        echo "<td valign=\"top\"><font class=\"pn-normal\"><b>".pnVarPrepForDisplay($posterdata['pn_uname'])."</b></font><br><br>\n";
		/* listed in profile
		if (!empty($posterdata['pn_user_from'])) {
			echo "<font class=\"pn-normal\">"._FROM.": " . pnVarPrepForDisplay($posterdata['pn_user_from']) . "</font><br><br>\n";
		}
		*/
		if ($posterdata['pn_user_avatar'] != "") {
		  echo "<img src='images/avatar/" . pnVarPrepForDisplay($posterdata['pn_user_avatar']) . "' alt=\"\">\n";
		}
		echo "</td><td><font class=\"pn-sub\">"._SUBJECT.": ";
		if ($myrow['msg_image'] != ""){
			echo "<img src=\"images/smilies/".$myrow['msg_image']."\">&nbsp;";
		}
		echo pnVarPrepForDisplay($myrow['subject'])."</font>&nbsp;&nbsp;&nbsp;"
			."<font class=\"pn-sub\">"._SENT.": ".ml_ftime(_DATETIMEBRIEF, GetUserTime($myrow['msg_time']))."</font>"
			."<hr noshade>\n";
		echo "<font class=\"pn-normal\">\n";

		// bit of a cheat here .. greg.
		$message = eregi_replace("\[addsig]", "<br>-----------------<br>" . nl2br($posterdata['pn_user_sig']), $myrow['msg_text']);

		$message = pn_bbencode($message);

		echo pnVarPrepHTMLDisplay($message) . "</font><br>"
			."<hr noshade>\n"
			."<a href=\"user.php?op=userinfo&amp;uname=" . pnVarPrepForDisplay($posterdata['pn_uname']) . "\"><img src=\"images/global/profile.gif\" border=\"0\" alt=\"\"></a><font class=\"pn-sub\">"._PROFILE."</font>\n";
		if($posterdata['pn_femail'] != '') {
			echo "&nbsp;&nbsp;<a href=\"mailto:$posterdata[pn_femail]\"><IMG SRC=\"images/global/email.gif\" border=\"0\" alt=\"\"></a><font class=\"pn-sub\">"._EMAIL."</font>\n";
		}
		if($posterdata['pn_url'] != '') {
			if(strstr("http://", $posterdata['pn_url'])) {
			$posterdata['pn_url'] = "http://" . $posterdata['pn_url'];
		}
        echo "&nbsp;&nbsp;<a href=\"" . pnVarPrepForDisplay($posterdata['pn_url']) . "\" TARGET=\"_blank\"><IMG SRC=\"images/global/www_icon.gif\" border=0 Alt=\"\"></a><font class=\"pn-sub\">www</font>\n";
    }
    if($posterdata['pn_user_icq'] != '')
        echo "&nbsp;&nbsp;<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . pnVarPrepForDisplay($posterdata['pn_user_icq']) . "\" TARGET=\"_blank\"><IMG SRC=\"http://wwp.icq.com/scripts/online.dll?icq=" . pnVarPrepForDisplay($posterdata['pn_user_icq']) . "&img=5\" border=0\" Alt=\"\"></a><font class=\"pn-sub\">"._ICQ."</font>\n";
    if($posterdata["pn_user_aim"] != '')
            echo "&nbsp;&nbsp;<a href=\"aim:goim?screenname=" . pnVarPrepForDisplay($posterdata['pn_user_aim']) . "&message=Hi+" . pnVarPrepForDisplay($posterdata['pn_user_aim']) . ".+Are+you+there?\"><img src=\"images/global/aim.gif\" border=\"0\" Alt=\"\"></a><font class=\"pn-sub\">"._AIM."</font>\n";
    if($posterdata["pn_user_yim"] != '')
            echo "&nbsp;&nbsp;<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . pnVarPrepForDisplay($posterdata['pn_user_yim']) . "&.src=pg\"><img src=\"images/global/yim.gif\" border=\"0\" Alt=\"\"></a><font class=\"pn-sub\">"._YIM."</font>\n";
    if($posterdata["pn_user_msnm"] != '')
            echo "&nbsp;&nbsp;<a href=\"user.php?op=userinfo&amp;uname=" . pnVarPrepForDisplay($posterdata['pn_uname']) . "\"><img src=\"images/global/msnm.gif\" border=\"0\" Alt=\"\"></a><font class=\"pn-sub\">"._MSNM."</font>\n";
    echo "</td></tr>"
        ."<tr bgcolor=\"$bgcolor1\" align=\"RIGHT\"><td width=20% COLSPAN=2 align=RIGHT><font class=\"pn-normal\">";
    $previous = $start-1;
    $next = $start+1;
    if ($previous >= 0) {
        echo "<a href=\"modules.php?op=modload&amp;name=$ModName&amp;file=readpmsg&amp;start=$previous&amp;total_messages=$total_messages\">"._PREVIOUSMESSAGE."</a> | ";
    } else {
        echo ""._PREVIOUSMESSAGE." | ";
    }
    if ($next < $total_messages) {
        echo "<a href=\"modules.php?op=modload&amp;name=$ModName&amp;file=readpmsg&amp;start=$next&amp;total_messages=$total_messages\">"._NEXTMESSAGE."</a></font>";
    } else {
        echo ""._NEXTMESSAGE."</font>";
    }
    echo "</td></tr>"
        ."<tr bgcolor=\"$bgcolor2\" align=\"left\"><td width=\"20%\" COLSPAN=\"2\" align=\"left\">"
        ."<font class=\"pn-normal\">|&nbsp;"
        ."<a href=\"modules.php?op=modload&amp;name=$ModName&amp;file=replypmsg&amp;reply=1&amp;msg_id=$myrow[msg_id]\"><b>"._REPLY."</b></a>"
        ."&nbsp;|&nbsp;<a href=\"modules.php?op=modload&amp;name=$ModName&amp;file=replypmsg&amp;delete=1&amp;msg_id=$myrow[msg_id]\"><b>"._DELETE."</b></a>&nbsp;|\n";
    }
    echo "</font></td></tr></table></td></tr></table>";
}
include 'footer.php';

?>