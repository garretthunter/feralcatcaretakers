<?php
// $Id: pnuserapi.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Purpose of file:  Ratings user API
// ----------------------------------------------------------------------

/**
 * get a rating for a specific item
 * @param $args['modname'] name of the module this rating is for
 * @param $args['objectid'] ID of the item this rating is for
 * @param $args['ratingtype'] type of rating (optional)
 * @returns int
 * @return rating the corresponding rating, or boid if no rating exists
 */
function ratings_userapi_get($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($modname)) ||
        (!isset($objectid))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return;
    }

    if (!isset($ratingtype)) {
        $ratingtype = 'default';
    }

    // Security check
    if (!pnSecAuthAction(0, 'Ratings::', "$modname:$ratingtype:$objectid", ACCESS_READ)) {
        return;
    }

    // Database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $ratingstable = $pntable['ratings'];
    $ratingscolumn = &$pntable['ratings_column'];

    // Get items
    $sql = "SELECT $ratingscolumn[rating]
            FROM $ratingstable
            WHERE $ratingscolumn[module] = '" . pnVarPrepForStore($modname) . "'
              AND $ratingscolumn[itemid] = '" . pnVarPrepForStore($objectid) . "'
              AND $ratingscolumn[ratingtype] = '" . pnVarPrepForStore($ratingtype) . "'";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', 'SQL Error');
        return;
    }

    $rating = $result->fields[0];
    $result->close();

    return $rating;
}

/**
 * rate an item
 * @param $args['modname'] module name of the item to rate
 * @param $args['id'] ID of the item to rate
 * @param $args['ratingtype'] type of rating (optional)
 * @param $args['rating'] actual rating
 * @returns int
 * @return the new rating for this item
 */
function ratings_userapi_rate($args)
{

    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($modname)) ||
        (!isset($objectid)) ||
        (!isset($rating))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return;
    }

    if (!isset($ratingtype)) {
        $ratingtype = 'default';
    }

    // Security check
    if (!pnSecAuthAction(0, 'Ratings::', "$modname:$ratingtype:$objectid", ACCESS_COMMENT)) {
        return;
    }

    // Database information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $ratingstable = $pntable['ratings'];
    $ratingscolumn = &$pntable['ratings_column'];
    $ratingslogtable = $pntable['ratingslog'];
    $ratingslogcolumn = &$pntable['ratingslog_column'];

    // Multipe rate check
    $seclevel = pnModGetVar('Ratings', 'seclevel');
    if ($seclevel == 'high') {
        if (pnUserLoggedIn()) {
            $ratingid = pnUserGetVar('uid');
        } else {
            $ratingid = $HTTP_SERVER_VARS['REMOTE_ADDR'];
            if (empty($ratingid)) {
                $ratingid = getenv('REMOTE_ADDR');
            }
        }
        // Check against table to see if user has already voted
        $sql = "SELECT COUNT(1)
                FROM $ratingslogtable
                WHERE $ratingslogcolumn[id] = '" . pnVarPrepForStore($objectid) . "'
                AND $ratingslogcolumn[ratingid] = '" . pnVarPrepForStore($ratingid) . "'";
        $result = $dbconn->Execute($sql);
        if (!$result->EOF) {
            $result->Close();
            return;
        }
        $result->Close();
    } elseif ($seclevel == 'medium') {
        // Check against session to see if user has voted recently
        if (pnSessionGetVar("Rated$modname$ratingtype$objectid")) {
            return;
        }
    } // No check for low

    // Get current information on rating
    $sql = "SELECT $ratingscolumn[rid],
                   $ratingscolumn[rating],
                   $ratingscolumn[numratings]
            FROM $ratingstable
            WHERE $ratingscolumn[module] = '" . pnVarPrepForStore($modname) . "'
              AND $ratingscolumn[itemid] = '" . pnVarPrepForStore($objectid) . "'
              AND $ratingscolumn[ratingtype] = '" . pnVarPrepForStore($ratingtype) . "'";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', 'SQL Error');
        return;
    }

    if (!$result->EOF) {
        // Update current rating
        list($rid, $currating, $numratings) = $result->fields;
        $result->close();

        // Calculate new rating
        $newnumratings = $numratings + 1;
        $newrating = (int)((($currating*$numratings) + $rating)/$newnumratings);

        // Insert new rating
        $sql = "UPDATE $ratingstable
                SET $ratingscolumn[rating] = " . pnVarPrepForStore($newrating) . ",
                    $ratingscolumn[numratings] = $newnumratings
                WHERE $ratingscolumn[rid] = $rid";
        $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            pnSessionSetVar('errormsg', 'SQL Error');
            return;
        }
    } else {
        $result->close();

        // Get a new ratings ID
        $rid = $dbconn->GenId($ratingstable);
        // Create new rating
        $sql = "INSERT INTO $ratingstable($ratingscolumn[rid],
                                          $ratingscolumn[module],
                                          $ratingscolumn[itemid],
                                          $ratingscolumn[ratingtype],
                                          $ratingscolumn[rating],
                                          $ratingscolumn[numratings])
                VALUES ($rid,
                        '" . pnVarPrepForStore($modname) . "',
                        '" . pnVarPrepForStore($objectid) . "',
                        '" . pnVarPrepForStore($ratingtype) . "',
                        " . pnVarPrepForStore($rating) . ",
                        1)";

        $dbconn->Execute($sql);

        if ($dbconn->ErrorNo() != 0) {
            pnSessionSetVar('errormsg', 'SQL Error');
            return;
        }

        $newrating = $rating;
    }

    // Set note that user has rated this item if required
    if ($seclevel == 'high') {
        $ratingslogtable = $pntable['ratingslog'];
        $ratingslogcolumn = &$pntable['ratingslog_column'];
        $sql = "INSERT INTO $ratingslogtable
                  ($ratingslogcolumn[id],
                   $ratingslogcolumn[ratingid],
                   $ratingslogcolumn[rating])
                VALUES ('" . pnVarPrepForStore($objectid) . "',
                        '" . pnVarPrepForStore($modname . $objectid . $ratingtype) . "',
                        $rating)";
        $dbconn->Execute($sql);
    } elseif ($seclevel == 'medium') {
        pnSessionSetVar("Rated$modname$ratingtype$objectid", true);
    }
    return $newrating;
}

?>