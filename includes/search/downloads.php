<?php // $Id: downloads.php,v 1.2 2002/11/14 20:46:02 larsneo Exp $ $Name:  $
// ----------------------------------------------------------------------
// Post-Nuke: Content Management System
// ====================================
// Module: Search/downloads plugin
//
// Copyright (c) 2001 by the Post Nuke development team
// http://www.postnuke.com
// ----------------------------------------------------------------------
// Search Module
// ===========================
//
// Copyright (c) 2001 by Patrick Kellum (webmaster@ctarl-ctarl.com)
// http://www.ctarl-ctarl.com
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
// ----------------------------------------------------------------------
// Filename: includes/search/downloads.php
// Original Author: Patrick Kellum
// Purpose: Search downloads
// ----------------------------------------------------------------------
// Download plugin: adam_baum, based on Patrick Kellum's reviews plugin.
// ----------------------------------------------------------------------

$search_modules[] = array(
    'title' => 'Downloads',
    'func_search' => 'search_downloads',
    'func_opt' => 'search_downloads_opt'
);
function search_downloads_opt() {
    global
        $bgcolor2,
        $textcolor1;

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'Downloads::', '::', ACCESS_READ)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\" style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_downloads\" id=\"active_downloads\" value=\"1\" checked>&nbsp;<label for=\"active_downloads\">"._SEARCH_DOWNLOADS."</label></font></td></tr></table>");
    }

    return $output->GetOutput();
}

function search_downloads() {

    list($q,
         $active_downloads,
         $bool,
         $startnum,
         $total) = pnVarCleanFromInput('q',
                                       'active_downloads',
                                       'bool',
                                       'startnum',
                                       'total');

    if(empty($active_downloads)) {
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();

    if (!isset($startnum)) {
        $startnum = 1;
    }

    $w = search_split_query($q);
    $flag = false;
    // fifers: have to explicitly name the columns so that if the underlying DB column names change, the code to access them doesn't.  We use the column names in assoc array later...
    $column = &$pntable['downloads_downloads_column'];
    $query = "SELECT $column[lid] as lid, $column[title] as title, $column[totalvotes] as totalvotes, $column[hits] as hits, $column[name] as name, $column[description] as description, $column[cid] as cid FROM $pntable[downloads_downloads] WHERE \n";

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
        // downloads
        $query .= "$column[description] LIKE '$word' OR \n";
        $query .= "$column[submitter] LIKE '$word' OR \n";
        $query .= "$column[name] LIKE '$word' OR \n";
        $query .= "$column[homepage] LIKE '$word' \n";
        $query .= ')';
        $flag = true;
    }
    $query .= " ORDER BY $column[lid]";

    if (empty($total)) {
        $countres = $dbconn->Execute($query);
        $total = $countres->PO_RecordCount();
        $countres->Close();
    }

    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if(!$result->EOF) {
        $output->Text(_DOWNLOADS . ': ' . $total . ' ' . _SEARCHRESULTS);
        $output->SetInputMode(_PNH_VERBATIMINPUT);

        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_downloads=1&amp;bool=$bool&amp;q=$q";

        $output->Text("<ul>");

        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);
			if (pnSecAuthAction(0, 'Downloads::Item', "$row[title]::$row[lid]", ACCESS_READ) && pnSecAuthAction(0, 'Downloads::Category', "::$row[cid]", ACCESS_READ)) {
            	$output->Text("<li><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Downloads&amp;file=index&amp;req=getit&lid=$row[lid]\">$row[title]</a> <font class=\"pn-normal\">(votes: $row[totalvotes] - hits: $row[hits])</font><br>Uploader: $row[name]<br>$row[description]</li>");
			}
            $result->MoveNext();
        }
        $output->Text("</ul>");
        $output->Linebreak(4);

        // Mung URL for template
        $urltemplate = $url . "&amp;startnum=%%&amp;total=$total";
        $output->Pager($startnum,
                $total,
                $urltemplate,
                10);
    } else {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<font class="pn-normal">'._SEARCH_NO_DOWNLOADS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>