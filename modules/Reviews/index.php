<?php // $Id: index.php,v 1.7 2002/11/08 11:17:51 magicx Exp $ $Name:  $
// ----------------------------------------------------------------------
// POSTNUKE Content Management System
// Copyright (C) 2001 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on: Reviews Addon
// Copyright (c) 2000 by Jeff Lambert (jeffx@ican.net)
// http://www.qchc.com
// More scripts on http://www.jeffx.qchc.com
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
// Filename: modules/Reviews/index.php
// Original Author of file: Jeff Lambert
// Purpose of file: Reviews system
// ----------------------------------------------------------------------

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}
/**
 * Credits to Edgar Miller -- http://www.bosna.de/ from his post on PHP-Nuke
 * (http://phpnuke.org/article.php?sid=2010&mode=nested&order=0&thold=0)
 */

$ModName = basename( dirname( __FILE__ ) );

modules_get_language();

// Security check
if (!pnSecAuthAction(0, 'Reviews::', '::', ACCESS_READ)) {
	include 'header.php';
	echo _BADAUTHKEY;
	include 'footer.php';
	return;
}

$alphabet = array(_ALL, "A","B","C","D","E","F","G","H","I","J","K","L","M",
                  "N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
          	  "1","2","3","4","5","6","7","8","9","0");

function alpha()
{
       $num = count($GLOBALS[alphabet]) - 1;

    echo "<center><font class=\"pn-normal\">[ ";
    $counter = 0;

    while (list(, $ltr) = each($GLOBALS[alphabet])) {
        echo "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$ltr\">".pnVarPrepForDisplay($ltr)."</a>";
        if($counter == round($num/2)) {
            echo " ]\n<br>\n[ ";
        } elseif($counter != $num ) {
            echo "&nbsp;|&nbsp;\n";
        }
        $counter++;
    }
    echo " ]<br><br></font></center>\n";
        if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_COMMENT)){
            echo "<center><font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=write_review\">"._WRITEREVIEW."</a> ]</font></center><br>\n\n";
        }
}

function display_score($score)
{
   
    $image = "<img src=\"modules/$GLOBALS[ModName]/images/blue.gif\" alt=\"\">";
    $halfimage = "<img src=\"modules/$GLOBALS[ModName]/images/bluehalf.gif\" alt=\"\">";
    $full = "<img src=\"modules/$GLOBALS[ModName]/images/star.gif\" alt=\"\">";

    if ($score == 10) {
        for ($i=0; $i < 5; $i++) {
            echo "$full";
        }
    } else {
        for ($i=0; $i < (floor($score/2)); $i++) {
            echo "$image";
        }
        if ($score % 2) {
            echo "$halfimage";
        }
    }
}

function write_review()
{
    // ML added rlanguage , dropdown with available languages , currentlang is pre-selected
  
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    include ('header.php');

    if (!(pnSecAuthAction(0, 'Reviews::', '::', ACCESS_COMMENT))) {
        echo _REVIEWSADDNOAUTH;
        include 'footer.php';
        return;
    }
    $sitename = pnVarPrepForDisplay(pnConfigGetVar('sitename'));

    OpenTable();
    echo "
    <font class=\"pn-normal\"><b>"._WRITEREVIEWFOR." $sitename</b><br><br>
    <i>"._ENTERINFO."</i><br><br>
    <form method=\"post\" action=\"modules.php\">
    <input type=\"hidden\" name=\"op\" value=\"modload\">
    <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
    <input type=\"hidden\" name=\"file\" value=\"index\">
    <b>"._PRODUCTTITLE.":</b><br>
    <input type=\"text\" name=\"title\" size=\"50\" maxlength=\"150\"><br>
    <i>"._NAMEPRODUCT."</i><br>";
    echo "<br><b>"._LANGUAGE.": </b>"
            ."<select name=\"rlanguage\">";
                                                // ML BEGIN
    $lang = languagelist();
    $sel_lang[$currentlang] = ' selected';
    print '<option value="">'._ALL.'</option>';
    $handle = opendir('language');
    while ($f = readdir($handle))
    {
        if (is_dir("language/$f") && (!empty($lang[$f])))
        {
            $langlist[$f] = $lang[$f];
            $sel_lang[$f] = '';
        }
    }
    asort($langlist);
    foreach ($langlist as $k=>$v)
    {
        print "<option value=\"$k\"$sel_lang[$k]>$v</option>\n";
    }
    echo "</select>";
    echo "<br><br><b>"._REVIEW.":</b><br>"
          ."<textarea name=\"text\" rows=\"15\" wrap=\"virtual\" cols=\"60\"></textarea><br>";

    if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_ADD)) {
        echo ""._PAGEBREAK."<br>";
    }
    echo "
    <i>"._CHECKREVIEW."</i><br><br>
    <b>"._YOURNAME.":</b><br>";
    echo "<input type=\"text\" name=\"reviewer\" size=\"41\" maxlength=\"40\" value=\"" . pnUserGetVar('name') . "\"><br>
    <i>"._FULLNAMEREQ."</i><br><br>
    <b>"._REMAIL.":</b><br>
    <input type=\"text\" name=\"email\" size=\"40\" maxlength=\"80\" value=\"" . pnUserGetVar('email') . "\"><br>
    <i>"._REMAILREQ."</i><br><br>
    <b>"._SCORE."</b><br>
    <select name=\"score\">
    <option name=\"score\" value=\"10\">(10) 5 "._STARS."</option>
    <option name=\"score\" value=\"9\">(9) 4 1/2 "._STARS."</option>
    <option name=\"score\" value=\"8\">(8) 4 "._STARS."</option>
    <option name=\"score\" value=\"7\">(7) 3 1/2 "._STARS."</option>
    <option name=\"score\" value=\"6\">(6) 3 "._STARS."</option>
    <option name=\"score\" value=\"5\">(5) 2 1/2 "._STARS."</option>
    <option name=\"score\" value=\"4\">(4) 2 "._STARS."</option>
    <option name=\"score\" value=\"3\">(3) 1 1/2 "._STARS."</option>
    <option name=\"score\" value=\"2\">(2) 1 "._STAR."</option>
    <option name=\"score\" value=\"1\">(1) 1/2 "._STAR."</option>
    </select>
    <i>"._SELECTSCORE."</i><br><br>
    <b>"._RELATEDLINK.":</b><br>
    <input type=\"text\" name=\"url\" size=\"40\" maxlength=\"100\"><br>
    <i>"._PRODUCTSITE."</i><br><br>
    <b>"._LINKTITLE.":</b><br>
    <input type=\"text\" name=\"url_title\" size=\"40\" maxlength=\"50\"><br>
    <i>"._LINKTITLEREQ."</i><br><br>";
    if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_ADD)) {
        echo "
        <b>"._RIMAGEFILE.":</b><br>
        <input type=\"text\" name=\"cover\" size=\"40\" maxlength=\"100\"><br>
        <i>"._RIMAGEFILEREQ."</i><br><br>";
    }
    echo "<i>"._CHECKINFO."</i><br><br>";
    echo "<input type=\"hidden\" name=\"req\" value=\"preview_review\">"
    ."<input type=\"submit\" value=\""._PREVIEW."\"> <input type=\"button\" onClick=\"history.go(-1)\" value=\""._CANCEL."\"></font></form>";
    CloseTable();
    include 'footer.php';
}

