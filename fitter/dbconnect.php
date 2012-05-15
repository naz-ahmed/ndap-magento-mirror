<?php   
//localhost is usually your server name.. if it's not, then put that in there instead.
//mysql_connect("localhost", "root", "") or die(mysql_error());
//mysql_select_db("carpartkings") or die(mysql_error());
if ($inApp == "yessir!"){ 
mysql_connect("magento01.c2lug8itjgui.us-east-1.rds.amazonaws.com", "magento", "C3HKMVYD7DQJsSVY") or die(mysql_error());     
mysql_select_db("magento") or die(mysql_error());
                        }
                            else {echo "Nothing to see here..."; 
                            }
?>
