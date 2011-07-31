<?

/************************************************************************/
/* Planet-nuke by Roberto Beltrame (r.beltrame@libero.it                */
/* http://www.Refoscoplanet.too.it                                      */
/*                                                                      */
/* Modifica di PHP-NUKE Web Portal System                               */
/* Thanks to Francisco Burzi for idea and work!                         */
/************************************************************************/

global $user,$banners,$cookie,$prefix,$table,$index,$button,$themename,$imgcoluser,$bguser;
    cookiedecode($user);
    $username = $cookie[1];
    if ($username == "") { $username = "Anonymous";  }

if (($username == "Anonymous") OR ($op == "logout")) {
echo "<!-- Anonymous Box -->\n"
	."<table cellpadding=\"3\" cellspacing=\"0\" width=\"100%\" border=\"0\" align=\"center\"";
		if ($imgcoluser ==2) { 
			echo " background=\"themes/$themename/table/bg-user.gif\">"; 
		} elseif ($imgcoluser ==1) { 
			echo " bgcolor=\"$bguser\">"; 
		} else {
			echo " >";
		}
/*Modify by Dezina.com*/

echo "  <tr>\n"
	."   <form action=\"modules.php?name=Your_Account\" method=\"post\">\n"
	."    <td>\n"
	."    <font class=\"registered\">Nick:</font> \n"
	."    <input type=\"text\" name=\"uname\" size=\"7\" maxlength=\"25\" class=\"formtexts\">\n"
	."    <font class=\"registered\">Pass:</font> \n"
	."    <input type=\"password\" name=\"pass\" size=\"7\" maxlength=\"20\" class=\"formtexts\">\n"
	."    <input type=\"hidden\" name=\"op\" value=\"login\">\n";
echo "<input type=\"submit\" value=\"login\" class=\"pulsanti\">\n";
echo "<input type=\"submit\" value=\"register\" class=\"pulsanti\">\n";
echo "    </td>\n"
	."  </form>\n\n";
    searchuserbar();
echo "  </tr>\n"
	."</table>\n";
} else {
echo "<!-- Login User Box -->\n"
	."<table cellpadding=\"3\" cellspacing=\"0\" width=\"100%\" border=\"0\" align=\"center\" ";
		if ($imgcoluser ==2) { 
			echo " background=\"themes/$themename/table/bg-user.gif\">"; 
		} elseif ($imgcoluser ==1) { 
			echo " bgcolor=\"$bguser\">"; 
		} else {
			echo " >";
		}
echo "  <tr>\n"
	."    <td>\n"
	."    &nbsp;&nbsp;".WELCOME."<font class=registered>&nbsp; $cookie[1]!&nbsp;</font>&nbsp;&nbsp;&nbsp;</td>\n"
	."     <form action=\"modules.php?name=Your_Account?op=edituser\" method=\"post\">"
	."      <td>\n";
echo "<input type=\"submit\" value=\"profile\" class=\"pulsanti\">\n";
echo "      </td>\n"
	."     </form>\n"
	."     <form action=\"modules.php?name=Your_Account\" method=\"post\">\n"
	."      <td>";
echo "<input type=\"submit\" value=\"info\" class=\"pulsanti\">\n";
echo "      </td></form>\n"
	."       <form action=\"modules.php?name=Your_Account&amp;op=logout\" method=\"post\">\n"
	."      <td>\n";
echo "<input type=\"submit\" value=\"logout\" class=\"pulsanti\">\n";
echo "      </td></form>\n\n";
    searchuserbar();
echo "  </tr>\n"
	."</table>\n";
}

function searchuserbar() {
global $user, $banners, $sitename, $slogan, $cookie, $prefix, $dbi;
echo "<!-- Search Box -->\n"
	."<form action=\"modules.php?name=Search\" method=\"post\"><font class=\"formtexts\" color=\"#000000\">\n"
	."<td align=\"right\" >\n"
	."<font class=\"registered\">Search:</font> \n"
	."<input type=\"text\" name=\"query\" size=\"10\"class=\"formtexts\"></font>\n";
echo "<input type=\"submit\" value=\"search!\" class=\"pulsanti\">";
	echo "</td>\n"
	."</form>\n\n"
	."<form action=\"modules.php?name=Search\" method=\"post\">\n"
	."<td align=\"right\">\n"
	."<font class=\"registered\">".translate("Topics")."\n";
    $toplist = mysql_query("select topicid, topictext from $prefix"._topics." order by topictext");
    echo "<select class=\"selectbox\" name=\"topic\"onChange='submit()'>\n"
	."<option value=\"\">".translate("All Topics")."</option>\n";
    while(list($topicid, $topics) = mysql_fetch_row($toplist)) {
    if ($topicid==$topic) { $sel = "selected "; }
	echo "<option $sel value=\"$topicid\">$topics</option>\n";
	$sel = "";
}	    
echo "</select></font></td></form>\n";
/* End Modify by Dezina.com*/

}
?>

