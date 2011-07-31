<?php // $Id: Version.php,v 1.2 2002/11/15 22:34:22 larsneo Exp $ $Name:  $

$modversion['name'] = 'News';
$modversion['version'] = '1.3';
$modversion['description'] = 'A module to display the news on your index page';
$modversion['credits'] = 'docs/credits.txt';
$modversion['help'] = 'docs/install.txt';
$modversion['changelog'] = 'docs/changelog.txt';
$modversion['license'] = 'docs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Francisco Burzi';
$modversion['contact'] = 'http://phpnuke.org';
$modversion['admin'] = 0;
$modversion['securityschema'] = array('Stories::Story' => 'Author ID:Category name:Story ID',
                                      'Stories::Category' => 'Category name::Category ID');

?>