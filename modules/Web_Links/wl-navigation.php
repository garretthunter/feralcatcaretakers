<?php
// File: $Id: wl-navigation.php,v 1.7 2002/11/30 21:45:14 skooter Exp $ $Name:  $
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
// 11-30-2001:ahumphr - created file as part of modularistation

/**
 * index
 * Display the main links categories
 */
function index()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");
    $mainlink = 0;

    if (!pnSecAuthAction(0, 'Web Links::', '::', ACCESS_READ)) {
        echo _WEBLINKSNOAUTH;
        include 'footer.php';
        exit;
    }

    menu($mainlink);

    $column = &$pntable['links_categories_column'];
    $result=$dbconn->Execute("select $column[cat_id], $column[title], $column[cdescription]
                              from $pntable[links_categories]
                              WHERE $column[parent_id]=0
                              ORDER BY $column[title]");
    $numcats = $result->PO_RecordCount();
    if ($numcats == 0) {
        echo "<b>"._LINKSNOCATS."</b>";
        include 'footer.php';
    } else {

        OpenTable();

        echo "<center><font class=\"pn-title\">"._LINKSMAINCAT."</font></center><br />";
        echo "<table border=\"0\" cellspacing=\"10\" cellpadding=\"0\" align=\"center\" width=\"98%\"><tr>";

        $count = 0;
        $dum = 0;
        while(list($cat_id, $title, $cdescription) = $result->fields)
        {
            $result->MoveNext();
    		/* Hide this web link if have no access to it */
            if (!pnSecAuthAction(0, 'Web Links::Category', "$title::$cat_id", ACCESS_READ)) {
    			continue;
    		}
            $cnumrows = CountSubLinks($cat_id);

            echo "<td valign=\"top\" width=\"50%\">"
               ."<img src=\"modules/".$GLOBALS['name']."/images/folder.gif\">&nbsp;&nbsp;"
    	   ."<b><a class=\"pn-title\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=$cat_id\">".pnVarPrepForDisplay($title)."</b></a>"
                   ." ($cnumrows)"
               ."</font>";

            if ($cdescription) {
                echo "<br /><font class=\"pn-normal\">".pnVarPrepHTMLDisplay($cdescription)."</font>";
            }
            categorynewlinkgraphic($cat_id);
    	echo "<br />";

            $column = &$pntable['links_categories_column'];
            $result2 = $dbconn->Execute("SELECT $column[cat_id], $column[title] FROM $pntable[links_categories] WHERE $column[parent_id]=".pnVarPrepForStore($cat_id)." ORDER BY $column[title]");

            $space = 0;
            while(list($scat_id, $stitle) = $result2->fields) {

                $result2->MoveNext();
                #if ($space>0) {
                #    echo "<br />";
                #}
                echo "<font class=\"pn-normal\">&nbsp;&nbsp;---&nbsp;"
                	    ."<img src=\"modules/".$GLOBALS['name']."/images/folder.gif\">&nbsp;&nbsp;"
                        ."<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewlink&amp;cid=$scat_id\">".pnVarPrepForDisplay($stitle)."</a>"
                    ."</font>";

                subcategorynewlinkgraphic($scat_id);
    	    //echo "|&nbsp;";
    	    echo "<BR />";
                $space++;
            }

            if ($count<1) {
                 echo "</td><td>&nbsp;&nbsp;</td>";
                 $dum = 1;
            }
            $count++;

            if ($count==2) {
                echo "</td></tr><tr>";
                $count = 0;
                $dum = 0;
            }
        } //While
        if ($dum == 1) {
            echo "</tr></table>";
        } elseif ($dum == 0) {
            echo "<td></td></tr></table>";
        }

         $result=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links]");
        list($numrows) = $result->fields;

        $result=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_categories]");
        list($catnum) = $result->fields;

        echo "<br /><br /><center><font class=\"pn-sub\">"._THEREARE." <b>$numrows</b> "._LINKS." "._AND." <b>$catnum</b> "._CATEGORIES." "._INDB."</font></center>";
        CloseTable();
        include("footer.php");
    }
}

/**
 * menu
 * builds the standard navigation menu
 * @param mainlink  integer switch. 1 means show _LINKSMAIN, 0 not.
 */
function menu($mainlink) {
    $query = pnVarCleanFromInput('query');

    OpenTable();
    echo "<center><a class=\"pn-logo\"  href=\"".$GLOBALS['modurl']."\">".pnConfigGetVar('sitename')." -- "._LINKPAGETITLE."</a><br />";
    echo "<form action=\"".$GLOBALS['modurl']."&amp;req=search&amp;query=".pnVarPrepForDisplay($query)."\" method=\"post\">"
      ."<font class=\"pn-normal\"><input type=\"text\" size=\"25\" name=\"query\"> "
        ."<input type=\"submit\" value=\""._SEARCH."\">"
      ."</font>"
    ."</form>";
    echo "<font class=\"pn-normal\">[ ";
    if ($mainlink>0) {
      echo "<a class=\"pn-normal\"  href=\"".$GLOBALS['modurl']."\">"._LINKSMAIN."</a> | ";
    }
    if (pnSecAuthAction(0, 'Web Links::Category', '::', ACCESS_READ)) {
        echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=AddLink\">"._ADDLINK."</a>";
    }
    echo " | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=NewLinks\">"._NEW."</a>"
    ." | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=MostPopular\">"._POPULAR."</a>"
    ." | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=TopRated\">"._TOPRATED."</a>"
    ." | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=RandomLink\" target=\"new\">"._RANDOM."</a> ]"
    ."</font></center>";
    CloseTable();
}

?>