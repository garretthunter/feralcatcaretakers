<?php
// File: $Id: ephem.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
$blocks_modules['ephem'] = array(
    'func_display' => 'blocks_ephem_block',
    'text_type' => 'Ephemerids',
    'text_type_long' => 'Ephemerids',
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => true
);

// Security
pnSecAddSchema('Ephemeridsblock::', 'Block title::');

function blocks_ephem_block($row) {

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $currentlang = pnUserGetLang();

    if (!pnSecAuthAction(0, 'Ephemeridsblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['ephem_column'];
        $querylang = "AND ($column[elanguage]='".pnVarPrepForStore($currentlang)."' OR $column[elanguage]='')";
    } else {
        $querylang = "";
    }
    $today = getdate();
    $eday = $today['mday'];
    $emonth = $today['mon'];
    $column = &$pntable['ephem_column'];
    $result = $dbconn->Execute("SELECT $column[yid], $column[content]
                              FROM $pntable[ephem]
                              WHERE $column[did]='".pnVarPrepForStore($eday)."' AND $column[mid]='".pnVarPrepForStore($emonth)."' $querylang");
    $boxstuff = '<span class="pn-normal"><b>'._ONEDAY.'</b></span><br />';

    while(list($yid, $content) = $result->fields) {

        $result->MoveNext();
        $boxstuff .= '<br /><br />';
        $boxstuff .= '<b>'.pnVarPrepForDisplay($yid).'</b><br />'.pnVarPrepHTMLDisplay(nl2br($content)).'';
    }
    if (empty($row['title'])) {
        $row['title'] = _EPHEMERIDS;
    }
    $row['content'] = $boxstuff;
    return themesideblock($row);
}
?>