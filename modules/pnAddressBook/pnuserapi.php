<?php
// $Id: pnuserapi.php,v 1.2 2003/04/19 23:36:29 garrett Exp $
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

function pnAddressBook_userapi_getDetailValues($args) {
	extract($args);
	list($dbconn) = pnDBGetConn();
	$pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
	$address_column = &$pntable['pnaddressbook_address_column'];
	$sql = "SELECT * FROM $address_table WHERE ($address_column[nr]=$id)";
	$result = $dbconn->Execute($sql);
	if($dbconn->ErrorNo() != 0) { return array(); }
	if(!isset($result)) { return $sql; }
	list($adr_id,$adr_cat_id,$adr_prefix,$adr_lname,$adr_fname,$adr_sortname,$adr_title,$adr_company,$adr_sortcompany,$adr_img,$adr_zip,$adr_city,$adr_address1,$adr_address2,$adr_state,$adr_country,$adr_contact_1,$adr_contact_2,$adr_contact_3,$adr_contact_4,$adr_contact_5,$adr_c_label_1,$adr_c_label_2,$adr_c_label_3,$adr_c_label_4,$adr_c_label_5,$adr_c_main,$adr_custom_1,$adr_custom_2,$adr_custom_3,$adr_custom_4,$adr_note,$adr_user,$adr_private,
	$adr_date,$adr_listname) = $result->fields;
	$detailValues = array('cat_id'=>$adr_cat_id,
						'prfx'=>$adr_prefix,
						'lname'=>$adr_lname,
						'fname'=>$adr_fname,
						'sortname'=>$adr_sortname,
						'title'=>$adr_title,
						'company'=>$adr_company,
						'sortcompany'=>$adr_sortcompany,
						'img'=>$adr_img,
						'zip'=>$adr_zip,
						'city'=>$adr_city,
						'address1'=>$adr_address1,
						'address2'=>$adr_address2,
						'state'=>$adr_state,
						'country'=>$adr_country,
						'contact_1'=>$adr_contact_1,
						'contact_2'=>$adr_contact_2,
						'contact_3'=>$adr_contact_3,
						'contact_4'=>$adr_contact_4,
						'contact_5'=>$adr_contact_5,
						'c_label_1'=>$adr_c_label_1,
						'c_label_2'=>$adr_c_label_2,
						'c_label_3'=>$adr_c_label_3,
						'c_label_4'=>$adr_c_label_4,
						'c_label_5'=>$adr_c_label_5,
						'c_main'=>$adr_c_main,
						'note'=>$adr_note,
						'user'=>$adr_user,
						'private'=>$adr_private,
						'date'=>$adr_date);

	$result->Close();
	return $detailValues;
}
function pnAddressBook_userapi_customFieldInformation($args) {
	extract($args);
	if( (!isset($id)) || (empty($id)) || ($id=='')) {
		$custs = pnAddressBook_userapi_getCustomfields();
        for($i=0; $i<count($custs); $i++) {
			if ((!strstr($custs[$i]['type'],'tinyint')) && (!strstr($custs[$i]['type'],'smallint'))) {
	        	$cus_Fields[$i]['nr'] = $custs[$i]['nr'];
				$cus_Fields[$i]['name'] = $custs[$i]['name'];
				$cus_Fields[$i]['type'] = $custs[$i]['type'];
				$cus_Fields[$i]['value'] = '';
			}
		}
		return $cus_Fields;
    }

	// get info from cus_table
	$custs = pnAddressBook_userapi_getCustomfields();

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];

	// build a sql statement
	$sql = 'SELECT ';
	foreach($custs as $cus) {
		if ((!strstr($cus['type'],'tinyint')) && (!strstr($cus['type'],'smallint'))) {
			$sql .= 'adr_custom_'.$cus[nr].',';
		}
	}
	$sql = substr($sql,0,-1);
	$sql .= ' FROM '.$adr_table.' WHERE '.$adr_column[nr].' = '.$id;

	// query
	$result = $dbconn->Execute($sql);
	if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

	$x=0;
	for($i=0; $i<count($custs); $i++) {
        $cus_Fields[$i]['nr'] = $custs[$i]['nr'];
		$cus_Fields[$i]['name'] = $custs[$i]['name'];
		$cus_Fields[$i]['type'] = $custs[$i]['type'];
		if ((!strstr($custs[$i]['type'],'tinyint')) && (!strstr($custs[$i]['type'],'smallint'))) {
			$cus_Fields[$i]['value'] = $result->fields[$x];
			$x++;
		}
		else {
			$cus_Fields[$i]['value'] = '';
		}
     }

	$result->Close();
	return $cus_Fields;
}

