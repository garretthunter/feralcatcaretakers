<?php
// $Id: pntables.php,v 1.2 2003/04/19 23:36:28 garrett Exp $
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
// Purpose of file:  Table information for pnAddressBook module
// ----------------------------------------------------------------------

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */
function pnAddressBook_pntables()
{
    // Initialise table array
    $pntable = array();
	$prefix = pnConfigGetVar('prefix');

    $pnaddressbook = $prefix . '_pnaddressbook_address';
    $pntable['pnaddressbook_address'] = $pnaddressbook;
    $pntable['pnaddressbook_address_column'] = array(
        'nr'			=> 'adr_id',
        'cat_id'         => 'adr_catid',
		'prefix'		=> 'adr_prefix',
        'lname'			=> 'adr_name',
        'fname'			=> 'adr_fname',
		'sortname'		=> 'adr_sortname',
        'title'			=> 'adr_title',
		'company'		=> 'adr_company',
		'sortcompany'	=> 'adr_sortcompany',
		'img'			=> 'adr_img',
        'zip'			=> 'adr_zip',
        'city'			=> 'adr_city',
        'address1'		=> 'adr_address1',
		'address2'		=> 'adr_address2',
		'state'			=> 'adr_state',
		'country'		=> 'adr_country',
        'contact_1'		=> 'adr_contact_1',
		'contact_2'		=> 'adr_contact_2',
		'contact_3'		=> 'adr_contact_3',
		'contact_4'		=> 'adr_contact_4',
		'contact_5'		=> 'adr_contact_5',
		'c_label_1'		=> 'adr_c_label_1',
		'c_label_2'		=> 'adr_c_label_2',
		'c_label_3'		=> 'adr_c_label_3',
		'c_label_4'		=> 'adr_c_label_4',
		'c_label_5'		=> 'adr_c_label_5',
		'c_main'		=> 'adr_c_main',
		'custom_1'		=> 'adr_custom_1',
		'custom_2'		=> 'adr_custom_2',
		'custom_3'		=> 'adr_custom_3',
		'custom_4'		=> 'adr_custom_4',
        'note'			=> 'adr_note',
		'user_id'		=> 'adr_user',
		'private'		=> 'adr_private',
		'date'			=> 'adr_date'
        );

	$pnaddressbook_labels = $prefix . '_pnaddressbook_labels';
    $pntable['pnaddressbook_labels'] = $pnaddressbook_labels;
    $pntable['pnaddressbook_labels_column'] = array(
        'nr'			=> 'lab_id',
        'name'			=> 'lab_name'
        );

	$pnaddressbook_categories = $prefix . '_pnaddressbook_categories';
    $pntable['pnaddressbook_categories'] = $pnaddressbook_categories;
    $pntable['pnaddressbook_categories_column'] = array(
        'nr'			=> 'cat_id',
        'name'			=> 'cat_name'
        );

	$pnaddressbook_customfields = $prefix . '_pnaddressbook_customfields';
    $pntable['pnaddressbook_customfields'] = $pnaddressbook_customfields;
    $pntable['pnaddressbook_customfields_column'] = array(
        'nr'			=> 'cus_id',
        'name'			=> 'cus_name',
		'type'			=> 'cus_type',
		'position'		=> 'cus_pos'
        );

	$pnaddressbook_prefixes = $prefix . '_pnaddressbook_prefixes';
    $pntable['pnaddressbook_prefixes'] = $pnaddressbook_prefixes;
    $pntable['pnaddressbook_prefixes_column'] = array(
        'nr'			=> 'pre_id',
        'name'			=> 'pre_name'
        );

    // Return the table information
    return $pntable;
}

?>