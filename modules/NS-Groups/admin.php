<?php
// File: $Id: admin.php,v 1.3 2002/10/29 20:04:39 skooter Exp $
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
// Original Author of file: Jim McDonald
// Purpose of file:  Group administration
// ----------------------------------------------------------------------

if (!eregi('admin.php', $PHP_SELF)) { die ('Access Denied'); }

modules_get_language();
modules_get_manual();

/*
 * viewGroups - view groups
 * Takes no parameters
 *
 */
function viewGroups()
{
   $module = pnVarCleanFromInput('module');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $grouptable = $pntable['groups'];
    $groupcolumn = &$pntable['groups_column'];

    include("header.php");
    GraphicAdmin();

    // Heading
    OpenTable();
    echo "<CENTER><FONT SIZE=\"4\"<B>"._GROUPADMIN."</B></FONT><BR>";

    if (!pnSecAuthAction(0, 'Groups::', '::', ACCESS_EDIT)) {
        echo _GROUPSNOAUTH;
        include 'footer.php';
        return;
    }

    // Options
    if (pnSecAuthAction(0, 'Groups::', '::', ACCESS_ADD)) {
        echo '<BR>
              <TABLE BORDER="0" WIDTH="100%">
              <TR>
              <TD>
              <CENTER>
                  <A CLASS="pn-title" HREF="admin.php?module='.$module.'&amp;op=secnewgroup">'._ADDGROUP.'</A>
              </CENTER>
              </TD>
              </TR><BR>
              </TABLE>
              <BR>';
    }

    // Get and display current groups
    $query = "SELECT $groupcolumn[gid],
                     $groupcolumn[name]
              FROM $grouptable
              ORDER BY $groupcolumn[name]";
    $result = $dbconn->Execute($query);
    if (!$result->EOF) {
        echo "<TABLE BORDER=\"5\">".
             "<TR FONT=\"pn-title\">".
             "<TD><CENTER>"._GROUPNAME."</CENTER></TD>".
             "<TD>&nbsp;</TD>".
             "</TR>";

        while(list($gid, $name) = $result->fields) {
            echo '<TR>';
            if (pnSecAuthAction(0, 'Groups::', "$name::$gid", ACCESS_EDIT)) {
                 echo "<TD><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroup&amp;gid=$gid\">".pnVarPrepForDisplay($name)."</A></TD>";
                 if (pnSecAuthAction(0, 'Groups::', "$name::$gid", ACCESS_DELETE)) {
                     echo "<TD>"
                     ."<FORM ACTION=\"admin.php\" METHOD=\"POST\">"
                     ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
                     ."<INPUT TYPE=\"HIDDEN\" NAME=\"op\" VALUE=\"secdeletegroup\">"
                     ."<input type=\"hidden\" NAME=\"gid\" VALUE=\"$gid\">"
                     .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'
                     .'<input type="submit" value="'._DELETE.'">'
                     .'</form>';
                 } else {
                     echo "<TD>&nbsp;</TD>";
                 }
                 echo "</TR>";
            }
            $result->MoveNext();
        }
        echo "</TABLE>";

    }

    CloseTable();
    include("footer.php");
}

/*
 * viewGroup - view a group
 * Takes one parameter:
 * - the gid
 */
