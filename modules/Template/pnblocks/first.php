<?php
// $Id: first.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Purpose of file: Show first items in template
// ----------------------------------------------------------------------

/**
 * initialise block
 */
function template_firstblock_init()
{
    // Security
    pnSecAddSchema('Template:Firstblock:', 'Block title::');
}

/**
 * get information on block
 */
function template_firstblock_info()
{
    // Values
    return array('text_type' => 'First',
                 'module' => 'Template',
                 'text_type_long' => 'Show first example items (alphabetical)',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}

/**
 * display block
 */
function template_firstblock_display($blockinfo)
{
    // Security check
    if (!pnSecAuthAction(0,
                         'Template:Firstblock:',
                         "$blockinfo[title]::",
                         ACCESS_READ)) {
        return;
    }

    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['numitems'])) {
        $vars['numitems'] = 5;
    }

    // Database information
    pnModDBInfoLoad('Template');
    list($dbconn) = pnDBGetConn();
    $pntable =pnDBGetTables();
    $templatetable = $pntable['template'];
    $templatecolumn = &$pntable['template_column'];

    // Query
    $sql = "SELECT $templatecolumn[tid],
                   $templatecolumn[name]
            FROM $templatetable
            ORDER by $templatecolumn[name]";
    $result = $dbconn->SelectLimit($sql, $vars['numitems']);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        return;
    }

    // Create output object
    $output = new pnHTML();

    // Display each item, permissions permitting
    for (; !$result->EOF; $result->MoveNext()) {
        list($tid, $name) = $result->fields;

        if (pnSecAuthAction(0,
                            'Template::',
                            "$name::$tid",
                            ACCESS_OVERVIEW)) {
            if (pnSecAuthAction(0,
                                'Template::',
                                "$name::$tid",
                                ACCESS_READ)) {
                $output->URL(pnModURL('Template',
                                      'user',
                                      'viewdetail',
                                      array('tid' => $tid)),
                             $name);
            } else {
                $output->Text($name);
            }
            $output->Linebreak();
        }

    }

    
    // Populate block info and pass to theme
    $blockinfo['content'] = $output->GetOutput();
    return themesideblock($blockinfo);
}


/**
 * modify block settings
 */
function template_firstblock_modify($blockinfo)
{
    // Create output object
    $output = new pnHTML();

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['numitems'])) {
        $vars['numitems'] = 5;
    }

    // Create row
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(_NUMITEMS);
    $row[] = $output->FormText('numitems',
                               pnVarPrepForDisplay($vars['numitems']),
                               5,
                               5);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Return output
    return $output->GetOutput();
}

/**
 * update block settings
 */
function template_firstblock_update($blockinfo)
{
    $vars['numitems'] = pnVarCleanFromInput('numitems');

    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}

?>