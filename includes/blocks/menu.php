<?php
// File: $Id: menu.php,v 1.4 2002/11/15 23:42:52 larsneo Exp $
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
// Original Author of file: Jim McDonald
// Purpose of file: Display menu, with lots of options
// ----------------------------------------------------------------------

$blocks_modules['menu'] = array(
    'func_display' => 'blocks_menu_block',
    'func_edit' => 'blocks_menu_select',
    'func_update' => 'blocks_menu_update',
    'text_type' => 'Menu',
    'text_type_long' => 'Generic menu',
    'allow_multiple' => true,
    'form_content' => false,
    'form_refresh' => false,
//  'support_xhtml' => true,
    'show_preview' => true
);

pnSecAddSchema('Menublock::', 'Block title:Link name:');

function blocks_menu_block($row)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Generic check
    if (!pnSecAuthAction(0, 'Menublock::', "$row[title]::", ACCESS_READ)) {
        return;
    }

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    // Display style
    // style = 1 - simple list
    // style = 2 - drop-down list

    // Title
    $block['title'] = $row['title'];

    // Styling
    if (empty($vars['style'])) {
        $vars['style'] = 1;
    }
    $block['content'] = startMenuStyle($vars['style']);

    $content = 0;

    // nkame: must start with some blank line, otherwise we're not able to
    // chose the first option in case of a drop-down menu.
    // a better solution would be to detect where we are, and adjust the selected
    // option in the list, and only add a blank line in case of no recognition.
    if($vars['style'] == 2)
        $block['content'] .= addMenuStyledUrl($vars['style'], "", "", "");

    // Content
    if (!empty($vars['content'])) {
        $contentlines = explode("LINESPLIT", $vars['content']);
        foreach ($contentlines as $contentline) {
            list($url, $title, $comment) = explode('|', $contentline);
            if (pnSecAuthAction(0, "Menublock::", "$row[title]:$title:", ACCESS_READ)) {
                $block['content'] .= addMenuStyledUrl($vars['style'], pnVarPrepForDisplay($title), $url, pnVarPrepForDisplay($comment));
                $content = 1;
            }
        }
    }

    // Modules
    if (!empty($vars['displaymodules'])) {
        $mods = pnModGetUserMods();

        // Separate from current content, if any
        if ($content == 1) {
            $block['content'] .= addMenuStyledUrl($vars['style'], "", "", "");
        }

        foreach($mods as $mod) {
// jgm - need to work back ML into modules table somehow
//            if (file_exists("modules/$mod/modname.php")) {
//                include "modules/$mod/modname.php";
//            } else {

            if (pnSecAuthAction(0, "$mod[name]::", "::", ACCESS_OVERVIEW)) {
                switch($mod['type']) {
                    case 1:
                        $block['content'] .= addMenuStyledUrl($vars['style'], pnVarPrepForDisplay($mod['displayname']), "modules.php?op=modload&name=" . pnVarPrepForDisplay($mod['directory']) . "&file=index", pnVarPrepForDisplay($mod['description']));
                        $content = 1;
                        break;
                    case 2:
                        $block['content'] .= addMenuStyledUrl($vars['style'],
                                                              pnVarPrepForDisplay($mod['displayname']),
                                                              pnModURL($mod['name'],
                                                                       'user',
                                                                       'main'),
                                                              pnVarPrepForDisplay($mod['description']));
                        $content = 1;
                        break;
                }

            }
        }

    }

    // Waiting content
    if (!empty($vars['displaywaiting'])) {
        // Separate from current content, if any
        if ($content == 1) {
            $block['content'] .= addMenuStyledUrl($vars['style'], "", "", "");
        }


        $header = 0;
        if (pnSecAuthAction(0, "Stories::Story", "::", ACCESS_ADD)) {
            $result = $dbconn->Execute("SELECT count(1) FROM $pntable[queue]
                                      WHERE {$pntable['queue_column']['arcd']}=0");
            if ($dbconn->ErrorNo() == 0) {
                list($qnum) = $result->fields;
                $result->Close();
                if ($qnum) {
                    if ($header == 0) {
                        $block['content'] .= addMenuStyledUrl($vars['style'], "<strong>" . _WAITINGCONT. "</strong>", "", "");
                        $header = 1;
                    }
                    $block['content'] .= addMenuStyledUrl($vars['style'], _SUBMISSIONS.": $qnum", "admin.php?module=NS-AddStory&op=submissions", "");
                    $content = 1;
                }
            }
        }

        if (pnSecAuthAction(0, "Reviews::", "::", ACCESS_ADD)) {
            $result = $dbconn->Execute("SELECT count(1) FROM $pntable[reviews_add]");
            if ($dbconn->ErrorNo() == 0) {
                list($rnum) = $result->fields;
                $result->Close();
                if ($rnum) {
                    if ($header == 0) {
                        $block['content'] .= addMenuStyledUrl($vars['style'], "<strong>" . _WAITINGCONT. "</strong>", "", "");
                        $header = 1;
                    }
                    $block['content'] .= addMenuStyledUrl($vars['style'], _WREVIEWS.": $rnum", "admin.php?module=Reviews&op=main", "");
                    $content = 1;
                }
            }
        }

        if (pnSecAuthAction(0, "Web Links::Link", "::", ACCESS_ADD)) {
            $result = $dbconn->Execute("SELECT count(1) FROM $pntable[links_newlink]");
            if ($dbconn->ErrorNo() == 0) {
                list($lnum) = $result->fields;
                $result->Close();
                if ($lnum) {
                    if ($header == 0) {
                        $block['content'] .= addMenuStyledUrl($vars['style'], "<strong>" . _WAITINGCONT. "</strong>", "", "");
                        $header = 1;
                    }
                    $block['content'] .= addMenuStyledUrl($vars['style'], _WLINKS.": $lnum", "admin.php?module=Web_Links&op=main", "");
                    $content = 1;
                }
            }
        }

        if (pnSecAuthAction(0, "Downloads::Item", "::", ACCESS_ADD)) {
            $result = $dbconn->Execute("SELECT count(1) FROM $pntable[downloads_newdownload]");
            if ($dbconn->ErrorNo() == 0) {
                list($dnum) = $result->fields;
                $result->Close();
                if ($dnum) {
                    if ($header == 0) {
                        $block['content'] .= addMenuStyledUrl($vars['style'], "<strong>" . _WAITINGCONT. "</strong>", "", "");
                        $header = 1;
                    }
                    $block['content'] .= addMenuStyledUrl($vars['style'], _WDOWNLOADS.": $dnum", "admin.php?module=Downloads&op=main", "");
                    $content = 1;
                }
            }
        }

        if (pnSecAuthAction(0, "FAQ::", "::", ACCESS_ADD)) {
            $faqcolumn = &$pntable['faqanswer_column'];
            $result = $dbconn->Execute("SELECT count(1) FROM $pntable[faqanswer] WHERE $faqcolumn[answer]=''");
            if ($dbconn->ErrorNo() == 0) {
                list($fnum) = $result->fields;
                $result->Close();
                if ($fnum) {
                    if ($header == 0) {
                        $block['content'] .= addMenuStyledUrl($vars['style'], "<strong>" . _WAITINGCONT. "</strong>", "", "");
                        $header = 1;
                    }
                    $block['content'] .= addMenuStyledUrl($vars['style'], _FQUESTIONS.": $fnum", "admin.php?module=FAQ&op=FaqCatUnanswered", "");
                    $content = 1;
                }
            }
        }
    }

    // Styling
    $block['content'] .= endMenuStyle($vars['style']);

    if ($content) {
        $row['title'] = $block['title'];
        $row['content'] = $block['content'];
        return themesideblock($row);
    }
}


