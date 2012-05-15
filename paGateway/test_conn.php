<?php

echo "<h3>test connection</h3>";

$con = mysql_connect("ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com", "magento", "C3HKMVYD7DQJsSVY");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}

if(!mysql_select_db("magento", $con))
{
	echo "connection failed";
}
else
{
	echo "connection succeeded";
}

// comment

?>