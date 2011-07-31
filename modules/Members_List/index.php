<?php 
// $Id: index.php,v 1.19 2003/02/28 00:33:45 tanis Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on Bjorn Sodergrens MyPHPortal Modified Member List.
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// Some code taken from MemberList coded by Paul Joseph Thompson
// of www.slug.okstate.edu
// In memoriam of Members List War ;)
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
// Purpose of file: Displays member listings
// ----------------------------------------------------------------------
// Updated: Alexander Graef aka MagicX
// ----------------------------------------------------------------------
if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

$ModName = basename( dirname( __FILE__ ) );

modules_get_language();
function getnumber($get)
{
	$a = 0;
	$dcolor_A = "".$GLOBALS['bgcolor3']."";
    $dcolor_B = "".$GLOBALS['bgcolor1']."";

        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();

            $sessioninfocolumn = &$pntable['session_info_column'];
            $sessioninfotable = $pntable['session_info'];

            $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);

            // Get list of users on-line
            $sql = "SELECT DISTINCT $sessioninfocolumn[uid]
                    FROM $sessioninfotable
                    WHERE $sessioninfocolumn[uid] != 0
                    and $sessioninfocolumn[lastused] > $activetime
                   GROUP BY $sessioninfocolumn[uid]";
            $result3 = $dbconn->Execute($sql);
                        $numusers = $result3->RecordCount();

            $unames = array();
            for (; !$result3->EOF; $result3->MoveNext()) {
                $unames[] = pnUserGetVar('uname', $result3->fields[0]);
            }
            $result3->Close();
     $query = "SELECT count( 1 )
              FROM $sessioninfotable
              WHERE $sessioninfocolumn[lastused] > $activetime AND $sessioninfocolumn[uid] = '0'
                          GROUP BY $sessioninfocolumn[ipaddr]
                         ";
    $result = $dbconn->Execute($query);
        $numguests = $result->RecordCount();

    for (; !$result->EOF; $result->MoveNext()) {
        list($type, $num) = $result->fields;
        if ($type == 0) {
            $numguests = $num;
        } else {
            $numusers++;
        }
    }
    $result->Close();

    // Pluralise
    if ($numguests == 1) {
        $guests = _GUEST;
    } else {
        $guests = _GUESTS;
    }
    if ($numusers == 1) {
        $users = _MEMBER;
    } else {
        $users = _MEMBERS;
    }
            // Sort by username
            sort($unames);
            reset($unames);
            $numregusers = count($unames);
        if($get=='number'){
        echo"<center>"._CURRENTLY." ".pnVarPrepForDisplay($numguests)."
".pnVarPrepForDisplay($guests)." "._AND." ".pnVarPrepForDisplay($numregusers)." ".pnVarPrepForDisplay($users)."
"._ONLINE."</center>";
}
if($get=='online'){
echo"<a name=online><br><table align=\"center\" width=\"95%\" cellspacing=\"1\" cellpadding=\"5\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><tr><td bgcolor=\"".$GLOBALS['bgcolor1']."\">";

                 foreach ($unames as $uname) {
                $myquery = buildSimpleQuery('users', array('uid', 'name', 'uname', 'femail', 'url'), 'pn_uname =$uname');
        $result = $dbconn->Execute($myquery);
					
                           $dcolor = ($a == 0 ? $dcolor_A : $dcolor_B);
               echo "<b>+ ".pnVarPrepForDisplay($uname)."&nbsp;</font></b> ";



                    $a = ($dcolor == $dcolor_A ? 1 : 0);
                         }
                         echo"</td></tr></table>";
                         }
                         if($get=='countnum'){
                         echo $numusers;
                         }
}


