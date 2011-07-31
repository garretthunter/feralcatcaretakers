<?php /* $Id: user.php,v 1.2 2002/10/29 20:22:15 skooter Exp $ */
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
// Purpose of this file:  new user routines
// ----------------------------------------------------------------------
//Added calls to pnVarCleanFromInput since it's more secure than using $var[]. - Skooter

$ModName = 'NS-NewUser';
modules_get_language();

function newuser_user_underage()
{
	include 'header.php';

    OpenTable();
        echo "<font class=\"pn-title\">"._SORRY."</font>";
        echo "<br><br>\n"
            ."<font class=\"pn-normal\">"._MUSTBE."<br>"
            ."<br>"._CLICK."<a href=\"index.php\">"._HERE."</a> "._RETURN."</font><br>\n";
   CloseTable();
   include 'footer.php';
}

function newuser_user_check_age($var)
{
    $sitename = pnConfigGetVar('sitename');

    include 'header.php';

    OpenTable();
    echo "<center>"
        ."<font class=\"pn-title\">"._WELCOMETO." ".pnVarPrepForDisplay($sitename)." "._REGISTRATION."</font>"
        ."<br><br>\n"
        ."<font class=\"pn-normal\">"._MUSTBE."</font><br>\n"

        ."<a href=\"user.php?op=register&amp;module=NS-NewUser\">"
        .""._OVER13.""
        ."</a><br><br>"

        ."<font class=\"pn-normal\">"._CONSENT."</font><br><br>\n"

        ."<a href=\"user.php?op=underage&amp;module=NS-NewUser\">"
        .""._UNDER13.""
        ."</a><br>\n"

        ."</font></center>\n";

    CloseTable();
    include 'footer.php';
}


function newuser_user_register()
{
    $system = pnConfigGetVar('system');

        include 'header.php';

        OpenTable();
        echo "<form name=\"Register\" action=\"user.php\" method=\"post\">\n"
            ."<font class=\"pn-title\">"._REGNEWUSER."</font><br><br>\n"
            ."<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n"
            ."<tr><td><font class=\"pn-normal\">"._NICKNAME.": </font></td><td><input type=\"text\" name=\"uname\" size=\"26\" maxlength=\"25\"></td></tr>\n"
            ."<tr><td><font class=\"pn-normal\">"._EMAIL.": </font></td><td><input type=\"text\" name=\"email\" size=\"25\" maxlength=\"60\"></td></tr>\n"
            ."<tr><td><font class=\"pn-normal\">"._OPTION.":</font> </td><td><INPUT TYPE=\"CHECKBOX\" NAME=\"user_viewemail\" VALUE=\"1\"><font class=\"pn-normal\"> "._ALLOWEMAILVIEW."</font></td></tr>\n";
            //Check for legal module
            if (pnModAvailable("legal")) {
                echo "<tr><td colspan=\"2\"><INPUT TYPE=\"CHECKBOX\" NAME=\"agreetoterms\" VALUE=\"1\"><font class=\"pn-normal\">"._REGISTRATIONAGREEMENT." <a href=\"modules.php?op=modload&amp;name=legal&amp;file=index\">"._TERMSOFUSE."</a> "._ANDCONNECTOR." <a href=\"modules.php?op=modload&amp;name=legal&amp;file=privacy\">"._PRIVACYPOLICY."</a>.</td></tr>\n";
            }
        echo "<tr><td>\n"
            ."<input type=\"hidden\" name=\"module\" value=\"NS-NewUser\">\n"
            ."<input type=\"hidden\" name=\"op\" value=\"confirmnewuser\">\n"
            ."<input type=\"submit\" value=\""._NEWUSER."\">\n"
            ."</td></tr></table>\n"
            ."</form>\n"
            ."<br>\n";
            if($system == 0) {
        echo "<font class=\"pn-normal\">" . _PASSWILLSEND . "</font><br><br>\n";
        }
            echo "<font class=\"pn-normal\">"._COOKIEWARNING."</font><br>\n"
            ."<font class=\"pn-normal\">"._ASREGUSER."</font><br>\n"
            ."<font class=\"pn-normal\"><ul>\n"
            ."<li>"._ASREG1."\n"
            ."<li>"._ASREG2."\n"
            ."<li>"._ASREG3."\n"
            ."<li>"._ASREG4."\n"
            ."<li>"._ASREG5."\n"
            ."<li>"._ASREG6."\n"
            ."<li>"._ASREG7."\n"
            ."</ul></font>\n"
            ."<font class=\"pn-title\">"._REGISTERNOW."</font><br>\n"
            ."<font class=\"pn-normal\">"._WEDONTGIVE."</font>\n";
        CloseTable();

		include 'footer.php';

}

