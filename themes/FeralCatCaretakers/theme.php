<?php
// $Id: theme.php,v 1.4 2003/05/22 22:58:36 garrett Exp $ Exp $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// Thatware - http://thatware.org/
// PHP-NUKE Web Portal System - http://phpnuke.org/
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
// Filename: theme.php
// Original  Author(s): Vanessa Haakenson vanessa@distance-educator.com
// Purpose:  Theme layout
// ----------------------------------------------------------------------
//

/**
 * version: 1.0 - Designs4Nuke.com
 */

/************************************************************/
/* Theme Colors Definition                                  */
/*                                                          */
/* Define colors for your web site. $GLOBALS[bgcolor2] is generaly   */
/* used for the tables border as you can see on OpenTable() */
/* function, $GLOBALS[bgcolor1] is for the table background and the  */
/* other two bgcolor variables follows the same criteria.   */
/* $texcolor1 and 2 are for tables internal texts           */
/************************************************************/

$thename = "FeralCatCaretakers";
$postnuke_theme = true;

themes_get_language();

$bgcolor1 = "#FFFFFF"; // White: 0/0/100
$bgcolor2 = "#E6E6E6"; // Lightest Grey: 0/0/90
$bgcolor3 = "#CCCCCC"; // Med Grey: 0/0/80
$bgcolor4 = "#F3F3F3"; // Light Gray - #4C5EA8
$bgcolor5 = "#ACB2D4"; // Light Cornflower Blue HSB: 213/19/83
$bgcolor6 = "#CC6600"; // Warm Orange: 30/100/80

$textcolor1 = "#000000"; // Black
$textcolor2 = "#000000"; // Black




/************************************************************/
/* OpenTable Functions                                      */
/*                                                          */
/* Define the tables look & feel for your site. For         */
/* this we have two options: OpenTable and OpenTable2       */
/* functions. Then CloseTable and CloseTable2               */
/* function to close our tables. The difference             */
/* is OpenTable has a 90% width and OpenTable2 has          */
/* a width according with the table content.                 */
/************************************************************/

function opentable() {

    echo "<table width=\"90%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\"><tr><td>\n";
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"5\"><tr><td>\n";
}

function opentable2() {

    echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\"><tr><td>\n";
    echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\"><tr><td>\n";
}

function closetable() {
    echo "</td></tr></table></td></tr></table>\n";
}

function closetable2() {
    echo "</td></tr></table></td></tr></table>\n";
}

/************************************************************/
/* Function themeheader()                                   */
/*                                                          */
/* Control the header for your site. You need to define the */
/* BODY tag and in some part of the code call the blocks    */
/* function for left side with: blocks(left);               */
/************************************************************/

