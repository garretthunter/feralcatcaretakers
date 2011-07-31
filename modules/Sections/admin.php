<?php
// File: $Id: admin.php,v 1.8 2002/12/09 10:30:54 larsneo Exp $ $Name:  $
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

if(!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

modules_get_language();
modules_get_manual();

/**
 * Sections Manager Functions
 */

function sections()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    $admart = pnConfigGetVar('admart');

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._SECTIONSADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secid], $column[secname]
                              FROM $pntable[sections] ORDER BY $column[secid]");
    if (!$result->EOF) {
        // Current sections
        if (pnSecAuthAction(0, 'Sections::Section', ':', ACCESS_EDIT)) {
            OpenTable();
            echo "<center><b>"._ACTIVESECTIONS."</b><br><font class=\"pn-normal\">"._CLICK2EDITSEC."</font></center><br>"
                ."<table border=0 width=100% align=center cellpadding=1 align=\"center\"><tr><td align=center>";

            while(list($secid, $secname) = $result->fields) {
                if (pnSecAuthAction(0, 'Sections::Section', "$secname:$secid", ACCESS_EDIT)) {
                    echo "<strong><big>&middot;</big></strong>&nbsp;&nbsp;<a href=\"admin.php?module=$GLOBALS[ModName]&op=sectionedit&amp;secid=$secid\">".pnVarPrepForDisplay($secname)."</a><br />";
                }
                $result->MoveNext();
            }
            echo "</td></tr></table>";
            CloseTable();
        }

        // Add article
        if (pnSecAuthAction(0, 'Sections::Article', ":$secname:", ACCESS_ADD)) {

            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._ADDSECARTICLE."</b></font></center><br>"
                ."<form action=\"admin.php\" method=\"post\">"
                ."<b>"._TITLE."</b><br>"
                ."<input type=\"text\" name=\"title\" size=\"60\"><br><br>"
                ."<b>"._SELSECTION.":</b><br>";
            $column = &$pntable['sections_column'];
            $result = $dbconn->Execute("SELECT $column[secid], $column[secname]
                                      FROM $pntable[sections] ORDER BY $column[secid]");

            while(list($secid, $secname) = $result->fields) {
                if (pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_ADD)) {
                    echo "<input type=\"radio\" name=\"secid\" value=\"$secid\"> ".pnVarPrepForDisplay($secname)."<br>";
                }
                $result->MoveNext();
            }
            echo "<font class=\"pn-normal\">"._DONTSELECT."</font><br>";
            echo "<br><br><b>"._LANGUAGE.": </b>"
                    ."<select name=\"slanguage\" class=\"pn-text\">";

            $lang = languagelist();
            $sel_lang[$currentlang] = ' selected';
            print '<option value="">'._ALL.'</option>';
            $handle = opendir('language');
            while ($f = readdir($handle))
            {
                if (is_dir("language/$f") && (!empty($lang[$f])))
                {
                    $langlist[$f] = $lang[$f];
                }
            }
            asort($langlist);
                        //  a bit ugly, but it works in E_ALL conditions (Andy Varganov)
                        foreach ($langlist as $k=>$v){
                        echo '<option value="'.$k.'"';
                        if (isset($sel_lang[$k])) echo ' selected';
                        echo '>'. $v . '</option>\n';
                        }
                        echo '</select>';
                        echo "<br><br><b>"._CONTENT."</b><br>"
                ."<textarea name=\"content\" cols=\"60\" rows=\"10\"></textarea><br>"
                ."<font class=\"pn-normal\">"._PAGEBREAK."</font><br><br>"
                ."<input type=\"hidden\" name=\"op\" value=\"secarticleadd\">"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"submit\" value=\""._ADDARTICLE."\">"
                ."</form>";
            CloseTable();
        }

        // Show current articles
        if (pnSecAuthAction(0, 'Sections::Article', '::', ACCESS_EDIT)) {
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._LAST." ".pnVarPrepForDisplay($admart)." "._ARTICLES."</b></font></center><br>"
                ."<ul>";       /* ML added slanguage for display */
            $column = &$pntable['seccont_column'];
            $query = buildSimpleQuery ('seccont', array ('artid', 'secid', 'title', 'content', 'slanguage' ), '', "$column[artid] DESC", $admart);
            $result = $dbconn->Execute($query);

            while(list($artid, $secid, $title, $content, $slanguage) = $result->fields) {
                $column = &$pntable['sections_column'];
                $result2 = $dbconn->Execute("SELECT $column[secid], $column[secname]
                                           FROM $pntable[sections]
                                           WHERE $column[secid]='".pnVarPrepForStore($secid)."'");

                list($secid, $secname) = $result2->fields;
                if (pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_EDIT)) {
                    echo "<li>$title - ($slanguage) - ($secname) [ <a href=\"admin.php?module=$GLOBALS[ModName]&op=secartedit&artid=$artid&authid=".pnSecGenAuthKey()."\">"._EDIT."</a> ";
                    if (pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_DELETE)) {
                        echo "| <a href=\"admin.php?module=$GLOBALS[ModName]&op=secartdelete&artid=$artid&ok=0\">"._DELETE."</a> ";
                    }
                    echo "]";
                }
                $result->MoveNext();
            }
            echo "</ul>"
                ."<form action=\"admin.php\" method=\"post\">"
                .""._EDITARTID.": <input type=\"text\" name=\"artid\" size=\"10\">&nbsp;&nbsp;"
                ."<input type=\"hidden\" name=\"op\" value=\"secartedit\">"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"submit\" value=\""._OK."\">"
                ."</form>";
            CloseTable();
        }
    }

    // Add section
    if (pnSecAuthAction(0, 'Sections::Section', "::", ACCESS_ADD)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ADDSECTION."</b></font></center><br>"
            ."<form action=\"admin.php\" method=\"post\"><br>"
            ."<b>"._SECTIONNAME.":</b><br>"
            ."<input type=\"text\" name=\"secname\" size=\"40\" maxlength=\"40\"><br><br>"
            ."<b>"._SECTIONIMG."</b><br><font class=\"pn-sub\">"._SECIMGEXAMPLE."</font><br>"
            ."<input type=\"text\" name=\"image\" size=\"40\" maxlength=\"50\"><br><br>"
            ."<input type=\"hidden\" name=\"op\" value=\"sectionmake\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<INPUT type=\"submit\" value=\""._ADDSECTIONBUT."\">"
            ."</form>";
        CloseTable();
    }
    include 'footer.php';
}

