<?php
// File: $Id: pnadminapi.php,v 1.9 2002/12/17 20:03:08 larsneo Exp $
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
// Purpose of file:  Modules administration API
// ----------------------------------------------------------------------

/**
 * update module information
 * @param $args['mid'] the id number of the module to update
 * @param $args['displayname'] the new display name of the module
 * @param $args['description'] the new description of the module
 * @returns bool
 * @return true on success, false on failure
 */
function modules_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($mid)||!is_numeric($mid)) ||
        (!isset($displayname)) ||
        (!isset($description))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Modules::', "::$mid", ACCESS_ADMIN)) {
        pnSessionSetVar('errormsg', _MODULESAPINOAUTH);
        return false;
    }

    // Rename operation
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "UPDATE $modulestable
              SET $modulescolumn[displayname] = '" . pnVarPrepForStore($displayname) . "',
                  $modulescolumn[description] = '" . pnVarPrepForStore($description) . "'
              WHERE $modulescolumn[id] = " . (int)pnVarPrepForStore($mid);
    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _MODULESAPIUPDATEFAILED);
        return false;
    }

    // Hooks

    // Get module name
    $modinfo = pnModGetInfo($mid);

    $hookstable = $pntable['hooks'];
    $hookscolumn = &$pntable['hooks_column'];
    $sql = "SELECT DISTINCT $hookscolumn[id],
                            $hookscolumn[smodule],
                            $hookscolumn[stype],
                            $hookscolumn[object],
                            $hookscolumn[action],
                            $hookscolumn[tarea],
                            $hookscolumn[tmodule],
                            $hookscolumn[ttype],
                            $hookscolumn[tfunc]
            FROM $hookstable
            WHERE $hookscolumn[smodule] IS NULL
            ORDER BY $hookscolumn[tmodule],
                     $hookscolumn[smodule] DESC";
    $result = $dbconn->Execute($sql);
    $displayed = array();
    for (; !$result->EOF; $result->MoveNext()) {
        list($hookid,
             $hooksmodname,
             $hookstype,
             $hookobject,
             $hookaction,
             $hooktarea,
             $hooktmodule,
             $hookttype,
             $hooktfunc,) = $result->fields;

        // Delete hook regardless
        $sql = "DELETE FROM $hookstable
                WHERE $hookscolumn[smodule] = '" . pnVarPrepForStore($modinfo['name']) . "'
                  AND $hookscolumn[tmodule] = '" . pnVarPrepForStore($hooktmodule) . "'";
        $dbconn->Execute($sql);

        // Get selected value of hook
        $hookvalue = pnVarCleanFromInput("hooks_$hooktmodule");

        // See if this is checked and isn't in the database
        if ((isset($hookvalue)) && (empty($hooksmodname))) {
            // Insert hook if required
            $sql = "INSERT INTO $hookstable (
                      $hookscolumn[id],
                      $hookscolumn[object],
                      $hookscolumn[action],
                      $hookscolumn[smodule],
                      $hookscolumn[tarea],
                      $hookscolumn[tmodule],
                      $hookscolumn[ttype],
                      $hookscolumn[tfunc])
                    VALUES (
                      " . pnVarPrepForStore($dbconn->GenId($hookstable)) . ",
                      '" . pnVarPrepForStore($hookobject) . "',
                      '" . pnVarPrepForStore($hookaction) . "',
                      '" . pnVarPrepForStore($modinfo['name']) . "',
                      '" . pnVarPrepForStore($hooktarea) . "',
                      '" . pnVarPrepForStore($hooktmodule) . "',
                      '" . pnVarPrepForStore($hookttype) . "',
                      '" . pnVarPrepForStore($hooktfunc) . "')";
            $dbconn->Execute($sql);

            if($dbconn->ErrorNo() != 0) {
                return false;
            }
        }

    }
    $result->Close();

    return true;
}

/**
 * obtain list of modules
 * @returns array
 * @return associative array of known modules
 */
function modules_adminapi_list()
{
    // Security check
    if (!pnSecAuthAction(0, 'Modules::', '::', ACCESS_ADMIN)) {
        pnSessionSetVar('errormsg', _MODULESAPINOAUTH);
        return false;
    }

    // Obtain information
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[id],
                     $modulescolumn[name],
                     $modulescolumn[type],
                     $modulescolumn[displayname],
                     $modulescolumn[description],
                     $modulescolumn[directory],
                     $modulescolumn[state]
              FROM $modulestable
              ORDER BY $modulescolumn[name]";
    $result = $dbconn->Execute($query);

    if ($result->EOF) {
        return false;
    }

    $resarray = array();
    while(list($mid, $name, $modtype, $displayname, $description, $directory, $state) = $result->fields) {
        $result->MoveNext();
        
        $resarray[] = array('id' => $mid,
                            'name' => $name,
                            'displayname' => $displayname,
                            'description' => $description,
                            'directory' => $directory,
                            'state' => $state);
    }
    $result->Close();

    return $resarray;
}

