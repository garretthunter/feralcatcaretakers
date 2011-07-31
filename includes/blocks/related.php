<?php
// File: $Id: related.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
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
// Original Author of file: Patrick Kellum
// Purpose of file: Display releated stories.  Only displayed when reading articles.
// ----------------------------------------------------------------------

$blocks_modules['related'] = array(
    'func_display' => 'blocks_related_block',
    'text_type' => 'Related',
    'text_type_long' => 'Story Related Links',
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
    'show_preview' => false
);

// Security
pnSecAddSchema('Relatedblock::', 'Block title::');

function blocks_related_block($row)
{
    global $sid,
           $story // set by advarticle.php
           ;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Relatedblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    if($story['topic']) {
        $row['content'] = '<font class="pn-normal">';
        $column = &$pntable['stories_column'];
        $sql = "SELECT $column[sid] as sid, $column[title] as title FROM $pntable[stories] WHERE $column[topic]=".pnVarPrepForStore($story['topic'])." ORDER BY $column[counter] DESC";
        $result = $dbconn->SelectLimit($sql,1);
        $mrow = $result->GetRowAssoc(false);
        $result->MoveNext();
        $column = &$pntable['related_column'];
        $result = $dbconn->Execute("SELECT $column[name] as name, $column[url] as url FROM $pntable[related] WHERE $column[tid]=".pnVarPrepForStore($story['topic'])."");
        while(!$result->EOF) {
            $lrow = $result->GetRowAssoc(false);
            $result->MoveNext();
            $row['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"$lrow[url]\" target=\"_blank\">".pnVarPrepForDisplay($lrow['name'])."</a><br>\n";
        }
        $row['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"advtopics.php?topic=$story[topic]\">"._MOREABOUT." ".pnVarPrepForDisplay($story['topicname'])."</a><br>\n"
            ."<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;overview=1&amp;active_stories=1&amp;stories_author=$story[aid]\">"._NEWSBY." ".pnVarPrepForDisplay($story['aid'])."</a><br>\n"
            .'</font><br><hr noshade width="95%" size="1"><b>'._MOSTREAD." ".pnVarPrepForDisplay($story['topicname']).":</b><br>\n"
            ."<center><a href=\"advarticle.php?sid=$mrow[sid]\">".pnVarPrepForDisplay($mrow['title'])."</a></center><br><br>\n"
            .'<div align="right">'
            ."<a href=\"print.php?sid=$mrow[sid]\"><img src=\"images/global/print.gif\" border=\"0\" alt=\""._PRINTER."\"></a>&nbsp;&nbsp;"
            ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Recommend_Us&amp;file=index&amp;req=FriendSend&amp;sid=$sid\"><img src=\"images/global/friend.gif\" border=\"0\" Alt=\""._FRIEND."\"></a>\n"
            .'</div>'
        ;
        return themesideblock($row);
    }
}
?>