<?
/*******************************************************************************/
/* Planet-nuke by Roberto Beltrame (r.beltrame@libero.it)                      */
/* http://www.Refoscoplanet.too.it                                             */
/*                                                                             */
/* Modifica di PHP-NUKE Web Portal System                                      */
/* Thanks to Francisco Burzi for idea and work!                                */
/*******************************************************************************/


/***********************************************************************************/
/* TEMPLATE BLOCKS
/***********************************************************************************/
/* title (image): YES  (MUST BE BIG=IMAGE)
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_1($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth;
global $boxtable,$boxtitle,$boxbg,$typeuse;
echo "<table width=$tablewidth bgcolor=$boxtable cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n"
."<tr>\n"
."    <td>\n"
."<table width=100% bgcolor=$boxtitle cellspacing=\"1\" cellpadding=\"0\" border=\"0\">\n"
."<tr>\n"
."    <td>\n"
."	<table width=100% bgcolor=$boxtitle cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n"
."<tr>\n"
."    <td></td><td align=\"left\"><font class=\"boxtitle\" >$title</font></td>\n"
."</tr>\n"
."</table>\n"
."<table width=100% bgcolor=$boxbg  cellspacing=\"2\" cellpadding=\"2\" border=\"0\">\n"
."<tr>\n"
."    <td>";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
."</tr>\n"
."</table>\n"
."	</td>\n"

."</tr>\n"
."</table>\n"
."	</td>\n"
."</tr>\n"
."</table><br>\n";
}
/***********************************************************************************/
/* title (image): YES  (MUST BE BIG=IMAGE)
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_2 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth;
global $boxtable,$boxtitle,$boxbg,$typeuse;
echo "<table width=$tablewidth bgcolor=$boxtable cellspacing=\"1\" cellpadding=\"0\" border=\"0\">\n"
."<tr>\n"
."    <td>\n"
."	<table width=100% bgcolor=$boxtitle cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n"
."<tr>\n"
."    <td></td><td align=\"left\"><font class=\"boxtitle\" >$title</font></td>\n"
."</tr>\n"
."</table>\n"
."<table width=100% bgcolor=$boxbg  cellspacing=\"2\" cellpadding=\"2\" border=\"0\">\n"
."<tr>\n"
."    <td>";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
."</tr>\n"
."</table>\n"
."	</td>\n"
."</tr>\n"
."</table><br>\n";
}
/***********************************************************************************/
/* title (image): 
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_3 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth;
global $boxtable,$boxtitle,$boxbg,$typeuse;
echo "<table bgcolor=$boxtable border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=$tablewidth>\n"
	."<tr>\n"
	."<td bgcolor=$boxtitle>\n"
    ."<font class=boxtitle>$title</font>\n"
	."</td>\n"
	."</tr>\n"
	."<tr valign=\"top\">\n"
	."<td bgcolor=$boxbg>\n";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
	."</tr>\n"
	."</table>\n"
	."<br>\n";
}
/***********************************************************************************/
/* title (image): 
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_4 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth;
global $boxtable,$boxtitle,$boxbg,$typeuse;
echo "<table bgcolor=$boxtable border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=$tablewidth>\n"
	."<tr>\n"
	."<td bgcolor=$boxtitle>\n"
    ."<font class=boxtitle>$title</font>\n"
	."</td>\n"
	."</tr>\n"
	."<tr valign=\"top\">\n"
	."<td bgcolor=$boxbg>\n";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
	."</td>\n"
	."</tr>\n"
	."</table>\n"
	."<br>\n";
}

/***********************************************************************************/
/* title (image): YES  (MUST BE LITTLE=ICON) title-blockname.gif
/* title (text) : YES
/* background: IMAGES
/* border HTML: NO
/* frame image: YES
/* tab frame image: YES
/* end dividers: YES (orizpixel.gif)
/***********************************************************************************/
function block_type_5 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$tabeight,$leftwidth,$rightwidth,$tablewidth,$block,$index;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo ""
    ."<TABLE width=$tablewidth border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n"
	."<TR>\n"
    	."<TD background=\"themes/$themeimage/table/$block-top-left.gif\"      height=$topheight width=$leftwidth> </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-top-center.gif\"    height=$topheight align=center><font class=boxtitle>$title</font></TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-top-right.gif\"     height=$topheight width=$rightwidth> </TD>\n"
    ."</TR>\n"
    ."<TR>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-left.gif\"                 width=$leftwidth> </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-center.gif\"                                 >\n";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                     ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                     ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                     ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
		."</TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-right.gif\"                width=$rightwidth> </TD>\n"
    ."</TR>\n"
    ."<TR>\n" 
        ."<TD background=\"themes/$themeimage/table/$block-bottom-left.gif\"   height=$botheight width=$leftwidth ></TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-bottom-center.gif\" height=$botheight>              </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-bottom-right.gif\"  height=$botheight width=$rightwidth ></TD>\n"
    ."</TR>\n"
    ."<TR>\n"
        ."</TR>\n"

