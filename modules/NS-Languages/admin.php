<?php
// $Id: admin.php,v 1.4 2002/11/12 13:04:29 magicx Exp $
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
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: PN boys and girls band
// Purpose of file: language file management
// ----------------------------------------------------------------------

   modules_get_language();
   modules_get_manual();

   function languages_option($module,$op,$language,$text,$img)
   {
      menu_add_option('admin.php?module='.$module.'&op='.$op.'&language='.$language,$text,$img);
   }

   function languages_menu($module,$language)
   {
      menu_title('admin.php?module='.$module.'&op=main&language='.$language,_LANG_CURENT.' : '.language_name($language));
      menu_graphic(false);
      menu_help();
      languages_option($module,'getConfig',$language,_LANGCONF,'');
      languages_option($module,'select',$language,_LANG_SELECT,'');
      languages_option($module,'constant',$language,_LANG_LOAD_CONSTANT,'');
      languages_option($module,'translation',$language,_LANG_LOAD_TRANSLATION,'');
      languages_option($module,'missing',$language,_LANG_ADD_MISSING,'');
      languages_option($module,'generate',$language,_LANG_GENERATE,'');
//    languages_option($module,'hardcode',$language,_LANG_HARDCODE,'');
   }

   function languages_file($file)
   {

      $part = explode('/',substr($file,2));
      if (!isset($part[3])) $part[3] = '';
      if ($part[0]=='modules')
      {
         if ($part[2]=='api.php')
            return 'GLOBAL';
         if (($part[2]=='data.php') or ($part[2].'/'.$part[3]=='admin/links') or $part[1].'/'.$part[2]=='NS-Admin/tools.php')
            return 'ADMIN';
         if ($part[1].'/'.$part[2]=='NS-User/tools.php')
            return 'USER';
         if ($part[2]=='pnblocks')
            return 'NONCOREBLOCK-'.$part[1].'-'.ereg_replace('\.php$','',$part[2]);
         if (($part[2]=='pnadmin.php') or ($part[2]=='pnadminapi.php') or ($part[2]=='pnuser.php') or ($part[2]=='pnuserapi.php') or ($part[2]=='pninit.php'))
            return 'NEWMODULE-'.$part[1].'-'.ereg_replace('\.php$','',$part[2]);
         if (substr($part[1],0,3)=='NS-')
            $part[1][2] = '_';
//            $part[1] = substr($part[1],3);
         return 'MODULE-'.$part[1];
      }
      elseif (($part[0]=='includes') and ($part[1]=='blocks'))
         return 'COREBLOCK-'.ereg_replace('\.php$','',$part[2]);
      elseif ($part[0]=='install')
      {
         return 'INSTALL';
      }
      elseif ($part[0]=='admin.php')
      {
         return 'ADMIN';
      }
      elseif ($part[0]=='themes')
      {
            return 'THEME-'.$part[1];
      }
      elseif ($part[0]=='user.php')
      {
         return 'USER';
      }
      elseif ($part[0]=='banners.php')
      {
         return 'BANNERS';
      }
      elseif ($part[0]=='error.php')
      {
         return 'ERROR';
      }
      else
      {
         return 'GLOBAL';
      }
   }

   function languages_choice_file($file1,$file2)
   {
      if ($file1=='GLOBAL' or $file2=='GLOBAL')
         return 'GLOBAL';
      elseif ($file1==$file2)
         return $file1;
      else
         return 'GLOBAL';
   }

   function languages_extract_constant($file,$line)
   {
      $pntable = pnDBGetTables();

      static $file_list = array();
      $count = preg_match_all('/\b(_[0-9A-Z]+)+\b/',$line,$matches);
      $file = languages_file($file);
//      echo "$file<br>";
      for ($i=0;$i<$count;$i++)
      {
         $constant = FixQuotes($matches[0][$i]);
         mysql_query("insert into ".$pntable['languages_constant']." (pn_constant,pn_file) values('" . pnVarPrepForStore($constant) . "','" . pnVarPrepForStore($file) . "')");
         if (mysql_errno()==1062)
         {
            list($old_file) = db_select_one_row("SELECT pn_file FROM ".$pntable['languages_constant']." where pn_constant='" . pnVarPrepForStore($constant) . "'");
            $file2 = languages_choice_file($file,$old_file);
//            echo "$file , $old_file => $file2 ($constant)<br>";
                if ($file2!=$old_file)
               mysql_query("update ".$pntable['languages_constant']." set pn_file='" . pnVarPrepForStore($file2) . "' where pn_constant='" . pnVarPrepForStore($constant) . "'");
         }
         elseif (mysql_errno()!=0)
           echo '('.mysql_errno().') '.mysql_error().'<br>';
         else
            $file2 = $file;
         if (!isset($file_list[$file2]))
         {
            $file_list[$file2] = '';
            $part = explode('-',$file2);
            if ($part[0]=='GLOBAL')
               $target = 'language/$language/global.php';
            elseif ($part[0]=='MODULE') {
               if (substr($part[1],0,3)=='NS_')
                   $part[1][2] = '-';
               $target = 'modules/'.$part[1].'/lang/$language/global.php';
            }
            elseif ($part[0]=='NEWMODULE') {
               if (substr($part[2],0,2)=='pn')
                   $part[2] = substr($part[2],2);
               $target = 'modules/'.$part[1].'/pnlang/$language/'.$part[2].'.php';
            }
            elseif (($part[0]=='THEME'))
               $target = 'themes/'.$part[1].'/lang/$language/global.php';
            elseif (($part[0]=='COREBLOCK'))
               $target = 'includes/language/blocks/$language/'.$part[1].'.php';
            elseif (($part[0]=='NONCOREBLOCK'))
               $target = 'modules/'.$part[1].'/pnlang/$language/'.$part[2].'.php';
            elseif (($part[0]=='ADMIN'))
               $target = 'language/$language/admin.php';
            elseif (($part[0]=='USER'))
               $target = 'language/$language/user.php';
            elseif (($part[0]=='BANNERS'))
               $target = 'language/$language/banners.php';
            elseif (($part[0]=='ERROR'))
               $target = 'language/$language/error.php';
            elseif (($part[0]=='INSTALL'))
               $target = 'install/lang/$language/global.php';
            else
               die('target unknown : '.$file2.'/'.$file);
            mysql_query("insert into ".$pntable['languages_file']." (pn_target,pn_source) values('" . pnVarPrepForStore($target) . "','" . pnVarPrepForStore($file2) . "')");
            if ((mysql_errno()!=0) and (mysql_errno()!=1062))
               echo '('.mysql_errno().') '.mysql_error().'<br>';
         }
      }

      mysql_query("delete from ".$pntable['languages_constant']." where pn_constant like '_ADODB%' or pn_constant like '_PNMODULE_STATE%' or pn_constant like '_PN_VERSION%' or pn_constant like '_UDCONST%' or pn_constant  like '_TYPOCODE%' or pn_constant = '_TW' or pn_constant ='_TB' or pn_constant='_TP' or pn_constant ='_FP' or pn_constant = '_FB' or pn_constant = '_FW' or pn_constant = '_PEAR' or pn_constant = '_SYLLABELS' or pn_constant = '_MAKEPASS_BOX' or pn_constant = '_MAKEPASS_LEN' or pn_constant = '_PN_CONFIG_MODULE' or pn_constant = '_PNH_KEEPOUTPUT' or pn_constant = '_PNH_PARSEINPUT' or pn_constant = '_PNH_RETURNOUTPUT' or pn_constant = '_PNH_VERBATIMINPUT' or pn_constant = '_PNNO' or pn_constant = '_PNPERMS_ALL' or pn_constant = '_PNPERMS_UNREGISTERED' or pn_constant = '_PNYES' or pn_constant = '_PNYES' or pn_constant = '_T' or pn_constant = '_BLANK' or pn_constant = '_DEFAULT' or pn_constant = '_POSTNUKE' or pn_constant = '_WIKI' or pn_constant = '_BBCODE'");

   }

   function languages_dir_walk($dir)
   {
      if (!$handle=@opendir($dir)) return;
      while ($file=readdir($handle))
      {
         if ($file=='.');
         elseif ($file=='..');
         elseif (ereg('\.php$',$file))
            languages_extract_constant($dir.'/'.$file,implode('',file($dir.'/'.$file)));
         elseif ($file=='lang');
         elseif ($file=='pnlang');
         elseif ($file=='language');
         elseif (!ereg('\.',$file))
            languages_dir_walk($dir.'/'.$file);
      }
      closedir($handle);
   }

