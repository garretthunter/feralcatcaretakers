<?php
	$handle=opendir(WHERE_IS_PERSO.'themes');

    while ($file = readdir($handle)) {
	if ((!ereg("[.]",$file)) ) {
	    if($file != "CVS") 
				{				   
			$themelist .= "$file ";
		}
		}
	}
    closedir($handle);
?>