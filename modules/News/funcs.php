<?php
// File: $Id: funcs.php,v 1.11 2002/11/30 13:44:55 class007 Exp $
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
// Original Author of file: Jim McDonald
// Purpose of file: Utility functions for news
// ----------------------------------------------------------------------

/*
 * Get an array of articles given specific
 * where and limit clauses
 */
function getArticles($where, $order, $limit, $startnum=0) {

	// $numstories is used by multi-column themes (need to know how many stores in advance)
    //  commenting out this global since three lines below we immediately set it to 0.  Do we need this???? Skooter
    //global $numstories;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

	$numstories=0;
    // Columns
    $storiescolumn = $pntable['stories_column'];
    $storiescatcolumn= $pntable['stories_cat_column'];
    $topicscolumn = $pntable['topics_column'];

    // Base query
    $query = "SELECT $storiescolumn[aid] AS \"aid\",
                    $storiescolumn[bodytext] AS \"bodytext\",
                    $storiescatcolumn[themeoverride] AS \"catthemeoverride\",
                    $storiescolumn[catid] AS \"cid\",
                    $storiescatcolumn[title] AS \"cattitle\",
                    $storiescolumn[comments] AS \"comments\",
                    $storiescolumn[counter] AS \"counter\",
                    $storiescolumn[hometext] AS \"hometext\",
                    $storiescolumn[informant] AS \"informant\",
                    $storiescolumn[notes] AS \"notes\",
                    $storiescolumn[sid] AS \"sid\",
                    $storiescolumn[themeoverride] AS \"themeoverride\",
                    $topicscolumn[topicid] AS \"tid\",
                    $storiescolumn[time] AS \"time\",
                    $storiescolumn[title] AS \"title\",
                    $topicscolumn[topicname] AS \"topicname\",
                    $topicscolumn[topicimage] AS \"topicimage\",
                    $topicscolumn[topictext] AS \"topictext\",
                    $topicscolumn[counter] AS \"tcounter\",
                    $storiescolumn[time] AS \"unixtime\",
                    $storiescolumn[withcomm] AS \"withcomm\"
             FROM $pntable[stories]";

    // left join syntax is not consistent.  We check for oracle and assume
    // MySQL if not.  Other databases added here.
    if (strcmp(pnConfigGetVar('dbtype'), 'oci8') == 0) {
        $query .=  " , $pntable[stories_cat], $pntable[topics]
                    WHERE $storiescolumn[catid]=$storiescatcolumn[catid](+)
                     AND $topicscolumn[topicid]=$storiescolumn[topic]";
        if (pnConfigGetVar('multilingual') == 1) {
            $query .= " AND ($storiescolumn[alanguage]='$currentlang' OR $storiescolumn[alanguage]='')";
        }

        if (!empty($limit)) {
            $query .= " AND ROWNUM < $limit";
        }

        if (!empty($where)) {
            $query .= " AND $where";
        }

        // User-added order by clause
        if (!empty($order)) {
            $query .= " ORDER BY $order";
        }

    } else {   // Assume mysql
        $query .= " LEFT JOIN $pntable[stories_cat] ON $storiescolumn[catid] = $storiescatcolumn[catid]
                    LEFT JOIN $pntable[topics] ON $storiescolumn[topic] = $topicscolumn[topicid]";

        if (pnConfigGetVar('multilingual') == 1) {
            $query .= " WHERE ($storiescolumn[alanguage]='" . pnUserGetLang() . "' OR $storiescolumn[alanguage]='')";
            if (!empty($where)) {
                $query .= " AND $where";
            }
        } else {
            if (!empty($where)) {
                $query .= " WHERE $where";
            }
        }

        // User-added order by clause
        if (!empty($order)) {
            $query .= " ORDER BY $order";
        }
		
        // Run query
        // Modified to use SelectLimit when necesary
        // just to be ADODB compliant
        if (!empty($order)) {
            $result = $dbconn->SelectLimit($query,$limit, $startnum);
        }
        else {
            $result = $dbconn->Execute($query);
        }
        // Error check
        if($dbconn->ErrorNo() != 0) {
            echo "DB Error: getArticles: " . $dbconn->ErrorNo() . ': ' . $dbconn->ErrorMsg() . '<br>';
            exit ();
        }

        // Compile rows
        $i = 0;
        $res = array();
		$numstories = $result->PO_RecordCount();
        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $row['unixtime']=$result->UnixTimeStamp($row['unixtime']);

            // A couple of bits for back-compatibility
            $row['topicid'] = $row['tid'];
            $row['topic'] = $row['tid'];
            $row['catid'] = $row['cid'];
            // add default category title for various pnSecAuthAction check
            // since _ARTICLES is a language define it's not that good idea, 
            // but almost all security checks are based on cattitle
            // 2002/11/17 larsneo
            if ($row['cattitle'] == "") { $row['cattitle'] = "". _ARTICLES . ""; }
            $res[$i] = $row;
            $i++;
            $result->MoveNext();
        }

        $result->Close();
        return($res);
    }
}

