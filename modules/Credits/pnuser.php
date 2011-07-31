<?php
// File: $Id: pnuser.php,v 1.6 2003/01/08 07:23:10 larsneo Exp $ $Name:  $
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
// Purpose of file: Credits administration
// ----------------------------------------------------------------------

/*
 * credits_user_list - list credits and current settings
 * Takes no parameters
 */
function credits_user_main()
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Credits::', '::', ACCESS_READ)) {
        $output->Text(_CREDITSNOAUTH);
        return $output->GetOutput();
    }
	
	$oimg = @getimagesize('modules/Credits/pnimages/official.gif');
	$cimg = @getimagesize('modules/Credits/pnimages/credits.gif');
	$himg = @getimagesize('modules/Credits/pnimages/help.gif');
	$limg = @getimagesize('modules/Credits/pnimages/license.gif');

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableStart(_CREDITSTITLE);
	$output->TableRowStart();
	$output->TableColStart();
	$output->Text(_CREDITSPOSTNUKE);
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableEnd();
	$output->TableStart();
	$output->TableRowStart();
	$output->TableColStart();
	$output->FormStart(pnModURL('Credits', 'user', 'display'));
	$output->FormHidden('authid', pnSecGenAuthKey());
	// Credits
    $row = array();
	$row[] = $output->Text('<input type="image" src="modules/Credits/pnimages/credits.gif" border="0" name="submit" alt=\""._CREDITSCREDITS."\" $cimg[3]> ');
    $row[] = $output->Text(pnVarPrepForDisplay(_CREDITSREADPNCR));
    $row[] = $output->FormHidden('crdirectory', '');
	$row[] = $output->FormHidden('crpath', 'CREDITS.txt');
	$row[] = $output->FormHidden('heading', 'Credits');
	$row[] = $output->FormHidden('displayname', pnConfigGetVar('Version_ID'));
	$row[] = $output->FormHidden('version', ''._PN_VERSION_NUM.'');
    $output->FormEnd();
	$output->TableColEnd();
	$output->TableColStart();
	$output->FormStart(pnModURL('Credits', 'user', 'display'));
	$output->FormHidden('authid', pnSecGenAuthKey());
	// Help
    $row = array();
	$row[] = $output->Text('<input type="image" src="modules/Credits/pnimages/help.gif" border="0" name="submit" alt=\""._CREDITSHELP."\" $himg[3]> ');
    $row[] = $output->Text(pnVarPrepForDisplay(_CREDITSREADPNHP));
    $row[] = $output->FormHidden('crdirectory', '');
	$row[] = $output->FormHidden('crpath', 'manual.txt');
	$row[] = $output->FormHidden('heading', 'Help');
	$row[] = $output->FormHidden('displayname', pnConfigGetVar('Version_ID'));
	$row[] = $output->FormHidden('version', ''._PN_VERSION_NUM.'');
    $output->FormEnd();
	$output->TableColEnd();
	$output->TableColStart();
	$output->FormStart(pnModURL('Credits', 'user', 'display'));
	$output->FormHidden('authid', pnSecGenAuthKey());
	// License
    $row = array();
	$row[] = $output->Text('<input type="image" src="modules/Credits/pnimages/license.gif" border="0" name="submit" alt=\""._CREDITSLICENSE."\" $limg[3]> ');
    $row[] = $output->Text(pnVarPrepForDisplay(_CREDITSREADPNLIC));
    $row[] = $output->FormHidden('crdirectory', '');
	$row[] = $output->FormHidden('crpath', 'COPYING.txt');
	$row[] = $output->FormHidden('heading', 'License');
	$row[] = $output->FormHidden('displayname', pnConfigGetVar('Version_ID'));
	$row[] = $output->FormHidden('version', ''._PN_VERSION_NUM.'');
    $output->FormEnd();
	$output->TableColEnd();
	$output->TableRowEnd();
	$output->TableEnd();
    $output->SetInputMode(_PNH_PARSEINPUT);



    // Get list of credits
    $mods = pnModGetUserMods();

    if ($mods == false) {
        $output->Text(_CREDITSNOMODS);
        return $output->GetOutput();
    }

    $columnHeaders = array(_HEADCREDITDISPNAME,
						   _HEADCREDITVERSION,
                           _HEADCREDITDESC,
						   _HEADCREDITAUTHOR,
						   _HEADCREDITDOCS);

    $output->TableStart(_CREDITSMODULES, $columnHeaders, 2);

    $authid = pnSecGenAuthKey();

    foreach($mods as $mods) {
		$f = $mods['directory'];
        // Add applicable actions
        //$actions = array();
		if (file_exists("modules/$f/Version.php"))
            {
                $modversion = '';
				$modversion['displayname'] = $mods['displayname'];
                $modversion['filename'] = $f;
                $modversion['name'] = '';
                $modversion['version'] = '';
                $modversion['description'] = '';
                $modversion['credits'] = '';
                $modversion['help'] = '';
                $modversion['changelog'] = '';
                $modversion['license'] = '';
                $modversion['official'] = 0;
                $modversion['author'] = '';
                $modversion['contact'] = '';
				$modversion['admin'] = 0;
                include "modules/$f/Version.php";
			} else if (file_exists("modules/$f/pnversion.php")) {
				$modversion = '';
				$modversion['displayname'] = $mods['displayname'];
                $modversion['filename'] = $f;
                $modversion['name'] = '';
                $modversion['version'] = '';
                $modversion['description'] = '';
                $modversion['credits'] = '';
                $modversion['help'] = '';
                $modversion['changelog'] = '';
                $modversion['license'] = '';
                $modversion['official'] = 0;
                $modversion['author'] = '';
                $modversion['contact'] = '';
				$modversion['admin'] = 0;
                include "modules/$f/pnversion.php";
            } else {
                $modversion = '';
				$modversion['displayname'] = $mods['displayname'];
                $modversion['filename'] = $f;
                $modversion['name'] = $f;
                $modversion['version'] = $mods['version'];
                $modversion['description'] = $mods['description'];
                $modversion['credits'] = '';
                $modversion['help'] = '';
                $modversion['changelog'] = '';
                $modversion['license'] = '';
                $modversion['official'] = 0;
                $modversion['author'] = '';
                $modversion['contact'] = '';
				$modversion['admin'] = 0;
            }
		
		$creditsname = pnVarPrepForDisplay($modversion['displayname']);
		if ($modversion['official']) {
			$creditsname .= "<img src=\"modules/Credits/pnimages/official.gif\" alt=\"\" border=\"0\" $oimg[3]>";
		}
		if ($modversion['contact']) {
			if(ereg('@', $modversion[contact]))
                {
                    $tempcontact = "mailto:$modversion[contact]";
                } else {
                    $tempcontact = "$modversion[contact]";
                }
			$creditauthor = "<a href=\"".$tempcontact."\">".$modversion['author']."</a>";
		} else {
			$creditauthor = pnVarPrepForDisplay($modversion['author']);
		}
		if ($modversion[credits] <> "")
        {
			$creditform = new pnHTML();
			$creditform->SetInputMode(_PNH_VERBATIMINPUT);
			$creditform->FormStart(pnModURL('Credits', 'user', 'display'));
			$creditform->FormHidden('authid', pnSecGenAuthKey());
			$row2 = array();
			$row2[] = $creditform->Text('<input type="image" src="modules/Credits/pnimages/credits.gif" border="0" name="submit" alt=\""._CREDITSCREDITS."\" $cimg[3]> ');
			$row2[] = $creditform->FormHidden('crdirectory', $f);
			$row2[] = $creditform->FormHidden('crpath', $modversion[credits]);
			$row2[] = $creditform->FormHidden('heading', 'Credits');
			$row2[] = $creditform->FormHidden('displayname', $modversion['displayname']);
			$row2[] = $creditform->FormHidden('version', $modversion['version']);
			$creditform->FormEnd();
			$creditcredits = $creditform->GetOutput();
        } else {
			$creditcredits = "";
		}
        if ($modversion[help] <> "")
        {
			$helpform = new pnHTML();
			$helpform->SetInputMode(_PNH_VERBATIMINPUT);
			$helpform->FormStart(pnModURL('Credits', 'user', 'display'));
			$helpform->FormHidden('authid', pnSecGenAuthKey());
			$row2 = array();
			$row2[] = $helpform->Text('<input type="image" src="modules/Credits/pnimages/help.gif" border="0" name="submit" alt=\""._CREDITSHELP."\" $himg[3]> ');
			$row2[] = $helpform->FormHidden('crdirectory', $f);
			$row2[] = $helpform->FormHidden('crpath', $modversion[help]);
			$row2[] = $helpform->FormHidden('heading', 'Help');
			$row2[] = $helpform->FormHidden('displayname', $modversion['displayname']);
			$row2[] = $helpform->FormHidden('version', $modversion['version']);
			$helpform->FormEnd();
			$credithelp = $helpform->GetOutput();
        } else {
			$credithelp = "";
		}
        if ($modversion[license] <> "")
        {
			$licenseform = new pnHTML();
			$licenseform->SetInputMode(_PNH_VERBATIMINPUT);
			$licenseform->FormStart(pnModURL('Credits', 'user', 'display'));
			$licenseform->FormHidden('authid', pnSecGenAuthKey());
			$row2 = array();
			$row2[] = $licenseform->Text('<input type="image" src="modules/Credits/pnimages/license.gif" border="0" name="submit" alt=\""._CREDITSLICENSE."\" $limg[3]> ');
			$row2[] = $licenseform->FormHidden('crdirectory', $f);
			$row2[] = $licenseform->FormHidden('crpath', $modversion[license]);
			$row2[] = $licenseform->FormHidden('heading', 'License');
			$row2[] = $licenseform->FormHidden('displayname', $modversion['displayname']);
			$row2[] = $licenseform->FormHidden('version', $modversion['version']);
			$licenseform->FormEnd();
			$creditlicense = $licenseform->GetOutput();
        } else {
			$creditlicense = "";
		}
		if ($modversion[changelog] <> "")
        {
			$logform = new pnHTML();
			$logform->SetInputMode(_PNH_VERBATIMINPUT);
			$logform->FormStart(pnModURL('Credits', 'user', 'display'));
			$logform->FormHidden('authid', pnSecGenAuthKey());
			$row2 = array();
			$row2[] = $logform->Text('<input type="image" src="modules/Credits/pnimages/credits.gif" border="0" name="submit" alt=\""._CREDITSCHANGELOG."\" $cimg[3]> ');
			$row2[] = $logform->FormHidden('crdirectory', $f);
			$row2[] = $logform->FormHidden('crpath', $modversion[changelog]);
			$row2[] = $logform->FormHidden('heading', 'changelog');
			$row2[] = $logform->FormHidden('displayname', $modversion['displayname']);
			$row2[] = $logform->FormHidden('version', $modversion['version']);
			$logform->FormEnd();
			$creditchangelog = $logform->GetOutput();
        } else {
			$creditchangelog = "";
		}
		$links = "<table border=\"0\"><tr><td>".$creditcredits.'</td><td>'.$credithelp.'</td><td>'.$creditlicense.'</td><td>'.$creditchangelog."</td></tr></table>";
        $row = array($creditsname,
					 pnVarPrepForDisplay($modversion['version']),
                     pnVarPrepForDisplay($modversion['description']),
					 $creditauthor,
					 $links);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddRow($row, 'CENTER');
        $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOutput();
}

