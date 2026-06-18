<?php
/*** CELLDB
order.php - order management subsystem

created 2008-06-03 - SVD
***/

// global include: connect to db and get important basic info about user prefs
$min_sec_level=6;
include_once "./celldb.php";
?>


<HTML>
<HEAD>
  <TITLE>celldb - Items</TITLE>
</HEAD>

<?php
orderheader();

if (isset($itemid)) {
  echo("<p><b>Orders for itemid $itemid:</b></p>\n");
  $sql="SELECT oorder.*,ocompany.name as co_name,date(dateordered) as odate FROM oorder,ocompany,oorderitem WHERE oorder.companyid=ocompany.id AND oorder.id=oorderitem.orderid AND oorderitem.itemid=$itemid AND not(oorder.bad) ORDER BY dateordered;";

} elseif (isset($companyid)) {
  echo("<p><b>Orders from company $companyid:</b></p>\n");
  $sql="SELECT oorder.*,ocompany.name as co_name,date(dateordered) as odate FROM oorder,ocompany WHERE oorder.companyid=ocompany.id AND oorder.companyid=$companyid AND not(oorder.bad) ORDER BY dateordered;";

} else {
  echo("<p><b>All orders:</b></p>\n");
  $sql="SELECT oorder.*,ocompany.name as co_name,date(dateordered) as odate FROM oorder,ocompany WHERE oorder.companyid=ocompany.id AND not(oorder.bad) ORDER BY dateordered;";
}

$cdata=mysqli_query($dbcnx, $sql);

echo("<table cellpadding=2>\n");
echo("<tr><td><b>Date</b></td>");
echo("<td><b>Company</b></td>");
echo("<td><b>By</b></td>");
echo("<td>&nbsp;</td>");
echo("<td>&nbsp;</td>");
echo("</tr>\n");
while ($row=mysqli_fetch_array($cdata)) {
  $orderid=$row["id"];
  echo("<tr><td>".$row["odate"]."</td>");
  echo("<td>".$row["co_name"]."</td>");
  echo("<td>".$row["addedby"]."</td>");
  echo("<td><a href=\"order_editorder.php?id=$orderid&action=1\">delete</a></td>");
  echo("<td><a href=\"order_editorder.php?id=$orderid\">edit</a></td>");
  echo("<td><a href=\"orderform.php?id=$orderid\">view</a></td>");
  echo("</tr>\n");
}

echo("<tr><td>New order</td>");
echo("<td></td>");
echo("<td></td>");
echo("<td><a href=\"order_editorder.php\">edit</a></td>");
echo("</tr>\n");

echo("</table>");

cellfooter();
?>

</HTML>
