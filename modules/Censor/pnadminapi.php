<?php
function Censor_adminapi_update($args)
{
    extract($args);
    if (!pnSecAuthAction(0, 'Censor::', "::", ACCESS_ADMIN)) {
        pnSessionSetVar('errormsg', _CENSORNOAUTH);
        return false;
    }

    if(!pnConfigSetVar('CensorMode', $censormode)){
        pnSessionSetVar('errormsg', _CENSORMODEFAIL);
        return false;
    }


    if(!pnConfigSetVar('CensorList', $censorlist)){
        pnSessionSetVar('errormsg', _CENSORLISTFAIL);
        return false;
    }

    if(!pnConfigSetVar('CensorReplace', $censorreplace)){
        pnSessionSetVar('errormsg', _CENSORREPLACEFAIL);
        return false;
    }
    
    return true;
}
?>