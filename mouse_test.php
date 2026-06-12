<?php

// use this command to run all the celldb login scripts
// include_once "./celldb.php";

// or just run this and skip all the celldb-specific stuff
$dbcnx=@mysqli_connect("metal.isr.umd.edu:3306","kdonald","lespaul");
mysqli_select_db("mouse", $dbcnx);
?>

<html>
<body>

<?php
$sql="SELECT * FROM crap";
$crapdata=mysqli_query($dbcnx, $sql);

while ($row=mysqli_fetch_array($crapdata)) {
  echo($row["crap1"] . "  - " . $row["crap2"] . "<br>\n");
}

echo("<a href=\"celllist.php\">go to celllist</a><br>\n");

?>
</body>
</html>