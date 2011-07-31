<?php // $Id: index.php,v 1.2 2002/11/09 13:15:07 nunizgb Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Copyright (c) 2001 by Patrick Kellum (webmaster@ctarl-ctarl.com)
// http://www.ctarl-ctarl.com
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
// Filename: modules/Search/index.php
// Original Author of file: Patrick Kellum
// Purpose of file: Search reviews/users/stories/topics/faqs
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

/*
Credits to Edgar Miller -- http://www.bosna.de/ from his post on PHP-Nuke ( http://phpnuke.org/article.php?sid=2010&mode=nested&order=0&thold=0 )
Further Credits go to Djordjevic Nebojsa (nesh) for the fix for the fix */

$ModName = basename(dirname(__FILE__));

modules_get_language();

/*
 * load all available search modules
 */
$search_modules = '';
$d = opendir('includes/search/');
while($f = readdir($d)) {
    if(substr($f, -3, 3) == 'php') {
        include 'includes/search/' . $f;
    }
}
closedir($d);
/*
 * splits the query string into words suitable for a mysql query
 */
function search_split_query($q) {
    if (!isset($q)) {
        return;
    }
    $w = array();
    $stripped = pnVarPrepForStore($q);
    $qwords = explode(' ', $stripped);
    foreach($qwords as $word) {
        $w[] = '%' . $word . '%';
    }
    return $w;
}
function search_form($vars) {

$search_modules = &$GLOBALS['search_modules'];
$bgcolor1 = &$GLOBALS['bgcolor1'];
$bgcolor2 = &$GLOBALS['bgcolor2'];
$bgcolor3 = &$GLOBALS['bgcolor3'];
$textcolor1 = &$GLOBALS['textcolor1'];
$textcolor2 = &$GLOBALS['textcolor2'];
$ModName = $GLOBALS['ModName'];
  /*  global
        $search_modules,
        $bgcolor1,
        $bgcolor2,
        $bgcolor3,
        $textcolor1,
        $textcolor2,
        $ModName
    ;   */
    if(!isset($vars['bool']) || $vars['bool'] == '') {
        $vars['bool'] = 'AND';
    }

        $bool_select = array('AND' => '', 'OR' => '');
    $bool_select[$vars['bool']] = ' selected';
    echo "<p align=\"center\">"
        ."<form method=\"post\" action=\"modules.php\">"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">"
        ."<input type=\"hidden\" name=\"name\" value=\"$ModName\">"
        ."<input type=\"hidden\" name=\"file\" value=\"index\">"
        ."<input type=\"hidden\" name=\"action\" value=\"search\">"
        ."<input type=\"hidden\" name=\"overview\" value=\"1\">"
    ;
    echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" summary=\""._SUMMARY."\">"
        ."<tr>"
        ."<td nowrap><font class=\"pn-normal\">"._SEARCH."&nbsp;"._FOR.":</font></td>"
        ."<td colspan=\"2\"><font class=\"pn-normal\">"
        ."<input type=\"text\" name=\"q\" id=\"q\" size=\"20\" maxlength=\"255\" value=\"" . (isset($vars['q']) ? htmlspecialchars($vars['q']) : '') . "\"> "
        ."<input type=\"submit\" value=\""._SEARCH."\">"
        ."</font></td>"
        ."</tr>"
        ."<tr>"
        ."<td>&nbsp;</td>"
        ."<td colspan=\"2\"><font class=\"pn-normal\">"
        .'<select name="bool" size="1">'
        .'<option value="AND"'.$bool_select['AND'].'>'._ALLWORDS.'</option>'
        .'<option value="OR"'.$bool_select['OR'].'>'._ANYWORDS.'</option>'
        .'</select>'
        ."</font></td>"
        ."</tr>"
        ."</table>"
    ;
    foreach($search_modules as $mods) {
        echo $mods['func_opt']($vars);
    }
    echo "</form></p>";
}
$vars = array_merge($HTTP_POST_VARS,$HTTP_GET_VARS);

if(!isset($vars['action']))
{
        $vars['action'] = 'form';
}

switch($vars['action']) {
    default:
    case 'form':
        include 'header.php';
        OpenTable();
        search_form($vars);
        CloseTable();
        include 'footer.php';
        break;
    case 'search':
        include 'header.php';
        OpenTable();
        echo "<font class=\"pn-normal\">";
        foreach($search_modules as $mods) {
            echo "<BR>";
            echo $mods['func_search']($vars);
        }
        echo "</font>";
        CloseTable();
        include 'footer.php';
        break;
}
?>