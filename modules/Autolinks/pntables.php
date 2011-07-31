<?php
// $Id: pntables.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
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
// Purpose of file:  Table information for autolinks module
// ----------------------------------------------------------------------

function autolinks_pntables()
{
    // Initialise table array
    $pntable = array();

    // Get the name for the autolinks item table
    $autolinks = pnConfigGetVar('prefix') . '_autolinks';

    // Set the table name
    $pntable['autolinks'] = $autolinks;

    // Set the column names
    $pntable['autolinks_column'] = array('lid'     => $autolinks . '.pn_lid',
                                         'keyword' => $autolinks . '.pn_keyword',
                                         'title'   => $autolinks . '.pn_title',
                                         'url'     => $autolinks . '.pn_url',
                                         'comment' => $autolinks . '.pn_comment');

    // Return the table information
    return $pntable;
}

?>