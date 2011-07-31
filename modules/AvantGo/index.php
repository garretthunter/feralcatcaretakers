<?php // File: $Id: index.php,v 1.2 2002/11/05 17:28:32 larsneo Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on PHP-NUKE Web Portal System
// Copyright (C) 2001 by Francisco Burzi (fbc@mandrakesoft.com)
// http://www.phpnuke.org/
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
// Original Author of this file: Tim Litwiller http://linux.made-to-order.net/
// Purpose of this file: This module is to view your last news items via
//   Palm or Windows CE devices, using AvantGo software or compatible
//   Palm device browsers.
// ----------------------------------------------------------------------
// Installation: Simply place this file in your root PostNuke install.
// ----------------------------------------------------------------------
// Changelog
// 2001-07-28  Tim Litwiller  converted to PostNuke
// 2000-12-02  Fabian Rodriguez - http://sourceforge.net/users/MagicFab/
//   - changed name of addon
//   - corrected logo image to reflect version 4.2 path
//   - included compliant html tags
//   - made it AvantGo "compliant" (see http://avantgo.com/developer/reference/tutorials/jumpstart/jumpstart.html)
// 2000-11-29  Tim Litwiller  original version
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

$ModName = basename( dirname( __FILE__ ) );
modules_get_language();

header("Content-Type: text/html");
?>

<HTML>
<HEAD>
    <TITLE><?php echo pnConfigGetVar('sitename'); ?></TITLE>
    <META NAME="HandheldFriendly" content="True">
</HEAD>
<BODY>
    <DIV ALIGN=CENTER>
<?php
if (pnSecAuthAction(0, 'AvantGo::', "::", ACCESS_READ)) {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column = &$pntable['stories_column'];
    $sql = "SELECT $column[aid],
                   $column[sid],
                   $column[title],
                   $column[time],
                   $column[catid]
                              FROM $pntable[stories]
                              ORDER BY $column[sid] DESC";
    $result = $dbconn->SelectLimit($sql,10);
    if ($result === false) {
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "An error ocurred");
    } else {
        echo "\t<h1>".pnConfigGetVar('sitename')."</h1>\r\n";
        echo "\t<table border=0 align=center>\r\n";
        echo" \t\t<tr>\r\n";
        echo "\t\t\t<td bgcolor=#EFEFEF>"._AVGARTICLES."</td>\r\n";
        echo "\t\t\t<td bgcolor=#EFEFEF>"._DATE."</td>\r\n";
        echo "\t\t</tr>\r\n";

        while(list($authid, $sid, $title, $time, $catid) = $result->fields) {
            if (!empty($catid)) {
                // Get cattitle from catid
                $catcolumn = &$pntable['stories_cat_column'];
                $sql = "SELECT $catcolumn[title]
                        FROM $pntable[stories_cat]
                        WHERE $catcolumn[catid] = $catid";
                $catresult = $dbconn->Execute($sql);
                list($cattitle) = $catresult->fields;
                $catresult->Close();
            } else {
                $cattitle = '';
            }
            if (pnSecAuthAction(0, 'Stories::Story', "$authid:$cattitle:$sid", ACCESS_READ)) {
                echo "\t\t<tr>\r\n";
                echo "\t\t\t<td><a href=print.php?sid=$sid>".pnVarPrepHTMLDisplay($title)."</a></td>\r\n";
                echo "\t\t\t<td>$time</td>\r\n";
                echo "\t\t</tr>\r\n";
            }
            $result->MoveNext();
        }
        $result->Close();
        echo"\t</table>\r\n";
    }
} else {
    echo "Not authorized to use AvantGo";
}

?>

</BODY>
</HTML>