<?php
// $Id: pnadmin.php,v 1.8 2002/11/27 02:12:37 neo Exp $
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
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file: Block administration
// ----------------------------------------------------------------------

/*********************************************************/
/* Blocks Functions                                      */
/*********************************************************/
        
function blocks_admin_main()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Blocks::', '::', ACCESS_ADMIN)) {
        $output->Text(_BLOCKSNOAUTH);
        return $output->GetOutput();
    }

    pnRedirect(pnModURL('blocks','admin','view'));
    return true;

}

/**
 * view blocks
 */
function blocks_admin_view()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Blocks::', '::', ACCESS_ADMIN)) {
        $output->Text(_BLOCKSNOAUTH);
        return $output->GetOutput();
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];

    // Work out if we're showing all blocks or just active ones
    if (pnSessionGetVar('blocks_show_all')) {
        $where = '';
    } else {
        $where = "WHERE $blockscolumn[active] = 1";
    }

    $sql = "SELECT $blockscolumn[bid],
                   $blockscolumn[bkey],
                   $blockscolumn[title],
                   $blockscolumn[url],
                   $blockscolumn[position],
                   $blockscolumn[weight],
                   $blockscolumn[mid],
                   $blockscolumn[active],
                   $blockscolumn[blanguage]
            FROM $blockstable
            $where
            ORDER BY $blockscolumn[position],
                     $blockscolumn[weight]";
    $result = $dbconn->Execute($sql);
    $numrows = $result->PO_RecordCount();

    // Main men
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(blocks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Table
    $output->TableStart(_VIEWBLOCKS,
                        array(_ORDER,
                              _POSITION,
                              _TITLE,
                              _MODULE,
                              _NAME,
                              _LANGUAGE,
                              _STATE,
                              _OPTIONS),
                        3);

    $authid = pnSecGenAuthKey();

    $rownum = 1;
    $lastpos = '';
    while(list($bid, $bkey, $title, $url, $position, $weight, $mid, $active, $blanguage) = $result->fields) {
        $result->MoveNext();

        // Sneaky lookahead
        if (!isset($result->fields[4])) {
            $nextpos = '';
        } else {
            $nextpos = $result->fields[4];
        }

        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);


        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $up = $output->URL(pnModURL('Blocks',
                                    'admin',
                                    'inc',
                                    array('bid' => $bid,
                                          'authid' => $authid)),
                                    '<img src="images/global/up.gif" alt="' . _UP . '" border=\"0\">');
        $down = $output->URL(pnModURL('Blocks',
                                      'admin',
                                      'dec',
                                      array('bid' => $bid,
                                            'authid' => $authid)),
                                      '<img src="images/global/down.gif" alt="' . _DOWN . '" border=\"0\">');
        switch($rownum) {
            case 1:
                if ($nextpos != $position) {
                    $arrows = '';
                } else {
                    $arrows = "$down";
                }
                break;
            case $numrows:
                if ($lastpos != $position) {
                    $arrows = '';
                } else {
                    $arrows = "$up";
                }
                break;
            default:
                // Sneaky bit of lookahead here...
                if ($result->fields[4] != $position) {
                    $arrows = "$up";
                } elseif ($position != $lastpos) {
                    $arrows = "$down";
                } else {
                    $arrows = "$up $down";
                }
                break;
        }
        $rownum++;
        $lastpos = $position;

        $row[] = $output->Text($arrows);
        switch($position) {
            case 'l':
                $pos = _LEFT;
                break;
            case 'r':
                $pos = _RIGHT;
                break;
            case 'c':
                $pos = _CENTRE;
                break;
        }

        $row[] = $output->Text($pos);

        $output->SetInputMode(_PNH_PARSEINPUT);
        $row[] = $output->Text($title);

        if ($mid == 0) {
            $modname = _CORE;
        } else {
            $modinfo = pnModGetInfo($mid);
            $modname = $modinfo['name'];
        }
        $row[] = $output->Text($modname);

        $row[] = $output->Text($bkey);
        if (empty($blanguage)) {
            $row[] = $output->Text(_ALL);
        } else {
            $row[] = $output->Text($blanguage);
        }
            
        $options = array();
        if ($active) {
            $state = _ACTIVE;
            $options[] = $output->URL(pnModURL('Blocks',
                                               'admin',
                                               'deactivate',
                                               array('bid' => $bid,
                                                     'authid' => $authid)),
                                               _DEACTIVATE);
        } else {
            $state = _INACTIVE;
            $options[] = $output->URL(pnModURL('Blocks',
                                               'admin',
                                               'activate',
                                               array('bid' => $bid,
                                                     'authid' => $authid)),
                                               _ACTIVATE);
        }

        $options[] = $output->URL(pnModURL('Blocks',
                                           'admin',
                                           'modify',
                                           array('bid' => $bid)),
                                           _EDIT);
        $options[] = $output->URL(pnModURL('Blocks',
                                           'admin',
                                           'delete',
                                           array('bid' => $bid)),
                                           _DELETE);
        $options = join(' | ', $options);
        $row[] = $output->Text($state);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $row[] = $output->Text($options);
        $output->SetInputMode(_PNH_PARSEINPUT);

        $output->SetOutputMode(_PNH_KEEPOUTPUT);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddRow($row);
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOutput();
}

