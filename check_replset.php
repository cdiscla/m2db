<?php
	$rs_description="";
	if(isset($cnn)) {
                $isMaster=$cnn->test->command(array('isMaster' => 1));
                if(isset($isMaster["ismaster"])) {
                        if($isMaster["ismaster"]=="true") {
                                if(isset($isMaster["msg"]))
                                        $rs_description="<b>Router</b> (mongos)";
                                else
                                        if(isset($isMaster["setName"]))
                                                $rs_description="<b>Primary</b> in replSet: <b>".$isMaster["setName"]."</b>";
                                        else
                                                $rs_description="<b>Standalone</b>";
                        }
                        else if($isMaster["secondary"]=="true")
                                $rs_description="<b>Secondary</b> in replSet: <b>".$isMaster["setName"]."</b>";
                        else if($isMaster["arbiterOnly"]=="true")
                                $rs_description="<b>Arbiter</b> in replSet: <b>".$isMaster["setName"]."</b>";
                }

                $shStatus=$cnn->admin->command(array('shardingState' => 1));
                if(isset($shStatus["enabled"])) {
                        if($shStatus["enabled"]=="1")
                                if($shStatus["shardName"]=="")
                                        $rs_description=$rs_description." as shard: <b>Config Server</b> ";
                                else
                                        $rs_description=$rs_description." for shard: <b>".$shStatus["shardName"]."</b>";
                }
	}
