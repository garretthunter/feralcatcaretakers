<?php
// File: $Id: print.php,v 1.3 2002/10/13 19:59:41 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
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
// Purpose of file: Displays a printer friendly (story) page
// ----------------------------------------------------------------------

include 'includes/pnAPI.php';
pnInit();
include 'includes/legacy.php';
// eugenio themeover 20020413
// pnThemeLoad();


if(!isset($sid)  || !is_numeric($sid)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

if (!pnLocalReferer() && pnConfigGetVar('refereronprint')) {
    pnRedirect("modules.php?op=modload&name=News&file=article&sid=$sid");
    exit;
}

function PrintPage($sid) {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    // grab the actual story from the database
    $column = &$pntable['stories_column'];
    $result = $dbconn->Execute("SELECT $column[title],
                                     $column[time],
                                     $column[hometext],
                                     $column[bodytext],
                                     $column[topic],
                                     $column[notes],
                                     $column[cid],
                                     $column[aid]
                              FROM $pntable[stories] where $column[sid] = '".pnVarPrepForStore($sid)."'");
    list($title, $time, $hometext, $bodytext, $topic, $notes, $cid, $aid) = $result->fields;

	if (!isset($title) || $title == '') {
        include 'header.php';
        echo _DBSELECTERROR;
        include 'footer.php';
        exit;
    }

    if ($dbconn->ErrorNo() != 0) {
        include 'header.php';
        echo _DBSELECTERROR;
        include 'footer.php';
        exit;
    }

    // Get data for "autorise check"
    // Just a temp. solution; Print.php needs completely redesign by using getArticles() and genArticleInfo()
    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title] FROM $pntable[stories_cat] WHERE $column[catid] = '".pnVarPrepForStore($cid)."'");
    list($cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Stories::', "$aid:$cattitle:$sid", ACCESS_READ)) {
        include "header.php";
        echo _BADAUTHKEY;
        include "footer.php";
        exit;
    }

    // Increment the read counter
    $column = &$pntable['stories_column'];
    $dbconn->Execute("UPDATE $pntable[stories] SET $column[counter]=$column[counter]+1 WHERE $column[sid]='".pnVarPrepForStore($sid)."'");

    // grab the topictext for the story
    $column2 = &$pntable['topics_column'];
    $result2 = $dbconn->Execute("SELECT $column2[topictext] FROM $pntable[topics] WHERE $column2[tid]='".pnVarPrepForStore($topic)."'");
    list($topictext) = $result2->fields;

    $datetime = formatTimestamp($time);

    $cWhereIsPerso = WHERE_IS_PERSO;

    if (!empty($cWhereIsPerso)) {
        include("modules/NS-Multisites/print.inc.php");
    } else {
        $themesarein = "";

        $ThemeSel = pnUserGetTheme();
    }

    pnModAPILoad('Wiki', 'user');
    list($title,
         $hometext,
         $bodytext,
         $notes) = pnModAPIFunc('wiki',
                                'user',
                                'transform',
                                array('objectid' => $sid,
                                      'extrainfo' => array($title,
                                                           $hometext,
                                                           $bodytext,
                                                           $notes)));

    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n"
        ."<html>\n"
        ."<head><title>" . pnConfigGetVar('sitename') . "</title>\n";

    if (defined("_CHARSET") && _CHARSET != "") {
        echo "<META HTTP-EQUIV=\"Content-Type\" ".
            "CONTENT=\"text/html; charset="._CHARSET."\">\n";
    }

	//changed to local stylesheet
    //echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$themesarein."themes/$ThemeSel/style/style.css\">";
    echo "<style type=\"text/css\">\n"
        ."<!--\n"
		.".print-title {\n"
		."background-color: transparent;\n"
		."color: #000000;\n"
		."font-family: Tahoma, Verdana, sans-serif;\n"
		."font-size: 12px;\n"
		."font-weight: bold;\n"
		."text-decoration: none;\n"
		."}\n"
		.".print-sub {\n"
		."background-color: transparent;\n"
		."color: #000000;\n"
		."font-family: Tahoma, Verdana, sans-serif;\n"
		."font-size: 10px;\n"
		."font-weight: normal;\n"
		."text-decoration: none;\n"
		."}\n"
		.".print-normal {\n"
		."background-color: transparent;\n"
		."color: #000000;\n"
		."font-family: Tahoma, Verdana, sans-serif;\n"
		."font-size: 11px;\n"
		."font-weight: normal;\n"
		."text-decoration: none;\n"
		."}\n"
        .".print {\n"  
        ."color: #000000;\n" 
        ."background-color: #FFFFFF;\n"
        ."}\n"
        ."-->\n"
        ."</style>\n";

    echo "</head>\n"
    	."<body class=\"print\" bgcolor=\"#FFFFFF\" text=\"#000000\">\n"
    	."<center>\n<table border=\"0\">\n<tr><td>\n<table border=\"0\" width=\"100%\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#FFFFFF\">\n"
    	."<tr><td>\n"
    	."<table border=\"0\" width=\"100%\" cellpadding=\"20\" cellspacing=\"1\" bgcolor=\"#FFFFFF\">\n"
        ."<tr><td align=\"center\">\n"
    	."<img src=\"".WHERE_IS_PERSO."images/" . pnConfigGetVar('site_logo') . "\" border=\"0\" alt=\"".pnConfigGetVar('sitename')."\">\n"
        ."<br /><br />\n"
    	."<font class=\"print-title\">" . pnVarPrepHTMLDisplay($title) . "</font><br />\n"
    	."<font class=\"print-sub\">"._DATE.": $datetime<br />\n"._TOPIC." $topictext</font>\n"
        ."<br /><br />\n"
        ."</td></tr>\n"
        ."<tr><td>\n"
    	."<font class=\"print-normal\">"
		. pnVarPrepHTMLDisplay($hometext) . "<br />\n";

    	if (!empty($bodytext)) {
			echo pnVarPrepHTMLDisplay($bodytext) . "<br />\n";
		}
		if (!empty($notes)) {
			echo pnVarPrepHTMLDisplay($notes) . "<br />\n";
    	} else {
        	echo "<br />\n";
    	}

    echo "</font>\n"
    	."</td></tr>\n"
    	."<tr><td align=\"center\">\n"
    	."<font class=\"print-normal\">\n"
    	.""._COMESFROM." " . pnConfigGetVar('sitename') . "<br />\n"
    	."<a class=\"print-normal\" href=\"" . pnGetBaseURL() . "\">"
    	.pnGetBaseURL()
    	."</a>\n"
    	."<br /><br />\n"
    	.""._THEURL.""
    	."<br />\n"
    	."<a class=\"print-normal\" href=\"" . pnGetBaseURL() . "modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid\">"
    	. pnGetBaseURL() . "modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid"
    	."</a>\n"
    	."</font>\n"
    	."</td></tr>\n"
    	."</table>\n</td></tr>\n</table>\n"
    	."</td>\n"
    	."</tr>\n"
    	."</table>\n"
    	."</center>\n"
    	."</body>\n"
    	."</html>\n";

}

PrintPage($sid);

?>