/**
 * show all blocks
 */
function blocks_admin_showall()
{
    pnSessionSetVar('blocks_show_all', 1);

    pnRedirect(pnModURL('Blocks', 'admin', 'view'));

    return true;
}

/**
 * show active blocks
 */
function blocks_admin_showactive()
{

    pnSessionDelVar('blocks_show_all');

    pnRedirect(pnModURL('Blocks', 'admin', 'view'));

    return true;
}

/**
 * increment position for a block
 */
function blocks_admin_inc()
{
    // Get parameters
    $bid = pnVarCleanFromInput('bid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks', 'admin', 'inc', array('bid' => $bid))) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKHIGHER);
    }

    // Redirect
    pnRedirect(pnModURL('Blocks', 'admin', 'view'));
    return true;
}

/**
 * deactivate a block
 */
function blocks_admin_deactivate()
{
    // Get parameters
    $bid = pnVarCleanFromInput('bid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid))) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKDEACTIVATED);
    }

    // Redirect
    pnRedirect(pnModURL('Blocks', 'admin', 'view'));
    return true;
}

/**
 * activate a block
 */
function blocks_admin_activate()
{
    // Get parameters
    $bid = pnVarCleanFromInput('bid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks', 'admin', 'activate', array('bid' => $bid))) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKACTIVATED);
    }

    // Redirect
    pnRedirect(pnModURL('Blocks', 'admin', 'view'));
    return true;
}



/**
 * decrement position for a block
 */
function blocks_admin_dec()
{
    // Get parameters
    $bid = pnVarCleanFromInput('bid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks', 'admin', 'dec', array('bid' => $bid))) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKLOWER);
    }

    // Redirect
    pnRedirect(pnModURL('Blocks', 'admin', 'view'));
    return true;
}

/**
 * modify a block
 */
