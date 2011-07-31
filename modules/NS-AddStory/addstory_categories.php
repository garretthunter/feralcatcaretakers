<?php
// File: $Id: addstory_categories.php,v 1.5 2002/11/17 15:28:17 larsneo Exp $
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
// Original Author of file:
// Purpose of file: extract category functions to make them generic
// ----------------------------------------------------------------------

// Notes by AV
// OK, the idea is to make 'Categories' manager a bit more intuitive to work with:
// 1. Every EDIT and PREVIEW story inherits the 'Category' chosen during story ADD
// 2. Every 'Category' EDIT and DELETE inherits previously chosen one
// 3. Every 'Category' modification menu contains and/or ends up with further logical actions

// Security Changes and removed Globals - Skooter.

if (!eregi("admin.php", $PHP_SELF)) { die ("Access Denied"); }

function _admin_cat_theme_list($selectname, $defaulttheme)
{
    $r = "<select name=\"${selectname}\">";

    $r .= "<option value=\"\"";
    if ($defaulttheme == "") {
        $r .= " selected";
    }
    $r .= ">"._CATOVERRIDENONE."</option>";

    $handle = opendir('themes');
    $themelist = array();
    while ($file = readdir($handle)) {
        if ((!ereg("[.]", $file))) {
            $themelist[] = $file;
        }
    }
    closedir($handle);

    sort($themelist);
    for ($i = 0; $i < sizeof($themelist); ++$i) {
        if (($themelist[$i] != "") && ($themelist[$i] != "CVS")) {
            $r .= "<option value=\"${themelist[$i]}\"";
            if ($defaulttheme == $themelist[$i]) {
                $r .= " selected";
            }
            $r .= ">${themelist[$i]}</option>";
        }
    }

    $r .= "</select>";

    return ($r);
}

