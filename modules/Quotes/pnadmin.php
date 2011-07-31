<?php
// File: $Id: pnadmin.php,v 1.3 2002/12/09 10:34:14 tanis Exp $ $Name:  $
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
// http://www.postnuke.com/
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
// Original Author of file:  Erik Slooff <erik@slooff.com> www.slooff.com
// Purpose of file:
// PHP-NUKE 5.0: Quote of the day Add-On
// Copyright (c) 2000 by Erik Slooff (erik@slooff.com)
// ----------------------------------------------------------------------
// Changes for this admin module thanks to Heinz Hombergs
// (heinz@hhombergs.de), http://www.kodewulf.za.net
// ----------------------------------------------------------------------

function quotes_admin_main()
{
    $output = new pnHTML();

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->Text(quotes_adminmenu());
    $output->LineBreak();

    if(!pnModAPILoad('Quotes', 'admin')) {
	$output->Text(_APILOADFAILED);
	return $output->GetOutput();
    }
    pnModAPILoad('Quotes', 'admin');

    $output->Text(_QOTDTOTAL . pnModAPIFunc('Quotes', 'admin', 'count'));
    $output->SetInputMode(_PNH_PARSEINPUT);

    return $output->GetOutput();
}

/**
 * Default
 */
function quotes_adminmenu()
{
    $output = new pnHTML();

    $authid = pnSecGenAuthKey();

    $output->TableStart(_QOTDADD);
    $output->Text(pnGetStatusMsg());
    $output->Linebreak();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    if(!pnSecAuthAction(0, 'Quotes::', '::', ACCESS_ADD)) {
	$output->Text(_QUOTESNOAUTH);
	$output->TableEnd();
	return $output->GetOuput();
    }
    $output->TableRowStart();
    $output->TableColStart();
    $output->FormStart(pnModURL('Quotes', 'admin', 'qotdadd'));
    $output->FormHidden('authid', $authid);
//    $output->LineBreak();
    $output->Text(_QOTDTEXT);
    $output->TableColEnd();
    $output->TableRowEnd();
    $output->TableRowStart();
    $output->TableColStart();
    $output->FormTextArea('qquote','', 8, 50);
    $output->LineBreak();
    $output->Text(_QOTDAUTHOR);
    $output->LineBreak();
    $output->FormText('qauthor', '', 31, 128);
    $output->LineBreak(2);
    $output->TableColEnd();
    $output->TableRowEnd();
    $output->TableRowStart();
    $output->TableColStart();
    $output->FormSubmit(_SUBMIT);
    $output->FormEnd();
    $output->LineBreak(1);
    $output->URL(pnModURL('Quotes', 'admin', 'qotddisplay', array('page' => 1, 'authid' => $authid)), _QOTDMODIFY);
    $output->TableColEnd();
    $output->TableRowEnd();
    $output->TableEnd();

    return $output->GetOutput();

}

/**
 * Generate quotes listing for display
 */