function blocks_admin_modify()
{
# Fix for php version 4.0.x and greater

if (phpversion() >= "4.2.0") {

		$_pv = $_POST;
		$_gv = $_GET;

	} else {

		global $HTTP_GET_VARS, $HTTP_POST_VARS;
		
		$_pv = $HTTP_POST_VARS;
		$_gv = $HTTP_GET_VARS;

}

    // Get parameters
    $bid = pnVarCleanFromInput('bid');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $blockstable = $pntable['blocks'];
    $blockscolumn = $pntable['blocks_column'];

    // Get details on current block
    $blockinfo = pnBlockGetInfo($bid);

    if (empty($blockinfo)) {
        pnSessionSetVar('errormsg', _NOSUCHBLOCK);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Start output
    $output = new pnHTML();

    // Load block
    $modinfo = pnModGetInfo($blockinfo['mid']);
    if (!pnBlockLoad($modinfo['name'], $blockinfo['bkey'])) {
        $output->Text(_NOSUCHBLOCK);
        return true;
    }

    // Menu
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(blocks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Title
    $output->Title(_MODIFYBLOCK);
    if (!empty($modinfo['name'])) {
        $output->Title("$modinfo[name]/$blockinfo[bkey]");
    } else {
        $output->Title("Core/$blockinfo[bkey]");
    }

    // Start form
    $output->FormStart(pnModURL('Blocks', 'admin', 'update'));
    $output->FormHidden('authid', pnSecGenAuthKey());
    $output->FormHidden('bid', $bid);

    $output->TableStart();

    // Title
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_TITLE));
    $row[] = $output->FormText('title', $blockinfo['title'], 40, 60);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Position
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_POSITION));
    $row[] = $output->FormSelectMultiple('position',
                                         array(array('id' => 'l',
                                                     'name' => _LEFT),
                                               array('id' => 'r',
                                                     'name' => _RIGHT),
                                               array('id' => 'c',
                                                     'name' => _CENTRE)),
                                         0,
                                         1,
                                         $blockinfo['position']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Language
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_LANGUAGE));
    $langlist = languagelist();
    $languages[] = array('id' => '',
                         'name' => _ALL);
    foreach ($langlist as $k => $v) {
        if (!empty($blockinfo['language']) && $blockinfo['language'] == $k) {
            $selected = 1;
        } else {
            $selected = 0;
        }
        $languages[] = array('id' => $k,
                             'name' => $v,
                             'selected' => $selected);
    }
    $row[] = $output->FormSelectMultiple('language', $languages);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Block-specific

    // New way
    $usname = preg_replace('/ /', '_', $modinfo['name']);
    $modfunc = $usname . '_' . $blockinfo['bkey'] . 'block_modify';
    if (function_exists($modfunc)) {
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text($modfunc($blockinfo));
        $output->SetInputMode(_PNH_PARSEINPUT);
    } else {
        // Old way
        $blocks_modules = $GLOBALS['blocks_modules'];
        if (!empty($blocks_modules[$blockinfo['bkey']]) && !empty($blocks_modules[$blockinfo['bkey']]['func_edit'])) {
            if (function_exists($blocks_modules[$blockinfo['bkey']]['func_edit'])) {
                $output->SetInputMode(_PNH_VERBATIMINPUT);
                //global $HTTP_GET_VARS, $HTTP_POST_VARS;
                $output->Text($blocks_modules[$blockinfo['bkey']]['func_edit'](array_merge($_gv, $_pv, $blockinfo)));
                $output->SetInputMode(_PNH_PARSEINPUT);
            }
        }
    }

    // If no block-specific just allow them to edit content
    if (!empty($blocks_modules[$blockinfo['bkey']]) && ($blocks_modules[$blockinfo['bkey']]['form_content'] == true)) {
        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);
        $row[] = $output->Text(pnVarPrepForDisplay(_CONTENT));
        $row[] = $output->FormTextArea('content', $blockinfo['content'], 10, 40);
        $output->SetOutputMode(_PNH_KEEPOUTPUT);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddRow($row, 'left');
        $output->SetInputMode(_PNH_PARSEINPUT);
        $output->Linebreak(2);
    }

    // Refresh
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_BLOCKSREFRESH));
    $refreshtimes = array(array('id' => 1800,
                                'name' => _BLOCKSHALFHOUR),
                          array('id' => 3600,
                                'name' => _BLOCKSHOUR),
                          array('id' => 7200,
                                'name' => _BLOCKSTWOHOURS),
                          array('id' => 14400,
                                'name' => _BLOCKSFOURHOURS),
                          array('id' => 43200,
                                'name' => _BLOCKSTWELVEHOURS),
                          array('id' => 86400,
                                'name' => _BLOCKSONEDAY));

    $row[] = $output->FormSelectMultiple('refresh', $refreshtimes, 0, 1, $blockinfo['refresh']);

    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // End form
    $output->TableEnd();
    $output->Linebreak(2);
    $output->FormSubmit(_COMMIT);
    $output->FormEnd();

    return $output->GetOutput();
}

/**
 * update a block
 */