function viewGroup()
{
    $gid = pnVarCleanFromInput('gid');
    $module = pnVarCleanFromInput('module');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $grouptable = $pntable['groups'];
    $groupcolumn = &$pntable['groups_column'];
    $groupmembershiptable = $pntable['group_membership'];
    $groupmembershipcolumn = &$pntable['group_membership_column'];
    $usertable = $pntable['users'];
    $usercolumn = &$pntable['users_column'];

    include("header.php");
    GraphicAdmin();

    // Get details on current group
    $query = "SELECT $groupcolumn[name]
              FROM $grouptable
              WHERE $groupcolumn[gid]=".(int)pnVarPrepForStore($gid);
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    // Heading
    OpenTable();
    echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
    ._GROUPADMIN."</B></FONT></A><font class=\"pn-title\"><B>: ".pnVarPrepForDisplay($gname)."</B></FONT></CENTER>";

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        echo _GROUPSNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    // Group options
    echo "<BR>".
         "<TABLE BORDER=\"0\" WIDTH=\"100%\">".
         "<TR>";
    if (pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        echo "<TD><A HREF=\"admin.php?module=".$module."&amp;op=secselectuserforgroup&amp;gid=$gid\"><CENTER><font class=\"pn-title\">"
        ._ADDUSERTOGROUP."</FONT></CENTER></A></TD>".
         "<TD><A HREF=\"admin.php?module=".$module."&amp;op=secmodifygroup&amp;gid=$gid\"><CENTER><font class=\"pn-title\">"
         ._MODIFYGROUP."</FONT></CENTER></A></TD>";
/*        if (pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_DELETE)) {
            echo "<TD><A HREF=\"admin.php?module=".$module."&amp;op=secdeletegroup&amp;gid=$gid&amp;ok=0\"><CENTER><font class=\"pn-title\">"
            ._DELETEGROUP."</FONT></CENTER></A></TD>";
        }
*/
    }
    echo "</TR>".
         "</TABLE>".
         "<BR>";


    // Get users in this group
    $query = "SELECT $groupmembershipcolumn[uid]
              FROM $groupmembershiptable
              WHERE $groupmembershipcolumn[gid]=".(int)pnVarPrepForStore($gid);
    $result = $dbconn->Execute($query);
    if (!$result->EOF) {
        for(;list($uid) = $result->fields;$result->MoveNext() ) {
            $uids[] = $uid;
        }
        $result->Close();
        $uidlist=implode(",", $uids);

        // Get names of users
        $query = "SELECT $usercolumn[uname],
                         $usercolumn[uid]
                  FROM $usertable
                  WHERE $usercolumn[uid] IN ($uidlist)
                  ORDER BY $usercolumn[uname]";
        $result = $dbconn->Execute($query);

        echo "<CENTER><B>"._USERSINGROUP."</B><BR>".
             "<TABLE BORDER=\"1\">".
             "<TR FONT=\"pn-title\">".
             "<TD><CENTER>"._USERNAME."</CENTER></TD>".
             "<TD>&nbsp;</TD>".
             "</TR>";

        while(list($uname, $uid) = $result->fields) {
            echo "<TR>".
                 "<TD>".pnVarPrepForDisplay($uname)."</TD>";
            if (pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_DELETE)) {
                echo "<TD><A HREF=\"admin.php?module=".$module."&amp;op=secdeleteuserfromgroup&amp;uid=$uid&amp;gid=$gid\">"
                ._DELETE."</A></TD>";
            } else {
                echo "<TD>&nbsp;</TD>";
            }
            echo "</TR>";
            $result->MoveNext();
        }
        $result->Close();
        echo "</TABLE></CENTER><BR>";
    } else {
        echo "<CENTER><B>"._NOONEINGROUP."</B></CENTER>";
    }

    CloseTable();
    include("footer.php");

}

/*
 * newGroup - create a new group
 * Takes no parameters
 */
