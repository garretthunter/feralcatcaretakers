<?php
// File: $Id: banners.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Original Author of file: Francisco Burzi
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Function to display banners in all pages
 */

if (!function_exists('pnInit')) {
    include 'includes/pnAPI.php';
    pnInit();
    include 'includes/legacy.php';
// eugenio themeover 20020413
//    pnThemeLoad();
}
/**
 * Load lang file
 */

if(file_exists("language/".pnVarPrepForOS(pnUserGetLang())."/banners.php")) {
    include "language/".pnVarPrepForOS(pnUserGetLang())."/banners.php";
} elseif (file_exists("language/eng/banners.php")) {
    include "language/eng/banners.php";
}

/**
 * Function to redirect the clicks to the
 * correct url and add 1 click
 */

function clickbanner()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $bid = pnVarCleanFromInput('bid');

    $column = &$pntable['banner_column'];
    $bresult = $dbconn->Execute("SELECT $column[clickurl]
                               FROM $pntable[banner]
                               WHERE $column[bid]=".pnVarPrepForStore($bid)."");
    list($clickurl) = $bresult->fields;
    $bresult->Close();
    $dbconn->Execute("UPDATE $pntable[banner]
                    SET $column[clicks]=$column[clicks]+1
                    WHERE $column[bid]=".pnVarPrepForStore($bid)."");
    Header("Location: $clickurl");
}

/* All of the crap below needs to be moved to a user module */

function clientlogin()
{
    include 'header.php';

    OpenTable();
    echo"<center>"
        ."<font class=\"pn-title\">"._BAN_ADVSTATS."</font><br /><br />"
       ."<form action=\"banners.php\" method=\"post\">"
        .""._BAN_LOGIN." <input type=\"text\" name=\"login\" size=\"12\" maxlength=\"10\"><br />"
        .""._BAN_PASSWORD." <input type=\"password\" name=\"pass\" size=\"12\" maxlength=\"10\"><br />"
        ."<input type=\"hidden\" name=\"op\" value=\"Ok\"><br />"
        .'<input type="submit" value="'._BAN_LOGIN.'">'
        ."</form>";
    CloseTable();

    include 'footer.php';
}

/**
 * Function to display the banners stats for
 * each client
 */

function bannerstats()
{
    list($login,
	 $pass) = pnVarCleanFromInput('login',
				      'pass');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $sitename = pnConfigGetVar('sitename');
    $column = &$pntable['bannerclient_column'];
    $result = $dbconn->Execute("SELECT ".$column['cid'].", "$column['name'].", ".$column['passwd']."
                              FROM ".$pntable['bannerclient']."
                              WHERE ".$column['login']."='".pnVarPrepForStore($login)."'");
    list($cid, $name, $passwd) = $result->fields;
    $result->Close();

    if ($login == "" AND $pass == "" OR $pass == "") {
        include 'header.php';
        echo "<center><br />"._BAN_LOGININCORR."<br /><br /><a href=\"javascript:history.go(-1)\">"._BAN_BACK."</a></center>";
        include 'footer.php';
    } else {
        if ($pass==$passwd) {
            include 'header.php';
            OpenTable();
             echo "<font class=\"pn-title\">"
                ."<center>"
                .""._BAN_CURRACTIVE." ".pnVarPrepForDisplay($name)."."
                ."</center>"
                ."<br />"
                ."</font>"
                ."<table width=\"100%\" border=\"0\"><tr>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_ID."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_IMP_MADE."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_IMP_TOTAL."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_IMP_LEFT."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_CLICKS."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_PERCENTCLICKS."</td>"
                ."<td align=\"center\"><font class=\"pn-title\">"._BAN_FUNCTIONS."</td></tr>";

            $column = &$pntable['banner_column'];
            $result = $dbconn->Execute("SELECT $column[bid], $column[imptotal], $column[impmade], $column[clicks], $column[date]
                                      FROM $pntable[banner]
                                      WHERE $column[cid]=".pnVarPrepForStore($cid)."");

            while(list($bid, $imptotal, $impmade, $clicks, $date) = $result->fields) {

                $result->MoveNext();
                if($impmade == 0) {
                    $percent = 0;
                } else {
                    $percent = substr(100 * $clicks / $impmade, 0, 5);
                }

                if($imptotal==0) {
                    $left = _BAN_UNLIMITED;
                } else {
                    $left = $imptotal-$impmade;
                }

                echo "<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($bid)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($impmade)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($imptotal)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($left)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($clicks)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($percent)."%</td>"
                    ."<td align=\"center\"><a href=\"banners.php?op=EmailStats&login=$login&cid=$cid&bid=$bid&pass=$pass\">"._BAN_EMAIL_STATS."</a></td><tr>";
            }

            echo "</table>";

            CloseTable();
            OpenTable();

            echo '<center>'
                .'<br /><br />'
                .''._BAN_ONYOURSITE.''
                .' '.pnVarPrepForDisplay($sitename).'<br /><br />';

            $column = &$pntable['banner_column'];
            $result = $dbconn->Execute ("SELECT $column[bid], $column[imageurl], $column[clickurl]
                                       FROM $pntable[banner]
                                       WHERE $column[cid]=".pnVarPrepForStore($cid)."");

            $foundrecs = !$result->EOF;

            while(list($bid, $imageurl, $clickurl) = $result->fields) {
                if ($foundrecs) {
                    echo "<hr noshade width=\"80%\"><br />";
                }

                echo "<img src=\"$imageurl\" border=\"1\"><br />"
                    ."<font class=\"pn-normal\">"._BAN_ID.": ".pnVarPrepForDisplay($bid)."<br />"
                    .""._BAN_SEND." <a href=\"banners.php?op=EmailStats&login=$login&cid=$cid&bid=$bid\">"._BAN_EMAIL_STATS."</a> "._BAN_FORTHIS."<br />"
                    ." <a href=\"$clickurl\">"._BAN_THISURL."</a><br />"
                    ."<form action=\"banners.php\" method=\"submit\">"
                    .""._BAN_CHANGEURL.": <input type=\"text\" name=\"url\" size=\"50\" maxlength=\"200\" value=\"$clickurl\">"
                    ."<input type=\"hidden\" name=\"login\" value=\"$login\">"
                    ."<input type=\"hidden\" name=\"bid\" value=\"$bid\">"
                    ."<input type=\"hidden\" name=\"pass\" value=\"$pass\">"
                    ."<input type=\"hidden\" name=\"cid\" value=\"$cid\">"
                    ."<input type=\"submit\" name=\"op\" value=\"Change\"></form></font>";
                $result->MoveNext();
            }

            CloseTable();

            /* Finnished Banners */
            /* Not working so good

            OpenTable();
            echo "<font class=\"pn-title\">"
                ."<center>"
                ."Banners Finished for ".pnVarPrepForDisplay($name).""
                ."</center>"
                ."<br />"
                ."</font>"
                ."<table width=\"100%\" border=\"0\"><tr>"
                ."<td align=\"center\"><font class=\"pn-title\">ID</td>"
                ."<td align=\"center\"><font class=\"pn-title\">Impressions</td>"
                ."<td align=\"center\"><font class=\"pn-title\">Clicks</td>"
                ."<td align=\"center\"><font class=\"pn-title\">% Clicks</td>"
                ."<td align=\"center\"><font class=\"pn-title\">Start Date</td>"
                ."<td align=\"center\"><font class=\"pn-title\">End Date</td></tr>";

            $column = &$pntable['bannerfinish_column'];
            $result = $dbconn->Execute("SELECT $column[bid], $column[impressions],
                                             $column[clicks], $column[datestart],
                                             $column[dateend]
                                      FROM $pntable[bannerfinish]
                                      WHERE $column[cid]=".pnVarPrepForStore($cid)."");
            while(list($bid, $impressions, $clicks, $datestart, $dateend) = $result->fields) {

                $result->MoveNext();
                $percent = substr(100 * $clicks / $impressions, 0, 5);

                echo "<tr><td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($bid)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($impressions)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($clicks)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($percent)."%</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($datestart)."</td>"
                    ."<td align=\"center\"><font class=\"pn-normal\">".pnVarPrepForDisplay($dateend)."</td></tr>"
                    ."</table>";
            }
            CloseTable();
            */

            include 'footer.php';

        } else {
            include 'header.php';
            echo "<center>"
                ."<font class=\"pn-normal\"><br />"._BAN_LOGININCORR."<br /><br /><a href=\"javascript:history.go(-1)\">"._BAN_BACK."</a>"
                ."</center>";
            include 'footer.php';
        }
    }
}

/**
 * Let the client email his
 * banner statistics
 */

function EmailStats()
{
    list($login,
	 $cid,
	 $bid,
	 $pass) = pnVarCleanFromInput('login',
				      'cid',
				      'bid',
				      'pass');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['bannerclient_column'];
    $result2 = $dbconn->Execute("SELECT ".$column['name'].", ".$column['email']."
                               FROM ".$pntable['bannerclient']."
                               WHERE ".$column['cid']."=".pnVarPrepForStore($cid)."");
    list($name, $email) = $result2->fields;

    if ($email == "") {
        include 'header.php';
        OpenTable();
        echo "<font class=\"pn-normal\">"._BAN_STATSFORBAN.";
        echo ".pnVarPrepForDisplay($bid)."";
        echo ""._BAN_CANTSEND.""
         ." ".pnVarPrepForDisplay($name)."<br />"
            .""._BAN_CONTACTADMIN."<br /><br />"
            ."<a href=\"javascript:history.go(-1)\">"._BAN_BACK."</a>";
        CloseTable();

        include 'footer.php';

    } else {
        $column = &$pntable['banner_column'];
        $result = $dbconn->Execute("SELECT $column[bid], $column[imptotal], $column[impmade], $column[clicks], $column[imageurl], $column[clickurl], $column[date]
                                  FROM $pntable[banner]
                                  WHERE $column[bid]=$bid AND $column[cid]=".pnVarPrepForStore($cid)."");
        list($bid, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $date) = $result->fields;

        if ($impmade == 0) {
            $percent = 0;
        } else {
            $percent = substr(100 * $clicks / $impmade, 0, 5);
        }

        if ($imptotal == 0) {
            $left =_BAN_UNLIMITED;
            $imptotal = _BAN_UNLIMITED;
        } else {
            $left = $imptotal-$impmade;
        }
        $sitename = pnConfigGetVar('sitename');
        $fecha = date("F jS Y, h:iA.");
        $subject = ""._BAN_YOURSTATS." $sitename";
        $message = ""._BAN_FORMAIL." $sitename:\n\n\n"._BAN_CLIENTNAME.": $name\n"._BAN_ID.": $bid\n"._BAN_IMAGE.": $imageurl\n"._BAN_URL.": $clickurl\n\n"._BAN_IMPPURCHASED.": $imptotal\n"._BAN_IMP_MADE.": $impmade\n"._BAN_IMP_LEFT.": $left\n"._BAN_CLICKS.": $clicks\n"._BAN_PERCENTCLICKS.": $percent%\n\n\n"._BAN_REPORTMADEON.": $fecha";
        $from = "$sitename";
        pnMail($email, $subject, $message, ""._BAN_FROM.": $from\nX-Mailer: PHP/" . phpversion());

        include 'header.php';
        OpenTable();
        echo "<font class=\"pn-normal\">"._BAN_STATSFORBAN." ".pnVarPrepForDisplay($bid)." "._BAN_SENTTO."<br />"
            ."<i>".pnVarPrepForDisplay($email)."</i> for ".pnVarPrepForDisplay($name)."<br /><br />"
            ."<a href=\"javascript:history.go(-1)\">"._BAN_BACK."</a>";
        CloseTable();
    }
}

/**
 * Let the client to change the
 * url for his banner
 */

function change_banner_url_by_client()
{
    list($login,
	 $pass,
	 $cid,
	 $bid,
	 $url) = pnVarCleanFromInput('login',
				     'pass',
				     'cid',
				     'bid',
				     'url');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column = &$pntable['bannerclient_column'];
    $result = $dbconn->Execute("SELECT $column[passwd]
                              FROM $pntable[bannerclient]
                              WHERE $column[cid]=".pnVarPrepForDisplay($cid)."");
    list($passwd) = $result->fields;
    $result->Close();

    if (!empty($pass) && $pass == $passwd) {
        $column = &$pntable['banner_column'];
        $dbconn->Execute("UPDATE $pntable[banner] SET $column[clickurl]='".pnVarPrepForStore($url)."' WHERE $column[bid]=".pnVarPrepForStore($bid)."");
        include 'header.php';
        OpenTable();
        echo "<font class=\"pn-normal\"><br />"._BAN_URLCHANGED."<br /><br /><a href=\"javascript:history.go(-1)\">".BAN_BACK."</a>";
        CloseTable();
        include 'footer.php';
    } else {
        include 'header.php';
        OpenTable();
        echo "<font class=\"pn-normal\"><br />"._BAN_BADLOGINPASS."<br /><br />"._BAN_PLEASE."<a href=\"banners.php?op=login\">"._BAN_LOGINAGAIN.".</a>";
        CloseTable();
        include 'footer.php';
    }
}

if(!isset($op)) {
    $op = '';
}

switch($op) {

    case "click":
        clickbanner();
        break;

    case "login":
        clientlogin();
        break;

    case "Ok":
        bannerstats();
        break;

    case "Change":
        change_banner_url_by_client();
        break;

    case "EmailStats":
        EmailStats();
        break;

    default:
        clientlogin();
        break;
}
?>