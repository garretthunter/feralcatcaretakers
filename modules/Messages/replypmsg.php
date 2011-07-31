<?php
// File: $Id: replypmsg.php,v 1.10 2002/11/29 08:16:34 larsneo Exp $ $Name:  $
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

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

$ModName = $GLOBALS['name'];

include 'modules/'.$ModName.'/functions.php';

modules_get_language();

list($dbconn) = pnDBGetConn();
$pntable = pnDBGetTables();

// show message icons (should be configurable)
$show_icons = 1;


if(isset($cancel)) {
    pnRedirect('modules.php?op=modload&name='.$ModName.'&file=index');
}

if (!pnUserLoggedIn()) {
    pnRedirect('user.php');
} else {
    include('header.php');
    $userdata = pnUserGetVar('uname');

if(isset($submit)) {
    if($subject == '') {
        forumerror("0017");
    }

/* there is always an icon    
if($image == '') {
		forumerror("0018");
	}
*/

if($message == '') {
        forumerror("0019");
    }

    list($image,
         $subject,
         $to_user,
         $message) = pnVarCleanFromInput('image',
                                         'subject',
                                         'to_user',
                                         'message');

	/* since we use HMTL-display this is not needed
    if(isset($allow_html) == 0 && (isset($html))) {
        $message = htmlspecialchars($message);
    }
    */

	/* since we always use bbcode this is not needed
    if(isset($allow_bbcode) == 1 && (!isset($bbcode))) {
        $message = pn_bbencode($message);
    }
    */

	/* since we use [addsig] this is not needed
    if(isset($sig)) {
	$user_sig = pnUserGetVar('user_sig');
        $message .= "<br>-----------------<br>" . $user_sig;
    }
    */

    $message = pn_bbencode($message);
    $message = nl2br($message);
    if(pnUserLoggedin()) {
		$message .= "[addsig]";
	}


	/* since smilies need image tag allowed this should be configurable
    if(!isset($smile)) {
        $message = smile($message);
    }
    */

    $time = date("Y-m-d H:i");
    $column = &$pntable['users_column'];
    $res = $dbconn->Execute("SELECT $column[uid] 
                           FROM $pntable[users] 
                           WHERE $column[uname]='" . pnVarPrepForDisplay($to_user) . "'
                             AND $column[uid] != 1");

    list($to_userid) = $res->fields;
    $res->Close();

    if ($to_userid == "") {
        OpenTable();
        echo "<center><font class=\"pn-normal\">"._USERNOTINDB."<br>"
            .""._CHECKNAMEANDTRY."<br><br>"
            .""._GOBACK."</font></center>";
        CloseTable();
        include("footer.php");
    } else {
        $column = &$pntable['priv_msgs_column'];
        $nextid = $dbconn->GenId($pntable['priv_msgs_column']);
	$from_uid = pnUserGetVar('uid');

        $sql = "INSERT INTO $pntable[priv_msgs]
                  ($column[msg_id],
                   $column[msg_image],
                   $column[subject],
                   $column[from_userid],
                   $column[to_userid],
                   $column[msg_time],
                   $column[msg_text]) 
                VALUES
                  ('" . pnVarPrepForStore($nextid). "',
                  '" . pnVarPrepForStore($image) . "',
                  '" . pnVarPrepForStore($subject) . "',
                  '" . pnVarPrepForStore($from_uid) . "',
                  '" . pnVarPrepForStore($to_userid) . "',
                  '" . pnVarPrepForStore($time) . "',
                  '" . pnVarPrepForStore($message) . "')";
        $res = $dbconn->Execute($sql);
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: " . $dbconn->ErrorMsg());
            error_log("SQL was: $sql");
            forumerror("0020");
        }
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._MSGPOSTED."<b></font><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\"><b>"._RETURNTOPMSG."</b></a></center>";
    CloseTable();
    }
}

if (isset($delete)) {
    if (isset($msg_id)) {
        if (is_array($msg_id)) {
            // delete multiple messages for a list
            $column = &$pntable['priv_msgs_column'];
            for ($i = 0; $i < $total_messages; $i++) {
                if (isset($msg_id[$i])) {
		    $from_uid = pnUserGetVar('uid');
		    		$sql = "DELETE FROM $pntable[priv_msgs] 
							WHERE $column[msg_id] = '" . pnVarPrepForStore($msg_id[$i]) ."'
							AND $column[to_userid] = '" . pnVarPrepForStore($from_uid) ."'";
                    $res = $dbconn->Execute($sql);
                    if ($dbconn->ErrorNo()<>0) {
                        error_log("DB Error: " . $dbconn->ErrorMsg());
                        forumerror("0021");
                    } else {
                        $status = 1;
                    }
                }
            }
        } else {
            // delete a single message 
            $column = &$pntable['priv_msgs_column'];
	    	$from_uid = pnUserGetVar('uid');
	    	$sql = "DELETE FROM $pntable[priv_msgs] 
					WHERE $column[msg_id] = '" . pnVarPrepForStore($msg_id) ."'
					AND $column[to_userid] = '" . pnVarPrepForStore($from_uid) ."'";
            $res = $dbconn->Execute($sql);
            if ($dbconn->ErrorNo() != 0) {
                error_log("DB Error: " . $dbconn->ErrorMsg());
                forumerror("0021");
            } else {
                $status = 1;
            }
        }
        if ($status) {
            OpenTable();
            echo "<center><font class=\"pn-normal\"><b>"._MSGDELETED."</b></font><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\">"._RETURNTOPMSG."</a></center>";
            CloseTable();
        } 
    } else {
        OpenTable();
        echo "<center><font class=\"pn-normal\"><b>"._NO_MESSAGE_SELECTED."</b></font><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\">"._RETURNTOPMSG."</a></center>";
        CloseTable();
    }
}

if (isset($reply) || (isset($send))) {
    if (isset($reply)) {
        $column = &$pntable['priv_msgs_column'];
        $sql = "SELECT $column[msg_image] AS msg_image,
                       $column[subject] AS subject, 
                       $column[from_userid] AS from_userid,
                       $column[to_userid] AS to_userid
                FROM $pntable[priv_msgs] 
                WHERE $column[msg_id] = '". pnVarPrepForStore($msg_id) ."'";
        $result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: " . $dbconn->ErrorMsg());
            forumerror("0022");
        }
//ADODBtag MoveNext ->fet_chrow(DB_FETCHMODE_ASSOC
        $row = $result->GetRowAssoc(false);
        $result->MoveNext();
        if (!$row) {
            forumerror("0023");
        }
        $fromuserdata = get_userdata_from_id($row['from_userid']);
        $touserdata = get_userdata_from_id($row['to_userid']);
	$user_id = pnUserGetVar('uid');
        if (pnUserLoggedIn() && ($user_id != $touserdata['pn_uid']) ) {
            forumerror("0024");
        }
    }
    OpenTable();
       print '<center><font class="pn-pagetitle">'._USENDPRIVATEMSG.'</font></center>';
    CloseTable();

    echo "<FORM ACTION=\"modules.php\" METHOD=\"POST\" NAME=\"coolsus\">"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">"
        ."<input type=\"hidden\" name=\"name\" value=\"$ModName\">"
        ."<input type=\"hidden\" name=\"file\" value=\"replypmsg\">"
        ."<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"100%\"><TR><TD>"
        ."<TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"1\" WIDTH=\"100%\">"
        ."<TR BGCOLOR=\"$bgcolor2\" ALIGN=\"LEFT\">"
        ."<TD colspan=2><FONT class=\"pn-normal\"><b>"._ABOUTPOSTING.": "._ALLREGCANPOST."</b></FONT></TD>"
        ."</TR>"
        ."<TR ALIGN=\"LEFT\">"
        ."<TD BGCOLOR=\"$bgcolor3\" width=\"25%\"><font class=\"pn-normal\"><b>"._TO.":</b></font></TD>";
    if (isset($reply)) {
        echo "<TD BGCOLOR=\"$bgcolor3\"><INPUT TYPE=\"HIDDEN\" NAME=\"to_user\" VALUE=\"" . pnVarPrepForDisplay($fromuserdata['pn_uname']) . "\"><font class=\"pn-normal\">" . pnVarPrepForDisplay($fromuserdata['pn_uname']) . "</font></TD>";
    } else {
        if (isset($uname)) {
        echo "<TD BGCOLOR=\"$bgcolor3\"><INPUT NAME=\"to_user\" SIZE=\"26\" maxlength=\"25\" value=\"" . pnVarPrepForDisplay($uname) . "\">";
    } else {
        echo "<TD BGCOLOR=\"$bgcolor3\"><INPUT NAME=\"to_user\" SIZE=\"26\" maxlength=\"25\">";
    }
    echo "</td>";
    }
    echo "</TR>"
        ."<TR ALIGN=\"LEFT\">"
        ."<TD BGCOLOR=\"$bgcolor3\" width=\"25%\"><font class=\"pn-normal\"><b>"._SUBJECT.":</b></font></TD>";
    if (isset($reply)) {
        echo "<TD  BGCOLOR=\"$bgcolor3\"><INPUT TYPE=\"TEXT\" NAME=\"subject\" VALUE=\""._RE.": " . pnVarPrepForDisplay($row['subject']) . "\" SIZE=\"70\" MAXLENGTH=\"100\"></TD>";
    } else {
        echo "<TD  BGCOLOR=\"$bgcolor3\"><INPUT TYPE=\"TEXT\" NAME=\"subject\" SIZE=\"70\" MAXLENGTH=\"100\"></TD>";
    }
    echo "</TR>";

	if ($show_icons == 1){
    echo "<TR ALIGN=\"LEFT\" VALIGN=\"TOP\">"
        ."<TD  BGCOLOR=\"$bgcolor3\" width=\"25%\"><font class=\"pn-normal\"><b>"._MESSAGEICON.":</b></font></TD>"
        ."<TD  BGCOLOR=\"$bgcolor3\">";

    $handle=opendir("images/smilies");
    while ($file = readdir($handle)) {
        $filelist[] = $file;
    }
    asort($filelist);
    $a = 1;
	$count = 1;
    echo "<table><tr>";
    while (list ($key, $file) = each ($filelist)) {
		ereg(".gif|.jpg",$file);
		if ($file != "." && $file != ".." && $file != "index.html") {
			$sel = "";
			//Determine if image should be checked.
			if (!isset($row)){
				if ($a == 1){
					$sel = "checked";
				}
			} else {
				if (($row['msg_image'] != "" && $file == $row['msg_image']) ||
			        ($row['msg_image'] == "" && $a == 1)) {
					$sel = "checked";
				}
			}
			echo "<td><INPUT TYPE='radio' NAME='image' VALUE=\"$file\" $sel><IMG SRC=\"images/smilies/$file\" BORDER=\"0\">&nbsp;</td>";
			$a++;
		}
		if ($count >= 10) {
			$count=1; 
			echo "</tr><tr>";
		}
		$count++;
    }
    echo "</tr></table>";

    echo "</TD></TR>";
    } else {
    	echo "<INPUT TYPE='hidden' NAME='image' VALUE='icon1.gif'>";
    }
    	
    echo "<TR ALIGN=\"LEFT\" VALIGN=\"TOP\">"
        ."<TD BGCOLOR=\"$bgcolor3\" width=\"25%\"><font class=\"pn-normal\"><b>"._MESSAGE.":</b></font><br><br>";

    echo "(<a class=\"pn-normal\" href=\"modules/$ModName/bbcode_ref.php\" TARGET=\"blank\">"._BB_CODE."</a>)";

    if (isset($reply)) {
        $column = &$pntable['priv_msgs_column'];
        $column2 = &$pntable['users_column'];
        $sql = "SELECT $column[msg_text] AS msg_text, 
						$column[msg_time] AS msg_time, 
						$column2[uname] AS uname
				FROM $pntable[priv_msgs], $pntable[users] 
				WHERE $column[msg_id] = '" . pnVarPrepForStore($msg_id) . "'
				AND $column[from_userid] = " . pnVarPrepForStore($column2['uid']) ."";
    	$result = $dbconn->Execute($sql);
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: " . $dbconn->ErrorMsg());
            $reply = "Error Contacting database. Please try again.\n";
        } else {
//ADODBtag MoveNext ->fet_chrow(DB_FETCHMODE_ASSOC
            $row = $result->GetRowAssoc(false);
            $result->MoveNext();
            $text = desmile($row['msg_text']);
            $text = preg_replace('/(<br[ \/]*?>)/i', "", $text);
            $text = pnVarPrepHTMLDisplay($text);
            $text = pn_bbdecode($text);
            $text = eregi_replace("\[addsig]", "", $text);

    		$row['msg_time'] = mktime( substr($row['msg_time'], 11, 2),     // hour
                                        substr($row['msg_time'], 14, 2),    // minute
                                        '0',                                // second
                                        substr($row['msg_time'], 5, 2),     // month
                                        substr($row['msg_time'], 8, 2),     // day
                                    	substr($row['msg_time'], 0, 4));    // year

            $reply = "[quote]\n$row[uname] "._WROTE." "._ON." ". ml_ftime(_DATETIMEBRIEF, GetUserTime($row['msg_time'])) .":\n$text\n[/quote]";
        }
    }
    echo "</font></TD>"
        ."<TD BGCOLOR=\"$bgcolor3\"><TEXTAREA NAME=\"message\" ROWS=\"15\" COLS=\"70\" WRAP=\"VIRTUAL\">";
    if (isset($reply)) {
        echo $reply;
    }
    echo "</TEXTAREA><BR>"._ALLOWEDHTML."<br>";
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
        if ($access > 0) echo " &lt;".$key."&gt;";
    }


    // PostNuke: make this a user set option 0=on / 1=off
	/* since there are no message icons we don't need this
    if($smilies==1) { echo "";  } else {

    putitems();

    } // PostNuke: end
	*/
	
    echo "</TD>"
        ."</TR>";

