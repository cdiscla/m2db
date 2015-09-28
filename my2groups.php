<?php
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>M2DB - Custom Statistics</title>
    <link rel="stylesheet" href="gc.css" />
    <link rel="icon" href="DBguru.png" />
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

<?php 
date_default_timezone_set('UTC'); echo '<!-- Time (UTC): '. date('Y-m-d G:i:s.u') ." -->\n";
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
    $cnn = mysql_connect($my2connRep["host"], $my2connRep["user"], $my2connRep["pass"]);
    $db=$my2connRep['db'];
    mysql_select_db($db,$cnn);
    $stat1="";
    $stat2="";
    if(preg_match("/(\d+)\.(\d+)/",$my2q,$arr)) {
        $stat1=$arr[1];
        $stat2=$arr[2];
    }
    else {
        $stat1=1;
        $stat2=1;
    }

    //get hostnames from group
    $hostnames=array();
    for($i=1;$i<=count($my2group['host'][$my2c]);$i++) {
        $hostnames[$i]=$my2group['host'][$my2c][$i];
        echo "data.addColumn('number', '". $my2group["host"][$my2c][$i] ."');";
    }

        $sql= "select
        date_format(timest,'%d%m%Y%H%i') timest,
        date_format(timest,'%d %H:%i') timest2
          from status
          where
          variable_name in ('".(isset($my2stat["val".$stat2][$stat1])?$my2stat["val".$stat2][$stat1]:"") ."')
          and timest > date_sub(now(), INTERVAL 24+".$my2ts." HOUR)
          group by timest
          order by timest limit 144";
    $result = mysql_query($sql);
    $first=1;
    $row="";
    while($record=mysql_fetch_array($result)) {
        $ts=$record["timest"];
        $ts2=$record["timest2"];
        if($first==0)
            $row.=",['".$ts2."'";
        else
            $row.="['".$ts2."'";
        for($i=1;$i<=count($hostnames);$i++) {
            $sql2="select variable_value from status where
              host_name='".$hostnames[$i]."' and
              variable_name='".$my2stat["val".$stat2][$stat1]."' and
              date_format(timest,'%d%m%Y%H%i')='".$ts."'";
              $q2=mysql_query($sql2);
              if($r2=mysql_fetch_object($q2))
                $row.=",".$r2->variable_value;
              else
                $row.=",0";
        }
        $row.="]";
        $first=0;
    }
?>
        data.addRows([
          <?php
    echo $row;
echo "]); var options = { title: '" . $my2stat["titl"][$stat1] ." - ".$my2stat["des".$stat2][$stat1]."', hAxis: {textStyle: {fontSize:8}}};";
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
echo '<a class="button" href="my2cust.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Statistics&nbsp;</a>&nbsp;';
echo '<a class="nobutton" href="my2groups.php?my2Conn='.$_SESSION['my2Conn'].'">&nbsp;Groups&nbsp;</a>&nbsp;';
?>

<div id="query" class="chart"></div>

<?php
if(isset($my2stat["hint"][$my2q])) {
    if ($my2stat["hint"][$my2q] <> '')
        echo "<b>Hint:</b> " . $my2stat["hint"][$my2q];
}
?>

<p>
<form method="get" action="my2groups.php" name="stat_form">
Choose Statistic:
<select name="my2Stat" id="sel_stat">
<?php
    ### echo ' <option value="Base" disabled>Base statistics</option>\n';
for ($i=1; $i<=count($my2stat['titl']); $i++)
    {
        //if ($i==11) echo ' <option value="Custom" disabled>Custom statistics</option>\n';
        //echo ' <option value="' . $i . '" >' . $my2stat['titl'][$i] ."</option>\n";
        for ($j=1;$j<=4;$j++) {
            if(isset($my2stat["des".$j][$i]))
                echo ' <option '.(($i==$stat1 && $j==($stat2))?"selected":"").' value="' .$i. '.'.$j.'">' . $my2stat["titl"][$i]." - ".$my2stat['des'.$j][$i] ."</option>\n";
        }
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
    echo "Connection group: <b>" . $my2group["name"][$my2c] . "</b>";
    //echo "<br>Host: " . $my2conn["host"][$my2c];
    echo "<br>Repository: " . $my2connRep["conn"];
}
?>

<p>
<form method="get" action="my2groups.php" name="login_form">
Choose Connection group:
<select name="my2Conn" id="sel_server">
<?php
for ($i=1; $i<=count($my2group["name"]); $i++)
    echo ' <option '.(($my2c==$i)?"selected":"").'  value="' .$i. '" >' .$my2group["name"][$i]."</option>\n";
?>
</select>
<input value="Change" type="submit">
<input type="hidden" name="target" value="my2cust.php">
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
