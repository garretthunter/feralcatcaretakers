<?php
// $Id: pnadminapi.php,v 1.2 2003/04/19 23:36:28 garrett Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
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
// Original Author of file: Thomas Smiatek
// Purpose of file:  pnAddressBook administration API
// ----------------------------------------------------------------------

function pnAddressBook_adminapi_getCategories() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cat_table = $pntable['pnaddressbook_categories'];
	$cat_column = $pntable['pnaddressbook_categories_column'];
    $sql = "SELECT $cat_column[nr], $cat_column[name]
            FROM $cat_table
			ORDER BY $cat_column[nr]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $categories = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($catid,$catname) = $result->fields;
        $categories[$i]['nr']     = $catid;
        $categories[$i++]['name']   = $catname;
     }
    $result->Close();
    return $categories;
}

function pnAddressBook_adminapi_updateCategories($args)
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
        $result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}
function pnAddressBook_adminapi_deleteCategories($args)
{
    extract($args);
    if(!isset($delete)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if($result === false) {
        return false;
    }
    return true;
}
function pnAddressBook_adminapi_addCategories($args)
{
    extract($args);
    if(!isset($name)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$cat_table = $pntable[pnaddressbook_categories];
	$cat_column = $pntable['pnaddressbook_categories_column'];

    $name = pnVarPrepForStore($name);

    $result = $dbconn->Execute("INSERT INTO $cat_table
                                ($cat_column[nr],$cat_column[name])
                                VALUES ('','$name')");
    if($result === false) {
        return false;
    }

	return true;
}
function pnAddressBook_adminapi_getCustomfields() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cus_table = $pntable['pnaddressbook_customfields'];
	$cus_column = $pntable['pnaddressbook_customfields_column'];
    $sql = "SELECT $cus_column[nr], $cus_column[name], $cus_column[type], $cus_column[position]
            FROM $cus_table WHERE $cus_column[nr] > 0
			ORDER BY $cus_column[position]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $customfields = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($cusid,$cusname,$custype,$cuspos) = $result->fields;
        $customfields[$i]['nr']     = $cusid;
        $customfields[$i]['name']   = $cusname;
		$customfields[$i]['type']   = $custype;
		$customfields[$i++]['position']   = $cuspos;
     }
    $result->Close();
    return $customfields;
}

function pnAddressBook_adminapi_updateCustomfields($args)
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
        $result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}
