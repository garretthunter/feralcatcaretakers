<?php
// File: $Id: dl-util.php,v 1.8 2002/11/28 04:08:35 skooter Exp $ $Name:  $
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
// Purpose of file: function lib, routines used by many other functions
// ----------------------------------------------------------------------

/**
 * @usedby nothing
 */
function SearchForm() {
    echo "<form action=\"modules.php\" method=\"post\">"
    ."<input type=\"hidden\" name=\"op\" value=\"modload\">\n"
    ."<input type=\"hidden\" name=\"name\" value=\"".$GLOBALS['ModName']."\">\n"
    ."<input type=\"hidden\" name=\"file\" value=\"index\">\n"
    ."<input type=\"hidden\" name=\"req\" value=\"search\">\n"
    ."<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
    ."<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">"
    ."<tr><td><font class=\"pn-normal\"><input type=\"text\" size=\"25\" name=\"query\"> <input type=\"submit\" value=\""._SEARCH."\"></td></tr>"
    ."</table>"
    ."</form>";
}

/**
 * @usedby viewdownloaddetails
 */
function downloadinfomenu($lid, $ttitle) {

    echo "<br /><font class=\"pn-normal\">[ ";
    $cattitle = downloads_CatNameFromIID($lid);
    $itemsubmitter = downloads_ItemSubmitterFromLID($lid);

    if (pnUserLoggedIn()) {
        $itemuser = pnUserGetVar('uname');
    } else {
        $itemuser = pnConfigGetVar('anonymous');
    }

    if (downloads_authitem((downloads_ItemCIDFromLID($lid)), (downloads_ItemSIDFromLID($lid)), $lid, ACCESS_COMMENT) ) {
        echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownloadcomments&amp;lid=$lid&amp;ttitle=$ttitle\">"._DOWNLOADCOMMENTS."</a> | ";
    }

    /*echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownloaddetails&amp;lid=$lid&amp;ttitle=$ttitle\">"._ADDITIONALDET."</a> | ";*/
    echo "<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownloadeditorial&amp;lid=$lid&amp;ttitle=$ttitle\">"._EDITORREVIEW."</a>";

    if ((downloads_authitem((downloads_ItemCIDFromLID($lid)), (downloads_ItemSIDFromLID($lid)), $lid, ACCESS_EDIT)) || ($itemuser == $itemsubmitter)) {
        echo " | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=modifydownloadrequest&amp;lid=$lid\">"._MODIFY."</a>";
    }

    echo " | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=brokendownload&amp;lid=$lid\">"._REPORTBROKEN."</a> ]</font>"; // get->post??
}

/**
 * @usedby mostpopular, search, toprated, viewlink
 */
