<?php
// File: $Id: legacy.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
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
// Original Author of file: Francisco Burzi
// Purpose of file: Legacy code still around for various old modules
// ----------------------------------------------------------------------

// Recreate $pnconfig['nukeurl']
global $pnconfig;
$pnconfig['nukeurl'] = pnGetBaseURI();

global $mainfile;
$mainfile = 1;
function delQuotes($string){
    // No recursive function to add quote to an HTML tag if needed
    // and delete duplicate spaces between attribs.
    $tmp="";    # string buffer
    $result=""; # result string
    $i=0;
    $attrib=-1; # Are us in an HTML attrib ?   -1: no attrib   0: name of the attrib   1: value of the atrib
    $quote=0;   # Is a string quote delimited opened ? 0=no, 1=yes
    $len = strlen($string);
    while ($i<$len) {
    switch($string[$i]) { # What car is it in the buffer ?
        case "\"": #"       # a quote.
        if ($quote==0) {
            $quote=1;
        } else {
            $quote=0;
            if (($attrib>0) && ($tmp != "")) { $result .= "=\"$tmp\""; }
            $tmp="";
            $attrib=-1;
        }
        break;
        case "=":           # an equal - attrib delimiter
        if ($quote==0) {  # Is it found in a string ?
            $attrib=1;
            if ($tmp!="") $result.=" $tmp";
            $tmp="";
        } else $tmp .= '=';
        break;
        case " ":           # a blank ?
        if ($attrib>0) {  # add it to the string, if one opened.
            $tmp .= $string[$i];
        }
        break;
        default:            # Other
        if ($attrib<0)    # If we weren't in an attrib, set attrib to 0
        $attrib=0;
        $tmp .= $string[$i];
        break;
    }
    $i++;
    }
    if (($quote!=0) && ($tmp != "")) {
    if ($attrib==1) $result .= "=";
    /* If it is the value of an atrib, add the '=' */
    $result .= "\"$tmp\"";  /* Add quote if needed (the reason of the function ;-) */
    }
    return $result;
}

/**
 * Fixes quoting on a string
 *
 * This function replaces all single single quotes with double single quotes
 * (' becomes '') and all occurrences of \' with '.
 *
 * @param $what string The string to be fixed
 * @return string The fixed string
 * @author ?
 */

function FixQuotes ($what = "") {
    $what = ereg_replace("'","''",$what);
    while (eregi("\\\\'", $what)) {
        $what = ereg_replace("\\\\'","'",$what);
    }
    return $what;
}

function check_html ($str, $strip = '') {
    // The core of this code has been lifted from phpslash
    // which is licenced under the GPL.

    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    
    if ($strip == "nohtml")
        $AllowableHTML=array('');
    $str = stripslashes($str);
    $str = eregi_replace("<[[:space:]]*([^>]*)[[:space:]]*>",
                         '<\\1>', $str);
// Delete all spaces from html tags .
    $str = eregi_replace("<a[^>]*href[[:space:]]*=[[:space:]]*\"?[[:space:]]*([^\" >]*)[[:space:]]*\"?[^>]*>",
                         '<a href="\\1">', $str); # "
// Delete all attribs from Anchor, except an href, double quoted.
    $tmp = "";
    while (ereg("<(/?[[:alpha:]]*)[[:space:]]*([^>]*)>",$str,$reg)) {
        $i = strpos($str,$reg[0]);
        $l = strlen($reg[0]);
        if ($reg[1][0] == "/") $tag = strtolower(substr($reg[1],1));
        else $tag = strtolower($reg[1]);
        if (isset($AllowableHTML[$tag])) {
            if ($a=$AllowableHTML[$tag])
            if ($reg[1][0] == "/") $tag = "</$tag>";
            elseif (($a == 1) || ($reg[2] == "")) $tag = "<$tag>";
            else {
              # Place here the double quote fix function.
              $attrb_list=delQuotes($reg[2]);
              $tag = "<$tag" . $attrb_list . ">";
            } # Attribs in tag allowed
        } else $tag = "";
        $tmp .= substr($str,0,$i) . $tag;
        $str = substr($str,$i+$l);
    }
    $str = $tmp . $str;
    return $str;
    exit;
    // Squash PHP tags unconditionally
    $str = ereg_replace("<\?","",$str);
    return $str;
}

function filter_text($Message, $strip="") {
    global $EditedMessage;
    check_words($Message);
    $EditedMessage=check_html($EditedMessage, $strip);
    return ($EditedMessage);
}

/**
 * formatting stories
 */

function formatTimestamp($time) {
    global $datetime;
    
    setlocale (LC_TIME, pnConfigGetVar('locale'));
    // Below ereg commented out 07-08-2001:Alarion - less strict ereg thanks to "Joe"
    //ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $time, $datetime);
    ereg ("([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)", $time, $datetime);

    // 07-07-2001:Alarion - For the time being, I added an ereg_replace to strip out
    // the timezone until I get a function in to replace the server timezone with the users timezone
    $datetime = strftime("".ereg_replace("%Z", "",_DATESTRING)."", mktime($datetime[4],$datetime[5],$datetime[6],$datetime[2],$datetime[3],$datetime[1]));
    $datetime = ucfirst($datetime);
    return($datetime);
}


