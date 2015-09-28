<?php
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE html>
<html>
  <head>
    <title> M2DB - Current Status</title>
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
        data.addRows(500);
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
echo("// Error: " . $e->getMessage()."\n");
}
if($conn_error==0) {
$db = $cnn->selectDB("admin");
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array('$all' => 1))["inprog"];
}
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
?>
        var table = new google.visualization.Table(document.getElementById('queryProcessList'));
        table.draw(data, {showRowNumber: true, allowHtml: true, pageSize: 8, page: 'enable', sortColumn: 4, sortAscending: false });
      }
</script>

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
        data.addRows(25);
<?php
if($conn_error==0) {
$record=$cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array("waitingForLock" => 1))["inprog"];
}
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
?>
        var table = new google.visualization.Table(document.getElementById('queryLock'));
        table.draw(data, {showRowNumber: true, allowHtml: true, pageSize: 5, page: 'enable'});
      }
</script>

<script type='text/javascript'>
      google.load('visualization', '1', {packages:['table']});
      google.setOnLoadCallback(drawTable);
      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'State');
        data.addColumn('boolean', 'Health');
        data.addColumn('number', 'Lag');
        data.addRows(12);
<?php
if($conn_error==0) {
$result = $cnn->admin->command(array("replSetGetStatus" => 1));
}
    if (isset($result['members'])) {
        foreach ($result['members'] as $member) {
            $parsedMember = array(
                'name' => $member['name'],
                'health' => $member['health'],
                'stateStr' => $member['stateStr'],
                'optime' => $member['optime']->sec);
            if ($member['state'] == 1) {
                $primary = $parsedMember;
            } else {
                $secondaries[$member['name']] = $parsedMember;
            }
        }
        if ($primary) {
            foreach ($secondaries as $secondary) {
                $secondaries[$secondary['name']]['lag'] = $primary['optime'] - $secondary['optime'];
            }
        } 
    }
echo("data.setCell(0,0,'". $primary[name] ."');");
echo("data.setCell(0,1,'". $primary[stateStr] ."');");
if ( $primary[health] ==1 )
    echo("data.setCell(0,2,true);");
else
    echo("data.setCell(0,2,false);");
echo("data.setCell(0,3,0);\n");

$x=1;
foreach ($secondaries as $sec) {
  echo("data.setCell(". $x .",0,'". $sec[name] ."');");
  echo("data.setCell(". $x .",1,'". $sec[stateStr] ."');");
if ( $sec[health] ==1 )
    echo("data.setCell(". $x .",2,true);");
else
    echo("data.setCell(". $x .",2,false);");
  echo("data.setCell(". $x .",3,". $sec[lag] .");\n");
  $x += 1;
}
?>
        var table = new google.visualization.Table(document.getElementById('queryRepl'));
        table.draw(data, {showRowNumber: true, allowHtml: true, pageSize: 5, page: 'enable'});
      }
</script>

<script type='text/javascript'>
      google.load('visualization', '1', {packages:['table']});
      google.setOnLoadCallback(drawTable);
      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'ID');
        data.addColumn('number', 'State');
        data.addColumn('string', 'Who');
        data.addColumn('string', 'When');
        data.addColumn('string', 'Why');
        data.addRows(40);
<?php
if($conn_error==0) {
    $record=$cnn->config->selectCollection('locks')->find();
}
$i=0; 
foreach ($record as $obj) {
    $when_php=date('Y-m-d H:i:s',$obj['when']->sec);
    echo "data.setCell(".$i.",0,'".$obj['_id']."');";
    if (isset($obj['state']))
       echo "data.setCell(".$i.",1,".$obj['state'].");";
    else
       echo "data.setCell(".$i.",1,0);";
    echo "data.setCell(".$i.",2,'".$obj['who']."');";
    echo "data.setCell(".$i.",3,'". $when_php ."');";
    echo "data.setCell(".$i.",4,'".$obj['why']."');";
    echo("\n");
    $i=$i+1;
    }
?>
        var table = new google.visualization.Table(document.getElementById('balLock'));
        table.draw(data, {showRowNumber: true, allowHtml: true, pageSize: 5, page: 'enable', sortColumn: 1, sortAscending: false});
      }
</script>

</head>

<body>
<?php
echo '<a class="button" href="my2dash.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Dashboard&nbsp;</a>&nbsp;';
echo '<a class="nobutton" href="my2curr.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Status&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2stat.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Performance&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2cust.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Statistics&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2groups.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Groups&nbsp;</a>&nbsp;';
?>
</div>
<h3>Operations (All)</h3>
<div id="queryProcessList"></div>
<p>
<table>
<tr><td>
 <h3>Waiting for Locks</h3>
 <p>
 <div id="queryLock"></div>
<td>
 <h3>Replication status</h3>
 <p>
 <div id="queryRepl"></div>
<td>
 <h3>Cluster Locks (mongos)</h3>
 <p>
 <div id="balLock"></div>
</table>
<p>


<?php
if(isset($my2conn["conn"][$my2c])) {
        include("check_replset.php");
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
<form method="get" action="my2curr.php" name="login_form">
Choose Connection:
<select name="my2Conn" id="sel_server">
<?php
for ($i=1; $i<=count($my2conn["conn"]); $i++)
    echo ' <option value="' .$i. '" >' .$my2conn["conn"][$i]."</option>\n";
?>
</select>
<input value="Change" type="submit">
<input type="hidden" name="target" value="my2curr.php">
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
