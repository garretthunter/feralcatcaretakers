<?php
// $Id: pnuserapi.php,v 1.3 2002/10/07 14:30:19 skooter Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Autolinks user API
// ----------------------------------------------------------------------

/**
 * get all links
 * @returns array
 * @return array of links, or false on failure
 */
function autolinks_userapi_getall($args)
{
    extract($args);

    // Optional arguments
    if (!isset($startnum) || !is_numeric($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems) || !is_numeric($numitems)) {
        $numitems = -1;
    }

    $links = array();
    if (!pnSecAuthAction(0, 'Autolinks::', '::', ACCESS_READ)) {
        return $links;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $autolinkstable = $pntable['autolinks'];
    $autolinkscolumn = $pntable['autolinks_column'];

    // Get links
    $sql = "SELECT $autolinkscolumn[lid],
                   $autolinkscolumn[keyword],
                   $autolinkscolumn[title],
                   $autolinkscolumn[url],
                   $autolinkscolumn[comment]
            FROM $autolinkstable
            ORDER BY $autolinkscolumn[keyword]";
    $result = $dbconn->SelectLimit($sql, (int)$numitems, (int)$startnum-1);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _SELECTFAILED);
        return false;
    }

    for (; !$result->EOF; $result->MoveNext()) {
        list($lid, $keyword, $title, $url, $comment) = $result->fields;
        if (pnSecAuthAction(0, 'Autolinks::', "$keyword::$lid", ACCESS_READ)) {
            $links[] = array('lid' => $lid,
                             'keyword' => $keyword,
                             'title' => $title,
                             'url' => $url,
                             'comment' => $comment);
        }
    }

    $result->Close();

    return $links;
}

/**
 * get a specific link
 * @poaram $args['lid'] id of link to get
 * @returns array
 * @return link array, or false on failure
 */
function autolinks_userapi_get($args)
{
    extract($args);

    if (!isset($lid) || !is_numeric($lid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $autolinkstable = $pntable['autolinks'];
    $autolinkscolumn = $pntable['autolinks_column'];

    // Get link
    $sql = "SELECT $autolinkscolumn[lid],
                   $autolinkscolumn[keyword],
                   $autolinkscolumn[title],
                   $autolinkscolumn[url],
                   $autolinkscolumn[comment]
            FROM $autolinkstable
            WHERE $autolinkscolumn[lid] = " . (int)pnVarPrepForStore($lid);
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _SELECTFAILED);
        return false;
    }

    list($lid, $keyword, $title, $url, $comment) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Autolinks::', "$keyword::$lid", ACCESS_READ)) {
        return false;
    }
    $link = array('lid' => $lid,
                  'keyword' => $keyword,
                  'title' => $title,
                  'url' => $url,
                  'comment' => $comment);

    return $link;
}

/**
 * count the number of links in the database
 * @returns integer
 * @returns number of links in the database
 */
function autolinks_userapi_countitems()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $autolinkstable = $pntable['autolinks'];

    $sql = "SELECT COUNT(1)
            FROM $autolinkstable";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        return false;
    }

    list($numitems) = $result->fields;

    $result->Close();

    return $numitems;
}

/**
 * transform text
 * @param $args['extrainfo'] string or array of text items
 * @returns string
 * @return string or array of transformed text items
 */
function autolinks_userapi_transform($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($extrainfo)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return;
    }

    if (is_array($extrainfo)) {
        $transformed = array();
        foreach($extrainfo as $text) {
            $transformed[] = autolinks_userapitransform($text);
        }
    } else {
        $transformed = autolinks_userapitransform($text);
    }

    return $transformed;
}

function autolinks_userapitransform($text)
{
    static $alsearch = array();
    static $alreplace = array();
    static $gotautolinks = 0;

    if (empty($gotautolinks)) {
        $gotautolinks = 1;
        pnModAPILoad('Autolinks', 'user');
        $tmpautolinks = pnModAPIFunc('Autolinks', 'user', 'getall');

        // Check if we want invisible links
        if (pnModGetVar('Autolinks', 'invisilinks')) {
            $style = 'style="text-decoration: none" ';
        } else {
            $style = '';
        }
        // Create search/replace array from autolinks information
        foreach ($tmpautolinks as $tmpautolink) {
            // Munge word boundaries to stop autolinks from linking to
            // themselves or other autolinks in step 2
            $tmpautolink['url'] = preg_replace('/(\b)/', '\\1ALSPACEHOLDER', $tmpautolink['url']);
            $tmpautolink['title'] = preg_replace('/(\b)/', '\\1ALSPACEHOLDER', $tmpautolink['title']);
            // Note use of assertions here to only match specific words,
            // for instance ones that are not part of a hyphenated phrase
            // or (most) bits of an email address
            $alsearch[] = '/(?<![\w@\.:-])(' . preg_quote($tmpautolink['keyword'], '/'). ')(?![\w@:-])(?!\.\w)/i';
            $alreplace[] = '<a ' . $style . 'href="' . htmlspecialchars($tmpautolink['url']) .
                           '" title="' . htmlspecialchars($tmpautolink['title']) .
                           '" target="_blank">\\1</a>';
        }
    }

    // Step 1 - move all tags out of the text and replace them with placeholders
    preg_match_all('/(<a\s+.*?\/a>|<[^>]+>)/i', $text, $matches);
    $matchnum = count($matches[1]);
    for ($i = 0; $i <$matchnum; $i++) {
        $text = preg_replace('/' . preg_quote($matches[1][$i], '/') . '/', "ALPLACEHOLDER{$i}PH", $text, 1);
    }

    // Step 2 - s/r of the remaining text
    if (pnModGetVar('Autolinks', 'linkfirst')) {
        $text = preg_replace($alsearch, $alreplace, $text, 1);
    } else {
        $text = preg_replace($alsearch, $alreplace, $text);
    }

    // Step 3 - replace the spaces we munged in step 2
    $text = preg_replace('/ALSPACEHOLDER/', '', $text);

    // Step 4 - replace the HTML tags that we removed in step 1
    for ($i = 0; $i <$matchnum; $i++) {
        $text = preg_replace("/ALPLACEHOLDER{$i}PH/", $matches[1][$i], $text, 1);
    }


    return $text;
}

?>