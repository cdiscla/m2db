<?php
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<html>
  <head>
    <title>M2DB - Dashboard</title>
    <link rel="stylesheet" href="gc.css" />
    <link rel="icon" href="DBguru.png" />
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

<?php date_default_timezone_set('UTC'); echo '<!Time (UTC): '. date('Y-m-d G:i:s.u') .">\n";
if ( ! file_exists('config.inc.php')) {
    echo '<h1>Configuration file missing</h1>';
    echo '<p>Please create <b>config.inc.php</b> configuration file';
    echo '<p>See the <b><a href="faq.htm#Con_00">online documentation</a></b> for more information.';
    exit;
}
include 'common.php';
if (isset($_GET['my2Conn']))
     $_SESSION['my2Conn'] = $_GET['my2Conn'];
else $_SESSION['my2Conn'] = 1;
$my2c = $_SESSION['my2Conn'];
if (isset($_GET['my2TS']))
     $_SESSION['my2TS'] = $_GET['my2TS'];
else $_SESSION['my2TS'] = 0;
$my2ts = $_SESSION['my2TS'];
?>

<!-- #1 graph -->
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
<?php
    $my2q=1;
    echo "data.addColumn('number', '". $my2stat["des1"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des2"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des3"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des4"][$my2q] ."');";
?>
        data.addRows([
<?php
    $mycnn = mysql_connect($my2connRep["host"], $my2connRep["user"], $my2connRep["pass"]);
    $db=$my2connRep['db'];
    mysql_select_db($db,$mycnn);
    $host_name=$my2conn["conn"][$my2c];

$sql= "select date_format(timest,'%d %H:%i') timest, date_format(timest,'%Y-%m-%d %H:%i') timest2,
    sum(if(variable_name='".(isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"") ."',variable_value,0)) val1
      ,sum(if(variable_name='".(isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"") ."',variable_value,0)) val2
      ,sum(if(variable_name='".(isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."',variable_value,0)) val3
      ,sum(if(variable_name='".(isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."',variable_value,0)) val4
      from status
      where host_name='".str_replace("'","''",$host_name)."' and variable_name in ('".(isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"") ."',
      '".(isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"") ."',
      '".(isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."',
      '".(isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."')
      group by timest, timest2
      order by timest2 limit 144";
$result = mysql_query($sql) or die(mysql_error());
$first=1;
while ($record = mysql_fetch_array($result)) {
   $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];
   if ($first==1) {$first=0;} else {echo ",";} 
   echo "['".$record['timest'] . "', ";
   echo $x1-0; echo ", ";
   echo $x2-0; echo ", ";
   echo $x3-0; echo ", ";
   echo $x4-0; echo "]\n";
   }
echo "]);var options = { title: '" . $my2stat["titl"][$my2q] ."',hAxis:{textStyle: {fontSize:8}},width:400,height:240};";
?>
        var chart = new google.visualization.LineChart(document.getElementById('query1'));
        chart.draw(data, options);
      }
</script>

<!-- #2 graph -->
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
<?php
$my2q=2;
echo "data.addColumn('number', '". $my2stat["des1"][$my2q] ."');";
echo "data.addColumn('number', '". $my2stat["des2"][$my2q] ."');";
echo "data.addColumn('number', '". $my2stat["des3"][$my2q] ."');";
echo "data.addColumn('number', '". $my2stat["des4"][$my2q] ."');";
?>
        data.addRows([
<?php
$sql= "select date_format(timest,'%d %H:%i') timest, date_format(timest,'%Y-%m-%d %H:%i') timest2, sum(if(variable_name='". $my2stat["val1"][$my2q] ."',variable_value,0)) val1 ".
      ",sum(if(variable_name='". $my2stat["val2"][$my2q] ."',variable_value,0)) val2 ".
      ",sum(if(variable_name='". $my2stat["val3"][$my2q] ."',variable_value,0)) val3 ".
      ",sum(if(variable_name='". $my2stat["val4"][$my2q] ."',variable_value,0)) val4 ".
      "from status ".
      "where host_name='".str_replace("'","''",$host_name)."' and
      variable_name in ('". $my2stat["val1"][$my2q] ."','". $my2stat["val2"][$my2q] ."','". $my2stat["val3"][$my2q] ."','". $my2stat["val4"][$my2q] ."') ".
      "and timest > date_sub(now(), INTERVAL 24+".$my2ts." HOUR) ".
      "group by timest, timest2 ".
      "order by timest2 limit 144";
$result = mysql_query($sql) or die(mysql_error());
$first=1;
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0; $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];} else {echo ",";} 
   echo "['".$record['timest'] . "', ";
   $tmp1=$record['val1']+0; $tmp2=$record['val2']+0; $tmp3=$record['val3']+0; $tmp4=$record['val4']+0;
   if ($tmp1-$x1>0) echo ($tmp1-$x1)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp2-$x2>0) echo ($tmp2-$x2)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp3-$x3>0) echo ($tmp3-$x3)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp4-$x4>0) echo ($tmp4-$x4)/$m2dbInterval; else echo 0;
   echo "]\n";
   $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];
   }
