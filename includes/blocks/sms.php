<?php
// File: $Id: sms.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
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
// Original Author of file: James McGlinn james@mcglinn.org
// Purpose of file:
// ----------------------------------------------------------------------

$blocks_modules['sms'] = array(
    'func_display' => 'blocks_sms_block',
    'text_type' => 'SMS',
    'text_type_long' => 'SMS',
    'allow_multiple' => true,
    'form_content' => false,
    'form_refresh' => false,
    'show_preview' => true
);

// Security
pnSecAddSchema('SMSblock::', 'Block title::');
/*
	jm_sms 0.81 12/11/01
	Copyright (C) 2001 James McGlinn james@mcglinn.org
	jm_sms is a class for sending GSM text messages via the
	free web-based services at http://www.mtnsms.com/ and
	http://www.sms.ac/.
	Updates can be found at http://james.mcglinn.org/jm_sms/.

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	(See http://www.gnu.org/copyleft/gpl.html)

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	Changes:
   0.81 12/11/01  Bugfix; change in MTNSMS code
                  thanks to Luis Gois (luisgois@netc.pt).
	0.8  12/10/01  Added support for SMS.AC
	               thanks to * RyliX * (rylix@k33P3r.EU.org).
	0.7  21/09/01  Randomised login array for faster sending
	               when over quota on first account.
	               Speed optimisations in network code
	               thanks to Mike Petrov (mike@deyton.ru).
	0.6  06/06/01  Added check for incorrect login details.
	0.5  31/05/01  Proxy support added
	               thanks to Bas (bhuism@odee.net).
	0.4  28/05/01  Provision for multiple mtnsms accounts.
	               Bug fix for accounts defaulting to smsxtra.
	0.3  27/04/01  New debug option outputs status data.
	0.2  24/04/01  Re-attempts connection if server is busy.
	0.1  23/04/01  Initial release.
*/
class jm_sms {

	var $server;
	var $cookies;
	var $login;
	var $curUrl;
	var $debug = FALSE;
	var $proxyServer;
	var $proxyPort;
	var $proxyUser;
	var $proxyPass;
	var $proxy = FALSE;
	var $useSmsAc = FALSE;

	function getServer() { return $this->server; }
	function setServer( $server ) { return $this->server = $server; }
	function getCurUrl() { return $this->curUrl; }
	function setCurUrl( $curUrl ) { return $this->curUrl = $curUrl; }
	function getDebug() { return $this->debug; }
	function setDebug( $debug ) { return $this->debug = $debug; }
	function getProxyServer() { return $this->proxyServer; }
	function setProxyServer( $proxyServer ) { return $this->proxyServer = $proxyServer; }
	function getProxyPort() { return $this->proxyPort; }
	function setProxyPort( $proxyPort ) { return $this->proxyPort = $proxyPort; }
	function getProxyUser() { return $this->proxyUser; }
	function setProxyUser( $proxyUser ) { return $this->proxyUser = $proxyUser; }
	function getProxyPass() { return $this->proxyPass; }
	function setProxyPass( $proxyPass ) { return $this->proxyPass = $proxyPass; }
	function getProxy() { return $this->proxy; }
	function setProxy( $proxy ) { return $this->proxy = $proxy; }
	function getSmsAc() { return $this->useSmsAc; }
	function setSmsAc( $smsAc ) { return $this->useSmsAc = $smsAc; }

	function jm_sms( $username, $password, $debug = FALSE, $smsAc = FALSE ) {
		if( $debug ) $this->setDebug( TRUE );
		if( $smsAc ) $this->setSmsAc( TRUE );
		$this->login = array();
		$this->cookies = array();
		$this->addLogin( $username, $password );
		if( $this->getSmsAc() == FALSE )
			$this->setServer( "www.mtnsms.com" );
		else
			$this->setServer( "www.sms.ac" );
		$this->setCurUrl( "http://" . $this->getServer() . "/" );
	}

	function addLogin( $username, $password ) {
		$this->login[$username] = $password;
		$this->shuffleLogins();
		return 1;
	}

