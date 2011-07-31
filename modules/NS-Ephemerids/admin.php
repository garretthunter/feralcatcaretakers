<?php
// File: $Id: admin.php,v 1.2 2002/10/31 21:06:50 tanis Exp $ $Name:  $
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

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

$ModName = $module;
modules_get_language();
modules_get_manual();

/**
 * Ephemerids Functions to have a Historic Ephemerids
 */

function Ephemerids()
{
    include 'header.php';
    $bgcolor1 = $GLOBALS['bgcolor1'];
    list($elanguage) = pnVarCleanFromInput('elanguage');
    

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Ephemerids::', '::', ACCESS_EDIT)) {
        echo _EPHEMNOAUTH;
        include 'footer.php';
        return;
    }

    if (pnSecAuthAction(0, 'Ephemerids::', '::', ACCESS_ADD)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ADDEPHEM."</b></font></center><br>"
            ."<form action=\"admin.php\" method=\"post\">";
        $nday = "1";
        echo ""._DAY.": <select name=\"did\">";
        while ($nday<=31) {
            echo "<option name=\"did\">$nday</option>";
            $nday++;
        }
        echo "</select>";
        $nmonth = "1";
        echo ""._UMONTH.": <select name=\"mid\">";
        while ($nmonth<=12) {
            echo "<option name=\"mid\">$nmonth</option>";
            $nmonth++;
        }
        echo "</select>"._YEAR.": <input type=\"text\" name=\"yid\" maxlength=\"4\" size=\"5\"><br><br>"
            .'<b>'._LANGUAGE.': </b>'
            .'<select name="elanguage" size="1">';
        $lang = languagelist();
        if (!isset($elanguage))
        {
            $sel_lang[0] = ' selected';
        } else {
			$sel_lang[0] = '';
            $sel_lang[$elanguage] = ' selected';
        }
        print "<option value=\"\" $sel_lang[0]>"._ALL.'</option>';
        $handle = opendir('language');
        while ($f = readdir($handle))
        {
            if (is_dir("language/$f") && (!empty($lang[$f])))
            {
                $langlist[$f] = $lang[$f];
            }
        }
        asort($langlist);
        foreach ($langlist as $k=>$v) {
        echo '<option value="'.$k.'"';
        if (isset($sel_lang[$k])) {
            echo ' selected';
        }
        echo '>'. $v . '</option>\n';
        }
		echo "</select>";
        print '<br><br>' /* ML END */
              ."<b>"._EPHEMDESC.":</b><br>"
              ."<textarea name=\"content\" cols=\"60\" rows=\"10\"></textarea><br><br>"
              ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
              ."<input type=\"hidden\" name=\"op\" value=\"Ephemeridsadd\">"
	      ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
              ."<input type=\"submit\" value=\""._OK."\">"
              ."</form>";
        CloseTable();
    }

    if (pnSecAuthAction(0, 'Ephemerids::', '::', ACCESS_EDIT)) {
        OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._EPHEMMAINT."</b></font></center><br>"
            ."<center><form action=\"admin.php\" method=\"post\">";
        $nday = "1";
        echo ""._DAY.": <select name=\"did\">";
        while ($nday<=31) {
            echo "<option name=\"did\">$nday</option>";
            $nday++;
        }
        echo "</select>";
        $nmonth = "1";
        echo ""._UMONTH.": <select name=\"mid\">";
        while ($nmonth<=12) {
            echo "<option name=\"mid\">$nmonth</option>";
            $nmonth++;
        }
        echo "</select>"
             ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
            ."<input type=\"hidden\" name=\"op\" value=\"Ephemeridsmaintenance\">"
	    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<input type=\"submit\" value=\""._EDIT."\">"
            ."</form></center>";
        CloseTable();
    }

    OpenTable();
    echo "<br>";
    echo "<center><b>Current Ephemerids</b></center><br>";
    echo "<center><table border=\"1\" width=\"100%\" bgcolor=\"$bgcolor1\"><tr>"
        ."<td><b>&nbsp;&nbsp;Day&nbsp;&nbsp;</b></td>"
        ."<td><b>&nbsp;&nbsp;Month&nbsp;&nbsp;</b></td>"
        ."<td><b>&nbsp;&nbsp;Year&nbsp;&nbsp;</b></td>"
        ."<td><b>&nbsp;&nbsp;Event Name</b></td>"
        ."<td colspan=2>Modify</td></tr>";
    $result=$dbconn->Execute(buildSimpleQuery('ephem', array ('eid', 'did', 'mid', 'yid', 'content', 'elanguage')));
    while(list($eid, $did, $mid, $yid, $content, $elanguage) = $result->fields) {

        $result->MoveNext();

        if (pnSecAuthAction(0, 'Ephemerids::', "$content:$eid", ACCESS_EDIT)) {
            echo "<tr><td align=\"center\">". pnVarPrepForDisplay($did)."</td>"
                ."<td align=\"center\">". pnVarPrepForDisplay($mid)."</td>"
                ."<td align=\"center\">". pnVarPrepForDisplay($yid)."</td>"
                ."<td width=\"100%\">&nbsp;&nbsp;". pnVarPrepHTMLDisplay(nl2br($content))."</td>";
            if (pnSecAuthAction(0, 'Ephemerids::', "$content:$eid", ACCESS_EDIT)) {

		$authid = pnSecGenAuthKey();

                echo "<td align=\"center\"><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=Ephemeridsedit&eid=$eid&did=$did&mid=$mid&amp;authid=$authid\">"._EDIT."</td>";
                if (pnSecAuthAction(0, 'Ephemerids::', "$content::$eid", ACCESS_DELETE)) {
                    echo "<td align=\"center\"><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=Ephemeridsdel&eid=$eid&did=$did&mid=$mid&amp;authid=$authid\">"._DELETE."</td>";
                } else {
                    echo "<td>&nbsp;</td>";
                }
            } else {
                echo "<td>&nbsp;</td><td>&nbsp;</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table>";
    CloseTable();

    include 'footer.php';
}

function Ephemeridsadd()
{
   list($did,
	$mid,
	$yid,
	$content,
	$elanguage) = pnVarCleanFromInput('did',
					  'mid',
					  'yid',
					  'content',
					  'elanguage');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Ephemerids::', "$content::", ACCESS_ADD)) {
        include "header.php";
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
        CloseTable();

	echo _EPHEMADDNOAUTH;
        include "footer.php";
        return;
    }
    $column =&$pntable['ephem_column'];
    $nextid = $dbconn->GenId($pntable['ephem']);
    $dbconn->Execute("INSERT INTO $pntable[ephem] ($column[eid], $column[did], $column[mid], $column[yid], $column[content], $column[elanguage]) VALUES (
    $nextid,
        '" .pnVarPrepForStore($did). "',
        '" .pnVarPrepForStore($mid). "',
        '" .pnVarPrepForStore($yid). "',
        '" .pnVarPrepForStore($content). "',
        '" .pnVarPrepForStore($elanguage). "'
    )");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=Ephemerids');
}

function Ephemeridsmaintenance()
{
    list($did,
	 $mid) = pnVarCleanFromInput('did',
				     'mid');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $authid = pnSecGenAuthKey();

    include 'header.php';

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Ephemerids::', '::', ACCESS_EDIT)) {
        echo _EPHEMEDITNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EPHEMMAINT." $did/$mid</b></font></center><br>";

    $column = &$pntable['ephem_column'];
    $result=$dbconn->Execute("SELECT $column[eid], $column[did], $column[mid], $column[yid], $column[content], $column[elanguage] " .
    			     "FROM $pntable[ephem] " .
    			     "WHERE $column[did]=" . pnVarPrepForStore($did) . " AND $column[mid]=" . pnVarPrepForStore($mid));
    while(list($eid, $did, $mid, $yid, $content, $elanguage) = $result->fields) {
	$result->MoveNext();
        echo "<font class=\"pn-normal\"><b>$yid</b> - ";
    if (!empty($elanguage)) {
        echo "($elanguage) - ";
    }
        if (pnSecAuthAction(0, 'Ephemerids::', "$content::$eid", ACCESS_EDIT)) {
        echo "[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=Ephemeridsedit&amp;eid=$eid&amp;did=$did&amp;mid=$mid&amp;authid=$authid\">"._EDIT."</a> ";
            if (pnSecAuthAction(0, 'Ephemerids::', "$content::$eid", ACCESS_DELETE)) {
                echo " | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=Ephemeridsdel&amp;eid=$eid&amp;did=$did&amp;mid=$mid&amp;authid=$authid\">"
                ._DELETE."</a> ]";
            } else {
                echo ' ]';
            }
        }
        echo "<br><font class=\"pn-sub\">". pnVarPrepHTMLDisplay(nl2br($content))."</font><br><br><br>";
    }

    CloseTable();
    include 'footer.php';
}

function Ephemeridsdel()
{
    list($eid,
	 $did,
	 $mid) = pnVarCleanFromInput('eid',
				     'did',
				     'mid');

    list($authid) = pnVarCleanFromInput('authid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['ephem_column'];

    $result = $dbconn->Execute("SELECT $column[content]
                              FROM $pntable[ephem]
                              WHERE $column[eid]=" . pnVarPrepForStore($eid));
    list($content) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Ephemerids::', "$content::$eid", ACCESS_DELETE)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
        CloseTable();

        echo _EPHEMDELETENOAUTH;
        include 'footer.php';
        return;
    }

    $dbconn->Execute("DELETE FROM $pntable[ephem] WHERE $column[eid]=" . pnVarPrepForStore($eid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=Ephemeridsmaintenance&did='.$did.'&mid='.$mid);
}

function Ephemeridsedit()
{
    list($eid,
	 $did,
	 $mid) = pnVarCleanFromInput('eid',
				     'did',
				     'mid');

    list($authid) = pnVarCleanFromInput('authid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['ephem_column'];
    $result=$dbconn->Execute("SELECT $column[yid], $column[content], $column[elanguage] 
    				FROM $pntable[ephem] 
    				WHERE $column[eid]=".pnVarPrepForStore($eid)."");
    list($yid, $content, $elanguage) = $result->fields;

    if (!pnSecAuthAction(0, 'Ephemerids::', "$content::$eid", ACCESS_EDIT)) {
        echo _EPHEMEDITNOAUTH;
        include "footer.php";
        return;
    }
    OpenTable();
    echo "<center><font size=4><b>"._EPHEMEDIT."</b></font></center><br>"
    ."<form action=\"admin.php\" method=\"post\">";

    $nday = "1";
    echo ""._DAY.": <select name=\"did\">";
    while ($nday<=31) {
        echo "<option name=\"did\" ";
	    if (pnVarPrepForDisplay($did) == $nday) {
		 echo "SELECTED";
	    }
	echo ">$nday</option>";
        $nday++;
    }
    echo "</select>&nbsp;&nbsp;";
    $nmonth = "1";
    echo ""._UMONTH.": <select name=\"mid\">";
    while ($nmonth<=12) {
        echo "<option name=\"mid\" ";
	    if (pnVarPrepForDisplay($mid) == $nmonth) {
		 echo "SELECTED";
	    }
	echo ">$nmonth</option>";
        $nmonth++;
    }
    echo "</select>&nbsp;&nbsp;"
    ."<b>"._YEAR.":</b> <input type=\"text\" name=\"yid\" value=\"". pnVarPrepForDisplay($yid)."\" maxlength=\"4\" size=\"5\"><br><br>"
        .'<b>'._LANGUAGE.': </b>' /* ML Added drop down to select one of the available languages, currentlang is pre-selected */
        .'<select name="elanguage" size="1">'   /* we could also set admlanguage (admin) to be default selected instead of currentlang */
    ;
    $lang = languagelist();
    if (!$elanguage) {
        $sel_lang[0] = ' selected';
    } else {
	$sel_lang[0] = '';
	$sel_lang[$elanguage] = ' selected';
    }
    print "<option value=\"\" $sel_lang[0]>"._ALL.'</option>';
    $handle = opendir('language');
    while ($f = readdir($handle)) {
        if (is_dir("language/$f") && (!empty($lang[$f]))) {
            $langlist[$f] = $lang[$f];
        }
    }
    asort($langlist);
    foreach ($langlist as $k=>$v) {
    echo '<option value="'.$k.'"';
    if (isset($sel_lang[$k])) {
        echo ' selected';
    }
    echo '>'. $v . '</option>\n';
    }
    echo "</select>";
    print '<br><br>' /* ML END */
    ."<b>"._EPHEMDESC."</b><br>"
    ."<textarea name=\"content\" cols=\"60\" rows=\"10\">". pnVarPrepHTMLDisplay($content)."</textarea><br><br>"
    ."<input type=\"hidden\" name=\"eid\" value=\"". pnVarPrepForDisplay($eid)."\">"
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"Ephemeridschange\">"
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    ."<input type=\"submit\" value=\""._SAVECHANGES."\">"
    ."</form>";
    CloseTable();
    include 'footer.php';
}

function Ephemeridschange()
{
    list($eid,
	 $did,
	 $mid,
	 $yid,
	 $content,
	 $elanguage) = pnVarCleanFromInput('eid',
					   'did',
					   'mid',
					   'yid',
					   'content',
					   'elanguage');
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();

    $pntable = pnDBGetTables();
    $column = &$pntable['ephem_column'];
    $result = $dbconn->Execute("SELECT $column[content]
                              FROM $pntable[ephem]
                              WHERE $column[eid]=".pnVarPrepForStore($eid)."");
    list($oldcontent) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Ephemerids::', "$oldcontent::$eid", ACCESS_EDIT)) {
        include "header.php";
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
        CloseTable();

        echo _EPHEMEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $dbconn->Execute("UPDATE $pntable[ephem] SET $column[yid]='". pnVarPrepForStore($yid)."', $column[content]='". pnVarPrepForStore($content)."', $column[elanguage]='". pnVarPrepForStore($elanguage)."', $column[did]='". pnVarPrepForStore($did)."', $column[mid]='".pnVarPrepForStore($mid)."' WHERE $column[eid]=".pnVarPrepForStore($eid)."");

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=Ephemerids');
}

function ephemerids_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   if (!pnSecAuthAction(0, 'Ephemerids::', '::', ACCESS_EDIT)) {
       include 'header.php';
       GraphicAdmin();
       OpenTable();
       echo "<center><font class=\"pn-title\"><b>"._EPHEMADMIN."</b></font></center>";
       CloseTable();

       echo _EPHEMNOAUTH;
       include 'footer.php';

   } else {
       switch($op) {

        case "Ephemeridsedit":
            Ephemeridsedit();
            break;

        case "Ephemeridschange":
            Ephemeridschange();
            break;

        case "Ephemeridsdel":
            Ephemeridsdel();
            break;

        case "Ephemeridsmaintenance":
            Ephemeridsmaintenance();
            break;

        case "Ephemeridsadd":
            Ephemeridsadd();
            break;

        case "Ephemerids":
            Ephemerids();
            break;

        default:
           Ephemerids();
           break;
       }
   }
}
?>