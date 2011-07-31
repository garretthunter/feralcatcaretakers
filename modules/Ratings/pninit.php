<?php
// $Id: pninit.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Initialisation functions for ratings
// ----------------------------------------------------------------------

/**
 * initialise the ratings module
 */
function ratings_init()
{
    // Get database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Create tables
    $ratingstable = $pntable['ratings'];
    $ratingscolumn = &$pntable['ratings_column'];
    $sql = "CREATE TABLE $ratingstable (
            $ratingscolumn[rid] int(10) NOT NULL auto_increment,
            $ratingscolumn[module] varchar(32) NOT NULL default '',
            $ratingscolumn[itemid] varchar(64) NOT NULL default '',
            $ratingscolumn[ratingtype] varchar(64) NOT NULL default '',
            $ratingscolumn[rating] double(3,5) NOT NULL default 0,
            $ratingscolumn[numratings] int(5) NOT NULL default 1,
            PRIMARY KEY(pn_rid))";
    // TODO: need to index this on module,itemid or similar
    $dbconn->Execute($sql);

    // Check database result
    if ($dbconn->ErrorNo() != 0) {
        // Report failed initialisation attempt
        return false;
    }

    $ratingslogtable = $pntable['ratingslog'];
    $ratingslogcolumn = &$pntable['ratingslog_column'];
    $sql = "CREATE TABLE $ratingslogtable (
            $ratingslogcolumn[id] varchar(32) NOT NULL default '',
            $ratingslogcolumn[ratingid] varchar(64) NOT NULL default '',
            $ratingslogcolumn[rating] int(3) NOT NULL default 0)";
    // TODO: need to index this on something
    $dbconn->Execute($sql);

    // Check database result
    if ($dbconn->ErrorNo() != 0) {
        // Report failed initialisation attempt
        return false;
    }


    // Set up module variables
    pnModSetVar('Ratings', 'defaultstyle', 'outoffivestars');
    pnModSetVar('Ratings', 'seclevel', 'medium');

    // Set up module hooks
    if (!pnModRegisterHook('item',
                           'display',
                           'GUI',
                           'Ratings',
                           'user',
                           'display')) {
        return false;
    }

    // Initialisation successful
    return true;
}

/**
 * upgrade the ratings module from an old version
 */
function ratings_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case 1.0:
            // Code to upgrade from version 1.0 goes here

            // Get database information
            list($dbconn) = pnDBGetConn();
            $pntable = pnDBGetTables();

            // rating went from int to double
            $ratingstable = $pntable['ratings'];
            $ratingscolumn = &$pntable['ratings_column'];
            $sql = "ALTER TABLE $ratingstable
                    CHANGE $ratingscolumn[rating] $ratingscolumn[rating] double(3,5) NOT NULL default 0";
            $dbconn->Execute($sql);

            // Check database result
            if ($dbconn->ErrorNo() != 0) {
                // Report failed upgrade
                return false;
            }

            // Carry out other upgrades
            return ratings_upgrade(1.1);
            break;
        case 1.1:
            // Code to upgrade from version 1.1 goes here
            break;
    }

    return true;
}

/**
 * delete the ratings module
 */
function ratings_delete()
{
    // Remove module hooks
    if (!pnModUnregisterHook('item',
                             'display',
                             'GUI',
                             'Ratings',
                             'user',
                             'display')) {
        pnSessionSetVar('errormsg', _RATINGSCOULDNOTUNREGISTER);
//        return false;
    }

    // Delete module variables
    pnModDelVar('Ratings', 'defaultstyle');
    pnModDelVar('Ratings', 'seclevel');

    // Get database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Delete tables
    $sql = "DROP TABLE $pntable[ratings]";
    $dbconn->Execute($sql);

    // Check database result
    if ($dbconn->ErrorNo() != 0) {
        // Report failed deletion attempt
        return false;
    }

    // Deletion successful
    return true;
}

?>