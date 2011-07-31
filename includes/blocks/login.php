<?php
// File: $Id: login.php,v 1.3 2002/11/02 04:42:49 class007 Exp $ $Name:  $
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

$blocks_modules['login'] = array(
    'func_display' => 'blocks_login_block',
    'text_type' => 'Login',
    'text_type_long' => "User's Login",
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => false
);

// Security
pnSecAddSchema('Loginblock::', 'Block title::');

function blocks_login_block($row) {
	global $HTTP_SERVER_VARS;

    if (empty($row['title'])) {
        $row['title'] = 'Login';
    }
    if (!pnSecAuthAction(0, 'Loginblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }
    if (!pnUserLoggedIn()) {
    // prettified a little with a table for inputs and button to avoid bugs like #493456 (Andy Varganov)
        $boxstuff  = '<form action="user.php" method="post">';
        $boxstuff .= '<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td>';
        $boxstuff .= '<span class="pn-normal">&nbsp;'._BLOCKNICKNAME.'</span></td></tr><tr><td>';
        $boxstuff .= '<input type="text" name="uname" size="14" maxlength="25"></td></tr><tr><td>';
        $boxstuff .= '<span class="pn-normal">&nbsp;'._BLOCKPASSWORD.'</span></td></tr><tr><td>';
        $boxstuff .= '<input type="password" name="pass" size="14" maxlength="20"></td></tr><tr><td>';
        if (pnConfigGetVar('seclevel') != 'High') {
            $boxstuff .= '<input type="checkbox" value="1" name="rememberme" />';
            $boxstuff .= '<span class="pn-normal">&nbsp;'._REMEMBERME.'</span></td></tr><tr><td>';
        }
        $boxstuff .= '<br>';
        $boxstuff .= '<input type="hidden" name="module" value="NS-User" />';
        $boxstuff .= '<input type="hidden" name="op" value="login" />';
        $boxstuff .= '<input type="hidden" name="url" value="' .$HTTP_SERVER_VARS['REQUEST_URI'].'" />';
        $boxstuff .= '<input type="submit" value="'._LOGIN.'" /></td></tr><tr><td>';
        $boxstuff .= '<br /><span class="pn-normal">'._ASREGISTERED.'</span></td></tr><tr><td></table></form>';
        if (empty($row['title'])) {
            $row['title'] = _LOGIN;
        }
        $row['content'] = $boxstuff;
        return themesideblock($row);
    }
}
?>