function secarticleadd()
{
    list($secid,
         $title,
         $content,
         $slanguage) = pnVarCleanFromInput('secid',
                                           'title',
                                           'content',
                                           'slanguage');

    // greg - this may not need to be here, I just moved it from
    // the switch function.
    if (!isset($secid) || !is_numeric($secid)){
        $secid = '';
    }
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secname]
                              FROM $pntable[sections] WHERE $column[secid]=".pnVarPrepForStore($secid)."");

    list($secname) = $result->fields;
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:", ACCESS_ADD)) {
        include 'header.php';
        echo _SECTIONSADDARTICLENOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['seccont_column'];
    $nextid = $dbconn->GenId($pntable['seccont']);
    $dbconn->Execute("INSERT INTO $pntable[seccont] ($column[artid],
                      $column[secid], $column[title], $column[content],
                      $column[counter], $column[slanguage])
                    VALUES ($nextid,'".pnVarPrepForStore($secid)."','".pnVarPrepForStore($title)."','".pnVarPrepForStore($content)."','0', '$slanguage')");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
}

function secartedit()
{
    $artid = pnVarCleanFromInput('artid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    include 'header.php';

    GraphicAdmin();

    OpenTable();
    $che = "";
    echo "<center><font class=\"pn-title\"><b>"._SECTIONSADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['seccont_column'];
    $result = $dbconn->Execute("SELECT $column[artid], $column[secid], $column[title],
                                $column[content], $column[slanguage]
                              FROM $pntable[seccont]
                              WHERE $column[artid]='".pnVarPrepForStore($artid)."'");
    if($result->EOF) {
        echo _SECTIONSEDITARTICLENOTEXIST;
        echo '<br /><br />'._GOBACK;
        include 'footer.php';
        return;
    }
    list($artid, $secid, $title, $content, $slanguage) = $result->fields;

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secname]
                              FROM $pntable[sections] WHERE $column[secid]=".pnVarPrepForStore($secid)."");

    list($secname) = $result->fields;
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_EDIT)) {
        echo _SECTIONSEDITARTICLENOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EDITARTICLE."</b></font></center><br>"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<b>"._TITLE."</b><br>"
        ."<input type=\"text\" name=\"title\" size=\"60\" value=\"$title\"><br><br>"
        ."<b>"._SELSECTION.":</b><br>";
    $column = &$pntable['sections_column'];
    $result2 = $dbconn->Execute("SELECT $column[secid], $column[secname]
                               FROM $pntable[sections] ORDER BY $column[secname]");

    while(list($secid2, $secname) = $result2->fields) {
        if ($secid2==$secid) {
            $che = "checked";
        }
        echo "<input type=\"radio\" name=\"secid\" value=\"$secid2\" $che>".pnVarPrepForDisplay($secname)."<br>";
        $che = "";
        $result2->MoveNext();
    }
    echo "<br><br><b>"._LANGUAGE.": </b>" /* ML added dropdown , currentlang is pre-selected */
                ."<select name=\"slanguage\" class=\"pn-text\">";
    $lang = languagelist();
    $sel_lang[$slanguage] = ' selected';

    echo '<option value="">'._ALL.'</option>';
    $handle = opendir('language');
    while ($f = readdir($handle))
    {
        if (is_dir("language/$f") && (!empty($lang[$f])))
        {
            $langlist[$f] = $lang[$f];
        }
    }
    asort($langlist);
        //  a bit ugly, but it works in E_ALL conditions (Andy Varganov)
        foreach ($langlist as $k=>$v){
        echo '<option value="'.$k.'"';
        //if  (!isset($sel_lang[$k]) || !is_numeric($sel_lang[$k])) echo ' selected';
      	if (isset($sel_lang[$k])) echo ' selected';
        echo '>'. $v . '</option>\n';
        }
        echo '</select>';
        echo "<br><br><b>"._CONTENT."</b><br>"
        ."<textarea name=\"content\" cols=\"60\" rows=\"10\">".pnVarPrepHTMLDisplay($content)."</textarea><br><br>"
        ."<input type=\"hidden\" name=\"artid\" value=\"$artid\">"
        ."<input type=\"hidden\" name=\"op\" value=\"secartchange\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\">";
        if (pnSecAuthAction(0, 'Sections::Article', "$secname:$secid:$artid", ACCESS_DELETE)) {
            echo " [ <a href=\"admin.php?module=$GLOBALS[ModName]&op=secartdelete&amp;artid=$artid&amp;ok=0\">"._DELETE."</a> ]";
        }
        echo "</form>";
    CloseTable();
    include 'footer.php';
}

function sectionmake()
{

    list($secname,
         $image) = pnVarCleanFromInput('secname',
                                       'image');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Sections::Section', "$secname::", ACCESS_ADD)) {
        include 'header.php';
        echo _SECTIONSADDNOAUTH;
        include 'footer.php';
        return;
    }

    if ( ($image == "") or ($image== "none") ) {
          $image = "transparent.gif";
    }

    $column = &$pntable['sections_column'];
    $nextid = $dbconn->GenId($pntable['sections']);
    $dbconn->Execute("INSERT INTO $pntable[sections] ($column[secid],
                      $column[secname], $column[image])
                    VALUES ($nextid,'".pnVarPrepForStore($secname)."', '".pnVarPrepForStore($image)."')");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
}

