<?
/*******************************************************************************/
/* Planet-nuke by Roberto Beltrame (r.beltrame@libero.it)                      */
/* http://www.refoscoplanet.too.it                                             */
/*                                                                             */
/* Modifica di PHP-NUKE Web Portal System                                      */
/* Thanks to Francisco Burzi for idea and work!                                */
/*******************************************************************************/
global $imgcollogo,$bglogo,$themename,$banners;
	echo "<table width=\"100%\" border=\"0\"";
    if ($imgcollogo==2) { 
		echo " background=\"themes/$themename/table/bg-logo.gif\">\n"; 
	} elseif ($imgcollogo==1) { 
		echo " bgcolor=$bglogo>\n"; 
	} else {
		echo " >\n"; 
	}
	echo "<tr>\n";
	echo "<td>\n";
	echo "<img src=\"themes/$themename/images/logo.gif\">\n";
	echo "</td>\n";
	if ($banners==1) {
	echo "<td>\n";
	include("banners.php");
	echo "</td>\n";
    } else {
	echo "<td>\n"
	."<img src=\"themes/$themename/images/ballbanner.gif\"></a>\n"
	."</td>\n";
	}
    echo "</tr></table>\n";
?>
