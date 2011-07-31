<?php // $Id: comments.php,v 1.2 2002/11/07 23:26:43 neo Exp $ $Name:  $
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
// Filename: modules/NS-Polls/index.php
// Original Author: Till Gerken (tig@skv.org)
// Purpose: Voting system
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = $GLOBALS['name'];
modules_get_language();

function modone()
{
    $moderate = pnConfigGetVar('moderate');

    if(($moderate == 1) || ($moderate==2))
    {
        echo "<form action=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments\" method=\"post\">";
    }
}
function modtwo($tid, $score, $reason)
{

    $moderate = pnConfigGetVar('moderate');
    $reasons = pnConfigGetVar('reasons');
    if((($moderate == 1) || ($moderate == 2)) && (pnUserLoggedIn())) {
        echo " | <select name=dkn$tid>";
        for($i=0; $i<sizeof($reasons); $i++) {
            echo "<option value=\"$score:$i\">".pnVarPrepForDisplay($reasons[$i])."</option>\n";
        }
        echo "</select>";
    }
}

function modthree($pollID, $mode, $order, $thold=0)
{

    $moderate = pnConfigGetVar('moderate');
    $userimg = pnConfigGetVar('userimg');

    if((($moderate == 1) || ($moderate==2)) && (pnUserLoggedIn())) echo "<center><input type=hidden name=pollID value=$pollID><input type=hidden name=mode value=$mode><input type=hidden name=order value=$order><input type=hidden name=\"thold\" value=\"$thold\">
    <input type=\"hidden\" name=\"req\" value=\"moderate\">
    <input type=\"submit\" value=\""._MODERATE."\"></form></center>";
}

function navbar($pollID, $title, $thold, $mode, $order)
{
    list($pid, $sid) = pnVarCleanFromInput('pid','sid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $pollcomm = pnConfigGetVar('pollcomm');
    $anonpost = pnConfigGetVar('anonpost');

    $query = $dbconn->Execute("SELECT COUNT(*)
                             FROM ".$pntable['pollcomments'].
                             " WHERE ".$pntable['pollcomments_column']['pollid']."=".(int)pnVarPrepForStore($pollID));
    list($count) = $query->fields;
    $column = &$pntable['poll_desc_column'];
    $result = $dbconn->Execute("SELECT $column[polltitle]
                              FROM $pntable[poll_desc]
                              WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID));

    list($title) = $result->fields;

    if(!isset($thold)) {
        $thold=0;
    }

    echo "\n\n<!-- COMMENTS NAVIGATION BAR START -->\n\n";
    echo "<table width=\"99%\" border=\"0\" cellspacing=\"1\" cellpadding=\"2\">\n";
    if($title) {
        echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><font class=\"pn-normal\">\"".pnVarPrepForDisplay($title)."\" | ";
        if (pnUserLoggedIn()) {
            echo "<a class=\"pn-normal\" href=\"user.php?op=editcomm\"><font class=\"pn-normal\">"._CONFIGURE."</font></a>";
        } else {
            echo "<a class=\"pn-normal\" href=\"user.php\"><font class=\"pn-normal\">"._LOGINCREATE."</font></a>";
        }
        if(($count==1)) {
            echo " | $count "._COMMENT."</font></td></tr>\n";
        } else {
            echo " | $count "._COMMENTS."</font></td></tr>\n";
        }
    }
    echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\" align=\"center\" width=\"100%\">\n"
    ."<table border=\"0\"><tr><td><font class=\"pn-normal\">\n"
    ."<form method=\"post\" action=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;req=results&pollID=$pollID\">\n";
    if (pnConfigGetVar('moderate')) {
        echo ""
            ."<font class=\"pn-normal\">"._THRESHOLD."</font> <select name=\"thold\">\n"
            ."<option value=\"-1\"";
        if ($thold == -1) {
         echo " selected";
        }
        echo ">-1</option>\n"
             ."<option value=\"0\"";
        if ($thold == 0) {
         echo " selected";
        }
        echo ">0</option>\n"
         ."<option value=\"1\"";
        if ($thold == 1) {
           echo " selected";
        }
        echo ">1</option>\n"
         ."<option value=\"2\"";
        if ($thold == 2) {
           echo " selected";
        }
        echo ">2</option>\n"
         ."<option value=\"3\"";
        if ($thold == 3) {
           echo " selected";
        }
        echo ">3</option>\n"
         ."<option value=\"4\"";
        if ($thold == 4) {
           echo " selected";
        }
        echo ">4</option>\n"
         ."<option value=\"5\"";
        if ($thold == 5) {
           echo " selected";
        }
        echo ">5</option>\n";
    }   else {
        echo "<input type=\"hidden\" name=\"thold\" value=\"0\">\n"; // I'm not sure it should be zero here but should be ok
    }
    echo ""
     ."</select> <select name=mode>"
     ."<option value=\"nocomments\"";
    if ($mode == 'nocomments') {
    echo " selected";
    }
    echo ">"._NOCOMMENTS."</option>\n"
     ."<option value=\"nested\"";
    if ($mode == 'nested') {
    echo " selected";
    }
    echo ">"._NESTED."</option>\n"
     ."<option value=\"flat\"";
    if ($mode == 'flat') {
    echo " selected";
    }
    echo ">"._FLAT."</option>\n"
     ."<option value=\"thread\"";
    if (!isset($mode) || $mode=='thread' || $mode=="") {
    echo " selected";
    }
    echo ">"._THREAD."</option>\n"
     ."</select> <select name=\"order\">"
     ."<option value=\"0\"";
    if (!$order) {
    echo " selected";
    }
    echo ">"._OLDEST."</option>\n"
     ."<option value=\"1\"";
    if ($order==1) {
    echo " selected";
    }
    echo ">"._NEWEST."</option>\n";
        if (pnConfigGetVar('moderate')) {
            echo "<option value=\"2\"";
            if ($order == 2) {
                echo " selected";
            }
            echo ">"._HIGHEST."</option>\n";
        }
        echo ""
         ."</select>\n"
     ."<input type=\"hidden\" name=\"sid\" value=\"$sid\">\n"
     ."<input type=\"submit\" value=\""._REFRESH."\"></form>\n";
    if (($pollcomm) AND ($mode != "nocomments")) {
    if ($anonpost==1 OR pnUserLoggedIn()) {
        echo "</font></td><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\"><form action=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments\" method=\"post\">"
        ."<input type=\"hidden\" name=\"pid\" value=\"$pid\">"
        ."<input type=\"hidden\" name=\"pollID\" value=\"$pollID\">"
        ."<input type=\"hidden\" name=\"req\" value=\"Reply\">"
        ."&nbsp;&nbsp;<input type=\"submit\" value=\""._REPLYMAIN."\">";
    }
    }
    echo "</form></font></td></tr></table>\n"
    ."</td></tr>"
    ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><font class=\"pn-sub\">"._COMMENTSWARNING."</font></td></tr>\n"
    ."</table>"
    ."\n\n<!-- COMMENTS NAVIGATION BAR END -->\n\n";
}

