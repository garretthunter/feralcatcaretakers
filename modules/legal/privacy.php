<?php // $Id: privacy.php,v 1.2 2002/11/15 23:30:29 larsneo Exp $ $Name:  $
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
// Filename: modules/legal/privacy.php
// Original Author of file: Michael M. Wechsler, Esq. (michael@thelaw.com)
// Purpose of file: Display a Privacy Policy for Your Site.
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

echo  "<p><font class=\"pn-title\">"._PPTOPTEXT."</font><br></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLEINTRO."</font><br><font class=\"pn-normal\">"._PPTEXTINTRO."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE1."</font><br><font class=\"pn-normal\">"._PPTEXT1."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE2."</font><br><font class=\"pn-normal\">"._PPTEXT2."</font></p>\n";

/* **NOTE: You may wish to include the following: We may also provide Data to sponsors and advertisers, to fulfill requests for products or services, for notification about special offers, or for other lawful purposes. */

echo"<p><font class=\"pn-title\">"._PPTITLE3."</font><br><font class=\"pn-normal\">"._PPTEXT3."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE4."</font><br>\n";

/* **NOTE: The following is highly recommended. If you intend to provide goods/services to children, you are advised to seek consultation with an attorney. */

echo "<font class=\"pn-normal\">"._PPTEXT4."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE5."</font><br><font class=\"pn-normal\">"._PPTEXT5."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE6."</font><br><font class=\"pn-normal\">"._PPTEXT6."</font></p>\n";

/* **NOTE: Delete the following if necessary but be aware of consequences */

echo "<p><font class=\"pn-title\">"._PPTITLE7."</font><br><font class=\"pn-normal\">"._PPTEXT7."</font></p>\n"
    ."<p><font class=\"pn-title\">"._PPTITLE8."</font><br><font class=\"pn-normal\">"._PPTEXT8."</font></p>\n";

/* **NOTE: Feel free to provide other contact information. */

//End
CloseTable();
include 'footer.php';

?>