function preview_review()
{
   
    list($date,
     $title,
     $text,
     $reviewer,
     $email,
     $score,
     $cover,
     $url,
     $url_title,
     $hits,
     $id,
     $rlanguage) = pnVarCleanFromInput('date',
                       'title',
                       'text',
                       'reviewer',
                       'email',
                       'score',
                       'cover',
                       'url',
                       'url_title',
                       'hits',
                       'id',
                       'rlanguage');

    if (strpos($text, "<!--pagebreak-->") !== false) {
        $text = str_replace("<!--pagebreak-->","&lt;!--pagebreak--&gt;",$text);
    }
    include ('header.php');

    if (!(pnSecAuthAction(0, 'Reviews::', '::', ACCESS_COMMENT))) {
        echo _REVIEWSADDNOAUTH;
        include 'footer.php';
        return;
    }
    OpenTable();
    echo "<form method=\"post\" action=\"modules.php\" name=\"review\">
    <input type=\"hidden\" name=\"op\" value=\"modload\">
    <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
    <input type=\"hidden\" name=\"file\" value=\"index\">
    <font class=\"pn-normal\">";

    if ($title == "") {
            $error = 1;
        echo ""._INVALIDTITLE."<br>";
    }
    if ($text == "") {
            $error = 1;
        echo ""._INVALIDTEXT."<br>";
    }
    if (($score < 1) || ($score > 10)) {
        $error = 1;
        echo ""._INVALIDSCORE."<br>";
    }
    if (($hits < 0) && ($id != 0)) {
        $error = 1;
        echo ""._INVALIDHITS."<br>";
    }
    if ($reviewer == "" || $email == "") {
        $error = 1;
        echo ""._CHECKNAME."<br>";
    } elseif($reviewer != "" && $email != "") {
        $res = pnVarValidate($email, 'email');
        if($res == false) {
        $error = 1;
            /* eregi checks for a valid email! works nicely for me! */
            /* nkame: centralization of mail validation */
            echo ""._INVALIDEMAIL."<br>";
        }
    $valid = pnVarValidate($url, 'url');
        if (($url_title != "" && $valid == false) || ($url_title == "" && !empty($url))) {
            $error = 1;
            echo ""._INVALIDLINK."<br>";
        }
	/**
         * else if (($url != "") && (!(eregi('(^http[s]*:[/]+)(.*)', $url))))
         * $url = "http://" . $url;
         *
         * If the user ommited the http, this nifty eregi will add it
         * nkame: centralization of url checking. 'http://' is added as well
         */
        if (isset($error) && $error == 1)
            echo "<br>"._GOBACK."";
        else {
            if($date == "")
              $date = date("Y-m-d H:i:s", time());
            $year2 = substr($date,0,4);
            $month = substr($date,5,2);
            $day = substr($date,8,2);
            $fdate = ml_ftime(_DATELONG,mktime (0,0,0,$month,$day,$year2));
            echo "<table border=\"0\" width=\"100%\"><tr><td colspan=\"2\">";
            echo "<p><font class=\"pn-title\"><i><b>".pnVarPrepForDisplay($title)."</b></i></font><br>";
            echo "<blockquote><p><font class=\"pn-normal\">";
            if ($cover != "")
              echo "<img src=\"modules/$GLOBALS[ModName]/images/$cover\" align=\"right\" border=\"1\" vspace=\"2\" alt=\"\">";
            echo "".pnVarPrepHTMLDisplay(nl2br($text))."<p>";
            echo "<b>"._ADDED."</b> ".pnVarPrepForDisplay($fdate)."<br>";
            if (isset ($rlanguage) && $rlanguage != '') {
            	echo "<b>"._LANGUAGE."</b> ".pnVarPrepForDisplay($rlanguage)."<br>"; /* ML Added rlanguage for display */
            }
            echo "<b>"._REVIEWER."</b> <a class=\"pn-normal\" href=\"mailto:". pnVarPrepForDisplay($email) ."\">".pnVarPrepForDisplay($reviewer)."</a><br>";
            echo "<b>"._SCORE."</b> ";

            display_score($score);

            if ($url != "")
                echo "<br><b>"._RELATEDLINK.":</b> <a class=\"pn-normal\" href=\"".pnVarPrepHTMLDisplay($url)."\" target=\"new\">".pnVarPrepForDisplay($url_title)."</a>";
            if ($id != 0) {
                echo "<br><b>"._REVIEWID.":</b>".pnVarPrepForDisplay($id)."<br>";
                echo "<b>"._HITS.":</b>".pnVarPrepForDisplay($hits)."<br>";
            }
            echo "</font></blockquote>";
            echo "</td></tr></table>";
            echo "<input type=\"hidden\" name=\"req\" value=send_review>";
            echo "<input type=\"hidden\" name=\"id\" value=$id>
               <input type=\"hidden\" name=\"hits\" value=\"".pnVarPrepHTMLDisplay($hits)."\">
               <input type=\"hidden\" name=\"date\" value=\"".pnVarPrepHTMLDisplay($date)."\">
               <input type=\"hidden\" name=\"title\" value=\"".pnVarPrepHTMLDisplay($title)."\">
               "._CHANGES."<br><textarea name=\"text\" rows=10 cols=60>".pnVarPrepHTMLDisplay($text)."</textarea>
               <input type=\"hidden\" name=\"reviewer\" value=\"".pnVarPrepHTMLDisplay($reviewer)."\">
               <input type=\"hidden\" name=\"email\" value=\"".pnVarPrepHTMLDisplay($email)."\">
               <input type=\"hidden\" name=\"score\" value=\"".pnVarPrepHTMLDisplay($score)."\">
               <input type=\"hidden\" name=\"url\" value=\"".pnVarPrepHTMLDisplay($url)."\">
               <input type=\"hidden\" name=\"url_title\" value=\"".pnVarPrepHTMLDisplay($url_title)."\">
               <input type=\"hidden\" name=\"cover\" value=\"".pnVarPrepHTMLDisplay($cover)."\">
               <input type=\"hidden\" name=\"rlanguage\" value=\"".pnVarPrepHTMLDisplay($rlanguage)."\">";
            echo "<p><i>"._LOOKSRIGHT."</i><br>
               <input type=\"submit\" name=\"req\" value=\""._YES."\"> </form><hr>";
    				echo "<form method=\"post\" action=\"modules.php\" name=\"preview\">
    					 <input type=\"hidden\" name=\"op\" value=\"modload\">
    					 <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
    					 <input type=\"hidden\" name=\"file\" value=\"index\">
    					 <input type=\"hidden\" name=\"req\" value=preview_review>
    					 <font class=\"pn-normal\">";
    				echo "<b>"._PRODUCTTITLE.":</b><br>
    					 <input type=\"text\" name=\"title\" size=\"50\" maxlength=\"150\" value=\"".pnVarPrepHTMLDisplay($title)."\"><br>
    					 <i>"._NAMEPRODUCT."</i><br>";
    				echo "<input type=\"hidden\" name=\"rlanguage\" value=\"".pnVarPrepHTMLDisplay($rlanguage)."\">";
    				echo "<br><br><b>"._REVIEW.":</b><br>"
          		 ."<textarea name=\"text\" rows=\"15\" wrap=\"virtual\" cols=\"60\">".pnVarPrepHTMLDisplay($text)."</textarea><br>";

				    if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_ADD)) {
        			 echo ""._PAGEBREAK."<br>";
    				}
    				echo "
    					 <i>"._CHECKREVIEW."</i><br><br>
    					 <b>"._YOURNAME.":</b><br>";
    				echo "<input type=\"text\" name=\"reviewer\" size=\"41\" maxlength=\"40\" value=\"".pnVarPrepHTMLDisplay($reviewer)."\"><br>
    					 <i>"._FULLNAMEREQ."</i><br><br>
    					 <b>"._REMAIL.":</b><br>
    					 <input type=\"text\" name=\"email\" size=\"40\" maxlength=\"80\" value=\"".pnVarPrepHTMLDisplay($email)."\"><br>
    					 <i>"._REMAILREQ."</i><br><br>
    					 <b>"._SCORE."</b><br>
    					 <input type=\"text\" name=\"score\" size=\"40\" maxlength=\"2\" value=\"".pnVarPrepHTMLDisplay($score)."\"><br>
    					 <i>"._SELECTSCORE." (1-10)</i><br><br>
    					 <b>"._RELATEDLINK.":</b><br>
    					 <input type=\"text\" name=\"url\" size=\"40\" maxlength=\"100\" value=\"".pnVarPrepHTMLDisplay($url)."\"><br>
    					 <i>"._PRODUCTSITE."</i><br><br>
    					 <b>"._LINKTITLE.":</b><br>
    					 <input type=\"text\" name=\"url_title\" size=\"40\" maxlength=\"50\" value=\"".pnVarPrepHTMLDisplay($url_title)."\"><br>
    					 <i>"._LINKTITLEREQ."</i><br><br>";
    				if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_ADD)) {
        		 		echo "
        							 <b>"._RIMAGEFILE.":</b><br>
        							 <input type=\"text\" name=\"cover\" size=\"40\" maxlength=\"100\" value=\"".pnVarPrepHTMLDisplay($cover)."\"><br>
        							 <i>"._RIMAGEFILEREQ."</i><br><br>";
    				}
            echo "<p><i>"._LOOKSRIGHT."</i><br>
                  		<input type=\"submit\" name=\"sub\" value=\""._PREVIEW."\"> </form>";
            if($id != 0) {
                    $word = ""._RMODIFIED."";
            } else {
                    $word = ""._RADDED."";
            }
            if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_ADD)) {
                echo "<br><br><b>"._NOTE."</b> "._ADMINLOGGED." $word.";
            }
        }
    }
    CloseTable();
    include ("footer.php");
}

