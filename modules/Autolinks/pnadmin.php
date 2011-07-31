<?php
// $Id: pnadmin.php,v 1.2 2002/10/07 14:30:19 skooter Exp $
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
// Purpose of file:  Autolinks administration display functions
// ----------------------------------------------------------------------

/**
 * the main administration function
 */
function autolinks_admin_main()
{
    // Create output object
    $output = new pnHTML();

    // Security check
    if (!pnSecAuthAction(0, 'Autolinks::', '::', ACCESS_EDIT)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(autolinks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Return the output
    return $output->GetOutput();
}

/**
 * add new item
 */
function autolinks_admin_new()
{
    // Create output object
    $output = new pnHTML();

    // Security check
    if (!pnSecAuthAction(0, 'Autolinks::', '::', ACCESS_ADD)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(autolinks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Title
    $output->Title(_AUTOLINKSADD);

    // Start form
    $output->FormStart(pnModURL('Autolinks', 'admin', 'create'));

    // Add an authorisation ID
    $output->FormHidden('authid', pnSecGenAuthKey());

    $output->TableStart();

    // Keyword
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSKEYWORD));
    $row[] = $output->FormText('keyword', '', 32, 100);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Title
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSTITLE));
    $row[] = $output->FormText('title', '', 32, 100);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // URL
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSURL));
    $row[] = $output->FormText('url', '', 40, 200);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Comment
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSCOMMENT));
    $row[] = $output->FormText('comment', '', 40, 200);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    $output->TableEnd();

    // End form
    $output->Linebreak(2);
    $output->FormSubmit(_AUTOLINKSCREATE);
    $output->FormEnd();

    // Return the output
    return $output->GetOutput();
}

/**
 * This is a standard function that is called with the results of the
 * form supplied by autolinks_admin_new() to create a new item
 * @param 'keyword' the keyword of the link to be created
 * @param 'title' the title of the link to be created
 * @param 'url' the url of the link to be created
 * @param 'comment' the comment of the link to be created
 */
function autolinks_admin_create($args)
{
    // Get parameters from whatever input we need
    list($keyword,
         $title,
         $url,
         $comment) = pnVarCleanFromInput('keyword',
                                         'title',
                                         'url',
                                         'comment');

    extract($args);

    // Confirm authorisation code.
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Autolinks', 'admin', 'view'));
        return true;
    }

    // Check arguments
    if (empty($keyword)) {
        $output = new pnHTML();
        $output->Text(_AUTOLINKSKEYWORDEMPTY);
        return $output->GetOutput();
    }
    if (empty($title)) {
        $output = new pnHTML();
        $output->Text(_AUTOLINKSTITLEEMPTY);
        return $output->GetOutput();
    }
    if (empty($url)) {
        $output = new pnHTML();
        $output->Text(_AUTOLINKSURLEMPTY);
        return $output->GetOutput();
    }

    // Load API
    if (!pnModAPILoad('Autolinks', 'admin')) {
        pnSessionSetVar('errormsg', _LOADFAILED);
        return $output->GetOutput();
    }

    // The API function is called
    $lid = pnModAPIFunc('Autolinks',
                        'admin',
                        'create',
                        array('keyword' => $keyword,
                              'title' => $title,
                              'url' => $url,
                              'comment' => $comment));

    if ($lid != false) {
        // Success
        pnSessionSetVar('statusmsg', _AUTOLINKSCREATED);
    }

    pnRedirect(pnModURL('Autolinks', 'admin', 'view'));

    // Return
    return true;
}

/**
 * modify an item
 * @param 'lid' the id of the link to be modified
 */
