<?php
// File: $Id: wl-mostpopular.php,v 1.4 2002/11/10 23:30:09 skooter Exp $ $Name:  $
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
 * MostPopular
 */
function MostPopular($ratenum, $ratetype) {
    global $datetime;
    
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $locale = pnConfigGetVar('locale');
    $mostpoplinkspercentrigger = pnConfigGetVar('mostpoplinkspercentrigger');
    $mostpoplinks = pnConfigGetVar('mostpoplinks');
    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');
    
    menu(1);

    OpenTable();
    echo "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">";
    if ($ratenum != "" && $ratetype != "") {
        $mostpoplinks = $ratenum;
        if ($ratetype == "percent") $mostpoplinkspercentrigger = 1;
    }
    if ($mostpoplinkspercentrigger == 1) {
        $toplinkspercent = $mostpoplinks;
        $result=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links]");
        list($totalmostpoplinks) = $result->fields;
        $mostpoplinks = $mostpoplinks / 100;
        $mostpoplinks = $totalmostpoplinks * $mostpoplinks;
        $mostpoplinks = round($mostpoplinks);
        $mostpoplinks = max(1, $mostpoplinks); // ensure we show at least one result!!
        echo "<center><font class=\"pn-normal\"><b>"._MOSTPOPULAR." $toplinkspercent% ("._OFALL." $totalmostpoplinks "._LINKS.")</b></font></center>";
    } else {
    echo "<center><font class=\"pn-normal\"><b>"._MOSTPOPULAR." $mostpoplinks</b></font></center>";
    }
    echo "<tr><td><center><font class=\"pn-normal\">"._SHOWTOP.": [ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=10&amp;ratetype=num\">10</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=25&amp;ratetype=num\">25</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=50&amp;ratetype=num\">50</a> | "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=1&amp;ratetype=percent\">1%</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=5&amp;ratetype=percent\">5%</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=10&amp;ratetype=percent\">10%</a> ]</font></center><br><br></td></tr>";
    $column = &$pntable['links_links_column'];
    $myquery = buildSimpleQuery ('links_links', array ('lid', 'cat_id', 'title', 'description', 'date', 'hits', 'linkratingsummary', 'totalvotes', 'totalcomments'), '', "$column[hits] DESC", $mostpoplinks);
    $result=$dbconn->Execute($myquery);
    echo "<tr><td>";
    while(list($lid, $cid, $title, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments)=$result->fields) {

        $result->MoveNext();
        $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
        echo "<font class=\"pn-title\"><a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".pnVarPrepForDisplay($lid)."\" target=\"new\">".pnVarPrepForDisplay($title)."</a></font>";
        newlinkgraphic($datetime, $time);
        popgraphic($hits);
        echo "<br />";
        echo "<font class=\"pn-normal\">"._WL_DESCRIPTION.": ".pnVarPrepHTMLDisplay($description)."<br>";
        setlocale (LC_TIME, $locale);
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
            //removed show star flag always display star. need to make it a config var - skooter.
            //if ($web_links_show_star) {
            echo " ".web_links_rateMakeStar($linkratingsummary, 10);
            //}
        }
        LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
        detecteditorial($lid, $transfertitle);
        echo "<br /><font class=\"pn-normal\">";
        echo ""._CATEGORY.": ".CatPath($cid,1,1,1)."<br /><br /></font>";
    }
    echo "</font></td></tr></table>";
    CloseTable();
    include("footer.php");
}
?>