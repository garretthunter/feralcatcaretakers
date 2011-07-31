<?
/************************************************************************/
/* Planet-nuke by Roberto Beltrame (r.beltrame@libero.it                */
/* http://www.Refoscoplanet.too.it                                      */
/*                                                                      */
/* Modifica di PHP-NUKE Web Portal System                               */
/* Thanks to Francisco Burzi for idea and work!                         */
/************************************************************************/
global $navtable,$navbg,$navover,$navout,$navbghome,$navoverhome,$navouthome,$table,$itemnavbar;
global $prefix, $dbi, $admin;
/* modified by dezina.com*/

    echo "<!-- Top Navigation Buttons -->\n"
    ."<table cellpadding=\"0\" cellspacing=\"1\" width=\"100%\" border=\"0\" align=\"center\" bgcolor=$navtable>\n"
    ."<tr>\n";
	echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='index.php'\"><a href=\"index.php\"><font class=navbar>Home</font></a></td>\n";
	echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='modules.php?name=Downloads\"><a href=\"modules.php?name=Downloads\"><font class=navbar>Downloads</font></a></td>\n";
	echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='modules.php?name=Submit_News\"><a href=\"modules.php?name=Submit_News\"><font class=navbar>Submit News</font></a></td>\n";
	echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='modules.php?name=Topics'\"><a href=\"modules.php?name=Topics\"><font class=navbar>Topics</font></a></td>\n";
      echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='modules.php?name=Top'\"><a href=\"modules.php?name=Top\"><font class=navbar>Top</font></a></td>\n";
     	echo "<td width=\"16%\" bgcolor=$navbghome class=\"cellbox\" align=\"center\" style=\"cursor:hand\" onMouseOver=\"this.style.background='$navoverhome'\" onMouseOut=\"this.style.background='$navouthome'\" onClick=\"window.location.href='modules.php?name=Your_Account'\"><a href=\"modules.php?name=Your_Account\"><font class=navbar>Your Account</font></a></td>\n";
	echo "</tr></table>\n";


?>