/**
 * include_once replacement
 *
 * Works basicly like include_once() (except not
 * include() aware, I'm not sure what array name
 * they use). Needed for older PHP4 installs.
 *
 * @param $f string The file/path to include
 * @return false if file was already included. true if first include
 * @author Patrick Kellum <webmaster@ctarl-ctarl.com>
 */
if (!function_exists('pninclude_once')) {
function pninclude_once($f)
{
//    static $postnuke_include_files;
//    if (!empty($postnuke_include_files[$f]))
//    {
//        return false;
//    }
//    include $f;
    include_once $f; // new
//    $postnuke_include_files[$f] = true;
    return true;
}
}

function check_words($Message) {
    global $EditedMessage;

    $CensorMode = pnConfigGetVar('CensorMode');
    $CensorList = pnConfigGetVar('CensorList');
    $CensorReplace = pnConfigGetVar('CensorReplace');

    $EditedMessage = $Message;
    if ($CensorMode != 0) {

    if (is_array($CensorList)) {
        $Replace = $CensorReplace;
        if ($CensorMode == 1) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("$CensorList[$i]([^a-zA-Z0-9])","$Replace\\1",$EditedMessage);
        }
        } elseif ($CensorMode == 2) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("(^|[^[:alnum:]])$CensorList[$i]","\\1$Replace",$EditedMessage);
        }
        } elseif ($CensorMode == 3) {
        for ($i = 0; $i < count($CensorList); $i++) {
            $EditedMessage = eregi_replace("$CensorList[$i]","$Replace",$EditedMessage);
        }
        }
    }
    }
    return ($EditedMessage);
}

/**
 * cross site scripting check
 */
function csssafe($checkArg = "op", $checkReferer = true)
{
    return true;
}

function myTextForm($url , $value , $useTable = false , $extraname="postnuke")
{
    $form = "";
    $form .= "<form action=\"$url\" method=\"post\">";
    if ($useTable){
        $form .= "<table border=\"0\" width=\"100%\" align=\"center\"><tr><td>\n";
    }
    $form .= "<input type=\"submit\" value=\"$value\" class=\"pn-normal\" style=\"text-align:center\">";
    $form .= "<input type=\"hidden\" name=\"$extraname\" value=\"$extraname\"></form>\n";
    if ($useTable){
        $form .= "</td></tr></table>\n";
    }
    return $form;
}

/**
 *  Error message due a ADODB SQL error and die
 */
function PN_DBMsgError($db='',$prg='',$line=0,$message='Error accesing to the database')
{
    $lcmessage = $message . "<br>" .
                 "Program: " . $prg . " - " . "Line N.: " . $line . "<br>" .
                 "Database: " . $db->database . "<br> ";

    if($db->ErrorNo()<>0) {
        $lcmessage .= "Error (" . $db->ErrorNo() . ") : " . $db->ErrorMsg() . "<br>";
    }
    die($lcmessage);
}

/*
 * Timezone information
 */
$tzinfo = array('0'    => '(GMT -12:00 hours) Eniwetok, Kwajalein',
                '1'    => '(GMT -11:00 hours) Midway Island, Samoa',
                '2'    => '(GMT -10:00 hours) Hawaii',
                '3'    => '(GMT -9:00 hours) Alaska',
                '4'    => '(GMT -8:00 hours) Pacific Time (US & Canada)',
                '5'    => '(GMT -7:00 hours) Mountain Time (US & Canada)',
                '6'    => '(GMT -6:00 hours) Central Time (US & Canada), Mexico City',
                '7'    => '(GMT -5:00 hours) Eastern Time (US & Canada), Bogota, Lima, Quito',
                '8'    => '(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz',
                '8.5'  => '(GMT -3:30 hours) Newfoundland',
                '9'    => '(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown',
                '10'   => '(GMT -2:00 hours) Mid-Atlantic',
                '11'   => '(GMT -1:00 hours) Azores, Cape Verde Islands',
                '12'   => '(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia',
                '13'   => '(GMT +1:00 hours) CET(Central Europe Time), Brussels, Copenhagen, Madrid, Paris',
                '14'   => '(GMT +2:00 hours) EET(Eastern Europe Time), Kaliningrad, South Africa',
                '15'   => '(GMT +3:00 hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg',
                '15.5' => '(GMT +3:30 hours) Tehran',
                '16'   => '(GMT +4:00 hours) Abu Dhabi, Muscat, Baku, Tbilisi',
                '16.5' => '(GMT +4:30 hours) Kabul',
                '17'   => '(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent',
                '17.5' => '(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi',
                '18'   => '(GMT +6:00 hours) Almaty, Dhaka, Colombo',
                '19'   => '(GMT +7:00 hours) Bangkok, Hanoi, Jakarta',
                '20'   => '(GMT +8:00 hours) Beijing, Perth, Singapore, Hong Kong, Chongqing, Urumqi, Taipei',
                '21'   => '(GMT +9:00 hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
                '21.5' => '(GMT +9:30 hours) Adelaide, Darwin',
                '22'   => '(GMT +10:00 hours) EAST(East Australian Standard)',
                '23'   => '(GMT +11:00 hours) Magadan, Solomon Islands, New Caledonia',
                '24'   => '(GMT +12:00 hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island');

?>