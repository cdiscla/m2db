<?php
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>M2DB - Statistics</title>
    <link rel="stylesheet" href="gc.css" />
    <link rel="icon" href="DBguru.png" />
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

<?php date_default_timezone_set('UTC'); echo '<!-- Time (UTC): '. date('Y-m-d G:i:s.u') ." -->\n";
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
?>

<script type='text/javascript'>
      google.load('visualization', '1', {packages:['table']});
      google.setOnLoadCallback(drawTable);
      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('number', 'ID');
        data.addColumn('string', 'Client');
        data.addColumn('string', 'DB');
        data.addColumn('string', 'Process');
        data.addColumn('boolean','Active');
        data.addColumn('number', 'Time');
        data.addColumn('number', 'Microsec.');
        data.addColumn('string', 'State');
        data.addColumn('string', 'Command');
        data.addRows(50);
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
    echo("// Error connecting to MongoDB server \n");
} catch (MongoException $e) {
    $conn_error=1;
echo('Error: ' . $e->getMessage()."\n");
}
if($conn_error==0) {
$db = $cnn->selectDB("admin");
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne()["inprog"];
$i=0; foreach ($record as $obj) {
    if (isset($obj['connectionId']))
        echo "data.setCell(".$i.",0,". $obj['connectionId'] .");";
    else
        echo "data.setCell(".$i.",0, 0);";
    echo "data.setCell(".$i.",1,'".$obj['client']."');";
    if (isset($obj['ns']))
        echo "data.setCell(".$i.",2,'". $obj['ns'] ."');";
    else
        echo "data.setCell(".$i.",2, '');";
    if (isset($obj['desc']))
        echo "data.setCell(".$i.",3,'". $obj['desc'] ."');";
    else
        echo "data.setCell(".$i.",3, '');";
    if ($obj['active'] == true)
        echo "data.setCell(".$i.",4, true);";
    else
        echo "data.setCell(".$i.",4, false);";
    echo "data.setCell(".$i.",5,'".$obj['secs_running']."');";
    echo "data.setCell(".$i.",6,'".$obj['microsecs_running']."');";
    if (isset($obj['op']))
        echo "data.setCell(".$i.",7,'". $obj['op'] ."');";
    else
        echo "data.setCell(".$i.",7, '');";

    echo "data.setCell(".$i.",8,'".$obj['query']["$"."eval"]."');";
    echo("\n");
    $i=$i+1;
}
}
?>
        var table = new google.visualization.Table(document.getElementById('queryActive'));
        table.draw(data, {showRowNumber: true, allowHtml: true, pageSize: 10, page: 'enable', sortColumn: 6, sortAscending: false});
      }
</script>

<script type='text/javascript'>
google.load('visualization', '1', {packages:['gauge']});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = new google.visualization.DataTable();
data.addColumn('string', 'Label');
data.addColumn('number', 'Value');
data.addRows([
<?php
if($conn_error==0) {
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array('$all' => 1))["inprog"];
}
$tot_oa=0; 
foreach ($record as $obj) {
    if ($obj['active']) $tot_oa += 1;
}
echo("['Active Operations', ". $tot_oa ."]\n");
?>
]);
var options = {
  width: 120, height: 120, min: 0, max: 50,
  redFrom: 25, redTo: 50, yellowFrom:10, yellowTo: 25,
  greenFrom:0, greenTo: 25, minorTicks: 1
};
var chart = new google.visualization.Gauge(document.getElementById('gau01'));
chart.draw(data, options);
}
</script>

<script type='text/javascript'>
google.load('visualization', '1', {packages:['gauge']});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = new google.visualization.DataTable();
data.addColumn('string', 'Label');
data.addColumn('number', 'Value');
data.addRows([
<?php
if($conn_error==0) {
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne()["inprog"];
}
$tot_oa=0; 
foreach ($record as $obj) {
    if ($obj['active']) $tot_oa += 1;
}
echo("['User Active', ". $tot_oa ."]\n");
?>
]);
var options = {
  width: 120, height: 120, min: 0, max: 20,
  redFrom: 10, redTo: 20, yellowFrom:5, yellowTo: 10,
  greenFrom:0, greenTo: 5, minorTicks: 1
};
var chart = new google.visualization.Gauge(document.getElementById('gau02'));
chart.draw(data, options);
}
</script>

<script type='text/javascript'>
google.load('visualization', '1', {packages:['gauge']});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = new google.visualization.DataTable();
data.addColumn('string', 'Label');
data.addColumn('number', 'Value');
data.addRows([
<?php
if($conn_error==0) {
$record=$cnn->listDBs();
$tot=0;
foreach ($record["databases"] as $obj) {
    $x=$cnn->$obj["name"]->command(array('dbStats' => 1));
    $tot += $x["indexSize"];
}
$tot=$tot/1024; $tot=$tot/1024;
$record=$cnn->test->command(array('hostInfo' => 1));

echo "['Mem/Idx Pressure', ";
if ($record["system"]["memSizeMB"]>$tot*2)
   echo( round((1-($record["system"]["memSizeMB"]-$tot*2)/$record["system"]["memSizeMB"]),4)*100 );
else
   echo("100");
}
echo "]\n";
?>
]);
var options = {
  width: 120, height: 120, min: 0, max: 100,
  redFrom: 75, redTo: 100, yellowFrom:25, yellowTo: 75,
  greenFrom:0, greenTo: 25, minorTicks: 5
};
var chart = new google.visualization.Gauge(document.getElementById('gau03'));
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
if($conn_error==0) {
$result = $cnn->admin->command(array("serverStatus" => 1));
}
$cmds = array('query','insert','update','delete','getmore','command');
for ($i=0; $i<count($cmds); $i++) {
    echo "['". $cmds[$i] ."', " . round($result["opcounters"][$cmds[$i]]/$result["uptime"],4) . "],\n";
    }
