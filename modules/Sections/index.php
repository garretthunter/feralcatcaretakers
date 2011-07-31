<?php // $Id: index.php,v 1.3 2002/11/06 21:12:50 nunizgb Exp $ $Name:  $
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
// Filename: modules/Sections/index.php
// Original Author: Francisco Burzi
// Purpose: displays the special sections on the site
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

// this is only here for the get language call
//$ModName = $GLOBALS['name'];
$ModName = basename( dirname( __FILE__ ) );


modules_get_language();

function listsections()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ('header.php');

    if (!pnSecAuthAction(0, 'Sections::Section', '::', ACCESS_OVERVIEW)) {
        echo _SECTIONSNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secid], $column[secname], $column[image]
                              FROM $pntable[sections] ORDER BY $column[secname]");

    $sitename = pnConfigGetVar('sitename');

    OpenTable();
    echo "<center><font class=\"pn-title\">"._SECWELCOME." ".pnVarPrepForDisplay($sitename).".</font><br /><br />"
        ."<font class=\"pn-normal\">"._YOUCANFIND."</font></center><br /><br />"
        ."<table border=\"0\" align=\"center\"><tr>";
    $count = 0;
    while(list($secid, $secname, $image) = $result->fields) {

        $result->MoveNext();
        if (pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_READ)) {
            if ($count == 2) {
                echo "</tr><tr>";
                $count = 0;
            }
            echo "<td><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=listarticles&amp;secid=$secid\">";
            if ( ($image == "transparent.gif") or ($image == "") or ($image== "none") )  {
              echo $secname;
            } else {
              echo "<img src=\"images/".strtolower($GLOBALS[ModName])."/$image\" border=\"0\" Alt=\"$secname\">";
            }
            echo "</a></td>";
            $count++;
        }
    }
    $result->Close();
    echo "</tr></table>";
    CloseTable();
    include ('footer.php');
}

