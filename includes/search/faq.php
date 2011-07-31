<?php // $Id: faq.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Search Module
// ===========================
//
// Copyright (c) 2001 by Patrick Kellum (webmaster@ctarl-ctarl.com)
// http://www.ctarl-ctarl.com
//
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
// adam_baum: faq.php based on Patrick Kellum's reviews.php search plugin
//           purpose: search the faq database.

$search_modules[] = array(
    'title' => 'FAQS',
    'func_search' => 'search_faqs',
    'func_opt' => 'search_faqs_opt'
);
function search_faqs_opt() {
    global
        $bgcolor2,
        $textcolor1;

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'FAQ::', '::', ACCESS_READ)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\" style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_faqs\" id=\"active_faqs\" value=\"1\" checked>&nbsp;<label for=\"active_faqs\">"._SEARCH_FAQS."</label></font></td></tr></table>");
    }

    return $output->GetOutput();
}

function search_faqs() {

    list($q,
         $bool,
         $startnum,
         $total,
         $active_faqs) = pnVarCleanFromInput('q',
                                             'bool',
                                             'startnum',
                                             'total',
                                             'active_faqs');

    if (empty($active_faqs)) {
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
    $column = &$pntable['faqanswer_column'];
    $faqcatcol = &$pntable['faqcategories_column'];
    $query = "SELECT $column[id_cat] as id_cat, $column[question] as question, $column[answer] as answer
              FROM $pntable[faqanswer] LEFT JOIN $pntable[faqcategories] ON $column[id_cat]=$faqcatcol[id_cat]
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
        // faqs
        $query .= "$column[question] LIKE '$word' OR \n";
        $query .= "$column[answer] LIKE '$word'\n";
        $query .= ')';
        $flag = true;
    }
    if (pnConfigGetVar('multilingual') == 1) {
           $query .= " AND ($faqcatcol[flanguage]='" . pnVarPrepForStore(pnUserGetLang()) . "' OR $faqcatcol[flanguage]='')";
    }
    $query .= " ORDER BY $column[id]";

    if (empty($total)) {
        $countres = $dbconn->Execute($query);
        $total = $countres->PO_RecordCount();
        $countres->Close();
    }
    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if (!$result->EOF) {
        $output->Text('<font class="pn-normal">'. _FAQ . ': ' . $total . ' ' . _SEARCHRESULTS . '</font>');

        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_faqs=1&amp;bool=$bool&amp;q=$q";

        $output->Text("<ul>");

        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);

            $output->Text("<li><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=FAQ&amp;file=index&amp;myfaq=yes&id_cat=$row[id_cat]\">$row[question]</a><br>Answer: $row[answer]</li>");
            $result->MoveNext();
        }
        $output->Text('</ul>');
        $output->Linebreak(4);

        // Munge URL for template
        $urltemplate = $url . "&amp;startnum=%%&amp;total=$total";
        $output->Pager($startnum,
                       $total,
                       $urltemplate,
                       10);
    } else {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<font class="pn-normal">'._SEARCH_NO_FAQS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>