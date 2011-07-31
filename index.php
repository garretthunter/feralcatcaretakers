<?php
// File: $Id: index.php,v 1.8 2003/01/07 12:19:38 tanis Exp $ $Name:  $
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
// Original Author of this file: Francisco Burzi
// Purpose of this file: Directs to the start page as defined in config.php
// ----------------------------------------------------------------------
include 'includes/pnAPI.php';
pnInit();
// Get variables
list($module,
     $func,
     $op,
     $name,
     $file,
     $type,) = pnVarCleanFromInput('module',
                                  'func',
                                  'op',
                                  'name',
                                  'file',
                                  'type');

// Defaults for variables

if (isset($catid)) {
	pnVarCleanFromInput('catid');
}

if (empty($op)) {
    $op = "modload";
}
if (empty($name)) {
    $name= pnConfigGetVar('startpage');
}
if (empty($type)) {
    $type = 'user';
}
if (empty($file)) {
    $file="index";
}

if (!empty($module)) {
    // New-new style of loading modules

    // Load the theme
// eugenio themeover 20020413
//    pnThemeLoad();

    // Load the module
    $loadedmod = pnModLoad($module, $type);

    if (empty($loadedmod)) {
        // Failed to load the module
        $output = new pnHTML();
        $output->StartPage();

        $output->Text('Failed to load module ' . $module);

        $output->EndPage();
        $output->PrintPage();
        exit;
    }

    // Run the function
    $return = pnModFunc($loadedmod, $type, $func);

    if (function_exists('session_write_close')) {
        session_write_close();
    }

    // Sort out return of function.  Can be
    // true - finished
    // false - need to redirect to module's
    //         main page
    // text - return information

    if ((empty($return)) || ($return == false)) {
        // Redirect
        pnRedirect(pnModURL($module, $type));
    } elseif (strlen($return) > 1) {
        // Text
        $output = new pnHTML();
        $output->StartPage();

        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text($return);
        $output->SetInputMode(_PNH_PARSEINPUT);

        $output->EndPage();
        $output->PrintPage();
    }
    exit;

} else {
    // Old stuff

    // Old-old style of loading modules
    include 'includes/legacy.php';
    switch ($op) {
        case 'modload':
            define("LOADED_AS_MODULE","1");
// eugenio themeover 20020413
//            pnThemeLoad();
            include 'modules/' . pnVarPrepForOS($name) . '/'  . pnVarPrepForOS($file) . '.php';
            break;
        default:
            die ("Sorry, you can't access this file directly...");
            break;
    }
    if (function_exists('session_write_close')) {
        session_write_close();
    } else {
        // Hack for old versions of PHP with bad session save
        $sessvars = '';
        foreach ($GLOBALS as $k => $v) {
            if ((preg_match('/^PNSV/', $k)) &&
                (isset($v))) {
                $sessvars .= "$k|" . serialize($v);
            }
        }
        pnSessionWrite(session_id(), $sessvars);
    }
}

?>