echo "]);var options = { title: '" . $my2stat["titl"][$my2q].' #/sec.' ."',hAxis:{textStyle: {fontSize:8}},width:400,height:240};";
?>
        var chart = new google.visualization.LineChart(document.getElementById('query2'));
        chart.draw(data, options);
      }
</script>

<!-- #3 graph -->
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
<?php
$my2q=4;
    echo "data.addColumn('number', '". $my2stat["des1"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des2"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des3"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des4"][$my2q] ."');";
?>
        data.addRows([
<?php
$sql= "select date_format(timest,'%d %H:%i') timest, date_format(timest,'%Y-%m-%d %H:%i') timest2,
        sum(if(variable_name='". (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"") ."',variable_value,0)) val1 ".
      ",sum(if(variable_name='". (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"")."',variable_value,0)) val2 ".
      ",sum(if(variable_name='". (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."',variable_value,0)) val3 ".
      ",sum(if(variable_name='". (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."',variable_value,0)) val4 ".
      "from status ".
        "where host_name='".str_replace("'","''",$host_name)."' and
      variable_name in ('".  (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"")."','".
      (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"")."','".
      (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."','".
      (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."') ".
      "and timest > date_sub(now(), INTERVAL 24+".$my2ts." HOUR) ".
      "group by timest, timest2 ".
      "order by timest2 limit 144";
$result = mysql_query($sql);
$first=1;
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0; $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];} else {echo ",";} 
   echo "['".$record['timest'] . "', ";
   $tmp1=$record['val1']+0; $tmp2=$record['val2']+0; $tmp3=$record['val3']+0; $tmp4=$record['val4']+0;
   if ($tmp1-$x1>0) echo ($tmp1-$x1)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp2-$x2>0) echo ($tmp2-$x2)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp3-$x3>0) echo ($tmp3-$x3)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp4-$x4>0) echo ($tmp4-$x4)/$m2dbInterval; else echo 0;
   echo "]\n";
   $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];
   }
echo "]);var options = { title: '" . $my2stat["titl"][$my2q] .' #/sec.' ."',hAxis:{textStyle: {fontSize:8}},width:400,height:240};";
?>
        var chart = new google.visualization.LineChart(document.getElementById('query3'));
        chart.draw(data, options);
      }
</script>

<!-- #4 graph -->
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
<?php
$my2q=3;
    echo "data.addColumn('number', '". $my2stat["des1"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des2"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des3"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des4"][$my2q] ."');";
?>
        data.addRows([
<?php
$sql= "select date_format(timest,'%d %H:%i') timest, date_format(timest,'%Y-%m-%d %H:%i') timest2,
        sum(if(variable_name='". (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"")."',variable_value,0)) val1 ".
      ",sum(if(variable_name='". (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"")."',variable_value,0)) val2 ".
      ",sum(if(variable_name='". (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"")."',variable_value,0)) val3 ".
      ",sum(if(variable_name='". (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"")."',variable_value,0)) val4 ".
      "from status ".
      "where host_name='".str_replace("'","''",$host_name)."' and
        variable_name in ('". (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"")."','".
       (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"")."','".
       (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"")."','".
       (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"")."') ".
      "and timest > date_sub(now(), INTERVAL 24+".$my2ts." HOUR) ".
      "group by timest, timest2 ".
      "order by timest2 limit 144";
$result = mysql_query($sql);
$first=1;
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0; $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];} else {echo ",";} 
   echo "['".$record['timest'] . "', ";
   $tmp1=$record['val1']+0; $tmp2=$record['val2']+0; $tmp3=$record['val3']+0; $tmp4=$record['val4']+0;
   if ($tmp1-$x1>0) echo ($tmp1-$x1)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp2-$x2>0) echo ($tmp2-$x2)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp3-$x3>0) echo ($tmp3-$x3)/$m2dbInterval; else echo 0;
   echo ", ";
   if ($tmp4-$x4>0) echo ($tmp4-$x4)/$m2dbInterval; else echo 0;
   echo "]\n";
   $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];
   }
echo "]);var options = { title: '" . $my2stat["titl"][$my2q] .' B/sec.'."',hAxis:{textStyle: {fontSize:8}},width:400,height:240};";
?>
        var chart = new google.visualization.LineChart(document.getElementById('query4'));
        chart.draw(data, options);
      }
</script>

