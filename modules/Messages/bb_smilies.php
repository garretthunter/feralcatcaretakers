<?php
// File: $Id: bb_smilies.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $ $Name:  $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Francisco Burzi
// Purpose of file: 
// ----------------------------------------------------------------------

?>

<html>
<head>
<style type="text/css">
<!--

<?php
echo "TD {font-size: 10pt; font-family: $site_font}\n";
echo "BODY {font-size: 10pt; font-family: $site_font}\n";
echo "-->\n</style>\n";
echo "<title>$sitename</title>\n";
?>
</head>

<BODY>
<p><br><p>
<center>
<TABLE width="<?php echo $table_width?>">
<tr><td colspan=3 bgcolor="<?php echo $bgcolor1?>">
<FONT SIZE="1" >
<center>
S M I L I E S
</CENTER>
<p align=center>
Smilies are small graphical images that can be used to convey an emotion or feeling.
<P>
</td></tr>
<TR bgcolor="<?php echo $bgcolor2?>">
<TR bgcolor="<?php echo $bgcolor2?>">
<TD><FONT SIZE="2" COLOR="<?php echo $textcolor2;?>"><b>What to Type</b></FONT></td>
<TD><FONT SIZE="2" COLOR="<?php echo $textcolor2;?>"><b>Emotion</b></FONT></td>
<TD><FONT SIZE="2" COLOR="<?php echo $textcolor2;?>"><b>Graphic That Will Appear</b></FONT></td>
</tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:)</FONT></td><td><FONT SIZE="1" > Smile&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_smile.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:-)</FONT></td><td><FONT SIZE="1" > Smile&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_smile.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:(</FONT></td><td><FONT SIZE="1" > Frown&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_frown.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:-(</FONT></td><td><FONT SIZE="1" > Frown&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_frown.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:-D</FONT></td><td><FONT SIZE="1" > Big Grin&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_biggrin.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:D</FONT></td><td><FONT SIZE="1" > Big Grin&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_biggrin.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >;)</FONT></td><td><FONT SIZE="1" > Wink&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_wink.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >;-)</FONT></td><td><FONT SIZE="1" > Wink&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_wink.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:o</FONT></td><td><FONT SIZE="1" > Eek&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_eek.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:O</FONT></td><td><FONT SIZE="1" > Eek&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_eek.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:-o</FONT></td><td><FONT SIZE="1" > Eek&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_eek.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:-O</FONT></td><td><FONT SIZE="1" > Eek&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_eek.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >8)</FONT></td><td><FONT SIZE="1" > Cool&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_cool.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >8-)</FONT></td><td><FONT SIZE="1" > Cool&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_cool.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:?</FONT></td><td><FONT SIZE="1" > Confused&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_confused.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:-?</FONT></td><td><FONT SIZE="1" > Confused&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_confused.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:p</FONT></td><td><FONT SIZE="1" > Razz&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_razz.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:P</FONT></td><td><FONT SIZE="1" > Razz&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_razz.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:-p</FONT></td><td><FONT SIZE="1" > Razz&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_razz.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:-P</FONT></td><td><FONT SIZE="1" > Razz&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_razz.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor1?>"><TD><FONT SIZE="2" >:-|</FONT></td><td><FONT SIZE="1" > Mad&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_mad.gif"></td></tr>
<TR BGCOLOR="<?php echo $bgcolor3?>"><TD><FONT SIZE="2" >:|</FONT></td><td><FONT SIZE="1" > Mad&nbsp;</FONT></td><td> <IMG SRC="images/smilies/icon_mad.gif"></td></tr>
<tr><td colspan=3 bgcolor="<?php echo $color1?>">
<FONT SIZE="1" color=FFFFFF>
<center>
<P>
Note: you may disable smilies in any post you are making, if you like.  Look for the "Disable Smilies" box on each post page, if you want to turn off smilie conversion in your particular post.
</font>
</td></tr></table>
</center>
<br><br>
</body>
</html>