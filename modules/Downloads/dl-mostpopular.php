<?php
// File: $Id: dl-mostpopular.php,v 1.3 2002/10/24 19:05:19 skooter Exp $ $Name:  $
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
 * MostPopular
 */
function MostPopular($ratenum, $ratetype) {
    
    //removed global - skooter
    //global $topdownloadspercent, $totalmostpopdownloads, $datetime, $transferfile;
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    menu(1);

    $mostpopdownloadspercentrigger = pnConfigGetVar('mostpopdownloadspercentrigger');
    $mostpopdownloads = pnConfigGetVar('mostpopdownloads');

    OpenTable();
    echo "<table border=\"0\" width=\"100%\"><tr><td align=\"center\">";
    if ($ratenum != "" && $ratetype != "") {
        $mostpopdownloads = $ratenum;
        if ($ratetype == "percent") $mostpopdownloadspercentrigger = 1;
    }
    if ($mostpopdownloadspercentrigger == 1) {
        $topdownloadspercent = $mostpopdownloads;
        $result=$dbconn->Execute("SELECT count(*) FROM $pntable[downloads_downloads]");
        list($totalmostpopdownloads) = $result->fields;
        $mostpopdownloads = $mostpopdownloads / 100;
        $mostpopdownloads = $totalmostpopdownloads * $mostpopdownloads;
        // skooter - Added if statement so it will return at least one row.
        // In cases where the total downloads is low, this may not return any
        // records and in my opinion it should always return one record.
        if ($mostpopdownloads < .5) {
        	$mostpopdownloads = 1;
       	}
       	else {
        	$mostpopdownloads = round($mostpopdownloads);
        }
    }
    if ($mostpopdownloadspercentrigger == 1) {
        echo "<center><font class=\"pn-title\">"._MOSTPOPULAR." ".pnVarPrepForDisplay($topdownloadspercent)."% ("._OFALL." ".pnVarPrepForDisplay($totalmostpopdownloads)." "._DOWNLOADS.")</font></center>";
    } else {
        echo "<center><font class=\"pn-title\">"._MOSTPOPULAR." ".pnVarPrepForDisplay($mostpopdownloads)."</font></center>";
    }
    echo "<tr><td><center><font class=\"pn-title\">"._SHOWTOP.":</font> [ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=10&amp;ratetype=num\">10</a> - "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=25&amp;ratetype=num\">25</a> - "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=50&amp;ratetype=num\">50</a> | "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=1&amp;ratetype=percent\">1%</a> - "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=5&amp;ratetype=percent\">5%</a> - "
    ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular&amp;ratenum=10&amp;ratetype=percent\">10%</a> ]</center><br><br></td></tr>";
    $column = &$pntable['downloads_downloads_column'];
    $sql = "SELECT $column[lid], $column[cid], $column[sid], $column[url],
                             $column[title], $column[description], $column[date],
                             $column[hits], $column[downloadratingsummary],
                             $column[totalvotes], $column[totalcomments],
                             $column[filesize], $column[version], $column[homepage]
                            FROM $pntable[downloads_downloads]
                            ORDER BY $column[hits] DESC";

    $result=$dbconn->SelectLimit($sql,$mostpopdownloads);

    echo "<tr><td>";

    while(list($lid, $cid, $sid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage)=$result->fields) {
        $result->MoveNext();
		downloads_outputitem ($lid, $url, $title, $description, $time, $hits, $downloadratingsummary, $totalvotes, $totalcomments, $filesize, $version, $homepage, $GLOBALS['modurl'], $GLOBALS['ModName']);
    }
    echo "</font>";
    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}

?>