function search()
{
        echo"
<form method=\"POST\" action=\"modules.php\">
  <table border=\"0\" cellspacing=\"2\" width=\"100%\">
           <tr>
          <td valign=\"top\" align=center>
        <b>"._SEARCH.":</b> <input type=\"text\" name=\"letter\" size=\"35\"><br>
                <input type=\"hidden\" name=\"op\" value=\"modload\">
                <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
                <input type=\"hidden\" name=\"file\" value=\"index\">
                <input type=\"radio\" name=\"sorting\" value=\"uname\" checked>"._MNICKNAME."
        <input type=\"radio\" name=\"sorting\" value=\"name\">"._MREALNAME."
        <input type=\"radio\" name=\"sorting\" value=\"url\">"._MURL."
	<input type=\"hidden\" name=\"authid\" value=\"".pnSecGenAuthKey()."\">
        <br>   <input type=\"submit\" value=\""._SUBMIT."\"></td>
        </tr>
         </table>
</form>";

}
function alpha()
{
    // Creates the list of letters and makes them a link.

        $alphabet = array (_ALL, "A","B","C","D","E","F","G","H","I","J","K","L","M",
                            "N","O","P","Q","R","S","T","U","V","W","X","Y","Z",_OTHER);
        $num = count($alphabet) - 1;
	$authid = pnSecGenAuthKey();
        echo "<center>[ ";
        $counter = 0;
        while(list(, $ltr) = each($alphabet)) {
            echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=".pnVarPrepForDIsplay($ltr)."&amp;sortby=$GLOBALS[sortby]&amp;authid=$authid\">".pnVarPrepForDIsplay($ltr)."</a>";
            if ($counter == round($num/2)) {
                echo " ]\n<br>\n[ ";
            } elseif($counter != $num) {
                echo "&nbsp;|&nbsp;\n";
            }
            $counter++;
        }
        echo " ]\n</center>\n<br>\n";
}

include 'header.php';
if (!pnSecAuthAction(0, 'Members_List::', '::', ACCESS_READ)) {
	echo _BADAUTHKEY;
        include 'footer.php';
        exit;
}

//Security Fix - Cleaning the search input
$sortby = pnVarCleanFromInput('sortby');
$pagesize = 20;
if(empty($sortby)){
	$sortby="uname";
} else {
	// Confirm authorisation code
/*	if (!pnSecConfirmAuthKey()) {
		echo _BADAUTHKEY;
		include 'footer.php';
		exit;
	}*/
}

if (!isset($letter)) {
    $letter = "A";
}

if (!isset($page)) {
    $page = 1;
}


 // TODO
 // All of the code from here to around line 125 will be optimized a little later
 // This is the header section that displays the last registered and who's logged in and whatnot
list($dbconn) = pnDBGetConn();
$pntable = pnDBGetTables();

$column = &$pntable['users_column'];
$myquery = buildSimpleQuery ('users', array ('uname'), '', "$column[uid] DESC", 1);
$result = $dbconn->Execute($myquery);
list($lastuser) = $result->fields;

echo "\n\n<!-- MEMBERS LIST -->\n\n";

        echo "<a name=top><center><img src=\"modules/$GLOBALS[ModName]/images/logo.gif\" border=\"0\"></center><br>\n";
        $result = $dbconn->Execute("SELECT COUNT(*) FROM $pntable[users] where pn_uname NOT LIKE 'Anonymous'");
        list($numrows) = $result->fields;
        $result->Close();

        if(pnSecAuthAction(0, 'Users::', '::', ACCESS_COMMENT)){


            echo "<font class=\"pn-normal\"><center>[ <b>"._NAVREG.": </b>".pnVarPrepForDIsplay($numrows)." | <b>"._NAVONLINE.": </b>";
                        getnumber('countnum');
                         echo" |\n";
            echo "<b>"._NAVNEW.":</b> ".pnVarPrepForDIsplay($lastuser)." ]</font></center> <br>";
        }

        alpha();


