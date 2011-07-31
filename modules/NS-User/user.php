<?php // File: $Id: user.php,v 1.4 2002/12/06 11:53:06 tanis Exp $ $Name:  $
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

$ModName = basename( dirname( __FILE__ ) );
modules_get_language();

function user_user_userinfo()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'UserInfo::', '::', ACCESS_READ)) {
        return;
    }
    $uname = pnVarCleanFromInput('uname');

    $column = &$pntable['users_column'];
    $sql = "SELECT $column[femail] AS femail,
        $column[url] AS url,
            $column[bio] AS bio,
        $column[user_avatar] AS user_avatar,
        $column[user_icq] AS user_icq,
            $column[user_aim] AS user_aim,
        $column[user_yim] AS user_yim,
        $column[user_msnm] AS user_msnm,
            $column[user_from] AS user_from,
        $column[user_occ] AS user_occ,
        $column[user_intrest] AS user_intrest,
            $column[user_sig] AS user_sig,
        $column[uid] AS pn_uid,
        $column[pass] AS pass FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($uname)."'";
    $result = $dbconn->Execute($sql);
    $userinfo = $result->GetRowAssoc(false);

    include 'header.php';

    OpenTable();

    echo "<center><font class=\"pn-pagetitle\">".pnVarPrepForDisplay($uname)."</font></center><br>";
    if ((!$result->EOF) && ($userinfo['url'] || $userinfo['femail'] || $userinfo['bio'] || $userinfo['user_avatar'] || $userinfo['user_icq'] || $userinfo['user_aim'] || $userinfo['user_yim'] || $userinfo['user_msnm'] || $userinfo['user_from'] || $userinfo['user_occ'] || $userinfo['user_intrest'] || $userinfo['user_sig'] || $userinfo['pn_uid'])) {
        echo "<center><font class=\"pn-normal\">";
        $userinfo['user_sig'] = nl2br($userinfo['user_sig']);
        if ($userinfo['user_avatar']) {
            echo "<img src=\"images/avatar/$userinfo[user_avatar]\" alt=\"\"><br>\n";
        }
        if ($userinfo['url']) {
            echo "<font class=\"pn-normal\">"._MYHOMEPAGE." <a class=\"pn-normal\" href=\"$userinfo[url]\">".pnVarPrepForDisplay($userinfo['url'])."</a><br></font>\n";
        }
        if ($userinfo['femail']) {
            echo "<font class=\"pn-normal\">"._MYEMAIL." <a class=\"pn-normal\" href=\"mailto:$userinfo[femail]\">".pnVarPrepForDisplay($userinfo['femail'])."</a><br></font>\n";
        }
        if ($userinfo['user_icq']) {
            echo "<font class=\"pn-normal\">"._ICQ.": ".pnVarPrepForDisplay($userinfo['user_icq'])."<br></font>\n";
        }
        if ($userinfo['user_aim']) {
            echo "<font class=\"pn-normal\">"._AIM.": ".pnVarPrepForDisplay($userinfo['user_aim'])."<br></font>\n";
        }
        if ($userinfo['user_yim']) {
            echo "<font class=\"pn-normal\">"._YIM.": ".pnVarPrepForDisplay($userinfo['user_yim'])."<br></font>\n";
        }
        if ($userinfo['user_msnm']) {
            echo "<font class=\"pn-normal\">"._MSNM.": ".pnVarPrepForDisplay($userinfo['user_msnm'])."<br></font>\n";
        }
        if ($userinfo['user_from']) {
            echo "<font class=\"pn-normal\">"._LOCATION.": ".pnVarPrepForDisplay($userinfo['user_from'])."<br></font>\n";
        }
        if ($userinfo['user_occ']) {
            echo "<font class=\"pn-normal\">"._OCCUPATION.": ".pnVarPrepForDisplay($userinfo['user_occ'])."<br>\n";
        }
        if ($userinfo['user_intrest']) {
            echo "<font class=\"pn-normal\">"._INTERESTS.": ".pnVarPrepForDisplay($userinfo['user_intrest'])."<br></font>\n";
        }
        if ($userinfo['user_sig']) {
            echo "<font class=\"pn-normal\"><br>"._SIGNATURE.":<br>".pnVarPrepHTMLDisplay($userinfo['user_sig'])."<br></font>\n";
        }
        if ($userinfo['bio']) {
            echo "<font class=\"pn-normal\"><br>"._EXTRAINFO.":<br>".pnVarPrepForDisplay($userinfo['bio'])."<br></font>\n";
        }
//        $column = &$pntable['session_column'];
//        $result = $dbconn->Execute("SELECT $column[username]
//                                  FROM $pntable[session]
//                                  WHERE $column[username]='".pnVarPrepForStore($uname)."'");
//        list($username) = $result->fields;
//        if ($username == "") {
//            $online = _OFFLINE;
//        } else {
//            $online = _ONLINE;
//        }
//                echo ""._REGISTEREDUSER." ".pnVarPrepForDisplay($userinfo['pn_uid'])."";
//        if (pnUserLoggedIn()) {
//            echo "<br>"._USERSTATUS.": ".pnVarPrepForDisplay($online)."<br>\n";
//        }
    if(pnModAvailable('Messages')) {
            echo "<br>[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Messages&amp;file=replypmsg&amp;send=1&amp;uname=$uname\">"._USENDPRIVATEMSG." ".pnVarPrepForDisplay($uname)."</a> ]<br>\n";
        }
        echo "</font></center>";
    } else {
        echo "<center><font class=\"pn-normal\">"._NOINFOFOR." ".pnVarPrepForDisplay($uname)."</font></center>";
    }
    CloseTable();

    user_main_last10com($uname);
    echo "<br>";
    user_main_last10submit($uname);
    echo "</center>";
    include("footer.php");
}

