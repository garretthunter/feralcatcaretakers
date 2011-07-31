<?php
// File: $Id: advblocks.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
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
// Original Author of file:  Patrick Kellum <webmaster@quahog-library.com>
// Purpose of file: Display the side blocks on the page
// ----------------------------------------------------------------------
// Advanced Blocks System
//
// Copyright (c) 2001 Patrick Kellum (webmaster@quahog-library.com)
// http://ctarl-ctarl.com/
//
// Based in part of the blocks system in PHP-Nuke
// Copyright (c) 2001 by Francisco Burzi (fbc@mandrakesoft.com)
// http://phpnuke.org/
// ----------------------------------------------------------------------
global $dbg_starttime;

/**
 * initialise time to render
 */
$mtime = explode(" ",microtime());
$dbg_starttime = $mtime[1] + $mtime[0];
$debug_sqlcalls = 0;

global $blocks_modules;
$blocks_modules = array();

/** 
 * change the function name so themes remain compatable 
 */
function blocks($side)
{
    global $blocks_modules, $blocks_side;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $currentlang = pnUserGetLang();

    if (pnConfigGetVar('multilingual') == 1) {
        $column = &$pntable['blocks_column'];
        $querylang = "AND ($column[blanguage]='$currentlang' OR $column[blanguage]='')";
    } else {
        $querylang = '';
    }
    $side = strtolower($side[0]);
    $block_side = $side;
    $column = &$pntable['blocks_column'];
    $result = $dbconn->Execute("SELECT $column[bid] as bid, $column[bkey] as bkey, $column[mid] as mid, $column[title] as title, $column[content] as content, $column[url] as url, $column[position] as position, $column[weight] as weight, $column[active] as active, $column[refresh] as refresh, $column[last_update] AS unix_update, $column[blanguage] as blanguage FROM $pntable[blocks] WHERE $column[position]='".pnVarPrepForStore($side)."' AND $column[active]=1 $querylang ORDER BY $column[weight]");
    while(!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $row['unix_update']=$result->UnixTimeStamp($row['unix_update']);

        $modinfo = pnModGetInfo($row['mid']);
        if (!$modinfo) {
            // Assume core
            $modinfo['name'] = 'Core';
        }
        echo pnBlockShow($modinfo['name'], $row['bkey'], $row);

        $result->MoveNext();
    }
}

/**
 * show a block
 * @param the module name
 * @param the name of the block
 * @param block information parameters
 */
function pnBlockShow($modname, $block, $blockinfo=array())
{
    global $blocks_modules;

    pnBlockLoad($modname, $block);

    $displayfunc = "{$modname}_{$block}block_display";

    if (function_exists($displayfunc)) {
        // New-style blocks
        return $displayfunc($blockinfo);
    } else {
        // Old-style blocks
        if (isset($blocks_modules[$block]['func_display'])) {
            return $blocks_modules[$block]['func_display']($blockinfo);
        } else {
            $blockinfo['title'] = "Block Type $block Not Found";
            $blockinfo['content'] = "The block type $block doesn't seem to exist.  Please check your includes/blocks/ directory.";
            return themesideblock($blockinfo);
        }
    }
}

/**
 * load a block
 * @param the module name
 * @param the name of the block
 */
