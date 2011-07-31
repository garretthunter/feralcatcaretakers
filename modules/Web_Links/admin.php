<?php
// File: $Id: admin.php,v 1.8 2002/11/14 21:40:26 larsneo Exp $
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
// 10-15-2002:skooter      - Cross Site Scripting security fixes and also using 
//                           pnAPI for displaying data.

if (!eregi("admin.php", $PHP_SELF)) { die ("Access Denied"); }

$ModName = $module;
modules_get_language();
modules_get_manual();

include_once ("modules/$ModName/wl-categories.php");
include_once ("modules/$ModName/wl-util.php");

/**
 * Links Modified Web Links
 */

function links()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-logo\">"._LINKSMAINCAT."</font><br>";
    $result=$dbconn->Execute("SELECT count(*) FROM $pntable[links_links]");

    list($numrows) = $result->fields;
    echo "<font class=\"pn-normal\">"._THEREARE." <b>".pnVarPrepForDisplay($numrows)."</b> "._LINKSINDB."</font></center>";
    CloseTable();

    $cl=CatList(0,0);

    if ((!pnSecAuthAction(0, 'Web Links::Category', '::', ACCESS_EDIT)) &&
        (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT))) {
        echo _WEBLINKSNOAUTH;
        include 'footer.php';
        exit;
    }

    // Status
    if (pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT)) {
        $column = &$pntable['links_modrequest_column'];
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_modrequest] WHERE $column[brokenlink]=1");

        list($totalbrokenlinks) = $result->fields;
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_modrequest] WHERE $column[brokenlink]=0");

        list($totalmodrequests) = $result->fields;
        OpenTable();
        echo "<center><font class=\"pn-normal\">[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksCleanVotes&authid=".pnSecGenAuthKey()."\">"._CLEANLINKSDB."</a> | "
            ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksListBrokenLinks\">"._BROKENLINKSREP." (".pnVarPrepForDisplay($totalbrokenlinks).")</a> | "
            ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksListModRequests\">"._LINKMODREQUEST." (".pnVarPrepForDisplay($totalmodrequests).")</a> | "
            ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksLinkCheck\">"._VALIDATELINKS."</a> ]</font></center>";
        CloseTable();

    }

    // Add category
    LinksNewCat();

    // Modify category
    LinksEditCat();

    // Add link
    if (pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_ADD)) {
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_categories]");

        list($numrows) = $result->fields;
        if ($numrows>0) {
            OpenTable();
            if (pnUserLoggedIn()) {
                $submitter = pnUserGetVar('uname');
            }
            echo "<form method=\"post\" action=\"admin.php\">"
                ."<font class=\"pn-title\"><b>"._ADDNEWLINK."</b></font><br><br>"
                .""._PAGETITLE.": <input type=\"text\" name=\"title\" size=\"50\" maxlength=\"100\"><br>"
                .""._PAGEURL.": <input type=\"text\" name=\"url\" size=\"75\" maxlength=\"254\" value=\"http://\"><br>";
            echo ""._CATEGORY.": <select name=\"cat\">$cl</select><br><br><br>"
                .""._DESCRIPTION255."<br><textarea name=\"description\" cols=\"60\" rows=\"5\"></textarea><br><br><br>"
                .""._NAME.": <input type=\"text\" name=\"name\" size=\"30\" maxlength=\"60\"><br>"
                .""._EMAIL.": <input type=\"text\" name=\"email\" size=\"30\" maxlength=\"60\"><br><br>"
                ."<input type=\"hidden\" name=\"submitter\" value=\"$submitter\">"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"op\" value=\"LinksAddLink\">"
                ."<input type=\"hidden\" name=\"new\" value=\"0\">"
                ."<input type=\"hidden\" name=\"lid\" value=\"0\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<center><input type=\"submit\" value=\""._ADDURL."\"><br>"
                ."</form>";
            CloseTable();
        }
    }

    // Modify link
    if (pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_MODERATE)) {
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links]");

        list($numrows) = $result->fields;
        if ($numrows>0) {
            OpenTable();
            echo "<form method=\"post\" action=\"admin.php\">"
                ."<font class=\"pn-title\"><b>"._MODLINK."</b></font><br><br>"
                .""._LINKID.": <input type=\"text\" name=\"lid\" size=\"12\" maxlength=\"11\">&nbsp;&nbsp;"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"op\" value=\"LinksModLink\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"submit\" value=\""._MODIFY."\">"
                ."</form>";
            CloseTable();
        }
    }

    // Validate links
    if (pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_ADD)) {
        $column = &$pntable['links_newlink_column'];
        $result = $dbconn->Execute("SELECT $column[lid], $column[cat_id], $column[title], $column[url], $column[description], $column[name], $column[email], $column[submitter] FROM $pntable[links_newlink] ORDER BY $column[lid]");
        if (!$result->EOF) {
            OpenTable();
            echo "<center><font class=\"pn-title\"><b>"._LINKSWAITINGVAL."</b></font></center><br><br>";
//ADODBtag MoveNext while+list+row
            while(list($lid, $cid, $title, $url, $description, $name, $email, $submitter) = $result->fields) {

                $result->MoveNext();
                if ($submitter == "") {
                    $submitter = _NONE;
                }
                echo "<form action=\"admin.php\" method=\"post\">"
                    ."<b>"._LINKID.": $lid</b><br><br>"
                    .""._SUBMITTER.":  ".pnVarPrepForDisplay($submitter)."<br>"
                    .""._PAGETITLE.": <input type=\"text\" name=\"title\" value=\"".pnVarPrepForDisplay($title)."\" size=\"50\" maxlength=\"100\"><br>"
                    .""._PAGEURL.": <input type=\"text\" name=\"url\" value=\"".pnVarPrepForDisplay($url)."\" size=\"75\" maxlength=\"254\">&nbsp;[ <a target=\"_blank\" href=\"".pnVarPrepForDisplay($url)."\">"._VISIT."</a> ]<br>"
                    .""._WL_DESCRIPTION.": <br><textarea name=\"description\" cols=\"60\" rows=\"10\">".pnVarPrepHTMLDisplay($description)."</textarea><br>"
                    .""._NAME.": <input type=\"text\" name=\"name\" size=\"20\" maxlength=\"100\" value=\"".pnVarPrepForDisplay($name)."\">&nbsp;&nbsp;"
                    .""._EMAIL.": <input type=\"text\" name=\"email\" size=\"20\" maxlength=\"100\" value=\"".pnVarPrepForDisplay($email)."\"><br>"
                    ."<input type=\"hidden\" name=\"new\" value=\"1\">"
                    ."<input type=\"hidden\" name=\"lid\" value=\"".pnVarPrepForDisplay($lid)."\">"
                    ."<input type=\"hidden\" name=\"submitter\" value=\"".pnVarPrepForDisplay($submitter)."\">"
                    .""._CATEGORY.": <select name=\"cat\">".CatList(0,$cid)."</select>"
                    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                    ."<input type=\"hidden\" name=\"op\" value=\"LinksAddLink\"><input type=\"submit\" value="
                    ._ADD."> [ <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksDelNew&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">"
                    ._DELETE."</a> ]</form><br /><hr noshade /><br />";
            }
            CloseTable();
        }
    }

// Access Web Links Settings
    OpenTable();
    echo "<font class=\"pn-title\"><b>"._WEBLINKSCONF."</b></font><br /><br />";
    echo "<a href=\"admin.php?module=".$GLOBALS['module']."&op=getConfig\">"._WEBLINKSCONF."</a>";
    CloseTable();
    include 'footer.php';
}

