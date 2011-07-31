<?php
// File: $Id: admin.php,v 1.6 2002/11/05 20:09:06 tanis Exp $ $Name:  $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!eregi('admin.php', $PHP_SELF))
{
	die('Access Denied');
}
$ModName = $module;

   if (!(pnSecAuthAction(0, 'Settings::', '::', ACCESS_ADMIN)))
   {
      include 'header.php';
      echo _SETTINGSNOAUTH;
      include 'footer.php';
   }

   modules_get_language();
   modules_get_manual();

// Fixes the ' duplicated in the slogan & footer fields - acm3
// Function commentd out by tanis. It seems to be no longer needed.
/*   function FixConfigQuotes ($what = "")
   {
      $what = ereg_replace(chr(146), chr(39), $what);
      while (ereg("\\\'", $what))
      {
         $what = ereg_replace("\\\'", chr(39), $what);
      }
      return $what;
   }
*/

/*********************************************************/
/* Configuration Functions to Setup all the Variables    */
/*********************************************************/

   function settings_admin_main($var)
   {
      $pnconfig = $GLOBALS["pnconfig"];
      if (strlen(WHERE_IS_PERSO)>0)
        $pnconfig['tipath'] = str_replace(WHERE_IS_PERSO, '', pnConfigGetVar('tipath'));

    include 'header.php';
    GraphicAdmin();
    OpenTable();
    print '<center><font size="4" class="pn-pagetitle">'._SITECONFIG.'</font></center>';
    CloseTable();

    if (!(pnSecAuthAction(0, 'Settings::', '::', ACCESS_ADMIN))) {
        echo _SETTINGSNOAUTH;
        include 'footer.php';
        return;
    }
    // Set the current settings for select fields, radio buttons and checkboxes.
    // Much better then using if() statements all over the place :-)
    $sel_dynkeywords['0'] = '';
    $sel_dynkeywords['1'] = '';
    $sel_dynkeywords[pnConfigGetVar('dyn_keywords')] = ' checked';
    $sel_storyhome['5'] = '';
    $sel_storyhome['10'] = '';
    $sel_storyhome['15'] = '';
    $sel_storyhome['20'] = '';
    $sel_storyhome['25'] = '';
    $sel_storyhome['30'] = '';
    $sel_storyhome[pnConfigGetVar('storyhome')] = ' selected';
    $sel_storyorder['0'] = '';
    $sel_storyorder['1'] = '';
    $sel_storyorder[pnConfigGetVar('storyorder')] = ' selected';
    $sel_defaulttheme[pnConfigGetVar('Default_Theme')] = ' selected';
    $sel_themechange['0'] = '';
    $sel_themechange['1'] = '';
    $sel_themechange[pnConfigGetVar('theme_change')] = ' checked';
    $sel_lang[pnConfigGetVar('language')] = ' selected';
    $sel_nobox['0'] = '';
    $sel_nobox['1'] = '';
    $sel_nobox[pnConfigGetVar('nobox')] = ' checked';
    $sel_tzoffset[pnConfigGetVar('timezone_offset')] = ' selected';
    $sel_backendlanguage[pnConfigGetVar('backend_language')] = ' selected';
    $sel_admingraphic['0'] = '';
    $sel_admingraphic['1'] = '';
    $sel_admingraphic[pnConfigGetVar('admingraphic')] = ' checked';
    $sel_admart['10'] = '';
    $sel_admart['15'] = '';
    $sel_admart['20'] = '';
    $sel_admart['25'] = '';
    $sel_admart['30'] = '';
    $sel_admart['50'] = '';
    $sel_admart[pnConfigGetVar('admart')] = ' selected';
    $sel_reportlevel['0'] = '';
    $sel_reportlevel['1'] = '';
    $sel_reportlevel['2'] = '';
    $sel_reportlevel[pnConfigGetVar('reportlevel')] = ' checked';
    $sel_funtext['0'] = '';
    $sel_funtext['1'] = '';
    $sel_funtext[pnConfigGetVar('funtext')] = ' checked';
    $sel_censormode['0'] = '';
    $sel_censormode['1'] = '';
    $sel_censormode[pnConfigGetVar('censormode')] = ' checked';
    $sel_intranet['0'] = '';
    $sel_intranet['1'] = '';
    $sel_WYSIWYGEditor['0'] = '';
    $sel_WYSIWYGEditor['1'] = '';
    $sel_WYSIWYGEditor[pnConfigGetVar('WYSIWYGEditor')] = ' checked';
    $sel_pnAntiCracker['0'] = '';
    $sel_pnAntiCracker['1'] = '';
    $sel_pnAntiCracker[pnConfigGetVar('pnAntiCracker')] = ' checked';
    $sel_intranet[pnConfigGetVar('intranet')] = ' checked';
    $sel_seclevel['High'] = '';
    $sel_seclevel['Medium'] = '';
    $sel_seclevel['Low'] = '';
    $sel_seclevel[pnConfigGetVar('seclevel')] = 'selected';
    $sel_htmlentities['0'] = '';
    $sel_htmlentities['1'] = '';
    $sel_htmlentities[pnConfigGetVar('htmlentities')] = 'checked';
    $sel_usecompression['0'] = '';
    $sel_usecompression['1'] = '';
    $sel_usecompression[pnConfigGetVar('UseCompression')] = ' selected';
    $sel_refereronprint['0'] = '';
    $sel_refereronprint['1'] = '';
    $sel_refereronprint[pnConfigGetVar('refereronprint')] = ' selected';


    //
    // let's pre-create an array of the current times for each TZ
    //
    $tzo = 0;
    $gmt = time() - date('Z');
    for ($i = -12; $i <= 12; $i++)
    {
        $tzstring["tz$tzo"] = strftime(_TIMEBRIEF, $gmt + (3600 * $i));
        $tzo++;
    }
    // some special cases
    $tzstring['tz8a'] = strftime(_TIMEBRIEF, $gmt - 12600);
    $tzstring['tz15a'] = strftime(_TIMEBRIEF, $gmt + 12600);
    $tzstring['tz16a'] = strftime(_TIMEBRIEF, $gmt + 16200);
    $tzstring['tz17a'] = strftime(_TIMEBRIEF, $gmt + 19800);
    $tzstring['tz21a'] = strftime(_TIMEBRIEF, $gmt + 34200);
    // done, now on to the form

    OpenTable();
    print '<center><font size="3" class="pn-title">'._GENSITEINFO.'</font></center>'
        .'<form action="admin.php" name="settings" method="post">'
        // The next line was added by sgk on Oct 23, 2001.
        // This hidden value will be used in ConfigSave() function.
        .'<input type="hidden" name="_magic_quotes_gpc_test" value="&quot;">'
        .'<table border="0"><tr><td class="pn-normal">'
        ._SITENAME.":</td><td><input type=\"text\" name=\"xsitename\" value=\"".pnConfigGetVar('sitename')."\" size=\"50\" maxlength=\"100\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._SITELOGO.":</td><td><input type=\"text\" name=\"xsite_logo\" value=\"".pnConfigGetVar('site_logo')."\" size=\"20\" maxlength=\"25\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._SITESLOGAN.":</td><td><input type=\"text\" name=\"xslogan\" value=\"".pnConfigGetVar('slogan')."\" size=\"50\" maxlength=\"100\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._METAKEYWORDS.':</td><td><textarea name="xmetakeywords" cols="60" rows="5" wrap="virtual" class="pn-normal">'.htmlspecialchars(pnConfigGetVar('metakeywords')).'</textarea>'
        .'</td></tr><tr><td class="pn-normal">'
        ._DYNKEYWORDS.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xdyn_keywords\" value=\"1\"".$sel_dynkeywords['1']." class=\"pn-normal\">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xdyn_keywords\" value=\"0\"".$sel_dynkeywords['0']." class=\"pn-normal\">"._NO.'&nbsp;'
        .'</td></tr><tr><td class="pn-normal">'
        ._STARTDATE.":</td><td><input type=\"text\" name=\"xstartdate\" value=\"".pnConfigGetVar('startdate')."\" size=\"20\" maxlength=\"30\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._ADMINEMAIL.":</td><td><input type=\"text\" name=\"xadminmail\" value=\"".pnConfigGetVar('adminmail')."\" size=30 maxlength=100>"
        .'</td></tr><tr><td class="pn-normal">'
        .'</td></tr><tr><td class="pn-normal">'
        ._DEFAULTTHEME.':</td><td><select name="xDefault_Theme" size="1" class="pn-normal">'
    ;
    $handle = opendir('themes');
    while ($f = readdir($handle))
    {
        if ($f != '.' && $f != '..' && $f != 'CVS' && !ereg("[.]",$f))

        {
            $themelist[] = $f;
        }
    }
    closedir($handle);
/* modif sebastien multi sites le 09/09/2001. */
    $cWhereIsPerso = WHERE_IS_PERSO;
    if (!(empty($cWhereIsPerso))) {
        $handle = opendir(WHERE_IS_PERSO.'themes');
        while ($f = readdir($handle))
        {
            if ($f != '.' && $f != '..' && $f != 'CVS' && !ereg("[.]",$f))

            {
                $themelist[] = $f;
            }
        }
        closedir($handle);
        }
/* fin modif sebastien */
    sort($themelist);
    foreach ($themelist as $v)
    {
        if (!isset($sel_defaulttheme[$v])) $sel_defaulttheme[$v]='';
        print "<option value=\"$v\"$sel_defaulttheme[$v]>$v</option>\n";
    }
    print '</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._THEMECHANGE.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xtheme_change\" value=\"0\" class=\"pn-normal\"$sel_themechange[0]>"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xtheme_change\" value=\"1\" class=\"pn-normal\"$sel_themechange[1]>"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._BLOCKSINARTICLES.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xnobox\" value=\"0\" class=\"pn-normal\"$sel_nobox[0]>"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xnobox\" value=\"1\" class=\"pn-normal\"$sel_nobox[1]>"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._LOCALEFORMAT.":</td><td><input type=\"text\" name=\"xlocale\" value=\"".pnConfigGetVar('locale')."\" size=\"20\" maxlength=\"40\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._TIMEZONEOFFSET.':</td><td class="pn-normal">';

    $tzoffset = pnConfigGetVar('timezone_offset');
    global $tzinfo;
    echo "<select name=\"xtimezone_offset\" size=\"1\" class=\"pn-normal\">\n";
    foreach ($tzinfo as $tzindex => $tzdata) {
        echo "<option value=\"$tzindex\"";
        if ($tzoffset == $tzindex) {
            echo "selected";
        }
        echo ">";
        echo $tzdata;
        echo "</option>";
    }

    echo '</select>'
        .'</td></tr><tr><td class="pn-normal">'
        .'</td></tr><tr><td class="pn-normal">'
        ._STARTPAGE."</td><td class=\"pn-normal\">"
        ."<select name=\"xstartpage\" size=\"1\" class=\"pn-normal\">\n";

/* Must changed */

    $handle = opendir('modules');
    while ($f = readdir($handle))
    {
        if ((!ereg('[.]', $f)) && $f != 'CVS' && (!ereg('NS-', $f)))
        {
            $startpagepath = "$f";
            if (pnConfigGetVar('startpage') == $startpagepath)
            {
                $sel_startpage = " selected";
            }
            else
            {
                $sel_startpage = "";
            }
            echo "<option value=\"$startpagepath\"$sel_startpage>$f</option>\n";
        }
    }
    closedir($handle);
    echo "</select> "._STARTPAGEDESCR."\n"
        ."</td></tr><tr><td class=\"pn-normal\">\n"
        ._ARTINADMIN.':</td><td>'
        .'<select name="xadmart" size="1" class="pn-normal">'
        ."<option value=\"10\"".$sel_admart['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_admart['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_admart['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_admart['25'].">25</option>\n"
        ."<option value=\"30\"".$sel_admart['30'].">30</option>\n"
        ."<option value=\"50\"".$sel_admart['50'].">50</option>\n"
        .'</select>'
        ."</td></tr><tr><td class=\"pn-normal\">\n"
        ._STORIESHOME.':</td><td>'
        .'<select name="xstoryhome" size="1" class="pn-normal">'
        ."<option value=\"5\"".$sel_storyhome['5'].">5</option>\n"
        ."<option value=\"10\"".$sel_storyhome['10'].">10</option>\n"
        ."<option value=\"15\"".$sel_storyhome['15'].">15</option>\n"
        ."<option value=\"20\"".$sel_storyhome['20'].">20</option>\n"
        ."<option value=\"25\"".$sel_storyhome['30'].">30</option>\n"
        ."<option value=\"30\"".$sel_storyhome['30'].">30</option>\n"
        .'</select>'
        ."</td></tr><tr><td class=\"pn-normal\">\n"
        ._STORIESORDER.':</td><td>'
        .'<select name="xstoryorder" size="1" class="pn-normal">'
        ."<option value=\"0\"".$sel_storyorder['0'].">" . _STORIESORDER0 . "</option>\n"
        ."<option value=\"1\"".$sel_storyorder['1'].">" . _STORIESORDER1 . "</option>\n"
        .'</select>'
        ."</td></tr><tr><td class=\"pn-normal\">\n"
        ._ADMINGRAPHIC.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xadmingraphic\" value=\"1\" class=\"pn-normal\"".$sel_admingraphic['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xadmingraphic\" value=\"0\" class=\"pn-normal\"".$sel_admingraphic['0'].">"._NO
        ."</td></tr><tr><td class=\"pn-normal\">\n"
        ._REPORTLEVEL.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xreportlevel\" value=\"0\" class=\"pn-normal\"".$sel_reportlevel['0'].">"._REPORTLEVEL0.' &nbsp;'
        ."<input type=\"radio\" name=\"xreportlevel\" value=\"1\" class=\"pn-normal\"".$sel_reportlevel['1'].">"._REPORTLEVEL1.' &nbsp;'
        ."<input type=\"radio\" name=\"xreportlevel\" value=\"2\" class=\"pn-normal\"".$sel_reportlevel['2'].">"._REPORTLEVEL2
        .'</td></tr><tr><td class="pn-normal">'
        ._FUNTEXT.'</td><td class="pn-normal">'
        ."<input type=\"radio\" name=\"xfuntext\" value=\"1\" class=\"pn-normal\"".$sel_funtext['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xfuntext\" value=\"0\" class=\"pn-normal\"".$sel_funtext['0'].">"._NO
      	.'</td></tr><tr><td class="pn-normal">'
        ._CENSORTEXT.'</td><td class="pn-normal">'
	      ."<input type=\"radio\" name=\"xcensormode\" value=\"1\" class=\"pn-normal\"".$sel_censormode['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xcensormode\" value=\"0\" class=\"pn-normal\"".$sel_censormode['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._WYSIWYGEDITORTEXT.'</td><td class="pn-normal">'
	      ."<input type=\"radio\" name=\"xWYSIWYGEditor\" value=\"1\" class=\"pn-normal\"".$sel_WYSIWYGEditor['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xWYSIWYGEditor\" value=\"0\" class=\"pn-normal\"".$sel_WYSIWYGEditor['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'
        ._PNANTICRACKERTEXT.'</td><td class="pn-normal">'
	      ."<input type=\"radio\" name=\"xpnAntiCracker\" value=\"1\" class=\"pn-normal\"".$sel_pnAntiCracker['1'].">"._YES.' &nbsp;'
        ."<input type=\"radio\" name=\"xpnAntiCracker\" value=\"0\" class=\"pn-normal\"".$sel_pnAntiCracker['0'].">"._NO
        .'</td></tr><tr><td class="pn-normal">'

// TODO - change this in to a dropdown
        ._DEFAULTGROUP.'</td><td class="pn-normal">'
        ."<input type=\"text\" name=\"xdefaultgroup\" value=\"" . pnConfigGetVar('defaultgroup') . "\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._SELLANGUAGE.':</td><td><select name="xlanguage" size="1" class="pn-normal">'
    ;
    $lang = languagelist();
    foreach ($lang as $k=>$v) {
        echo '<option value="'.$k.'"';
        if (isset($sel_lang[$k])) {
            echo ' selected';
        }
        echo '>';
        echo "[$k] ";
        echo "$v";
        echo '</option>' . "\n";
    }
    echo '</select>'
        .'</td></tr>'
    .'<tr><td class="pn-normal">'._USECOMPRESSION.'</td><td class="pn-normal">'
    ."<select name=\"xUseCompression\" class=\"pn-normal\">\n"
    ."<option value=\"0\"".$sel_usecompression['0']." class=\"pn-normal\">"._NO."</option>"
    ."<option value=\"1\"".$sel_usecompression['1']." class=\"pn-normal\">"._YES."</option>"
    ."</select>\n"
    .'</td></tr>'
    .'</table>';

    CloseTable();


    OpenTable();
    print '<center><font class="pn-title">'._FOOTERMSG.'</font></center>'
        .'<table border="0"><tr><td class="pn-normal">'
        ._FOOTERLINE.':</td><td><textarea name="xfoot1" cols="50" rows="5" wrap="soft" class="pn-normal">'.htmlspecialchars(pnConfigGetVar('foot1')).'</textarea>'
        .'</td></tr></table>';
    CloseTable();

    OpenTable();
    print '<center><font class="pn-title">'._BACKENDCONF.'</font></center>'
        .'<table border="0"><tr><td class="pn-normal">'
        ._BACKENDTITLE.":</td><td><input type=\"text\" name=\"xbackend_title\" value=\"".pnConfigGetVar('backend_title')."\" size=\"50\" maxlength=\"100\" class=\"pn-normal\">"
        .'</td></tr><tr><td class="pn-normal">'
        ._BACKENDLANG.':</td><td><select name="xbackend_language" size="1" class="pn-normal">'
    ;
    $rsslang = rsslanguagelist();
    foreach ($rsslang as $k=>$v)
    {
    echo '<option value="'.$k.'"';
    if (isset($sel_backendlanguage[$k])) echo ' selected';
        echo '>';
        echo "[$k] ";
        echo "$v";
        echo '</option>' . "\n";
    }
    echo '</select>'
        .'</td></tr></table>';
    CloseTable();

    print '<br>';
    OpenTable();
    print '<center><font size="3" class="pn-title">'._SECOPT.'</font></center>'
        .'<table border="0"><tr><td class="pn-normal">'
        ._SECLEVEL.':</td><td>'
        .'<select name="xseclevel" size="1" class="pn-normal">'
        ."<option value=\"High\" $sel_seclevel[High]>" . _SECHIGH ."</option>\n"
        ."<option value=\"Medium\" $sel_seclevel[Medium]>" . _SECMEDIUM . "</option>\n"
        ."<option value=\"Low\" $sel_seclevel[Low]>" . _SECLOW . "</option>\n"
        .'</select>'
        .'</td></tr><tr><td class="pn-normal">'
        ._SECMEDLENGTH.":</td><td><input type=\"text\" name=\"xsecmeddays\" value=\"".pnConfigGetVar('secmeddays')."\" size=\"4\" class=\"pn-normal\"> " .  _DAYS
        .'</td></tr><tr><td class="pn-normal">'
        ._SECINACTIVELENGTH.":</td><td><input type=\"text\" name=\"xsecinactivemins\" value=\"".pnConfigGetVar('secinactivemins')."\" size=\"4\" class=\"pn-normal\"> " .  _MINUTES
        ."</td></tr>"
        ."<tr><td class=\"pn-normal\">"
        ._REFERERONPRINT.'</td><td class="pn-normal">'

        ."<select name=\"xrefereronprint\" class=\"pn-normal\">\n"
        ."<option value=\"0\"".$sel_refereronprint['0']." class=\"pn-normal\">"._NO."</option>"
        ."<option value=\"1\"".$sel_refereronprint['1']." class=\"pn-normal\">"._YES."</option>"
        ."</select>\n"
        ."</td></tr></table>\n";
    CloseTable();

    // Intranet configuration
    OpenTable();
    print '<br>';
    print '<center><font size="3" class="pn-title">'._INTRANETOPT.'</font></center>';
    print '<table border="0">';
    print '<tr>';
    print '<td>'._INTRANET.'</td><td class="pn-normal">';
    print "<input type=\"radio\" name=\"xintranet\" value=\"1\" class=\"pn-normal\"".$sel_intranet['1'].">"._YES.' &nbsp;';
    print "<input type=\"radio\" name=\"xintranet\" value=\"0\" class=\"pn-normal\"".$sel_intranet['0'].">"._NO;
    print '</td></tr>';
    print '</table>';
    print '<b> ' . _INTRANETWARNING. '</b>';
    CloseTable();

    // Allowed HTML
    OpenTable();
    print '<br>';
    print '<center><font size="3" class="pn-title">'._HTMLOPT.'</font></center>'
         .'<table border="0"><tr><td class="pn-normal">'
         ._HTMLALLOWED.':</td>';
    echo '<table border="2">';
    echo '<tr><th>' . _HTMLTAGNAME . '</th>'
        .'<th>' . _HTMLTAGNOTALLOWED . '</th>'
        .'<th>' . _HTMLTAGALLOWED . '</th>'
        .'<th>' . _HTMLTAGALLOWEDWITHPARAMS . '</th>'
        .'</tr>';
    $htmltags = settingsGetHTMLTags();
    $currenthtmltags = pnConfigGetVar('AllowableHTML');
    foreach ($htmltags as $htmltag) {
        $selected[0] = '';
        $selected[1] = '';
        $selected[2] = '';
        if (isset($currenthtmltags[$htmltag])) {
            $selected[$currenthtmltags[$htmltag]] = 'checked';
        } else {
            $selected[0] = 'checked';
        }

        echo '<tr>';
        echo '<td>&lt;' . pnVarPrepForDisplay($htmltag) . '&gt;</td>';
        echo '<td align="center"><input type=radio value="0" name="htmlallow' . pnVarPrepForDisplay($htmltag) . 'tag" ' . $selected[0] . '></td>';
        echo '<td align="center"><input type=radio value="1" name="htmlallow' . pnVarPrepForDisplay($htmltag) . 'tag" ' . $selected[1] . '></td>';
        echo '<td align="center"><input type=radio value="2" name="htmlallow' . pnVarPrepForDisplay($htmltag) . 'tag" ' . $selected[2] . '></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<b> ' . _HTMLWARNING. '</b>';
    echo '<br />';

    echo _HTMLALLOWENTITIES .
         '<input type="radio" name="xhtmlentities" value="1" class="pn-normal"' . $sel_htmlentities[1]. '>' . _YES . ' &nbsp;' .
        '<input type="radio" name="xhtmlentities" value="0" class="pn-normal"' . $sel_htmlentities[0] . '>' . _NO;

    CloseTable();
    // Finish
    echo '<input type="hidden" name="op" value="generate">'
        .'<input type="hidden" name="module" value="NS-Settings">'
        .'<input type="hidden" name="authid" value="' . pnSecGenAuthKey() . '">'
        .'<center><input type="submit" value="'._SAVECHANGES.'" class="pn-normal" style="text-align:center"></center>'
        .'</form>';

    include 'footer.php';
}

function settings_admin_generate($vars) {

    if (!(pnSecAuthAction(0, 'Settings::', '::', ACCESS_ADMIN))) {
        include 'header.php';
        echo _SETTINGSNOAUTH;
        include 'footer.php';
        return;
    }

    /*
     * Write the vars
     */
    // TODO - fix this so that it fetches each value manually, otherwise
    // this is a security hole

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        echo _BADAUTHKEY;
        include 'footer.php';
    }

    foreach($vars as $name => $value) {
        if (substr($name, 0, 1) == 'x') {
            $var = pnVarCleanFromInput($name);
            pnConfigSetVar(substr($name, 1), $var);
        }
    }

    // Create
    $allowedhtml = array();
    $htmltags = settingsGetHTMLTags();
    foreach ($htmltags as $htmltag) {
        $tagval = pnVarCleanFromInput('htmlallow'.$htmltag.'tag');
        if (($tagval != 1) && ($tagval != 2)) {
            $tagval = 0;
        }
        $allowedhtml[$htmltag] = $tagval;
    }
    pnConfigSetVar('AllowableHTML', $allowedhtml);


    pnRedirect('admin.php');
}

// Local function to provide list of all possible HTML tags
function settingsGetHTMLTags() {
    // Possible allowed HTML tags
    return array('!--',
                  'a',
                  'abbr',
                  'acronym',
                  'address',
		  'applet',
		  'area',
                  'b',
		  'base',
		  'basefont',
		  'bdo',
                  'big',
                  'blockquote',
                  'br',
		  'button',
                  'caption',
                  'center',
                  'cite',
                  'code',
		  'col',
		  'colgroup',
		  'del',
                  'dfn',
		  'dir',
                  'div',
                  'dl',
                  'dd',
                  'dt',
                  'em',
                  'embed',
		  'fieldset',
                  'font',
		  'form',
                  'h1',
                  'h2',
                  'h3',
                  'h4',
                  'h5',
                  'h6',
                  'hr',
                  'i',
                  'iframe',
                  'img',
		  'input',
		  'ins',
		  'kbd',
		  'label',
		  'legend',
                  'li',
		  'map',
                  'marquee',
		  'menu',
		  'nobr',
                  'object',
                  'ol',
		  'optgroup',
		  'option',
                  'p',
                  'param',
                  'pre',
                  'q',
                  's',
                  'samp',
                  'script',
		  'select',
                  'small',
                  'span',
                  'strike',
                  'strong',
                  'sub',
                  'sup',
                  'table',
		  'tbody',
                  'td',
		  'textarea',
		  'tfoot',
                  'th',
		  'thead',
                  'tr',
		  'tt',
                  'u',
		  'ul',
		  'var');
}
?>