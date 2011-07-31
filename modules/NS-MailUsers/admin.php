<?PHP
// File: $Id: admin.php,v 1.3 2002/11/04 21:41:25 skooter Exp $ $Name:  $
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
// Original Author of file:  Christopher Thorjussen <joffer@online.no>
// Purpose of file:
// PHP-Nuke MailUsers Module for PHP-Nuke v5.0BETA
// Copyright (c) 2001 by Christopher Thorjussen (joffer@online.no)
// http://www.nukemodules.com
// Nuke MailUsers is a hack of the email_user script in
// PHP-Nuke AddOn v5.01
// Copyright (c)2001 Richard Tirtadji (rtirtadji@hotmail.com)
// URL: http://www.nukeaddon.com
// ----------------------------------------------------------------------

if (!eregi("admin.php", $PHP_SELF)) {
    die ("Access Denied");
}

modules_get_language();
modules_get_manual();

function MailUser($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';

    GraphicAdmin();

    if (!(pnSecAuthAction(0, 'MailUsers::', '::', ACCESS_ADMIN))) {
        echo _MAILUSERSNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();

    echo "<center><font class=\"pn-title\">"._NM_MAILUSER."</font></center>"
         ."<form action=\"admin.php\" method=\"post\">"
         ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
         ."<input type=\"hidden\" name=\"op\" value=\"send\">"
	 ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ."<table border=\"0\">"
         ."<tr><td><font class=\"pn-normal\"><b>"._NM_USERNAME."</b></font></td>"
         ."<td><select name=\"username\">\n";
    $column = &$pntable['users_column'];
    $result = $dbconn->Execute("SELECT $column[uid], $column[uname]
                              FROM $pntable[users] ORDER BY $column[uname]");
    $tmp1 = 0;
    while(list($uid, $uname) = $result->fields) {
        $result->MoveNext();

      if ($tmp1 == 0) {
        echo "<option value=\"$uid\">"._NM_CHOOSEUSER."</option>";
      }

      $anonymous = pnConfigGetVar('anonymous');

      if ($uname == $anonymous) {
	// echo "<option value=\"$uid\">"._NM_CHOOSEUSER."</option>";
	// $anonid = $uid;
      } else {
        echo "<option value=\"$uid\">".pnVarPrepForDisplay($uname)."</option>\n";
      }
      $tmp1 = $tmp1 + 1;
    }
    echo "</select></td></tr>
    <tr><td></td><td><font class=\"pn-normal\"><input type=\"checkbox\" name=\"all\" value=\"1\">"
    ._NM_MAILALLUSERS."</font></td></tr>
    <tr><td><font class=\"pn-normal\"><b>"._NM_FROM."</b></font></td>
    <td><input type=\"text\" size=\"28\" name=\"fromname\"></td></tr>
    <tr><td><font class=\"pn-normal\"><b>"._NM_REPLYTOADDRESS."</b>
    </font></td><td><input type=\"text\" size=\"28\" name=\"from\"></td></tr>
    <tr><td><font class=\"pn-normal\"><b>"._NM_SUBJECT."</b></font>
    </td><td><input type=\"text\" size=\"28\" name=\"subject\"></td></tr>
    <tr><td><font class=\"pn-normal\"><b>"._NM_MESSAGE."</b></font></td>
    <td><textarea wrap=\"virtual\" cols=\"42\" rows=\"12\" name=\"message\"></textarea></td></tr>
    <tr><td>&nbsp;</td><td>
    <input type=\"submit\" value=\""._NM_SEND_MAIL."\">&nbsp;&nbsp <font class=\"pn-normal\"><b>"
    ._NM_REMEMBER."</b></font></td></tr>
    </table></form>";
    CloseTable();

    include 'footer.php';
}

function error()
{
    $errorid = pnVarCleanFromInput('errorid');

    include 'header.php';

    GraphicAdmin();

    if (!(pnSecAuthAction(0, 'MailUsers::', '::', ACCESS_ADMIN))) {
        echo _MAILUSERSNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();

    echo '<center><br><b><font color='._NB_ERRORCOLOR.' size="3">';

    if($errorid == 1) {
	echo _NM_ERROR1;
    } elseif($errorid == 2) {
	echo _NM_ERROR2;
    } elseif($errorid == 3) {
	echo _NM_ERROR3;
    } elseif($errorid == 4) {
	echo _NM_ERROR4;
    } elseif($errorid == 5) {
	echo _NM_ERROR5;
    }
    echo  '</font></b><br><br>'._GOBACK.'</center>';

    CloseTable();

    include 'footer.php';
}

function send_email_to_user($var)
{
    if (!(pnSecAuthAction(0, 'MailUsers::', '::', ACCESS_ADMIN))) {
        include 'header.php';
        echo _MAILUSERSNOAUTH;
        include 'footer.php';
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

	//use pnVarCleanFromInput which is more reliable with scripting security issues. - Skooter
	list($username,
		 $all,
		 $fromname,
		 $from,
		 $subject,
		 $message) = pnVarCleanFromInput('username',
		 								 'all',
		 								 'fromname',
		 								 'from',
		 								 'subject',
		 								 'message');

    $message = stripslashes($message);
    $subject = stripslashes($subject);

    if (($username== 0) and (!$all)) {
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=error&errorid=1');
    } elseif ($fromname == "") {
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=error&errorid=2');
    } elseif ($from == "") {
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=error&errorid=3');
    } elseif (subject == "") {
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=error&errorid=4');
    } elseif ($message == "") {
        pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=error&errorid=5');
    }
	//  moved security key check to after audits since back button from error page gave auth error. - Skooter
	if (!pnSecConfirmAuthKey()) {
		include 'header.php';
		echo _BADAUTHKEY;
		include 'footer.php';
		exit;
	}

	if($all) {
		$column = &$pntable['users_column'];
		$result = $dbconn->Execute("SELECT DISTINCT $column[email] FROM $pntable[users]");
	}
	else {
		$column = &$pntable['users_column'];
		$result = $dbconn->Execute("SELECT $column[email]
							  FROM $pntable[users]
							  WHERE $column[uid]='".pnVarPrepForStore($username)."'");
	}

	if($dbconn->ErrorNo()<>0) {
		error_log("DB Error: nukemail.php" . $dbconn->ErrorMsg());
		echo $dbconn->ErrorNo(). ": ".$dbconn->ErrorMsg(). "<br>";
		exit();
	}

	//added logic to email in batches of 100 users to prevent problems with large headers - Skooter
	$email = "";
	$usercount = 0;

	if($all) {
	  while(list($useremail) = $result->fields) {
		  $result->MoveNext();

		  if (!empty($useremail)) {
			$email .= "$useremail, ";
			$usercount = $usercount + 1;
		  }
		  
		  //If we've processed more than 100 users send email and reset counters - Skooter
		  if ($usercount > 100){
			pnMail($from, $subject, $message, "From: \"".$fromname."\" <".$from.">\nBcc: $email\nX-Mailer: PHP/" . phpversion());
			$email = "";
			$usercount = 0;
		  }
		  
	  }
		//Leaving this pnmail call here to catch the last set of users if counter is > 0 - Skooter 
		// An email to all goes out To: the user sending the email,
		// and Bcc: all of the actual recipients
		// 11-09-01 eugeniobaldi not compliant with PHP < 4.0.5
		// pnMail($from, $subject, $message, "From: \"$fromname\" <$from>\nBcc: $email\nX-Mailer: PHP/" . phpversion(), "-f$fromname");
		if ($usercount >0){
			pnMail($from, $subject, $message, "From: \"".$fromname."\" <".$from.">\nBcc: $email\nX-Mailer: PHP/" . phpversion());
		}
	}
	else {
		list($email) = $result->fields;
		$email .= ", ";
		// 11-09-01 eugeniobaldi not compliant with PHP < 4.0.5
		// pnMail($email, $subject, $message, "From: \"$fromname\" <$from>\nX-Mailer: PHP/" . phpversion(), "-f$fromname");
		pnMail($email, $subject, $message, "From: \"".$fromname."\" <".$from.">\nBcc: $email\nX-Mailer: PHP/" . phpversion());
	}

	pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=main');

}

function mailusers_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   if (!(pnSecAuthAction(0, 'MailUsers::', '::', ACCESS_ADMIN))) {
       include 'header.php';
       echo _MAILUSERSNOAUTH;
       include 'footer.php';
   } else {
        switch ($op) {

        case "send":
             send_email_to_user($var);
             break;

        case "error":
             error();
             break;

        default:
            MailUser($var);
            break;
       }
   }
}
?>