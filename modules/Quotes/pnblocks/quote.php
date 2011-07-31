<?php
// File: $Id: quote.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
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
// Original Author of file: Patrick Kellum
// Purpose of file: Display a random quote
//                  Uses the quote tables and other stuff from Erik Slooff
// ----------------------------------------------------------------------

function quotes_quoteblock_init()
{
    // Security
    pnSecAddSchema('Quotes:Quoteblock:', 'Block title::');
}

function quotes_quoteblock_info()
{
    return array('text_type' => 'Quote',
                 'module' => 'Quotes',
                 'text_type_long' => 'Random Quote',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}

function quotes_quoteblock_display($blockinfo)
{
    // Database information
    pnModDBInfoLoad('Quotes');
    list($dbconn) = pnDBGetConn();

    $pntable = pnDBGetTables();
    $quotestable = $pntable['quotes'];
    $quotescolumn = &$pntable['quotes_column'];

    if (!pnSecAuthAction(0, 'Quotes:Quoteblock:', "$blockinfo[title]::", ACCESS_READ)) {
        return;
    }

    mt_srand((double)microtime()*1000000);

    $total_result = $dbconn->Execute("SELECT COUNT($quotescolumn[qid]) FROM $quotestable");
    list($total) = $total_result->fields;

    $output = new pnHTML();

    if ($total < 2) {
	$output->SetInputMode(_PNH_VERBATIMINPUT);
        $output->Text('There are too few quotes in the database');
	$output->SetInputMode(_PNH_PARSEINPUT);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

    } else {

        $p = mt_rand(0,($total - 1));

	$sql = "SELECT $quotescolumn[quote], $quotescolumn[author] FROM $quotestable order by $quotescolumn[qid]";
        
	$result = $dbconn->SelectLimit($sql,1,$p);

	while(list($quote, $author) = $result->fields) {
	    $result->MoveNext();

	    $output->SetInputMode(_PNH_VERBATIMINPUT);
	    $output->LineBreak();
	    $output->Text('<i>' .$quote. '</i>');
	    if(!empty($author)) {
		$output->LineBreak(2);
		$output->Text('-- ' . $author);
	    }
	    $output->SetInputMode(_PNH_PARSEINPUT);
	}
    }
    $blockinfo['content'] = $output->GetOutput();
    return themesideblock($blockinfo);
}
?>