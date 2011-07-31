<?php
// File: $Id: admin.php,v 1.2 2002/11/04 22:25:03 tanis Exp $ $Name:  $
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

if (!eregi("admin.php", $PHP_SELF))
{
	die ("Access Denied");
}

$ModName = $module;

if (!(pnSecAuthAction(0, 'Referers::', '::', ACCESS_ADMIN)))
{
      include 'header.php';
      echo _REFERERSNOAUTH;
      include 'footer.php';
}

modules_get_language();
modules_get_manual();

/*********************************************************/
/* Referer Functions to know who links to us             */
/*********************************************************/

function referers_admin_main()
{
    include ("header.php");

    $bgcolor2 = $GLOBALS["bgcolor2"];

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._HTTPREFERERS."</b></font></center>";
    CloseTable();

    if (!(pnSecAuthAction(0, 'Referers::', '::', ACCESS_ADMIN))) {
        echo _REFERERSNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<center><b>"._WHOLINKS."</b></center><br /><br />"
    ."<table border=0 width=100%> <tr><td>"._FREQUENCY."</td><td>"._URL."</td><td>"._PERCENT."</td></tr>";
    /**
     * fifers: grab the total count of referers for percentage calculations
     */
    $column = &$pntable['referer_column'];
    $hresult = $dbconn->Execute("SELECT SUM($column[frequency]) FROM $pntable[referer]");
    list($totalfreq) = $hresult->fields;
    $hresult = $dbconn->Execute("SELECT $column[url], $column[frequency] FROM $pntable[referer] ORDER BY $column[frequency] DESC");
    while(list($url, $freq) = $hresult->fields) {

        $hresult->MoveNext();
        echo "<tr>\n"
            ."<td bgcolor=\"$bgcolor2\">" . pnVarPrepForDisplay($freq) . "</td>\n"
            ."<td bgcolor=\"$bgcolor2\">".(($url == "bookmark")?(""):("<a target=_blank href=$url>")).pnVarPrepForDisplay($url).(($url == "bookmark")?(""):("</a>"))."</td>\n"
            ."<td bgcolor=\"$bgcolor2\">".round(($freq / $totalfreq * 100), 2)." %</td>\n"
            ."</tr>\n";
    }
    echo "</table>"._TOTAL." " . pnVarPrepForDisplay($totalfreq) . " <br />"
    ."<form action=\"admin.php\" method=\"post\">"
    ."<input type=\"hidden\" name=\"module\" value=\"NS-Referers\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"hidden\" name=\"op\" value=\"delete\">"
    ."<center><input type=\"submit\" value=\""._DELETEREFERERS."\"></center>";
    CloseTable();

// Access Referer Settings
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._REFERERSCONF."</b></font></center><br /><br />";
    echo "<center><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getConfig\">"._REFERERSCONF."</a></center>";
    CloseTable();

    include ("footer.php");
}

function referers_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_httpref['0'] = '';
    $sel_httpref['1'] = '';
    $sel_httpref[pnConfigGetVar('httpref')] = ' checked';
    $sel_httprefmax['100'] = '';
    $sel_httprefmax['250'] = '';
    $sel_httprefmax['500'] = '';
    $sel_httprefmax['1000'] = '';
    $sel_httprefmax['2000'] = '';
    $sel_httprefmax[pnConfigGetVar('httprefmax')] = ' selected';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._REFERERSCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._ACTIVATEHTTPREF.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xhttpref\" value=\"1\" class=\"pn-normal\"".$sel_httpref['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xhttpref\" value=\"0\" class=\"pn-normal\"".$sel_httpref['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._MAXREF.'</td><td>'
        .'<select name="xhttprefmax" size="1" class="pn-normal">'
        ."<option value=\"100\"".$sel_httprefmax['100'].">100</option>\n"
        ."<option value=\"250\"".$sel_httprefmax['250'].">250</option>\n"
        ."<option value=\"500\"".$sel_httprefmax['500'].">500</option>\n"
        ."<option value=\"1000\"".$sel_httprefmax['1000'].">1000</option>\n"
        ."<option value=\"1000\"".$sel_httprefmax['2000'].">2000</option>\n"
        .'</select>'
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function referers_admin_setConfig($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array (
        );

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
    // $var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array (
    );
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

function referers_admin_delete($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    if (!(pnSecAuthAction(0, 'Referers::', '::', ACCESS_ADMIN)))
    {
	include 'header.php';
	echo _REFERERSDELNOAUTH;
	include 'footer.php';
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DELETE FROM $pntable[referer]");
    pnRedirect('admin.php');
}
?>