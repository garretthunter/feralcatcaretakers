<?php // $Id: index.php,v 1.5 2002/11/15 23:31:01 larsneo Exp $ $Name:  $
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
// Filename: modules/Top_List/index.php
// Original Author of file: Francisco Burzi
// Purpose of file: Display top x listings on your site.
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = basename(dirname(__FILE__));

modules_get_language();

list($dbconn) = pnDBGetConn();
$pntable = pnDBGetTables();

$currentlang = pnUserGetLang();

if (pnConfigGetVar('multilingual') == 1) {
    $column = &$pntable['stories_column'];
    $queryalang = "($column[alanguage]='$currentlang' OR $column[alanguage]='')"; /* top stories */
    $column = &$pntable['seccont_column'];
    $queryslang = "($column[slanguage]='$currentlang' OR $column[slanguage]='')"; /* top section articles */
    $column = &$pntable['poll_desc_column'];
    $queryplang = "($column[planguage]='$currentlang' OR $column[planguage]='')"; /* top polls */
    $column = &$pntable['reviews_column'];
    $queryrlang = "($column[rlanguage]='$currentlang' OR $column[rlanguage]='')"; /* top reviews */
} else {
    $queryalang = "";
    $queryrlang = "";
    $queryplang = "";
    $queryslang = "";
}

include 'header.php';

if (!pnSecAuthAction(0, 'Top_List::', '::', ACCESS_OVERVIEW)) {
echo _BADAUTHKEY;
include("footer.php");
return;
}

$top = pnVarPrepForDisplay(pnConfigGetVar('top'));
$sitename = pnVarPrepForDisplay(pnConfigGetVar('sitename'));

OpenTable();
echo "<center><font class=\"pn-title\">"._TOPWELCOME." $sitename!</font></center>";
CloseTable();
echo "\n";
OpenTable();

/**
 * Top 10 read stories
 */
$column = &$pntable['stories_column'];
$myquery = buildSimpleQuery ('stories', array ('sid', 'title', 'time', 'counter'), $queryalang, "$column[counter] DESC", $top);
$result = $dbconn->Execute($myquery);

