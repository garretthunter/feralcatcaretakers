<?php
// File: $Id: admin.php,v 1.6 2002/11/25 18:59:41 larsneo Exp $ $Name:  $
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

// Modifications by Andy Varganov - the file was almost unmanageable with
// more than 2500 lines of code, so I have splitted it in three parts:
// Part 1 - this file where only the functions included in the switch reside
// Part 2 - addstory_functions.php where all other functions live
// Part 3 - category functions that might be reused for other parts of pn
// I'll try to add description of those in addstory_functions.php later

// Security related changes and removed globals. - skooter

if (!eregi("admin.php", $PHP_SELF)) { die ("Access Denied"); }

$ModName = basename( dirname( __FILE__ ) );
modules_get_language();
modules_get_manual();

include_once ("modules/$ModName/addstory_functions.php");
include_once ("modules/$ModName/addstory_categories.php");

function displayStory()
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($cat,
    	 $catid,
    	 $qid,
    	 $comm,
         $automated,
         $module) = pnVarCleanFromInput('cat',
         								   'catid',
         								   'qid',
         								   'comm',
                                           'automated',
                                           'module');

    if (!isset($automated)) {
        $automated = 0;
    }

     
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');
    include ('header.php');
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._SUBMISSIONSADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("SELECT $column[qid], $column[uid], $column[uname],
                                $column[subject], $column[story], $column[topic],
                                $column[alanguage], $column[bodytext]
                              FROM $pntable[queue] WHERE $column[qid]=".(int)pnVarPrepForStore($qid));

    list($qid, $uid, $uname, $subject, $story, $topic, $alanguage, $bodytext) = $result->fields;
    $result->Close();
    OpenTable();
    echo "<font class=\"pn-title\" size=\"3\">"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<b>"._NAME."</b><br>"
        ."<input type=\"text\" NAME=\"author\" size=\"25\" value=\"$uname\">";
    if ($uname != $anonymous) {
        $column = &$pntable['users_column'];
        $res = $dbconn->Execute("SELECT $column[email]
                               FROM $pntable[users]
                               WHERE $column[uname]='".pnVarPrepForStore($uname)."'");

        list($email) = $res->fields;
        echo "&nbsp;&nbsp;<font class=\"pn-normal\">[ <a href=\"mailto:$email\">"._EMAILUSER."</a> | "
        ."<a href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$uname\">"._SENDPRIVMSG."</a> ]</font>";
    }
    if($topic=="") {
        $topic = 1;
    }

    // Guess the format type.
    $format_type = defaultFormatType($story, $bodytext);

    echo "<br><br>";
    // Pass in format_type=0 at this stage to assume format is text.
    storyPreview($subject, $story, $bodytext, $notes="", $topic, $format_type);
    storyEdit($subject, $story, $bodytext, $notes="", $topic, $ihome="", $catid, $alanguage, $comm, $aid="", $informant="", $format_type);
    buildProgramStoryMenu($automated);
    buildCalendarMenu(false, $year, $day, $month, $hour, $min);
    echo "<input type=\"hidden\" name=\"module\" value=\"". pnVarPrepForDisplay($module) . "\">"
        ."<input type=\"hidden\" NAME=\"qid\" size=\"50\" value=\"" . pnVarPrepForDisplay($qid) . "\">"
        ."<input type=\"hidden\" NAME=\"uid\" size=\"50\" value=\"" . pnVarPrepForDisplay($uid) . "\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<br><br><select name=\"op\" class=\"pn-text\">"
        ."<option value=\"DeleteStory\">"._DELETESTORY."</option>"
        ."<option value=\"PreviewAgain\" selected>"._PREVIEWSTORY."</option>"
        ."<option value=\"PostStory\">"._POSTSTORY."</option>"
        ."</select>&nbsp;&nbsp;"
        ."<input type=\"submit\" value=\""._OK."\">"
        ."</form>";
    CloseTable();
    include ('footer.php');
}

function previewStory()
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($module,
    	 $automated,
         $year,
         $day,
         $month,
         $hour,
         $min,
         $qid,
         $uid,
         $author,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $notes,
         $catid,
         $ihome,
         $alanguage,
         $comm,
         $format_type,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('module',
         							  'automated',
                                      'year',
                                      'day',
                                      'month',
                                      'hour',
                                      'min',
                                      'qid',
                                      'uid',
                                      'author',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'notes',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'comm',
                                      'format_type',
                                      'format_type_home',
                                      'format_type_body');

    if (!isset($format_type)) {
        $format_type = 0;
    }

    if (isset($format_type_home) && isset($format_type_body)) {
        $format_type = ($format_type_body%4)*4 + $format_type_home%4;
    }
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');

    if (!isset($automated)) {
        $automated = 0;
    }

    include ('header.php');
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";
    OpenTable();
    echo "<font class=\"pn-title\">"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<b>"._NAME."</b><br>"
        ."<input type=\"text\" name=\"author\" size=\"25\" value=\"$author\">";
    if ($author != $anonymous) {
        $column = &$pntable['users_column'];
        $res = $dbconn->Execute("SELECT $column[email]
                               FROM $pntable[users]
                               WHERE $column[uname]='$author'");

        list($email) = $res->fields;
        echo "&nbsp;&nbsp;<font class=\"pn-normal\">[ <a href=\"mailto:$email\">"._EMAILUSER."</a> | "
        ."<a href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$author\">"._SENDPRIVMSG."</a> ]</font>";
    }
    echo "<br><br>";
    storyPreview($subject, $hometext, $bodytext, $notes, $topic, $format_type);
    storyEdit($subject, $hometext, $bodytext, $notes, $topic, $ihome, $catid, $alanguage, $comm, $uid, $author, $format_type);
    echo "<input type=\"hidden\" NAME=\"qid\" size=\"50\" value=\"$qid\">"
        ."<input type=\"hidden\" NAME=\"uid\" size=\"50\" value=\"$uid\">";
    buildProgramStoryMenu($automated);
    buildCalendarMenu(true, $year, $day, $month, $hour, $min);
    echo "<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<br><br><select name=\"op\" class=\"pn-text\">"
        ."<option value=\"DeleteStory\">"._DELETESTORY."</option>"
        ."<option value=\"PreviewAgain\" selected>"._PREVIEWSTORY."</option>"
        ."<option value=\"PostStory\">"._POSTSTORY."</option>"
        ."</select>&nbsp;"
        ."<input type=\"submit\" value=\""._OK."\">"
        ."</form>";
    CloseTable();
    include ('footer.php');
}

function postStory()
{
    // Confirm authorisation code
/*    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }*/

    list($module,
    	 $automated,
         $year,
         $day,
         $month,
         $hour,
         $min,
         $qid,
         $uid,
         $author,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $notes,
         $catid,
         $ihome,
         $alanguage,
         $comm,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('module',
         							  'automated',
                                      'year',
                                      'day',
                                      'month',
                                      'hour',
                                      'min',
                                      'qid',
                                      'uid',
                                      'author',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'notes',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'comm',
                                      'format_type_home',
                                      'format_type_body');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    // check for valid topic
    if (empty($topic)) {
        echo "Error: No valid topic given. Repost.";
        exit;
    }

    if (empty($subject)) {
        echo _ADDSTORYNOSUBJECT;
        exit;
    }

    if (!isset($automated)) {
        $automated = 0;
    }

    if (!isset($format_type_home)) {
        $format_type_home = 0;
    }

    if (!isset($format_type_body)) {
        $format_type_body = 0;
    }

    // Lowest two bits is the home format type, next two bits is the body format type.
    $format_type = (($format_type_body%4)*4) + ($format_type_home%4);

    // Get category from catID - needed for authorisation
    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid] = $catid");
    if ($result === false) {
        error_log("DB ERROR: can not get category" . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "DB ERROR: can not get category");
    }
    if ($result->PO_RecordCount($pntable['stories_cat'], "$column[catid] = $catid")== 1) {
        list($cattitle) = $result->fields;
    } else {
        $cattitle = "";
    }

    if (!pnSecAuthAction(0, 'Stories::Story', ":$cattitle:", ACCESS_ADD)) {
        include 'header.php';
        echo _STORIESADDNOAUTH;
        include 'footer.php';
        return;
    }

    // Only add br tags if the format type is text.

    if ($format_type_home == 0) {
        $hometext = nl2br($hometext);
    };

    if ($format_type_body == 0) {
        $bodytext = nl2br($bodytext);
    }

    $notes = nl2br($notes);

    if ($automated == 1) {
        if ($day < 10) {
            $day = "0$day";
        }
        if ($month < 10) {
            $month = "0$month";
        }
        $sec = "00";
        $date = "$year-$month-$day $hour:$min:$sec";
        if ($hometext == $bodytext) $bodytext = "";
        $column = &$pntable['autonews_column'];
        $nextid = $dbconn->GenId($pntable['autonews']);
        $result = $dbconn->Execute("INSERT INTO $pntable[autonews]
                                    ($column[anid], $column[catid], $column[aid],
                                    $column[title], $column[time], $column[hometext],
                                    $column[bodytext], $column[topic],
                                    $column[informant], $column[notes], $column[ihome],
                                    $column[alanguage], $column[withcomm])
                                  VALUES (" . pnVarPrepForStore($nextid) . ",
                                          " . pnVarPrepForStore($catid) . ",
                                          " . pnVarPrepForStore($uid) . ",
                                          '" . pnVarPrepForStore($subject) . "',
                                          '" . pnVarPrepForStore($date) . "',
                                          '" . pnVarPrepForStore($hometext) . "',
                                          '" . pnVarPrepForStore($bodytext) . "',
                                          '" . pnVarPrepForStore($topic) . "',
                                          '" . pnVarPrepForStore($author) . "',
                                          '" . pnVarPrepForStore($notes) . "',
                                          '" . pnVarPrepForStore($ihome) . "',
                                          '" . pnVarPrepForStore($alanguage) . "',
                                          " . pnVarPrepForStore($comm) . ")");

        if ($result === false) {
            error_log("DB ERROR: can not add story " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "DB ERROR: can not add story ");
        }
        if (!empty($uid)) {
            $column = &$pntable['users_column'];
            $result = $dbconn->Execute("UPDATE $pntable[users]
                                      SET $column[counter]=$column[counter]+1
                                      WHERE $column[uid]=$uid");
            if ($result === false) {
                error_log("ERROR: addStory can not update users table" . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "ERROR: addStory can not update users table");
            }
        }
        $queuetable = $pntable['queue'];
        $queuecolumn = &$pntable['queue_column'];
        $result = $dbconn->Execute("DELETE FROM $queuetable
                                  WHERE $queuecolumn[qid]=$qid");
        if ($result === false) {
            error_log("ERROR: addStory can not delete from queue" . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "ERROR: addStory can not delete from queue");
        }
        pnRedirect('admin.php'.'?module='.$module.'&op=submissions');
    } else {

        if ($hometext == $bodytext) $bodytext = "";
        $column = &$pntable['stories_column'];
        $nextid = $dbconn->GenId($pntable['stories']);
        $result = $dbconn->Execute("INSERT INTO $pntable[stories] ($column[sid],
                               $column[catid], $column[aid], $column[title],
                               $column[time], $column[hometext], $column[bodytext],
                               $column[comments], $column[counter], $column[topic],
                               $column[informant], $column[notes], $column[ihome],
                               $column[themeoverride], $column[alanguage],
                               $column[withcomm], $column[format_type])
                             VALUES (" . pnVarPrepForStore($nextid) . ",
                                     " . pnVarPrepForStore($catid) . ",
                                     " . pnVarPrepForStore($uid) . ",
                                     '" . pnVarPrepForStore($subject) . "',
                                     now(),
                                     '" . pnVarPrepForStore($hometext) . "',
                                     '" . pnVarPrepForStore($bodytext) . "',
                                     '" . pnVarPrepForStore(0) . "',
                                     '" . pnVarPrepForStore(0) . "',
                                     '" . pnVarPrepForStore($topic) . "',
                                     '" . pnVarPrepForStore($author) . "',
                                     '" . pnVarPrepForStore($notes) . "',
                                     '" . pnVarPrepForStore($ihome) . "',
                                     '',
                                     '" . pnVarPrepForStore($alanguage) . "',
                                     '" . pnVarPrepForStore($comm) . "',
                                     '" . pnVarPrepForStore($format_type) . "')");
        if ($result === false) {
            error_log("ERROR: add Story, can not add story" . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "ERROR: add Story, can not add story");
        }
        if (!empty($uid)) {
            $column = &$pntable['users_column'];
            $result = $dbconn->Execute("UPDATE {$pntable['users']}
                                      SET {$column['counter']}={$column['counter']}+1
                                      WHERE {$column['uid']}=$uid");
            if ($result === false) {
                error_log("ERROR:  add story can not update users" . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "ERROR:  add story can not update users");
            }
        }
        deleteStory($qid);
    }
}

/**

 * Delete a story
 *
 * This function, given a queue id, deletes a story from the queue
 *
 * @param $qid int Queue id of the the story
 * @return none
 * @author FB
 */

function deleteStory($qid)
{

    $module = pnVarCleanFromInput('module');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $result = $dbconn->Execute("DELETE FROM {$pntable['queue']}
                              WHERE {$pntable['queue_column']['qid']}='$qid'");
    if ($result === false) {
        error_log("stories->deleteStory: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
    }

    pnRedirect('admin.php'.'?module='.$module.'&op=submissions');
}

function editStory()
{
    $sid = pnVarCleanFromInput('sid');
    $module = pnVarCleanFromInput('module');
       
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['stories_column'];
    $catcolumn = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT {$column['catid']}, {$column['title']},
                                {$column['hometext']}, {$column['bodytext']},
                                {$column['topic']}, {$column['notes']}, {$column['ihome']},
                                {$column['alanguage']}, {$column['withcomm']}, {$column['aid']},
                                {$column['informant']}, {$column['format_type']}
                              FROM {$pntable['stories']}
                              WHERE {$column['sid']}=$sid");

    if ($result->EOF) {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();
        OpenTable();
        echo "<center><b>"._NOTSUCHARTICLE."</b><br><br>"._GOBACK."</center>";
        CloseTable();
        include("footer.php");
        return;
    }

    list($catid, $subject, $hometext, $bodytext, $topic, $notes, $ihome, $alanguage, $comm, $aid, $informant, $format_type) = $result->fields;
    $result->Close();
    $result = $dbconn->Execute("SELECT {$catcolumn['title']}
                              FROM {$pntable['stories_cat']}
                              WHERE {$catcolumn['catid']} = $catid");
    if ($result->PO_RecordCount($pntable['stories_cat'], "{$catcolumn['catid']} = $catid")== 1) {
        list($cattitle) = $result->fields;
    } else {
        $cattitle = "";
    }
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$sid", ACCESS_EDIT)) {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();
        echo "<br>";

        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._EDITARTICLE."</b></font></center><br>";

        // judgej - Removed pre-processing of strings to preview.
        storyPreview($subject, $hometext, $bodytext, unnltobr($notes), $topic, $format_type);
        echo "<form action=\"admin.php\" method=\"post\">";

        storyEdit($subject, $hometext, $bodytext, $notes, $topic, $ihome, $catid, $alanguage, $comm, $aid, $informant, $format_type);

        echo "<input type=\"hidden\" NAME=\"sid\" size=\"50\" value=\"$sid\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"ChangeStory\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
            ."</form>";
        CloseTable();
        include ('footer.php');
    } else {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center><b>"._NOTAUTHORIZED1."</b><br><br>"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
    }
}

function removeStory($sid, $ok=0)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['stories_column'];
    $sql = "SELECT $column[aid],
                   $column[catid],
                   $column[title]
            FROM $pntable[stories]
            WHERE $column[sid] = " . pnVarPrepForStore($sid);
    $result = $dbconn->Execute($sql);
    list($aid, $catid, $stitle) = $result->fields;
    $result->Close();

    $column = &$pntable['stories_cat_column'];
    $sql = "SELECT $column[title]
            FROM $pntable[stories_cat]
            WHERE $column[catid] = " . pnVarPrepForStore($catid);
    $result = $dbconn->Execute($sql);
    list($cattitle) = $result->fields;
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$sid", ACCESS_DELETE)) {
        if($ok == 1) {

        if (!pnSecConfirmAuthKey()) {
            include 'header.php';
            echo _BADAUTHKEY;
            include 'footer.php';
            exit;
            }
            $column = &$pntable['stories_column'];
            $sql = "DELETE FROM $pntable[stories]
                    WHERE $column[sid] = " . pnVarPrepForStore($sid);
            $dbconn->Execute($sql);
            if ($dbconn->ErrorNo()) {
                error_log("stories->removeStory: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->removeStory: Error accesing to the database");
            }
            $column = &$pntable['comments_column'];
            $sql = "DELETE FROM $pntable[comments]
                    WHERE $column[sid] = " . pnVarPrepForStore($sid);
            $dbconn->Execute($sql);
            if ($dbconn->ErrorNo()) {
                error_log("stories->removeStory: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->removeStory: Error accesing to the database");
            }
            pnRedirect('admin.php');
        } else {
            include("header.php");
            GraphicAdmin();
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
            CloseTable();

            OpenTable();
            echo "<center>"._REMOVESTORY."<b> $sid - $stitle</b> - "._ANDCOMMENTS."";
            echo "<table><tr><td>\n";
            echo myTextForm('admin.php', _NO);
            echo "</td><td>\n";
            echo myTextForm("admin.php?module=NS-AddStory&amp;op=RemoveStory&amp;sid=$sid&amp;ok=1&amp;authid=".pnSecGenAuthKey()."", _YES);
            echo "</td></tr></table>\n";
            echo "</center>\n";
            CloseTable();
            include("footer.php");
        }
    } else {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center><b>"._NOTAUTHORIZED1."</b><br><br>"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
    }
}

function changeStory()
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($sid,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $notes,
         $catid,
         $ihome,
         $alanguage,
         $comm,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('sid',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'notes',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'comm',
                                      'format_type_home',
                                      'format_type_body');

    if (!isset($format_type_home)) {
        $format_type_home = 0;
    }

    if (!isset($format_type_body)) {
        $format_type_body = 0;
    }

    $format_type = (($format_type_body%4)*4) + ($format_type_home%4);
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // jgm - need to get instance information here
    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid] = $catid");
    if ($result === false) {
        error_log("DB ERROR: can not get category" . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "DB ERROR: can not get category");
    }
    if ($result->PO_RecordCount($pntable['stories_cat'], "{$column['catid']} = $catid")== 1) {
        list($cattitle) = $result->fields;
    } else {
        $cattitle = "";
    }
    $result->Close();

    // TODO: handle these.
    if ($format_type_home == 0)
    {
        $hometext = nl2br($hometext);
    }
    if ($format_type_body == 0)
    {
        $bodytext = nl2br($bodytext);
    }
    $notes = nl2br($notes);

    $column = &$pntable['stories_column'];
    $result = $dbconn->Execute("SELECT $column[aid]
                              FROM $pntable[stories]
                              WHERE $column[sid]='$sid'");
    list($aid) = $result->fields;
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$sid", ACCESS_EDIT)) {

        $column = &$pntable['stories_column'];
        $result = $dbconn->Execute("UPDATE $pntable[stories]
                                    SET $column[catid]='" . pnVarPrepForStore($catid) . "',
                                        $column[title]='" . pnVarPrepForStore($subject) . "',
                                        $column[hometext]='" . pnVarPrepForStore($hometext) . "',
                                        $column[bodytext]='" . pnVarPrepForStore($bodytext) . "',
                                        $column[topic]='" . pnVarPrepForStore($topic) . "',
                                        $column[notes]='" . pnVarPrepForStore($notes) . "',
                                        $column[ihome]='" . pnVarPrepForStore($ihome) . "',
                                        $column[alanguage]='" . pnVarPrepForStore($alanguage) . "',
                                        $column[withcomm]='" . pnVarPrepForStore($comm) . "',
                                        $column[format_type]='" . pnVarPrepForStore($format_type) . "'
                                  WHERE $column[sid]=" . pnVarPrepForStore($sid));
        if ($result === false) {
            error_log("stories->changeStory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->changeStory: Error accesing to the database");
        }
        pnRedirect('admin.php'.'?module=NS-Admin&op=main');
    }
}

function adminStory() {

    if (file_exists("modules/".'NAME_OF_CALENDAR'."/cal.api")) {
        Header("Location: modules/".NAME_OF_CALENDAR."/stories.php");
    }

    list($module, $automated) = pnVarCleanFromInput('module','automated');
    if (!isset($automated)) {
        $automated = 0;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    modules_get_manual();
    include ('header.php');
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
    echo "<br><center><font size=\"pn-title\"> <b><a class=\"pn-normal\" href=\""
    ."admin.php?module=$module&op=submissions\">"._NEWSUBMISSIONS."</a> </b></font></center>";
    CloseTable();
    echo "<br>";
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ADDARTICLE."</b></font></center><br><br>"
        ."<form action=\"admin.php\" method=\"post\">";

    storyEdit($subject="", $hometext="", $bodytext="", $notes="", $topic="", $ihome=0, $catid="", $alanguage="", $comm=0, $aid="", $informant="", defaultFormatType("", ""));
    buildProgramStoryMenu($automated);
    buildCalendarMenu(false, $year, $day, $month, $hour, $min);
// die("NC: ".NAME_OF_CALENDAR);
    if (defined('NAME_OF_CALENDAR')) {
        if (file_exists("modules/".NAME_OF_CALENDAR."/ajStories.php")) {
            include("modules/".NAME_OF_CALENDAR."/lang/".pnVarPrepForOS(pnUserGetLang())."/global.php");
            include("modules/".NAME_OF_CALENDAR."/ajStories.php")    ;
        }
    }
    echo "<br><br><input type=\"hidden\" name=\"module\" value=\"$module\">"
         ."<select name=\"op\" class=\"pn-text\">"
         ."<option value=\"PreviewAdminStory\" selected>"._PREVIEWSTORY."</option>"
         ."<option value=\"PostAdminStory\">"._POSTSTORY."</option>"
         ."</select>"
         ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ."<input type=\"submit\" value=\""._OK."\">"
         ."</form>";
    CloseTable();
    include ('footer.php');
}

function previewAdminStory()
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($module,
    	 $automated,
         $year,
         $day,
         $month,
         $hour,
         $min,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $catid,
         $ihome,
         $alanguage,
         $notes,
         $comm,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('module',
         							  'automated',
                                      'year',
                                      'day',
                                      'month',
                                      'hour',
                                      'min',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'notes',
                                      'comm',
                                      'format_type_home',
                                      'format_type_body');

    if (!isset($format_type_home)) {
        $format_type_home = 0;
    }

    if (!isset($format_type_body)) {
        $format_type_body = 0;
    }

    $format_type = (($format_type_body%4)*4) + ($format_type_home%4);

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!isset($automated)) {
        $automated = 0;
    }

    include ('header.php');
    if ($topic<1) {
        $topic = 1;
    }
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
    CloseTable();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._PREVIEWSTORY."</b></font></center><br><br>"
    ."<form action=\"admin.php\" method=\"post\">";
    storyPreview($subject, $hometext, $bodytext, $notes, $topic, $format_type);
    storyEdit($subject, $hometext, $bodytext, $notes, $topic, $ihome, $catid, $alanguage, $comm, $aid="", $informant="", $format_type);
    buildProgramStoryMenu($automated);
    buildCalendarMenu(true, $year, $day, $month, $hour, $min);
    echo "<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
         ."<select name=\"op\" class=\"pn-text\">"
         ."<option value=\"PreviewAdminStory\" selected>"._PREVIEWSTORY."</option>"
         ."<option value=\"PostAdminStory\">"._POSTSTORY."</option>"
         ."</select>"
         ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ."<input type=\"submit\" value=\""._OK."\">"
         ."</form>";
    CloseTable();
    include ('footer.php');
}

function postAdminStory()
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($automated,
         $year,
         $day,
         $month,
         $hour,
         $min,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $catid,
         $ihome,
         $alanguage,
         $notes,
         $comm,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('automated',
                                      'year',
                                      'day',
                                      'month',
                                      'hour',
                                      'min',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'notes',
                                      'comm',
                                      'format_type_home',
                                      'format_type_body');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!isset($format_type_home)) {
        $format_type_home = 0;
    }

    if (!isset($format_type_body)) {
        $format_type_body = 0;
    }

    $format_type = (($format_type_body%4)*4) + ($format_type_home%4);

    if (empty($subject)) {
        include 'header.php';
        echo _ADDSTORYNOSUBJECT;
        include 'footer.php';
        exit;
    }

    if (!isset($automated)) {
        $automated = 0;
    }

    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');
        $name = pnUserGetVar('uname');
    } else {
        $uid = 0;
        $name = 'anonymous';
    }

    // Special munging of pseudo-html to add in <br> in place of \n
    // This will go away as soon as I write a dropdown to select the
    // appropriate translation (and some translation handlers)

    if ($format_type_home == 0)
    {
        $hometext = nl2br($hometext);
    }
    if ($format_type_body == 0)
    {
        $bodytext = nl2br($bodytext);
    }
    $notes = nl2br($notes);

    if ($automated == 1) {
        if ($day < 10) {
            $day = "0$day";
        }
        if ($month < 10) {
            $month = "0$month";
        }
        $sec = "00";
        $date = "$year-$month-$day $hour:$min:$sec";

        $column = &$pntable['autonews_column'];
        $nextid = $dbconn->GenId($pntable['autonews']);
        $result = $dbconn->Execute("INSERT INTO $pntable[autonews] (
                                      $column[anid],
                                      $column[catid],
                                      $column[aid],
                                      $column[title],
                                      $column[time],
                                      $column[hometext],
                                      $column[bodytext],
                                      $column[topic],
                                      $column[informant],
                                      $column[notes],
                                      $column[ihome],
                                      $column[alanguage],
                                      $column[withcomm])
                                    VALUES (
                                      " . pnVarPrepForStore($nextid) . ",
                                      " . pnVarPrepForStore($catid) . ",
                                      " . pnVarPrepForStore($uid) . ",
                                      '" . pnVarPrepForStore($subject) . "',
                                      '" . pnVarPrepForStore($date) . "',
                                      '" . pnVarPrepForStore($hometext) . "',
                                      '" . pnVarPrepForStore($bodytext) . "',
                                      '" . pnVarPrepForStore($topic) . "',
                                      '" . pnVarPrepForStore($name) . "',
                                      '" . pnVarPrepForStore($notes) . "',
                                      '" . pnVarPrepForStore($ihome) . "',
                                      '" . pnVarPrepForStore($alanguage) . "',
                                      '" . pnVarPrepForStore($comm) . "')");
        if ($result === false) {
            error_log("stories->postAdminStory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->postAdminStory: Error accesing to the database");
        }
        pnRedirect('admin.php?module=NS-Admin&op=main');
    } else {
        $column = &$pntable['stories_column'];
        $nextid = $dbconn->GenId($pntable['stories']);
        $result = $dbconn->Execute("INSERT INTO $pntable[stories] (
                                      $column[sid],
                                      $column[catid],
                                      $column[aid],
                                      $column[title],
                                      $column[time],
                                      $column[hometext],
                                      $column[bodytext],
                                      $column[comments],
                                      $column[counter],
                                      $column[topic],
                                      $column[informant],
                                      $column[notes],
                                      $column[ihome],
                                      $column[themeoverride],
                                      $column[alanguage],
                                      $column[withcomm],
                                      $column[format_type])
                                    VALUES (
                                      " . pnVarPrepForStore($nextid) . ",
                                      " . pnVarPrepForStore($catid) . ",
                                      " . pnVarPrepForStore($uid) . ",
                                      '" . pnVarPrepForStore($subject) . "',
                                      now(),
                                      '" . pnVarPrepForStore($hometext) . "',
                                      '" . pnVarPrepForStore($bodytext) . "',
                                      '0',
                                      '0',
                                      '" . pnVarPrepForStore($topic) . "',
                                      '" . pnVarPrepForStore($name) . "',
                                      '" . pnVarPrepForStore($notes) . "',
                                      '" . pnVarPrepForStore($ihome) . "',
                                      '',
                                      '" . pnVarPrepForStore($alanguage) . "',
                                      '" . pnVarPrepForStore($comm) . "',
                                      '" . pnVarPrepForStore($format_type) . "')");
        
        if ($result === false) {
            error_log("stories->postAdminStory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->postAdminStory: Error accesing to the database");
        }
     // Calendar code no longer valid - skooter
     // modif SB in calendar
     //   if (isset($GLOBALS["lincalendar"])) {
     //       if ($GLOBALS["day"] < 10) {
     //           $GLOBAL["day"] = "0".$GLOBALS['day'];
     //      }
     //       if ($GLOBALS["month"] < 10) {
     //           $GLOBAL["month"] = "0".$GLOBALS['month'];
     //       }
     //       $sec = "00";
     //       $min = "00";
     //       $hour = "00";
     //       $cLocation = "modules.php?op=modload&name=".NAME_OF_CALENDAR."&file=cal_ajoute&day=".$GLOBALS['add_day']."&month=".$GLOBALS['add_month']."&year=".$GLOBALS['add_year']."&event_title=".$GLOBALS['subject']."&event_text=".$hometext;
     //   } else {
     //       $cLocation = "admin.php?module=NS-Admin&op=main";
     //   }
     
        $cLocation = "admin.php?module=NS-Admin&op=main";
        pnRedirect($cLocation);
// fin modif SB in calendar
    }
}

function autodelete($anid, $ok=0)
{
    $module = pnConfigGetVar('module');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['autonews_column'];
    $result = $dbconn->Execute("SELECT {$column['title']}, {$column['aid']}, {$column['catid']}
                               FROM {$pntable['autonews']}
                               WHERE {$column['anid']}='$anid'");
    list($titlean, $aid, $catid) = $result->fields;
    $result->Close();

    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT {$column['title']}
                              FROM {$pntable['stories_cat']}
                              WHERE {$column['catid']}='$catid'");
    list($cattitle) = $result->fields;
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$anid", ACCESS_DELETE)) {
        if($ok && csssafe("postnuke")) {
            $result = $dbconn->Execute("DELETE FROM {$pntable['autonews']}
                                      WHERE {$pntable['autonews_column']['anid']}='$anid'");
            if ($result === false) {
                error_log("stories->autodelete: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->autodelete: Error accessing the database");
            }
            $column = &$pntable['users_column'];
            $result = $dbconn->Execute("UPDATE {$pntable['users']}
                                      SET {$column['counter']} = {$column['counter']} - 1
                                      WHERE {$column['uid']}=$aid");
            if ($result === false) {
                error_log("stories->autodelete: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->autodelete: Error accessing the database");
            }
            pnRedirect('admin.php');
        } else {
            include("header.php");
            GraphicAdmin();
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
            CloseTable();

            OpenTable();
            echo "<center>"._REMOVEAUTOSTORY."<b> $anid - $titlean</b>";
            echo "<table><tr><td>\n";
            echo myTextForm('admin.php', _NO);
            echo "</td><td>\n";
            echo myTextForm("admin.php?module=$module&op=autoDelete&anid=$anid&ok=1", _YES);
            echo "</td></tr></table>\n";
            echo "</center>\n";
            CloseTable();
            include("footer.php");
        }
    } else {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center><b>"._NOTAUTHORIZED1."</b><br><br>"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
    }
}

function autoEdit($anid)
{

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $module = pnVarCleanFromInput('module');
    $automated = pnVarCleanFromInput('automated');

    if (!isset($automated)) {
        $automated = 0;
    }

    $ancolumn = &$pntable['autonews_column'];
    $sccolumn = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $ancolumn[title], $sccolumn[title], $sccolumn[catid]
                               FROM $pntable[autonews], $pntable[stories_cat]
                               WHERE $ancolumn[anid]='$anid'
                               AND $ancolumn[catid]=$sccolumn[catid]");

    list($titleauto, $cattitle, $aid) = $result->fields;
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$anid", ACCESS_EDIT)) {

        include ("header.php");
        $column = &$pntable['autonews_column'];
        $result = $dbconn->Execute("SELECT $column[catid], $column[aid], $column[title],
                                    $column[time], $column[hometext],
                                    $column[bodytext], $column[topic],
                                    $column[informant], $column[notes],
                                    $column[ihome], $column[alanguage],
                                    $column[withcomm]
                                  FROM $pntable[autonews]
                                  WHERE $column[anid]=$anid");

        list($catid, $aid, $title, $time, $hometext, $bodytext, $topic, $informant, $notes, $ihome, $alanguage, $comm) = $result->fields;
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
        $day = $datetime[3];
        $month = $datetime[2];
        $year = $datetime[1];
        $hour = $datetime[4];
        $min = $datetime[5];
        $automated = 1;
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();
        echo "<br>";
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._AUTOSTORYEDIT."</b></font></center><br><br>";

        $format_type = defaultFormatType($hometext, $bodytext);

        storyPreview($title, $hometext, $bodytext, $notes, $topic, $format_type);

        echo "<form action=\"admin.php\" method=\"post\">";
        storyEdit($title, $hometext, $bodytext, $notes, $topic, $ihome, $catid, $alanguage, $comm, $aid, $informant, $format_type);
//        echo "<br><b>"._CHNGPROGRAMSTORY."</b><br><br>";
        buildCalendarMenu(true, $year, $day, $month, $hour, $min);
        echo "<input type=\"hidden\" name=\"anid\" value=\"$anid\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"autoSaveEdit\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
            ."</form>";
        CloseTable();
        include ('footer.php');
    } else {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center><b>"._NOTAUTHORIZED1."</b><br><br>"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
    }
}

function autoSaveEdit()
{
    list($anid,
         $year,
         $day,
         $month,
         $hour,
         $min,
         $subject,
         $hometext,
         $bodytext,
         $topic,
         $notes,
         $catid,
         $ihome,
         $alanguage,
         $comm,
         $format_type_home,
         $format_type_body) = pnVarCleanFromInput('anid',
                                      'year',
                                      'day',
                                      'month',
                                      'hour',
                                      'min',
                                      'subject',
                                      'hometext',
                                      'bodytext',
                                      'topic',
                                      'notes',
                                      'catid',
                                      'ihome',
                                      'alanguage',
                                      'comm',
                                      'format_type_home',
                                      'format_type_body');

    if (!isset($format_type_home)) {
        $format_type_home = 0;
    }

    if (!isset($format_type_body)) {
        $format_type_body = 0;
    }

    $format_type = (($format_type_body%4)*4) + ($format_type_home%4);

    if ($format_type_home == 0)
    {
        $hometext = nl2br($hometext);
    }
    if ($format_type_body == 0)
    {
        $bodytext = nl2br($bodytext);
    }
    $notes = nl2br($notes);

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['autonews_column'];
    $result = $dbconn->Execute("SELECT $column[aid]
                               FROM $pntable[autonews]
                               WHERE $column[anid]='$anid'");
    list($aid) = $result->fields;
    $result->Close();

    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid]='$catid'");
    list($cattitle) = $result->fields;
    $result->Close();

    if (pnSecAuthAction(0, 'Stories::Story', "$aid:$cattitle:$anid", ACCESS_EDIT)) {
        if ($day < 10) {
            $day = "0$day";
        }
        if ($month < 10) {
            $month = "0$month";
        }
        $sec = "00";
        $date = "$year-$month-$day $hour:$min:$sec";
        $title = $subject;
        $column = &$pntable['autonews_column'];
        $sql = "UPDATE $pntable[autonews]
                SET $column[catid]='" . pnVarPrepForStore($catid) . "',
                    $column[title]='" . pnVarPrepForStore($title) . "',
                    $column[time]='" . pnVarPrepForStore($date) . "',
                    $column[hometext]='" . pnVarPrepForStore($hometext) . "',
                    $column[bodytext]='" . pnVarPrepForStore($bodytext) . "',
                    $column[topic]='" . pnVarPrepForStore($topic) . "',
                    $column[notes]='" . pnVarPrepForStore($notes) . "',
                    $column[ihome]='" . pnVarPrepForStore($ihome) . "',
                    $column[alanguage]='" . pnVarPrepForStore($alanguage) . "',
                    $column[withcomm]='" . pnVarPrepForStore($comm) . "'
                WHERE $column[anid]=" . pnVarPrepForStore($anid);
        $result = $dbconn->Execute($sql);
        if ($dbconn->ErrorNo()) {
            error_log("stories->autoSaveEdit: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->autoSaveEdit: Error accesing to the database");
        }
        pnRedirect('admin.php?module=NS-Admin&op=main');
    } else {
        include ('header.php');
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ARTICLEADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center><b>"._NOTAUTHORIZED1."</b><br><br>"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
    }
}

function submissions()
{
    $module = pnVarCleanFromInput('module');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dummy = 0;
    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._SUBMISSIONSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    $lang = languagelist();
    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("SELECT $column[qid], $column[subject], $column[timestamp], $column[alanguage]
                             FROM $pntable[queue] WHERE $column[arcd]='0' ORDER BY $column[timestamp]");
    if($result->EOF) {
        echo "<table width=\"100%\"><tr><td align=\"center\"><b>"
        ._NOSUBMISSIONS."</b> [ <a class=\"pn-normal\" href=\""
        ."admin.php?module=".$module."&op=ListArchive\">"._ARCHIVESUBS."</a> ]</td></tr></table>\n";
    } else {
        echo "<center><font class=\"pn-normal\"><b>"._NEWSUBMISSIONS
        ."</b></font> [ <a class=\"pn-normal\" href=\"admin.php?module=".$module."&op=ListArchive\">"
        ._ARCHIVESUBS."</a> ]<table width=\"100%\" border=\"1\" ><BR>\n";

            while(list($qid, $subject, $timestamp, $alanguage) = $result->fields) {
                echo "<tr>\n";
                echo "<td align=\"center\">";

                echo "<table><tr><td>\n";
                if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_EDIT)) {
                    echo '<form action="admin.php" method="post">'."\n"
                     .'<input type="hidden" name="module" value="NS-AddStory">'."\n"
                     ." <input type=\"hidden\" NAME=\"qid\" VALUE=\"$qid\">"."\n"
                     .'<select name="op" class="pn-text">'."\n"
                     .'<option value="DisplayStory" SELECTED>'._PREVIEWSTORY.'</option>'."\n";
// jgm - this option doesn't work, removeStory() deletes stories from the stories table, not the
//       queue.  Commented out until someone can get around to fixing it
// TODO - create a removeQueue() or whatever to remove the story directly from the queue
//                    if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_DELETE)) {
//                        echo '<option value="RemoveStory">'._DELETE.'</option>'."\n";
//                    }
                    if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_DELETE)) {
                        echo '<option value="ArchiveStory">'._ARCHIVE.'</option>'."\n";
                    }
                echo  '</select>&nbsp;'."\n"
                     .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'
                     .'<input type="submit" value="'._GO.'">'."\n"
                     .'</form>'."\n"
                     .'</center>'."\n";
                }
                echo "</td></tr></table>\n";

                echo "<td width=\"100%\"><font class=\"pn-normal\">\n";

                if ($subject == "") {
                    echo "&nbsp; "._NOSUBJECT."</font>\n";
                } else {
                    echo "&nbsp; ".pnVarPrepHTMLDisplay($subject) ."</font>\n";
                }
                if ($alanguage=='') $lang[$alanguage]=""._ALL."";
                echo "</td><td align=\"center\"><font class=\"pn-normal\">$lang[$alanguage]</font>\n"; /* ML added column to display the language */
                //$timestamp = ereg_replace(" ", "@", $timestamp);
                $formatted_date = ml_ftime(_DATETIMELONG, $dbconn->UnixTimestamp($timestamp));

                echo "</td><td align=\"right\" nowrap><font class=\"pn-normal\">&nbsp;$formatted_date&nbsp;</font></td></tr>\n";
                $dummy++;
                $result->MoveNext();
            }
        if ($dummy < 1) {
        echo "<tr><td align=\"center\"><b>"._NOSUBMISSIONS."</b></form></td></tr></table>\n";
        } else {
        echo "</table></form>\n";
        }
    }
    CloseTable();
    include ("footer.php");
}

function ArchiveStory($qid)
{
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $module = pnVarCleanFromInput('module');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';
    GraphicAdmin();

    OpenTable();
        echo "<center><font class=\"pn-title\">"._ARCHIVE." "._FSTORY."</center></font>";
    CloseTable();

    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("SELECT {$column['subject']}
                              FROM {$pntable['queue']}
                              WHERE {$column['qid']}=$qid");
    if ($result === false) {
        error_log("stories->ArchiveStory: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->ArchiveStory: Error accesing to the database");
    }

    while(list($subject) = $result->fields) {

        echo "<p><font class=\"pn-normal\">"._ARCHIVECHOSE."</p>"
                ."<center>$subject</center></font>";

        $result->MoveNext();
    }
    echo "<form action=\"admin.php\" method=\"post\">"
            ."<p><font class=\"pn-normal\">"._LOOKSRIGHT."<p></font>"
            ."<input type=\"submit\" value=\""._YES."\">"
            ." "._GOBACK.""
            ."<input type=\"hidden\" name=\"qid\" value=\"$qid\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"Archive\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."</form>";

    include('footer.php');
}

function Archive($qid)
{
    $module = pnVarCleanFromInput('module');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';
    GraphicAdmin();

    OpenTable();
        echo "<center><font class=\"pn-title\">"._ARCHIVING."</center></font>";
    CloseTable();

    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("UPDATE {$pntable['queue']}
                              SET {$column['arcd']}='1'
                              WHERE {$column['qid']}=$qid");
    if ($result === false) {
        error_log("stories->Archive: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->Archive: Error accesing to the database");
    }

    echo "<p><font class=\"pn-normal\">"._ARCHIVESUCCESS."</p></font>";
    echo "[ <a class=\"pn-normal\" href=\"admin.php?module=".$module."&op=submissions\">"._SUBMISSIONS."</a> ]";

    include('footer.php');
}

function ListArchive()
{

    $module = pnVarCleanFromInput('module');
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include('header.php');
    GraphicAdmin();

    OpenTable();
        echo "<center><font class=\"pn-title\">"._ARCHIVESUBS."</center></font>";
    CloseTable();

    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("SELECT {$column['qid']}, {$column['subject']},
                                {$column['timestamp']}, {$column['alanguage']}
                              FROM {$pntable['queue']}
                              WHERE {$column['arcd']}='1' ORDER BY {$column['timestamp']}");
    if ($result === false) {
        error_log("stories->ListArchive: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->ListArchive: Error accesing to the database");
    }

    while(list($qid,$subject,$timestamp,$alanguage) = $result->fields) {
    	$formatted_date = ml_ftime(_DATETIMELONG, $dbconn->UnixTimestamp($timestamp));

        echo "<table><tr><td valign=\"top\">\n";
        echo "<p><font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"admin.php?module=".$module
        ."&op=DisplayStory&amp;qid=$qid\">$subject</a> ][ $alanguage ][ $formatted_date ] -- </p></font>";
        echo "</td><td>\n";
        echo myTextForm("admin.php?module=".$module."&op=DeleteStory&amp;qid=$qid&amp;authid=".pnSecGenAuthKey()."", _DELETE);
        echo "</td><td>\n";
        echo myTextForm("admin.php?module=".$module."&op=Unarchive&amp;qid=$qid", _UNARCHIVE);
        echo "</td></tr></table>\n";
        $result->MoveNext();
    }

    include('footer.php');
}

function Unarchive($qid)
{
    
    $module = pnVarCleanFromInput('module');    

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include('header.php');
    GraphicAdmin();

    OpenTable();
        echo "<center><font class=\"pn-title\">"._ARCHIVESUBS."</center></font>";
    CloseTable();

    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("UPDATE $pntable[queue]
                              SET $column[arcd]='0'
                              WHERE $column[qid]=$qid");
    if ($result === false) {
        error_log("stories->Unarchive: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->Unarchive: Error accesing to the database");
    }

    echo "<p><font class=\"pn-normal\">"._UNARCHIVESUCCESS."</p></font>";
    echo "<a class=\"pn-normal\" href=\"admin.php?module=".$module."&op=submissions\">"._SUBMISSIONS."</a>";

    include('footer.php');

}

function addstory_admin_main($var)
{
    //  changed to use pnVarCleanFromInput - skooter
    //  extract($var);
    list($op,
    	 $cat,
         $catid,
         $sid,
         $qid,
         $anid,
         $ok,
         $newcat,
         $title,
         $themeoverride
         ) = pnVarCleanFromInput('op',
         						 'cat',
         						 'catid',
    							 'sid',
         						 'qid',
         						 'anid',
    							 'ok',
    							 'newcat',
    							 'title',
    							 'themeoverride');

    if (!pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_EDIT)) {
       include 'header.php';
       echo _STORIESADDNOAUTH;
       include 'footer.php';
    } else {
      switch($op) {

        case "EditCategory":
            if(!isset($catid)) $catid='';
            EditCategory($catid);
            break;

        case "DelCategory":
            if(!isset($cat)) $cat='';
            DelCategory($cat, $catid);
            break;

        case "YesDelCategory":
            YesDelCategory($catid);
            break;

        case "NoMoveCategory":
            if(!isset($newcat)) $newcat='';
            NoMoveCategory($catid, $newcat);
            break;

        case "SaveEditCategory":
            SaveEditCategory($catid, $title, $themeoverride);
             break;

        case "SelectCategory":
            if(!isset($cat)) $cat='';
            SelectCategory($cat);
            break;

        case "AddCategory":
            AddCategory();
            break;

        case "SaveCategory":
            SaveCategory($title, $themeoverride);
            break;

        case "DisplayStory":
            displayStory();
            break;

        case "PreviewAgain":
            previewStory();
            break;

        case "PostStory":
            postStory();
            break;

        case "EditStory":
            editStory();
            break;

        case "RemoveStory":
            removeStory($sid, $ok);
            break;

        case "ChangeStory":
            changeStory();
            break;

        case "ArchiveStory":
            ArchiveStory($qid);
            break;

        case "Archive":
            Archive($qid);
            break;

        case "ListArchive":
            ListArchive();
            break;

        case "Unarchive":
            Unarchive($qid);
            break;

        case "DeleteStory":
            deleteStory($qid);
            break;

        case "adminStory":
            adminStory();
            break;

        case "PreviewAdminStory":
            previewAdminStory();
            break;

        case "PostAdminStory":
            postAdminStory();
            break;

        case "autoDelete":
            autodelete($anid, $ok);
            break;

        case "autoEdit":
            autoEdit($anid);
            break;

        case "autoSaveEdit":
            autoSaveEdit();
            break;

        case "submissions":
            submissions();
            break;

        default:
            adminStory();
            break;
       }
   }
}

function unnltobr($text) {
    return (preg_replace('/(<br[ \/]*?>)/i', "", $text));
}

?>
