<?php // $Id: stories.php,v 1.7 2002/11/30 11:51:09 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// PostNuke: Content Management System
// ====================================
// Module: Search/stories/topics plugin
//
// Copyright (c) 2001 by the PostNuke development team
// http://www.postnuke.com
// -----------------------------------------------------------------------
// Modified version of:
//
// Search Module
// ===========================
//
// Copyright (c) 2001 by Patrick Kellum (webmaster@ctarl-ctarl.com)
// http://www.ctarl-ctarl.com
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
//
// Filename: modules/Search/stories.php
// Original Author: Patrick Kellum
// Purpose: Search reviews/users/stories/topics
// -----------------------------------------------------------------------

$search_modules[] = array(
    'title' => 'Stories',
    'func_search' => 'search_stories',
    'func_opt' => 'search_stories_opt'
);
function search_stories_opt() {
    global
        $bgcolor2,
        $textcolor1,
        $info;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'Stories::', "::", ACCESS_READ)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\" style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_stories\" id=\"active_stories\" value=\"1\" checked>&nbsp;<label for=\"active_stories\">"._SEARCH_STORIES_TOPICS."</label></font></td></tr></table>");
        $output->Text("<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" summary=\"Search form to help in locating the stories you are looking for.\"><tr><td nowrap align=\"right\" valign=\"top\"><font class=\"pn-normal\"><label for=\"stories_topics[]\">"._TOPIC."</label>:</font></td><td><select name=\"stories_topics[]\" id=\"stories_topics[]\" multiple><option value=\"\" selected>"._SRCHALLTOPICS."</option>");
    $column = &$pntable['topics_column'];
    $result = $dbconn->Execute("SELECT $column[tid] as topicid, $column[topictext] as topictext, $column[topicname] as topicname
                              FROM $pntable[topics]
                              ORDER BY $column[topictext]");

    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        if (pnSecAuthAction(0, 'Topics::Topic', "$row[topicname]::$row[topicid]", ACCESS_READ)){
	        if(strlen($row['topictext']) > 23) {
    	        $row['topictext'] = substr($row['topictext'],0,20) . '...';
    	    } 
        	$output->Text("<option value=\"$row[topicid]\">$row[topictext]</option>");
		}
        $result->MoveNext();
    }
    $output->Text("</select></td></tr>");
// categories
    $output->Text("<tr><td nowrap align=\"right\" valign=\"top\"><font class=\"pn-normal\"><label for=\"stories_cat[]\">"._CATEGORY."</label>:</font></td><td><select name=\"stories_cat[]\" id=\"stories_cat[]\" multiple><option value=\"\" selected>All Categories</option>");
    $column = &$pntable['stories_cat_column'];
    $result = $dbconn->Execute("SELECT $column[catid] as catid, $column[title] as title
                              FROM $pntable[stories_cat]
                              ORDER BY $column[title]");

    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        if (pnSecAuthAction(0, 'Stories::Story', ":$row[title]:", ACCESS_READ)) {
	        if(strlen($row['title']) > 23) {
	            $row['title'] = substr($row['title'],0,20) . '...';
	        }
	        $output->Text("<option value=\"$row[catid]\">$row[title]</option>");
	}
        $result->MoveNext();
    }
    $output->Text("</select></td></tr>");

