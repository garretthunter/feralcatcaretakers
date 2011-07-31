<?php
// ----------------------------------------------------------------------
// Original Author of file: phpBB team
// Purpose of file: bbcode javascript
// ----------------------------------------------------------------------
echo "function openwindow(){\n";
echo "  window.open (\"${HTTP_GET_VARS['hlpfile']}\",\"Help\",\"toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=no,copyhistory=no,width=600,height=400\");\n";
echo "}\n";
?>