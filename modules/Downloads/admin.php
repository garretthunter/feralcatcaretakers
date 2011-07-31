<?php
// File: $Id: admin.php,v 1.6 2002/10/27 16:27:33 larsneo Exp $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

$ModName = $module;
modules_get_language();
modules_get_manual();

include_once ("modules/$ModName/dl-categories.php");
include_once ("modules/$ModName/dl-util.php");

if (pnSecAuthAction(0, "Downloads::", "::", ACCESS_ADMIN)) {

/**
 * Downloads Modified Web Downloads
 */

function downloads() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();

    if (!pnSecAuthAction(0, 'Downloads::', '::', ACCESS_EDIT)) {
        echo _DOWNLOADSACCESSNOAUTH;
        include 'footer.php';
        return;
    }
    echo "<center><font class=\"pn-logo\">"._DLOADPAGETITLE."</font><br /><br />";
    $result=$dbconn->Execute("SELECT count(*) FROM $pntable[downloads_downloads]");
    list($numrows) = $result->fields;
    echo "<font class=\"pn-normal\">"._THEREARE." <b>".pnVarPrepForDisplay($numrows)."</b> "._DOWNLOADSINDB."</font></center>";
    CloseTable();

/* Temporarily 'homeless' downloads functions (to be revised in admin.php breakup ) */
    $result = $dbconn->Execute("SELECT count(*)
                                FROM $pntable[downloads_modrequest]
                                WHERE {$pntable['downloads_modrequest_column']['brokendownload']}=1");

    list($totalbrokendownloads) = $result->fields;
    $result = $dbconn->Execute("SELECT count(*)
                                FROM $pntable[downloads_modrequest]
                                WHERE {$pntable['downloads_modrequest_column']['brokendownload']}=0");

    list($totalmodrequests) = $result->fields;

/* List Downloads waiting for validation */

    $column = &$pntable['downloads_newdownload_column'];
    $result = $dbconn->Execute("SELECT $column[lid],
                                       $column[cid],
                                       $column[sid],
                                       $column[title],
                                       $column[url],
                                       $column[description],
                                       $column[name],
                                       $column[email],
                                       $column[submitter],
                                       $column[filesize],
                                       $column[version],
                                       $column[homepage]
                                FROM $pntable[downloads_newdownload]
                                ORDER BY $column[lid]");
    if (!$result->EOF) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._DOWNLOADSWAITINGVAL."</b></font></center><br /><br />";
        while(list($lid, $cid, $sid, $title, $url, $description, $name, $email, $submitter, $filesize, $version, $homepage) = $result->fields)	 {

            $result->MoveNext();
            if ($submitter == "") {
                $submitter = _NONE;
            }
            $homepage = ereg_replace("http://","",$homepage);
            echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";  // changed layout into a table
            echo " <tr><td colspan=\"4\"><form action=\"admin.php\" method=\"post\">"
                ."<b>"._DOWNLOADID.": $lid</b><br /><br /></td></tr>"
                ."\n<tr><td colspan=\"4\">"._SUBMITTER.": <b>".pnVarPrepForDisplay($submitter)."</b></td></tr>"
                ."\n<tr><td colspan=\"4\">"._DOWNLOADNAME.": <input type=\"text\" name=\"title\" value=\"$title\" size=\"50\" maxlength=\"100\"></td></tr>"
                ."\n<tr><td colspan=\"4\">"._FILEURL.": <input type=\"text\" name=\"url\" value=\"$url\" size=\"50\" maxlength=\"254\">&nbsp;[ <a target=\"_blank\" href=\"$url\">"._CHECK."</a> ]</td></tr>"
                ."\n<tr><td colspan=\"4\">"._DESCRIPTION.": <br /><textarea name=\"description\" cols=\"60\" rows=\"10\">$description</textarea></td></tr>"
                ."\n<tr><td colspan=\"4\">"._AUTHORNAME.": <input type=\"text\" name=\"name\" size=\"20\" maxlength=\"100\" value=\"$name\">&nbsp;&nbsp;"
                .""._AUTHOREMAIL.": <input type=\"text\" name=\"email\" size=\"20\" maxlength=\"100\" value=\"$email\"></td></tr>"
                ."\n<tr><td colspan=\"4\">"._FILESIZE.": <input type=\"text\" name=\"filesize\" size=\"12\" maxlength=\"11\" value=\"$filesize\"></td></tr>"
                ."\n<tr><td colspan=\"4\">"._VERSION.": <input type=\"text\" name=\"version\" size=\"11\" maxlength=\"10\" value=\"$version\"></td></tr>"
                ."\n<tr><td colspan=\"4\">"._HOMEPAGE.": <input type=\"text\" name=\"homepage\" size=\"30\" maxlength=\"200\" value=\"http://$homepage\"> [ <a =\"http://$homepage\">"._VISIT."</a> ]</td></tr>";
            $column = &$pntable['downloads_categories_column'];
            $result2=$dbconn->Execute("SELECT $column[cid],
                                              $column[title]
                                       FROM $pntable[downloads_categories]
                                       ORDER BY $column[title]");
            echo "<tr><td valign=\"top\"><input type=\"hidden\" name=\"new\" value=\"1\">"
                ."\n<input type=\"hidden\" name=\"hits\" value=\"0\">"
                ."\n<input type=\"hidden\" name=\"lid\" value=\"$lid\">"
                ."\n<input type=\"hidden\" name=\"submitter\" value=\"$submitter\">"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ._CATEGORY.": <select name=\"cat\">";

            while(list($ccid, $ctitle) = $result2->fields) {
                $sel = "";
                if ($cid==$ccid AND $sid==0) {
                    $sel = "selected";
                }
                echo "<option value=\"$ccid\" $sel>".pnVarPrepForDisplay($ctitle)."</option>";
                $column = &$pntable['downloads_subcategories_column'];
                $result3=$dbconn->Execute("SELECT $column[sid],
                                                  $column[title]
                                           FROM $pntable[downloads_subcategories]
                                           WHERE $column[cid]=".pnVarPrepForDisplay($ccid)."
                                           ORDER BY $column[title]");

                while(list($ssid, $stitle) = $result3->fields) {
                    $sel = "";
                    if ($sid == $ssid) {
                        $sel = "selected";
                    }
                    echo "<option value=\"$ccid-$ssid\" $sel>".pnVarPrepForDisplay($ctitle)." / ".pnVarPrepForDisplay($stitle)."</option>";
                    $result3->MoveNext();
                }
                $result2->MoveNext();
            }
            echo "</select></td><td><input type=\"hidden\" name=\"submitter\" value=\"$submitter\">";
            echo "<input type=\"hidden\" name=\"op\" value=\"DownloadsAddDownload\">"
                 ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."\n<input type=\"submit\" value="._ADD.">&nbsp;</form></td><td>"
            ."\n<form action=\"admin.php\" method=\"post\">"
            ."\n<input type=\"hidden\" name=\"op\" value=\"DownloadsDelNew\">"
            ."\n<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."\n<input type=\"hidden\" name=\"lid\" value=\"$lid\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."\n<input type=\"submit\" value=\""._DELETE."\"></form></td>"
            ."\n<td width=\"10%\">&nbsp;</td></tr></table><hr noshade><br />\n\n";

        }
        CloseTable();
    }

    OpenTable();
    echo "<center><font class=\"pn-normal\">[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsCleanVotes&amp;authid=" . pnSecGenAuthKey() . "\">"._CLEANDOWNLOADSDB."</a> | "
    ."<a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsListBrokenDownloads\">"._BROKENDOWNLOADSREP." (".pnVarPrepForDisplay($totalbrokendownloads).")</a> | "
    ."<a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsListModRequests\">"._DOWNLOADMODREQUEST." (".pnVarPrepForDisplay($totalmodrequests).")</a> | "
    ."<a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsDownloadCheck\">"._VALIDATEDOWNLOADS."</a> ]</font></center>";
    CloseTable();

/* Add a New Main Category */
    DownloadsNewCat();

// Add a New Sub-Category
    DownloadsNewSubCat();

// Add a New Download to Database
/*
 * Hootbah: XXX FIXME XXX
 * This seems a little odd. Why do the same query again.
 */
    $column = &$pntable['downloads_categories_column'];
    $result = $dbconn->Execute("SELECT $column[cid],
                                       $column[title]
                                FROM $pntable[downloads_categories]
                                ORDER BY $column[title]");
    if (!$result->EOF) {
    OpenTable();
    echo "<form method=\"post\" action=\"admin.php\">"
        ."<font class=\"pn-title\"><b>"._ADDNEWDOWNLOAD."</b></font><br /><br />"
        .""._DOWNLOADNAME.": <input type=\"text\" name=\"title\" size=\"50\" maxlength=\"100\"><br />"
        .""._FILEURL.": <input type=\"text\" name=\"url\" size=\"50\" maxlength=\"100\" value=\"http://\"><br />";
    echo ""._CATEGORY.": <select name=\"cat\">";

    while(list($cid, $title) = $result->fields) {
        echo "<option value=\"$cid\">".pnVarPrepForDisplay($title)."</option>";
        $column = &$pntable['downloads_subcategories_column'];
        $result2=$dbconn->Execute("SELECT $column[sid],
                                          $column[title]
                                   FROM $pntable[downloads_subcategories]
                                   WHERE $column[cid]=".pnVarPrepForStore($cid)."
                                   ORDER BY $column[title]");

        while(list($sid, $stitle) = $result2->fields) {
            echo "<option value=\"$cid-$sid\">".pnVarPrepForDisplay($title)." / ".pnVarPrepForDisplay($stitle)."</option>";
            $result2->MoveNext();
        }
        $result->MoveNext();
    }
    echo "</select><br /><br /><br />"
        .""._DESCRIPTION255."<br /><textarea name=\"description\" cols=\"60\" rows=\"5\"></textarea><br /><br /><br />"
        .""._AUTHORNAME.": <input type=\"text\" name=\"name\" size=\"30\" maxlength=\"60\"><br /><br />"
        .""._AUTHOREMAIL.": <input type=\"text\" name=\"email\" size=\"30\" maxlength=\"60\"><br /><br />"
        .""._FILESIZE.": <input type=\"text\" name=\"filesize\" size=\"12\" maxlength=\"11\"> ("._INBYTES.")<br /><br />"
        .""._VERSION.": <input type=\"text\" name=\"version\" size=\"11\" maxlength=\"10\"><br /><br />"
        .""._HOMEPAGE.": <input type=\"text\" name=\"homepage\" size=\"30\" maxlength=\"200\" value=\"http://\"><br /><br />"
        .""._HITS.": <input type=\"text\" name=\"hits\" size=\"12\" maxlength=\"11\" value=\"0\"><br /><br />"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"DownloadsAddDownload\">"
            ."<input type=\"hidden\" name=\"new\" value=\"0\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"hidden\" name=\"lid\" value=\"0\">"
        ."<center><input type=\"submit\" value=\""._ADDURL."\"><br />"
        ."</form>";
    CloseTable();
    }

// Modify Category

    $column = &$pntable['downloads_categories_column'];
    $result=$dbconn->Execute("SELECT $column[cid],
                                     $column[title]
                              FROM $pntable[downloads_categories]
                              ORDER BY $column[title]");
    if (!$result->EOF) {
        OpenTable();
        echo "<form method=\"post\" action=\"admin.php\">"
            ."<font class=\"pn-title\"><b>"._MODCATEGORY."</b></font><br /><br />";
        echo ""._CATEGORY.": <select name=\"cat\">";

        while(list($cid, $title) = $result->fields) {
            echo "<option value=\"$cid\">".pnVarPrepForDisplay($title)."</option>";
            $column = &$pntable['downloads_subcategories_column'];
            $result2=$dbconn->Execute("SELECT $column[sid],
                                              $column[title]
                                       FROM $pntable[downloads_subcategories]
                                       WHERE $column[cid]=" . pnVarPrepForStore($cid). "
                                       ORDER BY $column[title]");

            while(list($sid, $stitle) = $result2->fields) {
                echo "<option value=\"$cid-$sid\">".pnVarPrepForDisplay($title)." / ".pnVarPrepForDisplay($stitle)."</option>";
                $result2->MoveNext();
            }
            $result->MoveNext();
        }
        echo "</select>"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"DownloadsModCat\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._MODIFY."\">"
            ."</form>";
        CloseTable();
    }

// Modify Downloads

    $result = $dbconn->Execute("SELECT COUNT(1)
                                FROM $pntable[downloads_downloads]");
    list($numrows) = $result->fields;
    if ($numrows>0) {
        OpenTable();
        echo "<form method=\"post\" action=\"admin.php\">"
            ."<font class=\"pn-title\"><b>"._MODDOWNLOAD."</b></font><br /><br />"
            .""._DOWNLOADID.": <input type=\"text\" name=\"lid\" size=\"12\" maxlength=\"11\">&nbsp;&nbsp;"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"DownloadsModDownload\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._MODIFY."\">"
            ."</form>";
        CloseTable();
    }

// Access Download Settings
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._DOWNLOADSCONF."</b></font></center><br /><br />";
    echo "<center><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getConfig\">"._DOWNLOADSCONF."</a></center>";
    CloseTable();
    include ("footer.php");
}

function DownloadsModDownload() {

    $lid = pnVarCleanFromInput('lid');

    /*if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    */
    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();

    $anonymous = pnConfigGetVar('anonymous');

    $column = &$pntable['downloads_downloads_column'];
    $result = $dbconn->Execute("SELECT $column[cid],
                                       $column[sid],
                                       $column[title],
                                       $column[url],
                                       $column[description],
                                       $column[name],
                                       $column[email],
                                       $column[hits],
                                       $column[filesize],
                                       $column[version],
                                       $column[homepage]
                                FROM $pntable[downloads_downloads]
                                WHERE $column[lid]=" . (int)pnVarPrepForStore($lid));
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._MODDOWNLOAD."</b></font></center><br /><br />";
    while(list($cid, $sid, $title, $url, $description, $name, $email, $hits, $filesize, $version, $homepage) = $result->fields) {

        $result->MoveNext();

        $homepage = ereg_replace("http://","",$homepage);
        echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";  // changed layout into a table
        echo " <tr><td colspan=\"4\"><form action=\"admin.php\" method=\"post\">"
        ."<b>"._DOWNLOADID.": $lid</b><br /><br /></td></tr>"
        ."\n<tr><td colspan=\"4\">"._DOWNLOADNAME.": <input type=\"text\" name=\"title\" value=\"$title\" size=\"50\" maxlength=\"100\"></td></tr>"
        ."\n<tr><td colspan=\"4\">"._FILEURL.": <input type=\"text\" name=\"url\" value=\"$url\" size=\"50\" maxlength=\"254\">&nbsp;[ <a target=\"_blank\" href=\"$url\">"._CHECK."</a> ]</td></tr>"
        ."\n<tr><td colspan=\"4\">"._DESCRIPTION.": <br /><textarea name=\"description\" cols=\"60\" rows=\"10\">$description</textarea></td></tr>"
        ."\n<tr><td colspan=\"4\">"._AUTHORNAME.": <input type=\"text\" name=\"name\" size=\"20\" maxlength=\"100\" value=\"$name\">&nbsp;&nbsp;"
        .""._AUTHOREMAIL.": <input type=\"text\" name=\"email\" size=\"20\" maxlength=\"100\" value=\"$email\"></td></tr>"
        ."\n<tr><td colspan=\"4\">"._FILESIZE.": <input type=\"text\" name=\"filesize\" size=\"12\" maxlength=\"11\" value=\"$filesize\"></td></tr>"
        ."\n<tr><td colspan=\"4\">"._VERSION.": <input type=\"text\" name=\"version\" size=\"11\" maxlength=\"10\" value=\"$version\"></td></tr>"
        ."\n<tr><td colspan=\"4\">"._HOMEPAGE.": <input type=\"text\" name=\"homepage\" size=\"30\" maxlength=\"200\" value=\"http://$homepage\"> [ <a href=\"http://$homepage\">"._VISIT."</a> ]</td></tr>"
        ."\n<tr><td colspan=\"4\">"._HITS.": <input type=\"text\" name=\"hits\" value=\"$hits\" size=\"12\" maxlength=\"11\"></td></tr>";
        $column = &$pntable['downloads_categories_column'];
        $result2=$dbconn->Execute("SELECT $column[cid],
                                          $column[title]
                                   FROM $pntable[downloads_categories]
                                   ORDER BY $column[title]");
        echo "<tr><td valign=\"top\"><input type=\"hidden\" name=\"lid\" value=\"$lid\">"
        .""._CATEGORY.": <select name=\"cat\">";

        while(list($ccid, $ctitle) = $result2->fields) {
            $sel = "";
            if ($cid==$ccid AND $sid==0) {
                $sel = "selected";
            }
            echo "<option value=\"$ccid\" $sel>".pnVarPrepForDisplay($ctitle)."</option>";
            $column = &$pntable['downloads_subcategories_column'];
            $result3=$dbconn->Execute("SELECT $column[sid],
                                              $column[title]
                                       FROM $pntable[downloads_subcategories]
                                       WHERE $column[cid]=" . pnVarPrepForStore($ccid) . "
                                       ORDER BY $column[title]");

            while(list($ssid, $stitle) = $result3->fields) {
                $sel = "";
                if ($sid==$ssid) {
                    $sel = "selected";
                }
                echo "<option value=\"$ccid-$ssid\" $sel>".pnVarPrepForDisplay($ctitle)." / ".pnVarPrepForDisplay($stitle)."</option>";
                $result3->MoveNext();
            }
            $result2->MoveNext();
        }
        echo "</select></td><td> "
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"DownloadsModDownloadS\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."\n<input type=\"submit\" value="._MODIFY.">&nbsp;</form></td><td>"
        ."\n<form action=\"admin.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."\n<input type=\"hidden\" name=\"op\" value=\"DownloadsDelDownload\">"
        ."\n<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."\n<input type=\"hidden\" name=\"lid\" value=\"$lid\">"
        ."\n<input type=\"submit\" value=\""._DELETE."\"></form></td>"
        ."\n<td width=\"10%\">&nbsp;</td></tr></table>\n\n";
    CloseTable();

    // Modify or Add Editorial

        $column = &$pntable['downloads_editorials_column'];
        $resulted2 = $dbconn->Execute("SELECT $column[adminid],
                                              $column[editorialtimestamp],
                                              $column[editorialtext],
                                              $column[editorialtitle]
                                       FROM $pntable[downloads_editorials]
                                       WHERE $column[downloadid]=" . (int)pnVarPrepForStore($lid));
        OpenTable();
    // if returns 'bad query' status 0 (add editorial)
        if ($resulted2->EOF) {
        	$editorialtitle = ''; // init for E_ALL
        	$editorialtext = ''; // init for E_ALL
            echo "<center><font class=\"pn-title\"><b>"._ADDEDITORIAL."</b></font></center><br /><br />"
            ."<form action=\"admin.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"downloadid\" value=\"$lid\">"
            .""._EDITORIALTITLE.":<br /><input type=\"text\" name=\"editorialtitle\" value=\"$editorialtitle\" size=\"50\" maxlength=\"100\"><br />"
            .""._EDITORIALTEXT.":<br /><textarea name=\"editorialtext\" cols=\"60\" rows=\"10\">$editorialtext</textarea><br />"
            ."</select>"."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"DownloadsAddEditorial\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\"Add\">";
        } else {
    // if returns 'cool' then status 1 (modify editorial)

              while(list($adminid, $editorialtimestamp, $editorialtext, $editorialtitle) = $resulted2->fields) {
/* Better to use ADODB to do this stuff
 * cocomp 2002/07/13
                ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $editorialtimestamp, $editorialtime);
                $timestamp = mktime($editorialtime[4],$editorialtime[5],$editorialtime[6],$editorialtime[2],$editorialtime[3],$editorialtime[1]);
                $formatted_date = date("F d, Y", $timestamp);
*/
		$formatted_date = date("F d, Y", $dbconn->UnixTimestamp($editorialtimestamp));
                echo "<center><font class=\"pn-title\"><b>Modify Editorial</b></font></center><br /><br />"
                    ."<form action=\"admin.php\" method=\"post\">"
                    .""._AUTHOR.": ".pnVarPrepForDisplay($adminid)."<br />"
                    .""._DATEWRITTEN.": $formatted_date<br /><br />"
                    ."<input type=\"hidden\" name=\"downloadid\" value=\"$lid\">"
                    .""._EDITORIALTITLE.":<br /><input type=\"text\" name=\"editorialtitle\" value=\"$editorialtitle\" size=\"50\" maxlength=\"100\"><br />"
                    .""._EDITORIALTEXT.":<br /><textarea name=\"editorialtext\" cols=\"60\" rows=\"10\">$editorialtext</textarea><br />"
                    ."</select>"    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                    ."<input type=\"hidden\" name=\"op\" value=\"DownloadsModEditorial\">"
                    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                    ."<input type=\"submit\" value=\""._MODIFY."\"> [ <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsDelEditorial&amp;downloadid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">"._DELETE."</a> ]";
                $resulted2->MoveNext();
            }
        }
    CloseTable();

    OpenTable();
    /* Show Comments */
    $column = &$pntable['downloads_votedata_column'];
// Depricated use of != '' for text columns use NOT LIKE '' instead for cross db
// compatibility - cocomp 2002/07/13
    $result5=$dbconn->Execute("SELECT $column[ratingdbid],
                                      $column[ratinguser],
                                      $column[ratingcomments],
                                      $column[ratingtimestamp]
                               FROM $pntable[downloads_votedata]
                               WHERE $column[ratinglid] = " . (int)pnVarPrepForStore($lid) . "
                               AND $column[ratingcomments] NOT LIKE ''
                               ORDER BY $column[ratingtimestamp] DESC");
    $totalcomments = $result5->PO_RecordCount();
    echo "<table valign=top width=100%>";
    echo "<tr><td colspan=7><b>Download Comments (total comments: ".pnVarPrepForDisplay($totalcomments).")</b><br /><br /></td></tr>";
    echo "<tr><td width=20 colspan=1><b>User  </b></td><td colspan=5><b>Comment  </b></td><td><b><center>"._DELETE."</center></b></td><br /></tr>";
    if ($totalcomments == 0) echo "<tr><td colspan=7><center><font color=cccccc>No Comments<br /></font></center></td></tr>";
    $x=0;
    $colorswitch="dddddd";

    while(list($ratingdbid, $ratinguser, $ratingcomments, $ratingtimestamp)=$result5->fields) {
/* Better to use ADODB to do this stuff
 * cocomp 2002/07/13
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
        $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
        $formatted_date = date("F d, Y", $timestamp);  // time format should be customizable -- besfred
*/
	$formatted_date = ml_ftime(_DATEBRIEF, $dbconn->UnixTimestamp($ratingtimestamp));
        echo "<tr><td valign=top bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinguser)."</td><td valign=top colspan=5 bgcolor=$colorswitch>" . pnVarPrepHTMLDisplay($ratingcomments) . "</td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsDelComment&amp;lid=$lid&amp;rid=$ratingdbid&amp;authid=" . pnSecGenAuthKey() . ">X</a></b></center></td><br /></tr>";
        $x++;
        if ($colorswitch=="dddddd") $colorswitch="ffffff";
            else $colorswitch="dddddd";

        $result5->MoveNext();
    }

    // Show Registered Users Votes
    $column = &$pntable['downloads_votedata_column'];
    $result5=$dbconn->Execute("SELECT $column[ratingdbid],
                                      $column[ratinguser],
                                      $column[rating],
                                      $column[ratinghostname],
                                      $column[ratingtimestamp]
                               FROM $pntable[downloads_votedata]
                               WHERE $column[ratinglid] = " .(int) pnVarPrepForStore($lid) . "
                               AND $column[ratinguser] != 'outside'
                               AND $column[ratinguser] != '" . pnVarPrepForStore($anonymous) . "'
                               ORDER BY $column[ratingtimestamp] DESC");
    $totalvotes = $result5->PO_RecordCount();
    echo "<tr><td colspan=7><br /><br /><b>Registered User Votes (total votes: $totalvotes)</b><br /><br /></td></tr>";
    echo "<tr><td><b>User  </b></td><td><b>IP Address  </b></td><td><b>Rating  </b></td><td><b>User AVG Rating  </b></td><td><b>Total Ratings  </b></td><td><b>Date  </b></td></font></b><td><b><center>"._DELETE."</center></b></td><br /></tr>";
    if ($totalvotes == 0) echo "<tr><td colspan=7><center><font color=cccccc>No Registered User Votes<br /></font></center></td></tr>";
    $x=0;
    $colorswitch="dddddd";

    while(list($ratingdbid, $ratinguser, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {
/* Better to use ADODB to do this stuff
 * cocomp 2002/07/13
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
        $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
        $formatted_date = date("F d, Y", $timestamp);
*/
	$formatted_date = ml_ftime(_DATEBRIEF, $dbconn->UnixTimestamp($ratingtimestamp));
        //Individual user information
        $column = &$pntable['downloads_votedata_column'];
        $result2=$dbconn->Execute("SELECT $column[rating]
                                   FROM $pntable[downloads_votedata]
                                   WHERE $column[ratinguser] = '" . pnVarPrepForStore($ratinguser) . "'");
            $usertotalcomments = $result2->PO_RecordCount();
            $useravgrating = 0;

        while(list($rating2)=$result2->fields) {
	        $useravgrating = $useravgrating + $rating2;
            $result2->MoveNext();
        }
        $useravgrating = $useravgrating / $usertotalcomments;
        $useravgrating = number_format($useravgrating, 1);
        echo "<tr><td bgcolor=$colorswitch>$ratinguser</td><td bgcolor=$colorswitch>$ratinghostname</td><td bgcolor=$colorswitch>$rating</td><td bgcolor=$colorswitch>$useravgrating</td><td bgcolor=$colorswitch>$usertotalcomments</td><td bgcolor=$colorswitch>$formatted_date  </font></b></td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsDelVote&amp;lid=$lid&amp;rid=$ratingdbid&amp;authid=" . pnSecGenAuthKey() . ">X</a></b></center></td></tr><br />";
        $x++;
        if ($colorswitch=="dddddd") $colorswitch="ffffff";
            else $colorswitch="dddddd";

        $result5->MoveNext();
    }

    // Show Unregistered Users Votes
    $column = &$pntable['downloads_votedata_column'];
    $result5=$dbconn->Execute("SELECT $column[ratingdbid],
                                      $column[rating],
                                      $column[ratinghostname],
                                      $column[ratingtimestamp]
                               FROM $pntable[downloads_votedata]
                               WHERE $column[ratinglid] = " . (int)pnVarPrepForStore($lid) . "
                               AND $column[ratinguser] = '" . pnVarPrepForStore($anonymous) . "'
                               ORDER BY $column[ratingtimestamp] DESC");
    $totalvotes = $result5->PO_RecordCount();
    echo "<tr><td colspan=7><b><br /><br />Unregistered User Votes (total votes: ".pnVarPrepForDisplay($totalvotes).")</b><br /><br /></td></tr>";
    echo "<tr><td colspan=2><b>IP Address  </b></td><td colspan=3><b>Rating  </b></td><td><b>Date  </b></font></td><td><b><center>"._DELETE."</center></b></td><br /></tr>";
    if ($totalvotes == 0) echo "<tr><td colspan=7><center><font color=cccccc>No Unregistered User Votes<br /></font></center></td></tr>";
    $x=0;
    $colorswitch="dddddd";

    while(list($ratingdbid, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {
/* Better to use ADODB to do this stuff
 * cocomp 2002/07/13
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
        $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
        $formatted_date = date("F d, Y", $timestamp);
*/
	$formatted_date = ml_ftime(_DATEBRIEF, $dbconn->UnixTimestamp($ratingtimestamp));
        echo "<td colspan=2 bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinghostname)."</td><td colspan=3 bgcolor=$colorswitch>$rating</td><td bgcolor=$colorswitch>".pnVarPrepForDisplay($formatted_date)."  </font></b></td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsDelVote&amp;lid=$lid&amp;rid=$ratingdbid&amp;authid=" . pnSecGenAuthKey() . ">X</a></b></center></td></tr><br />";
        $x++;
        if ($colorswitch=="dddddd") $colorswitch="ffffff";
            else $colorswitch="dddddd";

        $result5->MoveNext();
     }

    // Show Outside Users Votes
    $column = &$pntable['downloads_votedata_column'];
    $result5=$dbconn->Execute("SELECT $column[ratingdbid],
                                      $column[rating],
                                      $column[ratinghostname],
                                      $column[ratingtimestamp]
                               FROM $pntable[downloads_votedata]
                               WHERE $column[ratinglid] = " . (int)pnVarPrepForStore($lid) . "
                               AND $column[ratinguser] = 'outside'
                               ORDER BY $column[ratingtimestamp] DESC");
    $totalvotes = $result5->PO_RecordCount();
    echo "<tr><td colspan=7><b><br /><br />Outside User Votes (total votes: ".pnVarPrepForDisplay($totalvotes).")</b><br /><br /></td></tr>";
    echo "<tr><td colspan=2><b>IP Address  </b></td><td colspan=3><b>Rating  </b></td><td><b>Date  </b></td></font></b><td><b><center>"._DELETE."</center></b></td><br /></tr>";
    if ($totalvotes == 0) {
    $sitename = pnConfigGetVar('sitename');
    echo "<tr><td colspan=7><center><font color=cccccc>No Votes from Outside $sitename<br /></font></center></td></tr>";
    }
    $x=0;
    $colorswitch="dddddd";

    while(list($ratingdbid, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {
/* Better to use ADODB to do this stuff
 * cocomp 2002/07/13
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
        $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
        $formatted_date = date("F d, Y", $timestamp);
*/
	$formatted_date = ml_ftime(_DATEBRIEF, $dbconn->UnixTimestamp($ratingtimestamp));
        echo "<tr><td colspan=2 bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinghostname)."</td><td colspan=3 bgcolor=$colorswitch>".pnVarPrepForDisplay($rating)."</td><td bgcolor=$colorswitch>".pnVarPrepForDisplay($formatted_date)."  </font></b></td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&amp;op=DownloadsDelVote&amp;lid=$lid&amp;rid=$ratingdbid&amp;authid=" . pnSecGenAuthKey() . ">X</a></b></center></td></tr><br />";
        $x++;
        if ($colorswitch=="dddddd") {
	    $colorswitch="ffffff";
	} else {
	    $colorswitch="dddddd";
	}
        $result5->MoveNext();
    }

    echo "<tr><td colspan=6><br /></td></tr>";
    echo "</table>";

    }
    echo "</form>";
    CloseTable();

    include 'footer.php';
}

function DownloadsDelComment()
{

    list($lid,
         $rid) = pnVarCleanFromInput('lid',
                                     'rid');

    if (!isset($lid) || !is_numeric($lid) ||
        !isset($rid) || !is_numeric($rid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_votedata_column'];
    $dbconn->Execute("UPDATE $pntable[downloads_votedata]
                    SET $column[ratingcomments]=''
                    WHERE $column[ratingdbid] = " . (int)pnVarPrepForStore($rid));
// cocomp 2002/07/13 changed the table so must also change the column!
	$column = &$pntable['downloads_downloads_column'];
    $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                    SET $column[totalcomments] = ($column[totalcomments] - 1)
                    WHERE $column[lid] = " . (int)pnVarPrepForStore($lid));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsModDownload&lid='.$lid);

}

function DownloadsDelVote()
{
    list($lid,
         $rid) = pnVarCleanFromInput('lid',
                                     'rid');

    if	(!isset($lid) || !is_numeric($lid) ||
         !isset($rid) || !is_numeric($rid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_votedata_column'];
    $dbconn->Execute("DELETE FROM $pntable[downloads_votedata]
                    WHERE $column[ratingdbid]=$rid");
    $voteresult = $dbconn->Execute("SELECT $column[rating],
                                           $column[ratinguser],
                                           $column[ratingcomments]
                                    FROM $pntable[downloads_votedata]
                                    WHERE $column[ratinglid] = " . (int)pnVarPrepForStore($lid));
    $totalvotesDB = $voteresult->PO_RecordCount();
    $finalrating = calculateVote($voteresult, $totalvotesDB);
    $column = &$pntable['downloads_downloads_column'];
    $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                      SET $column[downloadratingsummary]=" . pnVarPrepForStore($finalrating) . ",
                          $column[totalvotes]=" . pnVarPrepForStore($totalvotesDB) . ",
                          $column[totalcomments]=" . pnVarPrepForStore($totalvotesDB) . "
                      WHERE $column[lid] = " . (int)pnVarPrepForStore($lid));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsModDownload&lid='.$lid);
}

function DownloadsListBrokenDownloads()
{

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    $column = &$pntable['downloads_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid],
                                       $column[lid],
                                       $column[modifysubmitter]
                                FROM $pntable[downloads_modrequest]
                                WHERE $column[brokendownload]=1
                                ORDER BY $column[requestid]");
    $totalbrokendownloads = $result->PO_RecordCount();
    echo "<center><font class=\"pn-title\"><b>"._DUSERREPBROKEN." (".pnVarPrepForDisplay($totalbrokendownloads).")</b></font></center><br /><br /><center>"
    .""._DIGNOREINFO."<br />"
    .""._DDELETEINFO."</center><br /><br /><br />"
    ."<table align=\"center\" width=\"450\">";
    if ($totalbrokendownloads==0) {
        echo "<center><font class=\"pn-title\">"._DNOREPORTEDBROKEN."</font></center><br /><br /><br />";
    } else {
        $colorswitch = $GLOBALS['bgcolor2'];
        echo "<tr>"
            ."<td><b>"._DOWNLOAD."</b></td>"
            ."<td><b>"._SUBMITTER."</b></td>"
            ."<td><b>"._DOWNLOADOWNER."</b></td>"
            ."<td><b>"._IGNORE."</b></td>"
            ."<td><b>"._DELETE."</b></td>"
            ."<td><b>"._EDIT."</b></td>"
            ."</tr>";
        while(list($requestid, $lid, $modifysubmitter)=$result->fields) {

            $result->MoveNext();
            $column = &$pntable['downloads_downloads_column'];
            $result2 = $dbconn->Execute("SELECT $column[title],
                                                $column[url],
                                                $column[submitter]
                                         FROM $pntable[downloads_downloads]
                                         WHERE $column[lid]=" . pnVarPrepForStore($lid));
            if ($modifysubmitter != '$anonymous') {
                $column = &$pntable['users_column'];
                $result3 = $dbconn->Execute("SELECT $column[email]
                                             FROM $pntable[users]
                                             WHERE $column[uname]='" . pnVarPrepForStore($modifysubmitter) . "'");

                list($email)=$result3->fields;
            }

            list($title, $url, $owner)=$result2->fields;
            $column = &$pntable['users_column'];
            $result4 = $dbconn->Execute("SELECT $column[email]
                                         FROM $pntable[users]
                                         WHERE $column[uname]='" . pnVarPrepForStore($owner) . "'");

            list($owneremail)=$result4->fields;
            echo "<tr>"
            ."<td bgcolor=\"$colorswitch\"><a href=\"$url\">".pnVarPrepForDisplay($title)."</a>"
            ."</td>";
            if ($email=='') {
        echo "<td bgcolor=\"$colorswitch\">".pnVarPrepForDisplay($modifysubmitter)."";
        } else {
        echo "<td bgcolor=\"$colorswitch\"><a href=\"mailto:$email\">".pnVarPrepForDisplay($modifysubmitter)."</a>";
        }
            echo "</td>";
            if ($owneremail=='') {
        echo "<td bgcolor=\"$colorswitch\">".pnVarPrepForDisplay($owner)."";
        } else {
        echo "<td bgcolor=\"$colorswitch\"><a href=\"mailto:$owneremail\">".pnVarPrepForDisplay($owner)."</a>";
        }
            echo "</td>"
            ."<td bgcolor=\"$colorswitch\"><center><a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsIgnoreBrokenDownloads&amp;lid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">X</a></center>"
            ."</td>"
            ."<td bgcolor=\"$colorswitch\"><center><a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsDelBrokenDownloads&amp;lid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">X</a></center>"
            ."</td>"
            ."<td bgcolor=\"$colorswitch\"><center><a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsModDownload&amp;lid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">X</a></center>"
            ."</td>"
        ."</tr>";
            if ($colorswitch == $GLOBALS['bgcolor2']) {
        $colorswitch = $GLOBALS['bgcolor1'];
            } else {
        $colorswitch = $GLOBALS['bgcolor2'];
        }
        }
    }
    echo "</table>";
    CloseTable();
    include 'footer.php';
}

function DownloadsDelBrokenDownloads() {

    $lid = pnVarCleanFromInput('lid');

    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[downloads_modrequest]
                    WHERE {$pntable['downloads_modrequest_column']['lid']}=".(int)pnVarPrepForStore($lid));
    $dbconn->Execute("DELETE FROM $pntable[downloads_downloads]
                    WHERE {$pntable['downloads_downloads_column']['lid']}=".(int)pnVarPrepForStore($lid));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsListBrokenDownloads');
}

function DownloadsIgnoreBrokenDownloads() {

    $lid = pnVarCleanFromInput('lid');

    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[downloads_modrequest]
                    WHERE {$pntable['downloads_modrequest_column']['lid']}=".(int)$lid."
                      AND {$pntable['downloads_modrequest_column']['brokendownload']}=1");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsListBrokenDownloads');
}

function DownloadsListModRequests() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    $column = &$pntable['downloads_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid],
                                       $column[lid],
                                       $column[cid],
                                       $column[sid],
                                       $column[title],
                                       $column[url],
                                       $column[description],
                                       $column[modifysubmitter],
                                       $column[name],
                                       $column[email],
                                       $column[filesize],
                                       $column[version],
                                       $column[homepage]
                                FROM $pntable[downloads_modrequest]
                                WHERE $column[brokendownload]=0
                                ORDER BY $column[requestid]");
    $totalmodrequests = $result->PO_RecordCount();
    echo "<center><font class=\"pn-title\"><b>"._DUSERMODREQUEST." (".pnVarPrepForDisplay($totalmodrequests).")</b></font></center><br /><br /><br />";
    echo "<table width=\"95%\"><tr><td>";
    while(list($requestid, $lid, $cid, $sid, $title, $url, $description, $modifysubmitter, $name, $email, $filesize, $version, $homepage)=$result->fields) {

        $result->MoveNext();
        /*
         * Hootbah: XXX FIXME XXX
         * There is somthing odd here. Why not just use one or two queries with
         * some joins?
         */
        $column = &$pntable['downloads_downloads_column'];
        $result2 = $dbconn->Execute("SELECT $column[cid],
                                            $column[sid],
                                            $column[title],
                                            $column[url],
                                            $column[description],
                                            $column[name],
                                            $column[email],
                                            $column[submitter],
                                            $column[filesize],
                                            $column[version],
                                            $column[homepage]
                                      FROM $pntable[downloads_downloads]
                                      WHERE $column[lid]=" . pnVarPrepForStore($lid));

        list($origcid, $origsid, $origtitle, $origurl, $origdescription, $origname, $origemail, $owner, $origfilesize, $origversion, $orighomepage)=$result2->fields;
        $column = &$pntable['downloads_categories_column'];
        $result3 = $dbconn->Execute("select $column[title] from $pntable[downloads_categories] where $column[cid]=".pnVarPrepForStore($cid)."");
        $column = &$pntable['downloads_subcategories_column'];
        $result4 = $dbconn->Execute("select $column[title] from $pntable[downloads_subcategories] where $column[cid]=$cid and $column[sid]=".pnVarPrepForStore($sid)."");
        $column = &$pntable['downloads_categories_column'];
        $result5 = $dbconn->Execute("select $column[title] from $pntable[downloads_categories] where $column[cid]=".pnVarPrepForStore($origcid)."");
        $column = &$pntable['downloads_subcategories_column'];
        $result6 = $dbconn->Execute("select $column[title] from $pntable[downloads_subcategories] where $column[cid]=$origcid and $column[sid]=".pnVarPrepForStore($origsid)."");
        $column = &$pntable['users_column'];
        $result7 = $dbconn->Execute("select $column[email] from $pntable[users] where $column[uname]='".pnVarPrepForStore($modifysubmitter)."'");
        $column = &$pntable['users_column'];
        $result8 = $dbconn->Execute("select $column[email] from $pntable[users] where $column[uname]='".pnVarPrepForStore($owner)."'");

        list($cidtitle)=$result3->fields;
        list($sidtitle)=$result4->fields;
        list($origcidtitle)=$result5->fields;
        list($origsidtitle)=$result6->fields;
        list($modifysubmitteremail)=$result7->fields;
        list($owneremail)=$result8->fields;

        if ($owner=="") {
            $owner="administration";
        }
        if ($origsidtitle=="") {
            $origsidtitle= "-----";
        }
        if ($sidtitle=="") {
            $sidtitle= "-----";
        }
        echo "<table border=\"1\" bordercolor=\"black\" cellpadding=\"5\" cellspacing=\"0\" align=\"center\" width=\"450\">"
            ."<tr>"
            ."<td>"
            ."<table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
            ."<tr>"
            ."<td valign=\"top\" width=\"45%\"><b>"._ORIGINAL."</b></td>"
            ."<td rowspan=\"10\" valign=\"top\" align=\"left\"><font class=\"pn-sub\"><br />"._DESCRIPTION.":<br />".pnVarPrepForDisplay($origdescription)."</font></td>"
            ."</tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._TITLE.": ".pnVarPrepForDisplay($origtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._URL.": <a href=\"$origurl\">".pnVarPrepForDisplay($origurl)."</a></td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._CATEGORY.": ".pnVarPrepForDisplay($origcidtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._SUBCATEGORY.": ".pnVarPrepForDisplay($origsidtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._AUTHORNAME.": ".pnVarPrepForDisplay($origname)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._AUTHOREMAIL.": ".pnVarPrepForDisplay($origemail)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._FILESIZE.": ".pnVarPrepForDisplay($origfilesize)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._VERSION.": ".pnVarPrepForDisplay($origversion)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._HOMEPAGE.": <a href=\"$orighomepage\" target=\"new\">".pnVarPrepForDisplay($orighomepage)."</a></td></tr>"
            ."</table>"
            ."</td>"
            ."</tr>"
            ."<tr>"
            ."<td>"
            ."<table width=\"100%\">"
            ."<tr>"
            ."<td valign=\"top\" width=\"45%\"><b>"._PROPOSED."</b></td>"
            ."<td rowspan=\"10\" valign=\"top\" align=\"left\"><font class=\"pn-sub\"><br />"._DESCRIPTION.":<br />".pnVarPrepForDisplay($description)."</font></td>"
            ."</tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._TITLE.": ".pnVarPrepForDisplay($title)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._URL.": <a href=\"$url\">".pnVarPrepForDisplay($url)."</a></td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._CATEGORY.": ".pnVarPrepForDisplay($cidtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._SUBCATEGORY.": ".pnVarPrepForDisplay($sidtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._AUTHORNAME.": ".pnVarPrepForDisplay($name)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._AUTHOREMAIL.": ".pnVarPrepForDisplay($email)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._FILESIZE.": ".pnVarPrepForDisplay($filesize)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._VERSION.": ".pnVarPrepForDisplay($version)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._HOMEPAGE.": <a href=\"$homepage\" target=\"new\">".pnVarPrepForDisplay($homepage)."</a></td></tr>"
            ."</table>"
            ."</td>"
            ."</tr>"
            ."</table>"
            ."<table align=\"center\" width=\"450\">"
            ."<tr>";
        if ($modifysubmitteremail=="") {
            echo "<td align=\"left\"><font class=\"pn-sub\">"._SUBMITTER.":  ".pnVarPrepForDisplay($modifysubmitter)."</font></td>";
        } else {
        echo "<td align=\"left\"><font class=\"pn-sub\">"._SUBMITTER.":  <a href=\"mailto:$modifysubmitteremail\">".pnVarPrepForDisplay($modifysubmitter)."</a></font></td>";
        }
        if ($owneremail=="") {
            echo "<td align=\"center\"><font class=\"pn-sub\">"._OWNER.":  ".pnVarPrepForDisplay($owner)."</font></td>";
        } else {
        echo "<td align=\"center\"><font class=\"pn-sub\">"._OWNER.": <a href=\"mailto:$owneremail\">".pnVarPrepForDisplay($owner)."</a></font></td>";
        }
        echo "<td align=\"right\"><font class=\"pn-sub\">( <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsChangeModRequests&amp;requestid=$requestid&amp;authid=" . pnSecGenAuthKey() . "\">"._ACCEPT."</a> / <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsChangeIgnoreRequests&amp;requestid=$requestid&amp;authid=" . pnSecGenAuthKey() . "\">"._IGNORE."</a> )</font></td></tr></table><br /><br />";
    }
    if ($totalmodrequests == 0) {
        echo "<center>"._NOMODREQUESTS."<br /><br />"
        .""._GOBACK."<br /><br /></center>";
    }
    echo "</td></tr></table>";
    CloseTable();
    include ("footer.php");
}

function DownloadsChangeModRequests() {

    $requestid = pnVarCleanFromInput('requestid');

    if	(!isset($requestid) || !is_numeric($requestid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid],
                                       $column[lid],
                                       $column[cid],
                                       $column[sid],
                                       $column[title],
                                       $column[url],
                                       $column[description],
                                       $column[name],
                                       $column[email],
                                       $column[filesize],
                                       $column[version],
                                       $column[homepage]
                                FROM $pntable[downloads_modrequest]
                                WHERE $column[requestid]=" . (int)pnVarPrepForStore($requestid));

    while(list($requestid, $lid, $cid, $sid, $title, $url, $description, $name, $email, $filesize, $version, $homepage)=$result->fields) {
        $column = &$pntable['downloads_downloads_column'];
        $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                          SET $column[cid]=" . pnVarPrepForStore($cid) . ",
                              $column[sid]=" . pnVarPrepForStore($sid) . ",
                              $column[title]='" . pnVarPrepForStore($title) . "',
                              $column[url]='" . pnVarPrepForStore($url) . "',
                              $column[description]='" . pnVarPrepForStore($description) . "',
                              $column[name]='" . pnVarPrepForStore($name) . "',
                              $column[email]='" . pnVarPrepForStore($email) . "',
                              $column[filesize]='" . pnVarPrepForStore($filesize) . "',
                              $column[version]='" . pnVarPrepForStore($version) . "',
                              $column[homepage]='" . pnVarPrepForStore($homepage) . "'
                        WHERE $column[lid] = " . pnVarPrepForStore($lid));

		$changerow = &$pntable['downloads_modrequest_column']['requestid'];
        $dbconn->Execute("DELETE FROM $pntable[downloads_modrequest]
						WHERE $changerow = ".pnVarPrepForStore($requestid)."");
        $result->MoveNext();
    }

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsListModRequests');
}

function DownloadsChangeIgnoreRequests() {

    $requestid = pnVarCleanFromInput('requestid');

    if	(!isset($requestid) || !is_numeric($requestid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();


    $ignorerow = &$pntable['downloads_modrequest_column']['requestid'];
    $dbconn->Execute("DELETE FROM $pntable[downloads_modrequest]
					 WHERE $ignorerow = ".pnVarPrepForStore($requestid)."");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=DownloadsListModRequests');
}

function DownloadsCleanVotes() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $totalvoteresult = $dbconn->Execute("SELECT DISTINCT {$pntable['downloads_votedata_column']['ratinglid']}
                                         FROM $pntable[downloads_votedata]");

    while(list($lid)=$totalvoteresult->fields) {
        $column = &$pntable['downloads_votedata_column'];
        $voteresult = $dbconn->Execute("SELECT $column[rating],
                                               $column[ratinguser],
                                               $column[ratingcomments]
                                        FROM $pntable[downloads_votedata]
                                        WHERE $column[ratinglid] = " . pnVarPrepForStore($lid));
        $totalvotesDB = $voteresult->PO_RecordCount();
        $finalrating = calculateVote($voteresult, $totalvotesDB);
        $column = &$pntable['downloads_downloads_column'];
        $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                          SET $column[downloadratingsummary]=" . pnVarPrepForStore($finalrating) . ",
                              $column[totalvotes]=" . pnVarPrepForStore($totalvotesDB) . ",
                              $column[totalcomments]=" . pnVarPrepForStore($totalvotesDB) . "
                          WHERE $column[lid] = " . pnVarPrepForStore($lid));
        $totalvoteresult->MoveNext();
    }

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=downloads');
}

function DownloadsModDownloadS()
{
    list($lid,
         $title,
         $url,
         $description,
         $name,
         $email,
         $hits,
         $cat,
         $filesize,
         $version,
         $homepage) = pnVarCleanFromInput('lid',
                                          'title',
                                          'url',
                                          'description',
                                          'name',
                                          'email',
                                          'hits',
                                          'cat',
                                          'filesize',
                                          'version',
                                          'homepage');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $cat = explode("-", $cat);
	if (empty($cat[0]) || !is_numeric($cat[0])) $cat[0] = 0;
	if (empty($cat[1]) || !is_numeric($cat[1])) $cat[1] = 0;
    $column = &$pntable['downloads_downloads_column'];
    $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                      SET $column[cid]=" . (int)pnVarPrepForStore($cat[0]) . ",
                          $column[sid]=" . (int)pnVarPrepForStore($cat[1]) . ",
                          $column[title]='" . pnVarPrepForStore($title) . "',
                          $column[url]='" . pnVarPrepForStore($url) . "',
                          $column[description]='" . pnVarPrepForStore($description) . "',
                          $column[name]='" . pnVarPrepForStore($name) . "',
                          $column[email]='" . pnVarPrepForStore($email) . "',
                          $column[hits]='" . pnVarPrepForStore($hits) . "',
                          $column[filesize]='" . pnVarPrepForStore($filesize) . "',
                          $column[version]='" . pnVarPrepForStore($version) . "',
                          $column[homepage]='" . pnVarPrepForStore($homepage) . "'
                      WHERE $column[lid]=" . (int)pnVarPrepForStore($lid));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=downloads');
}

function DownloadsDelDownload() {

    $lid = pnVarCleanFromInput('lid');

    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[downloads_downloads]
                    WHERE {$pntable[downloads_downloads_column][lid]}=".(int)$lid."");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=downloads');
}

function DownloadsDelNew() {
    $lid = pnVarCleanFromInput('lid');

    if	(!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[downloads_newdownload]
                    WHERE {$pntable['downloads_newdownload_column']['lid']}=".(int)pnVarPrepForStore($lid)."");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=downloads');
}

function DownloadsAddEditorial()
{
    list($aid,
         $downloadid,
         $editorialtitle,
         $editorialtext) = pnVarCleanFromInput('aid',
                                               'downloadid',
                                               'editorialtitle',
                                               'editorialtext');

    if	(!isset($downloadid) || !is_numeric($downloadid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column = &$pntable['downloads_editorials_column'];
// cocomp 2002/07/13 altered adminid to user pnUserGetVar('uid') as $aid was not
// supplied anyway.
// Also changed now() to $dbconn->DBTimestamp(time()) for cross db compatability
    $dbconn->Execute("INSERT INTO $pntable[downloads_editorials]
                        ($column[downloadid],
                         $column[adminid],
                         $column[editorialtimestamp],
                         $column[editorialtext],
                         $column[editorialtitle])
                      VALUES
                        (" . (int)pnVarPrepForStore($downloadid). ",
                         '" . pnVarPrepForStore(pnUserGetVar('uid')) . "',
						  " . $dbconn->DBTimestamp(time()) . ",
                         '" . pnVarPrepForStore($editorialtext) . "',
                         '" . pnVarPrepForStore($editorialtitle) . "')");
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><br />"
    ."<font size=3>"
    .""._EDITORIALADDED."<br /><br />"
    ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=downloads\">"._WEBDOWNLOADSADMIN."</a> ]<br /><br />";
    echo "<table border=\"0\"><tr><td align=\"left\">";
    echo "<b>"._DOWNLOADID.":</b> ".pnVarPrepForDisplay($downloadid)."<br /><br />"
    ."<b>"._EDITORIALTITLE.":</b><br />".pnVarPrepForDisplay($editorialtitle)."<br /><br />"
    ."<b>"._EDITORIALTEXT.":</b><br />".pnVarPrepHTMLDisplay($editorialtext)."<br />";
    echo "</td></tr></table>";
    CloseTable();
    include("footer.php");
}

function DownloadsModEditorial() {

    list($downloadid,
         $editorialtitle,
         $editorialtext) = pnVarCleanFromInput('downloadid',
                                               'editorialtitle',
                                               'editorialtext');

    if	(!isset($downloadid) || !is_numeric($downloadid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_editorials_column'];
    $dbconn->Execute("UPDATE $pntable[downloads_editorials]
                      SET $column[editorialtext]='" . pnVarPrepForStore($editorialtext) . "',
                          $column[editorialtitle]='" . pnVarPrepForStore($editorialtitle) . "'
                      WHERE $column[downloadid]=" . (int)pnVarPrepForStore($downloadid));
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br /><center>"
    ."<font class=\"pn-title\">"
    .""._EDITORIALMODIFIED."<br /><br />"
    ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=downloads\">"._WEBDOWNLOADSADMIN."</a> ]<br /><br />";
    CloseTable();
    include("footer.php");
}

function DownloadsDelEditorial()
{
    $downloadid = pnVarCleanFromInput('downloadid');

    if	(!isset($downloadid) || !is_numeric($downloadid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[downloads_editorials]
                    WHERE {$pntable[downloads_editorials_column][downloadid]}=".(int)pnVarPrepForDisplay($downloadid)."");
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br /><center>"
    ."<font class=\"pn-title\">"
    .""._EDITORIALREMOVED."<br /><br />"
    ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=downloads\">"._WEBDOWNLOADSADMIN."</a> ]<br /><br />";
    CloseTable();
    include("footer.php");
}

function DownloadsDownloadCheck() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._DOWNLOADVALIDATION."</b></font></center><br />"
        ."<table width=\"100%\" align=\"center\"><tr><td colspan=\"2\" align=\"center\">"
        ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsValidate&amp;cid=0&amp;sid=0\">"._CHECKALLDOWNLOADS."</a><br /><br /></td></tr>"
        ."<tr><td valign=\"top\"><center><b>"._CHECKCATEGORIES."</b><br />"._INCLUDESUBCATEGORIES."<br /><br /><font class=\"pn-sub\">";
    $column = &$pntable['downloads_categories_column'];
    $result = $dbconn->Execute("SELECT $column[cid],
                                       $column[title]
                                FROM $pntable[downloads_categories]
                                ORDER BY $column[title]");

    while(list($cid, $title) = $result->fields) {
        $transfertitle = str_replace (" ", "_", $title);
        echo "<a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsValidate&amp;cid=$cid&amp;sid=0&amp;ttitle=$transfertitle\">".pnVarPrepForDisplay($title)."</a><br />";
        $result->MoveNext();
    }
    echo "</font></center></td>";
    echo "<td valign=\"top\"><center><b>"._CHECKSUBCATEGORIES."</b><br /><br /><br /><font class=\"pn-sub\">";
    $column = &$pntable['downloads_subcategories_column'];
    $result = $dbconn->Execute("SELECT $column[sid],
                                       $column[cid],
                                       $column[title]
                                FROM $pntable[downloads_subcategories]
                                ORDER BY $column[title]");

    while(list($sid, $cid, $title) = $result->fields) {
        $transfertitle = str_replace (" ", "_", $title);
        $column = &$pntable['downloads_categories_column'];
        $result2 = $dbconn->Execute("SELECT $column[title]
                                     FROM $pntable[downloads_categories]
                                     WHERE $column[cid] = " . pnVarPrepForStore($cid));

        while(list($ctitle) = $result2->fields) {
            echo "<a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsValidate&amp;cid=0&amp;sid=$sid&amp;ttitle=$transfertitle\">".pnVarPrepForDisplay($ctitle)."</a>";
            $result2->MoveNext();
        }
        echo " / <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsValidate&amp;cid=0&amp;sid=$sid&amp;ttitle=$transfertitle\">".pnVarPrepForDisplay($title)."</a><br />";
        $result->MoveNext();
    }
    echo "</font></center></td></tr></table>";
    CloseTable();
    include ("footer.php");
}

function DownloadsValidate()
{
    list($cid,
         $aid,
         $sid,
         $ttitle) = pnVarCleanFromInput('cid',
                                        'aid',
                                        'sid',
                                        'ttitle');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!isset($sid)) {
        $sid = 0;
    }
    if (!isset($cid) || !is_numeric($cid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }


    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    $transfertitle = str_replace ("_", "", $ttitle);
    /* Check ALL Downloads */
    echo "<table width=100% border=0>";
    if ($cid==0 && $sid==0) {
    echo "<tr><td colspan=\"3\"><center><b>"._CHECKALLDOWNLOADS."</b><br />"._BEPATIENT."</center><br /><br /></td></tr>";
    $column = &$pntable['downloads_downloads_column'];
    $result = $dbconn->Execute("SELECT $column[lid],
                                       $column[title],
                                       $column[url],
                                       $column[name],
                                       $column[email],
                                       $column[submitter]
                                FROM $pntable[downloads_downloads]
                                ORDER BY $column[title]");
    }
    /* Check Categories & Subcategories */
    if ($cid!=0 && $sid==0) {
    echo "<tr><td colspan=\"3\"><center><b>"._VALIDATINGCAT.": $transfertitle</b><br />"._BEPATIENT."</center><br /><br /></td></tr>";
    $column = &$pntable['downloads_downloads_column'];
    $result = $dbconn->Execute("SELECT $column[lid],
                                       $column[title],
                                       $column[url],
                                       $column[name],
                                       $column[email],
                                       $column[submitter]
                                FROM $pntable[downloads_downloads]
                                WHERE $column[cid]=" . (int)pnVarPrepForStore($cid) . "
                                ORDER BY $column[title]");
    }
    /* Check Only Subcategory */
    if ($cid==0 && $sid!=0) {
    echo "<tr><td colspan=\"3\"><center><b>"._VALIDATINGSUBCAT.": ".pnVarPrepForDisplay($transfertitle)."</b><br />"._BEPATIENT."</center><br /><br /></td></tr>";
    $column = &$pntable['downloads_downloads_column'];
    $result = $dbconn->Execute("SELECT $column[lid],
                                       $column[title],
                                       $column[url],
                                       $column[name],
                                       $column[email],
                                       $column[submitter]
                                FROM $pntable[downloads_downloads]
                                WHERE $column[sid]=" . (int)pnVarPrepForStore($sid) . "
                                ORDER BY $column[title]");
    }
    echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._STATUS."</b></td><td bgcolor=\"".$GLOBALS['bgcolor2']."\" width=\"100%\"><b>"._DOWNLOADTITLE."</b></td><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._FUNCTIONS."</b></td></tr>";

    while(list($lid, $title, $url, $name, $email, $submitter) = $result->fields) {
        $vurl = parse_url($url);
        $fp = fsockopen ($vurl['host'], 80, $errno, $errstr, 30);
        if (!$fp){
                echo "<tr><td align=\"center\"><b>&nbsp;&nbsp;"._FAILED."&nbsp;(nc)&nbsp;</b></td>"
                ."<td>&nbsp;&nbsp;<a href=\"$url\" target=\"new\">".pnVarPrepForDisplay($title)."</a>&nbsp;&nbsp;</td>"
                ."<td align=\"center\"><font class=\"pn-normal\">&nbsp;&nbsp;[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsModDownload&amp;lid=$lid\">"._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsDelDownload&amp;lid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">"._DELETE."</a> ]&nbsp;&nbsp;</font>"
                ."</td></tr>";
        } else {
            fputs ($fp, "HEAD ".$url." HTTP/1.0\r\n\r\n");
            $buffer = fgets($fp,256);
            if( (eregi("OK", $buffer)) || (eregi("302 Found", $buffer)) ) {
                echo "<tr><td align=\"center\">&nbsp;&nbsp;"._OK."&nbsp;&nbsp;</td>"
                ."<td>&nbsp;&nbsp;<a href=\"$url\" target=\"new\">".pnVarPrepForDisplay($title)."</a>&nbsp;&nbsp;</td>"
                ."<td align=\"center\"><font class=\"pn-normal\">&nbsp;&nbsp;"._NONE."&nbsp;&nbsp;</font>"
                ."</td></tr>";
            } else {
                echo "<tr><td align=\"center\"><b>&nbsp;&nbsp;"._FAILED."</b>&nbsp;&nbsp;<br /><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"1\" class=\"pn-normal\">".str_replace("HTTP/1.0", "", $buffer)."</font></td>"
                ."<td>&nbsp;&nbsp;<a href=\"$url\" target=\"new\">".pnVarPrepForDisplay($title)."</a>&nbsp;&nbsp;</td>"
                ."<td align=\"center\"><font class=\"pn-normal\">&nbsp;&nbsp;[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsModDownload&amp;lid=$lid\">"._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=DownloadsDelDownload&amp;lid=$lid&amp;authid=" . pnSecGenAuthKey() . "\">"._DELETE."</a> ]&nbsp;&nbsp;</font>"
                ."</td></tr>";
            }
            fclose ($fp);
        }
        $result->MoveNext();
    }
    echo "</table>";
    CloseTable();
    include ("footer.php");
}

function DownloadsAddDownload()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($new,
         $lid,
         $title,
         $url,
         $cat,
         $description,
         $name,
         $email,
         $submitter,
         $filesize,
         $version,
         $homepage,
         $hits) = pnVarCleanFromInput('new',
                                      'lid',
                                      'title',
                                      'url',
                                      'cat',
                                      'description',
                                      'name',
                                      'email',
                                      'submitter',
                                      'filesize',
                                      'version',
                                      'homepage',
                                      'hits');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    /*
     * Hootbah: XXX FIXME XXX I don't think we need the following query.
     * It seems to be only used for numRows
     */
    $column = &$pntable['downloads_downloads_column'];
    $result = $dbconn->Execute("SELECT $column[url]
                                FROM $pntable[downloads_downloads]
                                WHERE $column[url]='" . pnVarPrepForStore($url) . "'");
    $numrows = $result->PO_RecordCount();

    $error="";
    if ($description=="")        { $error = _ERRORNODESCRIPTION;}
    elseif ($title=="")          { $error = _ERRORNOTITLE;  }
    elseif ($numrows>0)          { $error = _ERRORURLEXIST; }
    elseif ($url=="")            { $error = _ERRORNOURL;    }
    if ($hits == "") {
        $hits = 0;
    }

    if ($error!="") {
		include("header.php");
		GraphicAdmin();
		OpenTable();
		echo "<center><font class=\"pn-title\"><b>"._WEBDOWNLOADSADMIN."</b></font></center>";
		CloseTable();

		OpenTable();
		echo "<br /><center>"
			."<font class=\"pn-title\">"
			."<b>$error</b><br /><br />"
			.""._GOBACK."<br /><br />";
		CloseTable();
		include("footer.php");
    } else {

    $cat = explode("-", $cat);
    if (empty($cat[1])) $cat[1] = 0;
// cocomp 2002/07/13 Converted to use GenID and not use NULL for id insert
// removed now() replaced with DBTimestamp(time()) for cross db compatibility
	$downtable = $pntable['downloads_downloads'];
	$newid = $dbconn->GenID($downtable);
    $dbconn->Execute("INSERT INTO $downtable
                        ($column[lid],
                         $column[cid],
                         $column[sid],
                         $column[title],
                         $column[url],
                         $column[description],
                         $column[date],
                         $column[name],
                         $column[email],
                         $column[hits],
                         $column[submitter],
                         $column[downloadratingsummary],
                         $column[totalvotes],
                         $column[totalcomments],
                         $column[filesize],
                         $column[version],
                         $column[homepage])
                      VALUES
                        (" . (int)pnVarPrepForStore($newid) . ",
                         " . (int)pnVarPrepForStore($cat[0]) .",
                         " . (int)pnVarPrepForStore($cat[1]) .",
                         '" . pnVarPrepForStore($title) . "',
                         '" . pnVarPrepForStore($url) . "',
                         '" . pnVarPrepForStore($description) . "',
                          " . $dbconn->DBTimestamp(time()) . ",
                         '" . pnVarPrepForStore($name) . "',
                         '" . pnVarPrepForStore($email) . "',
                          " . pnVarPrepForStore($hits) . ",
                         '" . pnVarPrepForStore($submitter) . "',
                         0,
                         0,
                         0,
                         '" . pnVarPrepForStore($filesize) . "',
                         '" . pnVarPrepForStore($version) . "',
                         '" . pnVarPrepForStore($homepage) . "')");

    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br /><center>";
    echo "<font class=\"pn-title\">";
    echo ""._NEWDOWNLOADADDED."<br /><br />";
    echo "[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=downloads\">"._WEBDOWNLOADSADMIN."</a> ]</center><br /><br />";
    CloseTable();
    if ($new==1) {
    $column = &$pntable['downloads_newdownload_column'];
    $dbconn->Execute("DELETE FROM $pntable[downloads_newdownload] WHERE $column[lid]=".pnVarPrepForDisplay($lid)."");
    }
    include("footer.php");
    }
}

function downloads_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_anonadddownloadlock['0'] = '';
    $sel_anonadddownloadlock['1'] = '';
    $sel_anonadddownloadlock[pnConfigGetVar('downloads_anonadddownloadlock')] = ' checked';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._DOWNLOADSCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._ANONPOSTDOWNLOADS.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xdownloads_anonadddownloadlock\" value=\"1\" class=\"pn-normal\"".$sel_anonadddownloadlock['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xdownloads_anonadddownloadlock\" value=\"0\" class=\"pn-normal\"".$sel_anonadddownloadlock['0'].">"._NO
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function downloads_admin_setConfig($var) {

    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array();

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
	//$var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array('xdownloads_anonadddownloadlock');

    foreach ($fixvars as $v) {
        if (empty($var[$v])) {
            $var[$v] = 0;
        }
    }

    // all variables starting with x are the config vars.
    while (list ($key, $val) = each ($var)) {
        if (substr($key, 0, 1) == 'x') {
            pnConfigSetVar(substr($key, 1), $val);
        }
    }
    pnRedirect('admin.php');
}

function downloads_admin_main($var) {
    $op = pnVarCleanFromInput('op');
    extract($var);
    switch ($op)
    {
           case "downloads":
                downloads();
                break;

            case "DownloadsDelNew":
                 DownloadsDelNew();
                 break;

            case "DownloadsAddCat":
                 DownloadsAddCat();
                 break;

            case "DownloadsAddSubCat":
                 DownloadsAddSubCat();
                 break;

            case "DownloadsAddDownload":
                DownloadsAddDownload();
                 break;

            case "DownloadsAddEditorial":
                 DownloadsAddEditorial();
                 break;

            case "DownloadsModEditorial":
                 DownloadsModEditorial();
                 break;

            case "DownloadsDownloadCheck":
                 DownloadsDownloadCheck();
                 break;

            case "DownloadsValidate":
                 DownloadsValidate();
                 break;

            case "DownloadsDelEditorial":
                 DownloadsDelEditorial();
                 break;

            case "DownloadsCleanVotes":
                DownloadsCleanVotes();
                break;

            case "DownloadsListBrokenDownloads":
                DownloadsListBrokenDownloads();
                break;

            case "DownloadsDelBrokenDownloads":
                DownloadsDelBrokenDownloads();
                break;

            case "DownloadsIgnoreBrokenDownloads":
               DownloadsIgnoreBrokenDownloads();
               break;

            case "DownloadsListModRequests":
               DownloadsListModRequests();
               break;

            case "DownloadsChangeModRequests":
               DownloadsChangeModRequests();
               break;

            case "DownloadsChangeIgnoreRequests":
               DownloadsChangeIgnoreRequests();
               break;

            case "DownloadsDelCat":
                 DownloadsDelCat();
                 break;

            case "DownloadsModCat":
                 DownloadsModCat($cat);
                 break;

            case "DownloadsModCatS":
                 DownloadsModCatS();
                 break;

            case "DownloadsModDownload":
                 DownloadsModDownload();
                 break;

            case "DownloadsModDownloadS":
                 DownloadsModDownloadS();
                 break;

            case "DownloadsDelDownload":
                 DownloadsDelDownload();
                 break;

            case "DownloadsDelVote":
                 DownloadsDelVote();
                 break;

            case "DownloadsDelComment":
                 DownloadsDelComment();
                 break;

             case "getConfig":
                  downloads_admin_getConfig();
                  break;

            case "setConfig":
                 downloads_admin_setConfig($var);
                 break;

            default:
                    downloads();
                    break;
      }
   }
} else {
    echo "Access Denied";
}
?>