function userCheck($uname, $email, $agreetoterms)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $stop = '';
    $res = pnVarValidate($email, 'email');
    if($res == false) {
    	$stop = "<center><font class=\"pn-title\">"._ERRORINVEMAIL."</center></font><br>";
    }

    //Check for legal module
    if (pnModAvailable("legal")) {
        // If legal var agreetoterms checkbox not checked, value is 0 and results in error
        if ($agreetoterms == 0) {
           $stop = "<center><font class=\"pn-title\">"._ERRORMUSTAGREE."</center></font><br>";
        }
    }

    // By this test (without on printable characters) it should be possible to have
    // eg. Chinese characters on the username. (hinrich, 2002-07-03, reported by class 007
    //
    if ((!$uname) || !(/* preg_match("/^[[:print:]]+/",$uname) && */ !preg_match("/[[:space:]]/",$uname))) {

    // Here we test the uname. Any value is possible but space.
    // On special character sets you might configure the server.
    // (was bug #455288)
    // if ((!$uname) || !(ereg("^[[:print:]]+",$uname) && !ereg("[[:space:]]",$uname))) {
    /*if ((!$uname) || ($uname=="") || (ereg("[^a-zA-Z0-9_-]",$uname))) {*/

        $stop = "<center><font class=\"pn-title\">"._ERRORINVNICK."</center></font><br>";
    }
    if (strlen($uname) > 25) {
        $stop = "<center><font class=\"pn-title\">"._NICK2LONG."</center></font>";
    }
    if (preg_match('/((root)|(adm)|(linux)|(webmaster)|(admin)|(god)|(administrator)|(administrador)|(nobody)|(anonymous)|(anonimo)|(anóîimo)|(operator))/iAD',$uname)) {
        $stop = "<center><font class=\"pn-title\">"._NAMERESERVED."</center></font>";
    }
    if (strrpos($uname,' ') > 0) {
        $stop = "<center><font class=\"pn-title\">"._NICKNOSPACES."</center></font>";
    }
    $column = &$pntable['users_column'];
    $existinguser = $dbconn->Execute("SELECT $column[uname] FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($uname)."'");
    if (!$existinguser->EOF) {
        $stop = "<center><font class=\"pn-title\">"._NICKTAKEN."</center></font><br>";
    }
    $existinguser->Close();
    $existinguser = $dbconn->Execute("SELECT $column[email] FROM $pntable[users] WHERE $column[email]='".pnVarPrepForStore($email)."'");
    if (!$existinguser->EOF) {
        $stop = "<center><font class=\"pn-title\">"._EMAILREGISTERED."</center></font><br>";
    }
    $existinguser->Close();
    return($stop);
}

function newuser_user_confirmNewUser($var)
{
   	list($uname,$user_viewemail,$email,$agreetoterms) = 
		pnVarCleanFromInput('uname','user_viewemail','email','agreetoterms');
    
    include 'header.php';

    $uname = filter_text($uname);
    
    if(isset($user_viewemail) && $user_viewemail == 1) {
        $user_viewemail = "1";
        $femail = $email;
    } else {
        $user_viewemail = "0";
        $femail = "-";
    }
    if(empty($agreetoterms)) {
        $agreetoterms = '0';
    }
    //Removed if since there is not error checkin in this function it's not necessary.
    // Audits are completed in the finishnewuser function below. - skooter
    //if (!$stop) {
        OpenTable();
        echo "<font class=\"pn-normal\">"._USERNAME.": ".pnVarPrepForDisplay($uname)."<br>"
            .""._EMAIL.": ".pnVarPrepForDisplay($email)."<br></font><br><br>\n";
        echo ""._GOBACK."";
        echo "<form action=\"user.php\" method=\"post\">"
            ."<input type=\"hidden\" name=\"uname\" value=\"$uname\">"
            ."<input type=\"hidden\" name=\"email\" value=\"$email\">"
            ."<input type=\"hidden\" name=\"agreetoterms\" value=\"$agreetoterms\">"
            ."<input type=\"hidden\" name=\"user_viewemail\" value=\"$user_viewemail\">"
            ."<input type=\"hidden\" name=\"op\" value=\"finishnewuser\">"
            ."<input type=\"hidden\" name=\"module\" value=\"NS-NewUser\">"
            ."<input type=\"submit\" value=\""._FINISH."\"></form>";
        CloseTable();
    //} else {
    //    OpenTable();
    //    echo "<center><font class=\"pn-title\">Registration Error!</font><br><br>";
    //    echo "<font class=\"pn-normal\">$stop<br>"._GOBACK."</font></center>";
    //    CloseTable();
    //}
    include 'footer.php';
}