function send_review()
{
   
    list($date,
     $title,
     $text,
     $reviewer,
     $email,
     $score,
     $cover,
     $url,
     $url_title,
     $hits,
     $id,
     $rlanguage) = pnVarCleanFromInput('date',
                       'title',
                       'text',
                       'reviewer',
                       'email',
                       'score',
                       'cover',
                       'url',
                       'url_title',
                       'hits',
                       'id',
                       'rlanguage');
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ('header.php');

    if (!(pnSecAuthAction(0, 'Reviews::', '::', ACCESS_COMMENT))) {
        echo _REVIEWSSUBMITNOAUTH;
        include 'footer.php';
        return;
    }
    if (strpos($text, "<!--pagebreak-->") !== false) {
        $text = str_replace("<!--pagebreak-->","&lt;!--pagebreak--&gt;",$text);
    }
    if (strpos($text, "&lt;!--pagebreak--&gt;") !== false) {
        $text = str_replace("&lt;!--pagebreak--&gt;","<!--pagebreak-->",$text);
    }
    OpenTable();
    echo "<br><center><font class=\"pn-normal\">"._RTHANKS."</font>";
    if ($id != 0)
        echo "<font class=\"pn-normal\"> "._MODIFICATION."</font>";
    else
        echo "<font class=\"pn-normal\">, $reviewer";
    echo "!</font><br>";

    if ($id == 0) {
        // New review
        if (!(pnSecAuthAction(0, 'Reviews::', "$title::", ACCESS_COMMENT))) {
            echo _REVIEWSSUBMITNOAUTH;
            CloseTable();
            include 'footer.php';
            return;
        }

        if (pnSecAuthAction(0, 'Reviews::', "$title::", ACCESS_ADD)) {
            // Add immediately
            $column = &$pntable['reviews_column'];
            $newid = $dbconn->GenId($pntable['reviews']);
            $result = $dbconn->Execute("INSERT INTO $pntable[reviews] ($column[id], $column[date], $column[title], $column[text], $column[reviewer], $column[email], $column[score], $column[cover], $column[url], $column[url_title], $column[hits], $column[language]) VALUES ($newid,
            '".pnVarPrepForStore($date)."',
                '".pnVarPrepForStore($title)."',
                '".pnVarPrepForStore($text)."',
                '".pnVarPrepForStore($reviewer)."',
                '".pnVarPrepForStore($email)."',
                '".pnVarPrepForStore($score)."',
                '".pnVarPrepForStore($cover)."',
                '".pnVarPrepForStore($url)."',
                '".pnVarPrepForStore($url_title)."',
                '1',
                '".pnVarPrepForStore($rlanguage)."'
            )");
            if($dbconn->ErrorNo()<>0) {
                error_log("ERROR 1 : " . $dbconn->ErrorMsg());
            }
            echo "<font class=\"pn-normal\">"._ISAVAILABLE."</font>";
        } else {
            // Add to waiting list
            $column = &$pntable['reviews_add_column'];
            $nextid = $dbconn->GenId($pntable['reviews_add']);
            $result = $dbconn->Execute("INSERT INTO $pntable[reviews_add] ($column[id], $column[date], $column[title], $column[text], $column[reviewer], $column[email], $column[score], $column[url], $column[url_title], $column[language]) VALUES ($nextid,
            '".pnVarPrepForStore($date)."',
                '".pnVarPrepForStore($title)."',
                '".pnVarPrepForStore($text)."',
                '".pnVarPrepForStore($reviewer)."',
                '".pnVarPrepForStore($email)."',
                '".pnVarPrepForStore($score)."',
                '".pnVarPrepForStore($url)."',
                '".pnVarPrepForStore($url_title)."',
                '".pnVarPrepForStore($rlanguage)."'
            )");
            if($dbconn->ErrorNo()<>0) {
                error_log("ERROR 1 : " . $dbconn->ErrorMsg());
            }
            echo "<font class=\"pn-normal\">"._EDITORWILLLOOK."</font>";
        }

    } else {
        // Updated review
        if (!(pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_EDIT))) {
            echo _REVIEWSEDITNOAUTH;
            CloseTable();
            include 'footer.php';
            return;
        }

        $column = &$pntable['reviews_column'];
        $result = $dbconn->Execute("UPDATE $pntable[reviews] SET $column[date]='".pnVarPrepForStore($date)."', $column[title]='".pnVarPrepForStore($title)."', $column[text]='".pnVarPrepForStore($text)."', $column[reviewer]='".pnVarPrepForStore($reviewer)."', $column[email]='".pnVarPrepForStore($email)."', $column[score]='".pnVarPrepForStore($score)."', $column[cover]='".pnVarPrepForStore($cover)."', $column[url]='".pnVarPrepForStore($url)."', $column[url_title]='".pnVarPrepForStore($url_title)."', $column[hits]='".pnVarPrepForStore($hits)."', $column[language]='".pnVarPrepForStore($rlanguage)."' WHERE $column[id] = '".pnVarPrepForStore($id)."'");
        if($dbconn->ErrorNo()<>0) {
            error_log("ERROR 2 : " . $dbconn->ErrorMsg());
        }
        echo "<font class=\"pn-normal\">"._ISAVAILABLE."</font>";
    }
    echo "<br><br><font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index\">"._RBACK."</a> ]</font><br></center>";
    CloseTable();
    include 'footer.php';
}

