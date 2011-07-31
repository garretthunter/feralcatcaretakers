<?php
/* things could be changed here, SERVER_NAME may be useless in certain      
configurations. For instance, instead of using SERVER_NAME, you may have to      
use HTTP_HOST. Also, with subdomains, you may want to suppress the first level      
of the domain name, and, to continue with my example of "linux.mouzaia", you      
may rather use "mouzaia". */
// modification mouzaia .71
global $SERVER_NAME;
// -------------------
$serverName = $SERVER_NAME;
$serverName = str_replace("www","",$serverName);
$serverName = str_replace("essai","",$serverName);
$serverName = str_replace(".org","",$serverName);
$serverName = str_replace(".net","",$serverName); 
$serverName = str_replace(".com","",$serverName);
?>