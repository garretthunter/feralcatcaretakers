<?php
// $Id: pnadminapi.php,v 1.2 2002/10/07 14:36:31 skooter Exp $
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
// Purpose of file:  Blocks administration API
// ----------------------------------------------------------------------

/**
 * increment position of a block
 * <br>
 * This function moves a block such that it is higher in the
 * block display
 * @param $args['bid'] the ID of the block to increment
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_inc($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($bid) || !is_numeric($bid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "::$bid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _BLOCKSNOAUTH);
        return false;
    }


    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    // Get info on current position of block
    $sql = "SELECT $blockscolumn[weight],
                   $blockscolumn[position]
            FROM $blockstable
            WHERE $blockscolumn[bid]='" . (int)pnVarPrepForStore($bid)."'";
    $result = $dbconn->Execute($sql);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such block ID $bid");
        return false;
    }
    list($seq, $position) = $result->fields;
    $result->Close();

    // Get info on displaced block
    $sql = "SELECT $blockscolumn[bid],
                   $blockscolumn[weight]
            FROM $blockstable
            WHERE $blockscolumn[weight]<" . pnVarPrepForStore($seq) . "
            AND   $blockscolumn[position]='" . pnVarPrepForStore($position) . "'
            ORDER BY $blockscolumn[weight] DESC";
    $result = $dbconn->SelectLimit($sql, 1);
    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No block directly above that one");
        return false;
    }
    list($altbid, $altseq) = $result->fields;
    $result->Close();

    // Swap sequence numbers
    $sql = "UPDATE $blockstable
            SET $blockscolumn[weight]=$seq
            WHERE $blockscolumn[bid]=$altbid";
    $dbconn->Execute($sql);
    $sql = "UPDATE $blockstable
            SET $blockscolumn[weight]=$altseq
            WHERE $blockscolumn[bid]=$bid";
    $dbconn->Execute($sql);

    return true;
}

/**
 * decrement position of a block
 * <br>
 * This function moves a block such that it is lower in the
 * block display
 * @param $args['bid'] the ID of the block to decrement
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_dec($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($bid) || !is_numeric($bid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "::$bid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _BLOCKSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    // Get info on current position of block
    $sql = "SELECT $blockscolumn[weight],
                   $blockscolumn[position]
            FROM $blockstable
            WHERE $blockscolumn[bid]=" . (int)pnVarPrepForStore($bid);
    $result = $dbconn->Execute($sql);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such block ID $bid");
        return false;
    }
    list($seq, $position) = $result->fields;
    $result->Close();

    // Get info on displaced block
    $sql = "SELECT $blockscolumn[bid],
                   $blockscolumn[weight]
            FROM $blockstable
            WHERE $blockscolumn[weight]>" . pnVarPrepForStore($seq) . "
            AND   $blockscolumn[position]='" . pnVarPrepForStore($position) . "'
            ORDER BY $blockscolumn[weight] ASC";
    $result = $dbconn->SelectLimit($sql, 1);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No block directly below that one");
        return false;
    }
    list($altbid, $altseq) = $result->fields;
    $result->Close();

    // Swap sequence numbers
    $sql = "UPDATE $blockstable
            SET $blockscolumn[weight]=$seq
            WHERE $blockscolumn[bid]=$altbid";
    $dbconn->Execute($sql);
    $sql = "UPDATE $blockstable
            SET $blockscolumn[weight]=$altseq
            WHERE $blockscolumn[bid]=$bid";
    $dbconn->Execute($sql);

    return true;
}

/**
 * update attributes of a block
 * @param $args['bid'] the ID of the block to update
 * @param $args['title'] the new title of the block
 * @param $args['position'] the new position of the block
 * @param $args['url'] the new URL of the block
 * @param $args['language'] the new language of the block
 * @param $args['content'] the new content of the block
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Optional arguments
    if (!isset($url)) {
        $url = '';
    }
    if (!isset($content)) {
        $content = '';
    }

    // Argument check
    if ((!isset($bid) || !is_numeric($bid)) ||
        (!isset($content)) ||
        (!isset($title)) ||
        (!isset($url)) ||
        (!isset($language)) ||
        (!isset($refresh)) ||
        (!isset($position))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "$title::$bid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _BLOCKSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    $sql = "UPDATE $blockstable
            SET $blockscolumn[content]='" . pnVarPrepForStore($content) . "',
                $blockscolumn[url]='" . pnVarPrepForStore($url) . "',
                $blockscolumn[title]='" . pnVarPrepForStore($title) . "',
                $blockscolumn[position]='" . pnVarPrepForStore($position) . "',
                $blockscolumn[refresh]='" . pnVarPrepForStore($refresh) . "',
                $blockscolumn[blanguage]='" . pnVarPrepForStore($language) . "'
            WHERE $blockscolumn[bid]=" . (int)pnVarPrepForStore($bid);
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _UPDATEFAILED);
        return false;
    }

    return true;
}

/**
 * create a new block
 * @param $args['title'] the title of the block
 * @param $args['position'] the position of the block
 * @param $args['mid'] the module ID of the block
 * @param $args['language'] the language of the block
 * @param $args['bkey'] the key of the block
 * @returns int
 * @return block Id on success, false on failure
 */
