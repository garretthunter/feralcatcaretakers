<?php
// File: $Id: pnuserapi.php,v 1.2 2002/11/28 13:16:30 neo Exp $
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
// Original Author of file: Gregor J. Rothfuss
// Purpose of file: Implement XML-RPC backend
// ----------------------------------------------------------------------


/**
 * Initialise the installed XML-RPC server APIs
 * <br>
 * Carries out a number of initialisation tasks to get XML-RPC up and
 * running.
 * @returns void
 */
function xmlrpc_userapi_initServer()
{
  // include XML-RPC libraries
  include_once 'modules/xmlrpc/lib/xmlrpc.inc';
  include_once 'modules/xmlrpc/lib/xmlrpcs.inc';

  // include the various APIs (hardcoded for now)
  include_once 'modules/xmlrpc/api/blogger.php';
	// merge blogger functions 
	$functions = $_xmlrpc_blogger_dmap;

     include_once 'modules/xmlrpc/api/validator1.php';
   // merge validator1 functions
         $functions = array_merge($functions, $_xmlrpc_validator1_dmap);

  //create server instance
  $server = new xmlrpc_server($functions);
  if (!$server) {
    die("xmlrpc_userapi_initServer: can't create server");
  }
  return $server;
}

/**
 * Initialise the XML-RPC client libraries
 * <br>
 * Carries out a number of initialisation tasks to get XML-RPC up and
 * running.
 * @returns void
 */
function xmlrpc_userapi_initClient()
{
  // include XML-RPC libraries
  include_once 'modules/xmlrpc/lib/xmlrpc.inc';

  return true;
}

/**
 * Call a remote XML-RPC method
 * <br>
 * Opens an XML-RPC connection
 * with the specified parameters.
 * @returns resultrarray
 */
function xmlrpc_userapi_call($methodname, $params, $endpoint)
{
  // intialize client
  xmlrpc_userapi_initClient();

  // build the XML-RPC call and execute it
  $f=new xmlrpcmsg($methodname,
  array(new xmlrpcval($name), new xmlrpcval($url)));
  $c=new xmlrpc_client($endpoint['path'], $endpoint['site'], $endpoint['port']);
  $r=$c->send($f);
  if (!$r) { die('xmlrpc_userapi_call: send failed'); }
  $v=$r->value();
  if (!$r->faultCode()) {
     $result = array();
     for ($i = 0; $i <= $v; $i++)
     {
       $val = $v->structmem($i); $result[$i] = $val->scalarval();
     }
  } else {
    $result = 'Fault. Code: '.$r->faultCode().' Reason '.$r->faultString();
  }
  return $result;

}

function xmlrpc_userapi_get_userid($username) {
    $pntable = pnDBGetTables();
    list($dbconn) = pnDBGetConn();
		$cols = &$pntable['users_column'];

    $result = $dbconn->Execute("SELECT  $cols[uid] from $pntable[users] where $cols[uname] ='$username'");
    $uid = $result->fields[0];
    return $uid;
}


?>