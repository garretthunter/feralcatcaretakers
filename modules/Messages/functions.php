<?php
// File: $Id: functions.php,v 1.3 2002/10/24 20:10:43 skooter Exp $ $Name:  $
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
// Original Author of file: Francisco Burzi
// Purpose of file: Part of phpBB integration
//   Copyright (c) 2001 by
//   Richard Tirtadji AKA King Richard (rtirtadji@hotmail.com)
//   Hutdik Hermawan AKA hotFix (hutdik76@hotmail.com)
//   http://www.phpnuke.web.id/
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
}

function putitems() {
    echo "<font class=\"pn-normal\">Click on the <a href=\"modules/Messages/bb_smilies.php\">Smilies</a> to insert it on your Message:</font><br>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-) ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_smile.gif\" border=\"0\" alt=\":-)\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-( ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_frown.gif\" border=\"0\" alt=\":-(\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-D ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_biggrin.gif\" border=\"0\" alt=\":-D\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' ;-) ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_wink.gif\" border=\"0\" alt=\";-)\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-o ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_eek.gif\" border=\"0\" alt=\":-0\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' 8-) ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_cool.gif\" border=\"0\" alt=\"8-)\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-? ');\"><IMG width=\"15\" height=\"22\" src=\"images/smilies/icon_confused.gif\" border=\"0\" alt=\":-?\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-P ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_razz.gif\" border=\"0\" alt=\":-P\" hspace=\"5\"></A>";
    echo "<A href=\"javascript: x()\" onClick=\"DoSmilie(' :-| ');\"><IMG width=\"15\" height=\"15\" src=\"images/smilies/icon_mad.gif\" border=\"0\" alt=\":-|\" hspace=\"5\"></A>";
    echo "<br><br>";
    echo "<font class=\"pn-normal\">Click on the buttoms to add <a href=\"modules/Messages/bbcode_ref.php\">BBCode</a> to your message:</font><br><br>";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('url');\"><IMG src=\"images/global/b_url.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Web Address\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('email');\"><IMG src=\"images/global/b_email.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Email Address\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('image');\"><IMG src=\"images/global/b_image.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Load Image from Web\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('bold');\"><IMG src=\"images/global/b_bold.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Bold Text\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('italic');\"><IMG src=\"images/global/b_italic.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Italic Text\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('quote');\"><IMG src=\"images/global/b_quote.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Quote\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('code');\"><IMG src=\"images/global/b_code.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Code\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('listopen');\"><IMG src=\"images/global/b_listopen.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Open List\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('listitem');\"><IMG src=\"images/global/b_listitem.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: List Item\"></A>\n";
    echo "<A href=\"javascript: x()\" onClick=\"DoPrompt('listclose');\"><IMG src=\"images/global/b_listclose.gif\" width=\"83\" height=\"21\" border=\"0\" alt=\"BBCode: Close List\"></A>\n";
}

/**
 * Nathan Codding - July 19, 2000
 * Returns a count of the given userid's private messages.
 */
function get_pmsg_count($user_id)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['priv_msgs_column'];
    $sql = "SELECT count($column[msg_id])
            FROM $pntable[priv_msgs]
            WHERE $column[to_userid] = '" . pnVarPrepForStore($user_id) . "'";
    $resultID = $dbconn->Execute($sql);
    list($count) = $resultID->fields;
    return $count;
}

/**
 * Nathan Codding - July 19, 2000
 * Checks if a given username exists in the DB. Returns true if so, false if not.
 */
function check_username($username)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['users_column'];
    $sql = "SELECT count(1)
            FROM $pntable[users]
            WHERE $column[uname] = '" . pnVarPrepForStore($username). "'
              AND $column[uid] != 1";
    $resultID = $dbconn->Execute($sql);
    $valid = $resultID->fields;
    return $valid;
}

/**
 * Nathan Codding, July 19/2000
 * Get a user's data, given their user ID.
 */

function get_userdata_from_id($userid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['users_column'];
    $sql = "SELECT * FROM $pntable[users] WHERE $column[uid]= '" . pnVarPrepForStore($userid)."'";
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo()<>0) {
        $userdata = array("error" => "1");
        return ($userdata);
    }
    if(!$myrow = $result->GetRowAssoc(false)) {
        $userdata = array("error" => "1");
        return ($userdata);
    }
     return($myrow);
}

function get_userdata($username)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['users_column'];
    $sql = "SELECT * FROM $pntable[users] WHERE $column[uname]='" . pnVarPrepForStore($username) . "'";
    $result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo()<>0) {
        $userdata = array("error" => "1");
    }
    else if(!$myrow = $result->GetRowAssoc(false)) {
        $userdata = array("error" => "1");
    }
     return($myrow);
}

