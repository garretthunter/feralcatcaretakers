<?php // File: $Id: security.php,v 1.4 2003/01/05 15:42:31 larsneo Exp $
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
// Original Author of file: Jim McDonald
// Purpose of file: Provide a low-level security access mechanism
// ----------------------------------------------------------------------


/*
 * Notes on security system
 *
 * Special UID and GIDS:
 *  UID -1 corresponds to 'all users', includes unregistered users
 *  GID -1 corresponds to 'all groups', includes unregistered users
 *  UID 0 corresponds to unregistered users
 *  GID 0 corresponds to unregistered users
 *
 */

/*
 * Defines for access levels
 */
define('ACCESS_INVALID', -1);
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 100);
define('ACCESS_READ', 200);
define('ACCESS_COMMENT', 300);
define('ACCESS_MODERATE', 400);
define('ACCESS_EDIT', 500);
define('ACCESS_ADD', 600);
define('ACCESS_DELETE', 700);
define('ACCESS_ADMIN', 800);


/*
 * Translation functions - avoids globals in external code
 */

// Translate level -> name
function accesslevelname($level) {
    $accessnames = accesslevelnames();
    return $accessnames[$level];
}

// Get all level -> name
function accesslevelnames() {
    static $accessnames = array(  0 => _ACCESS_NONE,
                                100 => _ACCESS_OVERVIEW,
                                200 => _ACCESS_READ,
                                300 => _ACCESS_COMMENT,
                                400 => _ACCESS_MODERATE,
                                500 => _ACCESS_EDIT,
                                600 => _ACCESS_ADD,
                                700 => _ACCESS_DELETE,
                                800 => _ACCESS_ADMIN);

    return $accessnames;
}


/*
 * schemas - holds all component/instance schemas
 * Should wrap this in a static one day, but the information
 * isn't critical so we'll do it later
 */
global $schemas;
$schemas = array();

/*
 * addinstanceschemainfo - register an instance schema with the security
 *                         system
 *
 * Takes two parameters:
 * - a component
 * - an instance schema
 *
 * Will fail if an attempt is made to overwrite an existing schema
 */
function addinstanceschemainfo($component, $schema)
{
    pnSecAddSchema($component, $schema);
}

function pnSecAddSchema($component, $schema)
{
    global $schemas;

    if (!empty($schemas[$component])) {
        return false;
    }

    $schemas[$component] = $schema;

    return true;
}

// Get list of schemas
function getinstanceschemainfo() {
    global $schemas;
    static $gotschemas = 0;

    if ($gotschemas == 0) {
        // Get all module schemas
        getmodulesinstanceschemainfo();

        // Get all block schemas
        pnBlockLoadAll();

        $gotschemas = 1;
    }

    return $schemas;
}

// Get instance information from modules
function getmodulesinstanceschemainfo() {

    $moddir = opendir('modules/');
    while ($modname = readdir($moddir)) {
        // Old-style version file
        $osfile = 'modules/' . pnVarPrepForOS($modname) . '/Version.php';
        @include $osfile;
        // New-style version file
        $osfile = 'modules/' . pnVarPrepForOS($modname) . '/pnversion.php';
        @include $osfile;
        if (!empty($modversion['securityschema'])) {
            foreach ($modversion['securityschema'] as $component => $instance) {
                pnSecAddSchema($component, $instance);
            }
        }
        $modversion['securityschema'] = '';
    }
    closedir($moddir);
}

function authorised($testrealm, $testcomponent, $testinstance, $testlevel)
{
    // Wrapper for new pnSecAuthAction() function
    return pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel);

}

/**
 * see if a user is authorised to carry out a particular task
 * @public
 * @param realm the realm under test
 * @param component the component under test
 * @param instance the instance under test
 * @param level the level of access required
 * @returns bool
 * @return true if authorised, false if not
 */
function pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel)
{
    static $authinfogathered = 0;
    static $userperms, $groupperms;
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if ($authinfogathered == 0) {
        // First time here - get auth info
        list($userperms, $groupperms) = pnSecGetAuthInfo();

        if ((count($userperms) == 0) &&
            (count($groupperms) == 0)) {
                // No permissions
                return;
        }

        $authinfogathered = 1;
    }

    // Get user access level
    $userlevel = pnSecGetLevel($userperms, $testrealm, $testcomponent, $testinstance);

    // User access level is override, so return that if it exists
    if ( $userlevel > ACCESS_INVALID ) {
        // user has explicitly defined access level for this
        // realm/component/instance combination
        if ( $userlevel >= $testlevel ) {
            // permission is granted to user
            return true;
        } else {
            // permission is prohibited to user, so group perm
            // doesn't matter
            return false;
        }
    }

    // User access level not defined. Now check group access level
    $grouplevel = pnSecGetLevel($groupperms, $testrealm, $testcomponent, $testinstance);
    if ($grouplevel >= $testlevel) {
        // permission is granted to associated group
        return true;
    }

    // No access granted
    return false;
}