function blocks_admin_update()
{
# Fix for support of 4.0.x php and greater

if (phpversion() >= "4.2.0") {
	
		$_pv = $_POST;

	} else {

		global $HTTP_POST_VARS;
		$_pv = $HTTP_POST_VARS;

}

    // Get parameters
    list($bid,
         $title,
         $language,
         $content,
         $refresh,
         $position) = pnVarCleanFromInput('bid',
                                          'title',
                                          'language',
                                          'content',
                                          'refresh',
                                          'position');

    // Fix for null language
    if (!isset($language)) {
        $language = '';
    }

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Get and update block info
    $blockinfo = pnBlockGetInfo($bid);
    $blockinfo['title'] = $title;
    $blockinfo['bid'] = $bid;
    $blockinfo['language'] = $language;
    $blockinfo['content'] = $content;
    $blockinfo['refresh'] = $refresh;
    if ($blockinfo['position'] != $position) {
        // Moved position - try to keep weight (not that it means much)
        $blockinfo['weight'] += 0.5;
        $resequence = 1;
    }
    $blockinfo['position'] = $position;

    // Load block
    $modinfo = pnModGetInfo($blockinfo['mid']);
    if (!pnBlockLoad($modinfo['name'], $blockinfo['bkey'])) {
        $output->Text(_NOSUCHBLOCK);
        return true;
    }

    // Do block-specific update
    $usname = preg_replace('/ /', '_', $modinfo['name']);
    $updatefunc = $usname . '_' . $blockinfo['bkey'] . 'block_update';
    if (function_exists($updatefunc)) {
        $blockinfo = $updatefunc($blockinfo);
    } else {
        // Old way
        $blocks_modules = $GLOBALS['blocks_modules'];
        if (!empty($blocks_modules[$blockinfo['bkey']]) && !empty($blocks_modules[$blockinfo['bkey']]['func_update'])) {
            if (function_exists($blocks_modules[$blockinfo['bkey']]['func_update'])) {
                //global $HTTP_POST_VARS;
                $blockinfo = $blocks_modules[$blockinfo['bkey']]['func_update'](array_merge($_pv, $blockinfo));
            }
        }
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks',
                     'admin',
                     'update',
                     $blockinfo)) {
        // Success
        pnSessionSetVar('statusmsg', _UPDATEDBLOCK);

        if (!empty($resequence)) {
            // Also need to resequence
            pnModAPIFunc('Blocks', 'admin', 'resequence');
        }
    }

    pnRedirect(pnModURL('Blocks', 'admin', 'view'));

    return true;
}

/**
 * generate menu fragment
 */
function blocks_adminmenu() {

    $output = new pnHTML();

    $output->Text(pnGetStatusMsg());
    $output->Linebreak(2);

    $output->TableStart(_BLOCKS);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $columns = array();
    $columns[] = $output->URL(pnModURL('Blocks', 'admin', 'new'), _NEWBLOCK);
    $columns[] = $output->URL(pnModURL('Blocks', 'admin', 'view'), _VIEWBLOCKS);
    if (pnSessionGetVar('blocks_show_all')) {
        $columns[] = $output->URL(pnModURL('Blocks', 'admin', 'showactive'), _SHOWACTIVEBLOCKS);
    } else {
        $columns[] = $output->URL(pnModURL('Blocks', 'admin', 'showall'), _SHOWALLBLOCKS);
    }
    $columns[] = $output->URL(pnModURL('Blocks', 'admin', 'config'), _CONFIGURE);

    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($columns);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableEnd();

    return $output->GetOutput();
}

/**
 * display form for a new block
 */
function blocks_admin_new()
{

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Start output
    $output = new pnHTML();

    // Menu
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(blocks_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Title
    $output->Title(_ADDBLOCK);

    // Start form
    $output->FormStart(pnModURL('Blocks', 'admin', 'create'));
    $output->FormHidden('authid', pnSecGenAuthKey());

    $output->TableStart();

    // Title
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_TITLE));
    $row[] = $output->FormText('title', '', 40, 60);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Block

    // Load all blocks (trickier than it sounds)
    $blocks = pnBlockLoadAll();
    if (!$blocks) {
        $output->Text(_BLOCKLOADFAILED);
        return $output->GetOutput();
    }

    $blockinfo = array();
    foreach ($blocks as $block) {
        $blockinfo[] = array('id' => $block['mid'] . ':' . $block['bkey'],
                             'name' => $block['module'] . '/' . $block['text_type_long']);
    }

    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_BLOCK));

    $row[] = $output->FormSelectMultiple('blockid', $blockinfo);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Position
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_POSITION));
    $row[] = $output->FormSelectMultiple('position',
                                         array(array('id' => 'l',
                                                     'name' => _LEFT),
                                               array('id' => 'r',
                                                     'name' => _RIGHT),
                                               array('id' => 'c',
                                                     'name' => _CENTRE)));
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // Language
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(pnVarPrepForDisplay(_LANGUAGE));
    $langlist = languagelist();
    $languages = array();
    $languages[] = array('id' => '',
                         'name' => _ALL);
    foreach ($langlist as $k => $v) {
        $languages[] = array('id' => $k,
                             'name' => $v);
    }
    $row[] = $output->FormSelectMultiple('language', $languages, 0, 1, pnUserGetLang());
    $output->SetOutputMode(_PNH_KEEPOUTPUT);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);
    $output->Linebreak(2);

    // End form
    $output->TableEnd();
    $output->Linebreak(2);
    $output->FormSubmit(_COMMIT);
    $output->FormEnd();

    return $output->GetOutput();
}