function blocks_adminapi_create($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($title)) ||
        (!isset($position)) ||
        (!isset($mid)) ||
        (!isset($language)) ||
        (!isset($bkey))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "$title::", ACCESS_ADD)) {
        pnSessionSetVar('errormsg', _BLOCKSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    $nextId = $dbconn->GenId($blockstable);

    $sql = "INSERT INTO $blockstable (
              $blockscolumn[bid],
              $blockscolumn[bkey],
              $blockscolumn[title],
              $blockscolumn[content],
              $blockscolumn[url],
              $blockscolumn[position],
              $blockscolumn[weight],
              $blockscolumn[active],
              $blockscolumn[refresh],
              $blockscolumn[last_update],
              $blockscolumn[blanguage],
              $blockscolumn[mid])
            VALUES (
              " . pnVarPrepForStore($nextId) . ",
              '" . pnVarPrepForStore($bkey) . "',
              '" . pnVarPrepForStore($title) . "',
              '',
              '',
              '" . pnVarPrepForStore($position) . "',
              0.5,
              1,
              3600,
              0,
              '" . pnVarPrepForStore($language) . "',
              " . pnVarPrepForStore($mid) . ")";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATEFAILED);
        return false;
    }

    // Get bid to return
    $bid = $dbconn->PO_Insert_ID($blockstable, $blockscolumn['bid']);

    // Resequence the blocks
    blocks_adminapi_resequence();

    return $bid;
}

/**
 * deactivate a block
 * @param $args['bid'] the ID of the block to deactivate
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_deactivate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($bid) || !is_numeric($bid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "::$bid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    // Deactivate
    $sql = "UPDATE $blockstable
            SET $blockscolumn[active] = 0
            WHERE $blockscolumn[bid] = " . (int)pnVarPrepForStore($bid);
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _DEACTIVATEERROR);
        return false;
    }

    return true;
}

/**
 * activate a block
 * @param $args['bid'] the ID of the block to activate
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_activate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($bid) || !is_numeric($bid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "::$bid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PERMISSIONSNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    // Deactivate
    $sql = "UPDATE $blockstable
            SET $blockscolumn[active] = 1
            WHERE $blockscolumn[bid] = " . (int)pnVarPrepForStore($bid);
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _ACTIVATEERROR);
        return false;
    }

    return true;
}

/**
 * delete a block
 * @param $args['bid'] the ID of the block to delete
 * @returns bool
 * @return true on success, false on failure
 */
function blocks_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($bid) || !is_numeric($bid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Blocks::', "::$bid", ACCESS_DELETE)) {
        pnSessionSetVar('errormsg', _BLOCKSNOAUTH);
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    $sql = "DELETE FROM $blockstable
            WHERE $blockscolumn[bid]=" . (int)pnVarPrepForStore($bid);
    $dbconn->Execute($sql);

    blocks_adminapi_resequence(array());

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _DELETEERROR);
        return false;
    }

    return true;
}


/**
 * resequence a blocks table
 * @returns void
 */
function blocks_adminapi_resequence()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];


    // Get the information
    $query = "SELECT $blockscolumn[bid],
                     $blockscolumn[position],
                     $blockscolumn[weight]
              FROM $blockstable
              ORDER BY $blockscolumn[position],
                       $blockscolumn[weight],
                       $blockscolumn[active] DESC";
    $result = $dbconn->Execute($query);

    // Fix sequence numbers
    $seq=1;
    $lastpos = '';
    while(list($bid, $position, $curseq) = $result->fields) {
        $result->MoveNext();

        // Reset sequence number if we've changed block position
        if ($lastpos != $position) {
            $seq = 1;
        }
        $lastpos = $position;

        if ($curseq != $seq) {
            $query = "UPDATE $blockstable
                      SET $blockscolumn[weight]=" . pnVarPrepForStore($seq) . "
                      WHERE $blockscolumn[bid]=" . pnVarPrepForStore($bid);
            $dbconn->Execute($query);
        }
        $seq++;
    }
    $result->Close();

    return;
}

?>