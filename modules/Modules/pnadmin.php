<?php
// File: $Id: pnadmin.php,v 1.5 2002/12/04 22:43:29 larsneo Exp $ $Name:  $
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
// Purpose of file: Modules administration
// ----------------------------------------------------------------------

function modules_admin_main()
{
	/* if we display the mod right at the beginning there is an auth-problem
    pnRedirect(pnModURL('modules','admin','list'));                        
    return true;
    */
	$output = new pnHTML();

	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->Text(modules_adminmenu());
	$output->SetInputMode(_PNH_PARSEINPUT);

	return $output->GetOutput();
}

/*
 * modules_admin_modify - modify a module
 */
function modules_admin_modify()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();

    $id = pnVarCleanFromInput('id');
    $dbid = pnVarPrepForStore($id);

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[name],
                     $modulescolumn[displayname],
                     $modulescolumn[description]
              FROM $modulestable
              WHERE $modulescolumn[id] = $dbid";
    $result = $dbconn->Execute($query);

    if ($result->EOF) {
        $output->Text(_ERRMODNOSUCHMODID);
        return $output->GetOutput();
    }

    list($name, $displayname, $description) = $result->fields;
    $result->Close();


    if (!pnSecAuthAction(0, 'Modules::', "$name::$id", ACCESS_ADMIN))  {
        $output->Text(_MODULESEDITNOAUTH);
        return $output->GetOutput();
    }

    // Start form
    $output->FormStart(pnModURL('Modules', 'admin', 'update'));
    $output->FormHidden('authid', pnSecGenAuthKey());
    $output->FormHidden('id', $id);

    // Name
    $output->Text(_MODULESNEWNAME);
    $output->Linebreak();
    $output->FormText('newdisplayname', $displayname, 30, 30);
    $output->Linebreak(2);

    // Description
    $output->Text(_MODULESNEWDESCRIPTION);
    $output->Linebreak();
    $output->FormText('newdescription', $description, 60, 254);
    $output->Linebreak(2);

    // Hooks
    $hookstable = $pntable['hooks'];
    $hookscolumn = &$pntable['hooks_column'];
    $sql = "SELECT DISTINCT $hookscolumn[smodule],
                            $hookscolumn[tmodule]
            FROM $hookstable
            WHERE $hookscolumn[smodule] IS NULL
            OR $hookscolumn[smodule] = '" . pnVarPrepForStore($name) . "'
            ORDER BY $hookscolumn[tmodule],
                     $hookscolumn[smodule] DESC";
    $result = $dbconn->Execute($sql);
    $displayed = array();
    for (; !$result->EOF; $result->MoveNext()) {
        list($smodname, $tmodname) = $result->fields;

        // Only display once
        if (isset($displayed[$tmodname])) {
            continue;
        }
        $displayed[$tmodname] = true;
        if (!empty($smodname)) {
            $checked = 1;
        } else {
            $checked = 0;
        }

        $output->Text(_MODULESACTIVATE . ' ' . strtolower($tmodname) . ' ' . _MODULESFORTHIS);
        $output->FormCheckbox('hooks_' . pnVarPrepForDisplay($tmodname), $checked);
        $output->Linebreak(2);
    }
    $result->Close();

    // End form
    $output->FormSubmit(_COMMIT);
    $output->FormEnd();

    return $output->GetOutput();
}

