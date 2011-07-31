<?php
// File: $Id: poll.php,v 1.2 2002/11/07 01:11:59 skooter Exp $ $Name:  $
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

$blocks_modules['poll'] = array(
    'func_display' => 'blocks_poll_block',
    'func_edit' => 'blocks_poll_select',
    'func_update' => 'blocks_poll_update',
    'text_type' => 'Poll',
    'text_type_long' => 'Display poll',
    'allow_multiple' => true,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => true
);

// Security
pnSecAddSchema('Pollblock::', 'Block title::');

/**
 * poll functions
 */

function pollMain($pollID, $row)
{

    if (!pnSecAuthAction(0, 'Pollblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

	// if pollID isn't set use the latest pollID. Skooter
    if (!isset($pollID)     ||
         empty($pollID)     ||
        !is_numeric($pollID) ||
        $pollID == 0 ) {
      $pollID = pollLatest();
	}
    //could be a bug if pollID '1' is deleted. Skooter
    //if(!isset($pollID)) {
    //   $pollID = 1;
    //}
    
    if(!isset($url)) {
        $url = sprintf("modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;req=results&amp;pollID=%d", $pollID);
    }
    $boxContent = "<form action=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index\" method=\"post\">";
    $boxContent .= "<input type=\"hidden\" name=\"pollID\" value=\"".$pollID."\" />";
    $boxContent .= "<input type=\"hidden\" name=\"forwarder\" value=\"".$url."\" />";
    $column = &$pntable['poll_desc_column'];
    $result = $dbconn->Execute("SELECT $column[polltitle], $column[voters]
                              FROM $pntable[poll_desc]
                              WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID));
    if ($result->EOF) {
        return;
    }

    list($pollTitle, $voters) = $result->fields;
    $result->Close();

    if (!pnSecAuthAction(0, 'Polls::', "$row[title]::$pollID", ACCESS_OVERVIEW)) {
        return;
    }

    $boxContent .= "<font class=\"pn-normal\"><b>".pnVarPrepForDisplay($pollTitle)."</b></font><br><br>\n";

    $column = &$pntable['poll_data_column'];
    $result = $dbconn->Execute("SELECT $column[voteid], $column[optiontext] FROM $pntable[poll_data] WHERE ($column[pollid]=".(int)pnVarPrepForStore($pollID)." AND $column[optiontext] NOT LIKE \"\") ORDER BY $column[voteid]");

    while(list($voteid, $optionText) = $result->fields) {
        if (pnSecAuthAction(0, 'Polls::', "$row[title]::$pollID",ACCESS_COMMENT)) {
            $boxContent .= "<input type=\"radio\" name=\"voteID\" value=\"$voteid\" class=\"r-button\" /> <span class=\"pn-normal\">".pnVarPrepForDisplay($optionText)."</span><br />\n";
        } else {
            $boxContent .= "&middot;&nbsp;<span class=\"pn-normal\">".pnVarPrepForDisplay($optionText)."</span><br />\n";
        }
        $result->MoveNext();
    }
    if (pnSecAuthAction(0, 'Polls::', "$row[title]::$pollID", ACCESS_COMMENT)) {
        $boxContent .= "<br /><p style=\"text-align:center\"><span class=\"pn-normal\"><input class=\"pn-button\" type=\"submit\" value=\""._VOTE."\" /></span><br />";
    }

    $commentoptions = pnUserGetCommentOptions();

    $column = &$pntable['poll_data_column'];
    $result = $dbconn->Execute("SELECT SUM($column[optioncount]) AS sum
                              FROM $pntable[poll_data]
                              WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID));
    list($sum) = $result->fields;
    $boxContent .=  '<p style="text-align:center"><span class="pn-normal">[ ';
    if (pnSecAuthAction(0, 'Polls::', "$row[title]::$pollID", ACCESS_READ)) {
        $boxContent .= "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;req=results&amp;pollID=$pollID&amp;$commentoptions\"><b>"._RESULTS."</b></a> | ";
    }
    $boxContent .= '<a class="pn-normal" href="modules.php?op=modload&amp;name=NS-Polls&amp;file=index"><b>'._POLLS.'</b></a> ]</span></p><br />';
    if (pnConfigGetVar('pollcomm')) {
        $column = &$pntable['pollcomments_column'];
        $comres = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[pollcomments] WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID)."");
        list($numcom) = $comres->fields;
        $boxContent .= "<p style=\"text-align:center\"><span class=\"pn-normal\">"._NUMVOTES.": <b>$sum</b><br>"._PCOMMENTS." <b>$numcom</b></span></p>\n\n";
    } else {
        $boxContent .= "<p style=\"text-align:center\"><span class=\"pn-normal\">"._NUMVOTES.": <b>$sum</b></span></p>\n\n";
    }
    $boxContent .= "</form>\n\n";

    if (empty($row['title'])) {
        $row['title'] = _POLL;
    }

    if (empty($row['position'])) {
        $row['position'] = "c";
    }

    $row['content'] = $boxContent;
    return themesideblock($row);
}

