<?php // $Id:
// ----------------------------------------------------------------------
// Original Author of file: Thomas Smiatek
// Purpose of file: english Translation Files
// ----------------------------------------------------------------------
//

if (!defined('_PNADDRESSBOOK_NOAUTH')) {
        define('_PNADDRESSBOOK_NOAUTH',	'Not authorised to access the Address Book module.');
}
define('_PNADDRESSBOOK', 			'PostNuke Address Book');
define('_pnAB_MENU_AZ_1',			'[X] Show A - Z ');
define('_pnAB_MENU_AZ_2',			'[ ] Show A - Z ');
define('_pnAB_MENU_ALL_1',			' Show all records [ ]');
define('_pnAB_MENU_ALL_2',			' Show all records [X]');
define('_pnAB_MENU_ADD', 			'[Add new address...]');
//START - gehunter
define('_pnAB_MENU_EXPORT', 		'[Export address list...]');
//END - gehunter
define('_pnAB_MENU_SEARCH', 		'Search');
define('_pnAB_VIEWPRIVATE',			'Show');
define('_pnAB_VIEWPRIVATE_1',		'Show private contacts only [ ]');
define('_pnAB_VIEWPRIVATE_2',		'Show private contacts only [X]');
define('_pnAB_CATEGORY', 			'Category');
define('_pnAB_ALLCATEGORIES', 		'All Categories');
define('_pnAB_SORTBY',				'Sort&nbsp;by');
define('_pnAB_SORTORDER_1',			'Last Name, First Name');
define("_pnAB_SORTORDER_2",			"Company, Last Name");
define("_pnAB_TABLE_NAME",			"NAME");
define("_pnAB_TABLE_COMPANY",		"COMPANY");
define("_pnAB_TABLE_CONTACT",		"CONTACT");
define("_pnAB_TABLE_ACTION",		"ACTION");
define("_pnAB_TABLE_EDIT",			"Edit");
define("_pnAB_TABLE_DELETE",		"Delete");
define("_pnAB_TABLE_SHOWDETAIL",	"Details");
define("_pnAB_NORECORDS",			"There are no items to show in this view");
define("_pnAB_CONTACTINFO",			"General Information");
define('_pnAB_CONTACT',				'Contact');
define('_pnAB_NOTETAB',				'Note');
define("_pnAB_GOBACK",				"Back to list");
define("_pnAB_COPY",				"Copy to clipboard");
define("_pnAB_CONFIRMDELETE",		"Delete this Address Book item?");
define("_pnAB_CANCEL",				"Cancel");
define("_pnAB_DELETE",				"Delete");
define("_pnAB_DELETENOSUCCESS",		"Deletion of this record failed. Please contact your administrator!");
define("_pnAB_UPDATE_RECORD",		"Update");
define("_pnAB_INSERT_RECORD",		" Save ");
//gehINSERT
define('_pnAB_DONOR',				'Donor');
define('_pnAB_CARETAKER',			'Caretaker');
define('_pnAB_MEMBER',				'Member');
define('_pnAB_CMS_FLAGS',			'Additional');

//gehEND
define('_pnAB_PRIVATE',				'Private');
define('_pnAB_ADDRESS',				'Address');
define('_pnAB_LASTNAME',			'Last&nbsp;name');
define('_pnAB_FIRSTNAME',			'First&nbsp;name');
define('_pnAB_COMPANY',				'Company');
define('_pnAB_UNFILED',				'Unfiled');
define('_pnAB_CITY',				'City');
define('_pnAB_STATE',				'State');
define('_pnAB_ZIP',					'Zip');
define('_pnAB_COUNTRY',				'Country');
define('_pnAB_INSERT_pnAB_SUCCESS',	'Address Book Entry saved!');
define('_pnAB_INSERT_ERROR',		'An Error ocurred. The Address Book Entry could not be saved!');
define('_pnAB_INSERT_CHKMSG',		'An Address Book Entry must contain data in at least one field of the Name tab!');
define('_pnAB_UPDATE_ERROR',		'An Error ocurred. The Address Book Entry could not be updated!');
define('_pnAB_UPDATE_CHKMSG',		'An Address Book Entry must contain data in at least one field of the Name tab!');
define('_pnAB_LASTCHANGED',			'Last changed ');
define('_pnAB_TITLE',				'Title');
define('_pnAB_ALLCOMPANIES',		'Enter new company name or select a company...');
define("_pnAB_REGONLY", 			"This website require it's users to be registered to use the address book.<br>
									Register for free <a href=\"user.php\">here</a>, or <a href=\"user.php\">log in</a> 
									if you are already registered.");
// Version 2.0
define('_pnAB_PREFIX',				'Prefix/Title');
define('_pnAB_NOPREFIX',			'No Prefix/Title');
define('_pnAB_CHKMSG_1',			'There is a false numeric value in the '.pnModGetVar(__PNADDRESSBOOK__,'custom_tab').' tab.');
define('_pnAB_CHKMSG_2',			'In the '.pnModGetVar(__PNADDRESSBOOK__,'custom_tab').' tab there are characters in a digit-only field.');
define('_pnAB_CHKMSG_3',			'In the '.pnModGetVar(__PNADDRESSBOOK__,'custom_tab').' tab there is a false date format.');
define('_pnAB_IMAGE',				'Image');
define('_pnAB_NOIMAGE',				'No Image');
define('_pnAB_NAME',				'Name');
define('_pnAB_DATEFORMAT_1',		'MM.DD.YYYY');
define('_pnAB_DATEFORMAT_2',		'DD.MM.YYYY');
?>