<?php
// File: $Id: index.php,v 1.8 2002/11/20 18:15:11 larsneo Exp $ $Name:  $
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
// changelog: 2002/11/19 (larsneo) 
// switched backed to pnVarPrepHTMLdisplay, credits for bringing 
// a potential xss exploit to our attention goes to stephan ehlert
// uncommented the extrans option since we don't store the texttype in db
// should be restored for .725. set default to html for visual editor

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = $GLOBALS['name'];
modules_get_language();

include_once('modules/News/funcs.php');

function modtwo($tid, $score, $reason)
{
    $reasons = pnConfigGetVar('reasons');

    echo " | <select name=dkn$tid>";
    for($i=0; $i<sizeof($reasons); $i++) {
        echo "<option value=\"$score:$i\">" . pnVarPrepForDisplay($reasons[$i]) . "</option>\n";
    }
    echo "</select>";
}

function modthree($sid, $mode, $order, $thold=0)
{
    echo "<center><input type=\"hidden\" name=\"sid\" value=\"$sid\">\n"
        ."<input type=\"hidden\" name=\"mode\" value=\"$mode\">\n"
        ."<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        ."<input type=\"hidden\" name=\"thold\" value=\"$thold\">\n"
        ."<input type=\"hidden\" name=\"req\" value=\"moderate\">\n"
        ."<input type=\"submit\" value=\""._MODERATE."\">\n"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
        ."<input type=\"hidden\" name=\"name\" value=\"NS-Comments\">\n"
        ."<input type=\"hidden\" name=\"file\" value=\"index\">\n"
        ."</form></center>\n";
}

