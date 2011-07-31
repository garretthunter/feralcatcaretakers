<?php
// File: $Id: pnadmin.php,v 1.2 2002/11/12 13:20:45 magicx Exp $
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
// Purpose of file:  Permissions administration
// ----------------------------------------------------------------------

function permissions_admin_main()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Permissions::', '::', ACCESS_ADMIN)) {
        $output->Text(_PERMISSIONSNOAUTH);
        return $output->GetOutput();
    }

    pnRedirect(pnModURL('permissions','admin','view'));                        
    return true;
}

/**
 * view permissions
 */
function permissions_admin_view()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Permissions::', '::', ACCESS_ADMIN)) {
        $output->Text(_PERMISSIONSNOAUTH);
        return $output->GetOutput();
    }

    $permtype = pnVarCleanFromInput('permtype');

    // Work out which tables to operate against, and
    // various other bits and pieces
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    if ($permtype == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
        $idfield = $permcolumn['uid'];
        $mlpermtype = _USERPERMS;
        $viewperms = _VIEWUSERPERMS;
        $ids = permissions_getUsersInfo();
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
        $idfield = $permcolumn['gid'];
        $mlpermtype = _GROUPPERMS;
        $viewperms = _VIEWGROUPPERMS;
        $ids = permissions_getGroupsInfo();
    }

    $query = "SELECT $permcolumn[pid],
                     $idfield,
                     $permcolumn[sequence],
                     $permcolumn[realm],
                     $permcolumn[component],
                     $permcolumn[instance],
                     $permcolumn[level],
                     $permcolumn[bond]
              FROM $permtable
              ORDER BY $permcolumn[sequence]";
    $result  = $dbconn->Execute($query);

    // Main menu
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(permissions_adminmenu());
    // Javascript
    $output->Text(permissions_javascript());

    // Table
    $output->TableStart($viewperms,
                        array(_SEQUENCE,
// Realms not currently functional so hide the output - jgm
//                              _REALM,
                              $mlpermtype,
                              '<a href="javascript:showinstanceinformation()">'._COMPONENT,
                              '<a href="javascript:showinstanceinformation()">'._INSTANCE,
                              _PERMLEVEL,
                              _PERMOPS),
                        3);
    $output->SetInputMode(_PNH_PARSEINPUT);
    
    $realms = permissions_getRealmsInfo();
    $accesslevels = accesslevelnames();
    $i=0;
    $numrows = $result->PO_RecordCount();

    $authid = pnSecGenAuthKey();

    $rownum = 1;
    while(list($pid, $id, $sequence, $realm, $component, $instance, $level, $bond) = $result->fields) {
        $result->MoveNext();

        // Show the permission itself
        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);

        
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $up = $output->URL(pnModURL('Permissions',
                                     'admin',
                                     'inc',
                                     array('pid' => $pid,
                                           'permtype' => $permtype,
                                           'authid' => $authid)),
                           '<img src="images/global/up.gif" alt="' . _UP . '" border=\"0\">');
        $down = $output->URL(pnModURL('Permissions',
                                      'admin',
                                      'dec',
                                      array('pid' => $pid,
                                            'permtype' => $permtype,
                                            'authid' => $authid)),
                             '<img src="images/global/down.gif" alt="' . _DOWN . '" border=\"0\">');
        switch($rownum) {
            case 1:
                $arrows = "$down";
                break;
            case $numrows:
                $arrows = "$up";
                break;
            default:
                $arrows = "$up $down";
                break;
        }
        $rownum++;

        $row[] = $output->Text($arrows);
        $output->SetInputMode(_PNH_PARSEINPUT);

