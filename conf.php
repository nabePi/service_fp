<?php
// MySQL conf
$hostname = "127.0.0.1";

// Username presensi_db Password pr3DbGTK
$username = "root";
$password = "anjanimd5";
$database = "presensi_db";

// MySQL Connection
$con = new mysqli($hostname,$username,$password,$database);
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
// End MySQL Connection
?>
