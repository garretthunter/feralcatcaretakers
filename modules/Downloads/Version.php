<?php // $Id: Version.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $

$modversion['name'] = 'Downloads';
$modversion['version'] = '1.31';
$modversion['description'] = 'Downloads Module';
$modversion['credits'] = 'docs/credits.txt';
$modversion['changelog'] = 'docs/changelog.txt';
$modversion['license'] = 'docs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Francisco Burzi';
$modversion['contact'] = 'http://www.phpnuke.org';
$modversion['admin'] = 0;
$modversion['securityschema'] = array('Downloads::Category' => 'Category name::Category ID',
                                      'Downloads::Item' => 'File name::File ID');

?>