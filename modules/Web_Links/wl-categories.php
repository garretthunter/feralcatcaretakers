<?php
// File: $Id: wl-categories.php,v 1.4 2002/11/10 23:30:09 skooter Exp $ $Name:  $
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
// Purpose of file: function lib, routines used by many other functions
// ----------------------------------------------------------------------
// 10-15-2002:skooter      - Cross Site Scripting security fixes and also using 
//                           pnAPI for displaying data.

/**
 * CatList
 * Recursivly creates option tags for each sub category of $scat, selects category $sel
 * @usedby search, popular, addlink
 */
function CatList($scat, $sel) {

  if (!isset($scat) || !is_numeric($scat)){
      return _MODARGSERROR;
  }

  list($dbconn) = pnDBGetConn();
  $pntable = pnDBGetTables();

  $s="";
  $column = &$pntable['links_categories_column'];
  $result=$dbconn->Execute("SELECT $column[cat_id] FROM $pntable[links_categories] 
                        WHERE $column[parent_id]=".(int)pnVarPrepForStore($scat)."");
  while(list($cid)=$result->fields) {

      $result->MoveNext();
    if ($sel==$cid) {
      $selstr=" selected";
    } else {
      $selstr='';
    }
    $s.="<option value=\"$cid\"$selstr>".CatPath($cid,0,0,0)."</option>";
    $s.=CatList($cid, $sel);
  }
  return $s;
}

/**
 * Catpath
 * Creates the full path for a category title
 * New function by toph, 20/8/2001
 * @usedby search, popular, addlink
 */
function CatPath($cid, $start, $links, $linkmyself) {

    if (!isset($cid) || !is_numeric($cid)){
        return _MODARGSERROR;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['links_categories_column'];
    $result=$dbconn->Execute("SELECT $column[parent_id], $column[title] FROM $pntable[links_categories] 
                        WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid)."");
    list($pid, $title)=$result->fields;
    if ($linkmyself) {
        $cpath = "<a href=\"".$GLOBALS['modurl']."&req=viewlink&amp;cid=$cid\"> ".pnVarPrepForDisplay($title)." </a>";
    } else {
        $cpath = pnVarPrepForDisplay($title);
    }
    while ($pid!=0) {
        $column = &$pntable['links_categories_column'];
        $result=$dbconn->Execute("SELECT $column[cat_id], $column[parent_id], $column[title] 
                        FROM $pntable[links_categories] 
                        WHERE $column[cat_id]=".pnVarPrepForStore($pid)."");
        list($cid, $pid, $title)=$result->fields;
        if ($links) {
            $cpath = "<a href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=$cid\"> ".pnVarPrepForDisplay($title)."</a> / $cpath";
        } else {
            $cpath = pnVarPrepForDisplay($title)." / $cpath";
        }
    }
    if ($start) {
      $cpath="<a href=\"".$GLOBALS['modurl']."\">"._START."</a> / $cpath";
    }
    return $cpath;
}

function LinksNewCat() {

    if (pnSecAuthAction(0, 'Web Links::Category', '::', ACCESS_ADD)) {
       OpenTable();
       echo "<form method=\"post\" action=\"admin.php\">"
            ."<font class=\"pn-title\"><b>"._ADDCATEGORY."</b></font><br /><br />"
            .""._NAME.": <input type=\"text\" name=\"title\" size=\"30\" maxlength=\"100\">&nbsp;"._INCAT
            ."<select name=\"cid\">"
            ."<option value=\"0\">"._NONE
            .CatList(0,0)."</select>
            <br /><br />"
            .""._WL_DESCRIPTION.":<br /><textarea name=\"cdescription\" cols=\"60\" rows=\"10\"></textarea><br />"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"LinksAddCat\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._ADD."\"><br />"
            ."</form>";
       CloseTable();
    }
}

function LinksAddCat()
{
    list($cid,
         $title,
         $cdescription) = pnVarCleanFromInput('cid',
                                              'title',
                                              'cdescription');
    if (!pnSecConfirmAuthKey()) {
        echo _BADAUTHKEY;
        include 'footer.php';
        CloseTable();
    }

    if (!isset($cid) || !is_numeric($cid)){
        echo _MODARGSERROR;
        CloseTable();
        include 'footer.php';
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Web Links::Category', "$title::", ACCESS_ADD)) {
        echo _WEBLINKSCATADDNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_categories_column'];

    $result = $dbconn->Execute("SELECT $column[cat_id] FROM $pntable[links_categories] 
                        WHERE $column[title]='$title' 
                        AND $column[parent_id]=".(int)pnVarPrepForStore($cid)."");

    if (!$result->EOF) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<br /><center><font class=\"pn-title\">"
            ."<b>"._ERRORTHECATEGORY." ".pnVarPrepForDisplay($title)." "._ALREADYEXIST."</b><br /><br />"
            .""._GOBACK."<br /><br />";
        CloseTable();
        include 'footer.php';
        return;
    }
    $column = &$pntable['links_categories_column'];
    $nextid = $dbconn->GenId($pntable['links_categories']);
    $dbconn->Execute("INSERT INTO $pntable[links_categories] ($column[cat_id],
    $column[parent_id], $column[title], $column[cdescription]) VALUES ($nextid, ".(int)pnVarPrepForStore($cid).", '".pnVarPrepForStore($title)."', '".pnVarPrepForStore($cdescription)."')");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function LinksEditCat()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (pnSecAuthAction(0, 'Web Links::Category', '::', ACCESS_EDIT)) {
        $result = $dbconn->Execute("SELECT count(*) FROM $pntable[links_categories]");
        list($numrows) = $result->fields;
        if ($numrows>0) {
            OpenTable();
            echo "<form method=\"post\" action=\"admin.php\">"
                ."<font class=\"pn-title\"><b>"._MODCATEGORY."</b></font><br /><br />";
            echo ""._CATEGORY.": <select name=\"cid\">".CatList(0,0);
            echo "</select>"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"op\" value=\"LinksModCat\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"submit\" value=\""._MODIFY."\">"
                ."</form>";
            CloseTable();
        }
    }
}

function LinksModCat()
{
    $cid = pnVarCleanFromInput('cid');

    include 'header.php';

    if (!isset($cid) || !is_numeric($cid)){
        echo _MODARGSERROR;
        include 'footer.php';
        CloseTable();
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._MODCATEGORY."</b></font></center><br /><br />";

    $column = &$pntable['links_categories_column'];
    $result=$dbconn->Execute("SELECT $column[title], $column[cdescription] FROM $pntable[links_categories] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));

    list($title,$cdescription) = $result->fields;

    if (!pnSecAuthAction(0, 'Web Links::Category', "$title::$cid", ACCESS_EDIT)) {
        echo _WEBLINKSCATEDITNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    echo "<form action=\"admin.php\" method=\"post\">"
        .""._NAME.": <input type=\"text\" name=\"title\" value=\"".pnVarPrepForDisplay($title)."\" size=\"51\" maxlength=\"50\"><br />"
        .""._WL_DESCRIPTION.":<br><textarea name=\"cdescription\" cols=\"60\" rows=\"10\">".pnVarPrepForDisplay($cdescription)."</textarea><br />"
        ."<input type=\"hidden\" name=\"sub\" value=\"0\">"
        ."<input type=\"hidden\" name=\"cid\" value=\"".pnVarPrepForDisplay($cid)."\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".pnVarPrepForDisplay($GLOBALS['module'])."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"LinksModCatS\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<table border=\"0\"><tr><td>"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\"></form></td><td>"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"sub\" value=\"0\">"
        ."<input type=\"hidden\" name=\"ok\" value=\"0\">"
        ."<input type=\"hidden\" name=\"cid\" value=\"".pnVarPrepForDisplay($cid)."\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".pnVarPrepForDisplay($GLOBALS['module'])."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"LinksDelCat\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._DELETE."\"></form></td></tr></table>";
    CloseTable();
    include 'footer.php';
}

function LinksModCatS()
{
    list($cid,
         $sub,
         $title,
         $cdescription) = pnVarCleanFromInput('cid',
                                              'sub',
                                              'title',
                                              'cdescription');
    if (!isset($cid) || !is_numeric($cid)){
        echo _MODARGSERROR;
        CloseTable();
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $catcolumn[title]
                              FROM $cattable
                              WHERE $catcolumn[cat_id] = ".(int)pnVarPrepForStore($cid));

    list($oldtitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Category', "$oldtitle::$cid", ACCESS_EDIT)) {
        echo _WEBLINKSCATEDITNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    if (!pnSecConfirmAuthKey()) {
        echo _BADAUTHKEY;
        CloseTable();
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_categories_column'];
    $dbconn->Execute("UPDATE $pntable[links_categories] SET $column[title]='".pnVarPrepForStore($title)."',
                        $column[cdescription]='".pnVarPrepForStore($cdescription)."' WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function LinksDelCat()
{
    list($cid,
         $sub,
         $ok) = pnVarCleanFromInput('cid',
                                    'sub',
                                    'ok');

    if (!isset($cid) || !is_numeric($cid)){
        echo _MODARGSERROR;
        CloseTable();
        include 'footer.php';
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $catcolumn[title]
                              FROM $cattable
                              WHERE $catcolumn[cat_id] = ".(int)pnVarPrepForStore($cid));

    list($oldtitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Category', "$oldtitle::$cid", ACCESS_DELETE)) {
        echo _WEBLINKSCATDELNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    if (!pnSecConfirmAuthKey()) {
        echo _BADAUTHKEY;
        CloseTable();
        include 'footer.php';
        return;
    }

    if(!empty($ok)) {
        $column = &$pntable['links_categories_column'];
        $dbconn->Execute("DELETE FROM $pntable[links_categories] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));
        $column = &$pntable['links_links_column'];
        $dbconn->Execute("DELETE FROM $pntable[links_links] 
                            WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid)."");
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
    } else {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<br /><center><font class=\"pn-title\">";
        echo "<b>"._DELCATWARNING."</b><br /><br />";
    }
    echo "<form action=\"admin.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"LinksDelCat\">"
        ."<input type=\"hidden\" name=\"cid\" value=\"$cid\">"
        ."<input type=\"hidden\" name=\"sub\" value=\"$sub\">"
        ."<input type=\"hidden\" name=\"ok\" value=\"1\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._YES."\">"    
        ."&nbsp;&nbsp;&nbsp;| <a href=\"admin.php?module=".$GLOBALS['module']."&op=links\">"._NO."</a><br /><br />";
    CloseTable();
    include 'footer.php';
}
?>