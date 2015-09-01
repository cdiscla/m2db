<?php
//to avoid mysql_connect deprecated msg
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<html>
  <head>
    <title>M2DB - Custom Statistics</title>
    <link rel="stylesheet" href="gc.css" />
    <link rel="icon" href="DBguru.png" />
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

<?php 
date_default_timezone_set('UTC'); echo '<!Time (UTC): '. date('Y-m-d G:i:s.u') .">\n";
if ( ! file_exists('config.inc.php')) {
    echo '<h1>Configuration file missing</h1>';
    echo '<p>Please create <b>config.inc.php</b> configuration file';
    echo '<p>See the <b><a href="faq.htm#Con_00">online documentation</a></b> for more information.';
    exit;
}
include 'common.php';
if (isset($_GET['my2Stat']))
     $_SESSION['my2Stat'] = $_GET['my2Stat'];
else $_SESSION['my2Stat'] = 1;
$my2q = $_SESSION['my2Stat'];
if (isset($_GET['my2Conn']))
     $_SESSION['my2Conn'] = $_GET['my2Conn'];
else $_SESSION['my2Conn'] = 1;
$my2c = $_SESSION['my2Conn'];
if (isset($_GET['my2TS']))
     $_SESSION['my2TS'] = $_GET['my2TS'];
else $_SESSION['my2TS'] = 0;
$my2ts = $_SESSION['my2TS'];
?>

<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
<?php
    echo "data.addColumn('number', '". $my2stat["des1"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des2"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des3"][$my2q] ."');";
    echo "data.addColumn('number', '". $my2stat["des4"][$my2q] ."');";
?>
        data.addRows([
<?php
$cnn = mysql_connect($my2connRep["host"], $my2connRep["user"], $my2connRep["pass"]);
$host_name=$my2conn["conn"][$my2c];

$sql= "select date_format(timest,'%d %H:%i') timest2, sum(if(variable_name='".
    (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"") ."',variable_value,0)) val1 ".
      ",sum(if(variable_name='". (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"") ."',variable_value,0)) val2 ".
      ",sum(if(variable_name='". (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."',variable_value,0)) val3 ".
      ",sum(if(variable_name='". (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."',variable_value,0)) val4 ".
      "from m2db.status ".
      "where host_name='".str_replace("'","''",$host_name).
      "' and variable_name in ('".
      (isset($my2stat["val1"][$my2q])?$my2stat["val1"][$my2q]:"") ."','".
      (isset($my2stat["val2"][$my2q])?$my2stat["val2"][$my2q]:"") ."','".
      (isset($my2stat["val3"][$my2q])?$my2stat["val3"][$my2q]:"") ."','".
      (isset($my2stat["val4"][$my2q])?$my2stat["val4"][$my2q]:"") ."') ".
      "and timest > date_sub(now(), INTERVAL 24+".$my2ts." HOUR) ".
      "group by timest ".
      "order by timest limit 144";

$result = mysql_query($sql);
$first=1;
if ($my2stat["type"][$my2q]==0) {
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0;} else {echo ",";}
   echo "['".$record['timest2'] . "', " . $record['val1'].", " . $record['val2'].", " . $record['val3'].", " . $record['val4']. "]\n";
   }
} else {
if ($my2stat["type"][$my2q]==1) {
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0; $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];} else {echo ",";} 
   echo "['".$record['timest2'] . "', ";
   $tmp1=$record['val1']+0; $tmp2=$record['val2']+0; $tmp3=$record['val3']+0; $tmp4=$record['val4']+0;
   if ($tmp1-$x1>0) echo $tmp1-$x1; else echo 0;
   echo ", ";
   if ($tmp2-$x2>0) echo $tmp2-$x2; else echo 0;
   echo ", ";
   if ($tmp3-$x3>0) echo $tmp3-$x3; else echo 0;
   echo ", ";
   if ($tmp4-$x4>0) echo $tmp4-$x4; else echo 0;
   echo "]\n";

   $x1=$record['val1']; $x2=$record['val2']; $x3=$record['val3']; $x4=$record['val4'];
   }
} else { # type=2 ratio
while ($record = mysql_fetch_array($result)) {
   if ($first==1) {$first=0; $x1=$record['val1']; $x2=$record['val2'];} else {echo ",";} 
   echo "['".$record['timest2'] . "', ";
   $tmp1=$record['val1']+0; $tmp2=$record['val2']+0;

   if ($tmp1-$x1>0) 
      { if ($tmp2-$x2<=0) {echo 0;} else {echo ($tmp1-$x1)/($tmp2-$x2); } }
   else
      echo 0;
   echo ",0,0,0]\n";
   $x1=$record['val1']; $x2=$record['val2'];
   }
} }
echo "]); var options = { title: '" . $my2stat["titl"][$my2q] ."', hAxis: {textStyle: {fontSize:8}}};";
?>
        var chart = new google.visualization.LineChart(document.getElementById('query'));
        chart.draw(data, options);
      }