	function shuffleLogins() {
		$arrayOrig = $this->login;
		$arrayKeys = array();
		$arrayNew = array();
		while( list( $key, $value ) = each( $arrayOrig ) )
			$arrayKeys[] = $key;
		srand( (double) microtime() * 1000000 );
		shuffle( $arrayKeys );
		srand( (double) microtime() * 1000000 );
		shuffle( $arrayKeys );
		while( list( $key, $value ) = each( $arrayKeys ) )
			$arrayNew[$value] = $arrayOrig[$value];
		$this->login = $arrayNew;
	}

	function getUsername() {
		$username = key( $this->login );
		return $username;
	}

	function getPassword() {
		$password = current( $this->login );
		return $password;
	}

	function nextLogin() {
		if( next( $this->login ) != FALSE )
			return TRUE;
		else
			return FALSE;
	}

	function getCookies() {
		$cookies = implode( "; ", $this->cookies );
		if( $this->getDebug() ) echo "jm_sms: Found cookies: " . $cookies . "\n";
		return implode( "; ", $this->cookies );
	}

	function setCookies( $cookie ) {
		$cookie = substr( $cookie, 0, strpos( $cookie, ";" ) );
		if( ( substr( $cookie, 0, 14 ) == "mtnsms%2Ep%2E2" ) || ( substr( $cookie, 0, 20 ) == "ASPSESSIONIDQQGQQXQC" ) )
			$this->cookies[0] = $cookie;
		else if( ( substr( $cookie, 0, 14 ) == "mtnsms%2Et%2E2" ) || ( substr( $cookie, 0, 12 ) == "ASPSESSIONID" ) )
			$this->cookies[1] = $cookie;
		else
			$this->cookies[] = $cookie;
		if( $this->getDebug() ) echo "jm_sms: Set cookie: " . $cookie . "\n";
	}

	function postUrl( $url, $formdata ) {
		$url = "http://" . $this->getServer() . $url;
		if( $this->getDebug() ) echo "jm_sms: URL for POST: " . $url . "\n";
		$data =  "POST " . $url . " HTTP/1.0\r\n";
		$data .= $this->generateHttpHeader();
		$data .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$data .= "Content-Length: " . strlen( $formdata ) . "\r\n\r\n";
		$data .= $formdata;
		do {
			$errno = 0;
			$fp = @fsockopen( ( $this->getProxy()?$this->getProxyServer():$this->getServer() ), ( $this->getProxy()?$this->getProxyPort():80 ), $errno, $errstr, 30 );
			if( $this->getDebug() && $errno ) echo "jm_sms: Retrying Failed POST (" . $errno . "): " . $errstr . "\n";
			if( $errno ) sleep( 1 );
		} while( $errno );
		$result = "";
		fputs( $fp, $data );
		while( !feof( $fp ) )
			$result .= fgets( $fp, 128 );
		fclose( $fp );
		$this->setCurUrl( "http://" . $this->getServer() . $url );
		return $result;
	}

	function getUrl( $url ) {
		$url = "http://" . $this->getServer() . $url;
		if( $this->getDebug() ) echo "jm_sms: URL for GET: " . $url . "\n";
		$data =  "GET " . $url . " HTTP/1.0\r\n";
		$data .= $this->generateHttpHeader() . "\r\n";
		do {
			$errno = 0;
			$fp = @fsockopen( ( $this->getProxy()?$this->getProxyServer():$this->getServer() ), ( $this->getProxy()?$this->getProxyPort():80 ), $errno, $errstr, 30 );
			if( $this->getDebug() && $errno ) echo "jm_sms: Retrying Failed GET (" . $errno . "): " . $errstr . "\n";
			if( $errno ) sleep( 1 );
		} while( $errno );
		$result = "";
		fputs( $fp, $data );
		while( !feof( $fp ) )
			$result .= fgets( $fp, 128 );
		fclose( $fp );
		$this->setCurUrl( "http://" . $this->getServer() . $url );
		return $result;
	}

