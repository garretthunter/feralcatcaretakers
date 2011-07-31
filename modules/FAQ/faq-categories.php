<?php
// File: $Id: faq-categories.php,v 1.2 2002/10/07 14:41:29 skooter Exp $ $Name:  $
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
// Purpose of file: category functions, to be merged with other cat functions
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

$ModName = $module;

function faq_admin_FaqCatNew()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ADDCATEGORY."</b></font></center><br>"
    ."<form action=\"admin.php\" method=\"post\">"
    ."<table border=\"0\" width=\"100%\"><tr><td>"
    .""._CATEGORY.":</td><td><input type=\"text\" name=\"categoryname\" size=\"30\"></td>"
    .'<tr><td>'._LANGUAGE.':</td><td>';
    lang_dropdown();

    echo "<td>"._PARENTC.": ";
    echo "<select name=parent_cat>";
    echo "<option value=0>"._NEWTOPCATEGORY."</option>";
    // fifers: is it just me or did we just do this query a bit ago?
    $column = &$pntable['faqcategories_column'];
    $result = $dbconn->Execute("SELECT $column[id_cat], $column[categories]
                              FROM $pntable[faqcategories]
                              WHERE $column[parent_id]=0
                              ORDER BY $column[id_cat]");
    while(list($id_cat, $categories) = $result->fields) {
        $result->MoveNext();

        echo "<option value=$id_cat>".pnVarPrepForDisplay($categories)."</option>";
    }
    echo "</select>";
    echo "</td>";

    echo "</tr></table>"
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"FaqCatAdd\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value="._SAVE.">"
    ."</form>";
    CloseTable();
}

function faq_admin_FaqCatEdit($var)
{
    list($id_cat) = pnVarCleanFromInput('id_cat');
    if (!isset($id_cat) || !is_numeric($id_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    include(WHERE_IS_PERSO."config.php");
    include ("header.php");
    GraphicAdmin();
    OpenTable();
      echo "<center><font class=\"pn-pagetitle\">"._FAQADMIN."</font></center>";
    CloseTable();

    $column = &$pntable['faqcategories_column'];
    $result = $dbconn->Execute("SELECT $column[categories], $column[flanguage]
                              FROM $pntable[faqcategories]
                              WHERE $column[id_cat]=".(int)pnVarPrepForStore($id_cat));

    list($categories,$flanguage) = $result->fields;

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._EDITCATEGORY."</b></font></center>"
    ."<form action=\"admin.php\" method=\"post\">"
    ."<input type=\"hidden\" name=\"id_cat\" value=\"" . pnVarPrepHTMLDisplay($id_cat) ."\">"
    ."<table border=\"0\" width=\"100%\"><tr><td>"
    .""._CATEGORY.":</td><td><input type=\"text\" name=\"categoryname\" size=\"31\" value=\"".pnVarPrepHTMLDisplay($categories)."\">"
    .'<tr><td>'._LANGUAGE.':</td><td>';

    lang_dropdown();

    echo "</td></tr></table>"
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"FaqCatSave\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value=\""._SAVE."\"> "._GOBACK.""
    ."</form>";
    CloseTable();
    include 'footer.php';
}

function faq_admin_FaqCatSave($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($id_cat,
         $alanguage,
         $categoryname) = pnVarCleanFromInput('id_cat',
                                        'alanguage',
                                        'categoryname');
    if (!isset($id_cat) || !is_numeric($id_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $categoryname = stripslashes(FixQuotes($categoryname));

    $column = &$pntable['faqcategories_column'];
    $dbconn->Execute("UPDATE $pntable[faqcategories] SET $column[categories]='".pnVarPrepForStore($categoryname)."',
                                                         $column[flanguage]='".$alanguage."'
                                                   WHERE $column[id_cat]=".(int)pnVarPrepForStore($id_cat));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=main');
}


function faq_admin_FaqCatAdd($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($parent_cat,
         $alanguage,
         $categoryname) = pnVarCleanFromInput('parent_cat',
                                        'alanguage',
                                        'categoryname');
    if (!isset($parent_cat) || !is_numeric($parent_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $categoryname = stripslashes(FixQuotes($categoryname));
    $column = &$pntable['faqcategories_column'];
    $newid = $dbconn->GenId($pntable['faqcategories']);
    $dbconn->Execute("INSERT INTO $pntable[faqcategories] 
                             ($column[id_cat], $column[parent_id],$column[categories], $column[flanguage] ) 
                      VALUES ($newid, ".(int)$parent_cat.", '".$categoryname."', '".$alanguage."')");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=main');
}

function faq_admin_FaqCatDel($var)
{
    $authid = pnVarCleanFromInput('authid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($ok,$id_cat) = pnVarCleanFromInput('ok','id_cat');
    if (!isset($id_cat) || !is_numeric($id_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if($ok==1) {
        $dbconn->Execute("DELETE FROM $pntable[faqcategories] WHERE {$pntable['faqcategories_column']['id_cat']}=".(int)pnVarPrepForStore($id_cat));
        $dbconn->Execute("DELETE FROM $pntable[faqanswer] WHERE {$pntable['faqanswer_column']['id_cat']}=".(int)pnVarPrepForStore($id_cat));

        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=main');
    } else {
        include 'header.php';

        $authid = pnSecGenAuthKey();

        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._FAQADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<br><center><b>"._FAQDELWARNING."</b><br><br>";
    		echo "[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatDel&amp;id_cat=".$var['id_cat']."&amp;ok=1&amp;authid=$authid\">"
    		._YES."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=main\">"._NO."</a> ]</center><br><br>";
    		CloseTable();
    		include 'footer.php';
    }
}
?>
