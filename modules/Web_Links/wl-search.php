<?php
// File: $Id: wl-search.php,v 1.5 2002/11/24 22:40:14 nunizgb Exp $ $Name:  $
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
 * search
 */
function search($query, $min, $orderby, $show)
{
    global $datetime;
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!isset($perpage)||!is_numeric($perpage)) $perpage=pnConfigGetVar('perpage');
   // $perpage = pnConfigGetVar('perpage');
    $linksresults = pnConfigGetVar('linksresults');
    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');
    $locale = pnConfigGetVar('locale');

// moved to switch in WebLinks/index.php to satisfy E_ALL warning about undefined variables (Andy Varganov)
//    if (!isset($min)) $min=0;
    if (!isset($max)) $max=$min+$linksresults;
    if($orderby !="title ASC") {
        $orderby = convertorderbyin($orderby);
    }
    if ($show!="") {
        $linksresults = $show;
    } else {
        $show=$linksresults;
    }
    $query = stripslashes($query);

    $column = &$pntable['links_links_column'];
    $fullcountresult=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[title] LIKE '%".pnVarPrepForStore($query)."%' OR $column[description] LIKE '%".pnVarPrepForStore($query)."%' ");
    list($totalselectedlinks) = $fullcountresult->fields;
    $column = &$pntable['links_categories_column'];
    $resultx = $dbconn->Execute("SELECT count(*) FROM $pntable[links_categories] WHERE $column[title] LIKE '%".pnVarPrepForStore($query)."%'");
    list($nrowsx)=$resultx->fields;
    $column = &$pntable['links_links_column'];
    $mysearch = buildSimpleQuery ('links_links', array ('lid', 'cat_id', 'title', 'url', 'description', 'date', 'hits', 'linkratingsummary', 'totalvotes', 'totalcomments'), "$column[title] LIKE '%$query%' OR $column[description] LIKE '%$query%'", $orderby, $linksresults, $min);
    $result = $dbconn->Execute($mysearch);
    $nrows  = $result->PO_RecordCount();
    $x=0;
    include("header.php");
    menu(1);

    OpenTable();
    if ($query != "") {
        if ($nrows>0 OR $nrowsx>0) {
            echo "<font class=\"pn-normal\">"._SEARCHRESULTS4.": <b>$query</b></font><br><br>"
                ."<table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor1']."\"><tr><td><font class=\"pn-normal\"><b>"._USUBCATEGORIES."</b></font></td></tr></table>";
            $column = &$pntable['links_categories_column'];
            $result2 = $dbconn->Execute("SELECT $column[cat_id] FROM $pntable[links_categories] WHERE $column[title] LIKE '%".pnVarPrepForStore($query)."%' ORDER BY $column[title] DESC");
            while(list($cid) = $result2->fields) {

                $result2->MoveNext();
                                if (pnSecAuthAction(0, 'Web Links::Category', "$title::$cid", ACCESS_READ)) {
                                        $stitle = ereg_replace($query, "<b>$query</b>", CatPath($cid,1,1,1));
                        echo "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."\">$stitle</a> (".CountSubLinks($cid).")<br>";
                                }
            }
            echo "<br><table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor1']."\"><tr><td><font class=\"pn-normal\"><b>"._LINKS."</b></font></td></tr></table>";
            $orderbyTrans = convertorderbytrans($orderby);
            echo "<br><font class=\"pn-normal\">"._SORTLINKSBY.": "
                .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=titleA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=titleD\">D</a>)"
                .""._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=dateA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=dateD\">D</a>)"
                .""._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=ratingA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=ratingD\">D</a>)"
                .""._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=hitsA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=hitsD\">D</a>)"
                ."<br>"._SITESSORTED.": $orderbyTrans<br><br>";
            while(list($lid, $cid, $title, $url, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments) = $result->fields) {

                $result->MoveNext();
                    if (pnSecAuthAction(0, 'Web Links::Link', ":$title:$lid", ACCESS_READ) && pnSecAuthAction(0, 'Web Links::Category', "::$cid", ACCESS_READ)) {
                        $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
                        $title = stripslashes($title);
                        $description = stripslashes(pnVarPrepForDisplay($description));
                        $transfertitle = str_replace (" ", "_", $title);
                        $title = ereg_replace($query, "<b>$query</b>", pnVarPrepForDisplay($title));
                        echo "<a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".pnVarPrepForDisplay($lid)."\" target=\"new\">$title</a>";
                        newlinkgraphic($datetime, $time);
                        popgraphic($hits);
                        echo "<br>";
                        $description = ereg_replace($query, "<b>$query</b>", $description);
                        echo ""._WL_DESCRIPTION.": $description<br>";
                        setlocale (LC_TIME, $locale);
                        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
                        $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
                        $datetime = ucfirst($datetime);
                        echo ""._ADDEDON.": ".pnVarPrepForDisplay($datetime)." <br>"._HITS.": ".pnVarPrepForDisplay($hits)."";
                        /* voting & comments stats */
                        if ($totalvotes == 1) {
                            $votestring = _VOTE;
                        } else {
                            $votestring = _VOTES;
                        }
                        if ($linkratingsummary!="0" || $linkratingsummary!="0.0") {
                            echo " "._RATING.": ".pnVarPrepForDisplay($linkratingsummary)." (".pnVarPrepForDisplay($totalvotes)." ".pnVarPrepForDisplay($votestring).")";
                            //removed show star flag, need to make config var - skooter
                            //if ($web_links_show_star) {
                                echo " ".web_links_rateMakeStar($linkratingsummary, 10);
                            //}
                        }
                        LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
                        detecteditorial($lid, $transfertitle);
                        echo "<br>";
                        echo ""._CATEGORY.": ".CatPath($cid,1,1,1)."<br><br>";
                        $x++;
                }
            }
            echo "</font>";
            $orderby = convertorderbyout($orderby);
        } else {
            echo "<br><br><center><font class=\"pn-normal\"><b>"._NOMATCHES."</b></font><br><br>"._GOBACK."<br></center>";
        }
        /* Calculates how many pages exist.  Which page one should be on, etc... */
        $linkpagesint = ($totalselectedlinks / $linksresults);
        $linkpageremainder = ($totalselectedlinks % $linksresults);
        if ($linkpageremainder != 0) {
            $linkpages = ceil($linkpagesint);
            if ($totalselectedlinks < $linksresults) {
                $linkpageremainder = 0;
            }
        } else {
            $linkpages = $linkpagesint;
        }
        /* Page Numbering */
        if ($linkpages!=1 && $linkpages!=0) {
            echo "<br><br>"
                .""._SELECTPAGE.": ";
            $prev=$min-$linksresults;
            if ($prev>=0) {
                echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=".pnVarPrepForDisplay($query)."&amp;min=".pnVarPrepForDisplay($prev)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">"
                    ." &lt;&lt; "._PREVIOUS."</a> ]</b> ";
            }
            $counter = 1;
            $currentpage = ($max / $linksresults);
            while ($counter<=$linkpages ) {
                $cpage = $counter;
                $mintemp = ($perpage * $counter) - $linksresults;
                if ($counter == $currentpage) {
                    echo "<b>".pnVarPrepForDisplay($counter)."</b> ";
                } else {
                    echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=".pnVarPrepForDisplay($query)."&amp;min=".pnVarPrepForDisplay($mintemp)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">".pnVarPrepForDisplay($counter)."</a> ";
                }
                $counter++;
            }
            $next=$min+$linksresults;
            if ($x>=$perpage) {
                echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=".pnVarPrepForDisplay($query)."&amp;min=".pnVarPrepForDisplay($max)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">"
                    ." "._NEXT." &gt;&gt;</a> ]</b>";
            }
        }
        echo "<br><br><center><font class=\"pn-normal\">"
            .""._TRY2SEARCH." \"$query\" "._INOTHERSENGINES."<br>"
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.altavista.com/cgi-bin/query?pg=q&amp;sc=on&amp;hl=on&amp;act=2006&amp;par=0&amp;q=".pnVarPrepForDisplay($query)."&amp;kl=XX&amp;stype=stext\">Alta Vista</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.hotbot.com/?MT=".pnVarPrepForDisplay($query)."&amp;DU=days&amp;SW=web\">HotBot</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.infoseek.com/Titles?qt=".pnVarPrepForDisplay($query)."\">Infoseek</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.dejanews.com/dnquery.xp?QRY=".pnVarPrepForDisplay($query)."\">Deja News</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.lycos.com/cgi-bin/pursuit?query=".pnVarPrepForDisplay($query)."&amp;maxhits=20\">Lycos</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://search.yahoo.com/bin/search?p=".pnVarPrepForDisplay($query)."\">Yahoo</a>"
            ."<br>"
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://es.linuxstart.com/cgi-bin/sqlsearch.cgi?pos=1&amp;query=".pnVarPrepForDisplay($query)."&amp;language=&amp;advanced=&amp;urlonly=&amp;withid=\">LinuxStart</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://search.1stlinuxsearch.com/compass?scope=".pnVarPrepForDisplay($query)."&amp;ui=sr\">1stLinuxSearch</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.google.com/search?q=".pnVarPrepForDisplay($query)."\">Google</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.linuxlinks.com/cgi-bin/search.cgi?query=".pnVarPrepForDisplay($query)."&amp;engine=Links\">LinuxLinks</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.freshmeat.net/search.php?query=".pnVarPrepForDisplay($query)."\">Freshmeat</a> - "
            ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.justlinux.com/bin/search.pl?key=".pnVarPrepForDisplay($query)."\">JustLinux</a>"
            ."</font>";
    } else {
        echo "<center><font class=\"pn-normal\"><b>"._NOMATCHES."</b></font></center><br><br>";
    }
    CloseTable();
    include("footer.php");
}
?>