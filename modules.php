<?php // File: $Id: modules.php,v 1.6 2003/01/07 12:21:05 tanis Exp $ $Name:  $
// ----------------------------------------------------------------------
// Original Author of file: Francisco Burzi
// Purpose of file: Load the module in a safe and friendly enviornment
// ----------------------------------------------------------------------
/*	Allows Postnuke to work with register_globals set to off 
 *	Patch for php 4.2.x or greater
 */

if (phpversion() >= "4.2.0") {
	if ( ini_get('register_globals') != 1 ) {
		$supers = array('_REQUEST',
                                '_ENV',
                                '_SERVER',
                                '_POST',
                                '_GET',
                                '_COOKIE',
                                '_SESSION',
                                '_FILES',
                                '_GLOBALS' );

		foreach( $supers as $__s) {
			if ( (isset($$__s) == true) && (is_array( $$__s ) == true) ) extract( $$__s, EXTR_OVERWRITE );
		}
		unset($supers);
	}
} else {
	if ( ini_get('register_globals') != 1 ) {

		$supers = array('HTTP_POST_VARS',
                                'HTTP_GET_VARS',
                                'HTTP_COOKIE_VARS',
                                'GLOBALS',
                                'HTTP_SESSION_VARS',
                                'HTTP_SERVER_VARS',
                                'HTTP_ENV_VARS'
                                 );

		foreach( $supers as $__s) {
			if ( (isset($$__s) == true) && (is_array( $$__s ) == true) ) extract( $$__s, EXTR_OVERWRITE );
		}
		unset($supers);
	}
}

switch ($op) {
    case 'modload':
        define("LOADED_AS_MODULE","1");
        include_once 'includes/pnAPI.php';
	if(!function_exists('pnsessionsetup')){
            pnInit();
	}
        include_once 'includes/legacy.php';
// eugenio themeover 20020413
//        pnThemeLoad();


// Module not found error msg 08/08/00 Chris Bowler www.aquanuke.com 

if (file_exists('modules/' . pnVarPrepForOS($name) . '/' . 
pnVarPrepForOS($file) . '.php')) {
        include 'modules/' . pnVarPrepForOS($name) . '/' . 
pnVarPrepForOS($file) . '.php';
	} else {
 		pnRedirect('error.php');
 	}

        if (function_exists('session_write_close')) {
            session_write_close();
        } else {
            // Hack for old versions of PHP with bad session save
            $sessvars = '';
            foreach ($GLOBALS as $k => $v) {
                if ((preg_match('/^PNSV/', $k)) &&
                    (isset($v))) {
                    $sessvars .= "$k|" . serialize($v);
                }
            }
            pnSessionWrite(session_id(), $sessvars);
        }

        break;
    default:
        die ("Sorry, you can't access this file directly...");
        break;
}
?>