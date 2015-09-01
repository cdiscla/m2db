<?php
    include 'common.php';
    ini_set('date.timezone', $m2dbTZ);
    $date=date("Y/m/d H:i:s");
    function collect_stat($host_name, $variable_name, $variable_value, $date)
    {
        global $mycnn;
        $sql = "insert into status(host_name,variable_name,variable_value,timest) values(
                '" . str_replace("'", "''", $host_name) . "',
                '" . str_replace("'", "''", $variable_name) . "',
                '" . str_replace("'", "''", $variable_value) . "',
                '".$date."')";
        mysqli_query($mycnn, $sql);
        /*** echo("  DEBUG: " . $variable_name . " = " . $variable_value . "\n"); ***/
    }

    $mycnn = mysqli_connect($my2connRep["host"], $my2connRep["user"], $my2connRep["pass"]);
    mysqli_select_db($mycnn, $my2connRep['db']);
    global $mycnn;

    $daily_stats=0;
    if(isset($argv[1])) {
        if(strtolower($argv[1])=="daily")
            $daily_stats=1;
    }

    if($daily_stats==0) {
        for($im=1;$im<=count($my2conn["conn"]);$im++) {
            echo("Getting info from " . $my2conn['conn'][$im] . "  " . $my2conn['host'][$im] . " ");
	    echo(date("Y/m/d H:i:s")."\n");
            $conn_error=0;
            try {
                $host="mongodb://";
                if($my2conn['user'][$im]!="" && $my2conn['passwd'][$im]!="")
                    $host.=$my2conn['user'][$im].":".$my2conn['passwd'][$im]."@";
                $host.=$my2conn['host'][$im];
                $cnn = new MongoClient($host);
            } catch (MongoConnectionException $e) {
                $conn_error=1;
                echo(" Error connecting to MongoDB server\n");
            } catch (MongoException $e) {
                $conn_error=1;
                echo(' Error: ' . $e->getMessage()."\n");
            }

            if($conn_error==0) {
                $db = $cnn->selectDB("admin");

                echo(" Getting server status \n");
                $result = $cnn->admin->command(array("serverStatus" => 1));
                foreach ($result as $key => $val) {
                    if (!is_array($val)) {
                        collect_stat($my2conn['conn'][$im], $key, $val, $date);
                    } else {
                        foreach ($val as $sub_key => $sub_val) {
                            if (!is_array($sub_val))
                                collect_stat($my2conn['conn'][$im], $key . "." . $sub_key, $sub_val, $date);
                        }
                    }
                }

                echo(" Getting operations \n");
                $record = $cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array('$all' => 1))["inprog"];
                $tot_ua = 0;
                $tot_un = 0;
                $tot_ba = 0;
                $tot_bn = 0;
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
                collect_stat($my2conn['conn'][$im], "m2db.usersActive", $tot_ua, $date);
                collect_stat($my2conn['conn'][$im], "m2db.usersNotActive", $tot_un, $date);
                collect_stat($my2conn['conn'][$im], "m2db.sysActive", $tot_ba, $date);
                collect_stat($my2conn['conn'][$im], "m2db.sysNotActive", $tot_bn, $date);

                echo(" Getting replication status \n");
                $result = $cnn->admin->command(array("replSetGetStatus" => 1));
                foreach ($result as $key => $val) {
                    if (!is_array($val)) {
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus." . $key, $val, $date);
                    }
                }
                if (isset($result['members'])) {
                    foreach ($result['members'] as $member) {
                        $parsedMember = array(
                            'name' => $member['name'],
                            'health' => $member['health'],
                            'stateStr' => $member['stateStr'],
                            'optime' => $member['optime']->sec);
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.name." . $member["name"], $member["name"], $date);
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.health." . $member["name"], $member["health"], $date);
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.stateStr." . $member["name"], $member["stateStr"], $date, $date);
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.optime." . $member["name"], $member["optime"], $date);
                        if ($member['state'] == 1) {
                            $primary = $parsedMember;
                        } else {
                            $secondaries[$member['name']] = $parsedMember;
                        }
                    }
                    if ($primary) {
                        $max_lag = 0;
                        foreach ($secondaries as $secondary) {
                            $secondaries[$secondary['name']]['lag'] = $primary['optime'] - $secondary['optime'];
                            collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.secondary.lag." . $secondary['name'], $primary['optime'] - $secondary['optime'], $date);
                            if (($primary['optime'] - $secondary['optime']) > $max_lag)
                                $max_lag = $primary['optime'] - $secondary['optime'];
                        }
                        collect_stat($my2conn['conn'][$im], "replSetGetStatus.members.secondary.lag.MAX", $max_lag, $date);
                    }
                }

/*
                echo(" Getting Sharding status \n");
                $record = $cnn->admin->selectCollection('$cmd.sys.inprog')->findOne(array('$all' => 1))["inprog"];
                $tot_ua = 0;
                $tot_un = 0;
                $tot_ba = 0;
                $tot_bn = 0;
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
                collect_stat($my2conn['conn'][$im], "m2db.usersActive", $tot_ua, $date);
                collect_stat($my2conn['conn'][$im], "m2db.usersNotActive", $tot_un, $date);
                collect_stat($my2conn['conn'][$im], "m2db.sysActive", $tot_ba, $date);
                collect_stat($my2conn['conn'][$im], "m2db.sysNotActive", $tot_bn, $date);
*/

            }
        }
    }
    else {
        //daily stat
  	for($im=1;$im<=count($my2conn["conn"]);$im++) {
            echo("Getting info from " . $my2conn['conn'][$im] . "  " . $my2conn['host'][$im] . " ");
	    echo(date("Y/m/d H:i:s")."\n");
            $conn_error=0;
            try {
                $host="mongodb://";
                if($my2conn['user'][$im]!="" && $my2conn['passwd'][$im]!="")
                    $host.=$my2conn['user'][$im].":".$my2conn['passwd'][$im]."@";
                $host.=$my2conn['host'][$im];
                $cnn = new MongoClient($host);
            } catch (MongoConnectionException $e) {
                $conn_error=1;
                echo(" Error connecting to MongoDB server\n");
            } catch (MongoException $e) {
                $conn_error=1;
                echo(' Error: ' . $e->getMessage()."\n");
            }

            if($conn_error==0) {
                $db = $cnn->selectDB("admin");
                echo(" Getting DB statistics \n");
                $record = $cnn->listDBs();
                $tot = 0;
                $tot_d = 0; $tot_i = 0; $tot_s = 0; $tot_c = 0; $tot_x = 0;
                foreach ($record["databases"] as $obj) {
                    collect_stat($my2conn['conn'][$im], "dbStats.".$obj["name"].".sizeOnDisk", $obj["sizeOnDisk"], $date);
                    $tot += $obj["sizeOnDisk"];
                    $x = $cnn->$obj["name"]->command(array('dbStats' => 1));
                    $tot_d += $x["dataSize"];
                    $tot_i += $x["indexSize"];
                    $tot_s += $x["storageSize"];
                    if (isset($x["collections"])) $tot_c += $x["collections"];
                    $tot_x += $x["indexes"];
                    foreach ($x as $key => $val) {
                        if (!is_array($val)) {
                            collect_stat($my2conn['conn'][$im], "dbStats." . $obj["name"] . "." . $key, $val, $date);
                        }
                    }
		}

                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.sizeOnDisk", $tot, $date);
                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.dataSize", $tot_d, $date);
                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.indexSize", $tot_i, $date);
                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.storageSize", $tot_s, $date);
                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.collections", $tot_c, $date);
                collect_stat($my2conn['conn'][$im], "dbStats.TOTAL.indexes", $tot_x, $date);
            }
        }
    }

    mysqli_close($mycnn);
?>

