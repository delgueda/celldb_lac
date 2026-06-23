<?php

// global include: connect to db and get important basic info about user prefs
include_once "./celldb.php";

if (""==$orderby) {
  $orderby="tqueue.id";
}
if (""==$complete && ""==$lastjobcomplete) {
  $complete=-1;
} elseif (""==$complete) {
  $complete=$lastjobcomplete;
}
if (""==$user && ""==$lastjobuser) {
  $user=$userid;
} elseif (""==$orderby) {
  $user=$lastjobuser;
}

if (""!=$userid) {
  $sql="UPDATE guserprefs" .
    " SET lastjobuser=\"$user\", " .
    " lastjobcomplete=$complete" .
    " WHERE userid=\"$userid\"";
  $userdata = mysqli_query($dbcnx, $sql);
}

if (0!=$action && $target>0) {
  $sql="SELECT * FROM tqueue WHERE id=$target";
  $queuedata=mysqli_query($dbcnx, $sql);
  
  if (0==mysqli_num_rows($queuedata)) {
    //echo("Invalid queue id $target<br>");
  } else {

    // queue entry does exist
    $row = mysqli_fetch_array($queuedata);
    
    // check requested action and make sure user has permission
    if (-1==$action && ($seclevel>=5 || $row["user"]==$userid)) {
      //echo("Removing queue id $target<br>");
      $sql="DELETE FROM tqueue WHERE id=$target";
      $compdata=mysqli_query($dbcnx, $sql);
    } elseif (1==$action) {
      //echo("Reseting queue id $target<br>");
      $sql="UPDATE tqueue SET complete=0 WHERE id=$target";
      $compdata=mysqli_query($dbcnx, $sql);
    } else {
      //echo("Insufficient security level for requested action<br>");
    }
  }

  $redirurl="queuemonitor.php3?sessionid=$sessionid&userid=$userid&user=$user&complete=$complete&machinename=$machinename&orderby=$orderby&action=0";
  header("Location: $redirurl");
  exit;                 /* Make sure that code below does not execute */
}
?>

<HTML>
<HEAD>
  <TITLE>dbqueue job details</TITLE>
  <link rel="shortcut icon" href="favicon.ico" />
</HEAD>
<?php
echo("<BODY bgcolor=\"$userbg\" text=\"$userfg\"" .
     " link=\"$linkfg\" vlink=\"$vlinkfg\" alink=\"$alinkfg\">");

$redirurl="queuedetails.php?sessionid=$sessionid&userid=$userid&user=$user&complete=$complete&machinename=$machinename&orderby=$orderby&qid=$qid&action=0";
echo("<meta http-equiv=\"Refresh\" content=\"60; URL=$redirurl\">");
?>

<?php

echo( "<b>Job details</b>\n" );
echo(" (<a href=\"queue/queuemasterlog.txt\">today's log</a>");
echo(" <a href=\"queue/queuemasterlog.txt.1\">yesterday's log</a>)<br>");

// summary stats
include "./queuesum.php";

if (""==$qid) {
  $qid=0;
}

$sql="SELECT * FROM tqueue" .
    " WHERE id=$qid";
//echo("sql: $sql<br>\n");
$queuedata=mysqli_query($dbcnx, $sql);

if (!$queuedata) {
  echo("<P>Error performing query: " . mysqli_error($GLOBALS['dbcnx']) . "</P>");
  exit();
}

echo("<table border=1 cellspacing=0 cellpadding=2>\n");
while ( $row = mysqli_fetch_array($queuedata) ) {
  echo("<tr>\n");
  echo("   <td><b>ID:</b></td>");
  echo("   <td>" . $row["id"] . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>User:</b></td>");
  echo("   <td>" . $row["user"] . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Status:</b></td>");
  if ($row["complete"]==-1) {
    echo("   <td><font color=\"#999900\">");
    echo($compstrings[($row["complete"]+2)] . "</font>");
  } elseif ($row["complete"]==0) {
    echo("   <td><font color=\"#000099\">");
    echo($compstrings[($row["complete"]+2)] . "</font>");
  } elseif ($row["complete"]==1) {
    echo("   <td><font color=\"#999999\">");
    echo($compstrings[($row["complete"]+2)] . "</font>");
  } else {  //complete==2 : dead
    echo("   <td><font color=\"#cc0000\">");
    echo($compstrings[($row["complete"]+2)] . "</font>");
  }
  echo("</td>\n</tr>\n");
  

  echo("<tr>\n");
  echo("   <td><b>Note:</b></td>");
  echo("   <td>" . $row["note"] . "&nbsp;</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Matlab cmd:</b></td>");
  echo("   <td>" . $row["parmstring"] . "&nbsp;</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Queue cmd:</b></td>");
  echo("   <td>" . $row["progname"] . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Queued:</b></td>");
  echo("   <td>" . substr($row["queuedate"],5,11) . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Started:</b></td>");
  echo("   <td>" . substr($row["startdate"],5,11) . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Last tick:</b></td>");
  echo("   <td>" . parsetimestamplong($row["lastdate"]) . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Tick Count:</b></td>");
  echo("   <td>" . $row["progress"] . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>Machine:</b></td>");
  echo("   <td>" . $row["machinename"] . "</td>");
  echo("</tr>\n");
  
  echo("<tr>\n");
  echo("   <td><b>PID:</b></td>");
  echo("   <td>" . $row["pid"] . "</td>");
  echo("</tr>\n");
  
  

  if (0) {

  if ((1==$row["complete"] || 2==$row["complete"]) && ($seclevel>=5 || ($row["user"]==$userid && $seclevel>0))) {
    echo("   <td>&nbsp;" . $sorturl . $orderby . "&action=-1&target=" . $row["id"] . "\">DELETE</a>&nbsp;");
    echo($sorturl . $orderby . "&action=1&target=" . $row["id"] . "\">RESET</a>&nbsp;</td>");
  } elseif (0==$row["complete"] && ($seclevel>=5 || ($row["user"]==$userid && $seclevel>0))) {
    echo("   <td>&nbsp;" . $sorturl . $orderby . "&action=-1&target=" . $row["id"] . "\">DELETE</a>&nbsp;</td>");
  } else {
    echo("   <td></td>\n");
  }
  
  if (0==$row["complete"]) {
    echo("   <td>&nbsp;</td>\n");
  } else {
    echo("   <td><a href=\"queue/" . $row["id"]. ".out\">DUMP</a>&nbsp;</td>\n");
  }
  
  echo("</tr>\n");
  }
}

echo("</table>\n");


echo("<HR ALIGN=CENTER SIZE=1 WIDTH=100% NOSHADE>");

$sql="SELECT *,TIME_TO_SEC(NOW())-TIME_TO_SEC(daemonclick) as sec_ago FROM tglobaldata";
$globaldata=mysqli_query($dbcnx, $sql);
$row=mysqli_fetch_array($globaldata);
echo("<em>Queuemaster host:</em> " . $row["daemonhost"] .
     "  <em>Last tick:</em> " . $row["sec_ago"] . " sec (" . 
     $row["daemonclick"] . ")<br>");

?>

<em>This page refreshes automatically every 60 seconds</em><br>

</BODY>
</HTML>
