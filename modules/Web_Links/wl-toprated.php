<?php
// File: $Id: wl-toprated.php,v 1.3 2002/11/10 23:30:09 skooter Exp $ $Name:  $
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
// 10-15-2002:skooter      - Cross Site Scripting security fixes and also using 
//                           pnAPI for displaying data.
/**
 * TopRated
 */
function TopRated($ratenum, $ratetype)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    
    $toplinkspercentrigger = pnConfigGetVar('toplinkspercentrigger');
    $toplinks = pnConfigGetVar('toplinks');
    $linkvotemin = pnConfigGetVar('linkvotemin');
    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');
    $locale = pnConfigGetVar('locale');

    include("header.php");

    menu(1);

    OpenTable();
    echo "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">";
    if ($ratenum != "" && $ratetype != "") {
        $toplinks = $ratenum;
        if ($ratetype == "percent") {
        $toplinkspercentrigger = 1;
    }
    }
    if ($toplinkspercentrigger == 1) {
        $toplinkspercent = $toplinks;
        $column = &$pntable['links_links_column'];
        $result=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[linkratingsummary]!=0");
        list($totalratedlinks) = $result->fields;
        $toplinks = $toplinks / 100;
        $toplinks = $totalratedlinks * $toplinks;
        $toplinks = round($toplinks);
    }
    if ($toplinkspercentrigger == 1) {
    echo "<center><font class=\"pn-normal\"><b>"._BESTRATED." ".pnVarPrepForDisplay($toplinkspercent)."% ("._OF." ".pnVarPrepForDisplay($totalratedlinks)." "._TRATEDLINKS.")</b></font></center><br>";
    } else {
    echo "<center><font class=\"pn-normal\"><b>"._BESTRATED." ".pnVarPrepForDisplay($toplinks)." </b></font></center><br>";
    }
    echo "</td></tr>"
    ."<tr><td><center><font class=\"pn-normal\">"._NOTE." ".pnVarPrepForDisplay($linkvotemin)." "._TVOTESREQ."<br>"
    .""._SHOWTOP.":  [ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=10&amp;ratetype=num\">10</a> - "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=25&amp;ratetype=num\">25</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=50&amp;ratetype=num\">50</a> | "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=1&amp;ratetype=percent\">1%</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=5&amp;ratetype=percent\">5%</a> - "
        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated&amp;ratenum=10&amp;ratetype=percent\">10%</a> ]</font></center><br><br></td></tr>";
    $column = &$pntable['links_links_column'];
    $mysearch = buildSimpleQuery('links_links', array ('lid', 'cat_id', 'title', 'description', 'date', 'hits', 'linkratingsummary', 'totalvotes', 'totalcomments'), "$column[linkratingsummary] != 0 AND $column[totalvotes]>=$linkvotemin", "$column[linkratingsummary] DESC", $toplinks);
    $result=$dbconn->Execute($mysearch);
    echo "<tr><td>";
    while(list($lid, $cid, $title, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments)=$result->fields) {

        $result->MoveNext();
    $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
    $title = stripslashes($title);
    $description = stripslashes($description);
        echo "<a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".pnVarPrepForDisplay($lid)."\" target=\"new\">".pnVarPrepForDisplay($title)."</a>";
    newlinkgraphic($datetime, $time);
    popgraphic($hits);
    echo "<br>";
    echo ""._DESCRIPTION.": ".pnVarPrepForDisplay($description)."<br>";
    setlocale (LC_TIME, $locale);
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    echo ""._ADDEDON.": ".pnVarPrepForDisplay($datetime)." "._HITS.": ".pnVarPrepForDisplay($hits);
    $transfertitle = str_replace (" ", "_", $title);
    /* voting & comments stats */
        if ($totalvotes == 1) {
        $votestring = _VOTE;
        } else {
        $votestring = _VOTES;
    }
    if ($linkratingsummary!="0" || $linkratingsummary!="0.0") {
        echo " "._RATING.": <b> ".pnVarPrepForDisplay($linkratingsummary)." </b> (".pnVarPrepForDisplay($totalvotes)." ".pnVarPrepForDisplay($votestring).")";
        //removed show star flag need to make it config var. - skooter
        //if ($web_links_show_star) {
            echo " ".web_links_rateMakeStar($linkratingsummary, 10);
        //}
    }
    LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
    detecteditorial($lid, $transfertitle);
    echo "<br>";
    echo ""._CATEGORY." : ".CatPath($cid,1,1,1)."<br><br>";
    }
    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}
?>