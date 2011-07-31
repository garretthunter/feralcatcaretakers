<?php
// File: $Id: pnadminapi.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Purpose of file:  Permissions administration API
// ----------------------------------------------------------------------

/**
 * increment sequence number of a permission
 * <br>
 * This function raises a permission higher up in the overall
 * permissions sequence, thus making it more likely to be acted
 * against
 * @param $args['type'] the type of the permission to
 *        increment (user or group)
 * @param $args['pid'] the ID of the permission to increment
 * @returns bool
 * @return true on success, false on failure
 */
function permissions_adminapi_inc($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($type)) ||
        (!isset($pid))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Permissions::', "$type::$pid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    // Work out which tables to operate against, and
    // various other bits and pieces
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
    }

    // Get info on current perm
    $query = "SELECT $permcolumn[sequence]
              FROM $permtable
              WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such permissions ID $pid");
        return false;
    }
    list($seq) = $result->fields;
    $result->Close();

    if ($seq != 1) {
        $altseq = $seq-1;
        // Get info on displaced perm
        $query = "SELECT $permcolumn[pid]
                  FROM $permtable
                  WHERE $permcolumn[sequence]=" . pnVarPrepForStore($altseq);
        $result = $dbconn->Execute($query);
        if ($result->EOF) {
            pnSessionSetVar('errormsg', "No permission directly above that one");
            return false;
        }
        list($altpid) = $result->fields;
        $result->Close();

        // Swap sequence numbers
        $query = "UPDATE $permtable
                  SET $permcolumn[sequence]=" . pnVarPrepForStore($seq) . "
                  WHERE $permcolumn[pid]=" . pnVarPrepForStore($altpid);
        $dbconn->Execute($query);
        $query = "UPDATE $permtable
                  SET $permcolumn[sequence]=" . pnVarPrepForStore($altseq) . "
                  WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
        $dbconn->Execute($query);
    }

    return true;
}

/**
 * decrement sequence number of a permission
 * @param $args['type'] the type of the permission to
 *        decrement (user or group)
 * @param $args['pid'] the ID of the permission to decrement
 * @returns bool
 * @return true on success, false on failure
 */
function permissions_adminapi_dec($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($type)) ||
        (!isset($pid))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }


    // Security check
    if (!pnSecAuthAction(0, 'Permissions::', "$type::$pid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    // Work out which tables to operate against
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
    }

    // Get info on current perm
    $query = "SELECT $permcolumn[sequence]
              FROM $permtable
              WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such permissions ID $pid");
        return false;
    }
    list($seq) = $result->fields;
    $result->Close();

    $maxseq = permissions_adminapi_maxsequence(array('table' => $permtable,
                                                     'column' => $permcolumn['sequence']));
    if ($seq != $maxseq) {
        $altseq = $seq+1;
        // Get info on displaced perm
        $query = "SELECT $permcolumn[pid]
                  FROM $permtable
                  WHERE $permcolumn[sequence]=" . pnVarPrepForStore($altseq);
        $result = $dbconn->Execute($query);
        if ($result->EOF) {
            pnSessionSetVar('errormsg', "No permission directly below that one");
            return false;
        }
        list($altpid) = $result->fields;
        $result->Close();

        // Swap sequence numbers
        $query = "UPDATE $permtable
                  SET $permcolumn[sequence]=" . pnVarPrepForStore($seq) . "
                  WHERE $permcolumn[pid]=" . pnVarPrepForStore($altpid);
        $dbconn->Execute($query);
        $query = "UPDATE $permtable
                  SET $permcolumn[sequence]=" . pnVarPrepForStore($altseq) . "
                  WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
        $dbconn->Execute($query);
    }

    return true;
}

/**
 * update attributes of a permission
 * @param $args['type'] the type of the permission to update (user or group)
 * @param $args['pid'] the ID of the permission to update
 * @param $args['realm'] the new realm of the permission
 * @param $args['id'] the new group/user id of the permission
 * @param $args['component'] the new component of the permission
 * @param $args['instance'] the new instance of the permission
 * @param $args['level'] the new level of the permission
 * @returns bool
 * @return true on success, false on failure
 */