function pnAddressBook_adminapi_deleteCustomfields($args)
{
    extract($args);
    if(!isset($deletes)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($deletes as $delete) {
        $result = $dbconn->Execute($delete);
        if($result === false) {
           return false;
        }
		// Resequence the fields
	    pnAddressBook_adminapi_resequenceCustomfields();
    }
	return true;
}
function pnAddressBook_adminapi_addCustomfields($args)
{
    extract($args);
    if(!isset($inserts)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($inserts as $insert) {
        $result = $dbconn->Execute($insert);
        if($result === false) {
            return false;
        }
    }
	// Resequence the fields
    pnAddressBook_adminapi_resequenceCustomfields();
    return true;
}
function pnAddressBook_adminapi_resequenceCustomfields() {
	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cus_table = $pntable['pnaddressbook_customfields'];
	$cus_column = $pntable['pnaddressbook_customfields_column'];

    // Get the information
    $query = "SELECT $cus_column[nr], $cus_column[position]
              FROM $cus_table
              ORDER BY $cus_column[position]";
    $result = $dbconn->Execute($query);

    // Fix sequence numbers
    $seq=1;
    while(list($id, $curseq) = $result->fields) {
        $result->MoveNext();
        if ($curseq != $seq) {
            $query = "UPDATE $cus_table
                      SET $cus_column[position]=" . pnVarPrepForStore($seq) . "
                      WHERE $cus_column[nr]=" . pnVarPrepForStore($id);
            $dbconn->Execute($query);
        }
        $seq++;
    }
    $result->Close();
    return;
}
function pnAddressBook_adminapi_incCustomfields($args) {
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'pnAddressBook::', "::", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PNADDRESSBOOKNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cus_table = $pntable['pnaddressbook_customfields'];
	$cus_column = $pntable['pnaddressbook_customfields_column'];

    // Get info on current position of field
    $sql = "SELECT $cus_column[position]
            FROM $cus_table
            WHERE $cus_column[nr]=" . (int)pnVarPrepForStore($id);
    $result = $dbconn->Execute($sql);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such field ID $id");
        return false;
    }
    list($seq) = $result->fields;
    $result->Close();

    // Get info on displaced field
    $sql = "SELECT $cus_column[nr],
                   $cus_column[position]
            FROM $cus_table
            WHERE $cus_column[position]<" . pnVarPrepForStore($seq) . "
            ORDER BY $cus_column[position] DESC";
    $result = $dbconn->SelectLimit($sql, 1);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No field directly above that one");
        return false;
    }
    list($altid, $altseq) = $result->fields;
    $result->Close();

    // Swap sequence numbers
    $sql = "UPDATE $cus_table
            SET $cus_column[position]=$seq
            WHERE $cus_column[nr]=$altid";
    $dbconn->Execute($sql);
    $sql = "UPDATE $cus_table
            SET $cus_column[position]=$altseq
            WHERE $cus_column[nr]=$id";
    $dbconn->Execute($sql);

    return true;
}
function pnAddressBook_adminapi_decCustomfields($args) {
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($id) || !is_numeric($id)) {
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'pnAddressBook::', "::", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _PNADDRESSBOOKNOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $cus_table = $pntable['pnaddressbook_customfields'];
	$cus_column = $pntable['pnaddressbook_customfields_column'];

    // Get info on current position of field
    $sql = "SELECT $cus_column[position]
            FROM $cus_table
            WHERE $cus_column[nr]=" . (int)pnVarPrepForStore($id);
    $result = $dbconn->Execute($sql);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No such field ID $id");
        return false;
    }
    list($seq) = $result->fields;
    $result->Close();

    // Get info on displaced field
    $sql = "SELECT $cus_column[nr],
                   $cus_column[position]
            FROM $cus_table
            WHERE $cus_column[position]>" . pnVarPrepForStore($seq) . "
            ORDER BY $cus_column[position] ASC";
    $result = $dbconn->SelectLimit($sql, 1);

    if ($result->EOF) {
        pnSessionSetVar('errormsg', "No field directly below that one");
        return false;
    }
    list($altid, $altseq) = $result->fields;
    $result->Close();

    // Swap sequence numbers
    $sql = "UPDATE $cus_table
            SET $cus_column[position]=$seq
            WHERE $cus_column[nr]=$altid";
    $dbconn->Execute($sql);
    $sql = "UPDATE $cus_table
            SET $cus_column[position]=$altseq
            WHERE $cus_column[nr]=$id";
    $dbconn->Execute($sql);

    return true;
}
function pnAddressBook_adminapi_getPrefixes() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $pre_table = $pntable['pnaddressbook_prefixes'];
	$pre_column = $pntable['pnaddressbook_prefixes_column'];
    $sql = "SELECT $pre_column[nr], $pre_column[name]
            FROM $pre_table
			ORDER BY $pre_column[nr]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $prefixes = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($preid,$prename) = $result->fields;
        $prefixes[$i]['nr']     = $preid;
        $prefixes[$i++]['name']   = $prename;
     }
    $result->Close();
    return $prefixes;
}

function pnAddressBook_adminapi_updatePrefixes($args)
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
        $result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}