function navbar($info, $sid, $title, $thold, $mode, $order)
{
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    $textcolor1 = $GLOBALS['textcolor1'];
    $textcolor2 = $GLOBALS['textcolor2'];
    $pid = pnVarCleanFromInput('pid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonpost = pnConfigGetVar('anonpost');

    $result = $dbconn->Execute("SELECT count({$pntable['comments_column']['sid']})
                                                         FROM $pntable[comments]
                                                         WHERE {$pntable['comments_column']['sid']}=".pnVarPrepForStore($sid)."");
    list($count) = $result->fields;
    if(!isset($thold)) {
        $thold=0;
    }
    echo "\n\n<!-- COMMENTS NAVIGATION BAR START -->\n\n";
    echo "<table width=\"99%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
    if (!empty($info['title'])) {
        echo "<tr><td bgcolor=\"$bgcolor2\" align=\"center\"><font class=\"pn-title\">" . pnVarPrepHTMLDisplay($info[title]) . "</font><font class=\"pn-normal\"> | </font>";
    }
    if(pnUserLoggedIn()) {
        echo "<a class=\"pn-normal\" href=\"user.php?op=editcomm\">"._CONFIGURE."</a>";
    } else {
        echo "<a class=\"pn-normal\" href=\"user.php\">"._LOGINCREATE."</a>";
    }
    if(($count==1)) {
        echo "<font class=\"pn-normal\"> | ".pnVarPrepForDisplay($count)." "._COMMENT."</font></td></tr>\n";
    } else {
        echo "<font class=\"pn-normal\"> | ".pnVarPrepForDisplay($count)." "._COMMENTS."</font></td></tr>\n";
    }
    echo "<tr><td bgcolor=\"$bgcolor1\" align=\"center\" width=\"100%\">\n"
        ."<table border=\"0\"><tr><td>\n"
        ."<form method=\"post\" action=\"modules.php\">\n";
    if (pnConfigGetVar('moderate')) {    // no point in selecting threshold if site has no moderation
        echo "<font class=\"pn-normal\">"._THRESHOLD."</font> <select name=\"thold\">\n"
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
        echo ">5</option>\n"
         ."</select>";
    }   else {
        echo "<input type=\"hidden\" name=\"thold\" value=\"0\">\n"; // I'm not sure it should be zero here but should be ok
    }
    echo "<select name=mode>"
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
    if ($order == 1) {
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
         ."<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
         ."<input type=\"hidden\" name=\"name\" value=\"News\">\n"
         ."<input type=\"hidden\" name=\"file\" value=\"article\">\n"
         ."<input type=\"hidden\" name=\"sid\" value=\"$sid\">\n"
         ."<input type=\"submit\" value=\""._REFRESH."\"></form>\n";
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        echo "</td><td bgcolor=\"$bgcolor1\"><form action=\"modules.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"op\" value=\"modload\">"
            ."<input type=\"hidden\" name=\"name\" value=\"NS-Comments\">"
            ."<input type=\"hidden\" name=\"file\" value=\"index\">"
            ."<input type=\"hidden\" name=\"pid\" value=\"$pid\">"
            ."<input type=\"hidden\" name=\"sid\" value=\"$sid\">"
            ."<input type=\"hidden\" name=\"req\" value=\"Reply\">"
            ."&nbsp;&nbsp;<input type=\"submit\" value=\""._REPLYMAIN."\">";
    }
    echo "</form></td></tr></table>\n"
        ."</td></tr>"
        ."<tr><td bgcolor=\"$bgcolor2\" align=\"center\"><font class=\"pn-sub\">"._COMMENTSWARNING."</font></td></tr>\n"
        ."</table>"
        ."\n\n<!-- COMMENTS NAVIGATION BAR END -->\n\n";
}

function DisplayKids ($tid, $mode, $order=0, $thold=0, $level=0, $dummy=0, $tblwidth=99)
{
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    list($info) = pnVarCleanFromInput('info');
    
    $modoption= pnConfigGetVar('moderate');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $reasons = pnConfigGetVar('reasons');
    $anonymous = pnConfigGetVar('anonymous');
    $anonpost = pnConfigGetVar('anonpost');
    $commentlimit = pnConfigGetVar('commentlimit');

    $comments = 0;

    $noshowscore = pnUserGetVar('noscore');
    $maxcomments = pnUserGetVar('commentmax');

    $column = &$pntable['comments_column'];

    $sql = "SELECT $column[tid], $column[pid], $column[sid],
                              $column[date],
                              $column[name], $column[email], $column[url],
                              $column[host_name], $column[subject], $column[comment],
                              $column[score], $column[reason]
                              FROM $pntable[comments]
                              WHERE $column[pid] = ".pnVarPrepForStore($tid)."
                              ORDER BY $column[tid]";
    // 2 most popular first
    // 1 newest first
    // 0 - oldest first

    if($order==1) {
    	$sql .= ", $column[date] DESC";
    }
    if($order==0) {
    	$sql .= ", $column[date] ASC";
    }
    if($order==2) {
    	$sql .= ", $column[score] DESC";
    }
    $result = $dbconn->Execute($sql);

    if ($mode == 'nested') {
        /* without the tblwidth variable, the tables run of the screen with netscape */
        /* in nested mode in long threads so the text can't be read. */
        while(list($r_tid, $r_pid, $r_sid, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
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
                if(!eregi("[a-z0-9]",$r_name)) {
            		$r_name = $anonymous;
        	}
                if(!eregi("[a-z0-9]",$r_subject)) {
            		$r_subject = "["._NOSUBJECT."]";
        	}
                
                // HIJO enter hex color between first two appostrophe for second alt bgcolor
                echo "dummy: $dummy<br>";
                $r_bgcolor = ($dummy%2)?"$bgcolor1":"$bgcolor2";
                echo "<a name=\"$r_tid\">";
                echo "<table border=\"0\"><tr bgcolor=\"$r_bgcolor\"><td>";
                $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
                if ($r_email) {
                    echo "<font class=\"pn-title\">".pnVarPrepForDisplay($r_subject)."</font> <font class=\"pn-normal\">";

                    if (!$noshowscore && $modoption ) {
                        echo "("._SCORE." $r_score";
                        if($r_reason>0) echo ", ".pnVarPrepForDisplay($reasons[$r_reason])."";
                        echo ")";
                    }
                    echo "<br><font class=\"pn-normal\"> "._BY." </font><a class=\"pn-normal\" href=\"mailto:$r_email\">".pnVarPrepForDisplay($r_name)."</a> <font class=\"pn-normal\">(".pnVarPrepForDisplay($r_email).")</font><font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
                } else {
                    echo "<font class=\"pn-title\">".pnVarPrepForDisplay($r_subject)."</font>";
                    if (!$noshowscore && $modoption ) {
                        echo "<font class=\"pn-normal\">("._SCORE." ".pnVarPrepForDisplay($r_score)."";
                        if($r_reason>0) echo ", ".pnVarPrepForDisplay($reasons[$r_reason])."";
                        echo ")</font>";
                    }
                    echo "<br><font class=\"pn-normal\">"._BY." ".pnVarPrepForDisplay($r_name)." "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
                }
                if ($r_name != $anonymous) { 
                	echo "<br><font class=\"pn-normal\">(<a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$r_name&amp;module=NS-User\">"._USERINFO."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$r_name\">"._SENDAMSG."</a>) "; 
                }
                if (eregi("http://",$r_url)) { 
                	echo "<a class=\"pn-normal\" href=\"$r_url\" target=\"window\">$r_url</a> "; 
                }
                echo "</font></td></tr><tr><td>";
                if(($maxcomments) && (strlen($r_comment) > $maxcomments)) {
                	echo substr("$r_comment", 0, $maxcomments)."<br><br><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$r_sid&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a>";
                } elseif(strlen($r_comment) > $commentlimit) {
                	echo substr("$r_comment", 0, $commentlimit)."<br><br><b><a class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$r_sid&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a>";
                }
                else echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($r_comment)."</font>";
                echo "</td></tr></table><br><br>";
                if ($anonpost==1 OR pnUserLoggedIn()) {
                    echo "<div align=\"center\"><font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=Reply&amp;pid=$r_tid&amp;sid=$r_sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>";
                } else {
                    echo "[ "._NOANONCOMMENTS." ";
                }
                if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_MODERATE)) {
                    if ($modoption) modtwo($r_tid, $r_score, $r_reason);
                }
                if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_DELETE)) {
                    echo " | <a class=\"pn-normal\" href=\"admin.php?module=NS-Comments&amp;op=RemoveComment&amp;tid=$r_tid&amp;sid=$r_sid\">" ._DELETE."</a>";
                }
                echo " ]</font></div><br><br>\n";
                DisplayKids($r_tid, $mode, $order, $thold, $level+1, $dummy+1, $tblwidth);
            }
        }
    } elseif ($mode == 'flat') {
    	while(list($r_tid, $r_pid, $r_sid, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
        	$r_date=$result->UnixTimeStamp($r_date);
                $result->MoveNext();
                if($r_score >= $thold) {
                    	if (!eregi("[a-z0-9]",$r_name)) {
            			$r_name = $anonymous;
            		}
                    	if (!eregi("[a-z0-9]",$r_subject)) {
            			$r_subject = "<font class=\"pn-normal\">["._NOSUBJECT."]</font>";
            		}
                    	echo "<a name=\"$r_tid\">";
                    	echo "<hr><table width=\"99%\" border=\"0\"><tr bgcolor=\"$bgcolor1\"><td>";
                    	$datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
                    	if ($r_email) {
                        	echo "<font class=\"pn-title\">".pnVarPrepForDisplay($r_subject)."</font><font class=\"pn-normal\">";
                        	if (!$noshowscore && $modoption ) {
                            		echo "("._SCORE." ".pnVarPrepForDisplay($r_score)."";
                            		if($r_reason>0) echo ", ".pnVarPrepForDisplay($reasons[$r_reason])."";
                            		echo ")</font>";
                        	}
                        	echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$r_email\">".pnVarPrepForDisplay($r_name)."</a> <font class=\"pn-normal\">(".pnVarPrepForDisplay($r_email).")</font><font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
                     	} else {
                        	echo "<font class=\"pn-title\">".pnVarPrepForDisplay($r_subject)."</font> <font class=\"pn-normal\">";
                        	if (!$noshowscore && $modoption ) {
                            		echo "("._SCORE." ".pnVarPrepForDisplay($r_score)."";
                            		if($r_reason>0) echo ", ".pnVarPrepForDisplay($reasons[$r_reason])."";
                            		echo ")";
                        	}
                        	echo "<br>"._BY." ".pnVarPrepForDisplay($r_name)." "._ON." ".pnVarPrepForDisplay($datetime)."";
                    	}
                    	if ($r_name != $anonymous) {
				echo "<br><font class=\"pn-normal\">(<a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$r_name&amp;module=NS-User\">"._USERINFO."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$r_name\">"._SENDAMSG."</a>)</font> ";
            		}
                    	if (eregi("http://",$r_url)) {
            			echo "<a class=\"pn-normal\" href=\"$r_url\" target=\"window\">".pnVarPrepForDisplay($r_url)."</a> ";
            		}
                    	echo "</font></td></tr><tr><td><font class=\"pn-normal\">";
                    	if(($maxcomments) && (strlen($r_comment) > $maxcomments)) {
                    		echo substr("$r_comment", 0, $maxcomments)."<br><br><a  class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$r_sid&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a>";
                    	} elseif(strlen($r_comment) > $commentlimit) {
                    		echo substr("$r_comment", 0, $commentlimit)."<br><br><b><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$r_sid&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._READREST."</a>";
                    	} else {
                    		echo $r_comment;
                    	}
                    	echo "</font></td></tr></table><br><br>";
                    	if ($anonpost==1 OR pnUserLoggedIn()) {
	                        echo "<div align=\"center\"><font class=\"pn-normal\"> [ <a class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=Reply&amp;pid=$r_tid&amp;sid=$r_sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>";
                    	} else {
                        	echo "[ "._NOANONCOMMENTS." ";
                    	}
                    	if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_MODERATE)) {
                        	if ($modoption) modtwo($r_tid, $r_score, $r_reason);
                    	}
                    	if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_DELETE)) {
                        	echo " | <a class=\"pn-normal\" href=\"admin.php?module=NS-Comments&amp;op=RemoveComment&amp;tid=$r_tid&amp;sid=$r_sid\">"
                        	._DELETE."</a>";
                    	}
                    	echo " ]</font></div><br><br>";
                    	DisplayKids($r_tid, $mode, $order, $thold);
                }
	}
    } else {
	while(list($r_tid, $r_pid, $r_sid, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
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
                    echo "<li><font class=\"pn-normal\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=showreply&amp;tid=$r_tid&amp;sid=$r_sid&amp;pid=$r_pid&amp;mode=$mode&amp;order=$order&amp;thold=$thold#$r_tid\">$r_subject</a></font><font class=\"pn-sub\"> "._BY." ".pnVarPrepForDisplay($r_name)." "._ON." ".pnVarPrepForDisplay($datetime)."</font><br>";
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
    $column = &$pntable['comments_column'];
    $result = $dbconn->Execute("SELECT $column[tid], $column[pid], $column[sid],
                              $column[date], $column[name],
                              $column[email], $column[url], $column[host_name],
                              $column[subject], $column[comment], $column[score],
                              $column[reason]
                              FROM $pntable[comments]
                              WHERE $column[pid] = ".pnVarPrepForStore($tid)."
                              ORDER BY $column[date], $column[tid]");
    while(list($r_tid, $r_pid, $r_sid, $r_date, $r_name, $r_email, $r_url, $r_host_name, $r_subject, $r_comment, $r_score, $r_reason) = $result->fields) {
        $r_date=$result->UnixTimeStamp($r_date);
        $result->MoveNext();
        if (!isset($level)) {
        } else {
            if (!$comments) {
                echo "<ul>";
            }
        }
        $comments++;
        if (!eregi("[a-z0-9]",$r_name)) { $r_name = $anonymous; }
        if (!eregi("[a-z0-9]",$r_subject)) { $r_subject = "["._NOSUBJECT."]"; }
        $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($r_date));
        echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=showreply&amp;tid=$r_tid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">$r_subject</a></font><font class=\"pn-normal\"> "._BY." ".pnVarPrepForDisplay($r_name)." "._ON." ".pnVarPrepForDisplay($datetime)."<br>";
        DisplayBabies($r_tid, $level+1, $dummy+1);
    }
    if ($level && $comments) {
            echo "</ul>";
    }
}

function DisplayTopic ($info, $sid, $pid=0, $tid=0, $mode="thread", $order=0, $thold=0, $level=0, $nokids=0)
{
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    $bgcolor3 = $GLOBALS['bgcolor3'];

    list($hr,
    	 $datetime,
    	 $mainfile,
    	 $foot1,
    	 $subject,
    	 $title) = pnVarCleanFromInput('hr',
    	 				 'datetime',
    	 				 'mainfile',
    	 				 'foot1',
    	 				 'subject',
    	 				 'title');
    
    $modoption= pnConfigGetVar('moderate');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $reasons = pnConfigGetVar('reasons');
    $anonymous = pnConfigGetVar('anonymous');
    $anonpost = pnConfigGetVar('anonpost');
    $commentlimit = pnConfigGetVar('commentlimit');

    if(!$mainfile) {
        include 'header.php';
    }

    if($pid != 0) {
        include 'header.php';
    }

    $noshowscore = pnUserGetVar('noscore');
    $maxcomments = pnUserGetVar('commentmax');

    $column = &$pntable['comments_column'];
    $selectcolumns = array ('tid' => 0,
                            'pid' => 0,
                            'sid' => 0,
                            'name' => 0,
                            'email' => 0,
                            'url' => 0,
                            'host_name' => 0,
                            'subject' => 0,
                            'comment' => 0,
                            'score' => 0,
                            'reason' => 0 );
    $q = "SELECT ";
    $q .= getColumnsViaHashKeys('comments', $selectcolumns);
    $q .= ", $column[date]" ;
    $q .= " FROM $pntable[comments] WHERE $column[sid]=".pnVarPrepForStore($sid)." AND $column[pid]=".pnVarPrepForStore($pid)."";
    if($thold != "") {
        $q .= " AND $column[score]>=$thold";
    } else {
        $q .= " AND $column[score]>=0";
    }

    // 2 most popular first
    // 1 newest first
    // 0 - oldest first

    if ($order==1) {
    $q .= " ORDER BY $column[date] DESC";
    }
    if ($order==0) {
    $q .= " ORDER BY $column[date] ASC";
    }
    if ($order==2) {
    $q .= " ORDER BY $column[score] DESC";
    }

// I've set $bruce to $sid because $sid was getting corrupted later.
// I can't figure out where. - skribe

    $bruce = $sid;

    $something = $dbconn->Execute("$q");

    navbar($info, $sid, $title, $thold, $mode, $order);
    echo "<div align=\"left\">";
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        echo "<form action=\"modules.php\" method=\"post\">";
    }
      while(list($tid, $spid, $sid, $name, $email, $url, $host_name, $subject, $comment, $score, $reason, $date) = $something->fields) {
        $date = $something->UnixTimeStamp($date);
        $something->MoveNext();
        if ($name == "") {
        $name = $anonymous;
    }
        if ($subject == "") {
        $subject = "["._NOSUBJECT."]";
    }
        echo "<a name=\"$tid\"></a>\n";
        echo "<table width=\"99%\" border=\"0\">\n<tr bgcolor=\"$bgcolor1\">\n<td width=\"500\">\n";
        $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($date));
        if ($email) {
            echo "<font class=\"pn-title\">$subject <font class=\"pn-normal\">\n";
            if(!$noshowscore && $modoption ) {
                echo "("._SCORE." $score";
                if($reason>0) echo ", ".pnVarPrepForDisplay($reasons[$reason])."";
                echo ")\n";
            }
            echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$email\">".pnVarPrepForDisplay($name)."</a> <b>(".pnVarPrepForDisplay($email).")</b></font>\n<font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."";
        } else {
            echo "<font class=\"pn-title\">".pnVarPrepForDisplay($subject)."</font><font class=\"pn-normal\">\n";
            if(!$noshowscore && $modoption ) {
                echo "("._SCORE." $score";
                if($reason>0) echo ", ".pnVarPrepForDisplay($reasons[$reason])."";
                echo ")\n";
            }
            echo "<br>\n"._BY." ".pnVarPrepForDisplay($name)." "._ON." ".pnVarPrepForDisplay($datetime)."";
        }

        /* If you are admin you can see the Poster IP address (you have this right, no?) */
        /* with this you can see who is flaming you... ha-ha-ha */

        if($name != $anonymous) {
	    echo "<br>\n<font class=\"pn-normal\">(<a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$name&amp;module=NS-User\">"._USERINFO."</a> | <a href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$name\">"._SENDAMSG."</a>)</font>\n ";
    }
        if(eregi("http://",$url)) {
        echo "<a class=\"pn-normal\" href=\"$url\" target=\"window\">".pnVarPrepForDisplay($url)."</a>\n ";
    }
        if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_ADMIN)) {
            $column = &$pntable['comments_column'];
            $result= $dbconn->Execute("SELECT $column[host_name]
                                     FROM $pntable[comments]
                                     WHERE $column[tid]=".pnVarPrepForStore($tid)."");
            list($host_name) = $result->fields;
            echo "<br>\n<font class=\"pn-normal\">(IP: ".pnVarPrepForDisplay($host_name).")</font>\n";
        }

        echo "</font>\n</td>\n</tr>\n<tr>\n<td>\n";
        if(($maxcomments) && (strlen($comment) > $maxcomments)) echo substr("".pnVarPrepHTMLDisplay($comment)."", 0, $maxcomments)."<br><br>\n<b><a         class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$sid&tid=$tid&mode=$mode&order=$order&thold=$thold\">"._READREST."</a></b>\n";
        elseif(strlen($comment) > $commentlimit) echo substr("".pnVarPrepHTMLDisplay($comment)."", 0, $commentlimit)."<br><br>\n<b><a class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;sid=$sid&tid=$tid&mode=$mode&order=$order&thold=$thold\">"._READREST."</a></b>\n";
        else echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comment)."</font>\n";
        echo "</td>\n</tr>\n</table><br><br>\n";
        if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
            echo "<div align=\"center\">";
            echo "<font class=\"pn-normal\"> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=Reply&amp;pid=$tid&amp;sid=$sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a>\n";

            if ($spid != 0) {
                $column = &$pntable['comments_column'];
                $pidResult = $dbconn->Execute("SELECT $column[pid]
                                             FROM $pntable[comments]
                                             WHERE $column[tid]=".pnVarPrepForStore($spid)."");
                $showrepl = "";
                list($erin) = $pidResult->fields;
                if ($erin != 0) $showrepl = "req=showreply&amp;tid=$spid&amp;";
                echo " | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;".$showrepl."sid=$sid&pid=$erin&mode=$mode&order=$order&thold=$thold\">"._PARENT."</a>";
            }
            if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_MODERATE)) {
                if ($modoption) modtwo($tid, $score, $reason);
            }

            if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_DELETE)) {
                echo " | <a class=\"pn-normal\" href=\"admin.php?module=NS-Comments&amp;op=RemoveComment&amp;tid=$tid&amp;sid=$sid\">"
                ._DELETE."</a> ]</font><br><br>\n\n";
            } else {
                echo " ]</font><br><br>\n\n";
            }
        }
        echo "</div>";
        DisplayKids($tid, $mode, $order, $thold, $level);
        echo "</ul>";
        if($hr) echo "<hr noshade size=\"1\">";

