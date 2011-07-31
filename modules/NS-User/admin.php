<?php
// File: $Id: admin.php,v 1.5 2002/12/06 11:53:06 tanis Exp $ $Name:  $
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

modules_get_language();
modules_get_manual();

if (!pnSecAuthAction(0, 'Users::', '::', ACCESS_ADMIN)) {
    include 'header.php';
    echo _MODIFYUSERSNOAUTH;
    include 'footer.php';
}

/**
 * Users Functions
 */

function displayUsers() {

    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Users::', '::', ACCESS_EDIT)) {
        echo _MODIFYUSERSNOAUTH;
        include 'footer.php';
        return;
    }

    // Edit current user
    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_EDIT)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._EDITUSER."</b></font><br /><br />"
        ."<form method=\"post\" action=\"admin.php\">"
        ."<b>"._NICKNAME.": </b> <input type=\"text\" name=\"chng_uid\" size=\"20\">\n"
        ."<select name=\"op\">"
        ."<option value=\"modifyUser\">"._MODIFY."</option>\n"
        ."<option value=\"delUser\">"._DELETE."</option></select>\n"
        ."<input type=\"hidden\" name=\"module\" value=\"NS-User\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._OK."\"></form></center>";
        CloseTable();
    }

    // Add new user
    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_ADD)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._ADDUSER."</b></font><br /><br />"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<table border=\"0\" width=\"100%\">"
            ."<tr><td width=\"100\"><font class=\"pn-norma\">"._NICKNAME."</font></td>"
            ."<td><input type=\"text\" name=\"add_uname\" size=\"30\" maxlength=\"25\"> <font class=\"pn-sub\">"._REQUIRED."</font></td></tr>"
            ."<tr><td><font class=\"pn-normal\">"._EMAIL."</font></td>"
            ."<td><input type=\"text\" name=\"add_email\" size=\"30\" maxlength=\"60\"> <font class=\"pn-sub\">"._REQUIRED."</font></td></tr>"
            ."<tr><td><font class=\"pn_normal\">"._PASSWORD."</font></td>"
            ."<td><input type=\"password\" name=\"add_pass\" size=\"12\" maxlength=\"12\"> <font class=\"pn-sub\">"._REQUIRED."</font></td></tr>"
            ."<input type=\"hidden\" name=\"add_avatar\" value=\"blank.gif\">"
            ."<input type=\"hidden\" name=\"module\" value=\"NS-User\">"
            ."<input type=\"hidden\" name=\"op\" value=\"addUser\">"
	    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
            ."<tr><td><input type=\"submit\" value=\""._ADDUSERBUT."\"></form></td></tr>"
            ."</table>";
        CloseTable();
    }
// Access User Settings
    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_ADD)) {
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._USERCONF."</b></font></center><br /><br />";
        echo "<center><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getConfig\">"._USERCONF."</a></center>";
        echo "<center><a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getDynamic\">"._DYNAMICDATA."</a></center>";
        CloseTable();
        include("footer.php");
    }
}

