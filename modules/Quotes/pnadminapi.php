<?php
// File: $Id: pnadminapi.php,v 1.2 2002/11/09 13:25:58 nunizgb Exp $ $Name:  $
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
// Quotes module API: adam_baum (Greg)
// ----------------------------------------------------------------------
// Changes for this admin module thanks to Heinz Hombergs
// (heinz@hhombergs.de), http://www.kodewulf.za.net
// ----------------------------------------------------------------------

// grab total quotes in db
function quotes_adminapi_count()
{
    // Security check
    if (!pnSecAuthAction(0, 'Quotes::', '::', ACCESS_READ)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    $query = "SELECT COUNT(*) FROM $quotestable";

    $result = $dbconn->Execute($query);

    if($result->EOF) {
        return false;
    }

    list($quotes_total) = $result->fields;

    $result->Close();

    return $quotes_total;

}

// return an array containing quote id, quote, author
function quotes_adminapi_display()
{
    // Security check
    if (!pnSecAuthAction(0, 'Quotes::', '::', ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    $query = "SELECT $quotescolumn[qid], $quotescolumn[quote], $quotescolumn[author] FROM $quotestable ORDER BY $quotescolumn[qid] DESC";

    $result = $dbconn->Execute($query);

    if($result->EOF) {
        return false;
    }

    $resarray = array();

    while(list($qid, $quote, $author) = $result->fields) {
        $result->MoveNext();

        $resarray[] = array('qid' => $qid,
                            'quote' => $quote,
                            'author' => $author);
    }
    $result->Close();

    return $resarray;
}

// add quote to db
function quotes_adminapi_add($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($qquote)) || (!isset($qauthor))) {
        pnSessionSetVar('errormsg', _QUOTESARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Quotes::', '::', ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    $nextId = $dbconn->GenId($quotestable);
    $qquote = pnVarPrepForStore($qquote);
    $qauthor = pnVarPrepForStore($qauthor);

    $query = "INSERT INTO $quotestable ($quotescolumn[qid], $quotescolumn[quote], $quotescolumn[author])
                                     VALUES ($nextId, '$qquote', '$qauthor')";

    $result = $dbconn->Execute($query);

    return true;
}

// update quote
function quotes_adminapi_update($args)
{
    extract($args);

    if ((!isset($qid)) || (!isset($qquote)) || (!isset($qauthor))) {
        pnSessionSetVar('errormsg', _QUOTESARGSERROR);
        return false;
    }

    if (!pnSecAuthAction(0, 'Quotes::', "$qauthor::$qid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];
    $query = "UPDATE $quotestable
              SET $quotescolumn[quote] = '" . pnVarPrepForStore($qquote) . "',
                  $quotescolumn[author] = '" . pnVarPrepForStore($qauthor) . "'
              WHERE $quotescolumn[qid] = $qid";
    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _QUOTEAPIUPDATEFAILED);
        return false;
    }
    return true;
}

// delete quote
function quotes_adminapi_delete($args)
{
    extract($args);
  //   if (!isset($qid)) {
  if (!isset($qid) || !is_numeric($qid)) {
         pnSessionSetVar('errormsg', _QUOTESARGSERROR);
        return false;
    }

    if (!pnSecAuthAction(0, 'Quotes::', "$qauthor::$qid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];
    $query = "DELETE FROM $quotestable
              WHERE $quotescolumn[qid] = " . pnVarPrepForStore($qid);
    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _QUOTEAPIUPDATEFAILED);
        return false;
    }

    return true;
}

// return single array containing quote id, quote, author for editing
function quotes_adminapi_edit($args)
{
    extract($args);

    // Argument check
   // if (!isset($qid)) {
     if (!isset($qid) || !is_numeric($qid)) {
        pnSessionSetVar('errormsg', _QUOTESARGSERROR);
        return false;
    }

    // Security check
    if (!pnSecAuthAction(0, 'Quotes::', "$qauthor::$qid", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    $query = "SELECT $quotescolumn[qid],
                     $quotescolumn[quote],
                     $quotescolumn[author] FROM $quotestable
                                          WHERE $quotescolumn[qid] = ". pnVarPrepForStore($qid);

    $result = $dbconn->Execute($query);

    if($result->EOF) {
        return false;
    }

    $resarray = array();

    while(list($qid, $quote, $author) = $result->fields) {
        $result->MoveNext();

        $resarray[] = array('qid' => $qid,
                            'quote' => $quote,
                            'author' => $author);
    }
    $result->Close();

    return $resarray;
}

// return single array containing quote id, quote, author from keyword search.
function quotes_adminapi_search($args)
{
    extract($args);

    if (!isset($keyword)) {
        pnSessionSetVar('errormsg', _QUOTESARGSERROR);
        return false;
    }

    if (!pnSecAuthAction(0, 'Quotes::', "::", ACCESS_EDIT)) {
        pnSessionSetVar('errormsg', _QUOTESAPINOAUTH);
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    $query = "SELECT $quotescolumn[qid],
                     $quotescolumn[quote],
                     $quotescolumn[author] FROM $quotestable
                                          WHERE $quotescolumn[quote] LIKE '%".pnVarPrepForStore($keyword)."%'";

    $result = $dbconn->Execute($query);

    if($result->EOF) {
        return false;
    }

    $resarray = array();

    while(list($qid, $quote, $author) = $result->fields) {
        $result->MoveNext();

        $resarray[] = array('qid' => $qid,
                            'quote' => $quote,
                            'author' => $author);
    }
    $result->Close();

    return $resarray;
}
?>