function modules_admin_update()
{
    // Get parameters
    list($id,
         $newdisplayname,
         $newdescription) = pnVarCleanFromInput('id',
                                                'newdisplayname',
                                                'newdescription');

   if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Pass to API
    if (pnModAPIFunc('Modules',
                     'admin',
                     'update',
                     array('mid' => $id,
                           'displayname' => $newdisplayname,
                           'description' => $newdescription))) {
        // Success
        pnSessionSetVar('statusmsg', 'Updated module information');
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

/*
 * modules_admin_list - list modules and current settings
 * Takes no parameters
 */
function modules_admin_list()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Modules::', '::', ACCESS_ADMIN)) {
        $output->Text(_MODULESNOAUTH);
        return $output->GetOutput();
    }

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(modules_adminmenu());
    $output->SetInputMode(_PNH_PARSEINPUT);


    // Load modules API
    if (!pnModAPILoad('Modules', 'admin')) {
        $output->Text(_APILOADFAILED);
        return $output->GetOutput();
    }

    // Get list of modules
    $mods = pnModAPIFunc('Modules', 'admin', 'list');

    if ($mods == false) {
        $output->Text(_MODULESNOMODS);
        return $output->GetOutput();
    }

    $columnHeaders = array(_HEADMODNAME,
                           _HEADMODDISPNAME,
                           _HEADMODDESC,
                           _HEADMODDIR,
                           _HEADMODSTATE,
                           _HEADMODACTIONS);

    $output->TableStart('', $columnHeaders, 2);

    $authid = pnSecGenAuthKey();
    foreach($mods as $mod) {

        // Skip modules module
        if ($mod['name'] == 'Modules') {
            continue;
        }
        // Add applicable actions
        $actions = array();

        if (pnSecAuthAction(0, 'Modules::', "$mod[name]::$mod[id]", ACCESS_ADMIN)) {

            $output->SetOutputMode(_PNH_RETURNOUTPUT);
            switch ($mod['state']) {
                case _PNMODULE_STATE_ACTIVE:
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'deactivate', array('id' => $mod['id'], 'authid' => $authid)), _DEACTIVATE);
                    $output->URL(pnModURL('Modules', 'admin', 'regenerate'), _GENERATEMODS);
                    break;
                case _PNMODULE_STATE_INACTIVE:
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'activate', array('id' => $mod['id'], 'authid' => $authid)), _ACTIVATE);
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'remove', array('id' => $mod['id'], 'authid' => $authid)), _REMOVE);
                    break;
                case _PNMODULE_STATE_MISSING:
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'remove', array('id' => $mod['id'], 'authid' => $authid)), _REMOVE);
                    break;
                case _PNMODULE_STATE_UPGRADED:
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'upgrade', array('id' => $mod['id'], 'authid' => $authid)), _UPGRADE);
                    break;
                case _PNMODULE_STATE_UNINITIALISED:
                default:
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'initialise', array('id' => $mod['id'], 'authid' => $authid)), _INITIALISE);
                    $actions[] = $output->URL(pnModURL('Modules', 'admin', 'remove', array('id' => $mod['id'], 'authid' => $authid)), _REMOVE);
                    break;
            }

            $actions[] = $output->URL(pnModURL('Modules', 'admin', 'modify', array('id' => $mod['id'])), _EDIT);
            $output->SetOutputMode(_PNH_KEEPOUTPUT);
        }
        $actions = join(' | ', $actions);

        // Translate state
        switch($mod['state']) {
            case _PNMODULE_STATE_INACTIVE:
                $state = _INACTIVE;
                break;
            case _PNMODULE_STATE_ACTIVE:
                $state = _ACTIVE;
                break;
            case _PNMODULE_STATE_MISSING:
                $state = _FILESMISSING;
                break;
            case _PNMODULE_STATE_UPGRADED:
                $state = _UPGRADED;
                break;
            case _PNMODULE_STATE_UNINITIALISED:
            default:
                $state = _UNINIT;
                break;
        }

        $row = array(pnVarPrepForDisplay($mod['name']),
                     pnVarPrepForDisplay($mod['displayname']),
                     pnVarPrepForDisplay($mod['description']),
                     pnVarPrepForDisplay($mod['directory']),
                     pnVarPrepForDisplay($state),
                     $actions);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddRow($row, 'CENTER');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOutput();
}

/**
 * initialise a module
 */
function modules_admin_initialise()
{
    // Security and sanity checks
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    $id = pnVarCleanFromInput('id');
    if (empty($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODULESNOMODID);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Initialise module
    if(pnModAPIFunc('Modules',
                    'admin',
                    'initialise',
                    array('mid' => $id))) {
        // Success
        pnSessionSetVar('statusmsg', 'Module initialised');
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

/**
 * activate a module
 */
function modules_admin_activate()
{
    // Security and sanity checks
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    $id = pnVarCleanFromInput('id');
    if (empty($id)) {
        pnSessionSetVar('errormsg', _MODULESNOMODID);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Update state
    if (pnModAPIFunc('Modules',
                     'admin',
                     'setstate',
                     array('mid' => $id,
                           'state' => _PNMODULE_STATE_ACTIVE))) {
        // Success
        pnSessionSetVar('statusmsg', _MODACTIVATED);
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

/**
 * upgrade a module
 */
function modules_admin_upgrade()
{
    // Security and sanity checks
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    $id = pnVarCleanFromInput('id');
    if (empty($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODULESNOMODID);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Upgrade module
    if (pnModAPIFunc('Modules',
                     'admin',
                     'upgrade',
                     array('mid' => $id))) {
        // Success
        pnSessionSetVar('statusmsg', _MODACTIVATED);
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

/*
 * modules_admin_deactivate - deactivate a module
 */
function modules_admin_deactivate()
{
    // Security and sanity checks
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    $id = pnVarCleanFromInput('id');
    if (empty($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODULESNOMODID);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Update state
    if (pnModAPIFunc('Modules',
                     'admin',
                     'setstate',
                     array('mid' => $id,
                           'state' => _PNMODULE_STATE_INACTIVE))) {
        // Success
        pnSessionSetVar('statusmsg', _MODDEACTIVATED);
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

/*
 * modules_admin_remove - remove a module
 */
function modules_admin_remove()
{
    // Security and sanity checks
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    $id = pnVarCleanFromInput('id');
    if (empty($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODULESNOMODID);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Remove module
    if (pnModAPIFunc('Modules',
                     'admin',
                     'remove',
                     array('mid' => $id))) {
        // Success
        pnSessionSetVar('statusmsg', _MODREMOVED);
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}


function modules_admin_regenerate()
{
    // Security check
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Modules', 'admin', 'list'));
        return true;
    }

    // Load in API
    pnModAPILoad('Modules', 'admin');

    // Regenerate modules
    if (pnModAPIFunc('Modules', 'admin', 'regenerate')) {
        // Success
        pnSessionSetVar('statusmsg', _MODREGENERATED);
    }

    pnRedirect(pnModURL('Modules', 'admin', 'list'));

    return true;
}

function modules_adminmenu()
{
    $output = new pnHTML();

    $output->Text(pnGetStatusMsg());
    $output->Linebreak(2);

    $output->TableStart(_MODULES);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $columns = array();
    $columns[] = $output->URL(pnModURL('Modules', 'admin', 'list'), _LIST);
    $columns[] = $output->URL(pnModURL('Modules', 'admin', 'regenerate', array('authid' => pnSecGenAuthKey())), _REGENERATE);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($columns);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableEnd();

    return $output->GetOutput();
}

?>