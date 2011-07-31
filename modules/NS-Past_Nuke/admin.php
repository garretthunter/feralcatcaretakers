<?php
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
// Original Author of file: Pascal Riva
// Purpose of file: old admin module compatibility
// ----------------------------------------------------------------------

// for compatibility : use menu_add_option($url,$title,$image)
   function adminmenu($url,$title,$image)
   {
      if (!ereg('/',$image)) $image = pnConfigGetVar('adminimg').$image;
      menu_add_option($url,$title,$image);
   }

   function past_nuke_menu($help_file='')
   {
      menu_detail(false);
      menu_title('admin.php?module=NS-Past_Nuke&op=main',_PAST_ADMINMENU);
      menu_graphic(pnConfigGetVar('admingraphic'));
      if ($help_file!='') menu_help($help_file,_ONLINEMANUAL);
      $moddir = opendir('modules/');
      while ($modulename=readdir($moddir))
      {
         if (@is_dir($dir='modules/'.$modulename.'/admin/links/'))
         {
            $linksdir = opendir("modules/$modulename/admin/links/");
            while ($func = readdir($linksdir))
            {
               if (eregi('^links.',$func))
               {
                  $menulist[$func] = "modules/$modulename/admin/links";
               }
            }
            closedir($linksdir);
         }
      }
      closedir($moddir);
      if(isset($menulist)){
                  ksort($menulist);
                  foreach ($menulist as $k=>$v)
                  {
                         include "$v/$k";
                  }
      }
   }

   function past_nuke_admin_main($var)
   {
      $op = pnVarCleanFromInput('op');
      past_nuke_menu();
      if ($op=='main')
      {
         include('header.php');
         menu_draw();
         include('footer.php');
      }
      else
      {
         global $PHP_SELF, $ModName;
         extract($var);
         $caselist = array();
         $moddir = opendir('modules/');
         while ($modulename = readdir($moddir))
         {
            if (@is_dir("modules/$modulename/admin/case/"))
            {
               $casedir = opendir("modules/$modulename/admin/case/");
               while ($func = readdir($casedir))
               {
                  if (eregi('^case.', $func))
                  {
                     $caselist[$func]['path'] = "modules/$modulename/admin/case";
                     $caselist[$func]['module'] = $modulename;
                  }
               }
               closedir($casedir);
            }
         }
         closedir($moddir);
         ksort($caselist);
         foreach ($caselist as $k=>$v)
         {
            $ModName = $v['module'];
            include "$v[path]/$k";
         }
      }
   }
?>