// Realms not currently functional so hide the output - jgm
//        $row[] = $output->Text($realms[$realm]);
        $row[] = $output->Text($ids[$id]);
        $row[] = $output->Text($component);
        $row[] = $output->Text($instance);
        $row[] = $output->Text($accesslevels[$level]);

        $edit = $output->URL(pnModURL('Permissions',
                                      'admin',
                                      'modify',
                                      array('pid' => $pid,
                                             'permtype' => $permtype)),
                             _EDIT);
        $delete = $output->URL(pnModURL('Permissions',
                                        'admin',
                                        'delete',
                                        array('pid' => $pid,
                                              'permtype' => $permtype,
                                              'authid' => $authid)),
                               _DELETE);
        $options = "$edit | $delete";
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

function permissions_admin_inc()
{
    // Get parameters
    list($permtype, $pid) = pnVarCleanFromInput('permtype', 'pid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Permissions',
                            'admin',
                            'view',
                            array('permtype' => $permtype)));
        return true;
    }

    // Load in API
    pnModAPILoad('Permissions', 'admin');

    // Pass to API
    if (pnModAPIFunc('Permissions',
                     'admin',
                     'inc',
                     array('type' => $permtype,
                           'pid' => $pid))) {
        // Success
        pnSessionSetVar('statusmsg', 'Incremented permission');
    }

    // Redirect
    pnRedirect(pnModURL('Permissions',
                        'admin',
                        'view',
                        array('permtype' => $permtype)));

    return true;
}

/*
 * decrement a permission
 */
function permissions_admin_dec()
{
    // Get parameters
    list($permtype, $pid) = pnVarCleanFromInput('permtype', 'pid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Permissions',
                            'admin',
                            'view',
                            array('permtype' => $permtype)));
        return true;
    }

    // Load in API
    pnModAPILoad('Permissions', 'admin');

    // Pass to API
    if (pnModAPIFunc('Permissions',
                     'admin',
                     'dec',
                     array('type' => $permtype,
                           'pid' => $pid))) {
        // Success
        pnSessionSetVar('statusmsg', 'Decremented permission');
    }

    // Redirect
    pnRedirect(pnModURL('Permissions',
                        'admin',
                        'view',
                        array('permtype' => $permtype)));

    return true;
}

/*
 * Modify a permission
 */
function permissions_admin_modify()
{
    // Get parameters
    list($permtype, $pid) = pnVarCleanFromInput('permtype', 'pid');

    // Work out which tables to operate against
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    if ($permtype == "user") {
        $permtable = $pntable['user_perms'];
        $permcolumn = &$pntable['user_perms_column'];
        $idfield = $permcolumn['uid'];
        $mlheader = _MODIFYUSERPERM;
        $mlpermtype = _USERPERMS;
        $ids = permissions_getUsersInfo();
        foreach($ids as $k => $v) {
            $idinfo[] = array('id' => $k,
                              'name' => $v);
        }
    } else {
        $permtable = $pntable['group_perms'];
        $permcolumn = &$pntable['group_perms_column'];
        $idfield = $permcolumn['gid'];
        $mlheader = _MODIFYGROUPPERM;
        $mlpermtype = _GROUPPERMS;
        $ids = permissions_getGroupsInfo();
        foreach($ids as $k => $v) {
            $idinfo[] = array('id' => $k,
                              'name' => $v);
        }
    }
    
    // Get details on current perm
    $query = "SELECT $permcolumn[realm],
                     $idfield,
                     $permcolumn[component],
                     $permcolumn[instance],
                     $permcolumn[level]
              FROM $permtable
              WHERE $permcolumn[pid]=" . pnVarPrepForStore($pid);
    $result = $dbconn->Execute($query);
    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such $permtype permission found");
        pnRedirect(pnModURL('modules', 'admin', 'view', array('permtype' => $permtype)));
        return;
    }
    list($realm, $id, $component, $instance, $level) = $result->fields;
    $result->Close();

    // Start output
    $output = new pnHTML();
    
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    // Menu
    $output->Text(permissions_adminmenu());
    // Add Javascript - used for pop-up windows
    $output->Text(permissions_javascript());
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->FormStart(pnModURL('Permissions', 'admin', 'update'));
    $output->FormHidden('authid', pnSecGenAuthKey());
    $output->FormHidden('permtype', $permtype);
    $output->FormHidden('pid', $pid);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableStart($mlheader,
                        array(
// Realms currently non functional so hide output - jgm
//                              _REALM,
                              $mlpermtype,
                              '<a href="javascript:showinstanceinformation()">'._COMPONENT,
                              '<a href="javascript:showinstanceinformation()">'._INSTANCE,
                              _PERMLEVEL),
                        3);
    $output->SetInputMode(_PNH_PARSEINPUT);
    
    // Show the permission itself
    $row = array();
