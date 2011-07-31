<?php
// File: $Id: wl-linkdetails.php,v 1.5 2002/11/10 23:30:09 skooter Exp $ $Name:  $
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
// 10-15-2002:skooter      - Cross Site Scripting security fixes and also using 
//                           pnAPI for displaying data.

/**
 * @usedby index
 */
function viewlinkcomments($lid, $ttitle) {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include("header.php");
            if (!(pnSecAuthAction(0, 'Web Links::', '::', ACCESS_COMMENT))) {
                echo _WEBLINKSNOAUTH;
                include 'footer.php';
                return;
            }

    menu(1);
    echo "<br />";
    $column = &$pntable['links_votedata_column'];
    $result=$dbconn->Execute("SELECT $column[ratinguser], $column[rating], $column[ratingcomments], $column[ratingtimestamp] FROM $pntable[links_votedata] WHERE $column[ratinglid]=".(int)pnVarPrepForStore($lid)." AND $column[ratingcomments] != '' ORDER BY $column[ratingtimestamp] DESC");
    $totalcomments = $result->PO_RecordCount();
    $displaytitle = str_replace ("_", " ", $ttitle);
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._LINKPROFILE.": ".pnVarPrepForDisplay($displaytitle)."</b></font><br /><br />";
    linkinfomenu($lid, $ttitle);
    echo "<br /><br /><br />"._TOTALOF." ".pnVarPrepForDisplay($totalcomments)." "._COMMENTS."</font></center><br />"
    ."<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"450\">";
    $x=0;
    while(list($ratinguser, $rating, $ratingcomments, $ratingtimestamp)=$result->fields) {

        $result->MoveNext();
        /* Individual user information */
        $column = &$pntable['links_votedata_column'];
        $result2=$dbconn->Execute("SELECT SUM($column[rating]), COUNT(*) FROM $pntable[links_votedata] WHERE $column[ratinguser]='".pnVarPrepForStore($ratinguser)."'");
        list($useravgrating, $usertotalcomments)=$result2->fields;
        $useravgrating = $useravgrating / $usertotalcomments;
        $useravgrating = number_format($useravgrating, 1);
           echo "<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\">"
               ."<font class=\"pn-normal\"><b> "._USER.": </b><a class=\"pn-normal\" href=\"" . pnGetBaseURL() . "user.php?op=userinfo&amp;uname=".pnVarPrepForDisplay($ratinguser)."\">".pnVarPrepForDisplay($ratinguser)."</a></font>"
           ."</td>"
           ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\">"
           ."<font class=\"pn-normal\"><b>"._RATING.": </b>".pnVarPrepForDisplay($rating)."</font>"
           ."</td>"
           ."<td bgcolor=\"".$GLOBALS['bgcolor2']."\" align=\"right\">"
               ."<font class=\"pn-normal\">".pnVarPrepForDisplay($ratingtimestamp)."</font>"
           ."</td>"
           ."</tr>"
           ."<tr>"
           ."<td valign=\"top\">"
           ."<font class=\"pn-sub\">"._USERAVGRATING.": ".pnVarPrepForDisplay($useravgrating)."</font>"
           ."</td>"
           ."<td valign=\"top\" colspan=\"2\">"
           ."<font class=\"pn-sub\">"._NUMRATINGS.": ".pnVarPrepForDisplay($usertotalcomments)."</font>"
           ."</td>"
           ."</tr>"
               ."<tr>"
           ."<td colspan=\"3\">"
           ."<font class=\"pn-normal\">";
        if (pnSecAuthAction(0, 'Web Links::', '::', ACCESS_ADMIN)) {
            echo "<a class=\"pn-normal\" href=\"admin.php?module=".$GLOBALS['name']."&op=LinksModLink&amp;lid=$lid\"><img src=\"modules/".$GLOBALS['name']."/images/editicon.gif\" border=\"0\" alt=\""._EDITTHISLINK."\"></a>";
        }
        echo " ".pnVarPrepForDisplay($ratingcomments)."</font>"
            ."<br /><br /><br /></td></tr>";
        $x++;
    }
    echo "</table><br /><br /><center>";
    linkfooter($lid,$ttitle);
    echo "</center>";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index
 */
function viewlinkdetails($lid, $ttitle) {

    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $useoutsidevoting = pnConfigGetVar('useoutsidevoting');
    $anonymous = pnConfigGetVar('anonymous');
    $detailvotedecimal = pnConfigGetVar('detailvotedecimal');
    $anonweight = pnConfigGetVar('anonweight');
    $outsideweight = pnConfigGetVar('anonweight');

    menu(1);
    $column = &$pntable['links_votedata_column'];
    $voteresult = $dbconn->Execute("SELECT $column[rating], $column[ratinguser],
                                  $column[ratingcomments] 
                                  FROM $pntable[links_votedata]
                                  WHERE $column[ratinglid]=".(int)pnVarPrepForStore($lid)."");
    $totalvotesDB = $voteresult->PO_RecordCount();
    $anonvotes = 0;
    $anonvoteval = 0;
    $outsidevotes = 0;
    $outsidevoteeval = 0;
    $regvoteval = 0;
    $topanon = 0;
    $bottomanon = 11;
    $topreg = 0;
    $bottomreg = 11;
    $topoutside = 0;
    $bottomoutside = 11;
    $avv = array(0,0,0,0,0,0,0,0,0,0,0);
    $rvv = array(0,0,0,0,0,0,0,0,0,0,0);
    $ovv = array(0,0,0,0,0,0,0,0,0,0,0);
    $truecomments = $totalvotesDB;
    while(list($ratingDB, $ratinguserDB, $ratingcommentsDB) = $voteresult->fields) {

        $voteresult->MoveNext();
        if ($ratingcommentsDB==""){
            $truecomments--;
        }
        if ($ratinguserDB==pnConfigGetVar("anonymous")) {
            $anonvotes++;
            $anonvoteval += $ratingDB;
        }
        if (isset($useoutsidevoting) && $useoutsidevoting == 1) {
            if ($ratinguserDB=='outside') {
                $outsidevotes++;
                $outsidevoteval += $ratingDB;
            }
        } else {
            $outsidevotes = 0;
        }
        if ($ratinguserDB != pnConfigGetVar('anonymous') && $ratinguserDB!="outside") {
            $regvoteval += $ratingDB;
        }
        if ($ratinguserDB != pnConfigGetVar('anonymous') && $ratinguserDB!="outside") {
            if ($ratingDB > $topreg) {
                $topreg = $ratingDB;
            }
            if ($ratingDB < $bottomreg) {
                $bottomreg = $ratingDB;
            }
            for ($rcounter=1; $rcounter<11; $rcounter++) {
                if ($ratingDB==$rcounter) {
                    $rvv[$rcounter]++;
                }
            }
        }
        if ($ratinguserDB==pnConfigGetVar("anonymous")) {
            if ($ratingDB > $topanon) {
                $topanon = $ratingDB;
            }
            if ($ratingDB < $bottomanon) {
                $bottomanon = $ratingDB;
            }
            for ($rcounter=1; $rcounter<11; $rcounter++) {
                if ($ratingDB==$rcounter) {
                    $avv[$rcounter]++;
                }
            }
        }
        if ($ratinguserDB=="outside") {
            if ($ratingDB > $topoutside) {
                $topoutside = $ratingDB;
            }
            if ($ratingDB < $bottomoutside) {
                $bottomoutside = $ratingDB;
            }
            for ($rcounter=1; $rcounter<11; $rcounter++) {
                if ($ratingDB==$rcounter) {
                    $ovv[$rcounter]++;
                }
            }
        }
    }
    $regvotes = $totalvotesDB - $anonvotes - $outsidevotes;
    $avgRU = 0;
    $avgAU = 0;
    $avgOU = 0;
    if ($totalvotesDB == 0) {
        $finalrating = 0;
    } else if ($anonvotes == 0 && $regvotes == 0) {
        /* Figure Outside Only Vote */
        $finalrating = $outsidevoteval / $outsidevotes;
        $finalrating = number_format($finalrating, $detailvotedecimal);
        $avgOU = $outsidevoteval / $totalvotesDB;
        $avgOU = number_format($avgOU, $detailvotedecimal);
    } else if ($outsidevotes == 0 && $regvotes == 0) {
        /* Figure Anon Only Vote */
        $finalrating = $anonvoteval / $anonvotes;
        $finalrating = number_format($finalrating, $detailvotedecimal);
        $avgAU = $anonvoteval / $totalvotesDB;
        $avgAU = number_format($avgAU, $detailvotedecimal);
    } else if ($outsidevotes == 0 && $anonvotes == 0) {
        /* Figure Reg Only Vote */
        $finalrating = $regvoteval / $regvotes;
        $finalrating = number_format($finalrating, $detailvotedecimal);
        $avgRU = $regvoteval / $totalvotesDB;
        $avgRU = number_format($avgRU, $detailvotedecimal);
    } else if ($regvotes == 0 && $useoutsidevoting == 1 && $outsidevotes != 0 && $anonvotes != 0 ) {
        /* Figure Reg and Anon Mix */
        $avgAU = $anonvoteval / $anonvotes;
        $avgOU = $outsidevoteval / $outsidevotes;
        if ($anonweight > $outsideweight ) {
            /* Anon is 'standard weight' */
            $newimpact = $anonweight / $outsideweight;
            $impactAU = $anonvotes;
            $impactOU = $outsidevotes / $newimpact;
            $finalrating = ((($avgOU * $impactOU) + ($avgAU * $impactAU)) / ($impactAU + $impactOU));
            $finalrating = number_format($finalrating, $detailvotedecimal);
        } else {
            /* Outside is 'standard weight' */
            $newimpact = $outsideweight / $anonweight;
            $impactOU = $outsidevotes;
            $impactAU = $anonvotes / $newimpact;
            $finalrating = ((($avgOU * $impactOU) + ($avgAU * $impactAU)) / ($impactAU + $impactOU));
            $finalrating = number_format($finalrating, $detailvotedecimal);
        }
    } else {
        /* REG User vs. Anonymous vs. Outside User Weight Calutions */
        $impact = $anonweight;
        $outsideimpact = $outsideweight;
        if ($regvotes == 0) {
            $avgRU = 0;
        } else {
            $avgRU = $regvoteval / $regvotes;
        }
        if ($anonvotes == 0) {
            $avgAU = 0;
        } else {
            $avgAU = $anonvoteval / $anonvotes;
        }
        if ($outsidevotes == 0 ) {
            $avgOU = 0;
        } else {
            $avgOU = $outsidevoteval / $outsidevotes;
        }
        $impactRU = $regvotes;
        $impactAU = $anonvotes / $impact;
        $impactOU = $outsidevotes / $outsideimpact;
        $finalrating = (($avgRU * $impactRU) + ($avgAU * $impactAU) + ($avgOU * $impactOU)) / ($impactRU + $impactAU + $impactOU);
        $finalrating = number_format($finalrating, $detailvotedecimal);
    }
    if (!isset($avgOU) || $avgOU == 0 || $avgOU == "") {
        $avgOU = "";
    } else {
        $avgOU = number_format($avgOU, $detailvotedecimal);
    }
    if ($avgRU == 0 || $avgRU == "") {
        $avgRU = "";
    } else {
        $avgRU = number_format($avgRU, $detailvotedecimal);
    }
    if (!isset($avgAU) || $avgAU == 0 || $avgAU == "") {
        $avgAU = "";
    } else {
        $avgAU = number_format($avgAU, $detailvotedecimal);
    }
    if ($topanon == 0) $topanon = "";
    if ($bottomanon == 11) $bottomanon = "";
    if ($topreg == 0) $topreg = "";
    if ($bottomreg == 11) $bottomreg = "";
    if ($topoutside == 0) $topoutside = "";
    if ($bottomoutside == 11) $bottomoutside = "";
    $totalchartheight = 70;
    $chartunits = $totalchartheight / 10;
    $avvper     = array(0,0,0,0,0,0,0,0,0,0,0);
    $rvvper         = array(0,0,0,0,0,0,0,0,0,0,0);
    $ovvper         = array(0,0,0,0,0,0,0,0,0,0,0);
    $avvpercent     = array(0,0,0,0,0,0,0,0,0,0,0);
    $rvvpercent     = array(0,0,0,0,0,0,0,0,0,0,0);
    $ovvpercent     = array(0,0,0,0,0,0,0,0,0,0,0);
    $avvchartheight = array(0,0,0,0,0,0,0,0,0,0,0);
    $rvvchartheight = array(0,0,0,0,0,0,0,0,0,0,0);
    $ovvchartheight = array(0,0,0,0,0,0,0,0,0,0,0);
    $avvmultiplier = 0;
    $rvvmultiplier = 0;
    $ovvmultiplier = 0;
    for ($rcounter=1; $rcounter<11; $rcounter++) {
        if ($anonvotes != 0) $avvper[$rcounter] = $avv[$rcounter] / $anonvotes;
        if ($regvotes != 0) $rvvper[$rcounter] = $rvv[$rcounter] / $regvotes;
        if ($outsidevotes != 0) $ovvper[$rcounter] = $ovv[$rcounter] / $outsidevotes;
        $avvpercent[$rcounter] = number_format($avvper[$rcounter] * 100, 1);
        $rvvpercent[$rcounter] = number_format($rvvper[$rcounter] * 100, 1);
        $ovvpercent[$rcounter] = number_format($ovvper[$rcounter] * 100, 1);
        if ($avv[$rcounter] > $avvmultiplier) $avvmultiplier = $avv[$rcounter];
        if ($rvv[$rcounter] > $rvvmultiplier) $rvvmultiplier = $rvv[$rcounter];
        if ($ovv[$rcounter] > $ovvmultiplier) $ovvmultiplier = $ovv[$rcounter];
    }
    if ($avvmultiplier != 0) $avvmultiplier = 10 / $avvmultiplier;
    if ($rvvmultiplier != 0) $rvvmultiplier = 10 / $rvvmultiplier;
    if ($ovvmultiplier != 0) $ovvmultiplier = 10 / $ovvmultiplier;
    for ($rcounter=1; $rcounter<11; $rcounter++) {
        $avvchartheight[$rcounter] = ($avv[$rcounter] * $avvmultiplier) * $chartunits;
        $rvvchartheight[$rcounter] = ($rvv[$rcounter] * $rvvmultiplier) * $chartunits;
        $ovvchartheight[$rcounter] = ($ovv[$rcounter] * $ovvmultiplier) * $chartunits;
        if ($avvchartheight[$rcounter]==0) $avvchartheight[$rcounter]=1;
        if ($rvvchartheight[$rcounter]==0) $rvvchartheight[$rcounter]=1;
        if ($ovvchartheight[$rcounter]==0) $ovvchartheight[$rcounter]=1;
    }
    $transfertitle = ereg_replace ("_", " ", $ttitle);
    $displaytitle = $transfertitle;
    $column = &$pntable['links_links_column'];
    $res = $dbconn->Execute("SELECT $column[name], $column[email], $column[description]
                           FROM $pntable[links_links]
                           WHERE $column[lid]='".pnVarPrepForStore($lid)."'");
    list($name, $email, $description) = $res->fields;

    OpenTable();
    echo "<center><font class=\"pn-normal\">"._LINKPROFILE.":
        ".pnVarPrepForDisplay($displaytitle)."</font><br /><br />";
    linkinfomenu($lid, $ttitle);
    echo "<br /><br />"._LINKRATINGDET."<br />"
        .""._TOTALVOTES." ".pnVarPrepForDisplay($totalvotesDB)."<br />"
        .""._OVERALLRATING.": ".pnVarPrepForDisplay($finalrating)."</center><br /><br />"
        ."<table align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"455\">"
        ."<tr><td colspan=\"2\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
        ."<font class=\"pn-normal\"><b>"._REGISTEREDUSERS."</b></font>"
        ."</td></tr>"
        ."<tr>"
        ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\">"
        ."<font class=\"pn-normal\">"._NUMBEROFRATINGS.": ".pnVarPrepForDisplay($regvotes)."</font>"
        ."</td>"
        ."<td rowspan=\"5\" width=\"200\">";
    if ($regvotes==0) {
        echo "<center><font class=\"pn-normal\">"._NOREGUSERSVOTES."</font></center>";
    } else {
        echo "<table border=\"1\" width=\"200\">"
            ."<tr>"
            ."<td valign=\"top\" align=\"center\" colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-sub\">"._BREAKDOWNBYVAL."</font></td>"
            ."</tr>"
            ."<tr>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[1] "._LVOTES." ($rvvpercent[1]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[1]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[2] "._LVOTES." ($rvvpercent[2]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[2]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[3] "._LVOTES." ($rvvpercent[3]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[3]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[4] "._LVOTES." ($rvvpercent[4]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[4]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[5] "._LVOTES." ($rvvpercent[5]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[5]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[6] "._LVOTES." ($rvvpercent[6]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[6]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[7] "._LVOTES." ($rvvpercent[7]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[7]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[8] "._LVOTES." ($rvvpercent[8]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[8]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[9] "._LVOTES." ($rvvpercent[9]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[9]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$rvv[10] "._LVOTES." ($rvvpercent[10]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$rvvchartheight[10]\"></td>"
            ."</tr>"
            ."<tr><td colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
            ."<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"200\"><tr>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">1</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">2</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">3</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">4</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">5</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">6</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">7</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">8</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">9</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">10</font></td>"
            ."</tr></table>"
            ."</td></tr></table>";
    }
    echo "</td>"
        ."</tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LINKRATING.": $avgRU</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._HIGHRATING.": $topreg</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LOWRATING.": $bottomreg</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._NUMOFCOMMENTS.": ".pnVarPrepForDisplay($truecomments)."</font></td></tr>"
        ."<tr><td></td></tr>"
        ."<tr><td valign=\"top\" colspan=\"2\"><font class=\"pn-sub\"><br /><br />"._WEIGHNOTE." $anonweight "._TO." 1.</font></td></tr>"
        ."<tr><td colspan=\"2\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\"><b>"._UNREGISTEREDUSERS."</b></font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._NUMBEROFRATINGS.": $anonvotes</font></td>"
        ."<td rowspan=\"5\" width=\"200\">";
    if ($anonvotes==0) {
        echo "<center><font class=\"pn-normal\">"._NOUNREGUSERSVOTES."</font></center>";
    } else {
        echo "<table border=\"1\" width=\"200\">"
            ."<tr>"
            ."<td valign=\"top\" align=\"center\" colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-sub\">"._BREAKDOWNBYVAL."</font></td>"
            ."</tr>"
            ."<tr>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[1] "._LVOTES." ($avvpercent[1]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[1]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[2] "._LVOTES." ($avvpercent[2]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[2]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[3] "._LVOTES." ($avvpercent[3]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[3]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[4] "._LVOTES." ($avvpercent[4]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[4]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[5] "._LVOTES." ($avvpercent[5]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[5]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[6] "._LVOTES." ($avvpercent[6]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[6]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[7] "._LVOTES." ($avvpercent[7]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[7]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[8] "._LVOTES." ($avvpercent[8]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[8]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[9] "._LVOTES." ($avvpercent[9]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[9]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$avv[10] "._LVOTES." ($avvpercent[10]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$avvchartheight[10]\"></td>"
            ."</tr>"
            ."<tr><td colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
            ."<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"200\"><tr>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">1</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">2</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">3</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">4</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">5</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">6</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">7</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">8</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">9</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">10</font></td>"
            ."</tr></table>"
            ."</td></tr></table>";
    }
    echo "</td>"
        ."</tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LINKRATING.": $avgAU</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._HIGHRATING.": $topanon</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LOWRATING.": $bottomanon</font></td></tr>"
        ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">&nbsp;</font></td></tr>";
    if ($useoutsidevoting == 1) {
        echo "<tr><td valign=top colspan=\"2\"><font class=\"pn-sub\"><br><br>"._WEIGHOUTNOTE." $outsideweight "._TO." 1.</font></td></tr>"
            ."<tr><td colspan=\"2\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\"><b>"._OUTSIDEVOTERS."</b></font></td></tr>"
            ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._NUMBEROFRATINGS.": $outsidevotes</font></td>"
            ."<td rowspan=\"5\" width=\"200\">";
        if ($outsidevotes==0) {
            echo "<center><font class=\"pn-sub\">"._NOOUTSIDEVOTES."</font></center>";
        } else {
            echo "<table border=\"1\" width=\"200\">"
                ."<tr>"
            ."<td valign=\"top\" align=\"center\" colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-sub\">"._BREAKDOWNBYVAL."</font></td>"
            ."</tr>"
            ."<tr>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[1] "._LVOTES." ($ovvpercent[1]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[1]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[2] "._LVOTES." ($ovvpercent[2]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[2]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[3] "._LVOTES." ($ovvpercent[3]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[3]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[4] "._LVOTES." ($ovvpercent[4]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[4]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[5] "._LVOTES." ($ovvpercent[5]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[5]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[6] "._LVOTES." ($ovvpercent[6]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[6]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[7] "._LVOTES." ($ovvpercent[7]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[7]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[8] "._LVOTES." ($ovvpercent[8]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[8]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[9] "._LVOTES." ($ovvpercent[9]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[9]\"></td>"
            ."<td bgcolor=\"".$GLOBALS['bgcolor1']."\" valign=\"bottom\"><img border=\"0\" alt=\"$ovv[10] "._LVOTES." ($ovvpercent[10]% "._LTOTALVOTES.")\" src=\"modules/".$GLOBALS['name']."/images/blackpixel.gif\" width=\"15\" height=\"$ovvchartheight[10]\"></td>"
            ."</tr>"
            ."<tr><td colspan=\"10\" bgcolor=\"".$GLOBALS['bgcolor2']."\">"
            ."<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"200\"><tr>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">1</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">2</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">3</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">4</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">5</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">6</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">7</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">8</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">9</font></td>"
            ."<td width=\"10%\" valign=\"bottom\" align=\"center\"><font class=\"pn-sub\">10</font></td>"
            ."</tr></table>"
            ."</td></tr></table>";
        }
        echo "</td>"
            ."</tr>"
            ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LINKRATING.": $avgOU</font></td></tr>"
            ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">"._HIGHRATING.": $topoutside</font></td></tr>"
            ."<tr><td bgcolor=\"".$GLOBALS['bgcolor2']."\"><font class=\"pn-normal\">"._LOWRATING.": $bottomoutside</font></td></tr>"
            ."<tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\"><font class=\"pn-normal\">&nbsp;</font></td></tr>";
    }
    echo "</table><br /><br /><center>";
    linkfooter($lid,$ttitle);
    echo "</center>";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index
 */
function outsidelinksetup($lid)
{
    $sitename = pnConfigGetVar('sitename');

    include("header.php");

    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._PROMOTEYOURSITE."</b></font></center><br /><br /><font class=\"pn-normal\">

    "._PROMOTE01."<br /><br />

    <b>1) "._TEXTLINK."</b><br /><br />

    "._PROMOTE02."<br /><br />
    <center><a class=\"pn-normal\" href=\"" . pnGetBaseURL() . "".$GLOBALS['modurl']."&amp;req=ratelink&amp;lid=$lid\">"._RATETHISSITE." @ $sitename</a></center><br /><br />
    <center>"._HTMLCODE1."</center><br />
    <center><i>&lt;a href=\"" . pnGetBaseURL() . "".$GLOBALS['modurl']."&amp;req=ratelink&lid=$lid\"&gt;"._RATETHISSITE."&lt;/a&gt;</i></center>
    <br /><br />
    "._THENUMBER." \"$lid\" "._IDREFER."<br /><br />

    <b>2) "._BUTTONLINK."</b><br /><br />

    "._PROMOTE03."<br /><br />

    <center>
	<form action=\"" . pnGetBaseURL() . $GLOBALS['modurl']."\" method=\"post\">
	<input type=\"hidden\" name=\"lid\" value=\"$lid\">\n
    <input type=\"hidden\" name=\"req\" value=\"ratelink\">\n
    <input type=\"submit\" value=\""._RATEIT."\">\n
    </form>\n
    </center>

    <center>"._HTMLCODE2."</center><br /><br />

    <table border=\"0\" align=\"center\"><tr><td align=\"left\"><font class=\"pn-normal\"><i>
    &lt;form action=\"" . pnGetBaseURL() . "".$GLOBALS['modurl']."\" method=\"post\"&gt;<br />\n
    &nbsp;&nbsp;&lt;input type=\"hidden\" name=\"lid\" value=\"$lid\"&gt;<br />\n
    &nbsp;&nbsp;&lt;input type=\"hidden\" name=\"req\" value=\"ratelink\"&gt;<br />\n
    &nbsp;&nbsp;&lt;input type=\"submit\" value=\""._RATEIT."\"&gt;<br />\n
    &lt;/form&gt;\n
    </i></font></td></tr></table>

    <br /><br />

    <b>3) "._REMOTEFORM."</b><br /><br />

    "._PROMOTE04."

    <center>
    <form method=\"post\" action=\"" . pnGetBaseURL() . "".$GLOBALS['modurl']."\">
    <table align=\"center\" border=\"0\" width=\"175\" cellspacing=\"0\" cellpadding=\"0\">
    <tr><td align=\"center\"><b>"._VOTE4THISSITE."</b></a></td></tr>
    <tr><td>
    <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">
    <tr><td valign=\"top\">
        <select name=\"rating\">
        <option selected>--</option>
    <option>10</option>
    <option>9</option>
    <option>8</option>
    <option>7</option>
    <option>6</option>
    <option>5</option>
    <option>4</option>
    <option>3</option>
    <option>2</option>
    <option>1</option>
    </select>
    </td><td valign=\"top\">
    <input type=\"hidden\" name=\"ratinglid\" value=\"$lid\">
        <input type=\"hidden\" name=\"ratinguser\" value=\"outside\">
        <input type=\"hidden\" name=\"req\" value=\"addrating\">
    <input type=\"submit\" value=\""._LINKVOTE."\">
    </td></tr></table>
    </td></tr></table></form>

    <br />"._HTMLCODE3."<br /><br /></center>

    <blockquote><i>
    &lt;form method=\"post\" action=\"" . pnGetBaseURL() . "".$GLOBALS['modurl']."\"&gt;<br />
    &lt;table align=\"center\" border=\"0\" width=\"175\" cellspacing=\"0\" cellpadding=\"0\"&gt;<br />
        &lt;tr&gt;&lt;td align=\"center\"&gt;&lt;b&gt;"._VOTE4THISSITE."&lt;/b&gt;&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;<br />
        &lt;tr&gt;&lt;td&gt;<br />
        &lt;table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"&gt;<br />
        &lt;tr&gt;&lt;td valign=\"top\"&gt;<br />
            &lt;select name=\"rating\"&gt;<br />
            &lt;option selected&gt;--&lt;/option&gt;<br />
        &lt;option&gt;10&lt;/option&gt;<br />
        &lt;option&gt;9&lt;/option&gt;<br />
        &lt;option&gt;8&lt;/option&gt;<br />
        &lt;option&gt;7&lt;/option&gt;<br />
        &lt;option&gt;6&lt;/option&gt;<br />
        &lt;option&gt;5&lt;/option&gt;<br />
        &lt;option&gt;4&lt;/option&gt;<br />
        &lt;option&gt;3&lt;/option&gt;<br />
        &lt;option&gt;2&lt;/option&gt;<br />
        &lt;option&gt;1&lt;/option&gt;<br />
        &lt;/select&gt;<br />
        &lt;/td&gt;&lt;td valign=\"top\"&gt;<br />
        &lt;input type=\"hidden\" name=\"ratinglid\" value=\"$lid\"&gt;<br />
            &lt;input type=\"hidden\" name=\"ratinguser\" value=\"outside\"&gt;<br />
            &lt;input type=\"hidden\" name=\"req\" value=\"addrating\"&gt;<br />
        &lt;input type=\"submit\" value=\""._LINKVOTE."\"&gt;<br />
        &lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;<br />
    &lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;<br />
    &lt;/form&gt;<br />
    </i></blockquote>
    <br /><br /><center>
    "._PROMOTE05."<br /><br />
    - $sitename "._STAFF."
    <br /><br /></center>";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index
 */
function brokenlink($lid) {
    include("header.php");

    if (pnUserLoggedIn()) {
        $ratinguser = pnUserGetVar('uname');
    } else {
        $ratinguser = pnConfigGetVar("anonymous");
    }
    menu(1);

    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._REPORTBROKEN."</b></font><br /><br /><br />";
    echo "<form action=\"".$GLOBALS['modurl']."\" method=\"post\">";
    echo "<font class=\"pn-normal\">";
    echo "<input type=\"hidden\" name=\"lid\" value=\"$lid\">";
    echo "<input type=\"hidden\" name=\"modifysubmitter\" value=\"$ratinguser\">";
    echo "<input type=\"hidden\" name=\"authid\" value=\"".pnSecGenAuthKey()."\">";
    echo ""._THANKSBROKEN."<br>"._SECURITYBROKEN."<br><br>";
    echo "<input type=\"hidden\" name=\"req\" value=\"brokenlinkS\"><input type=\"submit\" value=\""._REPORTBROKEN."\"></font></form></center>";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index
 */
function brokenlinkS($lid, $modifysubmitter)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        menu(1);
        OpenTable();
        echo _BADAUTHKEY;
        CloseTable();
        include 'footer.php';
        return;
    }

    if (pnUserLoggedIn()) {
        $ratinguser = pnUserGetVar('uname');
    } else {
        $ratinguser = pnConfigGetVar('anonymous');
    }

    $nextid = $dbconn->GenId($pntable['links_modrequest']);
    $column = &$pntable['links_modrequest_column'];
    $dbconn->Execute("INSERT INTO $pntable[links_modrequest] ($column[requestid], $column[lid], $column[modifysubmitter], $column[brokenlink]) VALUES ($nextid, ".(int)pnVarPrepForStore($lid).", '".pnVarPrepForStore($ratinguser)."', 1)");
    include("header.php");
    menu(1);

    OpenTable();
    echo "<br /><center>"._THANKSFORINFO."<br /><br />"._LOOKTOREQUEST."</center><br />";
    CloseTable();
    include("footer.php");
}

/**
 * @usedby index
 */
function modifylinkrequest($lid) {
    include("header.php");

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');

    if (pnUserLoggedIn()) {
        $ratinguser = pnUserGetVar('uname');
    } else {
        $ratinguser = $anonymous;
    }
    menu(1);

    OpenTable();
    $blocknow = 0;
//    if ($blockunregmodify == 1 && $ratinguser == $anonymous) {
    if ($ratinguser == $anonymous) {
        echo "<br /><br /><center>"._ONLYREGUSERSMODIFY."</center>";
        $blocknow = 1;
    }
    if ($blocknow != 1) {
        $column = &$pntable['links_links_column'];
        $result = $dbconn->Execute("SELECT $column[cat_id], 
                            $column[title], $column[url], 
                            $column[description] 
                            FROM $pntable[links_links] 
                            WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
        echo "<center><font class=\"pn-normal\"><b>"._REQUESTLINKMOD."</b></font><br /><font class=\"pn-normal\">";
        while(list($cid, $title, $url, $description) = $result->fields) {

            $result->MoveNext();
            echo "<form action=\"".$GLOBALS['modurl']."\" method=\"post\">"
                .""._LINKID.": <b>".pnVarPrepForDisplay($lid)."</b></center><br /><br /><br />"
                .""._LINKTITLE.":<br /><input type=\"text\" name=\"title\" value=\"".pnVarPrepForDisplay($title)."\" size=\"50\" maxlength=\"100\"><br /><br />"
                .""._URL.":<br /><input type=\"text\" name=\"url\" value=\"".pnVarPrepForDisplay($url)."\" size=\"75\" maxlength=\"254\"><br /><br />"
                .""._DESCRIPTION255.": <br /><textarea name=\"description\" cols=\"60\" rows=\"10\">".pnVarPrepHTMLDisplay($description)."</textarea><br /><br />";
            echo "<input type=\"hidden\" name=\"lid\" value=\"".pnVarPrepForDisplay($lid)."\">"
                ."<input type=\"hidden\" name=\"modifysubmitter\" value=\"".pnVarPrepForDisplay($ratinguser)."\">"
                .""._CATEGORY.": <select name=\"cat\">";
            echo CatList(0, $cid)."</select><br /><br />"
                ."<input type=\"hidden\" name=\"req\" value=\"modifylinkrequestS\">"
                ."<input type=\"hidden\" name=\"authid\" value=\"".pnSecGenAuthKey()."\">"
                ."<input type=\"submit\" value=\""._SENDREQUEST."\"></form>";
        }
    }
    CloseTable();
    include("footer.php");
}

/*
 * @usedby index
 */
function modifylinkrequestS($lid, $cat, $title, $url, $description, $modifysubmitter)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!pnSecConfirmAuthKey()) {
        include 'header.php';
        OpenTable();
        echo _BADAUTHKEY;
        CloseTable();
        include 'footer.php';
        return;
    }

    $anonymous = pnConfigGetVar('anonymous');

    if (pnUserLoggedIn()) {
        $ratinguser = pnUserGetVar('uname');
    } else {
        $ratinguser = $anonymous;
    }
    $blocknow = 0;
//    if ($blockunregmodify == 1 && $ratinguser == $anonymous) {
    if ($ratinguser == $anonymous) {
        include("header.php");
        menu(1);

        OpenTable();
        echo "<center><font class=\"pn-normal\">"._ONLYREGUSERSMODIFY."</font></center>";
        $blocknow = 1;
        CloseTable();
        include("footer.php");
    }
    if ($blocknow != 1) {
        $nextid = $dbconn->GenId($pntable['links_modrequest']);
        $column = &$pntable['links_modrequest_column'];
        $dbconn->Execute("INSERT INTO $pntable[links_modrequest] ($column[requestid], $column[lid], $column[cat_id], $column[title], $column[url], $column[description], $column[modifysubmitter], $column[brokenlink])
                        VALUES ($nextid, ".(int)pnVarPrepForStore($lid).", ".pnVarPrepForStore($cat).", '".pnVarPrepForStore($title)."', '".pnVarPrepForStore($url)."', '".pnVarPrepForStore($description)."', '".pnVarPrepForStore($ratinguser)."', 0)");
        include("header.php");
        menu(1);

        OpenTable();
        echo "<center><font class=\"pn-normal\">"._THANKSFORINFO." "._LOOKTOREQUEST."</font></center>";
        CloseTable();
        include("footer.php");
    }
}
?>