// $sid changes value between here
      }
// and here - skribe
echo "</div>";
/**
 * I've changed $sid to $bruce below (until the end of the function) so
 * so that moderation will work.
 * - skribe
 */

    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_MODERATE)) {
        $column = &$pntable['comments_column'];
        $result2 = $dbconn->Execute("SELECT count(*) FROM $pntable[comments] WHERE $column[sid]='".pnVarPrepForStore($bruce)."'");
        list($numrow) = $result2->fields;
        if ($numrow == 0) {
            echo "";
            } else {
                if ($modoption) modthree($bruce, $mode, $order, $thold);
            }
         }

    if($pid==0) {
        return array($bruce, $pid, $subject);
    } else {
        include 'footer.php';
    }
// $sid to $bruce ends here - skribe
}

function singlecomment($info, $tid, $sid, $mode, $order, $thold)
{
    //global $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4;
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    $bgcolor3 = $GLOBALS['bgcolor3'];
    $bgcolor4 = $GLOBALS['bgcolor4'];

    //$datetime = pnVarCleanFromInput('datetime');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonpost = pnConfigGetVar('anonpost');

    include'header.php';

    $column = &$pntable['comments_column'];
    $result = $dbconn->Execute("SELECT $column[date],
                              $column[name], $column[email], $column[url],
                              $column[subject], $column[comment], $column[score],
                              $column[reason]
                              FROM $pntable[comments]
                              WHERE $column[tid]=".pnVarPrepForStore($tid)." AND $column[sid]=".pnVarPrepForStore($sid)."");
    list($date, $name, $email, $url, $subject, $comment, $score, $reason) = $result->fields;
    $date=$result->UnixTimeStamp($date);
    $titlebar = "<font class=\"pn-title\">".pnVarPrepForDisplay($subject)."</font><font class=\"pn-normal\">";
    if(empty($name)) {
    $name = $anonymous;
    }
    if(empty($subject)) {
    $subject = "["._NOSUBJECT."]</font>";
    }
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        echo "<form action=\"modules.php\" method=\"post\">";
    }
    echo "<table width=\"99%\" border=\"0\"><tr bgcolor=\"$bgcolor1\"><td width=\"500\">";
    $datetime = ml_ftime(_DATETIMEBRIEF, GetUserTime($date));
    if($email) {
        echo "<font class=\"pn-title\">".pnVarPrepForDisplay($subject)."</font><font class=\"pn-normal\">("._SCORE." ".pnVarPrepForDisplay($score).")<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$email\">".pnVarPrepForDisplay($name)."</a><font  class=\"pn-normal\">(".pnVarPrepForDisplay($email).")</font><font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
    } else {
        echo "<font class=\"pn-title\">".pnVarPrepForDisplay($subject)."</font><font class=\"pn-normal\">("._SCORE." ".pnVarPrepForDisplay($score).")<br>"._BY." ".pnVarPrepForDisplay($name)."</font><font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
    }
    echo "</td></tr><tr><td><font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comment)."</font></td></tr></table><br><br>";
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        echo "<font class=\"pn-normal\"> [ <a class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=NS-Comments&amp;file=index&amp;req=Reply&amp;pid=$tid&amp;sid=$sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._REPLY."</a> | <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._ROOT."</a>";
    }
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_MODERATE)) {
       if ($modoption) modtwo($tid, $score, $reason);
       echo " ]</font>";
       if ($modoption) modthree($sid, $mode, $order, $thold);
    }
    include 'footer.php';
}