function languages_admin_getConfig() {
       include ("header.php");

    // prepare vars
        $sel_lang[pnConfigGetVar('language')] = ' selected';
    $sel_multilingual['0'] = '';
    $sel_multilingual['1'] = '';
    $sel_multilingual[pnConfigGetVar('multilingual')] = ' checked';
    $sel_useflags['0'] = '';
    $sel_useflags['1'] = '';
    $sel_useflags[pnConfigGetVar('useflags')] = ' checked';

    GraphicAdmin();
    OpenTable();
    print '<center><font size="3" class="pn-title">'._LANGCONF.'</b></font></center><br />'
          .'<form action="admin.php" method="post">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._SELLANGUAGE.':</td><td class="pn-normal">'
        .'<select name="xlanguage" class="pn-normal">'
    ;
    $lang = languagelist();
    $handle = opendir('language');
    while ($f = readdir($handle))
    {
        if (is_dir("language/$f") && (!empty($lang[$f])))
        {
            $langlist[$f] = $lang[$f];
        }
    }
    asort($langlist);
        //  a bit ugly, but it works in E_ALL conditions (Andy Varganov)
        foreach ($langlist as $k=>$v)
        {
        echo '<option value="'.$k.'"';
        if (isset($sel_lang[$k])) echo ' selected';
        echo '>'. $v . "</option>\n";
        }
        echo '</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._ACTMULTILINGUAL.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xmultilingual\" value=\"1\" class=\"pn-normal\" ".$sel_multilingual['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xmultilingual\" value=\"0\" class=\"pn-normal\" ".$sel_multilingual['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._ACTUSEFLAGS.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xuseflags\" value=\"1\" class=\"pn-normal\"".$sel_useflags['1'].">"._YES." &nbsp;"
        ."<input type=\"radio\" name=\"xuseflags\" value=\"0\" class=\"pn-normal\"".$sel_useflags['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ."<input type=\"hidden\" name=\"module\" value=\"".$GLOBALS['module']."\">"
        ."<input type=\"hidden\" name=\"op\" value=\"setConfig\">"
        ."<input type=\"hidden\" name=\"authid\" value=\"" . pnSecGenAuthKey() . "\">"
        ."<input type=\"submit\" value=\""._SUBMIT."\">"
        ."</form>"
        .'</td></tr></table>'
    ;
    CloseTable();
    include ("footer.php");
}

function languages_admin_setConfig($var) {

    // Escape some characters in these variables.
    // hehe, I like doing this, much cleaner :-)
    $fixvars = array (
        );

    // todo: make FixConfigQuotes global / replace with other function
    foreach ($fixvars as $v) {
   //   $var[$v] = FixConfigQuotes($var[$v]);
    }

    // Set any numerical variables that havn't been set, to 0. i.e. paranoia check :-)
    $fixvars = array (
    );
    foreach ($fixvars as $v) {
        if (empty($var[$v])) {
            $var[$v] = 0;
        }
    }

    // all variables starting with x are the config vars.
    while (list ($key, $val) = each ($var)) {
        if (substr($key, 0, 1) == 'x') {
            pnConfigSetVar(substr($key, 1), $val);
        }
    }
    pnRedirect('admin.php');
}

   function languages_admin_main($var)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      if (!isset($var['language'])) $var['language'] = 'eng';
      languages_menu($var['module'],$var['language']);
      $language = $var['language'];
      include 'header.php';
      menu_draw();
      $result = $dbconn->Execute("SELECT count(1) FROM ".$pntable['languages_constant']);
      list($num) = $result->fields;
      opentable();
      echo _LANG_NUM_CONSTANT.' : '.$num."\n";
      echo '<br>'."\n";
      closetable();
      echo '<br>'."\n";
      opentable();
      $result = $dbconn->Execute("SELECT pn_language,
                                         COUNT(B.pn_translation),
                                         SUM(pn_level)
                                  FROM $pntable[languages_constant] A
                                  LEFT JOIN $pntable[languages_translation] B ON A.pn_constant=B.pn_constant
                                  WHERE pn_language IS NOT NULL
                                  GROUP BY pn_language");
      if ($num==0)
         $link = '[ <a href="admin.php?module='.$var['module'].'&op=constant&language='.$language.'">'._LANG_LOAD_CONSTANT.'</a> ]';
      else
         $link = '[ <a href="admin.php?module='.$var['module'].'&op=constant&language='.$language.'">'._LANG_RELOAD_CONSTANT.'</a> ]';
      echo  _LANG_NUM_TRANSLATION."\n"
           .'<br><br>'."\n"
           .'<table>'."\n"
           .'<tr><td>'._LANGUAGE.'</td><td>'._LANG_LANGUAGES_NUMBER.'</td><td>'._LANG_MISSING_NUMBER.'</td><td>'._LANG_DEFAULT_TRANSLATION.'</td><td>'._NEXT_OP_TODO.'</td></tr>'."\n"
           .'<tr><td>'._LANG_OBJECTIVE.'</td><td>'.$num.'</td><td>0</td><td>0</td><td>'.$link.'</td></tr>'."\n";
      while (!$result->EOF)
      {
         list($language,$total,$total3) = $result->fields;
         $total2 = $num - $total;
         $total3 = $total - $total3;
         if ($num==0)
            $link = '';
         elseif ($total2==0)
            $link = '[ <a href="admin.php?module='.$var['module'].'&op=generate&language='.$language.'">'._LANG_GENERATE.'</a> ]';
         else
            $link = '[ <a href="admin.php?module='.$var['module'].'&op=missing&language='.$language.'">'._LANG_ADD_MISSING.'</a> ]';
         echo '<tr><td>'.language_name($language).'</td><td>'.$total.'</td><td>'.$total2.'</td><td>'.$total3.'</td><td>'.$link.'</td</tr>'."\n";
         $result->MoveNext();
      }
      echo '</table>'."\n";
      closetable();
      echo '<br>'."\n";
      opentable();
      $result = $dbconn->Execute("SELECT A.pn_source,
                                         A.pn_target,
                                         COUNT(B.pn_constant),
                                         COUNT(C.pn_translation),
                                         SUM(pn_level)
                                  FROM $pntable[languages_file] A,
                                       $pntable[languages_constant] B
                                  LEFT JOIN $pntable[languages_translation] C ON B.pn_constant=C.pn_constant
                                        AND pn_language='" . pnVarPrepForStore($var['language']) . "'
                                  WHERE A.pn_source=B.pn_file
                                  GROUP by B.pn_file");
      echo '<table>'."\n";
      echo  _LANG_FILE_TARGET."\n"
           .'<br><br>'."\n"
           .'<table>'."\n"
           .'<tr><td>'._TARGET.'</td><td>'._LANG_FILE.'</td><td>'._LANG_LANGUAGES_NUMBER.'</td><td>'._LANG_MISSING_NUMBER.'</td><td>'._LANG_DEFAULT_TRANSLATION.'</td></tr>'."\n";
      while (!$result->EOF)
      {
         list($file,$target,$total,$total2,$total3) = $result->fields;
         $total2 = $total - $total2;
         $total3 = $total - $total3;
         echo '<tr><td>'.$file.'</td><td>'.$target.'</td><td>'.$total.'</td><td>'.$total2.'</td><td>'.$total3.'</td></tr>'."\n";
         $result->MoveNext();
      }
      echo '</table>'."\n";
      closetable();
      echo '<br>'."\n";
      include 'footer.php';
   }

   function languages_admin_select($var)
   {
      if (!isset($var['language'])) $var['language'] = 'eng';
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
      echo  '<form action="admin.php" methode="get">'."\n"
           ._LANGUAGE.' : <select name="language">'."\n";
      $handle = opendir('language');
      while ($file=readdir($handle))
      {
         if (!ereg('\.|html|CVS',$file))
         {
            $lang = substr($file,0,strlen($file));
            echo '<option value="'.$lang.'"';
            if ($lang==$var['language']) echo ' selected';
            echo '>'.language_name($lang).'</option>'."\n";
         }
      }
      closedir($handle);
      echo '</select>'."\n"
            .'<input type="hidden" name="module" value="'.$var['module'].'">'."\n"
            .'<input type="hidden" name="op" value="main">'."\n"
            .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'."\n"
            .'<input type="submit" value="'._LANG_SELECT.'">'."\n"
            .'</form>'."\n";
      closetable();
      include 'footer.php';
   }

   function languages_admin_constant($var)
   {
      $pntable = pnDBGetTables();
      db_delete("delete from ".$pntable['languages_constant']);
      db_delete("delete from ".$pntable['languages_file']);
      languages_dir_walk('.');
      if (!isset($var['language'])) $var['language'] = 'eng';
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
      echo ""._CONSTANTSLOADING." "._CLICKLINK." <b>"._LANG_LOAD_TRANSLATION."</b> \n";
      closetable();
      include 'footer.php';
   }
   function languages_extract_translation($file,$language,$line)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      $i = strpos($line,'define(');
      if ($i===false) return;
      $line = substr($line,$i+7);
      $i = strrpos($line,')');
      $line = substr($line,0,$i);
      $i = strpos($line,',');
      $constant = trim(substr($line,0,$i));
      $text = trim(substr($line,$i+1));
      $constant = substr($constant,1,strlen($constant)-2);
      $text = substr($text,1,strlen($text)-2);
//      echo "/$constant/$text/<br>";
      $text = fixquotes($text);
      if (($constant==$text) or (substr($text,0,2)=='&&'))
       {
          $text = $constant;
          $sql = "insert into
                 ".$pntable['languages_translation']."
                  (pn_language,pn_constant,pn_translation,pn_level)
                   values('" . pnVarPrepForStore($language) . "','" . pnVarPrepForStore($constant) . "','" . pnVarPrepForStore($text) . "',0)";
       }
       else
       {
          $sql = "insert into
                 ".$pntable['languages_translation']."
                  (pn_language,pn_constant,pn_translation,pn_level)
                  values('" . pnVarPrepForStore($language) . "','" . pnVarPrepForStore($constant) . "','" . pnVarPrepForStore($text) . "',1)";
          $dbconn->Execute($sql);
          if (mysql_errno()!=0 and mysql_errno()!=1062)
            {
              echo '('.mysql_errno().')'.mysql_error().'<br>'.$sql.'<br>///'.$text."///<br>\n";
            }
       }
   }

   function languages_get_file($file,$language)
   {
      echo $file.'<br>'."\n";
      $content = file($file);
      foreach ($content as $num=>$line)
         languages_extract_translation($file,$language,$line);
   }

   function languages_get_dir($dir,$language)
   {
      if (!$handle=@opendir($dir)) return;
      while ($file=readdir($handle))
      {
         if ($file=='.');
         elseif ($file=='..');
         elseif (ereg('\.php$',$file))
            languages_get_file($dir.'/'.$file,$language);
      }
      closedir($handle);
   }

   function languages_languages_walk($dir,$language)
   {
      if (!$handle=@opendir($dir)) return;
      while ($file=readdir($handle))
      {
         if ($file=='.');
         elseif ($file=='..');
         elseif ($file==$language.'.php')
            languages_get_file($dir.'/'.$file,$language);
         elseif ($file==$language)
            languages_get_dir($dir.'/'.$file,$language);
         elseif (!ereg('\.',$file))
            languages_languages_walk($dir.'/'.$file,$language);
      }
      closedir($handle);
   }

   function languages_admin_translation($var)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      if (!isset($var['language'])) $var['language'] = 'eng';
      $sql ="delete from ".$pntable['languages_translation']." where pn_language='".pnVarPrepForStore($var['language'])."'";
            $result=$dbconn->Execute($sql);
                  if ($result===false) die(mysql_error().'<br>'.$sql);
      if (file_exists($file='language/'.$var['language'].'/personnal.php'))
         languages_get_file($file,$var['language']);
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
         echo  ""._TRANSLATIONSLOADING." <b>".$var['language']."</b> "._CLICKLINK." <b>"._LANG_ADD_MISSING."</b><br><br>"."\n";
      echo "<b>"._ADMINTRANSLATION." :</b><br><br>"."\n";
         if (file_exists($file='language/'.$var['language'].'/personnal.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/missing.php'))
         languages_get_file($file,$var['language']);
      languages_languages_walk('includes/language',$var['language']);
      languages_languages_walk('install/lang',$var['language']);
      languages_languages_walk('modules',$var['language']);
      languages_languages_walk('themes',$var['language']);
      if (file_exists($file='language/'.$var['language'].'/global.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/admin.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/user.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/banners.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/error.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/total.php'))
         languages_get_file($file,$var['language']);
      if (file_exists($file='language/'.$var['language'].'/default.php'))
         languages_get_file($file,$var['language']);

      closetable();
      include 'footer.php';
   }

   function languages_admin_missing($var)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      if (!isset($var['language'])) $var['language'] = 'eng';
     $constcolumn = $pntable['languages_constant_column'];
     $transcolumn = $pntable['languages_translation_column'];
    //     $sql = "select A.$constcolumn[constant] from ".$pntable['languages_constant']." A left join ".$pntable['languages_translation']." B on A.$constcolumn[constant] =B.$transcolumn[constant] and B.$transcolumn[language]='".$var['language']."' where B.$transcolumn[constant] IS NULL";
   //  $sql = "select A.pn_constant from ".$pntable['languages_constant']." A left join ".$pntable['languages_translation']." B on A.pn_constant =B.pn_constant and B.pn_language='".$var['language']."' where B.pn_constant IS NULL";
    $sql = "select A.pn_constant, A.pn_file from ".$pntable['languages_constant']." A left join ".$pntable['languages_translation']." B on A.pn_constant =B.pn_constant and B.pn_language='".$var['language']."' where B.pn_constant IS NULL ORDER BY A.pn_file, A.pn_constant";

      $result = $dbconn->Execute($sql);
      if (mysql_errno()!=0)
         die(mysql_error().'<br>'.$sql);
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
      echo  "<br>"._ADDMISSING." "._CLICKLINK." <b>"._LANG_GENERATE."</b><br><br>"."\n";
        $currentfileid = "";
      while (!$result->EOF)
      {
            list($constant, $fileid) = $result->fields;

             if ($fileid != $currentfileid) {
             $sql2 = "select pn_target from ".$pntable['languages_file']." where pn_source = '".$fileid."'";
             $result2 = $dbconn->Execute($sql2);
             list($filename) = $result2->fields;
             if ($currentfileid != "") {
                echo "<br>";
             }
                  $language = $var['language'];
             eval("\$filename = \"$filename\";");
             echo "<b>". $filename . ":</b><br />";
             $currentfileid = $fileid;
         }

         $translation = '';
           if ($var['language']!='eng')
         list($translation) = mysql_fetch_row(mysql_query("select pn_translation from ".$pntable['languages_translation']." where pn_constant = '$constant'"));
         if ($translation=='') $translation = $constant;
         $dbconn->Execute("insert into ".$pntable['languages_translation']." (pn_language,pn_constant,pn_translation,pn_level) values('".pnVarPrepForStore($var['language'])."','" . pnVarPrepForStore($constant) . "','".pnVarPrepForStore($translation)."',0)");
         echo $var['language'].' : '.$constant.' => '.fixquotes($translation)."<br>\n";
         $result->MoveNext();
      }
      closetable();
      include 'footer.php';
   }