/*
 * Generate raw information for a given article
 * Requires row to have previously gone through
 * getArticles() and meet the prerequisites
 * for it
 */
function genArticleInfo($row) {


    setlocale(LC_TIME, pnConfigGetVar('locale'));

    // Copy stuff passed in so far
    $info = $row;

    // Version
    $info['version'] = 1;

    // Dates
    $info['longdatetime'] = ml_ftime(_DATETIMELONG, GetUserTime($info['unixtime']));
    $info['briefdatetime'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($info['unixtime']));
    $info['longdate'] = ml_ftime(_DATELONG, GetUserTime($info['unixtime']));
    $info['briefdate'] = ml_ftime(_DATEBRIEF, GetUserTime($info['unixtime']));

    // Tidy-up
    $anonymous = pnConfigGetVar('anonymous');
    if (empty($info['informant'])) {
        $info['informant'] = $anonymous;
    }

    list($info['title'],
         $info['hometext'],
         $info['bodytext'],
         $info['notes']) = pnModCallHooks('item',
                                          'transform',
                                          '',
                                          array($info['title'],
                                                $info['hometext'],
                                                $info['bodytext'],
                                                $info['notes']));

    // Title should not have any URLs in it
    $info['title'] = preg_replace('/<a\s+.*?>(.*?)<\/a>/i', '\\1', $info['title']);
    $info['title'] = pnVarPrepForDisplay(pnVarCensor($info['title']));
    $info['hometext'] = pnVarPrepHTMLDisplay(pnVarCensor($info['hometext']));
    $info['bodytext'] = pnVarPrepHTMLDisplay(pnVarCensor($info['bodytext']));
    $info['notes'] = pnVarPrepHTMLDisplay(pnVarCensor($info['notes']));
    $info['cattitle'] = pnVarPrepHTMLDisplay(pnVarCensor($info['cattitle']));

    // Create 'Cateogry: title'-style header -- Credit to Rabbit for the older theme compatibility.
    if ($info['catid']) {
        $info['catandtitle'] = $info['cattitle'].": ".$info['title'];
    } else {
        $info['catandtitle'] = $info['title'];
    }

    $info['maintext'] = $info['hometext']."\n".$info['bodytext'];
    if (!empty($info['notes'])) {
        $info['fulltext'] = $info['maintext']."\n".$info['notes'];
    } else {
        $info['fulltext'] = $info['maintext'];
    }

    return($info);
}

/*
 * Generate an array of links for a given article
 * Requires info to have previously gone through
 * genArticleInfo() and meet the prerequisites
 * for it
 */
function genArticleLinks($info) {

    // Component and instance
    $component = 'Stories::Story';
    $instance = "$info[aid]:$info[cattitle]:$info[sid]";

    $commentextra = pnUserGetCommentOptions();

    // Allowed to comment?
    if (pnSecAuthAction(0, $component, $instance, ACCESS_COMMENT)) {
        $comment = "modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$info[sid]&amp;$commentextra";
    } else {
        $comment = "";
    }

    // Allowed to read full article?
    if (pnSecAuthAction(0, $component, $instance, ACCESS_READ)) {
        $fullarticle = "modules.php?op=modload&amp;name=News&amp;file=article&amp;sid=$info[sid]&amp;$commentextra";
    } else {
        $fullarticle = "";
    }

    // Link to topic image if there is a topic
    if (!empty($info['tid'])) {
        $searchtopic = "modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=&amp;topic=$info[tid]";
    } else {
        $searchtopic = '';
    }

    // Set up the array itself
    $links = array (
                    "category" => "modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=$info[catid]",
                    "comment" => $comment,
                    "fullarticle" => $fullarticle,
                    "searchtopic" => $searchtopic,
                    "print" => "print.php?sid=$info[sid]",
                    "send" => "modules.php?op=modload&amp;name=Recommend_Us&amp;file=index&amp;req=FriendSend&amp;sid=$info[sid]",
                    "version" => 1
                    );

    return $links;
}

