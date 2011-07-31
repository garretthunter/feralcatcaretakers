<?php
   function db_num_rows($result)
   {
      return $result->PO_RecordCount();
   }

   function db_fetch_row($result)
   {
      list($dbconn) = pnDBGetConn();
      $row = $result->fields;
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg().'<br>db_fetch_row()');
      return $row;
   }

   function db_fetch_array($result)
   {
      $row = $result->GetRowAssoc(false);
      $result->MoveNext();
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg().'<br>db_fetch_array()');
      return $row;
   }

   function db_select($sql)
   {
      list($dbconn) = pnDBGetConn();
      $result = $dbconn->Execute($sql);
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg()."<br>$sql");
      return $result;
   }

   function db_select_one_row($sql)
   {
      $result = db_select($sql);
      $return = db_fetch_row($result);
      return $return;
   }

   function db_select_one_array($sql)
   {
      $result = db_select($sql);
      $return = db_fetch_array($result);
      return $return;
   }

   function db_select_all_row($sql)
   {
      $result = db_select($sql);
      while ($return[]=db_fetch_row($result));
      return $return;
   }

   function db_insert($sql)
   {
      list($dbconn) = pnDBGetConn();
      $dbconn->Execute($sql);
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg()."<br>$sql");
   }

   function db_insert_auto($sql)
   {
      die ("auto increment not allowed");
      return false;
   }

   function db_update($sql)
   {
      list($dbconn) = pnDBGetConn();
      $result = $dbconn->Execute($sql);
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg()."<br>$sql");
      return $dbconn->Affected_Rows();
   }

   function db_delete($sql)
   {
      list($dbconn) = pnDBGetConn();
      $result = $dbconn->Execute($sql);
      if($dbconn->ErrorNo()<>0)
         die($dbconn->ErrorNo(). " : ".$dbconn->ErrorMsg()."<br>$sql");
      return $dbconn->Affected_Rows();
   }
?>