function credits_user_display($args) {
	extract($args);
	list($crdirectory,$crpath,$heading,$displayname,$version) = pnVarCleanFromInput('crdirectory','crpath','heading','displayname','version');

	if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Credits', 'user', 'main'));
        return true;
    }

	$output = new pnHTML();

	if (!pnSecAuthAction(0, 'Credits::', '::', ACCESS_READ)) {
        $output->Text(_CREDITSNOAUTH);
        return $output->GetOutput();
    }
	$backurl = pnModURL('Credits','user','main');

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text("<a href=\"$backurl\">"._CREDITSBACK."</a>");
    $output->SetInputMode(_PNH_VERBATIMINPUT);
	
	if ($crdirectory == "") {
		$moduledir = "docs/";
	} else {
		$moduledir = "modules/";
		$crdirectory = $crdirectory."/";
	}
			 
	$heading = "".$heading." "._CREDITSFOR." ".$displayname." "._CREDITSVERSION." ".$version."";
	$output->Title($heading);
	$tmpl_file = "$moduledir$crdirectory$crpath";

	if (file_exists($tmpl_file)) {
		$thefile = implode("",file($tmpl_file));
		$thefile = nl2br(pnVarPrepForDisplay($thefile));
		$output->Text($thefile);
	} else {
		$output->Text(_CREDITSNOEXIST);
	}

	return $output->GetOutput();
}

?>