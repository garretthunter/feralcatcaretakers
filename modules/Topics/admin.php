<?php
// File: $Id: admin.php,v 1.2 2002/10/21 12:41:37 magicx Exp $ $Name:  $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) { die ("Access Denied"); }

$ModName = $module;
modules_get_language();
modules_get_manual();

/**
 * Topics Manager Functions
 */

function topicsmanager()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $tipath = pnConfigGetVar('tipath');
    $topicsinrow = pnConfigGetVar('topicsinrow');

    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._TOPICSMANAGER."</b></font></center>";
    CloseTable();

    // List of current topics
    if (pnSecAuthAction(0, 'Topics::Topic', '::', ACCESS_READ)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._CURRENTTOPICS."</b></font><br />"._CLICK2EDIT."</font></center><br />"
            ."<table border=\"0\" width=\"100%\" align=\"center\" cellpadding=\"2\">";
        $count = 0;
        $column = &$pntable['topics_column'];
        $result = $dbconn->Execute("SELECT $column[topicid], $column[topicname], $column[topicimage], $column[topictext] FROM $pntable[topics] ORDER BY $column[topicname]");
        while(list($topicid, $topicname, $topicimage, $topictext) = $result->fields) {

            $result->MoveNext();
            echo "<td align=\"center\">";
            if (pnSecAuthAction(0, 'Topics::Topic', "$topicname::$topicid", ACCESS_EDIT)) {
                echo "<a href=\"admin.php?module=".$GLOBALS['module']."&op=topicedit&amp;topicid=".pnVarPrepForDIsplay($topicid)."\"><img src=\"$tipath$topicimage\" border=\"0\" alt=\"\"></a><br />"
                    ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=topicedit&amp;topicid=".pnVarPrepForDIsplay($topicid)."\"><font class=\"pn-normal\"><b>$topictext</td></a>";
            } else {
                echo "<img src=\"".pnVarPrepForDIsplay($tipath)."".pnVarPrepForDIsplay($topicimage)."\" border=\"0\" alt=\"\"><br />"
                    ."<font class=\"pn-normal\"><b>".pnVarPrepForDIsplay($topictext)."</td></a>";
            }
            $count++;
            if ($count == $topicsinrow) {    // changed hardcoded number of topics icons - rwwood
                echo "</tr><tr>";
                $count = 0;
            }
        }

        echo "</table>";
        echo "<br /><center><font class=\"pn-title\"><b>"._ROWDEFINE."</b></font>";    // added for topics icon spacing - rwwood

        CloseTable();
    }

    // Add a topic
    if (pnSecAuthAction(0, 'Topics::Topic', '::', ACCESS_ADD)) {
        echo "<a name=\"Add\">";
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ADDATOPIC."</b></font></center><br />"
            ."<form action=\"admin.php\" method=\"post\">"
            ."<b>"._TOPICNAME.":</b><br /><font class=\"pn-sub\">"._TOPICNAME1."<br />"
            .""._TOPICNAME2."</font><br />"
            ."<input type=\"text\" name=\"topicname\" size=\"20\" maxlength=\"20\" value=\"$topicname\"><br /><br />"
            ."<b>"._TOPICTEXT.":</b><br /><font class=\"pn-sub\">"._TOPICTEXT1."<br />"
            .""._TOPICTEXT2."</font><br />"
            ."<input type=\"text\" name=\"topictext\" size=\"40\" maxlength=\"40\" value=\"$topictext\"><br /><br />"
            ."<b>"._TOPICIMAGE.":</b><br /><font class=\"pn-sub\">("._TOPICIMAGE1." $tipath)<br />"
            .""._TOPICIMAGE2."</font><br />"
            ."<input type=\"text\" name=\"topicimage\" size=\"20\" maxlength=\"20\" value=\"$topicimage\"><br /><br />"
            ."<input type=\"hidden\" name=\"op\" value=\"topicmake\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
	    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._ADDTOPIC."\">"
            ."</form>";
        CloseTable();
    }
// Access Topics Settings
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._TOPICSCONF."</b></font></center><br /><br />";
    echo "<center><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getConfig\">"._TOPICSCONF."</a></center>";
    CloseTable();
    include("footer.php");
}