function sectionedit()
{
    $secid = pnVarCleanFromInput('secid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';

    GraphicAdmin();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._SECTIONSADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secname], $column[image]
                              FROM $pntable[sections]
                              WHERE $column[secid]=".pnVarPrepForStore($secid)."");

    list($secname, $image) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_EDIT)) {
        echo _SECTIONSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['seccont_column'];
    $result2 = $dbconn->Execute("SELECT COUNT(*)
                               FROM $pntable[seccont]
                               WHERE $column[secid]=".pnVarPrepForStore($secid)."");
    $number = $result2->fields[0];
    $result2->Close();

    OpenTable();
    echo "<img src=\"images/sections/$image\" border=\"0\" alt=\"\"><br><br>"
        ."<font class=\"pn-title\"><b>"._EDITSECTION.": ".pnVarPrepForDisplay($secname)."</b></font>"
        ."<br>("._SECTIONHAS." $number "._ARTICLESATTACH.")"
        ."<br><br>"
       ."<form action=\"admin.php\" name=\"secartedit\" method=\"post\">"
    ."<select name=\"artid\">";
    $column = &$pntable['seccont_column'];
    $result = $dbconn->Execute("SELECT $column[artid], $column[title]
                              FROM $pntable[seccont]
                              WHERE $column[secid]=$secid ORDER BY $column[artid]");

    while(list($artid, $title) = $result->fields) {

        $result->MoveNext();
        if (pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_EDIT)) {
            echo "<option value=\"$artid\">".pnVarPrepForDisplay($title)."</option>";
        }
    }
    $result->Close();
    echo "</select>&nbsp;&nbsp;"
        ."<input type=\"hidden\" name=\"op\" value=\"secartedit\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"submit\" value=\""._OK."\">"
        ."</form><br>"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<b>"._SECTIONNAME."</b><br><font class=\"pn-sub\">"._40CHARSMAX."</font><br>"
        ."<input type=\"text\" name=\"secname\" size=\"40\" maxlength=\"40\" value=\"$secname\"><br><br>"
        ."<b>"._SECTIONIMG."</b><br><font class=\"pn-sub\">"._SECIMGEXAMPLE."</font><br>"
        ."<input type=\"text\" name=\"image\" size=\"40\" maxlength=\"50\" value=\"$image\"><br><br>"
        ."<input type=\"hidden\" name=\"secid\" value=\"$secid\">"
        ."<input type=\"hidden\" name=\"op\" value=\"sectionchange\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\">";
    if (pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_DELETE)) {
        echo " [ <a href=\"admin.php?module=$GLOBALS[ModName]&op=sectiondelete&amp;secid=$secid&amp;ok=0\">"._DELETE."</a> ]";
    }
    echo "</form>";
    CloseTable();

    include 'footer.php';
}

