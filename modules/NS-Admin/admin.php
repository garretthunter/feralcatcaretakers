<?php
// File: $Id: admin.php,v 1.4 2002/11/25 18:48:50 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
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
// Original Author of file: Pascal Riva
//          (extract of admin.php from Francisco Burzi)
// Purpose of file : common admin tools
// ----------------------------------------------------------------------
// Changelog:
// November 14th, 2001:
//      Chris van de Steeg:
//      Added ADOdb support, changed security-schema to userid:cattitle:storyid
// ================================================================================================================
// internal tools
// ================================================================================================================
function admin_main_automated()
{
   list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_EDIT)) {
        OpenTable();
        echo  '<center><b>'._AUTOMATEDARTICLES.'</b></center>'."\n"
             .'<br>'."\n";
        $count = 0;
        $column = &$pntable['autonews_column'];
        $query = buildSimpleQuery ('autonews', array ('anid', 'catid', 'aid', 'title', 'time', 'alanguage'), "$column[anid]!='" . language_sql('a','AND') . "'", "$column[time] ASC");
        $result = $dbconn->Execute($query);
        if ($result->EOF) {
            echo '<center><i>'._NOAUTOARTICLES.'</i></center>'."\n";
        } else {
            echo '<table border="1" width="100%">'."\n";
            while(list($anid,$catid,$said,$title,$time,$alanguage) = $result->fields) {
                echo '<tr>'."\n";
                if ($alanguage == '') $alanguage = 'x_all';
                if ($count == 0) $count = 1;
                $time = ereg_replace(" ", "@", $time);
                if ($catid == 0) {
                    // Default category
                    $cattitle = ""._ARTICLES."";
                } else {
                    $catcolumn = &$pntable['stories_cat_column'];
                    $catquery = buildSimpleQuery('stories_cat', array('title'), "$catcolumn[catid] = $catid");
                    $catresult = $dbconn->Execute($catquery);
                    list($cattitle) = $catresult->fields;
                }
                if (pnSecAuthAction(0, 'Stories::Story', "$said:$cattitle:", ACCESS_EDIT)) {
                    echo '<td align="right" nowrap>(<a href="admin.php?module=NS-AddStory&op=autoEdit&amp;anid='.$anid.'">'._EDIT.'</a>';
                    if (pnSecAuthAction(0, 'Stories::Story', "$said:$cattitle:", ACCESS_DELETE)) {
                            echo '-<a href="admin.php?module=NS-AddStory&op=autoDelete&amp;anid='.$anid.'">'._DELETE.'</a>'."\n";
                    }
                    echo ')';
                }
                echo  '<td width="100%">&nbsp;'.pnVarPrepForDisplay($title).'&nbsp;</td>'."\n"
                     .'<td align="center">&nbsp;'.language_name($alanguage).'&nbsp;</td>'."\n"
                     .'<td nowrap>&nbsp;'.$time.'&nbsp;</td>'."\n"
                     .'</tr>'."\n";
                $result->MoveNext();
            }
            echo '</table>'."\n";
        }
        CloseTable();
    }
}

