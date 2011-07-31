<?php
@define('__PNADDRESSBOOK__','pnAddressBook');
// $Id: pninit.php,v 1.2 2003/04/19 23:36:28 garrett Exp $
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
// Purpose of file:  Initialisation functions for pnAddressBook
// ----------------------------------------------------------------------

/**
 * initialise the pnAddressBook module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function pnAddressBook_init()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

	// create main address table
    $address_table = $pntable['pnaddressbook_address'];
    $address_column = &$pntable['pnaddressbook_address_column'];
    $sql = "CREATE TABLE $address_table (
            $address_column[nr] int(11) unsigned NOT NULL auto_increment,
			$address_column[cat_id] int(11),
			$address_column[prefix] int(11),
            $address_column[lname] varchar(100) default NULL,
            $address_column[fname] varchar(60) default NULL,
			$address_column[sortname] varchar(180) default NULL,
			$address_column[title] varchar(100) default NULL,
			$address_column[company] varchar(100) default NULL,
			$address_column[sortcompany] varchar(100) default NULL,
			$address_column[img] varchar(100) default NULL,
			$address_column[zip] varchar(30) default NULL,
			$address_column[city] varchar(100) default NULL,
			$address_column[address1] varchar(100) default NULL,
			$address_column[address2] varchar(100) default NULL,
			$address_column[state] varchar(60) default NULL,
			$address_column[country] varchar(60) default NULL,
			$address_column[contact_1] varchar(80) default NULL,
			$address_column[contact_2] varchar(80) default NULL,
			$address_column[contact_3] varchar(80) default NULL,
			$address_column[contact_4] varchar(80) default NULL,
			$address_column[contact_5] varchar(80) default NULL,
			$address_column[c_label_1] tinyint(4) default NULL,
			$address_column[c_label_2] tinyint(4) default NULL,
			$address_column[c_label_3] tinyint(4) default NULL,
			$address_column[c_label_4] tinyint(4) default NULL,
			$address_column[c_label_5] tinyint(4) default NULL,
			$address_column[c_main] tinyint(4) default NULL,
			$address_column[custom_1] varchar(60) default NULL,
			$address_column[custom_2] varchar(60) default NULL,
			$address_column[custom_3] varchar(60) default NULL,
			$address_column[custom_4] varchar(60) default NULL,
			$address_column[note] blob,
			$address_column[user_id] int(11) unsigned default NULL,
			$address_column[private] tinyint(4) default '0',
			$address_column[date] int(11) default '0' NOT NULL,
            PRIMARY KEY(adr_id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	// create label table
    $lab_table = $pntable['pnaddressbook_labels'];
    $lab_column = &$pntable['pnaddressbook_labels_column'];
    $sql = "CREATE TABLE $lab_table (
            $lab_column[nr] int(11) unsigned NOT NULL auto_increment,
            $lab_column[name] varchar(30) default NULL,
            PRIMARY KEY(lab_id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }
	// insert default values
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_WORK)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_FAX)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_MOBILE)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_HOME)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_EMAIL)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_URL)."')");
	$sql = $dbconn->Execute("INSERT INTO $lab_table ($lab_column[nr],$lab_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_OTHER)."')");

	// create category table
    $cat_table = $pntable['pnaddressbook_categories'];
    $cat_column = &$pntable['pnaddressbook_categories_column'];
    $sql = "CREATE TABLE $cat_table (
            $cat_column[nr] int(11) unsigned NOT NULL auto_increment,
            $cat_column[name] varchar(30) default NULL,
            PRIMARY KEY(cat_id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }
	// insert default values
	$sql = $dbconn->Execute("INSERT INTO $cat_table ($cat_column[nr],$cat_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_BUSINESS)."')");
	$sql = $dbconn->Execute("INSERT INTO $cat_table ($cat_column[nr],$cat_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_PERSONAL)."')");
	$sql = $dbconn->Execute("INSERT INTO $cat_table ($cat_column[nr],$cat_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_QUICKLIST)."')");

	// create custom field table
    $cus_table = $pntable['pnaddressbook_customfields'];
    $cus_column = &$pntable['pnaddressbook_customfields_column'];
    $sql = "CREATE TABLE $cus_table (
            $cus_column[nr] int(11) unsigned NOT NULL,
            $cus_column[name] varchar(30) default NULL,
			$cus_column[type] varchar(30) default NULL,
			$cus_column[position] int(11) unsigned NOT NULL,
            PRIMARY KEY(cus_id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	$tempcus_1 = pnVarPrepForStore(_pnAB_CUSTOM_1);
	$tempcus_2 = pnVarPrepForStore(_pnAB_CUSTOM_2);
	$tempcus_3 = pnVarPrepForStore(_pnAB_CUSTOM_3);
	$tempcus_4 = pnVarPrepForStore(_pnAB_CUSTOM_4);

	// insert default values
	$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('1','$tempcus_1','varchar(60)','1')");
	$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('2','$tempcus_2','varchar(60)','2')");
	$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('3','$tempcus_3','varchar(60)','3')");
	$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('4','$tempcus_4','varchar(60)','4')");

	// create prefix table
    $pre_table = $pntable['pnaddressbook_prefixes'];
    $pre_column = &$pntable['pnaddressbook_prefixes_column'];
    $sql = "CREATE TABLE $pre_table (
            $pre_column[nr] int(11) unsigned NOT NULL auto_increment,
            $pre_column[name] varchar(30) default NULL,
            PRIMARY KEY(pre_id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	// insert default values
	$sql = $dbconn->Execute("INSERT INTO $pre_table ($pre_column[nr],$pre_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_MR)."')");
	$sql = $dbconn->Execute("INSERT INTO $pre_table ($pre_column[nr],$pre_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_MRS)."')");

	// Set up an initial value for a module variable.
	pnModSetVar(__PNADDRESSBOOK__, 'abtitle', 'PostNuke Address Book');
    pnModSetVar(__PNADDRESSBOOK__, 'guestmode', 4);
	pnModSetVar(__PNADDRESSBOOK__, 'usermode', 7);
	pnModSetVar(__PNADDRESSBOOK__, 'itemsperpage', 30);
	pnModSetVar(__PNADDRESSBOOK__, 'globalprotect', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'menu_off', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'custom_tab', '');
	pnModSetVar(__PNADDRESSBOOK__, 'zipbeforecity', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'hidecopyright', 0);
	// since version 2.0
	pnModSetVar(__PNADDRESSBOOK__, 'use_prefix', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'use_img', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'textareawidth', 60);
	pnModSetVar(__PNADDRESSBOOK__, 'dateformat', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'numformat', '9,999.99');
	pnModSetVar(__PNADDRESSBOOK__, 'sortorder_1', 'adr_sortname,adr_zip');
	pnModSetVar(__PNADDRESSBOOK__, 'sortorder_2', 'adr_zip,adr_sortname');
	pnModSetVar(__PNADDRESSBOOK__, 'menu_semi', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'name_order', 0);
	pnModSetVar(__PNADDRESSBOOK__, 'special_chars_1', 'ִײÜהצüß');
	pnModSetVar(__PNADDRESSBOOK__, 'special_chars_2', 'AOUaous');

    // Initialisation successful
    return true;
}

/**
 * upgrade the module from an old version
 * This function can be called multiple times
 */