."</TABLE><br>\n";
}

/***********************************************************************************/
/* title (image): YES  (MUST BE BIG=IMAGE)
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_6 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$tabheight,$leftwidth,$rightwidth,$tablewidth,$block,$index;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo ""
    ."<TABLE width=$tablewidth border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n"
	."<TR>\n"
    	."<TD background=\"themes/$themeimage/table/$block-top-left.gif\"      height=$topheight width=$leftwidth> </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-top-center.gif\"    height=$topheight align=center></TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-top-right.gif\"     height=$topheight width=$rightwidth> </TD>\n"
    ."</TR>\n"
		."<TR>\n"
    	."<TD background=\"themes/$themeimage/table/$block-tab-left.gif\"      height=$tabheight width=$leftwidth> </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-tab-center.gif\"    height=$tabheight align=\"center\" valign=\"middle\"><font class=boxtitle>$title</font></TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-tab-right.gif\"     height=$tabheight width=$rightwidth> </TD>\n"
    ."</TR>\n"
    ."<TR>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-left.gif\"                 width=$leftwidth> </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-center.gif\"                                 >\n";
    if     ($typeuse=="B") { echo "$content\n"; } 
	elseif ($typeuse=="A") { echo "$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$content  <hr noshade width=\"98%\" size=\"1\">\n"; }
	elseif ($typeuse=="I") { echo "$content  <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$posted   <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
	echo "</td>\n"
		."</TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-middle-right.gif\"                width=$rightwidth> </TD>\n"
    ."</TR>\n"
    ."<TR>\n" 
        ."<TD background=\"themes/$themeimage/table/$block-bottom-left.gif\"   height=$botheight width=$leftwidth ></TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-bottom-center.gif\" height=$botheight>              </TD>\n"
        ."<TD background=\"themes/$themeimage/table/$block-bottom-right.gif\"  height=$botheight width=$rightwidth ></TD>\n"
    ."</TR>\n"
    ."<TR>\n"
        ."</TR>\n"

."</TABLE><br>\n";
}

/***********************************************************************************/
/* title (image): 
/* title (text) : NO
/* background: IMAGES
/* border HTML: NO
/* frame image: NO
/* tab frame image: NO
/***********************************************************************************/
function block_type_7 ($title, $content, $posted, $morelink) {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth;
global $boxtable,$boxtitle,$boxbg,$typeuse;
echo "<table bgcolor=$boxtable border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=$tablewidth>\n"
	."<tr>\n"
	."<td>\n"
	."<table bgcolor=$boxtable border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=$tablewidth>\n"
	."<tr>\n"
	."<td bgcolor=$boxtitle>\n"
	."<font class=boxtitle>$title</font>\n";
	if ($typeuse=="A") { echo "<br>$posted\n"; }
	echo "</td>"
	."</tr>\n"
	."</table>\n"
	."</td>\n"
	."</tr>\n"
	."<tr valign=\"top\">\n"
	."<td bgcolor=$boxbg>\n"
	."$content\n";
     if ($typeuse=="I") { echo "<br>$posted <hr noshade width=\"98%\" size=\"1\">\n"
			                   ."$morelink <hr noshade width=\"98%\" size=\"1\">\n"; }
			                   
	echo "</td>\n"
	."</tr>\n"
	."</table>\n"
	."<br>\n";
}


/***************************************************************************************/
/* TEMPLATE TABLES
/***************************************************************************************/
function OpenTable_type_1() {
    global $themeimage;
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"8\" >";
	echo "<tr><td>\n";
}
function CloseTable_type_1() {
    echo "</td></tr></table>\n";
}