// end of top memberlist section thingie
// This starts the beef...

        $min = $pagesize * ($page - 1); // This is where we start our record set from
        $max = $pagesize; // This is how many rows to select
        $column = &$pntable['users_column'];
        $count = "SELECT COUNT($column[uid]) FROM $pntable[users] ";

                //Security Fix - Cleaning the search input
                $sorting         = pnVarCleanFromInput('sorting');


                if (!$sorting){
        // Count all the users in the db..
        if (($letter != _OTHER) AND ($letter != _ALL)) {
            // are we listing all or "other" ?
            $where = "UPPER($column[uname]) LIKE UPPER('".pnVarPrepForStore($letter)."%')AND $column[uname] NOT LIKE 'Anonymous' ";
            // I guess we are not..
        } else if (($letter == _OTHER) AND ($letter != _ALL)) {
            // But other is numbers ?
            $where = "($column[uname] LIKE '0%'
                          OR $column[uname] LIKE '1%'
                          OR $column[uname] LIKE '2%'
                          OR $column[uname] LIKE '3%'
                          OR $column[uname] LIKE '4%'
                          OR $column[uname] LIKE '5%'
                          OR $column[uname] LIKE '6%'
                          OR $column[uname] LIKE '7%'
                          OR $column[uname] LIKE '8%'
                          OR $column[uname] LIKE '9%'
                          OR $column[uname] LIKE '-%'
                          OR $column[uname] LIKE '.%'
                          OR $column[uname] LIKE '@%'
                          OR $column[uname] LIKE '$%')";

            // fifers: while this is not the most eloquent solution, it is
            // cross database compatible.  We could do an if dbtype is mysql
            // then do the regexp.  consider for performance enhancement.
            //
            // "WHERE $column[uname] REGEXP \"^\[1-9]\" "
            // REGEX :D, although i think its MySQL only
            // Will have to change this later.
            // if you know a better way to match only the first char
            // to be a number in uname, please change it and email
            // sweede@gallatinriver.net the correction
            // or go to post-nuke project page and post
            // your correction there. Thanks, Bjorn.
        } else { // or we are unknown or all..
             $where = " $column[uname] NOT LIKE 'Anonymous'"; // this is to get rid of anoying "undefinied variable" message
        }
                }
                else
                {
                                $where = "$column[$sorting] LIKE '%".pnVarPrepForStore($letter)."%'";
                }
      search();

 $sort = "$sortby ASC"; //sorty by .....
        // This is where we get our limit'd result set.
        $myquery = buildSimpleQuery('users', array('uid', 'name', 'uname', 'femail', 'url', 'user_avatar','user_icq'), $where, pnVarPrepForStore($sort), $max, $min); //select our data

        $result = $dbconn->Execute($myquery);
        if ($result === false) {
            error_log("Error: " . $dbconn->ErrorNo() . ": " . $dbconn->ErrorMsg());
           PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
        }
        $num_rows_per_order = $result->PO_RecordCount();
        echo "<br>";


        if ($letter != "front") {
            echo "<table width=\"95%\" align=\"center\"><tr><td align=right>|<a href=\"#last\">"._LAST10REG."</a>| <a href=\"#online\">"._CURRENTONLINE."</a> |</td></tr></table><table width=\"95%\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=#000000 align=center><tr bgcolor=\"".$GLOBALS['bgcolor2']."\">\n";
            echo "<td width=\"10\"  align=\"center\"><center><font class=\"pn-normal\">"._ONLINESTATUS."</font></center></td><td align=\"center\" ><center><font class=\"pn-normal\"  >"._MAVATAR."</font></center></td>\n";
                        echo "<td  align=\"center\">";
                        if ($GLOBALS['sortby'] == "uname" OR !$sortby) {
            echo "<font class=\"pn-normal\" ><b>"._MNICKNAME."</b></font>";
        } else {
            echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=uname\">"._MNICKNAME."</a>";
        }
                        echo"</td>\n";
            echo "<td  align=\"center\">";
                            if ($GLOBALS['sortby'] == "name") {
            echo "<font class=\"pn-normal\"><b>"._MREALNAME."</b></font>";
        } else {
            echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=name\">"._MREALNAME."</a>";
        }
                        echo"</td>\n";
            echo "<td  align=\"center\">";
                            if ($GLOBALS['sortby'] == "femail") {
            echo "<font class=\"pn-normal\"><b>"._PM."</b></font>";
        } else {
            echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=femail\">"._PM."</a>";
        }
                        echo"</td>\n";
            echo "<td  align=\"center\">";
                         if ($GLOBALS['sortby'] == "url") {
            echo "<font class=\"pn-normal\"><b>"._MURL."&nbsp;</b></font>";
        } else {
            echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=url\">"._MURL."</a>";
        }
                        echo"</td>\n";
            $cols = 4;
            if (pnSecAuthAction(0, 'Users::', '::', ACCESS_EDIT)){
                $cols = 6;
                echo "<td  align=\"center\"><font class=\"pn-normal\"><b>"._FUNCTIONS."</b></font></td>\n";
            }
            echo "</tr>";
            $a = 0;

            $dcolor_A = "".$GLOBALS['bgcolor3']."";
            $dcolor_B = "".$GLOBALS['bgcolor1']."";

            $num_users = $result->PO_RecordCount(); //number of users per sorted and limit query
            if($num_rows_per_order > 0 ) {
                while(!$result->EOF) {
                    $user = $result->GetRowAssoc(false);
                    $result->MoveNext();
                                        $useruid= $user['uid'];
                                         $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);
  $sessioninfocolumn = &$pntable['session_info_column'];
            $sessioninfotable = $pntable['session_info'];
 $onlinesess = $dbconn->Execute("SELECT DISTINCT $sessioninfocolumn[uid]
                    FROM $sessioninfotable
                    WHERE $sessioninfocolumn[uid] = $useruid and $sessioninfocolumn[lastused] > $activetime");
    list($online) = $onlinesess->fields;
    if ($online > ''){
    $check = "<font color=red><b><img
src=\"modules/$GLOBALS[ModName]/images/online.gif\"  align=\"absmiddle\" border=\"0\"></a> "._STATUSONLINE."</b></font>";
    }
    else
    {
    $check = "<font color=black><img
src=\"modules/$GLOBALS[ModName]/images/offline.gif\" align=\"absmiddle\" border=\"0\"></a> "._STATUSOFFLINE."</font>";
    }
                    $dcolor = ($a == 0 ? $dcolor_A : $dcolor_B);
                    echo "<tr><td align=\"center\" width=\"10\" bgcolor=\"$dcolor\">$check</td><td  width=\"40\" bgcolor=\"$dcolor\" align=\"center\">";
if($user['user_avatar']!=''){
echo"<img  width=\"40\" src=\"images/avatar/".pnVarPrepForDIsplay($user['user_avatar'])."\" border=\"0\">";
}
echo"</td><td bgcolor=\"$dcolor\">&nbsp;&nbsp;<a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$user[uname]\"><b>".pnVarPrepForDIsplay($user['uname'])."</b></a>";
                                        if($user['user_icq'] >0) {
                echo "<img align=\"absmiddle\" src=\"http://wwp.icq.com/scripts/online.dll?icq=".pnVarPrepForDIsplay($user['user_icq'])."&amp;img=5\" border=\"0\">";
        }
                                        echo"&nbsp;</td>\n";
                    echo "<td bgcolor=\"$dcolor\">&nbsp;&nbsp;<font class=\"pn-normal\">".pnVarPrepForDIsplay($user['name'])."&nbsp;</font></b></td>\n";
                    echo "<td bgcolor=\"$dcolor\" align=\"center\"><b><font class=\"pn-normal\">";
                                         if (pnUserLoggedIn()) {
                                        echo"<a href=\"modules.php?op=modload&name=Messages&file=replypmsg&send=1&uname=".pnVarPrepForDIsplay($user['uname'])."\"><img src=\"modules/$GLOBALS[ModName]/images/mail.gif\" border=\"0\"></a>";
}
else
{
echo"<a href=\"user.php\"><img src=\"modules/$GLOBALS[ModName]/images/mail.gif\" border=\"0\"></a>";
}

echo"</font></td>\n";
                    echo "<td bgcolor=\"$dcolor\" align=\"center\">";

                if(!$user['url'] or $user['url']=="http://" or $user['url']=="http:///" )  {
                        $user['url'] = '';
                        $img ='';
                        }
                                else
                                {
                                $url = $user['url'] ;
                                $img ="<img src=\"modules/".$GLOBALS['ModName']."/images/url.gif\" border=0>";
                                }


            echo "<a href=\"$url\" target=\"new\">$img</a>&nbsp;</td>\n";
                    if (pnSecAuthAction(0, 'Users::', '::', ACCESS_EDIT)){
                        $authid = pnSecGenAuthKey();
                        echo "<td bgcolor=$dcolor align=center><font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"admin.php?module=NS-User&amp;op=modifyUser&amp;chng_uid=$user[uid]&amp;authid=$authid\"><font class=\"pn-normal\">"._EDIT."</font></a><font class=\"pn-sub\"> | </font>\n";
                        echo "<a class=\"pn-normal\" href=\"admin.php?module=NS-User&amp;op=delUser&amp;chng_uid=$user[uid]&amp;authid=$authid\"><font class=\"pn-normal\">"._DELETE."</font></a> ]</font></td>\n";
                    }
                    echo "</tr>";
                    $a = ($dcolor == $dcolor_A ? 1 : 0);
                }
                // start of next/prev/row links.
                echo "\n<tr><td colspan=\"9\" bgcolor=\"".$GLOBALS['bgcolor2']."\">";
                                getnumber('number');
                                echo"</td></tr><tr  height=\"1\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><td colspan=\"8\" bgcolor=\"".$GLOBALS['bgcolor2']."\">";
                                getnumber('online');
                                echo"<br></td></tr></table><br><table width=\"95%\"><tr><td bgcolor=\"#ffffff\" colspan='7' align='right'>\n";


                echo "\t<table width='95%' align=\"center\" cellspacing='0' cellpadding='0' border=\"0\"><tr>";
                if (!empty($where)) {
                    $where = " WHERE $where";
                } else {
                    $where = '';
                }

                $resultcount = $dbconn->Execute($count . $where);
                list ($numrows) = $resultcount->fields;
                $resultcount->Close();

                if ($numrows > $pagesize) {
                    $total_pages = ceil($numrows / $pagesize); // How many pages are we dealing with here ??
                    $prev_page = $page - 1;

                    if ( $prev_page > 0 ) {
                        echo "<td align='left' width='15%'><a class=\"pn-normal\" href='modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=$sortby&amp;page=$prev_page'>";
                        echo "<img src=\"modules/$GLOBALS[ModName]/images/left.gif\" border=\"0\" Alt=\""._PREVIOUS." (".pnVarPrepForDIsplay($prev_page).")\"></a></td>";
                    } else {
                        echo "<td width='15%'>&nbsp;</td>\n";
                    }

                    echo "<td align='center' width='70%'>";
                    echo "- <font class=\"pn-sub\"><b>".pnVarPrepForDIsplay($numrows)." "._USERSFOUND." ".pnVarPrepForDIsplay($letter)."</b> -<br>(".pnVarPrepForDIsplay($total_pages)." "._PAGES.", ".pnVarPrepForDIsplay($num_users)." "._USERSSHOWN.")</font>";
                    echo "</td>";

                    $next_page = $page + 1;
                    if ( $next_page <= $total_pages ) {
                        echo "<td align='right' width='15%'><a class=\"pn-normal\" href='modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=$sortby&amp;page=$next_page'>";
                        echo "<img src=\"modules/$GLOBALS[ModName]/images/right.gif\" border=\"0\" Alt=\""._NEXTPAGE." ($next_page)\"></a></td>";
                    } else {
                        echo "<td width='15%'>&nbsp;</td></tr>\n";
                    }
                        // Added a numbered page list, only shows up to 50 pages.
                        echo "<tr height=\"10\"><td colspan=\"3\"></td><tr><tr><td colspan=\"3\" align=\"center\">";
                        echo " <font class=\"pn-sub\">[ </font>";

                        for($n=1; $n < $total_pages; $n++) {
                            if ($n == $page) {
                                echo "<font class=\"pn-sub\"><b>$n</b></font></a>";
                        } else {
                echo "<a class=\"pn-normal\" href='modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=$GLOBALS[sortby]&amp;page=$n'>";
                echo "<font class=\"pn-sub\">".pnVarPrepForDIsplay($n)."</font></a>";
                }
                            if($n >= 50) {  // if more than 50 pages are required, break it at 50.
                                $break = true;
                                break;
                            } else {  // guess not.
                                echo "<font class=\"pn-sub\"> | </font>";
                            }
                        }

                        if(!isset($break)) { // are we supposed to break ?
                if($n == $page) {
                    echo "<font class=\"pn-sub\">".pnVarPrepForDIsplay($n)."</font></a>";
                } else {
                    echo "<a class=\"pn-normal\" href='modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;letter=$letter&amp;sortby=$GLOBALS[sortby]&amp;page=$total_pages'>";
                    echo "<font class=\"pn-sub\">".pnVarPrepForDIsplay($n)."</font></a>";
                }
                        }
                        echo " <font class=\"pn-sub\">]</font> ";
                        echo "</td></tr>";

// This is where it ends

                } else {  // or we dont have any users..
                    echo "<td align='center'>";
                    echo "<font class=\"pn-sub\">".pnVarPrepForDIsplay($num_rows_per_order)." "._USERSFOUND." ".pnVarPrepForDIsplay($letter)."</font>";
                    echo "</td></tr>";
                 }
                 echo "</table>\n";

                echo "</td></tr>\n";

// end of next/prev/row links

            } else { // no members for this letter.
                echo "<tr><td bgcolor=\"$dcolor_A\" colspan=\"8\" align=\"center\"><br>\n";
                echo "<font class=\"pn-normal\"><b>"._NOMEMBERS."<br>- ".pnVarPrepForDIsplay($letter)." -</b></font>\n";
                echo "<br><br></td></tr>\n";
                                echo "\n<tr><td colspan=\"9\" bgcolor=\"".$GLOBALS['bgcolor2']."\">";
                                getnumber('number');
                                echo"</td></tr><tr  height=\"1\" bgcolor=\"".$GLOBALS['bgcolor2']."\"><td colspan=\"8\" bgcolor=\"".$GLOBALS['bgcolor2']."\">";
                                getnumber('online');
                                echo"<br></td></tr>";

                               }
            echo "\n</table><br>\n";
        }