/**
 * create a new block
 */
function blocks_admin_create()
{
    // Get parameters
    list($title,
         $blockid,
         $language,
         $position) = pnVarCleanFromInput('title',
                                          'blockid',
                                          'language',
                                          'position');

    list($mid, $bkey) = split(':', $blockid);

    // Fix for null language
    if (!isset($language)) {
        $language = '';
    }

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    $bid = pnModAPIFunc('Blocks',
                        'admin',
                        'create', array('bkey' => $bkey,
                                        'title' => $title,
                                        'mid' => $mid,
                                        'language' => $language,
                                        'position' => $position));
    if ($bid != false) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKCREATED);

        // Send to modify page to update block specifics
        pnRedirect(pnModURL('Blocks', 'admin', 'modify', array('bid' => $bid)));

        return true;
    }

    pnRedirect(pnModURL('Blocks', 'admin', 'view'));

    return true;
}

/**
 * delete a block
 */
function blocks_admin_delete()
{

    // Get parameters
    list($bid, $confirm) = pnVarCleanFromInput('bid', 'confirm');

    // Check for confirmation
    if (empty($confirm)) {
        // No confirmation yet - get one

        $output = new pnHTML();

        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text(blocks_adminmenu());
        $output->SetInputMode(_PNH_PARSEINPUT);
        $output->Title(_DELETEBLOCK);

        // Get details on current block
        $blockinfo = pnBlockGetInfo($bid);

        $modinfo = pnModGetInfo($blockinfo['mid']);

        if (!empty($modinfo['name'])) {
            $output->Title("$modinfo[name]/$blockinfo[bkey]");
        } else {
            $output->Title("Core/$blockinfo[bkey]");
        }

        $output->ConfirmAction(_CONFIRMBLOCKDELETE,
                               pnModURL('Blocks','admin','delete'),
                               _CANCELBLOCKDELETE,
                               pnModURL('Blocks','admin','view'),
                               array('bid' => $bid));
        return $output->GetOutput();
    }

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'view'));
        return true;
    }

    // Load in API
    pnModAPILoad('Blocks', 'admin');

    // Pass to API
    if (pnModAPIFunc('Blocks',
                     'admin',
                     'delete', array('bid' => $bid))) {
        // Success
        pnSessionSetVar('statusmsg', _BLOCKDELETED);

    }

    pnRedirect(pnModURL('Blocks', 'admin', 'view'));

    return true;

}

/**
 * Any config options would likely go here in the future
 */
function blocks_admin_config()
{
    $output = new pnHTML();

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(blocks_adminmenu());
    $output->LineBreak();
    $output->FormStart(pnModURL('Blocks', 'admin', 'updateconfig'));
    $output->Text(_BLOCKSCONFIG);
    $output->FormHidden('authid', pnSecGenAuthKey());
    $output->FormCheckBox('collapseable', pnModGetVar('Blocks', 'collapseable'));
    $output->LineBreak();
    $output->FormSubmit(_UPDATE);
    $output->FormEnd();
    $output->SetInputMode(_PNH_PARSEINPUT);

    return $output->GetOutput();
}
/**
 * Set config variable(s)
 */
function blocks_admin_updateconfig()
{
    $collapseable = pnVarCleanFromInput('collapseable');

    if(!pnSecConfirmAuthKey()) {
    pnSessionSetVar('errmsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Blocks', 'admin', 'main'));
        return true;
    }
    if(!isset($collapseable)) {
    $collapseable = 0;
    }
    pnModSetVar('Blocks', 'collapseable', $collapseable);

    pnRedirect(pnModURL('Blocks', 'admin', 'main'));

    return true;
}
?>