<?php
// File: $Id: search.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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

$blocks_modules['search'] = array(
    'func_display' => 'blocks_search_block',
    'text_type' => 'Search',
    'text_type_long' => 'Search Box',
    'allow_multiple' => false,
    'form_content' => false,
    'form_refresh' => false,
    'show_preview' => true
);

// Security
pnSecAddSchema('Searchblock::', 'Block title::');

function blocks_search_block($row) {

    if (!pnSecAuthAction(0, 'Searchblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    $content = "<form method=\"post\" action=\"modules.php?op=modload&amp;name=Search&amp;file=index\">";
    $content .= "<br><center><input type=\"text\" name=\"q\" size=\"14\"></center>";
    $content .= "<input type=\"hidden\" name=\"action\" value=\"search\">";
    $content .= "<input type=\"hidden\" name=\"overview\" value=\"1\">";
    $content .= "<input type=\"hidden\" name=\"stories_topics[]\" value=\"0\">";
    $content .= "<input type=\"hidden\" name=\"stories_cat[]\" value=\"0\">";
    $content .= "<input type=\"hidden\" name=\"active_stories\" value=\"1\">";
    $content .= "</form>";
    if (empty($row['title'])) {
        $row['title'] = _SEARCH;
    }
    $row['content'] = $content;
    return themesideblock($row);
}
?>