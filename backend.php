<?php // File: $Id: backend.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
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
// 06.20.02 ColdRolledSteel	Fixed bug 482633 (Display only homepage stories)
//				Fixed bug 449959 (Added image tags for RSS)
//				Fixed bug where language not displayed
//				Added webMaster, managingEditor tags
//                              Added $headline count so admin can control number
//				 of stories to be displayed
//				Added show_content to feed non-HTML content
//				Renamed from backend.php to rss_feed.php
//				Modules capitalized for early 0.711 naming convention

include 'includes/pnAPI.php';
pnInit();

// currently un-used - maybe in the future ?
//pnThemeLoad();

header("Content-Type: text/xml");


$title = pnVarPrepForDisplay(pnConfigGetVar('sitename'));
$link = pnVarPrepForDisplay(pnGetBaseURL());
$description = pnVarPrepForDisplay(pnConfigGetVar('backend_title'));
$backend_language = pnVarPrepForDisplay(pnConfigGetVar('backend_language'));
$headline_limit = 10; // Allow administrator to change how many headlines are selected
$webmaster = pnVarPrepForDisplay(pnConfigGetVar('adminmail'));
$managingeditor = "" ; // RSS Parsers sometimes use this, format: emailaddress (Full Name)
$image_url = $link.'images/'.pnVarPrepForDisplay(pnConfigGetVar('site_logo'));
$image_title = $title;  // RSS parsers usually use this for the ALT tag on the image
$image_link = $link;  // RSS parsers usually use this as the link when users click on the image

// show_content controls whether hometext is included in the RSS feed.  This can only be done
// for text-only.  RSS chokes on HTML....

$show_content = 0; // Decide if you want to include the hometext in the RSS feed (1=yes, 0=no)

// fixed bug 482633 (frontpage only) & also get hometext for display
// $sql = "SELECT pn_sid, pn_title FROM $pntable[stories] ORDER BY pn_sid DESC";

$sql = "SELECT pn_sid, pn_title, pn_ihome, pn_hometext FROM $pntable[stories] WHERE pn_ihome = 0 ORDER BY pn_sid DESC";
$result = $dbconn->SelectLimit($sql,$headline_limit);

/* fifers - no need for a count var.  just use a while loop */
// fifers - should we spit out an error XML doc?
if ($result === false) {
    echo "\n\n<font class=\"pn-normal\">An error occured</font>";
} else {
    echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n";
    echo "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n";
    echo " \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n\n";
    echo "<rss version=\"0.91\">\n\n";
    echo "<channel>\n";

    echo "<title>$title</title>\n";
    echo "<link>$link</link>\n";
    echo "<description>$description</description>\n";
    echo "<language>$backend_language</language>\n";

// Tried to make site searches work, but the Search module uses HTTP_POST/GET_VARS to determine
// whether to display results and I couldn't munge the RSS content enough to make it work
// reliably.  (This exercise is left to the advanced student. :*)

//   echo "<textinput>\n";
//   echo " <title>Search ".$title."</title>\n";
//   echo " <name>op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_stories=1&amp;Search</name>\n";
//   echo " <link>".$link."/modules.php</link>\n";
//   echo "</textinput>\n";

    echo "<image>\n";
    echo " <title>$image_title</title>\n";
    echo " <url>$image_url</url>\n";
    echo " <link>$image_link</link>\n";
    echo "</image>\n";
    echo "<webMaster>$webmaster</webMaster>\n";
    if ($managingeditor != "") {
	echo "<managingEditor>$managingeditor</managingEditor>\n";
    };

//    while(list($sid, $title) = $result->fields) {

    while(list($sid, $title,$ihome,$hometext) = $result->fields) {
        $title = pnVarPrepHTMLDisplay($title);
        $link = pnVarPrepForDisplay(pnGetBaseURL() . "modules.php?op=modload&name=News&file=article&sid=$sid");
	$content = pnVarPrepForDisplay($hometext);

        echo "<item>\n";
        echo "<title>$title</title>\n";
        echo "<link>$link</link>\n";
	if ($show_content) {
		echo "<description>\n";
		echo $content;
		echo "</description>\n";
        };
        echo "</item>\n";
        $result->MoveNext();
    }

    echo "</channel>\n";
    echo "</rss>\n";
}
?>