function modifyUser($chng_user)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");
    GraphicAdmin();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
    CloseTable();

    $column = &$pntable['users_column'];
    $result = $dbconn->Execute("SELECT $column[uid], $column[uname], $column[name],
                                $column[url], $column[email], $column[femail],
                                $column[user_icq], $column[user_aim],
                                $column[user_yim], $column[user_msnm],
                                $column[user_from], $column[user_occ],
                                $column[user_intrest], $column[user_viewemail],
                                $column[user_avatar], $column[user_sig], $column[bio], $column[pass]
                              FROM $pntable[users] 
                              WHERE $column[uname]='$chng_user'");

    if($result->EOF) {
        $result = $dbconn->Execute("SELECT $column[uid], $column[uname], $column[name],
                                    $column[url], $column[email], $column[femail],
                                    $column[user_icq], $column[user_aim],
                                    $column[user_yim], $column[user_msnm],
                                    $column[user_from], $column[user_occ],
                                    $column[user_intrest], $column[user_viewemail],
                                    $column[user_avatar], $column[user_sig],
                                    $column[bio], $column[pass]
                                  FROM $pntable[users] 
                                  WHERE $column[uid]='$chng_user'");
     }

    if(!$result->EOF) {
        list($chng_uid, $chng_uname, $chng_name, $chng_url, $chng_email, $chng_femail, $chng_user_icq, $chng_user_aim, $chng_user_yim, $chng_user_msnm, $chng_user_from, $chng_user_occ, $chng_user_intrest, $chng_user_viewemail, $chng_avatar, $chng_user_sig, $chng_bio, $chng_pass) = $result->fields;
    if (!pnSecAuthAction(0, 'Users::', "$chng_uname::$chng_uid", ACCESS_EDIT)) {
        echo _MODIFYUSERSEDITNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._USERUPDATE.": <i>" . pnVarPrepForDisplay(stripslashes($chng_user)) . "</i></b></font></center>"
        ."<form action=\"admin.php\" method=\"post\">"
        ."<table border=\"0\">"
        ."<tr><td>"._USERID."</td>"
        ."<td><b>".pnVarPrepForDisplay($chng_uid)."</b></td></tr>"
        ."<tr><td>"._NICKNAME."</td>"
        ."<td><input type=\"text\" name=\"chng_uname\" value=\"$chng_uname\"> <font class=\"pn-sub\">"._REQUIRED."</font></td></tr>"
        ."<input type=\"hidden\" name=\"chng_name\" value=\"$chng_name\">"
        ."<input type=\"hidden\" name=\"chng_url\" value=\"$chng_url\">"
        ."<tr><td>"._EMAIL."</td>"
        ."<td><input type=\"text\" name=\"chng_email\" value=\"$chng_email\" size=\"30\" maxlength=\"60\"> <font class=\"pn-sub\">"._REQUIRED."</font></td></tr>"
        ."<input type=\"hidden\" name=\"chng_femail\" value=\"$chng_femail\">"
        ."<input type=\"hidden\" name=\"chng_user_icq\" value=\"$chng_user_icq\">"
        ."<input type=\"hidden\" name=\"chng_user_aim\" value=\"$chng_user_aim\">"
        ."<input type=\"hidden\" name=\"chng_user_yim\" value=\"$chng_user_yim\">"
        ."<input type=\"hidden\" name=\"chng_user_msnm\" value=\"$chng_user_msnm\">"
        ."<input type=\"hidden\" name=\"chng_user_from\" value=\"$chng_user_from\">"
        ."<input type=\"hidden\" name=\"chng_user_occ\" value=\"$chng_user_occ\">"
        ."<input type=\"hidden\" name=\"chng_user_intrest\" value=\"$chng_user_intrest\">"
        ."<tr><td>"._BIO."</td>"
        ."<td><textarea cols=\"40\" rows=\"6\" name=\"chng_bio\">".pnVarPrepHTMLDisplay(nl2br($chng_bio))."</textarea></td></tr>"
        ."<tr><td>"._OPTION."</td>";
    if ($chng_user_viewemail == 1) {
        echo "<td><input type=\"checkbox\" name=\"chng_user_viewemail\" value=\"1\" checked> "._ALLOWUSERS."</td></tr>";
    } else {
        echo "<td><input type=\"checkbox\" name=\"chng_user_viewemail\" value=\"1\"> "._ALLOWUSERS."</td></tr>";
    }
    echo "<input type=\"hidden\" name=\"chng_user_sig\" value=\"$chng_user_sig\">"
        ."<tr><td>"._PASSWORD."</td>"
        ."<td><input type=\"password\" name=\"chng_pass\" size=\"12\" maxlength=\"12\"></td></tr>"
        ."<tr><td>"._RETYPEPASSWD."</td>"
        ."<td><input type=\"password\" name=\"chng_pass2\" size=\"12\" maxlength=\"12\"> <font class=\"pn-sub\">"._FORCHANGES."</font></td></tr>"
        ."<input type=\"hidden\" name=\"chng_avatar\" value=\"$chng_avatar\">"
        ."<input type=\"hidden\" name=\"chng_uid\" value=\"$chng_uid\">"
        ."<input type=\"hidden\" name=\"module\" value=\"NS-User\">"
        ."<input type=\"hidden\" name=\"op\" value=\"updateUser\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<tr><td><input type=\"submit\" value=\""._SAVECHANGES."\"></form></td></tr>"
        ."</table>";
    CloseTable();
    } else {
    OpenTable();
    echo "<center><b>"._USERNOEXIST."</b><br /><br />"
        .""._GOBACK."</center>";
    CloseTable();
    }
    include("footer.php");
}

function updateUser()
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    list($chng_uid,
         $chng_uname,
         $chng_name,
         $chng_url,
         $chng_pass,
         $chng_pass2,
         $chng_email,
         $chng_femail,
         $chng_user_icq,
         $chng_user_aim,
         $chng_user_yim,
         $chng_user_msnm,
         $chng_user_from,
         $chng_user_occ,
         $chng_user_intrest,
         $chng_user_viewemail,
         $chng_avatar,
         $chng_bio) = pnVarCleanFromInput('chng_uid',
                                   'chng_uname',
                                   'chng_name',
                                   'chng_url',
                                   'chng_pass',
                                   'chng_pass2',
                                   'chng_email',
                                   'chng_femail',
                                   'chng_user_icq',
                                   'chng_user_aim',
                                   'chng_user_yim',
                                   'chng_nachng_user_msnmme',
                                   'chng_user_from',
                                   'chng_user_occ',
                                   'chng_user_intrest',
                                   'chng_user_viewemail',
                                   'chng_avatar',
                                   'chng_bio');


    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['users_column'];
    $result = $dbconn->Execute("SELECT $column[uname]
                                FROM $pntable[users]
                                WHERE $column[uid] = " . pnVarPrepForStore($chng_uid)."");


    if(!isset($chng_user_viewemail)) {
        $chng_user_viewemail = 0;
    }

    if(!$result->EOF) {
        list($old_uname) = $result->fields;
    } else {
        include 'header.php';
        echo _USERNOEXIST;
        include 'footer.php';
        return;
    }
    if (!pnSecAuthAction(0, 'Users::', $old_uname."::".$chng_uid, ACCESS_EDIT)) {
        include 'header.php';
        echo _MODIFYUSERSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    // Bug #507260 - space in username
    // 2002-01-27: hdonner
    if ((!$chng_uname) || !(ereg("^[[:print:]]+",$chng_uname) && !ereg("[[:space:]]",$chng_uname))) {

        include("header.php");
        GraphicAdmin();
        OpenTable();
        echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
        CloseTable();

        OpenTable();
        echo "<center>"._ERRORINVNICK."<br /><br />"
            .""._GOBACK."</center>";
        CloseTable();
        include("footer.php");
        exit;
    }

    if ($chng_url != "")
    {
        $url_array = explode(":", $chng_url);
        if ($url_array[0] != "http")
        {
            include("header.php");
            GraphicAdmin();
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
            CloseTable();

            OpenTable();
            echo "<center>"._ERRORINVURL."<br /><br />"
                .""._GOBACK."</center>";
            CloseTable();
            include("footer.php");
            exit;
        }
    }

    $tmp = 0;
    if ($chng_pass2 != "") {
        if($chng_pass != $chng_pass2) {
            include 'header.php';
            GraphicAdmin();
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
            CloseTable();

            OpenTable();
            echo "<center>"._PASSWDNOMATCH."<br /><br />"
                .""._GOBACK."</center>";
            CloseTable();
            include("footer.php");
            exit;
        }
        $tmp = 1;
    }

    if ($tmp == 0) {
        $column = &$pntable['users_column'];
        $result = $dbconn->Execute("UPDATE $pntable[users]
                                  SET $column[uname]='".pnVarPrepForStore($chng_uname)."',
                                    $column[name]='".pnVarPrepForStore($chng_name)."',
                                    $column[email]='".pnVarPrepForStore($chng_email)."',
                                    $column[femail]='".pnVarPrepForStore($chng_femail)."',
                                    $column[url]='".pnVarPrepForStore($chng_url)."',
                                    $column[user_icq]='".pnVarPrepForStore($chng_user_icq)."',
                                    $column[user_aim]='".pnVarPrepForStore($chng_user_aim)."',
                                    $column[user_yim]='".pnVarPrepForStore($chng_user_yim)."',
                                    $column[user_msnm]='".pnVarPrepForStore($chng_user_msnm)."',
                                    $column[user_from]='".pnVarPrepForStore($chng_user_from)."',
                                    $column[user_occ]='".pnVarPrepForStore($chng_user_occ)."',
                                    $column[user_intrest]='".pnVarPrepForStore($chng_user_intrest)."',
                                    $column[user_viewemail]='".pnVarPrepForStore($chng_user_viewemail)."',
                                    $column[user_avatar]='".pnVarPrepForStore($chng_avatar)."',
                                    $column[user_sig]='".pnVarPrepForStore($chng_user_sig)."',
                                    $column[bio]='".pnVarPrepForStore($chng_bio)."'
                                  WHERE $column[uid]='".pnVarPrepForStore($chng_uid)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: " . $dbconn->ErrorMsg());
        }
    }
    if ($tmp == 1) {
        $cpass = md5($chng_pass);
        $column = &$pntable['users_column'];
        $result = $dbconn->Execute("UPDATE $pntable[users]
                                    SET $column[uname]='".pnVarPrepForStore($chng_uname)."',
                                    $column[name]='".pnVarPrepForStore($chng_name)."',
                                    $column[email]='".pnVarPrepForStore($chng_email)."',
                                    $column[femail]='".pnVarPrepForStore($chng_femail)."',
                                    $column[url]='".pnVarPrepForStore($chng_url)."',
                                    $column[user_icq]='".pnVarPrepForStore($chng_user_icq)."',
                                    $column[user_aim]='".pnVarPrepForStore($chng_user_aim)."',
                                    $column[user_yim]='".pnVarPrepForStore($chng_user_yim)."',
                                    $column[user_msnm]='".pnVarPrepForStore($chng_user_msnm)."',
                                    $column[user_from]='".pnVarPrepForStore($chng_user_from)."',
                                    $column[user_occ]='".pnVarPrepForStore($chng_user_occ)."',
                                    $column[user_intrest]='".pnVarPrepForStore($chng_user_intrest)."',
                                    $column[user_viewemail]='".pnVarPrepForStore($chng_user_viewemail)."',
                                    $column[user_avatar]='".pnVarPrepForStore($chng_avatar)."',
                                    $column[user_sig]='".pnVarPrepForStore($chng_user_sig)."',
                                    $column[bio]='".pnVarPrepForStore($chng_bio)."',
                                    $column[pass]='".pnVarPrepForStore($cpass)."'
                                  WHERE $column[uid]='".pnVarPrepForStore($chng_uid)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("DB Error: " . $dbconn->ErrorMsg());
        }
    }
    pnRedirect("admin.php");
}

function deleteUser($chng_uid)
{
   list($dbconn) = pnDBGetConn();
   $pntable = pnDBGetTables();

   $authid = pnSecGenAuthKey();

   include("header.php");
   GraphicAdmin();
   OpenTable();
   echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
   CloseTable();

   OpenTable();
   echo "<center><font class=\"pn-title\"><b>"._DELETEUSER."</b></font><br /><br />";

   $column = &$pntable['users_column'];
   // Someone got uname and uid the wrong way around in the form.
   // This needs to be sorted one day to avoid further confusion
   $result = $dbconn->Execute("SELECT $column[uname], $column[uid]
           FROM $pntable[users]
           WHERE ($column[uid] = '$chng_uid') OR ($column[uname] = '$chng_uid')");

   if(!$result->EOF) {
       list($uname, $uid) = $result->fields;
   } else {
     echo _USERNOEXIST;
     CloseTable();
     include 'footer.php';
     exit;
   }
   if (!pnSecAuthAction(0, 'Users::', "$uname::$chng_uid", ACCESS_DELETE)) {
      echo _MODIFYUSERSDELNOAUTH;
      CloseTable();
      include 'footer.php';
      exit;
   }
   echo ""._SURE2DELETE." " . pnVarPrepForDisplay(stripslashes($chng_uid)) . "?<br /><br />"
   ."[ <a href=\"admin.php?module=NS-User&amp;op=delUserConf&amp;del_uid=$uid&amp;authid=$authid\">"._YES
   ."</a> | <a href=\"admin.php?module=NS-User&amp;op=mod_users\">"._NO."</a> ]</center>";
   CloseTable();
   include("footer.php");
}

function deleteUserConfirm($del_uid)
{

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['users_column'];

    $result = $dbconn->Execute("SELECT $column[uname]
         FROM $pntable[users]
         WHERE $column[uid] = \"$del_uid\"");

    if(!$result->EOF) {
       list($uname) = $result->fields;
    } else {
      include 'header.php';
      echo _USERNOEXIST;
      include 'footer.php';
      exit;
    }
    if (!pnSecAuthAction(0, 'Users::', "$uname::$del_uid", ACCESS_DELETE)) {
       include 'header.php';
       echo _MODIFYUSERSDELNOAUTH;
       include 'footer.php';
       exit;
    }
    $column = &$pntable['user_perms_column'];
    $dbconn->Execute("DELETE FROM $pntable[user_perms]
                            WHERE $column[uid]='$del_uid'");
    if($dbconn->ErrorNo()<>0) {
       echo $dbconn->ErrorMsg();
       error_log("DB Error: " . $dbconn->ErrorMsg());
    }
    $column = &$pntable['group_membership_column'];
    $dbconn->Execute("DELETE FROM $pntable[group_membership]
                            WHERE $column[uid]='$del_uid'");
    if($dbconn->ErrorNo()<>0) {
       echo $dbconn->ErrorMsg();
       error_log("DB Error: " . $dbconn->ErrorMsg());
    }
    $column = &$pntable['users_column'];
    $dbconn->Execute("DELETE FROM $pntable[users]
                            WHERE $column[uid]='$del_uid'");
    if($dbconn->ErrorNo()<>0) {
       echo $dbconn->ErrorMsg();
       error_log("DB Error: " . $dbconn->ErrorMsg());
    }
    pnRedirect("admin.php");

}

function addUser($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Users::', $var['add_uname']."::", ACCESS_ADD)) {
       include 'header.php';
       echo _MODIFYUSERSADDNOAUTH;
       include 'footer.php';
       exit;
    }

    $add_pass = md5($var['add_pass']);
    if (!($var['add_uname'] && $var['add_email'] && $var['add_pass'])) {
       include("header.php");
       GraphicAdmin();
       OpenTable();
       echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
       CloseTable();

       OpenTable();
       echo "<center><b>"._NEEDTOCOMPLETE."</b><br /><br />"
       .""._GOBACK."";
       CloseTable();
       include("footer.php");
       return;
    }

    userCheck($var);
    if (!isset($stop)) {

    if (empty($var['add_user_viewemail'])) {
       $var['add_user_viewemail'] = 0;
    }
    $Default_Theme = pnConfigGetVar('Default_Theme');
    $commentlimit = pnConfigGetVar('commentlimit');
    $storynum = pnConfigGetVar('storyhome');
    $timezoneoffset = pnConfigGetVar('timezone_offset');
    $user_regdate = time();
    $column = &$pntable['users_column'];
	$existinguser = $dbconn->Execute("SELECT $column[uname] FROM $pntable[users] WHERE $column[uname]='".$var['add_uname']."'");
	if (!$existinguser->EOF) {
		include 'header.php';
		echo "<div align=center><font class=\"pn-title\">"._USEREXIST
			." <a href=\"admin.php?module=NS-User&op=modifyUser&chng_uid=$var[add_uname] \">(".pnVarPrepForDisplay($var['add_uname']).") "
			."</a></font></div><br>";
		echo "<a href=\"admin.php?module=NS-User&op=main\">"._ADDUSER."</a>";
		include 'footer.php';
	}	else {
		$uid = $dbconn->GenId($pntable['users']);
		$sql = "INSERT INTO $pntable[users] ($column[uid], $column[name],
						 $column[uname], $column[email], $column[femail], $column[url],
						 $column[user_regdate], $column[user_icq], $column[user_aim],
						 $column[user_yim], $column[user_msnm], $column[user_from],
						 $column[user_occ], $column[user_intrest], $column[user_viewemail],
						 $column[user_avatar], $column[user_sig], $column[pass], $column[timezone_offset])
						 values (".pnVarPrepForStore($uid).",'','".$var['add_uname']."','".$var['add_email']."','',
						 '','".pnVarPrepForStore($user_regdate)."','','','','','','','','".$var['add_user_viewemail']."','blank.gif',
						 '','".pnVarPrepForStore($add_pass)."','".pnVarPrepForStore($timezoneoffset)."')";
		$result = $dbconn->Execute($sql);
		if($dbconn->ErrorNo()<>0) {
		   echo $dbconn->ErrorNo() . ": " . $dbconn->ErrorMsg() . "<br />";
		   error_log("DB Error: " . $dbconn->ErrorMsg());
		   return;
		}

		// Add user to group
		// get the generated id
		$uid = $dbconn->PO_Insert_ID($pntable['users'],$column['uid']);
		$column = &$pntable['groups_column'];
		$result = $dbconn->Execute("SELECT $column[gid] FROM $pntable[groups] WHERE $column[name]='".pnConfigGetVar('defaultgroup')."'");

		if($dbconn->ErrorNo()<>0) {
		    echo $dbconn->ErrorNo(). "Get default group: ".$dbconn->ErrorMsg(). "<br />";
		    error_log ($dbconn->ErrorNo(). "Get default group: ".$dbconn->ErrorMsg(). "<br />");
		    return;
		}
		if (!$result->EOF) {
		  list($gid) = $result->fields;
		  $result->Close();
		  $column = &$pntable['group_membership_column'];
		  $result = $dbconn->Execute("INSERT INTO $pntable[group_membership] ($column[gid], $column[uid]) VALUES (".pnVarPrepForStore($gid).", ".pnVarPrepForStore($uid).")");

		  if($dbconn->ErrorNo()<>0) {
			 echo $dbconn->ErrorNo(). "Add to default group: ".$dbconn->ErrorMsg(). "<br />";
			 error_log ($dbconn->ErrorNo(). "Add to default group: ".$dbconn->ErrorMsg(). "<br />");
			 return;
		  }
		}
		include 'header.php';
		echo "<div align=center><font class=\"pn-title\">"
			."<a href=\"admin.php?module=NS-User&op=modifyUser&chng_uid=$uid\">".pnVarPrepForDisplay(stripslashes($var['add_uname']))." ("
			._USERID." $uid)</A> "._ADDED."</div></font><br>";
		echo "<a href=\"admin.php?module=NS-User&op=main\">"._ADDUSER."</a>";
		include 'footer.php';
	}
        } else {
        echo "$stop";
	include 'footer.php';
    }
}

function user_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_usergraphic['0'] = '';
    $sel_usergraphic['1'] = '';
    $sel_usergraphic[pnConfigGetVar('usergraphic')] = ' checked';
    $sel_minpass['3'] = '';
    $sel_minpass['5'] = '';
    $sel_minpass['8'] = '';
    $sel_minpass['10'] = '';
    $sel_minpass[pnConfigGetVar('minpass')] = ' selected';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._USERCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._MINAGE."</td><td class=\"pn-normal\"><input type=\"text\" name=\"xminage\" value=\"".pnConfigGetVar('minage')."\" size=\"2\" maxlength=\"2\" class=\"pn-normal\" /> "._MINAGEDESCR."\n"
        .'</td></tr><tr><td class="pn-normal">'
        ._USERPATH."</td><td><input type=\"text\" name=\"xuserimg\" value=\"".pnConfigGetVar('userimg')."\" size=\"50\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._USERGRAPHIC.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xusergraphic\" value=\"1\" class=\"pn-normal\"".$sel_usergraphic['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xusergraphic\" value=\"0\" class=\"pn-normal\"".$sel_usergraphic['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._PASSWDLEN.'</td><td>'
        .'<select name="xminpass" size"1" class="pn-normal">'
        ."<option value=\"3\"".$sel_minpass['3'].">3</option>\n"
        ."<option value=\"5\"".$sel_minpass['5'].">5</option>\n"
        ."<option value=\"8\"".$sel_minpass['8'].">8</option>\n"
        ."<option value=\"10\"".$sel_minpass['10'].">10</option>\n"
        .'</select>'
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function user_dynamic_data()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

   $currentlangfile = 'language/' . pnVarPrepForOS(pnUserGetLang()) . '/user.php';
   $defaultlangfile = 'language/' . pnVarPrepForOS(pnConfigGetVar('language')) . '/user.php';
   if (file_exists($currentlangfile)) {
       include $currentlangfile;
   } elseif (file_exists($defaultlangfile)) {
       include $defaultlangfile;
   }

    include ("header.php");
    GraphicAdmin();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
    CloseTable();

    // This section displays the dynamic fields
    // and the order in which they are displayed
	OpenTable();
	print '<center><font size="3" class="pn-title">'._DYNAMICDATA.'</b></font></center><br />'
    .'<table class=\'pn-normal\' border=\'1\' width=\'100%\'>'
    .'<tr>'
    .'<th>'._FIELDACTIVE.'</th>'
    .'<th colspan=\'2\'>'._FIELDLABEL.'</th>'
    .'<th>'._FIELDWEIGHT.'</th>'
    .'<th>'._FIELDTYPE.'</th>'
    .'<th>'._FIELDLENGTH.'</th>'
    .'<th>'._DELETE.'</th>'
//    .'<th>'._FIELDVALIDATION.'</th>'
    .'</tr>';

    $column = &$pntable['user_property_column'];
    $result = $dbconn->Execute("SELECT $column[prop_id], $column[prop_label],$column[prop_dtype],
                              $column[prop_length], $column[prop_weight], $column[prop_validation]
                              FROM $pntable[user_property] ORDER BY $column[prop_weight]");
    if($dbconn->ErrorNo()<>0) {
       echo $dbconn->ErrorNo(). "List User Properties: ".$dbconn->ErrorMsg(). "<br />";
       error_log ($dbconn->ErrorNo(). "List User Properties: ".$dbconn->ErrorMsg(). "<br />");
       return;
    }
    $active_count = 0;
    $true_count = 0;
    $total_count = $result->PO_RecordCount();
    $prop_weight = 0;
    while(list($prop_id,$prop_label,$prop_dtype,$prop_length,$prop_weight,$prop_validation) = $result->fields) {
        $result->MoveNext();

        $true_count++;
        if ($prop_weight<>0) {
            $active_count++;
            $next_prop_weight = $active_count + 1;
        }

        $eval_cmd = "\$prop_label_text=$prop_label;";
        @eval($eval_cmd);

        // display the proper icom and link to enable or disable the field
        switch (TRUE) {
            // Mandatory Images can't be disabled
            case ($prop_dtype == _UDCONST_MANDATORY):
                $img_cmd = '<img src="images/global/green_dot.gif" border=0 ALT="'._FIELD_REQUIRED.'">';
                break;
            case ($prop_weight <> 0):
                $img_cmd = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=deactivate_property&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src="images/global/green_dot.gif" border=0 ALT="'._FIELD_DEACTIVATE.'">'
                .'</a>';
                break;
            default:
                $img_cmd = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=activate_property&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src="images/global/red_dot.gif" border=0 ALT="'._FIELD_ACTIVATE.'">'
                .'</a>';
        }

        switch ($prop_dtype) {
            case _UDCONST_MANDATORY:
                $data_type_text = _UDT_MANDATORY;
                $data_length_text = _FIELD_NA;
                break;
            case _UDCONST_CORE:
                $data_type_text = _UDT_CORE;
                $data_length_text = _FIELD_NA;
                break;
            case _UDCONST_STRING:
                $data_type_text = _UDT_STRING;
                $data_length_text = $prop_length;
                break;
            case _UDCONST_TEXT:
                $data_type_text = _UDT_TEXT;
                $data_length_text = _FIELD_NA;
                break;
            case _UDCONST_FLOAT:
                $data_type_text = _UDT_FLOAT;
                $data_length_text = _FIELD_NA;
                break;
            case _UDCONST_INTEGER:
                $data_type_text = _UDT_INTEGER;
                $data_length_text = _FIELD_NA;
                break;
            default:
                $data_length_text = "";
                $data_type_text = "";
        }

        switch (TRUE) {
            case ($active_count == 0):
                $arrows = "&nbsp";
                break;
            case ($active_count == 1):
                $arrows = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=increase_weight&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src=images/global/down.gif border=0>'
                .'</a>';
                break;
            case ($true_count == $total_count):
                $arrows = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=decrease_weight&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src=images/global/up.gif border=0>'
                .'</a>';
                break;
            default:
                $arrows = '<img src=images/global/up.gif>&nbsp;<img src=images/global/down.gif>';
                $arrows = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=decrease_weight&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src=images/global/up.gif border=0>'
                .'</a>&nbsp;'
                ."<a href=admin.php?module=".$GLOBALS['module']."&amp;op=increase_weight&amp;property=$prop_id&amp;weight=$prop_weight>"
                .'<img src=images/global/down.gif border=0>'
                .'</a>';

        }

        if (($prop_dtype == _UDCONST_MANDATORY) || ($prop_dtype == _UDCONST_CORE)) {
            $del_text = _FIELD_NA;
        } else {
            $del_text = "<a href=admin.php?module=".$GLOBALS['module']."&amp;op=delete_property&amp;property=$prop_id>"
            ._DELETE
            .'</a>';
        }
//        .'<img src=\'images/global/green_dot.gif\'>'
        print '<tr><td width=\'5%\' align=\'center\'>'
        ."$img_cmd"
        .'</td>'
        .'<td width=\'12%\'>'.$prop_label.'</td>'
        .'<td width=\'12%\'>'.$prop_label_text.'</td>'
        .'<td width=\'10%\' align=\'center\'>'.$arrows.'</td>'
        .'<td width=\'15%\' align=\'center\'>'.$data_type_text.'</td>'
        .'<td width=\'10%\' align=\'center\'>'.$data_length_text.'</td>'
        .'<td width=\'10%\' align=\'center\'>'.$del_text.'</td>'
//        .'<td width=\'15%\' align=\'center\'>'._FIELD_NA.'</td>'
        .'</tr>';
    }
    print '</table>';
	CloseTable();

    print "<br>";

	OpenTable();

	print '<center><font size="3" class="pn-title">'._ADDFIELD.'</b></font></center><br />'
    .'<form action="admin.php" method="post">'
    .'<table class=\'pn-normal\'>'
    .'<tr>'
    .'<th align=\'left\'>'._FIELDLABEL.':</th>'
    .'<td>'
    .'<input type="text" name="label" value="" size="20" maxlength="20" class="pn-normal" />'
    .'&nbsp;'._ADDINSTRUCTIONS
    .'</td>'
    .'</tr>'
    .'<tr>'
    .'<th align=\'left\'>'._FIELDTYPE.':</th>'
    .'<td>'
    .'<select name="dtype" class="pn-normal">'
    .'<option value="'._UDCONST_STRING.'">'._UDT_STRING.'</option>' . "\n"
    .'<option value="'._UDCONST_TEXT.'">'._UDT_TEXT.'</option>' . "\n"
    .'<option value="'._UDCONST_FLOAT.'">'._UDT_FLOAT.'</option>' . "\n"
    .'<option value="'._UDCONST_INTEGER.'">'._UDT_INTEGER.'</option>' . "\n"
    .'</select>'
    .'</td>'
    .'</tr>'
    .'<tr>'
    .'<th align=\'left\'>'._FIELDLENGTH.':</th>'
    .'<td>'
    .'<input type="text" name="prop_len" value="" size="3" maxlength="3" class="pn-normal" />'
    .'&nbsp;'._STRING_INSTRUCTIONS
    .'</td>'
    .'</tr>'
    .'<tr><td></td><td>'
    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
    ."<input type=\"hidden\" name=\"op\" value=\"addDynamic\">"
    ."<input type=\"submit\" value=\""._SUBMIT."\">"
    .'</td></tr>'
    .'</table>'
    .'<input type="hidden" name="prop_weight" value="'.$next_prop_weight.'">'
    .'<input type="hidden" name="validation" value="">'
    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
    .'<input type="hidden" name="op" value="add_property">'
    .'</form>';
    CloseTable();
    include ("footer.php");
}

function add_property()
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    global $label, $dtype, $prop_weight, $validation, $prop_len;

    addVar($label, $dtype, $prop_weight, $validation, $prop_len );
    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

function delete_property_confirm($var) {
//    print_r($var);
    removeVar($var['label']);
//    pnRedirect("admin.php?module=NS-User&op=getDynamic");

}

function delete_property($var)
{
   list($dbconn) = pnDBGetConn();
   $pntable = pnDBGetTables();

   include("header.php");

   GraphicAdmin();
   OpenTable();
   echo "<center><font class=\"pn-title\"><b>"._USERADMIN."</b></font></center>";
   CloseTable();

   OpenTable();
   echo "<center><font class=\"pn-title\"><b>"._DELETEFIELD."</b></font><br /><br />";

   $column = &$pntable['user_property_column'];

   $result = $dbconn->Execute("SELECT $column[prop_id], $column[prop_label], $column[prop_weight]
           FROM $pntable[user_property]
           WHERE $column[prop_id] = '$var[property]'");

   if(!$result->EOF) {
       list($pid, $plabel, $pweight) = $result->fields;
   } else {
     echo _FIELD_NOEXIST;
     CloseTable();
     include 'footer.php';
     exit;
   }
   if ($pweight != 0) {
      echo _FIELD_DEACTIVATE;
      //CloseTable();
      //include 'footer.php';
      //exit;
   }
   if (!pnSecAuthAction(0, 'Users::', '::', ACCESS_ADMIN)) {
      echo _MODIFYUSERSDELNOAUTH;
      CloseTable();
      include 'footer.php';
      exit;
   }
   echo ""._FIELD_DEL_SURE." ".pnVarPrepForDisplay($plabel)."?<br /><br />"
   ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=delPropConf&amp;label=".$plabel."\">"._YES
   ."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&amp;op=getDynamic\">"._NO."</a> ]</center>";
   CloseTable();
   include("footer.php");
}

/**
 * add a user variable to the database
 * @access public
 * @author Gregor J. Rothfuss
 * @since 1.22 - 2002/02/01
 * @param name the name of the variable
 * @param type the type of the variable
 * @param weight the weight of the variable for display
 * @param validation the name of the validation function to apply
 * @param length the length of the variable for text fields
 * @returns bool
 * @return true on success, false on failure
 */
function addVar($name, $type, $weight, $validation, $length=0)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $propertiestable = $pntable['user_property'];
    $columns = &$pntable['user_property_column'];

    // Prevent bogus entries
    if (empty($name) || ($name == 'uid') || ($name == 'email') ||
    ($name == 'password') || ($name == 'uname')) {
        return false;
    }

    // Don't want duplicates either
    $query = "SELECT $columns[prop_label] from $propertiestable
              WHERE $columns[prop_label] = '" . pnVarPrepForStore($name) ."'";
    $result = $dbconn->Execute($query);

    if ($result->PO_RecordCount() != 0) {
        return false;
    }

    // datatype checks
    if (($type != _UDCONST_STRING) && ($type != _UDCONST_TEXT)
        && ($type != _UDCONST_FLOAT) && ($type != _UDCONST_INTEGER)) {
        return false;
    }

    // further checks
    if (($type == _UDCONST_STRING) && (!is_numeric($length) || ($length <=0))) {
        return false;
    }

    if (!is_numeric($weight)) {
        return false;
    }

    $query = "INSERT INTO $propertiestable
                  ($columns[prop_label],
                   $columns[prop_dtype],
                   $columns[prop_length],
                   $columns[prop_weight],
                   $columns[prop_validation])
                  VALUES ('".pnVarPrepForStore($name)."',
                          '".pnVarPrepForStore($type)."',
                          '".pnVarPrepForStore($length)."',
                          '".pnVarPrepForStore($weight)."',
                          '".pnVarPrepForStore($validation)."')";
    $dbconn->Execute($query);

    if($dbconn->ErrorNo() != 0) {
      return false;
    }

    return true;
}

/**
 * remove a user variable from the database
 * @access public
 * @author Gregor J. Rothfuss
 * @since 1.22 - 2002/02/01
 * @param name the name of the variable
 * @returns bool
 * @return true on success, false on failure
 */
function removeVar($name)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $propertiestable = $pntable['user_property'];
    $datatable = $pntable['user_data'];
    $propcolumns = &$pntable['user_property_column'];
    $datacolumns = &$pntable['userdata_column'];

    // Prevent deletion of core fields (duh)
    if (empty($name) || ($name == 'uid') || ($name == 'email') ||
    ($name == 'password') || ($name == 'uname')) {
        return false;
    }

    // get property id for cascading delete later
    $query = "SELECT $propcolumns[prop_id] from $propertiestable
              WHERE $propcolumns[prop_label] = '" . pnVarPrepForStore($name) ."'";
    $result = $dbconn->Execute($query);

    if ($result->PO_RecordCount() == 0) {
        return false;
    }

    list ($id) = $result->fields;


    // Remove variable from properties
    $query = "DELETE from $propertiestable
              WHERE $propcolumns[prop_label] = '" . pnVarPrepForStore($name) ."'";
    $dbconn->Execute($query);

    if($dbconn->ErrorNo() != 0) {
      return false;
    }

    // Remove variable from user data
    $query = "DELETE from $datatable
              WHERE $datacolumns[uda_propid] = '" . pnVarPrepForStore($id) ."'";
    $dbconn->Execute($query);

    // Temp Fix for deleting a label with no data.  Will fix after release.
    //if($dbconn->ErrorNo() != 0) {
    //  return false;
    //}

    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

function increase_weight($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!empty($var['property']) && !empty($var['weight'])) {
        $new_weight = $var['weight'] + 1;
        $column = &$pntable['user_property_column'];
        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=".pnVarPrepForStore($new_weight)."
                                    WHERE $column[prop_id]='".$var['property']."' AND $column[prop_weight]='".$var['weight']."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Increase Weight 1".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Increase Weight 1: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }

        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=".$var['weight']."
                                    WHERE $column[prop_id]<>'".$var['property']."' AND $column[prop_weight]='".pnVarPrepForStore($new_weight)."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Increase Weight 2".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Increase Weight 2: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }
    }
    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

function decrease_weight($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!empty($var['property']) && !empty($var['weight'])) {
        $new_weight = $var['weight'] - 1;
        $column = &$pntable['user_property_column'];
        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=".pnVarPrepForStore($new_weight)."
                                    WHERE $column[prop_id]='".$var['property']."' AND $column[prop_weight]='".$var['weight']."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Decrease Weight 1".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Decrease Weight 1: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }

        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=".$var['weight']."
                                    WHERE $column[prop_id]<>'".$var['property']."' AND $column[prop_weight]='".pnVarPrepForStore($new_weight)."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Decrease Weight 2".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Decrease Weight 2: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }
    }
    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

function activate_property ($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!empty($var['property'])) {
        $max_weight = 0;
        $column = &$pntable['user_property_column'];
        $result = $dbconn->Execute("SELECT MAX($column[prop_weight]) max_weight FROM $pntable[user_property]");

        if(!$result->EOF) {
            list($max_weight) = $result->fields;
        }
        $max_weight++;
        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=".pnVarPrepForStore($max_weight)."
                                    WHERE $column[prop_id]='".$var['property']."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Activate User Property 1".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Activate User Property 1: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }
    }
    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

// deactive a user property
function deactivate_property($var)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!empty($var['property'])) {

        $column = &$pntable['user_property_column'];

        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=0
                                    WHERE $column[prop_id]='".$var['property']."'");
        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Deactivate User Property 1".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Deactivate User Property 1: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }
        $result = $dbconn->Execute("UPDATE $pntable[user_property] SET $column[prop_weight]=$column[prop_weight]-1
                                    WHERE $column[prop_weight]>'".$var['weight']."'");

        if($dbconn->ErrorNo()<>0) {
           echo $dbconn->ErrorNo(). "Deactivate User Property 2: ".$dbconn->ErrorMsg(). "<br />";
           error_log ($dbconn->ErrorNo(). "Deactivate User Property 2: ".$dbconn->ErrorMsg(). "<br />");
           return;
        }
    }
    pnRedirect("admin.php?module=NS-User&op=getDynamic");
}

function userCheck($var)
{
   
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $uname = $var['add_uname'];
    $email = $var['add_email'];

    $res = pnVarValidate($email, 'email');
    if($res == false) {
        $stop = "<center><font class=\"pn-title\">"._ERRORINVEMAIL."</center></font><br>";
    }
    
    // Here we test the uname. Any value is possible but space.
    // On special character sets you might configure the server.
    // (was bug #455288)
    if ((!$uname) || !(ereg("^[[:print:]]+",$uname) && !ereg("[[:space:]]",$uname))) {
    /*if ((!$uname) || ($uname=="") || (ereg("[^a-zA-Z0-9_-]",$uname))) {*/
        $stop = "<center><font class=\"pn-title\">"._ERRORINVNICK."</center></font><br>";
    }
    if (strlen($uname) > 25) {
        $stop = "<center><font class=\"pn-title\">"._NICK2LONG."</center></font>";
    }
    if (preg_match('/((root)|(adm)|(linux)|(webmaster)|(admin)|(god)|(administrator)|(administrador)|(nobody)|(anonymous)|(anonimo)|
(anóîimo)|(operator))/iAD',$uname)) {
        $stop = "<center><font class=\"pn-title\">"._NAMERESERVED."</center></font>";
    }
    if (strrpos($uname,' ') > 0) {
        $stop = "<center><font class=\"pn-title\">"._NICKNOSPACES."</center></font>";
    }
    $column = &$pntable['users_column'];
    $existinguser = $dbconn->Execute("SELECT $column[uname] FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($uname)."
'");
    if (!$existinguser->EOF) {
        $stop = "<center><font class=\"pn-title\">"._NICKTAKEN."</center></font><br>";
    }
    $existinguser->Close();
    $existinguser = $dbconn->Execute("SELECT $column[email] FROM $pntable[users] WHERE $column[email]='".pnVarPrepForStore($email)."
'");
    if (!$existinguser->EOF) {
        $stop = "<center><font class=\"pn-title\">"._EMAILREGISTERED."</center></font><br>";
    }
    $existinguser->Close();
    return($stop);
}

function user_admin_setConfig($var)
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
	// $var[$v] = FixConfigQuotes($var[$v]);
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
   function user_admin_main($var)
   {
      switch($var['op'])
      {
         case "modifyUser":
            modifyUser($var['chng_uid']);
            break;

         case "updateUser":
            updateUser($var);
            break;

         case "delUser":
            deleteUser($var['chng_uid']);
            break;

         case "delUserConf":
            deleteUserConfirm($var['del_uid']);
            break;

         case "addUser":
            addUser($var);
            break;

         case "getConfig":
              user_admin_getConfig();
              break;

         case "setConfig":
              user_admin_setConfig($var);
              break;

         case "getDynamic":
              user_dynamic_data();
              break;

         case "add_property":
              add_property();
              break;

         case "delete_property":
              delete_property($var);
              break;

         case "delPropConf":
              delete_property_confirm($var);
              break;

         case "deactivate_property":
              deactivate_property ($var);
              break;

         case "activate_property":
              activate_property ($var);
              break;

         case "increase_weight":
              increase_weight ($var);
              break;

         case "decrease_weight":
              decrease_weight ($var);
              break;

         default:
            displayUsers();
            break;
      }
   }

?>
