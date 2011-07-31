<?php // $Id: index.php,v 1.4 2002/11/28 10:17:29 magicx Exp $ $Name:  $
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
// Filename: modules/Submit_News/index.php
// Original Author of file: Francisco Burzi
// Purpose of file: Submit news to site
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = basename( dirname( __FILE__ ) );

modules_get_language();

function defaultDisplay()
{
// ML added global and dropdown with available languages

   // global $ModName, $topic, $sel;

    $ModName = $GLOBALS['ModName'];
    $topic = $GLOBALS['topic'];
    $sel = $GLOBALS['sel'];

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    include ('header.php');
    if (!pnSecAuthAction(0, 'Submit news::', '::', ACCESS_COMMENT)) {
        echo _NOTALLOWED;
        include 'footer.php';
        exit;
    }

    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._SUBMITNEWS."</font><br><br>";
    echo "<font class=\"pn-normal\">"._SUBMITADVICE."</font></center><br>";
    CloseTable();

    OpenTable();

    echo "<p><form action=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\" method=\"post\">"
    ."<font class=\"pn-normal\"><b>"._YOURNAME.":</b> ";
    if (pnUserLoggedIn()) {
        echo "<a class=\"pn-normal\" href=\"user.php\">" . pnUserGetVar('uname') . "</a>";
    } else {
        echo pnConfigGetVar('anonymous');
    }
    echo "<br><br>"
        ."<b>"._SUBTITLE."</b> "
        ."("._BEDESCRIPTIVE.")<br>"
        ."<input type=\"text\" name=\"subject\" size=\"50\" maxlength=\"80\"> "._REQUIRED."<br><font class=\"pn-normal\">("._BADTITLES.")</font>"
        ."<br><br>"
        ."<b>"._TOPIC.":</b> <select name=\"topic\" class=\"pn-text\">";
    $column = &$pntable['topics_column'];
    $toplist = $dbconn->Execute("SELECT $column[topicid], $column[topictext], $column[topicname]
                               FROM $pntable[topics]
                               ORDER BY $column[topictext]");
    echo "<option value=\"\">"._SELECTTOPIC."</option>\n";

    while(list($topicid, $topics, $topicname) = $toplist->fields) {
        if (pnSecAuthAction(0, 'Topics::Topic', "$topicname::$topicid", ACCESS_COMMENT))
        {
            if ($topicid==$topic) {
                $sel = "selected ";
            }
            echo "<option $sel value=\"".pnVarPrepForStore($topicid)."\">".pnVarPrepForDisplay($topics)."</option>\n";
            $sel = "";
        }
        $toplist->MoveNext();
    }
    echo "</select>";

    echo "<br><br><b>"._LANGUAGE.": </b>"; // ML added dropdown , currentlang is pre-selected

    lang_dropdown();

    echo "<br><br><b>"._ARTICLETEXT."</b> "
        ."("._HTMLISFINE.")<br>"
        ."<textarea cols=\"50\" rows=\"12\" name=\"storytext\"></textarea> "._REQUIRED."<br>"
        ."<br><b>"._EXTENDEDTEXT."</b>"
        ."<br><textarea cols=\"50\" rows=\"12\" name=\"bodytext\"></textarea><br>";


    // Show allowable HTML
    echo '<font class="pn-normal">'._ALLOWEDHTML.'&nbsp;';
    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    while (list($key, $access, ) = each($AllowableHTML)) {
        if ($access > 0) echo " &lt;".$key."&gt;";
    }
    echo "<br><br>("._AREYOUSURE.")</font><br><br>"
            ."<input type=\"submit\" name=\"request_preview\" value=\""._PREVIEW."\">";
    echo "</form>";
    CloseTable();
    include ('footer.php');
}

function PreviewStory()
{
    list($name,
         $address,
         $subject,
         $storytext,
         $topic,
         $alanguage,
         $bodytext) = pnVarCleanFromInput('name',
                                          'address',
                                          'subject',
                                          'storytext',
                                          'topic',
                                          'alanguage',
                                          'bodytext');

    $subject    = pnVarCensor($subject);
    $storytext  = pnVarCensor($storytext);
    $bodytext   = pnVarCensor($bodytext);

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

  //  global $bgcolor1, $bgcolor2, $ModName;

    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];
    $ModName = $GLOBALS['ModName'];


    include ('header.php');

    $tipath = pnConfigGetVar('tipath');
    $anonymous = pnConfigGetVar('anonymous');

    if (!pnSecAuthAction(0, 'Submit news::', '::', ACCESS_COMMENT)) {
        echo _NOTALLOWED;
        include 'footer.php';
        exit;
    }

    if($subject == '' || $storytext == '') {
        OpenTable2();
        echo "<font class=\"pn-normal\"><b>"._MPROBLEM."</b> "._NOSUBJECT."</font><br><br><br>";
        echo "<center>"._GOBACK."</center><br><br>";
        CloseTable2();
        include("footer.php");
        exit;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\">"._NEWSUBPREVIEW."</font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-normal\"><i>"._STORYLOOK."</i></font></center><br><br>";
    echo "<table width=\"70%\" bgcolor=\"$bgcolor2\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\"align=\"center\"><tr><td>"
    ."<table width=\"100%\" bgcolor=\"$bgcolor1\" cellpadding=\"8\" cellspacing=\"1\" border=\"0\"><tr><td>";
    if ($topic=="") {
        $topicimage="AllTopics.gif";
        $warning = "<p><b>"._SELECTTOPIC."</b></p>";
    } else {
        $warning = "";
        $column = &$pntable['topics_column'];
        $result = $dbconn->Execute("SELECT $column[topicimage]
                                  FROM $pntable[topics]
                                  WHERE $column[topicid]='".pnVarPrepForStore($topic)."'");
        list($topicimage) = $result->fields;
    }
    echo "<img src=\"$tipath$topicimage\" border=\"0\" align=\"right\" alt=\"Your Topic\">";

    story_preview($subject, $storytext, $bodytext);

    echo "<center>"
    ."".pnVarPrepHTMLDisplay($warning).""
    ."</center>"
    ."</td></tr></table></td></tr></table>"
    ."<br><br><center><font class=\"pn-sub\">"._CHECKSTORY."</font></center>";
    CloseTable();

    OpenTable();
    echo "<p><form action=\"modules.php?op=modload&amp;name=$ModName&amp;file=index\" method=\"post\"><font class=\"pn-normal\">"
    ."<b>"._YOURNAME.":</b> ";
    if (pnUserLoggedIn()) {
        echo "<a class=\"pn-normal\" href=\"user.php\">" . pnUserGetVar('uname') . "</a> <font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"user.php?module=NS-User&amp;op=logout\">"._LOGOUT."</a> ]</font>";
    } else {
        echo "".pnVarPrepForDisplay($anonymous)."";
    }
    echo "<br><br><b>"._SUBTITLE.":</b><br>"
    ."<input type=\"text\" name=\"subject\" size=\"50\" maxlength=\"80\" value=\"" . pnVarPrepForDisplay($subject) . "\"> "
    ._REQUIRED."<br><br><b>"._TOPIC.": </b><select name=\"topic\" class=\"pn-text\">";
    $column = &$pntable['topics_column'];
    $toplist = $dbconn->Execute("SELECT $column[topicid], $column[topictext], $column[topicname]
                               FROM $pntable[topics]
                               ORDER BY $column[topictext]");
    echo "<OPTION VALUE=\"\">"._SELECTTOPIC."</option>\n";
    while(list($topicid, $topics, $topicname) = $toplist->fields) {
        if (pnSecAuthAction(0,'Topics::Topic',"$topicname::$topicid", ACCESS_COMMENT))
        {
            if ($topicid == $topic) {
                $sel="selected";
                echo "<option value=\"$topicid\" $sel>".pnVarPrepForDisplay($topics)."</option>\n";
            } else {
                echo "<option value=\"$topicid\">".pnVarPrepForDisplay($topics)."</option>\n";
            }
            $sel="";
        }
        $toplist->MoveNext();
    }
    echo "</select>";
    echo "<br><br><b>"._LANGUAGE.": </b>";

    lang_dropdown();

    echo"<br><br><b>"._ARTICLETEXT."</b> "
        ."("._HTMLISFINE.")<br>"
        ."<textarea cols=\"50\" rows=\"12\" name=\"storytext\">" . pnVarPrepHTMLDisplay($storytext) . "</textarea> "._REQUIRED."<br>"
        ."<br><b>"._EXTENDEDTEXT."</b>"
        ."<br><textarea cols=\"50\" rows=\"12\" name=\"bodytext\">" . pnVarPrepHTMLDisplay($bodytext) . "</textarea><br>"
        ."<font class=\"pn-normal\">("._AREYOUSURE.")</font><br><br>"
        ."<input type=\"submit\" name=\"request_preview\" value=\""._PREVIEW."\"> <input type=\"submit\" name=\"request_ok\" value=\""._OK."\">"
        ."</font></form>";
    CloseTable();

    include 'footer.php';
}

function submitStory()
{
    list($name,
         $subject,
         $storytext,
         $topic,
         $alanguage,
         $bodytext) = pnVarCleanFromInput('name',
                                          'subject',
                                          'storytext',
                                          'topic',
                                          'alanguage',
                                          'bodytext');

 //   global $EditedMessage, $ModName;

    $EditedMessage = $GLOBALS['EditedMessage'];
    $ModName = $GLOBALS['ModName'];

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Submit news::', '::', ACCESS_COMMENT)) {
        include ('header.php');
        echo _NOTALLOWED;
        include 'footer.php';
        exit;
    }

    if (empty($subject)) {
        include 'header.php';
        ECHO _STORYNEEDSTITLE;
        include 'footer.php';
        exit;
    }

    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');
        $name = pnUserGetVar('uname');
    } else {
        $uid = 1;
		$uname = pnConfigGetVar('anonymous');
    }

    $column = &$pntable['queue_column'];
    $newid = $dbconn->GenId($pntable['queue']);
    $result = $dbconn->Execute("INSERT INTO $pntable[queue] (
                                  $column[qid],
                                  $column[uid],
                                  $column[arcd],
                                  $column[uname],
                                  $column[subject],
                                  $column[story],
                                  $column[timestamp],
                                  $column[topic],
                                  $column[alanguage],
                                  $column[bodytext])
                                VALUES (" . pnVarPrepForStore($newid). ",
                                        '" . pnVarPrepForStore($uid) . "',
                                        '0',
                                        '" . pnVarPrepForStore($name) . "',
                                        '" . pnVarPrepForStore($subject) . "',
                                        '" . pnVarPrepForStore($storytext) . "',
                                        now(),
                                        '" . pnVarPrepForStore($topic) . "',
                                        '" . pnVarPrepForStore($alanguage) . "',
                                        '" . pnVarPrepForStore($bodytext) . "')");

    if($dbconn->ErrorNo()<>0) {
        echo $dbconn->ErrorNo(). ": ".$dbconn->ErrorMsg(). "<br>";
        exit();
    }
    if(pnConfigGetVar('notify')) {
        pnMail(pnConfigGetVar('notify_email'),
             pnConfigGetVar('notify_subject'),
             pnConfigGetVar('notify_message'),
             'From: '.pnConfigGetVar('notify_from')
                     ."\nX-Mailer: PHP/".phpversion());
    }
    include 'header.php';

    OpenTable();
    $column = &$pntable['queue_column'];
    $result = $dbconn->Execute("SELECT count(*) FROM $pntable[queue] WHERE $column[arcd]='0'");
    list($waiting) = $result->fields;
    echo "<center><font class=\"pn-title\">"._SUBSENT."</font><br><br>"
    ."<font class=\"pn-normal\">"._THANKSSUB."<br><br>"
    .""._SUBTEXT.""
    ."<br>"._WEHAVESUB." $waiting "._WAITING."</font></center>";
    CloseTable();
    include ('footer.php');
}

/**
 * Preview function for submitted stories.
 */
function story_preview($title, $hometext, $bodytext="", $notes="") {
    echo "<font class=\"pn-title\"><b>" . pnVarPrepForDisplay($title) . "</b></font><br><br><font class=\"pn-normal\">" . pnVarPrepHTMLDisplay(nl2br($hometext)) . "</font>";
    if ($bodytext != "") {
        echo "<br><br><font class=\"pn-normal\">" . pnVarPrepHTMLDisplay(nl2br($bodytext)) . "</font>";
    }
    if ($notes != "") {
        echo "<br><br><font class=\"pn-normal\"><b>"._NOTE."</b> <i>" . pnVarPrepHTMLDisplay(nl2br($notes)) . "</i></font>";
    }
}

//
// Resolve the action requested: Preview or Ok (i.e. submit it!) ?
// [plamendp]
  //  global $request_preview, $request_ok;

    $request_preview = $GLOBALS['request_preview'];
    $request_ok = $GLOBALS['request_ok'];

$req = "";
if ($request_preview) $req = "PREVIEW";
elseif ($request_ok)  $req = "OK";

switch($req) {

    case "PREVIEW":
        PreviewStory();
        break;

    case "OK":
        SubmitStory();
        break;

    default:
        defaultDisplay();
        break;
}
?>