function reply()
{
    global $HTTP_COOKIE_VARS;
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    $bgcolor3 = $GLOBALS['bgcolor3'];

    list($pid,
         $sid,
         $mode,
         $order,
         $thold,
         $email,
         $comment,
         $temp_comment,
         $datetime) = pnVarCleanFromInput('pid',
                                       'sid',
                                       'mode',
                                       'order',
                                       'thold',
                                       'email',
                                       'comment',
                                       'temp_comment',
                                       'datetime');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

// ATTENTION: Is all this stuff necessary?
// Since it CAN'T work I comment it out!
// It also implies BUG #527970
// mouzaia, have you tested these modifications before commiting?
// MC

// modification multisites .71 mouzaia
// is it necessary ?
// include(WHERE_IS_PERSO."config.php");
// attention, corrected this, in order to allow not to have pntables in a private folder.
// if (file_exists(WHERE_IS_PERSO."pntables.php"))
//     { @include(WHERE_IS_PERSO."pntables.php"); }
// else
//     { @include("pntables.php"); }

// END MC

    include 'header.php';

    $row = getArticles("pn_sid=$sid", "", "");
    $info = genArticleInfo($row[0]);

    if($pid !=0) {
          $column = &$pntable['comments_column'];
          $result = $dbconn->Execute("SELECT $column[date], $column[name], $column[email], $column[url], $column[subject], $column[comment],
                                             $column[score] FROM $pntable[comments] WHERE $column[tid]=".pnVarPrepForStore($pid)."");
// modification mouzaia
// if not there, a warnin on line 654.
      $comment2 = "";
          list($date, $name, $email, $url, $subject, $comment, $score) = $result->fields;
    } else {
        $date = $info['time'];
        $subject = $info['title'];
        $temp_comment = $info['hometext'];
        $comment2 = $info['bodytext'];
        $name = $info['informant'];
        $notes = $info['notes'];
    }
    if(empty($comment)) {
        $comment = "$temp_comment<br><br>$comment2";
    } else {
        $comment = pnVarPrepHTMLDisplay($comment);
    }
    OpenTable();
        echo "<center><font class=\"pn-title\">"._COMMENTREPLY."</font></center>";
    CloseTable();

    OpenTable();
    if (!pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        echo _NOTAUTHORIZEDCOMMENT;
    CloseTable();
        include 'footer.php';
        exit;
    }
    $anonymous = pnConfigGetVar('anonymous');
    if(empty($name)) {
    $name = $anonymous;
    }
    if($subject == "") {
    $subject = "<font class=\"pn-normal\">["._NOSUBJECT."]</font>";
    }
    formatTimestamp($date);
    echo "<font class=\"pn-title\">" . pnVarPrepForDisplay($subject) . "</font><font class=\"pn-normal\">";
    if(!isset($temp_comment)) {
    echo "("._SCORE." ".pnVarPrepForDisplay($score).")";
    }
    if($email) {
        echo "<br>"._BY." <a class=\"pn-normal\" href=\"mailto:$email\">".pnVarPrepForDisplay($name)."</a> <font class=\"pn-normal\">(".pnVarPrepForDisplay($email).")</font><font class=\"pn-sub\"> "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
    } else {
        echo "<br><font class=\"pn-sub\">"._BY." ".pnVarPrepForDisplay($name)." "._ON." ".pnVarPrepForDisplay($datetime)."</font>";
    }
    echo "<br><br><font class=\"pn-normal\">" . pnVarPrepHTMLDisplay($comment) . "</font><br><br>";
    if ($pid == 0) {
        if (!empty($notes)) {
            echo "<b>"._NOTE."</b><font class=\"pn-normal\">".pnVarPrepHTMLDisplay($notes)."</font><br><br>";
        } else {
            echo "";
    }
    }
    if(!isset($pid) || !isset($sid)) { echo "<font class=\"pn-normal\">Something is not right. This message is just to keep things from messing up down the road</font>"; exit(); }
        if($pid == 0) {
            $column = &$pntable['stories_column'];
            $result = $dbconn->Execute("SELECT $column[title]
                                      FROM $pntable[stories]
                                      WHERE $column[sid]=".pnVarPrepForStore($sid)."");
            list($subject) = $result->fields;
        } else {
            $column = &$pntable['comments_column'];
            $result = $dbconn->Execute("SELECT $column[subject]
                                      FROM $pntable[comments]
                                      WHERE $column[tid]=".pnVarPrepForStore($pid)."");
            list($subject) = $result->fields;
    }
    CloseTable();

    OpenTable();
    echo "<form action=\"modules.php\" method=\"post\">";
    echo "<font class=\"pn-title\">"._YOURNAME.":</font> ";
    if (pnUserLoggedIn()) {
        echo "<a href=\"user.php\">" . pnUserGetVar('uname') . "</a> <font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"user.php?module=NS-User&amp;op=logout\">"._LOGOUT."</a> ]</font><br><br>";
    } else {
            echo "<font class=\"pn-normal\">".pnVarPrepForStore($anonymous)."";
        echo " <font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"user.php\">"._NEWUSER."</a> ]</font><br><br>";
    }
    echo "<font class=\"pn-title\">"._SUBJECT.":</font><br>";

    if (!stristr($subject, 'Re:')) {
        $subject = 'Re: '.$subject;
    }

    echo "<input type=\"text\" name=\"subject\" size=\"50\" maxlength=\"85\" value=\"" . pnVarPrepForDisplay($subject) . "\"><br><br>";
    echo "<font class=\"pn-title\">"._COMMENT.":</font><br>"
         ."<textarea wrap=\"virtual\" cols=\"50\" rows=\"10\" name=\"comment\"></textarea><br />"
         ."<font class=\"pn-sub\">"._ALLOWEDHTML."&nbsp;";

    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
        if ($access > 0) echo " &lt;".$key."&gt;";
    }

    echo "</font><br /><br />";
    if (pnConfigGetVar('anonpost')) {
        if (pnUserLoggedIn()) {
            echo "<input type=\"checkbox\" name=\"xanonpost\"><font class=\"pn-normal\"> "._POSTANON."</font><br>";
        }
    }
    echo "<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
        ."<input type=\"hidden\" name=\"name\" value=\"NS-Comments\">\n"
        ."<input type=\"hidden\" name=\"file\" value=\"index\">\n"
        ."<input type=\"hidden\" name=\"pid\" value=\"$pid\">\n"
        ."<input type=\"hidden\" name=\"sid\" value=\"$sid\">\n"
        ."<input type=\"hidden\" name=\"mode\" value=\"$mode\">\n"
        ."<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
        ."<input type=\"hidden\" name=\"thold\" value=\"$thold\">\n"
        ."<input type=\"submit\" name=\"req\" value=\""._PREVIEW."\">\n"
        ."<input type=\"submit\" name=\"req\" value=\""._OK."\">\n"
        ."<select name=\"posttype\">\n";
        // extrans not stored in DB - should be fixed for .725
        //."<option value=\"exttrans\">"._EXTRANS."</option>\n"
        echo "<option value=\"plaintext\" selected>"._PLAINTEXT."</option>\n"
        ."<option value=\"html\">"._HTMLFORMATED."</option>\n"
        ."</select></font></form>\n";
    CloseTable();
    include 'footer.php';
}

function replyPreview()
{
    list($pid,
         $sid,
         $subject,
         $comment,
         $xanonpost,
         $mode,
         $order,
         $thold,
         $posttype) = pnVarCleanFromInput('pid',
                                          'sid',
                                          'subject',
                                          'comment',
                                          'xanonpost',
                                          'mode',
                                          'order',
                                          'thold',
                                          'posttype');

    include 'header.php';

    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    $anonymous = pnConfigGetVar('anonymous');
    OpenTable();
    echo "<center><font class=\"pn-title\">"._COMREPLYPRE."</font></center>";
    CloseTable();

    OpenTable();
    if (!isset($pid) || !isset($sid)) {
        echo ""._NOTRIGHT."";
        exit();
    }
    if($subject == '' or $comment == '') {
        OpenTable2();
        echo "<font class=\"pn-normal\"><b>"._MPROBLEM."</b> "._NOSUBJECT."</font><br><br><br>";
        echo "<center>"._GOBACK."</center><br><br>";
        CloseTable2();
        include("footer.php");
        exit;
    }
    $subject = pnVarCensor($subject);
    echo "<font class=\"pn-title\">".pnVarPrepForDisplay($subject)."</font>";
    echo "<br><font class=\"pn-normal\">"._BY." ";
    if (pnUserLoggedIn() && !$xanonpost) {
        echo pnUserGetVar('uname');
    } else {
        echo "".pnVarPrepForDisplay($anonymous)."";
    }
    echo " "._ONN."</font><br><br>";
    echo "<font class=\"pn-normal\">";

    $postcomment = pnVarCensor($comment);
    if ($posttype == 'plaintext') {
        $postcomment = nl2br($postcomment);
    	// some quick'n dirty replacement - no htmlspecialchars possible
    	// since this is in pnVarPrepHTMLDisplay already / larsneo 2002/11/19
        // extrans not stored in DB - should be fixed for .725
	    // } elseif ($posttype == 'exttrans') {
    	// $postcomment = nl2br($postcomment);
    	// } elseif ($posttype == 'html') {
    	// HTML is preformatted so nothing is needed
        // $postcomment = $postcomment;
    }
    echo pnVarPrepHTMLDisplay($postcomment);

    echo "</font>";

    CloseTable();

    OpenTable();
    echo "<form action=\"modules.php\" method=\"post\"><font class=\"pn-title\">"._YOURNAME.":</font> ";
    if (pnUserLoggedIn()) {
        echo "<a href=\"user.php\">" . pnUserGetVar('uname') . "</a> <font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"user.php?module=NS-User&amp;op=logout\">"._LOGOUT."</a> ]</font><br><br>";
    } else {
        echo "<font class=\"pn-normal\">".pnVarPrepForStore($anonymous)."</font><br><br>";
    }
    echo "<font class=\"pn-title\">"._SUBJECT.":</font><br>"
        ."<input type=\"text\" name=\"subject\" size=\"50\" maxlength=\"85\" value=\"" . pnVarPrepForDisplay($subject) . "\"><br><br>"
        ."<font class=\"pn-title\">"._COMMENT.":</font><br>"
        ."<textarea wrap=\"virtual\" cols=\"50\" rows=\"10\" name=\"comment\">" . pnVarPrepForDisplay($comment) . "</textarea><br>"
        ."<font class=\"pn-sub\">"._ALLOWEDHTML."<br>";
    while (list($key, $access, ) = each($AllowableHTML)) {
        if ($access > 0) echo " &lt;".$key."&gt;";
    }
    echo "</font><br>";
    if (pnConfigGetVar('anonpost')) {
        if ($xanonpost) {
            echo "<input type=\"checkbox\" name=\"xanonpost\" checked><font class=\"pn-normal\"> "._POSTANON."</font><br>";
        } elseif (pnUserLoggedIn()) {
            echo "<input type=\"checkbox\" name=\"xanonpost\"><font class=\"pn-normal\"> "._POSTANON."</font><br>";
        }
    }
    echo "<input type=\"hidden\" name=\"pid\" value=\"$pid\">"
        ."<input type=\"hidden\" name=\"sid\" value=\"$sid\">"
        ."<input type=\"hidden\" name=\"mode\" value=\"$mode\">"
        ."<input type=\"hidden\" name=\"order\" value=\"$order\">"
        ."<input type=\"hidden\" name=\"thold\" value=\"$thold\">"
        ."<input type=\"submit\" name=\"req\" value=\""._PREVIEW."\">"
        ."<input type=\"submit\" name=\"req\" value=\""._OK."\">\n"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
        ."<input type=\"hidden\" name=\"name\" value=\"NS-Comments\">\n"
        ."<input type=\"hidden\" name=\"file\" value=\"index\">\n"
        ."<select name=\"posttype\">\n";
        // extrans not stored in DB - should be fixed for .725
    	//."<option value=\"exttrans\"";
    //if ($posttype=="exttrans") {
    //    echo " selected";
    //}
    //echo ">"._EXTRANS."</option>\n";

    echo "<OPTION value=\"plaintext\"";
    if ($posttype == "plaintext") {
        echo " selected";
    }
    echo ">"._PLAINTEXT."</option>";

	echo "<OPTION value=\"html\"";;
    if ($posttype == "html") {
        echo " selected";
    }
    echo ">"._HTMLFORMATED."</option>\n";
    
    echo "</select></font></form>";
    CloseTable();
    include 'footer.php';
}

function CreateTopic ()
{
    list($xanonpost,
         $subject,
         $comment,
         $pid,
         $sid,
         $host_name,
         $mode,
         $order,
         $thold,
         $posttype,
         $req) = pnVarCleanFromInput('xanonpost',
                                          'subject',
                                          'comment',
                                          'pid',
                                          'sid',
                                          'host_name',
                                          'mode',
                                          'order',
                                          'thold',
                                          'posttype',
                                          'req');

    global $EditedMessage, $options;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $AllowableHTML = pnConfigGetVar('AllowableHTML');

    if($subject == '' or $comment == '') {
        include("header.php");
        OpenTable2();
        echo "<font class=\"pn-normal\"><b>"._MPROBLEM."</b> "._NOSUBJECT."</font><br><br><br>";
        echo "<center>"._GOBACK."</center><br><br>";
        CloseTable2();
        include 'footer.php';
        exit;
    }

    $subject = pnVarCensor($subject);
    $comment = pnVarCensor($comment);

    if ($posttype == 'plaintext') {
        $comment = nl2br($comment);
    	// some quick'n dirty replacement - no htmlspecialchars possible
    	// since this is in pnVarPrepHTMLDisplay already / larsneo 2002/11/19
        // extrans not stored in DB - should be fixed for .725
    	// } elseif ($posttype == 'exttrans') {
    	// $postcomment = nl2br($postcomment);
    	// } elseif ($posttype == 'html') {
    	// HTML is preformatted so nothing is needed
        // $comment = nl2br($comment);
    }

    if (pnUserLoggedIn() && (!$xanonpost)) {
        $uname = pnUserGetVar('uname');
        $email = pnUserGetVar('femail');
        $url   = pnUserGetVar('url');
        $score = 1;
    } else {
        $uname = "";
    $email = "";
    $url   = "";
        $score = 0;
    }
    $ip = getenv("REMOTE_ADDR");

/* begin fake thread control */

    $result = $dbconn->Execute("SELECT count(*)
                                FROM $pntable[stories]
                                WHERE {$pntable['stories_column']['sid']}=".pnVarPrepForStore($sid)."");
    list($fake) = $result->fields;

/* begin duplicate control */

/*
 * hootbah:
 * Is this needed? If the table is set up correctly with indexes then this test
 * would be obsolete.
 */
    $column = &$pntable['comments_column'];
    if (strcmp(pnConfigGetVar('dbtype'), 'oci8') == 0) {
    $sql = "SELECT count(*) FROM $pntable[comments]
            WHERE $column[pid]=".pnVarPrepForStore($pid)."
              AND $column[sid]=".pnVarPrepForStore($sid)."
              AND $column[subject]='".pnVarPrepForStore($subject)."'
              AND DBMS_LOB.INSTR($column[comment], '$comment', 1, 1) > 0";
    } else {
        $sql = "SELECT count(*) FROM $pntable[comments]
                WHERE $column[pid]=".pnVarPrepForStore($pid)."
                  AND $column[sid]=".pnVarPrepForStore($sid)."
                  AND $column[subject]='".pnVarPrepForStore($subject)."'
                  AND $column[comment]='".pnVarPrepForStore($comment)."'";
    }
    $result = $dbconn->Execute($sql);
    list($tia) = $result->fields;

/* begin troll control */

    if(pnUserLoggedIn()) {
        $column = &$pntable['comments_column'];
        $result = $dbconn->Execute("SELECT count(*) FROM $pntable[comments]
                                    WHERE ($column[score]=-1)
                                    AND ($column[name]='".pnVarPrepForStore($uname)."')
                                    AND (to_days(now())
                                    - to_days($column[date]) < 3)");
        list($troll) = $result->fields;
    } elseif(!$score) {
        $column = &$pntable['comments_column'];
        $result = $dbconn->Execute("SELECT count(*) FROM $pntable[comments]
                                    WHERE ($column[score]=-1)
                                    AND ($column[host_name]='".pnVarPrepForStore($ip)."')
                                    AND (to_days(now())
                                    - to_days($column[date]) < 3)");
        list($troll) = $result->fields;
    }
    if((!$tia) && ($fake == 1) && ($troll < 6)) {
        $column = &$pntable['comments_column'];
        $nextid = $dbconn->GenId($pntable['comments']);
        $result = $dbconn->Execute("INSERT INTO $pntable[comments] ($column[tid], $column[pid],
                          $column[sid], $column[date], $column[name], $column[email],
                                                  $column[url], $column[host_name], $column[subject],
                                                  $column[comment], $column[score], $column[reason] )
                        VALUES ($nextid, ".pnVarPrepForStore($pid).", ".pnVarPrepForStore($sid).", now(), '".pnVarPrepForStore($uname)."', '".pnVarPrepForStore($email)."',
                                                  '".pnVarPrepForStore($url)."', '".pnVarPrepForStore($ip)."', '".pnVarPrepForStore($subject)."', '".pnVarPrepForStore($comment)."', '".pnVarPrepForStore($score)."', 0)");
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: Can not add comment: " . $dbconn->ErrorMsg());
        }

    } else {
        include 'header.php';
        if ($tia) {
            echo "<font class=\"pn-normal\">"._DUPLICATE."<br><br><a class=\"pn-normal\"  href=\"modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid&amp;mode=$mode&amp;order=$order&amp;thold=$thold\">"._COMMENTSBACK."</a></font>";
        } elseif($troll > 5) {
            echo '_TROLL';
        } elseif($fake == 0) {
            echo '_FAKETOPIC';
        }
        include 'footer.php';
        exit;
    }
    $column = &$pntable['stories_column'];
    $result = $dbconn->Execute("UPDATE $pntable[stories] SET $column[comments]=$column[comments]+1 WHERE $column[sid]=".pnVarPrepForStore($sid)."");

    $options .= pnUserGetCommentOptionsArray();

    $redURL = 'modules.php?op=modload&name=News&file=article&sid='.$sid.'&thold=';
    if (empty($thold)) {
    	$redURL .= "0&mode=";
    } else {
    	$redURL .= "$thold&mode=";
    }
    if (empty($mode)) {
    	$redURL .= "0&order=";
    } else {
    	$redURL .= "$mode&order=";
    }
    if (empty($order)) {
    	$redURL .= "0";
    } else {
    	$redURL .= "$order";
    }
    
    pnRedirect($redURL);
}



// Main module function

list($dbconn) = pnDBGetConn();

list($info,
     $pid,
     $tid,
     $mode,
     $order,
     $thold,
     $mainfile,
     $req,
     $moderate,
     $tdw,
     $emp,
     $sid) = pnVarCleanFromInput('info',
     				   'pid',
     				   'tid',
     				   'mode',
     				   'order',
     				   'thold',
     				   'mainfile',
     				   'req',
     				   'moderate',
     				   'tdw',
     				   'emp',
     				   'sid'); 

if(empty($req)) {
    $req = "";
}
switch($req) {

    case "Reply":
        reply($pid, $sid, $mode, $order, $thold);
        break;

    case ""._PREVIEW."":
        replyPreview ();
        break;

    case ""._OK."":
        CreateTopic();
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
                                                $column = &$pntable['comments_column'];
                        $q = "UPDATE $pntable[comments] SET";
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
                        if(strlen($q) > 20) {
                            $result = $dbconn->Execute($q);
                        }
                    }
                }
            }
        }
        pnRedirect('modules.php?op=modload&name=News&file=article&sid='.$sid.'&mode='.$mode.'&order='.$order.'&thold='.$thold);
        break;

    case "showreply":
        $info = pnVarCleanFromInput('info');
        DisplayTopic($info, $sid, $pid, $tid, $mode, $order, $thold);
        break;

    default:
        if ((isset($tid)) && (!isset($pid))) {
            singlecomment($info, $tid, $sid, $mode, $order, $thold);
        } elseif (($mainfile) xor (($pid==0) AND (!isset($pid)))) {
            pnRedirect('modules.php?op=modload&name=News&file=article&sid='.$sid.'&mode='.$mode.'&order='.$order.'&thold='.$thold);
        } else {
            if(!isset($pid)) {
        $pid=0;
        }
            if(!empty($info)) {
                DisplayTopic($info, $sid, $pid, $tid, $mode, $order, $thold);
            } else {
                include 'header.php';
                echo _COMMENTSNODIRECTACCESS;
                include 'footer.php';
            }
        }
        break;
}

// I've commented out the following function since it's not used anywhere in the
// core PostNuke code. I don't know if it's needed for backward compatibility
// with older version of PN. -tanis
/*
function check_words_comments($Message)
{
    global $EditedMessage;

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
*/
?>
