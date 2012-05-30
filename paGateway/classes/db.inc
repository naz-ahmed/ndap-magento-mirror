
<?php 


$dbconnect  = NULL;
$dbhost     = "ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com";
$dbusername = "etl";
$dbuserpass = "RtdKK3KQvhcwcV53";

$query      = NULL;

function db_connect($dbname)
{
   global $dbconnect, $dbhost, $dbusername, $dbuserpass;
   
   if (!$dbconnect) $dbconnect = mysql_connect($dbhost, $dbusername, $dbuserpass);
   if (!$dbconnect) {
      return 0;
   } elseif (!mysql_select_db($dbname)) {
      return 0;
   } else {
      return $dbconnect;
   } // if
   
} // db_connect


?>