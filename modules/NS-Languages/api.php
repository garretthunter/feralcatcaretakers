<?php
// $Id: api.php,v 1.6 2002/11/29 19:28:47 nunizgb Exp $ $Name:  $
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
// Original Author of file: PN boys and girls band
// Purpose of file: language API
// ----------------------------------------------------------------------

// Make common language selection dropdown (from Tim Litwiller)
// =======================================
   function lang_dropdown()
   {
      $currentlang = pnUserGetLang();
      echo "<select name=\"alanguage\" class=\"pn-text\">";
      $lang = languagelist();
      print "<option value=\"\">"._ALL.'</option>';
      $handle = opendir('language');
      while ($f = readdir($handle))
      {
         if (is_dir("language/$f") && @$lang[$f])
         {
            $langlist[$f] = $lang[$f];
         }
      }
      asort($langlist);
      foreach ($langlist as $k=>$v)
      {
         echo '<option value="'.$k.'"';
         if ( $currentlang == $k)
         {
            echo ' selected';
         }
         echo '>'. pnVarPrepForDisplay($v) . '</option> ';
      }
      echo "</select>";
   }

// Loads the required language file for module (from Patrick Kellum <webmaster@ctarl-ctarl.com>)
// ===========================================
   function modules_get_language($script = 'global')
   {

      $currentlang = pnSessionGetVar('lang');
      $language = pnConfigGetVar('language');

      if (file_exists("modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($currentlang)."/$script.php")) {
         @include "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($currentlang)."/$script.php";
      }
      elseif (!empty($language)) {
         if (file_exists("modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($language)."/$script.php")) {
            @include "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($language)."/$script.php";
         }
      } else {
         @include "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/eng/$script.php";
      }
      return;
   }

// Loads the required manual for module
// ===========================================
   function modules_get_manual()
   {

      $currentlang = pnSessionGetVar('lang');
      $language = pnConfigGetVar('language');

      if (file_exists("modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($currentlang)."/manual.html")) {
         $hlpfile = "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($currentlang)."/manual.html";
      }
      elseif (!empty($language)) {
         if (file_exists("modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($language)."/manual.html")) {
            $hlpfile = "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/".pnVarPrepForOS($language)."/manual.html";
         }
      } else {
         $hlpfile = "modules/".pnVarPrepForOS($GLOBALS['ModName'])."/lang/eng/manual.html";
      }
      return ;
   }

// Loads the required language file for themes (from Patrick Kellum <webmaster@ctarl-ctarl.com>)
// ===========================================
   function themes_get_language($script = 'global')
   {

      $currentlang = pnSessionGetVar('lang');
      $language = pnConfigGetVar('language');
      $Default_Theme = pnConfigGetVar('Default_Theme');

// modification multisites .71 mouzaia
// added the WHERE_IS_PERSO possibilities.
// is it good ?
// die( $currentlang . '-' . $language .'-'.$Default_Theme);

      if (file_exists($file='themes/' . pnVarPrepForOS($GLOBALS['thename']) . '/lang/' . pnVarPrepForOS($currentlang) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file='themes/' . pnVarPrepForOS($GLOBALS['thename']) . '/lang/' . pnVarPrepForOS($language) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      if (file_exists($file=WHERE_IS_PERSO.'themes/' . pnVarPrepForOS($GLOBALS['thename']) . '/lang/' . pnVarPrepForOS($currentlang) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file=WHERE_IS_PERSO.'themes/' . pnVarPrepForOS($GLOBALS['thename']) . '/lang/' . pnVarPrepForOS($language) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file='themes/' . pnVarPrepForOS($Default_Theme) . '/lang/' . pnVarPrepForOS($currentlang) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file='themes/' . pnVarPrepForOS($Default_Theme) . '/lang/' . pnVarPrepForOS($language) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file=WHERE_IS_PERSO.'themes/' . pnVarPrepForOS($Default_Theme) . '/lang/' . pnVarPrepForOS($currentlang) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
      elseif (file_exists($file=WHERE_IS_PERSO.'themes/' . pnVarPrepForOS($Default_Theme) . '/lang/' . pnVarPrepForOS($language) . '/' . pnVarPrepForOS($script) . '.php'))
         @include $file;
   }

// list of all availabe languages (from Patrick Kellum <webmaster@ctarl-ctarl.com>)
// ==============================
   function languagelist()
   {
      // Need to ensure this is loaded for language defines
      pnBlockLoad('Core', 'thelang');
//    All entries use ISO 639-2/T
      $lang['ara'] = _LANGUAGE_ARA; // Arabic
      $lang['bul'] = _LANGUAGE_BUL; // Bulgarian
      $lang['zho'] = _LANGUAGE_ZHO; // Chinese
      $lang['cat'] = _LANGUAGE_CAT; // Catalan
      $lang['ces'] = _LANGUAGE_CES; // Czech
      $lang['hrv'] = _LANGUAGE_HRV; // Croatian  HRV
      $lang['cro'] = _LANGUAGE_CRO; // Croatian  CRO
      $lang['dan'] = _LANGUAGE_DAN; // Danish
      $lang['nld'] = _LANGUAGE_NLD; // Dutch
      $lang['eng'] = _LANGUAGE_ENG; // English
      $lang['epo'] = _LANGUAGE_EPO; // Esperanto
      $lang['est'] = _LANGUAGE_EST; // Estonian
      $lang['fin'] = _LANGUAGE_FIN; // Finnish
      $lang['fra'] = _LANGUAGE_FRA; // French
      $lang['deu'] = _LANGUAGE_DEU; // German
      $lang['ell'] = _LANGUAGE_ELL; // Greek, Modern (1453-)
      $lang['heb'] = _LANGUAGE_HEB; // Hebrew
      $lang['hun'] = _LANGUAGE_HUN; // Hungarian
      $lang['isl'] = _LANGUAGE_ISL; // Icelandic
      $lang['ind'] = _LANGUAGE_IND; // Indonesian
      $lang['ita'] = _LANGUAGE_ITA; // Italian
      $lang['jpn'] = _LANGUAGE_JPN; // Japanese
      $lang['kor'] = _LANGUAGE_KOR; // Korean
      $lang['lav'] = _LANGUAGE_LAV; // Latvian
      $lang['lit'] = _LANGUAGE_LIT; // Lithuanian
      $lang['mas'] = _LANGUAGE_MAS; // Malay
      $lang['mkd'] = _LANGUAGE_MKD; // Macedonian
      $lang['nor'] = _LANGUAGE_NOR; // Norwegian
      $lang['pol'] = _LANGUAGE_POL; // Polish
      $lang['por'] = _LANGUAGE_POR; // Portuguese
      $lang['ron'] = _LANGUAGE_RON; // Romanian
      $lang['rus'] = _LANGUAGE_RUS; // Russian
      $lang['slv'] = _LANGUAGE_SLV; // Slovenian
      $lang['spa'] = _LANGUAGE_SPA; // Spanish
      $lang['swe'] = _LANGUAGE_SWE; // Swedish
      $lang['tha'] = _LANGUAGE_THA; // Thai
      $lang['tur'] = _LANGUAGE_TUR; // Turkish
      $lang['ukr'] = _LANGUAGE_UKR; // Ukrainian
      $lang['yid'] = _LANGUAGE_YID; // Yiddish
//    Non-ISO entries are written as x_[language name]
      $lang['x_all'] = _ALL; // all languages
      $lang['x_brazilian_portuguese'] = _LANGUAGE_X_BRAZILIAN_PORTUGUESE; // Brazilian Portuguese
      $lang['x_klingon'] = _LANGUAGE_X_KLINGON; // Klingon
      $lang['x_rus_koi8r'] = _LANGUAGE_X_RUS_KOI8R; // Russian KOI8-R
//    end of list
      return $lang;
   }

   function rsslanguagelist()
   {
    $rsslang['af'] = "Afrikaans";
    $rsslang['sq'] = "Albanian";
    $rsslang['ar-bh'] = "Arabic (Bahrain)";
    $rsslang['eu'] = "Basque";
    $rsslang['be'] = "Belarusian";
    $rsslang['bg'] = "Bulgarian";
    $rsslang['ca'] = "Catalan";
    $rsslang['zh-cn'] = 'Chinese (Simplified)';
    $rsslang['zh-tw'] = 'Chinese (Traditional)';
    $rsslang['hr'] = 'Croatian';
    $rsslang['cs'] = 'Czech';
    $rsslang['da'] = 'Danish';
    $rsslang['nl'] = 'Dutch';
    $rsslang['nl-be'] = 'Dutch (Belgium)';
    $rsslang['nl-nl'] = 'Dutch (Netherlands)';
    $rsslang['en'] = 'English';
    $rsslang['en-au'] = 'English (Australia)';
    $rsslang['en-bz'] = 'English (Belize)';
    $rsslang['en-ca'] = 'English (Canada)';
    $rsslang['en-ie'] = 'English (Ireland)';
    $rsslang['en-jm'] = 'English (Jamaica)';
    $rsslang['en-nz'] = 'English (New Zealand)';
    $rsslang['en-ph'] = 'English (Phillipines)';
    $rsslang['en-za'] = 'English (South Africa)';
    $rsslang['en-tt'] = 'English (Trinidad)';
    $rsslang['en-gb'] = 'English (United Kingdom)';
    $rsslang['en-us'] = 'English (United States)';
    $rsslang['en-zw'] = 'English (Zimbabwe)';
    $rsslang['fo'] = 'Faeroese';
    $rsslang['fi'] = 'Finnish';
    $rsslang['fr'] = 'French';
    $rsslang['fr-be'] = 'French (Belgium)';
    $rsslang['fr-ca'] = 'French (Canada)';
    $rsslang['fr-fr'] = 'French (France)';
    $rsslang['fr-lu'] = 'French (Luxembourg)';
    $rsslang['fr-mc'] = 'French (Monaco)';
    $rsslang['fr-ch'] = 'French (Switzerland)';
    $rsslang['gl'] = 'Galician';
    $rsslang['gd'] = 'Gaelic';
    $rsslang['de'] = 'German';
    $rsslang['de-at'] = 'German (Austria)';
    $rsslang['de-de'] = 'German (Germany)';
    $rsslang['de-li'] = 'German (Liechtenstein)';
    $rsslang['de-lu'] = 'German (Luxembourg)';
    $rsslang['de-ch'] = 'German (Switzerland)';
    $rsslang['el'] = 'Greek';
    $rsslang['hu'] = 'Hungarian';
    $rsslang['is'] = 'Icelandic';
    $rsslang['in'] = 'Indonesian';
    $rsslang['ga'] = 'Irish';
    $rsslang['it'] = 'Italian';
    $rsslang['it-it'] = 'Italian (Italy)';
    $rsslang['it-ch'] = 'Italian (Switzerland)';
    $rsslang['ja'] = 'Japanese';
    $rsslang['ko'] = 'Korean';
    $rsslang['mk'] = 'Macedonian';
    $rsslang['no'] = 'Norwegian';
    $rsslang['pl'] = 'Polish';
    $rsslang['pt'] = 'Portuguese';
    $rsslang['pt-br'] = 'Portuguese (Brazil)';
    $rsslang['pt-pt'] = 'Portuguese (Portugal)';
    $rsslang['ro'] = 'Romanian';
    $rsslang['ro-mo'] = 'Romanian (Moldova)';
    $rsslang['ro-ro'] = 'Romanian (Romania)';
    $rsslang['ru'] = 'Russian';
    $rsslang['KOI8-R'] = 'Russian KOI8-R';
    $rsslang['ru-mo'] = 'Russian (Moldova)';
    $rsslang['ru-ru'] = 'Russian (Russia)';
    $rsslang['sr'] = 'Serbian';
    $rsslang['sk'] = 'Slovak';
    $rsslang['sl'] = 'Slovenian';
    $rsslang['es'] = 'Spanish';
    $rsslang['es-ar'] = 'Spanish (Argentina)';
    $rsslang['es-bo'] = 'Spanish (Bolivia)';
    $rsslang['es-cl'] = 'Spanish (Chile)';
    $rsslang['es-co'] = 'Spanish (Colombia)';
    $rsslang['es-cr'] = 'Spanish (Costa Rica)';
    $rsslang['es-do'] = 'Spanish (Dominican Republic)';
    $rsslang['es-ec'] = 'Spanish (Ecuador)';
    $rsslang['es-sv'] = 'Spanish (El Salvador)';
    $rsslang['es-gt'] = 'Spanish (Guatemala)';
    $rsslang['es-hn'] = 'Spanish (Honduras)';
    $rsslang['es-mx'] = 'Spanish (Mexico)';
    $rsslang['es-ni'] = 'Spanish (Nicaragua)';
    $rsslang['es-pa'] = 'Spanish (Panama)';
    $rsslang['es-py'] = 'Spanish (Paraguay)';
    $rsslang['es-pe'] = 'Spanish (Peru)';
    $rsslang['es-pr'] = 'Spanish (Puerto Rico)';
    $rsslang['es-es'] = 'Spanish (Spain)';
    $rsslang['es-uy'] = 'Spanish (Uruguay)';
    $rsslang['es-ve'] = 'Spanish (Venezuela)';
    $rsslang['sv'] = 'Swedish';
    $rsslang['sv-fi'] = 'Swedish (Finland)';
    $rsslang['sv-se'] = 'Swedish (Sweden)';
    $rsslang['th'] = 'Thai';
    $rsslang['tr'] = 'Turkish';
    $rsslang['uk'] = 'Ukranian';
    $rsslang['ar'] = 'Arabic';
    $rsslang['ar-ae'] = 'Arabic (United Arab Emirates)';
    $rsslang['ar-bh'] = 'Arabic (Bahrain)';
    $rsslang['ar-dz'] = 'Arabic (Algeria)';
    $rsslang['ar-eg'] = 'Arabic (Egypt)';
    $rsslang['ar-iq'] = 'Arabic (Iraq)';
    $rsslang['ar-jo'] = 'Arabic (Jordan)';
    $rsslang['ar-kw'] = 'Arabic (Kuwait)';
    $rsslang['ar-lb'] = 'Arabic (Lebanon)';
    $rsslang['ar-ly'] = 'Arabic (Libya)';
    $rsslang['ar-ma'] = 'Arabic (Morocco)';
    $rsslang['ar-mr'] = 'Arabic (Mauritania)';
    $rsslang['ar-om'] = 'Arabic (Oman)';
    $rsslang['ar-qa'] = 'Arabic (Qatar)';
    $rsslang['ar-sa'] = 'Arabic (Saudi Arabia)';
    $rsslang['ar-sd'] = 'Arabic (Sudan)';
    $rsslang['ar-so'] = 'Arabic (Somalia)';
    $rsslang['ar-sy'] = 'Arabic (Syria)';
    $rsslang['ar-tn'] = 'Arabic (Tunisia)';
    $rsslang['ar-ye'] = 'Arabic (Yemen)';
    $rsslang['ar-km'] = 'Arabic (Comoros)';
    $rsslang['ar-dj'] = 'Arabic (Djibouti)';
    asort($rsslang);
    return $rsslang;
}

// Timezone Function by Fred B (fredb86)
function ml_ftime($datefmt, $timestamp = -1){

    if ($timestamp < 0){
        $timestamp = time();
    }
    $day_of_week_short = explode(' ', _DAY_OF_WEEK_SHORT);
    $month_short = explode(' ', _MONTH_SHORT);
    $day_of_week_long = explode(' ', _DAY_OF_WEEK_LONG);
    $month_long = explode(' ', _MONTH_LONG);

      $ml_date = ereg_replace('%a',$day_of_week_short[(int) strftime('%w',$timestamp)],$datefmt);
      $ml_date = ereg_replace('%A',$day_of_week_long[(int) strftime('%w',$timestamp)],$ml_date);
      $ml_date = ereg_replace('%b',$month_short[(int) strftime('%m',$timestamp)-1],$ml_date);
      $ml_date = ereg_replace('%B',$month_long[(int)strftime ('%m',$timestamp)-1],$ml_date);

    if(pnUserLoggedIn()) {
        $thezone = pnUserGetVar('timezone_offset');
    } else {
        $thezone = pnConfigGetVar('timezone_offset');
    }

    $timezone_all = explode(' ', _TIMEZONES);
    $offset_all = explode(' ', _TZOFFSETS);

    $indexofzone=0;
    for ($i=0; $i<sizeof($offset_all); $i++){
        if ($offset_all[$i] == $thezone){
           $indexofzone=$i;
        }
    }
    $ml_date = ereg_replace('%Z',$timezone_all [$indexofzone],$ml_date);
    return strftime($ml_date,$timestamp);
}


// get current language
// ====================
   function language_current($action='get',$new_language='')
   {
      static $language='';
      switch ($action)
      {
         case 'get':
            return $language;
         case 'set':
            $language = $new_language;
            break;
         default:
            die("language_current($action,$new_language)");
      }
   }

// build language sql clause for ml
// ================================
   function language_sql($table, $prefix='',$sql='WHERE')
   {
    $language = language_current();
    if ($language=='')
       return '';
    else
       return " $sql " . $pntable["{$table}_column"]["{$prefix}language"] . "='$language'";
   }

// get a language name
// ===================
function language_name($language) {
    static $name=array();
    if (!count($name)) {
        $name = languagelist();
    }
    return $name[$language];
}
?>