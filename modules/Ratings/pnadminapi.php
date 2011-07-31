<?php
// $Id: pnadminapi.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Purpose of file:  Raitings administration API
// ----------------------------------------------------------------------

/**
 * create a new template item
 * @param $args['name'] name of the item
 * @param $args['number'] number of the item
 * @returns int
 * @return template item ID on success, false on failure
 */
function ratings_adminapi_create($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($name)) ||
        (!isset($number))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Template', '::', ACCESS_ADD)) {
        pnSessionSetVar('errormsg', _RATINGSNOAUTH);
        return false;
    }

    // Get datbase setup
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $templatetable = $pntable['template'];
    $templatecolumn = &$pntable['template_column'];

    // Get next ID in table
    $nextId = $dbconn->GenId($templatetable);

    // Add item
    $sql = "INSERT INTO $templatetable (
              $templatecolumn[tid],
              $templatecolumn[name],
              $templatecolumn[number])
            VALUES (
              $nextId,
              '" . pnVarPrepForStore($name) . "',
              " . pnvarPrepForStore($number) . ")";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATEFAILED);
        return false;
    }

    // Get tid to return
    $tid = $dbconn->PO_Insert_ID($templatetable, $templatecolumn['tid']);

    return $tid;
}

/**
 * delete a template item
 * @param $args['tid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 */
function ratings_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($tid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Template', '::', ACCESS_DELETE)) {
        pnSessionSetVar('errormsg', _RATINGSNOAUTH);
        return false;
    }

    // Get datbase setup
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $templatetable = $pntable['template'];
    $templatecolumn = &$pntable['template_column'];

    // Delete item
    $sql = "DELETE FROM $templatetable
            WHERE $templatecolumn[tid] = " . pnVarPrepForStore($tid);
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _DELETEFAILED);
        return false;
    }

    return true;
}

/**
 * update a template item
 * @param $args['tid'] the ID of the item
 * @param $args['name'] the new name of the item
 * @param $args['number'] the new number of the item
 */
function ratings_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($tid)) ||
        (!isset($name)) ||
        (!isset($number))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Template', "$name::$tid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _RATINGSNOAUTH);
        return false;
    }

    // Get datbase setup
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $templatetable = $pntable['template'];
    $templatecolumn = &$pntable['template_column'];

    // Update the item
    $sql = "UPDATE $templatetable
            SET $templatecolumn[name] = '" . pnVarPrepForStore($name) . "',
                $templatecolumn[number] = " . pnVarPrepForStore($number) . "
            WHERE $templatecolumn[tid] = " . pnVarPrepForStore($tid);
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _DELETEFAILED);
        return false;
    }

    return true;
}
?>