function admin_main_article()
{
    $bgcolor1 = $GLOBALS["bgcolor1"];

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $admart = pnConfigGetVar('admart');

    if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_EDIT)) {
        OpenTable();
        echo  '<center><b>'._LAST.' '.pnVarPrepForDisplay($admart).' '._ARTICLES.'</b></center>'."\n"
             .'<br>'."\n"
             .'<table border="1" width="100%" bgcolor="'.$bgcolor1.'">';
        $storiescolumn = &$pntable['stories_column'];
        $topicscolumn = &$pntable['topics_column'];
        if (strcmp(pnConfigGetVar('dbtype'), 'oci8') == 0)   {
            $myquery = "SELECT $storiescolumn[sid],
                               $storiescolumn[cid],
                               $storiescolumn[aid],
                               $storiescolumn[title],
                               $storiescolumn[time],
                               $storiescolumn[topic],
                               $storiescolumn[informant],
                               $storiescolumn[alanguage],
                               $topicscolumn[topicname]
                        FROM $pntable[stories], $pntable[topics]
                        WHERE  $storiescolumn[topic]=$topicscolumn[topicid](+)
                        ORDER BY  $storiescolumn[time] DESC LIMIT $admart";
        } else {
            $myquery = "SELECT $storiescolumn[sid],
                               $storiescolumn[cid],
                               $storiescolumn[aid],
                               $storiescolumn[title],
                               $storiescolumn[time],
                               $storiescolumn[topic],
                               $storiescolumn[informant],
                               $storiescolumn[alanguage],
                               $topicscolumn[topicname]
                        FROM $pntable[stories]
                        LEFT JOIN $pntable[topics] ON $storiescolumn[topic]=$topicscolumn[topicid]
                        ORDER BY  $storiescolumn[time] DESC LIMIT $admart";
        }
        $result = $dbconn->Execute($myquery);
        while(list($sid, $cid, $said, $title, $time, $topic, $informant, $alanguage,$topicname) = $result->fields) {
            if ($alanguage=='') {
                $alanguage = 'x_all';
            }
            formatTimestamp($time);
            if ($title == "") {
                $title = '- No title -';
            }
            echo  '<tr>'."\n"
                 .'<td align="right"><b>'.pnVarPrepForDisplay($sid).'</b></td>'
                 .'<td align="left" width="100%"><a href="modules.php?op=modload&amp;name=News&amp;file=article&amp;sid='.$sid.'">'.pnVarPrepForDisplay($title).'</a></td>'."\n"
                 .'<td align="center">'.language_name($alanguage).'</td>'."\n"
                 .'<td align="right" nowrap>'.pnVarPrepForDisplay($topicname).'</td>'."\n";
            if ($cid == 0) {
                // Default category
                $cattitle = ""._ARTICLES."";
            } else {
                $catcolumn = &$pntable['stories_cat_column'];
                $catquery = buildSimpleQuery('stories_cat', array('title'), "$catcolumn[catid] = $cid");
                $catresult = $dbconn->Execute($catquery);
                list($cattitle) = $catresult->fields;
            }
            if (pnSecAuthAction(0, 'Stories::Story', "$said:$cattitle:", ACCESS_EDIT)) {
                echo '<td align="right" nowrap>(<a href="admin.php?module=NS-AddStory&amp;op=EditStory&amp;sid='.$sid.'">'._EDIT.'</a>';
                if (pnSecAuthAction(0, 'Stories::Story', "$said:$cattitle:", ACCESS_DELETE)) {
                    echo '-<a href="admin.php?module=NS-AddStory&amp;op=RemoveStory&amp;sid='.$sid.'">'._DELETE.'</a>'."\n";
                }
                echo ')</td>';
            } else {
                echo '<td>&nbsp;</td>';
            }
            echo '</tr>'."\n";
            $result->MoveNext();
        }
        echo '</table>'."\n";
        if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_EDIT)) {
            echo  '<center>'."\n"
                 .'<form action="admin.php" method="post">'."\n"
                 .'<input type="hidden" name="module" value="NS-AddStory">'."\n"
                 ._STORYID.' : <input type="text" NAME="sid" SIZE="10">'."\n"
                 .'<select name="op">'."\n"
                 .'<option value="EditStory" SELECTED>'._EDIT.'</option>'."\n";
            if (pnSecAuthAction(0, 'Stories::Story', '::', ACCESS_DELETE)) {
                echo '<option value="RemoveStory">'._DELETE.'</option>'."\n";
            }
            echo  '</select>'."\n"
                 .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'
                 .'<input type="submit" value="'._GO.'">'."\n"
                 .'</form>'."\n"
                 .'</center>'."\n";
        }
        CloseTable();
    }
}

function admin_main_poll()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['poll_desc_column'];
    $myquery = buildSimpleQuery ('poll_desc', array ('polltitle', 'pollid'), language_sql('p'), "$column[pollid] DESC", 1);
    $result = $dbconn->Execute($myquery);
    list($pollTitle, $pid) = $result->fields;
    if (pnSecAuthAction(0, 'Polls::', "$pollTitle::$pid", ACCESS_EDIT)) {
        OpenTable();
        echo "<center>"._CURRENTPOLL.": ".pnVarPrepForDisplay($pollTitle)."</center>";
        CloseTable();
    }
}

// View main admin page
// ====================
   function admin_admin_main($var)
   {
      include 'header.php';
      menu_draw();
      admin_main_automated();
      admin_main_article();
      admin_main_poll();
      include 'footer.php';
   }

?>