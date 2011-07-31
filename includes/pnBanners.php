<?php
// File: $Id: pnBanners.php,v 1.2 2002/11/30 12:28:57 magicx Exp $
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
// Purpose of file: Display banners
// ----------------------------------------------------------------------

/**
 * Function to display banners in all pages
 */

function pnBannerDisplay($type=0)
{
    // test on config settings
    if (pnConfigGetVar('banners') != 1) return '&nbsp;';

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['banner_column'];
    $bresult = $dbconn->Execute("SELECT count(*) AS count FROM $pntable[banner]
								WHERE $column[type] = $type");
    list($numrows) = $bresult->fields;
    // we no longer need this, free the resources
    $bresult->Close();

    /* Get a random banner if exist any. */
    /* More efficient random stuff, thanks to Cristian Arroyo from http://www.planetalinux.com.ar */

    if ($numrows>1) {
        $numrows = $numrows-1;
        mt_srand((double)microtime()*1000000);
        $bannum = mt_rand(0, $numrows);
    } else {
        $bannum = 0;
    }

    $column = &$pntable['banner_column'];
    $query = buildSimpleQuery ('banner', array ('bid', 'imageurl'), "$column[type] = $type", '', 1, $bannum);
    $bresult2 = $dbconn->Execute($query);
    list($bid, $imageurl) = $bresult2->fields;
    // we no longer need this, free the resources
    $bresult2->Close();

    $myIP = pnConfigGetVar('myIP');

    $myhost = getenv("REMOTE_ADDR");

    if ($myIP == $myhost) {
        // do nothing
    } else {
        $dbconn->Execute("UPDATE $pntable[banner]
                        SET $column[impmade]=$column[impmade]+1
                        WHERE $column[bid]=".pnVarPrepForStore($bid)."");
    }

    if ($numrows > 0) {
        $aborrar = $dbconn->Execute("SELECT $column[cid],$column[imptotal],
                                          $column[impmade], $column[clicks],
                                          $column[date]
                                   FROM $pntable[banner]
                                   WHERE $column[bid]=".pnVarPrepForStore($bid)."");
        list($cid, $imptotal, $impmade, $clicks, $date) = $aborrar->fields;
        $aborrar->Close();

        /* Check if this impression is the last one and print the banner */
        if ($imptotal == $impmade) {

            $column = &$pntable['bannerfinish_column'];
            $dbconn->Execute("INSERT INTO $pntable[bannerfinish]
                            ( $column[bid], $column[cid], $column[impressions], $column[clicks], $column[datestart], $column[dateend] )
                            VALUES (NULL, '".pnVarPrepForStore($cid)."', '".pnVarPrepForStore($impmade)."', '".pnVarPrepForStore($clicks)."', '".pnVarPrepForStore($date)."', now())");
            $dbconn->Execute("DELETE FROM $pntable[banner] WHERE $column[bid]=".pnVarPrepForStore($bid)."");
        }
if ($type == 1 or $type ==2  or $type==0){
        echo"<a href=\"banners.php?op=click&amp;bid=$bid\" target=\"_blank\"><img src=\"$imageurl\" border=\"0\" alt=\""._CLICK."\"></a>";
}
else
{
   $content ="<a href=\"banners.php?op=click&amp;bid=$bid\" target=\"_blank\"><img src=\"$imageurl\" border=\"0\" alt=\""._CLICK."\"></a>";
return $content;
}
    }
}


?>