<?php // File: $Id: index.php,v 1.9 2002/10/24 20:10:43 skooter Exp $ $Name:  $
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
// Purpose of file: 
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = $GLOBALS['name'];

include 'modules/'.$ModName.'/functions.php';

modules_get_language();

if (!pnUserLoggedIn()) {
    pnRedirect('user.php');
} else {
    include 'header.php';

    $userdata = pnUserGetVar('uid');
    $column = &$pntable['priv_msgs_column'];

    $sql = "SELECT $column[msg_id] AS msg_id, $column[msg_image] AS msg_image, 
             $column[subject] AS subject, $column[from_userid] AS from_userid, 
             $column[to_userid] AS to_userid, $column[msg_time] AS msg_time, 
             $column[msg_text] AS msg_text, $column[read_msg] AS read_msg 
             FROM $pntable[priv_msgs] 
             WHERE $column[to_userid]=".(int)pnVarPrepForStore($userdata);

    $resultID = $dbconn->Execute($sql); 
    if($dbconn->ErrorNo()<>0) {
        error_log("DB Error: " . $dbconn->ErrorMsg());
        echo  $dbconn->ErrorMsg() . "<br>";
        forumerror(0005);
    }
    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._PRIVATEMESSAGES."</font></center>";
    CloseTable();

    echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" align=\"center\" valign=\"top\" width=\"100%\"><tr><td>"
        ."<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\">"
        ."<form name=\"prvmsg\" action=\"modules.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">"
        ."<input type=\"hidden\" name=\"name\" value=\"$ModName\">"
        ."<input type=\"hidden\" name=\"file\" value=\"replypmsg\">"
        ."<tr bgcolor=\"$bgcolor2\" align=\"left\">"
        ."<td bgcolor=\"$bgcolor2\" align=\"center\" valign=\"middle\" width=\"5%\"><input name=\"allbox\" onclick=\"CheckAll();\" type=\"checkbox\" value=\""._CHECKALL."\"></td>"
        ."<td bgcolor=\"$bgcolor2\" align=\"center\" valign=\"middle\" width=\"5%\">&nbsp;</td>"
		."<td bgcolor=\"$bgcolor2\" align=\"center\" valign=\"middle\" width=\"5%\">&nbsp;</td>"
        ."<td width=\"15%\"><font class=\"pn-normal\">"._FROM."</font></td>"
        ."<td width=\"50%\" ><font class=\"pn-normal\" >"._SUBJECT."</font></td>"
        ."<td width=\"20%\"><font class=\"pn-normal\">"._DATE."</font></td>"
        ."</tr>";
    if (!$total_messages = $resultID->PO_RecordCount()) {
        echo "<td bgcolor=\"$bgcolor3\" colspan=\"6\" align=\"center\"><font class=\"pn-normal\">"._DONTHAVEMESSAGES."</font></td></tr>\n";
    } else {
        $display=1;
    }
    $count=0;
    while(!$resultID->EOF) {
        $myrow = $resultID->GetRowAssoc(false);
        $resultID->MoveNext();
        // get a Unix timestamp for this date/time  -- Alarion :: 08/21/2001
        $myrow['msg_time'] = mktime( substr($myrow['msg_time'], 11, 2),     // hour
                                     substr($myrow['msg_time'], 14, 2),    // minute
                                     '0',                                // second
                                     substr($myrow['msg_time'], 5, 2),     // month
                                     substr($myrow['msg_time'], 8, 2),     // day
                                     substr($myrow['msg_time'], 0, 4));    // year                                    
        echo "<tr align=\"left\">";
        echo "<td bgcolor=\"$bgcolor1\" valign=\"top\" width=\"2%\" align=\"center\"><input type=\"checkbox\" onclick=\"CheckCheckAll();\" name=\"msg_id[$count]\" value=\"$myrow[msg_id]\"></td>";
        if ($myrow['read_msg'] == "1") {
            echo "<td valign=\"top\" width=\"5%\" align=\"center\" bgcolor=\"$bgcolor1\">&nbsp;</td>";
        } else {
            echo "<td valign=\"top\" width=\"5%\" align=\"center\" bgcolor=\"$bgcolor1\"><img src=\"images/global/email.gif\" border=\"0\" alt=\""._NOTREAD."\"></td>";
        }
        
		echo "<td bgcolor=\"$bgcolor1\" valign=\"top\" width=\"5%\" align=\"center\">";
		if($myrow['msg_image'] != "") {
			echo "<img src=\"images/smilies/".$myrow['msg_image']."\" border=\"0\">";
		}
		echo "</td>";

        $posterdata = get_userdata_from_id($myrow['from_userid']);
        echo "<td bgcolor=\"$bgcolor1\" valign=\"middle\" width=\"10%\">".pnVarPrepForDisplay($posterdata['pn_uname'])."</td>"
            ."<td bgcolor=\"$bgcolor1\" valign=\"middle\"><font class=\"pn-normal\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$ModName&amp;file=readpmsg&amp;start=$count&amp;total_messages=$total_messages\">".pnVarPrepForDisplay($myrow['subject'])."</a></font></td>";
        
        echo "<td bgcolor=\"$bgcolor1\" valign=\"middle\" align=\"left\" width=\"25%\"><font class=\"pn-normal\">".ml_ftime(_DATETIMEBRIEF, GetUserTime($myrow['msg_time']))."</font></td></tr>";
        $count++;
    }
    if (isset($display)) {
        echo "<tr bgcolor=\"$bgcolor2\" align=\"left\">";
        echo "<td colspan=6 align='left'>";
        echo "<INPUT TYPE=\"submit\" NAME='send' VALUE=\""._SENDAMSG."\">   ";
        echo "<INPUT TYPE=\"submit\" NAME='delete' VALUE=\""._DELETE."\">";
        echo "</td></tr>";
    
        echo "<input type='hidden' name='total_messages' value='$total_messages'>";
        echo "</form>";
    }
    else {
        echo "<tr bgcolor=\"$bgcolor2\" align=\"left\">";
        echo "<td colspan=6 align='left'><b>|&nbsp;<a class=\"pn-normal\" href='modules.php?op=modload&amp;name=$ModName&amp;file=replypmsg&amp;send=1'>"._SENDAMSG."</a>&nbsp;|</b></td></tr>";
        echo "</form>";
    }
    echo "</table></td></tr></table>
    <script type=\"text/javascript\">\n\n
    <!--\n\n
    function CheckAll() {\n
      for (var i=0;i<document.prvmsg.elements.length;i++) {\n
        var e = document.prvmsg.elements[i];\n
        if ((e.name != 'allbox') && (e.type=='checkbox'))\n
        e.checked = document.prvmsg.allbox.checked;\n
      }\n
    }\n\n

    function CheckCheckAll() {\n
      var TotalBoxes = 0;\n
      var TotalOn = 0;\n
      for (var i=0;i<document.prvmsg.elements.length;i++) {\n
        var e = document.prvmsg.elements[i];\n
        if ((e.name != 'allbox') && (e.type=='checkbox')) {\n
          TotalBoxes++;\n
          if (e.checked) {\n
            TotalOn++;\n
          }\n
        }\n
      }\n
      if (TotalBoxes==TotalOn) {\n
        document.prvmsg.allbox.checked=true;\n
      } else {\n
        document.prvmsg.allbox.checked=false;\n
      }\n
    }\n\n

    -->\n
    </script>\n\n";
}

include 'footer.php';

?>