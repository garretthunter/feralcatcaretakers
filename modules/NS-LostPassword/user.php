<?php // File: $Id: user.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Original Author of this file:
// Purpose of this file: mail a new password if forgotten
// ----------------------------------------------------------------------

$ModName = basename( dirname( __FILE__ ) );

modules_get_language();

function lostpassword_user_lostpassscreen()
{
    include 'header.php';

    OpenTable();
    echo "<font class=\"pn-title\">"._PASSWORDLOST."</font><br><br>\n"
        ."<font class=\"pn-normal\">"._NOPROBLEM."</font><br>\n"
        ."<form action=\"user.php\" method=\"post\">\n"
        ."<table border=\"0\">\n"
        ."<tr>\n"
        ."<td><font class=\"pn-normal\">"._NICKNAME.": </font></td><td><input type=\"text\" name=\"uname\" size=\"26\" maxlength=\"25\"></td>"
        ."<td><font class=\"pn-normal\">"._EMAIL.": </font></td><td><input type=\"text\" name=\"email\" size=\"60\" maxlength=\"60\"></td>\n"
        ."</tr>\n"
        ."<tr><td><font class=\"pn-normal\">"._CONFIRMATIONCODE.": </font></td><td><input type=\"text\" name=\"code\" size=\"5\" maxlength=\"6\"></td></tr></table>\n"
        ."<input type=\"hidden\" name=\"op\" value=\"mailpasswd\">\n"
        ."<input type=\"hidden\" name=\"module\" value=\"NS-LostPassword\">\n"
        ."<input type=\"submit\" value=\""._SENDPASSWORD."\">\n"
        ."</form>\n";
    CloseTable();

    include 'footer.php';
}

function lostpassword_user_mailpasswd()
{
    list($uname,
         $email,
         $code)= pnVarCleanFromInput('uname',
                                     'email',
                                     'code');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $sitename = pnConfigGetVar('sitename');
    $system = pnConfigGetVar('system');
    $adminmail = pnConfigGetVar('adminmail');
    
    $column = &$pntable['users_column'];
    $wheres = array();
    if (!empty($email)) {
        $wheres[] = "$column[email] = '".pnVarPrepForStore($email)."'";
        $who = $email;
    }
    if (!empty($uname)) {
        $wheres[] = "$column[uname] = '".pnVarPrepForStore($uname)."'";
        $who = $uname;
    }
    $where = join('AND ', $wheres);
    $result = $dbconn->Execute("SELECT $column[uname],
                                       $column[email],
                                       $column[pass]
                                FROM $pntable[users]
                                WHERE $where");
    if (($dbconn->ErrorNo() != 0) || ($result->numRows() == 0)) {
        include 'header.php';
        OpenTable();
        echo "<center><font class=\"pn-normal\">"._SORRYNOUSERINFO."</center></font>";
        CloseTable();
        include 'footer.php';
    } else {
        $host_name = getenv("REMOTE_ADDR");
        list($uname, $email, $pass) = $result->fields;
        $areyou = substr($pass, 0, 5);
        if ($areyou == $code) {
            $newpass=makepass();
            $message = ""._USERACCOUNT." $uname "._AT." $sitename "._HASTHISEMAIL."  "._AWEBUSERFROM." $host_name "._HASREQUESTED."\n\n"._YOURNEWPASSWORD." $newpass\n\n "._YOUCANCHANGE . pnGetBaseURL() . "user.php\n\n"._IFYOUDIDNOTASK."";
            $subject = ""._USERPASSWORD4." $uname";
            pnMail($email, $subject, $message, "From: $adminmail\nX-Mailer: PHP/" . phpversion());

	    // Next step: add the new password to the database
            $cryptpass = md5($newpass);

            $column = &$pntable['users_column'];
            $query = "UPDATE $pntable[users] SET $column[pass]='".pnVarPrepForStore($cryptpass)."' WHERE $column[uname]='".pnVarPrepForStore($uname)."'";
            $result = $dbconn->Execute($query);

            if($dbconn->ErrorNo()<>0) {
                echo "<font class=\"pn-title\">"._UPDATEFAILED."</font>";
            }
            include 'header.php';

            OpenTable();
            echo "<center><font class=\"pn-title\">"._PASSWORD4." ".pnVarPrepForDisplay($uname)." "._MAILED."</center></font>";
            CloseTable();

            include 'footer.php';
        // If no Code, send it
        } else {
            $host_name = getenv("REMOTE_ADDR");
            $areyou = substr($pass, 0, 5);
            $message = ""._USERACCOUNT." '$uname' "._AT." $sitename "._HASTHISEMAIL." "._AWEBUSERFROM." $host_name "._CODEREQUESTED."\n\n"._YOURCODEIS." $areyou \n\n"._WITHTHISCODE."". pnGetBaseURL() . "user.php\n"._IFYOUDIDNOTASK2."";
            $subject = ""._CODEFOR." $uname";
	    // 11-09-01 eugeniobaldi not compliant with PHP < 4.0.5
	    // pnMail($email, $subject, $message, "From: $adminmail\nX-Mailer: PHP/" . phpversion(), "-f$adminmail");
            pnMail($email, $subject, $message, "From: $adminmail\nX-Mailer: PHP/" . phpversion());

            include 'header.php';

            echo "<center><font class=\"pn-title\">"._CODEFOR." ".pnVarPrepForDisplay($who)." "._MAILED."</font>";

            include 'footer.php';
        }
    }
}
?>