function reviews_index()
{
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    include ('header.php');
    $column = &$pntable['reviews_column'];
    if (pnConfigGetVar('multilingual') == 1) {
        $querylang = "($column[language]='$currentlang' OR $column[language]='')";
    } else {
        $querylang = "";
    }
    OpenTable();
    echo "<table border=\"0\" width=\"95%\" CELLPADDING=\"2\" CELLSPACING=\"4\" align=\"center\">
    <tr><td colspan=\"2\"><center><font class=\"pn-title\">"._RWELCOME."</font></center><br>";
    $column = &$pntable['reviews_main_column'];
    $result = $dbconn->Execute("SELECT $column[title], $column[description] FROM $pntable[reviews_main]");
    list($title, $description) = $result->fields;
    echo "<center><font class=\"pn-normal\"><b>$title</b><br><br>$description</font></center>";
    echo "<br>";
    alpha();
    echo "</td></tr>";
    echo "<tr><td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor2]\"><b>"._10MOSTPOP."</b></td>";
    echo "<td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor2]\"><b>"._10MOSTREC."</b></td></tr>";
    $column = &$pntable['reviews_column'];
    $popquery = buildSimpleQuery('reviews', array ('id', 'title', 'hits'), $querylang, "$column[hits] DESC", 10);
    $result_pop = $dbconn->Execute($popquery);
    $x = 0;
    while (!$result_pop->EOF)  {
        list($id, $title, $hits) = $result_pop->fields;
        $ida[$x] = $id;
        $titlea[$x] = $title;
        $hitsa[$x] = $hits;
        $x++;
        $result_pop->MoveNext();
    }

    $recquery = buildSimpleQuery('reviews', array ('id', 'title', 'date', 'hits'), $querylang, "$column[date] DESC", 10);
    $result_rec = $dbconn->Execute($recquery);
    $x = 0;
    while (!$result_rec->EOF)  {
        list($id, $title, $hits) = $result_rec->fields;
        $idb[$x] = $id;
        $titleb[$x] = $title;
        $hitsb[$x] = $hits;
        $x++;
        $result_rec->MoveNext();
    }

    $y = 1;
    for ($x = 0; $x < 10; $x++) {
        echo "<tr>";
//        list($id, $title, $hits)=$result_pop->fields;

        if(empty ($ida[$x])) {
	    $ida[$x]='';
	}
        $id=$ida[$x];
        if(empty ($titlea[$x])) {
	    $titlea[$x]='';
	}
        $title=$titlea[$x];

        if(empty($hitsa[$x])) {
	    $hitsa[$x]='';
	}
        $hits=$hitsa[$x];

        if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_READ)) {
            echo "<td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\"><font class=\"pn-normal\">".pnVarPrepForDisplay($y).") <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id\">".pnVarPrepForDisplay($title)."</a></font></td>";
        } else {
            echo "<td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\"><font class=\"pn-normal\">".pnVarPrepForDisplay($y).")</font></td>";
        }
//        list($id, $title, , $hits)=$result_rec->fields;
        if(empty($idb[$x])) {
	    $idb[$x]='';
	}
        $id=$idb[$x];
        if(empty($titleb[$x])) {
	    $titleb[$x]='';
	}
        $title=$titleb[$x];
        if(empty($hitsb[$x])) {
	    $hitsb[$x]='';
	}
        $hits=$hitsb[$x];
        if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_READ)) {
            echo "<td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\"><font class=\"pn-normal\">$y) <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id\">$title</a></font></td>";
        } else {
            echo "<td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\"><font class=\"pn-normal\">$y)</font></td>";
        }
        echo "</tr>";
        $y++;
    }
    echo "<tr><td colspan=\"2\"><br></td></tr>";
    $result = $dbconn->Execute("SELECT count(*) FROM $pntable[reviews]");
    list($numresults) = $result->fields;
    echo "<tr><td colspan=\"2\"><center><font class=\"pn-sub\">"._THEREARE."
    $numresults "._REVIEWSINDB."</font></center></td></tr></table>";
    /* memory flush */
    $result_pop->Close();
    $result_rec->Close();
    $result->Close();
    CloseTable();
    include 'footer.php';
}

