<?php // $Id: sections.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Purpose: Search sections
// ----------------------------------------------------------------------

$search_modules[] = array(
    'title' => 'Sections',
    'func_search' => 'search_sections',
    'func_opt' => 'search_sections_opt'
);

function search_sections_opt() {
    global
        $bgcolor2,
        $textcolor1,
        $secname,
        $secid;

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'Sections::Section', "$secname::$secid", ACCESS_READ)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\"
        style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_sections\" id=\"active_sections\"
        value=\"1\" checked>&nbsp;"._SEARCH_SECTIONS."</font></td></tr></table>");
    }

    return $output->GetOutput();
}
function search_sections() {

    list($active_sections,
         $startnum,
         $total,
         $bool,
         $q) = pnVarCleanFromInput('active_sections',
                                   'startnum',
                                   'total',
                                   'bool',
                                   'q');

    if(empty($active_sections)) {
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

    $seccol = &$pntable['seccont_column'];
    $query = "SELECT $seccol[artid] as id, $seccol[title] as title
              FROM $pntable[seccont]
              WHERE \n";
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
        $query .= "$seccol[title] LIKE '$word' OR \n";
        $query .= "$seccol[content] LIKE '$word')\n";
        $flag = true;
    }
    if (pnConfigGetVar('multilingual') == 1) {
           $query .= " AND ($seccol[slanguage]='" . pnVarPrepForStore(pnUserGetLang()) . "' OR $seccol[slanguage]='')";
    }
    $query .= " ORDER BY $seccol[artid]";


    if (empty($total)) {
        $countres = $dbconn->Execute($query);
        $total = $countres->PO_RecordCount();
        $countres->Close();
    }
    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if(!$result->EOF) {
        $output->Text('<font class="pn-normal">' . _SECTIONS . ': ' . $total . ' ' . _SEARCHRESULTS . '</font>');

        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_sections=1&amp;bool=$bool&amp;q=$q";

        $output->Text('<ul>');
        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $output->Text("<li><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=Sections&amp;file=index&amp;req=viewarticle&amp;artid=$row[id]\">$row[title]</a><br></li>");
            $result->MoveNext();
        }
        $output->Text('</ul>');

        // Munge URL for template
        $urltemplate = $url . "&amp;startnum=%%&amp;total=$total";
        $output->Pager($startnum,
                       $total,
                       $urltemplate,
                       10);
    } else {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<font class="pn-normal">'._SEARCH_NO_SECTIONS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>