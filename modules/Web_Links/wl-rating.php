<?php
// File: $Id: wl-rating.php,v 1.8 2002/12/01 16:26:21 nunizgb Exp $ $Name:  $
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
function rateinfo($lid)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['links_links_column'];
    $dbconn->Execute("UPDATE $pntable[links_links]
                    SET $column[hits]=$column[hits]+1
                    WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
    $result = $dbconn->Execute("SELECT $column[url]
                    FROM $pntable[links_links]
                    WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
    list($url) = $result->fields;
    Header('Location: '.$url);
}

/**
 *@usedby index, navigation
 */
function addrating($ratinglid, $ratinguser, $rating, $ratinghost_name, $ratingcomments)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $passtest = "yes";
    include("header.php");
        if (!(pnSecAuthAction(0, 'Web Links::', '::', ACCESS_READ))) {
            echo _WEBLINKSNOAUTH;
            include 'footer.php';
            return;
        }
    include(WHERE_IS_PERSO."config.php");
    completevoteheader();
    if (pnUserLoggedIn()) {
        $ratinguser = pnUserGetVar('uname');
    } else if ($ratinguser=="outside") {
        $ratinguser = "outside";
    } else {
        $ratinguser = pnConfigGetVar("anonymous");
    }
    $column = &$pntable['links_links_column'];
    $results3 = $dbconn->Execute("SELECT $column[title]
                                FROM $pntable[links_links]
                                WHERE $column[lid]=".(int)pnVarPrepForStore($ratinglid)."");
   while(list($title)=$results3->fields)   {
        $ttitle = $title;
        $results3->MoveNext();
    }
    /* Make sure only 1 anonymous from an IP in a single day. */
    $ip = getenv("REMOTE_HOST");
    if (empty($ip)) {
        $ip = getenv("REMOTE_ADDR");
    }
    /* Check if Rating is Null */
    if ($rating=="--") {
        $error = "nullerror";
        completevote($error);
        $passtest = "no";
    }
    /* Check if Link POSTER is voting (UNLESS Anonymous users allowed to post) */
    if ($ratinguser != pnConfigGetVar("anonymous") && $ratinguser != "outside") {
        $column = &$pntable['links_links_column'];
        $result=$dbconn->Execute("SELECT $column[submitter]
                                FROM $pntable[links_links]
                                WHERE $column[lid]=".(int)pnVarPrepForStore($ratinglid)."");
        while(list($ratinguserDB)=$result->fields) {

            $result->MoveNext();
            if ($ratinguserDB==$ratinguser) {
                $error = "postervote";
                completevote($error);
                $passtest = "no";
            }
        }
    }
    /* Check if REG user is trying to vote twice. */
    if ($ratinguser != pnConfigGetVar("anonymous") && $ratinguser != "outside") {
        $column = &$pntable['links_votedata_column'];
        $result = $dbconn->Execute("SELECT $column[ratinguser] FROM $pntable[links_votedata] WHERE $column[ratinglid]=".(int)pnVarPrepForStore($ratinglid)."");
        while(list($ratinguserDB)=$result->fields) {

            $result->MoveNext();
            if ($ratinguserDB==$ratinguser) {
                $error = "regflood";
                completevote($error);
                $passtest = "no";
            }
        }
    }
    /* Check if ANONYMOUS user is trying to vote more than once per day. */
    if ($ratinguser == pnConfigGetVar("anonymous")){
        $yesterdaytimestamp = (time()-(86400 * $anonwaitdays));
        $ytsDB = Date("Y-m-d H:i:s", $yesterdaytimestamp);
        $column = &$pntable['links_votedata_column'];
        $result=$dbconn->Execute("SELECT count(*)
                                FROM $pntable[links_votedata]
                                WHERE $column[ratinglid]=".(int)pnVarPrepForStore($ratinglid)."
                                AND $column[ratinguser]='".pnConfigGetVar("anonymous")."'
                                AND $column[ratinghostname]='".pnVarPrepForStore($ip)."'
                                AND TO_DAYS(NOW()) - TO_DAYS($column[ratingtimestamp]) < ".pnVarPrepForStore($anonwaitdays)."");
        list($anonvotecount) = $result->fields;
        if ($anonvotecount >= 1) {
            $error = "anonflood";
            completevote($error);
            $passtest = "no";
        }
    }
    /* Check if OUTSIDE user is trying to vote more than once per day. */
    if ($ratinguser == "outside"){
        $yesterdaytimestamp = (time()-(86400 * $outsidewaitdays));
        $ytsDB = date("Y-m-d H:i:s", $yesterdaytimestamp);
        $column = &$pntable['links_votedata_column'];
        $result=$dbconn->Execute("SELECT count(*) FROM $pntable[links_votedata]
                                WHERE $column[ratinglid]=".(int)pnVarPrepForStore($ratinglid)."
                                AND $column[ratinguser]='outside'
                                AND $column[ratinghostname]='".pnVarPrepForStore($ip)."'
                                AND TO_DAYS(NOW()) - TO_DAYS($column[ratingtimestamp]) < ".pnVarPrepForStore($outsidewaitdays)."");
        list($outsidevotecount) = $result->fields;
        if ($outsidevotecount >= 1) {
            $error = "outsideflood";
            completevote($error);
            $passtest = "no";
        }
    }
    /* Passed Tests */
    if ($passtest == "yes") {
        /* All is well.  Add to Line Item Rate to DB. */
        $nextid = $dbconn->GenId($pntable['links_votedata']);
        $column = &$pntable['links_votedata_column'];
        $dbconn->Execute("INSERT INTO $pntable[links_votedata]
                            ($column[ratingdbid], $column[ratinglid],
                             $column[ratinguser], $column[rating],
                             $column[ratinghostname], $column[ratingcomments],
                             $column[ratingtimestamp])
                             VALUES ($nextid,".(int)pnVarPrepForStore($ratinglid).", '".pnVarPrepForStore($ratinguser)."', '".pnVarPrepForStore($rating)."',
                             '".pnVarPrepForStore($ip)."', '".pnVarPrepForStore($ratingcomments)."', now())");
        /* All is well.  Calculate Score & Add to Summary (for quick retrieval & sorting) to DB. */
        /* NOTE: If weight is modified, ALL links need to be refreshed with new weight. */
        /*   Running a SQL statement with your modded calc for ALL links will accomplish this. */
        $voteresult = $dbconn->Execute("SELECT $column[rating], $column[ratinguser],
                                        $column[ratingcomments]
                                        FROM $pntable[links_votedata]
                                        WHERE $column[ratinglid] = ".(int)pnVarPrepForStore($ratinglid)."");
        $totalvotesDB = $voteresult->PO_RecordCount();
        $finalrating = calculateVote($voteresult, $totalvotesDB);
                $commresult = $dbconn->Execute("SELECT $column[ratingcomments]
                                                                                FROM $pntable[links_votedata]
                                                                                WHERE $column[ratinglid] = ".pnVarPrepForStore($ratinglid)."
                                                                                AND $column[ratingcomments] != ''");
                $truecomments = $commresult->PO_RecordCount();
        $column = &$pntable['links_links_column'];
        $dbconn->Execute("UPDATE $pntable[links_links]
                        SET $column[linkratingsummary] = ".pnVarPrepForStore($finalrating).",
                                                        $column[totalvotes] = ".pnVarPrepForStore($totalvotesDB).",
                            $column[totalcomments]= ".pnVarPrepForStore($truecomments)."
                         WHERE $column[lid] = ".(int)pnVarPrepForStore($ratinglid)."");
        $error = "none";
        completevote($error);
    }
        if ($error == "none")
    {
    completevotefooter($ratinglid, $ttitle, $ratinguser);
    }
    CloseTable();
    include("footer.php");
}

/*
 * @usedby function addrating
 */
function completevoteheader(){
    menu(1);

    OpenTable();
}

function completevotefooter($lid, $ttitle, $ratinguser)
{
    if (!isset($lid) || !is_numeric($lid)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $sitename = pnConfigGetVar('sitename');

    $column = &$pntable['links_links_column'];
    $result=$dbconn->Execute("SELECT $column[url]
                    FROM $pntable[links_links]
                    WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
    list($url)=$result->fields;
    echo "<center><font class=\"pn-normal\">"._THANKSTOTAKETIME." $sitename<br />. "._LETSDECIDE."</font></center><br /><br /><br />";
    if ($ratinguser=="outside") {
        echo "<center><font class=\"pn-normal\">".WEAPPREACIATE." ".pnVarPrepForDisplay($sitename)."!<br /><a class=\"pn-normal\" href=\"".pnVarPrepForDisplay($url)."\">"._RETURNTO." ".pnVarPrepForDisplay($ttitle)."</a></font><center><br /><br />";
        $result=$dbconn->Execute("SELECT $column[title] FROM $pntable[links_links]
                        WHERE $column[lid]=".(int)pnVarPrepForStore($lid)."");
        list($title)=$result->fields;
        $ttitle = ereg_replace (" ", "_", $title);
    }
    echo "<center><font class=\"pn-normal\">";
    linkinfomenu($lid,$ttitle);
    echo "</font></center>";
}

function completevote($error) {
    if ($error == "none")
    {
        echo "<center><font class=\"pn-normal\"><b>"._RATENOTE1ERROR."</b></font></center>";
    }
    elseif ($error == "anonflood")
    {
        $anonwaitdays = pnConfigGetVar('anonwaitdays');
        echo "<center><font class=\"pn-normal\"><b>"._RATENOTE2ERROR."</b></font></center><br />";
    }
    elseif ($error == "regflood")
    {
        echo "<center><font class=\"pn-normal\"><b>"._RATENOTE3ERROR."</b></font></center><br />";
    }
    elseif ($error == "postervote")
    {
        echo "<center><font class=\"pn-normal\"><b>"._RATENOTE4ERROR."</b></font></center><br />";
    }
    elseif ($error == "nullerror")
    {
        echo "<center><font class=\"pn-normal\"><b>"._RATENOTE5ERROR."</b></font></center><br />";
    }
    elseif ($error == "outsideflood")
    {
        $outsidewaitdays = pnConfigGetVar('outsidewaitdays');
        echo "<center><font class=\"pn-normal\"><b>Only one vote per IP address allowed every $outsidewaitdays day(s).</b></font></center><br />";
    }
}

/**
 * @usedby index
 */
function ratelink($lid, $ttitle) {
    include("header.php");
        if (!(pnSecAuthAction(0, 'Web Links::', '::', ACCESS_COMMENT))) {
            echo _WEBLINKSNOAUTH;
            include 'footer.php';
            return;
        }
    menu(1);

    OpenTable();
    $transfertitle = ereg_replace ("_", " ", $ttitle);
    $displaytitle = $transfertitle;
    $ip = getenv("REMOTE_HOST");
    if (empty($ip)) {
       $ip = getenv("REMOTE_ADDR");
    }
    echo "<font class=\"pn-normal\"><b>".pnVarPrepForDisplay($displaytitle)."</b></font>"
    ."<ul>"
    ."<li><font class=\"pn-sub\">"._RATENOTE1."</font>"
    ."<li><font class=\"pn-sub\">"._RATENOTE2."</font>"
    ."<li><font class=\"pn-sub\">"._RATENOTE3."</font>"
    ."<li><font class=\"pn-sub\">"._RATENOTE4."</font>"
    ."<li><font class=\"pn-sub\">"._RATENOTE5."</font>";
    if (pnUserLoggedIn()) {
        $name = pnUserGetVar('uname');
        echo "<li><font class=\"pn-sub\">"._YOUAREREGGED."</font>"
            ."<li><font class=\"pn-sub\">"._FEELFREE2ADD."</font>";
    } else {
        echo "<li><font class=\"pn-sub\">"._YOUARENOTREGGED."</font>"
            ."<li><font class=\"pn-sub\">"._IFYOUWEREREG."</font>";
        $name = pnConfigGetVar("anonymous");
    }
    echo "</ul>"
        ."<form method=\"post\" action=\"".$GLOBALS['modurl']."&amp;req=addrating\">"
        ."<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\">"
        ."<tr><td width=\"25\" nowrap></td>"
        ."<tr><td width=\"25\" nowrap></td><td width=\"550\">"
        ."<input type=\"hidden\" name=\"ratinglid\" value=\"$lid\">"
        ."<input type=\"hidden\" name=\"ratinguser\" value=\"$name\">"
        ."<input type=\"hidden\" name=\"ratinghost_name\" value=\"$ip\">"
        ."<font class=\"pn-normal\">"._RATETHISSITE."&nbsp;&nbsp;"
        ."<select name=\"rating\">"
        ."<option>--</option>"
        ."<option>10</option>"
        ."<option>9</option>"
        ."<option>8</option>"
        ."<option>7</option>"
        ."<option>6</option>"
        ."<option>5</option>"
        ."<option>4</option>"
        ."<option>3</option>"
        ."<option>2</option>"
        ."<option>1</option>"
        ."</select></font>"
    ."<font class=\"pn-sub\"><input type=\"submit\" value=\""._RATETHISSITE."\"></font>"
        ."<br /><br />";
    if (pnUserLoggedIn()) {
        echo "<font class=\"pn-normal\"><b>"._COMMENT." :</b></font><br /><textarea wrap=\"virtual\" cols=\"50\" rows=\"10\" name=\"ratingcomments\"></textarea>"
            ."<br /><br /><br />"
            ."</font></td>";
    } else {
        echo"<input type=\"hidden\" name=\"ratingcomments\" value=\"\">";
    }
    echo "</tr></table></form>";
    echo "<center>";
    linkfooterchild($lid);
    echo "</center>";
    CloseTable();
    include("footer.php");
}
?>