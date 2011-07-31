<?php
/*********************************************************************/
/* Theme generated with Theme-editor 1.3 beta                        */
/* Copyright (c) 2002 by Roberto Beltrame (r.beltrame@libero.it)     */
/* http://beltrame-caruso.it                                         */
/*                                                                   */
/* Modify of PHP-NUKE Web Portal System                              */
/* Thanks to Francisco Burzi                                         */
/*********************************************************************/
function themeheader() {
global $themename,$themeimage;
$themename  =  Capri;
$themeimage  = Capri;
/*** global colors ***************************************************/
global $themename, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4;
global $textcolor1, $textcolor2;
$bgcolor1   = "#d5d5d5";
$bgcolor2   = "#7b91ac";
$bgcolor3   = "#efefef";
$bgcolor4   = "#d5d5d5";
$textcolor1 = "#404040";
$textcolor2 = "#404040";

// COPYRIGHTS
    echo "<!----- PLEASE DO NOT REMOVE COPYRIGHT NOTICE ----->\n";
    echo "<!----- NEED CUSTOM DESIGNS? VISIT WWW.DEZINA.COM ----->\n";
    echo "<!----- Copyright (c) 2002 Dezina.com ----->\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";


/*******************************************************************************/
global $table;
$table      = "772";
theme();
themecore();
}
/*********************************************************************************/
function theme() {
global $themename,$themeimage,$table;
echo "<!----- Start body ----->\n";
echo "<body bgcolor=#333366>\n";
echo "<table width=772 border=0 align=center cellspacing=1 cellpadding=0 bgcolor=#cccccc>\n";
echo "<tr><td>\n";
echo "<table width=772 border=0 align=center cellspacing=1 cellpadding=0 bgcolor=#cccccc>\n";
/****************************** logo iniziale **************************/
global $bglogo, $imgcollogo;
$bglogo="#333366"; $imgcollogo="1";
echo "<tr>\n";
echo "<td>\n";
include("themes/themelogo.php");
echo "</td>\n";
echo "</tr>\n";
/*******************************barra user ******************************/
global $bguser, $imgcoluser;
$bguser="#6699FF"; $imgcoluser="1";
echo "<tr>\n";
echo "<td>\n";
include("themes/themeuser.php");
echo "</td>\n";
echo "</tr>\n";
/******************************* nav bar *******************************/
global $navtable,$navbg,$navover,$navout,$navbghome,$navoverhome,$navouthome,$itemnavbar;
$navtable   = "#333366";
$navbg      = "#99CCFF";
$navover    = "#ffffff";
$navout     = "#99CCFF";
$navbghome  = "#99CCFF";
$navoverhome= "#ffffff";
$navouthome = "#99CCFF";
$itemnavbar = "3";
echo "<tr>\n";
echo "<td>\n";
include("themes/themenavbar.php");
echo "</td>\n";
echo "</tr>\n";
}
/******** themecore *****************************************************************/
function themecore() {
global $themename,$themeimage,$bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6, $table, $themeimage;
global $textcolor1, $textcolor2;
global $layout,$button,$imgorcol, $index;
echo "<tr>\n";
echo "<td>\n";
echo "<table width=\"100%\" border=0 align=center cellspacing=0 cellpadding=0  bgcolor=#cccccc>\n";
echo "<tr>\n";
     echo "<td width=8>\n";
     echo "<td width=170 valign=top align=center ><br>\n";
     themeblocksleft();
     echo "</td>\n";
     echo "<td width=4 >\n";
     echo "<td width=1>\n";
  if ($index == 1) {
$tdfooter="402";
} else {
$tdfooter="573";
} 
     echo "<td width=$tdfooter valign=top align=center><br>\n";
}
/******************************* themefooter *******************************/
function themefooter() {
global $themename,$themeimage,$bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $bgcolor5, $bgcolor6;
global $textcolor1, $textcolor2, $index;
global $layout,$button,$imgorcol;
     echo "</td>\n";
  if ($index == 1) {
     echo "<td width=1>\n";
     echo "<td width=4 >\n";
     echo "<td width=170 valign=top align=center><br>\n";
     themeblocksright();
     echo "</td>\n";
}
     echo "<td width=8>\n";
     echo "</tr>\n";
     echo "</table>\n";
     echo "</td>\n";
     echo "</tr>\n";
/******************************* nav bar bottom *************************/
global $navtable,$navbg,$navover,$navout,$navbghome,$navoverhome,$navouthome,$itemnavbar;
$navtable   = "#333366";
$navbg      = "#99CCFF";
$navover    = "#ffffff";
$navout     = "#99CCFF";
$navbghome  = "#99CCFF";
$navoverhome= "#ffffff";
$navouthome = "#99CCFF";
$itemnavbar = "3";
echo "<tr>\n";
echo "<td>\n";
include("themes/themenavbar.php");
echo "</td>\n";
echo "</tr>\n";
/*******************************  bottom ********************************/
global $bgbottom, $imgcolbottom;
$bgbottom="#cccccc"; $imgcolbottom="1";
  echo "<tr>\n";
  echo "<td>\n";
  include("themes/themebottom.php");
  echo "</td>\n";
  echo "</tr>\n";
/***********************************************************************/
  echo "</table>\n";
  echo "</td></tr></table><br>\n";
}
include("themes/themetemplate.php");
/***************************************************************/
function themeblocksleft() {
blocks(LEFT);
}
/***************************************************************/
function themeblocksright() {
$linksurvey==1;
blocks(RIGHT);
}
/***********************************************************************/
/* THEMESIDEBOX ->                                                      /
/***********************************************************************/
function themesidebox($title, $content){
$posted=""; $morelink="";
themesideboxtempl($title, $content, $posted, $morelink); 
}
function themesideboxtempl($title, $content,$posted, $morelink){
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg,$block,$typeuse,$table;
$block="D"; $topheight="18"; $botheight="18"; $tabheight="37"; $leftwidth="14"; $rightwidth="14"; 
if ($table >100) {
  if ($linksurvey == 1) {
      $tablewidth="100%"; 
       } else {
      $tablewidth="100%"; 
       }
} else {
  if ($linksurvey == 1) {
      $tablewidth="100%"; 
       } else {
      $tablewidth=""; 
       }
}
$boxtable="#cccccc"; $boxtitle="#99CCFF"; $boxbg="#cccccc"; $typeuse="B";
block_type_5($title, $content,$posted, $morelink); 
}
/***********************************************************************/
/* OPENTABLE - CLOSETABLE                                              */
/***********************************************************************/
function OpenTable() {
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block;
$block="B"; $topheight="18";$botheight="18";$tabletabheight="";$leftwidth="14"; $rightwidth="14"; $tablewidth="100%"; 
$boxtable="#636163"; $boxtitle="#636163";$boxbg="#cccccc";
OpenTable_type_5(); 
}
function CloseTable() {
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block;
$block="B"; $topheight="18"; $botheight="18";$tabletabheight="";$leftwidth="14"; $rightwidth="14"; $tablewidth="100%"; 
$boxtable="#636163"; $boxtitle="#636163"; $boxbg="#cccccc";
CloseTable_type_5(); 
}
function OpenTable2() {
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block;
$block="B"; $topheight="18"; $botheight="18"; $leftwidth="14"; $rightwidth="14"; 
$boxtable="#636163"; $boxtitle="#636163"; $boxbg="#cccccc";
OpenTable_type_5(); 
}
function CloseTable2() {
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block;
$block="B"; $topheight="18"; $botheight="18"; $leftwidth="14"; $rightwidth="14"; $tablewidth="100%"; 
$boxtable="#636163"; $boxtitle="#636163"; $boxbg="#cccccc";
CloseTable_type_5(); 
}
/***********************************************************************/
function themearticle ($aid, $informant, $datetime, $title, $thetext, $topic, $topicname, $topicimage, $topictext) {
global $admin, $sid, $tipath;
$posted = ""._POSTEDON." $datetime "._BY." "; 
$posted .= $info[informant];
if (is_admin($admin)) {
$posted .= "<br>[ <a href=\"admin.php?op=EditStory&amp;sid=$sid\">"._EDIT."</a> | <a href=\"admin.php?op=RemoveStory&amp;sid=$sid\">"._DELETE."</a> ]";
}
if ($notes != "") {
$notes = "<br><br><b>"._NOTE."</b> <i>$notes</i>";
} else {
$notes = "";
}
if ("$aid" == "$informant") {
$content = "$thetext$notes";
} else {
if($informant != "") {
$content = "<a href=\"modules.php?name=Your_Account&amp;op=userinfo&amp;uname=$informant\">$informant</a> ";
} else {
$content = "$anonymous ";
}
$content .= ""._WRITES." <i>\"$thetext\"</i>$notes";
}
$content .="<a href=\"modules.php?name=News&amp;new_topic=$topic\"><img src=\"$tipath$topicimage\" border=\"0\" Alt=\"$topictext\" align=\"right\" hspace=\"10\" vspace=\"10\"></a> ";
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block, $typeuse;
$block="D"; $topheight="18"; $botheight="18";$articletabheight=""; $leftwidth="14"; $rightwidth="14"; 
$boxtable="#333366"; $boxtitle="#333366"; $boxbg="#cccccc"; $typeuse="A";
$tablewidth="100%"; 
block_type_5($title, $content, $posted, $morelink); 
}