function user_user_login() {
    list($uname,
         $pass,
         $url,
         $rememberme) = pnVarCleanFromInput('uname',
                                   'pass',
                                   'url',
                                   'rememberme');

//echo "uname: $uname<br>";
//echo "pass: $pass<br>";

    if (!isset($rememberme)) {
        $rememberme = '';
    }
    access_user_login($uname, $pass, $url, $rememberme);
}

 function user_user_getlogin()
{
        include 'header.php';

// Check if stop var is numeric
if ((isset($GLOBALS['stop']) && !empty($GLOBALS['stop']) && !is_numeric($GLOBALS['stop'])))
{
        include 'header.php';
        OpenTable();
        echo _MODARGSERROR;
        CloseTable();
        include 'footer.php';
}
//End of check
      OpenTable();
       if ($GLOBALS['stop']) {
            echo "<center><font class=\"pn-title\">"._LOGININCOR."</font></center>\n";
        } else {
            echo "<center><font class=\"pn-title\">"._USERREGLOGIN."</font>\n"
                ."</center><br><font class=\"pn-title\">";

            echo ""._SELECTOPTION."<br><br>"
                ."<a href=\"user.php?op=loginscreen&amp;module=NS-User\">"._LOGINSITE."</a><br><br>";

    // age will not be checked, if $pnconfig['minage'] is set to 0 in config.php
        if (pnConfigGetVar('minage') == 0) {
              echo "<a href=\"user.php?op=register&amp;module=NS-NewUser\">"._REGISTER."</a><br><br>";
        } else {
            echo "<a href=\"user.php?op=check_age&amp;module=NS-NewUser\">"._REGISTER."</a><br><br>";
        }

            echo "<a href=\"user.php?op=lostpassscreen&amp;module=NS-LostPassword\">"._RETRIEVEPASS."</a><br></font>";

        }

        CloseTable();

        include ("footer.php");
 }