function themeheader() {


    $slogan = pnConfigGetVar('slogan');
    $sitename = pnConfigGetVar('sitename');
    $banners = pnConfigGetVar('banners');
    $type = pnVarCleanFromInput('type');

    echo "</head>\n"
        ."<body text=\"#333333\" link=\"#000000\" alink=\"#FF9900\" vlink=\"#CC6600\" topmargin=\"0\" marginheight=\"0\" marginwidth=\"0\" leftmargin=\"0\" rightmargin=\"0\" bgcolor=\"$GLOBALS[bgcolor1]\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"$GLOBALS[bgcolor1]\">\n"
        ."<tr>\n"
        ."<td colspan=\"2\" valign=\"top\" height=\"91\" background=\"themes/$GLOBALS[thename]/images/hbkg.gif\">\n"
        ."<a href=\"index.php\"><img src=\"themes/$GLOBALS[thename]/images/logoshort.gif\" height=\"91\" border=\"0\" align=\"top\"></a>\n"
        ."</td>\n"
        ."</tr>\n"
        ."<tr>\n";
        if (!pnUserLoggedIn()) {
            echo "<td bgcolor=\"$GLOBALS[bgcolor4]\" valign=\"middle\" align=\"left\" width=\"50%\">&nbsp;<a class=\"pn-sub\" href=\"user.php?op=loginscreen&module=NS-User\">"._MEMBERLOGIN."<img src=\"themes/$GLOBALS[thename]/images/go.gif\" width=\"29\" height=\"29\" align=\"absmiddle\" border=\"0\"></a>\n";
        } else {
           echo "<td bgcolor=\"$GLOBALS[bgcolor4]\" valign=\"middle\" align=\"left\" width=\"50%\">&nbsp;<a class=\"pn-sub\" href=\"user.php\">"._YOURACCCOUNT."<img src=\"themes/$GLOBALS[thename]/images/go.gif\" width=\"29\" height=\"29\" align=\"absmiddle\" border=\"0\"></a>\n";
        }

    echo "</font></td>\n"
       ."<td width=\"50%\" height=\"21\" align=\"right\" bgcolor=\"$GLOBALS[bgcolor4]\" valign=\"middle\">\n";

	include "themes/$GLOBALS[thename]/top_links.php";

    echo "<img src=\"themes/$GLOBALS[thename]/images/blank.gif\" width=\"1\" height=\"1\"></td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n"
        ."<tr>\n"
        ."<td width=\"150\" valign=\"top\" bgcolor=\"$GLOBALS[bgcolor4]\">\n"
        ."<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n"
        ."<tr bgcolor=\"$GLOBALS[bgcolor4]\">\n"
        ."<td><img src=\"themes/$GLOBALS[thename]/images/blank.gif\" width=\"1\" height=\"17\" alt=\"\" border=\"0\">\n"
        ."</td>\n"
        ."</tr>\n"
        ."<tr>\n"
        ."<td valign=\"top\">\n";

       blocks('left');

    echo "</td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."</td>\n"
        ."<td width=\"35\" bgcolor=\"$GLOBALS[bgcolor1]\" align=\"left\" valign=\"top\">\n"
        ."<div align=\"left\"><img src=\"themes/$GLOBALS[thename]/images/lefttop.gif\" width=\"17\" height=\"17\" border=\"0\"  alt=\"\"></div>\n"
        ."</td>\n"
        ."<td width=\"100%\" valign=\"top\" align=\"center\" bgcolor=\"$GLOBALS[bgcolor1]\">\n";

    if ($GLOBALS['index'] == 1) {
        echo "<span class=\"message-centre\">\n";
        blocks('centre');
        echo "</span>\n";
    }
}


/************************************************************/
/* Function themefooter()                                   */
/*                                                          */
/* Controls the footer for your site. You don't need to     */
/* close BODY and HTML tags at the end. In some part call   */
/* the function for right blocks with: blocks(right);       */
/* Also, $GLOBALS[index] variable needs to be global and is used to  */
/* determine if the page you're viewing is the Homepage or  */
/* and internal one.                                        */
/* Remember the footer contains the formatting for the      */
/* right side bar on the three column page design           */
/************************************************************/


function themefooter() {


    if ($GLOBALS['index'] == 1) {
       echo "<td width=\"140\" valign=\"top\" bgcolor=\"$GLOBALS[bgcolor1]\">";

       blocks('right');

    }

    echo "<br></td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"$GLOBALS[bgcolor1]\">\n"
        ."<tr bgcolor=\"$GLOBALS[bgcolor3]\">\n"
        ."<td height=\"10\"><img src=\"themes/$GLOBALS[thename]/images/blank.gif\" width=\"1\" height=\"1\" border=0 alt=\"\"></td>\n"
        ."</tr>\n"
        ."<tr bgcolor=\"$GLOBALS[bgcolor4]\">\n"
        ."<td>\n"
        ."<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\">\n"
        ."<tr>\n"
        ."<td>\n"
        ."<div align=\"center\">\n"
        ."<font class=\"pn-sub\">\n";

    footmsg();

    echo "</font>\n"
        ."</div>\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n";

}

/************************************************************/
/* Function themeindex()                                    */
/* This formats the articles/stories on the homepage        */
/************************************************************/

