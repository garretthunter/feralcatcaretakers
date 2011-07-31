<?php
// $Id: messages.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Original Author of file: Jim McDonald
// Purpose of file: Display admin messages
// ----------------------------------------------------------------------

/**
 * initialise block
 */
function admin_messages_messagesblock_init()
{
    // Security
    pnSecAddSchema('Admin Messages:Messagesblock:', 'Block title::');
}

/**
 * get information on block
 */
function admin_messages_messagesblock_info()
{
    // Values
    return array('text_type' => 'Messages',
                 'module' => 'Admin Messages',
                 'text_type_long' => 'Show admin messages',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}

/**
 * display block
 */
function admin_messages_messagesblock_display($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable =pnDBGetTables();

    if (!isset($row['title'])) {
        $row['title'] = '';
    }

    if (!pnSecAuthAction(0, 'Admin Messages:Messagesblock:', "$row[title]::", ACCESS_READ)) {
        return;
    }

    $messagestable = $pntable['message'];
    $messagescolumn = &$pntable['message_column'];

    if (pnConfigGetVar('multilingual') == 1) {
        $currentlang = pnUserGetLang();
        $querylang = "AND ($messagescolumn[mlanguage]='$currentlang' OR $messagescolumn[mlanguage]='')";
    } else {
        $querylang = '';
    }

    $sql = "SELECT $messagescolumn[mid],
                   $messagescolumn[title],
                   $messagescolumn[content],
                   $messagescolumn[date],
                   $messagescolumn[view]
            FROM $messagestable
            WHERE $messagescolumn[active] = 1 
            AND  ( $messagescolumn[expire] > unix_timestamp(now())
                  OR $messagescolumn[expire] = 0)
            $querylang
            ORDER by $messagescolumn[mid] DESC";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    $output = new pnHTML();

    while (list($mid, $title, $content, $date, $view) = $result->fields) {
        $result->MoveNext();

        $show = 0;
        switch($view) {
            case 1:
                // Message for everyone
                $show = 1;
                break;
            case 2:
                // Message for users
                if (pnUserLoggedIn()) {
                    $show = 1;
                }
                break;
            case 3:
                // Messages for non-users
                if (!pnUserLoggedIn()) {
                    $show = 1;
                }
                break;
            case 4:
                // Messages for administrators of any description
                if (pnSecAuthAction(0, '::', '::', ACCESS_ADMIN)) {
                    $show = 1; 
                }
                break;
        }

        if ($show) {
            list($title,
                 $content) = pnModCallHooks('item',
                                            'transform',
                                            '',
                                            array($title,
                                                  $content));
            $output->TableStart('', '', 0);
            $output->SetInputMode(_PNH_VERBATIMINPUT);
            $output->SetOutputMode(_PNH_RETURNOUTPUT);
            $ttitle = $output->Linebreak();
            $ttitle .= $output->Title($title);
            $ttitle .= $output->Linebreak();
            $output->SetOutputMode(_PNH_KEEPOUTPUT);
            $output->TableAddRow(array("<center>$ttitle</center>" . pnVarPrepHTMLDisplay($content)), 'left');
            $output->SetInputMode(_PNH_PARSEINPUT);
            $output->TableEnd();
        }
    }

    if($output->output != "")
	{
	    // Don't want a title
	    $row['title'] = '';
	    $row['content'] = $output->GetOutput();
	    return themesideblock($row);
	}
}

?>
