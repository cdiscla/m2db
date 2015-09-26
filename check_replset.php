<?php
	$rs_description="";
	if(isset($cnn)) {
		$isMaster=$cnn->test->command(array('isMaster' => 1));
		if(isset($isMaster["ismaster"])) {
			$rs=$isMaster["setName"];
			if($isMaster["ismaster"]=="true") 
				$rs_description=" - actually <i>primary</i> in <b>".$rs."</b> replSet";
			else if($isMaster["secondary"]=="true") 
				$rs_description=" - actually <i>secondary</i> in <b>".$rs."</b> replSet";
			else if($isMaster["arbiterOnly"]=="true") 
				$rs_description=" - actually <i>arbiter</i> in <b>".$rs."</b> replSet";
		}
	}
