<?
/************************************************************************/
/* Planet-nuke by Roberto Beltrame (r.beltrame@libero.it                */
/* http://www.refoscoplanet.too.it                                      */
/*                                                                      */
/* Modifica di PHP-NUKE Web Portal System                               */
/* Thanks to Francisco Burzi for idea and work!                         */
/************************************************************************/
global  $table,$themename,$bgbottom,$imgcolbottom;
    echo "<table width=100% border=0 ";
	if ($imgcolbottom==2) { 
		echo "background=\"themes/$themename/table/bg-bottom.gif\">\n"; 
	} elseif ($imgcolbottom==1)  { 
		echo "bgcolor=\"$bgbottom\">"; 
	} else { 
		echo " >\n";
	}
    echo "<tr><td>\n";
/*** slogan ***/
global $nukeurl, $slogan, $table, $foot1, $foot2, $foot3, $foot4, $themename;
    echo "<table width=100% border=0><tr>\n"
    ."<td align=\"center\">\n"
	."<font class=\"slogan\"><br>$slogan<br></font>\n"
    ."</td>\n"
	."</tr></table>\n";
/*** footer ***/
global $nukeurl, $slogan, $table, $foot1, $foot2, $foot3, $foot4, $themename;
$footer_message = "$foot1\n$foot2\n$foot3\n$foot4\n";
    echo "<table width=100% border=0><tr>\n"
    ."<td align=\"center\">$footer_message<br></td>\n"
	."</tr></table>\n"
    ."</tr></td\n>";
?>