function blocks_menu_select($row)
{
    global $pntheme;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);
    $row['content'] = "";

    // Defaults
    if (empty($vars['style'])) {
        $vars['style'] = 1;
    }

    // What style of menu
    $output = '<tr><td class="pn-title">'._MENU_FORMAT.'</td><td></td></tr>';

    $output .= '<tr><td class="pn-normal">'._MENU_AS_LIST.':</td><td><input type="radio" name="style" value="1"';
    if ($vars['style'] == 1) {
        $output .= ' checked';
    }
    $output .= '></td></tr><tr><td class="pn-normal">'._MENU_AS_DROPDOWN.':</td><td><input type="radio" name="style" value="2"';
    if ($vars['style'] == 2) {
        $output .= ' checked';
    }
    $output .= ' /></td></tr>';

    // What to display
    $output .= '<tr><td class="pn-title">'._DISPLAY.'</td><td></td></tr>';

    $output .= '<tr><td class="pn-normal">'._MENU_MODULES.':</td><td><input type="checkbox" value="1" name="displaymodules"';
    if (!empty($vars['displaymodules'])) {
        $output .= ' checked';
    }

    $output .= ' /></td></tr><tr><td class="pn-normal">'._WAITINGCONT.':</td><td><input type="checkbox" value="1" name="displaywaiting"';
    if (!empty($vars['displaywaiting'])) {
        $output .= ' checked';
    }
    $output .= ' /></td></tr>';

    // Content
    $c=1;
    $output .= "</table><table>";
    $output .= "<tr><td valign=\"top\" class=\"pn-title\">"._MENU_CONTENT
    .":</td><td><table border=\"1\"><tr><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\"><b>"
    ._TITLE."</b></td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\"><b>"
    ._URL."</b></td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\"><b>"
    ._MENU_DESCRIPTION."&nbsp;</b><span class=\"pn-sub\"><b>("._OPTIONAL.")</b></span></td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\"><b>"
    ._DELETE."</b></td><td align=\"center\" class=\"pn-normal\" style=\"color:$pntheme[table_header_text]; background-color:$pntheme[table_header]; text-align:center\"><b>"._INSERT_BLANK_AFTER."</b></td></tr>";
    if (!empty($vars['content'])) {
        $contentlines = explode("LINESPLIT", $vars['content']);
        foreach ($contentlines as $contentline) {
            $link = explode('|', $contentline);
            $output .= "<tr><td valign=\"top\"><input type=\"text\" name=\"linkname[$c]\" size=\"30\" maxlength=\"255\" value=\"" . pnVarPrepForDisplay($link[1]) . "\" class=\"pn-normal\"></td><td valign=\"top\"><input type=\"text\" name=\"linkurl[$c]\" size=\"30\" maxlength=\"255\" value=\"" . pnVarPrepForDisplay($link[0]) . "\" class=\"pn-normal\"></td><td valign=\"top\"><input type=\"text\" name=\"linkdesc[$c]\" size=\"30\" maxlength=\"255\" value=\"" . pnVarPrepForDisplay($link[2]) . "\" class=\"pn-normal\" /></td><td valign=\"top\"><input type=\"checkbox\" name=\"linkdelete[$c]\" value=\"1\" class=\"pn-normal\"></td><td valign=\"top\"><input type=\"checkbox\" name=\"linkinsert[$c]\" value=\"1\" class=\"pn-normal\" /></td></tr>\n";
            $c++;
        }
    }

    $output .= "<tr><td><input type=\"text\" name=\"new_linkname\" size=\"30\" maxlength=\"255\" class=\"pn-normal\" /></td><td><input type=\"text\" name=\"new_linkurl\" size=\"30\" maxlength=\"255\" class=\"pn-normal\" /></td><td class=\"pn-normal\"><input type=\"text\" name=\"new_linkdesc\" size=\"30\" maxlength=\"255\" class=\"pn-normal\" /></td><td class=\"pn-normal\">"._NEWONE."</td><td class=\"pn-normal\"><input type=\"checkbox\" name=\"new_linkinsert\" value=\"1\" class=\"pn-normal\" /></td></tr>\n";
    $output .= '</table></td></tr>';

    return $output;

}

