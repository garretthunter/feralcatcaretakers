<?php
// File: $Id: user.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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

$blocks_modules['user'] = array(
    'func_display' => 'blocks_user_block',
    'text_type' => 'User',
    'text_type_long' => "User's Custom Box",
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
    'show_preview' => true
);

// Security
pnSecAddSchema('Userblock::', 'Block title::');

function blocks_user_block($row) {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Userblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    if ((pnUserLoggedIn()) && (pnUserGetVar('ublockon') == 1)) {
        $column = &$pntable['users_column'];
        $uid = pnUserGetVar('uid');
        $getblock = $dbconn->Execute("SELECT $column[ublock] FROM $pntable[users] WHERE $column[uid]=".pnVarPrepForStore($uid)."");
        list($ublock) = $getblock->fields;

        $username = pnUserGetVar('name');
        $row['title'] = _MENUFOR." ".pnVarPrepForDisplay($username)."";
        $row['content'] = $ublock;
        return themesideblock($row);
    }
}
?>