/**
 * get authorisation information for this user
 * @public
 * @returns array
 * @return two-element array of user and group permissions
 */
function pnSecGetAuthInfo()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Tables we use
    $userpermtable = $pntable['user_perms'];
    $userpermcolumn = &$pntable['user_perms_column'];

    $groupmembershiptable = $pntable['group_membership'];
    $groupmembershipcolumn = &$pntable['group_membership_column'];

    $grouppermtable = $pntable['group_perms'];
    $grouppermcolumn = &$pntable['group_perms_column'];

    $realmtable = $pntable['realms'];
    $realmcolumn = &$pntable['realms_column'];

    // Empty arrays
    $userperms = array();
    $groupperms = array();

    $uids[] = -1;
    // Get user ID
    if (!pnUserLoggedIn()) {
       // Unregistered UID
       $uids[] = 0;
       $vars['Active User'] = 'unregistered';
    } else {
        $uids[] = pnUserGetVar('uid');
        $vars['Active User'] = pnUserGetVar('uid');
    }
    $uids = implode(",", $uids);

    // Get user permissions
    $query = "SELECT $userpermcolumn[realm],
                     $userpermcolumn[component],
                     $userpermcolumn[instance],
                     $userpermcolumn[level]
              FROM $userpermtable
              WHERE $userpermcolumn[uid] IN (" . pnVarPrepForStore($uids) . ")
              ORDER by $userpermcolumn[sequence]";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return array($userperms, $groupperms);
    }

    while(list($realm, $component, $instance, $level) = $result->fields) {
        $result->MoveNext();

        // Fix component and instance to auto-insert '.*'
        $component = preg_replace('/^$/', '.*', $component);
        $component = preg_replace('/^:/', '.*:', $component);
        $component = preg_replace('/::/', ':.*:', $component);
        $component = preg_replace('/:$/', ':.*', $component);
        $instance = preg_replace('/^$/', '.*', $instance);
        $instance = preg_replace('/^:/', '.*:', $instance);
        $instance = preg_replace('/::/', ':.*:', $instance);
        $instance = preg_replace('/:$/', ':.*', $instance);

        $userperms[] = array("realm"     => $realm,
                             "component" => $component,
                             "instance"  => $instance,
                             "level"     => $level);
    }

    // Get all groups that user is in
    $query = "SELECT $groupmembershipcolumn[gid]
              FROM $groupmembershiptable
              WHERE $groupmembershipcolumn[uid] IN (" . pnVarPrepForStore($uids) . ")";

    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return array($userperms, $groupperms);
    }

    $usergroups[] = -1;
    if (!pnUserLoggedIn()) {
       // Unregistered GID
       $usergroups[] = 0;
    }
    while(list($gid) = $result->fields) {
        $result->MoveNext();

        $usergroups[] = $gid;
    }
    $usergroups = implode(",", $usergroups);

    // Get all group permissions
    $query = "SELECT $grouppermcolumn[realm],
                     $grouppermcolumn[component],
                     $grouppermcolumn[instance],
                     $grouppermcolumn[level]
              FROM $grouppermtable
              WHERE $grouppermcolumn[gid] IN (" . pnVarPrepForStore($usergroups) . ")
              ORDER by $grouppermcolumn[sequence]";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return array($userperms, $groupperms);
    }

    while(list($realm, $component, $instance, $level) = $result->fields) {
        $result->MoveNext();

        // Fix component and instance to auto-insert '.*' where
        // there is nothing there
        $component = preg_replace('/^$/', '.*', $component);
        $component = preg_replace('/^:/', '.*:', $component);
        $component = preg_replace('/::/', ':.*:', $component);
        $component = preg_replace('/:$/', ':.*', $component);
        $instance = preg_replace('/^$/', '.*', $instance);
        $instance = preg_replace('/^:/', '.*:', $instance);
        $instance = preg_replace('/::/', ':.*:', $instance);
        $instance = preg_replace('/:$/', ':.*', $instance);

        // Search/replace of special names
        while (preg_match("/<([^>]+)>/", $instance, $res)) {
            $instance = preg_replace("/<([^>]+)>/", $vars[$res[1]], $instance, 1);
        }

        $groupperms[] = array("realm"     => $realm,
                              "component" => $component,
                              "instance"  => $instance,
                              "level"     => $level);
    }

    return array($userperms, $groupperms);
}

