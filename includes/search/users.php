<?php // $Id: users.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// Post-Nuke: Content Management System
// ====================================
// Module: Search/stories/topics plugin
//
// Copyright (c) 2001 by the Post Nuke development team
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
    'title' => 'Users',
    'func_search' => 'search_users',
    'func_opt' => 'search_users_opt'
);
function search_users_opt() {
    global
        $bgcolor2,
        $textcolor1;

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_READ)) {
        $output->Text("<table border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\"><td><font class=\"pn-normal\" style=\"text-color:$textcolor1\"><input type=\"checkbox\" name=\"active_users\" id=\"active_users\" value=\"1\" checked>&nbsp;<label for=\"active_users\">"._SEARCH_MEMBERS."</label></font></td></tr></table>");
    }

    return $output->GetOutput();
}
function search_users() {

    list($active_users,
         $startnum,
         $total,
         $bool,
         $q) = pnVarCleanFromInput('active_users',
                                   'startnum',
                                   'total',
                                   'bool',
                                   'q');

    if(empty($active_users)) {
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
    $column = &$pntable['users_column'];
    $query = "SELECT $column[name] as name, $column[uname] as uname FROM $pntable[users] WHERE ";
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
        $query .= "$column[uname] LIKE '$word' OR ";
        $query .= "$column[name] LIKE '$word'";
        $query .= ')';
        $flag = true;
    }
    $query .= " ORDER BY $column[uname]";

    if (empty($total)) {
        $countres = $dbconn->Execute($query);
        $total = $countres->PO_RecordCount();
        $countres->Close();
    }
    $result = $dbconn->SelectLimit($query, 10, $startnum-1);

    if(!$result->EOF) {
        $output->Text('<font class="pn-normal">' . _SMEMBERS . ': ' . $total . ' ' . _SEARCHRESULTS . '</font>');

        $url = "modules.php?op=modload&amp;name=Search&amp;file=index&amp;action=search&amp;active_users=1&amp;bool=$bool&amp;q=$q";

        $output->Text("<ul>");
        while(!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $output->Text("<li><a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$row[uname]&amp;module=NS-User\">$row[uname]</a><br>$row[name]</li>");
            $result->MoveNext();
        }
        $output->Text("</ul>");

        // Munge URL for template
        $urltemplate = $url . "&amp;startnum=%%&amp;total=$total";
        $output->Pager($startnum,
                       $total,
                       $urltemplate,
                       10);

    }
    else {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('<font class="pn-normal">'._SEARCH_NO_MEMBERS.'</font>');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->Linebreak();

    return $output->GetOutput();
}
?>