function pollLatest()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $currentlang = pnUserGetLang();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['poll_desc_column'];
        $querylang = "WHERE ($column[planguage]='".pnVarPrepForStore($currentlang)."' OR $column[planguage]='')";
    } else {
        $querylang = '';
    }
    $column = &$pntable['poll_desc_column'];
	$sql = "SELECT $column[pollid] FROM $pntable[poll_desc] $querylang ORDER BY $column[pollid] DESC";
    $result = $dbconn->SelectLimit($sql,1);

    $pollID = $result->fields;
    return($pollID[0]);
}

function pollNewest()
{
    $pollID = pollLatest();
    $row = array();
    $row[title] = '';
    return pollMain($pollID,$row);
}

function pollCollector($pollID, $voteID, $forwarder)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Check that the user hasn't voted for this poll already
    if (pnSessionGetVar("poll_voted$pollID")) {
        $warn = "You already voted today!";
    } else {
        pnSessionSetVar("poll_voted$pollID", 1);
        $column = &$pntable['poll_data_column'];
        $dbconn->Execute("UPDATE $pntable[poll_data] SET $column[optioncount]=$column[optioncount]+1 WHERE ($column[pollid]=".(int)pnVarPrepForStore($pollID).") AND ($column[voteid]=".(int)pnVarPrepForStore($voteID).")");
        $column = &$pntable['poll_desc_column'];
        $dbconn->Execute("UPDATE $pntable[poll_desc] SET $column[voters]=$column[voters]+1 WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID)."");
    }

    pnRedirect($forwarder);
}

function pollList()
{
    if (!pnSecAuthAction(0, 'Polls::', "::", ACCESS_OVERVIEW)) {
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $currentlang = pnUserGetLang();

    $commentoptions = pnUserGetCommentOptions();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['poll_desc_column'];
        $querylang =  "WHERE ($column[planguage]='".pnVarPrepForStore($currentlang)."' OR $column[planguage]='')";
    } else {
        $querylang = "";
    }
    $column = &$pntable['poll_desc_column'];
    $result = $dbconn->Execute("SELECT $column[pollid], $column[polltitle], $column[timestamp], $column[voters] FROM $pntable[poll_desc] $querylang ORDER BY $column[timestamp]");
    OpenTable();
    OpenTable();
    echo "<p style=\"text-align:center\"><span class=\"pn-title\"><b>"._PASTSURVEYS."</b></span></p>";
    CloseTable();

    echo "<table border=\"0\" cellpadding=\"8\"><tr><td>";
    echo "<span class=\"pn-normal\">";
    $counter = 0;
    $resultArray = array();
    while($thisresult = $result->fields) {

        $result->MoveNext();
        $resultArray[$counter] = $thisresult;
        $counter++;
    }
    for ($count = 0; $count < count($resultArray); $count++) {
        $id = $resultArray[$count][0];
        $pollTitle = $resultArray[$count][1];
        $voters = $resultArray[$count][3];
        $column = &$pntable['poll_data_column'];
        $result2 = $dbconn->Execute("SELECT SUM($column[optioncount]) AS sum FROM $pntable[poll_data] WHERE $column[pollid]=".pnVarPrepForStore($id)."");
        list($sum) = $result2->fields;
        echo "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;pollID=$id\">".pnVarPrepForDisplay($pollTitle)."</a> ";
        echo "(<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;req=results&amp;pollID=$id&amp;$commentoptions\">"._RESULTS."</a> - $sum "._LVOTES.")<br />\n";
    }
    echo '</td></tr></table>';
    CloseTable();
}