function quotes_admin_qotddisplay()
{
    //list($page, $keyword) = pnVarCleanFromInput('page', 'keyword');

    $output = new pnHTML();

    $authid = pnSecGenAuthKey();

    if(!(pnSecAuthAction(0, 'Quotes::', '::', ACCESS_READ))) {
	$output->Text(_QUOTESNOAUTH);
        return $output->GetOutput();
    }
    $output->TableStart(_QOTDMODIFY);
    $output->LineBreak();
    //$output->FormStart(pnModURL('Quotes', 'admin', 'qotddisplay'));
    $output->FormStart(pnModURL('Quotes', 'admin', 'qotdsearch'));
    $output->FormHidden('authid', $authid);
    $output->Text(_QOTDSEARCH);
    $output->LineBreak();
    $output->FormText('keyword', '', 31, 128);
    $output->FormSubmit();
    $output->FormEnd();
    $output->TableEnd();

    $columnHeaders = array(_QOTDQUOTE,
			   _QOTDAUTHOR,
			   _QOTDACTION);

    $output->TableStart('', $columnHeaders, 1);

    if(!pnModAPILoad('Quotes', 'admin')) {
	$output->Text(_APILOADFAILED);
	return $output->GetOutput();
    }
    $quotes = pnModAPIFunc('Quotes', 'admin', 'display');

    if($quotes == false) {
	$output->Text(_QOTDNOQUOTES);
        // if no quotes found, end the table or the footer gets pulled up the page.
	$output->TableEnd();
	return $output->GetOutput();
    }
    foreach($quotes as $quote) {

	$actions = array();

	$output->SetOutputMode(_PNH_RETURNOUTPUT);

	    if(pnSecAuthAction(0, 'Quotes::', "$quote[author]::$quote[qid]", ACCESS_EDIT)) {
	        $actions[] = $output->URL(pnModURL('Quotes', 'admin', 'edit', array('qid' => $quote['qid'], 'qauthor' => urlencode($quote['author']), 'authid' => $authid)), _QOTDEDITACTION);
	    }
	    if(pnSecAuthAction(0, 'Quotes::', "$quote[author]::$quote[qid]", ACCESS_DELETE)) {
	        $actions[] = $output->URL(pnModURL('Quotes', 'admin', 'delete', array('qid' => $quote['qid'], 'qauthor' => urlencode($quote['author']), 'authid' => $authid)), _QOTDDELETEACTION);
	    }
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

    $actions = join(' | ', $actions);

    $row = array(pnVarPrepForDisplay($quote['quote']),
		 pnVarPrepForDisplay($quote['author']),
				     $actions);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'CENTER');
    $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOutput();
}

/**
 * Add new quote to database.
 */
function quotes_admin_qotdadd()
{
    list($qquote, $qauthor) = pnVarCleanFromInput('qquote', 'qauthor');

    if(!pnSecConfirmAuthKey()) {
	pnSessionSetVar('errormsg', _BADAUTHKEY);
	pnRedirect(pnModURL('Quotes', 'admin', 'main'));
	return true;
    }
    pnModAPILoad('Quotes', 'admin');

    if(pnModAPIFunc('Quotes',
		    'admin',
		    'add',
		    array('qquote' => $qquote,
			  'qauthor' => $qauthor))) {
	pnSessionSetVar('statusmsg', _QOTDADDSUCCESS);
    }
    pnRedirect(pnModURL('Quotes', 'admin', 'main'));

    return true;
}
/**
 * Search quote database by keyword - unfinished obviously.
 */
function quotes_admin_qotdsearch()
{
    $keyword = pnVarCleanFromInput('keyword');

    $authid = pnSecGenAuthKey();

    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Quotes', 'admin', 'main'));
        return true;
    }
    $output = new pnHTML();
    $output->Text(pnGetStatusMsg());
    $output->LineBreak();
    $output->Text(_QOTDSEARCHRESULTS);

    $columnHeaders = array(_QOTDQUOTE,
			   _QOTDAUTHOR,
			   _QOTDACTION);

    $output->TableStart('', $columnHeaders, 1);

    if(!pnModAPILoad('Quotes', 'admin')) {
	$output->Text(_APILOADFAILED);
	return $output->GetOutput();
    }
    $quotes = pnModAPIFunc('Quotes', 'admin', 'search', array('keyword' => $keyword));

    if($quotes == false) {
	$output->Text(_QOTDNOQUOTES);
	$output->TableEnd();
	return $output->GetOutput();
    }
    foreach($quotes as $quote) {

	$actions = array();

	$output->SetOutputMode(_PNH_RETURNOUTPUT);

	    if(pnSecAuthAction(0, 'Quotes::', "$quote[author]::$quote[qid]", ACCESS_EDIT)) {
	        $actions[] = $output->URL(pnModURL('Quotes', 'admin', 'edit', array('qid' => $quote['qid'], 'qauthor' => urlencode($quote['author']), 'authid' => $authid)), _QOTDEDITACTION);
	    }
	    if(pnSecAuthAction(0, 'Quotes::', "$quote[author]::$quote[qid]", ACCESS_DELETE)) {
	        $actions[] = $output->URL(pnModURL('Quotes', 'admin', 'delete', array('qid' => $quote['qid'], 'qauthor' => urlencode($quote['author']), 'authid' => $authid)), _QOTDDELETEACTION);
	    }
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

    $actions = join(' | ', $actions);

    $row = array(pnVarPrepForDisplay($quote['quote']),
		 pnVarPrepForDisplay($quote['author']),
				     $actions);

    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'CENTER');
    $output->SetInputMode(_PNH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOuput();

}