function pnAddressBook_upgrade($oldversion) {

	@include("modules/". __PNADDRESSBOOK__ . "/pnlang/" . pnVarPrepForOS(pnUserGetLang()) . "/init.php");
	switch($oldversion) {
        case '1.0':
			list($dbconn) = pnDBGetConn();
		    $pntable = pnDBGetTables();

			// changes to main address table
		    $address_table = $pntable['pnaddressbook_address'];
		    $address_column = &$pntable['pnaddressbook_address_column'];
			$sql = "ALTER TABLE $address_table ADD $address_column[prefix] int(11) AFTER $address_column[cat_id]";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}
			// add image column
			$sql = "ALTER TABLE $address_table ADD $address_column[img] varchar(100) default NULL AFTER $address_column[company]";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}
			// add sort order columns
			$sql = "ALTER TABLE $address_table ADD $address_column[sortname] varchar(180) default NULL AFTER $address_column[fname]";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}
			$sql = "ALTER TABLE $address_table ADD $address_column[sortcompany] varchar(100) default NULL AFTER $address_column[company]";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}

			// update sort order column
			$sql = "UPDATE $address_table SET $address_column[sortname] = REPLACE ( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE(CONCAT($address_column[lname],', ',$address_column[fname]) , 'ִ', 'A'), 'ײ', 'O'), 'Ü', 'U'), 'ה', 'a'), 'צ', 'o'), 'ü','u'), 'ß', 's')";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}
			$sql = "UPDATE $address_table SET $address_column[sortcompany] = REPLACE ( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE($address_column[company] , 'ִ', 'A'), 'ײ', 'O'), 'Ü', 'U'), 'ה', 'a'), 'צ', 'o'), 'ü','u'), 'ß', 's')";
			$dbconn->Execute($sql);
			if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        		return false;
			}

			// create custom field table
		    $cus_table = $pntable['pnaddressbook_customfields'];
		    $cus_column = &$pntable['pnaddressbook_customfields_column'];
		    $sql = "CREATE TABLE $cus_table (
		            $cus_column[nr] int(11) unsigned NOT NULL,
		            $cus_column[name] varchar(30) default NULL,
					$cus_column[type] varchar(30) default NULL,
					$cus_column[position] int(11) unsigned NOT NULL,
		            PRIMARY KEY(cus_id))";
		    $dbconn->Execute($sql);

		    if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
		        return false;
		    }

			$tempcus_1 = pnModGetVar(__PNADDRESSBOOK__, 'custom_1');
			$tempcus_2 = pnModGetVar(__PNADDRESSBOOK__, 'custom_2');
			$tempcus_3 = pnModGetVar(__PNADDRESSBOOK__, 'custom_3');
			$tempcus_4 = pnModGetVar(__PNADDRESSBOOK__, 'custom_4');

			// insert default values
			$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('1','$tempcus_1','VARCHAR(60)','1')");
			$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('2','$tempcus_2','VARCHAR(60)','2')");
			$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('3','$tempcus_3','VARCHAR(60)','3')");
			$sql = $dbconn->Execute("INSERT INTO $cus_table ($cus_column[nr],$cus_column[name],$cus_column[type],$cus_column[position]) VALUES ('4','$tempcus_4','VARCHAR(60)','4')");

			// create prefix table
		    $pre_table = $pntable['pnaddressbook_prefixes'];
		    $pre_column = &$pntable['pnaddressbook_prefixes_column'];
		    $sql = "CREATE TABLE $pre_table (
		            $pre_column[nr] int(11) unsigned NOT NULL auto_increment,
		            $pre_column[name] varchar(30) default NULL,
		            PRIMARY KEY(pre_id))";
		    $dbconn->Execute($sql);

		    if ($dbconn->ErrorNo() != 0) {
		        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
		        return false;
		    }

			// insert default values
			$sql = $dbconn->Execute("INSERT INTO $pre_table ($pre_column[nr],$pre_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_MR)."')");
			$sql = $dbconn->Execute("INSERT INTO $pre_table ($pre_column[nr],$pre_column[name]) VALUES ('','".pnVarPrepForStore(_pnAB_MRS)."')");

			// Set up an initial value for a module variable.
			pnModSetVar(__PNADDRESSBOOK__, 'use_prefix', 0);
			pnModSetVar(__PNADDRESSBOOK__, 'use_img', 0);
			pnModSetVar(__PNADDRESSBOOK__, 'textareawidth', 60);
			pnModSetVar(__PNADDRESSBOOK__, 'dateformat', 0);
			pnModSetVar(__PNADDRESSBOOK__, 'numformat', '9,999.99');
			pnModSetVar(__PNADDRESSBOOK__, 'sortorder_1', 'adr_sortname,adr_sortcompany');
			pnModSetVar(__PNADDRESSBOOK__, 'sortorder_2', 'adr_sortcompany,adr_sortname');
			pnModSetVar(__PNADDRESSBOOK__, 'menu_semi', 0);
			pnModSetVar(__PNADDRESSBOOK__, 'name_order', 0);
			pnModSetVar(__PNADDRESSBOOK__, 'special_chars_1', 'ִײÜהצüß');
			pnModSetVar(__PNADDRESSBOOK__, 'special_chars_2', 'AOUaous');

			// Delete unneeded module variables
			pnModDelVar(__PNADDRESSBOOK__, 'custom_1');
			pnModDelVar(__PNADDRESSBOOK__, 'custom_2');
			pnModDelVar(__PNADDRESSBOOK__, 'custom_3');
			pnModDelVar(__PNADDRESSBOOK__, 'custom_4');

			break;
	}

	return true;
}