/**
 * set the state of a module
 * @param $args['mid'] the module id
 * @param $args['state'] the state
 */
function modules_adminapi_setstate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($mid)||!is_numeric($mid)) ||
        (!isset($state))) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Modules::', '::', ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _MODULESAPINOAUTH);
        return false;
    }

    // Set state
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $sql = "SELECT $modulescolumn[name],
                   $modulescolumn[directory],
                   $modulescolumn[state]
            FROM $modulestable
            WHERE $modulescolumn[id] = " .(int)pnVarPrepForStore($mid);
    $result = $dbconn->Execute($sql);

    if ($result->EOF) {
        echo "No such module id $mid";
        return;
    }

    list($name, $directory, $oldstate) = $result->fields;
    $result->Close();

    // Check valid state transition
    switch ($state) {
        case _PNMODULE_STATE_UNINITIALISED:
            pnSessionSetVar('errormsg', _MODULESAPIINVALIDSTATETRANSITION);
            return false;
            break;
        case _PNMODULE_STATE_INACTIVE:
            break;
        case _PNMODULE_STATE_ACTIVE:
            if (($oldstate == _PNMODULE_STATE_UNINITIALISED) ||
                ($oldstate == _PNMODULE_STATE_MISSING) ||
                ($oldstate == _PNMODULE_STATE_UPGRADED)) {
                pnSessionSetVar('errormsg', _MODULESAPIINVALIDSTATETRANSITION);
                return false;
            }
            break;
        case _PNMODULE_STATE_MISSING:
            break;
        case _PNMODULE_STATE_UPGRADED:
            if ($oldstate == _PNMODULE_STATE_UNINITIALISED) {
                pnSessionSetVar('errormsg', _MODULESAPIINVALIDSTATETRANSITION);
                return false;
            }
            break;
    }

    $sql = "UPDATE $modulestable
            SET $modulescolumn[state] = " . pnVarPrepForStore($state) . "
            WHERE $modulescolumn[id] = " .(int)pnVarPrepForStore($mid);
    $result = $dbconn->Execute($sql);

    return true;
}

/**
 * remove a module
 * @param $args['mid'] the id of the module
 * @returns bool
 * @return true on success, false on failure
 */
function modules_adminapi_remove($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($mid)||!is_numeric($mid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Modules::', '::', ACCESS_ADMIN)) {
        pnSessionSetVar('errormsg', _MODULESAPINOAUTH);
        return false;
    }

    // Remove variables and module
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Get module information
    $modinfo = pnModGetInfo($mid);
    if (empty($modinfo)) {
        pnSessionSetVar('errormsg', _MODNOSUCHMOD);
        return false;
    }                

    // Get module database info
    pnModDBInfoLoad($modinfo['name'], $modinfo['directory']);

    // Module deletion function
    $osdir = pnVarPrepForOS($modinfo['directory']);
    @include("modules/$osdir/pninit.php");
    $func = $modinfo['name'] . '_delete';
    if (function_exists($func)) {
        if ($func() != true) {
            return false;
        }
    }

    // Delete any module variables that the module cleanup function might
    // have missed
    $modulevarstable = $pntable['module_vars'];
    $modulevarscolumn = &$pntable['module_vars_column'];
    $query = "DELETE FROM $modulevarstable
              WHERE $modulevarscolumn[modname] = " . pnVarPrepForStore($modinfo['name']);
    $dbconn->Execute($query);

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "DELETE FROM $modulestable
              WHERE $modulescolumn[id] = " .(int)pnVarPrepForStore($mid);
    $dbconn->Execute($query);

    return true;
}

/**
 * regenerate modules list
 * @returns bool
 * @return true on success, false on failure
 */
