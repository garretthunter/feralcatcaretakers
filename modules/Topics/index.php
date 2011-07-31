<?php // $Id: index.php,v 1.3 2002/10/24 11:49:35 magicx Exp $ $Name:  $
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
// Filename: modules/Topics/index.php
// Original Author of file: Francisco Burzi
// Purpose of file: display topics on your site
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

$ModName = basename( dirname( __FILE__ ) );

modules_get_language();

list($dbconn) = pnDBGetConn();
$pntable = pnDBGetTables();

$topicsinrow = pnConfigGetVar('topicsinrow');
$tipath = pnConfigGetVar('tipath');

$count = 0;
$column = &$pntable['topics_column'];
$result = $dbconn->Execute("SELECT $column[topicid], $column[topicname], $column[topicimage], $column[topictext] FROM $pntable[topics] ORDER BY $column[topicname]");
if ($result->EOF) {
    include 'header.php';
    if (!pnSecAuthAction(0, 'Topics::', '::', ACCESS_OVERVIEW)) {
        echo _TOPICSNOAUTH;
        include 'footer.php';
        return;
    }
    echo "<font class=\"pn-title\">"._NOACTIVETOPICS."</font>";
    include 'footer.php';
}
else {
    include 'header.php';
    if (!pnSecAuthAction(0, 'Topics::', '::', ACCESS_OVERVIEW)) {
        echo _TOPICSNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<center><font class=\"pn-title\">"._ACTIVETOPICS."</font><br>\n"
    ."<font class=\"pn-normal\">"._CLICK2LIST."</font></center><br>\n"
    ."<table border=\"0\" width=\"100%\" align=\"center\" cellpadding=\"2\"><tr>\n";
    while(list($topicid, $topicname, $topicimage, $topictext) = $result->fields) {

        $result->MoveNext();
        // someone forgot to add permissions check for Topics::Topic Topicname::TopicId
        // -- Rabbitt (aka Carl P. Corliss)
        if (pnSecAuthAction(0, 'Topics::Topic', "$topicname::$topicid", ACCESS_READ)){

            if ($count == $topicsinrow) {    // changed hardcoded number of topics icons - rwwood
                echo "<tr>\n";
                $count = 0;
            }
	
            echo "<td align=\"center\">\n"
                ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=&amp;topic=". pnVarPrepForDIsplay($topicid)."\"><img src=\"". pnVarPrepForDIsplay($tipath)."". pnVarPrepForDIsplay($topicimage)."\" border=\"0\" alt=\" ".pnVarPrepForDIsplay($topictext)."\"></a><br>\n"
                ."<font class=\"pn-normal\">".pnVarPrepForDIsplay($topictext)."</font>\n"
                ."</td>\n";

            /* Thanks to John Hoffmann from softlinux.org for the next 5 lines ;) */
            $count++;

            if ($count == $topicsinrow) {    // changed hardcoded number of topics icons - rwwood
                echo "</tr>\n";
            }
        }


    }
    if ($count == $topicsinrow) {    // changed hardcoded number of topics icons - rwwood
        echo "</table>\n";
    } else {
        echo "</tr></table>\n";
    }

    CloseTable();
    include 'footer.php';
}
$result->Close();
?>