function newuser_user_finishnewuser($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

	list($uname,$user_viewemail,$email,$agreetoterms) = 
		pnVarCleanFromInput('uname','user_viewemail','email','agreetoterms');
    
    $system = pnConfigGetVar('system');
    $adminmail = pnConfigGetVar('adminmail');
    $sitename = pnConfigGetVar('sitename');
    $Default_Theme = pnConfigGetVar('Default_Theme');
    $commentlimit = pnConfigGetVar('commentlimit');
    $storynum = pnConfigGetVar('storyhome');
    $timezoneoffset = pnConfigGetVar('timezone_offset');

    include 'header.php';
    $stop = userCheck($uname, $email, $agreetoterms);
    $user_regdate = time();
    if (empty($stop)) {
        $makepass = makepass();
        $cryptpass = md5($makepass);
        $uid = $dbconn->GenId($pntable['users']);
        $column = &$pntable['users_column'];
        $result = $dbconn->Execute("INSERT INTO $pntable[users] ($column[name], $column[uname], $column[email],
                           $column[femail], $column[url], $column[user_avatar], $column[user_regdate], $column[user_icq],
                           $column[user_occ], $column[user_from], $column[user_intrest], $column[user_sig],
                           $column[user_viewemail], $column[user_theme], $column[user_aim], $column[user_yim],
                           $column[user_msnm], $column[pass], $column[storynum], $column[umode], $column[uorder],
                           $column[thold], $column[noscore], $column[bio], $column[ublockon], $column[ublock],
                           $column[theme], $column[commentmax], $column[counter], $column[timezone_offset])
                           VALUES ('','".pnVarPrepForStore($uname)."','".pnVarPrepForStore($email)."','','','blank.gif',
                           '".pnVarPrepForStore($user_regdate)."','','','',
                           '','','".pnVarPrepForStore($user_viewemail)."','',
                           '','','','".pnVarPrepForStore($cryptpass)."','".pnVarPrepForStore($storynum)."','',0,0,0,'',0,'','',
                           '".pnVarPrepForStore($commentlimit)."', '0', '".pnVarPrepForStore($timezoneoffset)."')");
        if($dbconn->ErrorNo()<>0) {
            echo $dbconn->ErrorNo(). ": ".$dbconn->ErrorMsg(). "<br>";
            error_log ($dbconn->ErrorNo(). ": ".$dbconn->ErrorMsg(). "<br>");
        } else {
            // get the generated id
            $uid = $dbconn->PO_Insert_ID($pntable['users'],$column['uid']);
            // Add user to group
            $column = &$pntable['groups_column'];
            $result = $dbconn->Execute("SELECT $column[gid]
                                      FROM $pntable[groups]
                                      WHERE $column[name]='". pnConfigGetVar('defaultgroup') . "'");
            if($dbconn->ErrorNo()<>0) {
                echo $dbconn->ErrorNo(). "Get default group: ".$dbconn->ErrorMsg(). "<br>";
                error_log ($dbconn->ErrorNo(). "Get default group: ".$dbconn->ErrorMsg(). "<br>");
            } else {
                if (!$result->EOF) {
                    list($gid) = $result->fields;
                    $result->Close();
                    $column = &$pntable['group_membership_column'];
                    $result = $dbconn->Execute("INSERT INTO $pntable[group_membership] ($column[gid], $column[uid])
                                              VALUES (".pnVarPrepForStore($gid).", ".pnVarPrepForStore($uid).")");
                    if($dbconn->ErrorNo()<>0) {
                        echo $dbconn->ErrorNo(). "Create default group membership: ".$dbconn->ErrorMsg(). "<br>";
                        error_log ($dbconn->ErrorNo(). "Create default group membership: ".$dbconn->ErrorMsg(). "<br>");
                    }
                }
                $message = ""._WELCOMETO." $sitename!\n\n"._YOUUSEDEMAIL." ($email) "._TOREGISTER." $sitename. "._FOLLOWINGMEM."\n\n"._UNICKNAME." $uname\n"._UPASSWORD." $makepass";
                $subject=""._USERPASS4." $uname";
                $from="$adminmail";
                if ($system == 1) {
                    echo "<table align=\"center\"><tr><td><font class=\"pn-normal\">"._YOURPASSIS." <b>$makepass</b></font><br>";
                    echo "<a class=\"pn-normal\" href=\"user.php?module=NS-User&op=login&uname=$uname&pass=$makepass&url=user.php\">"._LOGIN."</a><font class=\"pn-normal\"> "._2CHANGEINFO."</font></td></tr></table>";
                } else {
// 11-09-01 eugeniobaldi not compliant with PHP < 4.0.5
//                    pnMail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion(), "-f$from");
                    pnMail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion());
                    OpenTable();
                    echo "<font class=\"pn-normal\">"._YOUAREREGISTERED."</font>";
                    CloseTable();
                }
            }
        }
    } else {
        echo "$stop";
    }
    include 'footer.php';
}
?>
