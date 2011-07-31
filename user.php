<?php // File: $Id: user.php,v 1.4 2003/01/07 12:19:38 tanis Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
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
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include 'includes/pnAPI.php';
pnInit();

include 'includes/legacy.php';
// eugenio themeover 20020413
// pnThemeLoad();
include 'modules/NS-User/tools.php';
include 'modules/NS-User/password.php';



$currentlangfile = 'language/' . pnVarPrepForOS(pnUserGetLang()) . '/user.php';
$defaultlangfile = 'language/' . pnVarPrepForOS(pnConfigGetVar('language')) . '/user.php';

if (file_exists($currentlangfile)) {
    include $currentlangfile;
} elseif (file_exists($defaultlangfile)) {
    include $defaultlangfile;
}

global $stop, $minage, $module;

if (!pnUserLoggedIn() && empty($op)) {
    $module='NS-User';
    $op='getlogin';
}


if (isset($op) && ($op == 'userinfo')) {
    $module='NS-User';
}

// New module way
// $module / $op control
if (pnUserLoggedIn() and (!isset($op) or ($op == 'adminMain'))) {
    $module = 'NS-User';
    $op     = 'main';
}

if (file_exists($file='modules/' . pnVarPrepForOS($module) . '/user.php'))
{
    user_menu();
    include $file;
    if (substr($module,0,3)=='NS-') {
        $function = substr($module,3).'_user_';
    } else {
        $function = $module.'_user_';
    }
    $function_op = $function.$op;
    $function_main = $function.'main';
//gehSTART - stop PHP warning
//    $var = array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS']);
    $var = @array_merge($GLOBALS['HTTP_GET_VARS'], $GLOBALS['HTTP_POST_VARS']);
//gehEND	

    if (function_exists($function_op)) {
        $function_op($var);
    } elseif (function_exists($function_main)) {
        $function_main($var);
    } else {
        die("error : user_execute($file,$function_op)");
    }

}

$caselist = array();
$moddir = opendir('modules/');
while ($modulename = readdir($moddir)) {
    if (@is_dir("modules/" . pnVarPrepForOS($modulename) . "/user/case/")) {
        $casedir = opendir("modules/" . pnVarPrepForOS($modulename) . "/user/case/");
        while ($func = readdir($casedir)) {
            if (eregi('^case.', $func)) {
                $caselist[$func]['path'] = "modules/" . pnVarPrepForOS($modulename) . "/user/case";
                $caselist[$func]['module'] = $modulename;
            }
        }
        closedir($casedir);
    }
}
closedir($moddir);
ksort($caselist);
foreach ($caselist as $k=>$v) {
    $ModName = $v['module'];
    include $v['path'] . '/' . pnVarPrepForOS($k);
}
?>