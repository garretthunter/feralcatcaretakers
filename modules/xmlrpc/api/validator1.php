<?php
// $Id: validator1.php,v 1.1 2002/11/28 13:14:40 neo Exp $
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
// Original Author of file: Edd Dumbill (C) 1999-2001 <edd@usefulinc.com>
// Additional authors: Marcel van der Boom <marcel@hsdev.com>
// Purpose of file: implementation of the validator1 xml-rpc api
//                  this allows validation of server at 
//                  http://validator.xmlrpc.com
// ----------------------------------------------------------------------

$v1_arrayOfStructs_sig=array(array($xmlrpcInt, $xmlrpcArray));
$v1_arrayOfStructs_doc='This handler takes a single parameter, an array of structs, 
each of which contains at least three elements named moe, larry and curly, all <i4>s. 
Your handler must add all the struct elements named curly and return the result.';

function v1_arrayOfStructs($m) {
  $sno=$m->getParam(0);
	$numcurly=0;
	for($i=0; $i<$sno->arraysize(); $i++) {
		$str=$sno->arraymem($i);
		$str->structreset();
		while(list($key,$val)=$str->structeach())
			if ($key=="curly")
				$numcurly+=$val->scalarval();
	}
	return new xmlrpcresp(new xmlrpcval($numcurly, "int"));
}


$v1_easyStruct_sig=array(array($xmlrpcInt, $xmlrpcStruct));
$v1_easyStruct_doc='This handler takes a single parameter, a struct, containing at 
least three elements named moe, larry and curly, all &lt;i4&gt;s. 
Your handler must add the three numbers and return the result.';

function v1_easyStruct($m) {
  $sno=$m->getParam(0);
	$moe=$sno->structmem("moe");
	$larry=$sno->structmem("larry");
	$curly=$sno->structmem("curly");
	$num=$moe->scalarval()+
		$larry->scalarval()+
		$curly->scalarval();
	return new xmlrpcresp(new xmlrpcval($num, "int"));
}


$v1_echoStruct_sig=array(array($xmlrpcStruct, $xmlrpcStruct));
$v1_echoStruct_doc='This handler takes a single parameter, a struct. 
Your handler must return the struct.';

function v1_echoStruct($m) {
  $sno=$m->getParam(0);
	return new xmlrpcresp($sno);
}

$v1_manyTypes_sig=array(array($xmlrpcArray, $xmlrpcI4, $xmlrpcBoolean,
															$xmlrpcString, $xmlrpcDouble, $xmlrpcDateTime,
															$xmlrpcBase64));
$v1_manyTypes_doc='This handler takes six parameters, and returns an array 
containing all the parameters.';

function v1_manyTypes($m) {
	return new xmlrpcresp(new xmlrpcval(array(
																						$m->getParam(0),
																						$m->getParam(1),
																						$m->getParam(2),
																						$m->getParam(3),
																						$m->getParam(4),
																						$m->getParam(5)),
																			"array"));
}


$v1_moderateSizeArrayCheck_sig=array(array($xmlrpcString, $xmlrpcArray));
$v1_moderateSizeArrayCheck_doc='This handler takes a single parameter, 
which is an array containing between 100 and 200 elements. Each of the items is a string, 
your handler must return a string containing the concatenated text of the first and last elements.';

function v1_moderateSizeArrayCheck($m) {
	$ar=$m->getParam(0);
	$sz=$ar->arraysize();
	$first=$ar->arraymem(0);
	$last=$ar->arraymem($sz-1);
	return new xmlrpcresp(new xmlrpcval($first->scalarval() . 
																			$last->scalarval(), "string"));
}


$v1_simpleStructReturn_sig=array(array($xmlrpcStruct, $xmlrpcI4));
$v1_simpleStructReturn_doc='This handler takes one parameter, and returns a struct 
containing three elements, times10, times100 and times1000, the result of multiplying 
the number by 10, 100 and 1000.';

function v1_simpleStructReturn($m) {
  $sno=$m->getParam(0);
	$v=$sno->scalarval();
	return new xmlrpcresp(new xmlrpcval(array(
																						"times10" =>
																						new xmlrpcval($v*10, "int"),
																						"times100" =>
																						new xmlrpcval($v*100, "int"),
																						"times1000" =>
																						new xmlrpcval($v*1000, "int")), 
																			"struct"));
}


