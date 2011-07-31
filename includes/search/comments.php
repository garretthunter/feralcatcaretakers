<?php // $Id: comments.php,v 1.2 2002/11/05 19:54:13 larsneo Exp $ $Name:  $
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
// Original Author: Patrick Kellum
// Purpose: Search comments
// ----------------------------------------------------------------------


$search_modules[] = array(
    'title' => 'Comments',
    'func_search' => 'search_comments',
    'func_opt' => 'search_comments_opt'
);

function search_comments_opt() {
    global
        $bgcolor2,
        $textcolor1,
        $info
    ;

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'Stories::Story', "$info[aid]:$info[cattitle]:$info[sid]", ACCESS_COMMENT)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\" style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_comments\" id=\"active_comments\" value=\"1\" checked>&nbsp;"._SEARCH_COMMENTS."</font></td></tr></table>");
    }

    return $output->GetOutput();
}

function search_comments() {

    list($active_comments,
         $startnum,
         $total,
         $bool,
         $q) = pnVarCleanFromInput('active_comments',
                                   'startnum',
                                   'total',
                                   'bool',
                                   'q');

    if(empty($active_comments)) {
        return;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (!isset($startnum)) {
        $startnum = 1;
    }

    $w = search_split_query($q);
    $flag = false;
    $column = &$pntable['comments_column'];
    $query = "SELECT $column[subject] as subject, $column[tid] as tid, ";
    $query .= "$column[sid] as sid, $column[pid] as pid FROM $pntable[comments] WHERE ";
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
        $query .= "$column[subject] LIKE '$word' OR ";
        $query .= "$column[comment] LIKE '$word'";
        $query .= ')';
        $flag = true;
    }
    $query .= " ORDER BY $column[subject]";

    if (empty($total)) {
        $countres = $dbconn->Execute($query);
        $total = $countres->PO_RecordCount();
        $countres->Close();
    }
    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if(!$result->EOF) {
        $output->Text('<font class="pn-normal">' . _COMMENTS . ': ' . $total . ' ' . _SEARCHRESULTS . '</font>');

        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_comments=1&amp;bool=$bool&amp;q=$q";

        $output->Text("<ul>");
        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);

            $output->Text("<li><a class=\"pn-normal\" href=\"modules.php?op=modload&name=NS-Comments&file=index&req=showeply&tid=$row[tid]&sid=$row[sid]&pid=$row[pid]\">$row[subject]</a></li>");
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
        $output->Text('<font class="pn-normal">'._SEARCH_NO_COMMENTS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>