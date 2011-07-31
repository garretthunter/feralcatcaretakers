<?php
// File: $Id: topic.php,v 1.2 2003/01/01 13:18:10 larsneo Exp $ $Name:  $
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

$blocks_modules['topic'] = array(
	'func_display' => 'blocks_topic_block',
        'text_type' => 'Topics',
        'text_type_long' => 'Topics Menu',
        'allow_multiple' => false,
        'form_content' => false,
        'form_refresh' => false,
        'show_preview' => true
	);

pnSecAddSchema('Topicblock::', 'Block title::');

function blocks_topic_block($row)
{
	//global $topic, $catid;
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $currentlang = pnUserGetLang();

    if (!pnSecAuthAction(0, 'Topicblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

	$language = pnConfigGetVar('language');
  
	if (pnConfigGetVar('multilingual') == 1) {
		$column = &$pntable['stories_column'];
		$querylang = "AND ($column[alanguage]='$currentlang' OR $column[alanguage]='')"; /* the OR is needed to display stories who are posted to ALL languages */
	} else {
		$querylang = '';
	}
	$column = &$pntable['topics_column'];
	$result = $dbconn->Execute("SELECT $column[topicid] AS topicid, $column[topicname] as topicname FROM $pntable[topics] ORDER BY topicname");
	if ($result->EOF) {
		return;
	} else {
	    $boxstuff = '<span class="pn-normal">';
		if ($topic == "") {
			$boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<b><a href=\"modules.php?op=modload&amp;name=Topics&amp;file=index\">"._ALL_TOPICS."</a></b><br>";
    	} else {
			$boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<a href=\"modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=$catid\">"._ALL_TOPICS."</a><br>";
		}

		while(!$result->EOF) {
    	    $srow = $result->GetRowAssoc(false);
        	$result->MoveNext();
        	if (authorised(0, 'Topics::Topic', "$srow[topicname]::$srow[topicid]", ACCESS_READ)) { 
				$column = &$pntable['stories_column'];
				$result2 = $dbconn->Execute("SELECT $column[time] AS unixtime FROM $pntable[stories] WHERE $column[topic]=$srow[topicid] $querylang ORDER BY $column[time] DESC");

				if (!$result2->EOF) {
					$story = $result2->GetRowAssoc(false);
					$story['unixtime']=$result2->UnixTimeStamp($story['unixtime']);
					$sdate = ml_ftime(_DATEBRIEF, $story['unixtime']);
					if ($topic == $srow['topicid']) {
						$boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<span class=\"pn-title\"><b>$srow[topicname]</b></span> <span class=\"pn-sub\">($sdate)</span><br>";
					} else {
						$boxstuff .= "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=$catid&amp;topic=$srow[topicid]\">$srow[topicname]</a> <span class=\"pn-sub\">($sdate)</span><br>";
					}
				}
			}
		}
	}
	$boxstuff .= '</span>';
	if (empty($row['title'])) {
		$row['title'] = _TOPICS;
	}
	$row['content'] = $boxstuff;
	return themesideblock($row);
}
?>