function reviews()
{
     list($letter,
     $field,
     $order) = pnVarCleanFromInput('letter',
				   'field',
				   'order');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $reviewstable = $pntable['reviews'];
    $column = &$pntable['reviews_column'];

    $currentlang = pnUserGetLang();

    include ('header.php');
    if (pnConfigGetVar('multilingual') == 1) {
        $querylang = "AND ($column[language] ='$currentlang' OR $column[language] = '')";
    } else {
        $querylang = "";
    }
    $sitename = pnVarPrepForDisplay(pnConfigGetVar('sitename'));

    OpenTable();
    echo "<center><b>$sitename "._REVIEWS."</b><br>";
    if ($letter == _ALL) {
      $query = "SELECT $column[id], $column[title], $column[hits], $column[reviewer], $column[score]
                FROM $reviewstable WHERE $column[id] != '' $querylang ";
    } else {
      echo "<i>"._REVIEWSLETTER." \"".pnVarPrepForDisplay($letter)."\"</i><br><br>";
      $query = "SELECT $column[id], $column[title], $column[hits], $column[reviewer], $column[score]
                FROM $reviewstable
                WHERE UPPER($column[title]) LIKE '$letter%'
                $querylang ";
    }

    switch($field) {

        case "reviewer":
            $query .= " ORDER by pn_reviewer $order";
            break;

        case "score":
            $query .= " ORDER by pn_score $order";
            break;

        case "hits":
            $query .= " ORDER by pn_hits $order";
            break;

        default:
            $query .= " ORDER by pn_title $order";
            break;
    }

    $result = $dbconn->Execute($query);
    if ($result === false) {
        error_log("Error: " . $dbconn->ErrorNo() . ": " . $dbconn->ErrorMsg());
       PN_DBMsgError($dbconn, __FILE__, __LINE__, "Error accesing to the database");
    }
    if ($result->EOF) {
        echo '<font class="pn-normal"><i><b>'._NOREVIEWS.' \''.$letter.'\'</b></i></font><br><br>';
    } else {
        echo "<TABLE BORDER=\"0\" width=\"100%\" CELLPADDING=\"2\" CELLSPACING=\"4\">
                <tr>
                <td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\">
                <P ALIGN=\"LEFT\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=title&amp;order=ASC\"><img src=\"modules/$GLOBALS[ModName]/images/up.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTASC."\"></a><font class=\"pn-normal\"><B> "._PRODUCTTITLE." </B></font><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=title&amp;order=DESC\"><img src=\"modules/$GLOBALS[ModName]/images/down.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTDESC."\"></a>
                </td>
                <td width=\"18%\" bgcolor=\"$GLOBALS[bgcolor4]\">
                <P ALIGN=\"CENTER\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=reviewer&amp;order=ASC\"><img src=\"modules/$GLOBALS[ModName]/images/up.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTASC."\"></a><font class=\"pn-normal\"><B> "._REVIEWER." </B></font><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=reviewer&amp;order=desc\"><img src=\"modules/$GLOBALS[ModName]/images/down.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTDESC."\"></a>
                </td>
                <td width=\"18%\" bgcolor=\"$GLOBALS[bgcolor4]\">
                <P ALIGN=\"CENTER\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=score&amp;order=ASC\"><img src=\"modules/$GLOBALS[ModName]/images/up.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTASC."\"></a><font class=\"pn-normal\"><B> "._SCORE." </B></font><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=score&amp;order=DESC\"><img src=\"modules/$GLOBALS[ModName]/images/down.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTDESC."\"></a>
                </td>
                <td width=\"14%\" bgcolor=\"$GLOBALS[bgcolor4]\">
                <P ALIGN=\"CENTER\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=hits&amp;order=ASC\"><img src=\"modules/$GLOBALS[ModName]/images/up.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTASC."\"></a><font class=\"pn-normal\"><B> "._HITS." </B></font><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=$letter&amp;field=hits&amp;order=DESC\"><img src=\"modules/$GLOBALS[ModName]/images/down.gif\" border=\"0\" width=\"15\" height=\"9\" Alt=\""._SORTDESC."\"></a>
                </td>
                </tr>";
        $numshown=0;
        while(!$result->EOF) {
            $myrow = $result->GetRowAssoc(false);
            $title = $myrow['pn_title'];
            $id = $myrow['pn_id'];
            $reviewer = $myrow['pn_reviewer'];
            if(!empty ($myrow['pn_email'])){
                $email = $myrow['pn_email'];
            } else {
              $email='';
            }
            $score = $myrow['pn_score'];
            $hits = $myrow['pn_hits'];

            if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_READ)) {
                echo "<tr>
                        <td width=\"50%\" bgcolor=\"$GLOBALS[bgcolor4]\"><a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id\">".pnVarPrepForDisplay($title)."</a></td>
                        <td width=\"18%\" bgcolor=\"$GLOBALS[bgcolor4]\">";
                if ($reviewer != "")
                    echo "<font class=\"pn-normal\"><center>".pnVarPrepForDisplay($reviewer)."</center></font>";
                echo "</td><td width=\"18%\" bgcolor=\"$GLOBALS[bgcolor4]\"><center>";
                display_score($score);
                echo "</center></td><td width=\"14%\" bgcolor=\"$GLOBALS[bgcolor4]\"><font class=\"pn-normal\"><center>".pnVarPrepForDisplay($hits)."</center></font></td>
                    </tr>";
                $numshown++;
            }
        $result->MoveNext();
        }
        echo "</TABLE>";
        echo "<br><font class=\"pn-sub\">$numshown "._TOTALREVIEWS."</font><br><br>";
    }
    echo "[ <a class=\"pn-normal\"href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index\">"._RETURN2MAIN."</a> ]</center>";
    /* memory flush */
    $result->Close();
    CloseTable();
    include ("footer.php");
}

