<?php
// $Id: pninit.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
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
// Purpose of file:  Initialisation functions for template
// ----------------------------------------------------------------------

/**
 * initialise the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function template_init()
{
    // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
    // return arrays but we handle them differently.  For pnDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For pnDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // It's good practice to name the table and column definitions you
    // are getting - $table and $column don't cut it in more complex
    // modules
    $templatetable = $pntable['template'];
    $templatecolumn = &$pntable['template_column'];

    // Create the table - the formatting here is not mandatory, but it does
    // make the SQL statement relatively easy to read.  Also, separating out
    // the SQL statement from the Execute() command allows for simpler
    // debug operation if it is ever needed
    $sql = "CREATE TABLE $templatetable (
            $templatecolumn[tid] int(10) NOT NULL auto_increment,
            $templatecolumn[name] varchar(32) NOT NULL default '',
            $templatecolumn[number] int(5) NOT NULL default 0,
            PRIMARY KEY(pn_tid))";
    $dbconn->Execute($sql);

    // Check for an error with the database code, and if so set an
    // appropriate error message and return
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED);
        return false;
    }

    // Set up an initial value for a module variable.  Note that all module
    // variables should be initialised with some value in this way rather
    // than just left blank, this helps the user-side code and means that
    // there doesn't need to be a check to see if the variable is set in
    // the rest of the code as it always will be
    pnModSetVar('Template', 'bold', 0);
    pnModSetVar('Template', 'itemsperpage', 10);

    // Initialisation successful
    return true;
}

/**
 * upgrade the template module from an old version
 * This function can be called multiple times
 */
function template_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case 0.5:
            // Version 0.5 didn't have a 'number' field, it was added
            // in version 1.0

            // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
            // return arrays but we handle them differently.  For pnDBGetConn()
            // we currently just want the first item, which is the official
            // database handle.  For pnDBGetTables() we want to keep the entire
            // tables array together for easy reference later on
            // This code could be moved outside of the switch statement if
            // multiple upgrades need it
            list($dbconn) = pnDBGetConn();
            $pntable = pnDBGetTables();

            // It's good practice to name the table and column definitions you
            // are getting - $table and $column don't cut it in more complex
            // modules
            // This code could be moved outside of the switch statement if
            // multiple upgrades need it
            $templatetable = $pntable['template'];
            $templatecolumn = &$pntable['template_column'];

            // Add a column to the table - the formatting here is not
            // mandatory, but it does make the SQL statement relatively easy
            // to read.  Also, separating out the SQL statement from the
            // Execute() command allows for simpler debug operation if it is
            // ever needed
            $sql = "ALTER TABLE $templatetable
                    ADD $templatecolumn[number] int(5) NOT NULL default 0";
            $dbconn->Execute($sql);

            // Check for an error with the database code, and if so set an
            // appropriate error message and return
            if ($dbconn->ErrorNo() != 0) {
                pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
                return false;
            }

            // At the end of the successful completion of this function we
            // recurse the upgrade to handle any other upgrades that need
            // to be done.  This allows us to upgrade from any version to
            // the current version with ease
            return template_upgrade(1.0);
        case 1.0:
            // Code to upgrade from version 1.0 goes here
            break;
        case 2.0:
            // Code to upgrade from version 2.0 goes here
            break;
    }

    // Update successful
    return true;
}

/**
 * delete the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function template_delete()
{
    // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
    // return arrays but we handle them differently.  For pnDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For pnDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Drop the table - for such a simple command the advantages of separating
    // out the SQL statement from the Execute() command are minimal, but as
    // this has been done elsewhere it makes sense to stick to a single method
    $sql = "DROP TABLE $pntable[template]";
    $dbconn->Execute($sql);

    // Check for an error with the database code, and if so set an
    // appropriate error message and return
    if ($dbconn->ErrorNo() != 0) {
        // Report failed deletion attempt
        return false;
    }

    // Delete any module variables
    pnModDelVar('Template', 'itemsperpage');
    pnModDelVar('Template', 'bold');

    // Deletion successful
    return true;
}

?>