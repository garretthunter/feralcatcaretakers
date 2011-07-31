<?php
// File: $Id: past.php,v 1.3 2002/11/18 16:48:01 larsneo Exp $
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

$blocks_modules['past'] = array(
    'func_display' => 'blocks_past_block',
    'func_edit' => 'blocks_past_select',
    'func_update' => 'blocks_past_update',
    'text_type' => 'Past',
    'text_type_long' => 'Past Articles',
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => true
    );

// Security
pnSecAddSchema('Pastblock::', 'Block title::');

// Story functions
include_once('modules/News/funcs.php');

function blocks_past_block($row)
{
    $catid = pnVarCleanFromInput('catid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $oldnum = pnConfigGetVar('perpage');

    if (!pnSecAuthAction(0, 'Pastblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }
    if (pnUserLoggedIn()) {
        $storyhome = pnUserGetVar('storynum');
    } else {
        $storyhome = pnConfigGetVar('storyhome');
    }

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    // Defaults
    if (empty($storynum)) $storynum = 10;
    if (empty($vars['limit'])) {
        $vars['limit'] = 10;
    }
    $storynum = $vars['limit'];

    $column = &$pntable['stories_column'];
    if ((!isset($catid)) || ($catid == '')) {
        $articles = getArticles("$column[ihome]=0", "$column[time] DESC", $storynum,$storyhome);
    } else {
        $articles = getArticles("$column[catid]=$catid", "$column[time] DESC", $storynum, $storyhome);
    }

    $time2 = "";

    setlocale (LC_TIME, pnConfigGetVar('locale'));
    $boxstuff = "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\" class=\"pn-normal\">\n";
    $vari = 0;
    $see = 0;
    foreach ($articles as $article) {

        $info = genArticleInfo($article);
        $links = genArticleLinks($info);
        $preformat = genArticlePreformat($info, $links);

        // a little bit tricky to remove the bold property from link description
        // (2001-11-15, hdonner)
        $preformat['title'] = str_replace("pn-title", "pn-normal", $preformat['title']);

        if (!pnSecAuthAction(0, 'Stories::Story',
                    "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_OVERVIEW)) {
            continue;
        }
        $see = 1;
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $info['time'], $datetime2);
        $datetime2 = ml_ftime(""._DATESTRING2."", mktime($datetime2[4],$datetime2[5],$datetime2[6],$datetime2[2],$datetime2[3],$datetime2[1]));
        $datetime2 = ucfirst($datetime2);
        if($time2==$datetime2) {
            $boxstuff .= "<tr><td valign=\"top\"><big><strong>&middot;</strong></big></td>"
                        ."<td valign=\"top\" width=\"100%\"><span class=\"pn-normal\">" . $preformat['title'] . "&nbsp;($info[comments])</span></td></tr>\n";
        } else {
            $boxstuff .= "<tr><td colspan=\"2\"><b>$datetime2</b></td></tr>\n"
                        ."<tr><td valign=\"top\"><big><strong>&middot;</strong></big></td>"
                        ."<td valign=\"top\" width=\"100%\"><span class=\"pn-normal\">$preformat[title]&nbsp;($info[comments])</span></td></tr>\n";
            $time2 = $datetime2;
        }
        $vari++;
        if ($vari == $vars['limit']) {
            $usernum = pnUserGetVar('storynum');
            if (!empty($usernum)) {
                $storynum = $usernum;
            } else {
                $storynum = pnConfigGetVar('storyhome');
            }
            $min = $oldnum + $storynum;
            $boxstuff .= "<tr><td>&nbsp;</td><td valign=\"top\"><a class=\"pn-normal\"";
            if (!isset($catid)) {
                $boxstuff .= "href=\"modules.php?op=modload&name=Search&file=index&action=search&overview=1&active_stories=1\"><b>"._OLDERARTICLES."</b></a></td></tr>\n";
            } else {
                $boxstuff .= "href=\"modules.php?op=modload&name=Search&file=index&action=search&overview=1&active_stories=1&stories_cat[0]=$catid\"><b>"._OLDERARTICLES."</b></a></td></tr>\n";;     
            }
        }
    }
    $boxstuff .= "</table>";
    if ($see == 1) {
        if (empty($row['title'])) {
            $row['title'] = _PASTARTICLES;
        }
        $row['content'] = $boxstuff;
        return themesideblock($row);
    }
}

function blocks_past_select($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    // Defaults
    if (empty($vars['limit'])) {
        $vars['limit'] = 10;
    }

    $row['content'] = "";

    // Number of stories
    $output = "<tr><td class=\"pn-normal\">Maximum number of stories to display:</td><td><input type=\"text\" name=\"limit\" size=\"2\" value=\"" . pnVarPrepForDisplay($vars['limit']) . "\"></td></tr>";

    return $output;
}

function blocks_past_update($row)
{
    $vars['limit'] = pnVarCleanFromInput('limit');

    $row['content'] = pnBlockVarsToContent($vars);

    return($row);
}

?>