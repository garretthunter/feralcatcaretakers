<?php
// File: $Id: admin.php,v 1.3 2002/10/31 19:55:05 tanis Exp $ $Name:  $
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

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

$ModName = $module;
modules_get_language();
modules_get_manual();

/**
 * Banners Administration Functions
 */

function BannersAdmin()
{
    include 'header.php';
    $bgcolor2 = $GLOBALS['bgcolor2'];
    list($clientname) = pnVarCleanFromInput('clientname');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._BANNERSADMIN."</b></font></center>";
    CloseTable();

    /* Check if Banners variable is active, if not then print a message */
    if (pnConfigGetVar('banners') == 0) {
        OpenTable();
        echo "<center><br><i><b>"._IMPORTANTNOTE."</b></i><br><br>"
            .""._BANNERSNOTACTIVE."<br>"
            .""._TOACTIVATE."<br><br></center>";
        CloseTable();
    }

    // Banners List
    echo "<a name=\"top\"></a>";
    if (pnSecAuthAction(0, 'Banners::Banner', '::', ACCESS_READ)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ACTIVEBANNERS."</b></font></center><br>"
            ."<table width=100% border=0>"
            ."<tr>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._ID."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._IMPRESSIONS."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._IMPLEFT."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKS."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKSPERCENT."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLIENTNAME."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._FUNCTIONS."</b></td>"
            ."</tr>";

        $column = $pntable['banner_column'];
        $column2 = $pntable['bannerclient_column'];
        $result = $dbconn->Execute("SELECT $column[bid],
                                         $column[cid],
                                         $column[imptotal], 
                                         $column[impmade],
                                         $column[clicks],
                                         $column[date],
                                         $column2[name]
                                  FROM $pntable[banner], $pntable[bannerclient]
                                  WHERE $column[cid] = ".pnVarPrepForStore($column2['cid'])." 
                                  ORDER BY $column[bid]");

        while(list($bid, $cid, $imptotal, $impmade, $clicks, $date, $name) = $result->fields) {

            $result->MoveNext();
            // jgm - Get and use $clientname
            if(!isset($clientname)) {
                $clientname = '';
            }
            if (pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_READ)) {
                if($impmade==0) {
                    $percent = 0;
                } else {
                    $percent = substr(100 * $clicks / $impmade, 0, 5);
                }
                if($imptotal==0) {
                    $left = _UNLIMITED;
                } else {
                    $left = $imptotal-$impmade;
                }
                echo "<tr>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($bid) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($impmade) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($left) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($clicks) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($percent) . "%</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=center>" . pnVarPrepForDisplay($name) . "</td>";
                if (pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_EDIT)) {
                    echo "<td bgcolor=\"$bgcolor2\" align=center><a href=\"admin.php?module="
                    .$GLOBALS['module']."&amp;op=BannerEdit&amp;bid=$bid\">"._EDIT."</a>";
                    if (pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_DELETE)) {
                        echo " | <a href=\"admin.php?module=".$GLOBALS['module']
                        ."&amp;op=BannerDelete&amp;bid=$bid&amp;ok=0\">"._DELETE."</a></td>";
                    } else {
                        echo "</td>";
                    }
                } else {
                    echo "<td bgcolor=\"$bgcolor2\">&nbsp;</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        CloseTable();
    }

/* Finished Banners List */
    if (pnSecAuthAction(0, 'Banners::Banner', '::', ACCESS_READ)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._FINISHEDBANNERS."</b></font></center><br>"
            ."<table width=\"100%\" border=\"0\"><tr>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._ID."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._IMP."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKS."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKSPERCENT."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._DATESTARTED."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._DATEENDED."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLIENTNAME."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._FUNCTIONS."</b></td><tr>";
         $column = $pntable['bannerfinish_column'];
         $column2 = $pntable['bannerclient_column'];
         $result = $dbconn->Execute("SELECT $column[bid],
                                          $column[cid],
                                          $column[impressions],
                                          $column[clicks],
                                          $column[datestart],
                                          $column[dateend],
                                          $column2[name]
                                   FROM $pntable[bannerfinish],
                                        $pntable[bannerclient]
                                   WHERE $column[cid] = ".pnVarPrepForStore($column2['cid'])."
                                   ORDER BY $column[bid]");
        while(list($bid, $cid, $impressions, $clicks, $datestart, $dateend, $name) = $result->fields) {

            $result->MoveNext();
            // jgm - get and use clientname
            if (pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_READ)) {
                $percent = substr(100 * $clicks / $impressions, 0, 5);
                echo "<tr>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($bid) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($impressions) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($clicks) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($percent) . "%</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($datestart) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($dateend) . "</td>"
                    ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($name) . "</td>";
                if (pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_DELETE)) {
                    echo "<td bgcolor=\"$bgcolor2\" align=\"center\"><a href=\"admin.php?module="
                    .$GLOBALS['module']."&amp;op=BannerFinishDelete&amp;bid=$bid&amp;authid=" . pnSecGenAuthKey() . "\">"._DELETE."</a></td>";
                } else {
                    echo "<td bgcolor=\"$bgcolor2\">&nbsp;</td>";
                }
                echo "</tr>";
            }
        }
       echo "</table>";
       CloseTable();
    }

    /* Clients List */
    if (pnSecAuthAction(0, 'Banners::Client', '::', ACCESS_READ)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ADVERTISINGCLIENTS."</b></font></center><br>"
            ."<table width=\"100%\" border=\"0\"><tr>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._ID."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLIENTNAME."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._ACTIVEBANNERS2."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CONTACTNAME."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CONTACTEMAIL."</b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._FUNCTIONS."</b></td><tr>";
        $column = $pntable['bannerclient_column'];
        $result = $dbconn->Execute("SELECT $column[cid],
                                         $column[name],
                                         $column[contact], 
                                         $column[email] 
                                  FROM $pntable[bannerclient]
                                  ORDER BY $column[cid]");

        while(list($cid, $name, $contact, $email) = $result->fields) {
            $result2 = $dbconn->Execute("SELECT COUNT(*) 
                                       FROM $pntable[banner] 
                                       WHERE {$pntable['banner_column']['cid']}=".pnVarPrepForStore($cid)."");
            list($numrows) = $result2->fields;
            echo "<tr>"
                ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($cid) . "</td>"
                ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($name) . "</td>"
                ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($numrows) . "</td>"
                ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($contact) . "</td>"
                ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($email) . "</td>";
            if (pnSecAuthAction(0, 'Banners::Client', "$name::$cid", ACCESS_EDIT)) {
                echo "<td bgcolor=\"$bgcolor2\" align=\"center\"><a href=\"admin.php?module="
                .$GLOBALS['module']."&amp;op=BannerClientEdit&amp;cid=$cid\">"._EDIT."</a>";
                if (pnSecAuthAction(0, 'Banners::Client', "$name::$cid", ACCESS_DELETE)) {
                    echo " | <a href=\"admin.php?module=".$GLOBALS['module']
                    ."&amp;op=BannerClientDelete&amp;cid=$cid\">"._DELETE."</a></td></tr>";
                } else {
                    echo "</td></tr>";
                }
            } else {
                echo "<td bgcolor=\"$bgcolor2\">&nbsp;</td></tr>";
            }
            $result->MoveNext();
        }
        echo "</table>";
        CloseTable();
    }

    /* Add Banner */
    if (pnSecAuthAction(0, 'Banners::Banner', '::', ACCESS_ADD)) {
        $column = $pntable['bannerclient_column'];
        $result = $dbconn->Execute("SELECT $column[cid], $column[name] 
                                  FROM $pntable[bannerclient]");
        if(!$result->EOF) {
            OpenTable();
            echo "<font class=\"pn-title\"><b>"._ADDNEWBANNER."</b></font></center><br><br>"
                ."<form action=\"admin.php\" method=\"post\">"
                .""._CLIENTNAME.":"
                ."<select name=\"cid\">";

            while(list($cid, $name) = $result->fields) {
                echo "<option value=\"$cid\">". pnVarPrepForDisplay($name) . "</option>";
                $result->MoveNext();
            }
            echo "</select><br>"
                    .""._PURCHASEDIMPRESSIONS.": <input type=\"text\" name=\"imptotal\" size=\"12\" maxlength=\"11\"> 0 = "._UNLIMITED."<br>"
                .""._BANTYPE.": <input type=\"text\" name=\"type\" size=\"2\" maxlength=\"2\"><br>"
                .""._IMAGEURL.": <input type=\"text\" name=\"imageurl\" size=\"50\" maxlength=\"250\"><br>"
                .""._CLICKURL.": <input type=\"text\" name=\"clickurl\" size=\"50\" maxlength=\"250\"><br>"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"op\" value=\"BannersAdd\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"submit\" value=\""._ADDBANNER."\">"
                ."</form>";
            CloseTable();
        }
    }

    /* Add Client */
    if (pnSecAuthAction(0, 'Banners::Client', '::', ACCESS_ADD)) {
        OpenTable();
        echo"<font class=\"pn-title\"><b>"._ADDCLIENT."</b></center><br><br>
            <form action=\"admin.php\" method=\"post\">
            "._CLIENTNAME.": <input type=\"text\" name=\"name\" size=\"30\" maxlength=\"60\"><br>
            "._CONTACTNAME.": <input type=\"text\" name=\"contact\" size=\"30\" maxlength=\"60\"><br>
            "._CONTACTEMAIL.": <input type=\"text\" name=\"email\" size=\"30\" maxlength=\"60\"><br>
            "._CLIENTLOGIN.": <input type=\"text\" name=\"login\" size=\"12\" maxlength=\"10\"><br>
            "._CLIENTPASSWD.": <input type=\"text\" name=\"passwd\" size=\"12\" maxlength=\"10\"><br><br>
            "._EXTRAINFO.":<br><textarea name=\"extrainfo\" cols=\"60\" rows=\"10\"></textarea><br>"
            ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"BannerAddClient\">"
            ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._ADDCLIENT2."\">"
            ."</form>";
        CloseTable();
    }
// Access Banner Settings
    OpenTable();
    echo "<font class=\"pn-title\"><b>"._BANNERSCONF."</b></font><br /><br />";
    echo "<a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getConfig\">"._BANNERSCONF."</a>";
    CloseTable();
    include ("footer.php");
}

function BannersAdd() {

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($name,
         $cid,
	 $type,
         $imptotal,
         $imageurl,
         $clickurl,
         $clientname) = pnVarCleanFromInput('name',
                                          'cid',
		 			  'type',
                                          'imptotal',
                                          'imageurl',
                                          'clickurl',
                                          'clientname');
    // jgm - get and use clientname
    if(!isset($clientname)) {
        $clientname = '';
    }
    if (!(pnSecAuthAction(0, 'Banners::Banner', "$clientname::", ACCESS_ADD))) {
        include 'header.php';
        echo _BANNERSADDBANNERNOAUTH;
        include 'footer.php';
        exit;
    }
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $column = $pntable['banner_column'];
    $result = $dbconn->Execute("INSERT INTO $pntable[banner] 
                                ($column[bid], $column[cid], $column[type], $column[imptotal], 
                                $column[impmade], $column[clicks], $column[imageurl],
                                $column[clickurl], $column[date]) 
                              VALUES (NULL, '".pnVarPrepForStore($cid)."', '".pnVarPrepForStore($type)."', '".pnVarPrepForStore($imptotal)."', '1', '0', 
                                '".pnVarPrepForStore($imageurl)."', '".pnVarPrepForStore($clickurl)."', now())");
    if($dbconn->ErrorNo()<>0) {
        error_log("Error: " . $dbconn->ErrorMsg());
    }
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
}

function BannerAddClient()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($name,
         $contact,
         $email,
         $login,
         $passwd,
         $extrainfo) = pnVarCleanFromInput('name',
                                           'contact',
                                           'email',
                                           'login',
                                           'passwd',
                                           'extrainfo');

    if (!(pnSecAuthAction(0, 'Banners::Client', '::', ACCESS_ADD))) {
        include 'header.php';
        echo _BANNERSADDCLIENTNOAUTH;
        include 'footer.php';
        exit;
    }


    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $column = $pntable['bannerclient_column'];
    $result = $dbconn->Execute("INSERT INTO $pntable[bannerclient] 
                               ($column[cid], $column[name], $column[contact], 
                                $column[email], $column[login], $column[passwd], 
                                $column[extrainfo]) 
                                VALUES (NULL, '".pnVarPrepForStore($name)."', '".pnVarPrepForStore($contact)."', '".pnVarPrepForStore($email)."', '".pnVarPrepForStore($login)."', 
                                '".pnVarPrepForStore($passwd)."', '".pnVarPrepForStore($extrainfo)."')");
    if($dbconn->ErrorNo()<>0) {
        error_log("Error: " . $dbconn->ErrorMsg());
    }
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
}

function BannerFinishDelete()
{    
    $bid = pnVarCleanFromInput('bid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $bannercolumn = &$pntable['banner_column'];
    $bannerclientcolumn =  &$pntable['bannerclient_column'];

    $result = $dbconn->Execute("SELECT $bannerclientcolumn[name]
                              FROM $pntable[banner], $pntable[bannerclient]
                              WHERE $bannercolumn[bid] = 1
                                AND $bannercolumn[cid] = ".pnVarPrepForStore($bannerclientcolumn['cid'])."");
    list($clientname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_DELETE)) {
        include 'header.php';
        echo _BANNERSDELBANNERNOAUTH;
        include 'footer.php';
        return;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $result = $dbconn->Execute("DELETE FROM $pntable[bannerfinish] 
                              WHERE {$pntable[bannerfinish_column][bid]}=".pnVarPrepForStore($bid)."");
    if($dbconn->ErrorNo()<>0) {
        error_log("Error: " . $dbconn->ErrorMsg());
    }

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
}

function BannerDelete()
{
    list($bid,
         $ok) = pnVarCleanFromInput('bid',
                                    'ok');
    if (!isset($ok)) {
        $ok = 0;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $bannercolumn = &$pntable['banner_column'];
    $bannerclientcolumn =  &$pntable['bannerclient_column'];

    $result = $dbconn->Execute("SELECT $bannerclientcolumn[name]
                              FROM $pntable[banner], $pntable[bannerclient]
                              WHERE $bannercolumn[bid] = 1
                                AND $bannercolumn[cid] = ".pnVarPrepForStore($bannerclientcolumn['cid'])."");
    list($clientname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_DELETE)) {
        include 'header.php';
        echo _BANNERSDELBANNERNOAUTH;
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

        $column = $pntable['banner_column'];
        $result = $dbconn->Execute("DELETE FROM $pntable[banner] 
                                  WHERE {$pntable['banner_column']['bid']}='".pnVarPrepForStore($bid)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("Error: " . $dbconn->ErrorMsg());
        } 

        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
    } else {
        include("header.php");
    	$bgcolor2 = $GLOBALS['bgcolor2'];
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._BANNERSADMIN."</b></font></center>";
        CloseTable();
        $column = $pntable['banner_column'];
        $column2 = $pntable['bannerclient_column'];
        $result = $dbconn->Execute("SELECT $column[cid], $column[imptotal], 
                                    $column[impmade], $column[clicks], 
                                    $column[imageurl], $column[clickurl],
                                    $column2[name]
                                  FROM $pntable[banner], $pntable[bannerclient] 
                                  WHERE $column[bid]=".pnVarPrepForStore($bid)." 
                                    AND $column[cid] = ".pnVarPrepForStore($column2['cid'])."");
        list($cid, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $name) = $result->fields;
        OpenTable();
        echo "<center><b>"._DELETEBANNER."</b><br><br>"
            ."<a href=\"$clickurl\"><img src=\"$imageurl\" border=\"1\" alt=\"\"></a><br>"
            ."<a href=\"$clickurl\">$clickurl</a><br><br>"
            ."<table width=\"100%\" border=\"0\"><tr>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._ID."<b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._IMPRESSIONS."<b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._IMPLEFT."<b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKS."<b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLICKSPERCENT."<b></td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\"><b>"._CLIENTNAME."<b></td><tr>";
        $percent = substr(100 * $clicks / $impmade, 0, 5);
        if($imptotal==0) {
            $left = _UNLIMITED;
        } else {
            $left = $imptotal-$impmade;
        }
        echo "<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($bid) . "</td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($impmade) . "</td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($left) . "</td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($clicks) . "</td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($percent) . "%</td>"
            ."<td bgcolor=\"$bgcolor2\" align=\"center\">" . pnVarPrepForDisplay($name) . "</td><tr>";
        echo "</td></tr></table><br>"
        .""._SURETODELBANNER."<br><br>"
        ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=BannersAdmin\">"
        ._NO."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=BannerDelete&amp;bid=$bid&amp;ok=1&amp;authid=" . pnSecGenAuthKey() . "\">"
        ._YES."</a> ]</center><br><br>";
        CloseTable();
        include("footer.php");
    }
}

function BannerEdit($bid) {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._BANNERSADMIN."</b></font></center>";
    CloseTable();

    $column = $pntable['banner_column'];
    $column2 = $pntable['bannerclient_column'];
    $result = $dbconn->Execute("SELECT $column[cid], $column[type], $column[imptotal], 
                                $column[impmade], $column[clicks], $column[imageurl],
                                $column[clickurl], $column2[name]
                              FROM $pntable[banner], $pntable[bannerclient]
                              WHERE $column[bid]=".pnVarPrepForStore($bid)."
                                AND $column[cid] = ".pnVarPrepForStore($column2['cid'])."");
    list($cid, $type, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $name) = $result->fields;


    if (!pnSecAuthAction(0, 'Banners::Banner', "$name::$bid", ACCESS_EDIT)) {
        echo _BANNERSEDITBANNERNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo"<font class=\"pn-title\">"
        ."<center><b>"._EDITBANNER."</b><br><br>"
        ."<img src=\"$imageurl\" border=\"1\" alt=\"\"></center><br><br>"
        ."<form action=\"admin.php\" method=\"post\">"
        .""._CLIENTNAME.": "
        ."<select name=\"cid\">";
    echo "<option value=\"$cid\" selected>".pnVarPrepForDisplay($name)."</option>";
    $column = $pntable['banner_column'];
    $result = $dbconn->Execute("SELECT $column2[cid], $column2[name] from 
                           $pntable[bannerclient]");
    while(list($ccid, $name) = $result->fields) {

        $result->MoveNext();
        if($cid!=$ccid) {
            echo "<option value=\"$ccid\">" . pnVarPrepForDisplay($name) . "</option>";
        }
    }
    echo "</select><br>";
    if($imptotal==0) {
        $impressions = _UNLIMITED;
    } else {
        $impressions = $imptotal;
    }
    echo "<br>"._ADDIMPRESSIONS.": <input type=\"text\" name=\"impadded\" size=\"12\" maxlength=\"11\"> "._PURCHASED.": <b>" . pnVarPrepForDisplay($impressions) . "</b> "._MADE.": <b>" . pnVarPrepForDisplay($impmade) . "</b><br>"
        .""._BANTYPE.": <input type=\"text\" name=\"type\" size=\"2\" maxlength=\"2\" value=\"$type\"><br>"
        .""._IMAGEURL.": <input type=\"text\" name=\"imageurl\" size=\"50\" maxlength=\"255\" value=\"$imageurl\"><br>"
        .""._CLICKURL.": <input type=\"text\" name=\"clickurl\" size=\"50\" maxlength=\"255\" value=\"$clickurl\"><br>"
        ."<input type=\"hidden\" name=\"bid\" value=\"$bid\">"
        ."<input type=\"hidden\" name=\"imptotal\" value=\"$imptotal\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"BannerChange\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
        ."</form>";
    CloseTable();
    include("footer.php");
}

function BannerChange() {

    list($bid,
         $cid,
         $type,
         $imptotal,
         $impadded,
         $imageurl,
         $clickurl) = pnVarCleanFromInput('bid',
                                          'cid',
                                          'type',
                                          'imptotal',
                                          'impadded',
                                          'imageurl',
                                          'clickurl');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $imp = $imptotal+$impadded;

    $bannercolumn = &$pntable['banner_column'];
    $bannerclientcolumn =  &$pntable['bannerclient_column'];

    $result = $dbconn->Execute("SELECT $bannerclientcolumn[name]
                              FROM $pntable[banner], $pntable[bannerclient]
                              WHERE $bannercolumn[bid] = 1
                                AND $bannercolumn[cid] = ".pnVarPrepForStore($bannerclientcolumn['cid'])."");
    list($clientname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Banners::Banner', "$clientname::$bid", ACCESS_EDIT)) {
        include 'header.php';
        echo _BANNERSEDITBANNERNOAUTH;
        include 'footer.php';
        return;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $result = $dbconn->Execute("UPDATE $pntable[banner] 
                              SET $bannercolumn[cid]='".pnVarPrepForStore($cid)."',
                                  $bannercolumn[type]='".pnVarPrepForStore($type)."',
                                  $bannercolumn[imptotal]='".pnVarPrepForStore($imp)."', 
                                  $bannercolumn[imageurl]='".pnVarPrepForStore($imageurl)."', 
                                  $bannercolumn[clickurl]='".pnVarPrepForStore($clickurl)."' 
                              WHERE $bannercolumn[bid]=".pnVarPrepForStore($bid)."");
    if($dbconn->ErrorNo()<>0) {
        error_log("Error: " . $dbconn->ErrorMsg());
    } 

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
}

function BannerClientDelete()
{
    list($cid,
         $ok) = pnVarCleanFromInput('cid',
                                    'ok');
    if (!isset($ok)) {
        $ok = 0;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();


    $bannerclientcolumn =  &$pntable['bannerclient_column'];

    $result = $dbconn->Execute("SELECT $bannerclientcolumn[name]
                              FROM $pntable[bannerclient]
                              WHERE $bannerclientcolumn[cid] = ".pnVarPrepForStore($cid)."");
    list($clientname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Banners::Client', "$clientname::$cid", ACCESS_DELETE)) {
        include 'header.php';
        echo _BANNERSDELCLIENTNOAUTH;
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

        $result = $dbconn->Execute("DELETE FROM $pntable[banner] 
                                  WHERE {$pntable['banner_column']['cid']}='".pnVarPrepForStore($cid)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("Error: " . $dbconn->ErrorMsg());
        }
        $result = $dbconn->Execute("DELETE FROM $pntable[bannerclient] 
                                  WHERE {$pntable['bannerclient_column']['cid']}='".pnVarPrepForStore($cid)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("Error: " . $dbconn->ErrorMsg());
        }

        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
    } else {
        include("header.php");
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._BANNERSADMIN."</b></font></center>";
        CloseTable();

        $column = $pntable['bannerclient_column'];
        $result = $dbconn->Execute("SELECT $column[cid], $column[name] 
                                  FROM $pntable[bannerclient] 
                                  WHERE $column[cid]=".pnVarPrepForStore($cid)."");

        list($cid, $name) = $result->fields;
        OpenTable();
        echo "<center><b>"._DELETECLIENT.": $name</b><br><br>
            "._SURETODELCLIENT."<br><br>";
        $column = $pntable['banner_column'];
        $result = $dbconn->Execute("SELECT $column[imageurl], $column[clickurl] 
                                   FROM $pntable[banner] 
                                   WHERE $column[cid]=".pnVarPrepForStore($cid)."");
        if($result->EOF) {
            echo ""._CLIENTWITHOUTBANNERS."<br><br>";
        } else {
            echo "<b>"._WARNING."!!!</b><br>
                "._DELCLIENTHASBANNERS.":<br><br>";
        }

        while(list($imageurl, $clickurl) = $result->fields) {
            echo "<a href=\"$clickurl\"><img src=\"$imageurl\" border=\"1\" alt=\"\"></a><br>
                <a href=\"$clickurl\">$clickurl</a><br><br>";
            $result->MoveNext();
        }
        echo ""._SURETODELCLIENT."<br><br>
        [ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=BannersAdmin\">"
        ._NO."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=BannerClientDelete&amp;cid=$cid&amp;ok=1&amp;authid=" . pnSecGenAuthKey() . "\">"
        ._YES."</a> ]</center><br><br></center>";
        CloseTable();
        include("footer.php");
    }
}

function BannerClientEdit()
{
    list($cid,
    	 $clientname) = pnVarCleanFromInput('cid',
    	 				    'clientname');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._BANNERSADMIN."</b></font></center>";
    CloseTable();

    $column = $pntable['bannerclient_column'];
    $result = $dbconn->Execute("SELECT $column[name],
                                     $column[contact],
                                     $column[email],
                                     $column[login],
                                     $column[passwd],
                                     $column[extrainfo] 
                              FROM $pntable[bannerclient] 
                              WHERE $column[cid]=".pnVarPrepForStore($cid)."");
    list($name, $contact, $email, $login, $passwd, $extrainfo) = $result->fields;

    if(!isset($clientname)) {
        $clientname = '';
    }
    if (!pnSecAuthAction(0, 'Banners::Client', "$clientname::$cid", ACCESS_EDIT)) {
        echo _BANNERSEDITCLIENTNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EDITCLIENT."</b></font></center><br><br>"
        ."<form action=\"admin.php\" method=\"post\">"
        .""._CLIENTNAME.": <input type=\"text\" name=\"name\" value=\"$name\" size=\"30\" maxlength=\"60\"><br>"
        .""._CONTACTNAME.": <input type=\"text\" name=\"contact\" value=\"$contact\" size=\"30\" maxlength=\"60\"><br>"
        .""._CONTACTEMAIL.": <input type=\"text\" name=\"email\" size=30 maxlength=\"60\" value=\"$email\"><br>"
        .""._CLIENTLOGIN.": <input type=\"text\" name=\"login\" size=12 maxlength=\"10\" value=\"$login\"><br>"
        .""._CLIENTPASSWD.": <input type=\"text\" name=\"passwd\" size=12 maxlength=\"10\" value=\"$passwd\"><br><br>"
        .""._EXTRAINFO."<br><textarea name=\"extrainfo\" cols=\"60\" rows=\"10\">$extrainfo</textarea><br>"
        ."<input type=\"hidden\" name=\"cid\" value=\"$cid\">"
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"BannerClientChange\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
        ."</form>";
    CloseTable();

    include 'footer.php';
}

function BannerClientChange()
{
    list($cid,
         $name,
         $contact,
         $email,
         $extrainfo,
         $login,
         $passwd) = pnVarCleanFromInput('cid',
                                        'name',
                                        'contact',
                                        'email',
                                        'extrainfo',
                                        'login',
                                        'passwd');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = $pntable['bannerclient_column'];

    // NB - authorisation is against *OLD* client name
    $bannerclientcolumn =  &$pntable['bannerclient_column'];

    $result = $dbconn->Execute("SELECT $bannerclientcolumn[name]
                              FROM $pntable[bannerclient]
                              WHERE $bannerclientcolumn[cid] = ".pnVarPrepForStore($cid)."");
    list($clientname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Banners::Client', "$clientname::$cid", ACCESS_EDIT)) {
        include 'header.php';
        echo _BANNERSEDITCLIENTNOAUTH;
        include 'footer.php';
        return;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $result = $dbconn->Execute("UPDATE $pntable[bannerclient] 
                              SET $column[name]='".pnVarPrepForStore($name)."', $column[contact]='".pnVarPrepForStore($contact)."',
                                $column[email]='".pnVarPrepForStore($email)."', $column[extrainfo]='".pnVarPrepForStore($extrainfo)."', 
				$column[login]='".pnVarPrepForStore($login)."', $column[passwd]='".pnVarPrepForStore($passwd)."' WHERE $column[cid]=".pnVarPrepForStore($cid)."");
    if($dbconn->ErrorNo()<>0) {
        error_log("Error: " . $dbconn->ErrorMsg());
    }

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=BannersAdmin');
}

function banners_admin_getConfig()
{
    include 'header.php';
    $bgcolor2 = $GLOBALS["bgcolor2"];

    // prepare vars
    $sel_banners['0'] = '';
    $sel_banners['1'] = '';
    $sel_banners[pnConfigGetVar('banners')] = ' checked';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._BANNERSCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._ACTBANNERS.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xbanners\" value=\"1\" class=\"pn-normal\" ".$sel_banners['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xbanners\" value=\"0\" class=\"pn-normal\" ".$sel_banners['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._YOURIP.':</td><td>'
        ."<input type=\"text\" name=\"xmyIP\" value=\"".pnConfigGetVar('myIP')."\" size=\"30\" class=\"pn-normal\">"
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();

    include 'footer.php';
}

function banners_admin_setConfig($var)
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
    while(list ($key, $val) = each ($var)) {
        if (substr($key, 0, 1) == 'x') {
            pnConfigSetVar(substr($key, 1), $val);
        }
    }
    pnRedirect('admin.php');
}

function banners_admin_main($var)
{
    list($op,
    	 $bid) = pnVarCleanFromInput('op',
    	 			     'bid');

   extract($var);

    if (!(pnSecAuthAction(0, 'Banners::Banner', '::', ACCESS_READ))) {
        include 'header.php';
        echo _BANNERSNOAUTH;
        include 'footer.php';
    } else {
        switch($op) {

            case "BannersAdmin":
                BannersAdmin();
                break;

            case "BannersAdd":
                BannersAdd();
                break;

            case "BannerAddClient":
                BannerAddClient();
                break;

            case "BannerFinishDelete":
                BannerFinishDelete();
                break;

            case "BannerDelete":
                BannerDelete();
                break;

            case "BannerEdit":
                BannerEdit($bid);
                break;

            case "BannerChange":
                BannerChange();
                break;

            case "BannerClientDelete":
                BannerClientDelete();
                break;

            case "BannerClientEdit":
                BannerClientEdit();
                break;
    
            case "BannerClientChange":
                BannerClientChange();
                break;
            
            case "getConfig":
                 banners_admin_getConfig();
                 break;

            case "setConfig":
                 banners_admin_setConfig($var);
                 break;

            default:
               BannersAdmin();
               break;
       }
   }
}
?>
