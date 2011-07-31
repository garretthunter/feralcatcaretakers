<?php
// File: $Id: menu.php,v 1.7 2002/11/30 05:40:23 class007 Exp $ $Name:  $
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

class menu_object
{
      var $title_file;
      var $title_text;
      var $help_file;
      var $help_text;
      var $detail_menu  = true;
      var $graphic_menu = true;
      var $nb_column    = 6;
      var $options      = array();

      function menu_object($url,$text)
      {
         $this->title_file = $url;
         $this->title_text = $text;
      }

      function set_help()
      {
      	 global $hlpfile;
		 
      	 $currentlang = pnUserGetLang();//fix bug: Admin Manual does not display by class007

         if (file_exists($file="modules/".$GLOBALS['module']."/lang/".pnVarPrepForOS($currentlang)."/manual.html")) {
            $hlpfile = $file;

         }
         $this->help_file = $file;
         $this->help_text = _ONLINEMANUAL;
      }

      function set_detail($top)
      {
         $this->detail_menu = $top;
      }

      function set_graphic($top)
      {
         $this->graphic_menu = $top;
      }

      function add_option($url,$text,$image='')
      {
	global $ModName;
      	$ModName = 'NS-Admin';
        modules_get_language('menu');  // cant give $ModName to function

        $current = count($this->options);
        $this->options[$current]['url'] = $url;
        if (defined($text) && (phpversion() > "4.0.4")) {
            $text = constant($text);
        }
        $this->options[$current]['text'] = $text;
        $this->options[$current]['image'] = $image;
      }

      function draw_option_cell($url,$text,$image='')
      {
         echo  '<td align="center">';
         if ($url!='') echo '<a href="'.$url.'">';
         if ($image=='')
            echo $text;
         else
            echo '<img src="'.$image.'" border="0" alt="'.pnVarPrepForDisplay($text).'">';
         if ($url!='') echo '</a>';
         echo  '<br><br></td>'."\n";
      }

      function draw_options_graphic()
      {
         $nb_total = count($this->options);
         $i = 0;
         while ($i<$nb_total)
         {
            $j = 0;
            $t = array();
            echo '<tr>'."\n";
            while (($j<$this->nb_column)and($i<$nb_total))
            {
               $this->draw_option_cell($this->options[$i]['url'],$this->options[$i]['text'],$this->options[$i]['image']);
               $t[] = $this->options[$i];
               $i++;
               $j++;
            }
            echo  '</tr>'."\n"
                 .'<tr>'."\n";
            for ($k=0;$k<count($t);$k++)
            {
               $this->draw_option_cell($t[$k]['url'],$t[$k]['text']);
            }
            echo  '</tr>'."\n";
         }
      }

      function draw_options()
      {
         $nb_total = count($this->options);
         $i = 0;
         while ($i<$nb_total)
         {
            echo '<tr>'."\n";
            $j = 0;
            while (($j<$this->nb_column)and($i<$nb_total))
            {
               $this->draw_option_cell($this->options[$i]['url'],$this->options[$i]['text']);
               $i++;
               $j++;
            }
            echo  '</tr>'."\n";
         }
      }

      function draw_menu()
      {
         global $hlpfile;
      	 $currentlang = pnVarCleanFromInput('currentlang');

         OpenTable();
         echo  '<center>'."\n";
         if (count($this->options) == 0) $this->title_file = '';
         if ($this->title_file!='') echo '<a href="'.$this->title_file.'" class="pn-title">';
         echo '<font class="pn-title"><b>'.pnVarPrepForDisplay($this->title_text).'</b></font>';
         if ($this->title_file!='') echo '</a>';
         echo  "\n"
              .'<br>'."\n";
     //    if (($this->detail_menu) or ($GLOBALS['module']=='oldway'))
      //   {
           // if (isset($this->help_file))
           // {
               if (file_exists($file="modules/".$GLOBALS['module']."/lang/".pnVarPrepForOS($currentlang)."/manual.html")) {
                  $hlpfile = $file;
               }
               echo  '[ <a href="javascript:openwindow('.')" class="pn-normal">'._ONLINEMANUAL.'</a> ]'."\n";
          //  }
     //    }
         if ($this->detail_menu)
         {
            if (count($this->options) == 0)
            {
               echo _ADMIN_NO_OPTION."\n";
            }
            else
            {
               echo  '<br><br>'."\n"
                    .'<table border="0" width="100%" cellspacing="1">'."\n";
               if ($this->graphic_menu)
                  $this->draw_options_graphic();
               else
                  $this->draw_options();
               echo  '</table>'."\n";
            }
         }
         CloseTable();
      }

}
//end of class menu_object

/// Main code ///

   function menu_action($action, $parm1='', $parm2='', $parm3='')
   {
      static $menu    = array(),
             $current = -1;

      if ($action=='title')
      {
         if ($current>=0) menu_detail(false);
         $current++;
         $menu[$current] = new menu_object($parm1,$parm2);
      }
      elseif ($action=='help')
         $menu[$current]->set_help();
      elseif ($action=='graphic')
         $menu[$current]->set_graphic($parm1);
      elseif ($action=='detail')
         $menu[$current]->set_detail($parm1);
      elseif ($action=='option')
         $menu[$current]->add_option($parm1,$parm2,$parm3);
      elseif ($action=='draw')
      {
         foreach ($menu as $i=>$m)
            $m->draw_menu();
         $menu = array();
         $current = -1;
      }
      else
         die('fatal / admin/tools/menu.php');
   }

   function menu_title($file,$text)              {menu_action('title', $file, $text);}
   function menu_help()                          {menu_action('help');}
   function menu_graphic($top)                   {menu_action('graphic', $top);}
   function menu_add_option($url,$text,$image)   {menu_action('option', $url, $text, $image);}
   function menu_detail($top)                    {menu_action('detail', $top);}
   function menu_draw()                          {menu_action('draw');}

?>