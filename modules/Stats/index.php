<?php // $Id: index.php,v 1.3 2002/10/24 18:35:19 larsneo Exp $ $Name:  $
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
// Purpose of file: Display site statistics
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}


list($dbconn) = pnDBGetConn();

$ModName = basename( dirname( __FILE__ ) ); 

modules_get_language();

// Security check
if (!pnSecAuthAction(0, 'Stats::', '::', ACCESS_READ)) {
	include 'header.php';
	echo _BADAUTHKEY;
	include 'footer.php';
	return;
}

function mk_percbar($pperc,$width=100,$xecho=true,$ThemeSel,$label='')
{
    $perc=round(($width*($pperc/100)),0);
        
    $what = "<img src=\"themes/".pnVarPrepForOS($ThemeSel)."/images/leftbar.gif\" height=\"15\" width=\"7\" alt=\"$label\"><img src=\"themes/".pnVarPrepForOS($ThemeSel)."/images/mainbar.gif\" height=\"15\" width=\"$perc\" alt=\"$label\"><img src=\"themes/".pnVarPrepForOS($ThemeSel)."/images/rightbar.gif\" height=\"15\" width=\"7\" alt=\"$label\">";
    
    if($xecho == true) {
	    echo $what; 
    } else {
    	return $what;
    }
}

// Stats Hour - Day Stats
$weekdaynames = array(_MODSUN,
		      _MODMON,
		      _MODTUE,
		      _MODWED,
		      _MODTHU,
		      _MODFRI,
		      _MODSAT);

$monthnames = array("",
		    _MODJAN,
		    _MODFEB,
		    _MODMAR,
		    _MODAPR,
		    _MODMAY,
		    _MODJUN,
		    _MODJUL,
		    _MODAUG,
		    _MODSEP,
		    _MODOCT,
		    _MODNOV,
		    _MODDEC);

$toddate = date("dmY");
// 24hours ago = yesterday
$yesdate = date("dmY",time()-(60*60*24));

$column = &$pntable['stats_date_column'];
$toddb=$dbconn->Execute("SELECT $column[hits] as hits FROM $pntable[stats_date] WHERE $column[date]='".pnVarPrepForStore($toddate)."'");
if(!$toddb->EOF) {
    list($valtoday)=$toddb->fields;
} else {
    $valtoday=0;
}

$column = &$pntable['stats_date_column'];
$yesdb=$dbconn->Execute("SELECT $column[hits] as hits FROM $pntable[stats_date] WHERE $column[date]='".pnVarPrepForStore($yesdate)."'");
if(!$yesdb->EOF) {
    list($valyesday)=$yesdb->fields;
} else {
    $valyesday=0;
}

// Fetch some more infos about best and worst day ever
//
setlocale(LC_ALL, pnConfigGetVar('locale'));

$column = &$pntable['stats_date_column'];
$query = buildSimpleQuery('stats_date', array('date', 'hits'), '', "$column[hits] DESC", 1);
$dbr = $dbconn->Execute("$query");
list ($best_day_date, $best_day_hits) = $dbr->fields;
$best_day = mktime(0, 0, 0, substr($best_day_date, 2, 2), substr($best_day_date, 0, 2), substr($best_day_date, 4, 4));
$query = buildSimpleQuery('stats_date', array('date', 'hits'), '', "$column[hits] ASC", 1);
$dbr = $dbconn->Execute("$query");
list ($worst_day_date, $worst_day_hits) = $dbr->fields;
$worst_day = mktime(0, 0, 0, substr($worst_day_date, 2, 2), substr($worst_day_date, 0, 2), substr($worst_day_date, 4, 4));

// get all rows from the db at once and go through the result
$column = &$pntable['stats_hour_column'];
$result = $dbconn->Execute("SELECT $column[hits] FROM $pntable[stats_hour]");
$hour = 0; $sumhour = 0;

