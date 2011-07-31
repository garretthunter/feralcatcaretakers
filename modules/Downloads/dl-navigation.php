<?php
// File: $Id: dl-navigation.php,v 1.5 2002/11/19 08:16:54 larsneo Exp $ $Name:  $
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
 * index
 * Display the main links categories
 */
function index() {
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $maindownload = 0;

    if (!pnSecAuthAction(0, 'Downloads::', '::', ACCESS_READ)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        exit;
    }

    menu($maindownload);

    $column = &$pntable['downloads_categories_column'];
    $result=$dbconn->Execute("SELECT $column[cid], $column[title], $column[cdescription]
                            FROM $pntable[downloads_categories]
                            ORDER BY $column[title]");
    $numcats = $result->PO_RecordCount();
    if ($numcats == 0) {
        echo _DOWNLOADSNOCATS;
        include 'footer.php';
    } else {

        OpenTable();
        // Main categories
        echo "<center><font class=\"pn-title\">"._DOWNLOADSMAINCAT."</font></center><br />";
        echo "<table border=\"0\" cellspacing=\"10\" cellpadding=\"0\" align=\"center\">";

        $count = 0;
        echo "<TR>";
        while(list($cid, $title, $cdescription) = $result->fields) {

            $result->MoveNext();

            if (pnSecAuthAction(0, 'Downloads::Category', "$title::$cid", ACCESS_READ)) {
                $cresult = $dbconn->Execute("SELECT count(*) FROM ".$pntable['downloads_downloads'].
                " WHERE ".$pntable['downloads_downloads_column']['cid']."=".pnVarPrepForStore($cid));
                list($cnumrows) = $cresult->fields;

                echo "<td valign=\"top\"><font class=\"pn-normal\">"
				. "<img src=\"modules/".$GLOBALS['ModName']."/images/icon_folder.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;"
				."<a class=\"pn-title\"  href=\"".$GLOBALS['modurl']."&amp;req=viewdownload&amp;cid=$cid\">".pnVarPrepForDisplay($title)."</a> (".pnVarPrepForDisplay($cnumrows).")</font>";
                categorynewdownloadgraphic($cid);
                if ($cdescription) {
                    echo "<br /><font class=\"pn-normal\">".pnVarPrepHTMLDisplay($cdescription)."</font>";
                }
                echo "<br />";

                echo "</td>";
                $count++;
//                if ($count==3) {
                    echo "</tr><tr>";
                    $count = 0;
//                }
            }

        }
        for ($i=$count; $i<3; $i++) {
            echo "<td>&nbsp;</td>";
        }
        echo "</tr></table>";

        // Number of current categories and subcategories
        $result=$dbconn->Execute("SELECT count(*) FROM $pntable[downloads_downloads]");
        list($numrows) = $result->fields;
        /*
         * hootbah: FIXME
         * This can be done in one database hit.
         */
        $result=$dbconn->Execute("SELECT count(*) FROM $pntable[downloads_categories]");
        list($catnum1) = $result->fields;
        $result=$dbconn->Execute("SELECT count(*) FROM $pntable[downloads_subcategories]");
        list($catnum2) = $result->fields;
        $catnum = $catnum1+$catnum2;

        echo "<br /><br /><center><font class=\"pn-normal\">"._THEREARE." ".pnVarPrepForDisplay($numrows)." "._DOWNLOADS." "._AND." ".pnVarPrepForDisplay($catnum)." "._CATEGORIES." "._INDB."</font></center>";
        CloseTable();
        include("footer.php");
    }
}


/**
 * menu
 * builds the standard navigation menu
 * @param maindownload  integer switch. 1 means show _DOWNLOADSMAIN, 0 not.
 */
function menu($maindownload) {
    $user_adddownload = pnConfigGetVar('user_adddownload');
    $query = pnVarCleanFromInput('query');

    if (!pnSecAuthAction(0, 'Downloads::', '::', ACCESS_READ)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><a class=\"pn-logo\"  href=\"".$GLOBALS['modurl']."\">".pnConfigGetVar('sitename').' -- '._DLOADPAGETITLE."</a><br /><br />";
    echo "<form action=\"modules.php\" method=\"post\">"
    ."\n<input type=\"hidden\" name=\"op\" value=\"modload\">"
    ."\n<input type=\"hidden\" name=\"name\" value=\"".$GLOBALS['ModName']."\">"
    ."\n<input type=\"hidden\" name=\"file\" value=\"index\">"
    ."\n<input type=\"hidden\" name=\"req\" value=\"search\">"
    ."\n<input type=\"hidden\" name=\"query\" value=\"$query\">"
    ."\n<font class=\"pn-normal\"><input type=\"text\" size=\"25\" name=\"query\"> <input type=\"submit\" value=\""._SEARCH."\"></font>"
    ."\n</form>";
    echo "<font class=\"pn-normal\">[ ";
    if ($maindownload>0) {
        echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."\">"._DOWNLOADSMAIN."</a> | ";
    }
    if (pnSecAuthAction(0, 'Downloads::Item', '::', ACCESS_COMMENT)) {
        echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=AddDownload\">"._ADDDOWNLOAD."</a>"
        ." | ";
    }
    echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewDownloads\">"._NEW."</a>"
    ." | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular\">"._POPULAR."</a>"
    ." | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated\">"._TOPRATED."</a> ]"
    ."</font></center>";
    CloseTable();
}
?>