// Hidden field for realm - jgm
    $output->FormHidden('realm', $realm);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);

    $realms = permissions_getRealmsInfo();
    foreach($realms as $k => $v) {
        $realminfo[] = array('id' => $k,
                             'name' => $v);
    }
// Realms currently non functional so hide output - jgm
//    $row[] = $output->FormSelectMultiple('realm', $realminfo, 0, 1, $realm);
    foreach ($idinfo as $idkey => $idval) {
        if ($idval['id'] == $id) {
            $idinfo[$idkey]['selected'] = 1;
        }
    }
    $row[] = $output->FormSelectMultiple('id', $idinfo);
    $row[] = $output->FormText('component', $component, 24, 255);
    $row[] = $output->FormText('instance', $instance, 36, 255);

    $accesslevels = array();
    foreach(accesslevelnames() as $k => $v) {
        $levelinfo[] = array('id' => $k,
                             'name' => $v);
    }
    $row[] = $output->FormSelectMultiple('level', $levelinfo, 0, 1, $level);

    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableRowStart();
    $output->TableColStart(5);
    $output->FormSubmit(_MODIFYPERM);
    $output->TableColEnd();
    $output->TableRowEnd();

    $output->TableEnd();
    $output->FormEnd();

    return $output->GetOutput();
}

function permissions_admin_update()
{
    // Get parameters
    list($permtype,
         $pid,
         $realm,
         $id,
         $component,
         $instance,
         $level) = pnVarCleanFromInput('permtype',
                                       'pid',
                                       'realm',
                                       'id',
                                       'component',
                                       'instance',
                                       'level');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Permissions',
                            'admin',
                            'view',
                            array('permtype' => $permtype)));
        return true;
    }

    // Load in API
    pnModAPILoad('Permissions', 'admin');

    // Pass to API
    if (pnModAPIFunc('Permissions',
                     'admin',
                     'update',
                     array('type' => $permtype,
                           'pid' => $pid,
                           'realm' => $realm,
                           'id' => $id,
                           'component' => $component,
                           'instance' => $instance,
                           'level' => $level))) {
        // Success
        pnSessionSetVar('statusmsg', "Updated permission");
    }

    pnRedirect(pnModURL('Permissions',
                        'admin',
                        'view',
                        array('permtype' => $permtype)));

    return true;
}

/**
 * display form for a new permission
 */
function permissions_admin_new()
{
    $permtype = pnVarCleanFromInput('permtype');

    // Work out which tables to operate against
    if ($permtype == "user") {
        $mlheader = _NEWUSERPERM;
        $mlpermtype = _USERPERMS;
        $action = "secadduserperm";
        $ids = permissions_getUsersInfo();
        foreach($ids as $k => $v) {
            $idinfo[] = array('id' => $k,
                              'name' => $v);
        }
    } else {
        $mlheader = _NEWGROUPPERM;
        $mlpermtype = _GROUPPERMS;
        $action = "secaddgroupperm";
        $ids = permissions_getGroupsInfo();
        foreach($ids as $k => $v) {
            $idinfo[] = array('id' => $k,
                              'name' => $v);
        }
    }

    // Start output
    $output = new pnHTML();
    
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    // Menu
    $output->Text(permissions_adminmenu());
    // Add Javascript - used for pop-up windows
    $output->Text(permissions_javascript());
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->FormStart(pnModURL('Permissions', 'admin', 'create'));
    $output->FormHidden('authid', pnSecGenAuthKey());
    $output->FormHidden('permtype', pnVarPrepForDisplay($permtype));
// Realms hard-coded - jgm
    $output->FormHidden('realm', 0);
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableStart($mlheader,
                        array(
// Realms not currently functional so hide the output - jgm
//                              _REALM,
                              $mlpermtype,
                              '<a href="javascript:showinstanceinformation()">'._COMPONENT,
                              '<a href="javascript:showinstanceinformation()">'._INSTANCE,
                              _PERMLEVEL),
                        3);
    $output->SetInputMode(_PNH_PARSEINPUT);
    
    // Show the permission itself
    $row = array();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);

    $realms = permissions_getRealmsInfo();
    foreach($realms as $k => $v) {
        $realminfo[] = array('id' => $k,
                             'name' => $v);
    }