function LinksModLink()
{
    list($lid,
         $rid) = pnVarCleanFromInput('lid',
                                     'rid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._MODLINK."</b></font></center><br><br>";
    $column = &$pntable['links_links_column'];
    $catcolumn = &$pntable['links_categories_column'];
    $result = $dbconn->Execute("SELECT $column[cat_id],
                                     $catcolumn[title],
                                     $column[title],
                                     $column[url],
                                     $column[description],
                                     $column[name],
                                     $column[email],
                                     $column[hits]
                              FROM $pntable[links_links], $pntable[links_categories]
                              WHERE $column[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $column[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
    if (!$result->EOF) {
//ADODBtag list+row
        list($cid, $cattitle, $title, $url, $description, $name, $email, $hits) = $result->fields;


        if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_MODERATE)) {
            echo _WEBLINKSMODERATENOAUTH;
            CloseTable();
            include 'footer.php';
            return;
        }

        if (pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_EDIT)) {
            echo "<form action=admin.php method=post>"
                .""._LINKID.": <b>".pnVarPrepForDisplay($lid)."</b><br>"
                .""._PAGETITLE.": <input type=\"text\" name=\"title\" value=\"".pnVarPrepForDisplay($title)."\" size=\"50\" maxlength=\"100\"><br>"
                .""._PAGEURL.": <input type=\"text\" name=\"url\" value=\"".pnVarPrepForDisplay($url)."\" size=\"75\" maxlength=\"254\">&nbsp;"
                ."[ <a href=\"$url\">Visit</a> ]<br>"
                .""._WL_DESCRIPTION.":<br><textarea name=\"description\" cols=\"60\" rows=\"10\">".pnVarPrepHTMLDisplay($description)."</textarea><br>"
                .""._NAME.": <input type=\"text\" name=\"name\" size=\"50\" maxlength=\"100\" value=\"".pnVarPrepForDisplay($name)."\"><br>"
                .""._EMAIL.": <input type=\"text\" name=\"email\" size=\"50\" maxlength=\"100\" value=\"".pnVarPrepForDisplay($email)."\"><br>"
                .""._HITS.": <input type=\"text\" name=\"hits\" value=\"".pnVarPrepForDisplay($hits)."\" size=\"12\" maxlength=\"11\"><br>"
                ."<input type=\"hidden\" name=\"lid\" value=\"".pnVarPrepForDisplay($lid)."\">"
                .""._CATEGORY.": <select name=\"cat\">".CatList(0,$cid)
                ."</select>"
                ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                ."<input type=\"hidden\" name=\"op\" value=\"LinksModLinkS\">"
                ."<input type=\"submit\" value=\""._MODIFY."\"> [ <a href=\"admin.php?module="
                .$GLOBALS['module']."&op=LinksDelLink&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">"._DELETE."</a> ]</form><br>";
            CloseTable();
        }

        if (pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_EDIT)) {
            /* Modify or Add Editorial */
            $column = &$pntable['links_editorials_column'];
            $resulted2 = $dbconn->Execute("SELECT $column[adminid], $column[editorialtimestamp], $column[editorialtext], $column[editorialtitle] FROM $pntable[links_editorials] WHERE $column[linkid]=".pnVarPrepForStore($lid)."");
            OpenTable();
            /* if returns 'bad query' status 0 (add editorial) */
            if ($resulted2->EOF) { // !
                echo "<center><font class=\"pn-title\"><b>"._ADDEDITORIAL."</b></font></center><br><br>"
                    ."<form action=\"admin.php\" method=\"post\">"
                    ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                    ."<input type=\"hidden\" name=\"linkid\" value=\"".pnVarPrepForDisplay($lid)."\">"
                    .""._EDITORIALTITLE.":<br><input type=\"text\" name=\"editorialtitle\" "
                    ."size=\"50\" maxlength=\"100\"><br>"
                    .""._EDITORIALTEXT.":<br><textarea name=\"editorialtext\" cols=\"60\" rows=\"10\"></textarea><br>"
                    ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                    ."<input type=\"hidden\" name=\"op\" value=\"LinksAddEditorial\"><input type=\"submit\" value=\"Add\">"
                    ."</form>";
            } else {
                /* if returns 'cool' then status 1 (modify editorial) */
//ADODBtag MoveNext while+list+row
                while(list($adminid, $editorialtimestamp, $editorialtext, $editorialtitle) = $resulted2->fields) {

                    $resulted2->MoveNext();
				/* Better to use ADODB to do this stuff
				 * skooter 2002/10/15
                    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $editorialtimestamp, $editorialtime);
                    $editorialtime = strftime("%F",mktime($editorialtime[4],$editorialtime[5],$editorialtime[6],$editorialtime[2],$editorialtime[3],$editorialtime[1]));
                    $date_array = explode("-", $editorialtime);
                    $timestamp = mktime(0, 0, 0, $date_array["1"], $date_array["2"], $date_array["0"]);
                    $formatted_date = date("F j, Y", $timestamp);
				*/
					$formatted_date = date("F d, Y", $dbconn->UnixTimestamp($editorialtimestamp));
                    echo "<center><font class=\"pn-title\"><b>Modify Editorial</b></font></center><br><br>"
                        ."<form action=\"admin.php\" method=\"post\">"
                        .""._AUTHOR.": ".pnVarPrepForDisplay($adminid)."<br>"
                        .""._DATEWRITTEN.": $formatted_date<br><br>"
                        ."<input type=\"hidden\" name=\"linkid\" value=\"".pnVarPrepForDisplay($lid)."\">"
                        .""._EDITORIALTITLE.":<br><input type=\"text\" name=\"editorialtitle\" "
                        ."value=\"".pnVarPrepForDisplay($editorialtitle)."\" size=\"50\" maxlength=\"100\"><br>"
                        .""._EDITORIALTEXT.":<br><textarea name=\"editorialtext\" cols=\"60\" "
                        ."rows=\"10\">".pnVarPrepForDisplay($editorialtext)."</textarea><br>"
                        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
                        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
                        ."<input type=\"hidden\" name=\"op\" value=\"LinksModEditorial\"><input type=\"submit\" value=\""
                        ._MODIFY."\"> [ <a href=\"admin.php?module=".$GLOBALS['module']
                        ."&op=LinksDelEditorial&linkid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">"._DELETE."</a> ]"
                        ."</form>";
                }
            }
            CloseTable();
        }

        OpenTable();
        /* Show Comments */
        $column = &$pntable['links_votedata_column'];
        $result5=$dbconn->Execute("SELECT $column[ratingdbid], $column[ratinguser], $column[ratingcomments], $column[ratingtimestamp] FROM $pntable[links_votedata] WHERE $column[ratinglid]=".pnVarPrepForStore($lid)." AND $column[ratingcomments] != '' ORDER BY $column[ratingtimestamp] DESC");
        $totalcomments = $result5->PO_RecordCount();
        echo "<table valign=top width=100%>";
        echo "<tr><td colspan=7><b>Link Comments (total comments: ".pnVarPrepForDisplay($totalcomments).")</b><br><br></td></tr>";
        echo "<tr><td width=20 colspan=1><b>User  </b></td><td colspan=5><b>Comment  </b></td><td><b><center>"._DELETE."</center></b></td><br></tr>";
        if ($totalcomments == 0) echo "<tr><td colspan=\"7\"><center><font color=\"cccccc\">No Comments<br></font></center></td></tr>";
        $x=0;
        $colorswitch="dddddd";
