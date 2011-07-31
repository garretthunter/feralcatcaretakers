<?php
// $Id: pninit.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// PostNukeContent Management System
// Copyright (C) 2001 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: 
// Purpose of file:  init for quotes module
// ----------------------------------------------------------------------

/**
 * init quote module
 */
function quotes_init()
{
    // Get database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Create tables
    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];
    $sql = "CREATE TABLE $quotestable (
            $quotescolumn[qid] int(10) unsigned NOT NULL auto_increment,
            $quotescolumn[quote] text,
            $quotescolumn[author] varchar(150) NOT NULL default '',
            PRIMARY KEY(pn_qid))";
    $dbconn->Execute($sql);

    // Check database result
    if ($dbconn->ErrorNo() != 0) {
        // Report failed initialisation attempt
        return false;
    }

    // Set up module variables
    pnModSetVar('Quotes', 'detail', 0);
    pnModSetVar('Quotes', 'table', 1);

    // Initialisation successful
    return true;
}

/**
 * upgrade
 */
function quotes_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case 1.0:
            // upgrade versions
            break;
        case 2.0:
            // upgrade versions
            break;
        case 2.5:
            // upgrade versions
            break;
    }
}

/**
 * delete the quotes module
 */
function quotes_delete()
{
    // Get database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Delete tables
    $sql = "DROP TABLE $pntable[quotes]";
    $dbconn->Execute($sql);

    // Check database result
    if ($dbconn->ErrorNo() != 0) {
        // Report failed deletion attempt
        return false;
    }

    // Delete module variables
    pnModDelVar('Quotes', 'detail');
    pnModDelVar('Quotes', 'table');

    // Deletion successful
    return true;
}

?>