function newGroup()
{
    $module = pnVarCleanFromInput('module');
    include("header.php");
    GraphicAdmin();

    // Heading
    OpenTable();
    echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
    ._GROUPADMIN."</B></FONT></A></CENTER>";
    echo "<BR>";

    if (!pnSecAuthAction(0, 'Groups::', '::', ACCESS_ADD)) {
        echo _GROUPSADDNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    echo "<FORM ACTION=\"admin.php\" METHOD=\"POST\">"
         ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
         ."<INPUT TYPE=\"HIDDEN\" NAME=\"op\" VALUE=\"secaddgroup\">"
	 ."<INPUT TYPE=\"HIDDEN\" NAME=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ._GROUPNAME. ": <INPUT TYPE=\"TEXT\" NAME=\"gname\"><P>"
         ."<INPUT TYPE=SUBMIT VALUE=\""._NEWGROUP."\">"
         ."</FORM>";

    CloseTable();
    include("footer.php");
}

/*
 * addGroup - add a group
 * Takes one parameter:
 * - the group name
 */
function addGroup()
{
    $gname = pnVarCleanFromInput('gname');
    $module = pnVarCleanFromInput('module');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Groups::', "$gname::", ACCESS_ADD)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo _GROUPSADDNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    $grouptable = $pntable['groups'];
    $groupcolumn = &$pntable['groups_column'];

    // Confirm that this group does not already exist
    $query = "SELECT COUNT(*) FROM $grouptable
              WHERE $groupcolumn[name] = \"$gname\"";

    $result = $dbconn->Execute($query);

    list($count) = $result->fields;
    $result->Close();
    if ($count == 1) {
        include("header.php");
        GraphicAdmin();
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
        echo "<BR>";
        echo _GROUPALREADYEXISTS;
    } else {
        $nextId = $dbconn->GenId($grouptable);
        $query = "INSERT INTO $grouptable
                  VALUES ($nextId, \"$gname\")";

        $dbconn->Execute($query);

        pnRedirect('admin.php?module='.$module.'&op=secviewgroups');
    }
}

/*
 * deleteGroup - delete a group
 * Takes two parameters:
 * - the group ID
 * - confirmation
 */
function deleteGroup()
{
    $module = pnVarCleanFromInput('module');
    list($gid,
	 $ok) = pnVarCleanFromInput('gid',
				    'ok');

    if(!isset($ok)) {
	$ok = 0;
    }

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $groupstable = $pntable['groups'];
    $groupscolumn = &$pntable['groups_column'];
    // Get details on current group
    $query = "SELECT $groupscolumn[name]
              FROM $groupstable
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid);
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_DELETE)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
        CloseTable();
        echo _GROUPSDELNOAUTH;
        include 'footer.php';
        return;
    }

    if ($ok != 1) {
        include("header.php");
        GraphicAdmin();

        // Heading
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
        echo "<BR>
              <CENTER>".
              _DELETEGROUPSURE.
             "<FORM ACTION=\"admin.php\" METHOD=\"POST\">
              <input type=\"hidden\" name=\"module\" value=\"".$module."\">
              <INPUT TYPE=\"HIDDEN\" NAME=\"op\" VALUE=\"secdeletegroup\">
              <INPUT TYPE=\"HIDDEN\" NAME=\"ok\" VALUE=\"1\">
              <INPUT TYPE=\"HIDDEN\" NAME=\"gid\" VALUE=\"$gid\">
	      <INPUT TYPE=\"HIDDEN\" NAME=\"authid\" value=\"" . pnSecGenAuthKey() . "\">
              <INPUT TYPE=\"SUBMIT\" VALUE=\"".
              _YES.
             "\">
              </FORM>
              <BR>
              <A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\">".
              _NO.
             "</A>
              </CENTER>";

        CloseTable();
        include("footer.php");
    } else {
        $groupmembershiptable = $pntable['group_membership'];
        $groupmembershipcolumn = &$pntable['group_membership_column'];
        $grouppermstable = $pntable['group_perms'];
        $grouppermscolumn = &$pntable['group_perms_column'];
        $groupstable = $pntable['groups'];
        $groupscolumn = &$pntable['groups_column'];

        // Delete permissions for the group
        $query = "DELETE FROM $grouppermstable
                  WHERE $grouppermscolumn[gid]=".pnVarPrepForStore($gid)."";
        $dbconn->Execute($query);

        // Delete membership of the group
        $query = "DELETE FROM $groupmembershiptable
                  WHERE $groupmembershipcolumn[gid]=".pnVarPrepForStore($gid)."";
        $dbconn->Execute($query);

        // Delete the group itself
        $query = "DELETE FROM $groupstable
                  WHERE $groupscolumn[gid]=".pnVarPrepForStore($gid)."";
        $dbconn->Execute($query);

        pnRedirect('admin.php?module='.$module.'&op=secviewgroups');
    }
}

/*
 * selectUserForGroup - select a user to add to
 *                      a group
 * Takes one parameter:
 * - the group ID
 */
