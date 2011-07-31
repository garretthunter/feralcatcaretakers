<?php
// File: $Id: online.php,v 1.3 2002/12/04 10:32:22 tanis Exp $ $Name:  $
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
// Purpose of file: count number of guests/members online
// 20/09/2001 - modified sql to cope with there being 0 members online
// ----------------------------------------------------------------------

$blocks_modules['online'] = array(
    'func_display' => 'blocks_online_block',
    'text_type' => 'Online',
    'text_type_long' => 'Online',
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => true
);

// Security
pnSecAddSchema('Onlineblock::', 'Block title::');

function blocks_online_block($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Onlineblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    $sessioninfocolumn = &$pntable['session_info_column'];
    $sessioninfotable = $pntable['session_info'];

   $sessioninfocolumn = &$pntable['session_info_column'];
   $sessioninfotable = $pntable['session_info'];
    $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);
    $query = "SELECT count( 1 )
             FROM $sessioninfotable
             WHERE $sessioninfocolumn[lastused] > $activetime AND $sessioninfocolumn[uid] >0
		  GROUP BY $sessioninfocolumn[uid]
		 ";
   $result = $dbconn->Execute($query);
$numusers = $result->RecordCount();
   $result->Close();
    $query2 = "SELECT count( 1 )
             FROM $sessioninfotable
              WHERE $sessioninfocolumn[lastused] > $activetime AND $sessioninfocolumn[uid] = '0'
			  GROUP BY $sessioninfocolumn[ipaddr]
			 ";
    $result2 = $dbconn->Execute($query2);
	$numguests = $result2->RecordCount();
    $result2->Close();
       // Pluralise
    if ($numguests == 1) {
        $guests = _GUEST;
    } else {
        $guests = _GUESTS;
    }
    if ($numusers == 1) {
        $users = _MEMBER;
    } else {
        $users = _MEMBERS;
    }
    $content = "<span class=\"pn-normal\">"._CURRENTLY." ".pnVarPrepForDisplay($numguests)." ".pnVarPrepForDisplay($guests)." "._AND." ".pnVarPrepForDisplay($numusers)." ".pnVarPrepForDisplay($users)." "._ONLINE."<br />\n";

    if (pnUserLoggedIn()) {
        $content .= '<br />'._YOUARELOGGED.' <b>' .pnUserGetVar('uname') . '</b>.<br />';
        $column = &$pntable['priv_msgs_column'];
        $result2 = $dbconn->Execute("SELECT count(*) FROM $pntable[priv_msgs] WHERE $column[to_userid]=" . pnUserGetVar('uid'));
        list($numrow) = $result2->fields;
        if ($numrow == 0) {
            $content .= '<br /></span>';
        } else {
            $content .= "<br />"._YOUHAVE." <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=index\"><b>".pnVarPrepForDisplay($numrow)."</b></a> ";
            if ($numrow==1) { 
               $content .= _PRIVATEMSG ;     
           }
           elseif ($numrow>1) { 
               $content .= _PRIVATEMSGS ;
           }
           $content .= "</span><br />";
        }
    } else {
        $content .= '<br />'._YOUAREANON.'</span><br />';
    }
    if (empty($row['title'])) {
        $row['title'] = _WHOSONLINE;
    }
    $row['content'] = $content;
    return themesideblock($row);
}
?>