<?php
// File: $Id: fxp.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Original Author of file: Patrick Kellum
// Purpose of file: Display currency exchange rates
// ----------------------------------------------------------------------

$blocks_modules['fxp'] = array (
    'func_display' => 'blocks_fxp_display',
    'func_add' => '',
    'func_update' => 'blocks_fxp_update',
    'func_edit' => 'blocks_fxp_edit',
    'text_type' => 'Currency',
    'text_type_long' => 'FXP Currency Exchange',
    'allow_multiple' => true,
    'form_content' => false,
    'form_refresh' => true,
//  'support_xhtml' => true,
    'show_preview' => true
);

pnSecAddSchema('fxpblock::', 'Block title::');

function blocks_fxp_display($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();


    if (!pnSecAuthAction(0, 'fxpblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    $blocktable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];
    $fxp_port = 5011;
    $past = time() - $row['refresh'];
    if ($row['unix_update'] < $past) {
//    if (true) {
        $fp = fsockopen('www.oanda.com', $fxp_port, $errno, $errstr, 5);
        if (!$fp)
        {
	    if(!isset($bid)) {
		$bid = '';
	    }
            $content = addslashes(_FXPPROBLEM);
            $next_try = time() + 600;
            $result = mysql_query("UPDATE $blocktable SET $blockscolumn[content]='$content',$blockscolumn[last_update]=FROM_UNIXTIME($next_try) WHERE $blockscolumn[bid]=".pnVarPrepForStore($bid)."");
            $row['title'] = "$row[title] *";
	    $row['content'] = "$row[content]\n\n\n<!--\n\n\n\n\n\n\n".ml_ftime(_DATESTRING,$row['unix_update'])."\n\n\n\n\n-->\n\n\n\n";
            return themesideblock($row);
        }
        // get an array of currencies
        $request = "fxp/1.1\r\n"
            ."Query: currencies\r\n"
            ."\r\n"
        ;
        fputs($fp, $request);
        if (trim(fgets($fp, 128)) == "fxp/1.1 200 ok")
        {
            while (trim(fgets($fp, 128)))
            {
                // nothing here but us chickens...
            }
            // ok, here we go...
            while ($response = trim(fgets($fp, 128)))
            {
                $fxp[] = $response;
            }
        }
        foreach ($fxp as $v)
        {
            $iso = substr($v, 0, 3);
            $desc = substr($v, 4);
            $currencies[$iso] = $desc;
        }
        asort($currencies);
        // get quotes
        $rates = explode("\n", trim($row['url']));
        usort($rates, 'blocks_fxp_sort');
        foreach ($rates as $v)
        {
            $temp = explode('|', $v);
            $request = "fxp/1.1\r\n"
                ."Query: quote\r\n"
                ."Quotecurrency: $temp[1]\r\n"
                ."Basecurrency: $temp[0]\r\n"
                ."\r\n"
            ;
            fputs($fp, $request);
            if (trim(fgets($fp, 128)) == "fxp/1.1 200 ok")
            {
                while (trim(fgets($fp, 128)))
                {
                    // nothing here but us chickens...
                }
                // ok, here we go...
		if(!isset($cur_cur)) { $cur_cur = ''; };
                while ($response = trim(fgets($fp, 128)))
                {
                    if ($cur_cur != $temp[1])
                    {
                        $quotes[] = '<b>'.$currencies[$temp[1]].':</b><br>';
                        $cur_cur = $temp[1];
                    }
                    $quotes[] = '<font class="pn-sub">&nbsp;&nbsp;&nbsp;'.$currencies[$temp[0]].": $response</font><br>";
                }
            } else {
                $content = addslashes(_FXPPROBLEM2);
                $next_try = time() + 600;
		if(!isset($bid)) {
		    $bid = '';
		}
                $result = mysql_query("UPDATE $blocktable SET $blockscolumn[content]='".pnVarPrepForStore($content)."',$blockscolumn[last_update]=FROM_UNIXTIME($next_try) WHERE $blockscolumn[bid]=".pnVarPrepForStore($bid)."");
                $row['title'] = "$row[title] *";
                $row['content'] = "$row[content]\n\n\n<!--\n\n\n\n\n\n\n".ml_ftime(_DATESTRING,$row['unix_update'])."\n\n\n\n\n-->\n\n\n\n";
                return themesideblock($row);
            }
        }
        fclose($fp);
        $row['content'] = implode("\n", $quotes);
        $sql_content = addslashes($row['content']);
        $sql = "UPDATE $blocktable SET $blockscolumn[content]='".pnVarPrepForStore($sql_content)."',$blockscolumn[last_update]=NOW() WHERE $blockscolumn[bid]=".pnVarPrepForStore($row['bid'])."";
        if(!mysql_query($sql)) {
            $row['title'] .= ' *';
            $row['content'] .= "<!--\n\n\n".mysql_error()."\n\n\n$sql\n\n\n-->";
        }
    }
    return themesideblock($row);
}

function blocks_fxp_update($vars)
{
    $vars['url'] = '';
    // edit quote
    $c = 0;

    if(count($vars['fxp_rname']))
    {
        $delete = $vars[fxp_delete];
        $insert = $vars[fxp_insert];
        foreach ($vars[fxp_rname] as $v)
        {
            $c++;
            if (!$vars[fxp_delete][$c])
            {
                $vars['url'] .= $vars[fxp_rname][$c].'|'.$vars[fxp_qname][$c]."\n";
            }
            // insert a blank link
            if ($vars[fxp_insert][$c])
            {
                $vars['url'] .= "|\n";
            }
        }
    }
    // new quote
    if($vars['fxp_new_rname'] && $vars['fxp_new_qname'])
    {
        $vars['url'] .= "$vars[fxp_new_rname]|$vars[fxp_new_qname]\n";
    }
    return $vars;
}

function blocks_fxp_edit($row)
{
    $fxp_port = 5011;
    global $pntheme;
    $fp = fsockopen('www.oanda.com', $fxp_port, $errno, $errstr, 5);
    if (!$fp)
    {
        return '<tr><td valign="top" class="pn-normal">'._FXP_ERROR.':</td><td>'
            .'Error Contacting FXP Server!'
            ."</td></tr>\n"
        ;
    }
    // get an array of currencies
    $request = "fxp/1.1\r\n"
        ."Query: currencies\r\n"
        ."\r\n"
    ;
    fputs($fp, $request);
    if (trim(fgets($fp, 128)) == "fxp/1.1 200 ok")
    {
        while (trim(fgets($fp, 128)))
        {
            // nothing here but us chickens...
        }
        // ok, here we go...
        while ($response = trim(fgets($fp, 128)))
        {
            $fxp[] = $response;
        }
    }
    fclose($fp);
    foreach ($fxp as $v)
    {
        $iso = substr($v, 0, 3);
        $desc = substr($v, 4);
        $currencies[$iso] = $desc;
    }
    asort($currencies);
    // currency code list
    $output = '<tr><td valign="top" class="pn-normal">'._CURRENCYCODES.':</td><td>'
        .'<table border="1"><tr>'
        ."<td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CODE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CURRENCY."</td>"
        ."<td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CODE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CURRENCY."</td>"
        ."<td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CODE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CURRENCY."</td>"
        ."<td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CODE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._CURRENCY."</td>"
        .'</tr>'
        .'<tr>'
    ;
    $c = 1;
    foreach ($currencies as $k=>$v)
    {
        if ($c > 4)
        {
            $output .= "</tr>\n<tr>";
            $c = 1;
        }
        $output .= "<td align=\"center\" class=\"pn-normal\" style=\"text-align:center\">$k</td><td align=\"center\" class=\"pn-normal\" style=\"text-align:center\">$v</td>\n";
        $c++;
    }
    $output .= '</tr></table></td></tr>';
    // build form
    $output .= '<tr><td valign="top" class="pn-normal">'._CURRENCYRATES.':</td><td>'
        ."<table border=\"1\"><tr><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._QUOTE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._BASE."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._INSERT."</td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\">"._DELETE."</td></tr>"
    ;
    $c = 0;
    $rates = explode("\n", trim($row['url']));
    if ($rates[0])
    {
        foreach ($rates as $v)
        {
            $c++;
            $temp = explode('|', $v);
            $output .= '<tr>';
            $output .= "<td valign=\"top\"><input type=\"text\" name=\"fxp_qname[$c]\" size=\"30\" value=\"$temp[1]\" class=\"pn-normal\" /></td>";
            $output .= "<td valign=\"top\"><input type=\"text\" name=\"fxp_rname[$c]\" size=\"30\" value=\"$temp[0]\" class=\"pn-normal\" /></td>";
            $output .= "<td valign=\"top\"><input type=\"checkbox\" name=\"fxp_insert[$c]\" value=\"1\" class=\"pn-normal\" /></td>";
            $output .= "<td valign=\"top\"><input type=\"checkbox\" name=\"fxp_delete[$c]\" value=\"1\" class=\"pn-normal\" /></td>";
            $output .= "</tr>\n";
        }
    }
    $output .= '<tr>';
    $output .= "<td valign=\"top\"><input type=\"text\" name=\"fxp_new_qname\" size=\"30\" class=\"pn-normal\" /></td>";
    $output .= "<td valign=\"top\"><input type=\"text\" name=\"fxp_new_rname\" size=\"30\" class=\"pn-normal\" /></td>";
    $output .= "<td valign=\"top\" class=\"pn-normal\" colspan=\"2\">"._NEW."</td></tr>\n";
    $output .= '</table></td></tr>';
    return $output;
}
function blocks_fxp_sort($left, $right)
{
    $ltemp = explode('|', $left);
    $rtemp = explode('|', $right);
    return strcasecmp($ltemp[1].'-'.$ltemp[0], $rtemp[1].'-'.$rtemp[0]);
}
?>