//==================================================================================================================================
// Language hardcode traitment
//==================================================================================================================================
   function languages_extract_constant_to_hardcode($file,$line)
   {
  
      $pntable = pnDBGetTables();
      $count = preg_match_all('/\b(_[0-9A-Z]+)+\b/',$line,$matches);
      for ($i=0;$i<$count;$i++)
      {
         $result = mysql_query('select pn_translation from '.$pntable['languages_translation']." where pn_constant='".pnVarPrepForStore($matches[0][$i])."'");
         if (mysql_errno()!=0)
           echo '('.mysql_errno().') '.mysql_error().'<br>';
         if(!list($translation)=mysql_fetch_row($result)) $translation = $matches[0][$i];
 
         $line = preg_replace('/\b'.$matches[0][$i].'\b/','"'.$translation.'"',$line);
         echo "\$line = ereg_replace('/\b'".$matches[0][$i]."'\b/','\"'".$translation."'\"',\$line);<br>";
      }
      $handle = fopen($file,'w');
      if (!$handle) die('Unable  to open '.$file.'<br>'.ereg_replace('<','&lt;',$line));
      fwrite($handle,$line);
   }

   function languages_dir_walk_to_hardcode($dir)
   {
      if (!$handle=@opendir($dir)) return;
      while ($file=readdir($handle))
      {
         if ($file=='.');
         elseif ($file=='..');
         elseif (ereg('\.php$',$file))
            languages_extract_constant_to_hardcode($dir.'/'.$file,implode('',file($dir.'/'.$file)));
         elseif ($file=='lang');
         elseif ($file=='language');
         elseif (!ereg('\.',$file))
            languages_dir_walk_to_hardcode($dir.'/'.$file);
      }
      closedir($handle);
   }

   function languages_admin_hardcode($var)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      if (!isset($var['language'])) $var['language'] = 'eng';
      $language = $var['language'];
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
      languages_dir_walk_to_hardcode('.');
      echo  _LANGUAGE_ALL_CONSTANT_HARDCODE.'<br>'."\n";
      closetable();
      include 'footer.php';
   }

   function languages_admin_generate($var)
   {
      $pntable = pnDBGetTables();
      list($dbconn) = pnDBGetConn();
      if (!isset($var['language'])) $var['language'] = 'eng';
      $language = $var['language'];
      languages_menu($var['module'],$var['language']);
      include 'header.php';
      menu_draw();
      opentable();
     echo  "<br><br>"._GENERATELANG2." <b>".$var['language']."</b> "._GENERATELANG3."".$var['language'].".</b><br><br>"._GENERATELANG4."<br><br>"._ADMINTRANSLATION." :<br><br>"."\n";
      $content = "<?php\n"
                ."// Generated: \$d\$ by \$id\$\n"
                ."// ----------------------------------------------------------------------\n"
                ."// POST-NUKE Content Management System\n"
                ."// Copyright (C) 2001 by the Post-Nuke Development Team.\n"
                ."// http://www.postnuke.com/\n"
                ."// ----------------------------------------------------------------------\n"
                ."// Based on:\n"
                ."// PHP-NUKE Web Portal System - http://phpnuke.org/\n"
                ."// Thatware - http://thatware.org/\n"
                ."// ----------------------------------------------------------------------\n"
                ."// LICENSE\n"
                ."//\n"
                ."// This program is free software; you can redistribute it and/or\n"
                ."// modify it under the terms of the GNU General Public License (GPL)\n"
                ."// as published by the Free Software Foundation; either version 2\n"
                ."// of the License, or (at your option) any later version.\n"
                ."//\n"
                ."// This program is distributed in the hope that it will be useful,\n"
                ."// but WITHOUT ANY WARRANTY; without even the implied warranty of\n"
                ."// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n"
                ."// GNU General Public License for more details.\n"
                ."//\n"
                ."// To read the license please visit http://www.gnu.org/copyleft/gpl.html\n"
                ."// ----------------------------------------------------------------------\n"
                ."// Original Author of file: Everyone\n"
                ."// Purpose of file: Translation files\n"
                ."// Translation team: Read credits in /docs/CREDITS.txt\n"
                ."// ----------------------------------------------------------------------\n";
      $current_file = '';
      $result = $dbconn->Execute("SELECT pn_target,
                                         B.pn_constant,
                                         pn_translation,
                                         pn_level
                                  FROM $pntable[languages_file] A,
                                       $pntable[languages_constant] B,
                                       $pntable[languages_translation] C
                                  WHERE A.pn_source = B.pn_file
                                  AND B.pn_constant = C.pn_constant
                                  AND pn_language='" . pnVarPrepForStore($language) . "'
                                  ORDER BY pn_target,
                                           pn_level,
                                          B.pn_constant");
      if (mysql_errno()!=0)
         die(mysql_error());
      $handle_total = fopen('language/'.$language.'/total.php','w');
      fwrite($handle_total,$content);
      $handle_missing = fopen('language/'.$language.'/missing.php','w');
      fwrite($handle_missing,$content);
      while (!$result->EOF)
      {
         list($file,$constant,$translation,$level) = $result->fields;
         if ($current_file!=$file)
         {
         // echo("$current_file!=$file<br>");
            if ($current_file!='')
            {
               fwrite($handle,"?>");
               fclose($handle);                  ;
            }
            eval("\$filename = \"language_\$language/$file\";");
            $part = explode('/',$filename);
            if ($part[1]=='modules')
               if (!file_exists('modules/'.$part[2]))
                  $part[2] = substr($part[2],3);
            $filename = '';
            for ($i=0;$i<count($part)-1;$i++)
            {
               if(!file_exists($filename.=$part[$i].'/'))
               {
                  mkdir($filename,0777);
                  echo ""._GENERATELANG." : ".$filename."<br>";
               }
            }
            $filename .= $part[count($part)-1];
            echo ""._GENERATELANG1." : ".$filename."<br>";
            $handle = fopen($filename,'w');
            if (!$handle) die(""._DIEOPEN." ".$filename."'<br>");
            $current_file = $file;
            fwrite($handle,$content);
         }
         //$translation = ereg_replace('"','\\"',$translation);
         //$translation = ereg_replace("'","\'",$translation);

         $translation = ereg_replace("'","\'",ereg_replace("''","'",$translation));
         if ($language=='fra')
            $html = array('é'=>'&eacute;','è'=>'&egrave;','à'=>'&agrave;','ê'=>'&ecirc;','û'=>'&circ;','ô'=>'$ocirc;');
        // this for croatian HTML char
        elseif ($language=='cro')
           $html = array('&#262;'=>'Æ','&#263;'=>'æ','&#268;'=>'È','&#269;'=>'è','&#272;'=>'Ð','&#273;'=>'ð','&#352;'=>'Š','&#353;'=>'š','&#381;'=>'Ž','&#382;'=>'ž',);
        //end
        else
            $html = array();
         foreach($html as $k=>$v) $translation = ereg_replace($k,$v,$translation);
         if ($level==0)
         {
            fwrite($handle,"define('$constant','&&$constant (from $filename)');\n");
            fwrite($handle_missing,"define('$constant','$constant'); //$translation ==> ". $filename . "\n");
         }
         else
         {
            fwrite($handle,"define('$constant','$translation');\n");
            fwrite($handle_total,"define('$constant','$translation');\n");
         }
         $result->MoveNext();
      }
      fwrite($handle,"?>");
      fclose($handle);
      fwrite($handle_total,"?>");
      fclose($handle_total);
      fwrite($handle_missing,"?>");
      fclose($handle_missing);
      closetable();
      include 'footer.php';
   }
?>