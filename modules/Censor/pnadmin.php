<?php
function Censor_admin_main()
{
    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Censor::', '::', ACCESS_EDIT)) {
        $output->Text(_CENSORNOAUTH);
        return $output->GetOutput();
    }

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(Censor_admin_modify(array()));
    $output->SetInputMode(_PNH_PARSEINPUT);

    return $output->GetOutput();
}

function Censor_admin_modify($args)
{
    list(
         $censormode,
         $censorlist
                    )= pnVarCleanFromInput(
                                           'censormode',
                                           'censorlist');
    extract($args);

    $output = new pnHTML();

    if (!pnSecAuthAction(0, 'Censor::', ":CensorList:", ACCESS_OVERVIEW)) {
        $output->Text(_CENSORNOAUTH);
        return $output->GetOutput();
    }

    $output->Title(_EDITCENSOR);

    $output->FormStart(pnModURL('Censor', 'admin', 'update'));

    $output->FormHidden('authid', pnSecGenAuthKey());

    $output->TableStart();

    $censormode = pnConfigGetVar('CensorMode');
    $censorlist = pnConfigGetVar('CensorList');
    $censorreplace = pnConfigGetVar('CensorReplace');

    if(pnSecAuthAction(0, 'Censor::', ":CensorMode:", ACCESS_OVERVIEW)){
        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);
        $row[] = $output->Text(pnVarPrepForDisplay(_CENSORMODE));
        $row[] = $output->FormCheckBox('censormode', pnVarPrepForDisplay($censormode));
        $output->SetOutputMode(_PNH_KEEPOUTPUT);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddrow($row, 'left');
        $output->SetInputMode(_PNH_PARSEINPUT);
        $output->Linebreak(2);
    }

    if(pnSecAuthAction(0, 'Censor::', ":CensorList:", ACCESS_OVERVIEW)){
        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);
        $row[] = $output->Text(pnVarPrepForDisplay(_CENSORLIST));
        $row[] = $output->FormTextArea('censorlist', pnVarPrepForDisplay(implode("\n",$censorlist)));
        $output->SetOutputMode(_PNH_KEEPOUTPUT);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddrow($row, 'left');
        $output->SetInputMode(_PNH_PARSEINPUT);
        $output->Linebreak(2);
    }

    if(pnSecAuthAction(0, 'Censor::', ":CensorReplace:", ACCESS_OVERVIEW)){
        $row = array();
        $output->SetOutputMode(_PNH_RETURNOUTPUT);
        $row[] = $output->Text(pnVarPrepForDisplay(_CENSORREPLACE));
        $row[] = $output->FormText('censorreplace', pnVarPrepForDisplay($censorreplace), 35,35);
        $output->SetOutputMode(_PNH_KEEPOUTPUT);
        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->TableAddrow($row, 'left');
        $output->SetInputMode(_PNH_PARSEINPUT);
        $output->Linebreak(2);
    }
    
    $output->TableEnd();
    
    $output->Linebreak(2);
    $output->FormSubmit(_CENSORUPDATE);
    $output->FormEnd();
    
    return $output->GetOutput();
}

function Censor_admin_update($args)
{
    list(
         $censormode,
         $censorlist,
         $censorreplace
                     ) = pnVarCleanFromInput(
                                              'censormode',
                                              'censorlist',
                                              'censorreplace'
                                                           );
    extract($args);
    
    if(empty($censormode)){
        $censormode = 0;
    }else{
        $censormode = 1;
    }
    
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Censor', 'admin', 'view'));
        return true;
    }

    if (!pnModAPILoad('Censor', 'admin')) {
        pnSessionSetVar('errormsg', _LOADFAILED);
        return $output->GetOutput();
    }


    $censorlist = preg_split ("/[\s,]+/", $censorlist);
    if(pnModAPIFunc('Censor',
                    'admin',
                    'update',
                    array(
                          'censormode' => $censormode,
                          'censorlist' => $censorlist,
                          'censorreplace' => $censorreplace
                                                      ))) {

        pnSessionSetVar('statusmsg', _CENSORUPDATED);
    }

    pnRedirect(pnModURL('Censor', 'admin', 'view'));

    return true;
}

?>