function pnBlockLoad($modname, $block)
{
    global $blocks_modules;

    static $loaded = array();

    if (isset($loaded["$modname$block"])) {
        return true;
    }
    if ((empty($modname)) || ($modname == 'Core')) {
        $modname = 'Core';
        $moddir = 'includes/blocks';
        $langdir = 'includes/language/blocks';
    } else {
        $modinfo = pnModGetInfo(pnModGetIdFromName($modname));
        $moddir = 'modules/' . pnVarPrepForOS($modinfo['directory']) . '/pnblocks';
        $langdir = 'modules/' . pnVarPrepForOS($modinfo['directory']) . '/pnlang';
    }

    // Load the block
    $incfile = $block . ".php";;
    $filepath = $moddir . '/' . pnVarPrepForOS($incfile);
    if (!file_exists($filepath)) {
        return false;
    }
    include_once $filepath;
    $loaded["$modname$block"] = 1;

    // Load the block language files
    $currentlangfile = $langdir . '/' . pnVarPrepForOS(pnUserGetLang()) . '/' . pnVarPrepForOS($incfile);
    $defaultlangfile = $langdir . '/' . pnVarPrepForOS(pnConfigGetVar('language')) . '/' . pnVarPrepForOS($incfile);
    if (file_exists($currentlangfile)) {
        include $currentlangfile;
    } elseif (file_exists($defaultlangfile)) {
        include "$defaultlangfile";
    }

    // Initialise block if required (new-style)
    $initfunc = "{$modname}_{$block}block_init";
    if (function_exists($initfunc)) {
        $initfunc();
    }

    return true;
}

/**
 * load all blocks
 */
function pnBlockLoadAll()
{
    // Load core and old-style blocks
    global $blocks_modules;
    $dib = opendir('includes/blocks/');
    while($f = readdir($dib)) {
        if (preg_match('/\.php$/', $f)) {
            $block= preg_replace('/\.php$/', '', $f);
            if (!pnBlockLoad('Core', $block)) {
                // Block load failed
                return false;
            }
            if (!isset($blocks_modules[$block]['module'])) {
                $blocks_modules[$block]['bkey'] = $block;
                $blocks_modules[$block]['module'] = 'Core';
                $blocks_modules[$block]['mid'] = 0;
            }
        }
    }
    closedir($dib);

    // Load new-style blocks
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $sql = "SELECT ".$modulescolumn['name'].",".
                   $modulescolumn['directory'].",".
                   $modulescolumn['id']."
            FROM $modulestable";
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    while (list($name, $directory, $mid) = $result->fields) {
        $result->MoveNext();

        $blockdir = 'modules/' . pnVarPrepForOS($directory) . '/pnblocks';
        if (!@is_dir($blockdir)) {
            continue;
        }
        $dib = opendir($blockdir);
        while($f = readdir($dib)) {
            if (preg_match('/\.php$/', $f)) {
                $block= preg_replace('/\.php$/', '', $f);
                if (!pnBlockLoad($name, $block)) {
                    // Block load failed
                    return false;
                }
                // Get info on the block
                $usname = preg_replace('/ /', '_', $name);
                $infofunc = $usname . '_' . $block . 'block_info';
                if (function_exists($infofunc)) {
                    $blocks_modules["$name$block"] = $infofunc();
                    if (!isset($blocks_modules["$name$block"]['module'])) {
                        $blocks_modules["$name$block"]['module'] = $name;
                    }
                    $blocks_modules["$name$block"]['bkey'] = $block;
                    $blocks_modules["$name$block"]['mid'] = $mid;
                } else {
                    // Might be old-style block in new place - sigh
                    if (!empty($blocks_modules[$block])) {
                        $blocks_modules["$name$block"] = $blocks_modules[$block];
                        unset($blocks_modules[$block]);
                        if (!isset($blocks_modules["$name$block"]['module'])) {
                            $blocks_modules["$name$block"]['module'] = $name;
                        }
                        $blocks_modules["$name$block"]['bkey'] = $block;
                        $blocks_modules["$name$block"]['mid'] = $mid;
                    }
                }
            }
        }
    }
    $result->Close();
    // Return information gathered
    return $blocks_modules;
}

/**
 * extract an array of config variables out of the content field of a
 block
 * @param the content from the db
 */
function pnBlockVarsFromContent($content)
{

    if (preg_match('/;}?$/', $content)) {
        // Serialised content
        return (unserialize($content));
    }
    // Unserialised content
    $links = explode("\n", $content);
    $vars = array();
    foreach ($links as $link) {
        $link = trim($link);
        if ($link) {
            $var = explode(":=", $link);
            if (isset($var[1])) {
                $vars[$var[0]] = $var[1];
            }
        }
    }
    return($vars);
}

