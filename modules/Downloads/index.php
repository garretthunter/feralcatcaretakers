<?php // $Id: index.php,v 1.8 2002/11/27 22:27:51 larsneo Exp $ $Name:  $
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
// Original Author of file: Francisco Burzi
// Purpose: Download tracker/organizer
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE"))
{
    die ("You can't access this file directly...");
}

$ModName = basename( dirname( __FILE__ ) );

// cocomp 2002/07/13 change to false if you don't want the rating stars to
// appear
$download_show_star = true;

$modurl="modules.php?op=modload&amp;name=$ModName&amp;file=index"; //Shorten url text

modules_get_language();

include_once ("modules/$ModName/dl-util.php");
include_once ("modules/$ModName/dl-categories.php");
include_once ("modules/$ModName/dl-navigation.php");

/**
 * Switch on $req
 * load the appropriate module, and call the appropriate function.
 */
$req = pnVarCleanFromInput('req');

if(empty($req)) {
	$req = '';
}

switch($req) {

    case "menu":
      menu($maindownload);
    break;

    // Menu downloads
    case "AddDownload":
      include_once ("modules/$ModName/dl-adddownload.php");
      AddDownload();
    break;

    case "NewDownloads":
      include_once ("modules/$ModName/dl-newdownloads.php");

      // E_ALL complains if we pass an empty argument, lets settle on a week for a starter
      // (AV) think this $var should initially come from the config system

      if(!isset($newdownloadshowdays)) {
		$newdownloadshowdays = 7;
      }
      NewDownloads($newdownloadshowdays);
    break;

    case "NewDownloadsDate":
      include_once ("modules/$ModName/dl-newdownloads.php");
      NewDownloadsDate($selectdate);
    break;
    
    case "CoolSize":
        $size = pnVarCleanFromInput('size');
        if(!isset($size) || !is_numeric($size)) {
  	      $min = 0;
        }
		CoolSize($size);
    break;
        
    case "TopRated":
      include_once ("modules/$ModName/dl-toprated.php");

      if(!isset($ratenum)) {
	    $ratenum = '';
      }

      if(!isset($ratetype)) {
	    $ratetype = '';
      }
      
      TopRated($ratenum, $ratetype);
    break;

    case "MostPopular":
      include_once ("modules/$ModName/dl-mostpopular.php");

      // E_ALL warnings here again...
      // initial $vars shouldn't be empty, but come from the module or system config
      if(!isset($ratenum)) {
	  $ratenum = '';
      }

      if(!isset($ratetype)) {
	  $ratetype = '';
      }
      MostPopular($ratenum, $ratetype);
    break;

    // currently not implemented. i just left this in. anyone plug it in?
    case "Randomdownload":
       include_once ("modules/$ModName/dl-randomdownload.php");
       Randomdownload();
    break;

    case "search":
     include_once ("modules/$ModName/dl-search.php");
	 if (!isset($min) || !is_numeric($min)) $min=0;
	 if (!isset($orderby)) $orderby="titleA";
	 if (!isset($show)) $show="";
	 $query = pnVarCleanFromInput('query');
     search($query, $min, $orderby, $show);
    break;

    //End of navigation Menu downloads
    //Display a download - called from index
    case "viewdownload":
      include_once ("modules/$ModName/dl-viewdownload.php");

      if(!isset($min) || !is_numeric($min)) {
	  $min = 0;
      }

	  if(!isset($orderby)) {
	  $orderby = 0;
      }

      if(!isset($show)) {
	  $show = '';
      }
      viewdownload($cid, $min, $orderby, $show);
    break;

    case "viewsdownload":
      include_once ("modules/$ModName/dl-viewdownload.php");

      if(!isset($min) || !is_numeric($min)) {
	  $min = 0;
      }

      if(!isset($show)) {
	  $show = '';
      }
      viewsdownload($sid, $min, $orderby, $show);
    break;

    case "brokendownload":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      brokendownload($lid);
    break;

    case "modifydownloadrequest":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      modifydownloadrequest($lid);
    break;

    case "modifydownloadrequestS":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      modifydownloadrequestS($lid, $cat, $title, $url, $description, $modifysubmitter, $aname, $email, $filesize, $version, $homepage);
    break;

    case "brokendownloadS":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      brokendownloadS($lid, $modifysubmitter);
    break;

    case "getit":
        visit($lid);
    break;

    case "Add":
      include_once ("modules/$ModName/dl-adddownload.php");
      Add();
    break;

    case "rateinfo":
      include_once ("modules/$ModName/dl-rating.php");
      rateinfo($lid, $title);
    break;

    case "ratedownload":
      include_once ("modules/$ModName/dl-rating.php");
      ratedownload($lid, $ttitle);
    break;

    case "addrating":
      include_once ("modules/$ModName/dl-rating.php");
      addrating($ratinglid, $ratinguser, $rating, $ratinghost_name, $ratingcomments);
    break;

    case "viewdownloadcomments":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      viewdownloadcomments($lid, $ttitle);
    break;

    case "outsidedownloadsetup":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      outsidedownloadsetup($lid);
    break;

    case "viewdownloaddetails":
      include_once ("modules/$ModName/dl-downloaddetails.php");
      viewdownloaddetails($lid, $ttitle);
    break;

    case "viewdownloadeditorial":
      include_once ("modules/$ModName/dl-downloadeditorial.php");
      viewdownloadeditorial($lid, $ttitle);
    break;

    default:
      index();
}
?>