<?php
 /*** CELLDB
demo_page.php - illustrate some basic programming structures in celldb

created 2007-07-12 - SVD
***/

// global include: connect to db and get important basic info about 
// the user who's currently logged in
include_once "./celldb.php";

?>
<hTML>
<HEAD>
<TITLE><?php echo($siteinfo)?> - Demo page</TITLE>
</HEAD>
<BODY bgcolor="#FFFFFF">
<?php

// display header bar at the top of the page (cellheader
// function defined in celldb.php)
cellheader();

// file paths/names are stored in linux format in the db. flag to convert
$windowsfmt=1;
$excludetest=1;  // exclude "test" animal from searches

// define the form for choosing search options
// calls this page using html GET so that search parameters
// are encoded in the URL
echo("<FORM ACTION=\"demo_page.php\" METHOD=GET>\n");

// hidden parameter saves how output list should be ordered
echo(" <input type=\"hidden\" name=\"orderby\" value=\"$orderby\">\n");

// inputs for various search options.
echo("Animal: <select name=\"animal\" size=\"1\">\n");
if ($animal == "All") {
  echo(" <option value=\"All\" selected>All</option>\n");
} else {
  echo(" <option value=\"All\">All</option>\n");
}
if ($exlcudetest) {
  $animaldata = mysqli_query($dbcnx, "SELECT DISTINCT animal FROM gcellmaster WHERE animal<>\"Test\" ORDER BY animal");
} else {
  $animaldata = mysqli_query($dbcnx, "SELECT DISTINCT animal FROM gcellmaster ORDER BY animal");
}
while ( $row = mysqli_fetch_array($animaldata) ) {
   if ($animal == $row["animal"]) {
       $sel=" selected";
   } else {
       $sel="";
   }
   echo(" <option  value=\"" . $row["animal"] . "\"$sel>" . $row["animal"] . "</option>\n");
}
echo("</select>\n");

echo("&nbsp;&nbsp;Site ID: <INPUT TYPE=TEXT SIZE=6 NAME=\"cellid\" VALUE=\"$cellid\">\n");

echo("&nbsp;&nbsp;Well: <select name=\"well\" size=\"1\">\n");
if (0==$well) {
  echo(" <option value=\"0\" selected>All</option>\n");
} else {
  echo(" <option value=\"0\">All</option>\n");
}
$welldata = mysqli_query($dbcnx, "SELECT DISTINCT well FROM gpenetration WHERE well>0 ORDER BY well");
while ( $row = mysqli_fetch_array($welldata) ) {
   if ($well == $row["well"]) {
       $sel=" selected";
   } else {
       $sel="";
   }
   echo(" <option  value=\"" . $row["well"] . "\"$sel>" . $row["well"] . "</option>\n");
}
echo("</select>\n");

echo("<br>Run class: <select name=\"runclassid\" size=1>");
if ($runclassid == -1) {
  echo(" <option value=\"-1\" selected>All</option>");
} else {
  echo(" <option value=\"-1\">All</option>");
}
$runclassdata=mysqli_query($dbcnx, "SELECT DISTINCT id,name" .
                          " FROM grunclass ORDER BY name,id");
while ( $row = mysqli_fetch_array($runclassdata) ) {
   if ($runclassid == $row["id"]) {
       $sel=" selected";
   } else {
       $sel="";
   }
   echo(" <option value=" . $row["id"] . "$sel>" . $row["name"] . "</option>");
}
echo(" </select>\n");

echo("&nbsp;&nbsp;Behavior: <select name=\"behavior\" size=1>");
$behaviorstrings=array("all","all physiology","training","active","passive","naive");
$behaviorsel=array("","","","","","");
for ($ii=0; $ii<count($behaviorstrings); $ii++) {
  if ($behaviorstrings[$ii]==$behavior) {
    $behaviorsel[$ii]=" selected";
  }
  echo(" <option value=\"" . $behaviorstrings[$ii] . "\"" .
       $behaviorsel[$ii] . ">" .
       $behaviorstrings[$ii] . "</option>\n");
}
echo("</select>");

echo("<br><INPUT TYPE=SUBMIT VALUE=\"Filter\">");
echo("</FORM><br>");

// generate SQL command for current query
$swhere="";