/**
 * delete the pnAddressBook module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function pnAddressBook_delete() {

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
	$address_table = $pntable['pnaddressbook_address'];
	$lab_table = $pntable['pnaddressbook_labels'];
	$cat_table = $pntable['pnaddressbook_categories'];
	$cus_table = $pntable['pnaddressbook_customfields'];
	$pre_table = $pntable['pnaddressbook_prefixes'];

	$sql = "DROP TABLE $address_table";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	$sql = "DROP TABLE $lab_table";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	$sql = "DROP TABLE $cat_table";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	$sql = "DROP TABLE $cus_table";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	$sql = "DROP TABLE $pre_table";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // Delete any module variables
    pnModDelVar(__PNADDRESSBOOK__, 'guestmode');
	pnModDelVar(__PNADDRESSBOOK__, 'usermode');
	pnModDelVar(__PNADDRESSBOOK__, 'itemsperpage');
	pnModDelVar(__PNADDRESSBOOK__, 'globalprotect');
	pnModDelVar(__PNADDRESSBOOK__, 'menu_off');
	pnModDelVar(__PNADDRESSBOOK__, 'custom_tab');
	pnModDelVar(__PNADDRESSBOOK__, 'zipbeforecity');
	pnModDelVar(__PNADDRESSBOOK__, 'abtitle');
	pnModDelVar(__PNADDRESSBOOK__, 'hidecopyright');
	pnModDelVar(__PNADDRESSBOOK__, 'use_img');
	pnModDelVar(__PNADDRESSBOOK__, 'use_prefix');
	pnModDelVar(__PNADDRESSBOOK__, 'textareawidth');
	pnModDelVar(__PNADDRESSBOOK__, 'dateformat');
	pnModDelVar(__PNADDRESSBOOK__, 'numformat');
	pnModDelVar(__PNADDRESSBOOK__, 'sortorder_1');
	pnModDelVar(__PNADDRESSBOOK__, 'sortorder_2');
	pnModDelVar(__PNADDRESSBOOK__, 'menu_semi');
	pnModDelVar(__PNADDRESSBOOK__, 'name_order');
	pnModDelVar(__PNADDRESSBOOK__, 'special_chars_1');
	pnModDelVar(__PNADDRESSBOOK__, 'special_chars_2');

    // Deletion successful
    return true;
}
?>