function OpenTable2_type_1() {
    global $themeimage;
    echo "<table width=\"100%\" table border=\"0\" cellspacing=\"2\" cellpadding=\"8\" >";
}
function CloseTable2_type_1() {
    echo "</td></tr></table>\n";
}
/***************************************************************************************/
function OpenTable_type_2() {
    global $themeimage;
    echo "<table width=\"100%\"  border=\"1\" cellspacing=\"2\" cellpadding=\"8\" >";
	echo "<tr><td>\n";
}
function CloseTable_type_2() {
    echo "</td></tr></table>\n";
}

function OpenTable2_type_2() {
    global $themeimage;
    echo "<table width=\"100%\" table border=\"1\" background=\"themes/$themeimage/table/bg-opentable.gif\" width=\"100%\" border=\"1\" cellspacing=\"2\" cellpadding=\"8\" >";
}
function CloseTable2_type_2() {
    echo "</td></tr></table>\n";
}

/***************************************************************************************/
function OpenTable_type_3() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "<table width=\"100%\" bgcolor=$boxtable width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" ><tr><td height=20>&nbsp;<br>&nbsp;\n";
    echo "<table width=\"100%\" bgcolor=$boxtitle width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\" ><tr><td  >\n";
    echo "<table width=\"100%\" bgcolor=$boxbg    width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" ><tr><td>\n";
}
function CloseTable_type_3() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "</td></tr></table></td></tr></table></td></tr></table><br>\n";
}

function OpenTable2_type_3() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "<table border=\"1\" cellspacing=\"1\" cellpadding=\"8\" bgcolor=\"$bgcolor1\"><tr><td>\n";
}
function CloseTable2_type_3() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "</td></tr></table>\n";
}
/***************************************************************************************/
function OpenTable_type_5() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=center width=\"100%\">";
    echo "<tr>";
    echo "<td background=\"themes/$themeimage/table/$block-top-left.gif\"   height=$topheight width=$leftwidth></td>";
    echo "<td background=\"themes/$themeimage/table/$block-top-center.gif\" height=$topheight>             </td>";
    echo "<td background=\"themes/$themeimage/table/$block-top-right.gif\"  height=$topheight width=$rightwidth></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td background=\"themes/$themeimage/table/$block-middle-left.gif\" width=$leftwidth></td>";
    echo "<td background=\"themes/$themeimage/table/$block-middle-center.gif\">";
}
function CloseTable_type_5() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
global $boxtable,$boxtitle,$boxbg,$typeuse;
    echo "</td>";
    echo "<td background=\"themes/$themeimage/table/$block-middle-right.gif\" width=$rightwidth></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td background=\"themes/$themeimage/table/$block-bottom-left.gif\"   height=$botheight width=$leftwidth></td>";
    echo "<td background=\"themes/$themeimage/table/$block-bottom-center.gif\" height=$botheight>             </td>";
    echo "<td background=\"themes/$themeimage/table/$block-bottom-right.gif\"  height=$botheight width=$rightwidth></td>";
    echo "</tr>";
    echo "</table>";
}

function OpenTable2_type_5() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
    echo "<table border=\"1\" width=\"100%\" cellspacing=\"1\" cellpadding=\"8\">";
	echo "<tr><td>\n";
}
function CloseTable2_type_5() {
global $themeimage,$topheight,$botheight,$leftwidth,$rightwidth,$tablewidth, $block;
    echo "</td></tr></table>\n";
}
/***************************************************************************************/

function FormatStory_type_1($thetext, $notes, $aid, $informant) {
    global $anonymous;
    if ($notes != "") {
	$notes = "<hr><b>"._NOTE."</b> <font class=note>$notes</font>\n <br>";

    } else {
	$notes = "";
    }
    if ("$aid" == "$informant") {
	echo "<hr><font class=textinformant>$thetext<br>$notes</font><hr>\n";

    } else {
	if($informant != "") {
	    $boxstuff = " ";
	} else {
	    $boxstuff = " ";
	}
	$boxstuff .= " \"$thetext\"<br><br>$notes\n";
	echo "<hr><font class=textanonymous>$boxstuff</font><hr>\n";
    }
}
?>