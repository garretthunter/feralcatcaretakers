<?php
// $Id: pninit.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
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
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Initialisation functions for autolinks
// ----------------------------------------------------------------------

/**
 * initialise the autolinks module
 */
function autolinks_init()
{
    // Set up database tables
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $autolinkstable = $pntable['autolinks'];
    $autolinkscolumn = $pntable['autolinks_column'];

    // See if there is an old autolinks table,
    // probably from an old PN install where autolinks
    // were in the core
    $sql = "SELECT COUNT(1) FROM $autolinkstable";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        // There was not, create table

        $sql = "CREATE TABLE $autolinkstable (
                $autolinkscolumn[lid] INT(11) NOT NULL auto_increment,
                $autolinkscolumn[keyword] VARCHAR(100) NOT NULL default '',
                $autolinkscolumn[title] VARCHAR(100) NOT NULL default '',
                $autolinkscolumn[url] VARCHAR(200) NOT NULL default '',
                $autolinkscolumn[comment] VARCHAR(200) NOT NULL default '',
                PRIMARY KEY (pn_lid),
                UNIQUE KEY keyword (pn_keyword))";
        $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            pnSessionSetVar('errormsg', _DBCREATETABLEERROR);
            return false;
        }
    }

    // Set up module variables
    pnModSetVar('Autolinks', 'itemsperpage', 20);
    pnModSetVar('Autolinks', 'linkfirst', 1);
    pnModSetVar('Autolinks', 'invisilinks', 0);

    // Set up module hooks
    if (!pnModRegisterHook('item',
                           'transform',
                           'API',
                           'Autolinks',
                           'user',
                           'transform')) {
        pnSessionSetVar('errormsg', _AUTOLINKSCOULDNOTREGISTER);
        return false;
    }

    // Initialisation successful
    return true;
}

/**
 * upgrade the smiley module from an old version
 */
function autolinks_upgrade($oldversion)
{
    return true;
}

/**
 * delete the smiley module
 */
function autolinks_delete()
{
    // Remove module hooks
    if (!pnModUnregisterHook('item',
                             'transform',
                             'API',
                             'Autolinks',
                             'user',
                             'transform')) {
        pnSessionSetVar('errormsg', _AUTOLINKSOULDNOTUNREGISTER);
        return false;
    }

    // Remove module variables
    pnModDelVar('Autolinks', 'invisilinks');
    pnModDelVar('Autolinks', 'linkfirst');
    pnModDelVar('Autolinks', 'itemsperpage');

    // Deletion successful
    return true;
}

?>