function AddCategory()
{
    $module = pnVarCleanFromInput('module');

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";

    if (!pnSecAuthAction(0, 'Stories::Category', '::', ACCESS_ADD)) {
        echo _STORIESADDCATNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORYADD."</b></font><br>"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<table border=\"3\" cellpadding=\"4\"><tr><td>"
        ."<b>"._CATNAME.":</b></td><td>"
        ."<input type=\"text\" name=\"title\" size=\"22\" maxlength=\"40\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"SaveCategory\"></td></tr><tr><td>"
        ."<b>"._CATOVERRIDE.":</b></td><td> " . _admin_cat_theme_list("themeoverride", "").""
        ."</td></tr><tr><td>&nbsp;</td><td><input type=\"submit\" value=\""._SAVE."\"></td></tr>"
        ."</table></form></center>";
    CloseTable();

    include ("footer.php");
}

function EditCategory($catid)
{
    $module = pnVarCleanFromInput('module');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $catid += 0;
    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute( "SELECT $column[title], $column[themeoverride]
                               FROM $pntable[stories_cat]
                               WHERE $column[catid] = '$catid'");

    list($title, $themeoverride) = $result->fields;

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::$catid", ACCESS_EDIT)) {
        echo _STORIESEDITCATNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();

    echo "<center><font class=\"pn-title\"><b>"._EDITCATEGORY."</b></font><br>";
    if (!$catid) {
        $column = &$pntable['stories_cat_column'];
        $selcat = $dbconn->Execute("SELECT $column[catid], $column[title]
                                  FROM $pntable[stories_cat]");
        echo "<form action=\"admin.php\" method=\"post\">";
        echo "<table border=\"3\" cellpadding=\"4\"><tr><td>";
        echo "<b>"._ASELECTCATEGORY."</b></td><td>";
        echo "<select name=\"catid\">";
//        echo "<option name=\"catid\" value=\"0\" $selcat>Articles</option>";

        while(list($catid, $title) = $selcat->fields) {
            echo "<option name=\"catid\" value=\"$catid\" $selcat>".pnVarPrepForDisplay($title)."</option>";
            $selcat->MoveNext();
        }
        echo "</select>"
             ."</td></tr><tr><td>&nbsp;</td><td>"
             ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
             ."<input type=\"hidden\" name=\"op\" value=\"EditCategory\">"
             ."<input type=\"submit\" value=\""._EDIT."\">"
             . "</td></tr></table><br>"
             ._NOARTCATEDIT.""
             ."</form>";
	} else {
        echo "<form action=\"admin.php\" method=\"post\">"
             ."<table border=\"3\" cellpadding=\"4\"><tr><td>"
             ."<b>"._CATEGORYNAME.":</b></td><td>"
             ."<input type=\"text\" name=\"title\" size=\"22\" maxlength=\"40\" value=\"$title\">"
             ."</td></tr><tr><td><b>"._CATOVERRIDE.":</b></td><td>"
             ."" . _admin_cat_theme_list('themeoverride', $themeoverride)
             ."<input type=\"hidden\" name=\"catid\" value=\"$catid\">"
             ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
             ."<input type=\"hidden\" name=\"op\" value=\"SaveEditCategory\">"
             ."</td></tr><tr><td>&nbsp;</td><td>"
             ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
             ."</td></tr></table><br>"
             .""._NOARTCATEDIT.""
             ."</form>";
    }
    echo "</center>";

    CloseTable();
    include("footer.php");
}

// we need $catid param to be able to carry on from previous op
function DelCategory($cat, $catid)
{
    $module = pnVarCleanFromInput('module');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";

    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid]='$cat'");

	list($title) = $result->fields;

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::$cat", ACCESS_DELETE)) {
        echo _STORIESDELCATNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._DELETECATEGORY."</b></font><br>";

    if (empty($catid)) {

		$column = &$pntable['stories_cat_column'];
		$thiscat = $dbconn->Execute( "SELECT $column[catid]
								   FROM $pntable[stories_cat]
								   WHERE $column[catid] = '$cat'");
		list($thiscat) = $thiscat->fields;

		$column = &$pntable['stories_cat_column'];
		$selcat = $dbconn->Execute("SELECT $column[catid], $column[title]
								  FROM $pntable[stories_cat]");

		echo "<form action=\"admin.php\" method=\"post\">"
			."<table border=\"3\" cellpadding=\"4\"><tr><td>"
			."<b>"._SELECTCATDEL.": </b>"
			."</td><td>"
			."<select name=\"catid\">";

		while(list($catid, $title) = $selcat->fields) {

			if ($catid == $thiscat) {
				$sel = "selected ";
			} else {
				$sel = "";
			}
				echo "<option name=\"catid\" value=\"$catid\" $sel>".pnVarPrepForDisplay($title)."</option>";

			$selcat->MoveNext();
		}
		echo "</select>"
			."</td></tr><tr><td>&nbsp;</td><td>"
			."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
			."<input type=\"hidden\" name=\"op\" value=\"DelCategory\">"
			."<input type=\"submit\" value=\"Delete\">"
			."</td></tr></table><br>"
			."</form>";
    } else {

        /* Get a quick count of the rows - Wandrer */
        $column = &$pntable['stories_column'];
        $result2 = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[stories]
                                   WHERE $column[catid]='$catid'");

        list($numrows) = $result2->fields;

        if ($numrows == 0) {
            $temp= &$pntable['stories_cat_column']['catid']; // TRICKY BIT - may need to be changed
            $result = $dbconn->Execute("DELETE FROM $pntable[stories_cat]
                                      WHERE ${temp}='$catid'");
            if ($result === false) {
                error_log("stories->DelCategory: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->DelCategory: Error accesing to the database");
            }
            echo "<br><br>"._CATDELETED."<br><br>[ <a href=\"admin.php\">"._GOTOADMIN."</a> ]";
        } else {
            echo "<br><br><b>"._WARNING.":</b> "._THECATEGORY." <b>".pnVarPrepForDisplay($title)."</b> "._HAS." <b>$numrows</b> "._STORIESINSIDE."<br>"
            .""._DELCATWARNING1."<br>"
            .""._DELCATWARNING2."<br><br>"
            .""._DELCATWARNING3."<br><br>"
            ."<b>[ <a href=\"admin.php?module=$module&amp;op=YesDelCategory&amp;catid=$catid\">"._YESDEL."</a> | "
            ."<a href=\"admin.php?module=$module&amp;op=NoMoveCategory&amp;catid=$catid\">"._NOMOVE."</a> ]</b>"
            ."<br><br>"._GOBACK."";
        }
    }
    echo "</center>";
    CloseTable();
    include("footer.php");
}

function YesDelCategory($catid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid]='$catid'");

    list($title) = $result->fields;

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::$cat", ACCESS_DELETE)) {
        echo _STORIESDELCATNOAUTH;
        include 'footer.php';
        return;
    }
	$temp = &$pntable['stories_cat_column']['catid'];
    $result = $dbconn->Execute("DELETE FROM $pntable[stories_cat]
                              WHERE ${temp}='$catid'");
    if ($result === false) {
        error_log("stories->YesDelCategory: " . $dbconn->ErrorMsg());
        PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->YesDelCategory: Error accesing to the database");
    }

    $column = &$pntable['stories_column'];
    $result = $dbconn->Execute("SELECT $column[sid]
                              FROM $pntable[stories]
                              WHERE $column[catid]='$catid'");

    while(list($sid) = $result->fields) {
        $results = $dbconn->Execute("DELETE FROM $pntable[stories]
                                  WHERE $column[catid]='$catid'");
        if ($results === false) {
            error_log("stories->YesDelCategory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->YesDelCategory: Error accesing to the database");
        }
        $temp2 = &$pntable['comments_column']['sid'];
        $resultc = $dbconn->Execute("DELETE FROM $pntable[comments]
                                  WHERE ${temp2}='$sid'");
        if ($resultc === false) {
            error_log("stories->YesDelCategory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->YesDelCategory: Error accesing to the database");
        }
        $result->MoveNext();
    }
    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";
	OpenTable();
	echo "<center><font class=\"pn-title\"><b>"._DELETECATEGORY."</b></font><br>";
	echo "<br>"._CATDELETED."<br><br>";
	add_edit_del_category($cat);
	echo "<br><br>[ <a href=\"admin.php\">"._GOTOADMIN."</a> ]";
	echo "</center>";
	CloseTable();
	include("footer.php");
 //   pnRedirect('admin.php');
}

function NoMoveCategory($catid, $newcat)
{
    $module = pnVarCleanFromInput('module');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[stories_cat]
                              WHERE $column[catid]='$catid'");

    list($title) = $result->fields;
    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::$catid", ACCESS_DELETE)) {
        echo _STORIESMOVECATNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._MOVESTORIES."</b></font><br><br>";
    if (!$newcat) {
        echo ""._ALLSTORIES." <b>".pnVarPrepForDisplay($title)."</b> "._WILLBEMOVED."<br><br>";
        $column = &$pntable['stories_cat_column'];
        $selcat = $dbconn->Execute("SELECT $column[catid], $column[title]
                                  FROM $pntable[stories_cat]");
        echo "<form action=\"admin.php\" method=\"post\">";
        echo "<b>"._SELECTNEWCAT.":</b> ";
        echo "<select name=\"newcat\">";
 //       echo "<option name=\"newcat\" value=\"0\">"._ARTICLES."</option>";

        while(list($newcat, $title) = $selcat->fields) {
            if (pnSecAuthAction(0, 'Stories::Story', ":$title:", ACCESS_ADD))
            	// lets skip the cat which is going to be deleted
            	if($newcat != $catid) echo "<option name=\"newcat\" value=\"$newcat\">".pnVarPrepForDisplay($title)."</option>";
            $selcat->MoveNext();
        }
        echo "</select>"
			."<input type=\"hidden\" name=\"module\" value=\"".$module."\">";
        echo "<input type=\"hidden\" name=\"catid\" value=\"$catid\">";
        echo "<input type=\"hidden\" name=\"op\" value=\"NoMoveCategory\">";
        echo "<input type=\"submit\" value=\""._OK."\">";
        echo "</form>";
    } else {
        $column = &$pntable['stories_column'];
        $resultm = $dbconn->Execute("SELECT $column[sid]
                                   FROM $pntable[stories]
                                   WHERE $column[catid]='$catid'");

        while(list($sid) = $resultm->fields) {
            $column = &$pntable['stories_column'];
            $result = $dbconn->Execute("UPDATE $pntable[stories]
                                      SET $column[catid]='$newcat'
                                      WHERE $column[sid]='$sid'");
            if ($result === false) {
                error_log("stories->NoMoveCategoryt: " . $dbconn->ErrorMsg());
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->NoMoveCategoryt: Error accesing to the database");
            }
            $resultm->MoveNext();
        }
        $temp = &$pntable['stories_cat_column']['catid'];
        $result = $dbconn->Execute("DELETE FROM $pntable[stories_cat]
                                  WHERE ${temp}='$catid'");
        if ($result === false) {
            error_log("stories->NoMoveCategory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->NoMoveCategory: Error accesing to the database");
        }
        echo ""._MOVEDONE."";
        echo "<br><br>";
        add_edit_del_category($cat);
        echo "<br><br>[ <a href=\"admin.php\">"._GOTOADMIN."</a> ]";
    }
    CloseTable();
    include("footer.php");
}

function SaveEditCategory($catid, $title, $themeoverride)
{
    list($title,
          $themeoverride) = pnVarCleanFromInput('title',
                                      'themeoverride');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::$catid", ACCESS_EDIT)) {
        include 'header.php';
        echo _STORIESEDITCATNOAUTH;
        include 'footer.php';
        return;
    }

    $catid += 0;

    $column = &$pntable['stories_cat_column'];
    $check = $dbconn->Execute("SELECT $column[catid]
                             FROM $pntable[stories_cat]
                             WHERE $column[title] = '".pnVarPrepForStore($title)."'
                               AND $column[themeoverride] = '".pnVarPrepForStore($themeoverride)."'");
    if (!$check->EOF) {
        $what1 = _CATEXISTS;
        $what2 = _GOBACK;
    } else {
        $what1 = _CATSAVED;
        $what2 = "[ <a href=\"admin.php\">"._GOTOADMIN."</a> ]";
        $column = &$pntable['stories_cat_column'];
        $result = $dbconn->Execute("UPDATE $pntable[stories_cat]
                                  SET $column[title] = '".pnVarPrepForStore($title)."',
                                    $column[themeoverride] = '".pnVarPrepForStore($themeoverride)."'
                                  WHERE $column[catid]='".pnVarPrepForStore($catid)."'");
        if ($result === false) {
            error_log("stories->SaveEditCategory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->SaveEditCategory: Error accesing to the database");
        }
    }

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>$what1</b></font><br><br>";
    echo "$what2</center>";
    CloseTable();
    include ("footer.php");
}

function SaveCategory($title, $themeoverride)
{
    list($title,$themeoverride) = pnVarCleanFromInput('title','themeoverride');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Stories::Category', "$title::", ACCESS_ADD)) {
        include 'header.php';
        echo _STORIESADDCATNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['stories_cat_column'];
    $check = $dbconn->Execute("SELECT $column[catid]
                             FROM $pntable[stories_cat]
                             WHERE $column[title]='".pnVarPrepForStore($title)."'");
    if (!$check->EOF) {
        $what1 = _CATEXISTS;
        $what2 = _GOBACK;
    } else {
        $what1 = _CATADDED;
        $what2 = "[ <a href=\"admin.php\">"._GOTOADMIN."</a> ]";
        $column = &$pntable['stories_cat_column'];
        $nextid = $dbconn->GenId($pntable['stories_cat']);
        $result = $dbconn->Execute("INSERT INTO $pntable[stories_cat]
                                    ($column[catid], $column[title], $column[counter],
                                     $column[themeoverride])
                                  VALUES ($nextid, '".pnVarPrepForStore($title)."', '0', '${themeoverride}')");
        if ($result === false) {
            error_log("stories->SaveCategory: " . $dbconn->ErrorMsg());
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "stories->SaveCategory: Error accesing to the database");
        }
    }

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._CATEGORIESADMIN."</b></font></center>";
    CloseTable();
    echo "<br>";
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>$what1</b></font><br><br>";
    if(!isset($cat)) {
		$cat = '';
    }
    add_edit_del_category($cat);
    echo "<br><br>$what2</center>";
    CloseTable();
    include ("footer.php");
}

function SelectCategory($cat)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['stories_cat_column'];
    $selcat = $dbconn->Execute("SELECT $column[catid], $column[title]
                              FROM $pntable[stories_cat]");
    echo "<b>"._CATEGORY."</b> ";

    echo "<select name=\"catid\" class=\"pn-text\">";
    if ($cat == 0) {
        $sel = "selected";
    } else {
        $sel = "";
    }
    if (pnSecAuthAction(0, 'Stories::Story', ':' . _ARTICLES . ':', ACCESS_ADD)) {
        echo "<option name=\"catid\" value=\"0\" $sel>"._ARTICLES."</option>";
    }

    while(list($catid, $title) = $selcat->fields) {
        if ($catid == $cat) {
            $sel = "selected";
        } else {
            $sel = "";
        }
        if (pnSecAuthAction(0, 'Stories::Story', ":$title:", ACCESS_ADD)) {
            echo "<option name=\"catid\" value=\"$catid\" $sel>".pnVarPrepForDisplay($title)."</option>";
        }
        $selcat->MoveNext();
    }
    echo "</select>";

    add_edit_del_category($cat);

}

// below are helper API function for this module
// probably they will be made redundant in later releases (AV)

function add_edit_del_category($cat)
{

    $module = pnVarCleanFromInput('module');

    if (pnSecAuthAction(0, 'Stories::Category', '::', ACCESS_DELETE)) {
        echo ' [ <a href="admin.php?module='.$module.'&amp;op=AddCategory">'._ADD.'</a>';
        echo ' | <a href="admin.php?module='.$module.'&amp;op=EditCategory&amp;catid='.$cat.'">'._EDIT.'</a>';
        echo ' | <a href="admin.php?module='.$module.'&amp;op=DelCategory&amp;catid='.$cat.'">'._DELETE.'</a> ]';
    } elseif (pnSecAuthAction(0, 'Stories::Category', '::', ACCESS_ADD)) {
        echo ' [ <a href="admin.php?module='.$module.'&amp;op=AddCategory">'._ADD.'</a>';
        echo ' | <a href="admin.php?module='.$module.'&amp;op=EditCategory&amp;catid='.$cat.'">'._EDIT.'</a> ]';
    } elseif (pnSecAuthAction(0, 'Stories::Category', '::', ACCESS_EDIT)) {
        echo ' [ <a href="admin.php?module='.$module.'&amp;op=EditCategory&amp;catid='.$cat.'">'._EDIT.'</a> ]';
	}
}

?>