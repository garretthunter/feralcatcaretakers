<?php
// File: $Id: dl-adddownload.php,v 1.5 2002/10/24 19:05:19 skooter Exp $ $Name:  $
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

function AddDownload() {
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!(pnSecAuthAction(0, 'Downloads::Item', '::', ACCESS_COMMENT))) {
        echo _DOWNLOADSADDNOAUTH;
        include 'footer.php';
        return;
    }
    $maindownload = 1;
    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-title\">"._ADDADOWNLOAD."</font></center><br><br>";
    if (pnUserLoggedIn() || pnConfigGetVar('downloads_anonadddownloadlock') != 1) {
        echo "<font class=\"pn-titlel\">"._INSTRUCTIONS.":</font><br>"
        ."<font class=\"pn-normal\"><strong><big>&middot;</big></strong> "._DSUBMITONCE."<br>"
        ."<strong><big>&middot;</big></strong> "._DPOSTPENDING."<br>"
        ."<strong><big>&middot;</big></strong> "._USERANDIP."</font><br>"
        ."<form action=\"modules.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
        ."<input type=\"hidden\" name=\"name\" value=\"".$GLOBALS['ModName']."\">\n"
        ."<input type=\"hidden\" name=\"file\" value=\"index\">"
        ."<font class=\"pn-normal\">"._DOWNLOADNAME."</font>: <input type=\"text\" name=\"title\" size=\"40\" maxlength=\"100\"><br>"
        ."<font class=\"pn-normal\">"._FILEURL."</font>: <input type=\"text\" name=\"url\" size=\"50\" maxlength=\"254\" value=\"http://\"><br>";
        $column = &$pntable['downloads_categories_column'];
        $result=$dbconn->Execute("SELECT $column[cid], $column[title]
                                FROM $pntable[downloads_categories]
                                ORDER BY $column[title]");
        echo "<font class=\"pn-normal\">"._CATEGORY."</font>: <select name=\"cat\">";
        while(list($cid, $title) = $result->fields) {

            $result->MoveNext();
            if (pnSecAuthAction(0, 'Downloads::Category', "$title::$cid", ACCESS_COMMENT)) {
                echo "<option value=\"$cid\">".pnVarPrepForDisplay($title)."</option>";
                $column=&$pntable['downloads_subcategories_column'];
                $result2=$dbconn->Execute("SELECT $column[sid], $column[title]
                                           FROM $pntable[downloads_subcategories]
                                           WHERE $column[cid]=".pnVarPrepForStore($cid)." ORDER BY $column[title]");
                while(list($sid, $stitle) = $result2->fields) {

                    $result2->MoveNext();
                    if (pnSecAuthAction(0, 'Downloads::Category', "$stitle::$sid", ACCESS_COMMENT)) {
                        echo "<option value=\"$cid-$sid\">".pnVarPrepForDisplay($title)." / ".pnVarPrepForDisplay($stitle)."</option>";
                    }
                }
            }
        }
        echo "</select><br><br>"
        ."<font class=\"pn-normal\">"._LDESCRIPTION."</font><br><textarea name=\"description\" cols=\"60\" rows=\"8\"></textarea><br><br>"
        ."<font class=\"pn-normal\">"._AUTHORNAME."</font>: <input type=\"text\" name=\"nname\" size=\"30\" maxlength=\"60\"><br>"
        ."<font class=\"pn-normal\">"._AUTHOREMAIL."</font>: <input type=\"text\" name=\"email\" size=\"30\" maxlength=\"60\"><br>"
        ."<font class=\"pn-normal\">"._FILESIZE."</font>: <input type=\"text\" name=\"filesize\" size=\"12\" maxlength=\"11\"> <font class=\"pn-normal\">("._INBYTES.")</font><br>"
        ."<font class=\"pn-normal\">"._VERSION."</font>: <input type=\"text\" name=\"version\" size=\"11\" maxlength=\"10\"><br>"
        ."<font class=\"pn-normal\">"._HOMEPAGE."</font>: <input type=\"text\" name=\"homepage\" size=\"50\" maxlength=\"200\" value=\"http://\"><br><br>"
        ."<input type=\"hidden\" name=\"req\" value=\"Add\">"
        ."<input type=\"submit\" value=\""._ADDTHISFILE."\"> <font class=\"pn-normal\">"._GOBACK."</font><br><br>"
        ."</form>";
    } else {
        echo "<center><font class=\"pn-normal\">"._DOWNLOADSNOTUSER1."<br>"
        .""._DOWNLOADSNOTUSER2."<br><br>"
        .""._DOWNLOADSNOTUSER3."<br>"
        .""._DOWNLOADSNOTUSER4."<br>"
        .""._DOWNLOADSNOTUSER5."<br>"
        .""._DOWNLOADSNOTUSER6."<br>"
        .""._DOWNLOADSNOTUSER7."<br><br>"
        .""._DOWNLOADSNOTUSER8."</font></center>";
    }
    CloseTable();
    include("footer.php");
}

function Add()
{
    list($title,
         $url,
         $nname,
         $cat,
         $description,
         $name,
         $email,
         $filesize,
         $version,
         $homepage) = pnVarCleanFromInput('title',
                                          'url',
                                          'nname',
                                          'cat',
                                          'description',
                                          'name',
                                          'email',
                                          'filesize',
                                          'version',
                                          'homepage');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column=&$pntable['downloads_downloads_column'];
/* hootbah: I think that this is only getting the count(*) value back.
 *
 *  $result = $dbconn->query("SELECT $column[url]
 *                            FROM $pntable[downloads_downloads]
 *                            WHERE $column[url]='$url'");
 *  $numrows = numRows($result);
 */
    include 'header.php';
    menu(1);

    if (!isset($cat)) {
        echo _DOWNLOADSNOCATS;
        include 'footer.php';
    }

    $catname = downloads_CatNameFromCID($cat);
    if (!(pnSecAuthAction(0, 'Downloads::Item', "$title:$catname:", ACCESS_COMMENT))) {
        echo _DOWNLOADSADDNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    $result = $dbconn->Execute("SELECT count(*)
                              FROM $pntable[downloads_downloads]
                              WHERE $column[url]='".pnVarPrepForStore($url)."'");
    list($numrows) = $result->fields;
    if ($numrows>0) {
        echo "<center><font class=\"pn-normal\">"._DOWNLOADALREADYEXT."</font><br><br>"
        ."<font class=\"pn-normal\">"._GOBACK."</font>";
        CloseTable();
        include("footer.php");
    } else {
        if (pnUserLoggedIn()) {
            $submitter = pnUserGetVar('uname');
        }
// Check if Title exist
        if ($title=="") {
            echo "<center><font class=\"pn-normal\">"._DOWNLOADNOTITLE."</font><br><br>"
            ."<font class=\"pn-normal\">"._GOBACK."</font>";
            CloseTable();
            include("footer.php");
            return;
        }
// Check if URL exist
        if ($url=="") {
            echo "<center><font class=\"pn-normal\">"._DOWNLOADNOURL."</font><br><br>"
            ."<font class=\"pn-normal\">"._GOBACK."</font>";
            CloseTable();
            include("footer.php");
            return;
        }
// Check if Description exist
        if ($description=="") {
            echo "<center><font class=\"pn-normal\">"._DOWNLOADNODESC."</font><br><br>"
            ."<font class=\"pn-normal\">"._GOBACK."</font>";
            CloseTable();
            include("footer.php");
            return;
        }
        $cat = explode("-", $cat);
		if	(!isset($cat[0]) || !is_numeric($cat[0])){
			$cat[0] = 0;
		}
		if	(empty($cat[1]) || !is_numeric($cat[1])){
			$cat[1] = 0;
		}

        $filesize = ereg_replace("\.","",$filesize);
        $filesize = ereg_replace("\,","",$filesize);
        $column = &$pntable['downloads_newdownload_column'];
// cocomp 2002/07/13 changed to use GenID instead of NULL for id insert
	$newtable = $pntable['downloads_newdownload'];
	$lid = $dbconn->GenID($newtable);
        $result=$dbconn->Execute("INSERT INTO $newtable
                                ($column[lid], $column[cid], $column[sid],
                                 $column[title], $column[url], $column[description],
                                 $column[name], $column[email], $column[submitter],
                                 $column[filesize], $column[version],
                                 $column[homepage])
                                VALUES (".(int)pnVarPrepForStore($lid).", ".(int)$cat[0].", ".(int)$cat[1].", '".pnVarPrepForStore($title)."', '".pnVarPrepForStore($url)."',
                                 '".pnVarPrepForStore($description)."', '".pnVarPrepForStore($nname)."', '".pnVarPrepForStore($email)."', '".pnVarPrepForStore($submitter)."',
                                 '".pnVarPrepForStore($filesize)."', '".pnVarPrepForStore($version)."', '".pnVarPrepForStore($homepage)."')");
        OpenTable();
        echo "<center><font class=\"pn-normal\">"._DOWNLOADRECEIVED."</font><br>";
        if ($email == "") {
            echo "<font class=\"pn-normal\">"._CHECKFORIT."</font>";
        }
        CloseTable();
    }
    CloseTable();
    include 'footer.php';
}
?>