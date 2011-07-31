<?php // File: $Id: modifycomments.php,v 1.2 2002/10/21 13:20:19 larsneo Exp $ $Name:  $

modules_get_language();

function editcomm()
{
    if (!pnUserLoggedIn()) {
        return;
    }
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // quick hack to actually load data -- dracosarcane
    $uid = pnUserGetVar('uid');
    $column = &$pntable['users_column'];
    $query = "SELECT $column[umode],
                  $column[uorder],
                  $column[thold],
                  $column[noscore],
                  $column[commentmax]
                  FROM $pntable[users]
              WHERE $column[uid]=".pnVarPrepForStore($uid)."";
    $result = $dbconn->Execute($query);
    list($mode, $order, $thold, $noscore, $commentmax) = $result->fields;

//  these calls aren't working -- dracosarcane
//    $mode = pnUserGetVar('umode');
//    $order = pnUserGetVar('uorder');
//    $thold = pnUserGetVar('thold');
//    $noscore = pnUserGetVar('noscore');
//    $commentsmax = pnUserGetVar('commentmax');


    include 'header.php';
    OpenTable();
    echo "<center><font class=\"pn-title\">"._COMMENTSCONFIG."</font></center>";
    CloseTable();

    OpenTable();
    echo "<table cellpadding=\"8\" border=\"0\"><tr><td>"
        ."<form action=\"user.php\" method=\"post\">"
        ."<font class=\"pn-normal\">"._DISPLAYMODE."</font><br>"
        ."<select name=\"umode\">";
?>
    <option value="nocomments" <?php if ($mode == 'nocomments') { echo "selected"; } ?>><?php echo _NOCOMMENTS ?>
    <option value="nested" <?php if ($mode == 'nested') { echo "selected"; } ?>><?php echo _NESTED ?>
    <option value="flat" <?php if ($mode == 'flat') { echo "selected"; } ?>><?php echo _FLAT ?>
    <option value="thread" <?php if (!isset($mode) || ($mode=="") || $mode=='thread') { echo " selected"; } ?>><?php echo _THREAD ?>
    </select>
    <br><br>
    <?php echo "<font class=\"pn-normal\">"._SORTORDER."</font><br>" ?>
    <select name="uorder">
    <option value="0" <?php if (!$order) { echo "selected"; } ?>><?php echo _OLDEST ?>
    <option value="1" <?php if ($order==1) { echo "selected"; } ?>><?php echo _NEWEST ?>
    <option value="2" <?php if ($order==2) { echo "selected"; } ?>><?php echo _HIGHEST ?>
    </select>
    <br><br>
    <?php echo "<font class=\"pn-normal\">"._THRESHOLD."</font>" ?>
    <?php echo "<font class=\"pn-normal\">"._COMMENTSWILLIGNORED."</font>" ?><br>
    <select name="thold">
    <option value="-1" <?php if ($thold==-1) { echo "selected"; } ?>>-1: <?php echo _UNCUT ?>
    <option value="0" <?php if ($thold==0) { echo "selected"; } ?>>0: <?php echo _EVERYTHING ?>
    <option value="1" <?php if ($thold==1) { echo "selected"; } ?>>1: <?php echo _FILTERMOSTANON ?>
    <option value="2" <?php if ($thold==2) { echo "selected"; } ?>>2: <?php echo _USCORE ?> +2
    <option value="3" <?php if ($thold==3) { echo "selected"; } ?>>3: <?php echo _USCORE ?> +3
    <option value="4" <?php if ($thold==4) { echo "selected"; } ?>>4: <?php echo _USCORE ?> +4
    <option value="5" <?php if ($thold==5) { echo "selected"; } ?>>5: <?php echo _USCORE ?> +5
    </select><br>
    <?php echo "<font class=\"pn-normal\">"._SCORENOTE."</font>" ?>
    <br><br>
    <INPUT type="checkbox" value="1" name="noscore" <?php if ($noscore==1) { echo "checked"; } ?>>
    <?php echo "<font class=\"pn-normal\">"._NOSCORES."</font>" ?> <?php echo "<font class=\"pn-normal\">"._HIDDESCORES."</font>" ?>
    <br><br>
    <?php echo "<font class=\"pn-normal\">"._MAXCOMMENT."</font>" ?> <?php echo "<font class=\"pn-normal\">"._TRUNCATES."</font>" ?><br>
    <input type="text" name="commentmax" value="<?php echo $commentmax ?>" size=11 maxlength=11> <?php echo "<font class=\"pn-normal\">"._BYTESNOTE."</font>" ?>
    <br><br>
    <input type="hidden" name="op" value="savecomm">
    <input type="submit" value="<?php echo _SAVECHANGES ?>">
    </form></td></tr></table>
<?php
    CloseTable();

    include 'footer.php';
}

function savecomm($umode, $uorder, $thold, $noscore, $commentmax)
{
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');
        if(isset($noscore) && ($noscore == 1)) {
            $noscore = '1';
        } else {
            $noscore = '0';
        }
        $column = &$pntable['users_column'];
        $query = "UPDATE $pntable[users]
                  SET $column[umode]='".pnVarPrepForStore($umode)."',
                      $column[uorder]='".pnVarPrepForStore($uorder)."',
                      $column[thold]='".pnVarPrepForStore($thold)."',
                      $column[noscore]='".pnVarPrepForStore($noscore)."',
                      $column[commentmax]='".pnVarPrepForStore($commentmax)."'
                  WHERE $column[uid]='".pnVarPrepForStore($uid)."'";
        $dbconn->Execute($query);
    }
    pnRedirect('user.php');
}

if (!isset($noscore)) {
    $noscore = '';
}

switch ($op)
{
    case "editcomm":
	editcomm();
        break;
    case "savecomm":
        savecomm($umode, $uorder, $thold, $noscore, $commentmax);
        break;
}
?>