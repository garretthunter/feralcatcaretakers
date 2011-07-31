<?php
// File: $Id: wl-addlink.php,v 1.5 2002/11/10 23:30:09 skooter Exp $ $Name:  $
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
// 11-30-2001:ahumphr - created file as part of modularistation
// 10-15-2002:skooter      - Cross Site Scripting security fixes and also using 
//                           pnAPI for displaying data.
/**
 * AddLink
 */
function AddLink()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");
    $mainlink = 1;
    menu(1);

    OpenTable();
	$yn = $ye = "";
    if (pnUserLoggedIn()) {
		$uid = pnUserGetVar('uid');
        $column = &$pntable['users_column'];
        $result=$dbconn->Execute("select $column[name], $column[email] from $pntable[users] WHERE $column[uid]='".pnVarPrepForStore($uid)."'");
        list($yn, $ye) = $result->fields;
    }

    echo "<center><font class=\"pn-title\">"._ADDALINK."</font></center><br /><br />";
    if (pnSecAuthAction(0, 'Web Links::', "::", ACCESS_COMMENT)) {
        echo "<font class=\"pn-normal\"><b>"._INSTRUCTIONS.":</b><br />"
        ."<strong><big>&middot;</big></strong> "._SUBMITONCE."<br />"
        ."<strong><big>&middot;</big></strong> "._POSTPENDING."<br />"
        ."<strong><big>&middot;</big></strong> "._USERANDIP."<br /></font>"
        ."<form method=\"post\" action=\"".$GLOBALS['modurl']."\">"
        ."<font class=\"pn-normal\">"
        .""._PAGETITLE.": <input type=\"text\" name=\"title\" size=\"50\" maxlength=\"100\"><br />"
        .""._PAGEURL.": <input type=\"text\" name=\"url\" size=\"75\" maxlength=\"254\" value=\"http://\"><br />";
        echo ""._CATEGORY.": <select name=\"cat\">";
        echo CatList(0, 0);
        echo "</select><br /><br />"
            .""._LDESCRIPTION."<br><textarea name=\"description\" cols=\"60\" rows=\"5\"></textarea><br /><br /><br />"
            .""._YOURNAME.": <input type=\"text\" name=\"nname\" size=\"30\" maxlength=\"60\" value=\"$yn\"><br />"
            .""._YOUREMAIL.": <input type=\"text\" name=\"email\" size=\"30\" maxlength=\"60\" value=\"$ye\"><br /><br />"
            ."<input type=\"hidden\" name=\"req\" value=\"Add\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"".pnSecGenAuthKey()."\">"
            ."<input type=\"submit\" value=\""._ADDURL."\"> "._GOBACK."<br /><br />"
            ."</font></form>";
    }else {
        echo "<font class=\"pn-normal\"><center>"._LINKSNOTUSER1."<br />"
        .""._LINKSNOTUSER2."<br /><br />"
            .""._LINKSNOTUSER3."<br />"
            .""._LINKSNOTUSER4."<br />"
            .""._LINKSNOTUSER5."<br />"
            .""._LINKSNOTUSER6."<br />"
            .""._LINKSNOTUSER7."<br /><br />"
            .""._LINKSNOTUSER8."</font>";
    }
    CloseTable();
    include("footer.php");
}

/**
 * Add
 */
function Add()
{
    list($title,
         $url,
         $nname,
         $cat,
         $description,
         $email) = pnVarCleanFromInput('title',
                                       'url',
                                       'nname',
                                       'cat',
                                       'description',
                                       'email');

    if (!isset($cat) || !is_numeric($cat)){
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[url]='".pnVarPrepForStore($url)."'");
    list($numrows) = $result->fields;

    if ($numrows>0) {
        include("header.php");
        menu(1);

        OpenTable();
        echo "<font class=\"pn-normal\"><center><b>"._LINKALREADYEXT."</b><br /><br />"
            ._GOBACK."</font>";
        CloseTable();
        include("footer.php");
    } else {
        if (pnUserLoggedIn()) {
            $submitter = pnUserGetVar('uname');
    }

// Check if Title exist
    if ($title=="") {
    include("header.php");
    menu(1);

    OpenTable();
    echo "<font class=\"pn-normal\"><center><b>"._LINKNOTITLE."</b><br /><br />"
        .""._GOBACK."</font>";
    CloseTable();
    include("footer.php");
    exit;
    }

// Check if URL exist
	$valid = pnVarValidate($url, 'url');
    if ($valid == false) {
    include("header.php");
    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._LINKNOURL."</b><br /><br />"
        .""._GOBACK."</font>";
    CloseTable();
    include("footer.php");
    exit;
    }

// Check if Category exists
    if ($cat=="") {
    include("header.php");
    menu(1);

    OpenTable();
    echo '<font class="pn-normal"><center><b>'._LINKNOCAT.'</b><br /><br />'
        ._GOBACK.'</font>';
    CloseTable();
    include("footer.php");
    exit;
}

// Check if Description exist
    if ($description=="") {
    include("header.php");
    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._LINKNODESC."</b><br /><br />"
        .""._GOBACK."</font>";
    CloseTable();
    include("footer.php");
    exit;
    }

    $column = &$pntable['links_newlink_column'];
    $nextid = $dbconn->GenId($pntable['links_newlink']);
    $dbconn->Execute("INSERT INTO $pntable[links_newlink] ($column[lid], $column[cat_id], $column[title], $column[url], $column[description], $column[name], $column[email], $column[submitter]) VALUES ($nextid, ".(int)pnVarPrepForStore($cat).", '".pnVarPrepForStore($title)."', '".pnVarPrepForStore($url)."', '".pnVarPrepForStore($description)."', '".pnVarPrepForStore($nname)."', '".pnVarPrepForStore($email)."', '".pnVarPrepForStore($submitter)."')");
    include("header.php");
    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._LINKRECEIVED."</b><br />";
    if ($email != "") {
        echo _EMAILWHENADD;
    } else {
        echo _CHECKFORIT;
    }
    CloseTable();
        include("footer.php");
    }
}

?>