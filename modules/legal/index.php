<?php // $Id: index.php,v 1.2 2002/11/15 23:30:14 larsneo Exp $ $Name:  $
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
// Filename: modules/legal/tou.php
// Original Author of file: Michael M. Wechsler, Esq. (michael@thelaw.com)
// Purpose of file: Display a Terms of Use for Your Site.
// ----------------------------------------------------------------------


if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = $GLOBALS['name'];
modules_get_language();

include 'header.php';

if (!pnSecAuthAction(0, 'legal::', '::', ACCESS_OVERVIEW)) {
echo _BADAUTHKEY;
include("footer.php");
return;
}

OpenTable();

echo "<p><font class=\"pn-title\">"._TOUTOPTEXT."</font><br></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE1."</font><br><font class=\"pn-normal\">"._TOUTEXT1."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE2."</font><br><font class=\"pn-normal\">"._TOUTEXT2."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE3."</font><br><font class=\"pn-normal\">"._TOUTEXT3."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE4."</font><br><font class=\"pn-normal\">"._TOUTEXT4."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE5."</font><br><font class=\"pn-normal\">"._TOUTEXT5."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE6."</font><br><font class=\"pn-normal\">"._TOUTEXT6."</font></p>\n"
    ."<p><font class=\"pn-normal\">"._TOUTEXT6MORE."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE7."</font><br><font class=\"pn-normal\">"._TOUTEXT7."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE8."</font><br><font class=\"pn-normal\">"._TOUTEXT8."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE9."</font><br><font class=\"pn-normal\">"._TOUTEXT9."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE10."</font><br><font class=\"pn-normal\">"._TOUTEXT10."</font></p>\n"
    ."<p><font class=\"pn-normal\">"._TOUTEXT10MORE."</font></p>\n"
    ."<p><font class=\"pn-normal\">"._TOUTEXT10MORE1."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE11."</font><br><font class=\"pn-normal\">"._TOUTEXT11."</font></p>\n"
    ."<p><font class=\"pn-normal\">"._TOUTEXT11MORE."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE12."</font><br><font class=\"pn-normal\">"._TOUTEXT12."</font></p>\n"
    ."<p><font class=\"pn-title\">"._TOUTITLE13."</font><br><font class=\"pn-normal\">"._TOUTEXT13."</font></p>\n"

    ."<!-- **NOTE: You are supposed to provide a name and mailing address -->\n"
    ."<!-- to accept service of papers. -->\n"

    ."<p><font class=\"pn-title\">"._TOUTITLE14."</font><br><font class=\"pn-normal\">"._TOUTEXT14."</font></p>\n"

    /*
    ."<!-- This section should properly appear in the following format: -->\n"
    ."<!--  You agree that this <a href=$nukeurl/termsofuse.php>Terms of Use</a>  -->\n"
    ."<!--  and any dispute arising out of your use of this web site or our products or  -->\n"
    ."<!--  services shall be governed by and construed in according with the laws of [<i>enter  -->\n"
    ."<!--  your country, state/province and county/city if applicable</i>] without regard  -->\n"
    ."<!--  to its conflict of law provisions. By registering or using this web site and  -->\n"
    ."<!--  service you consent and submit to the exclusive jurisdiction and venue of the  -->\n"
    ."<!--  appropriate courts located in [<i>enter county/city</i>], [<i>enter state/province</i>]  -->\n"
    ."<!--  of [<i>enter country</i>]. -->\n"
    */

    ."<p><font class=\"pn-title\">"._TOUTITLE15."</font><br><font class=\"pn-normal\">"._TOUTEXT15."</font></p>\n";

CloseTable();
include 'footer.php';
?>