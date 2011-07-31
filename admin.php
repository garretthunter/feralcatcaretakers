<?php
// File: $Id: admin.php,v 1.4 2003/01/04 08:42:58 larsneo Exp $ $Name:  $
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
// Original Author of file: Francisco Burzi
// Purpose of file:
// ----------------------------------------------------------------------

   include 'includes/pnAPI.php';
   pnInit();
   include 'includes/legacy.php';

/*	Allows Postnuke to work with register_globals set to off 
 *	Patch for php 4.2.x or greater
 */

 if ( function_exists('ini_get') ) { 
      $onoff = ini_get('register_globals'); 
 } else { 
      $onoff = get_cfg_var('register_globals'); 
 } 
 if ($onoff != 1) { 
      @extract($HTTP_SERVER_VARS, EXTR_SKIP); 
      @extract($HTTP_COOKIE_VARS, EXTR_SKIP); 
      @extract($HTTP_POST_FILES, EXTR_SKIP); 
      @extract($HTTP_POST_VARS, EXTR_SKIP); 
      @extract($HTTP_GET_VARS, EXTR_SKIP); 
      @extract($HTTP_ENV_VARS, EXTR_SKIP); 
      @extract($HTTP_SESSION_VARS, EXTR_SKIP); 
 }


// eugenio themeover 20020413
//   pnThemeLoad();
   include 'modules/NS-Admin/tools.php';

    $currentlangfile = 'language/' . pnVarPrepForOS(pnUserGetLang()) . '/admin.php';
    $defaultlangfile = 'language/' . pnVarPrepForOS(pnConfigGetVar('language')) . '/admin.php';
    if (file_exists($currentlangfile)) {
        include $currentlangfile;
    } elseif (file_exists($defaultlangfile)) {
        include $defaultlangfile;
    }

// $module / $op control
   if (!isset($op) or $op=='adminMain')
   {
      $module = 'NS-Admin';
      $op     = 'main';
   }
   elseif (!isset($module))
      $module = 'NS-Past_Nuke';

/*
Seems to be unused :
$basedir = dirname($SCRIPT_FILENAME);
$textrows = 20;
$textcols = 85;
$udir = dirname($PHP_SELF);
if (empty($wdir)) $wdir='/';
*/

// to be put somewhere else !!! review ?
   if (($module=='Past_Nuke') and ($op=='deleteNotice'))
   {
      deleteNotice($id, $table, $op_back);
      exit;
   }
// to be put somewhere else !!! end

// prepare the menu
   admin_menu('admin.html');

   if (file_exists($file='modules/' . pnVarPrepForOS($module) . '/admin.php'))
   {
      $ModName = $module;
      include $file;

      modules_get_language();
      modules_get_manual();

      if (substr($module,0,3)=='NS-') {
         $function = substr($module,3).'_admin_';
      }
      else {
         $function = $module.'_admin_';
      }

      $function_op = $function.$op;
      $function_main = $function.'main';
      $var = array_merge($GLOBALS['HTTP_GET_VARS'],$GLOBALS['HTTP_POST_VARS']);
      if (function_exists($function_op)) {
         $function_op($var);
      }
      elseif (function_exists($function_main)) {
         $function_main($var);
      }
      else {
         die("error : admin_execute($file,$function_op)");
      }
   }
   else
      die("Fatal error :<br>module = $module<br>op = $op<br>");

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
?>