function autolinks_admin_modify($args)
{
    // Get parameters from whatever input we need
    list($lid,
         $obid)= pnVarCleanFromInput('lid',
                                     'obid');

                           
    extract($args);

    if (!empty($obid)) {
        $lid = $obid;
    }                       

    // Create output object
    $output = new pnHTML();

    // Load API
    if (!pnModAPILoad('Autolinks', 'user')) {
        $output->Text(_LOADFAILED);
        return $output->GetOutput();
    }

    $link = pnModAPIFunc('Autolinks',
                         'user',
                         'get',
                         array('lid' => $lid));

    if ($link == false) {
        $output->Text(_AUTOLINKSNOSUCHLINK);
        return $output->GetOutput();
    }

    // Security check
    if (!pnSecAuthAction(0, 'Autolinks::Item', "$item[keyword]::$lid", ACCESS_EDIT)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(autolinks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Title
    $output->Title(_AUTOLINKSEDIT);

    // Start form
    $output->FormStart(pnModURL('Autolinks', 'admin', 'update'));

    // Add an authorisation ID
    $output->FormHidden('authid', pnSecGenAuthKey());

    // Add a hidden variable for the link id
    $output->FormHidden('lid', pnVarPrepForDisplay($lid));

    $output->TableStart();

    // Keyword
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSKEYWORD));
    $row[] = $output->FormText('keyword', $link['keyword'], 32, 100);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Title
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSTITLE));
    $row[] = $output->FormText('title', $link['title'], 32, 100);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // URL
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSURL));
    $row[] = $output->FormText('url', $link['url'], 40, 200);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Comment
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSCOMMENT));
    $row[] = $output->FormText('comment', $link['comment'], 40, 200);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    $output->TableEnd();

    // End form
    $output->Linebreak(2);
    $output->FormSubmit(_AUTOLINKSUPDATE);
    $output->FormEnd();
    
    // Return the output that has been generated by this function
    return $output->GetOutput();
}


/**
 * This is a standard function that is called with the results of the
 * form supplied by autolinks_admin_modify() to update a current item
 * @param 'lid' the id of the link to be updated
 * @param 'keyword' the keyword of the link to be updated
 * @param 'title' the title of the link to be updated
 * @param 'url' the url of the link to be updated
 * @param 'comment' the comment of the link to be updated
 */
function autolinks_admin_update($args)
{
    // Get parameters from whatever input we need
    list($lid,
         $obid,
         $keyword,
         $title,
         $url,
         $comment) = pnVarCleanFromInput('lid',
                                         'obid',
                                         'keyword',
                                         'title',
                                         'url',
                                         'comment');

    extract($args);
                            
    if (!empty($obid)) {
        $lid = $onid;
    }                       

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Autolinks', 'admin', 'view'));
        return true;
    }

    // Load API
    if (!pnModAPILoad('Autolinks', 'admin')) {
        pnSessionSetVar('errormsg', _LOADFAILED);
        return $output->GetOutput();
    }

    if(pnModAPIFunc('Autolinks',
                    'admin',
                    'update',
                    array('lid' => $lid,
                          'keyword' => $keyword,
                          'title' => $title,
                          'url' => $url,
                          'comment' => $comment))) {
        // Success
        pnSessionSetVar('statusmsg', _AUTOLINKSUPDATED);
    }

    pnRedirect(pnModURL('Autolinks', 'admin', 'view'));

    // Return
    return true;
}

/**
 * delete item
 * @param 'lid' the id of the item to be deleted
 * @param 'confirmation' confirmation that this item can be deleted
 */