//ADODBtag MoveNext while+list+row
        while(list($ratingdbid, $ratinguser, $ratingcomments, $ratingtimestamp)=$result5->fields) {

            $result5->MoveNext();
            ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
            $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
            $formatted_date = ml_ftime(_DATEBRIEF, $timestamp);
            echo "<tr><td valign=top bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinguser)."</td>"
            ."<td valign=top colspan=5 bgcolor=$colorswitch>".pnVarPrepForDisplay($ratingcomments)."</td>"
            ."<td bgcolor=$colorswitch><center><b><a href=admin.php?module="
            .$GLOBALS['module']."&op=LinksDelComment&lid=".pnVarPrepForDisplay($lid)."&rid=".pnVarPrepForDisplay($ratingdbid)."&authid=".pnSecGenAuthKey().">X</a></b></center></td><br></tr>";
            $x++;
            if ($colorswitch=="dddddd") $colorswitch="ffffff";
            else $colorswitch="dddddd";
        }


        // Show Registered Users Votes
        $column = &$pntable['links_votedata_column'];
        $result5=$dbconn->Execute("SELECT $column[ratingdbid], $column[ratinguser],
                                    $column[rating], $column[ratinghostname], $column[ratingtimestamp]
                                    FROM $pntable[links_votedata] WHERE $column[ratinglid] = '".pnVarPrepForStore($lid)."'
                                    AND $column[ratinguser] != 'outside'
                                    AND $column[ratinguser] != '".pnVarPrepForStore($anonymous)."'
                                    ORDER BY $column[ratingtimestamp] DESC");
        $totalvotes = $result5->PO_RecordCount();
        echo "<tr><td colspan=7><br><br><b>Registered User Votes (total votes: ".pnVarPrepForDisplay($totalvotes).")</b><br><br></td></tr>"
             ."<tr><td><b>User  </b></td><td><b>IP Address  </b></td><td><b>Rating  </b></td><td><b>"
             ."User AVG Rating  </b></td><td><b>Total Ratings  </b></td><td><b>Date  </b></td></font>"
             ."</b><td><b><center>"._DELETE."</center></b></td><br></tr>";
        if ($totalvotes == 0) echo "<tr><td colspan=7><center><font color=cccccc>No Registered User Votes<br></font></center></td></tr>";
        $x=0;
        $colorswitch="dddddd";
//ADODBtag MoveNext while+list+row
        while(list($ratingdbid, $ratinguser, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {

            $result5->MoveNext();
        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
        $timestamp = mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]);
        $formatted_date = ml_ftime(_DATEBRIEF, $timestamp);

            //Individual user information
            $column = &$pntable['links_votedata_column'];
            $result2=$dbconn->Execute("SELECT $column[rating] FROM $pntable[links_votedata] WHERE $column[ratinguser]='".pnVarPrepForStore($ratinguser)."'");
            $usertotalcomments = $result2->PO_RecordCount();
            $useravgrating = 0;
    //ADODBtag MoveNext while+list+row
    while(list($rating2)=$result2->fields) {
                $useravgrating = $useravgrating + $rating2;
                $result2->MoveNext();
            }
            $useravgrating = $useravgrating / $usertotalcomments;
            $useravgrating = number_format($useravgrating, 1);
            echo "<tr><td bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinguser)."</td><td bgcolor=$colorswitch>"
            .pnVarPrepForDisplay($ratinghostname)."</td><td bgcolor=$colorswitch>".pnVarPrepForDisplay($rating)."</td><td bgcolor=$colorswitch>"
            .pnVarPrepForDisplay($useravgrating)."</td><td bgcolor=$colorswitch>$usertotalcomments</td><td bgcolor="
            .$colorswitch.">$formatted_date  </font></b></td><td bgcolor=$colorswitch><center>"
            ."<b><a href=admin.php?module=".$GLOBALS['module']."&op=LinksDelVote&lid=".pnVarPrepForDisplay($lid)."&rid=".pnVarPrepForDisplay($ratingdbid)."&authid=".pnSecGenAuthKey().">X</a></b></center></td></tr><br>";
            $x++;
            if ($colorswitch=="dddddd") $colorswitch="ffffff";
            else $colorswitch="dddddd";
        }

        // Show Unregistered Users Votes
        $column = &$pntable['links_votedata_column'];
        $result5=$dbconn->Execute("SELECT $column[ratingdbid], $column[rating],
                                    $column[ratinghostname], $column[ratingtimestamp] FROM $pntable[links_votedata]
                                    WHERE $column[ratinglid] = ".(int)pnVarPrepForStore($lid)."
                                    AND $column[ratinguser] = '".pnVarPrepForStore($anonymous)."'
                                    ORDER BY $column[ratingtimestamp] DESC");
        $totalvotes = $result5->PO_RecordCount();
        echo "<tr><td colspan=7><b><br><br>Unregistered User Votes (total votes: ".pnVarPrepForDisplay($totalvotes).")</b><br><br></td></tr>"
        ."<tr><td colspan=2><b>IP Address  </b></td><td colspan=3><b>Rating  </b></td><td><b>Date  </b>"
        ."</font></td><td><b><center>"._DELETE."</center></b></td><br></tr>";
        if ($totalvotes == 0) echo "<tr><td colspan=\"7\"><center><font color=\"cccccc\">No Unregistered User Votes<br></font></center></td></tr>";
        $x=0;
        $colorswitch="dddddd";
//ADODBtag MoveNext while+list+row
        while(list($ratingdbid, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {

            $result5->MoveNext();
            ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
            $ratingtime = strftime("%F",mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]));
            $date_array = explode("-", $ratingtime);
            $timestamp = mktime(0, 0, 0, $date_array["1"], $date_array["2"], $date_array["0"]);
            $formatted_date = ml_ftime(_DATEBRIEF, $timestamp);
            echo "<td colspan=2 bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinghostname)
                ."</td><td colspan=3 bgcolor=$colorswitch>".pnVarPrepForDisplay($rating)."</td><td bgcolor=$colorswitch>$formatted_date  </font></b></td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&op=LinksDelVote&lid=".pnVarPrepForDisplay($lid)."&rid=".pnVarPrepForDisplay($ratingdbid)."&authid=".pnSecGenAuthKey().">X</a></b></center></td></tr><br>";
            $x++;
            if ($colorswitch=="dddddd") {
                $colorswitch="ffffff";
            } else {
                $colorswitch="dddddd";
            }
        }
        // Show Outside Users Votes
        $column = &$pntable['links_votedata_column'];
        $result5=$dbconn->Execute("SELECT $column[ratingdbid], $column[rating], $column[ratinghostname], $column[ratingtimestamp]
                                    FROM $pntable[links_votedata]
                                    WHERE $column[ratinglid] = ".(int)pnVarPrepForStore($lid)."
                                    AND $column[ratinguser] = 'outside'
                                    ORDER BY $column[ratingtimestamp] DESC");
        $totalvotes = $result5->PO_RecordCount();
        echo "<tr><td colspan=7><b><br><br>Outside User Votes (total votes: $totalvotes)</b><br><br></td></tr>";
        echo "<tr><td colspan=2><b>IP Address  </b></td><td colspan=\"3\"><b>Rating  </b></td><td><b>Date  </b></td></font></b><td><b><center>"._DELETE."</center></b></td><br></tr>";
        if ($totalvotes == 0) {
            echo "<tr><td colspan=\"7\"><center><font color=\"cccccc\">No Votes from Outside ".pnConfigGetVar("sitename")."<br></font></center></td></tr>";
        }
        $x=0;
        $colorswitch="dddddd";
//ADODBtag MoveNext while+list+row
        while(list($ratingdbid, $rating, $ratinghostname, $ratingtimestamp)=$result5->fields) {

            $result5->MoveNext();
            ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $ratingtimestamp, $ratingtime);
            $ratingtime = strftime("%F",mktime($ratingtime[4],$ratingtime[5],$ratingtime[6],$ratingtime[2],$ratingtime[3],$ratingtime[1]));
            $date_array = explode("-", $ratingtime);
            $timestamp = mktime(0, 0, 0, $date_array["1"], $date_array["2"], $date_array["0"]);
            $formatted_date = ml_ftime(_DATEBRIEF, $timestamp);
            echo "<tr><td colspan=2 bgcolor=$colorswitch>".pnVarPrepForDisplay($ratinghostname)."</td><td colspan=3 bgcolor=$colorswitch>".pnVarPrepForDisplay($rating)."</td><td bgcolor=$colorswitch>$formatted_date  </font></b></td><td bgcolor=$colorswitch><center><b><a href=admin.php?module=".$GLOBALS['module']."&op=LinksDelVote&lid=".pnVarPrepForDisplay($lid)."&rid=".pnVarPrepForDisplay($ratingdbid)."&authid=".pnSecGenAuthKey().">X</a></b></center></td></tr><br>";
            $x++;
            if ($colorswitch=="dddddd") $colorswitch="ffffff";
                else $colorswitch="dddddd";
            }

            echo "<tr><td colspan=6><br></td></tr>";
            echo "</table>";
            echo "</form>";
        } else {
            echo _NOSUCHLINK;
        }
        CloseTable();
        include ("footer.php");
}