function detecteditorial($lid, $ttitle, $img) {

    if (!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_editorials_column'];
    $resulted2 = $dbconn->Execute("SELECT $column[adminid]
                                 FROM $pntable[downloads_editorials]
                                 WHERE $column[downloadid]=".(int)pnVarPrepForStore($lid)."");
    if (!$resulted2->EOF) {
        if ($img == 1) {
            echo "&nbsp;&nbsp;<a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownloadeditorial&amp;lid=$lid&amp;ttitle=$ttitle\"><img src=\"modules/".$GLOBALS['ModName']."/images/cool.gif\" alt=\""._EDITORIAL."\" border=\"0\"></a>";
        } else {
            echo " | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=viewdownloadeditorial&amp;lid=$lid&amp;ttitle=$ttitle\">"._EDITORIAL."</a>";
        }
    }
}

/**
 * @usedby mostpopular, search, toprated, viewlink
 */
function popgraphic($hits) {
    $popular = pnConfigGetVar('popular');
    if ($hits>=$popular) {
        echo "&nbsp;<img src=\"modules/".$GLOBALS['ModName']."/images/popular.gif\" alt=\""._POPULAR."\">";
    }
}

/**
 * @usedby search, viewdownload
 */
function convertorderbyin($orderby) {
    if ($orderby == "titleA") {
    $orderby = "pn_title ASC";
    }
    if ($orderby == "dateA") {
    $orderby = "pn_date ASC";
    }
    if ($orderby == "hitsA") {
    $orderby = "pn_hits ASC";
    }
    if ($orderby == "ratingA") {
    $orderby = "pn_ratingsummary ASC";
    }
    if ($orderby == "titleD") {
    $orderby = "pn_title DESC";
    }
    if ($orderby == "dateD") {
    $orderby = "pn_date DESC";
    }
    if ($orderby == "hitsD") {
    $orderby = "pn_hits DESC";
    }
    if ($orderby == "ratingD") {
    $orderby = "pn_ratingsummary DESC";
    }

    return $orderby;
}

/**
 *
 * @usedby search, viewdownload
 */
function convertorderbytrans($orderby)
{
    if(!isset($orderbyTrans)) {
    $orderbyTrans = '';
    }
    if ($orderby == "pn_hits ASC") {
    $orderbyTrans = ""._POPULARITY1."";
    }
    if ($orderby == "pn_hits DESC") {
    $orderbyTrans = ""._POPULARITY2."";
    }
    if ($orderby == "pn_title ASC") {
    $orderbyTrans = ""._TITLEAZ."";
    }
    if ($orderby == "pn_title DESC") {
    $orderbyTrans = ""._TITLEZA."";
    }
    if ($orderby == "pn_date ASC") {
    $orderbyTrans = ""._DDATE1."";
    }
    if ($orderby == "pn_date DESC") {
    $orderbyTrans = ""._DDATE2."";
    }
    if ($orderby == "pn_ratingsummary ASC") {
    $orderbyTrans = ""._RATING1."";
    }
    if ($orderby == "pn_ratingsummary DESC") {
    $orderbyTrans = ""._RATING2."";
    }

    return $orderbyTrans;
}

/**
 * @usedby viewlink, search,
 */
function convertorderbyout($orderby) {
    if ($orderby == "pn_title ASC") {
    $orderby = "titleA";
    }
    if ($orderby == "pn_date ASC") {
    $orderby = "dateA";
    }
    if ($orderby == "pn_hits ASC") {
    $orderby = "hitsA";
    }
    if ($orderby == "pn_downloadratingsummary ASC") {
    $orderby = "ratingA";
    }
    if ($orderby == "pn_title DESC") {
    $orderby = "titleD";
    }
    if ($orderby == "pn_date DESC") {
    $orderby = "dateD";
    }
    if ($orderby == "pn_hits DESC") {
    $orderby = "hitsD";
    }
    if ($orderby == "pn_downloadratingsummary DESC") {
    $orderby = "ratingD";
    }

    return $orderby;
}

/**
 * @usedby index, mostpopular, search, toprated, viewlink
 */
function visit($lid)
{

    if (!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    if (downloads_authitem((downloads_ItemCIDFromLID($lid)), (downloads_ItemSIDFromLID($lid)), $lid, ACCESS_READ) ) {
        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();

        $column = &$pntable['downloads_downloads_column'];
        $dbconn->Execute("UPDATE $pntable[downloads_downloads]
                    SET $column[hits]=$column[hits]+1
                    WHERE $column[lid]=".pnVarPrepForStore($lid)."");
        $result = $dbconn->Execute("SELECT $column[url]
                              FROM $pntable[downloads_downloads]
                              WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
        list($url) = $result->fields;
        Header("Location: ".$url);
    } else {
        Header("Location: /index.php");
    }
}


/**
 * @usedby index, search, viewlink
 */
function CountSubLinks($cid)
{
    if (!isset($cid) || !is_numeric($cid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $ct=0;
    $column = &$pntable['links_links_column'];
    $result=$dbconn->Execute("SELECT COUNT(*) FROM $pntable[links_links] WHERE $column[cat_id]=".(int)pnVarPrepForStore($cid)."");
    list($ct) = $result->fields;

    // Now get all child nodes
    $column = &$pntable['links_categories_column'];
    $result=$dbconn->Execute("SELECT $column[cat_id] FROM $pntable[links_categories] WHERE $column[parent_id]=".(int)pnVarPrepForStore($cid)."");
    while(list($sid)=$result->fields) {

        $result->MoveNext();
        $ct+=CountSubLinks($sid);
    }
  return $ct;
}

/* Link Graphics */

/**
 * categorynewlinkgraphic
 * @usedby index, viewlink
 */
function categorynewdownloadgraphic($cat)
{

    if (!isset($cat) || !is_numeric($cat)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['downloads_downloads_column'];
    $sql = "SELECT $column[date]
                FROM $pntable[downloads_downloads]
                WHERE $column[cid]=".(int)pnVarPrepForStore($cat)."
                ORDER BY $column[date] DESC";

    $newresult = $dbconn->SelectLimit($sql,1);
    list($time)=$newresult->fields;
    echo "&nbsp;";
// cocomp 2002/07/13 if (isset($time))
	if (isset($time)) {
    setlocale (LC_TIME, pnConfigGetVar('locale'));
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    $startdate = time();
    $count = 0;
    while ($count <= 7) {
        // $daysold = date("d-M-Y", $startdate);
        $daysold = ml_ftime(""._LINKSDATESTRING."", $startdate);
        if ("$daysold" == "$datetime") {
            if ($count<=1) {
                echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_1.gif\" alt=\""._DCATNEWTODAY."\">";
            }
            if ($count<=3 && $count>1) {
                echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_3.gif\" alt=\""._DCATLAST3DAYS."\">";
            }
            if ($count<=7 && $count>3) {
                echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_7.gif\" alt=\""._DCATTHISWEEK."\">";
            }
        }
        $count++;
        $startdate = (time()-(86400 * $count));
    }
	}
}

/**
 * newdownloadgraphic
 * @usedby mostpopular, search
 */
// cocomp 2002/07/13 made $datetime a de-referenced variable
function newdownloadgraphic(&$datetime, $time) {
    echo "&nbsp;";
    setlocale (LC_TIME, pnConfigGetVar('locale'));
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = ml_ftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    $startdate = time();
    $count = 0;
    while ($count <= 7) {
    // $daysold = date("d-M-Y", $startdate);
    $daysold = ml_ftime(""._LINKSDATESTRING."", $startdate);
        if ("$daysold" == "$datetime") {
            if ($count<=1) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_1.gif\" alt=\""._NEWTODAY."\">";
        }
            if ($count<=3 && $count>1) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_3.gif\" alt=\""._NEWLAST3DAYS."\">";
        }
            if ($count<=7 && $count>3) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/new_7.gif\" alt=\""._NEWTHISWEEK."\">";
        }
    }
        $count++;
        $startdate = (time()-(86400 * $count));
    }
}

/**
 * subcategorynewlinkgraphic
 *
 * Post-Nuke mod --  Create a the new function for generating the 'new' graphics for
 * sub-categoires, based on the post-nuke categorynewlinkgraphic($cid)
 * @usedby index, viewlink
 */

function subcategorynewlinkgraphic($sid) {

    if (!isset($sid) || !is_numeric($sid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['links_links_column'];
// cocomp 2002/07/13 remove limit stuff from build use ADODB SelectLimit instead
    $query = buildSimpleQuery ('links_links', array ('date'), "$column[cat_id]=".(int)pnVarPrepForStore($sid)."", "$column[date] DESC");
//    $newresult = $dbconn->Execute($query);
	$newresult = $dbconn->SelectLimit($query, 1);
    list($time)=$newresult->fields;
    echo "&nbsp;";
    setlocale (LC_TIME, "$locale");
    ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    $datetime = strftime(""._LINKSDATESTRING."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    $startdate = time();
    $count = 0;
    while ($count <= 7) {
        // $daysold = date("d-M-Y", $startdate);
        $daysold = date("d-M-Y", $startdate);
        if ("$daysold" == "$datetime") {
        if ($count<=1) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/newred.gif\" alt=\""._CATNEWTODAY."\">";
    }
            if ($count<=3 && $count>1) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/newgreen.gif\" alt=\""._CATLAST3DAYS."\">";
    }
            if ($count<=7 && $count>3) {
        echo "<img src=\"modules/".$GLOBALS['ModName']."/images/newblue.gif\" alt=\""._CATTHISWEEK."\">";
    }
}
        $count++;
        $startdate = (time()-(86400 * $count));
    }
}

/**
 * @usedby mostpopular, search, toprated, viewlink
 */
function LinksBottomMenu($lid, $transfertitle, $totalvotes, $totalcomments){

    if (pnSecAuthAction(0, 'Downloads::Category', '::', ACCESS_COMMENT)) {
        echo "</font><br /><font class=\"pn-normal\"><a class=\"pn-normal\" href=\"${modurl}&amp;req=ratelink&amp;lid=$lid&amp;ttitle=$transfertitle\">"._RATESITE."</a>";
    }
    echo " | <a class=\"pn-normal\" href=\"${modurl}&amp;req=brokenlink&amp;lid=$lid\">"._REPORTBROKEN."</a>";
    if (pnSecAuthAction(0, 'Downloads::Category', '::', ACCESS_READ)) {
        if ($totalvotes != 0) {
            echo " | <a class=\"pn-normal\" href=\"${modurl}&amp;req=viewlinkdetails&amp;lid=$lid&amp;ttitle=$transfertitle\">"._DETAILS."</a>";
        }
    }
    if (pnSecAuthAction(0, 'Downloads::Category', '::', ACCESS_COMMENT)) {
        if ($totalcomments != 0) {
           echo " | <a class=\"pn-normal\" href=\"${modurl}&amp;req=viewlinkcomments&amp;lid=$lid&amp;ttitle=$transfertitle\">"._COMMENTS." ($totalcomments)</a></font>";
        }
    }
}

function downloads_rateMakeStar($score, $max_score)
{
    // this code is take from harpia project http://sourceforge.net/projects/harpia


    $score /= 2;    $max_score /=2; //      5 stars. comment for 10 stars
    $basedir="modules/".$GLOBALS['ModName']."/images/" ;   // for $basedir/image/xxx.gif
    $rateImgFull = $basedir.'rate_full.gif';
    $rateImgHalf = $basedir.'rate_half.gif';
    $rateImgNone = $basedir.'rate_none.gif';

    // Break up score
    if (strpos($score,".")==0){
        $full_stars=$score;
    }else{
        $full_stars=substr($score,0,strpos($score,"."));
    }

    // *** Is there half star
    if (substr($score,strpos($score,".")+1)==0){
        $half_stars=0;
    }else{
        $half_stars=1;
    }

    // *** Build Star Line
    $blank_stars=$max_score-($full_stars+$half_stars);
    $star_line="";
    for ($i=1;$i<=$max_score;$i++){
        if ($i<=$full_stars){
            $star_line.="<img src='".$rateImgFull."' border='0'>";
        }elseif ($i<=($half_stars+$full_stars)){
            $star_line.="<img src='".$rateImgHalf."' border='0'>";
        }elseif ($i<=$max_score){
            $star_line.="<img src='".$rateImgNone."' border='0'>";
        }
    }
    return $star_line;
}

/**
 * @usedby rating
 */
function downloadfooter($lid,$ttitle) {
    echo "<font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=getit&amp;lid=$lid\">"._DOWNLOADNOW."</a>";
    if (pnSecAuthAction(0, 'Downloads::', '::', ACCESS_COMMENT)){
        echo " | <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=ratedownload&amp;lid=$lid&amp;ttitle=$ttitle\">"._RATETHISSITE."</a>";
    }
    echo" ]</font><br /><br />";
    downloadfooterchild($lid);
}

/**
 * @usedby rating
 */
function downloadfooterchild($lid) {

    if (pnSecAuthAction(0, 'Downloads::', '::', ACCESS_COMMENT)){
        if ($useoutsidevoting = 1) {
            echo "<br /><font class=\"pn-normal\">"._ISTHISYOURSITE." <a class=\"pn-normal\" href=\"".$GLOBALS['modurl']."&amp;req=outsidedownloadsetup&amp;lid=$lid\">"._ALLOWTORATE."</a></font>";
        }
    }
}

function drawvotestats($baton) {
    $bgcolor1 = $GLOBALS['bgcolor1'];
    $bgcolor2 = $GLOBALS['bgcolor2'];

    if ($baton['weight']) {         // Note on the weighing of votes
        echo '<tr><td valign="top" colspan="2"><font class="pn-normal"<br /><br />'
        .$baton['weighnote'].' '.$baton['weightext'].' '._TO.' 1.</font></td></tr>';
    }
    echo '<tr><td colspan="2" bgcolor="'.$bgcolor2.'">'
    .'<font class="pn-normal">'.$baton['users'].'</font></td></tr>'
    .'<tr><td bgcolor="'.$bgcolor1.'"><font class="pn-normal">'
    ._NUMBEROFRATINGS.': '.$baton['votes'].'</font></td>'
    .'<td align="center" rowspan="5" width="200">';
    if ($baton['votes'] == '0') {
        echo '<font class="pn-normal">'.$baton['novotes'].'</font>';
    } else {
        echo '<table border="1" width="200">'   // charts table
        .'<tr><td valign="top" align="center" colspan="10" bgcolor="'.$bgcolor2.'">'
        .'<font class="pn-sub">'._BREAKDOWNBYVAL.'</font></td>'
        .'</tr><tr>';
        for ($i=1; $i<=10; $i++) {
            echo '<td bgcolor="'.$bgcolor1.'" valign="bottom">'
            .'<img border="0" alt="'.$baton['vv'][$i].' '._LVOTES.' ('.$baton['vvpercent'][$i].'% '
            ._LTOTALVOTES.')" src="modules/'.$GLOBALS['ModName'].'/images/blackpixel.gif" width="15" height="'
            .$baton['vvcharth'][$i].'"></td>';
        }
        echo '</tr><tr><td colspan="10" bgcolor="'.$bgcolor2.'">'
        .'<table cellspacing="0" cellpadding="0" border="0" width="205"><tr>';
        for ($i=1; $i<=10; $i++) {
            echo '<td width="10%" valign="bottom" align="center"><font class="pn-sub">'.$i.'</font></td>';
        }
        echo '</tr></table></td></tr></table>';
    }
    echo '</td></tr>';
    echo '<tr><td bgcolor="'.$bgcolor2.'"><font class="pn-normal">'._DOWNLOADRATING.': '.$baton['avg'].'</font></td></tr>'
    .'<tr><td bgcolor="'.$bgcolor1.'"><font class="pn-normal">'._HIGHRATING.': '.$baton['top'].'</font></td></tr>'
    .'<tr><td bgcolor="'.$bgcolor2.'"><font class="pn-normal">'._LOWRATING.': '.$baton['low'].'</font></td></tr>'
    .'<tr><td bgcolor="'.$bgcolor1.'"><font class="pn-normal">';
    if (!$baton['weight']) {
        echo _NUMOFCOMMENTS.': '.$baton['comments'].'</font></td></tr>';
    } else {
        echo '&nbsp;';
    }
    echo '</td></tr>';
}

/*
 * Get the item name given its IID
 */
function downloads_ItemNameFromIID($iid)
{
    if (!isset($iid) || !is_numeric($iid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dlitemtable = $pntable['downloads_downloads'];
    $dlitemcolumn = &$pntable['downloads_downloads_column'];

    $query = "SELECT $dlitemcolumn[title]
              FROM $dlitemtable
              WHERE $dlitemcolumn[lid] = ".(int)pnVarPrepForStore($iid)."";
    $result = $dbconn->Execute($query);
    list($itemname) = $result->fields;
    $result->Close();

    return $itemname;
}

function CoolSize($size) {
    $mb = 1024*1024;
    if ( $size > $mb ) {
        $mysize = sprintf ("%01.2f",$size/$mb) . " MB";
    } elseif ( $size >= 1024 ) {
        $mysize = sprintf ("%01.2f",$size/1024) . " Kb";
    } else {
        $mysize = $size . " bytes";
    }
    return $mysize;
}

function calculateVote($voteresult, $totalvotesDB)
{
$anonvotes = 0;
$anonvoteval = 0;
$outsidevotes = 0;
$outsidevoteval = 0;
$regvoteval = 0;
$truecomments = $totalvotesDB;

$anonweight = pnConfigGetVar('anonweight');
$anonymous = pnConfigGetVar('anonymous');
$outsideweight = pnConfigGetVar('outsideweight');
$useoutsidevoting = pnConfigGetVar('useoutsidevoting');


while(list($ratingDB, $ratinguserDB, $ratingcommentsDB) = $voteresult->fields) {
    $voteresult->MoveNext();
    if ($ratingcommentsDB == "") {
        --$truecomments;
    }
    if ($ratinguserDB == $anonymous) {
        $anonvotes++;
        $anonvoteval += $ratingDB;
    }
    if ($useoutsidevoting == 1) {
        if ($ratinguserDB == 'outside') {
            ++$outsidevotes;
            $outsidevoteval += $ratingDB;
        }
    } else {
        $outsidevotes = 0;
    }
    if ($ratinguserDB != $anonymous && $ratinguserDB != "outside") {
        $regvoteval += $ratingDB;
    }
}

$regvotes = $totalvotesDB - $anonvotes - $outsidevotes;

if ($totalvotesDB == 0) {
    $finalrating = 0;
} else if ($anonvotes == 0 && $regvotes == 0) {
    /* Figure Outside Only Vote */
    $finalrating = $outsidevoteval / $outsidevotes;
    $finalrating = number_format($finalrating, 4);
} else if ($outsidevotes == 0 && $regvotes == 0) {
    /* Figure Anon Only Vote */
    $finalrating = $anonvoteval / $anonvotes;
    $finalrating = number_format($finalrating, 4);
} else if ($outsidevotes == 0 && $anonvotes == 0) {
    /* Figure Reg Only Vote */
    $finalrating = $regvoteval / $regvotes;
    $finalrating = number_format($finalrating, 4);
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
        $finalrating = number_format($finalrating, 4);
    } else {
        /* Outside is 'standard weight' */
        $newimpact = $outsideweight / $anonweight;
        $impactOU = $outsidevotes;
        $impactAU = $anonvotes / $newimpact;
        $finalrating = ((($avgOU * $impactOU) + ($avgAU * $impactAU)) / ($impactAU + $impactOU));
        $finalrating = number_format($finalrating, 4);
    }
} else {
    /* Registered User vs. Anonymous vs. Outside User Weight Calutions */
    $impact = $anonweight;
    $outsideimpact = $outsideweight;
    if ($regvotes == 0) {
        $regvotes = 0;
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
    $finalrating = number_format($finalrating, 4);
}
    return $finalrating;
}
?>