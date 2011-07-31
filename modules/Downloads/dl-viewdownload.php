<?php
// File: $Id: dl-viewdownload.php,v 1.7 2002/11/26 23:33:14 neo Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
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
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * viewdownload
 */
function viewdownload($cid, $min, $orderby, $show) {
    include("header.php");

    if ((!isset($cid) || !is_numeric($cid)) ||
        (!isset($min) || !is_numeric($min)) ||
        (!isset($orderby))){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    $transfertitle = pnVarCleanFromInput('transfertitle');
    if (!isset($transfertitle)) {
        $transfertitle = '';
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $perpage = pnConfigGetVar('perpage');

    $cattitle = downloads_CatNameFromCID($cid);
    if (!pnSecAuthAction(0, 'Downloads::Category', "$cattitle::$cid", ACCESS_READ)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }

    if (!isset($min) || !is_numeric($min)) $min=0;
    if (!isset($max) || !is_numeric($max)) $max=$min+$perpage;
    if(isset($orderby)) {
        $orderby = convertorderbyin($orderby);
    } else {
        $orderby = $pntable['downloads_downloads_column']['title'] . ' ASC';
    }
    if ($show!="") {
        $perpage = $show;
    } else {
        $show=$perpage;
    }
    menu(1);

    OpenTable();
    $result=$dbconn->Execute("SELECT ".$pntable['downloads_categories_column']['title'].
                            " FROM ".$pntable['downloads_categories'].
                            " WHERE ".$pntable['downloads_categories_column']['cid']."=".(int)pnVarPrepForStore($cid));

    list($title) = $result->fields;
    echo "<center><font class=\"pn-title\">"._CATEGORY.": ".pnVarPrepForDisplay($title)."</font></center><br>";
    $carrytitle = $title;
    $column = &$pntable['downloads_subcategories_column'];
	$sorderby = $pntable['downloads_subcategories_column']['title']. ' ASC';
    $subresult=$dbconn->Execute("SELECT $column[sid], $column[title]
                               FROM $pntable[downloads_subcategories]
                               WHERE $column[cid]=".(int)pnVarPrepForStore($cid).
					           " ORDER BY ".$sorderby);
    if (!$subresult->EOF) {
        $scount = 0;
        echo "<center><font class=\"pn-normal\">"._DLALSOAVAILABLE." ".pnVarPrepForDisplay($title)." "._SUBCATEGORIES.":</font></center><br>"
        ."<table align=\"center\" border=\"0\"><tr>";

        $downloadscolumn = &$pntable['downloads_downloads_column'];
        $downloadstable = $pntable['downloads_downloads'];

        while(list($sid, $title) = $subresult->fields) {

	    if (downloads_authsubcat($cid, $sid, ACCESS_READ)) {
                $result2 = $dbconn->Execute("SELECT count(*)
                                             FROM $downloadstable
                                             WHERE $downloadscolumn[sid]=".(int)pnVarPrepForStore($sid));

                list($numrows) = $result2->fields;
                echo "<td>"
		. "<img src=\"modules/".$GLOBALS['ModName']."/images/icon_folder.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;"
		. "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid\">".pnVarPrepForDisplay($title)."</a> (".pnVarPrepForDisplay($numrows).")&nbsp;&nbsp;</td>";
                $scount++;
                if ($scount==4) {
                    echo "</tr><tr>";
                    $scount = 0;
                }
            }
            $subresult->MoveNext();
        }
        for ($i=$scount; $i<4; $i++) {
            echo "<td>&nbsp;</td>";
        }
        echo "</tr></table>";
    }
    echo "<hr noshade size=\"1\">";
    $orderbyTrans = convertorderbytrans($orderby);
    echo "<center><font class=\"pn-normal\">"._SORTDOWNLOADSBY.": "
        .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=titleA\">A</a>\<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=titleD\">D</a>) "
        .""._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=dateA\">A</a>\<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=dateD\">D</a>) "
        .""._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=ratingA\">A</a>\<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=ratingD\">D</a>) "
        .""._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=hitsA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid&amp;orderby=hitsD\">D</a>)"
        ."<br>"._RESSORTED.":$orderbyTrans</font></center><br><br>";
    $column = &$pntable['downloads_downloads_column'];
    $sql = "SELECT $column[lid], $column[url], $column[title],
                   $column[description], $column[date], $column[hits],
                   $column[downloadratingsummary], $column[totalvotes],
                   $column[totalcomments], $column[filesize],
                   $column[version], $column[homepage]
            FROM $pntable[downloads_downloads]
            WHERE $column[cid]=".pnVarPrepForStore($cid)."
            AND $column[sid]=0 ORDER BY $orderby";

    $result=$dbconn->SelectLimit($sql,$perpage,$min);

    $fullcountresult=$dbconn->Execute("SELECT $column[lid], $column[title],
                                              $column[description], $column[date],
                                              $column[hits], $column[downloadratingsummary],
                                              $column[totalvotes], $column[totalcomments]
                                       FROM $pntable[downloads_downloads]
                                       WHERE $column[cid]=".(int)pnVarPrepForStore($cid)." AND $column[sid]=0");
    $totalselecteddownloads = $fullcountresult->PO_RecordCount();

    echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";

    while(list($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage)=$result->fields) {
        $result->MoveNext();
		# Fixes layout of the description in downloads annoying without this - Neo
		$description = nl2br($description);
	    downloads_outputitem ($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage, $GLOBALS['modurl'], $GLOBALS['ModName']);
    }

    echo "</font>";

    downloads_outputpagelinks($cid, $GLOBALS['modurl'], $orderby, $totalselecteddownloads, $perpage, $min, $max, $show, "viewdownload", "cid");

    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}

function viewsdownload($sid, $min, $orderby, $show)
{
    include("header.php");

    /*
    if ((!isset($sid) || !is_numeric($sid)) ||
        (!isset($min) || !is_numeric($min)) ||
		(!isset($orderby))){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }
    */

    $transfertitle = pnVarCleanFromInput('transfertitle');
    if (!isset($transfertitle)) {
        $transfertitle = '';
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    menu(1);

    $perpage = pnConfigGetVar('perpage');

    $column = &$pntable['downloads_subcategories_column'];
    $result = $dbconn->Execute("SELECT $column[cid], $column[title]
                              FROM $pntable[downloads_subcategories]
                              WHERE $column[sid]=".(int)pnVarPrepForStore($sid));
    list($cid, $stitle) = $result->fields;

    $column = &$pntable['downloads_categories_column'];
    $result2 = $dbconn->Execute("SELECT $column[cid], $column[title]
                               FROM $pntable[downloads_categories]
                               WHERE $column[cid]=".(int)pnVarPrepForStore($cid));
    list($cid, $title) = $result2->fields;

// DJD - Fix - Auth used to be called with Cat name instead of SubCat Name 04/06/2002

    if (downloads_authsubcat($cid, $sid, "ACCESS_READ")) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }

    if (!isset($min)) $min=0;
    if (!isset($max)) $max=$min+$perpage;
    if(isset($orderby)) {
        $orderby = convertorderbyin($orderby);
    } else {
        $orderby = convertorderbyin("titleA");
    }
    if ($show != "") {
        $perpage = $show;
    } else {
        $show = $perpage;
    }

    OpenTable();

    echo "<center><font class=\"pn-title\"><a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."\">"._MAIN."</a> / <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid\">".pnVarPrepForDisplay($title)."</a> / ".pnVarPrepForDisplay($stitle)."</center></font>";
    $orderbyTrans = convertorderbytrans($orderby);

    echo "<br><center><font class=\"pn-normal\">"._SORTDOWNLOADSBY.": "
    .""._TITLE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=titleA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=titleD\">D</a>)"
    ." "._DATE." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=dateA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=dateD\">D</a>)"
    ." "._RATING." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=ratingA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=ratingD\">D</a>)"
    ." "._POPULARITY." (<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=hitsA\">A</a>\<a href=\"".$GLOBALS['modurl']."&amp;req=viewsdownload&amp;sid=$sid&amp;orderby=hitsD\">D</a>)"
    ."<br>"._RESSORTED.": $orderbyTrans</center></font><br><br>";

    $column = &$pntable['downloads_downloads_column'];
    $sql = "SELECT $column[lid], $column[url], $column[title],
                   $column[description], $column[date], $column[hits],
                   $column[downloadratingsummary], $column[totalvotes],
                   $column[totalcomments], $column[filesize],
                   $column[version], $column[homepage]
            FROM $pntable[downloads_downloads]
            WHERE $column[sid]=".(int)pnVarPrepForStore($sid)."
            ORDER BY $orderby";

    $result=$dbconn->SelectLimit($sql,$perpage,$min);
//
// Temporary Fixed       eugeniobaldi 01/07/11
//                            ORDER BY  {$column[$orderby]} LIMIT $min,$perpage");
    $fullcountresult=$dbconn->Execute("SELECT $column[lid], $column[title],
                                              $column[description], $column[date],
                                              $column[hits], $column[downloadratingsummary],
                                              $column[totalvotes], $column[totalcomments]
                                       FROM $pntable[downloads_downloads]
                                       WHERE $column[sid]=".(int)pnVarPrepForStore($sid));

    $totalselecteddownloads = $fullcountresult->PO_RecordCount();

    echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";

    while(list($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage) = $result->fields) {
        $result->MoveNext();
	downloads_outputitem ($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage, $GLOBALS['modurl'], $GLOBALS['ModName']);
    }

    echo "</font>";

    downloads_outputpagelinks($sid, $GLOBALS['modurl'], $orderby, $totalselecteddownloads, $perpage, $min, $max, $show, "viewsdownload", "sid");

    echo "</td></tr></table>";
    CloseTable();
    include 'footer.php';
}
?>