// author
    $output->Text("<tr><td nowrap align=\"right\" valign=\"top\"><font class=\"pn-normal\"><label for=\"stories_author\">"._AUTHOR."</label>:</font></td><td colspan=\"3\"><font class=\"pn-normal\"><input type=\"text\" name=\"stories_author\" id=\"stories_author\" size=\"20\" maxlength=\"255\"></font></td></tr></table>");
    }

    return $output->GetOutput();
}
function search_stories() {

    list($startnum,
         $active_stories,
         $total,
         $stories_topics,
         $stories_cat,
         $stories_author,
         $q,
         $bool) = pnVarCleanFromInput('startnum',
                                      'active_stories',
                                      'total',
                                      'stories_topics',
                                      'stories_cat',
                                      'stories_author',
                                      'q',
                                      'bool');

    if(!isset($active_stories) || !$active_stories) {
        return;
    }

    $output = new pnHTML();

    if (!isset($startnum)) {
        $startnum = 1;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (empty($bool)) {
        $bool = 'OR';
    }

    $flag = false;

    $storcol = &$pntable['stories_column'];
    $stcatcol = &$pntable['stories_cat_column'];
    $topcol = &$pntable['topics_column'];
    $query = "";
    $query1 = "SELECT $storcol[sid] as sid,
                     $topcol[tid] as topicid,
                     $topcol[topicname] as topicname,
                     $topcol[topictext] as topictext,
                     $storcol[catid] as catid,
                     $storcol[time] AS fdate,
                     $storcol[title] AS story_title,
                     $storcol[aid] AS aid,
                     $stcatcol[title] AS cat_title
               FROM $pntable[stories]
               LEFT JOIN $pntable[stories_cat] ON ($storcol[catid]=$stcatcol[catid])
               LEFT JOIN $pntable[topics] ON ($storcol[topic]=$topcol[tid])
               WHERE ";

    // hack to get this to work, but much better than what we had before
    //$query .= " 1 = 1 ";
// words
    $w = search_split_query($q);
    if (isset($w)) {
        foreach($w as $word) {
            if($flag) {
                switch($bool) {
                    case 'AND' :
                        $query .= ' AND ';
                        break;
                    case 'OR' :
                    default :
                        $query .= ' OR ';
                        break;
                }
            }
            $query .= '(';
            $query .= "$storcol[title] LIKE '$word' OR ";
            $query .= "$storcol[hometext] LIKE '$word' OR ";
            $query .= "$storcol[bodytext] LIKE '$word' OR ";
            $query .= "$storcol[comments] LIKE '$word' OR ";
            $query .= "$storcol[informant] LIKE '$word' OR ";
            $query .= "$storcol[notes] LIKE '$word'";
            $query .= ')';
            $flag = true;
            $no_flag = false;
        }
    } else {
     $no_flag = true;
    }
// topics
    if(isset($stories_topics) && (!empty($stories_topics))) {
        $flag = false;
        $start_flag = false;
            // dont set AND/OR if nothing is in front
            foreach($stories_topics as $v) {
              if (empty($v)) continue;
                if ( (!$no_flag) and (!$start_flag) ) {
                  $query .= " AND (";
                  $start_flag = true;
                }
              if ($flag) $query .= " OR ";
              $query .= "$storcol[topic]=$v";
              $flag = true;
            }
          if ( (!$no_flag) and ($start_flag) ) {
              $query .= ") ";
              $no_flag = false;
          }
    }
// categories
if (!is_array($stories_cat)) $stories_cat[0] = '';
    if(isset($stories_cat[0]) &&(!empty($stories_cat[0]))) {
          if (!$no_flag) {
            $query .= " AND (";
          }
        $flag = false;
        foreach($stories_cat as $v) {
              if($flag) {
                  $query .= " OR ";
              }
            $query .= "$stcatcol[catid]=$v";
            $flag = true;
        }
          if (!$no_flag) {
              $query .= ") ";
              $no_flag = false;
          }
    }
// authors
    if(isset($stories_author) && $stories_author != "") {
          if (!$no_flag) {
            $query .= " AND (";
          }
        $query .= "$storcol[informant]='$stories_author'";

        $result = $dbconn->Execute("SELECT {$pntable['users_column']['uid']} as pn_uid FROM $pntable[users] WHERE {$pntable['users_column']['uname']} LIKE '%$stories_author%' OR {$pntable['users_column']['name']} LIKE '%$stories_author%'");
         while(!$result->EOF) {
             $row = $result->GetRowAssoc(false);
             $query .= " OR $storcol[aid]=$row[pn_uid]";
             $result->MoveNext();
         }
          if (!$no_flag) {
              $query .= ") ";
              $no_flag = false;
          }
    }
    else
    {
        $stories_author = '';
    }

    if (pnConfigGetVar('multilingual') == 1) {
           if (!empty($query)) $query .= " AND";
           $query .= " ($storcol[alanguage]='" . pnVarPrepForStore(pnUserGetLang()) . "' OR $storcol[alanguage]='')";
    }
    if (empty($query)) $query = "1";
    $query .= " ORDER BY $storcol[time] DESC";
    $query = $query1.$query;

    if (empty($total)) {
   // echo $query;
   	$total = 0;
        $countres = $dbconn->Execute($query);
        //$total = $countres->PO_RecordCount();
        while(!$countres->EOF) {
        	$row = $countres->GetRowAssoc(false);
        	if (pnSecAuthAction(0, 'Stories::Story', "$row[aid]:$row[cat_title]:$row[sid]", ACCESS_READ) && pnSecAuthAction(0, 'Topics::Topic', "$row[topicname]::$row[topicid]", ACCESS_READ)) {
        		$total++;
        	}
        	$countres->MoveNext();
        	// $countres->Close();
        }
    }

    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if(!$result->EOF) {
        $output->Text(_STORIES_TOPICS . ': ' . $total . ' ' . _SEARCHRESULTS);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        // Rebuild the search string from previous information
        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_stories=1&amp;stories_author=".$stories_author;
        if (isset($stories_cat) && $stories_cat) {
            foreach($stories_cat as $v) {
                $url .= "&amp;stories_cat%5B%5D=$v";
            }
        }
        if (isset($stories_topics) && $stories_topics){
            foreach($stories_topics as $v) {
                $url .= ("&amp;stories_topics%5B%5D=$v");
            }
        }
        $url .= "&amp;bool=".$bool;
        if (isset($q)) {
            $url .= "&amp;q=".$q;
        }

        $output->Text("<ul>");

        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);
	    if (pnSecAuthAction(0, 'Stories::Story', "$row[aid]:$row[cat_title]:$row[sid]", ACCESS_READ) && pnSecAuthAction(0, 'Topics::Topic', "$row[topicname]::$row[topicid]", ACCESS_READ)) {
	            $row['fdate'] = ml_ftime(_DATELONG,$result->UnixTimeStamp($row['fdate']));
	            $output->Text("<li>");
	            if (!empty($row['topicid'])) {
	                $output->Text("<b><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_stories=1&amp;stories_topics[0]=".$row['topicid']."\">".$row['topictext']."</a></b> - ");
	            }
	            if (!empty($row['catid'])) {
	                $output->Text("<a href=\"index.php?catid=".$row['catid']."\">".$row['cat_title']."</a>: ");
	            }
	
	            if ($row['story_title'] == '') {
	                $row['story_title'] = 'No Title';
	            }
	
	            $output->Text('<i><a class="pn-normal" href="modules.php?op=modload&amp;name=News&amp;file=article&amp;sid='.$row['sid'].'">'. pnVarPrepHTMLDisplay($row['story_title']) . '</a></i> - ' .$row['fdate']."</li>");
	   }
           $result->MoveNext();
        }
        $output->Text("</ul>");
        $output->Linebreak(4);

        // Munge URL for template
        $urltemplate = $url . "&amp;startnum=%%&amp;total=$total";
        $output->Pager($startnum,
                       $total,
                       $urltemplate,
                       10);
    } else {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<font class="pn-normal">'._SEARCH_NO_STORIES_TOPICS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>