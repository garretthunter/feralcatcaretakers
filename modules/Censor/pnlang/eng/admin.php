<?php // $Id: admin.php,v 1.1 2002/12/02 00:22:00 neo Exp $
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Language defines for pnadmin.php
// ----------------------------------------------------------------------
//
define('_EDITCENSOR', 'Configure Censorship Options');
define('_LOADFAILED', 'Load of module failed');
define('_CENSORUPDATE', 'Update');
define('_CENSORUPDATED', 'Censorship Options Updated');
define('_CENSORMODE', 'Censor Mode On');
define('_CENSORLIST', 'List of Censored Words');
define('_CENSORREPLACE', 'Replace Censored Words with');
define('_CENSORMODEFAIL', 'Update of Censor Mode Failed');
define('_CENSORLISTFAIL', 'Update of Censor List Failed');

if (!defined('_CONFIRM')) {
	define('_CONFIRM', 'Confirm');
}
if (!defined('_CENSORNOAUTH')) {
	define('_CENSORNOAUTH','Not authorised to access the Censor Module');
}
?>