echo"<a name=last><table width=\"95%\" bgcolor=\"#000000\" cellspacing=\"1\" cellpadding=\"3\" align=\"center\"><tr bgcolor=\"".$GLOBALS['bgcolor2']."\"><td colspan=\"6\"><b><u>"._LAST10REG.":</u></b></td></tr>";
 $myquery2 = buildSimpleQuery('users', array('uid', 'name', 'uname', 'femail', 'url','user_regdate'),'pn_uname NOT LIKE "Anonymous"' , 'pn_user_regdate DESC' , 10);

//select our data

        $result = $dbconn->Execute($myquery2);
                if ($result === false) {
            error_log("Error: " . $dbconn->ErrorNo() . ": " . $dbconn->ErrorMsg());
           PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
        }
        $num_rows_per_order = $result->PO_RecordCount();
        echo "<br>";
                  $a = 0;

            $dcolor_A = "".$GLOBALS['bgcolor3']."";
            $dcolor_B = "".$GLOBALS['bgcolor1']."";

            $num_users = $result->PO_RecordCount(); //number of users per sorted and limit query
            if($num_rows_per_order > 0 ) {
                while(!$result->EOF) {
                    $user = $result->GetRowAssoc(false);
                    $result->MoveNext();
                                        $useruid= $user['uid'];
                                         $activetime = time() - (pnConfigGetVar('secinactivemins') * 60);
                          $sessioninfocolumn = &$pntable['session_info_column'];
            $sessioninfotable = $pntable['session_info'];
                         $onlinesess = $dbconn->Execute("SELECT DISTINCT $sessioninfocolumn[uid]
                    FROM $sessioninfotable
                    WHERE $sessioninfocolumn[uid] = $useruid and $sessioninfocolumn[lastused] > $activetime");
    list($online) = $onlinesess->fields;
    if ($online > ''){
    $check = "<font color=red><b><img
src=\"modules/$GLOBALS[ModName]/images/online.gif\"  align=absmiddle border=0></a>"._STATUSONLINE."</b></font>";
    }
    else
    {
    $check = "<font color=black><img
src=\"modules/$GLOBALS[ModName]/images/offline.gif\" align=absmiddle border=0></a>"._STATUSOFFLINE."</font>";
    }
                    $dcolor = ($a == 0 ? $dcolor_A : $dcolor_B);
                                        $newresult = $dbconn->Execute("select pn_user_regdate from $pntable[users] where pn_uid=".pnVarPrepForDIsplay($user['uid'])."");
            list($user_regdate)= $newresult->fields;
                    echo "<tr><td align=\"center\" width=\"10\" bgcolor=\"$dcolor\">
$check</td><td bgcolor=\"$dcolor\"><a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=".pnVarPrepForDIsplay($user['uname'])."\"><b>".pnVarPrepForDIsplay($user['uname'])."</b></a></td><td bgcolor=\"$dcolor\">".date('d.m.Y',$user_regdate)."</td>\n";
                    echo "<td bgcolor=\"$dcolor\">&nbsp;&nbsp;<font class=\"pn-normal\">".pnVarPrepForDIsplay($user['name'])."&nbsp;</font></b></td>\n";
                    echo "<td bgcolor=\"$dcolor\" width=\"11\" align=\"center\">";
if(!$user['url'] or $user['url']=="http://" or $user['url']=="http:///")  {
                        $user['url'] = '';
                        $img ='&nbsp;';
                        }
                                else
                                {
                                $url = $user['url'] ;
                                $img ="<img src=\"modules/$GLOBALS[ModName]/images/url.gif\" border=\"0\">";
                                }


            echo "<a href=\"$url\" target=\"new\">$img</a></td>\n";

 echo "<td bgcolor=\"$dcolor\" width=\"18\" align=\"center\"><a href=\"modules.php?op=modload&name=Messages&file=replypmsg&send=1&uname=".pnVarPrepForDIsplay($user['uname'])."\"><img src=\"modules/$GLOBALS[ModName]/images/mail.gif\" border=\"0\"></a></td>\n";


                    echo "</tr>";
                    $a = ($dcolor == $dcolor_A ? 1 : 0);
                }
                                }
echo"</table><table width=\"95%\" align=\"center\"><tr><td align=\"right\">|<a href=\"#top\">"._BACKTOTOP."</a>|</td></tr></table><br>";

        include 'footer.php';
?>