/**
 * Delete selected quote
 */
function quotes_admin_delete()
{
    list($qid, $qauthor, $confirm) = pnVarCleanFromInput('qid', 'qauthor', 'confirm');

    if (empty($confirm)) {

        $output = new pnHTML();

        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Title(_QOTDDEL);
	$output->SetInputMode(_PNH_PARSEINPUT);
        $output->ConfirmAction(_QOTDDEL,
                               pnModURL('Quotes','admin','delete'),
                               _CANCELQUOTEDELETE,
                               pnModURL('Quotes','admin','qotddisplay'), array('qid' => $qid, 'qauthor' => $qauthor));
        return $output->GetOutput();
    }
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Quotes', 'admin', 'qotddisplay'));
        return true;
    }
    pnModAPILoad('Quotes', 'admin');

    if (pnModAPIFunc('Quotes',
                     'admin',
                     'delete', array('qid' => $qid, 'qauthor' => $qauthor))) {
        pnSessionSetVar('statusmsg', _QOTDDELETED);
    }
    pnRedirect(pnModURL('Quotes', 'admin', 'main'));

    return true;
}

/**
 * Edit quote
 */
function quotes_admin_edit()
{
    list($qid, $qauthor) = pnVarCleanFromInput('qid', 'qauthor');

    $output = new pnHTML();

    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Quotes', 'admin', 'qotddisplay'));
        return true;
    }
    if(!pnModAPILoad('Quotes', 'admin', 'edit')) {
	$output->Text(_APILOADFAILED);
	return $output->GetOuput();
    }
    $quotes = pnModAPIFunc('Quotes', 'admin', 'edit', array('qid' => $qid, 'qauthor' => $qauthor));

    if($quotes == false) {
	$output->Text(_QOTDNQ);
	return $output->GetOuput();
    }
    $authid = pnSecGenAuthKey();

    $output->TableStart(_QOTDMODIFY);

    foreach($quotes as $quote) {

	$output->FormStart(pnModURL('Quotes', 'admin', 'qotdupdate'));
    	$output->FormHidden('authid', $authid);
	$output->FormHidden('qid', $qid);
    	$output->LineBreak();
    	$output->Text(_QOTDTEXT);
    	$output->FormTextArea('qquote',$quote['quote'], 5, 60);
    	$output->LineBreak();
    	$output->Text(_QOTDAUTHOR);
    	$output->FormText('qauthor', $quote['author'], 31, 128);
    	$output->LineBreak();

    	$output->FormSubmit(_SUBMIT);
    	$output->FormEnd();
    }
    $output->TableEnd();

    return $output->GetOutput();
}

/**
 * Called from quotes_admin_edit.
 * Confirm update of quote first.
 * On confirmation, load API and
 * update.
 */
function quotes_admin_qotdupdate()
{
    list($qid, $qquote, $qauthor, $confirm) = pnVarCleanFromInput('qid', 'qquote', 'qauthor', 'confirm');

    if (empty($confirm)) {

        $output = new pnHTML();

        $output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Title('Update Quote');
	$output->SetInputMode(_PNH_PARSEINPUT);
        $output->ConfirmAction(_QOTDSAVE,
                               pnModURL('Quotes','admin','qotdupdate'),
                               _CANCELQUOTEDELETE,
                               pnModURL('Quotes','admin','qotddisplay'), array('qid' => $qid,
										'qquote' => $qquote,
										'qauthor' => $qauthor));
        return $output->GetOutput();
    }
    if (!pnSecConfirmAuthKey()) {
        pnSessionSetVar('errormsg', _BADAUTHKEY);
        pnRedirect(pnModURL('Quotes', 'admin', 'main'));
        return true;
    }
    pnModAPILoad('Quotes', 'admin');

    if (pnModAPIFunc('Quotes',
                     'admin',
                     'update', array('qid' => $qid, 'qquote' => $qquote, 'qauthor' => $qauthor))) {

        pnSessionSetVar('statusmsg', 'Quote Successfully Updated');
    }
    pnRedirect(pnModURL('Quotes', 'admin', 'main'));

    return true;
}
?>