function autolinks_admin_delete($args)
{
    // Get parameters from whatever input we need
    list($lid,
         $obid,
         $confirmation) = pnVarCleanFromInput('lid',
                                              'obid',
                                              'confirmation');
    extract($args);

     if (!empty($obid)) {
         $lid = $obid;
     }                     

    // Load API
    if (!pnModAPILoad('Autolinks', 'user')) {
        $output->Text(_LOADFAILED);
        return $output->GetOutput();
    }

    // The user API function is called
    $link = pnModAPIFunc('Autolinks',
                         'user',
                         'get',
                         array('lid' => $lid));

    if ($link == false) {
        $output->Text(_AUTOLINKSNOSUCHITEM);
        return $output->GetOutput();
    }

    // Security check
    if (!pnSecAuthAction(0, 'Autolinks::Item', "$item[keyword]::$lid", ACCESS_DELETE)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    // Check for confirmation. 
    if (empty($confirmation)) {
        // No confirmation yet

        // Create output object
        $output = new pnHTML();

        // Add menu to output
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text(autolinks_adminmenu());
        $output->SetInputMode(_PNH_PARSEINPUT);

        // Title
        $output->Title(_AUTOLINKSDELETE . " $item[keyword]");

        // Add confirmation to output.
        $output->ConfirmAction(_AUTOLINKSCONFIRMDELETE,
                               pnModURL('Autolinks',
                                        'admin',
                                        'delete'),
                               _AUTOLINKSCANCELDELETE,
                               pnModURL('Autolinks',
                                        'admin',
                                        'view'),
                               array('lid' => $lid));

        // Return the output that has been generated by this function
        return $output->GetOutput();
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Autolinks', 'admin', 'view'));
        return true;
    }

    // Load API
    if (!pnModAPILoad('Autolinks', 'admin')) {
        $output->Text(_LOADFAILED);
        return $output->GetOutput();
    }

    // The API function is called
    if (pnModAPIFunc('Autolinks',
                     'admin',
                     'delete',
                     array('lid' => $lid))) {
        // Success
        pnSessionSetVar('statusmsg', _AUTOLINKSDELETED);
    }

    pnRedirect(pnModURL('Autolinks', 'admin', 'view'));
    
    // Return
    return true;
}

/**
 * view items
 */
function autolinks_admin_view()
{
    // Get parameters from whatever input we need
    $startnum = pnVarCleanFromInput('startnum');

    // Create output object
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Autolinks::', '::', ACCESS_EDIT)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    // Add menu to output
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(autolinks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Title
    $output->Title(_AUTOLINKSVIEW);

    // Load API
    if (!pnModAPILoad('Autolinks', 'user')) {
        $output->Text(_LOADFAILED);
        return $output->GetOutput();
    }

    // The user API function is called
    $links = pnModAPIFunc('Autolinks',
                          'user',
                          'getall',
                          array('startnum' => $startnum,
                                'numitems' => pnModGetVar('Autolinks',
                                                          'itemsperpage')));

    if (empty($links)) {
        $output->Text(_AUTOLINKSNOLINKS);
        return $output->GetOutput();
    }

    // Start output table
    $output->TableStart('',
                        array(_AUTOLINKSKEYWORD,
                              _AUTOLINKSTITLE,
                              _AUTOLINKSURL,
                              _AUTOLINKSCOMMENT,
                              _AUTOLINKSOPTIONS),
                        3);

    foreach ($links as $link) {

        $row = array();

        if (pnSecAuthAction(0, 'Autolinks::', "$link[keyword]::$link[lid]", ACCESS_READ)) {
    
            $row[] = pnVarPrepForDisplay($link['keyword']);
            $row[] = pnVarPrepForDisplay($link['title']);
            $row[] = pnVarPrepForDisplay($link['url']);
            if (empty($link['comment'])) {
                $row[] = '&nbsp;';
            } else {
                $row[] = pnVarPrepForDisplay($link['comment']);
            }

            // Options for the link

            $options = array();
            $output->SetOutputMode(_PNH_RETURNOUTPUT);
            if (pnSecAuthAction(0, 'Autolinks::', "$link[keyword]::$link[lid]", ACCESS_EDIT)) {
                $options[] = $output->URL(pnModURL('Autolinks',
                                                   'admin',
                                                   'modify',
                                                   array('lid' => $link['lid'])),
                                          _EDIT);
                if (pnSecAuthAction(0, 'Autolinks::', "$item[keyword]::$link[lid]", ACCESS_DELETE)) {
                    $options[] = $output->URL(pnModURL('Autolinks',
                                                       'admin',
                                                       'delete',
                                                       array('lid' => $link['lid'])),
                                              _DELETE);
                }
            }

            $options = join(' | ', $options);
            $output->SetInputMode(_PNH_VERBATIMINPUT);
            $row[] = $output->Text($options);
            $output->SetOutputMode(_PNH_KEEPOUTPUT);
            $output->TableAddRow($row);
            $output->SetInputMode(_PNH_PARSEINPUT);
        }
    }
    $output->TableEnd();

    // Display pager
    $output->Pager($startnum,
                    pnModAPIFunc('Autolinks', 'user', 'countitems'),
                    pnModURL('Autolinks',
                             'admin',
                             'view',
                            array('startnum' => '%%')),
                    pnModGetVar('Autolinks', 'itemsperpage'));


    // Return the output that has been generated by this function
    return $output->GetOutput();
}

/**
 * modify configuration
 */
function autolinks_admin_modifyconfig()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Autolinks::', '::', ACCESS_ADMIN)) {
        $output->Text(_AUTOLINKSNOAUTH);
        return $output->GetOutput();
    }

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(autolinks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->Title(_AUTOLINKSMODIFYCONFIG);

    $output->FormStart(pnModURL('Autolinks', 'admin', 'updateconfig'));

    $output->FormHidden('authid', pnSecGenAuthKey());

    $output->TableStart();

    // Link everything or just first occurrance
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSLINKFIRST));
    $row[] = $output->FormCheckbox('linkfirst', pnModGetVar('Autolinks', 'linkfirst'));
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Remove text decoration for links
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_AUTOLINKSNODECORATION));
    $row[] = $output->FormCheckbox('invisilinks', pnModGetVar('Autolinks', 'invisilinks'));
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddrow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    $output->TableEnd();
    $output->Linebreak(2);
    $output->FormSubmit(_AUTOLINKSUPDATECONFIG);
    $output->FormEnd();

    return $output->GetOutput();
}

