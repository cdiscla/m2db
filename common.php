<?php
# m2db configuration parameters
# DO NOT TOUCH!
$m2dbInterval=600;
$m2dbTZ='Europe/Rome';

# Statistics
$i=0; 
$i++;
$my2stat["titl"][$i]='Operations';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='m2db.usersActive';
$my2stat["val2"][$i]='m2db.sysActive';
$my2stat["val3"][$i]='m2db.usersNotActive';
$my2stat["val4"][$i]='m2db.sysNotActive';
$my2stat["des1"][$i]='Users Active';
$my2stat["des2"][$i]='System Active';
$my2stat["des3"][$i]='Users Not Active';
$my2stat["des4"][$i]='System Not Active';

$i++;
$my2stat["titl"][$i]='Commands (CRUD)';
$my2stat["type"][$i]=1;
$my2stat["val1"][$i]='opcounters.query';
$my2stat["val2"][$i]='opcounters.delete';
$my2stat["val3"][$i]='opcounters.update';
$my2stat["val4"][$i]='opcounters.insert';
$my2stat["des1"][$i]='Query';
$my2stat["des2"][$i]='Delete';
$my2stat["des3"][$i]='Update';
$my2stat["des4"][$i]='Insert';

$i++;
$my2stat["titl"][$i]='Network traffic';
$my2stat["type"][$i]=1;
$my2stat["val1"][$i]='network.bytesIn';
$my2stat["val2"][$i]='network.bytesOut';
$my2stat["val3"][$i]='network.numRequests';
$my2stat["val4"][$i]='';
$my2stat["des1"][$i]='In';
$my2stat["des2"][$i]='Out';
$my2stat["des3"][$i]='Requests';
$my2stat["des4"][$i]='';

$i++;
$my2stat["titl"][$i]='Commands (Other)';
$my2stat["type"][$i]=1;
$my2stat["val1"][$i]='opcounters.query';
$my2stat["val2"][$i]='opcounters.getmore';
$my2stat["val3"][$i]='opcounters.command';
$my2stat["val4"][$i]='';
$my2stat["des1"][$i]='Query';
$my2stat["des2"][$i]='Get More';
$my2stat["des3"][$i]='Command';
$my2stat["des4"][$i]='';

$i++;
$my2stat["titl"][$i]='Space Usage';
$my2stat["type"][$i]=0;
$my2stat["val1"][$i]='dbStats.TOTAL.dataSize';
$my2stat["val2"][$i]='dbStats.TOTAL.indexSize';
$my2stat["des1"][$i]='Data';
$my2stat["des2"][$i]='Index';

$i++;
$my2stat["titl"][$i]='Connection Rate';
$my2stat["type"][$i]=2;
$my2stat["val1"][$i]='connections.totalCreated';
$my2stat["val2"][$i]='uptime';
$my2stat["des1"][$i]='Rate (Op./sec.)';

$i++;
$my2stat["titl"][$i]='Open Cursor Rate';
$my2stat["type"][$i]=2;
$my2stat["val1"][$i]='cursors.totalOpen';
$my2stat["val2"][$i]='uptime';
$my2stat["des1"][$i]='Rate (Op./sec.)';

$i++;
$my2stat["titl"][$i]='Query Rate';
$my2stat["type"][$i]=2;
$my2stat["val1"][$i]='opcounters.query';
$my2stat["val2"][$i]='uptime';
$my2stat["des1"][$i]='Rate (Op./sec.)';

$i++;
$my2stat["titl"][$i]='Commit Rate';
$my2stat["type"][$i]=2;
$my2stat["val1"][$i]='dur.commits';
$my2stat["val2"][$i]='uptime';
$my2stat["des1"][$i]='Rate (Op./sec.)';

$i++;
$my2stat["titl"][$i]='Indexes Miss Rate';
$my2stat["val1"][$i]='indexCounters.missRatio';
$my2stat["type"][$i]=0;
$my2stat["des1"][$i]='Misses%';

## Custom Graphs
include 'custom.php';

## Custom Configuration
include 'config.inc.php';
?>