	function generateHttpHeader() {
		$data  = "Referer: " . $this->getCurUrl() . "\r\n";
		$data .= "User-Agent: Mozilla/4.72 [en] (X11; U; Linux 2.2.14-5.0 i586)\r\n";
		$data .= "Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, image/png, */*\r\n";
		$data .= "Accept-Encoding: gzip\r\n";
		$data .= "Accept-Language: en\r\n";
		$data .= "Accept-Charset: iso-8859-1,*,utf-8\r\n";
		$data .= "Cookie: " . $this->getCookies() . "\r\n";
		$data .= "Host: " . $this->getServer() . "\r\n";
		$data .= "Cache-Control: max-age=259200\r\n";
		$data .= "Connection: close\r\n";
		if( $this->getProxy() )
			$data .= "Proxy-Authorization: Basic " . base64_encode( $this->getProxyUser() . ":" . $this->getProxyPass() ) . "\r\n";
		return $data;
	}

	function parseRedirect( $HttpHeader ) {
		$headerParts = explode( "\r\n", $HttpHeader );
		$cookies = array();
		while( list( $key, $val ) = each( $headerParts ) ) {
			if( substr( $val, 0, 10 ) == "Location: " )
				$location = substr( $val, 10 );
			else if( substr( $val, 0, 12 ) == "Set-Cookie: " )
				$this->setCookies( substr( $val, 12 ) );
		}
		if( substr( $location, 0, 7 ) == "http://" ) {
			$location = substr( $location, 7 );
			$this->setServer( substr( $location, 0, strpos( $location, "/" ) ) );
			$location = substr( $location, strpos( $location, "/" ) );
		}
		if( $this->getDebug() ) echo "jm_sms: Found HTTP Redirect To: " . $location . "\n";
		return $location;
	}

	function login() {
		if( $this->getSmsAc() == FALSE )
			$command = "username=" . urlencode( $this->getUsername() ) . "&password=" . $this->getPassword() . "&email=++&joinusclick=no&returl=&x=48&y=36";
		else
			$command = "loginuserid=" . urlencode( $this->getUsername() ) . "&loginpassword=" . $this->getPassword();
		if( $this->getSmsAc() == FALSE )
			$result = $this->postUrl( "/session.asp", $command );
		else
			$result = $this->postUrl( "/login.asp", $command );
		$redirectUrl = $this->parseRedirect( $result );
		// check for incorrect login details
		if( strstr( $redirectUrl, "err=203" ) != "" ) {
			if( $this->getDebug() ) echo "jm_sms: Username " . $this->getUsername() . " unknown.\n";
			if( $this->nextLogin() ) {
				return $this->login();
			} else
				return FALSE;
		} else if( strstr( $redirectUrl, "err=204" ) != "" ) {
			if( $this->getDebug() ) echo "jm_sms: Password for " . $this->getUsername() . " incorrect.\n";
			if( $this->nextLogin() ) {
				return $this->login();
			} else
				return FALSE;
		}
		$this->getUrl( $redirectUrl );
		if( $redirectUrl == "/members/contacts/contacts.asp" ) {
			if( $this->getDebug() ) echo "jm_sms: Retrieved contacts.asp, now retrieving xsms.asp\n";
			$this->getUrl( "/sms/xsms.asp" );
		} else if( $redirectUrl == "mainframe.asp" ) {
			if( $this->getDebug() ) echo "jm_sms: Retrieved mainframe.asp, now retrieving d3fault.asp\n";
			$this->getUrl( "d3fault.asp" );
		}
		return TRUE;
	}