function permissions_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($type)) ||
        (!isset($pid)) ||
        (!isset($realm)) ||
        (!isset($id)) ||
        (!isset($component)) ||
        (!isset($instance)) ||
        (!isset($level))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Permissions::', "$type::$pid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Work out which tables to operate against
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
        $idfield = $permcolumn['uid'];
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
        $idfield = $permcolumn['gid'];
    }

    $query = "UPDATE $permtable
              SET $permcolumn[realm]=" . pnVarPrepForStore($realm) . ",
                  $idfield=" . pnVarPrepForStore($id) . ",
                  $permcolumn[component]='" . pnVarPrepForStore($component) . "',
                  $permcolumn[instance]='" . pnVarPrepForStore($instance) . "',
                  $permcolumn[level]=" . pnVarPrepForStore($level) . "
              WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', "Error updating $type permission $pid");
        return false;
    }

    return true;
}

/**
 * create a new perm
 * @param $args['type'] the type of the permission to update (user or group)
 * @param $args['realm'] the new realm of the permission
 * @param $args['id'] the new group/user id of the permission
 * @param $args['component'] the new component of the permission
 * @param $args['instance'] the new instance of the permission
 * @param $args['level'] the new level of the permission
 * @returns bool
 * @return true on success, false on failure
 */
function permissions_adminapi_create($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($type)) ||
        (!isset($realm)) ||
        (!isset($id)) ||
        (!isset($component)) ||
        (!isset($instance)) ||
        (!isset($level))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Permissions::', '::', ACCESS_ADD)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    // Work out which tables to operate against
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
        $idfield = $permcolumn['uid'];
        $view = "secviewuserperms";
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
        $idfield = $permcolumn['gid'];
        $view = "secviewgroupperms";
    }

    $maxseq = permissions_adminapi_maxsequence(array('table' => $permtable,
                                                     'column' => $permcolumn['sequence']));
    $newseq = $maxseq + 1;
    $nextId = $dbconn->GenId($permtable);

    $query = "INSERT INTO $permtable
               ($permcolumn[pid],
                $permcolumn[realm],
                $idfield,
                $permcolumn[sequence],
                $permcolumn[component],
                $permcolumn[instance],
                $permcolumn[level],
                $permcolumn[bond])
             VALUES
               (" . pnVarPrepForStore($nextId) . ",
                " . pnVarPrepForStore($realm) . ",
                " . pnVarPrepForStore($id) . ",
                " . pnVarPrepForStore($newseq) . ",
                '" . pnVarPrepForStore($component) . "',
                '" . pnVarPrepForStore($instance) . "',
                " . pnVarPrepForStore($level) . ",
                0)";
    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', "Error adding $type permission");
        return false;
    }

    return true;
}

/**
 * delete a perm
 * @param $args['type'] the type of the permission to update (user or group)
 * @param $args['pid'] the ID of the permission to delete
 * @returns bool
 * @return true on success, false on failure
 */
function permissions_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($type)) ||
        (!isset($pid))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Permissions::', "$type::$pid", ACCESS_DELETE)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Work out which tables to operate against
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
        $idfield = $permcolumn['uid'];
        $view = "secviewuserperms";
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
        $idfield = $permcolumn['gid'];
        $view = "secviewgroupperms";
    }

    $query = "DELETE FROM $permtable
              WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
    $dbconn->Execute($query);

    permissions_adminapi_resequence(array('type' => $type));

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', "Error deleting $type permission $pid");
        return false;
    }

    return true;
}


/**
 * get the maximum sequence number currently in a given table
 * @param $args['table'] the table name
 * @param $args['column'] the sequence column name
 * @returns int
 * @return the maximum sequence number
 */
function permissions_adminapi_maxsequence($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($table)) ||
        (!isset($column))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();

    $query = "SELECT MAX($column)
              FROM $table";
    $result = $dbconn->Execute($query);
    list($maxseq) = $result->fields;
    $result->Close();

    return($maxseq);
}

/**
 * resequence a permissions table
 * @param $args['type'] the type of permissions to resequence
 * @returns void
 */
function permissions_adminapi_resequence($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($type)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Work out which tables to operate against
    if ($type == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
    }

    // Get the information
    $query = "SELECT $permcolumn[pid],
                     $permcolumn[sequence]
              FROM $permtable
              ORDER BY $permcolumn[sequence]";
    $result = $dbconn->Execute($query);

    // Fix sequence numbers
    $seq=1;
    while(list($pid, $curseq) = $result->fields) {

        $result->MoveNext();
        if ($curseq != $seq) {
            $query = "UPDATE $permtable
                      SET $permcolumn[sequence]=" . pnVarPrepForStore($seq) . "
                      WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
            $dbconn->Execute($query);
        }
        $seq++;
    }
    $result->Close();

    return;
}

?>