while (!$result->EOF) {

    $hourhitamount[$hour] = $result->fields[0];
    if ($hour == 0) {
        $hourbesthits = $hourhitamount[$hour];
        $hourbest = 0;
        $hourbadhits = $hourhitamount[$hour];
        $hourbad = 0;
    }
    if ($hourhitamount[$hour] > $hourbesthits) {
        $hourbesthits = $hourhitamount[$hour];
        $hourbest = $hour;
    }
    if ($hourhitamount[$hour] < $hourbadhits) {
        $hourbadhits = $hourhitamount[$hour];
        $hourbad = $hour;
    }
    $sumhour += $hourhitamount[$hour];
    $hour++;
    $result->MoveNext();
}
$result->Close();

// get all rows from the db at once and go through the result
$column = &$pntable['stats_week_column'];
$result = $dbconn->Execute("SELECT $column[hits] FROM $pntable[stats_week]");
$weekday = 0; $sumweek = 0;
while (!$result->EOF) {
    $weekhitamount[$weekday] = $result->fields[0];
    if ($weekday == 0) {
        $weekdaybesthits = $weekhitamount[$weekday];
        $weekdaybest = 0;
        $weekdaybadhits = $weekhitamount[$weekday];
        $weekdaybad = 0;
    }
    if ($weekhitamount[$weekday] > $weekdaybesthits) {
        $weekdaybesthits = $weekhitamount[$weekday];
        $weekdaybest = $weekday;
    }
    if ($weekhitamount[$weekday] < $weekdaybadhits) {
        $weekdaybadhits = $weekhitamount[$weekday];
        $weekdaybad = $weekday;
    }
    $sumweek += $weekhitamount[$weekday];
    $weekday++;
    $result->MoveNext();
}
$result->Close();

// get all rows from the db at once and go through the result
$column = &$pntable['stats_month_column'];
$result = $dbconn->Execute("SELECT $column[hits] FROM $pntable[stats_month]");
$month = 1; $summon = 0;
$monthbesthits = 0;
$monthbadhits = 0;
while (!$result->EOF) {
    $monthhitamount[$month] = $result->fields[0];
    if ($monthhitamount[$month] > $monthbesthits) {
        $monthbesthits = $monthhitamount[$month];
        $monthbest = $month;
    }
    if ($monthhitamount[$month] < $monthbadhits) {
        $monthbadhits = $monthhitamount[$month];
        $monthbad = $month;
    }
    $summon += $monthhitamount[$month];
    $month++;
    $result->MoveNext();
}
$result->Close();

include("header.php");

