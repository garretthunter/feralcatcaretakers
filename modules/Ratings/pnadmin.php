<?php
// $Id: pnadmin.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
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
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Ratings administration display functions
// ----------------------------------------------------------------------

/**
 * the main administration function
 */
function ratings_admin_main()
{
    // Create output object
    $output = new pnHTML();

    // Security check
    if (!pnSecAuthAction(0, 'Ratings::', '::', ACCESS_DELETE)) {
        $output->Text(_RATINGSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(ratings_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Return output
    return $output->GetOutput();
}

/**
 * Modify configuration
 */
function ratings_admin_modifyconfig()
{
    // Create output object
    $output = new pnHTML();

    // Security check
    if (!pnSecAuthAction(0, 'Ratings::', '::$', ACCESS_ADMIN)) {
        $output->Text(_RATINGSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(ratings_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Title
    $output->Title(_RATINGSMODIFYCONFIG);

    // Start form
    $output->FormStart(pnModURL('Ratings', 'admin', 'updateconfig'));
    $output->FormHidden('authid', pnSecGenAuthKey());

    $output->TableStart();

    // Default rating style
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_RATINGSDEFAULTSTYLE));
    $row[] = $output->FormSelectMultiple('style',
                                         array(array('id' => 'percentage',
                                                     'name' => _RATINGSPERCENTAGE),
                                               array('id' => 'outoffive',
                                                     'name' => _RATINGSOUTOFFIVE),
                                               array('id' => 'outoffivestars',
                                                     'name' => _RATINGSOUTOFFIVESTARS),
                                               array('id' => 'outoften',
                                                     'name' => _RATINGSOUTOFTEN),
                                               array('id' => 'outoftenstars',
                                                     'name' => _RATINGSOUTOFTENSTARS)),
                                         0,
                                         1,
                                         pnModGetVar('Ratings', 'defaultstyle'));
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Ratings security
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_RATINGSSECURITY));
    $row[] = $output->FormSelectMultiple('seclevel',
                                         array(array('id' => 'low',
                                                     'name' => _RATINGSSECLOW),
                                               array('id' => 'medium',
                                                     'name' => _RATINGSSECMEDIUM),
                                               array('id' => 'high',
                                                     'name' => _RATINGSSECHIGH)),
                                         0,
                                         1,
                                         pnModGetVar('Ratings', 'seclevel'));
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // End form
    $output->TableEnd();
    $output->Linebreak(2);
    $output->FormSubmit(_RATINGSUPDATECONFIG);
    $output->FormEnd();

    return $output->GetOutput();
}

/**
 * Update configuration
 */
function ratings_admin_updateconfig()
{
    // Get parameters
    list($style,
         $seclevel) = pnVarCleanFromInput('style',
                                          'seclevel');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Ratings', 'admin', 'main'));
        return true;
    }

    // Update default style
    if (!isset($style)) {
        $style = 'outoffivestars';
    }
    pnModSetVar('Ratings', 'defaultstyle', $style);

    // Update security level
    if (!isset($seclevel)) {
        $seclevel = 'medium';
    }
    pnModSetVar('Ratings', 'seclevel', $seclevel);

    pnRedirect(pnModURL('Ratings', 'admin', 'main'));

    return true;
}

/**
 * Main administration menu
 */
function ratings_adminmenu()
{
    // Create output object
    $output = new pnHTML();

    // Display status message if any
    $output->Text(pnGetStatusMsg());
    $output->Linebreak(2);

    // Start options menu
    $output->TableStart(_RATINGS);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);

    // Menu options
    $columns = array();
    $columns[] = $output->URL(pnModURL('Ratings',
                                        'admin',
                                        'modifyconfig'),
                              _RATINGSMODIFYCONFIG); 
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($columns);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableEnd();

    // Return output
    return $output->GetOutput();
}

?>