	function sendSMS( $gsmNumber, $signature, $message ) {
		if( $this->login() == FALSE ) {
			if( $this->getDebug() ) echo "jm_sms: Unable to log in.\n";
			return FALSE;
		}
		if( $this->getSmsAc() ) {
			$message = $message . " >> " . $signature;
			$checkString = "ShowMsgResults.asp";
			$quotaString = "Your daily messaging limit has been reached.";
			$command = "Gender2=F&AgeStart2=18&AgeEnd2=24&schooltype=college&mobilenumber=" . urlencode( $gsmNumber ) . "&message=" . urlencode( $message ) . "&sizebox=0&SendSMS=Send+SMS&EmailCopy=YesEmail&WhoSex=F&interest=&poll=poll1";
			$sendUrl = "/default2.asp";
		} else {
			$checkString = "message sent to ";
			$quotaString = "Daily message quota exceeded.";
			$command = "smsToCTs=&smsToNumbers=" . urlencode( $gsmNumber ) . "&smsMessage=" . urlencode( $message ) . "&smsSig=1&smsSigDyna=" . urlencode( $signature ) . "&msgCL=138&lenSSig=4&lenLSig=3&lenSysSig=11";
			$sendUrl = "/sms/xsms.asp";
		}
		$result = $this->postUrl( $sendUrl, $command );
		if( strstr( $result, $checkString ) ) {
			if( $this->getDebug() ) echo "jm_sms: SMS Message Sent Successfully\n";
			return TRUE;
		} else {
			if( $this->getDebug() ) echo "jm_sms: SMS Send Failed\n";
            echo $result;
			if( strstr( $result, $quotaString ) ) {
				if( $this->getDebug() ) echo "jm_sms: Quota for current user [" . $this->getUsername() . "] exceeded.\n";
				if( $this->nextLogin() ) {
					if( $this->getDebug() ) echo "jm_sms: Advancing to next user.\n";
					return $this->sendSMS( $gsmNumber, $signature, $message );
				} else {
					if( $this->getDebug() ) echo "jm_sms: No more configured users.\n";
					return FALSE;
				}
			}
			return FALSE;
		}
	}
}

function blocks_sms_block($row) {
global $smsphonenumber, $smsmessage, $smssignature;

    if (!pnSecAuthAction(0, 'SMSblock::', "$row[title]::", ACCESS_READ)) {
        return;
    }
# Enter your mtnsms.com login info here.
# If you don't have an account yet, just goto www.mtnsms.com and sign up.
$mtnsmsemail = "dctanner@xoasis.com";
$mtnsmspass =  "merlin";

$boxstuff = "";

if ((strlen($smsphonenumber) > 10) && (strlen($smsmessage) > 0))  {
    $jm_sms = new jm_sms( $mtnsmsemail, $mtnsmspass);

    // Proxy Server Details
    // $jm_sms->setProxyServer( "proxyserver" );
    // $jm_sms->setProxyPort( 81 );
    // $jm_sms->setProxyUser( "proxyusername" );
    // $jm_sms->setProxyPass( "proxypassword" );
    // $jm_sms->setProxy( TRUE );

    ob_implicit_flush( TRUE );
    if( $jm_sms->sendSMS( $smsphonenumber, $smssignature, $smsmessage ) )  {
        $boxstuff .= "<font class=\"pn-normal\"><b>SMS message sent!</b></font><br><br>";
    }
}

    $boxstuff .= "<form action=\"index.php\" method=\"post\">";
    $boxstuff .= "<center><font class=\"pn-normal\">Mobile number</font><br><font class=\"pn-sub\">(remember to include your country code!)</font><br>";
    $boxstuff .= "<input class=\"pn-text\" type=\"text\" name=\"smsphonenumber\" size=\"12\" maxlength=\"25\" value=\"+\"><br>";
    $boxstuff .= "<font class=\"pn-normal\">Message</font><br>";
    $boxstuff .= "<textarea class=\"pn-text\" name=\"smsmessage\" rows=\"4\" cols=\"16\"></textarea><br>";
    $boxstuff .= "<center><font class=\"pn-normal\">Your name</font><br>";
    $boxstuff .= "<input class=\"pn-text\" type=\"text\" name=\"smssignature\" size=\"12\" maxlength=\"25\"><br>";
    $boxstuff .= "<input class=\"pn-button\" type=\"submit\" value=\"Send SMS!\"></font></center></form>";
    $row['content'] = $boxstuff;
    return themesideblock($row);
}
?>