function selectUserForGroup()
{
    $module = pnVarCleanFromInput('module');
    $gid = pnVarCleanFromInput('gid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $grouptable = $pntable['groups'];
    $groupcolumn = &$pntable['groups_column'];
    $groupmembershiptable = $pntable['group_membership'];
    $groupmembershipcolumn = &$pntable['group_membership_column'];
    $usertable = $pntable['users'];
    $usercolumn = &$pntable['users_column'];

    include("header.php");
    GraphicAdmin();

    // Get details on current group
    $query = "SELECT $groupcolumn[name]
              FROM $grouptable
              WHERE $groupcolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    // Heading
    OpenTable();
    echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
    ._GROUPADMIN."</B></FONT></A><font class=\"pn-title\"><B>: ".pnVarPrepForDisplay($gname)."</B></FONT></CENTER>";
    echo "<BR>";

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        CloseTable();
        echo _GROUPSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    // Get list of users already in this group
    $query = "SELECT $groupmembershipcolumn[uid]
              FROM $groupmembershiptable
              WHERE $groupmembershipcolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    $uids = array();
    while(list($uid) = $result->fields) {
        $uids[] = $uid;
        $result->MoveNext();
    }
    $uidlist = implode(",", $uids);
    $result->Close();

    // Get list of eligible users
    $query = "SELECT $usercolumn[uid],
                     $usercolumn[uname]
              FROM $usertable";
    if (!empty($uidlist)) {
        $query .= " WHERE $usercolumn[uid] NOT IN ($uidlist)";
    }
    $query .= " ORDER BY $usercolumn[uname]";
    $result = $dbconn->Execute($query);
    if (!$result->EOF) {
        echo "<BR>"
             ."<FORM ACTION=\"admin.php\" METHOD=\"POST\">"
             ._USERTOADD.": "
              ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
             ."<INPUT TYPE=\"HIDDEN\" NAME=\"op\" VALUE=\"secaddusertogroup\">"
	     ."<INPUT TYPE=\"HIDDEN\" NAME=\"authid\" VALUE=\"" . pnSecGenAuthKey() . "\">"
             ."<INPUT TYPE=\"HIDDEN\" NAME=\"gid\" VALUE=\"$gid\">"
             ."<SELECT NAME=\"uid\">";

        while(list($uid, $uname) = $result->fields) {
            echo "<OPTION VALUE=\"$uid\">".pnVarPrepForDisplay($uname)."</OPTION>";
            $result->MoveNext();
        }
       echo "</SELECT>".
            "  <INPUT TYPE=\"SUBMIT\" VALUE=\""._CONFIRM."\">".
            "</FORM>";
    } else {
        echo "<B>All users are currently in this group</B>";
    }
    $result->Close();

    CloseTable();
    include("footer.php");
}

/*
 * addUserToGroup - add a user to a group
 * Takes two parameters:
 * - the user ID
 * - the group ID
 */
function addUserToGroup()
{
    $module = pnVarCleanFromInput('module');
    list($uid,
	 $gid) = pnVarCleanFromInput('uid',
				     'gid');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Get details on current group
    $groupstable = $pntable['groups'];
    $groupscolumn = &$pntable['groups_column'];
    $query = "SELECT $groupscolumn[name]
              FROM $groupstable
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
        CloseTable();
        echo _GROUPSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $groupmembershiptable = $pntable['group_membership'];
    $groupmembershipcolumn = &$pntable['group_membership_column'];

    $query = "INSERT INTO $groupmembershiptable
              ($groupmembershipcolumn[uid],
               $groupmembershipcolumn[gid])
              VALUES (".(int)pnVarPrepForStore($uid).", ".(int)pnVarPrepForStore($gid).")";
    $dbconn->Execute($query);

    pnredirect('admin.php?module='.$module.'&op=secviewgroup&gid='.$gid);
}

/*
 * deleteUserFromGroup - delete a user from a group
 * Takes two parameters:
 * - the user ID
 * - the group ID
 */
function deleteUserFromGroup()
{
    $module = pnVarCleanFromInput('module');
    list($uid,
	 $gid) = pnVarCleanFromInput('uid',
				     'gid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Get details on current group
    $groupstable = $pntable['groups'];
    $groupscolumn = &$pntable['groups_column'];
    $query = "SELECT $groupscolumn[name]
              FROM $groupstable
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForStore($gname)."</CENTER>";
        CloseTable();
        echo _GROUPSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $groupmembershiptable = $pntable['group_membership'];
    $groupmembershipcolumn = &$pntable['group_membership_column'];

    $query = "DELETE FROM $groupmembershiptable
              WHERE $groupmembershipcolumn[uid]=".pnVarPrepForStore($uid)."
                AND $groupmembershipcolumn[gid]=".pnVarPrepForStore($gid)."";
    $dbconn->Execute($query);

    pnRedirect('admin.php?module='.$module.'&op=secviewgroup&gid='.$gid);
}

/*
 * modifyGroup - modify group details
 * Takes one parameter:
 * - the group ID
 */
function modifyGroup()
{
    $module = pnVarCleanFromInput('module');
    $gid = pnVarCleanFromInput('gid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $groupstable = $pntable['groups'];
    $groupscolumn = &$pntable['groups_column'];

    include("header.php");
    GraphicAdmin();

    $query = "SELECT $groupscolumn[name]
              FROM $groupstable
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($gname) = $result->fields;
    $result->Close();

    // Heading
    OpenTable();
    echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
    ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
    echo "<br>";

    if (!pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_EDIT)) {
        CloseTable();
        echo _GROUPSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    echo "<form action=\"admin.php\" method=\"post\">"
         ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
         ."<input type=\"hidden\" name=\"op\" value=\"secrenamegroup\">"
         ."<input type=\"hidden\" name=\"gid\" value=\"$gid\">"
	 ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ._GROUPNAME. ": <input type=\"text\" name=\"gname\" value=\"$gname\"><P>"
         ."<input type=submit value=\""._RENAMEGROUP."\">"
         ."</form>";

    if (pnSecAuthAction(0, 'Groups::', "$gname::$gid", ACCESS_DELETE)) {
        echo "<FORM ACTION=\"admin.php\" METHOD=\"POST\">"
            ."<input type=\"hidden\" name=\"module\" value=\"".$module."\">"
            ."<INPUT TYPE=\"HIDDEN\" NAME=\"op\" VALUE=\"secdeletegroup\">"
            ."<input type=\"hidden\" NAME=\"gid\" VALUE=\"$gid\">"
            .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'
            .'<input type="submit" value="'._DELETE.'">'
            .'</form>';
    } else {
       echo "<TD>&nbsp;</TD>";
    }
    CloseTable();
    include("footer.php");
}

/*
 * renameGroup - rename group
 * Takes two parameters:
 * - the group ID
 * - the new group name
 */
function renameGroup()
{
    $module = pnVarCleanFromInput('module');
    list($gid,
	 $gname) = pnVarCleanFromInput('gid',
				       'gname');

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $groupstable = $pntable['groups'];
    $groupscolumn = &$pntable['groups_column'];

    // Get details on current group
    $query = "SELECT $groupscolumn[name]
              FROM $groupstable
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        die("No such group ID $gid");
    }

    list($oldgname) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Groups::', "$oldgname::$gid", ACCESS_EDIT)) {
        include 'header.php';
        GraphicAdmin();
        OpenTable();
        echo "<CENTER><A HREF=\"admin.php?module=".$module."&amp;op=secviewgroups\" CLASS=\"pn-title\"><FONT SIZE=\"4\"<B>"
        ._GROUPADMIN."</B></FONT></A>: ".pnVarPrepForDisplay($gname)."</CENTER>";
        CloseTable();
        echo _GROUPSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $query = "UPDATE $groupstable
              SET $groupscolumn[name]=\"$gname\"
              WHERE $groupscolumn[gid]=".(int)pnVarPrepForStore($gid)."";
    $dbconn->Execute($query);

    pnRedirect('admin.php?module='.$module.'&op=secviewgroup&gid='.$gid);
}


function groups_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   if (!pnSecAuthAction(0, 'Groups::', '::', ACCESS_EDIT)) {
       include 'header.php';
       echo _GROUPSNOAUTH;
       include 'footer.php';
   } else {
       switch($op) {

        case "secviewgroups";
            viewGroups();
            break;

        case "secviewgroup";
            viewGroup();
            break;

        case "secnewgroup";
            newGroup();
            break;

        case "secaddgroup";
            addGroup();
            break;

        case "secdeletegroup";
            deleteGroup();
            break;

        case "secselectuserforgroup";
            selectUserForGroup();
            break;

        case "secaddusertogroup";
            addUserToGroup();
            break;

        case "secdeleteuserfromgroup";
            deleteUserFromGroup();
            break;

        case "secmodifygroup";
            modifyGroup();
            break;

        case "secrenamegroup";
            renameGroup();
            break;

        default:
           viewGroups();
           break;
       }
   }
}
?>