// PostNuke: remove last of icon/smiley stuff
	/* not needed right now
    if($smilies==1) { 
	echo "";  
    } 
    else {

    echo "<TR ALIGN=\"LEFT\">"
        ."<TD BGCOLOR=\"$bgcolor3\" width=\"25%\"><font class=\"pn-normal\">"._OPTIONS.":</font></TD>"
        ."<TD BGCOLOR=\"$bgcolor1\">";
    if($allow_html == 1) {
        echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"html\"><font class=\"pn-normal\">"._HTMLDISSABLE."<font><br>";
    }
    if($allow_bbcode == 1) {
        echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"bbcode\"><font class=\"pn-normal\">"._BBCODEDISSABLE."</font><br>";
    }

    echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"smile\"><font class=\"pn-normal\">"._SMILEDISSABLE."</font><br>"
    ."</TD>"
        ."</TR>";
}
	*/

// PostNuke: end

if(!isset($msg_id)) {
	$msg_id = '';
}

        echo "<TR>"
        ."<TD BGCOLOR=\"$bgcolor2\" colspan=\"2\" ALIGN=\"left\">"
        ."<INPUT TYPE=\"HIDDEN\" NAME=\"msg_id\" VALUE=\"$msg_id\">"
        ."<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\""._SUBMIT."\">&nbsp;";
    if (isset($reply)) {
        echo "&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"cancel\" VALUE=\""._CANCELREPLY."\">";
    } else {
        echo "&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"cancel\" VALUE=\""._CANCELSEND."\">";
    }
    echo "</TD>"
        ."</TR>"
        ."</TABLE></TD></TR></TABLE>"
        ."</FORM>"
        ."<BR>";
    }
}
include('footer.php');

?>