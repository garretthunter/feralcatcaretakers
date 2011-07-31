<?php
// File: $Id: wl-linkeditorial.php,v 1.2 2002/10/16 01:51:52 skooter Exp $ $Name:  $
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
 * @usedby index
 */
function viewlinkeditorial($lid, $ttitle)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");
        if (!(pnSecAuthAction(0, 'Web Links::', '::', ACCESS_READ))) {
            echo _WEBLINKSNOAUTH;
            include 'footer.php';
            return;
        }

    menu(1);

    $column = &$pntable['links_editorials_column'];
    $result=$dbconn->Execute("SELECT $column[adminid], $column[editorialtimestamp], $column[editorialtext], $column[editorialtitle] 
                            FROM $pntable[links_editorials] 
                            WHERE $column[linkid]=".(int)pnVarPrepForStore($lid)."");

    $displaytitle = str_replace ("_", " ", $ttitle);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._LINKPROFILE.": ".pnVarPrepForDisplay($displaytitle)."</b></font><br />";
    linkinfomenu($lid, $ttitle);
    echo "<br /><br />";
    if (!$result->EOF) {
    while(list($adminid, $editorialtimestamp, $editorialtext, $editorialtitle)=$result->fields) {

        $result->MoveNext();

        OpenTable2();
        $formatted_date = ml_ftime(_DATELONG, $dbconn->UnixTimestamp($editorialtimestamp));
		$uname = pnUserGetVar('uname', $adminid);

        echo "<center><font class=\"pn-normal\"><b>".pnVarPrepForDisplay($editorialtitle)."</b></font></center>"
        ."<center><font class=\"pn-sub\">"._EDITORIALBY." ".pnVarPrepForDisplay($uname)." - ".pnVarPrepForDisplay($formatted_date)."</font></center><br /><br />"
        .pnVarPrepHTMLDisplay($editorialtext);
        CloseTable2();
     }
    } else {
        echo "<br /><br /><center><font class=\"pn-normal\"><b>"._NOEDITORIAL."</b></font></center>";
    }
    echo "<br /><br /><center>";
    linkfooter($lid,$ttitle);
    echo "</center>";
    CloseTable();
    include("footer.php");
}
?>