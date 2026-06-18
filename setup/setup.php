<?php

/*** CELLDB
setup.php - initial database creation

created SVD 2012-07-27

***/
// read in user settings
include_once "../config.php";

if (!isset($dbserver)) {
  fatal_error("Database server not specified. <tt>config.php</tt> missing?");
}

// initial db connection needed basically for anything
if (!$dbcnx=@mysqli_connect($dbserver.":3306",$dbuser,$dbpassword)) {
  
  $errormsg=rawurlencode("Error connecting to mysql (server: $dbserver, user: $dbuser)\n");
  header("Location: error.php?errormsg=".
         $errormsg . "&refurl=setup/setup.php");
  exit();
}

// use database called $dbname (specified in config.php)
if (!mysqli_select_db($dbcnx, $dbname)) {
  header("Location: error.php?errormsg=".
         rawurlencode("Could connect to db server but could not open database $dbname. Make dbname specification is correct in <tt>config.php</tt>.") . 
         "&refurl=setup/setup.php");
 }

echo("Sucessfully connected to server: $dbserver, database: $dbname<br>\n");

echo("Creating tables...<br>\n");

$res=exec("mysql -u".$dbuser." -p".$dbpassword." ".$dbname." < celldb_struct.sql");

echo("Table creation complete ($res).<br>\n");

echo("Creating test database entries...<br>\n");

$sql="INSERT INTO ganimal (animal,cellprefix,notes,addedby,species,lab)".
  " VALUES ('Test','tst','General behavior/experiment notes here',".
  "'admin','ferret','$LAB')";
echo("$sql<br>");
mysqli_query($dbcnx, $sql);

$sql="INSERT INTO ganimal (animal,cellprefix,notes,addedby,species,lab)".
  " VALUES ('TestMouse','tsm','General behavior/experiment notes here',".
  "'admin','mouse','$LAB')";
mysqli_query($dbcnx, $sql);

$res=exec("mysql -u".$dbuser." -p".$dbpassword." ".$dbname." < grunclass.sql");

$sessionid=md5('Ferret1');
$sql="INSERT INTO guserprefs" .
  " (userid,password,seclevel,email,realname,lab)".
  " VALUES (\"david\",\"" . 
  "$sessionid\",2,\"stephen.v.david@gmail.com\",\"Stephen David\",".
  "\"$LAB\")";
$result=mysqli_query($dbcnx, $sql);

echo("Test database entry creation complete.<br>\n");
$path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
$celldb_path=$path_parts['dirname'];
echo("Pathinfo: $celldb_path<br>\n");

?>