function user_main_last10com($uname)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $column1 = &$pntable['comments_column'];
    $column2 = &$pntable['stories_column'];

    /*
     *  Fetch active laguage
     */
    if (pnConfigGetVar('multilingual') == 1) {
        $querylang = "AND (".$column2['alanguage']."='".pnVarPrepForStore(pnUserGetLang())."' OR "
                           .$column2['alanguage']."='') ";
    } else {
        $querylang = '';
    }

    /*
     *  Build up SQL
     */
    $query = "SELECT ".$column1['tid'].", "
                      .$column1['sid'].", "
                      .$column1['subject']." "
            ."FROM ".$pntable['comments'].", "
                    .$pntable['stories']." "
            ."WHERE (".$column1['name']."='".pnVarPrepForStore($uname)."' AND "
                    .$column1['sid']."=".$column2['sid'].") "
                    .$querylang
            ."ORDER BY ".$column1['sid']." DESC";

    /*
     *  Make limited select
     */
    $result = $dbconn->SelectLimit($query, 10, 0);

    /*
     *  Do output
     */
    OpenTable();
    echo "<font class=\"pn-title\">"._LAST10COMMENTS." ".pnVarPrepForDisplay($uname).":</font><br>";

    while(list($tid, $sid, $subject) = $result->fields) {

        $result->MoveNext();
        echo "<li><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=article&amp;thold=-1&amp;mode=flat&amp;order=0&amp;sid=$sid#$tid\">".pnVarPrepForDisplay($subject)."</a><br>";
    }
    CloseTable();
}

function user_main_last10submit($uname)
{
    $pntable = pnDBGetTables();
    list($dbconn) = pnDBGetConn();
    $column = &$pntable['stories_column'];

    /*
     *  Fetch active laguage
     */
    if (pnConfigGetVar('multilingual') == 1) {
        $querylang = "AND (".$column['alanguage']."='".pnVarPrepForStore(pnUserGetLang())."' OR "
                           .$column['alanguage']."='') ";
    } else {
        $querylang = '';
    }

    /*
     *  Build up SQL
     */
    $query = "SELECT ".$column['sid'].", "
                      .$column['title']." "
            ."FROM ".$pntable['stories']." "
            ."WHERE ".$column['informant']."='".pnVarPrepForStore($uname)."' "
                    .$querylang
            ."ORDER BY ".$column['sid']." DESC";

    /*
     *  Make limited select
     */
    $result = $dbconn->SelectLimit($query, 10, 0);

    /*
     *  Do output
     */
    OpenTable();
    echo "<font class=\"pn-title\">"._LAST10SUBMISSIONS." ".pnVarPrepForDisplay($uname).":</font><br>";
    while(list($sid, $title) = $result->fields) {

        $result->MoveNext();
        If (!$title)  {
            $title = '- no Title -' ;
        }
        echo "<li><a class=\"pn-normal\" href=\"modules.php?op=modload&name=News&file=article&sid=$sid\">" . pnVarPrepForDisplay($title) . "</a><br>";
    }
    CloseTable();
}

// View main user page
// ====================
function user_user_main($var)
{
    include 'header.php';
    user_menu_draw();

    if (pnUserLoggedIn()) {
        $uname = pnUserGetVar('uname');
        user_main_last10com($uname);
        user_main_last10submit($uname);
        include 'footer.php';
    } // ?else
}

function user_user_loginscreen()
{
    include 'header.php';
    OpenTable();
    echo "<form action=\"user.php\" method=\"post\">\n"
        ."<font class=\"pn-title\">"._USERLOGIN."</font><br><br>\n"
        ."<table border=\"0\"><tr><td>\n"
        ."<font class=\"pn-normal\">"._NICKNAME.": </font></td><td><input type=\"text\" name=\"uname\" size=\"26\" maxlength=\"25\"></td></tr>\n"
        ."<tr><td><font class=\"pn-normal\">"._PASSWORD.": </font></td><td><input type=\"password\" name=\"pass\" size=\"21\" maxlength=\"20\"></td></tr>\n";
        if (pnConfigGetVar('seclevel') != 'High') {
            echo "<tr><td><font class=\"pn-normal\">"._REMEMBERME.": </font></td><td><input type=\"checkbox\" name=\"rememberme\"></td></tr>\n";
        }
        echo "</table>\n"
        ."<input type=\"hidden\" name=\"url\" value=\"".getenv("HTTP_REFERER")."\">\n";
        user_submit('NS-User','login',_LOGIN);
    echo "</form>\n";
    CloseTable();

    include 'footer.php';
}

function user_user_logout($var)
{
    pnUserLogOut();

    redirect_index(_YOUARELOGGEDOUT);
}
?>