// Realms not currently functional so hide the output - jgm
//    $row[] = $output->FormSelectMultiple('realm', $realminfo);
    $row[] = $output->FormSelectMultiple('id', $idinfo);
    $row[] = $output->FormText('component', '', 24);
    $row[] = $output->FormText('instance', '', 36);

    $accesslevels = array();
    foreach(accesslevelnames() as $k => $v) {
        $levelinfo[] = array('id' => $k,
                             'name' => $v);
    }
    $row[] = $output->FormSelectMultiple('level', $levelinfo);

    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableRowStart();
    $output->TableColStart(5);
    $output->FormSubmit(_NEWPERM);
    $output->TableColEnd();
    $output->TableRowEnd();

    $output->TableEnd();
    $output->FormEnd();

    return $output->GetOutput();
}

/*
 * create a new permission
 */
function permissions_admin_create()
{
    // Get parameters
    list($permtype,
         $realm,
         $id,
         $component,
         $instance,
         $level) = pnVarCleanFromInput('permtype',
                                       'realm',
                                       'id',
                                       'component',
                                       'instance',
                                       'level');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Permissions',
                            'admin',
                            'view',
                            array('permtype' => $permtype)));
        return true;
    }

    // Load in API
    pnModAPILoad('Permissions', 'admin');

    // Pass to API
    if (pnModAPIFunc('Permissions',
                     'admin',
                     'create', array('type' => $permtype,
                                     'realm' => $realm,
                                     'id' => $id,
                                     'component' => $component,
                                     'instance' => $instance,
                                     'level' => $level))) {
        // Success
        pnSessionSetVar('statusmsg', 'Created permission');
    }

    pnRedirect(pnModURL('Permissions',
                        'admin',
                        'view',
                        array('permtype' => $permtype)));

    return true;
}

/*
 * delete a permission
 */
function permissions_admin_delete()
{
    // Get parameters
    list($permtype,
         $pid) = pnVarCleanFromInput('permtype',
                                     'pid');

    // Confirm authorisation code
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Permissions',
                            'admin',
                            'view',
                            array('permtype' => $permtype)));
        return true;
    }

    // Load in API
    pnModAPILoad('Permissions', 'admin');

    // Pass to API
    if (pnModAPIFunc('Permissions',
                     'admin',
                     'delete',
                     array('type' => $permtype,
                           'pid' => $pid))) {
        // Success
        pnSessionSetVar('statusmsg', "Deleted permission");
    }

    pnRedirect(pnModURL('Permissions',
                        'admin',
                        'view',
                        array('permtype' => $permtype)));
    return true;
}


/*
 * getUsersInfo - get users information
 * Takes no parameters
 */
function permissions_getUsersInfo()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $usertable = $pntable['users'];
    $usercolumn = &$pntable['users_column'];

    $query = "SELECT $usercolumn[uid],
                     $usercolumn[uname]
              FROM $usertable
              ORDER BY $usercolumn[uname]";
    $result = $dbconn->Execute($query);
    $users[_PNPERMS_ALL] = _ALLUSERS;
    $users[_PNPERMS_UNREGISTERED] = _UNREGISTEREDUSER;
    while(list($id, $name) = $result->fields) {

        $result->MoveNext();
        $users[$id] = $name;
    }
    $result->Close();

    return($users);
}

