<?PHP
// File: $Id: admin.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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

if (pnSecAuthAction(0, 'Submit News::', '::', ACCESS_ADMIN)) {

modules_get_language();
modules_get_manual();

function submit_news_admin_getConfig() {

    include ("header.php");

    // prepare vars
    $sel_notify['0'] = '';
    $sel_notify['1'] = '';
    $sel_notify[pnConfigGetVar('notify')] = ' checked';

    GraphicAdmin();
    OpenTable();
    echo "<center><font class=\"pn-title\"><b>"._SUBMITCONF."</b></font></center>";
    CloseTable();

    OpenTable();
    print '<table border="0"><tr><td class="pn-normal">'
        ._NOTIFYSUBMISSION.'</td><td class="pn-normal">'
	.'<form action="admin.php" method="post">'
        ."<input type=\"radio\" name=\"xnotify\" value=\"1\" class=\"pn-normal\"".$sel_notify['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xnotify\" value=\"0\" class=\"pn-normal\"".$sel_notify['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._EMAIL2SENDMSG.":</td><td><input type=\"text\" name=\"xnotify_email\" value=\"".pnConfigGetVar('notify_email')."\" size=\"30\" maxlength=\"100\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._EMAILSUBJECT.":</td><td><input type=\"text\" name=\"xnotify_subject\" value=\"".pnConfigGetVar('notify_subject')."\" size=\"50\" maxlength=\"100\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._EMAILMSG.':</td><td><textarea name="xnotify_message" cols="40" rows="8" wrap="soft" class="pn-normal">'.htmlspecialchars(pnConfigGetVar('notify_message')).'</textarea>'
        .'</td></tr><tr><td class="pn-normal">'
        ._EMAILFROM.":</td><td><input type=\"text\" name=\"xnotify_from\" value=\"".pnConfigGetVar('notify_from')."\" size=\"15\" maxlength=\"255\" class=\"pn-normal\">"
        .'</td></tr><tr></table>'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
	."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>";
    ;
    CloseTable();

    include ("footer.php");

}

function submit_news_admin_setConfig($var)
{
    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
        exit;
    }
    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array('xnotify_message',
		     'xnotify_from',
		     'xnotify_subject');

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
	//$var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array ('xtop',);

    foreach($fixvars as $v) {
        if(empty($var[$v])) {
            $var[$v] = 0;
        }
    }

    // all variables starting with x are the config vars.
    while(list($key, $val) = each($var)) {
        if(substr($key, 0, 1) == 'x') {
            pnConfigSetVar(substr($key, 1), $val);
        }
    }
    pnRedirect('admin.php');
}

function submit_news_admin_main($var)
{
   $op = pnVarCleanFromInput('op');
   extract($var);

   switch ($op) {

    case "getConfig":
        submit_news_admin_getConfig();
        break;

    case "setConfig":
        submit_news_admin_setConfig($var);
        break;

    default:
        submit_news_admin_getConfig();
        break;
   }
}

} else {
    echo "Access Denied";
}
?>