function pnAddressBook_userapi_getCategories() {
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

    $categories[] = array('id'=>'0','name'=>pnVarPrepHTMLDisplay(_pnAB_ALLCATEGORIES));
    for($i=1; !$result->EOF; $result->MoveNext()) {
        list($catid,$catname) = $result->fields;
        $categories[]     = array('id'=>$catid,'name'=>$catname);
     }
    $result->Close();
    return $categories;
}

function pnAddressBook_userapi_getFormCategories() {
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

function pnAddressBook_userapi_getFormPrefixes() {
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
function pnAddressBook_userapi_getPrefixes() {
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

	$prefixes[] = array('id'=>'0','name'=>pnVarPrepHTMLDisplay(_pnAB_NOPREFIX));
    for($i=1; !$result->EOF; $result->MoveNext()) {
        list($preid,$prename) = $result->fields;
        $prefixes[$i]['nr']     = $preid;
        $prefixes[$i++]['name']   = $prename;
     }
    $result->Close();
    return $prefixes;
}
function pnAddressBook_userapi_getCustomfields() {
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
function pnAddressBook_userapi_getCompanies() {
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];
    $sql = "SELECT DISTINCT $adr_column[company]
            FROM $adr_table
			ORDER BY $adr_column[company]";
    $result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $companies[] = array('id'=>'0','name'=>pnVarPrepHTMLDisplay(_pnAB_ALLCOMPANIES));
    for($i=1; !$result->EOF; $result->MoveNext()) {
        list($adr_company) = $result->fields;
        $companies[]     = array('id'=>$adr_company,'name'=>$adr_company);
     }
    $result->Close();
    return $companies;
}
function pnAddressBook_userapi_getCompanyAddress($args) {
    extract($args);
	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $adr_table = $pntable['pnaddressbook_address'];
	$adr_column = $pntable['pnaddressbook_address_column'];
    $sql = "SELECT $adr_column[company],$adr_column[address1],$adr_column[address2],$adr_column[zip],$adr_column[city],$adr_column[state],$adr_column[country]
            FROM $adr_table
			WHERE $adr_column[company] = '$id'";

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
function pnAddressBook_userapi_checkAccessLevel($args) {
	$access=false;
	$usermode = (pnModGetVar(__PNADDRESSBOOK__, 'usermode'));
	$guestmode = (pnModGetVar(__PNADDRESSBOOK__, 'guestmode'));
	extract($args);

	switch($option) {
		case 'view':
			if (pnUserLoggedIn()) {
				if ((pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_EDIT)) || (pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_MODERATE))) {
					$access = true;
					break;
				}
				else {
					if ($usermode >= 4) {
						$access = true;
						break;
					}
					else {
						$access = false;
						break;
					}
				}
			}
			else {
				if ($guestmode >= 4) {
					$access = true;
					break;
				}
				else {
					$access = false;
					break;
				}
			}
		case 'create':
			if (pnUserLoggedIn()) {
				if ((pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_EDIT)) || (pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_MODERATE))) {
					$access = true;
					break;
				}
				else {
					if (($usermode == 6) || ($usermode == 7) || ($usermode == 2) || ($usermode == 3)) {
						$access = true;
						break;
					}
					else {
						$access = false;
						break;
					}
				}
			}
			else {
				if (($guestmode == 6) || ($guestmode == 7) || ($guestmode == 2) || ($guestmode == 3)) {
					$access = true;
					break;
				}
				else {
					$access = false;
					break;
				}
			}
		case 'edit':
			if (pnUserLoggedIn()) {
				if ((pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_EDIT)) || (pnSecAuthAction(0, 'pnAddressBook::', '::', ACCESS_MODERATE))) {
					$access = true;
					break;
				}
				else {
					if (($usermode == 5) || ($usermode == 7) || ($usermode == 1) || ($usermode == 3)) {
						$access = true;
						break;
					}
					else {
						$access = false;
						break;
					}
				}
			}
			else {
				if (($guestmode == 5) || ($guestmode == 7) || ($guestmode == 1) || ($guestmode == 3)) {
					$access = true;
					break;
				}
				else {
					$access = false;
					break;
				}
			}
	}

	return $access;
}

function pnAddressBook_userapi_getLabels()
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

