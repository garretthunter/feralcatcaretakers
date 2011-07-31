<?php
// File: $Id: wl-newlinks.php,v 1.4 2002/12/01 16:26:21 nunizgb Exp $ $Name:  $
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
// 11-30-2001:ahumphr - created file as part of modularistation

/**
 * NewLinks
 * @usedby index
 */
function NewLinks($newlinkshowdays) {
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._NEWLINKS."</b></font></center><br>";
    $counter = 0;
    $allweeklinks = 0;
    while ($counter <= 7-1){
    $newlinkdayRaw = (time()-(86400 * $counter));
    $newlinkday = date("d-M-Y", $newlinkdayRaw);
    $newlinkView = date("F d, Y", $newlinkdayRaw);
    $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[date] LIKE '%".pnVarPrepForStore($newlinkDB)."%'");
    list($totallinks) = $result->fields;
    $counter++;
    $allweeklinks = $allweeklinks + $totallinks;
    }
    $counter = 0;
    if (!isset($allmonthlinks)) {
    $allmonthlinks = 0;
    }
    while ($counter < 30){
        $newlinkdayRaw = (time()-(86400 * $counter));
        $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[date] LIKE '%".pnVarPrepForStore($newlinkDB)."%'");
        list($totallinks) = $result->fields;
        $allmonthlinks = $allmonthlinks + $totallinks;
        $counter++;
    }
    echo "<center><font class=\"pn-normal\"><b>"._TOTALNEWLINKS.":</b> "._LASTWEEK." - ".pnVarPrepForDisplay($allweeklinks)." \ "._LAST30DAYS." - ".pnVarPrepForDisplay($allmonthlinks)."<br />"
    .""._SHOW.": <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewLinks&amp;newlinkshowdays=7\">"._1WEEK."</a> - <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewLinks&amp;newlinkshowdays=14\">"._2WEEKS."</a> - <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewLinks&amp;newlinkshowdays=30\">"._30DAYS."</a>"
    ."</font></center><br />";
    /* List Last VARIABLE Days of Links */
// moved to switch in Web_Links/index.php to satisfy E_ALL - not very nice, but it works (Andy Varganov)
//    if (!isset($newlinkshowdays)) {
//    $newlinkshowdays = 7;
//    }
    echo "<br /><center><font class=\"pn-normal\"><b>"._TOTALFORLAST." ".pnVarPrepForDisplay($newlinkshowdays)." "._DAYS." :</b></font><br /><br />";
    $counter = 0;
    $allweeklinks = 0;
    while ($counter <= $newlinkshowdays-1) {
        $newlinkdayRaw = (time()-(86400 * $counter));
        $newlinkday = date("d-M-Y", $newlinkdayRaw);
//      $newlinkView = date("F d, Y", $newlinkdayRaw);
        $newlinkView = ml_ftime(_DATEBRIEF, $newlinkdayRaw);
        $newlinkDB = Date("Y-m-d", $newlinkdayRaw);
        $column = &$pntable['links_links_column'];
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[date] LIKE '%".pnVarPrepForStore($newlinkDB)."%'");
        list($totallinks) = $result->fields;
        $counter++;
        $allweeklinks = $allweeklinks + $totallinks;
        echo "<font class=\"pn-normal\"><strong><big>&middot;</big></strong> <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewLinksDate&amp;selectdate=".pnVarPrepForDisplay($newlinkdayRaw)."\">".pnVarPrepForDisplay($newlinkView)."</a>&nbsp(".pnVarPrepForDisplay($totallinks).")</font><br />";
    }
    $counter = 0;
    $allmonthlinks = 0;
    echo "</center>";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index, newlinks,
 */
function NewLinksDate($selectdate) {
    global $datetime;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');
    $locale = pnConfigGetVar('locale');

    $dateDB = (date("d-M-Y", $selectdate));
    $dateView = (ml_ftime(_DATELONG, $selectdate));
    include("header.php");
    menu(1);

    OpenTable();
    $newlinkDB = date("Y-m-d", $selectdate);
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[date] LIKE '%".pnVarPrepForStore($newlinkDB)."%'");
    list($totallinks) = $result->fields;
    echo "<font class=\"pn-normal\"><b>".pnVarPrepForDisplay($dateView)." - ".pnVarPrepForDisplay($totallinks)." "._NEWLINKS."</b></font>"
    ."<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";
    $result=$dbconn->Execute("SELECT $column[lid], $column[cat_id], $column[title], $column[description], $column[date], $column[hits], $column[linkratingsummary], $column[totalvotes], $column[totalcomments] FROM $pntable[links_links] WHERE $column[date] LIKE '%".pnVarPrepForStore($newlinkDB)."%' ORDER BY $column[title] ASC");
    while(list($lid, $cid, $title, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments)=$result->fields) {
    $result->MoveNext();
    $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
    echo "<a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".pnVarPrepForDisplay($lid)."\" target=\"new\">".pnVarPrepForDisplay($title)."</a>";
    newlinkgraphic($datetime, $time);
    popgraphic($hits);
    echo "<br />"._WL_DESCRIPTION.": ".pnVarPrepHTMLDisplay($description)."<br />";
    setlocale (LC_TIME, $locale);
    /* INSERT code for *editor review* here */
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    echo ""._ADDEDON.": ".pnVarPrepForDisplay($datetime)." | "._HITS.": ".pnVarPrepForDisplay($hits);
        $transfertitle = str_replace (" ", "_", $title);
        /* voting & comments stats */
        if ($totalvotes == 1) {
        $votestring = _VOTE;
        } else {
        $votestring = _VOTES;
    }
        if ($linkratingsummary!="0" || $linkratingsummary!="0.0") {
        echo " "._RATING.": ".pnVarPrepForDisplay($linkratingsummary)." (".pnVarPrepForDisplay($totalvotes)." ".pnVarPrepForDisplay($votestring).")";
        //removed show star flag - need to make it config var. - Skooter
        //if ($web_links_show_star){
            echo " ".web_links_rateMakeStar($linkratingsummary, 10);
        //}
    }
    LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
    detecteditorial($lid, $transfertitle);
    echo "<br />";
    echo ""._CATEGORY.": ".CatPath($cid,1,1,1)."<br /><br />";
    }
    echo "</font></td></tr></table>";
    CloseTable();
    include("footer.php");
}
?>