if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
        ."<font class=\"pn-title\">$top "._READSTORIES.".</font><br><br>\n";
    $lugar=1;

    while(list($sid, $title, $time, $counter) = $result->fields) {

        if($counter>0) {
            $commentlink = pnUserGetCommentOptions();

            if (empty($title)) {
                $title = '- '._NOTITLE.' -';
            }
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid&amp;mode=$commentlink\">" . pnVarPrepForDisplay($title) . "</a><font class=\"pn-normal\"> - ($counter "._READS.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 commented stories
 */
$column = &$pntable['stories_column'];
$myquery = buildSimpleQuery ('stories', array ('sid', 'title', 'comments'), $queryalang, "$column[comments] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
    ."<font class=\"pn-title\">$top "._COMMENTEDSTORIES."</font><br><br>\n";
    $lugar=1;

    while(list($sid, $title, $comments) = $result->fields) {
        if($comments>0) {
            $commentlink = pnUserGetCommentOptions();
            if ($title == "")
                {
                   $title = '- '._NOTITLE.' -';
                }
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$sid&amp;$commentlink\">" . pnVarPrepForDisplay($title) . "</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($comments)." "._COMMENTS.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 categories
 */

$column = &$pntable['stories_cat_column'];
$myquery = buildSimpleQuery ('stories_cat', array ('catid', 'title', 'counter'), '', "$column[counter] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._ACTIVECAT."</font><br><br>\n";
    $lugar=1;

    while(list($catid, $title, $counter) = $result->fields) {
        if($counter>0) {
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"index.php?&amp;catid=$catid\">" . pnVarPrepForDisplay($title) . "</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($counter)." "._HITS.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 articles in special sections
 */

$column = &$pntable['seccont_column'];
$myquery = buildSimpleQuery ('seccont', array ('artid', 'secid', 'title', 'content', 'counter'), $queryslang, "$column[counter] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._READSECTION."</font><br><br>\n";
    $lugar=1;

    while(list($artid, $secid, $title, $content, $counter) = $result->fields) {
        echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=Sections&amp;file=index&amp;req=viewarticle&amp;artid=$artid\">" . pnVarPrepForDisplay($title) . "</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($counter)." "._READS.")</font><br>\n";
       $lugar++;
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 users submitters
 */

$column = &$pntable['users_column'];
$myquery = buildSimpleQuery ('users', array ('uname', 'counter'), "$column[counter]>0", "$column[counter] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._NEWSSUBMITTERS."</font><br><br>\n";
    $lugar=1;

    while(list($uname, $counter) = $result->fields) {
        if($counter>0) {
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"user.php?op=userinfo&amp;uname=$uname\">$uname</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($counter)." "._NEWSSENT.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 Polls
 */

$column = &$pntable['poll_desc_column'];
$myquery = buildSimpleQuery ('poll_desc', array ('pollid', 'polltitle', 'voters'), $queryplang, "$column[voters] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
        ."<font class=\"pn-title\">$top "._VOTEDPOLLS."</font><br><br>\n";
    $lugar=1;

    while(list($pollID, $pollTitle, $voters) = $result->fields) {
        if(empty($pollTitle)) {
            $pollTitle = '';
        }
        $column = &$pntable['poll_data_column'];
        $result2 = $dbconn->Execute("SELECT SUM($column[optioncount]) AS sum FROM $pntable[poll_data] WHERE $column[pollid]=".pnVarPrepForStore($pollID)."");

        list($sum) = $result2->fields;
        if((int)$sum>0) {
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;pollID=$pollID\">" . pnVarPrepForDisplay($pollTitle) . "</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($voters)." "._TOPLISTVOTES.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 authors
 */

$column = &$pntable['stories_column'];
$column1 = &$pntable['users_column'];
$myquery ="SELECT $column1[uname], count(*) AS num
                                FROM $pntable[stories], $pntable[users]
                                WHERE  $column[aid] = ".pnVarPrepForStore($column1['uid'])."
                                GROUP BY $column1[uname]
                                ORDER BY num DESC";

$result = $dbconn->SelectLimit($myquery,$top);

if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._MOSTACTIVEAUTHORS."</font><br><br>\n";
    $lugar=1;

    while(list($aid, $counter) = $result->fields) {
        if($counter>0) {
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font>"
             ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;overview=1&amp;active_stories=1&amp;stories_author=$aid\">$aid</a>"
             ."<font class=\"pn-normal\"> - (".pnVarPrepForDisplay($counter)." "._NEWSPUBLISHED.")</font><br>\n";
            $lugar++;
        }
    $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 reviews
 */

$column = &$pntable['reviews_column'];
$myquery = buildSimpleQuery ('reviews', array ('id', 'title', 'hits'), $queryrlang, "$column[hits] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._READREVIEWS."</font><br><br>\n";
    $lugar=1;

    while(list($id, $title, $hits) = $result->fields) {
        if($hits>0) {
           if(empty($title))
               {
                  $title = '- '._NOTITLE.' -';
               }
           echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=Reviews&amp;file=index&amp;req=showcontent&amp;id=$id\">" . pnVarPrepForDisplay($title) . "</a><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($hits)." "._READS.")</font><br>\n";
           $lugar++;
       }
        $result->MoveNext();
    }
    echo "</td></tr></table><br>\n";
}
$result->Close();

/**
 * Top 10 downloads
 */

$column = &$pntable['downloads_downloads_column'];
$myquery = buildSimpleQuery ('downloads_downloads', array ('lid', 'cid', 'title', 'hits'), '', "$column[hits] DESC", $top);
$result = $dbconn->Execute($myquery);
if (!$result->EOF) {
    echo "<table border=\"0\" cellpadding=\"10\" width=\"100%\"><tr><td align=\"left\">\n"
            ."<font class=\"pn-title\">$top "._DOWNLOADEDFILES."</font><br><br>\n";
    $lugar=1;
    while(list($lid, $cid, $title, $hits) = $result->fields) {
        if($hits>0) {
            $column = &$pntable['downloads_categories_column'];
            $res = $dbconn->Execute("SELECT $column[title]
                                   FROM $pntable[downloads_categories]
                                   WHERE $column[cid]='".pnVarPrepForStore($cid)."'");

            list($ctitle) = $res->fields;
            $res->Close();
            $utitle = ereg_replace(" ", "_", $title);

            if(empty($title)) {
                $title = '- '._NOTITLE.' -';
            }
            echo "<font class=\"pn-normal\">&nbsp;$lugar:</font> <a href=\"modules.php?op=modload&amp;name=Downloads&amp;file=index&amp;req=viewdownloaddetails&amp;lid=$lid&amp;ttitle=$utitle\">" . pnVarPrepForDisplay($title) . "</a> <font class=\"pn-normal\"> ("._CATEGORY.": ".pnVarPrepForDisplay($ctitle).")</font><font class=\"pn-normal\"> - (".pnVarPrepForDisplay($hits)." "._DOWNLOADS.")</font><br>\n";
            $lugar++;
        }
        $result->MoveNext();
    }
    echo "</td></tr></table>\n\n";
}
$result->Close();

CloseTable();

include 'footer.php';
?>