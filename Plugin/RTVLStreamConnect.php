<?php

$dbhost = 'localhost';			// database hostname

$dbuser = 'deb20122_wp3';			// database username

$dbpass = 'HHqHFVQ4';			// database password

$dbname = 'deb20122_wp3';			// databasename



$conn = mysql_connect($dbhost,$dbuser,$dbpass) or die (mysql_error());

mysql_select_db($dbname,$conn) or die (mysql_error());
$sql = "SET NAMES 'utf8'";
mysql_query($sql) or die (mysql_error());
?>