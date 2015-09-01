<?php
# Custom Statistics Configuration parameters

# Statistics
$i++;
$my2stat["titl"][$i]='Available Connections';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='connections.available';
$my2stat["des1"][$i]='Available';
$my2stat["val2"][$i]='connections.current';
$my2stat["des2"][$i]='Current';

$i++;
$my2stat["titl"][$i]='Disk Flush';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='backgroundFlushing.last_ms';
$my2stat["val2"][$i]='backgroundFlushing.average_ms';
$my2stat["des1"][$i]='Last';
$my2stat["des2"][$i]='Running Average';

$i++;
$my2stat["titl"][$i]='Replication Lag';
$my2stat["val1"][$i]='replSetGetStatus.members.secondary.lag.MAX';
$my2stat["type"][$i]=0;
$my2stat["des1"][$i]='Lag';

$i++;
$my2stat["titl"][$i]='Storage';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='dbStats.TOTAL.storageSize';
$my2stat["des1"][$i]='Storage';

$i++;
$my2stat["titl"][$i]='Objects';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='dbStats.TOTAL.collections';
$my2stat["des1"][$i]='Collections';
$my2stat["val2"][$i]='dbStats.TOTAL.indexes';
$my2stat["des2"][$i]='Indexes';

$i++;
$my2stat["titl"][$i]='Lock wait Rate';
$my2stat["type"][$i]=2;
$my2stat["val1"][$i]='globalLock.totalTime';
$my2stat["val2"][$i]='uptime';
$my2stat["des1"][$i]='Rate';
?>