/***********************************************************************/
function themeindex ($aid, $informant, $time, $title, $counter, $topic, $thetext, $notes, $morelink, $topicname, $topicimage, $topictext) {
global $anonymous, $tipath;
if ($notes != "") {
$notes = "<br><br><b>"._NOTE."</b> <i>$notes</i>";
} else {
$notes = "";
}
if ("$aid" == "$informant") {
$content = "$thetext$notes
";
} else {
if($informant != "") {
   $content = "<a href=\"modules.php?name=Your_Account&amp;op=userinfo&amp;uname=$informant\">$informant</a> ";
} else {
   $content = "$anonymous ";
}
$content .= ""._WRITES." <i>\"$thetext\"</i>$notes";
}
$content .="<a href=\"modules.php?name=News&amp;new_topic=$topic\"><img src=\"$tipath$topicimage\" border=\"0\" Alt=\"$topictext\" align=\"right\" hspace=\"10\" vspace=\"10\"></a> ";
$posted = ""._POSTEDBY." ";
$posted .= $info[informant];
$posted .= " "._ON." $time $timezone ($counter "._READS.")";
global $themename,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$themeimage;
global $boxtable,$boxtitle,$boxbg, $block, $typeuse;
$block="C"; $topheight="18"; $botheight="18";$indextabheight=""; $leftwidth="14"; $rightwidth="14"; 
$boxtable="#333366"; $boxtitle="#99CCFF"; $boxbg="#cccccc"; $typeuse="I";
block_type_5 ($title, $content, $posted, $morelink); 
}

?>