function listarticles()
{
    $secid = pnVarCleanFromInput('secid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['seccont_column'];
        $querylang = "AND ($column[slanguage]='$currentlang' OR $column[slanguage]='')"; /* the OR is needed to display stories who are posted to ALL languages */
    } else {
    $querylang = "";
    }
    include ('header.php');
    OpenTable();

    $column = &$pntable['sections_column'];
    $result = $dbconn->Execute("SELECT $column[secname]
                              FROM $pntable[sections]
                              WHERE $column[secid]=".pnVarPrepForStore($secid)."");
    list($secname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_READ)) {
        echo _SECTIONSARTICLENOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    $column = &$pntable['seccont_column'];
    $result = $dbconn->Execute("SELECT $column[artid], $column[secid], $column[title],
                                $column[content], $column[counter]
                              FROM $pntable[seccont]
                              WHERE $column[secid]=".pnVarPrepForStore($secid)." $querylang");
    $column = &$pntable['sections_column'];
    $result2 = $dbconn->Execute("SELECT $column[image]
                               FROM $pntable[sections]
                               WHERE $column[secid]=".pnVarPrepForStore($secid)."");
    list($image) = $result2->fields;

    if ( ($image == "") or ($image == "none") )  {
      $image = "transparent.gif";
    }

    echo "<center><img src=\"images/".strtolower($GLOBALS[ModName])."/$image\" border=\"0\" alt=\"\"><br /><br />"
        ."<font class=\"pn-title\">"
        ._THISISSEC." $secname.<br />"._FOLLOWINGART."</font></center><br /><br />"
        ."<table border=\"0\" align=\"center\">";
    while(list($artid, $secid, $title, $content, $counter) = $result->fields) {

        $result->MoveNext();
        if (pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_READ)) {
            echo "<tr><td align=\"left\" nowrap>"
                ."<strong><big>&middot;</big></strong>&nbsp;"
                ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=viewarticle&amp;artid=$artid&amp;page=1\">".pnVarPrepForDisplay($title)."</a> (".pnVarPrepForDisplay($counter)." "._READS.")"
                ."&nbsp;<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=printpage&amp;artid=$artid\"><img src=\"modules/".pnVarPrepForOS($GLOBALS['name'])."/images/print.gif\" border=\"0\" Alt=\""._PRINTER."\"></a>"
                ."</td></tr>";
        }
    }
    echo "</table>"
        ."<br /><br /><br /><center>"
        ."<font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index\">"._SECRETURN."</a> ]</font></center>";
    CloseTable();
    $result->Close();
    include ('footer.php');
}

function viewarticle()
{
    list($artid,
         $page) = pnVarCleanFromInput('artid',
                                      'page');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");

    if (!isset($page)) {
        $page = 1;
    }
    if (($page == 1) || ($page == "")) {
        $column = &$pntable['seccont_column'];
        $result = $dbconn->Execute("UPDATE $pntable[seccont]
                                  SET $column[counter]=$column[counter]+1
                                  WHERE $column[artid]='".pnVarPrepForStore($artid)."'");
    }
    $column = &$pntable['seccont_column'];
    $result = $dbconn->Execute("SELECT $column[artid], $column[secid], $column[title],
                             $column[content], $column[counter]
                           FROM $pntable[seccont]
                           WHERE $column[artid]=".pnVarPrepForStore($artid)."");
    list($artid, $secid, $title, $content, $counter) = $result->fields;

    $column = &$pntable['sections_column'];
    $result2 = $dbconn->Execute("SELECT $column[secid], $column[secname]
                               FROM $pntable[sections] WHERE $column[secid]=".pnVarPrepForStore($secid)."");
    list($secid, $secname) = $result2->fields;
    $words = sizeof(explode(" ", $content));
    echo "<center>";
    OpenTable();
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_READ)) {
        echo _SECTIONSARTICLENOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    $contentpages = explode( "<!--pagebreak-->", $content );
    $pageno = count($contentpages);
    if ( $page=="" || $page < 1 )
        $page = 1;
    if ( $page > $pageno )
        $page = $pageno;
    $arrayelement = (int)$page;
    $arrayelement --;
    echo "<font class=\"pn-title\">".pnVarPrepForDisplay($title)."</font><br /><br />";
    if ($pageno > 1) {
        echo "<font class=\"pn-normal\">"._PAGE.": ".pnVarPrepForDisplay($page)."/".pnVarPrepForDisplay($pageno)."</font><br />";
    }
    echo "<font class=\"pn-normal\">(".pnVarPrepForDisplay($words)." "._TOTALWORDS.")</font><br />"
        ."<font class=\"pn-normal\">(".pnVarPrepForDisplay($counter)." "._READS.")</font> &nbsp;&nbsp;"
        ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=printpage&amp;artid=$artid\"><img src=\"modules/".pnVarPrepForOS($GLOBALS['name'])."/images/print.gif\" border=\"0\" Alt=\""._PRINTER."\"></a>"
        ."</font><br /><br />";
    echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($contentpages[$arrayelement])."</font>";
    if($page >= $pageno) {
      $next_page = "";
    } else {
        $next_pagenumber = $page + 1;
        if ($page != 1) {
            $next_page = "<img src=\"modules/".pnVarPrepForOS($GLOBALS[ModName])."/images/blackpixel.gif\" width=\"10\" height=\"2\" border=\"0\" alt=\"\"> &nbsp;&nbsp; ";
        }
        $next_page = "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=viewarticle&amp;artid=$artid&amp;page=$next_pagenumber\">"._NEXT." (".pnVarPrepForDisplay($next_pagenumber)."/".pnVarPrepForDisplay($pageno).")</a> <a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;req=viewarticle&amp;artid=$artid&amp;page=$next_pagenumber\"><img src=\"modules/".pnVarPrepForOS($GLOBALS['name'])."/images/right.gif\" border=\"0\" alt=\""._NEXT."\"></a>";
    }
    if($page <= 1) {
        $previous_page = "";
    } else {
        $previous_pagenumber = $page - 1;
        $previous_page = "<a href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=viewarticle&amp;artid=$artid&amp;page=$previous_pagenumber\"><img src=\"modules/".pnVarPrepForOS($GLOBALS['name'])."/images/left.gif\" border=\"0\" alt=\""._PREVIOUS."\"></a> <a href=\"modules.php?op=modload&amp;name=".$GLOBALS['name']."&amp;file=index&amp;req=viewarticle&artid=$artid&page=$previous_pagenumber\">"._PREVIOUS." ($previous_pagenumber/$pageno)</a>";
    }
    echo "</td></tr>"
        ."<tr><td align=\"center\">"
        ."$previous_page &nbsp;&nbsp; $next_page<br /><br />"
        ."[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=listarticles&amp;secid=$secid\">"._BACKTO." ".pnVarPrepForDisplay($secname)."</a> | "
        ."<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;listsections\">"._SECINDEX."</a> ]";
    CloseTable();
    echo "</center>";
    $result->Close();
    $result2->Close();
    include ('footer.php');
}

function PrintSecPage()
{
    $artid = pnVarCleanFromInput('artid');

      $datetime = &$GLOBALS['datetime']; 

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $site_logo = pnConfigGetVar('site_logo');

    $column = &$pntable['seccont_column'];
    $result=$dbconn->Execute("SELECT $column[title], $column[content] , $column[secid]
                            FROM $pntable[seccont]
                            WHERE $column[artid]=".pnVarPrepForStore($artid)."");
    list($title, $content, $secid) = $result->fields;
    $column = &$pntable['sections_column'];
    $result2 = $dbconn->Execute("SELECT $column[secname]
                               FROM $pntable[sections]
                               WHERE $column[secid]=".pnVarPrepForStore($secid)."");
    list($secname) = $result2->fields;
    if (!pnSecAuthAction(0, 'Sections::Article', "$title:$secname:$artid", ACCESS_READ)) {
        echo _SECTIONSARTICLENOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    $sitename = pnConfigGetVar('sitename');
    $search = array ("'<!--pagebreak-->'si");
    $replace = array ("");
    $content = preg_replace ($search, $replace, $content);

    echo "<html><head><title>$sitename</title>";

    if (defined("_CHARSET") && _CHARSET != "") {
        echo "<META HTTP-EQUIV=\"Content-Type\" "."CONTENT=\"text/html; charset="._CHARSET."\">\n";
    }

    echo "</head>"
        ."<body bgcolor=\"#FFFFFF\" text=\"#000000\">"
        ."<table border=\"0\"><tr><td>"
        ."<table border=\"0\" width=\"640\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#000000\"><tr><td>"
        ."<table border=\"0\" width=\"640\" cellpadding=\"20\" cellspacing=\"1\" bgcolor=\"#FFFFFF\"><tr><td>"
        ."<center>"
        ."<img src=\"images/$site_logo\" border=\"0\" alt=\"\"><br /><br />"
        ."<b>".pnVarPrepForDisplay($title)."</b><br />"
        ."</center>"
        ."".pnVarPrepHTMLDisplay(nl2br($content))."<br /><br />";
    echo "</td></tr></table></td></tr></table>"
        ."<br /><br /><center>"
        ."<font class=\"pn-normal\">"
        ._COMESFROM." ".pnVarPrepForDisplay($sitename)."<br />"
        ."<a class=\"pn-normal\" href=\"" . pnGetBaseURL() . "\">" . pnGetBaseURL() . "</a></font><br /><br />"
        ."</center>"
        ."</td></tr></table>"
        ."</body></html>";
}

if(empty($req)) {
        $req = '';
}

switch($req) {

    case "viewarticle":
        viewarticle();
        break;
    case "listarticles":
        listarticles();
        break;
    case "printpage":
        PrintSecPage();
        break;
    default:
        listsections();
        break;
}
?>