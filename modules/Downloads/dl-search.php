<?php
// File: $Id: dl-search.php,v 1.5 2002/11/28 04:08:35 skooter Exp $ $Name:  $
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

/**
 * search
 */
function search($query, $min, $orderby, $show) {

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $perpage = pnConfigGetVar('perpage');
    $locale = pnConfigGetVar('locale');
    $downloadsresults = pnConfigGetVar('downloadsresults');

    if (!isset($min) || !is_numeric($min)) $min=0;
    if (!isset($max) || !is_numeric($max)) $max=$min+$downloadsresults;
    if(isset($orderby)) {
        $orderby = convertorderbyin($orderby);
    } else {
        $orderby = $pntable['downloads_downloads_column']['title'] . ' ASC';
    }
    if ($show!="") {
        $downloadsresults = $show;
    } else {
        $show=$downloadsresults;
    }
    $query = stripslashes($query);
    $column = &$pntable['downloads_downloads_column'];
	$sql = "SELECT $column[lid], $column[cid], $column[sid],
                              $column[title], $column[url], $column[description],
                              $column[date], $column[hits], $column[downloadratingsummary],
                              $column[totalvotes], $column[totalcomments],
                              $column[filesize], $column[version], $column[homepage]
                              FROM $pntable[downloads_downloads]
                              WHERE $column[title] LIKE '%".pnVarPrepForStore($query)."%'
                                OR $column[description] LIKE '%".pnVarPrepForStore($query)."%'
                                ORDER BY $pntable[downloads_downloads].$orderby";

    $result = $dbconn->SelectLimit($sql, $downloadsresults, (int)$min);

    $fullcountresult=$dbconn->Execute("SELECT count(*) from $pntable[downloads_downloads]
                                     WHERE $column[title] LIKE '%pnVarPrepForStore($query)%'
                                       OR $column[description] LIKE '%pnVarPrepForStore($query)%' ");
    $totalselecteddownloads = $fullcountresult->PO_RecordCount();
    $nrows  = $result->PO_RecordCount();
    $resultx = $dbconn->Execute("SELECT * FROM ".$pntable['downloads_subcategories'].
                               " WHERE ".$pntable['downloads_subcategories_column']['title']." LIKE '%".pnVarPrepForStore($query).
                               "%' ORDER BY ".$pntable['downloads_subcategories_column']['title']." DESC");
    $nrowsx  = $resultx->PO_RecordCount();
    $x=0;
    include("header.php");
    menu(1);

    OpenTable();
    if ($query != "") {
        if ($nrows>0 OR $nrowsx>0) {
            $column = &$pntable['downloads_subcategories_column'];
            $result2 = $dbconn->Execute("SELECT $column[cid], $column[sid],
                                       $column[title] FROM $pntable[downloads_subcategories]
                                       WHERE $column[title] LIKE '%".pnVarPrepForStore($query)."%'
                                        ORDER BY $column[title] DESC");
            echo "<font class=\"pn-title\">"._SEARCHRESULTS4.": $query</font><br><br>"
            ."<table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><tr><td><font class=\"pn-title\">"._USUBCATEGORIES."</font></td></tr></table>";

            while(list($cid, $sid, $stitle) = $result2->fields) {
				if (downloads_authsubcat($cid, $sid, ACCESS_READ)) {
					$ctitle = downloads_CatNameFromCID($cid);
					$subnumfiles = downloads_SubCatNumItems($sid);

					$ctitle = ereg_replace($query, "<font class=\"pn-normal\">$query</font>", pnVarPrepForDisplay($ctitle));
			        $stitle = ereg_replace($query, "<font class=\"pn-normal\">$query</font>", pnVarPrepForDisplay($stitle));
			        echo "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid\">$ctitle / $stitle</a> ($subnumfiles)<br>";
			        $result2->MoveNext();
				}
			}

            echo "<br><table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><tr><td><font class=\"pn-title\">"._UDOWNLOADS."</font></td></tr></table>";
            $orderbyTrans = convertorderbytrans($orderby);

            echo "<center><font class=\"pn-normal\">"._SORTDOWNLOADSBY.": "
            .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=titleA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=titleD\">D</a>) "
            .""._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=dateA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=dateD\">D</a>) "
            .""._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=ratingA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=ratingD\">D</a>) "
            .""._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=hitsA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=$query&amp;orderby=hitsD\">D</a>)"
            ."<br>"._RESSORTED.": $orderbyTrans</center><br><br><br>";

            while(list($lid, $cid, $sid, $title, $url, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage) = $result->fields) {
                $result->MoveNext();
                $title = ereg_replace($query, "$query", $title); // Skooter - no nead to use pnVarPrepForDisplay here it is done in downloads_outputitem
				downloads_outputitem ($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage, $GLOBALS['modurl'], $GLOBALS['ModName']);

//                $catname = downloads_CatNameFromCID($cid);
//                 echo "<font class=\"pn-normal\">"._CATEGORY.": $ctitle $slash $stitle</font><br><br>";

                  $x++;
                }
            }
            echo "</font>";
            $orderby = convertorderbyout($orderby);

		downloads_outputpagelinks($query, $GLOBALS['modurl'], $orderby, $totalselecteddownloads, $perpage, $min, $max, $show, "search", "query");

        echo "<br><br><center><font class=\"pn-normal\">"
        .""._TRY2SEARCH." \"$query\" "._INOTHERSENGINES."<br>"
        ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://www.google.com/search?q=$query\">Google</a> - "
        ."<a class=\"pn-normal\" target=\"_blank\" href=\"http://search.yahoo.com/bin/search?p=$query\">Yahoo</a>"
        ."</font>";

	} else {
        echo "<center><font class=\"pn-title\">"._NOMATCHES."</center></font><br><br>";
    }
    CloseTable();
    include("footer.php");
}
?>