function themeindex ($_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $info, $links, $preformat) {
    echo "<p>\n"
        ."<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n"
        ."<tr>\n"
        ."<td><font class=\"pn-title\">$preformat[catandtitle]</font><br>\n"
        ."<font class=\"pn-normal\">$info[hometext]</font><br>\n";

        if ($preformat['notes']){
            echo "<font class=\"pn-sub\">$preformat[notes]</font><br>\n";
        }

        echo "<font class=\"pn-sub\">"._PUBLISHED." $info[briefdatetime]</font>\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."</p>\n";


}
/************************************************************/
/* Function themearticle()                                  */
/* This function formats the stories on the story,          */
/* when you click on the "Title Link" or "Read More..."     */
/* link                                                     */
/************************************************************/
function themearticle ($_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $info, $links, $preformat) {

    OpenTable();

    echo "<img src=\"themes/$GLOBALS[thename]/images/note_icon.gif\" width=\"14\" height=\"15\" alt=\"*\" border=\"0\" align=\"absmiddle\">\n"
        ."<font class=\"pn-normal\">$preformat[catandtitle]</font><br>\n"
        ."<font class=\"pn-sub\">"._POSTED." $info[briefdatetime]</font><hr size=\"1\">\n"
        ."<div align=\"right\"><b>\n"
        ."<font class=\"pn-sub\">$preformat[send] "._EMAILTOAFRIEND."&nbsp;&nbsp;$preformat[print] "._PRINTTHISSTORY."</font></b></div><p>\n"
        ."<font class=\"pn-normal\">\n";

    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_EDIT)) {
        echo "<span class=\"pn-sub\"> [ <a href=\"admin.php?module=NS-AddStory&amp;op=EditStory&amp;sid=$info[sid]\">"._EDIT."</a> ]";
    }
    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_DELETE)) {
        echo "[ <a href=\"admin.php?module=NS-AddStory&amp;op=RemoveStory&amp;sid=$info[sid]\">"._DELETE."</a> ]</span>";
    }
    echo "<br><br>\n"
        ."<font class=\"pn-art\">$preformat[searchtopic]\n"
        ."$preformat[fulltext]</font>";

      CloseTable();

}

/************************************************************/
/* Function themesidebox()                                  */
/* Controls look of the left side blocks.                   */
/************************************************************/

function themesidebox($block) {

if (empty($block['position'])) {
    $block['position'] = "a";
}

if($block['position'] == 'l') { // left side blocks

    echo "<table width=\"150\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\n"
        ."<tr>\n"
        ."<td class=\"pn-title-lblock\">$block[title]</td>\n"
        ."</tr>\n"
        ."<tr>\n"
        ."<td valign=\"top\">$block[content]\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n"
        ."<table width=\"150\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\">\n"
        ."<tr>\n"
        ."<td>\n"
        ."<hr size=\"1\">\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n";
}

if($block['position'] == 'r') {
    echo "<br><table width=\"140\" border=\"0\" background=\"themes/$GLOBALS[thename]/images/right_bkg.gif\" cellpadding=\"0\" cellspacing=\"0\">\n"
        ."<tr>\n"
        ."<td valign=\"top\" height=\"13\" colspan=\"2\">\n"
        ."<div align=\"left\"><img src=\"themes/$GLOBALS[thename]/images/corner_top_right.gif\" width=\"15\" height=\"13\"></div>\n"
        ."</td>\n"
        ."<td rowspan=\"3\" background=\"themes/$GLOBALS[thename]/images/orange.gif\" width=\"1%\"><img src=\"themes/$GLOBALS[thename]/images/blank.gif\" width=\"2\" height=\"100%\" alt=\"\" border=\"0\"></td>\n"
        ."</tr>\n"
        ."<tr>\n"
        ."<td width=\"8%\"><img src=\"themes/$GLOBALS[thename]/images/blank.gif\" width=\"10\" height=\"1\" alt=\"\" border=\"0\"></td>\n"
        ."<td width=\"91%\">$block[content]</td>\n"
        ."</tr>\n"
        ."<tr>\n"
        ."<td height=\"11\" colspan=\"2\" valign=\"bottom\">\n"
        ."<div align=\"left\"><img src=\"themes/$GLOBALS[thename]/images/corner_bottom_right.gif\" width=\"11\" height=\"11\"></div>\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>";

}

if($block['position'] == 'c') {
    echo "<font class=\"pn-normal\">$block[content]</font>";
    }
}

?>
