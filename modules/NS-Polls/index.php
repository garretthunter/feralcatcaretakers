<?php // $Id: index.php,v 1.2 2002/11/07 23:26:43 neo Exp $ $Name:  $
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
// Filename: modules/NS-Polls/index.php
// Original Author: Till Gerken (tig@skv.org)
// Purpose: Voting system
// ----------------------------------------------------------------------
// added code to protect from bad parms and cleaned up logic - Skooter

if (!defined("LOADED_AS_MODULE")) {
         die ("You can't access this file directly...");
     }

$ModName = $GLOBALS['name'];
modules_get_language();

include_once 'includes/blocks/poll.php';

// Don't delete this strings in 0.71 _LVOTES _NUMVOTES _ONEPERDAY _OTHERPOLLS
// _PASTSURVEYS _PCOMMENTS _POLLS _RESULTS _TOTALVOTES _VOTE _VOTING he-he

//get parameters this code is looking for.
list($req, $pollID, $voteID, $forwarder, $mode) = pnVarCleanFromInput('req','pollID','voteID','forwarder','mode');

if (empty($pollID) || !is_numeric($pollID))$pollID = 0;
if (empty($voteID) || !is_numeric($voteID))$voteID = 0;

// If no pollID is passed in display a list of available polls
if($pollID == 0) {
    include ('header.php');
    pollList();
    include ('footer.php');
    return true;
}

// Count votes from a forwarder
if(!empty($forwarder) && $voteID > 0 && $pollID > 0) {
    pollCollector($pollID, $voteID, $forwarder);
    return true;
}

//Count regular votes
if($voteID > 0 && $pollID > 0) {
    pollCollector($pollID, $voteID);
    return true;
}

// Display results for a pollID
if($req == "results" && $pollID > 0) {
    include ("header.php");
    OpenTable();
    print '<center><font class="pn-title">'._CURRENTPOLLRESULTS.'</font></center>';
    CloseTable();

    OpenTable2();
    pollResults($pollID);
    CloseTable2();
    if (pnConfigGetVar('pollcomm') && ($mode != "nocomments")) {
        print '<br><br>';
        include("modules/$ModName/comments.php");
    }
    include ("footer.php");
	return true;    
}

// Display a selected poll
if($pollID > 0 && $pollID != pollLatest()) {
    include ('header.php');
    OpenTable();
    print '<center><font class="pn-title">'._POLL.'</font></center>';
    CloseTable();

    print '<table border="0" align="center"><tr><td>';
    $row = array();
    $row[title] = '';
    pollMain($pollID, $row);
    print '</td></tr></table>';
    include ('footer.php');
    return true;
}

//Default is to display current poll.
include ('header.php');
OpenTable();
print '<center><font class="pn-title">'._CURRENTPOLL.'</font></center>';
CloseTable();

print '<table border="0" align="center"><tr><td>';
pollNewest();
print '</td></tr></table>';
include ('footer.php');
return true;
?>