/*
 * Generate an array of preformatted HTML bites for a given article
 * Requires info to have previously gone through
 * genArticleInfo() and meet the prerequisites
 * for it
 * Requires links to have been generated from
 * genArticleLinks()
 */
function genArticlePreformat($info, $links) {

    // Component and instance
    $component = 'Stories::Story';
    $instance = "$info[aid]:$info[cattitle]:$info[sid]";

    // Only allow send if the module exists
    if (pnModAvailable("Recommend_Us")) {
        $send = "<a class=\"pn-normal\" href=\"$links[send]\"><img src=\"images/global/friend.gif\" border=\"0\" alt=\""._FRIEND."\"></a>";
    } else {
        $send = "";
    }

    $hometext = $info['hometext'];
    $bodytext = $info['bodytext'];

    // Only bother with readmore if there is more to read
    $bytesmore = strlen($info['bodytext']);
    $readmore = "";
    $bytesmorelink = "";
    if ($bytesmore > 0) {
        if (pnSecAuthAction(0, $component, $instance, ACCESS_READ)) {
            $readmore = "<a class=\"pn-normal\" href=\"$links[fullarticle]\">"._READMORE."</a>";
        }
        $bytesmorelink = "$bytesmore "._BYTESMORE;
    }

    // Allowed to read full article?
    if (pnSecAuthAction(0, $component, $instance, ACCESS_READ)) {
        $title = "<a class=\"pn-title\" href=\"$links[fullarticle]\">$info[title]</a>";
    } else {
        $title = $info['title'];
    }

    // Allowed to read full article?
    if (pnSecAuthAction(0, $component, $instance, ACCESS_READ)) {
        $print = "<a class=\"pn-normal\" href=\"$links[print]\"><img src=\"images/global/print.gif\" border=\"0\" alt=\""._PRINTER."\"></a>";
    } else {
        $print = '';
    }

    // Work out how to say 'comment(s)(?)' correctly
    $comment = "";
    if ($info['withcomm'] == 0) {
        if ($info['comments'] == 0) {
            $comment = _COMMENTSQ;
        } else if ($info['comments'] == 1) {
            $comment = "1 " . _COMMENT;
        } else {
            $comment = "$info[comments] "._COMMENTS;
        }
    }

    // Allowed to comment?
    if (pnSecAuthAction(0, $component, $instance, ACCESS_COMMENT)) {
        $comment = "<a class=\"pn-normal\" href=\"$links[comment]\">$comment</a>";
    } else if (pnSecAuthAction(0, $component, $instance, ACCESS_READ)) {
        $comment = "$comment";
    } else {
        $comment = "";
    }

    // Notes, if there are any
    if (!empty($info['notes'])) {
        $notes = _NOTE." <i>$info[notes]</i>";
    } else {
        $notes = "";
    }

    // Set up the array itself
    $preformat = array(
                       "bodytext" => $bodytext,
                       "bytesmore" => $bytesmorelink,
                       "category" => "<a href=\"$links[category]\">$info[cattitle]</a>",
                       "comment" => $comment,
                       "hometext" => $hometext,
                       "notes" => $notes,
                       "searchtopic" => "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=News&amp;file=index&amp;catid=&amp;topic=$info[tid]\"><img src=\"".pnConfigGetVar('tipath')."$info[topicimage]\" border=\"0\" Alt=\"$info[topictext]\" align=\"left\" hspace=\"5\" vspace=\"5\" ></a>",
                       "print" => $print,
                       "readmore" => $readmore,
                       "send" => $send,
                       "title" => $title,
                       "version" => 1
                       );

    // More complex extras - use values in the array
    $preformat['more'] = "";
    if ($bytesmore > 0) {
        $preformat['more'] .= "$preformat[readmore] ($preformat[bytesmore]) ";
    }
    $preformat['more'] .= "$preformat[comment] $preformat[send] $preformat[print]";

    if ($info['catid']) {
        $preformat['catandtitle'] = "$preformat[category]: $preformat[title]";
    } else {
        $preformat['catandtitle'] = $preformat['title'];
    }

    $preformat['maintext'] = "$preformat[hometext]<BR><BR>$preformat[bodytext]";
    if (!empty($preformat['notes'])) {
        $preformat['fulltext'] = "$preformat[maintext]<BR><BR>$preformat[notes]";
    } else {
        $preformat['fulltext'] = "$preformat[maintext]";
    }

    return $preformat;
}

