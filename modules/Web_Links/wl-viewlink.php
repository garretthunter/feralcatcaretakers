<?php
// File: $Id: wl-viewlink.php,v 1.7 2002/11/24 22:40:14 nunizgb Exp $ $Name:  $
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
 *sortLinksByMenu
 * generates the sort links by menu
 */
function sortLinksByMenu($cid, $orderbyTrans)
{

    echo "<center><font class=\"pn-normal\">"._SORTLINKSBY.": "
        .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=titleA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=titleD\">D</a>) "
        .""._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=dateA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=dateD\">D</a>) "
        .""._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=ratingA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=ratingD\">D</a>) "
        .""._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=hitsA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."&amp;orderby=hitsD\">D</a>)"
        ."<br /><b>"._SITESSORTED.": ".pnVarPrepForDisplay($orderbyTrans)."</b></font></center><br /><br />";
}

/**
 * viewlink
 */
function viewlink($cid, $min, $orderby, $show)
{
    global $datetime;
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column = &$pntable['links_categories_column'];

   if (!isset($perpage)||!is_numeric($perpage)) $perpage=pnConfigGetVar('perpage');
  //  $perpage = pnConfigGetVar('perpage');
    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');
    $locale = pnConfigGetVar('locale');

    // check if this or parent category is accessible to user
    $result=$dbconn->Execute("select $column[parent_id], $column[title] from $pntable[links_categories] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));
    list($parent_id, $title) = $result->fields;
    $result_par=$dbconn->Execute("select $column[title] from $pntable[links_categories] WHERE $column[cat_id]=".(int)pnVarPrepForStore($parent_id));
        list($parent_title) = $result_par->fields;
    if (!pnSecAuthAction(0, 'Web Links::Category', "$title::$cid" , ACCESS_READ) || !pnSecAuthAction(0, 'Web Links::Category', "$parent_title::$parent_id" , ACCESS_READ)) {
        echo "Not authorized";
        include 'footer.php';
        return;
    }

    if (!isset($min)) $min=0;
    if (!isset($max)) $max=$min+$perpage;
    if(isset($orderby)) {
        $orderby = convertorderbyin($orderby);
    } else {
        $orderby = $pntable['links_links_column']['title'] . ' ASC';
    }
    if ($show!="") {
        $perpage = $show;
    } else {
        $show=$perpage;
    }
    menu(1);

    OpenTable();
//    $column = &$pntable['links_categories_column'];
    $result=$dbconn->Execute("SELECT $column[title] , $column[cdescription]
                        FROM $pntable[links_categories]
                        WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid)."");
    list($title, $description) = $result->fields;
    echo "<center><font class=\"pn-normal\"><b>"._CATEGORY.": ".CatPath($cid,1,1,0)."</b></font>"
    ."<br />".pnVarPrepHTMLDisplay($description)."</center><br />";
    $carrytitle = $title;

    $column = &$pntable['links_categories_column'];
    $subresult=$dbconn->Execute("SELECT $column[cat_id], $column[title]
                                        FROM $pntable[links_categories] WHERE $column[parent_id]=".(int)pnVarPrepForStore($cid));
    $numrows = $subresult->PO_RecordCount();
    if ($numrows != 0) {
        $scount = 0;
        echo "<center><font class=\"pn-normal\">"._LALSOAVAILABLE." <i>".pnVarPrepForDisplay($title)."</i> "._SUBCATEGORIES.":</font></center><br />"
            ."<table align=\"center\" border=\"0\"><tr>";
        while(list($sid, $title) = $subresult->fields) {

            $subresult->MoveNext();
            $column = &$pntable['links_links_column'];
            $result2 = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[cat_id]=".(int)pnVarPrepForStore($sid)."");
            list($numrows) = $result2->fields;

            echo "<td><a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".(int)pnVarPrepForStore($sid)."\">"
            .pnVarPrepForDisplay($title)."</a><font class=\"pn-normal\"> (".CountSubLinks($sid).")&nbsp;</font>";
            subcategorynewlinkgraphic($sid);
            echo "&nbsp;&nbsp;</td>";

            $scount++;
            if ($scount==4) {
                echo "</tr><tr>";
                $scount = 0;
            }
        }
        if ($scount != 0) {
            echo "</tr></table>";
        } else {
            echo "<td></td></tr></table>";
        }
        echo "<hr noshade size=\"1\" />";
    }
    $orderbyTrans = convertorderbytrans($orderby);

    sortLinksByMenu($cid, $orderbyTrans);

    $column = &$pntable['links_links_column'];
    $fullcountresult=$dbconn->Execute("SELECT count(*) FROM $pntable[links_links] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid)."");
    list($totalselectedlinks) = $fullcountresult->fields;

    echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";
    $query = buildSimpleQuery ('links_links', array ('lid', 'title', 'description', 'date', 'hits', 'linkratingsummary', 'totalvotes', 'totalcomments'), "$column[cat_id]=".(int)$cid, pnVarPrepForStore($orderby), (int)$perpage, (int)$min);
    $result=$dbconn->Execute($query);
    while(list($lid, $title, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments)=$result->fields) {

        $result->MoveNext();
        if (pnSecAuthAction(0, 'Web Links::Link', ":$title:$lid", ACCESS_READ)) {
                $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
                echo "<a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".(int)$lid."\" target=\"new\">".pnVarPrepForDisplay($title)."</a>";
                newlinkgraphic($datetime, $time);
                popgraphic($hits);
                /* INSERT code for *editor review* here */
                echo "<br />";
                echo ""._WL_DESCRIPTION.": ".pnVarPrepHTMLDisplay($description)."<br />";
                setlocale (LC_TIME, $locale);
                ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
                $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
                $datetime = ucfirst($datetime);
                echo ""._ADDEDON.": ".pnVarPrepForDisplay($datetime)." | "._HITS.": ".(int)$hits;
                $transfertitle = str_replace (" ", "_", $title);
                /* voting & comments stats */
                if ($totalvotes == 1) {
                    $votestring = _VOTE;
                } else {
                    $votestring = _VOTES;
                }
                if ($linkratingsummary!="0" || $linkratingsummary!="0.0") {
                    echo " | "._RATING.": ".pnVarPrepForDisplay($linkratingsummary)." (".(int)$totalvotes." ".pnVarPrepForDisplay($votestring).")";
                    //removed show star flag need to replace with config var - skooter
                    //if ($web_links_show_star) {
                        echo " ".web_links_rateMakeStar($linkratingsummary, 10);
                    //}
                }
                LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
                detecteditorial($lid, $transfertitle);
                echo "<br /><br />";
                }
    }
    echo "</font>";
    $orderby = convertorderbyout($orderby);
    /* Calculates how many pages exist. Which page one should be on, etc... */
    $linkpagesint = ($totalselectedlinks / $perpage);
    $linkpageremainder = ($totalselectedlinks % $perpage);
    if ($linkpageremainder != 0) {
        $linkpages = ceil($linkpagesint);
        if ($totalselectedlinks < $perpage) {
            $linkpageremainder = 0;
        }
    } else {
        $linkpages = $linkpagesint;
    }
    /* Page Numbering */
    if ($linkpages > 1) {
        echo "<br /><br />";
        echo ""._SELECTPAGE.": ";
        $prev=$min-$perpage;
        if ($prev>=0) {
            echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".(int)$cid."&amp;min=".(int)$prev."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">";
            echo " &lt;&lt; "._PREVIOUS."</a> ]</b> ";
        }
        $counter = 1;
        $currentpage = ($max / $perpage);
        while ($counter<=$linkpages ) {
            $cpage = $counter;
            $mintemp = ($perpage * $counter) - $perpage;
            if ($counter == $currentpage) {
                echo "<b>".(int)$counter."</b>&nbsp";
            } else {
                echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".(int)$cid."&amp;min=".(int)$mintemp."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">".(int)$counter."</a> ";
            }
            $counter++;
        }
        $next=$min+$perpage;
        if ($currentpage < $linkpages) {
            echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=".(int)$cid."&amp;min=".(int)$max."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">";
            echo " "._NEXT." &gt;&gt;</a> ]</b> ";
        }
    }
    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}
/* the following function doesnt seem to be needed anywhere. */

function viewslink($sid, $min, $orderby, $show)
{

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!isset($perpage)||!is_numeric($perpage)) $perpage=pnConfigGetVar('perpage');
  //  $perpage = pnConfigGetVar('perpage');
    $locale = pnConfigGetVar('locale');
    $mainvotedecimal = pnConfigGetVar('mainvotedecimal');

    include("header.php");
    menu(1);
    if (!isset($min)) $min=0;
    if (!isset($max)) $max=$min+$perpage;
    if(isset($orderby)) {
        $orderby = convertorderbyin($orderby);
    } else {
        $orderby = convertorderbyin("titleA");
    }
    if ($show!="") {
        $perpage = $show;
    } else {
        $show=$perpage;
    }

    OpenTable();
    $column = &$pntable['links_categories_column'];
    $result2 = $dbconn->Execute("SELECT $column[cid], $column[title] FROM $pntable[links_categories] WHERE $column[cid]=".(int)pnVarPrepForStore($cid)."");
    list($cid, $title) = $result2->fields;

    echo "<center><font class=\"pn-normal\"><b><a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."\">"._MAIN."</a> / <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&ampreq=viewlink&amp;cid=".pnVarPrepForDisplay($cid)."\">".pnVarPrepForDisplay(title)."</a> / ".pnVarPrepForDisplay($stitle)."</b></font></center>";
    $orderbyTrans = convertorderbytrans($orderby);
    echo "<br /><center><font class=\"pn-normal\">"._SORTLINKSBY.": "
        .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=titleA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=titleD\">D</a>)"
        ." "._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=dateA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=dateD\">D</a>)"
        ." "._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=ratingA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=ratingD\">D</a>)"
        ." "._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=hitsA\">A</a>/<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;orderby=hitsD\">D</a>)"
        ."<br /><b>"._SITESSORTED.": ".pnVarPrepForDisplay($orderbyTrans)."</b></font></center><br /><br />";
    $column = &$pntable['links_links_column'];
    $fullcountresult=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[cat_id]=".pnVarPrepForStore($sid));
    list($totalselectedlinks) = $fullcountresult->fields;
    echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";
    $x=0;
    $myquery = buildSimpleQuery ('links_links', array ('lid', 'url', 'title', 'description', 'date', 'hits', 'linkratingsummary', 'totalvotes', 'totalcomments'), "$column[cat_id]=$sid", $orderby, $perpage, $min);
    $result=$dbconn->Execute($myquery);
    while(list($lid, $url, $title, $description, $time, $hits, $linkratingsummary, $totalvotes, $totalcomments)=$result->fields) {

        $result->MoveNext();
        $linkratingsummary = number_format($linkratingsummary, $mainvotedecimal);
        echo "<a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=visit&amp;lid=".pnVarPrepForDisplay($lid)."\" target=\"new\">".pnVarPrepForDisplay($title)."</a>";
        newlinkgraphic($datetime, $time);
        popgraphic($hits);
        // code for *editor review* insert here
        echo "<br />"._WL_DESCRIPTION.": ".pnVarPrepHTMLDisplay($description)."<br />";
        setlocale (LC_TIME, "$locale");
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
        $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
        $datetime = ucfirst($datetime);
        echo ""._ADDEDON.": $datetime "._HITS.": ".pnVarPrepForDisplay($hits);
        $transfertitle = str_replace (" ", "_", $title);
        // voting & comments stats
        if ($totalvotes == 1) {
            $votestring = _VOTE;
        } else {
            $votestring = _VOTES;
        }
        if ($linkratingsummary!="0" || $linkratingsummary!="0.0") {
            echo " "._RATING.": ".pnVarPrepForDisplay($linkratingsummary)." (".pnVarPrepForDisplay($totalvotes)." ".pnVarPrepForDisplay($votestring).")";
            //removed show star flag need to replace with config var. - skooter
            //if ($web_links_show_star) {
                echo " ".web_links_rateMakeStar($linkratingsummary, 10);
            //}
        }
        LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments);
        detecteditorial($lid, $transfertitle);
        echo "<br /><br />";
        $x++;
    }
    echo "</font>";
    $orderby = convertorderbyout($orderby);
    // Calculates how many pages exist.  Which page one should be on, etc...
    $linkpagesint = ($totalselectedlinks / $perpage);
    $linkpageremainder = ($totalselectedlinks % $perpage);
    if ($linkpageremainder != 0) {
        $linkpages = ceil($linkpagesint);
        if ($totalselectedlinks < $perpage) {
            $linkpageremainder = 0;
        }
    } else {
        $linkpages = $linkpagesint;
    }
    // Page Numbering
    if ($linkpages!=1 && $linkpages!=0) {
        echo "<br /><br />"
            .""._SELECTPAGE.": ";
        $prev=$min-$perpage;
        if ($prev>=0) {
            echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;min=".pnVarPrepForDisplay($prev)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">"
            ." &lt;&lt; "._PREVIOUS."</a> ]</b> ";
        }
        $counter = 1;
        $currentpage = ($max / $perpage);
        while ($counter<=$linkpages ) {
            $cpage = $counter;
            $mintemp = ($perpage * $counter) - $perpage;
            if ($counter == $currentpage) {
            echo "<b>".pnVarPrepForDisplay($counter)."</b>&nbsp";
        } else {
            echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;min=".pnVarPrepForDisplay($mintemp)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">".pnVarPrepForDisplay($counter)."</a> ";
        }
            $counter++;
        }
        $next=$min+$perpage;
        if ($x>=$perpage) {
            echo "&nbsp;&nbsp;<b>[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewslink&amp;sid=".pnVarPrepForDisplay($sid)."&amp;min=".pnVarPrepForDisplay($max)."&amp;orderby=".pnVarPrepForDisplay($orderby)."&amp;show=".pnVarPrepForDisplay($show)."\">"
            ." "._NEXT." &gt;&gt;</a> ]</b> ";
        }
    }
    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}
?>