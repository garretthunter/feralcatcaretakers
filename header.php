<?php
// File: $Id: header.php,v 1.11 2003/02/25 10:37:49 tanis Exp $ $Name:  $
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

global $PHP_SELF;

if (eregi("header.php", $PHP_SELF)) {
    die ("You can't access this file directly...");
}

/**
 * Include some common header for HTML generation.
 *
 * XHTML Support by Matt Jarjoura. <mjarjo1@umbc.edu>
 */

$header = 1;

function head() {

    global
    $index,
        $artpage,
        $topic,
        $hlpfile,
        $hr,
        $theme,
        $bgcolor1,
        $bgcolor2,
        $bgcolor3,
        $bgcolor4,
        $bgcolor5,
        $textcolor1,
        $textcolor2,
        $textcolor3,
        $textcolor4,
        $forumpage,
        $thename,
        $postnuke_theme,
        $pntheme,
        $themename,
        $themeimages,
        $additional_header,
        $themeOverrideCategory,
        $themeOverrideStory;

    // modification mouzaia .71
        $cWhereIsPerso = WHERE_IS_PERSO;
        if ( !(empty($cWhereIsPerso)) )
                { include("modules/NS-Multisites/head.inc.php"); }
        else
                {
        global $themesarein;
        if ((pnUserLoggedIn()) && (pnConfigGetVar('theme_change') != 1)) {
            $thistheme = pnUserGetTheme();
        if(isset ($theme)) {
            $thistheme=pnVarPrepForOs($theme);
        }
        } else {
        $thistheme = pnConfigGetVar('Default_Theme');
        if (isset ($theme)) {
            $thistheme=pnVarPrepForOs($theme);
        }
        }
// eugenio themeover 20020413
    // override the theme per category or story
    // precedence is story over category override
        if (($themeOverrideCategory != '') && (file_exists("themes/$themeOverrideCategory"))) {
            $thistheme = $themeOverrideCategory;
        }
        if (($themeOverrideStory != '') && (file_exists("themes/$themeOverrideStory"))) {
            $thistheme = $themeOverrideStory;
        }
        if (@file(WHERE_IS_PERSO."themes/".$thistheme."/theme.php")) {
            $themesarein = WHERE_IS_PERSO;
        } else {
            $themesarein = "";
        }
    }
// eugenio themeover 20020413
        pnThemeLoad($thistheme);
    /**
     * Simple XHTML Beginnings
     */

    if(pnConfigGetVar('supportxhtml'))
    {
    //include("includes/xhtml.php");
    xhtml_head_start(0);     /* Transitional Support for now */
    }
    else {
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
        echo "<html>\n<head>\n";

    if (defined("_CHARSET") && _CHARSET != "") {
            echo "<meta http-equiv=\"Content-Type\" ".
                     "content=\"text/html; charset="._CHARSET."\">\n";
        }
    }

    if ($artpage==1)
    {
    /**
     * article page output
     */
        global $info, $hometext;
        echo "<title>$info[title] :: ".pnConfigGetVar('sitename').' :: '.pnConfigGetVar('slogan')."</title>\n";
        if (pnConfigGetVar('dyn_keywords') == 1) {
           $htmlless = check_html($info['maintext'], $strip ='nohtml');
           $symbolLess = trim(ereg_replace('("|\?|!|:|\.|\(|\)|;|\\\\)+', ' ', $htmlless));
           $keywords = ereg_replace('( |'.CHR(10).'|'.CHR(13).')+', ',', $symbolLess);
           $metatags = ereg_replace(",+", ",",$keywords);
           echo "<meta http-equiv=\"Keywords\" content=\"$metatags\" />\n";
        } else {
           echo "<meta name=\"KEYWORDS\" content=\"".pnConfigGetVar('metakeywords')."\" />\n";
        }
    } else {
    /**
     * all other page output
     */
    echo '<title>'.pnConfigGetVar('sitename').' :: '.pnConfigGetVar('slogan')."</title>\n";
    echo '<meta name="KEYWORDS" content="'.pnConfigGetVar('metakeywords')."\" />\n";
    }
    echo '<meta name="DESCRIPTION" content="'.pnConfigGetVar('slogan')."\" />\n";
    echo "<meta name=\"ROBOTS\" content=\"INDEX,FOLLOW\" />\n";
    echo "<meta name=\"resource-type\" content=\"document\" />\n";
    echo "<meta http-equiv=\"expires\" content=\"0\" />\n";
    echo '<meta name="author" content="'.pnConfigGetVar('sitename')."\" />\n";
    echo '<meta name="copyright" content="Copyright (c) 2001 by '.pnConfigGetVar('sitename')."\" />\n";
    echo "<meta name=\"revisit-after\" content=\"1 days\" />\n";
    echo "<meta name=\"distribution\" content=\"Global\" />\n";
    echo '<meta name="generator" content="PostNuke '._PN_VERSION_NUM." - http://postnuke.com\" />\n";
    echo "<meta name=\"rating\" content=\"General\" />\n";

    global $themesarein;

    echo "<link rel=\"StyleSheet\" href=\"".$themesarein."themes/".$thistheme."/style/styleNN.css\" type=\"text/css\" />\n";
    echo "<style type=\"text/css\">";
    echo "@import url(\"".$themesarein."themes/".$thistheme."/style/style.css\"); ";
    echo "</style>\n";

    echo "<script type=\"text/javascript\" src=\"javascript/showimages.php\"></script>\n\n";

	/* Enable Wysiwyg editor configuration at seeting Added by bharvey42 edited by Neo */ 

	$pnWysiwygEditor = pnConfigGetVar('WYSIWYGEditor'); 
	if ( is_numeric( $pnWysiwygEditor ) && $pnWysiwygEditor == 1 ) {  
          $pnWSEditorPath = pnGetBaseURI();
		  echo     "<!--Visual Editor Plug-in-->" 
          ."<script type=\"text/javascript\">QBPATH='".$pnWSEditorPath."/javascript'; VISUAL=0; SECURE=1;</script>" 
          ."<script type=\"text/javascript\" src='".$pnWSEditorPath."/javascript/quickbuild.js'></script>" 
          ."<script type=\"text/javascript\" src='".$pnWSEditorPath."/javascript/tabedit.js'></script>"; 
	} else { 
	} 

    if ($forumpage == 1) {
    echo "<script type=\"text/javascript\" src=\"javascript/bbcode.php\"></script>\n\n";
    }
    echo "<script type=\"text/javascript\" src=\"javascript/openwindow.php?hlpfile=$hlpfile\"></script>\n\n";
 
    if(isset($additional_header))
    {
        echo @implode("\n", $additional_header);
    }

    themeheader();
}



/**
 * if you want to do overrides, set the global vars $themeOverrideCategory
 * and/or $themeOverrideStory before including header.php
 */
head();
?>