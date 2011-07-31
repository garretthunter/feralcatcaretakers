<?php
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
// Original Author of file: phpBB team
// Purpose of file: bbcode javascript
// ----------------------------------------------------------------------
    echo "function x () {\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "function DoSmilie(addSmilie) {\n";
    echo "\n";
    echo "var addSmilie;\n";
    echo "var revisedMessage;\n";
    echo "var currentMessage = document.coolsus.message.value;\n";
    echo "revisedMessage = currentMessage+addSmilie;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "function DoPrompt(action) {\n";
    echo "var revisedMessage;\n";
    echo "var currentMessage = document.coolsus.message.value;\n";
    echo "\n";
    echo "if (action == \"url\") {\n";
    echo "var thisURL = prompt(\"Enter the URL for the link you want to add.\", \"http://\");\n";
    echo "var thisTitle = prompt(\"Enter the web site title\", \"Page Title\");\n";
    echo "var urlBBCode = \"[URL=\"+thisURL+\"]\"+thisTitle+\"[/URL]\";\n";
    echo "revisedMessage = currentMessage+urlBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"email\") {\n";
    echo "var thisEmail = prompt(\"Enter the email address you want to add.\", \"\");\n";
    echo "var emailBBCode = \"[EMAIL]\"+thisEmail+\"[/EMAIL]\";\n";
    echo "revisedMessage = currentMessage+emailBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"bold\") {\n";
    echo "var thisBold = prompt(\"Enter the text that you want to make bold.\", \"\");\n";
    echo "var boldBBCode = \"[B]\"+thisBold+\"[/B]\";\n";
    echo "revisedMessage = currentMessage+boldBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"italic\") {\n";
    echo "var thisItal = prompt(\"Enter the text that you want to make italic.\", \"\");\n";
    echo "var italBBCode = \"[I]\"+thisItal+\"[/I]\";\n";
    echo "revisedMessage = currentMessage+italBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"image\") {\n";
    echo "var thisImage = prompt(\"Enter the URL for the image you want to display.\", \"http://\");\n";
    echo "var imageBBCode = \"[IMG]\"+thisImage+\"[/IMG]\";\n";
    echo "revisedMessage = currentMessage+imageBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"quote\") {\n";
    echo "var quoteBBCode = \"[QUOTE]  [/QUOTE]\";\n";
    echo "revisedMessage = currentMessage+quoteBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"code\") {\n";
    echo "var codeBBCode = \"[CODE]  [/CODE]\";\n";
    echo "revisedMessage = currentMessage+codeBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"listopen\") {\n";
    echo "var liststartBBCode = \"[LIST]\";\n";
    echo "revisedMessage = currentMessage+liststartBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"listclose\") {\n";
    echo "var listendBBCode = \"[/LIST]\";\n";
    echo "revisedMessage = currentMessage+listendBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "if (action == \"listitem\") {\n";
    echo "var thisItem = prompt(\"Enter the new list item. Note that each list group must be preceeded by a List Open and must be ended with List Close.\", \"\");\n";
    echo "var itemBBCode = \"[*]\"+thisItem;\n";
    echo "revisedMessage = currentMessage+itemBBCode;\n";
    echo "document.coolsus.message.value=revisedMessage;\n";
    echo "document.coolsus.message.focus();\n";
    echo "return;\n";
    echo "}\n";
    echo "\n";
    echo "}\n";
?>