function modules_adminapi_regenerate()
{
    // Security check
    if (!pnSecAuthAction(0, 'Modules::', '::', ACCESS_ADMIN)) {
        pnSessionSetVar('errormsg', _MODULESAPINOAUTH);
        return false;
    }

    // Get all modules on filesystem
    $filemodules = array();
    $dh = opendir('modules');
    while ($dir = readdir($dh)) {
		unset($modtype);
        if ((is_dir("modules/$dir")) &&
                ($dir != '.') &&
                ($dir != '..') &&
                ($dir != 'CVS')) {

            // Found a directory

            // Work out name from directory
            $name = preg_replace('/^NS-/', '', $dir);
            $displayname = preg_replace('/_/', ' ', $name);
            // Credit to Joerg Napp (jnapp) for SF Bug [ 562518 ] 
			unset($modtype); 

            // Work out if admin-capable
            if (file_exists("modules/$dir/pnadmin.php")) {
                $adminCapable = _PNYES;
                $modtype = 2;
            } elseif (file_exists("modules/$dir/admin.php")) {
                $adminCapable = _PNYES;
                $modtype = 1;
            } else {
                $adminCapable = _PNNO;
            }

            // Work out if user-capable
            if (file_exists("modules/$dir/pnuser.php")) {
                $userCapable=_PNYES;
                if (!isset($modtype)) {
                    $modtype = 2;
                }
            } elseif (file_exists("modules/$dir/index.php")) {
                $userCapable = _PNYES;
                if (!isset($modtype)) {
                    $modtype = 1;
                }
            } else {
                $userCapable = _PNNO;
            }
            if (empty($modtype)) {
                $modtype = 1;
            }

            // Valid module

            // Work out correct name and get registry ID
            if (!$regid = modules_adminapi_getreginfo(array('name' => $name))) {
                $regid = 0;
            }

            // Get the module version
            $modversion['version'] = '0';
            $modversion['description'] = '';
            @include("modules/$dir/Version.php");
            @include("modules/$dir/pnversion.php");
            $version = $modversion['version'];
            $description = $modversion['description'];

            $filemodules[$name] = array('directory' => $dir,
                                        'name' => $name,
                                        'type' => $modtype,
                                        'displayname' => $displayname,
                                        'regid' => $regid,
                                        'version' => $version,
                                        'description' => $description,
                                        'admincapable' => $adminCapable,
                                        'usercapable' => $userCapable);
        }
    }
    closedir($dh);

    // Get all modules in DB
    $dbmodules = array();

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[id],
                     $modulescolumn[name],
                     $modulescolumn[type],
                     $modulescolumn[displayname],
                     $modulescolumn[regid],
                     $modulescolumn[directory],
                     $modulescolumn[admin_capable],
                     $modulescolumn[user_capable],
                     $modulescolumn[version],
                     $modulescolumn[state]
              FROM $modulestable";
    $result = $dbconn->Execute($query);
    while(list($mid, $name, $modtype, $displayname, $regid, $directory, $adminCapable, $userCapable, $version, $state) = $result->fields) {
        $result->MoveNext();
        $dbmodules[$name] = array('id' => $mid,
                                  'directory' => $directory,
                                  'admincapable' => $adminCapable,
                                  'usercapable' => $userCapable,
                                  'version' => $version,
                                  'state' => $state);
    }
    $result->Close();

    // See if we have lost any modules since last generation
    foreach ($dbmodules as $name => $modinfo) {
        if (empty($filemodules[$name])) {
            // Old module

            // Get module ID
            $modulestable = $pntable['modules'];
            $modulescolumn = &$pntable['modules_column'];
            $query = "SELECT $modulescolumn[id]
                      FROM $modulestable
                      WHERE $modulescolumn[name] = '" . pnVarPrepForStore($name) . "'";
            $result = $dbconn->Execute($query);

            if ($result->EOF) {
                die("Failed to get module ID");
            }

            list($mid) = $result->fields;
            $result->Close();

            // Set state of module to 'missing'
            modules_adminapi_setstate(array('mid'=>$mid,'state'=> _PNMODULE_STATE_MISSING));
            unset($dbmodules[$name]);
        }
    }

    // See if we have gained any modules since last generation,
    // or if any current modules have been upgraded
    foreach ($filemodules as $name => $modinfo) {
        if (empty($dbmodules[$name])) {
            // New module

            $modid = $dbconn->GenId($pntable['modules']);
            $sql = "INSERT INTO $modulestable
                      ($modulescolumn[id],
                       $modulescolumn[name],
                       $modulescolumn[type],
                       $modulescolumn[regid],
                       $modulescolumn[displayname],
                       $modulescolumn[directory],
                       $modulescolumn[admin_capable],
                       $modulescolumn[user_capable],
                       $modulescolumn[state],
                       $modulescolumn[version],
                       $modulescolumn[description])
                    VALUES
                      (" . pnVarPrepForStore($modid) . ",
                       '" . pnVarPrepForStore($modinfo['name']) . "',
                       '" . pnVarPrepForStore($modinfo['type']) . "',
                       " . pnVarPrepForStore($modinfo['regid']) . ",
                       '" . pnVarPrepForStore($modinfo['displayname']) . "',
                       '" . pnVarPrepForStore($modinfo['directory']) . "',
                       " . pnVarPrepForStore($modinfo['admincapable']) . ",
                       " . pnVarPrepForStore($modinfo['usercapable']) . ",
                       " . _PNMODULE_STATE_UNINITIALISED . ",
                       '" . pnVarPrepForStore($modinfo['version']) . "',
                       '" . pnVarPrepForStore($modinfo['description']) . "')";
            $dbconn->Execute($sql);
        } else {
            if ($dbmodules[$name]['version'] != $modinfo['version']) {
                if ($dbmodules[$name]['state'] != _PNMODULE_STATE_UNINITIALISED) {
                    //	20021127 : Roger Raymond
					//		Removed code that set new module version
					$sql = "UPDATE $modulestable
                            SET $modulescolumn[state] = " . _PNMODULE_STATE_UPGRADED . "
                            WHERE $modulescolumn[id] = " . pnVarPrepForStore($dbmodules[$name]['id']);
                    $dbconn->Execute($sql);
                }
            }
        }
    }

    return true;
}