$v1_nestedStruct_sig=array(array($xmlrpcInt, $xmlrpcStruct));
$v1_nestedStruct_doc='This handler takes a single parameter, a struct, that models 
a daily calendar. At the top level, there is one struct for each year. Each year is 
broken down into months, and months into days. Most of the days are empty in the struct 
you receive, but the entry for April 1, 2000 contains a least three elements named moe, 
larry and curly, all &lt;i4&gt;s. Your handler must add the three numbers and return the 
result.';

function v1_nestedStruct($m) {
  $sno=$m->getParam(0);
	
	$twoK=$sno->structmem("2000");
	$april=$twoK->structmem("04");
	$fools=$april->structmem("01");
	$curly=$fools->structmem("curly");
	$larry=$fools->structmem("larry");
	$moe=$fools->structmem("moe");
	return new xmlrpcresp(new xmlrpcval($curly->scalarval()+
																			$larry->scalarval()+
																			$moe->scalarval(), "int"));
	
}

$v1_countTheEntities_sig=array(array($xmlrpcStruct, $xmlrpcString));
$v1_countTheEntities_doc='This handler takes a single parameter, a string, 
that contains any number of predefined entities, namely &lt;, &gt;, &amp; \' 
and ".<BR>Your handler must return a struct that contains five fields, all numbers:  
ctLeftAngleBrackets, ctRightAngleBrackets, ctAmpersands, ctApostrophes, ctQuotes.';

function v1_countTheEntities($m) {
  $sno=$m->getParam(0);
	$str=$sno->scalarval();
	$gt=0; $lt=0; $ap=0; $qu=0; $amp=0;
	for($i=0; $i<strlen($str); $i++) {
		$c=substr($str, $i, 1);
		switch($c) {
		case ">":
			$gt++;
			break;
		case "<":
			$lt++;
			break;
		case "\"":
			$qu++;
			break;
		case "'":
			$ap++;
			break;
		case "&":
			$amp++;
			break;
		default:
			break;
		}
	}
	return new xmlrpcresp(new xmlrpcval(array("ctLeftAngleBrackets" =>
																						new xmlrpcval($lt, "int"),
																						"ctRightAngleBrackets" =>
																						new xmlrpcval($gt, "int"),
																						"ctAmpersands" =>
																						new xmlrpcval($amp, "int"),
																						"ctApostrophes" =>
																						new xmlrpcval($ap, "int"),
																						"ctQuotes" =>
																						new xmlrpcval($qu, "int")), 
																			"struct"));
}

// Dispatch map for validator functions
$_xmlrpc_validator1_dmap= array(
																"validator1.arrayOfStructsTest" =>
																array("function" => "v1_arrayOfStructs",
																			"signature" => $v1_arrayOfStructs_sig,
																			"docstring" => $v1_arrayOfStructs_doc),
																
																"validator1.easyStructTest" =>
																array("function" => "v1_easyStruct",
																			"signature" => $v1_easyStruct_sig,
																			"docstring" => $v1_easyStruct_doc),
																
																"validator1.echoStructTest" =>
																array("function" => "v1_echoStruct",
																			"signature" => $v1_echoStruct_sig,
																			"docstring" => $v1_echoStruct_doc),
																
																"validator1.manyTypesTest" =>
																array("function" => "v1_manyTypes",
																			"signature" => $v1_manyTypes_sig,
																			"docstring" => $v1_manyTypes_doc),
																
																"validator1.moderateSizeArrayCheck" =>
																array("function" => "v1_moderateSizeArrayCheck",
																			"signature" => $v1_moderateSizeArrayCheck_sig,
																			"docstring" => $v1_moderateSizeArrayCheck_doc),
																"validator1.simpleStructReturnTest" =>
																array("function" => "v1_simpleStructReturn",
																			"signature" => $v1_simpleStructReturn_sig,
																			"docstring" => $v1_simpleStructReturn_doc),
																
																"validator1.nestedStructTest" =>
																array("function" => "v1_nestedStruct",
																			"signature" => $v1_nestedStruct_sig,
																			"docstring" => $v1_nestedStruct_doc),
																
																"validator1.countTheEntities" =>
																array("function" => "v1_countTheEntities",
																			"signature" => $v1_countTheEntities_sig,
																			"docstring" => $v1_countTheEntities_doc)
			);

?>