function pnAddressBook_userapi_getsubmitvalues() {
    $args = array();

	$id = pnVarCleanFromInput('id');
	$cat_id = pnVarCleanFromInput('cat_id');
	$prfx = pnVarCleanFromInput('prfx');
    $lname = pnVarCleanFromInput('lname');
    $fname = pnVarCleanFromInput('fname');
	$title = pnVarCleanFromInput('title');
	$company = pnVarCleanFromInput('company');
	$img = pnVarCleanFromInput('img');
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
	$note = pnVarCleanFromInput('note');
	$user_id = pnVarCleanFromInput('user_id');
	$private = pnVarCleanFromInput('private');
	$date = pnVarCleanFromInput('date');
	$formcall = pnVarCleanFromInput('formcall');

	$args['id'] = ((isset($id)) ? $id : false);
	$args['cat_id'] = ((isset($cat_id)) ? $cat_id : false);
	$args['prfx'] = ((isset($prfx)) ? $prfx : false);
	$args['lname'] = ((isset($lname)) ? $lname : false);
	$args['fname'] = ((isset($fname)) ? $fname : false);
	$args['title'] = ((isset($title)) ? $title : false);
	$args['company'] = ((isset($company)) ? $company : false);
	$args['img'] = ((isset($img)) ? $img : false);
	$args['address1'] = ((isset($address1)) ? $address1 : false);
	$args['address2'] = ((isset($address2)) ? $address2 : false);
	$args['city'] = ((isset($city)) ? $city : false);
	$args['state'] = ((isset($state)) ? $state : false);
	$args['zip'] = ((isset($zip)) ? $zip : false);
	$args['country'] = ((isset($country)) ? $country : false);
	$args['contact_1'] = ((isset($contact_1)) ? $contact_1 : false);
	$args['contact_2'] = ((isset($contact_2)) ? $contact_2 : false);
	$args['contact_3'] = ((isset($contact_3)) ? $contact_3 : false);
	$args['contact_4'] = ((isset($contact_4)) ? $contact_4 : false);
	$args['contact_5'] = ((isset($contact_5)) ? $contact_5 : false);
	$args['c_label_1'] = ((isset($c_label_1)) ? $c_label_1 : '1');
	$args['c_label_2'] = ((isset($c_label_2)) ? $c_label_2 : '2');
	$args['c_label_3'] = ((isset($c_label_3)) ? $c_label_3 : '3');
	$args['c_label_4'] = ((isset($c_label_4)) ? $c_label_4 : '4');
	$args['c_label_5'] = ((isset($c_label_5)) ? $c_label_5 : '5');
	$args['c_main'] = ((isset($c_main)) ? $c_main : 0);
	$args['user_id'] = ((isset($user_id)) ? $user_id : false);
	$args['note'] = ((isset($note)) ? $note : false);
	$args['private'] = ((isset($private)) ? $private : 0);
	$args['date'] = ((isset($date)) ? $date : false);
	$args['formcall'] = ((isset($formcall)) ? $formcall : false);

	// add custom field values
	$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>''));
	foreach($cus_fields as $cus) {
		$the_name = 'custom_'.$cus['nr'];
		$temp = pnVarCleanFromInput($the_name);
		$args[$the_name] = ((isset($temp)) ? $temp : false);
	}

    return $args;
}

function pnAddressBook_userapi_getMenuValues() {
    $args = array();

	$catview = pnVarCleanFromInput('catview');
    $sortview = pnVarCleanFromInput('sortview');
    $formSearch = pnVarCleanFromInput('formSearch');
	$all = pnVarCleanFromInput('all');
	$menuprivate = pnVarCleanFromInput('menuprivate');
	$total=pnVarCleanFromInput('total');
	$page=pnVarCleanFromInput('page');
	$char=pnVarCleanFromInput('char');

	if ((isset($catview)) && (!empty($catview))) { $args['catview'] = $catview; }
	else { $args['catview'] = '0'; }
	$args['sortview'] = ((isset($sortview)) ? $sortview : '0');
	$args['formSearch'] = ((isset($formSearch)) ? $formSearch : '');
	$args['all'] = ((isset($all)) ? $all : '1');
	$args['menuprivate'] = ((isset($menuprivate)) ? $menuprivate : '0');
	//if (isset($total)) { $args['total'] = $total; }
	//if (isset($page)) { $args['page'] = $page; }
	//if (isset($char)) { $args['char'] = $char; }
	$args['total'] = ((isset($total)) ? $total : false);
	$args['page'] = ((isset($page)) ? $page : '1');
	$args['char'] = ((isset($char)) ? $char : false);

    return $args;
}

