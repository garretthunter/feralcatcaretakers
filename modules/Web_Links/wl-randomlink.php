<?php
// File: $Id: wl-randomlink.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
 * RandomLink
 */
function RandomLink()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    
    $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links]");
    list($numrows) = $result->fields;
    if ($numrows < 1 ) { // if no data
        include("header.php");
        menu(1);

        OpenTable();
        echo "<font class=\"pn-normal\"><center><b>"._LINKNODATA."</b><br><br>\n";
        echo _GOBACK."</font>\n";
        CloseTable();
        include("footer.php");
        return;
    }
    if ($numrows == 1) {
        $random = 1;
    } else {
        srand((double)microtime()*1000000);
        $random = rand(1,$numrows);
    }
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT $column[url] FROM $pntable[links_links] WHERE $column[lid]='$random'");
    list($url) = $result->fields;
    $dbconn->Execute("UPDATE $pntable[links_links] SET $column[hits]=$column[hits]+1 WHERE $column[lid]=$random");
    Header('Location: '.$url);
}
?>