function smile($message) {
    $message = str_replace(":)", "<IMG SRC=\"images/smilies/icon_smile.gif\">", $message);
    $message = str_replace(":-)", "<IMG SRC=\"images/smilies/icon_smile.gif\">", $message);
    $message = str_replace(":(", "<IMG SRC=\"images/smilies/icon_frown.gif\">", $message);
    $message = str_replace(":-(", "<IMG SRC=\"images/smilies/icon_frown.gif\">", $message);
    $message = str_replace(":-D", "<IMG SRC=\"images/smilies/icon_biggrin.gif\">", $message);
    $message = str_replace(":D", "<IMG SRC=\"images/smilies/icon_biggrin.gif\">", $message);
    $message = str_replace(";)", "<IMG SRC=\"images/smilies/icon_wink.gif\">", $message);
    $message = str_replace(";-)", "<IMG SRC=\"images/smilies/icon_wink.gif\">", $message);
    $message = str_replace(":o", "<IMG SRC=\"images/smilies/icon_eek.gif\">", $message);
    $message = str_replace(":O", "<IMG SRC=\"images/smilies/icon_eek.gif\">", $message);
    $message = str_replace(":-o", "<IMG SRC=\"images/smilies/icon_eek.gif\">", $message);
    $message = str_replace(":-O", "<IMG SRC=\"images/smilies/icon_eek.gif\">", $message);
    $message = str_replace("8)", "<IMG SRC=\"images/smilies/icon_cool.gif\">", $message);
    $message = str_replace("8-)", "<IMG SRC=\"images/smilies/icon_cool.gif\">", $message);
    $message = str_replace(":?", "<IMG SRC=\"images/smilies/icon_confused.gif\">", $message);
    $message = str_replace(":-?", "<IMG SRC=\"images/smilies/icon_confused.gif\">", $message);
    $message = str_replace(":p", "<IMG SRC=\"images/smilies/icon_razz.gif\">", $message);
    $message = str_replace(":P", "<IMG SRC=\"images/smilies/icon_razz.gif\">", $message);
    $message = str_replace(":-p", "<IMG SRC=\"images/smilies/icon_razz.gif\">", $message);
    $message = str_replace(":-P", "<IMG SRC=\"images/smilies/icon_razz.gif\">", $message);
    $message = str_replace(":-|", "<IMG SRC=\"images/smilies/icon_mad.gif\">", $message);
    $message = str_replace(":|", "<IMG SRC=\"images/smilies/icon_mad.gif\">", $message);
    return($message);
}

function desmile($message) {
    $message = str_replace("<IMG SRC=\"images/smilies/icon_smile.gif\">", ":-)",  $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_frown.gif\">", ":-(", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_biggrin.gif\">",":-D",  $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_wink.gif\">", ";-)", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_eek.gif\">", ":-o", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_eek.gif\">", ":-O", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_cool.gif\">", "8-)", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_confused.gif\">", ":-?", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_razz.gif\">", ":-p", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_razz.gif\">", ":-P", $message);
    $message = str_replace("<IMG SRC=\"images/smilies/icon_mad.gif\">", ":-|", $message);

    return($message);
}

/***
 * modified by Sebastien, oct 2001.
 * I extract pn_bbencode() and pn_bbdecode() from there, in order to use them for Wiki and bbCode
 * translation. I was not able to include directly functions.php because it was trying to 
 * open tables, databases, strange for a procedure file non ? 
 */
include_once 'modules/Messages/bbcode.lib.php';

function forumerror($e_code) {

    if ($e_code == "0005") {
    $error_msg = ""._MSGERROR0005."";
    }
    if ($e_code == "0017") {
    $error_msg = ""._MSGERROR0017."";
    }
    if ($e_code == "0018") {
    $error_msg = ""._MSGERROR0018."";
    }
    if ($e_code == "0019") {
    $error_msg = ""._MSGERROR0019."";
    }
    if ($e_code == "0020") {
    $error_msg = ""._MSGERROR0020."";
    }
    if ($e_code == "0021") {
    $error_msg = ""._MSGERROR0021."";
    }
    if ($e_code == "0022") {
    $error_msg = ""._MSGERROR0022."";
    }
    if ($e_code == "0023") {
    $error_msg = ""._MSGERROR0023."";
    }
    if ($e_code == "0024") {
		$error_msg = ""._MSGERROR0024."";
    }
    OpenTable2();
    echo "<center><font class=\"pn-title\"><b>"._MSGERROR."</b></font><br/><br/><br/>";
    echo "<font class=\"pn-normal\">".pnVarPrepForDisplay($error_msg)."</font><br/><br/><br/>";
    echo "[ <a class=\"pn-normal\" href=\"javascript:history.go(-1)\">"._MSGGOBACK."</a> ]<br/><br/>";
    CloseTable2();
    include 'footer.php';
    die("");
}
?>