function sectionchange()
{
    list($secid,
         $secname,
         $image) = pnVarCleanFromInput('secid',
                                       'secname',
                                       'image');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_EDIT)) {
        include 'header.php';
        echo _SECTIONSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    if ( ($image == "") or ($image== "none") ) {
          $image = "transparent.gif";
    }

    $column = &$pntable['sections_column'];
    $dbconn->Execute("UPDATE $pntable[sections]
                    SET $column[secname]='".pnVarPrepForStore($secname)."', $column[image]='".pnVarPrepForStore($image)."'
                    WHERE $column[secid]=".pnVarPrepForStore($secid)."");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
}

function secartchange()
{
    list($artid,
         $secid,
         $title,
         $content,
         $slanguage) = pnVarCleanFromInput('artid',
                                           'secid',
                                           'title',
                                           'content',
                                           'slanguage');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Have to get old title/sectionname
    $column = &$pntable['sections_column'];
    $contcolumn = &$pntable['seccont_column'];

    $result = $dbconn->Execute("SELECT $column[secname], $contcolumn[title]
                              FROM $pntable[sections], $pntable[seccont]
                              WHERE $contcolumn[artid]=".pnVarPrepForStore($artid)."
                              AND $column[secid] = ".pnVarPrepForStore($secid)."");

    list($secname, $orig_title) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_EDIT)) {
        include 'header.php';
        echo _SECTIONSEDITARTICLENOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['seccont_column'];
    $dbconn->Execute("UPDATE $pntable[seccont]
                    SET $column[secid]='".pnVarPrepForStore($secid)."', $column[title]='".pnVarPrepForStore($title)."',
                      $column[content]='".pnVarPrepForStore($content)."',
                      $column[slanguage]='".pnVarPrepForStore($slanguage)."'
                    WHERE $column[artid]=".pnVarPrepForStore($artid)."");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
}

function sectiondelete()
{
    list($secid,
         $ok) = pnVarCleanFromInput('secid',
                                    'ok');

    if(!isset($ok)) {
        $ok = 0;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secname]
                              FROM $pntable[sections]
                              WHERE $column[secid]=".pnVarPrepForStore($secid)."");

    list($secname) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_DELETE)) {
        include 'header.php';
        echo _SECTIONSDELNOAUTH;
        include 'footer.php';
        return;
    }

    if($ok==1) {

        if (!pnSecConfirmAuthKey()) {
            include 'header.php';
            echo _BADAUTHKEY;
            include 'footer.php';
            exit;
        }
        $dbconn->Execute("DELETE FROM $pntable[seccont]
                        WHERE ".$pntable['seccont_column']['secid']."='".pnVarPrepForStore($secid)."'");
        $dbconn->Execute("DELETE FROM $pntable[sections]
                        WHERE ".$pntable['sections_column']['secid']."='".pnVarPrepForStore($secid)."'");
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
    } else {
        include 'header.php';

        GraphicAdmin();

        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._SECTIONSADMIN."</b></font></center>";
        CloseTable();

        $column = &$pntable['sections_column'];
        $result= $dbconn->Execute("SELECT $column[secname]
                                 FROM $pntable[sections]
                                 WHERE $column[secid]=".pnVarPrepForStore($secid)."");

        list($secname) = $result->fields;
        OpenTable();
        echo "<center><b>"._DELSECTION.": $secname</b><br><br>\n";
        echo "<table><tr><td>\n";
        echo myTextForm("admin.php?module=".$GLOBALS['module']."&op=sections", _NO);
        echo "</td><td>\n";
        echo myTextForm("admin.php?module=".$GLOBALS['module']."&op=sectiondelete&amp;secid=$secid&amp;ok=1&amp;authid=".pnSecGenAuthKey()."", _YES);
        echo "</td></tr></table>\n";
        echo "</center>\n";
        CloseTable();
        include 'footer.php';
    }
}

function secartdelete()
{
    list($artid,
         $ok) = pnVarCleanFromInput('artid',
                                    'ok');

    if(!isset($ok)) {
        $ok = 0;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['sections_column'];
    $contcolumn = &$pntable['seccont_column'];
    $result = $dbconn->Execute("SELECT $column[secname], $contcolumn[title]
                              FROM $pntable[sections], $pntable[seccont]
                              WHERE $contcolumn[artid]=".pnVarPrepForStore($artid)."
                              AND $column[secid] = ".pnVarPrepForStore($contcolumn['secid'])."");

    list($secname, $title) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_DELETE)) {
        include 'header.php';
        echo _SECTIONSDELARTICLENOAUTH;
        include 'footer.php';
        return;
    }

    if($ok == 1) {
        if (!pnSecConfirmAuthKey()) {
            include 'header.php';
            echo _BADAUTHKEY;
            include 'footer.php';
            exit;
        }
        $dbconn->Execute("DELETE FROM $pntable[seccont]
                        WHERE {$pntable['seccont_column']['artid']}='".pnVarPrepForStore($artid)."'");
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=sections');
    } else {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._SECTIONSADMIN."</b></font></center>";
        CloseTable();

        $column = &$pntable['seccont_column'];
        $result = $dbconn->Execute("SELECT $column[title]
                                  FROM $pntable[seccont]
                                  WHERE $column[artid]=".pnVarPrepForStore($artid)."");

        list($title) = $result->fields;
        OpenTable();
        echo "<center><b>"._DELARTICLE.": ".pnVarPrepForDisplay($title)."</b><br><br>\n";
        echo "<table><tr><td>\n";
        echo myTextForm("admin.php?module=".$GLOBALS['module']."&op=sections", _NO);
        echo "</td><td>\n";
        echo myTextForm("admin.php?module=".$GLOBALS['module']."&op=secartdelete&amp;artid=$artid&amp;ok=1&amp;authid=".pnSecGenAuthKey()."", _YES);
        echo "</td></tr></table>\n";
        echo "</center>\n";
        CloseTable();
        include 'footer.php';
    }
}

function sections_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   if ((!pnSecAuthAction(0, 'Sections::Section', '::', ACCESS_EDIT)) &&
       (!pnSecAuthAction(0, 'Sections::Article', '::', ACCESS_EDIT))) {
       include 'header.php';
       echo _SECTIONSNOAUTH;
       include 'footer.php';
   } else {

    switch ($op)
    {
        case "sections":
            sections();
            break;

        case "sectionedit":
            sectionedit();
            break;

        case "sectionmake":
            sectionmake();
            break;

        case "sectiondelete":
            sectiondelete();
            break;

        case "sectionchange":
            sectionchange();
            break;

        case "secarticleadd":
            secarticleadd();
            break;

        case "secartedit":
            secartedit();
            break;

        case "secartchange":
            secartchange();
            break;

        case "secartdelete":
            secartdelete();
            break;

        default:
            sections();
            break;
       }
   }
}
?>