function blocks_menu_update($row)
{
    list($vars['displaymodules'],
         $vars['displaywaiting'],
         $vars['style'])
      = pnVarCleanFromInput('displaymodules',
                            'displaywaiting',
                            'style');

    // Defaults
    if (empty($vars['displaymodules'])) {
        $vars['displaymodules'] = 0;
    }
    if (empty($vars['displaywaiting'])) {
        $vars['displaywaiting'] = 0;
    }
    if (empty($vars['style'])) {
        $vars['style'] = 1;
    }

    // User links
    $content = array();
    $c = 1;
    if (isset($row['linkname'])) {
        list($linkurl, $linkname, $linkdesc) = pnVarCleanFromInput('linkurl', 'linkname', 'linkdesc');
        foreach ($row['linkname'] as $v) {
            if (!isset($row['linkdelete'][$c])) {
                $content[] = "$linkurl[$c]|$linkname[$c]|$linkdesc[$c]";
            }
            if (isset($row['linkinsert'][$c])) {
                $content[] = "||";
            }
            $c++;
        }
    }
    if ($row['new_linkname']) {
       $content[] = pnVarCleanFromInput('new_linkurl').'|'.pnVarCleanFromInput('new_linkname').'|'.pnVarCleanFromInput('new_linkdesc');
    }
    $vars['content'] = implode("LINESPLIT", $content);

    $row['content']=pnBlockVarsToContent($vars);

    return($row);
}