function pnAddressBook_adminapi_deletePrefixes($args)
{
    extract($args);
    if(!isset($delete)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if($result === false) {
        return false;
    }
    return true;
}
function pnAddressBook_adminapi_addPrefixes($args)
{
    extract($args);
    if(!isset($name)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$pre_table = $pntable[pnaddressbook_prefixes];
	$pre_column = $pntable['pnaddressbook_prefixes_column'];

    $name = pnVarPrepForStore($name);

    $result = $dbconn->Execute("INSERT INTO $pre_table
                                ($pre_column[nr],$pre_column[name])
                                VALUES ('','$name')");
    if($result === false) {
        return false;
    }

	return true;
}
function pnAddressBook_adminapi_getLabels()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $lab_table = $pntable['pnaddressbook_labels'];
	$lab_column = $pntable['pnaddressbook_labels_column'];
    $sql = "SELECT $lab_column[nr], $lab_column[name]
            FROM $lab_table
			ORDER BY $lab_column[nr]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $labels = array();
    for($i=0; !$result->EOF; $result->MoveNext()) {
        list($labid,$labname) = $result->fields;
        $labels[$i]['nr']     = $labid;
        $labels[$i++]['name']   = $labname;
     }
    $result->Close();
    return $labels;
}
function pnAddressBook_adminapi_updatelabels($args)
{
    extract($args);
    if(!isset($updates)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    foreach($updates as $update) {
        $result = $dbconn->Execute($update);
        if($result === false) {
            return false;
        }
    }
    return true;
}
function pnAddressBook_adminapi_deletelabels($args)
{
    extract($args);
    if(!isset($delete)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if($result === false) {
        return false;
    }
    return true;
}
function pnAddressBook_adminapi_addlabels($args)
{
    extract($args);
    if(!isset($name)) {
        return false;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$lab_table = $pntable[pnaddressbook_labels];
	$lab_column = $pntable['pnaddressbook_labels_column'];

    $name = pnVarPrepForStore($name);

    $result = $dbconn->Execute("INSERT INTO $lab_table
                                ($lab_column[nr],$lab_column[name])
                                VALUES ('','$name')");
    if($result === false) {
        return false;
    }
    return true;
}

function pnAddressBook_adminapi_getsubmitvalues() {
    $args = array();

	$cat_id = pnVarCleanFromInput('cat_id');
	$prfx = pnVarCleanFromInput('prfx');
    $lname = pnVarCleanFromInput('lname');
    $fname = pnVarCleanFromInput('fname');
	$title = pnVarCleanFromInput('title');
	$company = pnVarCleanFromInput('company');
	$zip = pnVarCleanFromInput('zip');
	$city = pnVarCleanFromInput('city');
	$address1 = pnVarCleanFromInput('address1');
	$address2 = pnVarCleanFromInput('address2');
	$state = pnVarCleanFromInput('state');
	$country = pnVarCleanFromInput('country');
	$contact_1 = pnVarCleanFromInput('contact_1');
	$contact_2 = pnVarCleanFromInput('contact_2');
	$contact_3 = pnVarCleanFromInput('contact_3');
	$contact_4 = pnVarCleanFromInput('contact_4');
	$contact_5 = pnVarCleanFromInput('contact_5');
	$c_label_1 = pnVarCleanFromInput('c_label_1');
	$c_label_2 = pnVarCleanFromInput('c_label_2');
	$c_label_3 = pnVarCleanFromInput('c_label_3');
	$c_label_4 = pnVarCleanFromInput('c_label_4');
	$c_label_5 = pnVarCleanFromInput('c_label_5');
	$c_main = pnVarCleanFromInput('c_main');
	$custom_1 = pnVarCleanFromInput('custom_1');
	$custom_2 = pnVarCleanFromInput('custom_2');
	$custom_3 = pnVarCleanFromInput('custom_3');
	$custom_4 = pnVarCleanFromInput('custom_4');
	$note = pnVarCleanFromInput('note');
	$user_id = pnVarCleanFromInput('user_id');
	$private = pnVarCleanFromInput('private');

	if (isset($cat_id)) { $args['cat_id'] = $cat_id; }
	if (isset($prfx)) { $args['prfx'] = $prfx; }
	if (isset($lname)) { $args['lname'] = $lname; }
	if (isset($fname)) { $args['fname'] = $fname; }
	if (isset($title)) { $args['title'] = $title; }
	if (isset($company)) { $args['company'] = $company; }
	if (isset($address1)) { $args['address1'] = $address1; }
	if (isset($address2)) { $args['address2'] = $address2; }
	if (isset($city)) { $args['city'] = $city; }
	if (isset($state)) { $args['state'] = $state; }
	if (isset($zip)) { $args['zip'] = $zip; }
	if (isset($country)) { $args['country'] = $country; }
	if (isset($contact_1)) { $args['contact_1'] = $contact_1; }
	if (isset($contact_2)) { $args['contact_2'] = $contact_2; }
	if (isset($contact_3)) { $args['contact_3'] = $contact_3; }
	if (isset($contact_4)) { $args['contact_4'] = $contact_4; }
	if (isset($contact_5)) { $args['contact_5'] = $contact_5; }
	if (isset($c_label_1)) { $args['c_label_1'] = $c_label_1; }
	else { $args['c_label_1'] = '1'; }
	if (isset($c_label_2)) { $args['c_label_2'] = $c_label_2; }
	else { $args['c_label_2'] = '2'; }
	if (isset($c_label_3)) { $args['c_label_3'] = $c_label_3; }
	else { $args['c_label_3'] = '3'; }
	if (isset($c_label_4)) { $args['c_label_4'] = $c_label_4; }
	else { $args['c_label_4'] = '4'; }
	if (isset($c_label_5)) { $args['c_label_5'] = $c_label_5; }
	else { $args['c_label_5'] = '5'; }
	if (isset($c_main)) { $args['c_main'] = $c_main; }
	else { $args['c_main'] = 0; }
	if (isset($custom_1)) { $args['custom_1'] = $custom_1; }
	if (isset($custom_2)) { $args['custom_2'] = $custom_2; }
	if (isset($custom_3)) { $args['custom_3'] = $custom_3; }
	if (isset($custom_4)) { $args['custom_4'] = $custom_4; }
	if (isset($user_id)) { $args['user_id'] = $user_id; }
	if (isset($note)) { $args['note'] = $note; }
	if (isset($private)) { $args['private'] = $private; }

    return $args;
}
function pnAddressBook_adminapi_checksubmitvalues($args) {
	extract($args);
	if ( (empty($lname)) && (empty($fname)) && (empty($title)) && (empty($company))) {
		return false;
	}
	return true;
}
function pnAddressBook_adminapi_insertrecord($args) {
	extract($args);

    $lname = pnAddressBook_adminapi_SecurityCheck($lname);
    $fname = pnAddressBook_adminapi_SecurityCheck($fname);
	$title = pnAddressBook_adminapi_SecurityCheck($title);
	$company = pnAddressBook_adminapi_SecurityCheck($company);
	$zip = pnAddressBook_adminapi_SecurityCheck($zip);
	$city = pnAddressBook_adminapi_SecurityCheck($city);
	$address1 = pnAddressBook_adminapi_SecurityCheck($address1);
	$address2 = pnAddressBook_adminapi_SecurityCheck($address2);
	$state = pnAddressBook_adminapi_SecurityCheck($state);
	$country = pnAddressBook_adminapi_SecurityCheck($country);
	$contact_1 = pnAddressBook_adminapi_SecurityCheck($contact_1);
	$contact_2 = pnAddressBook_adminapi_SecurityCheck($contact_2);
	$contact_3 = pnAddressBook_adminapi_SecurityCheck($contact_3);
	$contact_4 = pnAddressBook_adminapi_SecurityCheck($contact_4);
	$contact_5 = pnAddressBook_adminapi_SecurityCheck($contact_5);
	$custom_1 = pnAddressBook_adminapi_SecurityCheck($custom_1);
	$custom_2 = pnAddressBook_adminapi_SecurityCheck($custom_2);
	$custom_3 = pnAddressBook_adminapi_SecurityCheck($custom_3);
	$custom_4 = pnAddressBook_adminapi_SecurityCheck($custom_4);
	$note = pnAddressBook_adminapi_SecurityCheck($note);
	if (!isset($private)) { $private=0; }
	$date = GetUserTime(time());

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
    $address_column = &$pntable['pnaddressbook_address_column'];
	$sql = "INSERT INTO $address_table (
			$address_column[cat_id],
            $address_column[prefix],
			$address_column[lname],
            $address_column[fname],
			$address_column[title],
			$address_column[company],
			$address_column[zip],
			$address_column[city],
			$address_column[address1],
			$address_column[address2],
			$address_column[state],
			$address_column[country],
			$address_column[contact_1],
			$address_column[contact_2],
			$address_column[contact_3],
			$address_column[contact_4],
			$address_column[contact_5],
			$address_column[c_label_1],
			$address_column[c_label_2],
			$address_column[c_label_3],
			$address_column[c_label_4],
			$address_column[c_label_5],
			$address_column[c_main],
			$address_column[custom_1],
			$address_column[custom_2],
			$address_column[custom_3],
			$address_column[custom_4],
			$address_column[note],
			$address_column[user_id],
			$address_column[private],
			$address_column[date])
			VALUES (
			".pnVarPrepForStore($cat_id).",
			'".pnVarPrepForStore($prfx)."',
            '".pnVarPrepForStore($lname)."',
            '".pnVarPrepForStore($fname)."',
			'".pnVarPrepForStore($title)."',
			'".pnVarPrepForStore($company)."',
			'".pnVarPrepForStore($zip)."',
			'".pnVarPrepForStore($city)."',
			'".pnVarPrepForStore($address1)."',
			'".pnVarPrepForStore($address2)."',
			'".pnVarPrepForStore($state)."',
			'".pnVarPrepForStore($country)."',
			'".pnVarPrepForStore($contact_1)."',
			'".pnVarPrepForStore($contact_2)."',
			'".pnVarPrepForStore($contact_3)."',
			'".pnVarPrepForStore($contact_4)."',
			'".pnVarPrepForStore($contact_5)."',
			".pnVarPrepForStore($c_label_1).",
			".pnVarPrepForStore($c_label_2).",
			".pnVarPrepForStore($c_label_3).",
			".pnVarPrepForStore($c_label_4).",
			".pnVarPrepForStore($c_label_5).",
			".pnVarPrepForStore($c_main).",
			'".pnVarPrepForStore($custom_1)."',
			'".pnVarPrepForStore($custom_2)."',
			'".pnVarPrepForStore($custom_3)."',
			'".pnVarPrepForStore($custom_4)."',
			'".pnVarPrepForStore($note)."',
			".pnVarPrepForStore($user_id).",
			".pnVarPrepForStore($private).",
			".pnVarPrepForStore($date).")";

	$result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return $sql; }

	$result->Close();
	return true;
}
function pnAddressBook_adminapi_SecurityCheck($value) {
	//SecurityCheck
	$value = preg_replace("'<img(.*)src=(.*)(;|\()(.*?)>'i","*******",$value);
	$value = preg_replace("#(<[a-zA-Z])(.*)(;|\()(.*?)(/[a-zA-Z])(.*?)>#si","*******",$value);
	$value = eregi_replace("javascript:","*******",$value);

	return $value;
}
function pnAddressBook_adminapi_getCompanies() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];
    $sql = "SELECT DISTINCT $adr_column[company],$adr_column[nr]
            FROM $adr_table
			ORDER BY $adr_column[company]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $companies[] = array('id'=>'0','name'=>pnVarPrepHTMLDisplay(_pnAB_ALLCOMPANIES));
    for($i=1; !$result->EOF; $result->MoveNext()) {
        list($adr_company,$adr_id) = $result->fields;
        $companies[]     = array('id'=>$adr_id,'name'=>$adr_company);
     }
    $result->Close();
    return $companies;
}

function pnAddressBook_adminapi_getCompanyAddress($args) {
    extract($args);
	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];
    $sql = "SELECT $adr_column[company],$adr_column[address1],$adr_column[address2],$adr_column[zip],$adr_column[city],$adr_column[state],$adr_column[country]
            FROM $adr_table
			WHERE $adr_column[nr] = $id";

    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
	if(!isset($result)) { return $sql; }

    //for($i=1; !$result->EOF; $result->MoveNext()) {
        list($adr_company,$adr_address1,$adr_address_2,$adr_zip,$adr_city,$adr_state,$adr_country) = $result->fields;
        $compaddress = array(	'company'=>$adr_company,
								'address1'=>$adr_address1,
								'address2'=>$adr_address_2,
								'zip'=>$adr_zip,
								'city'=>$adr_city,
								'state'=>$adr_state,
								'country'=>$adr_country);
     //}
    $result->Close();
    return $compaddress;
}
function pnAddressBook_adminapi_FormSelect($args) {
    extract($args);

	if (empty ($fieldname))
    {
        return;
    }

    // Set up selected if required
    if (!empty($selected)) {
        for ($i=0; !empty($data[$i]); $i++) {
            if ($data[$i]['id'] == $selected) {
                $data[$i]['selected'] = 1;
            }
        }
    }

    $c = count($data);
    if ($c < $size)
    {
        $size = $c;
    }
    $output = '<select';
    $output .= ' name="'.pnVarPrepForDisplay($fieldname).'"';
    $output .= ' id="'.pnVarPrepForDisplay($fieldname).'"';
    $output .= ' size="'.pnVarPrepForDisplay($size).'"';
    $output .= (($multiple == 1) ? ' multiple="multiple"' : '');
    $output .= ((empty ($accesskey)) ? '' : ' accesskey="'.pnVarPrepForDisplay($accesskey).'"');
    $output .= ' tabindex="'.$this->tabindex.'"';
    if (isset($lookup)) {
			$output .= ' onchange="this.form.action.value=5;this.form.submit();"';
	}
	else {
			$output .= ' onchange="this.form.submit();"';
	}
	$output .= '>';

    foreach ($data as $datum)
    {
        $output .= '<option'
            .' value="'.pnVarPrepForDisplay($datum['id']).'"'
            .((empty ($datum['selected'])) ? '' : ' selected="selected"')
            .'>'
            .pnVarPrepForDisplay($datum['name'])
            .'</option>'
        ;
    }
    $output .= '</select>';
    return $output;
}
function pnAddressBook_adminapi_customFieldInformation($args) {
	extract($args);
	if( (!isset($id)) || (empty($id)) || ($id=='')) {
		$custs = pnAddressBook_adminapi_getCustomfields();
        for($i=0; $i<count($custs); $i++) {
        	$cus_Fields[$i]['nr'] = $custs[$i]['nr'];
			$cus_Fields[$i]['name'] = $custs[$i]['name'];
			$cus_Fields[$i]['type'] = $custs[$i]['type'];
			$cus_Fields[$i]['value'] = '';
		}
		return $cus_Fields;
    }

	// get info from cus_table
	$custs = pnAddressBook_adminapi_getCustomfields();

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];

	// build a sql statement
	$sql = 'SELECT ';
	foreach($custs as $cust) {
		$sql .= 'adr_custom_'.$cust[nr].',';
	}
	$sql = substr($sql,0,-1);
	$sql .= ' FROM '.$adr_table.' WHERE '.$adr_column[nr].' = '.$id;

	// query
	$result = $dbconn->Execute($sql);
	if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

	for($i=0; $i<count($custs); $i++) {
        $cus_Fields[$i]['nr'] = $custs[$i]['nr'];
		$cus_Fields[$i]['name'] = $custs[$i]['name'];
		$cus_Fields[$i]['type'] = $custs[$i]['type'];
		$cus_Fields[$i]['value'] = $result->fields[$i];
     }

	$result->Close();
	return $cus_Fields;
}
?>