function pnAddressBook_userapi_checksubmitvalues($args) {
	extract($args);
	// check for empty fields
	if ( (empty($lname)) && (empty($fname)) && (empty($title)) && (empty($company))) {
		return _pnAB_UPDATE_CHKMSG_1;
	}
	// check for type of custom fields
	$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>''));
	foreach($cus_fields as $cus) {
		$the_name = 'custom_'.$cus['nr'];
		switch ($cus['type']) {
				case 'decimal(10,2) default NULL':
					if ((empty($$the_name) != 1) && (!ereg("^[+|-]{0,1}[0-9.,]{0,8}[.|,]{0,1}[0-9]{0,2}$",$$the_name,$regs))) {
						return _pnAB_CHKMSG_1;
					}
					break;
				case 'int default NULL':
					if ((empty($$the_name) != 1) && (!ereg("^[0-9]{1,9}$",$$the_name,$regs))) {
						return _pnAB_CHKMSG_2;
					}
					break;
				case 'date default NULL':
					if (empty($$the_name) != 1) {
						$dateformat = pnModGetVar(__PNADDRESSBOOK__,'dateformat');
						$token = "-./ ";
						$p1 = strtok($$the_name,$token);
						$p2 = strtok($token);
						$p3 = strtok($token);
						$p4 = strtok($token);
						$date = ""; $y = ""; $m = ""; $d = "";
						if ($dateformat == 1) {
							$y = $p3;
							$m = $p2;
							$d = $p1;
						}
						else {
							$y = $p3;
							$m = $p1;
							$d = $p2;
						}
						if ($y != "" && $y <= 99) {
							if ($y >= 70) $y = $y + 1900;
							if ($y < 70) $y = $y + 2000;
						}
						if (!checkdate($m, $d, $y)) {
							return _pnAB_CHKMSG_3;
						}
					}
					break;
			}
	}

	return false;
}
function pnAddressBook_userapi_insertrecord($args) {
	extract($args);

    $lname = pnAddressBook_userapi_SecurityCheck($lname);
    $fname = pnAddressBook_userapi_SecurityCheck($fname);
	$title = pnAddressBook_userapi_SecurityCheck($title);
	$company = pnAddressBook_userapi_SecurityCheck($company);
	$zip = pnAddressBook_userapi_SecurityCheck($zip);
	$city = pnAddressBook_userapi_SecurityCheck($city);
	$address1 = pnAddressBook_userapi_SecurityCheck($address1);
	$address2 = pnAddressBook_userapi_SecurityCheck($address2);
	$state = pnAddressBook_userapi_SecurityCheck($state);
	$country = pnAddressBook_userapi_SecurityCheck($country);
	$contact_1 = pnAddressBook_userapi_SecurityCheck($contact_1);
	$contact_2 = pnAddressBook_userapi_SecurityCheck($contact_2);
	$contact_3 = pnAddressBook_userapi_SecurityCheck($contact_3);
	$contact_4 = pnAddressBook_userapi_SecurityCheck($contact_4);
	$contact_5 = pnAddressBook_userapi_SecurityCheck($contact_5);
	$note = pnAddressBook_userapi_SecurityCheck($note);
	if (!isset($private)) { $private=0; }
	if (!pnUserLoggedIn()) { $user_id=0; }
	$date = GetUserTime(time());
	// custom field values
	$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>''));
	foreach($cus_fields as $cus) {
		$test = 'custom_'.$cus['nr'];
		if (strstr($cus['type'],'varchar')) {
			$$test = pnAddressBook_userapi_SecurityCheck($$test);
		}
	}
	// sort column
	if (pnModGetVar(__PNADDRESSBOOK__, 'name_order')==1) {
		$sortvalue = $fname.' '.$lname;
	}
	else {
		$sortvalue = $lname.', '.$fname;
	}
	$special1 = pnModGetVar(__PNADDRESSBOOK__, 'special_chars_1');
	$special2 = pnModGetVar(__PNADDRESSBOOK__, 'special_chars_2');
	for ($i=0;$i<strlen($special1);$i++) {
		$a[substr($special1,$i,1)]=substr($special2,$i,1);
	}
	if (is_array($a)) {
		$sortvalue = strtr($sortvalue, $a);
		$sortvalue2 = strtr($company, $a);
	}
	else {
		$sortvalue2 = $company;
	}

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
    $address_column = &$pntable['pnaddressbook_address_column'];
	$sql = "INSERT INTO $address_table (
			$address_column[cat_id],
			$address_column[prefix],
            $address_column[lname],
            $address_column[fname],
			$address_column[sortname],
			$address_column[title],
			$address_column[company],
			$address_column[sortcompany],
			$address_column[img],
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
			$address_column[c_main],";
			foreach($cus_fields as $cus) {
				$the_name = 'custom_'.$cus['nr'];
				$sql .= "adr_$the_name,";
			}
			$sql.="$address_column[note],
			$address_column[user_id],
			$address_column[private],
			$address_column[date])
			VALUES (
			".pnVarPrepForStore($cat_id).",
			".pnVarPrepForStore($prfx).",
            '".pnVarPrepForStore($lname)."',
            '".pnVarPrepForStore($fname)."',
			'".pnVarPrepForStore($sortvalue)."',
			'".pnVarPrepForStore($title)."',
			'".pnVarPrepForStore($company)."',
			'".pnVarPrepForStore($sortvalue2)."',
			'".pnVarPrepForStore($img)."',
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
			".pnVarPrepForStore($c_main).",";
			foreach($cus_fields as $cus) {
				$the_name = 'custom_'.$cus['nr'];
				if (strstr($cus['type'],'varchar')) {
					$sql .= "'".pnVarPrepForStore($$the_name)."',";
				}
				else {
					if ($cus['type']=='date default NULL') {
						$$the_name = pnModAPIFunc(__PNADDRESSBOOK__,'user','td2stamp',array('idate'=>$$the_name));
					}
					if ($cus['type']=='decimal(10,2) default NULL') {
						$$the_name = pnModAPIFunc(__PNADDRESSBOOK__,'user','input2numeric',array('inum'=>$$the_name));
					}
					if ((empty($$the_name)) || ($$the_name == '')) {
						$$the_name = 'NULL';
					}
					$sql .= pnVarPrepForStore($$the_name).",";
				}
			}
			$sql.="'".pnVarPrepForStore($note)."',
			".pnVarPrepForStore($user_id).",
			".pnVarPrepForStore($private).",
			".pnVarPrepForStore($date).")";

 	$result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) { return false; }

	$result->Close();
	return true;
}