function DisplayKids ($tid, $mode, $order=0, $thold=0, $level=0, $dummy=0, $tblwidth=99)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $reasons = pnConfigGetVar('reasons');
    $anonymous = pnConfigGetVar('anonymous');
    $anonpost = pnConfigGetVar('anonpost');
    $commentlimit = pnConfigGetVar('commentlimit');

    $comments = 0;

    $noshowscore = pnUserGetVar('noscore');
    $commentsmax = pnUserGetVar('commentmax');

    $column = &$pntable['pollcomments_column'];
    $result = $dbconn->Execute("SELECT $column[tid], $column[pid], $column[pollid],
                                $column[date], $column[name],
                                $column[email], $column[url], $column[host_name],
                                $column[subject], $column[comment], $column[score],
                                $column[reason]
                              FROM $pntable[pollcomments]
                              WHERE $column[pid] = ".pnVarPrepForStore($tid)."
                              ORDER BY $column[date], $column[tid]");
    if ($mode == 'nested') {
        /* without the tblwidth variable, the tables run of the screen with netscape
           in nested mode in long threads so the text can't be read. */

        while(list($r_tid, $r_pid, $r_pollID, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
            $r_date=$result->UnixTimeStamp($r_date);
            $result->MoveNext();
            if($r_score >= $thold) {
                if (!isset($level)) {
                } else {
                    if (!$comments) {
                        echo "<ul>";
                        $tblwidth -= 5;
                    }
                }
                $comments++;
                if (!eregi("[a-z0-9]",$r_name)) $r_name = $anonymous;
                if (!eregi("[a-z0-9]",$r_subject)) $r_subject = "["._NOSUBJECT."]";
            // enter hex color between first two appostrophe for second alt bgcolor
                $r_bgcolor = ($dummy%2)?"":"".$GLOBALS['bgcolor1']."";
                echo "<a name=\"$r_tid\">";
                echo "<table border=\"0\"><tr bgcolor=\"$r_bgcolor\"><td>";
                //formatTimestamp($r_date);
                $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
                if ($r_email) {
                    echo "<p><font class=\"pn-normal\">$r_subject</font> <font class=\"pn-normal\">";
                    if(!$noshowscore && $modoption ) {
                        echo "("._SCORE." $r_score";
                        if($r_reason>0) echo ", $reasons[$r_reason]";
                        echo ")";
                    }
                    echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$r_email\">$r_name</a> <font class=\"pn-normal\"><b>($r_email)</b></font> "._ON." $datetime";
                } else {
                    echo "<p><font class=\"pn-normal\"><b>$r_subject</b></font> <font class=\"pn-normal\">";
                    if(!$noshowscore && $modoption ) {
                        echo "("._SCORE." $r_score";
                        if($r_reason>0) echo ", $reasons[$r_reason]";
                        echo ")";
                    }
                    echo "<br>"._BY." $r_name "._ON." $datetime";
                }
                if ($r_name != $anonymous) { echo "<BR>(<a class=\"pn-normal\" href=\"user.php?op=userinfo&uname=$r_name\">"._USERINFO."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$r_name\">"._SENDAMSG."</a>) "; }
                if (eregi("http://",$r_url)) { echo "<a class=\"pn-normal\" href=\"$r_url\" target=\"window\">$r_url</a> "; }
                echo "</font></td></tr><tr><td>";
                if(($commentsmax) && (strlen($r_comment) > $commentsmax)) echo substr("$r_comment", 0, $commentsmax)."<br><br><b><a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$r_pollID&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a></b>";
                elseif(strlen($r_comment) > $commentlimit) echo substr("$r_comment", 0, $commentlimit)."<br><br><b><a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$r_pollID&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a></b>";
                else echo "<font class=\"pn-normal\">$r_comment</font>";
                echo "</td></tr></table><br><p>";
                echo "<div align=\"center\">";
                if ($anonpost==1 OR pnUserLoggedIn()) {
                    echo "<font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=Reply&amp;pid=$r_tid&amp;pollID=$r_pollID&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>";
                }
                modtwo($r_tid, $r_score, $r_reason);
                echo " ]</font><p>";
                echo "</div>";
                DisplayKids($r_tid, $mode, $order, $thold, $level+1, $dummy+1, $tblwidth);
            }
        }
    } elseif ($mode == 'flat') {

        while(list($r_tid, $r_pid, $r_pollID, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {

            $result->MoveNext();
            if($r_score >= $thold) {
                if (!eregi("[a-z0-9]",$r_name)) $r_name = $anonymous;
                if (!eregi("[a-z0-9]",$r_subject)) $r_subject = "["._NOSUBJECT."]";
                echo "<a name=\"$r_tid\">";
                echo "<hr><table width=\"99%\" border=\"0\"><tr bgcolor=\"".$GLOBALS['bgcolor1']."\"><td>";
                $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
                if ($r_email) {
                    echo "<p><font class=\"pn-normal\">$r_subject</font> <font class=\"pn-normal\">";
                    if(!$noshowscore && $modoption ) {
                        echo "("._SCORE." $r_score";
                        if($r_reason>0) echo ", $reasons[$r_reason]";
                        echo ")";
                    }
                    echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$r_email\">$r_name</a> <font class=\"pn-normal\"><b>($r_email)</b></font> "._ON." $datetime";
                } else {
                    echo "<p><font class=\"pn-normal\">$r_subject</font> <font class=\"pn-normal\">";
                    if(!$noshowscore && $modoption ) {
                        echo "("._SCORE." $r_score";
                        if($r_reason>0) echo ", $reasons[$r_reason]";
                        echo ")";
                    }
                    echo "<br>"._BY." $r_name "._ON." $datetime";
                }
                if ($r_name != $anonymous) { echo "<BR>(<a class=\"pn-normal\" href=\"user.php?op=userinfo&uname=$r_name\">"._USERINFO."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$r_name\">"._SENDAMSG."</a>) "; }
                if (eregi("http://",$r_url)) { echo "<a class=\"pn-normal\" href=\"$r_url\" target=\"window\">$r_url</a> "; }
                echo "</font></td></tr><tr><td>";
                if(($commentsmax) && (strlen($r_comment) > $commentsmax)) echo substr("$r_comment", 0, $commentsmax)."<br><br><b><a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$r_pollID&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a></b>";
                elseif(strlen($r_comment) > $commentlimit) echo substr("$r_comment", 0, $commentlimit)."<br><br><b><a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$r_pollID&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a></b>";
                else echo "<font class=\"pn-normal\">$r_comment</font>";                
                echo "</td></tr></table><br><p>"
                    ."<div align=\"center\">"
                    ."<font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=Reply&amp;pid=$r_tid&amp;pollID=$r_pollID&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>";
                modtwo($r_tid, $r_score, $r_reason);
                echo " ]</font><p>";
                echo "</div>";
                DisplayKids($r_tid, $mode, $order, $thold);
            }
        }
    } else {

        while(list($r_tid, $r_pid, $r_pollID, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {

            $r_date=$result->UnixTimeStamp($r_date);
            $result->MoveNext();
            if($r_score >= $thold) {
                if (!isset($level)) {
                } else {
                    if (!$comments) {
                        echo "<ul>";
                    }
                }
                $comments++;
                if (!eregi("[a-z0-9]",$r_name)) $r_name = $anonymous;
                if (!eregi("[a-z0-9]",$r_subject)) $r_subject = "["._NOSUBJECT."]";
                $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
                echo "<li><font class=\"pn-normal\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=showreply&amp;tid=$r_tid&amp;pollID=$r_pollID&amp;pid=$r_pid&amp;mode=$mode&amp;order=$order&amp;thold=$thold#$r_tid\">$r_subject</a> "._BY." $r_name "._ON." $datetime</font><br>";

                DisplayKids($r_tid, $mode, $order, $thold, $level+1, $dummy+1);
            }
        }
    }
    if ($level && $comments) {
        echo "</ul>";
    }

}

function DisplayBabies ($tid, $level=0, $dummy=0)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');

    $comments = 0;
    $column = &$pntable['pollcomments_column'];
    $result = $dbconn->Execute("SELECT $column[tid], $column[pid], $column[pollID],
                                $column[date], $column[name],
                                $column[email], $column[url], $column[host_name],
                                $column[subject], $column[comment], $column[score],
                                $column[reason]
                              FROM $pntable[pollcomments]
                              WHERE $column[pid] = ".pnVarPrepForStore($tid)."
                                ORDER BY $column[date], $column[tid]");

    while(list($r_tid, $r_pid, $r_pollID, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
        $r_date=$result->UnixTimeStamp($r_date);
        $result->MoveNext();
        if (!isset($level)) {
        } else {
            if (!$comments) {
                echo "<ul>";
            }
        }
        $comments++;
        if(!eregi("[a-z0-9]",$r_name)) {
        $r_name = $anonymous;
    }
        if(!eregi("[a-z0-9]",$r_subject)) {
        $r_subject = "["._NOSUBJECT."]";
    }
        $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
        echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=showreply&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">$r_subject</a><font class=\"pn-normal\"> "._BY." $r_name "._ON." $datetime<br>";
        DisplayBabies($r_tid, $level+1, $dummy+1);
    }
    if ($level && $comments) {
        echo "</ul>";
    }
}

function DisplayTopic ($pollID, $pid=0, $tid=0, $mode="thread", $order=0, $thold=0, $level=0, $nokids=0)
{
    global $hr, $mainfile, $subject;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $commentlimit = pnConfigGetVar('commentlimit');
    $anonymous = pnConfigGetVar('anonymous');
    $reasons = pnConfigGetVar('reasons');
    $anonpost = pnConfigGetVar('anonpost');

    if($mainfile) {
        global $title;
    } else {
        global $title;
        include("header.php");
    }
    if ($pid!=0) {
        include("header.php");
    }
    $count_times = 0;

    $noshowscore = pnUserGetVar('noscore');
    $commentsmax = pnUserGetVar('commentmax');

    $column = &$pntable['pollcomments_column'];
    $q = "SELECT $column[tid], $column[pid], $column[pollid],
            $column[date], $column[name], $column[email],
            $column[url], $column[host_name], $column[subject], $column[comment],
            $column[score], $column[reason]
          FROM $pntable[pollcomments]
          WHERE $column[pollid]=".pnVarPrepForStore($pollID)." AND $column[pid]=".pnVarPrepForStore($pid)."";
    if($thold != "") {
        $q .= " AND $column[score]>=$thold";
    } else {
        $q .= " AND $column[score]>=0";
    }
    if ($order==1) $q .= " ORDER BY $column[date] DESC";
    if ($order==2) $q .= " ORDER BY $column[score] DESC";
    $something = $dbconn->Execute("$q");
    $num_tid = $something->PO_RecordCount();
    navbar($pollID, $title, $thold, $mode, $order);
    echo "<div align=\"left\">";
    modone();
    while ($count_times < $num_tid) {
        list($tid, $pid, $pollID, $date, $name, $email, $url, $host_name, $subject, $comment, $score, $reason) = $something->fields;
        $date=$something->UnixTimeStamp($date);
        $something->MoveNext();
        if ($name == "") { $name = $anonymous; }
        if ($subject == "") { $subject = "["._NOSUBJECT."]"; }

        echo "<a name=\"$tid\">";
        echo "<table width=\"99%\" border=\"0\"><tr bgcolor=\"".$GLOBALS['bgcolor1']."\"><td width=500>";
        //formatTimestamp($date);
        $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($date));
        if ($email) {
            echo "<p><font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font> <font class=\"pn-normal\">";
            if(!$noshowscore && $modoption ) {
                echo "("._SCORE." ".pnVarPrepForDisplay($score);
                if($reason>0) echo ", ".pnVarPrepForDisplay($reasons[$reason]);
                echo ")";
            }
            echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$email\">".pnVarPrepForDisplay($name)."</a> ($email) "._ON." $datetime";
        } else {
            echo "<p><font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font> <font class=\"pn-normal\">";
            if(!$noshowscore && $modoption ) {
                echo "("._SCORE." ".pnVarPrepForDisplay($score);
                if($reason>0) echo ", ".pnVarPrepForDisplay($reasons[$reason]);
                echo ")";
            }
            echo "<br>"._BY." ".pnVarPrepForDisplay($name)." "._ON." $datetime";
        }

    // If you are admin you can see the Poster IP address (you have this right, no?)
    // with this you can see who is flaming you... ha-ha-ha

        if ($name != $anonymous) { echo "<br>(<a class=\"pn-normal\" href=\"user.php?op=userinfo&uname=$name\">"._USERINFO."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$name\">"._SENDAMSG."</a>) "; }
        if (eregi("http://",$url)) { echo "<a class=\"pn-normal\" href=\"$url\" target=\"window\">$url</a> "; }

        if (pnSecAuthAction(0, 'Comments::', '::', ACCESS_ADMIN)) {
            $column = &$pntable['pollcomments_column'];
            $result= $dbconn->Execute("SELECT $column[host_name]
                                     FROM $pntable[pollcomments]
                                     WHERE $column[tid]='".pnVarPrepForStore($tid)."'");
            list($host_name) = $result->fields;
            echo "<br><font class=\"pn-normal\">(IP: $host_name)</font>";
        }

        echo "</font></td></tr><tr><td>";
        if(($commentsmax) && (strlen($comment) > $commentsmax)) echo substr("$comment", 0, $commentsmax)."<br><br><b><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$pollID&amp;tid=$tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a></b>";
        elseif(strlen($comment) > $commentlimit) echo substr("$comment", 0, $commentlimit)."<br><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$pollID&amp;tid=$tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a>";
        else echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comment)."</font>";
        echo "</td></tr></table><br><p>";
        echo "<div align=\"center\">";
        if ($anonpost==1 OR pnUserLoggedIn()) {
            echo "<font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=Reply&amp;pid=$tid&amp;pollID=$pollID&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>";
        } else {
            echo "[ "._NOANONCOMMENTS." ";
        }
        if ($pid != 0) {
            $column = &$pntable['pollcomments_column'];
            $result = $dbconn->Execute("SELECT $column[pid]
                                      FROM $pntable[pollcomments]
                                      WHERE $column[tid]=".pnVarPrepForStore($pid)."");

            list($erin) = $result->fields;
            echo "| <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;pollID=$pollID&amp;pid=$erin&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._PARENT."</a>";
        }
        modtwo($tid, $score, $reason);

        if(pnSecAuthAction(0, 'Comments::', '::', ACCESS_DELETE)) {
            echo " | <a class=\"pn-normal\" href=\"admin.php?module=NS-Comments&amp;op=RemovePollComment&tid=$tid&pollID=$pollID&ok=\">"._DELETE."</a> ]</font><p>";
        } else {
            echo " ]</font><p>";
        }
        echo "</div>";
        DisplayKids($tid, $mode, $order, $thold, $level);
        echo "</ul>";
        if($hr) echo "<hr noshade size=1>";
        echo "</p>";
        echo "</div>";
        $count_times += 1;
    }
    modthree($pollID, $mode, $order, $thold);
    if($pid==0) return array($pollID, $pid, $subject);
    else include("footer.php");
}

function singlecomment($tid, $pollID, $mode, $order, $thold)
{
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonpost = pnConfigGetVar('anonpost');
    $anonymous = pnConfigGetVar('anonymous');

    $column = &$pntable['pollcomments_column'];
    $deekayen = $dbconn->Execute("SELECT $column[date], $column[name],
                                  $column[email], $column[url], $column[subject],
                                  $column[comment], $column[score], $column[reason]
                                FROM $pntable[pollcomments]
                                WHERE $column[tid]=".pnVarPrepForStore($tid)." AND $column[pollid]=".pnVarPrepForStore($pollID)."");

    list($date, $name, $email, $url, $subject, $comment, $score, $reason) = $deekayen->fields;
    $date=$deekayen->UnixTimeStamp($date);
    $titlebar = "<font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font>";
    if($name == "") $name = $anonymous;
    if($subject == "") $subject = "["._NOSUBJECT."]";
    modone();
    echo "<table width=\"99%\" border=\"0\"><tr bgcolor=\"".$GLOBALS['bgcolor1']."\"><td width=500>";
    $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($date));
    if($email) echo "<p><font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font> <font class=\"pn-normal\">("._SCORE." $score)<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$email\"><font class=\"pn-normal\">".pnVarPrepForDisplay($name)."</font></a> <font class=\"pn-normal\">(".pnVarPrepForDisplay($email)."</font> "._ON." $datetime";
    else echo "<p><font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font> <font class=\"pn-normal\">("._SCORE." $score)<br>"._BY." $name "._ON." $datetime";
    echo "</td></tr><tr><td><font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comment)."</font></td></tr></table><br><p><font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments&amp;req=Reply&amp;pid=$tid&amp;pollID=$pollID&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;pollID=$pollID\">"._ROOT."</a>";
    modtwo($tid, $score, $reason);
    echo " ]";
    modthree($pollID, $mode, $order, $thold);
    include("footer.php");
}

function reply($pid, $pollID, $mode, $order, $thold)
{
    include("header.php");
    list($comment) = pnVarCleanFromInput('comment');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    $anonymous = pnConfigGetVar('anonymous');

    if($pid!=0) {
        $column = &$pntable['pollcomments_column'];
        $result = $dbconn->Execute("SELECT $column[date], $column[name], $column[email],
                                    $column[url], $column[subject], $column[comment],
                                    $column[score]
                                  FROM $pntable[pollcomments]
                                  WHERE $column[tid]=".pnVarPrepForStore($pid)."");

        list($date, $name, $email, $url, $subject, $comment, $score) = $result->fields;
    } else {
        $column = &$pntable['poll_desc_column'];
        $result =  $dbconn->Execute("SELECT $column[polltitle]
                                   FROM $pntable[poll_desc]
                                   WHERE $column[pollid]=".pnVarPrepForStore($pollID)."");

        list($subject) = $result->fields;
    }
    $titlebar = "<font class=\"pn-normal\">.".pnVarPrepForDisplay($subject)."</font>";
    if($subject == "") $subject = "["._NOSUBJECT."]";
    //formatTimestamp($date);
    OpenTable();
    echo "<center><font class=\"pn-title\">"._POLLCOM."</font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</center><br>";
    if ($comment == "") {
        echo "<center>"._DIRECTCOM."</font></center><br>";
    } else {
        echo "<br>".pnVarPrepHTMLDisplay($comment)."</font>";
    }
    CloseTable();
    if(!isset($pid) || !isset($pollID)) { echo "Something is not right. This message is just to keep things from messing up down the road"; exit(); }

    OpenTable();
    echo "<form action=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments\" method=post>";
    echo "<font class=\"pn-title\">"._YOURNAME.":</font> ";
    if (pnUserLoggedIn()) {
        echo "<font class=\"pn-normal\"><a class=\"pn-normal\" href=\"user.php\">" . pnVarPrepForDisplay(pnUserGetVar('uname')) . "</a> [ <a class=\"pn-normal\" href=\"user.php?module=NS-User&amp;op=logout\">"._LOGOUT."</a> ]</font>";
    } else {
        echo "<font class=\"pn-normal\">" . pnVarPrepForDisplay($anonymous)."</font>";
        $xanonpost=1;
    }
    echo "<br><br><font class=\"pn-title\">"._SUBJECT.":</font><br>";
    if (!eregi("Re:",$subject)) $subject = "Re: ".substr($subject,0,81)."";
    echo "<INPUT TYPE=\"text\" NAME=\"subject\" SIZE=50 maxlength=85 value=\"" . pnVarPrepForDisplay($subject)."\"><BR>";
    echo "<br><br><font class=\"pn-title\">"._COMMENT.":</font><br>"
        ."<TEXTAREA wrap=\"virtual\" cols=\"50\" rows=\"10\" name=\"comment\"></TEXTAREA><br>
        <font class=\"pn-normal\">"._ALLOWEDHTML."<br>";
        while (list($key, $access, ) = each($AllowableHTML)) {
            if ($access > 0) echo " &lt;".$key."&gt;";
        }
        echo "<br>";
    if (pnUserLoggedIn()) {
        echo "<INPUT type=checkbox name=xanonpost> "._POSTANON."<br>";
    }
    echo "<INPUT type=\"hidden\" name=\"pid\" value=\"$pid\">"
        ."<INPUT type=\"hidden\" name=\"pollID\" value=\"$pollID\">"
        ."<INPUT type=\"hidden\" name=\"mode\" value=\"$mode\">"
        ."<INPUT type=\"hidden\" name=\"order\" value=\"$order\">"
        ."<INPUT type=\"hidden\" name=\"thold\" value=\"$thold\">"
        ."<INPUT type=submit name=req value=\""._PREVIEW."\">"
        ."<INPUT type=submit name=req value=\""._OK."\">"
        ."<SELECT name=\"posttype\">"
        ."<OPTION value=\"exttrans\">"._EXTRANS."</option>"
        ."<OPTION value=\"html\" >"._HTMLFORMATED."</option>"
        ."<OPTION value=\"plaintext\" SELECTED>"._PLAINTEXT."</option>"
        ."</SELECT>"
        ."</FORM>";
    CloseTable();
    include("footer.php");
}

function replyPreview ($pid, $pollID, $subject, $comment, $xanonpost, $mode, $order, $thold, $posttype)
{
    include 'header.php';

    if(!isset($xanonpost)) {
    $xanonpost = '';
    }
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    $anonymous = pnConfigGetVar('anonymous');

    $subject = stripslashes($subject);
    $comment = stripslashes($comment);
    if (!isset($pid) || !isset($pollID)) {
        echo "<font class=\"pn-normal\">"._NOTRIGHT."</font>";
        exit();
    }
    OpenTable();
    echo "<center><font class=\"pn-title\">"._POLLCOMPRE."</font></center>";
    CloseTable();

    OpenTable();
    echo "<font class=\"pn-normal\">".pnVarPrepForDisplay($subject)."</font><br>";
    echo "<font class=\"pn-normal\">"._BY." ";
    if (pnUserLoggedIn()) {
        echo pnVarPrepForDisplay(pnUserGetVar('uname'));
    } else {
        echo "$anonymous ";
    }
    echo "&nbsp;"._ONN."</font><br><br>";
    if ($posttype=="exttrans") {
        echo "<font class=\"pn-normal\">".nl2br(htmlspecialchars($comment))."</font>";
    } elseif ($posttype=="plaintext") {
        echo "<font class=\"pn-normal\">".nl2br(pnVarPrepForDisplay($comment))."</font>";
    } else {
        echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comment)."</font>";
    }
    CloseTable();

    OpenTable();
    echo "<form action=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=comments\" method=post>"
        ."<font class=\"pn-title\">"._YOURNAME.":</font> ";
    if (pnUserLoggedIn()) {
        echo "<font class=\"pn-normal\"><a class=\"pn-normal\" href=\"user.php\">" . pnVarPrepForDisplay(pnUserGetVar('uname')) . "</a> <font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"user.php?op=logout\">"._LOGOUT."</a> ]</font>";
    } else {
        echo "<font class=\"pn-normal\">$anonymous</font>";
    }
    echo "<br><br><font class=\"pn-title\">"._SUBJECT.":</font><br>"
        ."<INPUT TYPE=\"text\" NAME=\"subject\" SIZE=50 maxlength=85 value=\"$subject\"><br><br>"
        ."<P><font class=\"pn-title\">"._COMMENT.":</FONT><BR>"
        ."<TEXTAREA wrap=\"virtual\" cols=\"50\" rows=\"10\" name=\"comment\">$comment</TEXTAREA><br>";
        echo"<font class=\"pn-normal\">"._ALLOWEDHTML."<br>";
        while (list($key, $access, ) = each($AllowableHTML)) {
            if ($access > 0) echo " &lt;".$key."&gt;";
        }
        echo "<br>";
    if ($xanonpost == 1) {
        echo "<INPUT type=checkbox name=xanonpost checked> "._POSTANON."<br>";
    } elseif (pnUserLoggedIn()) {
        echo "<INPUT type=checkbox name=xanonpost> "._POSTANON."<br>";
    }
    echo "<INPUT type=\"hidden\" name=\"pid\" value=\"$pid\">"
        ."<INPUT type=\"hidden\" name=\"pollID\" value=\"$pollID\"><INPUT type=\"hidden\" name=\"mode\" value=\"$mode\">"
        ."<INPUT type=\"hidden\" name=\"order\" value=\"$order\"><INPUT type=\"hidden\" name=\"thold\" value=\"$thold\">"
        ."<INPUT type=submit name=req value=\""._PREVIEW."\">"
        ."<INPUT type=submit name=req value=\""._OK."\"> <SELECT name=\"posttype\"><OPTION value=\"exttrans\"";
        if($posttype=="exttrans") echo" SELECTED";
        echo  ">"._EXTRANS."<OPTION value=\"html\"";;
        if($posttype=="html") echo" SELECTED";
        echo ">"._HTMLFORMATED."<OPTION value=\"plaintext\"";
        if(($posttype!="exttrans") && ($posttype!="html")) echo" SELECTED";
        echo ">"._PLAINTEXT."</SELECT></FORM>";
    CloseTable();
    include("footer.php");
}

function CreateTopic ($xanonpost, $subject, $comment, $pid, $pollID, $host_name, $mode, $order, $thold, $posttype)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    //$author not used - skooter
    //$author = FixQuotes($author);
    $subject = FixQuotes(filter_text($subject, "nohtml"));
    if ($posttype=="exttrans") {
        $comment = FixQuotes(nl2br(htmlspecialchars(check_words_poll($comment))));
    } elseif ($posttype=="plaintext") {
        $comment = FixQuotes(nl2br(filter_text($comment)));
    } else {
        $comment = FixQuotes(filter_text($comment));
    }

    if (pnUserLoggedIn() && (!isset($xanonpost))) {
        $name = pnUserGetVar('uname');
        $email = pnUserGetVar('femail');
        $url = pnUserGetVar('url');
        $score = 1;
    } else {
        $name = ""; $email = ""; $url = "";
        $score = 0;
    }

    $ip = getenv("REMOTE_HOST");
    if (empty($ip)) {
        $ip = getenv("REMOTE_ADDR");
    }

    // default $pid if it is not set
    if (!$pid) $pid=0;

//begin fake thread control
    $column = &$pntable['poll_desc_column'];
    $result = $dbconn->Execute("SELECT COUNT(*)
                              FROM $pntable[poll_desc]
                              WHERE $column[pollid]=".pnVarPrepForStore($pollID)."");

    list($fake) = $result->fields;

//begin duplicate control
    $column = &$pntable['pollcomments_column'];
    $result = $dbconn->Execute("SELECT COUNT(*)
                              FROM $pntable[pollcomments]
                              WHERE $column[pid]='".pnVarPrepForStore($pid)."' AND $column[pollid]='".pnVarPrepForStore($pollID)."'
                                AND $column[subject]='".pnVarPrepForStore($subject)."'
                                AND $column[comment]='".pnVarPrepForStore($comment)."'");
    //added following line to fix duplicate comment bug. - Skooter
    list($tia) = $result->fields;
//begin troll control
    if(pnUserLoggedIn()) {
        $column = &$pntable['pollcomments_column'];
        $result = $dbconn->Execute("SELECT COUNT(*)
                               FROM $pntable[pollcomments]
                               WHERE ($column[score]=-1)
                                 AND ($column[name]='" . pnUserGetVar('uname') . "'
                                 AND (to_days(now()) - to_days($column[date]) < 3)");

        list($troll) = $result->fields;
    } elseif(!$score) {
        $column = &$pntable['pollcomments_column'];
        $result = $dbconn->Execute("SELECT COUNT(*)
                                  FROM $pntable[pollcomments]
                                  WHERE ($column[score]=-1)
                                    AND ($column[host_name]='".pnVarPrepForStore($ip)."')
                                    AND (to_days(now()) - to_days($column[date]) < 3)");

        list($troll) = $result->fields;
    }
    if((!$tia) && ($fake == 1) && ($troll < 6)) {
        $column = &$pntable['pollcomments_column'];
        $nextid = $dbconn->GenId($pntable['pollcomments']);
        $result = $dbconn->Execute("INSERT INTO $pntable[pollcomments] ($column[tid],
                                    $column[pid], $column[pollid], $column[date],
                                    $column[name], $column[email], $column[url],
                                    $column[host_name], $column[subject],
                                    $column[comment], $column[score], $column[reason])
                                    VALUES ($nextid, '".pnVarPrepForStore($pid)."', '".pnVarPrepForStore($pollID)."', now(), '".pnVarPrepForStore($name)."',
                                      '".pnVarPrepForStore($email)."', '".pnVarPrepForStore($url)."', '".pnVarPrepForStore($ip)."', '".pnVarPrepForStore($subject)."', '".pnVarPrepForStore($comment)."',
                                      '".pnVarPrepForStore($score)."', '0')");
        if($dbconn->ErrorNo()<>0)
        {
            error_log("Error: creating pollcomments, " . $dbconn->ErrorMsg);
        }
    } else {
        include("header.php");
        if($tia) echo "<font class=\"pn-normal\">"._DUPLICATE."<br><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;req=results&pollID=$pollID\">Back to Poll</a>";
        elseif($troll > 5) echo _TROLLMESSAGE."<br><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;pollID=$pollID\">Back to Poll</a>";
        elseif($fake == 0) echo _TOPICMISSING."</font>";
        include("footer.php");
        exit;
    }
    pnRedirect("modules.php?op=modload&name=".$GLOBALS['name']."&file=index&req=results&pollID=".$pollID);

}


list($req, $pid, $pollID, $mode, $order, $thold) = 
    pnVarCleanFromInput('req','pid','pollID','mode','order','thold');
    
if(!isset($req)) {
    $req = '';
}

switch($req) {

    case "Reply":
        reply($pid, $pollID, $mode, $order, $thold);
        break;

    case ""._PREVIEW."":
        list($xanonpost,$subject,$comment,$posttype) = 
            pnVarCleanFromInput('xanonpost','subject','comment','posttype');
		if(empty($xanonpost)) {
			$xanonpost = 0;
		}
		replyPreview ($pid, $pollID, $subject, $comment, $xanonpost, $mode, $order, $thold, $posttype);
		break;

    case ""._OK."":
        list($xanonpost, $subject, $comment, $host_name, $posttype) = 
            pnVarCleanFromInput('xanonpost','subject','comment','host_name','posttype');
        CreateTopic($xanonpost, $subject, $comment, $pid, $pollID, $host_name, $mode, $order, $thold, $posttype);
        break;

    case "moderate":
		if(!isset($moderate)) {
			$moderate = 2;
		}
        if($moderate == 2) {
            while(list($tdw, $emp) = each($HTTP_POST_VARS)) {
                if (eregi("dkn",$tdw)) {
                    $emp = explode(":", $emp);
                    if($emp[1] != 0) {
                        $tdw = ereg_replace("dkn", "", $tdw);
                        $column = &$pntable['pollcomments_column'];
                        $q = "UPDATE $pntable[pollcomments] SET";
                        if(($emp[1] == 9) && ($emp[0]>=0)) { # Overrated
                            $q .= " $column[score]=$column[score]-1 WHERE $column[tid]=".pnVarPrepForStore($tdw)."";
                        } elseif (($emp[1] == 10) && ($emp[0]<=4)) { # Underrated
                            $q .= " $column[score]=$column[score]+1 WHERE $column[tid]=".pnVarPrepForStore($tdw)."";
                        } elseif (($emp[1] > 4) && ($emp[0]<=4)) {
                            $q .= " $column[score]=$column[score]+1, $column[reason]=$emp[1] WHERE $column[tid]=".pnVarPrepForStore($tdw)."";
                        } elseif (($emp[1] < 5) && ($emp[0] > -1)) {
                            $q .= " $column[score]=$column[score]-1, $column[reason]=$emp[1] WHERE $column[tid]=".pnVarPrepForStore($tdw)."";
                        } elseif (($emp[0] == -1) || ($emp[0] == 5)) {
                            $q .= " $column[reason]=$emp[1] WHERE $column[tid]=".pnVarPrepForStore($tdw)."";
                        }
                        if(strlen($q) > 20) $dbconn->Execute($q);
                    }
                }
            }
        }
		pnRedirect("modules.php?op=modload&name=".$GLOBALS['name']."&file=index&req=results&pollID=$pollID");
        break;

    case "showreply":
        DisplayTopic($pollID, $pid, $tid, $mode, $order, $thold);
        break;

    default:
        list ($tid, $pid) = pnVarCleanFromInput('tid','pid');
        if ((!empty($tid)) && (empty($pid))) {
            singlecomment($tid, $pollID, $mode, $order, $thold);
        } elseif (($mainfile) xor (($pid==0) AND (!isset($pid)))) {
            pnRedirect("modules.php?op=modload&name=".$GLOBALS['name']."&file=index&req=results&pollID=$pollID&mode=$mode&order=$order&thold=$thold");
        } else {
            if(!isset($pid)) $pid=0;
            DisplayTopic($pollID, $pid, $tid, $mode, $order, $thold);
        }
        break;
}

function check_words_poll($Message)
{
    $CensorMode = pnConfigGetVar('CensorMode');
    $CensorList = pnConfigGetVar('CensorList');
    $CensorReplace = pnConfigGetVar('CensorReplace');

    $EditedMessage = $Message;
    if ($CensorMode != 0) {

        if (is_array($CensorList)) {
            $Replace = $CensorReplace;
            if ($CensorMode == 1) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = eregi_replace("$CensorList[$i]([^a-zA-Z0-9])","$Replace\\1",$EditedMessage);
                }
            } elseif ($CensorMode == 2) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = eregi_replace("(^|[^[:alnum:]])$CensorList[$i]","\\1$Replace",$EditedMessage);
                }
            } elseif ($CensorMode == 3) {
                for ($i = 0; $i < count($CensorList); $i++) {
                    $EditedMessage = eregi_replace("$CensorList[$i]","$Replace",$EditedMessage);
                }
            }
        }
    }
    return ($EditedMessage);
}
?>
