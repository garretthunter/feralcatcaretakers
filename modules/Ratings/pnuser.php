<?php
// $Id: pnuser.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Purpose of file:  ratings user display functions
// ----------------------------------------------------------------------

/**
 * the main user function
 */
function ratings_user_main()
{
    // Create output object
    $output = new pnHTML();

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(ratings_usermenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Security check
    if (!pnSecAuthAction(0, 'Ratings::', '::', ACCESS_READ)) {
        $output->Text(_RATINGSNOAUTH);
        return $output->GetOutput();
    }

    // Return output
    return $output->GetOutput();
}

/**
 * display rating for a specific item, and request rating
 * @param $args['objectid'] ID of the item this rating is for
 * @param $args['extrainfo'] URL to return to if user chooses to rate
 * @param $args['style'] style to display this rating in (optional)
 * @param $args['ratingtype'] specific type of rating for this item (optional)
 * @returns output
 * @return output with rating information
 */
function ratings_user_display($args)
{
    // Create new output object
    $output = new pnHTML();

    extract($args);

    if (!isset($style)) {
        $style = pnModGetVar('Ratings', 'defaultstyle');
    }
    if (!isset($ratingtype)) {
        $ratingtype = 'default';
    }

    // Load API
    if (!pnModAPILoad('Ratings', 'user')) {
        $output->Text(_LOADFAILED);
        return $output->GetOutput();
    }

    // Run API function
    $args['modname'] = pnModGetName();
    $rating = pnModAPIFunc('Ratings',
                           'user',
                           'get',
                           $args);

    if (isset($rating)) {
        // Display current rating
        $output->Text(_RATING . ' ');
        switch($style) {
            case 'percentage':
                $output->Text($rating . '%');
                break;
            case 'outoffive':
                $rating = (int)(($rating+10)/20);
                $output->Text($rating . '/5');
                break;
            case 'outoffivestars':
                $rating = (int)($rating/2);
                $intrating = (int)($rating/10);
                $fracrating = $rating - (10*$intrating);
                $output->SetInputMode(_PNH_VERBATIMINPUT);
                for ($i=0; $i < $intrating; $i++) {
                    $output->Text('<IMG SRC="modules/Ratings/pnimages/star.gif">');
                }
                if ($fracrating >= 5) {
                    $output->Text('<IMG SRC="modules/Ratings/pnimages/halfstar.gif">');
                }
                $output->SetInputMode(_PNH_PARSEINPUT);
                break;
            case 'outoften':
                $rating = (int)(($rating+5)/10);
                $output->Text($rating . '/10');
                break;
            case 'outoftenstars':
                $intrating = (int)($rating/10);
                $fracrating = $rating - (10*$intrating);
                $output->SetInputMode(_PNH_VERBATIMINPUT);
                for ($i=0; $i < $intrating; $i++) {
                    $output->Text('<IMG SRC="modules/Ratings/pnimages/star.gif">');
                }
                if ($fracrating >= 5) {
                    $output->Text('<IMG SRC="modules/Ratings/pnimages/halfstar.gif">');
                }
                $output->SetInputMode(_PNH_PARSEINPUT);
                break;
        }
    }

    // Multipe rate check
    $seclevel = pnModGetVar('Ratings', 'seclevel');
    if ($seclevel == 'high') {
        // Database information
        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();
        $ratingslogtable = $pntable['ratingslog'];
        $ratingslogcolumn = &$pntable['ratingslog_column'];

        // Check against table to see if user has already voted
        if (pnUserLoggedIn()) {
            $logid = pnUserGetVar('uid');
        } else {
            $logid = $HTTP_SERVER_VARS['REMOTE_ADDR'];
            if (empty($logid)) {
                $logid = getenv('REMOTE_ADDR');
            }
        }
        $sql = "SELECT $ratingslogcolumn[id]
                FROM $ratingslogtable
                WHERE $ratingslogcolumn[id] = '" . pnVarPrepForStore($logid) . "'
                  AND $ratingslogcolumn[ratingid] = '" . pnVarPrepForStore(pnModGetName() . $objectid . $ratingtype) . "'";
        $result = $dbconn->Execute($sql);
        if (!$result->EOF) {
            $result->Close();
            return $output->GetOutput();
        }
    } elseif ($seclevel == 'medium') {
        // Check against session to see if user has voted recently
        if (pnSessionGetVar("Rated" . pnModGetName() . "$ratingtype$objectid")) {
            return $output->GetOutput();
        }
    } // No check for low

    // This user hasn't rated this yet, ask them
    $output->FormStart(pnModURL('Ratings',
                                'user',
                                'rate'));
    $output->FormSubmit(_RATETHISITEM);
    $output->Text('  ');

    switch($style) {
        case 'default':
        case 'percentage':
            $output->FormText('rating', '', 3, 3);
            $output->Text('%  ');
            break;
        case 'outoffive':
        case 'outoffivestars':
            $output->FormSelectMultiple('rating', array(array('id' =>0,
                                                              'name' => 0),
                                                        array('id' =>20,
                                                              'name' => 1),
                                                        array('id' =>40,
                                                              'name' => 2),
                                                        array('id' =>60,
                                                              'name' => 3,
                                                              'selected' => 1),
                                                        array('id' =>80,
                                                              'name' => 4),
                                                        array('id' =>100,
                                                              'name' => 5)));
            break;
        case 'outoften':
        case 'outoftenstars':
            $output->FormSelectMultiple('rating', array(array('id' => 0,
                                                              'name' => 0),
                                                        array('id' =>10,
                                                              'name' => 1),
                                                        array('id' =>20,
                                                              'name' => 2),
                                                        array('id' =>30,
                                                              'name' => 3),
                                                        array('id' =>40,
                                                              'name' => 4),
                                                        array('id' =>50,
                                                              'name' => 5,
                                                              'selected' => 1),
                                                        array('id' =>60,
                                                              'name' => 6),
                                                        array('id' =>70,
                                                              'name' => 7),
                                                        array('id' =>80,
                                                              'name' => 8),
                                                        array('id' =>90,
                                                              'name' => 9),
                                                        array('id' =>100,
                                                              'name' => 10)));
            break;
    }

    $output->FormHidden('returnurl', $extrainfo);
    $output->FormHidden('modname', pnModGetName());
    $output->FormHidden('objectid', $objectid);
    $output->FormHidden('ratingtype', $ratingtype);
    $output->FormHidden('authid', pnSecGenAuthKey('Ratings'));
    $output->FormEnd();

    return $output->GetOutput();
}

function ratings_user_rate($args)
{
    // Get parameters
    list($modname,
         $objectid,
         $ratingtype,
         $returnurl,
         $rating) = pnVarCleanFromInput('modname',
                                        'objectid',
                                        'ratingtype',
                                        'returnurl',
                                        'rating');
    
    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect($returnurl);
        return true;
    }

    // Load API
    pnModAPILoad('Ratings', 'user');

    // Pass to API
    $newrating = pnModAPIFunc('Ratings',
                              'user',
                              'rate',
                              array('modname'    => $modname,
                                    'objectid'   => $objectid,
                                    'ratingtype' => $ratingtype,
                                    'rating'     => $rating));

    if (isset($newrating)) {
        // Success
        pnSessionSetVar('statusmsg', _THANKYOUFORRATING);
    }

    pnRedirect($returnurl);

    return true;
}

?>