?>
        ]);
        var options = {
          title: 'Cmd #/sec',
          width: 320, height: 180,
          pieSliceText: 'value',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('pie01'));
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
if($conn_error==0) {
$result = $cnn->admin->command(array("serverStatus" => 1));
}
$cmds = array('query','insert','update','delete','getmore','command');
for ($i=0; $i<count($cmds); $i++) {
    echo "['". $cmds[$i] ."', " . round($result["opcountersRepl"][$cmds[$i]]/$result["uptime"],4) . "],\n";
    }
?>
        ]);
        var options = {
          title: 'Repl Cmd #/sec',
          width: 320, height: 180,
          pieSliceText: 'value',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('pie02'));
        chart.draw(data, options);
      }
</script>

<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'bytesIn');
        data.addColumn('number', 'bytesOut');
        data.addRows([
<?php
if($conn_error==0) {
$result = $cnn->admin->command(array("serverStatus" => 1));
}
echo "['Input',  ". round($result["network"]["bytesIn"] /$result["uptime"],4) ."] , ";
echo "['Output', ". round($result["network"]["bytesOut"]/$result["uptime"],4) ."] ]);\n";
?>
	var options = { title: 'Network B/sec', width: 240, height: 180, legend: { position: 'none' }};
        var chart = new google.visualization.ColumnChart(document.getElementById('bar01'));
        chart.draw(data, options);
      }
</script>

</head>

<body>
<?php
echo '<a class="button" href="my2dash.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Dashboard&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2curr.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Status&nbsp;</a>&nbsp;';
echo '<a class="nobutton" href="my2stat.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Performance&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2cust.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Statistics&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2groups.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Groups&nbsp;</a>&nbsp;';
?>
</div>

<table border=0>
<tr >
<?php
echo ' <td><a href="my2cust.php?my2Stat=4&my2Conn=' .$_SESSION['my2Conn'].'"><div id="gau01"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=5&my2Conn=' .$_SESSION['my2Conn'].'"><div id="gau02"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=7&my2Conn=' .$_SESSION['my2Conn'].'"><div id="pie01"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=6&my2Conn=' .$_SESSION['my2Conn'].'"><div id="gau03"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=8&my2Conn=' .$_SESSION['my2Conn'].'"><div id="bar01"></div></a>';
echo ' <td><a href="my2cust.php?my2Stat=9&my2Conn=' .$_SESSION['my2Conn'].'"><div id="pie02"></div></a>';
?>
</table>

<table border=0>
<tr >
 <td>
    <h3>Operations (Active Users)</h3>
    <div id="queryActive"></div>
</table>
<p>

<?php
if(isset($my2conn["conn"][$my2c])) {
	include("check_replset.php");
	echo "Connection: <b>" . $my2conn["conn"][$my2c] . "</b>";
    echo "<br>Host: " . $my2conn["host"][$my2c];
}
if($conn_error==0) {
$record=$cnn->test->command(array('serverStatus' => 1));
	echo "<i><br>Version: ".$record['version'];
	echo "<br>Started: ".date('Y-m-d H:i:s', $record['localTime']->sec - $record['uptime'])." (UTC)";
	echo " Running as: ".$rs_description;
	echo "<br>Date: ".date('Y-m-d H:i:s', $record['localTime']->sec)." (UTC)</i>";
}
?>
<p>
<form method="get" action="my2stat.php" name="login_form">
Choose Connection:
<select name="my2Conn" id="sel_server">
<?php
for ($i=1; $i<=count($my2conn["conn"]); $i++)
    echo ' <option value="' .$i. '" >' .$my2conn["conn"][$i]."</option>\n";
?>
</select>
<input value="Change" type="submit">
<input type="hidden" name="target" value="my2stat.php">
</form>

M2DB <img src="my2s.png" alt="my2 Logo">
v.0.0.2 (Alpha) - Copyright &copy; 2015 by <a href="mailto:mail@meo.bogliolo.name">meo</a>
	&amp; <a href="mailto:mail@christian.disclafani@xenialab.it">chris</a>
<hr>
<p >
<b>M2DB</b> displays useful performance charts for MongoDB.
See the <b><a href="faq.htm" onclick="javascript:void window.open('faq.htm','win_name','width=820,height=700,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=600,top=0');return false;">FAQ</a></b> 
for more information.
</body>
</html>