function startMenuStyle($style)
{
    // Nothing to do for style == 1 (bullet list)
    $content = "";
    if ($style == 2) {
        $content = "<br><center><form method=\"post\" action=\"index.php\"><select class=\"pn-text\" name=\"newlanguage\" onChange=\"top.location.href=this.options[this.selectedIndex].value\">";
    }

    return $content;
}

function endMenuStyle($style)
{
    // Nothing to do for style == 1 (bullet list)
    $content = "";
    if ($style == 2) {
        $content = "</select></form></center>";
    }

    return $content;
}

function addMenuStyledUrl($style, $name, $url, $comment)
{
    if ($style == 1) {
        // Bullet list
        if (empty($url)) {
            // Separator
            if (empty($name)) {
                $content = "<br />";
            } else {
                $content = "<br /><b>$name</b><br />";
            }
        } else {
        switch ($url[0]) // Used to allow support for linking to modules with the use of bracket
        {
            case '[': // old style module link
            {
                $url = explode(':', substr($url, 1,  - 1));
                $url = 'modules.php?op=modload&amp;name='.$url[0].'&amp;file='.((isset($url[1])) ? $url[1]:'index');
                break;
            }
            case '{': // new module link
            {
                $url = explode(':', substr($url, 1,  - 1));
                $url = 'index.php?module='.$url[0].'&amp;func='.((isset($url[1])) ? $url[1]:'main');
                break;
            }
        }  // End Bracket Linking
            $content = "<strong><big>&middot;</big></strong>&nbsp;<a class=\"pn-normal\" href=\"$url\" title=\"$comment\">$name</a><br />";
        }
    } else if ($style == 2) {
        // Drop-down lilst
        if (empty($url)) {
            // Separator
            $content = "<option>-----</option>";
            if (!empty($name)) {
                $content .= "<option>$name</option>";
                $content .= "<option>-----</option>";
            }
        } else {

        switch ($url[0])  // Used to allow support for linking to modules with the use of bracket
        {
            case '[': // module link
            {
                $url = explode(':', substr($url, 1,  - 1));
                $url = 'modules.php?op=modload&amp;name='.$url[0].'&amp;file='.((isset($url[1])) ? $url[1]:'index');
                break;
            }
            case '{': // new module link
            {
                $url = explode(':', substr($url, 1,  - 1));
                $url = 'index.php?module='.$url[0].'&amp;func='.((isset($url[1])) ? $url[1]:'main');
                break;
            }
        } // End bracket linking.
            $content = "<option value=\"$url\">$name</option>";
        }
    }

    return $content;
}
?>