/*
 * getGroupsInfo - get groups information
 * Takes no parameters
 */
function permissions_getGroupsInfo()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $grouptable = $pntable['groups'];
    $groupcolumn = &$pntable['groups_column'];

    $query = "SELECT $groupcolumn[gid],
                     $groupcolumn[name]
              FROM $grouptable
              ORDER BY $groupcolumn[name]";
    $result = $dbconn->Execute($query);
    $groups[_PNPERMS_ALL] = _ALLGROUPS;
    $groups[_PNPERMS_UNREGISTERED] = _UNREGISTEREDGROUP;
    while(list($gid, $name) = $result->fields) {

        $result->MoveNext();
        $groups[$gid] = $name;
    }
    $result->Close();

    return($groups);
}

/*
 * getRealmsInfo - get realms information
 * Takes no parameters
 */
function permissions_getRealmsInfo()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $realmtable = $pntable['realms'];
    $realmcolumn = &$pntable['realms_column'];

    $query = "SELECT $realmcolumn[rid],
                     $realmcolumn[name]
              FROM $realmtable";
    $result = $dbconn->Execute($query);
    $realms[0] = _ALLREALMS;
    while(list($rid, $rname) = $result->fields) {

        $result->MoveNext();
        $realms[$rid] = $rname;
    }
    $result->Close();

    return($realms);
}

/*
 * showInstanceInformation  - Show instance information gathered
 *                            from blocks and modules
 * Takes no parameters
 */
function permissions_admin_viewinstanceinfo()
{
    // Pretty much raw HTML here
    $output =  '<html>
                <head>
                </head>
                <body>
                <center>
                <h1>'._PERMISSIONINFO.'</h1>
                <table border="3">
                <tr>
                <th><center>
                '._REGISTEREDCOMP.'
                </center></th>
                <th><center>
                '._INSTANCETEMP.'
                </center></th>
                </tr>';

    $schemas = getinstanceschemainfo();
    ksort($schemas);
    foreach ($schemas as $k => $v) {
        $output .= '<tr>
                    <td><center>';
        $output .= $k;
        $output .= '</center></td>
                    <td><center>';
        $output .= $v;
        $output .= '</center></td>
                    </tr>';
   }
   $output .= '</table>
               </center>
               </body>
               </html>';

    echo $output;
    return true;
}

/*
 * Generate menu fragment
 */
function permissions_adminmenu()
{
    $output = new pnHTML();

    $output->Text(pnGetStatusMsg());
    $output->Linebreak(2);

    $output->TableStart(_PERMISSIONS);
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $columns = array();
    $columns[] = $output->URL(pnModURL('Permissions', 'admin', 'new', array('permtype' => 'group')), _NEWGROUPPERM);
    $columns[] = $output->URL(pnModURL('Permissions', 'admin', 'new', array('permtype' => 'user')), _NEWUSERPERM);
    $columns[] = $output->URL(pnModURL('Permissions', 'admin', 'view', array('permtype' => 'group')), _VIEWGROUPPERMS);
    $columns[] = $output->URL(pnModURL('Permissions', 'admin', 'view', array('permtype' => 'user')), _VIEWUSERPERMS);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($columns);
    $output->SetInputMode(_PNH_PARSEINPUT);

    $output->TableEnd();

    return $output->GetOutput();
}

/*
 * Generate Javascript fragment
 */
function permissions_javascript()
{
    $output = new pnHTML();

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text('<script type="text/javascript">
                   function showinstanceinformation() {
                     window.open ("'. pnModURL('Permissions',
                                               'admin',
                                               'viewinstanceinfo') . '",
                                  "Instance_Information","toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=no,copyhistory=no,width=400,height=300");
                   }</script>');
    return $output->GetOutput();
}

?>