function topicedit()
{
    $topicid = pnVarCleanFromInput('topicid');

    $authid = pnSecGenAuthKey();

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $tipath = pnConfigGetVar('tipath');

    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._TOPICSMANAGER."</b></font></center>";
    CloseTable();

    $column = &$pntable['topics_column'];
    $result = $dbconn->Execute("SELECT $column[topicid], $column[topicname], $column[topicimage], $column[topictext] FROM $pntable[topics] WHERE $column[topicid]=".pnVarPrepForStore($topicid)."");
    list($topicid, $topicname, $topicimage, $topictext) = $result->fields;

    if (!(pnSecAuthAction(0, 'Topics::Topic', "$topicname::$topicid", ACCESS_EDIT))) {
        echo _TOPICSEDITNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<img src=\"$tipath$topicimage\" border=\"0\" align=\"right\" alt=\"$topictext\">"
        ."<font class=\"pn-title\"><b>"._EDITTOPIC.": ".pnVarPrepForDIsplay($topictext)."</b></font>"
        ."<br /><br />"
        ."<form action=\"admin.php\" method=\"post\"><br />"
        ."<b>"._TOPICNAME.":</b><br /><font class=\"pn-sub\">"._TOPICNAME1."<br />"
        .""._TOPICNAME2."</font><br />"
        ."<input type=\"text\" name=\"topicname\" size=\"20\" maxlength=\"20\" value=\"".pnVarPrepForDIsplay($topicname)."\"><br /><br />"
        ."<b>"._TOPICTEXT.":</b><br /><font class=\"pn-sub\">"._TOPICTEXT1."<br />"
        .""._TOPICTEXT2."</font><br />"
        ."<input type=\"text\" name=\"topictext\" size=\"40\" maxlength=\"40\" value=\"".pnVarPrepForDIsplay($topictext)."\"><br /><br />"
        ."<b>"._TOPICIMAGE.":</b><br /><font class=\"pn-sub\">("._TOPICIMAGE1." $tipath)<br />"
        .""._TOPICIMAGE2."</font><br />"
        ."<input type=\"text\" name=\"topicimage\" size=\"20\" maxlength=\"20\" value=\"".pnVarPrepForDIsplay($topicimage)."\"><br /><br />";
    if (pnSecAuthAction(0, 'Topics::Related', "$topicname::", ACCESS_ADD)) {
        echo "<b>"._ADDRELATED.":</b><br />"
             ._SITENAME.": <input type=\"text\" name=\"name\" size=\"30\" maxlength=\"30\"><br />"
             .""._URL.": <input type=\"text\" name=\"url\" value=\"http://\" size=\"50\" maxlength=\"200\"><br /><br />";
    }
    if (pnSecAuthAction(0, 'Topics::Related', "$topicname::", ACCESS_EDIT)) {
        echo "<b>"._ACTIVERELATEDLINKS.":</b><br />";
        OpenTable2();
        $column = &$pntable['related_column'];
        $res=$dbconn->Execute("SELECT $column[rid], $column[name], $column[url] FROM $pntable[related] WHERE $column[tid]=".pnVarPrepForStore($topicid)."");
        if ($res->EOF) {
            echo "<tr><td><font class=\"pn-sub\">"._NORELATED."</font></td></tr>";
        }
        while(list($rid, $name, $url) = $res->fields) {

            $res->MoveNext();
            echo "<tr><td align=\"center\"><font class=\"pn-normal\"><strong><big>&middot;</big></strong>&nbsp;&nbsp;<a href=\"$url\">$name</a></td>"
                    ."<td align=\"center\"><font class=\"pn-normal\"><a href=\"".pnVarPrepForDIsplay($url)."\">$url</a></td>";
            if (pnSecAuthAction(0, 'Topics::Related', "$topicname::", ACCESS_EDIT)) {
                echo "<td align=\"right\"><font class=\"pn-normal\">[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=relatededit&amp;tid=$topicid&amp;rid=".pnVarPrepForDIsplay($rid)."&amp;authid=$authid\">"._EDIT."</a>";
                if (pnSecAuthAction(0, 'Topics::Related', "$topicname::", ACCESS_DELETE)) {
                    echo " | <a href=\"admin.php?module=".$GLOBALS['module']."&op=relateddelete&amp;tid=$topicid&amp;rid=".pnVarPrepForDIsplay($rid)."&amp;authid=$authid\">"._DELETE."</a> ]";
                } else {
                    echo " ]";
                }
            }
            echo "</td></tr>";
        }
        CloseTable2();
        echo "<br /><br />";
    }
    echo "<input type=\"hidden\" name=\"topicid\" value=\"$topicid\">"
        ."<input type=\"hidden\" name=\"op\" value=\"topicchange\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<INPUT type=\"submit\" value=\""._SAVECHANGES."\"> <font class=\"pn-normal\">"
        ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=topicdelete&amp;topicid=$topicid&amp;ok=0&amp;authid=$authid\">"._DELETE."</a> ]</font>"
        ."</form>";
    CloseTable();
    include("footer.php");
}

function relatededit()
{
    list($tid,
	 $rid) = pnVarCleanFromInput('tid',
				     'rid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $tipath = pnConfigGetVar('tipath');

    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._TOPICSMANAGER."</b></font></center>";
    CloseTable();

    // grab an entry from the related table

    $column = &$pntable['related_column'];
    $sql = buildQuery(array('related'), array($column['name'], $column['url']), "$column[rid]=$rid", '');
    $result = $dbconn->SelectLimit($sql,1);
    list($name, $url) = $result->fields;
    $result->Close();

    // grab the topic and description
    $column = &$pntable['topics_column'];
    $sql = buildQuery(array('topics'), array($column['topictext'], $column['topicimage']), "$column[topicid]=".pnVarPrepForStore($tid)."", '');
    $result = $dbconn->SelectLimit($sql,1);
    list($topictext, $topicimage) = $result->fields;

    if (!(pnSecAuthAction(0, 'Topics::Related', "$name:$topicname:$tid", ACCESS_EDIT))) {
        echo _TOPICSEDITNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<center>"
        ."<img src=\"$tipath$topicimage\" border=\"0\" alt=\"$topictext\" align=\"right\">"
        ."<font class=\"pn-title\"><b>"._EDITRELATED."</b></font><br />"
        ."<b>"._TOPIC.":</b> $topictext</center>"
        ."<form action=\"admin.php\" method=\"post\">"
        .""._SITENAME.": <input type=\"text\" name=\"name\" value=\"$name\" size=\"30\" maxlength=\"30\"><br /><br />"
        .""._URL.": <input type=\"text\" name=\"url\" value=\"$url\" size=\"60\" maxlength=\"200\"><br /><br />"
        ."<input type=\"hidden\" name=\"op\" value=\"relatedsave\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"tid\" value=\"".pnVarPrepForDIsplay($tid)."\">"
        ."<input type=\"hidden\" name=\"rid\" value=\"".pnVarPrepForDIsplay($rid)."\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\"> "._GOBACK.""
        ."</form>";
    CloseTable();
    include("footer.php");
}

function relatedsave()
{
    list($tid,
	 $rid,
	 $name,
	 $url) = pnVarCleanFromInput('tid',
				     'rid',
				     'name',
				     'url');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['topics_column'];
	$sql = "SELECT $column[topicname]
               FROM $pntable[topics]
               WHERE $column[topicid]=".pnVarPrepForStore($tid)." ORDER BY $column[topicid]";

    $result=$dbconn->SelectLimit($sql,1);

    list($topicname) = $result->fields;
    $result->Close();
    if (!(pnSecAuthAction(0, 'Topics::Related', "$name:$topicname:$tid", ACCESS_EDIT))) {
        include 'header.php';
        echo _TOPICSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['related_column'];
    $dbconn->Execute("UPDATE $pntable[related] SET $column[name]='".pnVarPrepForStore($name)."', $column[url]='".pnVarPrepForStore($url)."' WHERE $column[rid]=".pnVarPrepForStore($rid)."");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=topicedit&topicid='.$tid);
}

function relateddelete()
{
    list($tid,
	  $rid) = pnVarCleanFromInput('tid',
				      'rid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['topics_column'];
	$sql = "SELECT $column[topicname]
              FROM $pntable[topics]
              WHERE $column[topicid]=".pnVarPrepForStore($tid)." ORDER BY $column[topicid]";

    $result=$dbconn->SelectLimit($sql,1);

    list($topicname) = $result->fields;
    $result->Close();
    if (!(pnSecAuthAction(0, 'Topics::Related', "$name:$topicname:$tid", ACCESS_DELETE))) {
        include 'header.php';
        echo _TOPICSDELNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['related_column'];
    $dbconn->Execute("DELETE FROM $pntable[related] WHERE $column[rid]='".pnVarPrepForStore($rid)."'");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=topicedit&topicid='.$tid);
}

function topicmake()
{
    list($topicname,
	 $topicimage,
	 $topictext) = pnVarCleanFromInput('topicname',
					   'topicimage',
					   'topictext');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!(pnSecAuthAction(0, 'Topics::Topic', "$topicname::", ACCESS_ADD))) {
        include 'header.php';
        echo _TOPICSADDNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['topics_column'];
    $nextid = $dbconn->GenId($pntable['topics']);
    $dbconn->Execute("INSERT INTO $pntable[topics] ($column[topicid], $column[topicname], $column[topicimage], $column[topictext], $column[counter]) VALUES ($nextid,'".pnVarPrepForStore($topicname)."','".pnVarPrepForStore($topicimage)."','".pnVarPrepForStore($topictext)."',0)");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=topicsmanager');
}

function topicchange()
{
    list($topicid,
	 $topicname,
	 $topicimage,
	 $topictext,
	 $name,
	 $url) = pnVarCleanFromInput('topicid',
				     'topicname',
				     'topicimage',
				     'topictext',
				     'name',
				     'url');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Must use old topicname for authorisation check
    $column = &$pntable['topics_column'];
	$sql = "SELECT $column[topicname]
              FROM $pntable[topics]
              WHERE $column[topicid]=".pnvarPrepForStore($topicid)." ORDER BY $column[topicid]";

    $result=$dbconn->SelectLimit($sql,1);

    list($oldtopicname) = $result->fields;
    $result->Close();
    if (!(pnSecAuthAction(0, 'Topics::Topic', "$oldtopicname::$topicid", ACCESS_EDIT))) {
        include 'header.php';
        echo _TOPICSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['topics_column'];
    $dbconn->Execute("UPDATE $pntable[topics] SET $column[topicname]='".pnvarPrepForStore($topicname)."', $column[topicimage]='".pnvarPrepForStore($topicimage)."', $column[topictext]='".pnvarPrepForStore($topictext)."' WHERE $column[topicid]=".pnvarPrepForStore($topicid)."");
    if (!$name) {
    } else {
        $nextid = $dbconn->GenId($pntable['related']);
        $column = &$pntable['related_column'];
        $dbconn->Execute("INSERT INTO $pntable[related] ($column[rid], $column[tid], $column[name], $column[url]) VALUES ($nextid, '".pnvarPrepForStore($topicid)."','".pnvarPrepForStore($name)."','".pnvarPrepForStore($url)."')");
    }
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=topicsmanager');
}

function topicdelete()
{
    list($topicid,
	 $ok) = pnVarCleanFromInput('topicid',
				    'ok');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['topics_column'];
	$sql = "SELECT $column[topicname]
               FROM $pntable[topics]
               WHERE $column[topicid]=".pnvarPrepForStore($topicid)." ORDER BY $column[topicid]";

    $result=$dbconn->SelectLimit($sql,1);

    list($oldtopicname) = $result->fields;
    $result->Close();
    if (!(pnSecAuthAction(0, 'Topics::Topic', "$oldtopicname::$topicid", ACCESS_DELETE))) {
        include 'header.php';
        echo _TOPICSDELNOAUTH;
        include 'footer.php';
        return;
    }
    if ($ok==1) {

    	if (!pnSecConfirmAuthKey()) {
            include 'header.php';
            echo _BADAUTHKEY;
            include 'footer.php';
            exit;
	}
        $column = &$pntable['stories_column'];
        $result=$dbconn->Execute("SELECT $column[sid] FROM $pntable[stories] WHERE $column[topic]='".pnvarPrepForStore($topicid)."'");
        list($sid) = $result->fields;
        $dbconn->Execute("DELETE FROM $pntable[stories] WHERE {$pntable['stories_column']['topic']}='".pnvarPrepForStore($topicid)."'");
        $dbconn->Execute("DELETE FROM $pntable[topics] WHERE {$pntable['topics_column']['topicid']}='".pnvarPrepForStore($topicid)."'");
        $dbconn->Execute("DELETE FROM $pntable[related] WHERE {$pntable['related_column']['tid']}='".pnvarPrepForStore($topicid)."'");
        $column = &$pntable['comments_column'];
        $result = $dbconn->Execute("SELECT $column[sid] FROM $pntable[comments] WHERE $column[sid]='".pnvarPrepForStore($sid)."'");
        list($sid) = $result->fields;
        $result->Close();
        $dbconn->Execute("DELETE FROM $pntable[comments] WHERE {$pntable['comments_column']['sid']}='".pnvarPrepForStore($sid)."'");
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=topicsmanager');
    } else {
        global $topicimage;

        $tipath = pnConfigGetVar('tipath');
	$authid = pnSecGenAuthKey();

        include("header.php");
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._TOPICSMANAGER."</b></font></center>";
        CloseTable();

	$column = &$pntable['topics_column'];
        $result=$dbconn->Execute("SELECT $column[topicimage], $column[topictext] FROM $pntable[topics] WHERE $column[topicid]='".pnvarPrepForStore($topicid)."'");
        list($topicimage, $topictext) = $result->fields;
        OpenTable();
        echo "<center><img src=\"$tipath$topicimage\" border=\"0\" alt=\"$topictext\"><br /><br />"
            ."<b>"._DELETETOPIC." $topictext</b><br /><br />"
            .""._TOPICDELSURE." <i>$topictext</i>?<br />"
            .""._TOPICDELSURE1."<br /><br />"
            ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=topicsmanager\">"._NO.
            "</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=topicdelete&amp;topicid=$topicid&amp;ok=1&amp;authid=$authid\">"._YES.
            "</a> ]</center><br /><br />";
        CloseTable();
        include("footer.php");
    }
}

function topics_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_topicsinrow['1'] = '';
    $sel_topicsinrow['2'] = '';
    $sel_topicsinrow['3'] = '';
    $sel_topicsinrow['4'] = '';
    $sel_topicsinrow['5'] = '';
    $sel_topicsinrow[pnConfigGetVar('topicsinrow')] = ' selected';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._TOPICSCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._TOPICSPATH."</td><td><input type=\"text\" name=\"xtipath\" value=\"".pnConfigGetVar('tipath')."\" size=\"50\" class=\"pn-normal\">"
        .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._TOPICSINROW.'</td><td>'
        .'<select name="xtopicsinrow" size="1" class="pn-normal">';
        $topicsinrows = array('1', '2', '3', '4', '5');
        foreach ( $topicsinrows as $topicsinrow){
                echo "<option value=\"$topicsinrow\"$sel_topicsinrow[$topicsinrow]>$topicsinrow</option>\n";
                }
        echo "</select>\n"
        .'</td></tr>'
        .'</table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function topics_admin_setConfig($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array();

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
	//$var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array();
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

function topics_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   if (!pnSecAuthAction(0, 'Topics::', '::', ACCESS_EDIT)) {
       include 'header.php';
       echo _TOPICSNOAUTH;
       include 'footer.php';
   } else {
       switch ($op) {

        case "topicsmanager":
            topicsmanager();
            break;

        case "topicedit":
            topicedit();
            break;

        case "topicmake":
            topicmake();
            break;

        case "topicdelete":
            topicdelete();
            break;

        case "topicchange":
            topicchange();
            break;

        case "relatedsave":
            relatedsave();
            break;

        case "relatededit":
            relatededit();
            break;

        case "relateddelete":
            relateddelete();
            break;

       case "getConfig":
            topics_admin_getConfig();
            break;

      case "setConfig":
           topics_admin_setConfig($var);
           break;

        default:
            topicsmanager();
            break;
       }
   }
}
?>