function pnAddressBook_userapi_updaterecord($args) {
	extract($args);

    $lname = pnAddressBook_userapi_SecurityCheck($lname);
    $fname = pnAddressBook_userapi_SecurityCheck($fname);
	$title = pnAddressBook_userapi_SecurityCheck($title);
	$company = pnAddressBook_userapi_SecurityCheck($company);
	$zip = pnAddressBook_userapi_SecurityCheck($zip);
	$city = pnAddressBook_userapi_SecurityCheck($city);
	$address1 = pnAddressBook_userapi_SecurityCheck($address1);
	$address2 = pnAddressBook_userapi_SecurityCheck($address2);
	$state = pnAddressBook_userapi_SecurityCheck($state);
	$country = pnAddressBook_userapi_SecurityCheck($country);
	$contact_1 = pnAddressBook_userapi_SecurityCheck($contact_1);
	$contact_2 = pnAddressBook_userapi_SecurityCheck($contact_2);
	$contact_3 = pnAddressBook_userapi_SecurityCheck($contact_3);
	$contact_4 = pnAddressBook_userapi_SecurityCheck($contact_4);
	$contact_5 = pnAddressBook_userapi_SecurityCheck($contact_5);
	$note = pnAddressBook_userapi_SecurityCheck($note);
	if (!isset($private)) { $private=0; }
	$date = GetUserTime(time());
	// custom field values
	$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>''));
	foreach($cus_fields as $cus) {
		$test = 'custom_'.$cus['nr'];
		if (strstr($cus['type'],'varchar')) {
			$$test = pnAddressBook_userapi_SecurityCheck($$test);
		}
	}
	// sort column
	if (pnModGetVar(__PNADDRESSBOOK__, 'name_order')==1) {
		$sortvalue = $fname.' '.$lname;
	}
	else {
		$sortvalue = $lname.', '.$fname;
	}
	$special1 = pnModGetVar(__PNADDRESSBOOK__, 'special_chars_1');
	$special2 = pnModGetVar(__PNADDRESSBOOK__, 'special_chars_2');
	for ($i=0;$i<strlen($special1);$i++) {
		$a[substr($special1,$i,1)]=substr($special2,$i,1);
	}
	if (is_array($a)) {
		$sortvalue = strtr($sortvalue, $a);
		$sortvalue2 = strtr($company, $a);
	}
	else {
		$sortvalue2 = $company;
	}

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
    $address_column = &$pntable['pnaddressbook_address_column'];
	$sql = "UPDATE $address_table
			SET $address_column[cat_id]=".pnVarPrepForStore($cat_id).",
			$address_column[prefix]=".pnVarPrepForStore($prfx).",
            $address_column[lname]='".pnVarPrepForStore($lname)."',
            $address_column[fname]='".pnVarPrepForStore($fname)."',
			$address_column[sortname]='".pnVarPrepForStore($sortvalue)."',
			$address_column[title]='".pnVarPrepForStore($title)."',
			$address_column[company]='".pnVarPrepForStore($company)."',
			$address_column[sortcompany]='".pnVarPrepForStore($sortvalue2)."',
			$address_column[img]='".pnVarPrepForStore($img)."',
			$address_column[zip]='".pnVarPrepForStore($zip)."',
			$address_column[city]='".pnVarPrepForStore($city)."',
			$address_column[address1]='".pnVarPrepForStore($address1)."',
			$address_column[address2]='".pnVarPrepForStore($address2)."',
			$address_column[state]='".pnVarPrepForStore($state)."',
			$address_column[country]='".pnVarPrepForStore($country)."',
			$address_column[contact_1]='".pnVarPrepForStore($contact_1)."',
			$address_column[contact_2]='".pnVarPrepForStore($contact_2)."',
			$address_column[contact_3]='".pnVarPrepForStore($contact_3)."',
			$address_column[contact_4]='".pnVarPrepForStore($contact_4)."',
			$address_column[contact_5]='".pnVarPrepForStore($contact_5)."',
			$address_column[c_label_1]=".pnVarPrepForStore($c_label_1).",
			$address_column[c_label_2]=".pnVarPrepForStore($c_label_2).",
			$address_column[c_label_3]=".pnVarPrepForStore($c_label_3).",
			$address_column[c_label_4]=".pnVarPrepForStore($c_label_4).",
			$address_column[c_label_5]=".pnVarPrepForStore($c_label_5).",
			$address_column[c_main]=".pnVarPrepForStore($c_main).",";

	foreach($cus_fields as $cus) {
		$the_name = 'custom_'.$cus['nr'];
		if (strstr($cus['type'],'varchar')) {
			$sql .= "adr_$the_name = '".pnVarPrepForStore($$the_name)."',";
		}
		else {
			if ($cus['type']=='date default NULL') {
				$$the_name = pnModAPIFunc(__PNADDRESSBOOK__,'user','td2stamp',array('idate'=>$$the_name));
			}
			if ($cus['type']=='decimal(10,2) default NULL') {
				$$the_name = pnModAPIFunc(__PNADDRESSBOOK__,'user','input2numeric',array('inum'=>$$the_name));
			}
			if ((empty($$the_name)) || ($$the_name == '')) {
				$$the_name = 'NULL';
			}
			$sql .= "adr_$the_name = ".pnVarPrepForStore($$the_name).",";
		}
	}

	$sql .=	"$address_column[note]='".pnVarPrepForStore($note)."',
			$address_column[private]=".pnVarPrepForStore($private).",
	        $address_column[date]=".pnVarPrepForStore($date)."
			WHERE $address_column[nr]=$id";

	$result = $dbconn->Execute($sql);
    if($dbconn->ErrorNo() != 0) { return false; }

	$result->Close();
	return true;
}

