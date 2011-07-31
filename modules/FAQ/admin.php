<?php
// File: $Id: admin.php,v 1.7 2002/10/29 16:43:29 skooter Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
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
// Original Author of file: Richard Tirtadji <rtirtadji@hotmail.com>,
//                          Hutdik Hermawan <hutdik76@hotmail.com>
// Purpose of file: FAQ system, based on PHP-Nuke Add-On http://nukeaddon.com/
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

$ModName = $module;
if (pnSecAuthAction(0, 'FAQ::', '::', ACCESS_ADMIN)) {

modules_get_language();
modules_get_manual();

include_once ("modules/$ModName/faq-categories.php");

/**
 * Faq Admin Function
 */

function faq_admin_main()
{

    include 'header.php';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $authid = pnSecGenAuthKey();

    $currentlang = pnUserGetLang();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._FAQADMIN."</font></center>";
    CloseTable();

    $lang = languagelist();
    $column = &$pntable['faqcategories_column'];
    $result = $dbconn->Execute("SELECT count($column[id_cat])
                              FROM $pntable[faqcategories]");
    list($count) = $result->fields;
    if ($count > 0) {
        OpenTable();
        echo "<center><font class=\"pn-title\">"._ACTIVEFAQS."</font><br/><br/>"
        ."<font class=\"pn-normal\"><b>[<a href=\"admin.php?module=".$GLOBALS['module']."&op=FaqCatUnanswered\">"
        ._VIEWUNANSWERED."</a>]</b></font></center><br>"
        ."<table border=\"0\" width=\"100%\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\"><tr>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._ID."</b></td>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._CATEGORIES."</b></td>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._SUBCATEGORIES."</b></td>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._LANGUAGE."</b></td>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._FUNCTIONS."</b></td></tr><tr>";

        $column = &$pntable['faqcategories_column'];
        $result = $dbconn->Execute("SELECT $column[id_cat], $column[categories], $column[language]
                                  FROM $pntable[faqcategories]
                                  WHERE $column[parent_id]=0
                                  ORDER BY $column[id_cat]");
        while(list($id_cat, $categories, $language) = $result->fields) {
            $result->MoveNext();

           echo "<tr>";
           echo "<td align=\"center\" bgcolor=\"".$GLOBALS['bgcolor3']."\">".pnVarPrepForDisplay($id_cat)."</td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor3']."\" align=\"center\">".pnVarPrepForDisplay($categories)."</td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor3']."\"></td>";
            if (!empty($language)) {
                echo "<td bgcolor=\"".$GLOBALS['bgcolor3']."\" align=\"center\">".pnVarPrepForDisplay($lang[$language])."</td>";
            } else {
                echo "<td bgcolor=\"".$GLOBALS['bgcolor3']."\" align=\"center\">".pnVarPrepForDisplay(_ALL)."</td>";
            }
            echo "<td bgcolor=\"".$GLOBALS['bgcolor3']."\" align=\"center\">[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=FaqCatGo&amp;id_cat=$id_cat\">"
            ._CONTENT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=FaqCatEdit&amp;id_cat=$id_cat\">"
            ._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=FaqCatDel&amp;id_cat=$id_cat&amp;ok=0&amp;authid=$authid\">"._DELETE."</a> ]</td>";
            echo "</tr>";

            $column = &$pntable['faqcategories_column'];
            $subresult = $dbconn->Execute("SELECT $column[id_cat], $column[categories], $column[language]
                                         FROM $pntable[faqcategories]
                                         WHERE $column[parent_id]=".(int)pnVarPrepForStore($id_cat)."
                                         ORDER BY $column[id_cat]");
             while(list($sid_cat, $scategories, $language) = $subresult->fields) {
                 $subresult->MoveNext();

                echo "<tr>";
                echo "<td align=\"center\">".pnVarPrepForDisplay($sid_cat)."</td>"
                ."<td></td>"
                ."<td bgcolor=\"".$GLOBALS['bgcolor3']."\" colspan=1 align=\"center\">".pnVarPrepForDisplay($scategories)."</td>";
                if (!empty($language)) {
                    echo "<td align=\"center\">".pnVarPrepForDisplay($lang[$language])."</td>";
                } else {
                    echo "<td align=\"center\">".pnVarPrepForDisplay(_ALL)."</td>";
                }
                echo "<td align=\"center\">[ <a href=\"admin.php?module=".$GLOBALS['module']
                ."&op=FaqCatGo&amp;id_cat=$sid_cat\">"
                ._CONTENT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']
                ."&op=FaqCatEdit&amp;id_cat=$sid_cat\">"
                ._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=FaqCatDel&amp;id_cat=$sid_cat&amp;ok=0&amp;authid=$authid\">"
                ._DELETE."</a> ]</td>";
                echo "</tr>";
            }
        }

        echo "</td></tr></table>";
    CloseTable();
    }
    faq_admin_FaqCatNew();
    include 'footer.php';
}

function faq_admin_FaqCatGo($var)
{
    include 'header.php';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $authid = pnSecGenAuthKey();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._FAQADMIN."</font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\">"._QUESTIONS."</font></center><br>"
    ."<table border=\"0\" width=\"100%\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\"><tr>"
    ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><font class=\"pn-normal\"><b>"._CONTENT."</b></font></td>"
    ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><font class=\"pn-normal\"><b>"._FUNCTIONS."</b></font></td></tr>";
    $column = &$pntable['faqanswer_column'];
    $result = $dbconn->Execute("SELECT $column[id], $column[question], $column[answer]
                              FROM $pntable[faqanswer]
                              WHERE $column[id_cat]='".pnVarPrepForStore($var['id_cat'])."' AND $column[answer]<>''
                              ORDER BY $column[id]");
    while(list($id, $question, $answer) = $result->fields) {

        $result->MoveNext();
    echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor3']."\" width=\"90%\"><i>".pnVarPrepHTMLDisplay(nl2br($question))."</i><br><br>".pnVarPrepHTMLDisplay(nl2br($answer)).""
        ."</td><td bgcolor=\"".$GLOBALS['bgcolor2']."\" width=\"10%\" align=\"center\"><font class=\"pn-normal\">"
	    ."<b>[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatGoEdit&amp;id=$id\">"
        ._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatGoDel&amp;id=$id&amp;ok=0&amp;authid=$authid\">"._DELETE."</a> ]</b></font></td></tr>"
        ."</td></tr>";
    }
    echo "</table>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._ADDQUESTION."</b></center><br>"
    ."<form action=\"admin.php\" method=\"post\">"
    ."<table border=\"0\" width=\"100%\"><tr><td>"
    .""._QUESTION.":</td><td><input type=\"text\" name=\"question\" size=\"40\"></td></tr><tr><td>"
    .""._ANSWER." </td><td><textarea name=\"answer\" cols=\"60\" rows=\"10\"></textarea>"
    ."</td></tr></table>"
    ."<input type=\"hidden\" name=\"id_cat\" value=\"".$var['id_cat']."\">"
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"FaqCatGoAdd\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value="._SAVE."> "._GOBACK.""
    ."</form>";
    CloseTable();
    include 'footer.php';
}

function faq_admin_FaqCatGoEdit($var)
{
    include 'header.php';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-pagetitle\">"._FAQADMIN."</font></center>";
    CloseTable();

    $column = &$pntable['faqanswer_column'];
    $result = $dbconn->Execute("SELECT $column[question], $column[answer], $column[id_cat]
                              FROM $pntable[faqanswer]
                              WHERE $column[id]='".pnVarPrepForStore($var['id'])."'");
    list($question, $answer, $id_cat) = $result->fields;
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._EDITQUESTIONS."</b></font></center>"
    ."<form action=\"admin.php\" method=\"post\">"
    ."<input type=\"hidden\" name=\"id\" value=\"".$var['id']."\">"
    ."<table border=\"0\" width=\"100%\"><tr><td>"
    .""._QUESTION.":</td><td><input type=\"text\" name=\"question\" size=\"62\" value=\"".pnVarPrepHTMLDisplay($question)."\"></td></tr><tr><td>"
    .""._ANSWER.":</td><td><textarea name=\"answer\" cols=\"60\" rows=\"5\">".pnVarPrepHTMLDisplay($answer)."</textarea>"
    ."</td></tr>"
    ."<tr><td>"
    ._CATEGORY
    ."</td><td>";

    echo "<select name=\"id_cat\">";
    $column = &$pntable['faqcategories_column'];
    $result = $dbconn->Execute("SELECT $column[id_cat], $column[categories]
                              FROM $pntable[faqcategories]
                              WHERE $column[parent_id] = 0
                              ORDER BY $column[id_cat]");
    while(list($nid_cat, $ncategories) = $result->fields)
    {
        $result->MoveNext();

        if( $nid_cat == $id_cat )
        {
            echo "<option value=\"$nid_cat\" selected=\"true\">".pnVarPrepForDisplay($ncategories)."</option>";
        } else {
            echo "<option value=\"$nid_cat\">".pnVarPrepForDisplay($ncategories)."</option>";
        }

        $column = &$pntable['faqcategories_column'];
        $cresult = $dbconn->Execute("SELECT $column[id_cat], $column[categories]
                                   FROM $pntable[faqcategories]
                                   WHERE $column[parent_id]=".pnVarPrepForStore($nid_cat)."");
        while(list($cid_cat, $ccategories) = $cresult->fields )
        {
            $cresult->MoveNext();

            if($cid_cat == $id_cat)
            {
                echo "<option value=\"$cid_cat\" selected> ".pnVarPrepForDisplay($ncategories)." --> ".pnVarPrepForDisplay($ccategories)."</option>";
            } else {
                echo "<option value=\"$cid_cat\"> ".pnVarPrepForDisplay($ncategories)." --> ".pnVarPrepForDisplay($ccategories)."</option>";
            }
        }
    }
    echo "</select>";

    echo "</td></tr></table>"
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"FaqCatGoSave\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value="._SAVE."> "._GOBACK.""
    ."</form>";
    CloseTable();
    include 'footer.php';
}

function faq_admin_FaqCatGoSave($var)
{
    list($id,
         $id_cat,
         $question,
         $answer)= pnVarCleanFromInput('id',
                                       'id_cat',
                                       'question',
                                       'answer');

    if (!isset($id) || !is_numeric($id)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }
    if (!isset($id_cat) || !is_numeric($id_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

	//make sure question is filled in. - skooter
    if (empty($question)){
   	    include 'header.php';
		OpenTable();
		echo "<center><br><b>";
        echo _QUESTIONBLANK;
		echo  "</b><br><br>"._GOBACK."</center>";
		CloseTable();
		include 'footer.php';
		exit;
	}
    
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable ['faqanswer_column'];
    $dbconn->Execute("UPDATE $pntable[faqanswer]
                     SET $column[question]='".pnVarPrepForStore($question)."',
                         $column[answer]='".pnVarPrepForStore($answer)."',
                         $column[id_cat]=".(int)pnVarPrepForStore($id_cat)."
                    WHERE $column[id]	=".(int)pnVarPrepForStore($id));

	//changing redirect to take back to category list that admin is editing - skooter
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=FaqCatGo&id_cat='.$id_cat);
}

function faq_admin_FaqCatGoAdd($var)
{
    list($id_cat,
         $question,
         $answer) = pnVarCleanFromInput('id_cat',
                                        'question',
                                        'answer');
    if (!isset($id_cat) || !is_numeric($id_cat)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }

	//make sure question is filled in - skooter
    if (empty($question)){
   	    include 'header.php';
		OpenTable();
		echo "<center><br><b>";
        echo _QUESTIONBLANK;
		echo  "</b><br><br>"._GOBACK."</center>";
		CloseTable();
		include 'footer.php';
		exit;
	}
    
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column = &$pntable ['faqanswer_column'];
    $newid = $dbconn->GenId($pntable['faqanswer']);
    $dbconn->Execute("INSERT INTO $pntable[faqanswer]
                              ($column[id], $column[id_cat], $column[question], $column[answer] )
                       values ($newid, ".(int)pnVarPrepForStore($id_cat).", '".pnVarPrepForStore($question)."', '".pnVarPrepForStore($answer)."')");
  
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=FaqCatGo&id_cat='.$id_cat);
}

function faq_admin_FaqCatGoDel($var)
{
    $authid = pnVarCleanFromInput('authid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($ok,$id) = pnVarCleanFromInput('ok','id');
    if (!isset($id) || !is_numeric($id)) {
        include 'header.php';
        echo _MODARGSERROR;
        include 'footer.php';
        exit;
    }
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if($ok==1) {
        $dbconn->Execute("DELETE FROM $pntable[faqanswer] WHERE {$pntable['faqanswer_column']['id']}=".(int)pnVarPrepForStore($id));
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=main');
    } else {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._FAQADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<br><center><b>"._QUESTIONDEL."</b><br><br>";
	$authid = pnSecGenAuthKey();

	echo "[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatGoDel&amp;id=".$var['id']."&amp;ok=1&amp;authid=$authid\">" ._YES."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=main\">"._NO."</a> ]</center><br><br>";
	CloseTable();
	include 'footer.php';
	}
}

function faq_admin_FaqCatUnanswered()
{
    include 'header.php';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $authid = pnSecGenAuthKey();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._FAQADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._UNANSWEREDQUESTIONS."</b></font></center><br>"
    ."<table border=1 width=100% align=\"center\"><tr>"
    ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\">"._CONTENT."</td>"
    ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\">"._FUNCTIONS."</td></tr>";

    $column = &$pntable['faqanswer_column'];
    $result = $dbconn->Execute("SELECT $column[id], $column[question], $column[answer]
                              FROM $pntable[faqanswer]
                              WHERE $column[answer]=''
                              ORDER BY $column[id]");
    while(list($id, $question, $answer) = $result->fields) {

        $result->MoveNext();
    echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\" width=\"90%\"><i>".pnVarPrepHTMLDisplay($question)."</i>"
        ."</td><td align=\"center\" width=\"10%\">[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatGoEdit&amp;id=$id\">"
        ._ANSWER."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=FaqCatGoDel&amp;id=$id&amp;ok=0&amp;authid=$authid\">"._DELETE."</a> ]</td></tr>"
        ."</td></tr>";
    }

    echo "</table>";
    CloseTable();

    OpenTable();
    echo "<center>" . _GOBACK . "</center>";
    CloseTable();

    include 'footer.php';
}

} else {
    echo "Access Denied";
}
?>
