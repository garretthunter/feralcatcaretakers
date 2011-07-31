<?php
include("parameters/whoisit.inc.php"); 
if (!(empty($serverName)))
	{ include("parameters/".$serverName."/config.php"); }

/* this next defined is coming before the one I put in mainfile2.php. So there are 2 possibilities, to destroy the one I put in mainfile2.php, or to let it, as it is coming after the one below, it wont affect WHERE_IS_PERSO. */

define("WHERE_IS_PERSO","parameters/".$serverName."/"); 
?>