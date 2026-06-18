<?php
/*** CELLDB
batchinfo.php - list/edit analysis batches with data in celldb
created 2009-11-30 - SVD
***/

// global include: connect to db and get important basic info about user prefs
include_once "../celldb.php";

?>

<HTML>
<HEAD>
<TITLE><?php echo("$siteinfo"); ?> - Batches</TITLE>
  <link rel="shortcut icon" href="favicon.ico" />
</HEAD>

<?php


echo("<BODY bgcolor=\"$userbg\" text=\"$userfg\"" .
     " link=\"$linkfg\" vlink=\"$vlinkfg\" alink=\"$alinkfg\">");

// header
cellheader("../");

if (""!=$errormsg) {
  echo("<p><b><font color=\"#CC0000\">$errormsg</font></b></p>\n");
}

$sql="SELECT sbatch.*,max(narfresults.id) as lastrun".
  " FROM sbatch LEFT JOIN narfresults ON sbatch.id=narfresults.batch".
  " GROUP BY sbatch.id ORDER BY lastrun DESC";
$batchdata=mysqli_query($dbcnx, $sql);

echo("<table>\n");
while ($row=mysqli_fetch_array($batchdata)) {
  echo("<tr>\n");
  echo("  <td>\n");
  echo("  <a href=\"batchinfo.php?batchid=".$row["id"]."\">".
       $row["id"]." - ".$row["name"]." - ".$row["details"]."</a>\n");
  echo("  </td>\n");
  echo("</tr>\n");
 }

echo("</table>\n");

cellfooter();

?>
