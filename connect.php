<?php
//Change if needed
 $servername = "localhost";
 $username = "root";
 $password = "";
 $db = "student_allowance_tracker";
 $port = "3306";

 // Create connection MySQLi
 $conn = mysqli_connect($servername, $username, $password, $db, $port);
 // Check connection
 if (!$conn) {
 die("Connection failed: " . mysqli_connect_error());
 }
 ?>