/**
 * calculate security level for a test item
 * @public
 * @param perms array of permissions to test against
 * @param testrealm realm of item under test
 * @param testcomponent component of item under test
 * @param testinstanc instance of item under test
 * @returns int
 * @return matching security level
 */
function pnSecGetLevel($perms, $testrealm, $testcomponent, $testinstance)
{
    $level = ACCESS_INVALID;

    // If we get a test component or instance purely consisting of ':' signs
    // then it counts as blank
    $testcomponent = preg_replace('/^:*$/', '', $testcomponent);
    $testinstance = preg_replace('/^:*$/', '', $testinstance);

    // Test for generic permission
    if ((empty($testcomponent)) &&
        (empty($testinstance))) {
        // Looking for best permission
        foreach ($perms as $perm) {
            // Confirm generic realm, or this particular realm
            if (($perm['realm'] != 0) && ($perm['realm'] != $testrealm)) {
                continue;
            }

            if ($perm['level'] > $level) {
                $level = $perm['level'];
            }
        }
        return $level;
    }

    // Test for generic instance
    if (empty($testinstance)) {
        // Looking for best permission
        foreach ($perms as $perm) {
            // Confirm generic realm, or this particular realm
            if (($perm['realm'] != 0) && ($perm['realm'] != $testrealm)) {
                continue;
            }

            // Confirm that component matches
            if (preg_match("/^$perm[component]$/", $testcomponent)) {
                if ($perm['level'] > $level) {
                    $level = $perm['level'];
                }
            }
        }
        return $level;
    }


    // Normal permissions check
    foreach ($perms as $perm) {

        // Confirm generic realm, or this particular realm
        if (($perm['realm'] != 0) && ($perm['realm'] != $testrealm)) {
            continue;
        }

        if (($testcomponent != '') && ($testinstance != '')) {
            // Confirm that component and instance match
            if (!((ereg("^$perm[component]$", $testcomponent)) &&
                  (ereg("^$perm[instance]$", $testinstance)))) {
                continue;
            }
        } elseif (($testcomponent == '') && ($testinstance != '')) {
            // Confirm that instance matches
            if (!ereg("^$perm[instance]$", $testinstance)) {
                continue;
            }
        }

        // We have a match - set the level and quit
        $level = $perm['level'];
        break;

    }
    return($level);
}

/**
 * generate an authorisation key
 * <br>
 * The authorisation key is used to confirm that actions requested by a
 * particular user have followed the correct path.  Any stage that an
 * action could be made (e.g. a form or a 'delete' button) this function
 * must be called and the resultant string passed to the client as either
 * a GET or POST variable.  When the action then takes place it first calls
 * <code>pnSecConfirmAuthKey()</code> to ensure that the operation has
 * indeed been manually requested by the user and that the key is valid
 *
 * @public
 * @param modname the module this authorisation key is for (optional)
 * @returns string
 * @return an encrypted key for use in authorisation of operations
 */
function pnSecGenAuthKey($modname='')
{

    if (empty($modname)) {
        $modname = pnVarCleanFromInput('module');
    }

// Date gives extra security but leave it out for now
//    $key = pnSessionGetVar('rand') . $modname . date ('YmdGi');
    $key = pnSessionGetVar('rand') . strtolower($modname);

    // Encrypt key
    $authid = md5($key);

    // Return encrypted key
    return $authid;
}

/**
 * confirm an authorisation key is valid
 * <br>
 * See description of <code>pnSecGenAuthKey</code> for information on
 * this function
 * @public
 * @returns bool
 * @return true if the key is valid, false if it is not
 */
function pnSecConfirmAuthKey()
{
    list($module, $authid) = pnVarCleanFromInput('module', 'authid');

    // Regenerate static part of key
    $partkey = pnSessionGetVar('rand') . strtolower($module);

// Not using time-sensitive keys for the moment
//    // Key life is 5 minutes, so search backwards and forwards 5
//    // minutes to see if there is a match anywhere
//    for ($i=-5; $i<=5; $i++) {
//        $testdate  = mktime(date('G'), date('i')+$i, 0, date('m') , date('d'), date('Y'));
//
//        $testauthid = md5($partkey . date('YmdGi', $testdate));
//        if ($testauthid == $authid) {
//            // Match
//
//            // We've used up the current random
//            // number, make up a new one
//            srand((double)microtime()*1000000);
//            pnSessionSetVar('rand', rand());
//
//            return true;
//        }
//    }

    if ((md5($partkey)) == $authid) {
        // Match - generate new random number for next key and leave happy
        srand((double)microtime()*1000000);
        pnSessionSetVar('rand', rand());

        return true;
    }

    // Not found, assume invalid
    return false;
}

?>