$column = &$pntable['counter_column'];
$result = $dbconn->Execute("SELECT $column[type], $column[var], $column[count] FROM $pntable[counter] ORDER BY $column[type] DESC");
if($dbconn->ErrorNo()<>0) { 
    echo $dbconn->ErrorNo(). ": ".$dbconn->ErrorMsg(). "<br>"; exit(); 
}
while(list($type, $var, $count) = $result->fields) {

    $result->MoveNext();

/* Lets do one big switch instead of many if/then statements */

switch ($type) {
    case "total":
        switch ($var) {
            case "hits":
                $total = $count;
                break;
        }
    case "browser":
        switch ($var) {
            case "Netscape":
                $netscape[] = $count;
                $netscape[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "MSIE":
                $msie[] = $count;
                $msie[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Konqueror":
                $konqueror[] = $count;
                $konqueror[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Opera":
                $opera[] = $count;
                $opera[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Lynx":
                $lynx[] = $count;
                $lynx[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Bot":
                $bot[] = $count;
                $bot[] =  substr(100 * $count / $total, 0, 5);
                break;

            case "Other":
                $b_other[] = $count;
                $b_other[] =  substr(100 * $count / $total, 0, 5);
                break;
        }
    case "os":
        switch ($var) {
            case "Windows":
                $windows[] = $count;
                $windows[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Mac":
                $mac[] = $count;
                $mac[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Linux":
                $linux[] = $count;
                $linux[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "FreeBSD":
                $freebsd[] = $count;
                $freebsd[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "SunOS":
                $sunos[] = $count;
                $sunos[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "IRIX":
                $irix[] = $count;
                $irix[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "BeOS":
                $beos[] = $count;
                $beos[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "OS/2":
                $os2[] = $count;
                $os2[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "AIX":
                $aix[] = $count;
                $aix[] =  substr(100 * $count / $total, 0, 5);
                break;
            case "Other":
                $os_other[] = $count;
                $os_other[] =  substr(100 * $count / $total, 0, 5);
                break;
        }
}
}

$ThemeSel = pnUserGetTheme();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">".pnConfigGetVar('sitename').' '._STATS."</font>";
CloseTable2();
echo "<br /><font class=\"pn-normal\">"._WERECEIVED." <b>".pnVarPrepForDisplay($total)."</b> "
	._PAGESVIEWS.' '.pnConfigGetVar('startdate').", <b>".pnVarPrepForDisplay($valtoday)."</b> "
	._MODTODAY." <b>".pnVarPrepForDisplay($valyesday)."</b> "._MODYESTERDAY.".<br /><br />"
	._MOALLBESTDAY.ml_ftime(_DATELONG, $best_day)." (<b>".pnVarPrepForDisplay($best_day_hits)."</b> "._MODPAGES."), "
	._MODWHILE." ".ml_ftime(_DATELONG, $worst_day)." (<b>".pnVarPrepForDisplay($worst_day_hits)."</b> "._MODPAGES
	.") "._MOALLWORSTDAY.".<br /><br />\n"
	._MODON." <b>".pnVarPrepForDisplay($weekdaynames[$weekdaybest])."</b> "
	._MODTOTAL." <b>".pnVarPrepForDisplay($weekdaybesthits)."</b> "._MODPAGES.", "._MODWHILE." <b>"
	.pnVarPrepForDisplay($weekdaynames[$weekdaybad])."</b> "._MODISNTOURDAY." <b>"
	.pnVarPrepForDisplay($weekdaybadhits)."</b> "._MODPAGES.". "._MODINAVRG." <b>"
	.pnVarPrepForDisplay($hourbesthits)."</b> "._MODPAGES." "._MODAT." <b>"
	.pnVarPrepForDisplay($hourbest)."</b> "._MODOCLOCK." "._MODFANSONLY." <b>".pnVarPrepForDisplay($hourbad)."</b> "
	._MODOCLOCK." "._MODWITHONLY." <b>".pnVarPrepForDisplay($hourbadhits)."</b> "._MODPAGES.").</font>";
CloseTable();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._BROWSERS."</font>";
CloseTable2();
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
echo "<tr><td><img src=\"modules/$ModName/images/explorer.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._MSIE.": </font></td><td>".mk_percbar($msie[1],200,false,$ThemeSel,"$msie[1] %")."<font class=\"pn-normal\"> $msie[1] % ($msie[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/netscape.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._NETSCAPE.":</font></td><td>".mk_percbar($netscape[1],200,false,$ThemeSel,"$netscape[1] %")."<font class=\"pn-normal\"> $netscape[1] % ($netscape[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/opera.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._OPERA.": </font></td><td>".mk_percbar($opera[1],200,false,$ThemeSel,"$opera[1] %")."<font class=\"pn-normal\"> $opera[1] % ($opera[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/konqueror.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._KONQUEROR.": </font></td><td>".mk_percbar($konqueror[1],200,false,$ThemeSel,"$konqueror[1] %")."<font class=\"pn-normal\"> $konqueror[1] % ($konqueror[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/lynx.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._LYNX.":</font></td><td>".mk_percbar($lynx[1],200,false,$ThemeSel,"$lynx[1] %")."<font class=\"pn-normal\"> $lynx[1] % ($lynx[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/altavista.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._SEARCHENGINES.": </font></td><td>".mk_percbar($bot[1],200,false,$ThemeSel,"$bot[1] %")."<font class=\"pn-normal\"> $bot[1] % ($bot[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/question.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._UNKNOWN.": </font></td><td>".mk_percbar($b_other[1],200,false,$ThemeSel,"$b_other[1] %")."<font class=\"pn-normal\"> $b_other[1] % ($b_other[0])</font>\n";
echo "</td></tr></table>";
CloseTable();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._OPERATINGSYS."</font>";
CloseTable2();
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
echo "<tr><td><img src=\"modules/$ModName/images/windows.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">Windows:</font></td><td>".mk_percbar($windows[1],200,false,$ThemeSel,"$windows[1] %")."<font class=\"pn-normal\"> $windows[1] % ($windows[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/linux.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">Linux:</font></td><td>".mk_percbar($linux[1],200,false,$ThemeSel,"$linux[1] %")."<font class=\"pn-normal\"> $linux[1] % ($linux[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/mac.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">Mac/PPC:</font></td><td>".mk_percbar($mac[1],200,false,$ThemeSel,"$mac[1] %")."<font class=\"pn-normal\"> $mac[1] % ($mac[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/bsd.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">FreeBSD:</font></td><td>".mk_percbar($freebsd[1],200,false,$ThemeSel,"$freebsd[1] %")."<font class=\"pn-normal\"> $freebsd[1] % ($freebsd[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/sun.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">SunOS:</font></td><td>".mk_percbar($sunos[1],200,false,$ThemeSel,"$sunos[1] %")."<font class=\"pn-normal\"> $sunos[1] % ($sunos[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/irix.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">IRIX:</font></td><td>".mk_percbar($irix[1],200,false,$ThemeSel,"$irix[1] %")."<font class=\"pn-normal\"> $irix[1] % ($irix[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/be.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">BeOS:</font></td><td>".mk_percbar($beos[1],200,false,$ThemeSel,"$beos[1] %")."<font class=\"pn-normal\"> $beos[1] % ($beos[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/os2.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">OS/2:</font></td><td>".mk_percbar($os2[1],200,false,$ThemeSel,"$os2[1] %")."<font class=\"pn-normal\"> $os2[1] % ($os2[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/aix.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">AIX:</font></td><td>".mk_percbar($aix[1],200,false,$ThemeSel,"$aix[1] %")."<font class=\"pn-normal\"> $aix[1] % ($aix[0])</font></td></tr>\n";
echo "<tr><td><img src=\"modules/$ModName/images/question.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._UNKNOWN.":</font></td><td>".mk_percbar($os_other[1],200,false,$ThemeSel,"$os_other[1] %")."<font class=\"pn-normal\"> $os_other[1] % ($os_other[0])</font>\n";
echo "</td></tr></table>\n";
CloseTable();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._MODBYHOUR."</font>";
CloseTable2();
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
if (_24HOUR == true) {
	for ($hour = 0; $hour < 24; $hour++) {
		$percent = round((100*$hourhitamount[$hour])/$sumhour,2);
		$label = "".pnVarPrepForDisplay($hour).":00 - ".pnVarPrepForDisplay($hour).":59";
	    echo "\t<tr>\n"
    	    ."\t\t<td align=\"right\">$label:</td>\n"
        	."\t\t<td>".mk_percbar(round((100*$hourhitamount[$hour])/$hourbesthits,0),200,false,$ThemeSel,"$percent %")."&nbsp;\n"
	        ."$percent % ($hourhitamount[$hour])</td>\n"
    	    ."\t</tr>\n";
	}
} else {
	for ($hour = 1; $hour < 24; $hour++) {
		$percent = round((100*$hourhitamount[$hour])/$sumhour,2);
		if ($hour < 13) $label = "".pnVarPrepForDisplay($hour)." am";
		else $label = "".pnVarPrepForDisplay($hour % 12)." pm";
	    echo "\t<tr>\n"
    	    ."\t\t<td align=\"right\">$label:</td>\n"
        	."\t\t<td>".mk_percbar(round((100*$hourhitamount[$hour])/$hourbesthits,0),200,false,$ThemeSel,"$percent %")."&nbsp;\n"
	        ."$percent % ($hourhitamount[$hour])</td>\n"
    	    ."\t</tr>\n";
	}
	$percent = round((100*$hourhitamount[0])/$sumhour,2);
    echo "\t<tr>\n"
   	    ."\t\t<td align=\"right\">12 pm:</td>\n"
       	."\t\t<td>".mk_percbar(round((100*$hourhitamount[0])/$hourbesthits,0),200,false,$ThemeSel,"$percent %")."&nbsp;\n"
	    ."$percent % ($hourhitamount[0])</td>\n"
        ."\t</tr>\n";
}
echo "</table>\n";
CloseTable();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._MODBYWEEK."</font>";
CloseTable2();
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
for ($weekday=1;$weekday<=6;$weekday++) {
    echo "\t<tr>\n"
        ."\t\t<td>$weekdaynames[$weekday]:</td>\n"
        ."\t\t<td>".mk_percbar(round((100*$weekhitamount[$weekday])/$weekdaybesthits,0),200,false,$ThemeSel)."&nbsp;\n"
        .round((100*$weekhitamount[$weekday])/$sumweek,2),"% ($weekhitamount[$weekday])</td>\n"
        ."\t</tr>\n";
}
echo "\t<tr>\n"
    ."\t\t<td>$weekdaynames[0]:</td>\n"
    ."\t\t<td>".mk_percbar(round((100*$weekhitamount[0])/$weekdaybesthits,0),200,false,$ThemeSel)."&nbsp;\n"
    .round((100*$weekhitamount[0])/$sumweek,2),"% ($weekhitamount[0])</td>\n"
    ."\t</tr>\n";
echo "</table>\n";
CloseTable();

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._MODBYMONTH."</font>";
CloseTable2();
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
for ($month=1;$month<=12;$month++)
{
    echo "\t<tr>\n"
        ."\t\t<td>$monthnames[$month]:</td>\n"
        ."\t\t<td>".mk_percbar(round((100*$monthhitamount[$month])/$monthbesthits,0),200,false,$ThemeSel)."&nbsp;\n"
        .round((100*$monthhitamount[$month])/$summon,2),"% ($monthhitamount[$month])</td>\n"
        ."\t</tr>\n";
}
echo "</table>\n";
CloseTable();

$unum_res =    $dbconn->Execute("SELECT count(*) AS count FROM $pntable[users]");

$column = &$pntable['stories_column'];

$anum_res =    $dbconn->Execute("SELECT $column[aid], count(*) AS count FROM $pntable[stories] GROUP BY $column[aid]");
$snum_res =    $dbconn->Execute("SELECT count(*) AS count FROM $pntable[stories]");
$cnum_res =    $dbconn->Execute("SELECT count(*) AS count FROM $pntable[comments]");
$secnum_res =  $dbconn->Execute("SELECT count(*) AS count FROM $pntable[sections]");
$secanum_res = $dbconn->Execute("SELECT count(*) AS count FROM $pntable[seccont]");
$subnum_res =  $dbconn->Execute("SELECT count(*) AS count FROM $pntable[queue] WHERE pn_arcd='0'");
$tnum_res =    $dbconn->Execute("SELECT count(*) AS count FROM $pntable[topics]");
$links_res =   $dbconn->Execute("SELECT count(*) AS count FROM $pntable[links_links]");
$cat_res =    $dbconn->Execute("SELECT count(*) AS count FROM $pntable[links_categories]");

/* Now lets get the count info */

list($unum)    = $unum_res->fields;
// $anum 	       = $anum_res->PO_RecordCount();
list($snum)    = $snum_res->fields;
list($cnum)    = $cnum_res->fields;
list($secnum)  = $secnum_res->fields;
list($secanum) = $secanum_res->fields;
list($subnum)  = $subnum_res->fields;
list($tnum)    = $tnum_res->fields;
list($links)   = $links_res->fields;
list($cat)     = $cat_res->fields;

OpenTable();
OpenTable2();
echo "<font class=\"pn-title\">"._MISCSTATS."</font>";
CloseTable2();
$vlgn = "valign=\"bottom\"";
$algn = "align=\"right\"";
echo "<br /><table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\">\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/users.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._REGUSERS."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($unum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/news.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._STORIESPUBLISHED."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($snum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/topics.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._SACTIVETOPICS."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($tnum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/comments.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._COMMENTSPOSTED."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($cnum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/sections.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._SSPECIALSECT."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($secnum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/articles.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._ARTICLESSEC."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($secanum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/topics.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._LINKSINLINKS."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($links)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/sections.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._LINKSCAT."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($cat)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/waiting.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._NEWSWAITING."</font></td><td $algn><font class=\"pn-normal\">".pnVarPrepForDisplay($subnum)."</font></td></tr>\n";
echo "<tr $vlgn><td><img src=\"modules/$ModName/images/sections.gif\" border=\"0\" alt=\"\"></td><td>&nbsp;<font class=\"pn-normal\">"._NUKEVERSION."</font></td><td $algn><font class=\"pn-normal\">"._PN_VERSION_NUM."</font>\n";
echo "</td></tr></table>\n";
CloseTable();

include 'footer.php';
?>