function modules_adminapi_getreginfo($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($name)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    static $modreg;

    if (empty($modreg[1])) {
        include 'modules/Modules/modreg.php';
    }

    if (isset($modreg[$name])) {
        return $modreg[$name];
    } else {
        return false;
    }
}

/**
 * initialise a module
 */
function modules_adminapi_initialise($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($mid)||!is_numeric($mid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Get module information
    $modinfo = pnModGetInfo($mid);
    if (empty($modinfo)) {
        pnSessionSetVar('errormsg', _MODNOSUCHMOD);
        return false;
    }                

    // Get module database info
    pnModDBInfoLoad($modinfo['name'], $modinfo['directory']);

    // Module initialisation function
    $osdir = pnVarPrepForOS($modinfo['directory']);
    @include("modules/$osdir/pninit.php");
    @include("modules/$osdir/pnlang/" . pnVarPrepForOS(pnUserGetLang()) . "/init.php");
    $func = $modinfo['name'] . '_init';
    if (function_exists($func)) {
        if ($func() != true) {
            return false;
        }
    }

    // Update state of module
    if (!modules_adminapi_setstate(array('mid' => $mid,
                                         'state' => _PNMODULE_STATE_INACTIVE))) {
        pnSessionSetVar('errormsg', _MODCHANGESTATEFAILED);
        return false;
    }

    // Success
    return true;
}

/**
 * upgrade a module
 */
function modules_adminapi_upgrade($args)
{
    // 20021216 fixed the fix : larsneo (thx to cmgrote and jojodee)
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($mid)||!is_numeric($mid)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Get module information
    $modinfo = pnModGetInfo($mid);
    if (empty($modinfo)) {
        pnSessionSetVar('errormsg', _MODNOSUCHMOD);
        return false;
    }                

    // Get module database info
    pnModDBInfoLoad($modinfo['name'], $modinfo['directory']);
		
    // Module upgrade function
	$osdir = pnVarPrepForOS($modinfo['directory']);
    @include("modules/$osdir/pninit.php");
    $func = $modinfo['name'] . '_upgrade';
    if (function_exists($func)) {
        if ($func($modinfo['version']) != true) {
            return false;
        } 
    }
    
    // Update state of module
	if (!modules_adminapi_setstate(array('mid' => $mid,																		
										'state' => _PNMODULE_STATE_INACTIVE))) {			
		return false;
	}
	// BEGIN bugfix (561802) - cmgrote
	// Get the new version information...
	$modversion['version'] = '0';
	@include("modules/$modinfo[directory]/Version.php");
	@include("modules/$modinfo[directory]/pnversion.php");
	$version = $modversion['version'];
	
	// Note the changes in the database...
	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();
	
	$modulestable = $pntable['modules'];
	$modulescolumn = &$pntable['modules_column'];
	$sql = "UPDATE $modulestable
			SET $modulescolumn[version] = '" . pnVarPrepForStore($modversion['version']) . "',
				$modulescolumn[admin_capable] = '" . pnVarPrepForStore($modversion['admin']) . "',
				$modulescolumn[description] = '" . pnVarPrepForStore($modversion['description']) . "'
			WHERE $modulescolumn[id] = " . pnVarPrepForStore($mid);
	$dbconn->Execute($sql);
	// END bugfix (561802) - cmgrote
	
	// Message
	pnSessionSetVar('errormsg', _MODULESAPIUPGRADED);
	
	// Success 
	return true;
}
?>