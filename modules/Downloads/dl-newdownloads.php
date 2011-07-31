<?php
// File: $Id: dl-newdownloads.php,v 1.4 2002/10/24 19:05:19 skooter Exp $ $Name:  $
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

function NewDownloads($newdownloadshowdays)
{

    include 'header.php';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    menu(1);

    if (!pnSecAuthAction(0, 'Downloads::', '::', ACCESS_READ)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\">"._NEWDOWNLOADS."</font></center><br>";
    $allweekdownloads = 0;
    $allmonthdownloads = 0;
    for ($counter = 0; $counter < 7; $counter++){
        $newdownloaddayRaw = (time()-(86400 * $counter));
        $newdownloadday = date("d-M-Y", $newdownloaddayRaw);
        //$newdownloadView = date("F d, Y", $newdownloaddayRaw);
        $newdownloadView = ml_ftime(_DATEBRIEF, $newdownloaddayRaw);
        $newdownloadDB = Date("Y-m-d", $newdownloaddayRaw);
/* cocomp 2002/07/13 cross db compatibility - can't compare dates using LIKE '%".$date."%'
        $result = $dbconn->Execute("SELECT count(*) FROM ".$pntable['downloads_downloads'].
        " WHERE ".$pntable['downloads_downloads_column']['date']." LIKE '%".$newdownloadDB."%'");
*/
	$newdownloadDB_upper = date("Y-m-d", $newdownloaddayRaw + 86400);
	$sql = "SELECT count(*) FROM ".$pntable['downloads_downloads'] .
		" WHERE " . $pntable['downloads_downloads_column']['date'] . " >= '" . pnVarPrepForStore($newdownloadDB) . "'" .
		" AND " . $pntable['downloads_downloads_column']['date'] . " < '" . pnVarPrepForStore($newdownloadDB_upper) . "'";
	$result = $dbconn->Execute($sql);
        list($totaldownloads) = $result->fields;
        $allweekdownloads = $allweekdownloads + $totaldownloads;
    }
    for ($counter = 0; $counter < 30; $counter++){
        $newdownloaddayRaw = (time()-(86400 * $counter));
        $newdownloadDB = Date("Y-m-d", $newdownloaddayRaw);
/* cocomp 2002/07/13 cross db compatibility - can't compare dates using LIKE '%".$date."%'
        $result = $dbconn->Execute("SELECT count(*) FROM ".$pntable['downloads_downloads'].
        " WHERE ".$pntable['downloads_downloads_column']['date']." LIKE '%".$newdownloadDB."%'");
*/
	$newdownloadDB_upper = date("Y-m-d", $newdownloaddayRaw + 86400);
	$sql = "SELECT count(*) FROM ".$pntable['downloads_downloads'] .
		" WHERE " . $pntable['downloads_downloads_column']['date'] . " >= '" . pnVarPrepForStore($newdownloadDB) . "'" .
		" AND " . $pntable['downloads_downloads_column']['date'] . " < '" . pnVarPrepForStore($newdownloadDB_upper) . "'";
	$result = $dbconn->Execute($sql);
        list($totaldownloads) = $result->fields;
        $allmonthdownloads = $allmonthdownloads + $totaldownloads;
    }
    echo "<center><font class=\"pn-title\">"._TOTALNEWDOWNLOADS.":</font> <font class=\"pn-normal\">"._LASTWEEK." - ".pnVarPrepForDisplay($allweekdownloads)." / "._LAST30DAYS." - ".pnVarPrepForDisplay($allmonthdownloads)."</font><br>"
    ."<font class=\"pn-title\">"._SHOW.": <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewDownloads&amp;newdownloadshowdays=7\">"._1WEEK."</a> - <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewDownloads&amp;newdownloadshowdays=14\">"._2WEEKS."</a> - <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewDownloads&amp;newdownloadshowdays=30\">"._30DAYS."</a>"
    ."</font></center><br>";
    /* List Last VARIABLE Days of Downloads */
    if (!isset($newdownloadshowdays)) {
        $newdownloadshowdays = 7;
    }
    echo "<br><center><font class=\"pn-title\">"._DTOTALFORLAST." ".pnVarPrepForDisplay($newdownloadshowdays)." "._DAYS.":</font><br><br>";
    $allweekdownloads = 0;
    for ($counter = 0; $counter < $newdownloadshowdays; $counter++) {
        $newdownloaddayRaw = (time()-(86400 * $counter));
        $newdownloadday = date("d-M-Y", $newdownloaddayRaw);
        //$newdownloadView = date("F d, Y", $newdownloaddayRaw);
        $newdownloadView = ml_ftime(_DATEBRIEF, $newdownloaddayRaw);
        $newdownloadDB = Date("Y-m-d", $newdownloaddayRaw);
/* cocomp 2002/07/13 cross db compatibility - can't compare dates using LIKE '%".$date."%'
        $result = $dbconn->Execute("select count(*) FROM ".$pntable['downloads_downloads'].
        " WHERE ".$pntable['downloads_downloads_column']['date']." LIKE '%".$newdownloadDB."%'");
*/
	$newdownloadDB_upper = date("Y-m-d", $newdownloaddayRaw + 86400);
	$sql = "SELECT count(*) FROM ".$pntable['downloads_downloads'] .
		" WHERE " . $pntable['downloads_downloads_column']['date'] . " >= '" . pnVarPrepForStore($newdownloadDB) . "'" .
		" AND " . $pntable['downloads_downloads_column']['date'] . " < '" . pnVarPrepForStore($newdownloadDB_upper) . "'";
	$result = $dbconn->Execute($sql);
        list($totaldownloads) = $result->fields;
        $allweekdownloads = $allweekdownloads + $totaldownloads;
        echo "<strong><big>&middot;</big></strong> <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewDownloadsDate&amp;selectdate=$newdownloaddayRaw\">".pnVarPrepForDisplay($newdownloadView)."</a>&nbsp<font class=\"pn-normal\">(".pnVarPrepForDisplay($totaldownloads).")</font><br>";
    }
    $counter = 0;
    $allmonthdownloads = 0;
    echo "</center>";
    CloseTable();
    include 'footer.php';
}

function NewDownloadsDate($selectdate)
{

    $dateDB = (date("d-M-Y", $selectdate));
    $dateView = (date("F d, Y", $selectdate));
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    menu(1);


    if (!pnSecAuthAction(0, 'Downloads::', '::', ACCESS_READ)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    $newdownloadDB = Date("Y-m-d", $selectdate);
/* cocomp 2002/07/13 cross db compatibility - can't compare dates using LIKE '%".$date."%'
    $result = $dbconn->Execute("SELECT count(*) FROM $pntable[downloads_downloads] WHERE {$pntable['downloads_downloads_column']['date']} LIKE '%$newdownloadDB%'");
*/
	$newdownloadDB_upper = date("Y-m-d", $selectdate + 86400);
	$sql = "SELECT count(*) FROM ".$pntable['downloads_downloads'] .
		" WHERE " . $pntable['downloads_downloads_column']['date'] . " >= '" . pnVarPrepForStore($newdownloadDB) . "'" .
		" AND " . $pntable['downloads_downloads_column']['date'] . " < '" . pnVarPrepForStore($newdownloadDB_upper) . "'";
	$result = $dbconn->Execute($sql);
    list($totaldownloads) = $result->fields;
    echo "<font class=\"pn-title\">".pnVarPrepForDisplay($dateView)." - ".pnVarPrepForDisplay($totaldownloads)." "._NEWDOWNLOADS."</font>"
    ."<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"><tr><td><font class=\"pn-normal\">";
    $column = &$pntable['downloads_downloads_column'];
/* cocomp 2002/07/13 cross db compatibility - can't compare dates using LIKE '%".$date."%'
    $result=$dbconn->Execute("SELECT $column[lid], $column[cid], $column[sid], $column[url],
                             $column[title], $column[description], $column[date],
                             $column[hits], $column[downloadratingsummary],
                             $column[totalvotes], $column[totalcomments],
                             $column[filesize], $column[version], $column[homepage]
                            FROM $pntable[downloads_downloads]
                            WHERE $column[date] LIKE '%$newdownloadDB%'
                            ORDER BY $column[title] ASC");
*/
	$sql = "SELECT $column[lid], $column[cid], $column[sid], $column[url],
		$column[title], $column[description], $column[date],
		$column[hits], $column[downloadratingsummary],
		$column[totalvotes], $column[totalcomments],
		$column[filesize], $column[version], $column[homepage]
		FROM $pntable[downloads_downloads]
		WHERE $column[date] >= '" . pnVarPrepForStore($newdownloadDB) . "'
		AND $column[date] < '" . pnVarPrepForStore($newdownloadDB_upper) . "'
		ORDER BY $column[title] ASC";
	$result = $dbconn->Execute($sql);
    while(list($lid, $cid, $sid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage)=$result->fields) {
        $result->MoveNext();
		downloads_outputitem ($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage, $GLOBALS['modurl'], $GLOBALS['ModName']);
    }
    echo "</font></td></tr></table>";
    CloseTable();
    include 'footer.php';
}
?>