function LinksDelComment()
{
    list($lid,
         $rid) = pnVarCleanFromInput('lid',
                                     'rid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_MODERATE)) {
        include 'header.php';
        echo _WEBLINKSMODERATENOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_votedata_column'];
    $dbconn->Execute("UPDATE $pntable[links_votedata] SET $column[ratingcomments]='' WHERE $column[ratingdbid] = '".pnVarPrepForStore($rid)."'");
    $column = &$pntable['links_links_column'];
    $dbconn->Execute("UPDATE $pntable[links_links] SET $column[totalcomments] = ($column[totalcomments] - 1) WHERE $column[lid] = ".(int)pnVarPrepForStore($lid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksModLink&lid='.pnVarPrepForDisplay($lid));
}

function LinksDelVote()
{
    list($lid,
         $rid) = pnVarCleanFromInput('lid',
                                     'rid');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_MODERATE)) {
        include 'header.php';
        echo _WEBLINKSMODERATENOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_votedata_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_votedata] WHERE $column[ratingdbid]=".(int)pnVarPrepForStore($rid));
    $voteresult = $dbconn->Execute("SELECT $column[rating], $column[ratinguser], $column[ratingcomments] FROM $pntable[links_votedata] WHERE $column[ratinglid] = ".(int)pnVarPrepForStore($lid));
    $totalvotesDB = $voteresult->PO_RecordCount();
    $finalrating = calculateVote($voteresult, $totalvotesDB);
    $column = &$pntable['links_links_column'];
    $dbconn->Execute("UPDATE $pntable[links_links] SET $column[linkratingsummary]='".pnVarPrepForStore($finalrating)."', $column[totalvotes]='".pnVarPrepForStore($totalvotesDB)."', $column[totalcomments]='".pnVarPrepForStore($truecomments)."' WHERE $column[lid] = ".(int)pnVarPrepForStore($lid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksModLink&lid='.pnVarPrepForDisplay($lid));
}

function LinksListBrokenLinks()
{

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';

    GraphicAdmin();

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT)) {
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    $column = &$pntable['links_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid], $column[lid], $column[modifysubmitter] FROM $pntable[links_modrequest] WHERE $column[brokenlink]=1 ORDER BY $column[requestid]");
    $totalbrokenlinks = $result->PO_RecordCount();
    echo "<center><font class=\"pn-title\"><b>"._USERREPBROKEN." (".pnVarPrepForDisplay($totalbrokenlinks).")</b></font></center><br><br><center>"
    .""._IGNOREINFO."<br>"
    .""._DELETEINFO."</center><br><br><br>"
    ."<table align=\"center\" width=\"450\">";
    if ($totalbrokenlinks==0) {
        echo "<center><font class=\"pn-title\">"._NOREPORTEDBROKEN."</font></center><br><br><br>";
    } else {
        $colorswitch = $GLOBALS['bgcolor2'];
        echo "<tr>"
            ."<td><b>"._LINK."</b></td>"
            ."<td><b>"._SUBMITTER."</b></td>"
            ."<td><b>"._LINKOWNER."</b></td>"
            ."<td><b>"._IGNORE."</b></td>"
            ."<td><b>"._DELETE."</b></td>"
            ."</tr>";
//ADODBtag MoveNext while+list+row
        while(list($requestid, $lid, $modifysubmitter)=$result->fields) {

            $result->MoveNext();
            $column = &$pntable['links_links_column'];
            $result2 = $dbconn->Execute("SELECT $column[title], $column[url], $column[submitter] FROM $pntable[links_links] WHERE $column[lid]='".pnVarPrepForStore($lid)."'");
            if ($modifysubmitter != '$anonymous') {
                $column = &$pntable['users_column'];
                $result3 = $dbconn->Execute("select $column[email] from $pntable[users] where $column[uname]='".pnVarPrepForStore($modifysubmitter)."'");
//ADODBtag list+row
                list($email)=$result3->fields;
            }
//ADODBtag list+row
            list($title, $url, $owner)=$result2->fields;

            $column = &$pntable['users_column'];
            $result4 = $dbconn->Execute("SELECT $column[email] FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($owner)."'");
//ADODBtag list+row
            list($owneremail)=$result4->fields;
            echo "<tr>"
                ."<td bgcolor=\"$colorswitch\"><a href=\"$url\">".pnVarPrepForDisplay($title)."</a>"
                ."</td>";
            if ($email=='') {
                echo "<td bgcolor=\"$colorswitch\">".pnVarPrepForDisplay($modifysubmitter);
            } else {
                echo "<td bgcolor=\"$colorswitch\"><a href=\"mailto:".pnVarPrepForDisplay($email)."\">".pnVarPrepForDisplay($modifysubmitter)."</a>";
            }
            echo "</td>";
            if ($owneremail=='') {
               echo "<td bgcolor=\"$colorswitch\">".pnVarPrepForDisplay($owner);
            } else {
               echo "<td bgcolor=\"$colorswitch\"><a href=\"mailto:".pnVarPrepForDisplay($owneremail)."\">".pnVarPrepForDisplay($owner)."</a>";
            }
            echo "</td>"
            ."<td bgcolor=\"$colorswitch\"><center><a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksIgnoreBrokenLinks&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">X</a></center>"
            ."</td>"
            ."<td bgcolor=\"$colorswitch\"><center><a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksDelBrokenLinks&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">X</a></center>"
            ."</td>"
            ."</tr>";
            if ($colorswitch == $GLOBALS['bgcolor2']) {
                $colorswitch = $GLOBALS['bgcolor1'];
            } else {
                $colorswitch = $GLOBALS['bgcolor2'];
            }
        }
    }
    echo "</table>";
    CloseTable();

    include 'footer.php';
}

function LinksDelBrokenLinks()
{
    $lid = pnVarCleanFromInput('lid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_DELETE)) {
        include 'header.php';
        echo _WEBLINKSDELNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_modrequest_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_modrequest] WHERE $column[lid]='".(int)pnVarPrepForStore($lid)."'");
    $column = &$pntable['links_links_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_links] WHERE $column[lid]='".(int)pnVarPrepForStore($lid)."'");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksListBrokenLinks');
}

function LinksIgnoreBrokenLinks()
{
    $lid = pnVarCleanFromInput('lid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_MODERATE)) {
        include 'header.php';
        echo _WEBLINKSMODERATENOAUTH;
        include 'footer.php';
        return;
    }
    $column = &$pntable['links_modrequest_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_modrequest] WHERE $column[lid]=".(int)pnVarPrepForStore($lid)." AND $column[brokenlink]=1");
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksListBrokenLinks');
}

//jgm
function LinksListModRequests()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT)) {
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    $column = &$pntable['links_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid], $column[lid], $column[cat_id], $column[title], $column[url], $column[description], $column[modifysubmitter] FROM $pntable[links_modrequest] WHERE $column[brokenlink]=0 ORDER BY $column[requestid]");
    $totalmodrequests = $result->PO_RecordCount();
    echo "<center><font class=\"pn-title\"><b>"._USERMODREQUEST." (".pnVarPrepForDisplay($totalmodrequests).")</b></font></center><br><br><br>";
    echo "<table width=\"95%\"><tr><td>";
//ADODBtag MoveNext while+list+row
    while(list($requestid, $lid, $cid, $title, $url, $description, $modifysubmitter)=$result->fields) {

        $result->MoveNext();
    $column = &$pntable['links_links_column'];
    $result2 = $dbconn->Execute("SELECT $column[cat_id], $column[title], $column[url], $column[description], $column[submitter] FROM $pntable[links_links] WHERE $column[lid]=".(int)pnVarPrepForStore($lid));
//ADODBtag list+row
    list($origcid, $origtitle, $origurl, $origdescription, $owner)=$result2->fields;
    $column = &$pntable['users_column'];
    $result7 = $dbconn->Execute("SELECT $column[email] FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($modifysubmitter)."'");
//ADODBtag list+row
    list($modifysubmitteremail)=$result7->fields;
    $result8 = $dbconn->Execute("SELECT $column[email] FROM $pntable[users] WHERE $column[uname]='".pnVarPrepForStore($owner)."'");
    $cidtitle=CatPath($cid,0,0,0);
    $origcidtitle=CatPath($origcid,0,0,0);
//ADODBtag list+row
    list($owneremail)=$result8->fields;
        if ($owner=="") {
        $owner="administration";
    }
        echo "<table border=\"1\" bordercolor=\"black\" cellpadding=\"5\" cellspacing=\"0\" align=\"center\" width=\"450\">"
            ."<tr>"
            ."<td>"
            ."<table width=\"100%\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
            ."<tr>"
            ."<td valign=\"top\" width=\"45%\"><b>"._ORIGINAL."</b></td>"
            ."<td rowspan=\"5\" valign=\"top\" align=\"left\"><font class=\"pn-sub\"><br>"._WL_DESCRIPTION.":<br>".pnVarPrepForDisplay($origdescription)."</font></td>"
            ."</tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._TITLE.": ".pnVarPrepForDisplay($origtitle)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._URL.": <a href=\"".pnVarPrepForDisplay($origurl)."\">".pnVarPrepForDisplay($origurl)."</a></td></tr>"
        ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._CATEGORY.": ".pnVarPrepForDisplay($origcidtitle)."</td></tr>"
            ."</table>"
            ."</td>"
            ."</tr>"
            ."<tr>"
            ."<td>"
            ."<table width=\"100%\">"
            ."<tr>"
            ."<td valign=\"top\" width=\"45%\"><b>"._PROPOSED."</b></td>"
            ."<td rowspan=\"5\" valign=\"top\" align=\"left\"><font class=\"pn-sub\"><br>"._WL_DESCRIPTION.":<br>".pnVarPrepHTMLDisplay($description)."</font></td>"
            ."</tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._TITLE.": ".pnVarPrepForDisplay($title)."</td></tr>"
            ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._URL.": <a href=\"".pnVarPrepForDisplay($url)."\">".pnVarPrepForDisplay($url)."</a></td></tr>"
        ."<tr><td valign=\"top\" width=\"45%\"><font class=\"pn-sub\">"._CATEGORY.": ".pnVarPrepForDisplay($cidtitle)."</td></tr>"
            ."</table>"
            ."</td>"
            ."</tr>"
            ."</table>"
            ."<table align=\"center\" width=\"450\">"
            ."<tr>";
        if ($modifysubmitteremail=="") {
        echo "<td align=\"left\"><font class=\"pn-sub\">"._SUBMITTER.":  ".pnVarPrepForDisplay($modifysubmitter)."</font></td>";
    } else {
        echo "<td align=\"left\"><font class=\"pn-sub\">"._SUBMITTER.":  <a href=\"mailto:".pnVarPrepForDisplay($modifysubmitteremail)."\">".pnVarPrepForDisplay($modifysubmitter)."</a></font></td>";
    }
        if ($owneremail=="") {
        echo "<td align=\"center\"><font class=\"pn-sub\">"._OWNER.":  ".pnVarPrepForDisplay($owner)."</font></td>";
    } else {
        echo "<td align=\"center\"><font class=\"pn-sub\">"._OWNER.": <a href=\"mailto:".pnVarPrepForDisplay($owneremail)."\">".pnVarPrepForDisplay($owner)."</a></font></td>";
    }
        echo "<td align=\"right\"><font class=\"pn-sub\">( <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksChangeModRequests&amp;requestid=".pnVarPrepForDisplay($requestid)."&authid=".pnSecGenAuthKey()."\">"._ACCEPT."</a> / <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksChangeIgnoreRequests&amp;requestid=".pnVarPrepForDisplay($requestid)."&authid=".pnSecGenAuthKey()."\">"._IGNORE."</a> )</font></td></tr></table>";
    }
    if ($totalmodrequests == 0) {
    echo "<center>"._NOMODREQUESTS."</center><br><br>";
    }
    echo "</td></tr></table>";
    CloseTable();
    include ("footer.php");
}

function LinksChangeModRequests()
{
    $requestid = pnVarCleanFromInput('requestid');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $column = &$pntable['links_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[requestid], $column[lid], $column[cat_id], $column[title], $column[url], $column[description] FROM $pntable[links_modrequest] WHERE $column[requestid]=".(int)pnVarPrepForStore($requestid));
//ADODBtag MoveNext while+list+row
    while(list($requestid, $lid, $cid, $modtitle, $url, $description)=$result->fields) {

        $result->MoveNext();

        $linkcolumn = &$pntable['links_links_column'];
        $linktable = $pntable['links_links'];
        $catcolumn = &$pntable['links_categories_column'];
        $cattable = $pntable['links_categories'];
        $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                         $catcolumn[title]
                                  FROM $linktable, $cattable
                                  WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                                  AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
        list($title, $cattitle) = $result->fields;
        if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_EDIT)) {
            include 'header.php';
            echo _WEBLINKSEDITNOAUTH;
            include 'footer.php';
            return;
        }
        $column = &$pntable['links_links_column'];
        $dbconn->Execute("UPDATE $pntable[links_links] SET $column[cat_id]=".(int)pnVarPrepForStore($cid).", $column[title]='".pnVarPrepForStore($modtitle)."', $column[url]='".pnVarPrepForStore($url)."', $column[description]='".pnVarPrepForStore($description)."' WHERE $column[lid] = ".(int)pnVarPrepForStore($lid));
        $column = &$pntable['links_modrequest_column'];
        $dbconn->Execute("DELETE FROM $pntable[links_modrequest] WHERE $column[requestid]=".(int)pnVarPrepForStore($requestid));
    }
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksListModRequests');
}

function LinksChangeIgnoreRequests()
{
    $requestid = pnVarCleanFromInput('requestid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $column = &$pntable['links_modrequest_column'];
    $result = $dbconn->Execute("SELECT $column[lid] FROM $pntable[links_modrequest] WHERE $column[requestid]=".(int)pnVarPrepForStore($requestid));
//ADODBtag list+row
    list($lid) = $result->fields;
    $result->Close();
    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
    list($title, $cattitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$lid", ACCESS_EDIT)) {
        include 'header.php';
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_modrequest_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_modrequest] WHERE $column[requestid]=".(int)pnVarPrepForStore($requestid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=LinksListModRequests');
}

function LinksCleanVotes()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT)) {
        include 'header.php';
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    $column = &$pntable['links_votedata_column'];
    $totalvoteresult = $dbconn->Execute("SELECT DISTINCT $column[ratinglid] FROM $pntable[links_votedata]");
    while(list($lid)=$totalvoteresult->fields) {

        $totalvoteresult->MoveNext();
        $column = &$pntable['links_votedata_column'];
        $voteresult = $dbconn->Execute("SELECT $column[rating], $column[ratinguser], $column[ratingcomments] FROM $pntable[links_votedata] WHERE $column[ratinglid] = ".(int)pnVarPrepForStore($lid));
        $totalvotesDB = $voteresult->PO_RecordCount();
        $finalrating = calculateVote($voteresult, $totalvotesDB);
        $column = &$pntable['links_links_column'];
        $dbconn->Execute("UPDATE $pntable[links_links] SET $column[linkratingsummary]=$finalrating,$column[totalvotes]=$totalvotesDB,$column[totalcomments]=$truecomments WHERE $column[lid] = ".(int)pnVarPrepForStore($lid));
    }
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function LinksModLinkS()
{
    list($lid,
         $title,
         $url,
         $description,
         $name,
         $email,
         $hits,
         $cat) = pnVarCleanFromInput('lid',
                                     'title',
                                     'url',
                                     'description',
                                     'name',
                                     'email',
                                     'hits',
                                     'cat');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($oldtitle, $cattitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$oldtitle:$lid", ACCESS_EDIT)) {
        include 'header.php';
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_links_column'];
    $dbconn->Execute("UPDATE $pntable[links_links] SET $column[cat_id]=".(int)pnVarPrepForStore($cat).", $column[title]='".pnVarPrepForStore($title)."', $column[url]='".pnVarPrepForStore($url)."', $column[description]='".pnVarPrepForStore($description)."', $column[name]='".pnVarPrepForStore($name)."', $column[email]='".pnVarPrepForStore($email)."', $column[hits]='".pnVarPrepForStore($hits)."' WHERE $column[lid]=".(int)pnVarPrepForStore($lid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function LinksDelLink()
{
    $lid = pnVarCleanFromInput('lid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($lid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($oldtitle, $cattitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$oldtitle:$lid", ACCESS_DELETE)) {
        include 'header.php';
        echo _WEBLINKSDELNOAUTH;
        include 'footer.php';
        return;
    }
    $column = &$pntable['links_links_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_links] WHERE $column[lid]=".(int)pnVarPrepForStore($lid));
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}


function LinksDelNew()
{
    $lid = pnVarCleanFromInput('lid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    if (!pnSecAuthAction(0, 'Web Links::Link', "::$lid", ACCESS_DELETE)) {
        include 'header.php';
        echo _WEBLINKSDELNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_newlink_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_newlink] WHERE $column[lid]=".(int)pnVarPrepForStore($lid));

    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function LinksAddEditorial()
{

    list($linkid,
         $editorialtitle,
         $editorialtext) = pnVarCleanFromInput('linkid',
                                               'editorialtitle',
                                               'editorialtext');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($linkid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$linkid", ACCESS_EDIT)) {
        include 'header.php';
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_editorials_column'];
    $dbconn->Execute("INSERT INTO $pntable[links_editorials]
                       ($column[linkid],
                        $column[adminid],
                        $column[editorialtimestamp],
                        $column[editorialtext],
                        $column[editorialtitle])
                      VALUES
                       (".pnVarPrepForStore($linkid).",
                        '".pnVarPrepForStore(pnUserGetVar('uid'))."',
                        now(),
                        '".pnVarPrepForStore($editorialtext)."',
                        '".pnVarPrepForStore($editorialtitle)."')");
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><br>"
        ."<font class=\"pn-title\">"
        .""._EDITORIALADDED."<br><br>"
        ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=links\">"._WEBLINKSADMIN."</a> ]<br><br>";
    //echo pnVarPrepForDisplay($linkid)."  ".pnVarPrepForDisplay(pnUserGetVar('uid')).", ".pnVarPrepForDisplay($editorialtitle).", ".pnVarPrepForDisplay($editorialtext);
    CloseTable();
    include("footer.php");
}

function LinksModEditorial()
{
    list($linkid,
         $editorialtitle,
         $editorialtext) = pnVarCleanFromInput('linkid',
                                               'editorialtitle',
                                               'editorialtext');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($linkid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$linkid", ACCESS_EDIT)) {
        include 'header.php';
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_editorials_column'];
    $dbconn->Execute("UPDATE $pntable[links_editorials] SET $column[editorialtext]='".pnVarPrepForStore($editorialtext)."', $column[editorialtitle]='".pnVarPrepForStore($editorialtitle)."' WHERE $column[linkid]=".(int)pnVarPrepForStore($linkid));
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br><center>"
    ."<font class=\"pn-title\">"
    .""._EDITORIALMODIFIED."<br><br>"
    ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=links\">"._WEBLINKSADMIN."</a> ]<br><br>";
    CloseTable();
    include("footer.php");
}

function LinksDelEditorial()
{
    $linkid = pnVarCleanFromInput('linkid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $linkcolumn = &$pntable['links_links_column'];
    $linktable = $pntable['links_links'];
    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $linkcolumn[title],
                                     $catcolumn[title]
                              FROM $linktable, $cattable
                              WHERE $linkcolumn[lid] = ".(int)pnVarPrepForStore($linkid)."
                              AND $linkcolumn[cat_id] = ".pnVarPrepForStore($catcolumn['cat_id'])."");
//ADODBtag list+row
    list($title, $cattitle) = $result->fields;
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle:$title:$linkid", ACCESS_DELETE)) {
        include 'header.php';
        echo _WEBLINKSDELNOAUTH;
        include 'footer.php';
        return;
    }

    $column = &$pntable['links_editorials_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_editorials] WHERE $column[linkid]=".(int)pnVarPrepForStore($linkid));
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br><center>"
    ."<font class=\"pn-title\">"
    .""._EDITORIALREMOVED."<br><br>"
    ."[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=links\">"._WEBLINKSADMIN."</a> ]<br><br>";
    CloseTable();
    include("footer.php");
}

function LinksLinkCheck()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    if (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_EDIT)) {
        echo _WEBLINKSEDITNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._LINKVALIDATION."</b></font><br>"
    ."<a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksValidate&amp;cid=0\">"._CHECKALLLINKS."</a><br><br>"
    ."<b>"._CHECKCATEGORIES."</b><br><br><font class=\"pn-sub\">";
    $column = &$pntable['links_categories_column'];
    $result = $dbconn->Execute("SELECT $column[cat_id], $column[title] FROM $pntable[links_categories] ORDER BY $column[title]");
    echo "<form method=\"post\" action=\"admin.php\">"
         ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
         ."<input type=\"hidden\" name=\"op\" value=\"LinksValidate\">"
         ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
         ."<select name=\"cid\">".CatList(0,0)."</select>"
         ."&nbsp;<input type=\"submit\" name=\"Ok\" value=\""._CHECKCATEGORIES."\"></form>"
         ."</font></center>";
    CloseTable();
    include ("footer.php");
}

function LinksValidate()
{
   list($cid,
         $lid,
         $ttitle) = pnVarCleanFromInput('cid',
                                               'lid',
                                               'ttitle');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    $catcolumn = &$pntable['links_categories_column'];
    $cattable = $pntable['links_categories'];
    $result = $dbconn->Execute("SELECT $catcolumn[title]
                              FROM $cattable
                              WHERE $catcolumn[cat_id] = ".(int)pnVarPrepForStore($cid));
//ADODBtag list+row
    list($cattitle) = $result->fields;
    $result->Close();
    if (!pnSecAuthAction(0, 'Web Links::Link', "$cattitle::$lid", ACCESS_EDIT)) {
        echo _WEBLINKSEDITNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }

    OpenTable();
    $transfertitle = str_replace ("_", "", $ttitle);
    /* Check ALL Links */
    echo "<table width=100% border=0>";
    if ($cid==0) {
    echo "<tr><td colspan=\"3\"><center><b>"._CHECKALLLINKS."</b><br>"._BEPATIENT."</center><br><br></td></tr>";
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT $column[lid], $column[title], $column[url], $column[name], $column[email], $column[submitter] FROM $pntable[links_links] ORDER BY $column[title]");
    }
    /* Check Categories */
    if ($cid!=0) {
    $column = &$pntable['links_categories_column'];
    $result = $dbconn->Execute("SELECT $column[title] FROM $pntable[links_categories] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));
//ADODBtag list+row
    list($transfertitle) = $result->fields;
    echo "<tr><td colspan=\"3\"><center><b>"._CHECKCATEGORIES.": ".pnVarPrepForDisplay($transfertitle)."</b><br>"._BEPATIENT."</center><br><br></td></tr>";
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT $column[lid], $column[title], $column[url], $column[name], $column[email], $column[submitter] FROM $pntable[links_links] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid));
    }
    echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._STATUS."</b></td><td bgcolor=\"".$GLOBALS['bgcolor2']."\" width=\"100%\"><b>"._LINKTITLE."</b></td><td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"center\"><b>"._FUNCTIONS."</b></td></tr>";
//ADODBtag MoveNext while+list+row
    while(list($lid, $title, $url, $name, $email, $submitter) = $result->fields) {

        $result->MoveNext();
    if ($url == 'http://' OR $url == '' )
    {
    $fp = false;
    }
    else    {
    $vurl = parse_url($url);
    $fp = fsockopen($vurl['host'], 80, $errno, $errstr, 15);
      }
    if (!$fp){
        echo "<tr><td align=\"center\"><b>&nbsp;&nbsp;"._FAILED."&nbsp;&nbsp;</b></td>"
        ."<td>&nbsp;&nbsp;<a href=\"".pnVarPrepForDisplay($url)."\" target=\"new\">".pnVarPrepForDisplay($title)."</a>&nbsp;&nbsp;</td>"
        ."<td align=\"center\"><font class=\"pn-normal\">&nbsp;&nbsp;[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksModLink&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">"._EDIT."</a> | <a href=\"admin.php?module=".$GLOBALS['module']."&op=LinksDelLink&lid=".pnVarPrepForDisplay($lid)."&authid=".pnSecGenAuthKey()."\">"._DELETE."</a> ]&nbsp;&nbsp;</font>"
        ."</td></tr>";
    }
    if ($fp){
        echo "<tr><td align=\"center\">&nbsp;&nbsp;"._OK."&nbsp;&nbsp;</td>"
        ."<td>&nbsp;&nbsp;<a href=\"$url\" target=\"new\">".pnVarPrepForDisplay($title)."</a>&nbsp;&nbsp;</td>"
        ."<td align=\"center\"><font class=\"pn-normal\">&nbsp;&nbsp;"._NONE."&nbsp;&nbsp;</font>"
        ."</td></tr>";
    }
    }
    echo "</table>";
    CloseTable();
    include ("footer.php");
}

function LinksAddLink()
{
    list($new,
         $lid,
         $title,
         $url,
         $cat,
         $description,
         $name,
         $email,
         $submitter) = pnVarCleanFromInput('new',
                                           'lid',
                                           'title',
                                           'url',
                                           'cat',
                                           'description',
                                           'name',
                                           'email',
                                           'submitter');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }

    $sitename = pnConfigGetVar('sitename');
    $adminmail = pnConfigGetVar('adminmail');

    if (!pnSecAuthAction(0, 'Web Links::Link', ':$title:', ACCESS_ADD)) {
        include 'header.php';
        echo _WEBLINKSADDNOAUTH;
        include 'footer.php';
        return;
    }

    /* Get a quick count - Wandrer */
    $column = &$pntable['links_links_column'];
    $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[url]='".pnVarPrepForStore($url)."'");
//ADODBtag list+row
    list($numrows) = $result->fields;
    if ($numrows>0) {
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<br><center>"
        ."<font class=\"pn-title\">"
        ."<b>"._ERRORURLEXIST."</b><br><br>"
        .""._GOBACK."<br><br>";
    CloseTable();
    include("footer.php");
    } else {
/* Check if Title exist */
    if ($title=="") {
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<br><center>"
        ."<font class=\"pn-title\">"
        ."<b>"._ERRORNOTITLE."</b><br><br>"
        .""._GOBACK."<br><br>";
    CloseTable();
    include("footer.php");
    return;
    }
/* Check if URL exist */
    if ($url=="") {
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<br><center>"
        ."<font class=\"pn-title\">"
        ."<b>"._ERRORNOURL."</b><br><br>"
        .""._GOBACK."<br><br>";
    CloseTable();
    include("footer.php");
    return;
    }
// Check if Description exist
    if ($description=="") {
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._WEBLINKSADMIN."</b></font></center>";
    CloseTable();

    OpenTable();
    echo "<br><center>"
        ."<font class=\"pn-title\">"
        ."<b>"._ERRORNODESCRIPTION."</b><br><br>"
        .""._GOBACK."<br><br>";
    CloseTable();
    include("footer.php");
    return;
    }

    $column = &$pntable['links_links_column'];
    $nextid = $dbconn->GenId($pntable['links_links']);
    $dbconn->Execute("INSERT INTO $pntable[links_links] ($column[lid], $column[cat_id],
                        $column[title], $column[url], $column[description], $column[date], $column[name],
                        $column[email], $column[hits], $column[submitter], $column[linkratingsummary],
                        $column[totalvotes], $column[totalcomments])
                        VALUES ('".pnVarPrepForStore($nextid)."', ".(int)pnVarPrepForStore($cat).", '".pnVarPrepForStore($title)."',
                        '".pnVarPrepForStore($url)."', '".pnVarPrepForStore($description)."', now(), '".pnVarPrepForStore($name)."', '".pnVarPrepForStore($email)."', '0','".pnVarPrepForStore($submitter)."',0,0,0)");
    include("header.php");
    GraphicAdmin();
    OpenTable();
    echo "<br><center>";
    echo "<font class=\"pn-title\">";
    echo ""._NEWLINKADDED."<br><br>";
    echo "[ <a href=\"admin.php?module=".$GLOBALS['module']."&op=links\">"._WEBLINKSADMIN."</a> ]</center><br><br>";
    CloseTable();
    if ($new==1) {
    $column = &$pntable['links_newlink_column'];
    $dbconn->Execute("DELETE FROM $pntable[links_newlink] WHERE $column[lid]=".(int)pnVarPrepForStore($lid));
    if ($email=="") {
    } else {
    	$from = $adminmail;
        $subject = ""._YOURLINKAT." ".pnVarPrepForDisplay($sitename);
        $message = ""._HELLO." ".pnVarPrepForDisplay($name).":\n\n"._WEAPPROVED."\n\n"._LINKTITLE
        .": ".pnVarPrepForDisplay($title)."\n"._URL.": ".pnVarPrepForDisplay($url)."\n"._WL_DESCRIPTION.": ".pnVarPrepHTMLDisplay($description)."\n\n\n"
        ._YOUCANBROWSEUS. " " .pnGetBaseURL() . "modules.php?op=modload&name=Web_Links&file=index\n\n"
        ._THANKS4YOURSUBMISSION."\n\n".pnVarPrepForDisplay($sitename)." "._TEAM."";
//        pnMail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion(), "-f$from");
        pnMail($email, $subject, $message, "From: $from\nX-Mailer: PHP/" . phpversion());
    }
    }
    include("footer.php");
    }
}

function web_links_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_perpage['10'] = '';
    $sel_perpage['15'] = '';
    $sel_perpage['20'] = '';
    $sel_perpage['25'] = '';
    $sel_perpage['30'] = '';
    $sel_perpage['50'] = '';
    $sel_perpage[pnConfigGetVar('perpage')] = ' selected';
    $sel_popular['100'] = '';
    $sel_popular['250'] = '';
    $sel_popular['500'] = '';
    $sel_popular['1000'] = '';
    $sel_popular['1500'] = '';
    $sel_popular['2000'] = '';
	$sel_popular[pnConfigGetVar('popular')] = ' selected';
    $sel_newlinks['10'] = '';
    $sel_newlinks['15'] = '';
    $sel_newlinks['20'] = '';
    $sel_newlinks['25'] = '';
    $sel_newlinks['30'] = '';
    $sel_newlinks['50'] = '';
    $sel_newlinks[pnConfigGetVar('newlinks')] = ' selected';
    $sel_toplinks['10'] = '';
    $sel_toplinks['15'] = '';
    $sel_toplinks['20'] = '';
    $sel_toplinks['25'] = '';
    $sel_toplinks['30'] = '';
    $sel_toplinks['50'] = '';
    $sel_toplinks[pnConfigGetVar('toplinks')] = ' selected';
    $sel_linksresults['10'] = '';
    $sel_linksresults['15'] = '';
    $sel_linksresults['20'] = '';
    $sel_linksresults['25'] = '';
    $sel_linksresults['30'] = '';
    $sel_linksresults['50'] = '';
    $sel_linksresults[pnConfigGetVar('linksresults')] = ' selected';
    $sel_anonaddlinklock['0'] = '';
    $sel_anonaddlinklock['1'] = '';
    $sel_anonaddlinklock[pnConfigGetVar('links_anonaddlinklock')] = ' checked';
    $sel_useoutsidevoting['0'] = '';
    $sel_useoutsidevoting['1'] = '';
    $sel_useoutsidevoting[pnConfigGetVar('useoutsidevoting')] = ' checked';
    $sel_featurebox['0'] = '';
    $sel_featurebox['1'] = '';
    $sel_featurebox[pnConfigGetVar('featurebox')] = ' checked';
    $sel_blockunregmodify['0'] = '';
    $sel_blockunregmodify['1'] = '';
    $sel_blockunregmodify[pnConfigGetVar('blockunregmodify')] = ' checked';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._WEBLINKSCONF.'</b></font></center>'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._LINKSPAGE.':</td><td>'
        .'<select name="xperpage" size="1" class="pn-normal">'
        ."<option value=\"10\"".$sel_perpage['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_perpage['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_perpage['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_perpage['25'].">25</option>\n"
        ."<option value=\"30\"".$sel_perpage['30'].">30</option>\n"
        ."<option value=\"50\"".$sel_perpage['50'].">50</option>\n"
        .'</select>'
        .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._ANONWAITDAYS."</td><td><input type=\"text\" name=\"xanonwaitdays\" value=\"".pnConfigGetVar('anonwaitdays')."\" size=\"4\" class=\"pn-normal\"> "._DAYS
        .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._OUTSIDEWAITDAYS."</td><td><input type=\"text\" name=\"xoutsidewaitdays\" value=\"".pnConfigGetVar('outsidewaitdays')."\" size=\"4\" class=\"pn-normal\"> "._DAYS
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._USEOUTSIDEVOTING.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xuseoutsidevoting\" value=\"1\" class=\"pn-normal\"".$sel_useoutsidevoting['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xuseoutsidevoting\" value=\"0\" class=\"pn-normal\"".$sel_useoutsidevoting['0'].">"._NO
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._ANONWEIGHT."</td><td><input type=\"text\" name=\"xanonweight\" value=\"".pnConfigGetVar('anonweight')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._OUTSIDEWEIGHT."</td><td><input type=\"text\" name=\"xoutsideweight\" value=\"".pnConfigGetVar('outsideweight')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._DETAILVOTEDECIMAL."</td><td><input type=\"text\" name=\"xdetailvotedecimal\" value=\"".pnConfigGetVar('detailvotedecimal')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._MAINVOTEDECIMAL."</td><td><input type=\"text\" name=\"xmainvotedecimal\" value=\"".pnConfigGetVar('mainvotedecimal')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._TOPLINKSPERCENTRIGGER."</td><td><input type=\"text\" name=\"xtoplinkspercentrigger\" value=\"".pnConfigGetVar('toplinkspercentrigger')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._TOPLINKS."</td><td><input type=\"text\" name=\"xtoplinks\" value=\"".pnConfigGetVar('toplinks')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._MOSTPOPLINKSPERCENTRIGGER."</td><td><input type=\"text\" name=\"xmostpoplinkspercentrigger\" value=\"".pnConfigGetVar('mostpoplinkspercentrigger')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._MOSTPOPLINKS."</td><td><input type=\"text\" name=\"xmostpoplinks\" value=\"".pnConfigGetVar('mostpoplinks')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._FEATUREBOX.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xfeaturebox\" value=\"1\" class=\"pn-normal\"".$sel_featurebox['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xfeaturebox\" value=\"0\" class=\"pn-normal\"".$sel_featurebox['0'].">"._NO
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._LINKVOTEMIN."</td><td><input type=\"text\" name=\"xlinkvotemin\" value=\"".pnConfigGetVar('linkvotemin')."\" size=\"4\" class=\"pn-normal\">"
       .'</td></tr>'
        .'<tr><td class="pn-normal">'
        ._BLOCKUNREGMODIFY.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xblockunregmodify\" value=\"1\" class=\"pn-normal\"".$sel_blockunregmodify['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xblockunregmodify\" value=\"0\" class=\"pn-normal\"".$sel_blockunregmodify['0'].">"._NO
       .'</td></tr>'
         .'<tr><td class="pn-normal">'
        ._TOBEPOPULAR.':</td><td>'
        .'<select name="xpopular" size="1" class="pn-normal">'
        ."<option value=\"100\"".$sel_popular['100'].">100</option>\n"
        ."<option value=\"250\"".$sel_popular['250'].">250</option>\n"
        ."<option value=\"500\"".$sel_popular['500'].">500</option>\n"
        ."<option value=\"1000\"".$sel_popular['1000'].">1000</option>\n"
        ."<option value=\"1500\"".$sel_popular['1500'].">1500</option>\n"
        ."<option value=\"2000\"".$sel_popular['2000'].">2000</option>\n"
        .'</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._LINKSASNEW.':</td><td>'
        .'<select name="xnewlinks" size="1" class="pn-normal">'
        ."<option value=\"10\"".$sel_newlinks['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_newlinks['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_newlinks['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_newlinks['25'].">25</option>\n"
        ."<option value=\"30\"".$sel_newlinks['30'].">30</option>\n"
        ."<option value=\"50\"".$sel_newlinks['50'].">50</option>\n"
        .'</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._LINKSASBEST.':</td><td>'
        .'<select name="xtoplinks" size="1" class="pn-normal">'
        ."<option value=\"10\"".$sel_toplinks['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_toplinks['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_toplinks['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_toplinks['25'].">25</option>\n"
        ."<option value=\"30\"".$sel_toplinks['30'].">30</option>\n"
        ."<option value=\"50\"".$sel_toplinks['50'].">50</option>\n"
        .'</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._LINKSINRES.':</td><td>'
        .'<select name="xlinksresults" class="pn-normal">'
        ."<option value=\"10\"".$sel_linksresults['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_linksresults['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_linksresults['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_linksresults['25'].">25</option>\n"
        ."<option value=\"30\"".$sel_linksresults['30'].">30</option>\n"
        ."<option value=\"50\"".$sel_linksresults['50'].">50</option>\n"
        .'</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._ANONPOSTLINKS.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xlinks_anonaddlinklock\" value=\"1\" class=\"pn-normal\"".$sel_anonaddlinklock['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xlinks_anonaddlinklock\" value=\"0\" class=\"pn-normal\"".$sel_anonaddlinklock['0'].">"._NO
        .'</td></tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    CloseTable();
    include ("footer.php");
}

function web_links_admin_setConfig($var) {

    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array (
        );

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
   //   $var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array (
        'xdownloads_anonadddownloadlock'
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
    pnRedirect('admin.php?module='.$GLOBALS['module'].'&op=links');
}

function web_links_admin_main($var)
{
$op = pnVarCleanFromInput('op');
extract($var);

if ((!pnSecAuthAction(0, 'Web Links::Category', '::', ACCESS_EDIT)) && (!pnSecAuthAction(0, 'Web Links::Link', '::', ACCESS_MODERATE))) {
    include 'header.php';
    echo _WEBLINKSNOAUTH;
    include 'footer.php';
} else {
    switch ($op) {

        case "links":
            links();
            break;

        case "LinksDelNew":
            LinksDelNew();
            break;

        case "LinksAddCat":
            LinksAddCat();
            break;

        case "LinksAddLink":
            LinksAddLink();
            break;

        case "LinksAddEditorial":
            LinksAddEditorial();
            break;

        case "LinksModEditorial":
            LinksModEditorial();
            break;

        case "LinksLinkCheck":
            LinksLinkCheck();
            break;

        case "LinksValidate":
            LinksValidate();
            break;

        case "LinksDelEditorial":
            LinksDelEditorial();
            break;

        case "LinksCleanVotes":
            LinksCleanVotes();
            break;

        case "LinksListBrokenLinks":
            LinksListBrokenLinks();
            break;

        case "LinksDelBrokenLinks":
            LinksDelBrokenLinks();
            break;

        case "LinksIgnoreBrokenLinks":
            LinksIgnoreBrokenLinks();
            break;

        case "LinksListModRequests":
            LinksListModRequests();
            break;

        case "LinksChangeModRequests":
            LinksChangeModRequests();
            break;

        case "LinksChangeIgnoreRequests":
            LinksChangeIgnoreRequests();
            break;

        case "LinksDelCat":
            LinksDelCat();
            break;

        case "LinksModCat":
            LinksModCat();
            break;

        case "LinksModCatS":
            LinksModCatS();
            break;

        case "LinksModLink":
            LinksModLink();
            break;

        case "LinksModLinkS":
            LinksModLinkS();
            break;

        case "LinksDelLink":
            LinksDelLink();
            break;

        case "LinksDelVote":
            LinksDelVote();
            break;

        case "LinksDelComment":
            LinksDelComment();
            break;

       case "getConfig":
            web_links_admin_getConfig();
            break;

       case "setConfig":
            web_links_admin_setConfig($var);
            break;

        default:
            links();
            break;
    }
}

}
?>