/**
 * update configuration
 */
function autolinks_admin_updateconfig()
{
    list($linkfirst,
         $invisilinks)= pnVarCleanFromInput('linkfirst',
                                            'invisilinks');

    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Autolinks', 'admin', 'view'));
        return true;
    }

    if (!isset($linkfirst)) {
        $linkfirst = 0;
    }
    pnModSetVar('Autolinks', 'linkfirst', $linkfirst);

    if (!isset($invisilinks)) {
        $invisilinks = 0;
    }
    pnModSetVar('Autolinks', 'invisilinks', $invisilinks);

    pnSessionSetVar('statusmsg', _AUTOLINKSUPDATEDCONFIG);

    pnRedirect(pnModURL('Autolinks', 'admin', 'view'));

    return true;
}

/**
 * Main administration menu
 */
function autolinks_adminmenu()
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $output = new pnHTML();

    // Display status message if any.  Note that in future this functionality
    // will probably be in the theme rather than in this menu, but this is the
    // best place to keep it for now
    $output->Text(pnGetStatusMsg());
    $output->Linebreak(2);

    // Start options menu
    $output->TableStart(_AUTOLINKS);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);

    // Menu options.  These options are all added in a single row, to add
    // multiple rows of options the code below would just be repeated
    $columns = array();
    $columns[] = $output->URL(pnModURL('Autolinks',
                                       'admin',
                                       'new'),
                              _AUTOLINKSADD); 
    $columns[] = $output->URL(pnModURL('Autolinks',
                                       'admin',
                                       'view'),
                              _AUTOLINKSVIEW); 
    $columns[] = $output->URL(pnModURL('Autolinks',
                                       'admin',
                                       'modifyconfig'),
                              _AUTOLINKSMODIFYCONFIG); 
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($columns);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableEnd();

    // Return the output that has been generated by this function
    return $output->GetOutput();
}

?>
