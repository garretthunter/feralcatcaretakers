<?php
// File: $Id: index.php,v 1.12 2002/12/05 08:38:03 larsneo Exp $ $Name:  $
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
// Original Author of this file: Francisco Burzi
// Purpose of this file:
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

if ((empty($catid)) && (empty($topic))) {
    $index = 1;
} 
else 
{
	$index = 0;
}

// Check if the entered topic or catid are numeric
if ((isset($GLOBALS['topic']) && !empty($GLOBALS['topic']) && !is_numeric($GLOBALS['topic'])) or 
	(isset($GLOBALS['catid']) && !empty($GLOBALS['catid']) && !is_numeric($GLOBALS['catid']))    ) 
{
	include 'header.php';
	OpenTable();
	echo _MODARGSERROR;
	CloseTable();
	include 'footer.php';
}
//End of check

$ModName = $GLOBALS['name'];

modules_get_language();

include_once("modules/$ModName/funcs.php");

automatednews();

/**
 * Prints out the index
 * Prints out the index screen.
 * @return none
 * @author FB
 */


function theindex()
{

	$allstories = &$GLOBALS['allstories']; 

// Furbo: to stop hacking vars on the url
    $topic = pnVarCleanFromInput('topic');
    $catid = pnVarCleanFromInput('catid');
// end furbo hack stop

		
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['stories_column'];
        $querylang = "AND ($column[alanguage]='$currentlang' OR $column[alanguage]='')"; /* the OR is needed to display stories who are posted to ALL languages */
    } else {
        $querylang = '';
    }

    // use a theme override if we're displaying a category
    if ((!empty($catid)) && ($catid > 0)) {
        $column = &$pntable['stories_cat_column'];
        $result = $dbconn->Execute("SELECT $column[themeoverride]
                                  FROM $pntable[stories_cat]
                                  WHERE $column[catid]=".(int)pnVarPrepForStore($catid)."");
        //list($themeOverrideCategory) = $result->fields;
        if ($result) $themeOverrideCategory = $result->fields[0];
    }

    include 'header.php';

    if (pnUserLoggedIn()) {
        $storynum = pnUserGetVar('storynum');
    }
    if (empty($storynum)) {
        $storynum = pnConfigGetVar('storyhome');
    }
//  start eugeniobaldi 20020310
//  When  you view stories for Catid or for topic , you can select of view all stories 
	if ((!empty($catid)) or (!empty($topic))) {
  		if(!empty($allstories) && $allstories == 1) {
     	  	    $storynum = 999;
   		} else {
//magicx : Removed instances of <br />
		    echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=$catid&amp;topic=$topic&amp;allstories=1\">"._SEEALL."</a><br><br>";
   		}
   	}
    $allstories = 0;
// end  eugeniobaldi 20020310

    $storcol = &$pntable['stories_column'];
    $storcatcol = &$pntable['stories_cat_column'];
    $topiccol = &$pntable['topics_column'];
    if (!empty($catid) && !empty($topic)) { // show only one category and one topic
        $result = $dbconn->Execute("UPDATE $pntable[topics] SET $topiccol[counter]=$topiccol[counter]+1 WHERE $topiccol[topicid]=".(int)pnVarPrepForStore($topic)."");
        if($dbconn->ErrorNo() != 0) {
            error_log("DB Error updating $pntable[topics]: "
                    . $dbconn->ErrorNo() . ": "
                    . $dbconn->ErrorMsg());
        }

        $dbconn->Execute("UPDATE $pntable[stories_cat] SET $storcatcol[counter]=$storcatcol[counter]+1 WHERE $storcatcol[catid]=".(int)pnVarPrepForStore($catid)."");
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error updating $pntable[stories_cat]: "
                    . $dbconn->ErrorNo() . ": "
                    . $dbconn->ErrorMsg());
        }

        $whereclause = "$topiccol[topicid]=$topic AND $storcol[catid]=".pnVarPrepForStore($catid)." ";
    } else if (!empty($catid)) { // show only one category
        $dbconn->Execute("UPDATE $pntable[stories_cat] SET $storcatcol[counter]=$storcatcol[counter]+1 WHERE $storcatcol[catid]=".(int)pnVarPrepForStore($catid)."");
        if($dbconn->ErrorNo() != 0) {
            error_log("DB Error updating $pntable[stories_cat]: "
                    . $dbconn->ErrorNo() . ": "
                    . $dbconn->ErrorMsg());
        }

        $whereclause = "$storcol[catid]=$catid ";

    } else if (!empty($topic)) { // show only one category
        $dbconn->Execute("UPDATE $pntable[topics] SET $topiccol[counter]=$topiccol[counter]+1 WHERE $topiccol[topicid]=".(int)pnVarPrepForStore($topic)."");
        if($dbconn->ErrorNo() != 0) {
            error_log("DB Error updating $pntable[topics]: "
                    . $dbconn->ErrorNo() . ": "
                    . $dbconn->ErrorMsg());
        }
// eugeniobaldi 2002-02-18 fixed sf patch # 511223 All Cat. doesn't work whenTopic=#
//        $whereclause = "($topiccol[topicid]=$topic OR $topiccol[topicid]=0) AND ($storcol[ihome]=0 OR $storcol[catid]=0) ";
        $whereclause = "($topiccol[topicid]=".(int)pnVarPrepForStore($topic)." OR $topiccol[topicid]=0) ";
    } else {
        $whereclause = "$storcol[ihome]=0";
    }

    switch (pnConfigGetVar('storyorder')) {
    	case '1':
    		$storyorder = "$storcol[time] DESC";
    		break;
    	default:
    		$storyorder = "$storcol[sid] DESC";
    		break;
    }

    $articles = getArticles($whereclause, $storyorder, $storynum);
    foreach ($articles as $row) {

        // $info is array holding raw information.
        // Used below and also passed to the theme - jgm
        $info = genArticleInfo($row);

        // Need to at least have overview permissions on this story
        // February 19, 2002 -- Rabbitt (aka Carl P. Corliss) -- Added Topics permission check
        if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_OVERVIEW) &&
            pnSecAuthAction(0, 'Topics::Topic',"$info[topicname]::$info[tid]",ACCESS_OVERVIEW)) {

            // $links is an array holding pure URLs to
            // specific functions for this article.
            // Used below and also passed to the theme - jgm
            $links = genArticleLinks($info);

            // $preformat is an array holding chunks of
            // preformatted text for this article.
            // Used below and also passed to the theme - jgm
            $preformat = genArticlePreformat($info, $links);

            if ($GLOBALS['postnuke_theme']) {
                themeindex($info['aid'], $info['informant'], $info['longdatetime'], $info['catandtitle'], $info['counter'], $info['topic'], $preformat['hometext'], $info['notes'], $preformat['more'], $info['topicname'], $info['topicimage'], $info['topictext'], $info, $links, $preformat);
            } else {
                themeindex($info['aid'], $info['informant'], $info['longdatetime'], $info['catandtitle'], $info['counter'], $info['topic'], $preformat['hometext'], $info['notes'], $preformat['more'], $info['topicname'], $info['topicimage'], $info['topictext']);
            }
        }
    }
    include 'footer.php';
}
theindex();
?>