function pnAddressBook_userapi_deleterecord($args) {
	extract($args);

	list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
    $address_column = &$pntable['pnaddressbook_address_column'];
	$sql = "DELETE FROM $address_table WHERE $address_column[nr]=$id";

	$result = $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return false; }

	$result->Close();
	return true;
}

function pnAddressBook_userapi_SecurityCheck($value) {
	//SecurityCheck
	$value = preg_replace("'<img(.*)src=(.*)(;|\()(.*?)>'i","*******",$value);
	$value = preg_replace("#(<[a-zA-Z])(.*)(;|\()(.*?)(/[a-zA-Z])(.*?)>#si","*******",$value);
	$value = eregi_replace("javascript:","*******",$value);

	return $value;
}

function pnAddressBook_userapi_FormSelect($args) {
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
    //$output .= ' tabindex="'.$this->tabindex.'"';
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

function pnAddressBook_userapi_is_url ($args) {
	extract($args);
	$UrlElements = parse_url($url);
	if( (empty($UrlElements)) or (!$UrlElements) ) {
		return false;
	}
	$UrlElements = parse_url($url);
	$HostName = $UrlElements[host];
	if ((!isset($UrlElements['host'])) || (empty($HostName))) {
	//if(empty($HostName)) {
		return false;
	}
	return true;
}

function pnAddressBook_userapi_is_email ($args) {
	extract($args);

	if (empty($email)) {
		return false;
	}
	if (!ereg("@",$email)) {
		return false;
	}
	list($User,$Host) = split("@",$email);
	if(!ereg("\.",$Host)) {
		return false;
	}
	list($dom,$count) = split("\.",$Host);
	if ( (empty($User)) or (empty($dom)) or (empty($count)) ) {
		return false;
	}
	if ((ereg("[ 	]",$User)) || (ereg("[ 	]",$Host))) {
		return false;
	}
	$EmailRegExp="^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$";
	if (!eregi($EmailRegExp,$email)) {
		return false;
	}
	return true;
}
function pnAddressBook_userapi_checkForIE() {
	require_once('modules/pnAddressBook/phpsniff/phpSniff.class.php');

	// initialize some vars
	if(!isset($UA)) $UA = '';
	if(!isset($cc)) $cc = '';
	if(!isset($dl)) $dl = '';
	if(!isset($am)) $am = '';
	$sniffer_settings = array('check_cookies'=>$cc,
                          'default_language'=>$dl,
                          'allow_masquerading'=>$am);
	$client = new phpSniff($UA,$sniffer_settings);
	if ($client->property('browser') != 'ie') {
		return false;
	}
	if ($client->property('maj_ver') < 5) {
		return false;
	}
	return true;
}
function pnAddressBook_userapi_td2stamp($args){
	extract($args);
	if( (!isset($idate)) || (empty($idate)) || ($idate=='')) {
		return 'NULL';
	}
	$dateformat = pnModGetVar(__PNADDRESSBOOK__,'dateformat');
	$token = "-./ ";
	$p1 = strtok($idate,$token);
	$p2 = strtok($token);
	$p3 = strtok($token);
	$p4 = strtok($token);
	$date = ""; $y = ""; $m = ""; $d = "";
	if ($dateformat == 1) {
		$y = $p3;
		$m = $p2;
		$d = $p1;
	}
	else {
		$y = $p3;
		$m = $p1;
		$d = $p2;
	}
	if (($y != "") && ($y <= 99)) {
		if ($y >= 70) $y = $y + 1900;
		if ($y < 70) $y = $y + 2000;
	}
	/*
	$timestamp = mktime(1,0,0,$m,$d,$y);
	if($timestamp == -1){
		return 'NULL';
	}
	else {
		return $timestamp;
	}
	*/
	$returnValue = $y.$m.$d;
	return $returnValue;
}
function pnAddressBook_userapi_stamp2date($args){
	extract($args);
	if( (!isset($idate)) || (empty($idate)) || ($idate=='')) {
		return '';
	}
	$token = "-";
	$p1 = strtok($idate,$token);
	$p2 = strtok($token);
	$p3 = strtok($token);
	$p4 = strtok($token);
	$returnValue = '';
	$dateformat = pnModGetVar(__PNADDRESSBOOK__,'dateformat');
	if ($dateformat == 1) {
		//$returnValue = date("d.m.Y",$idate);
		$returnValue = $p3.'.'.$p2.'.'.$p1;
	}
	else {
		//$returnValue = date("m.d.Y",$idate);
		$returnValue = $p2.'.'.$p3.'.'.$p1;
	}
	return $returnValue;
}
function pnAddressBook_userapi_input2numeric($args){
	extract($args);
	if( (!isset($inum)) || (empty($inum)) || ($inum=='')) {
		return 'NULL';
	}
	$check_format = ereg_replace(",",".",$inum);
	$split_format = explode(".",$check_format);
	$count_array = count($split_format);

	// example 1000
	if($count_array == 1){
		if(ereg("^[+|-]{0,1}[0-9]{1,}$",$check_format)){
			$num="$split_format[0]";
		}
	}

	// example 1000,20 or 1.000
	if($count_array == 2){
		if(ereg("^[+|-]{0,1}[0-9]{1,}.[0-9]{0,2}$",$check_format)){
			$num="$split_format[0].$split_format[1]";
		}
	}

	// example 1,000.20 or 1.000,20
	if($count_array == 3){
		if(ereg("^[+|-]{0,1}[0-9]{1,}.[0-9]{3}.[0-9]{0,2}$",$check_format)){
			$num="$split_format[0]$split_format[1].$split_format[2]";
		}
	}
	return $num; // Zurueckgeben des formatierten Wertes
}
function pnAddressBook_userapi_num4display($args){
	extract($args);
	if( (!isset($inum)) || (empty($inum)) || ($inum=='')) {
		return '';
	}
	$returnValue = '';
	$dateformat = pnModGetVar(__PNADDRESSBOOK__,'numformat');
	if ($dateformat == '9.999,99') {
		$returnValue = number_format($inum,2,',','.');
	}
	else {
		$returnValue = number_format($inum,2,'.',',');
	}
	return $returnValue;
}
function pnAddressBook_userapi_getListHeader($args){
	extract($args);
	if (!isset($sort)) {
		return false;
	}
	if ($sort == 1) {
		$sortCols = explode(',',pnModGetVar(__PNADDRESSBOOK__, 'sortorder_1'));
	}
	else {
		$sortCols = explode(',',pnModGetVar(__PNADDRESSBOOK__, 'sortorder_2'));
	}
	for ($i=0;$i<2;$i++) {
		switch ($sortCols[$i]) {
			case 'adr_sortname':
				$returnArray[$i] = strtoupper(_pnAB_TABLE_NAME);
				break;
			case 'adr_title':
				$returnArray[$i] = strtoupper(_pnAB_TITLE);
				break;
			case 'adr_sortcompany':
				$returnArray[$i] = strtoupper(_pnAB_COMPANY);
				break;
			case 'adr_zip':
				$returnArray[$i] = strtoupper(_pnAB_ZIP);
				break;
			case 'adr_city':
				$returnArray[$i] = strtoupper(_pnAB_CITY);
				break;
			case 'adr_state':
				$returnArray[$i] = strtoupper(_pnAB_STATE);
				break;
			case 'adr_country':
				$returnArray[$i] = strtoupper(_pnAB_COUNTRY);
				break;
		}
		$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
		if ((!empty($custom_tab)) && ($custom_tab != '')) {
			$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>$id));
			foreach($cus_fields as $cus) {
				$the_name = 'adr_custom_'.$cus['nr'];
				if ($sortCols[$i] == $the_name) {
					$returnArray[$i] = strtoupper($cus['name']);
				}
			}
		}
	}
	return $returnArray;
}
function pnAddressBook_userapi_getSortBy($args){
	extract($args);
	if (!isset($sort)) {
		return false;
	}
	if ($sort == 1) {
		$sortCols = explode(',',pnModGetVar(__PNADDRESSBOOK__, 'sortorder_1'));
	}
	else {
		$sortCols = explode(',',pnModGetVar(__PNADDRESSBOOK__, 'sortorder_2'));
	}
	for ($i=0;$i<2;$i++) {
		switch ($sortCols[$i]) {
			case 'adr_sortname':
				$returnArray[$i] = _pnAB_NAME;
				break;
			case 'adr_title':
				$returnArray[$i] = _pnAB_TITLE;
				break;
			case 'adr_sortcompany':
				$returnArray[$i] = _pnAB_COMPANY;
				break;
			case 'adr_zip':
				$returnArray[$i] = _pnAB_ZIP;
				break;
			case 'adr_city':
				$returnArray[$i] = _pnAB_CITY;
				break;
			case 'adr_state':
				$returnArray[$i] = _pnAB_STATE;
				break;
			case 'adr_country':
				$returnArray[$i] = _pnAB_COUNTRY;
				break;
		}
		$custom_tab = pnModGetVar(__PNADDRESSBOOK__,'custom_tab');
		if ((!empty($custom_tab)) && ($custom_tab != '')) {
			$cus_fields = pnModAPIFunc(__PNADDRESSBOOK__,'user','customFieldInformation',array('id'=>$id));
			foreach($cus_fields as $cus) {
				$the_name = 'adr_custom_'.$cus['nr'];
				if ($sortCols[$i] == $the_name) {
					$returnArray[$i] = $cus['name'];
				}
			}
		}
	}
	$returnString = $returnArray[0].', '.$returnArray[1];
	return $returnString;
}
?>