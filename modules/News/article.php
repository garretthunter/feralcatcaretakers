<?php
// File: $Id: article.php,v 1.6 2002/11/18 16:47:27 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on PHP-NUKE Web Portal System
// Copyright (C) 2001 by Francisco Burzi (fbc@mandrakesoft.com)
// http://www.phpnuke.org/
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
// Original Author of this file: Francisco Burzi
// Purpose of this file:
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

if ((empty($sid) && empty($tid)) ||
    (!is_numeric($sid) && !is_numeric($tid))) {
	include 'header.php';
	OpenTable();
	echo _MODARGSERROR;
	CloseTable();
	include 'footer.php';
}

list ($save, $op, $mode, $order, $thold) = pnVarCleanFromInput('save', 'op', 'mode', 'order', 'thold');

$ModName = $GLOBALS['name'];

modules_get_language();

$pntable = pnDBGetTables();

include_once("modules/$ModName/funcs.php");
$page = "modules/News/article.php";
// eugenio themeover 20020413
// pnThemeLoad();


if (isset($save) &&  (pnUserLoggedIn())) {
    $uid = pnUserGetVar('uid');
    $column = &$pntable['users_column'];
    $dbconn->Execute("UPDATE $pntable[users] SET $column[umode]='$mode', $column[uorder]=$order, $column[thold]=$thold WHERE $column[uid]=$uid");
}

if ($op == "Reply") {
    pnRedirect('modules.php?op=modload&name=NS-Comments&file=index'
                      .'&req=Reply&pid=0&sid='.$sid.'&mode='
                      .$mode.'&order='.$order.'&thold='.$thold);
}

// Get the article we're looking at
$results = getArticles("{$pntable['stories_column']['sid']}=$sid", "", "");
$info = genArticleInfo($results[0]);

if (!pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_OVERVIEW) ||
    !pnSecAuthAction(0, 'Topics::Topic', "$info[topicname]::$info[tid]", ACCESS_READ)) {
   include "header.php";
   echo ""._NOTAUTHSTORY." ".$info[title]."";
   include "footer.php";
   exit;
}
$links = genArticleLinks($info);
$preformat = genArticlePreformat($info, $links);

$column = &$pntable['stories_column'];
$dbconn->Execute("UPDATE $pntable[stories] SET $column[counter]=$column[counter]+1 WHERE $column[sid]=$sid");

// set theme overrides prior to header
$themeOverrideCategory = $info['catthemeoverride'];
$themeOverrideStory = $info['themeoverride'];

$artpage = 1;
include ("header.php");
$artpage = 0;

// Backwards compatibility
formatTimestamp(GetUserTime($info['time']));
$notes = $info['notes'];

echo "<table width=\"100%\" border=\"0\"><tr><td valign=\"top\" width=\"85%\">\n";

if ($GLOBALS['postnuke_theme']) {
    themearticle($info['aid'], $info['informant'], $info['time'], $info['catandtitle'], $preformat['maintext'], $info['topic'], $info['topicname'], $info['topicimage'], $info['topictext'], $info, $links, $preformat);
} else {
    themearticle($info['aid'], $info['informant'], $info['time'], $info['catandtitle'], $preformat['maintext'], $info['topic'], $info['topicname'], $info['topicimage'], $info['topictext']);
}

if(pnConfigGetVar('nobox') == 0) {
    echo "</td><td>&nbsp;</td><td valign=\"top\">\n";

    $column = &$pntable['blocks_column'];
    $myquery = buildSimpleQuery ('blocks', array ('active', 'position', 'weight'), "$column[bkey]='login'");
    $result = $dbconn->Execute($myquery);
    while(list($active, $position, $weight) = $result->fields) {

        $result->MoveNext();
        if($active == '1' && $position == 'r'){
            $row = array();
            pnBlockShow('Core', 'login', array('position' => 'r'));
        }
    }

    // Only do topic things if the story had a topic
    if (!empty($info['tid'])) {
        $boxtitle = _RELATED;
        $boxstuff = "<font class=\"pn-normal\">";

        $column = &$pntable['related_column'];
        $myquery = buildSimpleQuery ('related', array ('name', 'url'), "$column[tid]=$info[tid]");
        $result = $dbconn->Execute($myquery);
        while(list($name, $url) = $result->fields) {

            $result->MoveNext();
            $boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"$url\" target=\"new\">$name</a><br>\n";
        }

        $boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" ";
        $boxstuff .= "href=\"modules.php?op=modload&amp;name=Search&amp;file=index&amp;";
        $boxstuff .= "action=search&amp;overview=1&amp;active_stories=1&amp;";
        $boxstuff .= "stories_topics[0]=$info[tid]\"";
        $boxstuff .= ">"._MOREABOUT." $info[topictext]</a><br>\n";

        $boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" ";
        $boxstuff .= "href=\"modules.php?op=modload&amp;name=Search&amp;file=index&amp;";
        $boxstuff .= "action=search&amp;overview=1&amp;active_stories=1&amp;";
        $boxstuff .= "stories_author=$info[informant]\"";
        $boxstuff .= ">"._NEWSBY." $info[informant]</a><br>\n";

        // $boxlink is not defined and can't be found.  Not sure what this code is doing. Comment it out - Skooter.
        //$boxstuff .= $boxlink;

        $boxstuff .= "</font><br><hr noshade width=\"95%\" size=\"1\"><div align=\"center\"><font class=\"pn-normal\">"._MOSTREAD." $info[topictext]:<br>\n";

        // Last story on this topic
        $column = &$pntable['stories_column'];
        $results = getArticles("$column[topic]=$info[tid]", "$column[counter] DESC", 1);

        // # solve on bug #524506
        //
        $ta_row = $results[0];
        $ta_info = genArticleInfo($ta_row);
        $ta_links = genArticleLinks($ta_info);
        $ta_preformat = genArticlePreformat($ta_info, $ta_links);

        $boxstuff .= "$ta_preformat[title]</font></div><br>";
        $boxstuff .= "<table border=\"0\" width=\"100%\"><tr><td align=\"left\">\n";
        $boxstuff .= "</td><td align=\"right\">\n";
        $boxstuff .= "</td></tr></table>\n";

        $tbl = 180;
        $box['title'] = $boxtitle;
        $box['content'] = $boxstuff;
        $box['position'] = 'r';
        themesideblock($box);
    }
}

$tbl = 0;
echo "</td></tr></table>\n";

if ($info['withcomm'] == 0) {
    if ($GLOBALS['mode'] != "nocomments") {
    	if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_READ)) {
 	       include("modules/NS-Comments/index.php");
 	    }
    }
}

include ("footer.php");
?>