/**
 * Updates autonews
 *
 * This function selects all appropriate autonews items from the autonews table
 * (keeping in mind the current time and language) and removes them from that
 * table; it also inserts them into the stories table.
 *
 * @return none
 * @author ?
 */

function automatednews()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['autonews_column'];
        $querylang = "WHERE ($column[alanguage]='$currentlang' OR $column[alanguage]='')";
    } else {
        $querylang = "";
    }
    $today = getdate();
    $day = $today['mday'];
    if ($day < 10) {
        $day = "0$day";
    }
    $month = $today['mon'];
    if ($month < 10) {
        $month = "0$month";
    }
    $year = $today['year'];
    $hour = $today['hours'];
    if ($hour < 10) {
        $hour = "0$hour";
    }
    $min = $today['minutes'];
    if ($min < 10) {
        $min = "0$min";
    }
    $sec = "00";
    $column = &$pntable['autonews_column'];
    $result = $dbconn->Execute("SELECT $column[anid], $column[time]
                              FROM $pntable[autonews]
                              $querylang");
    while(list($anid, $time) = $result->fields) {

        $result->MoveNext();
// EugenioBaldi Change the test of date
//        ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $date);
        $timenews = substr($time, 0,4).substr($time, 5,2).substr($time, 8,2).substr($time, 11,2).substr($time, 14,2).substr($time, 17,2) ;
        $timenewsn = $timenews + 1 - 1;
        $now = "$year$month$day$hour$min$sec";
        $nown = $now + 1 - 1;
        if ($timenewsn < $nown)     {
//        if (($date[1] <= $year) AND ($date[2] <= $month) AND ($date[3] <= $day)) {
//            if (($date[4] < $hour) AND ($date[5] >= $min) OR ($date[4] <= $hour) AND ($date[5] <= $min)) {
            $column = &$pntable['autonews_column'];
            $result2 = $dbconn->Execute("SELECT $column[catid], $column[aid], $column[title], $column[hometext], $column[bodytext], $column[topic], $column[informant], $column[notes], $column[ihome], $column[alanguage] FROM $pntable[autonews] WHERE $column[anid]='$anid'");

                while(list($catid, $aid, $title, $hometext, $bodytext, $topic, $author, $notes, $ihome, $alanguage) = $result2->fields) {
                    $title = stripslashes(FixQuotes($title));
                    $hometext = stripslashes(FixQuotes($hometext));
                    $bodytext = stripslashes(FixQuotes($bodytext));
                    $notes = stripslashes(FixQuotes($notes));
                    $scol = &$pntable['stories_column'];
                    $new_sid = $dbconn->GenId($pntable['stories']);
                    $insert = $dbconn->Execute("INSERT INTO $pntable[stories] ($scol[sid], $scol[cid], $scol[aid], $scol[title], $scol[time], $scol[hometext], $scol[bodytext], $scol[comments], $scol[counter], $scol[topic], $scol[informant], $scol[notes], $scol[ihome], $scol[themeoverride], $scol[alanguage] ) VALUES ($new_sid, '$catid', '$aid', '$title', now(), '$hometext', '$bodytext', '0', '0', '$topic', '$author', '$notes', '$ihome', '', '$alanguage')");
                    if ($insert === false) {
                        error_log ("Problem deleting autonews story: $anid, '$title'");
                        PN_DBMsgError($dbconn, __FILE__, __LINE__, "Problem deleting autonews story");
                    } else {
                        $dbconn->Execute("DELETE FROM $pntable[autonews] where $column[anid]='$anid'");
                                        }
                    $result2->MoveNext();
                }
//            }
        }
    }
}
?>