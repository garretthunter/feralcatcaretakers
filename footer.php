<?php
// File: $Id: footer.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Purpose of file:
// ----------------------------------------------------------------------

global $PHP_SELF;

if (eregi("footer.php", $PHP_SELF)) {
	die ("You can't access this file directly...");
}

// XHTML Support by Matt Jarjoura. <mjarjo1@umbc.edu>

function footmsg()
{
   echo "<font class=\"pn-sub\">\n
	".pnConfigGetVar('foot1')."<br>\n
	</font>\n";
}

function foot()
{
    global $index;

// modification .71 multisites mouzaia
/* it should not be necessary here, since config.php is in a table.
    if (!isset($index)) {
	include(WHERE_IS_PERSO."config.php");
    }
*/
    themefooter();
    /**
     * DebugXHTML will place a link at the bottom of all pages which directs
     * the page to w3.org's validator server.  This will allow all
     * module developers and theme writers to check their code for XHTML
     * compliance.  Transitional XHTML is hard-coded till the next major
     * release.
     */
    $debugxhtml = -1;
    if (pnConfigGetVar('supportxhtml')) {
	if ($debugxhtml)
	    xhtml_display_test();
    }
    echo "</body>\n</html>";
}

foot();

global $pnconfig, $pndebug, $dbg, $debug_sqlcalls, $dbg_starttime;

// show time to render
$mtime = explode(" ",microtime());
$dbg_endtime = $mtime[1] + $mtime[0];
$dbg_totaltime = ($dbg_endtime - $dbg_starttime);

// printf("<center><font size=-1>Page created in %f seconds.</font></center>", $dbg_totaltime);
if ($pndebug['debug']){
    $dbg->v($dbg_totaltime,"Page created in (seconds)");
    $dbg->v($debug_sqlcalls,"Number of SQL Calls");
}
?>