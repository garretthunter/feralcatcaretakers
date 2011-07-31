<?php // $Id: Version.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $

$modversion['name'] = 'Sections';
$modversion['version'] = '1.0';
$modversion['description'] = 'Displays Extra Sections on Site';
$modversion['credits'] = 'docs/credits.txt';
$modversion['help'] = 'docs/install.txt';
$modversion['changelog'] = 'docs/changelog.txt';
$modversion['license'] = 'docs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Francisco Burzi';
$modversion['contact'] = 'http://www.phpnuke.org';
$modversion['admin'] = 0;
$modversion['securityschema'] = array('Sections::Section' => 'Section name::Section ID',
                                      'Sections::Article' => 'Article name:Section name:Article ID');

?>