function postcomment()
{
    
    list($id,
     $title) = pnVarCleanFromInput('id',
                       'title');

    $AllowableHTML = pnConfigGetVar('AllowableHTML');
    $anonymous = pnConfigGetVar('anonymous');

    include("header.php");
    $title = urldecode($title);
    OpenTable();
    echo "<center><font class=\"pn-normal\"><b>"._REVIEWCOMMENT." ".pnVarPrepForDisplay($title)."</b><br><br></font></center>
    <form action=\"modules.php\" method=\"post\">
    <input type=\"hidden\" name=\"op\" value=\"modload\">
    <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
    <input type=\"hidden\" name=\"file\" value=\"index\">
    ";
    if (!(pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_COMMENT))) {
        echo _REVIEWSCOMMENTNOAUTH;
        include 'footer.php';
        return;
    }
    if (!pnUserLoggedIn()) {
        echo "<font class=\"pn-normal\"><b>"._YOURNICK."</b> ".pnVarPrepForDisplay($anonymous)." [ "._RCREATEACCOUNT." ]<br><br>";
        $uname = $anonymous;
    } else {
        $uname = pnUserGetVar('uname');
        echo "<font class=\"pn-normal\"><b>"._YOURNICK."</b> ".pnVarPrepForDisplay($uname)."<br>
        <input type=checkbox name=xanonpost> "._POSTANON."<br><br>";
    }
    echo "
    <input type=hidden name=uname value=$uname>
    <input type=hidden name=id value=$id>
    <b>"._SELECTSCORE."</b>
    <select name=score>
    <option value=10>10</option>
    <option value=9>9</option>
    <option value=8>8</option>
    <option value=7>7</option>
    <option value=6>6</option>
    <option value=5>5</option>
    <option value=4>4</option>
    <option value=3>3</option>
    <option value=2>2</option>
    <option value=1>1</option>
    </select><br><br>
    <b>"._YOURCOMMENT."</b><br>
    <textarea name=comments rows=10 cols=70></textarea><br>
    "._ALLOWEDHTML."<br>";
    while (list($key, $access, ) = each($AllowableHTML)) {
        if ($access > 0) echo " &lt;".$key."&gt;";
    }
    echo "<br><br>
    <input type=\"hidden\" name=\"req\" value=\"savecomment\">
    <input type=\"submit\" value=\""._SUBMIT."\">
    </font></form>";
    CloseTable();
    include("footer.php");
}

function savecomment()
{
    
    list($xanonpost,
     $uname,
     $id,
     $score,
     $comments) = pnVarCleanFromInput('xanonpost',
                      'uname',
                      'id',
                      'score',
                      'comments');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $anonymous = pnConfigGetVar('anonymous');

    // jgm - need to get review title for proper authorisation
    if (!(pnSecAuthAction(0, 'Reviews::', "::$id", ACCESS_COMMENT))) {
        include 'header.php';
        echo _REVIEWSCOMMENTNOAUTH;
        include 'footer.php';
        return;
    }

    if ($xanonpost) {
        $uname = $anonymous;
    }
    $column = &$pntable['reviews_comments_column'];
    $newid = $dbconn->GenId($pntable['reviews_comments_column']);
    $result = $dbconn->Execute("INSERT INTO $pntable[reviews_comments] ($column[cid], $column[rid], $column[userid], $column[date], $column[comments], $column[score]) VALUES (
    $newid,
        '".pnVarPrepForStore($id)."',
        '".pnVarPrepForStore($uname)."',
        now(),
        '".pnVarPrepForStore($comments)."',
        '".pnVarPrepForStore($score)."'
    )");
        if($dbconn->ErrorNo()<>0)
        {
            error_log("ERROR 4 : " . $dbconn->ErrorMsg());
        }
    pnRedirect('modules.php?op=modload&name='.$GLOBALS[ModName].'&file=index');
 }