function pollResults($pollID)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

	// if pollID isn't set use the latest pollID. Skooter
    //if(!isset($pollID)) $pollID = 1;  //could be a bug if pollID '1' is deleted. Skooter
    if(!isset($pollID) || empty($pollID) || 
        (is_numeric($pollID) && ($pollID == 0)) ) {
    	$pollID = pollLatest();
	}
    $column = &$pntable['poll_desc_column'];
    $result = $dbconn->Execute("SELECT $column[polltitle] FROM $pntable[poll_desc] WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID));
    list($holdtitle) = $result->fields;
    echo "<br /><span class=\"pn-normal\"><b>".pnVarPrepForDisplay($holdtitle)."</b></span><br /><br />";
    $result->Close();
    $column = &$pntable['poll_data_column'];
    $result = $dbconn->Execute("SELECT SUM($column[optioncount]) AS sum FROM $pntable[poll_data] WHERE $column[pollid]=".(int)pnVarPrepForStore($pollID));
    list($sum) = $result->fields;
    $result->Close();
    echo "<table border=\"0\">";
    /* cycle through all options */
    $column = &$pntable['poll_data_column'];
    $result = $dbconn->Execute("SELECT $column[optiontext], $column[optioncount] FROM $pntable[poll_data] WHERE ($column[pollid]=".(int)pnVarPrepForStore($pollID)." AND $column[optiontext] NOT LIKE \"\") ORDER BY $column[voteid]");
    while(list($optionText, $optionCount) = $result->fields) {

        $result->MoveNext();
        echo "<tr><td>";
        echo "<span class=\"pn-normal\">".pnVarPrepForDisplay($optionText)."</span>";
        echo "</td>";
        if($sum) {
            $percent = 100 * $optionCount / $sum;
        } else {
            $percent = 0;
        }
        echo "<td>";
        $percentInt = (int)$percent * 4 * pnConfigGetVar('BarScale');
        $percent2 = (int)$percent;

        $ThemeSel = pnUserGetTheme();

        if ($percent > 0) {
            echo "<img src=\"themes/$ThemeSel/images/leftbar.gif\" height=\"15\" width=\"7\" alt=\"$percent2 %\" />";
            echo "<img src=\"themes/$ThemeSel/images/mainbar.gif\" height=\"15\" width=\"$percentInt\" alt=\"$percent2 %\" />";
            echo "<img src=\"themes/$ThemeSel/images/rightbar.gif\" height=\"15\" width=\"7\" alt=\"$percent2 %\" />";
        } else {
            echo "<img src=\"themes/$ThemeSel/images/leftbar.gif\" height=\"15\" width=\"7\" alt=\"$percent2 %\" />";
            echo "<img src=\"themes/$ThemeSel/images/mainbar.gif\" height=\"15\" width=\"3\" alt=\"$percent2 %\" />";
            echo "<img src=\"themes/$ThemeSel/images/rightbar.gif\" height=\"15\" width=\"7\" alt=\"$percent2 %\" />";
        }
        printf("<span class=pn-normal> %.2f %% (%d)</span>", $percent, $optionCount);
        echo "</td></tr>";
    }
    echo "</table><br />";
    echo "<p style=\"text-align:center\"><span class=\"pn-normal\">";
    echo "<b>"._TOTALVOTES." $sum</b><br />";
    echo "</span><span class=\"pn-sub\">"._ONEPERDAY."</span><span class=\"pn-normal\"><br /><br />";
    $booth = $pollID;
    echo("[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index&amp;pollID=$booth\">"._VOTING."</a> | ");
    echo("<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=NS-Polls&amp;file=index\">"._OTHERPOLLS."</a> ]</span></p>");
    return(1);
}

function blocks_poll_block($row)
{
// for MSSQL that always have an space
    $row['content'] = trim($row['content']);
    if (!empty($row['content'])) {
        $pollID = $row['content'];
    } else {
        $pollID = pollLatest();
    }
    return pollMain($pollID, $row);
}

function blocks_poll_select($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();


    $zerochecked = "";
    $onechecked = "";

    if (!empty($row['content'])) {
        $pollID = $row['content'];
        $showspecific = 1;
        $onechecked = "checked";
    } else {
        $pollID = 0;
        $showspecific = 0;
        $zerochecked = "checked";
    }

    $output = "<tr><td class=\"pn-title\">"._POLL_DISPLAY.":</td></tr>";
    $output .= "<tr><td class=\"pn-normal\">"._POLL_LATEST."</td><td><input type=\"radio\" name=\"polltype\" value=\"0\" $zerochecked></td></tr>";
    $output .= "<tr><td class=\"pn-normal\">"._POLL_SPECIFIC."</td><td><input type=\"radio\" name=\"polltype\" value=\"1\" $onechecked>&nbsp;&nbsp;";

    $output .= "<select name=\"pollid\">";

    // Get list of polls
    $polltable = $pntable['poll_desc'];
    $pollcolumn = $pntable['poll_desc_column'];
    $sql = "SELECT $pollcolumn[polltitle],
                   $pollcolumn[pollid]
            FROM $polltable
            ORDER BY $pollcolumn[polltitle]";
    $result = $dbconn->Execute($sql);
    while(list($title, $id) = $result->fields) {
        $result->MoveNext();
        $output .= "<option value=\"$id\"";
        if ($pollID == $id) {
           $output .= " selected";
        }
        $output .= ">".pnVarPrepForDisplay($title)."</option>";
    }
    $result->Close();

    $output .= "</select></td></tr>";

    return $output;
}

function blocks_poll_update($row)
{
    if (($row['polltype'] == 1) && (!empty($row['pollid']))) {
        $row['content'] = $row['pollid'];
    } else {
        $row['content'] = "";
    }

    return($row);
}
?>