</script>
</head>

<body>
<?php
echo '<a class="button" href="my2dash.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Dashboard&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2curr.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Status&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2stat.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Performance&nbsp;</a>&nbsp;';
echo '<a class="nobutton" href="my2cust.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Statistics&nbsp;</a>&nbsp;';
echo '<a class="button" href="my2groups.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Groups&nbsp;</a>&nbsp;';
?>

<div id="query" class="chart"></div>

<?php
if(isset($my2stat["hint"][$my2q])) {
    if ($my2stat["hint"][$my2q] <> '')
        echo "<b>Hint:</b> " . $my2stat["hint"][$my2q];
}
?>

<p>
<form method="get" action="my2cust.php" name="stat_form">
Choose Statistic:
<select name="my2Stat" id="sel_stat">
<?php
    ### echo ' <option value="Base" disabled>Base statistics</option>\n';
for ($i=1; $i<=count($my2stat['titl']); $i++)
    {
    if ($i==11) echo ' <option value="Custom" disabled>Custom statistics</option>\n';
    echo ' <option value="' . $i . '" >' .
        $my2stat['titl'][$i] ."</option>\n";
    }

echo'<input type="hidden" name="my2Conn" value="'. $my2c .'">';
?>
</select>
Time shift (hours): 
<?php
echo '<input type="text" name="my2TS" value="'. $my2ts .'" size="4">';
?>
<input value="Execute" type="submit">
<input type="hidden" name="target" value="my2cust.php">
</form>


<?php
if(isset($my2conn["conn"][$my2c])) {
    echo "Connection: <b>" . $my2conn["conn"][$my2c] . "</b>";
    echo "<br>Host: " . $my2conn["host"][$my2c];
    echo "<br>Repository: " . $my2connRep["conn"];
}
?>

<p>
<form method="get" action="my2cust.php" name="login_form">
Choose Connection:
<select name="my2Conn" id="sel_server">
<?php
for ($i=1; $i<=count($my2conn["conn"]); $i++)
    echo ' <option value="' .$i. '" >' .$my2conn["conn"][$i]."</option>\n";
?>
</select>
<input value="Change" type="submit">
<input type="hidden" name="target" value="my2cust.php">
</form>

M2DB <img src="my2s.png" alt="my2 Logo">
v.0.0.1 (Alpha) - Copyright &copy; 2015 by <a href="mailto:mail@meo.bogliolo.name">meo</a>
	&amp; <a href="mailto:mail@christian.disclafani@xenialab.it">chris</a>
<hr>
<p >
<b>M2DB</b> displays useful performance charts for MongoDB.
See the <b><a href="faq.htm" onclick="javascript:void window.open('faq.htm','win_name','width=820,height=700,toolbar=0,menubar=0,location=0,status=0,scrollbars=1,resizable=1,left=600,top=0');return false;">FAQ</a></b> 
for more information.
</body>
</html>