/**
 * put an array of config variables in the content field of a block
 * @param the config vars array, in key->value form
 */
function pnBlockVarsToContent($vars)
{
    return (serialize($vars));
}

// adapter function for themesidebox function in old themes
function themesideblock($row)
{
    global $postnuke_theme,
	   $pntheme;

    if(!isset($row['bid'])) {
	$row['bid'] = '';
    }
    if(!isset($row['title'])) {
	$row['title'] = '';
    }
    // check for collapseable menus being enabled.
    if (pnModGetVar('Blocks', 'collapseable') == 1) {    
	if (pnUserLoggedIn()) {
	    if (checkuserblock($row)=='1') {
		if (!empty($row['title'])) {
		    $row['title'] .=" <a href=\"modules.php?op=modload&name=Blocks&file=index&req=ChangeStatus&bid=$row[bid]&amp;authid=".pnSecGenAuthKey()."\"><img src=\"images/global/upb.gif\" border=\"0\" alt=\"\"></a>";
		}
	    } else {
		$row['content'] ='';
		if (!empty($row['title'])) {
		    $row['title'] .=" <a href=\"modules.php?op=modload&name=Blocks&file=index&req=ChangeStatus&bid=$row[bid]&amp;authid=".pnSecGenAuthKey()."\"><img src=\"images/global/downb.gif\" border=\"0\" alt=\"\"></a>";
		}
	    }
	}
    }
    // end collapseable menu config
    if ($postnuke_theme || $pntheme['support_blocks2'])
    {
        return themesidebox($row);
    } else {
        return themesidebox($row['title'], $row['content']);
    }
}

function checkuserblock($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable =pnDBGetTables();


    if(!isset($row['bid'])) {
		$row['bid'] = '';
	    }
    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');

        $column = &$pntable['userblocks_column'];
        $sql="SELECT $column[active] FROM ".$pntable['userblocks']
		." WHERE ". $column['bid']. "='".pnVarPrepForStore($row['bid'])
		."' AND ".$column['uid']."=".pnVarPrepForStore($uid);
        $result = $dbconn->Execute($sql);
        if ($result === false) {
            PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error <br>$sql");
        }
        if($result->EOF) {
            $uid = pnVarPrepForStore($uid);
            $row['bid'] = pnVarPrepForStore($row['bid']);
            $sql="INSERT INTO $pntable[userblocks] ($column[uid], $column[bid], $column[active]) VALUES (".pnVarPrepForStore($uid).", '$row[bid]', '1')";
            $result = $dbconn->Execute($sql);
            if ($result === false) {
                PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error <br>$sql");
            }
            return true;
        } else {
            list($active)=$result->fields;
            return $active;
        }
    } else {
        return false;
    }
}

/**
 * get block information
 * @param bid the block id
 * @returns array
 * @return array of block information
 */
function pnBlockGetInfo($bid)
{
    list ($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $blockstable = $pntable['blocks'];
    $blockscolumn = &$pntable['blocks_column'];
    $sql = "SELECT $blockscolumn[bkey],
                   $blockscolumn[title],
                   $blockscolumn[content],
                   $blockscolumn[url],
                   $blockscolumn[position],
                   $blockscolumn[weight],
                   $blockscolumn[active],
                   $blockscolumn[refresh],
                   $blockscolumn[last_update],
                   $blockscolumn[blanguage],
                   $blockscolumn[mid]
            FROM $blockstable
            WHERE $blockscolumn[bid] = " . pnVarPrepForStore($bid);
    $result = $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }
    if ($result->EOF) {
        return false;
    }
    list($resarray['bkey'],
         $resarray['title'],
         $resarray['content'],
         $resarray['url'],
         $resarray['position'],
         $resarray['weight'],
         $resarray['active'],
         $resarray['refresh'],
         $resarray['last_update'],
         $resarray['language'],
         $resarray['mid']) = $result->fields;
    $result->Close();

    return $resarray;
}
?>