<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Type');
        data.addColumn('number', 'Count');
        data.addRows([
<?php
$host="mongodb://";
if($my2conn['user'][$my2c]!="" && $my2conn['passwd'][$my2c]!="")
    $host.=$my2conn['user'][$my2c].":".$my2conn['passwd'][$my2c]."@";
$host.=$my2conn['host'][$my2c];

$conn_error=0;
try {
    $cnn = new MongoClient($host);
} catch (MongoConnectionException $e) {
    $conn_error=1;
    echo('Error connecting to MongoDB server');
} catch (MongoException $e) {
    $conn_error=1;
echo('Error: ' . $e->getMessage());
}
if($conn_error==0) {
$cnn->setReadPreference(MongoClient::RP_NEAREST, array());
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array('$all' => 1))["inprog"];
$tot_ua=0; $tot_un=0; $tot_ba=0; $tot_bn=0; 
foreach ($record as $obj) {
    if (isset($obj['connectionId']))
	if ($obj['active'])
		$tot_ua += 1;
	else
		$tot_un += 1;
    else
	if ($obj['active'])
		$tot_ba += 1;
	else
		$tot_bn += 1;

}
echo "['User Active', " . $tot_ua . "],\n";
echo "['User Inactive', " . $tot_un . "],\n";
echo "['System Active', " . $tot_ba . "],\n";
echo "['System Inactive', " . $tot_bn . "],\n";
}
?>
        ]);
        var options = {
          title: 'Operations',
          width: 320, height: 180,
          pieSliceText: 'value',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('pie1'));
        chart.draw(data, options);
      }
</script>

<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Type');
        data.addColumn('number', 'Count');
        data.addRows([
<?php
    $host_name=$my2conn["conn"][$my2c];
    $sql="select variable_name,variable_value,timest,variable_value+0 val_numb from status
        where host_name='".str_replace("'","''",$host_name)."' and
        variable_name like 'dbStats.%.sizeOnDisk'
        and variable_name <> 'dbStats.TOTAL.sizeOnDisk'
        order by timest desc, val_numb desc";

    $prev_timestamp="";
    $q=mysql_query($sql);
    while($r=mysql_fetch_array($q)) {
        if($prev_timestamp!="" && $r["timest"]!=$prev_timestamp)
            break;

        $db_name=substr($r["variable_name"],8);
        echo "['". $db_name . "', " . round($r['variable_value']/(1024*1024)) . "],\n";

        $prev_timestamp=$r["timest"];
    }
?>
        ]);
        var options = {
          title: 'Space usage (MB)',
          width: 320, height: 180,
          pieSliceText: 'value',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('pie2'));
        chart.draw(data, options);
      }
</script>
</head>

<body>
<?php
echo '<a class="nobutton" href="my2dash.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Dashboard&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2curr.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Status&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2stat.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Performance&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2cust.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Statistics&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2groups.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Groups&nbsp;</a>&nbsp;';
?>

<table border=0>
<tr >
<?php
echo ' <td><a href="my2cust.php?my2Stat=1&my2Conn='.$_SESSION['my2Conn'].'"><div id="pie1">  </div></a>';
echo ' <td><a href="my2cust.php?my2Stat=1&my2Conn='.$_SESSION['my2Conn'].'"><div id="query1"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=8&my2Conn='.$_SESSION['my2Conn'].'"><div id="query2"></div></a>';
echo '<tr>';
echo ' <td>  <div id="pie2"></div>';
echo ' <td><a href="my2cust.php?my2Stat=3&my2Conn='.$_SESSION['my2Conn'].'"><div id="query3"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=5&my2Conn='.$_SESSION['my2Conn'].'"><div id="query4"></div></a>';
?>
</table>

<?php
if(isset($my2conn["conn"][$my2c])) {
	include("check_replset.php");
    echo "Connection: <b>" . $my2conn["conn"][$my2c] . "</b><span style='background-color:yellow'>".$rs_description."</span>";
    echo "<br>Host: " . $my2conn["host"][$my2c];
}
echo "<br>Repository: ".$my2connRep["conn"];

if($conn_error==0) {
	$record=$cnn->test->command(array('serverStatus' => 1));
	echo "<i><br>Version: ".$record['version'];
	echo "<br>Started: ".date('Y-m-d H:i:s', $record['localTime']->sec - $record['uptime'])." (UTC)";
	echo "<br>Date: ".date('Y-m-d H:i:s', $record['localTime']->sec)." (UTC)</i>";
}
?>
<p>
<form method="get" action="my2dash.php" name="login_form">
Choose Connection:
<select name="my2Conn" id="sel_server">
<?php
for ($i=1; $i<=count($my2conn["conn"]); $i++)
    echo ' <option '.(($my2c==$i)?"selected":"").' value="' .$i. '" >' .$my2conn["conn"][$i]."</option>\n";
?>
</select>
<input value="Change" type="submit">
<input type="hidden" name="target" value="my2dash.php">
</form>

M2DB <img src="my2s.png" alt="my2 Logo">
v.0.0.1 (Alpha) - Copyright &copy; 2015 by <a href="mailto:mail@meo.bogliolo.name">meo</a>
	&amp; <a href="mailto:christian.disclafani@xenialab.it">chris</a>
<hr>
<p >
<b>M2DB</b> displays useful performance charts for MongoDB.
See the <b><a href="faq.htm" onclick="javascript:void window.open('faq.htm','win_name','width=1000,height=700,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=600,top=0');return false;">FAQ</a></b> 
for more information.
</body>
</html>