function r_comments()
{
    

    list($id,
     $title) = pnVarCleanFromInput('id',
                       'title');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['reviews_comments_column'];
    $result = $dbconn->Execute("SELECT $column[cid], $column[userid], $column[date], $column[comments], $column[score]
                              FROM $pntable[reviews_comments]
                              WHERE $column[rid]='".pnVarPrepForStore($id)."'
                              ORDER BY $column[date] DESC");
    while(!$result->EOF) {
        list($cid, $uname, $date, $comments, $score) = $result->fields;
        $date=$result->UnixTimeStamp($date);
        OpenTable();
        $title = urldecode($title);
        echo "<font class=\"pn-normal\"><b>$title</b><br>";
        if ($uname == "Anonymous") {
            echo ""._POSTEDBY." $uname "._ON." ". ml_ftime(_DATETIMEBRIEF,GetUserTime($date))."<br>";
        } else {
            echo ""._POSTEDBY." <a class=\"pn-normal\" href=\"user.php?op=userinfo&amp;uname=$uname\">$uname</a> "._ON." ". ml_ftime(_DATETIMEBRIEF,GetUserTime($date))."<br>";
        }
        echo ""._MYSCORE." ";
        display_score($score);
        if (pnSecAuthAction(0, 'Reviews::', '::', ACCESS_DELETE)) {
            echo "<br><b>"._ADMIN."</b> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=del_comment&amp;cid=$cid&amp;id=$id\">"._DELETE."</a> ]</font><hr noshade size=1>";
        } else {
            echo "</font><hr noshade size=1>";
        }
        echo "<font class=\"pn-normal\">".pnVarPrepHTMLDisplay($comments)."</font>";
        CloseTable();
        $result->MoveNext();
    }
}

function showcontent()
{
      list($id,
     $page) = pnVarCleanFromInput('id',
                      'page');

    if(!isset($page)) {
    $page = 1;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    include ('header.php');

    // jgm - need to get review title for proper authorisation
    if (!(pnSecAuthAction(0, 'Reviews::', "::$id", ACCESS_READ))) {
        echo _REVIEWSREADNOAUTH;
        include 'footer.php';
        return;
    }

    OpenTable();
    if ($page == 1) {
        $column = &$pntable['reviews_column'];
        $result = $dbconn->Execute("UPDATE $pntable[reviews] SET $column[hits]=$column[hits]+1 WHERE $column[id]=".pnVarPrepForStore($id)."");
        if($dbconn->ErrorNo()<>0)
        {
            error_log("ERROR 5 : " . $dbconn->ErrorMsg());
        }
    }
        $column = &$pntable['reviews_column'];
        $query = "SELECT * FROM $pntable[reviews] WHERE $column[id]=".pnVarPrepForStore($id)."";
        // 0=pn_id, 1=pn_date, 2=pn_title, 3=pn_text, 4=pn_reviewer, 5=pn_email, 6=pn_score,
        // 7=pn_cover, 8=pn_url, 9=pn_url_title, 10=pn_hits, 11=pn_language
        $result = $dbconn->Execute($query);

        echo "<center><TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"3\" width=\"95%\"><tr><td width=\"100%\"><P>";

        $id        =  $result->fields[0];
        $date      = $result->fields[1];
        $year      = substr($date,0,4);
        $month     = substr($date,5,2);
        $day       = substr($date,8,2);
        $fdate     = ml_ftime(_DATELONG,mktime (0,0,0,$month,$day,$year));
        $title     = $result->fields[2];
        $text      = $result->fields[3];
        $reviewer  = $result->fields[4];
        $email     = $result->fields[5];
        $score     = $result->fields[6];
        $cover     = $result->fields[7];
        $url       = $result->fields[8];
        $url_title = $result->fields[9];
        $hits      = $result->fields[10];
        $rlanguage = $result->fields[11]; /* ML added */

    $contentpages = explode( "<!--pagebreak-->", $text );
    $pageno = count($contentpages);
    if ( $page < 1 )
        $page = 1;
    if ( $page > $pageno )
        $page = $pageno;
    $arrayelement = (int)$page;
    $arrayelement --;
        echo "<p><font class=\"pn-title\"><b><i>".pnVarPrepForDisplay($title)."</i></b></font><br>";
        echo "<p align=justify><font class=\"pn-normal\">";
        if ($cover != "")
        echo "<img src=\"modules/$GLOBALS[ModName]/images/$cover\" align=right border=1 vspace=2 alt=\"\">";
        echo "".pnVarPrepHTMLDisplay(nl2br($contentpages[$arrayelement]))."</font><p><font class=\"pn-normal\">";
        if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_EDIT)) {
            echo "<b>"._ADMIN."</b> [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=mod_review&amp;id=$id\">"._EDIT."</a> ";
            if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_DELETE)) {
                echo "| <a class=\"pn-normal\" href=modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=del_review&amp;id_del=$id>"._DELETE."</a> ";
            }
            echo "]";
        }
        echo "<br>";
        echo "<b>"._ADDED."</b>&nbsp; ".pnVarPrepForDisplay($fdate)."<br>";
        if ($reviewer != "")
        echo "<b>"._REVIEWER."</b> &nbsp;<a class=\"pn-normal\" href=\"mailto:$email\">".pnVarPrepForDisplay($reviewer)."</a><br>";
        if ($score != "")
        echo "<b>"._SCORE."</b> ";
        display_score($score);
        if ($url != "")
                echo "<br><b>"._RELATEDLINK.":</b>&nbsp; <a class=\"pn-normal\" href=\"".pnVarPrepForDisplay($url)."\" target=_BLANK>".pnVarPrepForDisplay($url_title)."</a>";
        echo "<br><b>"._HITS.":</b>&nbsp;".pnVarPrepForDisplay($hits)."";
        if (isset($rlanguage) && $rlanguage != ''){
        	echo "<br><b>"._LANGUAGE.":</b>&nbsp;".pnVarPrepForDisplay($rlanguage).""; /* ML ADDED */
    	}
    	if ($pageno > 1) {
        	echo "<br><b>"._PAGE.":</b> $page/$pageno<br>";
    	}
        echo "</font>";
        echo "</td></tr></TABLE>";
        echo "</CENTER>";

        // memory flush
        $result->Close();
    if($page >= $pageno) {
          $next_page = "";
    } else {
        $next_pagenumber = $page + 1;
        if ($page != 1) {
            $next_page .= "<img src=\"modules/$GLOBALS[ModName]/images/blackpixel.gif\" width=\"10\" height=\"2\" border=\"0\" alt=\"\"> &nbsp;&nbsp; ";
        }
        $next_page .= "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id&amp;page=$next_pagenumber\">"._NEXT." ($next_pagenumber/$pageno)</a> <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id&amp;page=$next_pagenumber\"><img src=\"modules/$GLOBALS[ModName]/images/right.gif\" border=\"0\" alt=\""._NEXT."\"></a>";
    }
    if($page <= 1) {
        $previous_page = "";
    } else {
        $previous_pagenumber = $page - 1;
        $previous_page = "<a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id&amp;page=$previous_pagenumber\"><img src=\"modules/$GLOBALS[ModName]/images/left.gif\" border=\"0\" alt=\""._PREVIOUS."\"></a> <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=showcontent&amp;id=$id&amp;page=$previous_pagenumber\">"._PREVIOUS." ($previous_pagenumber/$pageno)</a>";
    }
    echo "</td></tr>"
        ."<tr><td align=\"center\">"
        ."$previous_page &nbsp;&nbsp; $GLOBALS[next_page]<br><br>"
        ."<font class=\"pn-normal\">[ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index\">"._RBACK."</a> ]";
	if (pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_COMMENT)) {
		echo " [ <a class=\"pn-normal\" href=\"modules.php?op=modload&amp;name=$GLOBALS[ModName]&amp;file=index&amp;req=postcomment&amp;id=$id&amp;title=$title\">"._REPLYMAIN."</a> ]</font>";
	}
    CloseTable();
    if ($page == 1) {
        r_comments($id, $title);
    }
    include ("footer.php");
}