if (""!=$animal && $animal!="All") {
  $swhere=$swhere . " AND gcellmaster.animal=\"$animal\"";
}
if ($well>0) {
  $swhere=$swhere . " AND gcellmaster.well=$well";
}
if (""!=$runclassid && $runclassid>=0) {
  $swhere=$swhere . " AND gdataraw.runclassid=$runclassid";
}
if ($stimspeedid>0) {
  $swhere=$swhere . " AND gdataraw.stimspeedid=$stimspeedid";
}
if (""!=$cellid) {
  $swhere=$swhere . " AND gcellmaster.cellid like \"" . $cellid. "%\"";
}
if (""!=$orderby) {
  $sorder=" $orderby,";
}
if (""==$behavior || "all"==$behavior) {
  // do nothing
} elseif ("training"==$behavior) {
  $swhere=$swhere . " AND gdataraw.training=1";
} elseif ("all physiology"==$behavior) {
  $swhere=$swhere . " AND gdataraw.training=0";
} else {
  $swhere=$swhere." AND gdataraw.training=0 AND gdataraw.behavior='$behavior'";
}

if ($excludetest) {
  $swhere=$swhere . " AND not(gcellmaster.animal like 'test')";
}

// if no parameters passed, search returns nothing 
// ... this avoids massive accidental search results
if (""==$swhere) {
  $swhere=" AND 0";
}

$rawsql="SELECT gdataraw.*,gpenetration.pendate,gcellmaster.penid,".
     " gcellmaster.animal".
     " FROM gdataraw, gcellmaster, gpenetration" . 
     " WHERE gdataraw.masterid=gcellmaster.id" .
     " AND gpenetration.id=gcellmaster.penid" .
     " AND gdataraw.bad=0" .
     $swhere .
     " ORDER BY $sorder gcellmaster.animal,gdataraw.id";
//echo("sql: $rawsql<br>\n");

$rawfiledata=mysqli_query($dbcnx, $rawsql);

if (!$rawfiledata) {
  echo("<P>Error performing query: " . mysqli_error($GLOBALS['dbcnx']) . "($sql)</P>");
  exit();
}

// list each cell
$sorturl="<a href=\"demo_page.php?userid=$userid&sessionid=$sessionid&animal=$animal&well=$well&runclassid=$runclassid&stimspeedid=$stimspeedid&stimfmtcode=$stimfmtcode&showunproc=$showunproc&behavior=$behavior&orderby=";

echo("<table>");
echo("<tr>\n");
echo("    <td><b>" . $sorturl . "gdataraw.id\">RID</a></b></td>\n");
echo("    <td><b>" . $sorturl . "gcellmaster.animal\">Animal</a></b></td>\n");
echo("    <td><b>" . $sorturl . "gdataraw.cellid\">SiteID</a></b></td>\n");
echo("    <td><b>" . $sorturl . "gpenetration.pendate\">Date</a></b></td>\n");
echo("    <td><b>" . $sorturl . "gdataraw.runclassid\">Class</a></b></td>\n");
echo("    <td><b>Recs</b></td>\n");
echo("    <td><b>Parameter file</b></td>\n");
echo("    </tr>\n");

// loop through each row returned by the search
while ( $row = mysqli_fetch_array($rawfiledata) ) {
  
  $cellid=$row["cellid"];
  $penid=$row["penid"];
  $parmfile=$row["parmfile"];
  $resppath=$row["resppath"];
  if (""==$row["matlabfile"]) {
    $file_sorted=0;
  } else {
    $file_sorted=1;
  }
  if ($parmfile[0]=="/" || $parmfile[1]==":") {
    // already contains path
  } else {
    $parmfile=$resppath . $parmfile;
  }
  if ($windowsfmt) {
    $parmfile=str_replace("/afs/glue.umd.edu/department/isr/labs/nsl/projects/daqsc",
                          "M:\\daq",$parmfile);
    $parmfile=str_replace("/auto/data/daq","M:\\daq",$parmfile);
    $parmfile=str_replace("/","\\",$parmfile);
  }
  
  echo("<tr>\n");
  echo("   <td><a href=\"$fnpeninfo?userid=$userid&penid=$penid");
  echo("#$cellid\">" . $row["id"] . "</a>&nbsp;</td>\n");
  echo("   <td>" . $row["animal"] . "</td>\n");
  echo("   <td>" . $cellid . "</td>\n");
  echo("   <td>" . $row["pendate"] . "</td>\n");
  echo("   <td>" . $row["runclass"] . "</td>\n");
  echo("   <td>" . $row["trials"] . "</td>\n");
  echo("   <td>$parmfile</td>\n");
  if ($file_sorted) {
    echo("   <td>sorted</td>\n");
  } else {
    echo("   <td>&nbsp;</td>\n");
  }
  
  echo("</tr>\n");
}
echo("</table>\n");

echo("<p>sql: $rawsql</p>\n");

// display page footer
cellfooter();

?>

</BODY>
</HTML>