function mod_review()
{
   
    $id = pnVarCleanFromInput('id');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $currentlang = pnUserGetLang();

    if (isset($multilingual) && $multilingual == 1) {
       $querylang = "AND (language='$currentlang' OR language='')";
    } else {
        $querylang = "";
    }

    include ('header.php');

    OpenTable();
    if ($id != 0) {
        $column = &$pntable['reviews_column'];
        $query = "SELECT * FROM $pntable[reviews] WHERE $column[id]=".pnVarPrepForStore($id)."";
        // 0=pn_id, 1=pn_date, 2=pn_title, 3=pn_text, 4=pn_reviewer, 5=pn_email, 6=pn_score,
        // 7=pn_cover, 8=pn_url, 9=pn_url_title, 10=pn_hits, 11=pn_language
        $result = $dbconn->Execute($query);
        if (!$result->EOF) {
            $id        = $result->fields[0];
            $date      = $result->fields[1];
            $title     = $result->fields[2];
            $text      = $result->fields[3];
            $reviewer  = $result->fields[4];
            $email     = $result->fields[5];
            $score     = $result->fields[6];
            $cover     = $result->fields[7];
            $url       = $result->fields[8];
            $url_title = $result->fields[9];
            $hits      = $result->fields[10];
            $rlanguage = $result->fields[11]; /* ML ADDED */
        }
        else
        {
           // Might have been given an invalid ID - blank it
           $id = "";
        }
        $result->Close();
        if (!(pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_EDIT))) {
            echo _REVIEWSEDITNOAUTH;
            CloseTable();
            include 'footer.php';
            return;
        }
        echo "<center><font class=\"pn-normal\"><b>"._REVIEWMOD."</b></font></center><br><br>";
        echo "<form method=\"post\" action=\"modules.php\">
        <input type=\"hidden\" name=\"op\" value=\"modload\">
        <input type=\"hidden\" name=\"name\" value=\"$GLOBALS[ModName]\">
        <input type=\"hidden\" name=\"file\" value=\"index\">
        <input type=\"hidden\" name=\"req\" value=\"preview_review\">
        <input type=hidden name=id value=$id>";
        echo "<TABLE BORDER=0 width=100%>
                <tr>
                        <td width=12%><font class=\"pn-normal\"><b>"._RDATE."</b></font></font></td>
                        <td><INPUT TYPE=text NAME=date SIZE=15 VALUE=\"".pnVarPrepForDisplay($date)."\" MAXLENGTH=10></td>
                </tr>
                <tr>
                        <td width=12%><font class=\"pn-normal\"><b>"._RTITLE."</b></font></font></td>
                        <td><INPUT TYPE=text NAME=title SIZE=50 MAXLENGTH=150 value=\"".pnVarPrepForDisplay($title)."\"></td>
                </tr>
                <tr>
                        <td width=12%><b>"._LANGUAGE."</b></td>
                        <td><select name=\"rlanguage\" class=\"pn-text\">";

    $lang = languagelist();
        $sel_lang[$currentlang] = ' selected';
        print '<option value="">'._ALL.'</option>';
        $handle = opendir('language');
        while ($f = readdir($handle)) {
            if (is_dir("language/$f") && (!empty($lang[$f]))) {
                $langlist[$f] = $lang[$f];
            }
        }
        asort($langlist);
        //  a bit ugly, but it works in E_ALL conditions (Andy Varganov)
        foreach ($langlist as $k=>$v){
        echo '<option value="'.$k.'"';
        if (isset($sel_lang[$k])) echo ' selected';
        echo '>'. $v . '</option>\n';
        }
        echo "</select></td>
                            </tr>
                            <tr>
                             <tr>
                                    <td width=12%><b>"._RTEXT."</b></td>
                                    <td><TEXTAREA class=\"pn-normal\" name=text rows=20 wrap=virtual cols=60>".pnVarPrepHTMLDisplay($text)."</TEXTAREA></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._REVIEWER."</b></font></td>
                                    <td><INPUT TYPE=text NAME=reviewer SIZE=41 MAXLENGTH=40 value=\"".pnVarPrepForDisplay($reviewer)."\"></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._REVEMAIL."</b></font></td>
                                    <td><INPUT TYPE=text NAME=email value=\"".pnVarPrepForDisplay($email)."\" SIZE=30 MAXLENGTH=80></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._SCORE."</b></font></td>
                                    <td><INPUT TYPE=text NAME=score value=\"".pnVarPrepForDisplay($score)."\" size=3 maxlength=2></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._RLINK."</b></font></td>
                                    <td><INPUT TYPE=text NAME=url value=\"".pnVarPrepForDisplay($url)."\" size=30 maxlength=100></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._RLINKTITLE."</b></font></td>
                                    <td><INPUT TYPE=text NAME=url_title value=\"".pnVarPrepForDisplay($url_title)."\" size=30 maxlength=50></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._COVERIMAGE."</b></font></td>
                                    <td><INPUT TYPE=text NAME=cover value=\"".pnVarPrepForDisplay($cover)."\" size=30 maxlength=100></td>
                            </tr>
                            <tr>
                                    <td width=12%><font class=\"pn-normal\"><b>"._HITS.":</b></font></td>
                                    <td><INPUT TYPE=text NAME=hits value=\"".pnVarPrepForDisplay($hits)."\" size=5 maxlength=5></td>
                            </tr>
                    </TABLE>";
        echo "<input type=\"hidden\" name=\"req\" value=\"preview_review\"><input type=\"submit\" value=\""._PREMODS."\">&nbsp;&nbsp;<input type=button onClick=history.go(-1) value="._CANCEL."></form>";
    } else {
      echo _REVIEWSINVALIDID;
    }
    CloseTable();
    include ("footer.php");
}

function del_review()
{
   
    $id_del = pnVarCleanFromInput('id_del');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['reviews_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[reviews]
                              WHERE $column[id]=".pnVarPrepForStore($id_del)."");
    list($title) = $result->fields;
    $result->Close();
    if (!(pnSecAuthAction(0, 'Reviews::', "$title::$id_del", ACCESS_DELETE))) {
        echo _REVIEWSDELNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    $dbconn->Execute("DELETE FROM $pntable[reviews] WHERE {$pntable['reviews_column']['id']}=".pnVarPrepForStore($id_del)."");
    $dbconn->Execute("DELETE FROM $pntable[reviews_comments] WHERE {$pntable['reviews_comments_column']['rid']}='".pnVarPrepForStore($id_del)."'");
    pnRedirect('modules.php?op=modload&name='.$GLOBALS[ModName].'&file=index');
}

function del_comment()
{
    
    list($cid,
     $id) = pnVarCleanFromInput('cid',
                    'id');

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $column = &$pntable['reviews_column'];
    $result = $dbconn->Execute("SELECT $column[title]
                              FROM $pntable[reviews]
                              WHERE $column[id]=".pnVarPrepForStore($id)."");
    list($title) = $result->fields;
    if (!(pnSecAuthAction(0, 'Reviews::', "$title::$id", ACCESS_DELETE))) {
        echo _REVIEWSDELNOAUTH;
        CloseTable();
        include 'footer.php';
        return;
    }
    $dbconn->Execute("DELETE FROM $pntable[reviews_comments] WHERE {$pntable['reviews_comments_column']['cid']}='".pnVarPrepForStore($cid)."'");
    pnRedirect('modules.php?op=modload&name='.$GLOBALS[ModName].'&file=index');
}

if (!isset($req)) {
    $req = '';
}

if(in_array($req, $alphabet)) {
    reviews($letter=$req);
} else {

    switch($req) {

        case "showcontent":
            showcontent();
            break;

        case "write_review":
            write_review();
            break;

        case "preview_review":
            preview_review();
            break;

        case ""._YES."":
            send_review();
            break;

        case "del_review":
            del_review();
            break;

        case "mod_review":
            mod_review();
            break;

        case "postcomment":
            postcomment();
            break;

        case "savecomment":
            savecomment();
            break;

        case "del_comment":
            del_comment();
            break;

        default:
            reviews_index();
            break;
    }
}
?>
